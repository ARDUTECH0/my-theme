<?php
/**
 * ECM — 3D Logo (.glb) Support
 *
 * بيعرض لوجو ثلاثي الأبعاد (.glb) في الهيدر بدل اللوجو العادي،
 * باستخدام <model-viewer> من Google. كله محمي — لو مفيش ملف، يرجع للوجو العادي.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;


// ── السماح برفع ملفات 3D في الوسائط ──────────────────────────
function ecm_allow_3d_uploads( $mimes ) {
    $mimes['glb']  = 'model/gltf-binary';
    $mimes['gltf'] = 'model/gltf+json';
    return $mimes;
}
add_filter( 'upload_mimes', 'ecm_allow_3d_uploads' );

/** يخلّي WordPress يقبل فحص نوع ملفات glb/gltf */
function ecm_check_3d_filetype( $data, $file, $filename, $mimes ) {
    $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
    if ( in_array( $ext, [ 'glb', 'gltf' ], true ) ) {
        $data['ext']  = $ext;
        $data['type'] = ( 'glb' === $ext ) ? 'model/gltf-binary' : 'model/gltf+json';
    }
    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'ecm_check_3d_filetype', 10, 4 );


// ── هل في لوجو 3D متحدّد؟ ─────────────────────────────────────
function ecm_has_3d_logo() {
    return (bool) get_theme_mod( 'ecm_logo_glb', '' );
}


// ── تحميل مكتبة model-viewer ──────────────────────────────────
function ecm_enqueue_model_viewer() {
    if ( wp_script_is( 'ecm-model-viewer', 'enqueued' ) ) {
        return;
    }
    wp_enqueue_script(
        'ecm-model-viewer',
        'https://unpkg.com/@google/model-viewer@3.5.0/dist/model-viewer.min.js',
        [],
        '3.5.0',
        true
    );
}

/** model-viewer لازم يتحمّل كـ ES module */
function ecm_model_viewer_module_tag( $tag, $handle ) {
    if ( 'ecm-model-viewer' === $handle ) {
        return str_replace( '<script ', '<script type="module" ', $tag );
    }
    return $tag;
}
add_filter( 'script_loader_tag', 'ecm_model_viewer_module_tag', 10, 2 );

/** حمّل المكتبة تلقائيًا لو الهيدر بيستخدم لوجو 3D */
function ecm_maybe_enqueue_3d_logo() {
    if ( ecm_has_3d_logo() ) {
        ecm_enqueue_model_viewer();
    }
}
add_action( 'wp_enqueue_scripts', 'ecm_maybe_enqueue_3d_logo' );


// ── باني ماركب model-viewer (مشترك: الهيدر + الشورت كود + ودجِت Elementor) ──
function ecm_3d_model_markup( $args = [] ) {
    $args = wp_parse_args( $args, [
        'src'             => '',
        'width'           => 0,        // 0 = يحسبها من الارتفاع
        'height'          => 80,
        'auto_rotate'     => true,
        'rotate_speed'    => 30,       // درجة/ثانية
        'camera_controls' => true,     // السحب باليد للف
        'effect'          => 'none',   // none | float | pulse | swing | tilt
        'play_animation'  => false,    // تشغيل أنيميشن مدمج في الـ glb
        'alt'             => '',
        'inline_size'     => true,     // false = سيب المقاس للـ CSS (لودجِت Elementor)
        'class'           => 'ecm-3d-model',
        'environment'     => 'neutral',// إضاءة: neutral | none | رابط HDR
        'zoom'            => false,    // تكبير/تصغير بالعجلة + تحريك بالماوس
        'fullscreen'      => false,    // زر ملء الشاشة
        'exposure'        => 1.0,      // السطوع/البرايتنس (0–2)
        'shadow'          => 0.0,      // شدة الظل (0–1)
        'shadow_softness' => 1.0,      // نعومة الظل (0–1)
        'tone_mapping'    => '',       // نمط الألوان: '' | neutral | commerce | aces
        'frame_zoom'      => 0,        // حجم الموديل في الإطار % (أقل=أكبر); 0=تلقائي
        'loading'         => 'auto',   // auto | lazy | eager — لفصل تحميل النسخ
    ] );

    if ( ! $args['src'] ) {
        return '';
    }
    ecm_enqueue_model_viewer();

    $h = max( 1, (int) $args['height'] );
    $w = (int) $args['width'] > 0 ? (int) $args['width'] : (int) round( $h * 2.6 );

    $effect       = in_array( $args['effect'], [ 'float', 'pulse', 'swing', 'tilt' ], true ) ? $args['effect'] : '';
    $effect_class = $effect ? ' ecm-3d-' . $effect : '';

    $attrs = [ 'interaction-prompt="none"' ];
    $attrs[] = 'camera-target="auto auto auto"'; // توسيط الموديل على مركزه
    $attrs[] = 'loading="' . esc_attr( $args['loading'] ) . '"';
    $attrs[] = 'exposure="' . floatval( $args['exposure'] ) . '"';
    $attrs[] = 'shadow-intensity="' . floatval( $args['shadow'] ) . '"';
    $attrs[] = 'shadow-softness="' . floatval( $args['shadow_softness'] ) . '"';
    if ( $args['tone_mapping'] ) {
        $attrs[] = 'tone-mapping="' . esc_attr( $args['tone_mapping'] ) . '"';
    }
    if ( (int) $args['frame_zoom'] > 0 ) {
        // % نسبة لمسافة التأطير التلقائية — أقل = الموديل أكبر
        $attrs[] = 'camera-orbit="0deg 75deg ' . (int) $args['frame_zoom'] . '%"';
    }
    if ( $args['auto_rotate'] ) {
        $attrs[] = 'auto-rotate';
        $attrs[] = 'rotation-per-second="' . (int) $args['rotate_speed'] . 'deg"';
    }
    if ( $args['camera_controls'] || $args['zoom'] ) {
        $attrs[] = 'camera-controls';
        if ( ! $args['zoom'] ) {
            // لوجو ثابت: السحب بيلف بس — من غير زوم/تحريك
            $attrs[] = 'disable-zoom';
            $attrs[] = 'disable-pan';
        }
    }
    if ( $args['play_animation'] ) {
        $attrs[] = 'autoplay';
    }
    if ( $args['environment'] && 'none' !== $args['environment'] ) {
        $attrs[] = 'environment-image="' . esc_attr( $args['environment'] ) . '"';
    }

    $alt   = '' !== $args['alt'] ? $args['alt'] : get_bloginfo( 'name' ) . ' 3D';
    $style = $args['inline_size'] ? sprintf( ' style="width:%dpx;height:%dpx;"', $w, $h ) : '';

    $mv = sprintf(
        '<model-viewer class="%1$s%2$s" src="%3$s" alt="%4$s" %5$s%6$s></model-viewer>',
        esc_attr( $args['class'] ),
        esc_attr( $effect_class ),
        esc_url( $args['src'] ),
        esc_attr( $alt ),
        implode( ' ', $attrs ), // attributes مبنية داخليًا (آمنة)
        $style
    );

    // زر ملء الشاشة (اختياري) — المعالج في js/ecm-ux.js (من غير inline JS)
    if ( $args['fullscreen'] ) {
        $fs = '<button type="button" class="ecm-3d-fs-btn" title="ملء الشاشة" aria-label="ملء الشاشة">⛶</button>';
        return '<div class="ecm-3d-holder">' . $mv . $fs . '</div>';
    }

    return $mv;
}

// ── عرض لوجو الهيدر 3D (من التخصيص) ──────────────────────────
function ecm_render_3d_logo( $args = [] ) {
    $glb = get_theme_mod( 'ecm_logo_glb', '' );
    if ( ! $glb ) {
        return '';
    }
    $defaults = [
        'src'             => $glb,
        'height'          => (int) get_theme_mod( 'ecm_logo_glb_height', 48 ),
        'auto_rotate'     => (bool) get_theme_mod( 'ecm_logo_glb_autorotate', true ),
        'rotate_speed'    => (int) get_theme_mod( 'ecm_logo_glb_speed', 30 ),
        'camera_controls' => (bool) get_theme_mod( 'ecm_logo_glb_controls', true ),
        'effect'          => get_theme_mod( 'ecm_logo_glb_effect', 'none' ),
        'exposure'        => (float) get_theme_mod( 'ecm_logo_glb_exposure', 1.0 ),
        'shadow'          => (float) get_theme_mod( 'ecm_logo_glb_shadow', 0.0 ),
        'class'           => 'ecm-header-3d',  // كلاس مستقل عن موديلات الصفحة
        'loading'         => 'eager',          // يتحمّل فورًا ومستقل عن المحتوى
    ];
    return ecm_3d_model_markup( wp_parse_args( $args, $defaults ) );
}

/** شورت كود: [ecm_logo_3d src="..." height="120" effect="float" rotate="yes"] — يشتغل في أي مكان */
function ecm_logo_3d_shortcode( $atts ) {
    $atts = shortcode_atts( [
        'src'        => get_theme_mod( 'ecm_logo_glb', '' ),
        'height'     => 100,
        'width'      => 0,
        'effect'     => 'none',
        'rotate'     => 'yes',
        'speed'      => 30,
        'controls'   => 'yes',
        'zoom'       => 'no',
        'fullscreen' => 'no',
        'play'       => 'no',
        'exposure'   => 1,
        'shadow'     => 0,
        'tone'       => '',
        'frame'      => 0,
    ], $atts, 'ecm_logo_3d' );

    return ecm_3d_model_markup( [
        'src'             => $atts['src'],
        'height'          => (int) $atts['height'],
        'width'           => (int) $atts['width'],
        'effect'          => sanitize_key( $atts['effect'] ),
        'auto_rotate'     => 'yes' === $atts['rotate'],
        'rotate_speed'    => (int) $atts['speed'],
        'camera_controls' => 'yes' === $atts['controls'],
        'zoom'            => 'yes' === $atts['zoom'],
        'fullscreen'      => 'yes' === $atts['fullscreen'],
        'play_animation'  => 'yes' === $atts['play'],
        'exposure'        => (float) $atts['exposure'],
        'shadow'          => (float) $atts['shadow'],
        'tone_mapping'    => sanitize_key( $atts['tone'] ),
        'frame_zoom'      => (int) $atts['frame'],
        'class'           => 'ecm-3d-model ecm-3d-inline',
        'loading'         => 'lazy',
    ] );
}
add_shortcode( 'ecm_logo_3d', 'ecm_logo_3d_shortcode' );


// ── التخصيص (Customizer) ──────────────────────────────────────
function ecm_logo_3d_customizer( $wp_customize ) {
    $wp_customize->add_section( 'ecm_logo_3d', [
        'title'       => '🧊 لوجو ثلاثي الأبعاد (3D)',
        'priority'    => 22,
        'description' => 'ارفع ملف .glb من «الوسائط» والصق رابطه هنا — هيظهر بدل اللوجو العادي في الهيدر.',
    ] );

    $wp_customize->add_setting( 'ecm_logo_glb', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'ecm_logo_glb', [
        'label'       => 'رابط ملف .glb',
        'description' => 'مثال: https://موقعك/wp-content/uploads/logo.glb',
        'section'     => 'ecm_logo_3d',
        'type'        => 'url',
    ] );

    $wp_customize->add_setting( 'ecm_logo_glb_height', [
        'default'           => 48,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'ecm_logo_glb_height', [
        'label'   => 'ارتفاع اللوجو في الهيدر (px)',
        'section' => 'ecm_logo_3d',
        'type'    => 'number',
    ] );

    $wp_customize->add_setting( 'ecm_logo_glb_autorotate', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ] );
    $wp_customize->add_control( 'ecm_logo_glb_autorotate', [
        'label'   => 'دوران تلقائي',
        'section' => 'ecm_logo_3d',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 'ecm_logo_glb_speed', [
        'default'           => 30,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'ecm_logo_glb_speed', [
        'label'       => 'سرعة الدوران (درجة/ثانية)',
        'section'     => 'ecm_logo_3d',
        'type'        => 'number',
        'input_attrs' => [ 'min' => 5, 'max' => 180, 'step' => 5 ],
    ] );

    $wp_customize->add_setting( 'ecm_logo_glb_controls', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ] );
    $wp_customize->add_control( 'ecm_logo_glb_controls', [
        'label'       => 'تدوير باليد (السحب بالماوس)',
        'section'     => 'ecm_logo_3d',
        'type'        => 'checkbox',
    ] );

    $wp_customize->add_setting( 'ecm_logo_glb_effect', [
        'default'           => 'none',
        'sanitize_callback' => 'sanitize_key',
    ] );
    $wp_customize->add_control( 'ecm_logo_glb_effect', [
        'label'   => 'حركة إضافية',
        'section' => 'ecm_logo_3d',
        'type'    => 'select',
        'choices' => [
            'none'  => 'بدون',
            'float' => 'تعويم (فوق وتحت)',
            'pulse' => 'نبض',
            'swing' => 'تأرجح',
            'tilt'  => 'ميل عند المرور',
        ],
    ] );

    // السطوع / البرايتنس
    $wp_customize->add_setting( 'ecm_logo_glb_exposure', [
        'default'           => 1.0,
        'sanitize_callback' => 'ecm_sanitize_float',
    ] );
    $wp_customize->add_control( 'ecm_logo_glb_exposure', [
        'label'       => 'السطوع / البرايتنس (0–2)',
        'section'     => 'ecm_logo_3d',
        'type'        => 'number',
        'input_attrs' => [ 'min' => 0, 'max' => 2, 'step' => 0.05 ],
    ] );

    // شدة الظل
    $wp_customize->add_setting( 'ecm_logo_glb_shadow', [
        'default'           => 0.0,
        'sanitize_callback' => 'ecm_sanitize_float',
    ] );
    $wp_customize->add_control( 'ecm_logo_glb_shadow', [
        'label'       => 'شدة الظل (0–1)',
        'section'     => 'ecm_logo_3d',
        'type'        => 'number',
        'input_attrs' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ],
    ] );
}
add_action( 'customize_register', 'ecm_logo_3d_customizer' );

/** تنظيف قيمة رقم عشري */
function ecm_sanitize_float( $value ) {
    return is_numeric( $value ) ? (float) $value : 0.0;
}
