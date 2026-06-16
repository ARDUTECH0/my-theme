<?php
/**
 * ECM Customizer — Software Page + Nav Buttons
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

function ecm_software_customizer( $wp_customize ) {

    // ══════════════════════════════════════════════════
    // §1  أزرار النافيبار
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_nav_buttons', [
        'title'    => '🔗 أزرار النافيبار — Nav Buttons',
        'priority' => 25,
    ] );

    $nav_fields = [
        'ecm_nav_cta_text'  => [ 'حمّل التطبيق', 'نص الزر الأخضر — CTA Button Text' ],
        'ecm_nav_btn1_text' => [ 'الرئيسية',     'زر 1 — نص' ],
        'ecm_nav_btn1_link' => [ '/',             'زر 1 — رابط' ],
        'ecm_nav_btn2_text' => [ 'التحكم',        'زر 2 — نص' ],
        'ecm_nav_btn2_link' => [ '#controls',     'زر 2 — رابط' ],
        'ecm_nav_btn3_text' => [ 'المميزات',      'زر 3 — نص' ],
        'ecm_nav_btn3_link' => [ '#features',     'زر 3 — رابط' ],
        'ecm_nav_btn4_text' => [ 'المواصفات',     'زر 4 — نص' ],
        'ecm_nav_btn4_link' => [ '#specs',        'زر 4 — رابط' ],
        'ecm_nav_btn5_text' => [ 'التوثيق',       'زر 5 — نص' ],
        'ecm_nav_btn5_link' => [ '',              'زر 5 — رابط (اتركه فارغ = تلقائي)' ],
        'ecm_nav_btn6_text' => [ 'تواصل',         'زر 6 — نص' ],
        'ecm_nav_btn6_link' => [ '#contact',      'زر 6 — رابط' ],
    ];

    foreach ( $nav_fields as $id => $data ) {
        $wp_customize->add_setting( $id, [ 'default' => $data[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $id, [ 'label' => $data[1], 'section' => 'ecm_nav_buttons' ] );
    }


    // ══════════════════════════════════════════════════
    // §2  صفحة السوفت وير
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_software', [
        'title'    => '🔧 السوفت وير والتحديثات — Software',
        'priority' => 38,
    ] );

    // Basic info
    $sw = [
        'ecm_sw_title'        => [ 'السوفت وير والتحديثات', 'عنوان الصفحة — Page Title' ],
        'ecm_sw_desc'         => [ 'حمّل آخر إصدار من السوفت وير — تحديثات الأداء وإصلاح المشاكل', 'الوصف — Description' ],
        'ecm_sw_version'      => [ '2.0.0', 'رقم الإصدار الحالي — Current Version' ],
        'ecm_sw_date'         => [ '2024-06-01', 'تاريخ الإصدار — Release Date' ],
        'ecm_sw_size'         => [ '128 MB', 'حجم الملف — File Size' ],
        'ecm_sw_prev_version' => [ '1.9.5', 'رقم الإصدار السابق — Previous Version' ],
    ];

    foreach ( $sw as $id => $data ) {
        $wp_customize->add_setting( $id, [ 'default' => $data[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $id, [ 'label' => $data[1], 'section' => 'ecm_software' ] );
    }

    // Download links
    $wp_customize->add_setting( 'ecm_sw_download_link', [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp_customize->add_control( 'ecm_sw_download_link', [
        'label'       => '⬇️ رابط تحميل السوفت وير — Download Link',
        'section'     => 'ecm_software',
        'type'        => 'url',
        'description' => 'ارفع الملف في الميديا والصق الرابط هنا',
    ] );

    $wp_customize->add_setting( 'ecm_sw_prev_link', [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp_customize->add_control( 'ecm_sw_prev_link', [
        'label'   => '📦 رابط الإصدار السابق — Previous Version Link',
        'section' => 'ecm_software',
        'type'    => 'url',
    ] );

    // Changelog (6 items)
    $log_defaults = [
        1 => 'تحسين استجابة التحكم اللاسلكي — تأخير أقل من 8ms',
        2 => 'إصلاح مشكلة انقطاع البلوتوث عند المسافات البعيدة',
        3 => 'إضافة وضع التصوير البطيء Slow Motion 240fps',
        4 => 'تحسين عمر البطارية بنسبة 15%',
        5 => 'دعم كاميرات Sony A7S III و Canon R5 C',
        6 => '',
    ];

    for ( $i = 1; $i <= 6; $i++ ) {
        $wp_customize->add_setting( "ecm_sw_log_{$i}", [ 'default' => $log_defaults[$i], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( "ecm_sw_log_{$i}", [
            'label'   => "📋 تحديث {$i} — Changelog Item {$i}",
            'section' => 'ecm_software',
        ] );
    }


    // ══════════════════════════════════════════════════
    // §3  صفحة رفع السوفت وير المباشر — Web Flasher Page
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_upload_redirect_section', [
        'title'    => '🚀 صفحة الرفع المباشر — Web Flasher',
        'priority' => 39,
    ] );

    $upload_fields = [
        'ecm_upload_page_title'     => [ 'أداة رفع وتحديث السوفت وير المباشر', 'عنوان الصفحة بالعربية — Arabic Title' ],
        'ecm_upload_page_title_en'  => [ 'Direct Firmware Flasher & Uploader', 'عنوان الصفحة بالإنجليزية — English Title' ],
        'ecm_upload_page_desc'      => [ 'لتحديث السوفت وير أو رفعه مباشرة إلى جهازك، يرجى الانتقال إلى الموقع المخصص للرفع.', 'الوصف بالعربية — Arabic Description' ],
        'ecm_upload_page_desc_en'   => [ 'To flash or upload the software directly to your device, please proceed to the dedicated web utility.', 'الوصف بالإنجليزية — English Description' ],
        'ecm_upload_btn_text'       => [ 'انتقل إلى موقع الرفع والتحديث 🚀', 'نص الزر بالعربية — Arabic Button Text' ],
        'ecm_upload_btn_text_en'    => [ 'Go to Web Flasher Website 🚀', 'نص الزر بالإنجليزية — English Button Text' ],
    ];

    foreach ( $upload_fields as $id => $data ) {
        $wp_customize->add_setting( $id, [ 'default' => $data[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $id, [ 'label' => $data[1], 'section' => 'ecm_upload_redirect_section' ] );
    }

    $wp_customize->add_setting( 'ecm_upload_target_url', [ 'default' => 'https://esp-web-tools.github.io/', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp_customize->add_control( 'ecm_upload_target_url', [
        'label'       => '🔗 رابط موقع الرفع (التوجيه) — Target Upload URL',
        'section'     => 'ecm_upload_redirect_section',
        'type'        => 'url',
        'description' => 'الرابط الذي ينتقل إليه المستخدم عند الضغط على الزر (مثال: أداة فلاش ESP أو صفحة تحكم ويب)',
    ] );
}

add_action( 'customize_register', 'ecm_software_customizer' );
