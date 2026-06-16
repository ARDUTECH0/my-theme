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

/** التشغيل التلقائي — مرة واحدة */
function ecm_seed_starter_templates(): void {
    if ( get_option( 'ecm_starter_templates_v3' ) ) {
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

    update_option( 'ecm_starter_templates_v3', true );
}
add_action( 'admin_init', 'ecm_seed_starter_templates', 30 );

/** إعادة الإنشاء عند تفعيل الثيم من جديد */
function ecm_reset_starter_templates() {
    delete_option( 'ecm_starter_templates_v3' );
}
add_action( 'after_switch_theme', 'ecm_reset_starter_templates' );
