<?php
/**
 * ECM — Elementor Auto-Seeder
 *
 * يحوّل تصميم الصفحة الرئيسية (الهاردكود) إلى بيانات Elementor حقيقية،
 * فتظهر كل الأقسام داخل Elementor كبلوكات قابلة للسحب والتعديل وتغيير الأماكن.
 *
 * يعمل تلقائيًا مرة واحدة عند تفعيل الثيم (مع حماية: لا يكتب فوق صفحة
 * سبق بناؤها بـ Elementor).
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════
// §1  مولّدات عناصر Elementor (Helpers)
// ════════════════════════════════════════════════════════════

/** مُعرّف فريد بصيغة Elementor (7 خانات hex) */
function ecm_el_id(): string {
    return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
}

/** settings لازم تكون object في JSON — حتى لو فاضية */
function ecm_el_settings( array $settings ) {
    return empty( $settings ) ? new \stdClass() : $settings;
}

/** ودجِت */
function ecm_el_widget( string $type, array $settings = [] ): array {
    return [
        'id'         => ecm_el_id(),
        'elType'     => 'widget',
        'widgetType' => $type,
        'settings'   => ecm_el_settings( $settings ),
        'elements'   => [],
    ];
}

/** عمود */
function ecm_el_column( int $size, array $elements, array $settings = [] ): array {
    $settings = array_merge( [ '_column_size' => $size, '_inline_size' => null ], $settings );
    return [
        'id'       => ecm_el_id(),
        'elType'   => 'column',
        'settings' => ecm_el_settings( $settings ),
        'elements' => $elements,
        'isInner'  => false,
    ];
}

/** سيكشن */
function ecm_el_section( array $columns, array $settings = [], bool $inner = false ): array {
    return [
        'id'       => ecm_el_id(),
        'elType'   => 'section',
        'settings' => ecm_el_settings( $settings ),
        'elements' => $columns,
        'isInner'  => $inner,
    ];
}

/**
 * شبكة (Grid): يقسّم الودجِتس على صفوف، كل صف سيكشن بأعمدة متساوية.
 * يعيد مصفوفة سيكشنز.
 */
function ecm_el_grid( array $widgets, int $per_row ): array {
    $sections = [];
    $size     = (int) floor( 100 / max( 1, $per_row ) );
    foreach ( array_chunk( $widgets, $per_row ) as $chunk ) {
        $cols = [];
        foreach ( $chunk as $w ) {
            $cols[] = ecm_el_column( $size, [ $w ] );
        }
        $sections[] = ecm_el_section( $cols, [
            'gap'       => 'extended',
            'structure' => (string) ( $per_row * 10 ),
        ] );
    }
    return $sections;
}

/** عنوان قسم موحّد: Eyebrow + H2 + وصف (متمركز) */
function ecm_el_header( string $eyebrow, string $title, string $desc = '' ): array {
    $widgets = [];
    if ( $eyebrow !== '' ) {
        $widgets[] = ecm_el_widget( 'ecm_eyebrow', [
            'text'      => $eyebrow,
            'show_dot'  => 'yes',
            'html_tag'  => 'span',
            '_css_classes' => 'ecm-el-center',
        ] );
    }
    $widgets[] = ecm_el_widget( 'heading', [
        'title'        => $title,
        'header_size'  => 'h2',
        'align'        => 'center',
    ] );
    if ( $desc !== '' ) {
        $widgets[] = ecm_el_widget( 'text-editor', [
            'editor' => '<p style="text-align:center;">' . esc_html( $desc ) . '</p>',
            'align'  => 'center',
        ] );
    }
    return ecm_el_section(
        [ ecm_el_column( 100, $widgets ) ],
        [ 'content_position' => 'middle' ]
    );
}

/** زر بستايل الثيم */
function ecm_el_button( string $text, string $url, string $classes = 'ecm-btn-primary' ): array {
    return ecm_el_widget( 'button', [
        'text'         => $text,
        'link'         => [ 'url' => $url, 'is_external' => '', 'nofollow' => '' ],
        'align'        => 'center',
        '_css_classes' => $classes,
    ] );
}


// ════════════════════════════════════════════════════════════
// §2  بناء محتوى الصفحة الرئيسية (مطابق للتصميم الجاهز)
// ════════════════════════════════════════════════════════════
function ecm_build_frontpage_elements(): array {
    $m = function ( $key, $default = '' ) {
        return get_theme_mod( $key, $default );
    };

    $sections = [];

    // ── HERO (من السلايد الأول) ──
    $hero_widgets = [
        ecm_el_widget( 'ecm_eyebrow', [
            'text'        => $m( 'ecm_slide_1_eyebrow', 'SYSTEM ACTIVE' ),
            'show_dot'    => 'yes',
            'html_tag'    => 'span',
            '_css_classes'=> 'ecm-el-center',
        ] ),
        ecm_el_widget( 'heading', [
            'title'       => trim( $m( 'ecm_slide_1_title_g', 'التحكم' ) . ' ' . $m( 'ecm_slide_1_title_w', '— ECM' ) ),
            'header_size' => 'h1',
            'align'       => 'center',
        ] ),
        ecm_el_widget( 'heading', [
            'title'       => $m( 'ecm_slide_1_sub', 'E.Camera.Man' ),
            'header_size' => 'h3',
            'align'       => 'center',
        ] ),
        ecm_el_widget( 'text-editor', [
            'editor' => '<p style="text-align:center;">' . esc_html( $m( 'ecm_slide_1_desc', 'نظام تحكم احترافي بالكاميرات — تحكم لاسلكي بدقة عالية وأداء لا مثيل له' ) ) . '</p>',
        ] ),
        ecm_el_button( $m( 'ecm_slide_1_btn', 'اكتشف الآن' ), $m( 'ecm_slide_1_link', '#features' ) ),
    ];
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, $hero_widgets ) ],
        [
            'content_position' => 'middle',
            'padding'          => [ 'unit' => 'px', 'top' => '120', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ],
        ]
    );

    // ── STATS ──
    $stat_defaults = [
        [ '120', 'FPS', 'إطارات في الثانية' ],
        [ '4K',  '',    'دقة التصوير' ],
        [ '100m','',    'نطاق التحكم' ],
        [ '8h',  '+',   'عمر البطارية' ],
    ];
    $stat_widgets = [];
    foreach ( $stat_defaults as $i => $d ) {
        $n = $i + 1;
        $stat_widgets[] = ecm_el_widget( 'ecm_stat_box', [
            'number' => $m( "ecm_stat_{$n}_num",    $d[0] ),
            'suffix' => $m( "ecm_stat_{$n}_suffix", $d[1] ),
            'label'  => $m( "ecm_stat_{$n}_label",  $d[2] ),
        ] );
    }
    $sections = array_merge( $sections, ecm_el_grid( $stat_widgets, 4 ) );

    // ── CONTROL CARDS ──
    $sections[] = ecm_el_header(
        $m( 'ecm_ctrl_eyebrow', '01 — CAMERA CONTROLS' ),
        $m( 'ecm_ctrl_title', 'لوحة التحكم' ),
        $m( 'ecm_ctrl_desc', 'تحكم كامل بجميع محاور الكاميرا بدقة متناهية' )
    );
    $card_defaults = [
        [ '↔',  'SLIDER', 9,  52, 52, 105 ],
        [ '↻',  'PAN',    14, 78, 78, 180 ],
        [ '↕',  'TILT',   7,  45, 45, 90  ],
        [ '◎',  'FOCUS',  12, 65, 65, 100 ],
        [ '🔍', 'ZOOM',   5,  35, 35, 100 ],
        [ '🎬', 'CRANE',  8,  60, 60, 120 ],
    ];
    $ctrl_widgets = [];
    foreach ( $card_defaults as $i => $d ) {
        $n = $i + 1;
        $ctrl_widgets[] = ecm_el_widget( 'ecm_ctrl_card', [
            'icon'        => $m( "ecm_card_{$n}_icon",  $d[0] ),
            'title'       => $m( "ecm_card_{$n}_title", $d[1] ),
            'speed_label' => 'SPEED',
            'speed_val'   => (int) $m( "ecm_card_{$n}_speed", $d[2] ),
            'bar_pct'     => [ 'unit' => 'px', 'size' => (int) $m( "ecm_card_{$n}_bar", $d[3] ) ],
            'current_label' => 'Current',
            'current_val'   => (int) $m( "ecm_card_{$n}_current", $d[4] ),
            'target_label'  => 'Target',
            'target_val'    => (int) $m( "ecm_card_{$n}_target", $d[5] ),
        ] );
    }
    $sections = array_merge( $sections, ecm_el_grid( $ctrl_widgets, 3 ) );

    // ── FEATURES ──
    $sections[] = ecm_el_header(
        $m( 'ecm_feat_eyebrow', '02 — FEATURES' ),
        $m( 'ecm_feat_title', 'المميزات' ),
        $m( 'ecm_feat_desc', 'تقنيات متقدمة لتجربة تصوير احترافية بلا حدود' )
    );
    $feat_defaults = [
        [ '📡', 'التحكم اللاسلكي', 'اتصال مستقر على مسافة تصل إلى 100 متر مع تأخير أقل من 10 مللي ثانية للتحكم الفوري.' ],
        [ '🎥', 'بث مباشر', 'بث مباشر بدقة 4K مع مراقبة فورية وتحكم كامل في جميع إعدادات الكاميرا عن بُعد.' ],
        [ '🎯', 'دقة عالية', 'محركات عالية الدقة مع نظام تثبيت متقدم يضمن حركة سلسة بدون أي اهتزاز.' ],
        [ '🔋', 'بطارية طويلة', 'بطارية ليثيوم عالية الكفاءة تدوم حتى 8 ساعات متواصلة مع شحن سريع في 90 دقيقة.' ],
        [ '📱', 'تطبيق ذكي', 'تطبيق متوافق مع iOS و Android للتحكم والمراقبة وبرمجة مسارات الكاميرا مسبقاً.' ],
        [ '⚙️', 'برمجة المسارات', 'إمكانية برمجة مسارات حركة معقدة مسبقاً وتشغيلها بضغطة زر للقطات متكررة بدقة.' ],
    ];
    $feat_widgets = [];
    foreach ( $feat_defaults as $i => $d ) {
        $n = $i + 1;
        $feat_widgets[] = ecm_el_widget( 'ecm_feat_card', [
            'icon'  => $m( "ecm_feat_{$n}_icon",  $d[0] ),
            'title' => $m( "ecm_feat_{$n}_title", $d[1] ),
            'text'  => $m( "ecm_feat_{$n}_text",  $d[2] ),
        ] );
    }
    $sections = array_merge( $sections, ecm_el_grid( $feat_widgets, 3 ) );

    // ── SPECS (قائمة عمودية) ──
    $sections[] = ecm_el_header(
        $m( 'ecm_spec_eyebrow', '03 — SPECIFICATIONS' ),
        $m( 'ecm_spec_title', 'المواصفات الفنية' ),
        $m( 'ecm_spec_desc', 'مواصفات تقنية احترافية لأفضل أداء' )
    );
    $spec_defaults = [
        [ 'الوزن', '450g', '' ],
        [ 'الأبعاد', '280 × 85 × 45 mm', '' ],
        [ 'نطاق التحكم', '100m', 'yes' ],
        [ 'البطارية', 'Li-Po 5000mAh', '' ],
        [ 'وقت الشحن', '90 دقيقة', '' ],
        [ 'دقة الفيديو', '4K @ 120fps', 'yes' ],
        [ 'درجة التشغيل', '-10°C ~ +50°C', '' ],
        [ 'التوصيل', 'WiFi 6 + BLE 5.3', '' ],
        [ 'التوافق', 'Canon / Sony / Nikon / RED', '' ],
    ];
    $spec_widgets = [];
    foreach ( $spec_defaults as $i => $d ) {
        $n = $i + 1;
        $spec_widgets[] = ecm_el_widget( 'ecm_spec_row', [
            'label'     => $m( "ecm_spec_{$n}_label", $d[0] ),
            'val'       => $m( "ecm_spec_{$n}_val",   $d[1] ),
            'highlight' => $m( "ecm_spec_{$n}_hl",    $d[2] ) === 'yes' ? 'yes' : 'no',
        ] );
    }
    $sections[] = ecm_el_section( [ ecm_el_column( 100, $spec_widgets ) ] );

    // ── DOCUMENTATION (كروت من heading + نص) ──
    $sections[] = ecm_el_header(
        $m( 'ecm_docs_eyebrow', '04 — DOCUMENTATION' ),
        $m( 'ecm_docs_title', 'التوثيق والأدلة' ),
        $m( 'ecm_docs_desc', 'كل ما تحتاجه لاستخدام وصيانة نظام ECM' )
    );
    $docs_defaults = [
        [ '📘', 'دليل المستخدم', 'شرح شامل لكل وظائف النظام من البداية للاحتراف مع صور توضيحية.', 'PDF — 24 صفحة' ],
        [ '🔧', 'دليل التركيب', 'خطوات تركيب وإعداد النظام على أي كاميرا مع إعدادات البلوتوث والواي فاي.', 'PDF — 12 صفحة' ],
        [ '📱', 'دليل التطبيق', 'كيفية تحميل وإعداد تطبيق ECM على iOS و Android وربطه بالنظام.', 'PDF — 8 صفحات' ],
        [ '⚡', 'الأسئلة الشائعة', 'إجابات لأكثر الأسئلة شيوعاً حول النظام والضمان والدعم الفني.', 'FAQ — محدّث' ],
        [ '🎥', 'فيديو تعليمي', 'سلسلة فيديوهات تشرح كل خطوة من الإعداد للتشغيل في الميدان.', 'فيديو — 6 حلقات' ],
        [ '🛡', 'الضمان والصيانة', 'شروط الضمان وجدول الصيانة الدورية وكيفية طلب قطع الغيار.', 'PDF — 4 صفحات' ],
    ];
    $doc_widgets = [];
    foreach ( $docs_defaults as $d ) {
        // كل كارت = عمود فيه عنوان + وصف + شارة
        $doc_widgets[] = ecm_el_widget( 'heading', [
            'title'       => $d[0] . ' ' . $d[1],
            'header_size' => 'h3',
        ] );
        $doc_widgets[] = ecm_el_widget( 'text-editor', [
            'editor' => '<p>' . esc_html( $d[2] ) . '</p><p><strong>' . esc_html( $d[3] ) . '</strong></p>',
        ] );
    }
    // اجمع كل كارت (عنوان + وصف) في عمود واحد — 3 كروت بالصف
    $doc_cols = [];
    foreach ( array_chunk( $doc_widgets, 2 ) as $pair ) {
        $doc_cols[] = ecm_el_column( 33, $pair );
    }
    foreach ( array_chunk( $doc_cols, 3 ) as $row ) {
        $sections[] = ecm_el_section( $row, [ 'gap' => 'extended', 'structure' => '30' ] );
    }

    // ── CTA ──
    $wa    = get_theme_mod( 'ecm_whatsapp', '' );
    $email = get_theme_mod( 'ecm_email', '' );
    $cta_widgets = [
        ecm_el_widget( 'ecm_eyebrow', [
            'text' => $m( 'ecm_cta_eyebrow', 'READY?' ), 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center',
        ] ),
        ecm_el_widget( 'heading', [
            'title' => $m( 'ecm_cta_title', 'جاهز تبدأ؟' ), 'header_size' => 'h2', 'align' => 'center',
        ] ),
        ecm_el_widget( 'text-editor', [
            'editor' => '<p style="text-align:center;">' . esc_html( $m( 'ecm_cta_desc', 'تواصل معنا الآن واحصل على نظام ECM لتحكم احترافي بالكاميرات' ) ) . '</p>',
        ] ),
    ];
    if ( $wa ) {
        $cta_widgets[] = ecm_el_button( $m( 'ecm_cta_btn_text', 'تواصل معنا' ), 'https://wa.me/' . $wa );
    } else {
        $cta_widgets[] = ecm_el_button( $m( 'ecm_cta_btn_text', 'تواصل معنا' ), $email ? 'mailto:' . $email : '#' );
    }
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, $cta_widgets ) ],
        [
            'content_position' => 'middle',
            'padding'          => [ 'unit' => 'px', 'top' => '80', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ],
        ]
    );

    return $sections;
}


/** ودجِت قائمة (نقطية أو مرقّمة) */
function ecm_el_list( array $items, bool $ordered = false ): array {
    $tag  = $ordered ? 'ol' : 'ul';
    $html = '<' . $tag . '>';
    foreach ( $items as $it ) {
        if ( $it === '' ) continue;
        $html .= '<li>' . esc_html( $it ) . '</li>';
    }
    $html .= '</' . $tag . '>';
    return ecm_el_widget( 'text-editor', [ 'editor' => $html ] );
}


// ════════════════════════════════════════════════════════════
// §2b  محتوى صفحة تحميل التطبيق
// ════════════════════════════════════════════════════════════
function ecm_build_app_elements(): array {
    $m = function ( $k, $d = '' ) { return get_theme_mod( $k, $d ); };
    $sections = [];

    // Hero
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'ecm_eyebrow', [ 'text' => 'DOWNLOAD APPLICATION', 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center' ] ),
            ecm_el_widget( 'heading', [ 'title' => $m( 'ecm_app_name', 'ECM Controller' ), 'header_size' => 'h1', 'align' => 'center' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">' . esc_html( $m( 'ecm_app_desc', 'تطبيق التحكم الاحترافي بالكاميرات — تحكم لاسلكي، برمجة مسارات، ومراقبة مباشرة.' ) ) . '</p>' ] ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '120', 'right' => '20', 'bottom' => '60', 'left' => '20', 'isLinked' => false ] ]
    );

    // Apps snapshot (لقطة من التطبيقات المضافة في لوحة التحكم وقت التحويل)
    $admin_apps = get_option( 'ecm_apps_data', [] );
    $app_widgets = [];
    foreach ( (array) $admin_apps as $app ) {
        if ( empty( $app['name'] ) ) continue;
        $meta = [];
        if ( ! empty( $app['version'] ) ) $meta[] = 'v' . $app['version'];
        if ( ! empty( $app['size'] ) )    $meta[] = $app['size'];
        $col_widgets = [
            ecm_el_widget( 'heading', [ 'title' => $app['name'], 'header_size' => 'h3', 'align' => 'center' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">' . esc_html( implode( ' · ', $meta ) ) . '</p>' ] ),
        ];
        if ( ! empty( $app['link'] ) ) {
            $col_widgets[] = ecm_el_button( '⬇️ تحميل — Download', $app['link'] );
        }
        $app_widgets[] = $col_widgets;
    }
    if ( $app_widgets ) {
        $sections[] = ecm_el_header( 'CHOOSE YOUR PLATFORM', 'تحميل التطبيق لجميع الأجهزة', 'اختر المنصة الخاصة بك للتحميل المباشر أو الانتقال للمتجر' );
        foreach ( array_chunk( $app_widgets, 3 ) as $row ) {
            $cols = [];
            foreach ( $row as $cw ) $cols[] = ecm_el_column( 33, $cw );
            $sections[] = ecm_el_section( $cols, [ 'gap' => 'extended', 'structure' => '30' ] );
        }
    }

    // App features
    $sections[] = ecm_el_header( 'APP FEATURES', 'مميزات التطبيق — Key Features', 'كل ما تحتاجه للتحكم الاحترافي في تطبيق واحد' );
    $features = [
        [ '📡', 'تحكم لاسلكي — Wireless Control', 'تحكم كامل بالكاميرا عبر WiFi أو Bluetooth بتأخير أقل من 10ms.' ],
        [ '🎬', 'برمجة المسارات — Track Programming', 'سجّل مسارات حركة وشغّلها بضغطة زر مع تحكم في السرعة.' ],
        [ '📺', 'بث مباشر — Live Feed', 'شاهد ما تصوره الكاميرا مباشرة على شاشة جهازك.' ],
        [ '🔋', 'مراقبة البطارية — Battery Monitor', 'متابعة مستوى بطارية الجهاز والكاميرا في الوقت الحقيقي.' ],
        [ '⚙️', 'إعدادات متقدمة — Advanced Settings', 'تحكم في كل إعداد — ISO، White Balance، Shutter Speed.' ],
        [ '☁️', 'نسخ احتياطي — Cloud Backup', 'حفظ إعداداتك ومساراتك على السحابة واسترجاعها في أي وقت.' ],
    ];
    $fw = [];
    foreach ( $features as $f ) {
        $fw[] = ecm_el_widget( 'ecm_feat_card', [ 'icon' => $f[0], 'title' => $f[1], 'text' => $f[2] ] );
    }
    $sections = array_merge( $sections, ecm_el_grid( $fw, 3 ) );

    // Requirements
    $sections[] = ecm_el_header( 'SYSTEM REQUIREMENTS', 'المتطلبات — Requirements', '' );
    $reqs = [
        [ '🤖 Android', 'Android 8.0 or higher', 'no' ],
        [ '🍎 iOS', 'iOS 14.0 or higher', 'no' ],
        [ '📡 Connection', 'WiFi 5Ghz or Bluetooth 5.0+', 'no' ],
        [ '💵 Price', 'Free / مجاني', 'yes' ],
    ];
    $rw = [];
    foreach ( $reqs as $r ) {
        $rw[] = ecm_el_widget( 'ecm_spec_row', [ 'label' => $r[0], 'val' => $r[1], 'highlight' => $r[2] ] );
    }
    $sections[] = ecm_el_section( [ ecm_el_column( 100, $rw ) ] );

    // CTA
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'ecm_eyebrow', [ 'text' => 'START NOW', 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center' ] ),
            ecm_el_widget( 'heading', [ 'title' => 'ابدأ التحكم في كاميرتك الآن', 'header_size' => 'h2', 'align' => 'center' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">حمّل التطبيق الرسمي واستمتع بحرية الإبداع اللاسلكي</p>' ] ),
            ecm_el_button( '← الرجوع للرئيسية — Home Page', home_url( '/' ), 'ecm-btn-ghost' ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '80', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ] ]
    );

    return $sections;
}


// ════════════════════════════════════════════════════════════
// §2c  محتوى صفحة التوثيق (نسخة يدوية — بدون BetterDocs)
// ════════════════════════════════════════════════════════════
function ecm_build_docs_elements(): array {
    $m = function ( $k, $d = '' ) { return get_theme_mod( $k, $d ); };
    $sections = [];

    // Hero
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'ecm_eyebrow', [ 'text' => 'DOCUMENTATION', 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center' ] ),
            ecm_el_widget( 'heading', [ 'title' => $m( 'ecm_docspage_title', 'التوثيق والأدلة' ), 'header_size' => 'h1', 'align' => 'center' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">' . esc_html( $m( 'ecm_docspage_desc', 'كل ما تحتاجه لاستخدام نظام ECM — أدلة شاملة، شروحات مصورة، وأسئلة شائعة' ) ) . '</p>' ] ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '120', 'right' => '20', 'bottom' => '60', 'left' => '20', 'isLinked' => false ] ]
    );

    // Manual docs
    $sections[] = ecm_el_header( 'MANUAL DOCS', 'الأدلة المتوفرة', '' );
    $docs_defaults = [
        [ '📘', 'دليل المستخدم', 'شرح شامل لكل وظائف النظام من البداية للاحتراف.', 'PDF — 24 صفحة' ],
        [ '🔧', 'دليل التركيب', 'خطوات تركيب وإعداد النظام على أي كاميرا.', 'PDF — 12 صفحة' ],
        [ '📱', 'دليل التطبيق', 'كيفية تحميل وإعداد تطبيق ECM.', 'PDF — 8 صفحات' ],
        [ '⚡', 'الأسئلة الشائعة', 'إجابات لأكثر الأسئلة شيوعاً.', 'FAQ — محدّث' ],
        [ '🎥', 'فيديو تعليمي', 'سلسلة فيديوهات تعليمية.', 'فيديو — 6 حلقات' ],
        [ '🛡', 'الضمان والصيانة', 'شروط الضمان وجدول الصيانة.', 'PDF — 4 صفحات' ],
    ];
    $doc_widgets = [];
    foreach ( $docs_defaults as $d ) {
        $doc_widgets[] = ecm_el_widget( 'heading', [ 'title' => $d[0] . ' ' . $d[1], 'header_size' => 'h3' ] );
        $doc_widgets[] = ecm_el_widget( 'text-editor', [ 'editor' => '<p>' . esc_html( $d[2] ) . '</p><p><strong>' . esc_html( $d[3] ) . '</strong></p>' ] );
    }
    $doc_cols = [];
    foreach ( array_chunk( $doc_widgets, 2 ) as $pair ) {
        $doc_cols[] = ecm_el_column( 33, $pair );
    }
    foreach ( array_chunk( $doc_cols, 3 ) as $row ) {
        $sections[] = ecm_el_section( $row, [ 'gap' => 'extended', 'structure' => '30' ] );
    }

    // CTA
    $wa = get_theme_mod( 'ecm_whatsapp', '' );
    $cta = [
        ecm_el_widget( 'ecm_eyebrow', [ 'text' => 'NEED HELP?', 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center' ] ),
        ecm_el_widget( 'heading', [ 'title' => 'محتاج مساعدة؟', 'header_size' => 'h2', 'align' => 'center' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">لو ما لقيتش اللي بتدور عليه، تواصل مع فريق الدعم الفني</p>' ] ),
    ];
    if ( $wa ) $cta[] = ecm_el_button( '💬 واتساب الدعم', 'https://wa.me/' . $wa );
    $cta[] = ecm_el_button( '← الرئيسية', home_url( '/' ), 'ecm-btn-ghost' );
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, $cta ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '80', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ] ]
    );

    return $sections;
}


// ════════════════════════════════════════════════════════════
// §2d  محتوى صفحة السوفت وير
// ════════════════════════════════════════════════════════════
function ecm_build_software_elements(): array {
    $m = function ( $k, $d = '' ) { return get_theme_mod( $k, $d ); };
    $sections = [];

    $sw_version  = $m( 'ecm_sw_version', '2.0.0' );
    $sw_prev_ver = $m( 'ecm_sw_prev_version', '1.9.5' );

    // Hero
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'ecm_eyebrow', [ 'text' => '⚙️ FIRMWARE & SOFTWARE', 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center' ] ),
            ecm_el_widget( 'heading', [ 'title' => $m( 'ecm_sw_title', 'السوفت وير والتحديثات' ), 'header_size' => 'h1', 'align' => 'center' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">' . esc_html( $m( 'ecm_sw_desc', 'حمّل آخر إصدار من السوفت وير — تحديثات الأداء وإصلاح المشاكل' ) ) . '</p>' ] ),
            ecm_el_widget( 'heading', [ 'title' => 'LATEST VERSION — v' . $sw_version . ' — STABLE', 'header_size' => 'h4', 'align' => 'center' ] ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '120', 'right' => '20', 'bottom' => '60', 'left' => '20', 'isLinked' => false ] ]
    );

    // Download cards (latest + previous)
    $sw_link      = $m( 'ecm_sw_download_link', '' );
    $sw_prev_link = $m( 'ecm_sw_prev_link', '' );

    $changelog = [];
    for ( $i = 1; $i <= 6; $i++ ) {
        $log = $m( "ecm_sw_log_{$i}", '' );
        if ( $log !== '' ) $changelog[] = $log;
    }
    if ( empty( $changelog ) ) {
        $changelog = [
            'تحسين استجابة التحكم اللاسلكي — تأخير أقل من 8ms',
            'إصلاح مشكلة انقطاع البلوتوث عند المسافات البعيدة',
            'إضافة وضع التصوير البطيء Slow Motion 240fps',
            'تحسين عمر البطارية بنسبة 15%',
            'دعم كاميرات Sony A7S III و Canon R5 C',
        ];
    }

    $latest_col = [
        ecm_el_widget( 'heading', [ 'title' => '✅ ECM Firmware v' . $sw_version, 'header_size' => 'h2' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p>📅 ' . esc_html( $m( 'ecm_sw_date', '2024-06-01' ) ) . ' · 📦 ' . esc_html( $m( 'ecm_sw_size', '128 MB' ) ) . ' · 🟢 مستقر — Stable</p>' ] ),
    ];
    if ( $sw_link ) $latest_col[] = ecm_el_button( '⬇️ تحميل السوفت وير — Download', $sw_link );
    $latest_col[] = ecm_el_widget( 'heading', [ 'title' => '📋 ما الجديد — Changelog', 'header_size' => 'h3' ] );
    $latest_col[] = ecm_el_list( $changelog );

    $prev_col = [
        ecm_el_widget( 'heading', [ 'title' => '📦 ECM Firmware v' . $sw_prev_ver . ' — Previous', 'header_size' => 'h3' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p>لو الإصدار الجديد فيه مشكلة — حمّل الإصدار السابق المستقر.</p>' ] ),
    ];
    if ( $sw_prev_link ) $prev_col[] = ecm_el_button( '⬇️ تحميل v' . $sw_prev_ver, $sw_prev_link, 'ecm-btn-ghost' );
    $prev_col[] = ecm_el_widget( 'heading', [ 'title' => '🔄 طريقة التحديث — How to Update', 'header_size' => 'h4' ] );
    $prev_col[] = ecm_el_list( [
        'حمّل ملف السوفت وير على الكمبيوتر',
        'وصّل جهاز ECM بكابل USB',
        'انقل الملف لمجلد UPDATE على الجهاز',
        'افصل الكابل وأعد تشغيل الجهاز',
        'التحديث هيبدأ تلقائياً — لا تفصل الجهاز!',
    ], true );

    $sections[] = ecm_el_section(
        [ ecm_el_column( 50, $latest_col ), ecm_el_column( 50, $prev_col ) ],
        [ 'gap' => 'extended', 'structure' => '20' ]
    );

    // Troubleshooting
    $sections[] = ecm_el_header( '🛠 TROUBLESHOOTING', 'استكشاف الأخطاء وإصلاحها', 'لو واجهت أي مشكلة — هنا الحلول الشائعة' );
    $troubles = [
        [ 'الجهاز مش بيتعرف على الكاميرا', 'تأكد إن الكاميرا في وضع Remote Control. أعد تشغيل الجهازين. لو المشكلة مستمرة، حدّث السوفت وير لآخر إصدار.' ],
        [ 'السوفت وير وقع أثناء التشغيل', 'حمّل آخر إصدار من السوفت وير من هذه الصفحة. لو المشكلة تكررت، حمّل الإصدار السابق المستقر واتواصل مع الدعم الفني.' ],
        [ 'التحديث فشل أو توقف في النص', 'لا تفصل الجهاز! اضغط مع الاستمرار على زر Reset لمدة 10 ثواني. الجهاز هيرجع للإصدار السابق تلقائياً.' ],
        [ 'البلوتوث أو الواي فاي مش شغال بعد التحديث', 'روح إعدادات الجهاز > إعادة ضبط الشبكة. لو ما اشتغلش، حمّل الإصدار السابق من السوفت وير.' ],
    ];
    $faq_cols = [];
    foreach ( $troubles as $t ) {
        $faq_cols[] = ecm_el_column( 50, [
            ecm_el_widget( 'heading', [ 'title' => '❓ ' . $t[0], 'header_size' => 'h4' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p>' . esc_html( $t[1] ) . '</p>' ] ),
        ] );
    }
    foreach ( array_chunk( $faq_cols, 2 ) as $row ) {
        $sections[] = ecm_el_section( $row, [ 'gap' => 'extended', 'structure' => '20' ] );
    }

    // Emergency CTA
    $wa    = $m( 'ecm_whatsapp', '' );
    $email = $m( 'ecm_email', '' );
    $cta = [
        ecm_el_widget( 'ecm_eyebrow', [ 'text' => '🚨 EMERGENCY SUPPORT', 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center' ] ),
        ecm_el_widget( 'heading', [ 'title' => 'محتاج مساعدة فورية؟', 'header_size' => 'h2', 'align' => 'center' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">لو السوفت وير وقع أو الجهاز مش شغال — تواصل مع الدعم الفني فوراً</p>' ] ),
    ];
    if ( $wa )    $cta[] = ecm_el_button( '💬 واتساب الدعم الفني', 'https://wa.me/' . $wa );
    if ( $email ) $cta[] = ecm_el_button( '✉️ إرسال تقرير', 'mailto:' . $email, 'ecm-btn-ghost' );
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, $cta ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '80', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ] ]
    );

    return $sections;
}


// ════════════════════════════════════════════════════════════
// §2e  محتوى صفحة رفع السوفت وير المباشر
// ════════════════════════════════════════════════════════════
function ecm_build_upload_elements(): array {
    $m = function ( $k, $d = '' ) { return get_theme_mod( $k, $d ); };
    $sections = [];

    $target_url = $m( 'ecm_upload_target_url', 'https://esp-web-tools.github.io/' );

    // Hero + CTA card
    $hero = [
        ecm_el_widget( 'heading', [ 'title' => $m( 'ecm_upload_page_title', 'أداة رفع وتحديث السوفت وير المباشر' ), 'header_size' => 'h1', 'align' => 'center' ] ),
        ecm_el_widget( 'heading', [ 'title' => $m( 'ecm_upload_page_title_en', 'Direct Firmware Flasher & Uploader' ), 'header_size' => 'h4', 'align' => 'center' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">' . esc_html( $m( 'ecm_upload_page_desc', 'لتحديث السوفت وير أو رفعه مباشرة إلى جهازك، يرجى الانتقال إلى الموقع المخصص للرفع.' ) ) . '</p>'
            . '<p style="text-align:center;opacity:.8;">' . esc_html( $m( 'ecm_upload_page_desc_en', 'To flash or upload the software directly to your device, please proceed to the dedicated web utility.' ) ) . '</p>' ] ),
    ];
    if ( $target_url ) {
        $hero[] = ecm_el_button( $m( 'ecm_upload_btn_text', 'انتقل إلى موقع الرفع والتحديث 🚀' ), $target_url );
    }
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, $hero ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '120', 'right' => '20', 'bottom' => '40', 'left' => '20', 'isLinked' => false ] ]
    );

    // Steps (AR + EN)
    $steps_ar = [
        'وصّل جهاز ECM بالكمبيوتر الخاص بك باستخدام كابل USB ملائم.',
        'تأكد من إغلاق أي برامج تحكم أخرى تستخدم نفس المنفذ.',
        'انقر فوق زر الانتقال إلى موقع الرفع الموضح أدناه.',
        'اختر منفذ الاتصال (COM Port) المناسب لجهازك وابدأ الرفع مباشرة.',
    ];
    $steps_en = [
        'Connect your ECM device to your computer using a suitable USB cable.',
        'Make sure to close any other control software using the same serial port.',
        'Click the "Go to Web Flasher Website" button displayed below.',
        'Select the correct connection port (COM Port) and start uploading immediately.',
    ];
    $sections[] = ecm_el_section(
        [
            ecm_el_column( 50, [
                ecm_el_widget( 'heading', [ 'title' => 'خطوات الرفع والتحديث:', 'header_size' => 'h3' ] ),
                ecm_el_list( $steps_ar, true ),
            ] ),
            ecm_el_column( 50, [
                ecm_el_widget( 'heading', [ 'title' => 'How to upload:', 'header_size' => 'h3' ] ),
                ecm_el_list( $steps_en, true ),
            ] ),
        ],
        [ 'gap' => 'extended', 'structure' => '20' ]
    );

    // Support
    $wa = $m( 'ecm_whatsapp', '' );
    $sup = [
        ecm_el_widget( 'ecm_eyebrow', [ 'text' => 'SUPPORT & TROUBLESHOOTING', 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center' ] ),
        ecm_el_widget( 'heading', [ 'title' => 'هل تواجه مشكلة في الرفع؟ — Having Trouble?', 'header_size' => 'h2', 'align' => 'center' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">في حال لم يتعرف المتصفح على الجهاز، تأكد من تثبيت تعريفات الـ USB (CH340 / CP210x Drivers) وجرب كابل بيانات آخر.</p>' ] ),
    ];
    if ( $wa ) $sup[] = ecm_el_button( '💬 واتساب الدعم', 'https://wa.me/' . $wa );
    $sections[] = ecm_el_section(
        [ ecm_el_column( 100, $sup ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '40', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ] ]
    );

    return $sections;
}


// ════════════════════════════════════════════════════════════
// §3  كتابة البيانات في الصفحة
// ════════════════════════════════════════════════════════════

/**
 * يزرع بيانات Elementor في صفحة — بدون الكتابة فوق صفحة مبنية مسبقًا.
 */
function ecm_seed_elementor_page( int $page_id, array $elements, bool $force = false ): bool {
    if ( ! $page_id ) return false;

    // حماية: لو الصفحة فيها بيانات Elementor فعلاً — لا تلمسها (إلا مع force)
    if ( ! $force ) {
        $existing = get_post_meta( $page_id, '_elementor_data', true );
        if ( ! empty( $existing ) && $existing !== '[]' ) {
            return false;
        }
    }

    update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
    update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
    if ( defined( 'ELEMENTOR_VERSION' ) ) {
        update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
    }
    // Elementor يخزّن الـ JSON مع slashes
    update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );

    return true;
}

/** يبحث عن صفحة بقالبها (page template) */
function ecm_find_page_by_template( string $tpl ): int {
    $ids = get_posts( [
        'post_type'   => 'page',
        'post_status' => 'any',
        'numberposts' => 1,
        'fields'      => 'ids',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => $tpl,
    ] );
    return $ids ? (int) $ids[0] : 0;
}

/**
 * يزرع كل الصفحات. يعيد عدد الصفحات اللي اتزرعت فعلاً.
 *
 * @param bool $force لو true بيكتب فوق التصميم الموجود (لإعادة البناء يدويًا).
 */
function ecm_seed_all_elementor_pages( bool $force = false ): int {
    if ( ! current_user_can( 'edit_pages' ) ) return 0;
    if ( ! did_action( 'elementor/loaded' ) ) return 0;          // Elementor لازم يكون شغّال

    $count = 0;

    // الصفحة الرئيسية
    $front_id = (int) get_option( 'page_on_front' );
    if ( $front_id && ecm_seed_elementor_page( $front_id, ecm_build_frontpage_elements(), $force ) ) {
        $count++;
    }

    // باقي الصفحات (لقطة snapshot من البيانات الحالية)
    $page_map = [
        'page-download-app.php'    => 'ecm_build_app_elements',
        'page-documentation.php'   => 'ecm_build_docs_elements',
        'page-software.php'        => 'ecm_build_software_elements',
        'page-upload-software.php' => 'ecm_build_upload_elements',
    ];
    foreach ( $page_map as $tpl => $builder ) {
        $pid = ecm_find_page_by_template( $tpl );
        if ( $pid && function_exists( $builder ) && ecm_seed_elementor_page( $pid, call_user_func( $builder ), $force ) ) {
            $count++;
        }
    }

    // امسح كاش Elementor عشان الصفحات تتولّد من جديد
    if ( class_exists( '\Elementor\Plugin' ) ) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }

    return $count;
}

/** التشغيل التلقائي — مرة واحدة عند التفعيل */
function ecm_maybe_seed_elementor_pages(): void {
    if ( get_option( 'ecm_elementor_seeded_v1' ) ) return;       // مرة واحدة فقط
    if ( ! did_action( 'elementor/loaded' ) ) return;
    ecm_seed_all_elementor_pages( false );
    update_option( 'ecm_elementor_seeded_v1', true );
}
add_action( 'admin_init', 'ecm_maybe_seed_elementor_pages', 20 );

/** إعادة الزرع عند تفعيل الثيم من جديد */
function ecm_reset_elementor_seed() {
    delete_option( 'ecm_elementor_seeded_v1' );
}
add_action( 'after_switch_theme', 'ecm_reset_elementor_seed' );


/**
 * معالج زر "إعادة بناء صفحات Elementor" من لوحة التحكم (force).
 */
function ecm_handle_reseed() {
    if ( empty( $_POST['ecm_reseed'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;
    check_admin_referer( 'ecm_reseed_nonce' );

    $count = ecm_seed_all_elementor_pages( true );
    set_transient( 'ecm_reseed_count', $count, 60 );
    wp_safe_redirect( admin_url( 'admin.php?page=ecm-dashboard&ecm_reseeded=1' ) );
    exit;
}
add_action( 'admin_init', 'ecm_handle_reseed', 5 );


/**
 * حالة صفحات Elementor — للتشخيص في لوحة التحكم.
 * يعيد صفوف: [ الاسم, ID, مبني؟, حجم البيانات بالبايت, رابط تعديل Elementor ]
 */
function ecm_elementor_pages_status(): array {
    $pages = [
        'الصفحة الرئيسية' => (int) get_option( 'page_on_front' ),
        'تحميل التطبيق'   => ecm_find_page_by_template( 'page-download-app.php' ),
        'التوثيق'         => ecm_find_page_by_template( 'page-documentation.php' ),
        'السوفت وير'      => ecm_find_page_by_template( 'page-software.php' ),
        'رفع السوفت وير'  => ecm_find_page_by_template( 'page-upload-software.php' ),
        'التحكم في الكاميرا' => ecm_find_page_by_template( 'page-camera-control.php' ),
        'التحكم في المركبة'  => ecm_find_page_by_template( 'page-vehicle-control.php' ),
        'لوحة المفاتيح'      => ecm_find_page_by_template( 'page-relay-panel.php' ),
        'التحكم بذراع الألعاب' => ecm_find_page_by_template( 'page-gamepad.php' ),
    ];

    $out = [];
    foreach ( $pages as $label => $id ) {
        if ( ! $id ) {
            $out[] = [ $label, 0, false, 0, '' ];
            continue;
        }
        $data  = (string) get_post_meta( $id, '_elementor_data', true );
        $mode  = get_post_meta( $id, '_elementor_edit_mode', true );
        $built = ( 'builder' === $mode && '' !== $data && '[]' !== $data );
        $edit  = admin_url( 'post.php?post=' . $id . '&action=elementor' );
        $out[] = [ $label, $id, $built, strlen( $data ), $edit ];
    }
    return $out;
}


// ════════════════════════════════════════════════════════════
// §4  CSS مساعد للبلوكات المزروعة (توسيط Eyebrow وغيره)
// ════════════════════════════════════════════════════════════
function ecm_seed_helper_css() {
    echo "<style id=\"ecm-seed-css\">\n";
    echo ".ecm-el-center{ text-align:center; }\n";
    echo ".ecm-el-center .ecm-eyebrow{ display:inline-flex; justify-content:center; }\n";
    echo "</style>\n";
}
add_action( 'wp_head', 'ecm_seed_helper_css' );
