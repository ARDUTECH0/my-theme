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

// ── CSS — تصميم بريميوم ───────────────────────────────────────
add_filter( 'woocommerce_email_styles', function ( $css ) {
    $neon = ECM_MAIL_NEON;
    $dark = ECM_MAIL_DARK;
    $css .= "
        body, #body_content, #body_content td, #body_content p, #body_content table, #header_wrapper h1 {
            font-family:'Segoe UI','Helvetica Neue',Helvetica,Tahoma,Arial,sans-serif !important;
            -webkit-font-smoothing:antialiased !important; }
        body, #wrapper { background-color:#ecefec !important; }
        #wrapper { padding:34px 14px !important; }
        #template_container {
            border-radius:18px !important; overflow:hidden !important; border:0 !important;
            box-shadow:0 14px 40px rgba(14,26,16,.12) !important; }

        /* ── الهيدر ── */
        #template_header { background-color:{$dark} !important; border-radius:0 !important;
            border-bottom:3px solid {$neon} !important;
            background-image:radial-gradient(circle at 18% -20%, rgba(156,255,0,.20), transparent 58%) !important; }
        #template_header_image { padding:30px 0 2px; text-align:center; background-color:{$dark} !important; }
        #template_header_image img { max-width:184px !important; max-height:72px !important;
            height:auto !important; width:auto !important; margin:0 auto !important; }
        #header_wrapper { padding:30px 48px 38px !important; text-align:center !important; }
        #header_wrapper h1 { color:#ffffff !important; font-weight:800 !important; font-size:25px !important;
            text-shadow:none !important; line-height:1.4 !important; letter-spacing:.3px !important; }

        /* ── المحتوى ── */
        #body_content { background-color:#ffffff !important; }
        #body_content > table > tbody > tr > td { padding:40px 48px !important; }
        #body_content p, #body_content td, #body_content li {
            color:#41484a !important; font-size:15px !important; line-height:1.8 !important; }
        #body_content h2, #body_content h2 a { color:{$dark} !important; font-size:19px !important;
            font-weight:800 !important; letter-spacing:.2px !important; }
        #body_content h2 { position:relative; padding-bottom:8px; margin:0 0 18px !important; }
        #body_content h2:after { content:''; display:block; width:46px; height:3px;
            background:{$neon}; border-radius:3px; margin-top:8px; }
        a { color:#2e7d00 !important; font-weight:600 !important; }

        /* ── جدول الطلب ── */
        table#body_content table.td, table.td {
            border:1px solid #edf1ec !important; border-radius:12px !important; overflow:hidden !important; }
        table.td th { background:{$dark} !important; color:#ffffff !important; font-weight:700 !important;
            font-size:12.5px !important; letter-spacing:.4px !important; text-transform:uppercase !important;
            border:0 !important; padding:13px 16px !important; }
        table.td td { border-color:#f0f3ef !important; padding:14px 16px !important; vertical-align:middle !important; }
        table.td tbody tr:nth-child(even) td { background:#fbfdfa !important; }
        table.td tfoot th, table.td tfoot td { background:#ffffff !important; color:#41484a !important;
            font-size:14px !important; border-color:#f0f3ef !important; padding:11px 16px !important;
            text-transform:none !important; letter-spacing:0 !important; }
        table.td tfoot tr:last-child th, table.td tfoot tr:last-child td {
            color:{$dark} !important; font-size:18px !important; font-weight:800 !important;
            border-top:2px solid {$neon} !important; background:#f6ffe9 !important; }

        /* ── الأزرار ── */
        .button, a.button, p.button a, .email-order-details a.button, td.text a.button {
            background:{$neon} !important; color:{$dark} !important; border:0 !important;
            border-radius:11px !important; font-weight:800 !important; font-size:15px !important;
            text-decoration:none !important; padding:14px 30px !important; display:inline-block !important;
            box-shadow:0 4px 14px rgba(156,255,0,.35) !important; }

        /* ── العناوين (الفاتورة/الشحن) ── */
        #addresses { margin-top:8px !important; }
        #addresses h2 { color:{$dark} !important; font-size:14px !important; }
        .address { background:#fafcf8 !important; border:1px solid #eef2ec !important;
            border-radius:12px !important; padding:16px 18px !important; color:#41484a !important;
            font-size:14px !important; line-height:1.7 !important; }

        /* ── الفوتر ── */
        #template_footer td { padding:0 !important; }
        #template_footer #credit { border:0 !important; padding:0 !important; }
    ";
    return $css;
}, 20 );

// ── فوتر مصمّم بهوية ECM + تواصل ──────────────────────────────
add_filter( 'woocommerce_email_footer_text', function () {
    $neon = ECM_MAIL_NEON;
    $dark = ECM_MAIL_DARK;
    $host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
    $year = gmdate( 'Y' );

    // روابط التواصل (الموقع + واتساب لو متحط)
    $links = [];
    $links[] = '<a href="' . esc_url( home_url( '/' ) ) . '" style="color:#c9f97a;text-decoration:none;font-weight:600;">🌐 ' . esc_html( $host ) . '</a>';
    $wa = get_option( 'ecm_whatsapp' );
    if ( $wa ) {
        $wa_num = preg_replace( '/[^0-9]/', '', (string) $wa );
        if ( $wa_num ) {
            $links[] = '<a href="https://wa.me/' . esc_attr( $wa_num ) . '" style="color:#c9f97a;text-decoration:none;font-weight:600;">💬 ' . esc_html__( 'واتساب', 'ecm-theme' ) . '</a>';
        }
    }
    $links_html = implode( ' &nbsp;·&nbsp; ', $links );

    ob_start();
    ?>
    <div style="background:<?php echo esc_attr( $dark ); ?>;border-radius:0 0 18px 18px;margin:0 -10px;padding:30px 28px;text-align:center;">
        <div style="font-size:18px;font-weight:800;color:#ffffff;letter-spacing:.4px;">E.Camera.Man</div>
        <div style="width:38px;height:3px;background:<?php echo esc_attr( $neon ); ?>;border-radius:3px;margin:10px auto 14px;"></div>
        <div style="color:#aeb8af;font-size:13.5px;line-height:1.7;margin-bottom:12px;">
            <?php esc_html_e( 'شكرًا لثقتك بينا 💚 — إحنا دايمًا في خدمتك.', 'ecm-theme' ); ?>
        </div>
        <div style="font-size:13.5px;margin-bottom:14px;"><?php echo wp_kses_post( $links_html ); ?></div>
        <div style="color:#6f7a70;font-size:12px;">© <?php echo esc_html( $year ); ?> E.Camera.Man — <?php esc_html_e( 'كل الحقوق محفوظة', 'ecm-theme' ); ?></div>
    </div>
    <?php
    return (string) ob_get_clean();
} );
