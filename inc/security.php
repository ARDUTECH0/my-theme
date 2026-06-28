<?php
/**
 * ECM — تقوية الأمان (Security Hardening)
 * هيدرات أمان · منع حصر المستخدمين · إخفاء النسخة · حماية تسجيل الدخول.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

// ── هيدرات أمان على كل الصفحات ────────────────────────────────
add_action( 'send_headers', function () {
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    header( 'X-XSS-Protection: 1; mode=block' );
    // الكاميرا مسموحة للموقع نفسه (صفحة تفعيل الجهاز بتقرأ باركود)
    header( 'Permissions-Policy: geolocation=(), microphone=(), camera=(self)' );
} );

// ── إخفاء نسخة ووردبريس ───────────────────────────────────────
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// ── منع حصر أسماء المستخدمين (?author=N) ──────────────────────
add_action( 'template_redirect', function () {
    if ( ! is_admin() && isset( $_GET['author'] ) && ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
} );

// ── إخفاء قائمة المستخدمين من REST للزوّار ────────────────────
add_filter( 'rest_endpoints', function ( $endpoints ) {
    if ( ! is_user_logged_in() ) {
        unset( $endpoints['/wp/v2/users'] );
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
} );

// ── رسالة دخول مبهمة (متكشفش لو اليوزر موجود) ────────────────
add_filter( 'login_errors', function () {
    return __( 'بيانات الدخول غير صحيحة.', 'ecm-theme' );
} );

// ── منع الـ pingbacks (تُستخدم في هجمات DDoS) ─────────────────
add_filter( 'xmlrpc_methods', function ( $methods ) {
    unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
    return $methods;
} );
add_filter( 'wp_headers', function ( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
} );


// ════════════════════════════════════════════════════════════
//  حماية تسجيل الدخول (Brute-force) — مشتركة للـ API والموقع
// ════════════════════════════════════════════════════════════
function ecm_client_ip(): string {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    return sanitize_text_field( wp_unslash( $ip ) );
}

function ecm_login_attempts_key(): string {
    return 'ecm_login_fail_' . md5( ecm_client_ip() );
}

/** هل الـ IP محظور مؤقتًا من المحاولات؟ */
function ecm_login_blocked(): bool {
    return (int) get_transient( ecm_login_attempts_key() ) >= 10;
}

/** تسجيل محاولة فاشلة */
function ecm_login_register_fail(): void {
    $key = ecm_login_attempts_key();
    $n   = (int) get_transient( $key );
    set_transient( $key, $n + 1, 15 * MINUTE_IN_SECONDS );
}

/** مسح المحاولات عند نجاح الدخول */
function ecm_login_clear(): void {
    delete_transient( ecm_login_attempts_key() );
}

// تطبيق الحظر على تسجيل دخول الموقع نفسه + تسجيل المحاولات
add_filter( 'authenticate', function ( $user, $username ) {
    if ( '' !== $username && ecm_login_blocked() ) {
        return new WP_Error( 'ecm_too_many', __( 'محاولات كتير — استنى 15 دقيقة وحاول تاني.', 'ecm-theme' ) );
    }
    return $user;
}, 30, 2 );

add_action( 'wp_login_failed', function () {
    ecm_login_register_fail();
} );
add_action( 'wp_login', function () {
    ecm_login_clear();
} );
