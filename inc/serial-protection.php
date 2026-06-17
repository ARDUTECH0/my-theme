<?php
/**
 * ECM — حماية الأجهزة بالسيريال + ربط بالإيميل
 *
 * - الأدمن بيضيف السيريالات الأصلية.
 * - فحص أصالة السيريال (شورت‌كود + REST).
 * - المستخدم يربط جهازه بحسابه/إيميله (مرة واحدة).
 * - REST API للتطبيق يتأكد إن السيريال مربوط بنفس الإيميل/التوكن.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** اسم جدول السيريالات */
function ecm_serial_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'ecm_serials';
}

// ── إنشاء/تحديث الجدول ────────────────────────────────────────
function ecm_serials_install() {
    if ( get_option( 'ecm_serials_db_v2' ) ) {
        return;
    }
    global $wpdb;
    $table   = ecm_serial_table();
    $charset = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        serial VARCHAR(100) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'genuine',
        user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        email VARCHAR(190) NOT NULL DEFAULT '',
        token VARCHAR(64) NOT NULL DEFAULT '',
        warranty_months INT NOT NULL DEFAULT 12,
        activated_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY serial (serial),
        KEY token (token)
    ) {$charset};" );

    update_option( 'ecm_serials_db_v2', 1 );
    // نحتاج flush للـ endpoint بتاع "أجهزتي"
    update_option( 'ecm_serials_flush', 1 );
}
add_action( 'admin_init', 'ecm_serials_install' );
add_action( 'init', 'ecm_serials_install' );

// ── دوال مساعدة ───────────────────────────────────────────────
function ecm_serial_normalize( string $s ): string {
    return strtoupper( trim( preg_replace( '/\s+/', '', $s ) ) );
}

function ecm_serial_find( string $serial ) {
    global $wpdb;
    $serial = ecm_serial_normalize( $serial );
    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . ecm_serial_table() . ' WHERE serial = %s', $serial ) );
}

function ecm_serial_find_by_token( string $token ) {
    global $wpdb;
    if ( '' === $token ) {
        return null;
    }
    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . ecm_serial_table() . ' WHERE token = %s', $token ) );
}

function ecm_serial_add( string $serial, int $warranty = 12 ): bool {
    global $wpdb;
    $serial = ecm_serial_normalize( $serial );
    if ( '' === $serial || ecm_serial_find( $serial ) ) {
        return false;
    }
    return (bool) $wpdb->insert( ecm_serial_table(), [
        'serial'          => $serial,
        'status'          => 'genuine',
        'warranty_months' => max( 0, $warranty ),
        'created_at'      => current_time( 'mysql' ),
    ] );
}

/** هل المستخدم عنده جهاز مفعّل؟ */
function ecm_user_has_active_device( int $user_id ): bool {
    if ( ! $user_id ) {
        return false;
    }
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . ecm_serial_table() . ' WHERE user_id = %d', $user_id ) ) > 0;
}

/** أحدث سيريال مفعّل للمستخدم (أو '') */
function ecm_user_primary_serial( int $user_id ): string {
    if ( ! $user_id ) {
        return '';
    }
    global $wpdb;
    $s = $wpdb->get_var( $wpdb->prepare( 'SELECT serial FROM ' . ecm_serial_table() . ' WHERE user_id = %d ORDER BY activated_at DESC LIMIT 1', $user_id ) );
    return $s ? (string) $s : '';
}

/** رابط صفحة تفعيل الجهاز */
function ecm_activation_page_url(): string {
    $page = function_exists( 'ecm_page_by_title' ) ? ecm_page_by_title( 'تفعيل الجهاز' ) : null;
    return $page ? get_permalink( $page->ID ) : home_url( '/' );
}

/** توكن الـ API (يتولّد مرة ويتخزّن) */
function ecm_get_api_token(): string {
    $t = (string) get_option( 'ecm_api_token', '' );
    if ( '' === $t ) {
        $t = wp_generate_password( 48, false );
        update_option( 'ecm_api_token', $t );
    }
    return $t;
}

/** صلاحية الـ API: أدمن (كوكيز) أو توكن صحيح في الهيدر/الرابط */
function ecm_api_auth(): bool {
    if ( current_user_can( 'manage_woocommerce' ) ) {
        return true;
    }
    $token = '';
    if ( ! empty( $_SERVER['HTTP_X_ECM_TOKEN'] ) ) {
        $token = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_ECM_TOKEN'] ) );
    } elseif ( isset( $_GET['api_key'] ) ) {
        $token = sanitize_text_field( wp_unslash( $_GET['api_key'] ) );
    }
    return '' !== $token && hash_equals( ecm_get_api_token(), $token );
}

function ecm_mask_email( string $email ): string {
    if ( ! $email || strpos( $email, '@' ) === false ) {
        return '';
    }
    list( $user, $domain ) = explode( '@', $email, 2 );
    $u = mb_substr( $user, 0, 2 ) . str_repeat( '*', max( 1, mb_strlen( $user ) - 2 ) );
    return $u . '@' . $domain;
}

/** ربط السيريال بمستخدم. يرجّع [success, message, token] */
function ecm_serial_bind( string $serial, int $user_id, string $email ): array {
    global $wpdb;
    $row = ecm_serial_find( $serial );
    if ( ! $row ) {
        return [ false, __( 'السيريال ده مش أصلي أو غير موجود.', 'ecm-theme' ), '' ];
    }
    if ( (int) $row->user_id > 0 ) {
        if ( (int) $row->user_id === $user_id ) {
            return [ true, __( 'الجهاز ده مربوط بحسابك بالفعل ✅', 'ecm-theme' ), $row->token ];
        }
        return [ false, __( 'الجهاز ده متسجّل بحساب تاني بالفعل ❌', 'ecm-theme' ), '' ];
    }
    $token = wp_generate_password( 40, false );
    $wpdb->update( ecm_serial_table(), [
        'user_id'      => $user_id,
        'email'        => $email,
        'token'        => $token,
        'activated_at' => current_time( 'mysql' ),
    ], [ 'id' => $row->id ] );
    return [ true, __( 'تم ربط الجهاز بحسابك بنجاح ✅', 'ecm-theme' ), $token ];
}


// ════════════════════════════════════════════════════════════
// §  لوحة الأدمن — إضافة وعرض السيريالات
// ════════════════════════════════════════════════════════════
add_action( 'admin_menu', function () {
    add_menu_page(
        __( 'حماية الأجهزة', 'ecm-theme' ),
        __( '🛡️ حماية الأجهزة', 'ecm-theme' ),
        'manage_options',
        'ecm-serials',
        'ecm_serials_admin_page',
        'dashicons-shield',
        58
    );
} );

function ecm_serials_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    global $wpdb;
    $table = ecm_serial_table();

    // إضافة سيريالات
    if ( isset( $_POST['ecm_add_serials'] ) && check_admin_referer( 'ecm_serials' ) ) {
        $warranty = isset( $_POST['ecm_warranty'] ) ? max( 0, (int) $_POST['ecm_warranty'] ) : 12;
        $lines    = preg_split( '/[\r\n,]+/', (string) wp_unslash( $_POST['ecm_serials_input'] ?? '' ) );
        $added    = 0;
        foreach ( $lines as $line ) {
            if ( ecm_serial_add( sanitize_text_field( $line ), $warranty ) ) {
                $added++;
            }
        }
        echo '<div class="notice notice-success"><p>' . sprintf( esc_html__( 'تم إضافة %1$d سيريال بضمان %2$d شهر.', 'ecm-theme' ), (int) $added, (int) $warranty ) . '</p></div>';
    }

    // تعديل مدة الضمان لجهاز
    if ( isset( $_POST['ecm_set_warranty'], $_POST['ecm_sid'] ) && check_admin_referer( 'ecm_serials' ) ) {
        $wpdb->update( $table, [ 'warranty_months' => max( 0, (int) $_POST['ecm_warranty_val'] ) ], [ 'id' => (int) $_POST['ecm_sid'] ] );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'تم تحديث مدة الضمان.', 'ecm-theme' ) . '</p></div>';
    }

    // فك ربط
    if ( isset( $_GET['unbind'] ) && check_admin_referer( 'ecm_unbind_' . (int) $_GET['unbind'] ) ) {
        $wpdb->update( $table, [ 'user_id' => 0, 'email' => '', 'token' => '', 'activated_at' => null ], [ 'id' => (int) $_GET['unbind'] ] );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'تم فك ربط الجهاز.', 'ecm-theme' ) . '</p></div>';
    }

    // توليد توكن API جديد
    if ( isset( $_POST['ecm_regen_token'] ) && check_admin_referer( 'ecm_serials' ) ) {
        update_option( 'ecm_api_token', wp_generate_password( 48, false ) );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'تم توليد توكن API جديد.', 'ecm-theme' ) . '</p></div>';
    }

    $rows     = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 200" );
    $total    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    $bound    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE user_id > 0" );
    $avail    = $total - $bound;
    $expiring = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE user_id > 0 AND activated_at IS NOT NULL AND DATE_ADD(activated_at, INTERVAL warranty_months MONTH) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)" );
    $api_token = ecm_get_api_token();
    $api_base  = rest_url( 'ecm/v1' );
    ?>
    <style>
        .ecm-sp { max-width: 1100px; }
        .ecm-sp h1 { display:flex; align-items:center; gap:8px; }
        .ecm-sp-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin:18px 0 26px; }
        .ecm-sp-card { background:#fff; border:1px solid #e2e4e7; border-radius:12px; padding:18px 20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .ecm-sp-card .n { font-size:30px; font-weight:800; line-height:1; }
        .ecm-sp-card .l { color:#646970; font-size:13px; margin-top:6px; }
        .ecm-sp-card.green .n{color:#1a7f37;} .ecm-sp-card.blue .n{color:#2271b1;} .ecm-sp-card.orange .n{color:#bd8600;} .ecm-sp-card.grey .n{color:#3c434a;}
        .ecm-sp-box { background:#fff; border:1px solid #e2e4e7; border-radius:12px; padding:22px 24px; margin-bottom:22px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .ecm-sp-box h2 { margin-top:0; }
        .ecm-sp-token { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .ecm-sp-token code { background:#1d2327; color:#7CFF6B; padding:10px 14px; border-radius:8px; font-size:13px; word-break:break-all; flex:1; min-width:280px; }
        .ecm-sp-endpoints { margin-top:14px; }
        .ecm-sp-endpoints code { display:block; background:#f6f7f7; border:1px solid #e2e4e7; border-radius:6px; padding:8px 12px; margin:6px 0; font-size:12.5px; color:#1d2327; }
        .ecm-sp-table { width:100%; border-collapse:collapse; }
        .ecm-sp-table th { text-align:start; background:#f6f7f7; padding:11px 12px; font-size:12px; color:#646970; border-bottom:1px solid #e2e4e7; }
        .ecm-sp-table td { padding:11px 12px; border-bottom:1px solid #f0f0f1; font-size:13px; }
        .ecm-sp-table tr:hover td { background:#fafafa; }
        .ecm-pill { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .ecm-pill.on { background:#e6f4ea; color:#1a7f37; } .ecm-pill.off { background:#eef0f2; color:#646970; }
    </style>
    <div class="wrap ecm-sp">
        <h1>🛡️ <?php esc_html_e( 'حماية الأجهزة بالسيريال', 'ecm-theme' ); ?></h1>

        <div class="ecm-sp-stats">
            <div class="ecm-sp-card grey"><div class="n"><?php echo (int) $total; ?></div><div class="l"><?php esc_html_e( 'إجمالي السيريالات', 'ecm-theme' ); ?></div></div>
            <div class="ecm-sp-card green"><div class="n"><?php echo (int) $bound; ?></div><div class="l"><?php esc_html_e( 'أجهزة مفعّلة', 'ecm-theme' ); ?></div></div>
            <div class="ecm-sp-card blue"><div class="n"><?php echo (int) $avail; ?></div><div class="l"><?php esc_html_e( 'متاحة (غير مفعّلة)', 'ecm-theme' ); ?></div></div>
            <div class="ecm-sp-card orange"><div class="n"><?php echo (int) $expiring; ?></div><div class="l"><?php esc_html_e( 'ضمانها يقرب يخلص (30 يوم)', 'ecm-theme' ); ?></div></div>
        </div>

        <div class="ecm-sp-box">
            <h2>🔌 <?php esc_html_e( 'الـ API والتوكن', 'ecm-theme' ); ?></h2>
            <p><?php esc_html_e( 'استخدم التوكن ده في تطبيقك للوصول لبيانات الأجهزة (في الهيدر X-ECM-Token أو ?api_key=).', 'ecm-theme' ); ?></p>
            <div class="ecm-sp-token">
                <code id="ecm-api-token"><?php echo esc_html( $api_token ); ?></code>
                <button type="button" class="button" onclick="navigator.clipboard.writeText(document.getElementById('ecm-api-token').textContent);this.textContent='✓ تم النسخ';"><?php esc_html_e( 'نسخ', 'ecm-theme' ); ?></button>
                <form method="post" style="display:inline;" onsubmit="return confirm('توليد توكن جديد هيوقف القديم. متأكد؟');">
                    <?php wp_nonce_field( 'ecm_serials' ); ?>
                    <button class="button" name="ecm_regen_token" value="1"><?php esc_html_e( 'توليد جديد', 'ecm-theme' ); ?></button>
                </form>
            </div>
            <div class="ecm-sp-endpoints">
                <strong><?php esc_html_e( 'نقاط النهاية:', 'ecm-theme' ); ?></strong>
                <code>GET <?php echo esc_html( $api_base ); ?>/devices?api_key=TOKEN</code>
                <code>GET <?php echo esc_html( $api_base ); ?>/devices?status=activated&amp;warranty=valid&amp;search=ECM&amp;limit=50&amp;offset=0</code>
                <code>GET <?php echo esc_html( $api_base ); ?>/stats?api_key=TOKEN</code>
                <code>GET <?php echo esc_html( $api_base ); ?>/verify?serial=ECM-0001&amp;email=USER (عام — للتطبيق)</code>
            </div>
        </div>

        <div class="ecm-sp-box">
            <h2>➕ <?php esc_html_e( 'إضافة سيريالات أصلية', 'ecm-theme' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field( 'ecm_serials' ); ?>
                <p><?php esc_html_e( 'اكتب سيريال في كل سطر (أو افصل بفاصلة):', 'ecm-theme' ); ?></p>
                <textarea name="ecm_serials_input" rows="5" style="width:100%;max-width:560px;" placeholder="ECM-0001&#10;ECM-0002"></textarea>
                <p>
                    <label><?php esc_html_e( 'مدة الضمان (بالشهور):', 'ecm-theme' ); ?>
                        <input type="number" name="ecm_warranty" value="12" min="0" max="120" style="width:90px;">
                    </label>
                </p>
                <p><button class="button button-primary" name="ecm_add_serials" value="1"><?php esc_html_e( 'إضافة', 'ecm-theme' ); ?></button></p>
            </form>
        </div>

        <div class="ecm-sp-box">
        <h2>📋 <?php esc_html_e( 'آخر 200 سيريال', 'ecm-theme' ); ?></h2>
        <table class="ecm-sp-table">
            <thead><tr>
                <th><?php esc_html_e( 'السيريال', 'ecm-theme' ); ?></th>
                <th><?php esc_html_e( 'الحالة', 'ecm-theme' ); ?></th>
                <th><?php esc_html_e( 'مربوط بـ', 'ecm-theme' ); ?></th>
                <th><?php esc_html_e( 'تاريخ التفعيل', 'ecm-theme' ); ?></th>
                <th><?php esc_html_e( 'متبقّي للضمان', 'ecm-theme' ); ?></th>
                <th><?php esc_html_e( 'الضمان (شهور)', 'ecm-theme' ); ?></th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $r ) :
                list( $warr_left_txt, $warr_ok ) = $r->user_id ? ecm_warranty_left( $r->activated_at, (int) $r->warranty_months ) : [ '—', false ]; ?>
                <tr>
                    <td><code><?php echo esc_html( $r->serial ); ?></code></td>
                    <td><?php echo $r->user_id
                        ? '<span class="ecm-pill on">🔒 ' . esc_html__( 'مفعّل', 'ecm-theme' ) . '</span>'
                        : '<span class="ecm-pill off">' . esc_html__( 'متاح', 'ecm-theme' ) . '</span>'; ?></td>
                    <td><?php echo $r->email ? esc_html( $r->email ) : '—'; ?></td>
                    <td><?php echo $r->activated_at ? esc_html( $r->activated_at ) : '—'; ?></td>
                    <td><?php
                        if ( ! $r->user_id ) {
                            echo '—';
                        } elseif ( $warr_ok ) {
                            echo '<span style="color:#1a7f37;font-weight:600;">🟢 ' . esc_html( $warr_left_txt ) . '</span>';
                        } else {
                            echo '<span style="color:#d63638;font-weight:600;">🔴 ' . esc_html( $warr_left_txt ) . '</span>';
                        }
                    ?></td>
                    <td>
                        <form method="post" style="display:flex;gap:4px;align-items:center;">
                            <?php wp_nonce_field( 'ecm_serials' ); ?>
                            <input type="hidden" name="ecm_sid" value="<?php echo (int) $r->id; ?>">
                            <input type="number" name="ecm_warranty_val" value="<?php echo (int) ( $r->warranty_months ?? 12 ); ?>" min="0" max="120" style="width:70px;">
                            <button class="button button-small" name="ecm_set_warranty" value="1"><?php esc_html_e( 'حفظ', 'ecm-theme' ); ?></button>
                        </form>
                    </td>
                    <td><?php if ( $r->user_id ) : ?>
                        <a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ecm-serials&unbind=' . $r->id ), 'ecm_unbind_' . $r->id ) ); ?>" onclick="return confirm('فك ربط الجهاز؟');"><?php esc_html_e( 'فك الربط', 'ecm-theme' ); ?></a>
                    <?php endif; ?></td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="7"><?php esc_html_e( 'لا يوجد سيريالات بعد.', 'ecm-theme' ); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php
}


// ════════════════════════════════════════════════════════════
// §  شورت‌كود فحص الأصالة [ecm_serial_check]
// ════════════════════════════════════════════════════════════
add_shortcode( 'ecm_serial_check', function () {
    ob_start();
    ?>
    <div class="ecm-serial-check">
        <h3><?php esc_html_e( '🔍 تحقّق من أصالة جهازك', 'ecm-theme' ); ?></h3>
        <p><?php esc_html_e( 'اكتب سيريال الجهاز عشان تتأكد إنه أصلي.', 'ecm-theme' ); ?></p>
        <div class="ecm-serial-row">
            <input type="text" id="ecm-serial-input" placeholder="ECM-0000" autocomplete="off">
            <button type="button" id="ecm-serial-btn" class="ecm-btn-primary"><?php esc_html_e( 'تحقّق', 'ecm-theme' ); ?></button>
        </div>
        <div id="ecm-serial-result" class="ecm-serial-result"></div>
    </div>
    <script>
    ( function () {
        var btn = document.getElementById( 'ecm-serial-btn' );
        var inp = document.getElementById( 'ecm-serial-input' );
        var out = document.getElementById( 'ecm-serial-result' );
        if ( ! btn ) { return; }
        function check() {
            var v = ( inp.value || '' ).trim();
            if ( ! v ) { return; }
            out.className = 'ecm-serial-result is-loading';
            out.textContent = '...';
            var fd = new FormData();
            fd.append( 'action', 'ecm_serial_check' );
            fd.append( 'serial', v );
            fetch( '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', body: fd } )
                .then( function ( r ) { return r.json(); } )
                .then( function ( d ) {
                    out.className = 'ecm-serial-result ' + ( d.genuine ? 'is-ok' : 'is-bad' );
                    out.innerHTML = d.message;
                } )
                .catch( function () { out.className = 'ecm-serial-result is-bad'; out.textContent = 'خطأ في الاتصال'; } );
        }
        btn.addEventListener( 'click', check );
        inp.addEventListener( 'keydown', function ( e ) { if ( e.key === 'Enter' ) { check(); } } );
    } )();
    </script>
    <?php
    return ob_get_clean();
} );

// معالج فحص الأصالة
function ecm_ajax_serial_check() {
    $serial = isset( $_POST['serial'] ) ? sanitize_text_field( wp_unslash( $_POST['serial'] ) ) : '';
    $row    = $serial ? ecm_serial_find( $serial ) : null;

    if ( ! $row || 'genuine' !== $row->status ) {
        wp_send_json( [
            'genuine' => false,
            'message' => '❌ <strong>' . esc_html__( 'السيريال ده مش موجود — يُحتمل إنه مقلّد.', 'ecm-theme' ) . '</strong>',
        ] );
    }

    if ( (int) $row->user_id > 0 ) {
        wp_send_json( [
            'genuine' => true,
            'message' => '✅ <strong>' . esc_html__( 'جهاز أصلي', 'ecm-theme' ) . '</strong> — ' . sprintf( esc_html__( 'مسجّل ومربوط بحساب (%s).', 'ecm-theme' ), esc_html( ecm_mask_email( $row->email ) ) ),
        ] );
    }

    wp_send_json( [
        'genuine' => true,
        'message' => '✅ <strong>' . esc_html__( 'جهاز أصلي', 'ecm-theme' ) . '</strong> — ' . esc_html__( 'غير مسجّل بعد. تقدر تربطه بحسابك من «أجهزتي».', 'ecm-theme' ),
    ] );
}
add_action( 'wp_ajax_ecm_serial_check', 'ecm_ajax_serial_check' );
add_action( 'wp_ajax_nopriv_ecm_serial_check', 'ecm_ajax_serial_check' );


// ════════════════════════════════════════════════════════════
// §  صفحة «أجهزتي» في حساب WooCommerce
// ════════════════════════════════════════════════════════════
add_action( 'init', function () {
    add_rewrite_endpoint( 'my-devices', EP_ROOT | EP_PAGES );
    if ( get_option( 'ecm_serials_flush' ) ) {
        flush_rewrite_rules( false );
        delete_option( 'ecm_serials_flush' );
    }
} );

// بند في قائمة الحساب
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
    $new = [];
    foreach ( $items as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'downloads' === $key ) {
            $new['my-devices'] = __( 'أجهزتي', 'ecm-theme' );
        }
    }
    if ( ! isset( $new['my-devices'] ) ) {
        $logout = $new['customer-logout'] ?? null;
        if ( $logout ) {
            unset( $new['customer-logout'] );
        }
        $new['my-devices'] = __( 'أجهزتي', 'ecm-theme' );
        if ( $logout ) {
            $new['customer-logout'] = $logout;
        }
    }
    return $new;
} );

// أيقونة للبند الجديد
add_action( 'wp_head', function () {
    echo '<style>.woocommerce-MyAccount-navigation-link--my-devices a::before{content:"🛡️";}</style>';
} );

// معالجة ربط السيريال (POST)
add_action( 'template_redirect', function () {
    if ( empty( $_POST['ecm_bind_serial'] ) || ! is_user_logged_in() ) {
        return;
    }
    if ( ! isset( $_POST['ecm_bind_nonce'] ) || ! wp_verify_nonce( $_POST['ecm_bind_nonce'], 'ecm_bind_device' ) ) {
        return;
    }
    $serial = sanitize_text_field( wp_unslash( $_POST['ecm_bind_serial'] ) );
    $user   = wp_get_current_user();
    list( $ok, $msg ) = ecm_serial_bind( $serial, $user->ID, $user->user_email );
    wc_add_notice( $msg, $ok ? 'success' : 'error' );
} );

// محتوى صفحة «أجهزتي»
add_action( 'woocommerce_account_my-devices_endpoint', function () {
    global $wpdb;
    $user = wp_get_current_user();
    $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . ecm_serial_table() . ' WHERE user_id = %d ORDER BY id DESC', $user->ID ) );
    ?>
    <h3><?php esc_html_e( '🛡️ أجهزتي', 'ecm-theme' ); ?></h3>
    <p><?php esc_html_e( 'اربط جهازك بحسابك مرة واحدة — بعدها أكواد التحميل بتشتغل لحسابك بس.', 'ecm-theme' ); ?></p>

    <form method="post" class="ecm-bind-form">
        <?php wp_nonce_field( 'ecm_bind_device', 'ecm_bind_nonce' ); ?>
        <input type="text" name="ecm_bind_serial" placeholder="<?php esc_attr_e( 'اكتب سيريال جهازك', 'ecm-theme' ); ?>" required>
        <button type="submit" class="button"><?php esc_html_e( 'ربط الجهاز', 'ecm-theme' ); ?></button>
    </form>

    <?php if ( $rows ) : ?>
        <div class="ecm-act-devices">
            <?php foreach ( $rows as $r ) :
                $warr_m   = (int) ( $r->warranty_months ?? 12 );
                $act_ts   = $r->activated_at ? strtotime( $r->activated_at ) : time();
                $warr_end = strtotime( '+' . $warr_m . ' months', $act_ts );
                $valid    = ( current_time( 'timestamp' ) < $warr_end ); ?>
                <div class="ecm-act-card">
                    <div class="ecm-act-top"><span class="ecm-act-serial"><?php echo esc_html( $r->serial ); ?></span><span class="ecm-act-badge">✅ <?php esc_html_e( 'مفعّل', 'ecm-theme' ); ?></span></div>
                    <ul class="ecm-act-meta">
                        <li>📅 <?php esc_html_e( 'تاريخ التفعيل:', 'ecm-theme' ); ?> <strong><?php echo esc_html( date_i18n( 'Y/m/d', $act_ts ) ); ?></strong></li>
                        <li>⏳ <?php esc_html_e( 'مسجّل من:', 'ecm-theme' ); ?> <strong><?php echo esc_html( ecm_duration_ar( $act_ts ) ); ?></strong></li>
                        <li>🗓️ <?php esc_html_e( 'مدة الضمان:', 'ecm-theme' ); ?> <strong><?php echo (int) $warr_m; ?> <?php esc_html_e( 'شهر', 'ecm-theme' ); ?></strong></li>
                        <?php if ( $valid ) : ?>
                            <li class="ecm-warr-ok">🛡️ <?php esc_html_e( 'باقي على انتهاء الضمان:', 'ecm-theme' ); ?> <strong><?php echo esc_html( ecm_secs_to_ar( $warr_end - current_time( 'timestamp' ) ) ); ?></strong></li>
                            <li>📌 <?php esc_html_e( 'ساري حتى:', 'ecm-theme' ); ?> <strong><?php echo esc_html( date_i18n( 'Y/m/d', $warr_end ) ); ?></strong></li>
                        <?php else : ?>
                            <li class="ecm-warr-end">⚠️ <?php esc_html_e( 'انتهى الضمان في:', 'ecm-theme' ); ?> <strong><?php echo esc_html( date_i18n( 'Y/m/d', $warr_end ) ); ?></strong></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="ecm-no-devices"><?php esc_html_e( 'لسه مربطتش أي جهاز.', 'ecm-theme' ); ?></p>
    <?php endif; ?>
    <?php
} );


// ════════════════════════════════════════════════════════════
// §  صفحة تفعيل الجهاز [ecm_device_activation]  (دخول إجباري)
// ════════════════════════════════════════════════════════════

/** مدة الضمان بالأشهر (قابلة للتعديل بفلتر) */
function ecm_warranty_months(): int {
    return (int) apply_filters( 'ecm_warranty_months', 12 );
}

/** تحويل عدد ثواني لنص عربي: "3 أشهر و5 أيام" */
function ecm_secs_to_ar( int $secs ): string {
    $secs   = max( 0, $secs );
    $days   = (int) floor( $secs / 86400 );
    $months = (int) floor( $days / 30 );
    $rdays  = $days - ( $months * 30 );
    $parts  = [];
    if ( $months > 0 ) {
        $parts[] = $months . ' ' . ( 1 === $months ? 'شهر' : ( 2 === $months ? 'شهرين' : ( $months <= 10 ? 'أشهر' : 'شهر' ) ) );
    }
    if ( $rdays > 0 || $months === 0 ) {
        $parts[] = $rdays . ' ' . ( 1 === $rdays ? 'يوم' : ( 2 === $rdays ? 'يومين' : ( $rdays <= 10 ? 'أيام' : 'يوم' ) ) );
    }
    return implode( ' و', $parts );
}

/** مدة بالعربي من timestamp في الماضي لحد دلوقتي */
function ecm_duration_ar( int $from_ts ): string {
    return ecm_secs_to_ar( current_time( 'timestamp' ) - $from_ts );
}

/** المتبقّي على انتهاء الضمان (نص + حالة): [text, is_valid] */
function ecm_warranty_left( $activated_at, int $warranty_months ): array {
    if ( ! $activated_at ) {
        return [ '—', false ];
    }
    $act_ts   = strtotime( $activated_at );
    $warr_end = strtotime( '+' . max( 0, $warranty_months ) . ' months', $act_ts );
    $left     = $warr_end - current_time( 'timestamp' );
    if ( $left > 0 ) {
        return [ ecm_secs_to_ar( $left ), true ];
    }
    return [ __( 'انتهى الضمان', 'ecm-theme' ), false ];
}

add_shortcode( 'ecm_device_activation', function () {
    ob_start();
    echo '<div class="ecm-activate">';

    // ── دخول إجباري ──
    if ( ! is_user_logged_in() ) {
        $here  = get_permalink();
        $login = add_query_arg( 'redirect_to', rawurlencode( $here ? $here : home_url( '/' ) ), wc_get_page_permalink( 'myaccount' ) );
        echo '<div class="ecm-activate-gate">';
        echo '<div class="ecm-gate-ic">🔒</div>';
        echo '<h3>' . esc_html__( 'سجّل دخولك الأول', 'ecm-theme' ) . '</h3>';
        echo '<p>' . esc_html__( 'عشان تفعّل جهازك لازم تسجّل دخولك أو تعمل حساب جديد.', 'ecm-theme' ) . '</p>';
        echo '<a class="ecm-btn-primary" href="' . esc_url( $login ) . '">' . esc_html__( 'تسجيل الدخول / حساب جديد', 'ecm-theme' ) . '</a>';
        echo '</div></div>';
        return ob_get_clean();
    }

    $user = wp_get_current_user();
    $msg  = '';
    $type = '';

    // ── معالجة التفعيل ──
    if ( isset( $_POST['ecm_activate_serial'], $_POST['ecm_activate_nonce'] )
        && wp_verify_nonce( $_POST['ecm_activate_nonce'], 'ecm_activate' ) ) {
        $serial          = sanitize_text_field( wp_unslash( $_POST['ecm_activate_serial'] ) );
        list( $ok, $msg ) = ecm_serial_bind( $serial, $user->ID, $user->user_email );
        $type             = $ok ? 'ok' : 'bad';
    }

    echo '<h3>🛡️ ' . esc_html__( 'تفعيل الجهاز', 'ecm-theme' ) . '</h3>';
    echo '<p class="ecm-activate-lead">' . sprintf( esc_html__( 'أهلاً %s — اكتب سيريال جهازك عشان تفعّله وتربطه بحسابك.', 'ecm-theme' ), esc_html( $user->display_name ) ) . '</p>';

    echo '<form method="post" class="ecm-activate-form">';
    wp_nonce_field( 'ecm_activate', 'ecm_activate_nonce' );
    echo '<input type="text" name="ecm_activate_serial" placeholder="' . esc_attr__( 'مثال: ECM-0001', 'ecm-theme' ) . '" required>';
    echo '<button type="submit" class="ecm-btn-primary">' . esc_html__( 'تفعيل', 'ecm-theme' ) . '</button>';
    echo '</form>';

    if ( $msg ) {
        echo '<div class="ecm-activate-msg is-' . esc_attr( $type ) . '">' . esc_html( $msg ) . '</div>';
    }

    // ── أجهزة المستخدم المفعّلة ──
    global $wpdb;
    $rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . ecm_serial_table() . ' WHERE user_id = %d ORDER BY activated_at DESC', $user->ID ) );

    echo '<h4 class="ecm-activate-sub">' . esc_html__( 'أجهزتك المفعّلة', 'ecm-theme' ) . '</h4>';
    if ( $rows ) {
        echo '<div class="ecm-act-devices">';
        foreach ( $rows as $r ) {
            $warr_m   = (int) ( $r->warranty_months ?? 12 );
            $act_ts   = $r->activated_at ? strtotime( $r->activated_at ) : time();
            $dur      = ecm_duration_ar( $act_ts );
            $warr_end = strtotime( '+' . $warr_m . ' months', $act_ts );
            $valid    = ( current_time( 'timestamp' ) < $warr_end );
            echo '<div class="ecm-act-card">';
            echo '<div class="ecm-act-top"><span class="ecm-act-serial">' . esc_html( $r->serial ) . '</span><span class="ecm-act-badge">✅ ' . esc_html__( 'متفعّل', 'ecm-theme' ) . '</span></div>';
            echo '<ul class="ecm-act-meta">';
            echo '<li>📅 ' . esc_html__( 'تاريخ التفعيل:', 'ecm-theme' ) . ' <strong>' . esc_html( date_i18n( 'Y/m/d', $act_ts ) ) . '</strong></li>';
            echo '<li>⏳ ' . esc_html__( 'مسجّل من:', 'ecm-theme' ) . ' <strong>' . esc_html( $dur ) . '</strong></li>';
            echo '<li>🗓️ ' . esc_html__( 'مدة الضمان:', 'ecm-theme' ) . ' <strong>' . (int) $warr_m . ' ' . esc_html__( 'شهر', 'ecm-theme' ) . '</strong></li>';
            if ( $valid ) {
                $left_secs = $warr_end - current_time( 'timestamp' );
                echo '<li class="ecm-warr-ok">🛡️ ' . esc_html__( 'باقي على انتهاء الضمان:', 'ecm-theme' ) . ' <strong>' . esc_html( ecm_secs_to_ar( $left_secs ) ) . '</strong></li>';
                echo '<li>📌 ' . esc_html__( 'الضمان ساري حتى:', 'ecm-theme' ) . ' <strong>' . esc_html( date_i18n( 'Y/m/d', $warr_end ) ) . '</strong></li>';
            } else {
                echo '<li class="ecm-warr-end">⚠️ ' . esc_html__( 'انتهى الضمان في:', 'ecm-theme' ) . ' <strong>' . esc_html( date_i18n( 'Y/m/d', $warr_end ) ) . '</strong></li>';
            }
            echo '</ul></div>';
        }
        echo '</div>';
    } else {
        echo '<p class="ecm-no-devices">' . esc_html__( 'لسه مفعّلتش أي جهاز. اكتب السيريال فوق.', 'ecm-theme' ) . '</p>';
    }

    echo '</div>';
    return ob_get_clean();
} );

// ── إنشاء صفحة «تفعيل الجهاز» تلقائيًا ────────────────────────
add_action( 'admin_init', function () {
    if ( get_option( 'ecm_activate_page_v1' ) || ! function_exists( 'ecm_page_by_title' ) ) {
        return;
    }
    $existing = ecm_page_by_title( 'تفعيل الجهاز' );
    if ( $existing ) {
        wp_update_post( [ 'ID' => $existing->ID, 'post_content' => '[ecm_device_activation]', 'post_status' => 'publish' ] );
    } else {
        wp_insert_post( [
            'post_title'   => 'تفعيل الجهاز',
            'post_content' => '[ecm_device_activation]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
    }
    update_option( 'ecm_activate_page_v1', 1 );
} );


// ════════════════════════════════════════════════════════════
// §  REST API للتطبيق — التحقق من السيريال/التوكن + الإيميل
// ════════════════════════════════════════════════════════════
add_action( 'rest_api_init', function () {
    register_rest_route( 'ecm/v1', '/verify', [
        'methods'             => 'GET',
        'callback'            => 'ecm_rest_verify_device',
        'permission_callback' => '__return_true',
    ] );
} );

function ecm_rest_verify_device( $request ) {
    $serial = sanitize_text_field( (string) $request->get_param( 'serial' ) );
    $token  = sanitize_text_field( (string) $request->get_param( 'token' ) );
    $email  = sanitize_email( (string) $request->get_param( 'email' ) );

    $row = '' !== $token ? ecm_serial_find_by_token( $token ) : ( '' !== $serial ? ecm_serial_find( $serial ) : null );

    if ( ! $row ) {
        return rest_ensure_response( [
            'ok'      => false,
            'genuine' => false,
            'message' => 'سيريال غير معروف',
        ] );
    }

    $genuine = ( 'genuine' === $row->status );
    $bound   = ( (int) $row->user_id > 0 );
    $match   = ( '' !== $email && $bound ) ? ( strtolower( $email ) === strtolower( $row->email ) ) : null;

    // صالح = أصلي + مربوط + (لو اتبعت إيميل لازم يطابق)
    $valid = $genuine && $bound && ( null === $match ? true : $match );

    $warr_m   = (int) ( $row->warranty_months ?? 12 );
    $act_ts   = $row->activated_at ? strtotime( $row->activated_at ) : 0;
    $warr_end = $act_ts ? strtotime( '+' . $warr_m . ' months', $act_ts ) : 0;

    return rest_ensure_response( [
        'ok'             => (bool) $valid,
        'genuine'        => (bool) $genuine,
        'bound'          => (bool) $bound,
        'match'          => $match,
        'email'          => $bound ? ecm_mask_email( $row->email ) : '',
        'serial'         => $row->serial,
        'activated_at'   => $row->activated_at,
        'warranty_months'=> $warr_m,
        'warranty_until' => $warr_end ? date_i18n( 'Y-m-d', $warr_end ) : '',
        'warranty_valid' => $warr_end ? ( time() < $warr_end ) : false,
        'message'        => $valid ? 'تم التحقق ✅' : ( ! $genuine ? 'غير أصلي' : ( ! $bound ? 'غير مربوط بعد' : 'الجهاز مربوط بحساب آخر' ) ),
    ] );
}


// ════════════════════════════════════════════════════════════
// §  بوابة الشراء — ممنوع الشراء قبل تفعيل جهاز
// ════════════════════════════════════════════════════════════

/** منع إضافة المنتج للسلة قبل تفعيل جهاز */
add_filter( 'woocommerce_add_to_cart_validation', function ( $passed ) {
    if ( ! is_user_logged_in() ) {
        return $passed; // الدخول إجباري أصلًا عند الدفع
    }
    if ( ! ecm_user_has_active_device( get_current_user_id() ) ) {
        wc_add_notice(
            sprintf(
                '🛡️ ' . __( 'لازم تسجّل جهازك الأول عشان تقدر تشتري. %sفعّل جهازك الآن%s', 'ecm-theme' ),
                '<a href="' . esc_url( ecm_activation_page_url() ) . '"><strong>',
                '</strong></a>'
            ),
            'error'
        );
        return false;
    }
    return $passed;
}, 10, 1 );

/** منع الوصول للدفع قبل تفعيل جهاز */
add_action( 'template_redirect', function () {
    if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
        return;
    }
    if ( is_user_logged_in() && ! ecm_user_has_active_device( get_current_user_id() ) ) {
        wc_add_notice( '🛡️ ' . __( 'لازم تسجّل جهازك الأول قبل إتمام الشراء.', 'ecm-theme' ), 'error' );
        wp_safe_redirect( ecm_activation_page_url() );
        exit;
    }
} );

/** بانر تنبيه في صفحات المتجر لو الجهاز مش مفعّل */
add_action( 'woocommerce_before_main_content', function () {
    if ( ! is_user_logged_in() ) {
        return;
    }
    if ( ecm_user_has_active_device( get_current_user_id() ) ) {
        return;
    }
    if ( ! ( is_shop() || is_product() || is_product_category() || is_cart() ) ) {
        return;
    }
    echo '<div class="ecm-activate-banner">🛡️ '
        . esc_html__( 'لازم تسجّل جهازك الأول عشان تقدر تشتري.', 'ecm-theme' )
        . ' <a href="' . esc_url( ecm_activation_page_url() ) . '">' . esc_html__( 'فعّل جهازك الآن ←', 'ecm-theme' ) . '</a></div>';
}, 5 );


// ════════════════════════════════════════════════════════════
// §  API — عرض الأجهزة + إحصائيات (بتوكن أو صلاحية أدمن)
// ════════════════════════════════════════════════════════════
add_action( 'rest_api_init', function () {
    register_rest_route( 'ecm/v1', '/devices', [
        'methods'             => 'GET',
        'callback'            => 'ecm_rest_list_devices',
        'permission_callback' => 'ecm_api_auth',
    ] );
    register_rest_route( 'ecm/v1', '/stats', [
        'methods'             => 'GET',
        'callback'            => 'ecm_rest_stats',
        'permission_callback' => 'ecm_api_auth',
    ] );
} );

function ecm_rest_list_devices( $request ) {
    global $wpdb;
    $table  = ecm_serial_table();
    $status = sanitize_text_field( (string) $request->get_param( 'status' ) ); // activated | available | all
    $warr   = sanitize_text_field( (string) $request->get_param( 'warranty' ) ); // valid | expired
    $expir  = (int) $request->get_param( 'expiring' ); // خلال X يوم
    $search = sanitize_text_field( (string) $request->get_param( 'search' ) );
    $limit  = min( 500, max( 1, (int) ( $request->get_param( 'limit' ) ?: 100 ) ) );
    $offset = max( 0, (int) $request->get_param( 'offset' ) );

    // توافق قديم: bound=1
    if ( (int) $request->get_param( 'bound' ) === 1 && '' === $status ) {
        $status = 'activated';
    }

    $where = '1=1';
    $args  = [];

    if ( 'activated' === $status ) {
        $where .= ' AND user_id > 0';
    } elseif ( 'available' === $status ) {
        $where .= ' AND user_id = 0';
    }
    if ( 'valid' === $warr ) {
        $where .= ' AND user_id > 0 AND activated_at IS NOT NULL AND DATE_ADD(activated_at, INTERVAL warranty_months MONTH) > NOW()';
    } elseif ( 'expired' === $warr ) {
        $where .= ' AND user_id > 0 AND activated_at IS NOT NULL AND DATE_ADD(activated_at, INTERVAL warranty_months MONTH) <= NOW()';
    }
    if ( $expir > 0 ) {
        $where  .= ' AND user_id > 0 AND activated_at IS NOT NULL AND DATE_ADD(activated_at, INTERVAL warranty_months MONTH) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL %d DAY)';
        $args[]  = $expir;
    }
    if ( '' !== $search ) {
        $where .= ' AND (serial LIKE %s OR email LIKE %s)';
        $like   = '%' . $wpdb->esc_like( $search ) . '%';
        $args[] = $like;
        $args[] = $like;
    }

    // العدد الكلي قبل limit
    $total = (int) $wpdb->get_var( $args ? $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where}", $args ) : "SELECT COUNT(*) FROM {$table} WHERE {$where}" );

    $sql      = "SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT %d OFFSET %d";
    $q_args   = array_merge( $args, [ $limit, $offset ] );
    $rows     = $wpdb->get_results( $wpdb->prepare( $sql, $q_args ) );

    $out = [];
    foreach ( (array) $rows as $r ) {
        $warr_m   = (int) $r->warranty_months;
        $act_ts   = $r->activated_at ? strtotime( $r->activated_at ) : 0;
        $warr_end = $act_ts ? strtotime( '+' . $warr_m . ' months', $act_ts ) : 0;
        $valid    = $warr_end ? ( current_time( 'timestamp' ) < $warr_end ) : false;
        $out[]    = [
            'serial'           => $r->serial,
            'activated'        => ( (int) $r->user_id > 0 ),
            'email'            => $r->email,
            'user_id'          => (int) $r->user_id,
            'warranty_months'  => $warr_m,
            'activated_at'     => $r->activated_at,
            'registered_since' => $act_ts ? ecm_secs_to_ar( current_time( 'timestamp' ) - $act_ts ) : '',
            'warranty_until'   => $warr_end ? date_i18n( 'Y-m-d', $warr_end ) : '',
            'warranty_valid'   => $valid,
            'warranty_left'    => ( $valid && $warr_end ) ? ecm_secs_to_ar( $warr_end - current_time( 'timestamp' ) ) : '',
        ];
    }

    return rest_ensure_response( [
        'total'   => $total,
        'count'   => count( $out ),
        'limit'   => $limit,
        'offset'  => $offset,
        'devices' => $out,
    ] );
}

function ecm_rest_stats( $request ) {
    global $wpdb;
    $table = ecm_serial_table();
    return rest_ensure_response( [
        'total'         => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ),
        'activated'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE user_id > 0" ),
        'available'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE user_id = 0" ),
        'warranty_valid'=> (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE user_id > 0 AND activated_at IS NOT NULL AND DATE_ADD(activated_at, INTERVAL warranty_months MONTH) > NOW()" ),
        'expiring_30d'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE user_id > 0 AND activated_at IS NOT NULL AND DATE_ADD(activated_at, INTERVAL warranty_months MONTH) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)" ),
        'expired'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE user_id > 0 AND activated_at IS NOT NULL AND DATE_ADD(activated_at, INTERVAL warranty_months MONTH) <= NOW()" ),
    ] );
}
