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

/** سيريالات الأجهزة (كل الصفوف من الجدول) */
function ecm_backup_collect_serials(): array {
    if ( ! function_exists( 'ecm_serial_table' ) ) {
        return [];
    }
    global $wpdb;
    $table = ecm_serial_table();
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
        return [];
    }
    return $wpdb->get_results( "SELECT * FROM `{$table}`", ARRAY_A ) ?: []; // phpcs:ignore
}

/** ربط الأجهزة + توكنات التطبيق (مربوطة بالإيميل) */
function ecm_backup_collect_user_app(): array {
    $users = get_users( [
        'meta_query'  => [
            'relation' => 'OR',
            [ 'key' => 'ecm_app_token', 'compare' => 'EXISTS' ],
            [ 'key' => 'ecm_app_device', 'compare' => 'EXISTS' ],
        ],
        'number'      => 9999,
        'count_total' => false,
    ] );
    $out = [];
    foreach ( $users as $u ) {
        $out[] = [
            'email'  => $u->user_email,
            'token'  => get_user_meta( $u->ID, 'ecm_app_token', true ),
            'device' => get_user_meta( $u->ID, 'ecm_app_device', true ),
        ];
    }
    return $out;
}

/** صفحات ECM (المحتوى + القالب + بيانات Elementor) */
function ecm_backup_collect_pages(): array {
    $titles = [];
    if ( function_exists( 'ecm_get_expected_pages' ) ) {
        foreach ( ecm_get_expected_pages() as $row ) {
            $titles[] = $row[0];
        }
    }
    $titles   = array_unique( array_merge( $titles, [ 'ECM Home' ] ) );
    $meta_keys = [ '_wp_page_template', '_elementor_data', '_elementor_edit_mode',
        '_elementor_version', '_elementor_template_type', '_elementor_page_settings' ];
    $front_id = (int) get_option( 'page_on_front' );

    $pages = [];
    foreach ( $titles as $title ) {
        $p = function_exists( 'ecm_page_by_title' ) ? ecm_page_by_title( $title ) : null;
        if ( ! $p ) {
            continue;
        }
        $meta = [];
        foreach ( $meta_keys as $mk ) {
            $mv = get_post_meta( $p->ID, $mk, true );
            if ( '' !== $mv && null !== $mv ) {
                $meta[ $mk ] = $mv;
            }
        }
        $pages[] = [
            'title'    => $p->post_title,
            'name'     => $p->post_name,
            'status'   => $p->post_status,
            'content'  => $p->post_content,
            'is_front' => ( $p->ID === $front_id ),
            'meta'     => $meta,
        ];
    }
    return $pages;
}

/** يجمع كل بيانات النسخة الاحتياطية الكاملة */
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
        'serials'    => ecm_backup_collect_serials(),
        'user_app'   => ecm_backup_collect_user_app(),
        'pages'      => ecm_backup_collect_pages(),
    ];
}

/** استعادة سيريالات الأجهزة */
function ecm_backup_restore_serials( array $rows ): int {
    if ( empty( $rows ) || ! function_exists( 'ecm_serial_table' ) ) {
        return 0;
    }
    global $wpdb;
    $table = ecm_serial_table();
    // اتأكد إن الجدول موجود
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table
        && function_exists( 'ecm_serials_install' ) ) {
        delete_option( 'ecm_serials_db_v3' );
        ecm_serials_install();
    }
    $cols = [ 'id', 'serial', 'status', 'user_id', 'email', 'token',
        'warranty_months', 'free_all', 'activated_at', 'created_at' ];
    $n = 0;
    foreach ( $rows as $row ) {
        if ( empty( $row['serial'] ) ) {
            continue;
        }
        $clean = [];
        foreach ( $cols as $c ) {
            if ( array_key_exists( $c, $row ) ) {
                $clean[ $c ] = $row[ $c ];
            }
        }
        if ( $wpdb->replace( $table, $clean ) ) { // phpcs:ignore
            $n++;
        }
    }
    return $n;
}

/** استعادة ربط الأجهزة وتوكنات التطبيق */
function ecm_backup_restore_user_app( array $list ): int {
    $n = 0;
    foreach ( $list as $row ) {
        if ( empty( $row['email'] ) ) {
            continue;
        }
        $u = get_user_by( 'email', $row['email'] );
        if ( ! $u ) {
            continue;
        }
        if ( ! empty( $row['token'] ) ) {
            update_user_meta( $u->ID, 'ecm_app_token', $row['token'] );
        }
        if ( ! empty( $row['device'] ) && is_array( $row['device'] ) ) {
            update_user_meta( $u->ID, 'ecm_app_device', $row['device'] );
        }
        $n++;
    }
    return $n;
}

/** استعادة صفحات ECM */
function ecm_backup_restore_pages( array $pages ): int {
    $n = 0;
    foreach ( $pages as $pg ) {
        if ( empty( $pg['title'] ) ) {
            continue;
        }
        $existing = function_exists( 'ecm_page_by_title' ) ? ecm_page_by_title( $pg['title'] ) : null;
        $postarr  = [
            'post_title'   => $pg['title'],
            'post_content' => $pg['content'] ?? '',
            'post_status'  => $pg['status'] ?? 'publish',
            'post_type'    => 'page',
        ];
        if ( $existing ) {
            $postarr['ID'] = $existing->ID;
            $pid = wp_update_post( $postarr );
        } else {
            $pid = wp_insert_post( $postarr );
        }
        if ( ! $pid || is_wp_error( $pid ) ) {
            continue;
        }
        if ( ! empty( $pg['meta'] ) && is_array( $pg['meta'] ) ) {
            foreach ( $pg['meta'] as $mk => $mv ) {
                update_post_meta( $pid, $mk, $mv );
            }
        }
        if ( ! empty( $pg['is_front'] ) ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', $pid );
        }
        $n++;
    }
    return $n;
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

    if ( ! is_array( $data ) || ( empty( $data['options'] ) && empty( $data['theme_mods'] )
        && empty( $data['serials'] ) && empty( $data['pages'] ) ) ) {
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

    // السيريالات + ربط الأجهزة + الصفحات
    if ( ! empty( $data['serials'] ) && is_array( $data['serials'] ) ) {
        $count += ecm_backup_restore_serials( $data['serials'] );
    }
    if ( ! empty( $data['user_app'] ) && is_array( $data['user_app'] ) ) {
        $count += ecm_backup_restore_user_app( $data['user_app'] );
    }
    if ( ! empty( $data['pages'] ) && is_array( $data['pages'] ) ) {
        $count += ecm_backup_restore_pages( $data['pages'] );
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

        <p class="description" style="max-width:780px;">
            <?php esc_html_e( 'نسخة كاملة من كل حاجة: السيريالات والأجهزة، إعدادات البريد والألوان والتوكنات، إعدادات المتجر والمظهر، وصفحات ECM (بمحتواها وقوالبها). نزّلها وارجّعها وقت ما تحب.', 'ecm-theme' ); ?>
        </p>

        <div style="background:#f4f8f1;border:1px solid #e2ebdc;border-radius:12px;padding:14px 18px;max-width:780px;margin:8px 0 4px;">
            <strong><?php esc_html_e( 'النسخة بتشمل:', 'ecm-theme' ); ?></strong>
            <span style="color:#5a6160;">
                🔑 <?php esc_html_e( 'السيريالات والأجهزة', 'ecm-theme' ); ?> ·
                📧 <?php esc_html_e( 'البريد', 'ecm-theme' ); ?> ·
                🎨 <?php esc_html_e( 'الألوان والمظهر', 'ecm-theme' ); ?> ·
                🛒 <?php esc_html_e( 'المتجر', 'ecm-theme' ); ?> ·
                📄 <?php esc_html_e( 'الصفحات', 'ecm-theme' ); ?> ·
                🔗 <?php esc_html_e( 'ربط التطبيقات', 'ecm-theme' ); ?>
            </span>
        </div>

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
