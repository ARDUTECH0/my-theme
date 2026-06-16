<?php
/**
 * ECM — Elementor Seeder for System Pages
 *
 * بيحوّل صفحات الأنظمة (الكاميرا / المركبة / المفاتيح / الذراع) لبلوكات
 * Elementor حقيقية، فتتفتح بـ "Edit with Elementor" وتتعدّل بالسحب والإفلات.
 *
 * بيعتمد على helpers الـ seeder الأساسي (ecm_el_*, ecm_seed_elementor_page,
 * ecm_find_page_by_template) المعرّفة في elementor-seed.php.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;


// ════════════════════════════════════════════════════════════
// §1  أدوات بناء عامة
// ════════════════════════════════════════════════════════════

/** صورة placeholder من Elementor */
function ecm_sys_placeholder() {
    if ( class_exists( '\Elementor\Utils' ) && method_exists( '\Elementor\Utils', 'get_placeholder_image_src' ) ) {
        return \Elementor\Utils::get_placeholder_image_src();
    }
    return get_template_directory_uri() . '/assets/img/logo.png';
}

/** سيكشن أزرار (زر أساسي وسط) */
function ecm_sys_buttons() {
    return ecm_el_section(
        [ ecm_el_column( 100, [ ecm_el_button( 'تواصل معنا', home_url( '/#contact' ) ) ] ) ],
        [ 'content_position' => 'middle' ]
    );
}

/** سيكشن صورة غلاف */
function ecm_sys_image() {
    return ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'image', [ 'image' => [ 'url' => ecm_sys_placeholder(), 'id' => '' ], 'align' => 'center' ] ),
        ] ) ]
    );
}

/** ودجِت فيديو — بصورة غلاف وزر تشغيل (تحط رابط YouTube بعدين) */
function ecm_sys_video() {
    return ecm_el_widget( 'video', [
        'video_type'         => 'youtube',
        'youtube_url'        => '',
        'aspect_ratio'       => '169',
        'show_image_overlay' => 'yes',
        'image_overlay'      => [ 'url' => ecm_sys_placeholder(), 'id' => '' ],
        'lightbox'           => 'yes',
    ] );
}

/** سيكشن فيديوهات — عدد من ودجِتس الفيديو جنب بعض */
function ecm_sys_videos( int $count = 2 ) {
    $cols = [];
    $size = (int) floor( 100 / max( 1, $count ) );
    for ( $i = 0; $i < $count; $i++ ) {
        $cols[] = ecm_el_column( $size, [ ecm_sys_video() ] );
    }
    return ecm_el_section( $cols, [ 'gap' => 'extended', 'structure' => (string) ( $count * 10 ) ] );
}

/** سيكشن شرح لوحة التحكم — Hotspot: نقط فوق الصورة كل واحدة ليها شرحها */
function ecm_sys_control_guide() {
    // [ العنوان, الشرح, موضع أفقي %, موضع رأسي % ]
    $points = [
        [ 'تغيير السرعة', 'أزرار + و − لزيادة أو تقليل سرعة الموتور.', 28, 52 ],
        [ 'التسارع (Acc)', 'يتحكم في نعومة بداية ونهاية الحركة.', 60, 52 ],
        [ 'الموقع الحالي', 'مكان الموتور دلوقتي (Current).', 30, 70 ],
        [ 'الموقع الهدف', 'المكان اللي الموتور رايح له (Target).', 60, 70 ],
        [ 'زيادة / نقص الهدف', 'أزرار + و − لضبط الموضع المستهدف.', 68, 84 ],
    ];

    $hotspots = [];
    $n = 1;
    foreach ( $points as $p ) {
        $hotspots[] = [
            '_id'                    => ecm_el_id(),
            'hotspot_type'           => 'text',
            'hotspot_text'           => (string) $n,
            'tooltip_content'        => $p[0] . ' — ' . $p[1],
            'horizontal_orientation' => 'left',
            'offset_x'               => [ 'unit' => '%', 'size' => $p[2] ],
            'vertical_orientation'   => 'top',
            'offset_y'               => [ 'unit' => '%', 'size' => $p[3] ],
        ];
        $n++;
    }

    return ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'ecm_eyebrow', [ 'text' => 'GUIDE · شرح لوحة التحكم', 'show_dot' => 'yes', 'html_tag' => 'span', '_css_classes' => 'ecm-el-center' ] ),
            ecm_el_widget( 'heading', [ 'title' => 'إزاي تستخدم لوحة التحكم', 'header_size' => 'h2', 'align' => 'center' ] ),
            ecm_el_widget( 'text-editor', [ 'editor' => '<p style="text-align:center;">مرّر على النقط فوق الصورة لمعرفة وظيفة كل جزء.</p>' ] ),
            ecm_el_widget( 'hotspot', [
                'image'            => [ 'url' => ecm_sys_placeholder(), 'id' => '' ],
                'hotspots'         => $hotspots,
                'tooltip_trigger'  => 'hover',
                'tooltip_position' => 'top',
                '_css_classes'     => 'ecm-hotspot',
            ] ),
        ] ) ],
        [ 'content_position' => 'middle' ]
    );
}

/** سيكشن إحصائيات (ecm_stat_box) — يرجّع سيكشن واحد */
function ecm_sys_stats( array $stats ) {
    $cols = [];
    $size = (int) floor( 100 / max( 1, count( $stats ) ) );
    foreach ( $stats as $st ) {
        $cols[] = ecm_el_column( $size, [
            ecm_el_widget( 'ecm_stat_box', [
                'number' => $st[0],
                'suffix' => $st[1] ?? '',
                'label'  => $st[2] ?? '',
            ] ),
        ] );
    }
    return ecm_el_section( $cols, [ 'gap' => 'extended', 'structure' => (string) ( count( $stats ) * 10 ) ] );
}

/** شبكة كروت Flip Box (Pro) [أيقونة/مفتاح, عنوان, وصف] — يرجّع مصفوفة سيكشنز */
function ecm_sys_cards( array $cards, int $per_row = 3 ) {
    $sections = [];
    $size     = (int) floor( 100 / max( 1, $per_row ) );
    foreach ( array_chunk( $cards, $per_row ) as $chunk ) {
        $cols = [];
        foreach ( $chunk as $c ) {
            $cols[] = ecm_el_column( $size, [
                ecm_el_widget( 'flip-box', [
                    'graphic_element'         => 'none',
                    'title_text_a'            => trim( $c[0] . ' ' . $c[1] ),
                    'description_text_a'      => '',
                    'title_text_b'            => $c[1],
                    'description_text_b'      => $c[2],
                    'button_text'             => '',
                    '_css_classes'            => 'ecm-flip',
                    // الصورة خلفية كاملة للوش (cover)
                    'background_a_background'  => 'classic',
                    'background_a_image'      => [ 'url' => ecm_sys_placeholder(), 'id' => '' ],
                    'background_a_position'   => 'center center',
                    'background_a_repeat'     => 'no-repeat',
                    'background_a_size'       => 'cover',
                ] ),
            ] );
        }
        $sections[] = ecm_el_section( $cols, [ 'gap' => 'extended', 'structure' => (string) ( $per_row * 10 ) ] );
    }
    return $sections;
}

/** سيكشن CTA — ودجِت Call to Action (Pro) */
function ecm_sys_cta( string $title, string $text ) {
    return ecm_el_section(
        [ ecm_el_column( 100, [
            ecm_el_widget( 'call-to-action', [
                'skin'            => 'classic',
                'graphic_element' => 'none',
                'title'           => $title,
                'description'     => $text,
                'button'          => 'تواصل معنا',
                'link'            => [ 'url' => home_url( '/#contact' ), 'is_external' => '', 'nofollow' => '' ],
                'alignment'       => 'center',
            ] ),
        ] ) ]
    );
}


// ════════════════════════════════════════════════════════════
// §2  بناء كل صفحة
// ════════════════════════════════════════════════════════════

function ecm_build_camera_elements(): array {
    $axes = [
        [ '🔄', 'بان (Pan)', 'تدوير الكاميرا يمين وشمال بنعومة — لتتبّع الحركة واستعراض المشهد أفقيًا.' ],
        [ '↕️', 'تيلت (Tilt)', 'رفع وخفض زاوية الكاميرا لفوق ولتحت بثبات تام.' ],
        [ '🧭', 'الرأس (Head)', 'محور إضافي لدوران رأس الكاميرا — لحركات إبداعية.' ],
        [ '🎞️', 'الميل (Roll)', 'يميل الكاميرا للحصول على لقطات سينمائية.' ],
        [ '🔍', 'الزووم (Zoom)', 'تحكم دقيق في تقريب وتبعيد العدسة بسلاسة.' ],
        [ '🎯', 'الفوكس (Focus)', 'ضبط التركيز البؤري بدقة عالية لحظة بلحظة.' ],
        [ '🏗️', 'الونش (Crane)', 'يرفع ويخفض الكاميرا على الرافعة مع معايرة خاصة.' ],
        [ '↔️', 'السلايدر (Slider)', 'يحرّك الكاميرا على القضيب الانزلاقي للقطات Dolly.' ],
    ];
    $feats = [
        [ '🎚️', 'السرعة والنعومة', 'تظبط سرعة ونعومة كل محور حسب اللقطة.' ],
        [ '💾', 'أوضاع جاهزة', 'احفظ ٦ إعدادات وبدّل بينها في ثانية.' ],
        [ '🏠', 'رجوع للصفر', 'يرجّع كل المحاور لوضع البداية بضغطة.' ],
        [ '⏹️', 'إيقاف فوري', 'يوقّف كل الحركات في الحال للأمان.' ],
        [ '🛡️', 'حدود آمنة', 'حماية تمنع الكاميرا تتعدّى الحد المسموح.' ],
        [ '🎮', 'دعم ذراع التحكم', 'تحكم سلس واحترافي بذراع ألعاب.' ],
    ];
    $stats = [ [ '٨', '', 'محاور حركة' ], [ '<50', 'ms', 'زمن الاستجابة' ], [ '٦', '', 'أوضاع محفوظة' ], [ '100', '%', 'تحكم لاسلكي' ] ];
    $steps = [
        [ '1️⃣', 'وصّل', 'ركّب الكاميرا ووصّل المحاور — كل محور بيتعرّف تلقائيًا.' ],
        [ '2️⃣', 'تحكّم', 'حرّك أي محور واضبط السرعة والنعومة حسب اللقطة.' ],
        [ '3️⃣', 'احفظ وكرّر', 'خزّن أوضاعك المفضّلة وارجعلها بضغطة.' ],
    ];

    $s   = [ ecm_el_header( 'CAMERA CONTROL · التحكم في الكاميرا', 'تحكم احترافي كامل في حركة الكاميرا', 'نظام ECM بيدّيك السيطرة الكاملة على كل حركات الكاميرا — لاسلكيًا ومن مكان واحد.' ) ];
    $s[] = ecm_sys_buttons();
    $s[] = ecm_sys_image();
    $s[] = ecm_sys_stats( $stats );
    $s[] = ecm_el_header( '8 AXES · المحاور الثمانية', 'كل زاوية للكاميرا تحت أصابعك', '' );
    $s   = array_merge( $s, ecm_sys_cards( $axes, 4 ) );
    $s[] = ecm_sys_control_guide();
    $s[] = ecm_el_header( 'FEATURES · المزايا', 'احترافية في كل تفصيلة', '' );
    $s   = array_merge( $s, ecm_sys_cards( $feats, 3 ) );
    $s[] = ecm_el_header( 'HOW IT WORKS · كيف يعمل', 'جاهز في ٣ خطوات', '' );
    $s   = array_merge( $s, ecm_sys_cards( $steps, 3 ) );
    $s[] = ecm_el_header( 'VIDEOS · فيديوهات شرح', 'اتفرّج وتعلّم', '' );
    $s[] = ecm_sys_videos( 2 );
    $s[] = ecm_sys_cta( 'جاهز تتحكم باحتراف؟', 'اكتشف باقي أنظمة ECM، أو تواصل معانا لاختيار الإعداد المناسب لشغلك.' );
    return $s;
}

function ecm_build_vehicle_elements(): array {
    $groups = [
        [ '🛞', 'العجلات', 'حركة للأمام والخلف بنعومة، مع مسافات جاهزة وتحكم في السرعة.' ],
        [ '🧭', 'التوجيه', 'توجيه دقيق بأربعة أوضاع تناسب كل موقف.' ],
        [ '📐', 'الزاوية', 'تحكم في زاوية الذراع بخمس درجات جاهزة.' ],
    ];
    $steering = [
        [ '🔄', 'كل العجلات', 'الأربع عجلات بتتوجّه مع بعض — أقصى مرونة.' ],
        [ '⬆️', 'توجيه أمامي', 'العجلات الأمامية — للقيادة المستقرة.' ],
        [ '⬇️', 'توجيه خلفي', 'العجلات الخلفية — للمناورات الدقيقة.' ],
        [ '🌀', 'دوران مكاني', 'المركبة بتلفّ حول نفسها في مكانها.' ],
    ];
    $run = [
        [ '▶️', 'تشغيل', 'يشغّل المسار المسجّل ويحرّك المركبة تلقائيًا.' ],
        [ '🏠', 'رجوع', 'يرجّع المركبة على نفس المسار للبداية.' ],
        [ '⏹️', 'إيقاف فوري', 'يوقّف كل الحركة في الحال للأمان.' ],
    ];
    $stats = [ [ '٤', '', 'أوضاع توجيه' ], [ '٦', '', 'مستخدمين' ], [ '١٠', '', 'خطوات لكل مسار' ], [ '🎮', '', 'دعم جيمباد' ] ];

    $s   = [ ecm_el_header( 'VEHICLE CONTROL · التحكم في المركبة', 'قيادة وتوجيه وتسجيل مسارات', 'تحكم كامل في حركة المركبة وتوجيهها — وسجّل مسار حركة كامل وشغّله بضغطة.' ) ];
    $s[] = ecm_sys_buttons();
    $s[] = ecm_sys_image();
    $s[] = ecm_sys_stats( $stats );
    $s[] = ecm_el_header( 'CONTROL · مجموعات التحكم', 'ثلاث مجموعات متكاملة', '' );
    $s   = array_merge( $s, ecm_sys_cards( $groups, 3 ) );
    $s[] = ecm_el_header( 'STEERING · أوضاع التوجيه', 'أربعة أوضاع لكل موقف', '' );
    $s   = array_merge( $s, ecm_sys_cards( $steering, 4 ) );
    $s[] = ecm_el_header( 'RECORD · تسجيل المسارات', 'سجّل حركتك وشغّلها تلقائيًا', 'سجّل مسار حركة كامل لكل مستخدم، وشغّله بضغطة عشان المركبة تعيد نفس الحركة بدقة.' );
    $s[] = ecm_el_header( 'MODES · أوضاع التشغيل', 'تحكم كامل بضغطة', '' );
    $s   = array_merge( $s, ecm_sys_cards( $run, 3 ) );
    $s[] = ecm_el_header( 'VIDEOS · فيديوهات شرح', 'اتفرّج وتعلّم', '' );
    $s[] = ecm_sys_videos( 2 );
    $s[] = ecm_sys_cta( 'عايز تحكم أدق في مركبتك؟', 'تواصل معانا ونساعدك تجهّز نظام التحكم الأنسب لمركبتك.' );
    return $s;
}

function ecm_build_relay_elements(): array {
    $feats = [
        [ '🟢', 'حالة بصرية فورية', 'تعرف حالة كل مفتاح بنظرة — شغّال أو مقفول.' ],
        [ '👆', 'تشغيل/إيقاف بضغطة', 'اضغط أي مفتاح عشان تشغّله أو تقفله فورًا.' ],
        [ '✏️', 'أسماء قابلة للتعديل', 'سمِّ كل مفتاح باسم الجهاز المتوصّل بيه.' ],
        [ '⚡', 'تشغيل/إيقاف الكل', 'تحكم في كل المفاتيح دفعة واحدة بضغطة.' ],
    ];
    $caps = [
        [ '🔌', '٨ مخارج مستقلة', 'تتحكم في ٨ أجهزة كهربائية منفصلة.' ],
        [ '🎛️', 'تحكم فردي أو جماعي', 'لكل جهاز لوحده أو الكل مرة واحدة.' ],
        [ '📡', 'تحكم لاسلكي', 'من أي مكان من غير أسلاك.' ],
    ];
    $stats = [ [ '٨', '', 'مخارج' ], [ '👆', '', 'تحكم فردي' ], [ '⚡', '', 'تحكم جماعي' ], [ '🟢', '', 'حالة فورية' ] ];

    $s   = [ ecm_el_header( 'RELAY PANEL · لوحة المفاتيح', 'تحكم في كل أجهزتك بضغطة', 'لوحة بسيطة فيها ٨ مفاتيح تتحكم بيها في الإضاءة والأجهزة — كل مفتاح بحالة واضحة واسم تختاره.' ) ];
    $s[] = ecm_sys_buttons();
    $s[] = ecm_sys_image();
    $s[] = ecm_sys_stats( $stats );
    $s[] = ecm_el_header( 'CAPABILITIES · الإمكانيات', 'كل أجهزتك في مكان واحد', '' );
    $s   = array_merge( $s, ecm_sys_cards( $caps, 3 ) );
    $s[] = ecm_el_header( 'FEATURES · المزايا', 'بسيطة وقوية', '' );
    $s   = array_merge( $s, ecm_sys_cards( $feats, 4 ) );
    $s[] = ecm_el_header( 'VIDEOS · فيديوهات شرح', 'اتفرّج وتعلّم', '' );
    $s[] = ecm_sys_videos( 2 );
    $s[] = ecm_sys_cta( 'عايز تتحكم في كل معدّاتك بسهولة؟', 'تواصل معانا ونساعدك تجهّز لوحة المفاتيح المناسبة لمعدّاتك.' );
    return $s;
}

function ecm_build_gamepad_elements(): array {
    $g1 = [
        [ 'Y', 'تشغيل', 'يشغّل المسار المسجّل ويبدأ الحركة تلقائيًا.' ],
        [ 'X', 'رجوع', 'يرجّع على نفس المسار لنقطة البداية.' ],
        [ 'A', 'إيقاف فوري', 'يوقّف كل الحركة في الحال مع صوت تنبيه.' ],
        [ 'B', 'تسجيل', 'يبدأ أو يوقف تسجيل خطوات المسار.' ],
    ];
    $g2 = [
        [ '🕹️', 'العصا اليسرى', 'تحرّك المركبة للأمام والخلف.' ],
        [ '🕹️', 'العصا اليمنى', 'توجّه العجلات يمين وشمال.' ],
        [ 'L1/R1', 'تبديل التوجيه', 'تنقّل بين أوضاع التوجيه الأربعة.' ],
        [ 'L2/R2', 'السرعة', 'تزوّد أو تقلّل سرعة الحركة.' ],
    ];
    $g3 = [
        [ '← →', 'تبديل المستخدم', 'تنقّل بين الأوضاع المحفوظة.' ],
        [ '↑ ↓', 'الزاوية', 'تزوّد أو تقلّل زاوية الذراع.' ],
        [ 'START', 'تحميل الإعداد', 'يحمّل قيم المستخدم الحالي.' ],
        [ 'SELECT', 'وضع الإعداد', 'يفعّل أو يعطّل وضع الإعداد.' ],
    ];

    $s   = [ ecm_el_header( 'GAMEPAD · ذراع التحكم', 'تحكم كامل بذراع الألعاب', 'استخدم ذراع تحكم احترافي عشان تتحكم في المركبة والكاميرا بإحساس طبيعي وسلس.' ) ];
    $s[] = ecm_sys_buttons();
    $s[] = ecm_sys_image();
    $s[] = ecm_el_header( 'MAP · أزرار التشغيل', 'الأزرار الأساسية', '' );
    $s   = array_merge( $s, ecm_sys_cards( $g1, 4 ) );
    $s[] = ecm_el_header( 'MAP · العصي والأذرع', 'الحركة والتوجيه', '' );
    $s   = array_merge( $s, ecm_sys_cards( $g2, 4 ) );
    $s[] = ecm_el_header( 'MAP · الأسهم والأزرار الخاصة', 'تحكم إضافي', '' );
    $s   = array_merge( $s, ecm_sys_cards( $g3, 4 ) );
    $s[] = ecm_el_header( 'VIDEOS · فيديوهات شرح', 'اتفرّج وتعلّم', '' );
    $s[] = ecm_sys_videos( 2 );
    $s[] = ecm_sys_cta( 'جاهز تتحكم بذراعك؟', 'تواصل معانا ونساعدك تجهّز نظام التحكم بذراع الألعاب لمعدّاتك.' );
    return $s;
}


// ════════════════════════════════════════════════════════════
// §3  الزرع التلقائي
// ════════════════════════════════════════════════════════════
function ecm_seed_system_pages( bool $force = false ): int {
    if ( ! did_action( 'elementor/loaded' ) ) {
        return 0;
    }
    if ( ! current_user_can( 'edit_pages' ) ) {
        return 0;
    }

    $map = [
        'page-camera-control.php'  => 'ecm_build_camera_elements',
        'page-vehicle-control.php' => 'ecm_build_vehicle_elements',
        'page-relay-panel.php'     => 'ecm_build_relay_elements',
        'page-gamepad.php'         => 'ecm_build_gamepad_elements',
    ];

    $count = 0;
    foreach ( $map as $tpl => $builder ) {
        $pid = function_exists( 'ecm_find_page_by_template' ) ? ecm_find_page_by_template( $tpl ) : 0;
        if ( $pid && function_exists( $builder ) && function_exists( 'ecm_seed_elementor_page' ) ) {
            if ( ecm_seed_elementor_page( $pid, call_user_func( $builder ), $force ) ) {
                $count++;
            }
        }
    }
    return $count;
}

/** تشغيل تلقائي — يعيد البناء لما تتغيّر البلوكات (نسخة) */
function ecm_maybe_seed_system_pages() {
    $current = 7; // ارفع الرقم لما تتغيّر بلوكات الأنظمة عشان تتعاد
    $done    = (int) get_option( 'ecm_system_seeded_ver', 0 );
    // كان مزروع بالنسخة القديمة (الأساسية)؟ اعتبره نسخة 1 عشان يترقّى بالـ force
    if ( ! $done && get_option( 'ecm_system_seeded_v1' ) ) {
        $done = 1;
        delete_option( 'ecm_system_seeded_v1' );
    }
    if ( $done >= $current ) {
        return;
    }
    if ( ! did_action( 'elementor/loaded' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_pages' ) ) {
        return;
    }
    // أول زرع: عادي · ترقية من نسخة أقدم: إعادة بناء بودجِتس البرو
    ecm_seed_system_pages( $done > 0 );
    update_option( 'ecm_system_seeded_ver', $current );
}
add_action( 'admin_init', 'ecm_maybe_seed_system_pages', 25 );

/** إعادة الزرع عند تفعيل الثيم من جديد */
add_action( 'after_switch_theme', function () {
    delete_option( 'ecm_system_seeded_ver' );
} );

/** زر «إعادة بناء صفحات Elementor» في اللوحة يعيد بناء صفحات الأنظمة كمان (force) */
function ecm_handle_reseed_systems() {
    if ( empty( $_POST['ecm_reseed'] ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    check_admin_referer( 'ecm_reseed_nonce' );
    ecm_seed_system_pages( true );
}
add_action( 'admin_init', 'ecm_handle_reseed_systems', 4 );
