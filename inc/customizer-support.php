<?php
/**
 * ECM Customizer — Support Page Settings
 * روابط الدعم (واتساب / إيميل / يوتيوب) — تظهر في صفحة الدعم والفوتر
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

function ecm_support_customizer( $wp_customize ) {

    $wp_customize->add_section( 'ecm_support', [
        'title'       => '🆘 صفحة الدعم',
        'priority'    => 38,
        'description' => 'روابط التواصل اللي تظهر في صفحة الدعم.',
    ] );

    // WhatsApp (رقم بكود الدولة، من غير + أو مسافات)
    $wp_customize->add_setting( 'ecm_support_whatsapp', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'ecm_support_whatsapp', [
        'label'       => 'رقم واتساب (بكود الدولة — مثال: 201001234567)',
        'description' => 'الرقم بس من غير + أو مسافات.',
        'section'     => 'ecm_support',
        'type'        => 'text',
    ] );

    // Email
    $wp_customize->add_setting( 'ecm_support_email', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_email',
    ] );
    $wp_customize->add_control( 'ecm_support_email', [
        'label'   => 'إيميل الدعم',
        'section' => 'ecm_support',
        'type'    => 'email',
    ] );

    // YouTube
    $wp_customize->add_setting( 'ecm_support_youtube', [
        'default'           => 'https://youtube.com/@ArduTechs',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'ecm_support_youtube', [
        'label'   => 'رابط قناة يوتيوب',
        'section' => 'ecm_support',
        'type'    => 'url',
    ] );
}

add_action( 'customize_register', 'ecm_support_customizer' );
