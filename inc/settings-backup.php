<?php
/**
 * ECM — النسخ الاحتياطي للإعدادات (Backup / Restore)
 *
 * تصدير كل إعدادات ECM لملف JSON، واستيرادها تاني بضغطة.
 * بيشمل: إعدادات البريد، ألوان الإيميل، التوكنات، إعدادات المتجر،
 * وكل إعدادات الـ Customizer (theme mods).
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** قائمة الأوبشنز اللي بتتحفظ (allowlist — للأمان وقت الاستيراد) */
function ecm_backup_option_keys(): array {
    return [
        // البريد + الإيميل
        'ecm_smtp', 'ecm_smtp_status', 'ecm_email_styled_v1',
        'woocommerce_email_base_color', 'woocommerce_email_background_color',
        'woocommerce_email_body_background_color', 'woocommerce_email_text_color',
        'woocommerce_email_header_image',
        // التوكنات والمفاتيح
        'ecm_api_token', 'ecm_link_secret', 'ecm_google_client_id',
        'ecm_gh_repo', 'ecm_gh_token',
        // بيانات التطبيقات/السوفت وير
        'ecm_apps_data', 'ecm_firmware_data', 'ecm_sw_version',
        // المتجر والتواصل
        'ecm_free_store', 'ecm_free_store_text', 'ecm_email', 'ecm_whatsapp',
    ];
}

/** يجمع كل بيانات النسخة الاحتياطية */
function ecm_backup_collect(): array {
    $options = [];
    foreach ( ecm_backup_option_keys() as $k ) {
        $v = get_option( $k, null );
        if ( null !== $v ) {
            $options[ $k ] = $v;
        }
    }
    return [
        '_meta' => [
            'plugin'  => 'ECM',
            'version' => defined( 'ECM_VERSION' ) ? ECM_VERSION : '',
            'site'    => home_url(),
            'date'    => current_time( 'mysql' ),
        ],
        'options'    => $options,
        'theme_mods' => get_theme_mods() ?: [],
    ];
}

// ── تصدير: تنزيل ملف JSON ─────────────────────────────────────
add_action( 'admin_post_ecm_export_settings', function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'غير مصرّح', 'ecm-theme' ) );
    }
    check_admin_referer( 'ecm_backup' );

    $data = ecm_backup_collect();
    $json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    $name = 'ecm-backup-' . gmdate( 'Y-m-d-His' ) . '.json';

    nocache_headers();
    header( 'Content-Type: application/json; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="' . $name . '"' );
    header( 'Content-Length: ' . strlen( $json ) );
    echo $json; // phpcs:ignore WordPress.Security.EscapeOutput
    exit;
} );

// ── استيراد: قراءة الملف واستعادة الإعدادات ──────────────────
add_action( 'admin_post_ecm_import_settings', function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'غير مصرّح', 'ecm-theme' ) );
    }
    check_admin_referer( 'ecm_backup' );

    $redirect = admin_url( 'admin.php?page=ecm-backup' );

    if ( empty( $_FILES['ecm_backup_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['ecm_backup_file']['tmp_name'] ) ) {
        wp_safe_redirect( add_query_arg( 'imported', 'nofile', $redirect ) );
        exit;
    }

    $raw  = file_get_contents( $_FILES['ecm_backup_file']['tmp_name'] ); // phpcs:ignore
    $data = json_decode( (string) $raw, true );

    if ( ! is_array( $data ) || empty( $data['options'] ) && empty( $data['theme_mods'] ) ) {
        wp_safe_redirect( add_query_arg( 'imported', 'bad', $redirect ) );
        exit;
    }

    $count   = 0;
    $allowed = ecm_backup_option_keys();

    // الأوبشنز (allowlist فقط)
    if ( ! empty( $data['options'] ) && is_array( $data['options'] ) ) {
        foreach ( $data['options'] as $k => $v ) {
            if ( in_array( $k, $allowed, true ) ) {
                update_option( $k, $v );
                $count++;
            }
        }
    }

    // theme mods (إعدادات الـ Customizer)
    if ( ! empty( $data['theme_mods'] ) && is_array( $data['theme_mods'] ) ) {
        foreach ( $data['theme_mods'] as $k => $v ) {
            set_theme_mod( $k, $v );
            $count++;
        }
    }

    wp_safe_redirect( add_query_arg( 'imported', $count, $redirect ) );
    exit;
} );

// ── صفحة النسخ الاحتياطي ──────────────────────────────────────
add_action( 'admin_menu', function () {
    add_submenu_page(
        'ecm-dashboard',
        __( 'النسخ الاحتياطي', 'ecm-theme' ),
        '💾 ' . __( 'نسخة احتياطية', 'ecm-theme' ),
        'manage_options',
        'ecm-backup',
        'ecm_backup_page'
    );
}, 31 );

function ecm_backup_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $imported = isset( $_GET['imported'] ) ? sanitize_text_field( wp_unslash( $_GET['imported'] ) ) : '';
    ?>
    <style>
        .ecm-bk-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;margin-top:18px;max-width:900px;}
        .ecm-bk-card{background:#fff;border:1px solid #e4e7e4;border-radius:14px;padding:24px;}
        .ecm-bk-card h2{margin:0 0 6px;color:#0e1a10;font-size:17px;}
        .ecm-bk-card p{color:#5a6160;font-size:13.5px;line-height:1.7;margin:0 0 16px;}
        .ecm-bk-card .ico{font-size:32px;}
        .ecm-bk-btn{display:inline-block;background:#0e1a10;color:#fff !important;text-decoration:none;
            border:0;cursor:pointer;border-radius:9px;padding:11px 20px;font-weight:700;font-size:14px;}
        .ecm-bk-btn:hover{background:#9CFF00;color:#0e1a10 !important;}
        .ecm-bk-file{margin-bottom:12px;display:block;}
    </style>
    <div class="wrap">
        <h1>💾 <?php esc_html_e( 'النسخ الاحتياطي للإعدادات', 'ecm-theme' ); ?></h1>

        <?php if ( '' !== $imported ) : ?>
            <?php if ( 'nofile' === $imported ) : ?>
                <div class="notice notice-error"><p><?php esc_html_e( 'اختر ملف النسخة الأول.', 'ecm-theme' ); ?></p></div>
            <?php elseif ( 'bad' === $imported ) : ?>
                <div class="notice notice-error"><p><?php esc_html_e( 'الملف مش صالح أو تالف.', 'ecm-theme' ); ?></p></div>
            <?php else : ?>
                <div class="notice notice-success"><p><?php
                    /* translators: %d: count */
                    printf( esc_html__( 'تم الاستيراد ✅ — استُعيد %d إعداد.', 'ecm-theme' ), (int) $imported );
                ?></p></div>
            <?php endif; ?>
        <?php endif; ?>

        <p class="description" style="max-width:760px;">
            <?php esc_html_e( 'احفظ نسخة من كل إعداداتك (البريد، الألوان، التوكنات، إعدادات المتجر والمظهر) في ملف، وارجّعها وقت ما تحب.', 'ecm-theme' ); ?>
        </p>

        <div class="ecm-bk-grid">
            <div class="ecm-bk-card">
                <div class="ico">⬇️</div>
                <h2><?php esc_html_e( 'تنزيل نسخة احتياطية', 'ecm-theme' ); ?></h2>
                <p><?php esc_html_e( 'هينزّل ملف JSON فيه كل إعداداتك الحالية. احتفظ بيه في مكان آمن.', 'ecm-theme' ); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'ecm_backup' ); ?>
                    <input type="hidden" name="action" value="ecm_export_settings">
                    <button type="submit" class="ecm-bk-btn">⬇️ <?php esc_html_e( 'تنزيل النسخة', 'ecm-theme' ); ?></button>
                </form>
            </div>

            <div class="ecm-bk-card">
                <div class="ico">⬆️</div>
                <h2><?php esc_html_e( 'استعادة من نسخة', 'ecm-theme' ); ?></h2>
                <p><?php esc_html_e( 'ارفع ملف JSON اللي نزّلته قبل كده عشان ترجّع كل الإعدادات.', 'ecm-theme' ); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data"
                    onsubmit="return confirm('هيستبدل الإعدادات الحالية بالموجودة في الملف. تمام؟');">
                    <?php wp_nonce_field( 'ecm_backup' ); ?>
                    <input type="hidden" name="action" value="ecm_import_settings">
                    <input class="ecm-bk-file" type="file" name="ecm_backup_file" accept="application/json,.json" required>
                    <button type="submit" class="ecm-bk-btn">⬆️ <?php esc_html_e( 'استعادة', 'ecm-theme' ); ?></button>
                </form>
            </div>
        </div>

        <p class="description" style="max-width:760px;margin-top:18px;">
            ⚠️ <?php esc_html_e( 'الملف بيحتوي على توكنات ومفاتيح حساسة — متشاركهوش مع حد.', 'ecm-theme' ); ?>
        </p>
    </div>
    <?php
}
