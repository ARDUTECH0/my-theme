<?php
/**
 * ECM — تحسينات الأداء (Performance)
 * تحسينات آمنة لتسريع الموقع: preconnect للخطوط، تأجيل الـ JS،
 * تعطيل الإيموجي/الـ embeds الزايدة، تقليل heartbeat، إلخ.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

// ── preconnect لخوادم الخطوط (تحميل أسرع) ─────────────────────
add_action( 'wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1 );

// ── تعطيل سكربتات الإيموجي (مش محتاجينها — بتثقّل) ─────────────
add_action( 'init', function () {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    add_filter( 'emoji_svg_url', '__return_false' );
} );

// ── إزالة wp-embed.js في الواجهة ──────────────────────────────
add_action( 'wp_footer', function () {
    if ( ! is_admin() ) {
        wp_dequeue_script( 'wp-embed' );
    }
} );

// ── تأجيل سكربتات الثيم (غير حرجة) لتسريع العرض ────────────────
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
    $defer = [ 'ecm-ux-js', 'ecm-slider-js' ];
    if ( in_array( $handle, $defer, true ) && false === strpos( $tag, ' defer' ) ) {
        $tag = str_replace( ' src', ' defer src', $tag );
    }
    return $tag;
}, 10, 2 );

// ── تقليل تردد الـ heartbeat (حمل أقل على السيرفر) ─────────────
add_filter( 'heartbeat_settings', function ( $settings ) {
    $settings['interval'] = 60;
    return $settings;
} );

// ── تعطيل XML-RPC (أمان + حمل أقل) ────────────────────────────
add_filter( 'xmlrpc_enabled', '__return_false' );

// ── كاش للأصول الثابتة عبر الهيدر (حل مؤقت لحد ما تثبّت إضافة كاش) ─
add_action( 'send_headers', function () {
    if ( is_admin() || is_user_logged_in() ) {
        return;
    }
    // كاش بسيط لصفحات المتجر العامة (تنزيل أسرع للزيارات المتكررة)
    if ( function_exists( 'is_product' ) && ( is_product() || is_shop() || is_product_category() ) ) {
        header( 'Cache-Control: public, max-age=300, stale-while-revalidate=600' );
    }
} );
