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
    if ( get_option( 'ecm_serials_db_v1' ) ) {
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
        activated_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY serial (serial),
        KEY token (token)
    ) {$charset};" );

    update_option( 'ecm_serials_db_v1', 1 );
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

function ecm_serial_add( string $serial ): bool {
    global $wpdb;
    $serial = ecm_serial_normalize( $serial );
    if ( '' === $serial || ecm_serial_find( $serial ) ) {
        return false;
    }
    return (bool) $wpdb->insert( ecm_serial_table(), [
        'serial'     => $serial,
        'status'     => 'genuine',
        'created_at' => current_time( 'mysql' ),
    ] );
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
        $lines = preg_split( '/[\r\n,]+/', (string) wp_unslash( $_POST['ecm_serials_input'] ?? '' ) );
        $added = 0;
        foreach ( $lines as $line ) {
            if ( ecm_serial_add( sanitize_text_field( $line ) ) ) {
                $added++;
            }
        }
        echo '<div class="notice notice-success"><p>' . sprintf( esc_html__( 'تم إضافة %d سيريال.', 'ecm-theme' ), (int) $added ) . '</p></div>';
    }

    // فك ربط
    if ( isset( $_GET['unbind'] ) && check_admin_referer( 'ecm_unbind_' . (int) $_GET['unbind'] ) ) {
        $wpdb->update( $table, [ 'user_id' => 0, 'email' => '', 'token' => '', 'activated_at' => null ], [ 'id' => (int) $_GET['unbind'] ] );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'تم فك ربط الجهاز.', 'ecm-theme' ) . '</p></div>';
    }

    $rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 200" );
    $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    $bound = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE user_id > 0" );
    ?>
    <div class="wrap">
        <h1>🛡️ <?php esc_html_e( 'حماية الأجهزة بالسيريال', 'ecm-theme' ); ?></h1>
        <p><strong><?php echo (int) $total; ?></strong> <?php esc_html_e( 'سيريال إجمالي', 'ecm-theme' ); ?> · <strong><?php echo (int) $bound; ?></strong> <?php esc_html_e( 'مربوط بحسابات', 'ecm-theme' ); ?></p>

        <h2><?php esc_html_e( 'إضافة سيريالات أصلية', 'ecm-theme' ); ?></h2>
        <form method="post">
            <?php wp_nonce_field( 'ecm_serials' ); ?>
            <p><?php esc_html_e( 'اكتب سيريال في كل سطر (أو افصل بفاصلة):', 'ecm-theme' ); ?></p>
            <textarea name="ecm_serials_input" rows="6" style="width:100%;max-width:560px;" placeholder="ECM-0001&#10;ECM-0002"></textarea>
            <p><button class="button button-primary" name="ecm_add_serials" value="1"><?php esc_html_e( 'إضافة', 'ecm-theme' ); ?></button></p>
        </form>

        <h2><?php esc_html_e( 'آخر 200 سيريال', 'ecm-theme' ); ?></h2>
        <table class="widefat striped">
            <thead><tr>
                <th><?php esc_html_e( 'السيريال', 'ecm-theme' ); ?></th>
                <th><?php esc_html_e( 'الحالة', 'ecm-theme' ); ?></th>
                <th><?php esc_html_e( 'مربوط بـ', 'ecm-theme' ); ?></th>
                <th><?php esc_html_e( 'تاريخ التفعيل', 'ecm-theme' ); ?></th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php if ( $rows ) : foreach ( $rows as $r ) : ?>
                <tr>
                    <td><code><?php echo esc_html( $r->serial ); ?></code></td>
                    <td><?php echo $r->user_id ? '🔒 ' . esc_html__( 'مفعّل', 'ecm-theme' ) : '🟢 ' . esc_html__( 'متاح', 'ecm-theme' ); ?></td>
                    <td><?php echo $r->email ? esc_html( $r->email ) : '—'; ?></td>
                    <td><?php echo $r->activated_at ? esc_html( $r->activated_at ) : '—'; ?></td>
                    <td><?php if ( $r->user_id ) : ?>
                        <a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ecm-serials&unbind=' . $r->id ), 'ecm_unbind_' . $r->id ) ); ?>" onclick="return confirm('فك ربط الجهاز؟');"><?php esc_html_e( 'فك الربط', 'ecm-theme' ); ?></a>
                    <?php endif; ?></td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="5"><?php esc_html_e( 'لا يوجد سيريالات بعد.', 'ecm-theme' ); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
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
        <div class="ecm-devices-grid">
            <?php foreach ( $rows as $r ) :
                $verify = add_query_arg( 'token', $r->token, rest_url( 'ecm/v1/verify' ) ); ?>
                <div class="ecm-device-card">
                    <div class="ecm-device-info">
                        <span class="ecm-device-serial"><?php echo esc_html( $r->serial ); ?></span>
                        <span class="ecm-device-status">🔒 <?php esc_html_e( 'مربوط بحسابك', 'ecm-theme' ); ?></span>
                    </div>
                    <div class="ecm-qr-cell">
                        <div class="ecm-qr" data-qr="<?php echo esc_attr( $verify ); ?>"></div>
                        <span class="ecm-qr-cap">📱 <?php esc_html_e( 'كود التفعيل', 'ecm-theme' ); ?></span>
                    </div>
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

/** مدة بالعربي من timestamp لحد دلوقتي: "3 أشهر و5 أيام" */
function ecm_duration_ar( int $from_ts ): string {
    $secs   = max( 0, current_time( 'timestamp' ) - $from_ts );
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
        $warr_m = ecm_warranty_months();
        echo '<div class="ecm-act-devices">';
        foreach ( $rows as $r ) {
            $act_ts   = $r->activated_at ? strtotime( $r->activated_at ) : time();
            $dur      = ecm_duration_ar( $act_ts );
            $warr_end = strtotime( '+' . $warr_m . ' months', $act_ts );
            $valid    = ( current_time( 'timestamp' ) < $warr_end );
            echo '<div class="ecm-act-card">';
            echo '<div class="ecm-act-top"><span class="ecm-act-serial">' . esc_html( $r->serial ) . '</span><span class="ecm-act-badge">✅ ' . esc_html__( 'متفعّل', 'ecm-theme' ) . '</span></div>';
            echo '<ul class="ecm-act-meta">';
            echo '<li>📅 ' . esc_html__( 'تاريخ التفعيل:', 'ecm-theme' ) . ' <strong>' . esc_html( date_i18n( 'Y/m/d', $act_ts ) ) . '</strong></li>';
            echo '<li>⏳ ' . esc_html__( 'مسجّل من:', 'ecm-theme' ) . ' <strong>' . esc_html( $dur ) . '</strong></li>';
            if ( $valid ) {
                echo '<li class="ecm-warr-ok">🛡️ ' . esc_html__( 'الضمان ساري حتى:', 'ecm-theme' ) . ' <strong>' . esc_html( date_i18n( 'Y/m/d', $warr_end ) ) . '</strong></li>';
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

    return rest_ensure_response( [
        'ok'      => (bool) $valid,
        'genuine' => (bool) $genuine,
        'bound'   => (bool) $bound,
        'match'   => $match,
        'email'   => $bound ? ecm_mask_email( $row->email ) : '',
        'serial'  => $row->serial,
        'message' => $valid ? 'تم التحقق ✅' : ( ! $genuine ? 'غير أصلي' : ( ! $bound ? 'غير مربوط بعد' : 'الجهاز مربوط بحساب آخر' ) ),
    ] );
}
