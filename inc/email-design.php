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
    update_option( 'ecm_email_styled_v1', 1 );
} );

/** أفضل لوجو متاح لإيميلات الموقع (ديناميكي — يجيب الحالي دايمًا) */
function ecm_email_logo_url(): string {
    // 1) لوجو إيميل مخصّص (لو حُدّد)
    $mod = get_theme_mod( 'ecm_email_logo' );
    if ( $mod ) {
        $u = is_numeric( $mod ) ? wp_get_attachment_image_url( (int) $mod, 'full' ) : $mod;
        if ( $u ) {
            return $u;
        }
    }
    // 2) لوجو الموقع
    $logo_id = get_theme_mod( 'custom_logo' );
    if ( $logo_id ) {
        $u = wp_get_attachment_image_url( (int) $logo_id, 'full' );
        if ( $u ) {
            return $u;
        }
    }
    // 3) أيقونة الموقع (Site Icon)
    if ( function_exists( 'get_site_icon_url' ) ) {
        $u = get_site_icon_url( 512 );
        if ( $u ) {
            return $u;
        }
    }
    return '';
}

// اللوجو في هيدر الإيميل — ديناميكي (يتجاوز الإعداد المحفوظ مهما كانت نسخة ووكومرس)
add_filter( 'option_woocommerce_email_header_image', function ( $img ) {
    $logo = ecm_email_logo_url();
    return $logo ?: $img;
}, 99 );
add_filter( 'woocommerce_email_header_image', function ( $img ) {
    $logo = ecm_email_logo_url();
    return $logo ?: $img;
}, 99 );

// ── CSS اللمسات الخضرا ────────────────────────────────────────
add_filter( 'woocommerce_email_styles', function ( $css ) {
    $neon = ECM_MAIL_NEON;
    $dark = ECM_MAIL_DARK;
    $css .= "
        body, #body_content, #body_content td, #body_content p {
            font-family:'Segoe UI',Tahoma,Arial,sans-serif !important; }
        #wrapper { background-color:#f3f6f3 !important; padding:30px 0 !important; }
        #template_container { border-radius:16px !important; overflow:hidden !important;
            border:0 !important; box-shadow:0 10px 34px rgba(0,0,0,.08) !important;
            border-top:5px solid {$neon} !important; }

        /* الهيدر */
        #template_header { background-color:{$dark} !important; border-radius:0 !important;
            border-bottom:3px solid {$neon} !important; background-image:
            radial-gradient(circle at 15% -10%, rgba(156,255,0,.16), transparent 60%) !important; }
        #template_header_image { padding:26px 0 4px; text-align:center; background-color:{$dark} !important; }
        #template_header_image img { max-width:180px !important; max-height:70px !important;
            height:auto !important; width:auto !important; margin:0 auto !important; }
        #header_wrapper { padding:34px 44px !important; text-align:center !important; }
        #header_wrapper h1 { color:#ffffff !important; font-weight:800 !important; font-size:26px !important;
            text-shadow:none !important; line-height:1.35 !important; letter-spacing:.2px !important; }

        /* المحتوى */
        #body_content { background-color:#ffffff !important; }
        #body_content > table > tbody > tr > td { padding:38px 44px !important; }
        #body_content p, #body_content td { color:#3a403e !important; font-size:14.5px !important; line-height:1.75 !important; }
        #body_content h2, #body_content h2 a { color:{$dark} !important; font-size:18px !important; font-weight:700 !important; }
        #body_content h2 { border-bottom:2px solid {$neon}; display:inline-block; padding-bottom:5px; margin-bottom:14px; }
        a { color:#2e7d00 !important; font-weight:600 !important; }

        /* جدول الطلب */
        table.td { border-radius:10px !important; overflow:hidden !important; }
        table.td th { background:#f4f8f1 !important; color:{$dark} !important; font-weight:700 !important;
            border-color:#e9efe6 !important; padding:12px 14px !important; }
        table.td td { border-color:#eef2ee !important; padding:12px 14px !important; }
        table.td tfoot th, table.td tfoot td { font-size:14px !important; }
        table.td tfoot tr:last-child th, table.td tfoot tr:last-child td {
            color:{$dark} !important; font-size:17px !important; font-weight:800 !important;
            border-top:2px solid {$neon} !important; background:#fbfff5 !important; }

        /* الأزرار */
        .button, a.button, p.button a, .email-order-details a.button {
            background:{$neon} !important; color:{$dark} !important; border-radius:10px !important;
            font-weight:800 !important; text-decoration:none !important; padding:13px 26px !important;
            display:inline-block !important; }

        /* العناوين والفوتر */
        #addresses h2, .address { color:#3a403e !important; }
        .address { background:#fafbf9 !important; border:1px solid #eef2ee !important;
            border-radius:10px !important; padding:14px 16px !important; }
        #template_footer #body_content_inner, #template_footer td { padding:26px 44px !important; }
        #template_footer td, #template_footer #credit { color:#7a8079 !important; font-size:13px !important;
            line-height:1.7 !important; text-align:center !important; border-top:1px solid #e8ece7 !important; }
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
