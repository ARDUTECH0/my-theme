<?php
/**
 * ECM Customizer — App Download Page Settings
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

function ecm_app_customizer( $wp_customize ) {

    $wp_customize->add_section( 'ecm_app_page', [
        'title'    => '📱 صفحة التطبيق',
        'priority' => 35,
    ] );

    // App info
    $fields = [
        'ecm_app_name'         => [ 'ECM Controller', 'اسم التطبيق' ],
        'ecm_app_desc'         => [ 'تطبيق التحكم الاحترافي بالكاميرات — تحكم لاسلكي، برمجة مسارات، ومراقبة مباشرة.', 'وصف التطبيق' ],
        'ecm_app_version'      => [ '2.0.0', 'رقم الإصدار' ],
        'ecm_app_size'         => [ '45 MB', 'حجم التطبيق' ],
    ];

    foreach ( $fields as $id => $data ) {
        $wp_customize->add_setting( $id, [ 'default' => $data[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $id, [ 'label' => $data[1], 'section' => 'ecm_app_page' ] );
    }

    // Links
    $links = [
        'ecm_app_android_link' => [ '', 'رابط Google Play' ],
        'ecm_app_ios_link'     => [ '', 'رابط App Store' ],
        'ecm_app_apk_link'     => [ '', 'رابط تحميل APK مباشر (الملف أو الرابط)' ],
        'ecm_app_page_link'    => [ '', 'رابط صفحة التحميل (يظهر في الهيدر كزر)' ],
    ];

    foreach ( $links as $id => $data ) {
        $wp_customize->add_setting( $id, [ 'default' => $data[0], 'sanitize_callback' => 'esc_url_raw' ] );
        $wp_customize->add_control( $id, [ 'label' => $data[1], 'section' => 'ecm_app_page', 'type' => 'url' ] );
    }
}

add_action( 'customize_register', 'ecm_app_customizer' );
