<?php
/**
 * ECM — تسجيل الدخول بحساب Google (Sign in with Google)
 * بيستخدم Google Identity Services (ID Token) ويتحقق منه على السيرفر بأمان،
 * وبيعمل/يدخّل المستخدم تلقائيًا. يتكامل مع صفحة حساب WooCommerce.
 *
 * الإعداد: Google Cloud Console → APIs & Services → Credentials →
 * OAuth Client ID (Web) → Authorized JavaScript origins = رابط موقعك.
 * ثم حُط الـ Client ID في: المظهر → تخصيص → تسجيل دخول جوجل.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** الـ Client ID المحفوظ */
function ecm_google_client_id(): string {
    return trim( (string) get_theme_mod( 'ecm_google_client_id', '' ) );
}

// ── إعداد الـ Client ID في التخصيص (Customizer) ───────────────
add_action( 'customize_register', function ( $wp_customize ) {
    $wp_customize->add_section( 'ecm_google_login', [
        'title'    => __( '🔑 تسجيل دخول جوجل', 'ecm-theme' ),
        'priority' => 165,
    ] );
    $wp_customize->add_setting( 'ecm_google_client_id', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'ecm_google_client_id', [
        'label'       => __( 'Google Client ID', 'ecm-theme' ),
        'description' => __( 'من Google Cloud Console (OAuth Client ID — Web). سيبه فاضي عشان تخفي زر جوجل.', 'ecm-theme' ),
        'section'     => 'ecm_google_login',
        'type'        => 'text',
    ] );
} );

/** HTML زر «الدخول بجوجل» */
function ecm_google_button_html(): string {
    $cid = ecm_google_client_id();
    if ( '' === $cid ) {
        return '';
    }
    $redirect = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
    $ajax     = admin_url( 'admin-ajax.php' );
    $nonce    = wp_create_nonce( 'ecm_google_login' );

    ob_start();
    ?>
    <div class="ecm-google-login">
        <div class="ecm-google-card">
            <p class="ecm-google-title">⚡ <?php esc_html_e( 'دخول سريع', 'ecm-theme' ); ?></p>
            <p class="ecm-google-sub"><?php esc_html_e( 'ادخل بحسابك في جوجل بضغطة واحدة', 'ecm-theme' ); ?></p>
            <div id="g_id_onload"
                 data-client_id="<?php echo esc_attr( $cid ); ?>"
                 data-callback="ecmGoogleCallback"
                 data-auto_prompt="false"></div>
            <div class="g_id_signin ecm-g-btn"
                 data-type="standard" data-shape="pill" data-theme="filled_black"
                 data-text="continue_with" data-size="large" data-logo_alignment="left" data-width="360"></div>
        </div>
        <div class="ecm-or-sep"><span><?php esc_html_e( 'أو سجّل بالإيميل', 'ecm-theme' ); ?></span></div>
        <input type="hidden" id="ecm-g-redirect" value="<?php echo esc_attr( $redirect ); ?>">
    </div>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
    function ecmGoogleCallback( resp ) {
        if ( ! resp || ! resp.credential ) { return; }
        var fd = new FormData();
        fd.append( 'action', 'ecm_google_login' );
        fd.append( 'credential', resp.credential );
        fd.append( 'redirect', ( document.getElementById('ecm-g-redirect') || {} ).value || '' );
        fd.append( 'nonce', '<?php echo esc_js( $nonce ); ?>' );
        fetch( '<?php echo esc_url( $ajax ); ?>', { method: 'POST', body: fd, credentials: 'same-origin' } )
            .then( function ( r ) { return r.json(); } )
            .then( function ( d ) {
                if ( d && d.success ) { window.location = d.data.redirect; }
                else { alert( ( d && d.data && d.data.message ) || 'فشل تسجيل الدخول بجوجل' ); }
            } )
            .catch( function () { alert( 'تعذّر الاتصال بالخادم' ); } );
    }
    </script>
    <?php
    return ob_get_clean();
}

// ── عرض الزر مرة واحدة فوق نموذج الدخول/التسجيل ───────────────
add_action( 'woocommerce_before_customer_login_form', function () {
    if ( is_user_logged_in() ) {
        return;
    }
    echo ecm_google_button_html();
}, 20 );

/** اسم مستخدم فريد من الإيميل */
function ecm_unique_username_from_email( string $email ): string {
    $parts = explode( '@', $email );
    $base  = sanitize_user( $parts[0], true );
    if ( '' === $base ) {
        $base = 'user';
    }
    $username = $base;
    $i        = 1;
    while ( username_exists( $username ) ) {
        $username = $base . $i;
        $i++;
    }
    return $username;
}

// ── معالجة الدخول بجوجل (التحقق + الدخول/الإنشاء) ──────────────
function ecm_google_login_handler() {
    check_ajax_referer( 'ecm_google_login', 'nonce' );

    $cid        = ecm_google_client_id();
    $credential = isset( $_POST['credential'] ) ? sanitize_text_field( wp_unslash( $_POST['credential'] ) ) : '';
    if ( '' === $cid || '' === $credential ) {
        wp_send_json_error( [ 'message' => 'إعداد جوجل ناقص.' ] );
    }

    // التحقق من التوكن عند جوجل (يتحقق من التوقيع والصلاحية)
    $resp = wp_remote_get( 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode( $credential ), [ 'timeout' => 15 ] );
    if ( is_wp_error( $resp ) ) {
        wp_send_json_error( [ 'message' => 'تعذّر التحقق من جوجل.' ] );
    }
    $data = json_decode( wp_remote_retrieve_body( $resp ), true );

    if ( empty( $data['email'] ) || empty( $data['aud'] ) ) {
        wp_send_json_error( [ 'message' => 'توكن غير صالح.' ] );
    }
    if ( ! hash_equals( $cid, (string) $data['aud'] ) ) {
        wp_send_json_error( [ 'message' => 'عدم تطابق معرّف التطبيق.' ] );
    }
    $verified = $data['email_verified'] ?? false;
    if ( 'true' !== $verified && true !== $verified ) {
        wp_send_json_error( [ 'message' => 'الإيميل غير مُوثّق في جوجل.' ] );
    }

    $email = sanitize_email( $data['email'] );
    $user  = get_user_by( 'email', $email );

    if ( ! $user ) {
        $uid = wp_insert_user( [
            'user_login'   => ecm_unique_username_from_email( $email ),
            'user_email'   => $email,
            'user_pass'    => wp_generate_password( 24 ),
            'display_name' => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
            'first_name'   => isset( $data['given_name'] ) ? sanitize_text_field( $data['given_name'] ) : '',
            'last_name'    => isset( $data['family_name'] ) ? sanitize_text_field( $data['family_name'] ) : '',
            'role'         => 'customer',
        ] );
        if ( is_wp_error( $uid ) ) {
            wp_send_json_error( [ 'message' => 'تعذّر إنشاء الحساب.' ] );
        }
        update_user_meta( $uid, 'ecm_google_linked', 1 );
        $user = get_user_by( 'id', $uid );
    }

    // تسجيل الدخول
    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID, true );
    do_action( 'wp_login', $user->user_login, $user );

    $redirect = ! empty( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : '';
    if ( '' === $redirect ) {
        $redirect = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
    }
    wp_send_json_success( [ 'redirect' => $redirect ] );
}
add_action( 'wp_ajax_nopriv_ecm_google_login', 'ecm_google_login_handler' );
add_action( 'wp_ajax_ecm_google_login', 'ecm_google_login_handler' );
