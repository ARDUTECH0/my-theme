<?php
/**
 * ECM — Elementor Starter Templates
 *
 * بيحقن قوالب صفحات جاهزة بستايل ECM في مكتبة Elementor (My Templates).
 * فلما تعمل صفحة جديدة وتفتحها بـ Elementor → Add Template → My Templates،
 * تلاقي القوالب دي جاهزة، تدخلها وتملا بياناتك بس.
 *
 * بيعتمد على الـ helpers المعرّفة في elementor-seed.php (ecm_el_*).
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════
// §1  مولّد قسم نصّي (عنوان فرعي + فقرة) — مكرّر في الصفحات القانونية
// ════════════════════════════════════════════════════════════
function ecm_starter_text_section( array $items ): array {
    $widgets = [];
    foreach ( $items as $it ) {
        $widgets[] = ecm_el_widget( 'heading', [
            'title'       => $it[0],
            'header_size' => 'h3',
        ] );
        $widgets[] = ecm_el_widget( 'text-editor', [
            'editor' => '<p>' . esc_html( $it[1] ) . '</p>',
        ] );
    }
    return ecm_el_section(
        [ ecm_el_column( 100, $widgets ) ],
        [ 'padding' => [ 'unit' => 'px', 'top' => '20', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ] ]
    );
}


// ════════════════════════════════════════════════════════════
// §2  القوالب
// ════════════════════════════════════════════════════════════

/** سياسة الخصوصية */
function ecm_starter_privacy(): array {
    return [
        ecm_el_header( 'PRIVACY · سياسة الخصوصية', 'سياسة الخصوصية', 'آخر تحديث: [اكتب التاريخ]. بنوضّح هنا إزاي بنتعامل مع بياناتك ونحميها.' ),
        ecm_starter_text_section( [
            [ 'مقدمة', 'احنا في ECM بنحترم خصوصيتك. السياسة دي بتوضّح أنواع البيانات اللي بنجمعها وإزاي بنستخدمها. عدّل النص ده ببياناتك.' ],
            [ 'البيانات اللي بنجمعها', 'بنجمع بيانات زي الاسم والإيميل لما تتواصل معانا أو تستخدم خدماتنا. اكتب هنا تفاصيل البيانات اللي بتجمعها.' ],
            [ 'استخدام البيانات', 'بنستخدم بياناتك لتحسين الخدمة، الرد على استفساراتك، وإرسال التحديثات. عدّل حسب احتياجك.' ],
            [ 'ملفات الكوكيز (Cookies)', 'الموقع بيستخدم كوكيز لتحسين تجربتك. المستخدم يقدر يتحكم فيها من إعدادات المتصفح.' ],
            [ 'حماية البيانات', 'بنطبّق إجراءات أمان مناسبة لحماية بياناتك من الوصول غير المصرّح به.' ],
            [ 'حقوقك', 'من حقك تطلب الوصول لبياناتك أو تعديلها أو حذفها في أي وقت.' ],
            [ 'التواصل', 'لأي استفسار بخصوص الخصوصية، تواصل معانا على: [الإيميل].' ],
        ] ),
    ];
}

/** الشروط والأحكام */
function ecm_starter_terms(): array {
    return [
        ecm_el_header( 'TERMS · الشروط والأحكام', 'الشروط والأحكام', 'باستخدامك للموقع والخدمات فإنك توافق على الشروط التالية.' ),
        ecm_starter_text_section( [
            [ 'قبول الشروط', 'باستخدامك لموقع ECM فإنك توافق على الالتزام بالشروط والأحكام دي. لو مش موافق، برجاء عدم استخدام الموقع.' ],
            [ 'استخدام الخدمة', 'بتلتزم باستخدام الخدمة للأغراض المشروعة فقط، ومتعملش أي حاجة تضر بالموقع أو المستخدمين.' ],
            [ 'الملكية الفكرية', 'كل المحتوى والتصاميم والعلامات التجارية ملك لـ ECM ولا يجوز استخدامها بدون إذن.' ],
            [ 'حدود المسؤولية', 'الخدمة مقدّمة "كما هي". عدّل النص ده حسب سياستك القانونية.' ],
            [ 'التعديلات', 'بنحتفظ بحق تعديل الشروط دي في أي وقت، والتعديلات بتسري فور نشرها.' ],
            [ 'التواصل', 'لأي استفسار عن الشروط، تواصل معانا على: [الإيميل].' ],
        ] ),
    ];
}

/** من نحن */
function ecm_starter_about(): array {
    $intro = ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'text-editor', [
                'editor' => '<p style="text-align:center;">ECM نظام تحكم احترافي بحركة الكاميرا، مصمم لصنّاع المحتوى والمصورين المحترفين. اكتب هنا قصة المنتج ورؤيتك.</p>',
            ] ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '10', 'right' => '20', 'bottom' => '40', 'left' => '20', 'isLinked' => false ] ]
    );

    $stats = [
        ecm_el_widget( 'ecm_stat_box', [ 'number' => '8',    'suffix' => '',  'label' => 'قنوات تحكم' ] ),
        ecm_el_widget( 'ecm_stat_box', [ 'number' => '<50',  'suffix' => 'ms','label' => 'زمن الاستجابة' ] ),
        ecm_el_widget( 'ecm_stat_box', [ 'number' => '100',  'suffix' => '%', 'label' => 'تحكم لاسلكي' ] ),
    ];

    $sections   = [ ecm_el_header( 'ABOUT · من نحن', 'من نحن', '' ), $intro ];
    $sections   = array_merge( $sections, ecm_el_grid( $stats, 3 ) );
    $sections[] = ecm_starter_text_section( [
        [ 'رؤيتنا', 'اكتب هنا رؤية الشركة وأهدافها وليه ECM مختلف.' ],
        [ 'فريقنا', 'عرّف بفريق العمل والخبرة اللي وراء المنتج.' ],
    ] );
    return $sections;
}

/** اتصل بنا */
function ecm_starter_contact(): array {
    $info = ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'text-editor', [
                'editor' => '<p style="text-align:center;">عايز تتواصل معانا؟ استخدم أي وسيلة من دي — هنرد عليك في أقرب وقت.</p>',
            ] ),
            ecm_el_button( '💬 واتساب', '#', 'ecm-btn-primary' ),
            ecm_el_button( '✉️ إيميل', 'mailto:[الإيميل]', 'ecm-btn-ghost' ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '10', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ] ]
    );
    return [
        ecm_el_header( 'CONTACT · اتصل بنا', 'اتصل بنا', 'احنا هنا نساعدك.' ),
        $info,
    ];
}

/** صفحة احترافية فاضية — نقطة بداية */
function ecm_starter_blank(): array {
    return [
        ecm_el_header( 'SECTION · قسم', 'عنوان الصفحة', 'وصف مختصر للصفحة — عدّله بالنص بتاعك.' ),
        ecm_el_section(
            [ ecm_el_column( 100, [
                ecm_el_widget( 'heading', [ 'title' => 'عنوان فرعي', 'header_size' => 'h3' ] ),
                ecm_el_widget( 'text-editor', [ 'editor' => '<p>اكتب محتوى الصفحة هنا. تقدر تسحب أي ودجِت من ECM Elements وتضيفه.</p>' ] ),
                ecm_el_button( 'زر إجراء', '#', 'ecm-btn-primary' ),
            ] ) ],
            [ 'padding' => [ 'unit' => 'px', 'top' => '20', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ] ]
        ),
    ];
}


/** صورة placeholder من Elementor (أو افتراضي) */
function ecm_starter_placeholder_img(): string {
    if ( class_exists( '\Elementor\Utils' ) && method_exists( '\Elementor\Utils', 'get_placeholder_image_src' ) ) {
        return \Elementor\Utils::get_placeholder_image_src();
    }
    return get_template_directory_uri() . '/assets/img/logo.png';
}

/** ودجِت صورة بستايل ECM (مع كلاس hover) */
function ecm_starter_image_widget( string $src, string $link = '', bool $hover = true ): array {
    $settings = [
        'image'        => [ 'url' => $src, 'id' => '' ],
        'image_size'   => 'large',
        '_css_classes' => $hover ? 'ecm-img-card ecm-img-hover' : 'ecm-img-card',
    ];
    if ( $link !== '' ) {
        $settings['link_to'] = 'custom';
        $settings['link']    = [ 'url' => $link, 'is_external' => '', 'nofollow' => '' ];
    }
    return ecm_el_widget( 'image', $settings );
}

/** صف «صورة + شرح» (يتعكس الترتيب لو reversed) */
function ecm_starter_media_row( string $img, string $title, string $text, bool $reversed = false ): array {
    $img_col  = ecm_el_column( 50, [ ecm_starter_image_widget( $img, '', true ) ] );
    $text_col = ecm_el_column( 50, [
        ecm_el_widget( 'heading', [ 'title' => $title, 'header_size' => 'h3' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p>' . esc_html( $text ) . '</p>' ] ),
    ], [ 'content_position' => 'center' ] );

    $cols = $reversed ? [ $text_col, $img_col ] : [ $img_col, $text_col ];
    return ecm_el_section( $cols, [
        'gap'              => 'extended',
        'structure'        => '20',
        'content_position' => 'middle',
        'padding'          => [ 'unit' => 'px', 'top' => '24', 'right' => '20', 'bottom' => '24', 'left' => '20', 'isLinked' => false ],
    ] );
}

/** قالب: صور + شرح (Media + Text) */
function ecm_starter_media_text(): array {
    $ph = ecm_starter_placeholder_img();
    return [
        ecm_el_header( 'GUIDE · شرح بالصور', 'اشرح فكرتك بالصور', 'حُط صورة جنب كل نقطة واكتب شرحها — مثالي للمميزات أو خطوات الاستخدام.' ),
        ecm_starter_media_row( $ph, 'العنوان الأول', 'اكتب شرح النقطة هنا. غيّر الصورة من أيقونة الصورة، والنص من هنا.', false ),
        ecm_starter_media_row( $ph, 'العنوان الثاني', 'الصورة على الجنب التاني — الترتيب بيتبادل تلقائيًا للشكل الاحترافي.', true ),
        ecm_starter_media_row( $ph, 'العنوان الثالث', 'كرّر الصفوف على قد ما تحتاج — انسخ أي صف من الزر اليمين في Elementor.', false ),
    ];
}

/** قالب: معرض صور بتأثير hover (Gallery) */
function ecm_starter_gallery(): array {
    $ph   = ecm_starter_placeholder_img();
    $imgs = [];
    for ( $i = 0; $i < 6; $i++ ) {
        $imgs[] = ecm_starter_image_widget( $ph, '#', true );
    }
    $sections   = [ ecm_el_header( 'GALLERY · معرض', 'معرض الصور', 'اسحب صورك مكان الصور دي — كل صورة عليها تأثير hover ورابط تقدر تغيّره.' ) ];
    $sections   = array_merge( $sections, ecm_el_grid( $imgs, 3 ) );
    return $sections;
}

/** ودجِت موديل 3D جاهز للقالب */
function ecm_starter_3d_widget(): array {
    return ecm_el_widget( 'ecm_logo_3d', [
        'glb_url'         => '',
        'auto_rotate'     => 'yes',
        'rotate_speed'    => [ 'size' => 30, 'unit' => 'px' ],
        'camera_controls' => 'yes',
        'zoom'            => 'yes',
        'fullscreen'      => 'yes',
        'effect'          => 'none',
        'studio_light'    => 'yes',
        'exposure'        => [ 'size' => 1.1, 'unit' => 'px' ],
        'shadow'          => [ 'size' => 0.5, 'unit' => 'px' ],
        'shadow_softness' => [ 'size' => 1, 'unit' => 'px' ],
        'tone_mapping'    => 'neutral',
        'stage_style'     => 'glow',
        'height'          => [ 'size' => 380, 'unit' => 'px' ],
        'width'           => [ 'size' => 100, 'unit' => '%' ],
        'frame_zoom'      => [ 'size' => 95, 'unit' => 'px' ],
        'align'           => 'center',
    ] );
}

/** قالب: عرض منتج/قطعة 3D (موديل + تفاصيل) */
function ecm_starter_3d_showcase(): array {
    $model_col = ecm_el_column( 50, [ ecm_starter_3d_widget() ] );

    $details_col = ecm_el_column( 50, [
        ecm_el_widget( 'ecm_eyebrow', [ 'text' => '3D PREVIEW · معاينة', 'show_dot' => 'yes', 'html_tag' => 'span' ] ),
        ecm_el_widget( 'heading', [ 'title' => 'اسم القطعة / المنتج', 'header_size' => 'h2' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p>اكتب وصف القطعة هنا — المميزات، الخامة، أي تفاصيل تحب تعرضها للعميل.</p>' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'الخامة', 'val' => 'ألومنيوم', 'highlight' => '' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'الوزن', 'val' => '-- جم', 'highlight' => '' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'المقاس', 'val' => '-- مم', 'highlight' => 'yes' ] ),
        ecm_el_button( 'اطلب الآن', '#' ),
    ], [ 'content_position' => 'center' ] );

    $section = ecm_el_section(
        [ $model_col, $details_col ],
        [
            'gap'              => 'extended',
            'structure'        => '20',
            'content_position' => 'middle',
            'padding'          => [ 'unit' => 'px', 'top' => '40', 'right' => '20', 'bottom' => '60', 'left' => '20', 'isLinked' => false ],
        ]
    );

    return [
        ecm_el_header( 'SHOWCASE · عرض ثلاثي الأبعاد', 'اعرض قطعتك بـ 3D', 'حُط ملف الـ .glb في ودجِت الموديل، واكتب تفاصيل القطعة جنبه.' ),
        $section,
    ];
}


// ════════════════════════════════════════════════════════════
// §2.5  قالب: شرح لوحة التحكم (Control Panel) — سلايدر 3D + شرح أزرار
// ════════════════════════════════════════════════════════════

/** صفّ repeater مع _id فريد (مطلوب لعناصر الـ repeater في Elementor) */
function ecm_starter_rep_row( array $row ): array {
    $row['_id'] = ecm_el_id();
    return $row;
}

/** سلايدر 3D للقالب — 3 شرائح تشرح فكرة التحكم */
function ecm_starter_control_slider(): array {
    $slides = [
        ecm_starter_rep_row( [
            'glb_url'  => '',
            'eyebrow'  => 'CRANE',
            'title'    => 'تحكم احترافي بحركة الكاميرا',
            'subtitle' => 'حُط ملف الكاميرا (.glb) هنا — اسحب باللف، عجلة الماوس للزوم.',
            'btn_text' => '',
            'btn_link' => [ 'url' => '', 'is_external' => '', 'nofollow' => '' ],
        ] ),
        ecm_starter_rep_row( [
            'glb_url'  => '',
            'eyebrow'  => 'SPEED · ACC',
            'title'    => 'سرعة وتسارع تحت أمرك',
            'subtitle' => 'اضبط سرعة الحركة (Speed) ونعومتها (Acceleration) بدقّة من التطبيق.',
            'btn_text' => '',
            'btn_link' => [ 'url' => '', 'is_external' => '', 'nofollow' => '' ],
        ] ),
        ecm_starter_rep_row( [
            'glb_url'  => '',
            'eyebrow'  => 'POSITION',
            'title'    => 'الموقع الحالي والهدف',
            'subtitle' => 'تابع الموقع الحالي لحظيًا، وحدّد الموقع الهدف بضغطة زر.',
            'btn_text' => '',
            'btn_link' => [ 'url' => '', 'is_external' => '', 'nofollow' => '' ],
        ] ),
    ];

    return ecm_el_widget( 'ecm_slider_3d', [
        'slides'         => $slides,
        'height'         => [ 'size' => 480, 'unit' => 'px' ],
        'autoplay'       => 'yes',
        'autoplay_delay' => 6,
        'arrows'         => 'yes',
        'dots'           => 'yes',
        'full_width'     => '',
        'auto_rotate'    => '',
        'play_animation' => 'yes',
        'zoom'           => 'yes',
        'zoom_buttons'   => '',
        'model_size'     => [ 'size' => 90, 'unit' => 'px' ],
        'media_side'     => 'start',
        'text_box'       => 'yes',
    ] );
}

/** شبكة شرح أزرار لوحة التحكم */
function ecm_starter_control_gridw(): array {
    $cells = [
        ecm_starter_rep_row( [
            'media_type' => '3d', 'glb_url' => '', 'arrow' => 'up-down',
            'title' => 'السرعة (Speed)',
            'desc'  => 'بتحدد سرعة حركة الكاميرا. زوّدها أو قلّلها بأزرار + و −. القيمة بتظهر فوق مباشرة (مثال: 45).',
            'btn_text' => '', 'btn_link' => [ 'url' => '' ],
        ] ),
        ecm_starter_rep_row( [
            'media_type' => '3d', 'glb_url' => '', 'arrow' => 'right',
            'title' => 'التسارع (Acc)',
            'desc'  => 'قيمة التسارع — بتتحكم في نعومة بداية ونهاية الحركة. اسحب المؤشّر لضبط الدرجة.',
            'btn_text' => '', 'btn_link' => [ 'url' => '' ],
        ] ),
        ecm_starter_rep_row( [
            'media_type' => '3d', 'glb_url' => '', 'arrow' => 'down',
            'title' => 'الموقع الحالي (Current)',
            'desc'  => 'بيعرض موقع الكاميرا الحالي لحظيًا أثناء الحركة (مثال: 16).',
            'btn_text' => '', 'btn_link' => [ 'url' => '' ],
        ] ),
        ecm_starter_rep_row( [
            'media_type' => '3d', 'glb_url' => '', 'arrow' => 'up',
            'title' => 'الموقع الهدف (Target)',
            'desc'  => 'الموقع اللي الكاميرا هتروح له. حدّده بالرقم وبأزرار + و − (مثال: 903).',
            'btn_text' => '', 'btn_link' => [ 'url' => '' ],
        ] ),
        ecm_starter_rep_row( [
            'media_type' => '3d', 'glb_url' => '', 'arrow' => 'left-right',
            'title' => 'أزرار الزيادة والنقصان',
            'desc'  => 'كل قيمة ليها زرّين: + لزيادتها و − لتقليلها بخطوة محددة — للضبط الدقيق.',
            'btn_text' => '', 'btn_link' => [ 'url' => '' ],
        ] ),
        ecm_starter_rep_row( [
            'media_type' => '3d', 'glb_url' => '', 'arrow' => 'rotate-cw',
            'title' => 'تنفيذ الحركة',
            'desc'  => 'بعد ضبط القيم، التطبيق بيبعت الأمر للموتور فيتحرك للهدف بالسرعة والتسارع المحددين.',
            'btn_text' => '', 'btn_link' => [ 'url' => '' ],
        ] ),
    ];

    return ecm_el_widget( 'ecm_control_grid', [
        'shared_glb'  => '',
        'cells'       => $cells,
        'columns'     => '3',
        'cell_height' => [ 'size' => 220, 'unit' => 'px' ],
        'gap'         => [ 'size' => 22, 'unit' => 'px' ],
        'auto_rotate' => '',
        'model_fill'  => [ 'size' => 85, 'unit' => 'px' ],
        'card_style'  => 'card',
        'arrow_color' => '#ff4d2e',
    ] );
}

/** قسم المواصفات الفنية (Technical) */
function ecm_starter_control_tech(): array {
    $col = ecm_el_column( 100, [
        ecm_el_widget( 'ecm_eyebrow', [ 'text' => 'TECHNICAL · مواصفات فنية', 'show_dot' => 'yes', 'html_tag' => 'span' ] ),
        ecm_el_widget( 'heading', [ 'title' => 'تفاصيل تقنية', 'header_size' => 'h2' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'بروتوكول الاتصال', 'val' => 'Wi-Fi / ESP-NOW', 'highlight' => '' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'زمن الاستجابة', 'val' => '< 50 مللي ثانية', 'highlight' => 'yes' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'نطاق السرعة', 'val' => '0 – 100', 'highlight' => '' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'دقة الموقع', 'val' => '± 1 خطوة', 'highlight' => '' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'نوع التحكم', 'val' => 'موقع مطلق (Absolute) + سرعة', 'highlight' => '' ] ),
    ] );
    return ecm_el_section( [ $col ], [
        'padding' => [ 'unit' => 'px', 'top' => '20', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ],
    ] );
}

/** قسم: لوحة CRANE الحيّة + شرحها (2 عمود) */
function ecm_starter_control_card_section(): array {
    $card_col = ecm_el_column( 50, [
        ecm_el_widget( 'ecm_ctrl_card', [
            'icon'          => '🎥',
            'title'         => 'CRANE',
            'speed_label'   => 'Speed',
            'speed_val'     => 45,
            'bar_pct'       => [ 'size' => 60, 'unit' => 'px' ],
            'current_label' => 'Current',
            'current_val'   => 16,
            'target_label'  => 'Target',
            'target_val'    => 903,
        ] ),
    ] );

    $text_col = ecm_el_column( 50, [
        ecm_el_widget( 'ecm_eyebrow', [ 'text' => 'LIVE PANEL · اللوحة الحيّة', 'show_dot' => 'yes', 'html_tag' => 'span' ] ),
        ecm_el_widget( 'heading', [ 'title' => 'لوحة التحكم بالتفصيل', 'header_size' => 'h2' ] ),
        ecm_el_widget( 'text-editor', [ 'editor' => '<p>دي نفس اللوحة اللي بتشوفها في التطبيق. كل عنصر فيها ليه وظيفة محددة — من السرعة للتسارع للموقع الهدف. الأرقام بتتحدّث لحظيًا مع حركة الكاميرا.</p>' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'Speed', 'val' => 'سرعة الحركة (0–100)', 'highlight' => '' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'Acc', 'val' => 'نعومة التسارع', 'highlight' => '' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'Current', 'val' => 'الموقع الحالي لحظيًا', 'highlight' => '' ] ),
        ecm_el_widget( 'ecm_spec_row', [ 'label' => 'Target', 'val' => 'الموقع الهدف', 'highlight' => 'yes' ] ),
    ], [ 'content_position' => 'center' ] );

    return ecm_el_section(
        [ $card_col, $text_col ],
        [
            'gap'              => 'extended',
            'structure'        => '20',
            'content_position' => 'middle',
            'padding'          => [ 'unit' => 'px', 'top' => '30', 'right' => '20', 'bottom' => '40', 'left' => '20', 'isLinked' => false ],
        ]
    );
}

/** قسم فيديو شرح (عنوان + فيديو) */
function ecm_starter_video_section( string $eyebrow, string $heading, string $sub ): array {
    return ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'ecm_eyebrow', [ 'text' => $eyebrow, 'show_dot' => 'yes', 'html_tag' => 'span' ] ),
            ecm_el_widget( 'heading', [ 'title' => $heading, 'header_size' => 'h2' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">' . esc_html( $sub ) . '</p>' ] ),
            ecm_el_widget( 'video', [
                'video_type'         => 'youtube',
                'youtube_url'        => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
                'aspect_ratio'       => '169',
                'show_image_overlay' => '',
                'lightbox'           => 'yes',
                '_css_classes'       => 'ecm-video-pro',
            ] ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '20', 'right' => '20', 'bottom' => '40', 'left' => '20', 'isLinked' => false ] ]
    );
}

/** قسم كروت المميزات (3 كروت) */
function ecm_starter_control_features(): array {
    $cards = [
        ecm_el_widget( 'ecm_feat_card', [ 'icon' => '📡', 'title' => 'تحكم لاسلكي', 'text' => 'اتصال Wi-Fi / ESP-NOW سريع ومستقر — تحكّم في الكاميرا من بعيد بدون أسلاك.' ] ),
        ecm_el_widget( 'ecm_feat_card', [ 'icon' => '🎯', 'title' => 'دقة عالية', 'text' => 'تحديد الموقع الهدف بدقة ± خطوة واحدة، مع تحكم كامل في السرعة والتسارع.' ] ),
        ecm_el_widget( 'ecm_feat_card', [ 'icon' => '⚡', 'title' => 'استجابة فورية', 'text' => 'زمن استجابة أقل من 50 مللي ثانية — الحركة بتتنفّذ لحظة ما تضغط.' ] ),
    ];
    $sections = [ ecm_el_header( 'FEATURES · المميزات', 'ليه نظام التحكم ده؟', 'مصمّم لصنّاع المحتوى المحترفين اللي محتاجين دقة وسلاسة.' ) ];
    return array_merge( $sections, ecm_el_grid( $cards, 3 ) );
}

/** قسم خطوات الاستخدام (4 خطوات) */
function ecm_starter_control_steps(): array {
    $steps = [
        ecm_el_widget( 'ecm_feat_card', [ 'icon' => '1', 'title' => 'افتح التطبيق', 'text' => 'شغّل تطبيق ECM وتأكد إن الجهاز متصل بنفس الشبكة.' ] ),
        ecm_el_widget( 'ecm_feat_card', [ 'icon' => '2', 'title' => 'اضبط السرعة والتسارع', 'text' => 'حدّد قيمة Speed وAcc المناسبة لنوع اللقطة اللي عايزها.' ] ),
        ecm_el_widget( 'ecm_feat_card', [ 'icon' => '3', 'title' => 'حدّد الموقع الهدف', 'text' => 'اكتب قيمة Target أو استخدم أزرار + و − للوصول للموقع المطلوب.' ] ),
        ecm_el_widget( 'ecm_feat_card', [ 'icon' => '4', 'title' => 'نفّذ الحركة', 'text' => 'اضغط تنفيذ — الكاميرا هتتحرك للهدف بسلاسة وحسب الإعدادات.' ] ),
    ];
    $sections = [ ecm_el_header( 'HOW TO · خطوات الاستخدام', 'استخدمه في 4 خطوات', 'من فتح التطبيق لتنفيذ الحركة — كله بسيط.' ) ];
    return array_merge( $sections, ecm_el_grid( $steps, 4 ) );
}

/** قسم CTA نهائي */
function ecm_starter_control_cta(): array {
    return ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'heading', [ 'title' => 'جاهز تبدأ التحكم؟', 'header_size' => 'h2' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">حمّل التطبيق وابدأ تتحكم في كاميرتك باحترافية.</p>' ] ),
            ecm_el_button( '⬇️ حمّل التطبيق', '#', 'ecm-btn-primary' ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '30', 'right' => '20', 'bottom' => '80', 'left' => '20', 'isLinked' => false ] ]
    );
}

/** القالب الكامل: شرح لوحة التحكم (احترافي ومفصّل) */
function ecm_starter_control_panel(): array {
    $slider_sec = ecm_el_section(
        [ ecm_el_column( 100, [ ecm_starter_control_slider() ] ) ],
        [ 'padding' => [ 'unit' => 'px', 'top' => '20', 'right' => '20', 'bottom' => '20', 'left' => '20', 'isLinked' => false ] ]
    );

    $intro = ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'heading', [ 'title' => 'إزاي تتحكم من التطبيق', 'header_size' => 'h2' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">لوحة CRANE بتدّيك تحكم كامل في حركة الكاميرا — السرعة، التسارع، والموقع. تحت كل عنصر شرح مبسّط لوظيفته.</p>' ] ),
        ] ) ],
        [ 'content_position' => 'middle', 'padding' => [ 'unit' => 'px', 'top' => '20', 'right' => '20', 'bottom' => '10', 'left' => '20', 'isLinked' => false ] ]
    );

    $grid_sec = ecm_el_section(
        [ ecm_el_column( 100, [ ecm_starter_control_gridw() ] ) ],
        [ 'padding' => [ 'unit' => 'px', 'top' => '10', 'right' => '20', 'bottom' => '40', 'left' => '20', 'isLinked' => false ] ]
    );

    $sections = [
        ecm_el_header( 'CONTROL PANEL · لوحة التحكم', 'شرح لوحة تحكم CRANE', 'سلايدر 3D · فيديوهات شرح · تفصيل كل زر ووظيفته في التطبيق.' ),
        $slider_sec,
        ecm_starter_control_card_section(),
        ecm_starter_video_section( 'WALKTHROUGH · شرح بالفيديو', 'جولة كاملة في لوحة التحكم', 'فيديو يشرح كل أزرار اللوحة خطوة بخطوة — غيّر الرابط لفيديوك.' ),
        $intro,
        $grid_sec,
    ];
    $sections   = array_merge( $sections, ecm_starter_control_features() );
    $sections   = array_merge( $sections, ecm_starter_control_steps() );
    $sections[] = ecm_starter_video_section( 'TIPS · نصائح متقدّمة', 'حركات احترافية بالكاميرا', 'أمثلة عملية لحركات سينمائية وإزاي تظبط إعداداتها — استبدله بفيديوك.' );
    $sections[] = ecm_starter_control_tech();
    $sections[] = ecm_starter_control_cta();

    return $sections;
}


// ════════════════════════════════════════════════════════════
// §3  إنشاء القوالب في مكتبة Elementor
// ════════════════════════════════════════════════════════════

/** يبحث عن قالب في المكتبة بالعنوان (من غير الدالة المهجورة get_page_by_title) */
function ecm_find_library_template( string $title ): int {
    $q = new WP_Query( [
        'post_type'      => 'elementor_library',
        'post_status'    => 'any',
        'title'          => $title,
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ] );
    return $q->have_posts() ? (int) $q->posts[0] : 0;
}

/** ينشئ قالب صفحة في مكتبة Elementor (لو مش موجود) */
function ecm_create_starter_template( string $title, array $elements ): int {
    $existing = ecm_find_library_template( $title );
    if ( $existing ) {
        return $existing;
    }

    $id = wp_insert_post( [
        'post_title'  => $title,
        'post_status' => 'publish',
        'post_type'   => 'elementor_library',
    ] );
    if ( ! $id || is_wp_error( $id ) ) {
        return 0;
    }

    update_post_meta( $id, '_elementor_edit_mode', 'builder' );
    update_post_meta( $id, '_elementor_template_type', 'page' );
    if ( defined( 'ELEMENTOR_VERSION' ) ) {
        update_post_meta( $id, '_elementor_version', ELEMENTOR_VERSION );
    }
    update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );

    // تصنيف القالب كـ "صفحة" عشان يظهر في Add Template
    wp_set_object_terms( $id, 'page', 'elementor_library_type' );

    return $id;
}

/** يمسح قالب موجود بالعنوان (عشان النسخة الجديدة تتعمل من جديد) */
function ecm_delete_library_template( string $title ): void {
    $id = ecm_find_library_template( $title );
    if ( $id ) {
        wp_delete_post( $id, true );
    }
}

/** التشغيل التلقائي — مرة واحدة */
function ecm_seed_starter_templates(): void {
    if ( get_option( 'ecm_starter_templates_v5' ) ) {
        return;
    }
    if ( ! did_action( 'elementor/loaded' ) ) {
        return; // Elementor لازم يكون شغّال (التاكسونومي بتتسجّل منه)
    }
    if ( ! current_user_can( 'edit_pages' ) ) {
        return;
    }

    ecm_create_starter_template( 'ECM — سياسة الخصوصية',        ecm_starter_privacy() );
    ecm_create_starter_template( 'ECM — الشروط والأحكام',       ecm_starter_terms() );
    ecm_create_starter_template( 'ECM — من نحن',                ecm_starter_about() );
    ecm_create_starter_template( 'ECM — اتصل بنا',              ecm_starter_contact() );
    ecm_create_starter_template( 'ECM — صفحة احترافية (بداية)', ecm_starter_blank() );
    ecm_create_starter_template( 'ECM — صور وشرح (Media + Text)', ecm_starter_media_text() );
    ecm_create_starter_template( 'ECM — معرض صور (Gallery)',      ecm_starter_gallery() );
    ecm_create_starter_template( 'ECM — عرض منتج 3D (Showcase)',  ecm_starter_3d_showcase() );

    // قالب لوحة التحكم — نمسح القديم (لو موجود) ونعمل النسخة الاحترافية المفصّلة
    ecm_delete_library_template( 'ECM — شرح لوحة التحكم (Control Panel)' );
    ecm_create_starter_template( 'ECM — شرح لوحة التحكم (Control Panel)', ecm_starter_control_panel() );

    update_option( 'ecm_starter_templates_v5', true );
}
add_action( 'admin_init', 'ecm_seed_starter_templates', 30 );

/** إعادة الإنشاء عند تفعيل الثيم من جديد */
function ecm_reset_starter_templates() {
    delete_option( 'ecm_starter_templates_v5' );
}
add_action( 'after_switch_theme', 'ecm_reset_starter_templates' );
