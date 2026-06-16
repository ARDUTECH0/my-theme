<?php
/**
 * ECM Customizer — Front Page Settings
 * كل محتوى الصفحة الرئيسية قابل للتعديل
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

function ecm_frontpage_customizer( $wp_customize ) {

    // ════════════════════════════════════════
    // PANEL: الصفحة الرئيسية
    // ════════════════════════════════════════
    $wp_customize->add_panel( 'ecm_frontpage', [
        'title'    => '🏠 الصفحة الرئيسية',
        'priority' => 15,
    ] );


    // ══════════════════════════════════════════════════
    // §1  HERO SECTION
    // ══════════════════════════════════════════════════
    // ══════════════════════════════════════════════════
    // §1  HERO SLIDER (3 slides)
    // ══════════════════════════════════════════════════
    $slides_defaults = [
        1 => [
            'eyebrow' => 'SYSTEM ACTIVE',
            'title_g' => 'التحكم',
            'title_w' => '— ECM',
            'sub'     => 'E.Camera.Man',
            'desc'    => 'نظام تحكم احترافي بالكاميرات — تحكم لاسلكي بدقة عالية وأداء لا مثيل له',
            'btn'     => 'اكتشف الآن',
            'link'    => '#features',
        ],
        2 => [
            'eyebrow' => 'PRECISION CONTROL',
            'title_g' => 'دقة',
            'title_w' => 'بلا حدود',
            'sub'     => '120 FPS — 4K READY',
            'desc'    => 'تحكم في كل محور بدقة عالية — Slider، Pan، Tilt، Zoom، Focus، Crane',
            'btn'     => 'شوف المواصفات',
            'link'    => '#specs',
        ],
        3 => [
            'eyebrow' => 'WIRELESS RANGE',
            'title_g' => '100 متر',
            'title_w' => 'لاسلكي',
            'sub'     => 'WiFi 6 + BLE 5.3',
            'desc'    => 'اتصال مستقر بتأخير أقل من 10ms — تحكم كامل عن بُعد بدون أسلاك',
            'btn'     => 'تواصل معنا',
            'link'    => '#contact',
        ],
    ];

    for ( $s = 1; $s <= 3; $s++ ) {
        $def = $slides_defaults[ $s ];
        $wp_customize->add_section( "ecm_fp_slide_{$s}", [
            'title' => "🎬 سلايد {$s}",
            'panel' => 'ecm_frontpage',
        ] );

        $fields = [
            "ecm_slide_{$s}_eyebrow" => [ $def['eyebrow'], 'النص الصغير (Eyebrow)' ],
            "ecm_slide_{$s}_title_g" => [ $def['title_g'], 'العنوان — الجزء الأخضر' ],
            "ecm_slide_{$s}_title_w" => [ $def['title_w'], 'العنوان — الجزء الأبيض' ],
            "ecm_slide_{$s}_sub"     => [ $def['sub'],     'العنوان الفرعي' ],
            "ecm_slide_{$s}_desc"    => [ $def['desc'],    'الوصف' ],
            "ecm_slide_{$s}_btn"     => [ $def['btn'],     'نص الزر' ],
            "ecm_slide_{$s}_link"    => [ $def['link'],    'رابط الزر' ],
        ];

        foreach ( $fields as $id => $data ) {
            $wp_customize->add_setting( $id, [ 'default' => $data[0], 'sanitize_callback' => 'sanitize_text_field' ] );
            $wp_customize->add_control( $id, [ 'label' => $data[1], 'section' => "ecm_fp_slide_{$s}" ] );
        }

        // صورة خلفية السلايد
        $wp_customize->add_setting( "ecm_slide_{$s}_image", [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "ecm_slide_{$s}_image", [
            'label'   => '🖼 صورة خلفية السلايد',
            'section' => "ecm_fp_slide_{$s}",
        ] ) );

        // شفافية الصورة
        $wp_customize->add_setting( "ecm_slide_{$s}_overlay", [ 'default' => '60', 'sanitize_callback' => 'absint' ] );
        $wp_customize->add_control( "ecm_slide_{$s}_overlay", [
            'label'       => 'شفافية الطبقة السوداء (0-100)',
            'section'     => "ecm_fp_slide_{$s}",
            'type'        => 'number',
            'input_attrs' => [ 'min' => 0, 'max' => 100 ],
        ] );
    }


    // ══════════════════════════════════════════════════
    // §2  STATS BAR
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_fp_stats', [
        'title' => '📊 شريط الإحصائيات',
        'panel' => 'ecm_frontpage',
    ] );

    $stats_defaults = [
        1 => [ '⚡', '120', 'FPS', 'إطارات في الثانية' ],
        2 => [ '📺', '4K', '', 'دقة التصوير' ],
        3 => [ '📡', '100m', '', 'نطاق التحكم' ],
        4 => [ '🔋', '8h', '+', 'عمر البطارية' ],
    ];

    foreach ( $stats_defaults as $i => $def ) {
        $wp_customize->add_setting( "ecm_stat_{$i}_icon",   [ 'default' => $def[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_stat_{$i}_num",    [ 'default' => $def[1], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_stat_{$i}_suffix", [ 'default' => $def[2], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_stat_{$i}_label",  [ 'default' => $def[3], 'sanitize_callback' => 'sanitize_text_field' ] );

        $wp_customize->add_control( "ecm_stat_{$i}_icon",   [ 'label' => "إحصائية {$i} — أيقونة",  'section' => 'ecm_fp_stats' ] );
        $wp_customize->add_control( "ecm_stat_{$i}_num",    [ 'label' => "إحصائية {$i} — الرقم",   'section' => 'ecm_fp_stats' ] );
        $wp_customize->add_control( "ecm_stat_{$i}_suffix", [ 'label' => "إحصائية {$i} — لاحقة",   'section' => 'ecm_fp_stats' ] );
        $wp_customize->add_control( "ecm_stat_{$i}_label",  [ 'label' => "إحصائية {$i} — التسمية", 'section' => 'ecm_fp_stats' ] );
    }


    // ══════════════════════════════════════════════════
    // §3  CONTROL CARDS
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_fp_controls', [
        'title' => '🎛 كروت التحكم',
        'panel' => 'ecm_frontpage',
    ] );

    // Section title
    $wp_customize->add_setting( 'ecm_ctrl_eyebrow', [ 'default' => '01 — CAMERA CONTROLS', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_ctrl_eyebrow', [ 'label' => 'Eyebrow القسم', 'section' => 'ecm_fp_controls' ] );
    $wp_customize->add_setting( 'ecm_ctrl_title', [ 'default' => 'لوحة التحكم', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_ctrl_title', [ 'label' => 'عنوان القسم', 'section' => 'ecm_fp_controls' ] );
    $wp_customize->add_setting( 'ecm_ctrl_desc', [ 'default' => 'تحكم كامل بجميع محاور الكاميرا بدقة متناهية', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_ctrl_desc', [ 'label' => 'وصف القسم', 'section' => 'ecm_fp_controls' ] );

    $ctrl_defaults = [
        1 => [ '↔', 'SLIDER', '9', '52', '52', '105' ],
        2 => [ '↻', 'PAN',    '14', '78', '78', '180' ],
        3 => [ '↕', 'TILT',   '7', '45', '45', '90' ],
        4 => [ '◎', 'FOCUS',  '12', '65', '65', '100' ],
        5 => [ '🔍', 'ZOOM',  '5', '35', '35', '100' ],
        6 => [ '🎬', 'CRANE', '8', '60', '60', '120' ],
    ];

    foreach ( $ctrl_defaults as $i => $def ) {
        $wp_customize->add_setting( "ecm_card_{$i}_icon",    [ 'default' => $def[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_card_{$i}_title",   [ 'default' => $def[1], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_card_{$i}_speed",   [ 'default' => $def[2], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_card_{$i}_bar",     [ 'default' => $def[3], 'sanitize_callback' => 'absint' ] );
        $wp_customize->add_setting( "ecm_card_{$i}_current", [ 'default' => $def[4], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_card_{$i}_target",  [ 'default' => $def[5], 'sanitize_callback' => 'sanitize_text_field' ] );

        $wp_customize->add_control( "ecm_card_{$i}_icon",    [ 'label' => "كارت {$i} — أيقونة", 'section' => 'ecm_fp_controls' ] );
        $wp_customize->add_control( "ecm_card_{$i}_title",   [ 'label' => "كارت {$i} — العنوان", 'section' => 'ecm_fp_controls' ] );
        $wp_customize->add_control( "ecm_card_{$i}_speed",   [ 'label' => "كارت {$i} — السرعة",  'section' => 'ecm_fp_controls' ] );
        $wp_customize->add_control( "ecm_card_{$i}_bar",     [ 'label' => "كارت {$i} — % الشريط", 'section' => 'ecm_fp_controls', 'type' => 'number', 'input_attrs' => [ 'min' => 0, 'max' => 100 ] ] );
        $wp_customize->add_control( "ecm_card_{$i}_current", [ 'label' => "كارت {$i} — الحالي",   'section' => 'ecm_fp_controls' ] );
        $wp_customize->add_control( "ecm_card_{$i}_target",  [ 'label' => "كارت {$i} — الهدف",    'section' => 'ecm_fp_controls' ] );
    }


    // ══════════════════════════════════════════════════
    // §4  FEATURE CARDS
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_fp_features', [
        'title' => '✨ كروت المميزات',
        'panel' => 'ecm_frontpage',
    ] );

    $wp_customize->add_setting( 'ecm_feat_eyebrow', [ 'default' => '02 — FEATURES', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_feat_eyebrow', [ 'label' => 'Eyebrow القسم', 'section' => 'ecm_fp_features' ] );
    $wp_customize->add_setting( 'ecm_feat_title', [ 'default' => 'المميزات', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_feat_title', [ 'label' => 'عنوان القسم', 'section' => 'ecm_fp_features' ] );
    $wp_customize->add_setting( 'ecm_feat_desc', [ 'default' => 'تقنيات متقدمة لتجربة تصوير احترافية بلا حدود', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_feat_desc', [ 'label' => 'وصف القسم', 'section' => 'ecm_fp_features' ] );

    $feat_defaults = [
        1 => [ '📡', 'التحكم اللاسلكي', 'اتصال مستقر على مسافة تصل إلى 100 متر مع تأخير أقل من 10 مللي ثانية للتحكم الفوري.' ],
        2 => [ '🎥', 'بث مباشر', 'بث مباشر بدقة 4K مع مراقبة فورية وتحكم كامل في جميع إعدادات الكاميرا عن بُعد.' ],
        3 => [ '🎯', 'دقة عالية', 'محركات عالية الدقة مع نظام تثبيت متقدم يضمن حركة سلسة بدون أي اهتزاز.' ],
        4 => [ '🔋', 'بطارية طويلة', 'بطارية ليثيوم عالية الكفاءة تدوم حتى 8 ساعات متواصلة مع شحن سريع في 90 دقيقة.' ],
        5 => [ '📱', 'تطبيق ذكي', 'تطبيق متوافق مع iOS و Android للتحكم والمراقبة وبرمجة مسارات الكاميرا مسبقاً.' ],
        6 => [ '⚙️', 'برمجة المسارات', 'إمكانية برمجة مسارات حركة معقدة مسبقاً وتشغيلها بضغطة زر للقطات متكررة بدقة.' ],
    ];

    foreach ( $feat_defaults as $i => $def ) {
        $wp_customize->add_setting( "ecm_feat_{$i}_icon",  [ 'default' => $def[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_feat_{$i}_title", [ 'default' => $def[1], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_feat_{$i}_text",  [ 'default' => $def[2], 'sanitize_callback' => 'sanitize_text_field' ] );

        $wp_customize->add_control( "ecm_feat_{$i}_icon",  [ 'label' => "ميزة {$i} — أيقونة", 'section' => 'ecm_fp_features' ] );
        $wp_customize->add_control( "ecm_feat_{$i}_title", [ 'label' => "ميزة {$i} — العنوان", 'section' => 'ecm_fp_features' ] );
        $wp_customize->add_control( "ecm_feat_{$i}_text",  [ 'label' => "ميزة {$i} — الوصف",  'section' => 'ecm_fp_features', 'type' => 'textarea' ] );
    }


    // ══════════════════════════════════════════════════
    // §5  SPECS TABLE
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_fp_specs', [
        'title' => '📋 المواصفات الفنية',
        'panel' => 'ecm_frontpage',
    ] );

    $wp_customize->add_setting( 'ecm_spec_eyebrow', [ 'default' => '03 — SPECIFICATIONS', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_spec_eyebrow', [ 'label' => 'Eyebrow القسم', 'section' => 'ecm_fp_specs' ] );
    $wp_customize->add_setting( 'ecm_spec_title', [ 'default' => 'المواصفات الفنية', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_spec_title', [ 'label' => 'عنوان القسم', 'section' => 'ecm_fp_specs' ] );
    $wp_customize->add_setting( 'ecm_spec_desc', [ 'default' => 'مواصفات تقنية احترافية لأفضل أداء', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_spec_desc', [ 'label' => 'وصف القسم', 'section' => 'ecm_fp_specs' ] );

    $spec_defaults = [
        1 => [ 'الوزن', '450g', '' ],
        2 => [ 'الأبعاد', '280 × 85 × 45 mm', '' ],
        3 => [ 'نطاق التحكم', '100m', 'yes' ],
        4 => [ 'البطارية', 'Li-Po 5000mAh', '' ],
        5 => [ 'وقت الشحن', '90 دقيقة', '' ],
        6 => [ 'دقة الفيديو', '4K @ 120fps', 'yes' ],
        7 => [ 'درجة التشغيل', '-10°C ~ +50°C', '' ],
        8 => [ 'التوصيل', 'WiFi 6 + BLE 5.3', '' ],
        9 => [ 'التوافق', 'Canon / Sony / Nikon / RED', '' ],
    ];

    foreach ( $spec_defaults as $i => $def ) {
        $wp_customize->add_setting( "ecm_spec_{$i}_label", [ 'default' => $def[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_spec_{$i}_val",   [ 'default' => $def[1], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_spec_{$i}_hl",    [ 'default' => $def[2], 'sanitize_callback' => 'sanitize_text_field' ] );

        $wp_customize->add_control( "ecm_spec_{$i}_label", [ 'label' => "مواصفة {$i} — الاسم",  'section' => 'ecm_fp_specs' ] );
        $wp_customize->add_control( "ecm_spec_{$i}_val",   [ 'label' => "مواصفة {$i} — القيمة", 'section' => 'ecm_fp_specs' ] );
        $wp_customize->add_control( "ecm_spec_{$i}_hl",    [ 'label' => "مواصفة {$i} — تمييز (اكتب yes)", 'section' => 'ecm_fp_specs' ] );
    }


    // ══════════════════════════════════════════════════
    // §6  CTA SECTION
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_fp_cta', [
        'title' => '📢 قسم التواصل (CTA)',
        'panel' => 'ecm_frontpage',
    ] );

    $cta = [
        'ecm_cta_eyebrow'  => [ 'READY?', 'النص الصغير' ],
        'ecm_cta_title'    => [ 'جاهز تبدأ؟', 'العنوان' ],
        'ecm_cta_desc'     => [ 'تواصل معنا الآن واحصل على نظام ECM لتحكم احترافي بالكاميرات', 'الوصف' ],
        'ecm_cta_btn_text' => [ 'تواصل معنا', 'نص الزر' ],
        'ecm_cta_btn2_text' => [ 'الرجوع للأعلى', 'نص الزر الثاني' ],
    ];

    foreach ( $cta as $id => $data ) {
        $wp_customize->add_setting( $id, [ 'default' => $data[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_control( $id, [ 'label' => $data[1], 'section' => 'ecm_fp_cta' ] );
    }


    // ══════════════════════════════════════════════════
    // §7  DOCUMENTATION SECTION
    // ══════════════════════════════════════════════════
    $wp_customize->add_section( 'ecm_fp_docs', [
        'title' => '📘 التوثيق والأدلة',
        'panel' => 'ecm_frontpage',
    ] );

    $wp_customize->add_setting( 'ecm_docs_eyebrow', [ 'default' => '04 — DOCUMENTATION', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_docs_eyebrow', [ 'label' => 'Eyebrow القسم', 'section' => 'ecm_fp_docs' ] );
    $wp_customize->add_setting( 'ecm_docs_title', [ 'default' => 'التوثيق والأدلة', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_docs_title', [ 'label' => 'عنوان القسم', 'section' => 'ecm_fp_docs' ] );
    $wp_customize->add_setting( 'ecm_docs_desc', [ 'default' => 'كل ما تحتاجه لاستخدام وصيانة نظام ECM', 'sanitize_callback' => 'sanitize_text_field' ] );
    $wp_customize->add_control( 'ecm_docs_desc', [ 'label' => 'وصف القسم', 'section' => 'ecm_fp_docs' ] );

    $docs_defaults = [
        1 => [ '📘', 'دليل المستخدم', 'شرح شامل لكل وظائف النظام من البداية للاحتراف مع صور توضيحية.', 'PDF — 24 صفحة' ],
        2 => [ '🔧', 'دليل التركيب', 'خطوات تركيب وإعداد النظام على أي كاميرا مع إعدادات البلوتوث والواي فاي.', 'PDF — 12 صفحة' ],
        3 => [ '📱', 'دليل التطبيق', 'كيفية تحميل وإعداد تطبيق ECM على iOS و Android وربطه بالنظام.', 'PDF — 8 صفحات' ],
        4 => [ '⚡', 'الأسئلة الشائعة', 'إجابات لأكثر الأسئلة شيوعاً حول النظام والضمان والدعم الفني.', 'FAQ — محدّث' ],
        5 => [ '🎥', 'فيديو تعليمي', 'سلسلة فيديوهات تشرح كل خطوة من الإعداد للتشغيل في الميدان.', 'فيديو — 6 حلقات' ],
        6 => [ '🛡', 'الضمان والصيانة', 'شروط الضمان وجدول الصيانة الدورية وكيفية طلب قطع الغيار.', 'PDF — 4 صفحات' ],
    ];

    foreach ( $docs_defaults as $i => $def ) {
        $wp_customize->add_setting( "ecm_doc_{$i}_icon",  [ 'default' => $def[0], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_doc_{$i}_title", [ 'default' => $def[1], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_doc_{$i}_desc",  [ 'default' => $def[2], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_doc_{$i}_meta",  [ 'default' => $def[3], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp_customize->add_setting( "ecm_doc_{$i}_link",  [ 'default' => '',       'sanitize_callback' => 'esc_url_raw' ] );

        $wp_customize->add_control( "ecm_doc_{$i}_icon",  [ 'label' => "دليل {$i} — أيقونة",   'section' => 'ecm_fp_docs' ] );
        $wp_customize->add_control( "ecm_doc_{$i}_title", [ 'label' => "دليل {$i} — العنوان",   'section' => 'ecm_fp_docs' ] );
        $wp_customize->add_control( "ecm_doc_{$i}_desc",  [ 'label' => "دليل {$i} — الوصف",    'section' => 'ecm_fp_docs', 'type' => 'textarea' ] );
        $wp_customize->add_control( "ecm_doc_{$i}_meta",  [ 'label' => "دليل {$i} — نوع الملف", 'section' => 'ecm_fp_docs' ] );
        $wp_customize->add_control( "ecm_doc_{$i}_link",  [ 'label' => "دليل {$i} — رابط التحميل", 'section' => 'ecm_fp_docs', 'type' => 'url' ] );
    }
}

add_action( 'customize_register', 'ecm_frontpage_customizer' );
