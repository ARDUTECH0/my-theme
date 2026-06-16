<?php
/**
 * ECM Customizer — Documentation Page + Videos
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

function ecm_docs_customizer( $wp_customize ) {

    // ══════════════════════════════════════════════════
    // صفحة التوثيق
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_docspage', [
        'title'    => '📖 صفحة التوثيق',
        'priority' => 36,
    ] );

    $wp_customize->add_setting( 'ecm_docspage_title', [ 'default' => 'التوثيق والأدلة', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_docspage_title', [ 'label' => 'عنوان الصفحة', 'section' => 'ecm_docspage' ] );

    $wp_customize->add_setting( 'ecm_docspage_desc', [ 'default' => 'كل ما تحتاجه لاستخدام نظام ECM — أدلة شاملة، شروحات مصورة، وأسئلة شائعة', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_docspage_desc', [ 'label' => 'وصف الصفحة', 'section' => 'ecm_docspage', 'type' => 'textarea' ] );


    // ══════════════════════════════════════════════════
    // فيديوهات تعليمية
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_videos', [
        'title'    => '🎥 فيديوهات تعليمية',
        'priority' => 37,
    ] );

    $wp_customize->add_setting( 'ecm_vid_section_title', [ 'default' => 'فيديوهات تعليمية', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_vid_section_title', [ 'label' => 'عنوان قسم الفيديو', 'section' => 'ecm_videos' ] );

    $wp_customize->add_setting( 'ecm_vid_section_desc', [ 'default' => 'شروحات مصورة خطوة بخطوة لاحتراف استخدام نظام ECM', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_vid_section_desc', [ 'label' => 'وصف القسم', 'section' => 'ecm_videos' ] );

    $vid_titles = [
        1 => 'مقدمة عن نظام ECM',
        2 => 'طريقة التركيب والإعداد',
        3 => 'التحكم بالسلايدر',
        4 => 'برمجة مسارات الكاميرا',
        5 => 'ربط التطبيق بالنظام',
        6 => 'نصائح للتصوير الاحترافي',
    ];

    for ( $v = 1; $v <= 6; $v++ ) {
        $wp_customize->add_setting( "ecm_vid_{$v}_title", [ 'default' => $vid_titles[$v], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( "ecm_vid_{$v}_title", [ 'label' => "فيديو {$v} — العنوان", 'section' => 'ecm_videos' ] );

        $wp_customize->add_setting( "ecm_vid_{$v}_url", [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
        $wp_customize->add_control( "ecm_vid_{$v}_url", [
            'label'       => "فيديو {$v} — رابط YouTube",
            'section'     => 'ecm_videos',
            'type'        => 'url',
            'description' => 'الصق رابط YouTube هنا (مثال: https://youtube.com/watch?v=xxxxx)',
        ] );
    }
}

add_action( 'customize_register', 'ecm_docs_customizer' );
