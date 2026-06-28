<?php
/**
 * ECM — تصميم إيميلات ووكومرس
 *
 * هوية ECM في كل الإيميلات (الفواتير، الطلبات...): هيدر غامق + اللوجو +
 * لمسات خضرا نيون. الألوان بتتحفظ كإعدادات حقيقية تقدر تعدّلها من
 * ووكومرس > الإعدادات > الإيميلات، والـ CSS بيضيف اللمسات.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

const ECM_MAIL_NEON = '#9CFF00';
const ECM_MAIL_DARK = '#0e1a10';

// ── ضبط ألوان الإيميل + اللوجو مرة واحدة (تفضل قابلة للتعديل) ──
add_action( 'init', function () {
    if ( get_option( 'ecm_email_styled_v1' ) || ! function_exists( 'WC' ) ) {
        return;
    }
    update_option( 'woocommerce_email_base_color', ECM_MAIL_DARK );        // خلفية الهيدر
    update_option( 'woocommerce_email_background_color', '#f3f6f3' );       // الخلفية الخارجية
    update_option( 'woocommerce_email_body_background_color', '#ffffff' );  // خلفية المحتوى
    update_option( 'woocommerce_email_text_color', '#2b2b2b' );

    // اللوجو في الهيدر من لوجو الموقع (لو متحط)
    $logo_id = get_theme_mod( 'custom_logo' );
    if ( $logo_id ) {
        $url = wp_get_attachment_image_url( $logo_id, 'full' );
        if ( $url ) {
            update_option( 'woocommerce_email_header_image', $url );
        }
    }
    update_option( 'ecm_email_styled_v1', 1 );
} );

// ── CSS اللمسات الخضرا ────────────────────────────────────────
add_filter( 'woocommerce_email_styles', function ( $css ) {
    $neon = ECM_MAIL_NEON;
    $dark = ECM_MAIL_DARK;
    $css .= "
        #wrapper { background-color:#f3f6f3 !important; padding:24px 0 !important; }
        #template_container { border-radius:14px !important; overflow:hidden !important;
            border:0 !important; box-shadow:0 6px 26px rgba(0,0,0,.07) !important; }
        #template_header { background-color:{$dark} !important; border-radius:0 !important;
            border-bottom:4px solid {$neon} !important; }
        #template_header_image { padding-top:20px; }
        #template_header_image img { max-width:170px !important; height:auto !important; margin:0 auto !important; }
        #header_wrapper { padding:30px 40px !important; text-align:center !important; }
        #header_wrapper h1 { color:#ffffff !important; font-weight:800 !important;
            text-shadow:none !important; line-height:1.3 !important; }
        #body_content { background-color:#ffffff !important; }
        #body_content h2, #body_content h2 a { color:{$dark} !important; }
        #body_content h2 { border-bottom:2px solid {$neon}; display:inline-block; padding-bottom:4px; }
        #body_content p, #body_content td { color:#2b2b2b !important; }
        a { color:#2e7d00 !important; }
        table.td th, table.td td { border-color:#eef2ee !important; }
        table.td tfoot tr:last-child th, table.td tfoot tr:last-child td {
            color:{$dark} !important; font-size:16px !important; border-top:2px solid {$neon} !important; }
        .button, a.button, p.button a, .email-order-details a.button {
            background:{$neon} !important; color:{$dark} !important; border-radius:9px !important;
            font-weight:700 !important; text-decoration:none !important; }
        #addresses h2, .address { color:#2b2b2b !important; }
        #template_footer td, #template_footer #credit { color:#6b7280 !important; }
    ";
    return $css;
}, 20 );

// ── فوتر بهوية ECM ────────────────────────────────────────────
add_filter( 'woocommerce_email_footer_text', function () {
    $host = wp_parse_url( home_url(), PHP_URL_HOST );
    return 'E.Camera.Man — ' . esc_html__( 'شكرًا لثقتك بينا 💚', 'ecm-theme' )
        . '<br><a style="color:#2e7d00;text-decoration:none;" href="' . esc_url( home_url( '/' ) ) . '">'
        . esc_html( (string) $host ) . '</a>';
} );
