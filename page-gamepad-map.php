<?php
/**
 * Template Name: 🎮 ECM — خريطة أزرار الجويستيك
 *
 * مرجع كامل لخريطة أزرار ذراع التحكم (DS4) الافتراضية في تطبيق ECM —
 * خاص بنظام التحكم في الكاميرا فقط.
 *
 * @package ecm-theme
 */

get_header();

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single' ) ) {
    get_footer();
    return;
}
if ( function_exists( 'ecm_is_built_with_elementor' ) && ecm_is_built_with_elementor( get_queried_object_id() ) ) {
    ?>
    <main id="ecm-main" class="ecm-main-content" role="main">
        <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
    </main>
    <?php
    get_footer();
    return;
}

// ── شرائح أعلى الصفحة ──
$top_chips = [ '13 زرار', '4 محاور ستيك', '2 سهم D-Pad', '🔄 ارتباط تلقائي' ];

// ── مجموعات الأزرار (13 زرار) ──
// كل عنصر: id, key, alt, pos, title, desc, tag(instant|hold|toggle|empty), tag_label, meta, critical
$button_groups = [
    'أزرار الوجه' => [
        [
            'id' => 'gp-a', 'key' => 'A', 'alt' => '✕', 'pos' => 'الزرار السفلي',
            'title' => 'إيقاف كل الحركة', 'desc' => 'يوقف كل موتورات الرِج فورًا، أيًا كان اللي شغّال دلوقتي.',
            'tag' => 'instant', 'tag_label' => 'فوري', 'critical' => true, 'meta' => '🛑 STOP',
        ],
        [
            'id' => 'gp-b', 'key' => 'B', 'alt' => '◯', 'pos' => 'الزرار اليمين',
            'title' => 'بدون أمر', 'desc' => 'مش متعيّن له حاجة افتراضيًا — أول مرشّح جاهز لو عايز تضيف أمر خاص بيك.',
            'tag' => 'empty', 'tag_label' => 'فاضي للتخصيص',
        ],
        [
            'id' => 'gp-x', 'key' => 'X', 'alt' => '▢', 'pos' => 'الزرار الشمال',
            'title' => 'الرجوع للصفر (GO ZERO)', 'desc' => 'يرجّع كل المحاور لنقطة الصفر، بنفس إعدادات السرعة والتسارع المحفوظة لليوزر الحالي.',
            'tag' => 'instant', 'tag_label' => 'فوري',
        ],
        [
            'id' => 'gp-y', 'key' => 'Y', 'alt' => '△', 'pos' => 'الزرار العلوي',
            'title' => 'تشغيل / PLAY', 'desc' => 'يشغّل تسلسل اليوزر المختار حاليًا — لازم يكون فيه يوزر مختار من الشاشة.',
            'tag' => 'instant', 'tag_label' => 'فوري', 'meta' => '👤 محتاج يوزر مختار',
        ],
    ],
    'الكتف والتريجر' => [
        [
            'id' => 'gp-l1', 'key' => 'L1', 'pos' => 'الكتف الشمال',
            'title' => 'الفوكس — خلف ◀', 'desc' => 'دوس واستمر يحرّك الفوكس للخلف، سيب إيدك يوقف — زي زرار الفوكس في الشاشة بالظبط.',
            'tag' => 'hold', 'tag_label' => 'ضغط مستمر',
        ],
        [
            'id' => 'gp-r1', 'key' => 'R1', 'pos' => 'الكتف اليمين',
            'title' => 'الفوكس — أمام ▶', 'desc' => 'نفس فكرة L1 بس بالاتجاه العكسي.',
            'tag' => 'hold', 'tag_label' => 'ضغط مستمر',
        ],
        [
            'id' => 'gp-l2', 'key' => 'L2', 'pos' => 'التريجر الشمال',
            'title' => 'مود الزوم الحالي — خلف ◀', 'desc' => 'بيحرّك المحور اللي محدّده R3 دلوقتي: ZOOM افتراضيًا، أو EX1 / EX2 لو بدّلت المود.',
            'tag' => 'hold', 'tag_label' => 'ضغط مستمر', 'meta' => '🔗 مرتبط بـ R3',
        ],
        [
            'id' => 'gp-r2', 'key' => 'R2', 'pos' => 'التريجر اليمين',
            'title' => 'مود الزوم الحالي — أمام ▶', 'desc' => 'عكس اتجاه L2 — بيحرّك نفس المحور اللي حدّده R3.',
            'tag' => 'hold', 'tag_label' => 'ضغط مستمر', 'meta' => '🔗 مرتبط بـ R3',
        ],
    ],
    'وسط الدراع' => [
        [
            'id' => 'gp-start', 'key' => 'START', 'alt' => 'Options', 'pos' => 'وسط الدراع',
            'title' => 'حفظ القيم (SET)', 'desc' => 'يحفظ وضع المحاور الحالي كقيمة لليوزر المختار، وينسخها لحقول الإدخال في الشاشة.',
            'tag' => 'instant', 'tag_label' => 'فوري',
        ],
        [
            'id' => 'gp-select', 'key' => 'SELECT', 'alt' => 'Share', 'pos' => 'وسط الدراع',
            'title' => 'PAE (محجوز)', 'desc' => 'زرار احتياطي — بيبدّل متغيّر داخلي من غير أي وظيفة ظاهرة في الواجهة حاليًا. مرشّح تاني كويس للتخصيص.',
            'tag' => 'empty', 'tag_label' => 'محجوز',
        ],
        [
            'id' => 'gp-ps', 'key' => 'PS', 'alt' => 'MODE', 'pos' => 'زرار البلايستيشن',
            'title' => 'الرجوع للرئيسية', 'desc' => 'يقفل صفحة الكاميرا ويرجع للشاشة الرئيسية — إلا لو فيه بوب أب مفتوح دلوقتي.',
            'tag' => 'instant', 'tag_label' => 'فوري',
        ],
    ],
    'ضغطة الستيكات' => [
        [
            'id' => 'gp-l3', 'key' => 'L3', 'pos' => 'ضغطة الستيك الشمال',
            'title' => 'تفعيل / إلغاء الليمِت', 'desc' => 'يبدّل حدود الحركة (Limit) للمحاور — تفعيل أو إلغاء بكل ضغطة.',
            'tag' => 'toggle', 'tag_label' => 'مفتاح تبديل',
        ],
        [
            'id' => 'gp-r3', 'key' => 'R3', 'pos' => 'ضغطة الستيك اليمين',
            'title' => 'تبديل مود الزوم', 'desc' => 'يلف بين ٣ أوضاع لتريجرات L2 / R2: ZOOM ← EX1 ← EX2 ← يرجع لـ ZOOM.',
            'tag' => 'toggle', 'tag_label' => 'مفتاح تبديل', 'meta' => '🔗 بيتحكّم في L2 / R2',
        ],
    ],
];

// ── الستيكات التناظرية (4 محاور) ──
$sticks = [
    [
        'name' => 'الستيك الشمال', 'en' => 'L Stick',
        'axes' => [
            [ 'code' => 'AXIS_X', 'label' => 'PAN', 'desc' => 'أفقي: يمين يحرّك PAN يمين، شمال يحرّكه شمال.', 'note' => 'وضع الجيمبال: يتحكّم في PAN الجيمبال نفسه' ],
            [ 'code' => 'AXIS_Y', 'label' => 'TILT', 'desc' => 'رأسي: فوق يحرّك TILT لأعلى، تحت يحرّكه لأسفل.', 'note' => 'وضع الجيمبال: يتحكّم في TILT الجيمبال نفسه' ],
        ],
    ],
    [
        'name' => 'الستيك اليمين', 'en' => 'R Stick',
        'axes' => [
            [ 'code' => 'AXIS_Z', 'label' => 'ROTATE / SLIDER', 'desc' => 'أفقي: بيتحكم دايمًا في موتور ROTATE/SLIDER بالرِج — مش بيتأثر بوضع الجيمبال.', 'note' => 'دايمًا رِج' ],
            [ 'code' => 'AXIS_RZ', 'label' => 'CRANE', 'desc' => 'رأسي: بيتحكم دايمًا في موتور CRANE — مش بيتأثر بوضع الجيمبال برضه.', 'note' => 'دايمًا رِج' ],
        ],
    ],
];

// ── الـ D-Pad (سهمين) ──
$dpad = [
    [ 'id' => 'gp-hatx', 'code' => 'AXIS_HAT_X', 'title' => 'تبديل اليوزر (افتراضي)', 'desc' => 'دفعة يمين أو شمال = التنقل بين اليوزرات من ١ لـ ٦.' ],
    [ 'id' => 'gp-haty', 'code' => 'AXIS_HAT_Y', 'title' => 'خطوة السرعة (افتراضي)', 'desc' => 'دفعة فوق أو تحت = تبديل خطوة السرعة بين ٣ مستويات: بطيء ← متوسط ← سريع (10 ← 300 ← 1500)، وبتلف دائريًا.' ],
];

// ── مساحة التخصيص الكاملة ──
$custom_stats = [
    [ 'num' => '١٥+', 'title' => 'أمر عام لأي زرار', 'desc' => 'إيقاف، صفر، PLAY، SET، تبديل يوزر، سرعة… تقدر تحطهم على أي زرار من الـ١٣.' ],
    [ 'num' => '٨', 'title' => 'محاور رِج بالزرار', 'desc' => 'PAN / TILT / CRANE / ROTATE / ZOOM / FOCUS / EX1 / EX2 — كل واحد ينفع يتحرك بزرار مباشرة، مش بس بالستيك.' ],
    [ 'num' => '١٢', 'title' => 'أمر جيمبال', 'desc' => 'حركة وريسنتر وتبديل مود — دول بس بيشتغلوا لما وضع الجيمبال يكون مفعّل.' ],
    [ 'num' => '١٢', 'title' => 'هدف لكل ستيك', 'desc' => 'أي محور تحبه، أو «بدون» لو عايز توقفه — ومعاه اختيار عكس الاتجاه.' ],
];

$camera_page = function_exists( 'ecm_page_by_title' ) ? ecm_page_by_title( 'التحكم في الكاميرا' ) : null;

/** لون/تصنيف كل تاج */
function ecm_gpmap_tag_class( string $tag ): string {
    $map = [
        'instant' => 'ecm-badge-green',
        'hold'    => 'ecm-badge-orange',
        'toggle'  => 'ecm-badge-blue',
        'empty'   => 'ecm-badge-grey',
    ];
    return $map[ $tag ] ?? 'ecm-badge-grey';
}
?>

<style>
    /* ══ خريطة الجويستيك — أنماط خاصة بالصفحة دي ══ */
    .ecm-badge-grey { background: rgba(127,131,144,0.14); color: var(--ecm-grey-mid); border: 1px solid var(--ecm-border); }

    .ecm-gpmap-chips { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin: 6px 0 8px; }
    .ecm-gpmap-chip {
        display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:30px;
        background: var(--ecm-bg-card); border:1px solid var(--ecm-border);
        font-family:'Cairo',sans-serif; font-size:13px; font-weight:700; color: var(--ecm-grey-light);
    }
    .ecm-gpmap-chip b { color: var(--ecm-green); font-family:'Orbitron','Cairo',sans-serif; }

    .ecm-gpmap-note {
        display:flex; gap:12px; align-items:flex-start; max-width:760px; margin:0 auto;
        background: var(--ecm-bg-card); border:1px solid var(--ecm-border); border-inline-start:3px solid var(--ecm-green);
        border-radius: var(--ecm-radius-md); padding:16px 20px; font-size:14px; line-height:1.85; color: var(--ecm-grey-light);
    }
    .ecm-gpmap-note .ic { font-size:20px; flex-shrink:0; }

    /* — الرسم التخطيطي — */
    .ecm-gpmap-diagram { background: var(--ecm-bg-panel); border:1px solid var(--ecm-border); border-radius: var(--ecm-radius-lg); padding: 36px 28px; }
    .ecm-gpmap-diagram-grid {
        direction: ltr;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        grid-template-areas:
            "l2   .    .    .    r2"
            "l1   .    .    .    r1"
            "lst  dpad mid  face rst";
        gap: 16px 10px;
        align-items: center;
    }
    .ecm-gpmap-pill {
        display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px;
        min-height:56px; padding:8px 6px; text-align:center; text-decoration:none;
        background: var(--ecm-bg-card); border:1px solid var(--ecm-border); border-radius: var(--ecm-radius-sm);
        transition: border-color var(--ecm-transition), transform var(--ecm-transition), background var(--ecm-transition);
    }
    .ecm-gpmap-pill:hover { border-color: var(--ecm-green); transform: translateY(-2px); color: var(--ecm-white); }
    .ecm-gpmap-pill .k { font-family:'Orbitron','Cairo',sans-serif; font-weight:800; font-size:12px; color: var(--ecm-green); }
    .ecm-gpmap-pill .t { font-size:10px; color: var(--ecm-grey-mid); line-height:1.3; }

    .ecm-gpmap-mid { grid-area: mid; display:flex; flex-direction:column; gap:8px; }

    .ecm-gpmap-dpad { grid-area: dpad; display:grid; grid-template-columns:repeat(3,1fr); grid-template-rows:repeat(3,1fr); gap:4px; width:84px; height:84px; margin:0 auto; }
    .ecm-gpmap-dpad a {
        display:flex; align-items:center; justify-content:center; text-decoration:none;
        background: var(--ecm-bg-card); border:1px solid var(--ecm-border); border-radius:4px;
        color: var(--ecm-grey-light); font-size:13px; transition: border-color var(--ecm-transition), color var(--ecm-transition);
    }
    .ecm-gpmap-dpad a:hover { border-color: var(--ecm-green); color: var(--ecm-green); }
    .ecm-gpmap-dpad .du { grid-area: 1 / 2; } .ecm-gpmap-dpad .dl { grid-area: 2 / 1; }
    .ecm-gpmap-dpad .dr { grid-area: 2 / 3; } .ecm-gpmap-dpad .dd { grid-area: 3 / 2; }

    .ecm-gpmap-face { grid-area: face; display:grid; grid-template-columns:repeat(3,1fr); grid-template-rows:repeat(3,1fr); gap:4px; width:100px; height:100px; margin:0 auto; }
    .ecm-gpmap-face a { grid-area: auto; }
    .ecm-gpmap-face .fy { grid-area: 1 / 2; } .ecm-gpmap-face .fx { grid-area: 2 / 1; }
    .ecm-gpmap-face .fb { grid-area: 2 / 3; } .ecm-gpmap-face .fa { grid-area: 3 / 2; }
    .ecm-gpmap-face a .k { font-size:13px; }

    .ecm-gpmap-caption { text-align:center; color: var(--ecm-grey-mid); font-size:13px; margin-top:22px; }

    @media (max-width: 760px) {
        .ecm-gpmap-diagram-grid { display:flex; flex-wrap:wrap; justify-content:center; gap:14px; }
        .ecm-gpmap-diagram-grid > * { width:auto; }
        .ecm-gpmap-pill { min-width:74px; }
    }

    /* — كروت الأزرار — */
    .ecm-gpmap-card {
        scroll-margin-top: calc(var(--ecm-nav-height) + 20px);
        background: var(--ecm-bg-card); border:1px solid var(--ecm-border); border-radius: var(--ecm-radius-md);
        padding: 26px 24px; display:flex; flex-direction:column; gap:14px; position:relative;
    }
    .ecm-gpmap-card.is-critical { border-inline-start:3px solid var(--ecm-red); }
    .ecm-gpmap-card-head { display:flex; align-items:center; gap:14px; }
    .ecm-gpmap-card .ecm-gp-key { flex-shrink:0; }
    .ecm-gpmap-card-pos { font-size:12px; color: var(--ecm-grey-mid); }
    .ecm-gpmap-card-alt { font-size:11px; color: var(--ecm-grey-dark); }
    .ecm-gpmap-card h3 { font-family:'Cairo',sans-serif; font-size:17px; font-weight:800; color: var(--ecm-white); margin:0; }
    .ecm-gpmap-card p { font-size:14px; line-height:1.8; color: var(--ecm-grey-mid); margin:0; }
    .ecm-gpmap-card-foot { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-top:auto; padding-top:4px; }
    .ecm-gpmap-card-meta { font-size:12px; color: var(--ecm-grey-dark); }

    /* — الستيكات — */
    .ecm-gpmap-stick-card { background: var(--ecm-bg-card); border:1px solid var(--ecm-border); border-radius: var(--ecm-radius-md); padding:26px; }
    .ecm-gpmap-stick-card > header { display:flex; align-items:baseline; gap:10px; margin-bottom:18px; }
    .ecm-gpmap-stick-card > header h3 { font-family:'Cairo',sans-serif; font-size:17px; font-weight:800; color: var(--ecm-white); margin:0; }
    .ecm-gpmap-stick-card > header span { font-family:'Orbitron','Cairo',sans-serif; font-size:12px; color: var(--ecm-green); }
    .ecm-gpmap-axis-row { padding:14px 0; border-top:1px solid var(--ecm-border); }
    .ecm-gpmap-axis-row:first-of-type { border-top:none; padding-top:0; }
    .ecm-gpmap-axis-row .row-top { display:flex; align-items:center; gap:10px; margin-bottom:6px; flex-wrap:wrap; }
    .ecm-gpmap-axis-code { font-family:'Orbitron','Cairo',sans-serif; font-size:11px; color: var(--ecm-green); background: var(--ecm-green-subtle); border-radius:4px; padding:2px 8px; }
    .ecm-gpmap-axis-label { font-family:'Cairo',sans-serif; font-weight:800; color: var(--ecm-white); font-size:15px; }
    .ecm-gpmap-axis-row p { font-size:13.5px; color: var(--ecm-grey-mid); line-height:1.75; margin:0 0 6px; }
    .ecm-gpmap-axis-note { font-size:12px; color: var(--ecm-grey-dark); }

    .ecm-gpmap-2col { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
    @media (max-width: 760px) { .ecm-gpmap-2col { grid-template-columns:1fr; } }

    /* — كروت D-Pad — */
    .ecm-gpmap-dpad-card { scroll-margin-top: calc(var(--ecm-nav-height) + 20px); background: var(--ecm-bg-card); border:1px solid var(--ecm-border); border-radius: var(--ecm-radius-md); padding:24px; }
    .ecm-gpmap-dpad-card .code { font-family:'Orbitron','Cairo',sans-serif; font-size:12px; color: var(--ecm-green); display:block; margin-bottom:8px; }
    .ecm-gpmap-dpad-card h3 { font-family:'Cairo',sans-serif; font-size:16px; font-weight:800; color: var(--ecm-white); margin:0 0 8px; }
    .ecm-gpmap-dpad-card p { font-size:13.5px; color: var(--ecm-grey-mid); line-height:1.8; margin:0; }

    /* — التخصيص الكامل — */
    .ecm-gpmap-custom-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
    @media (max-width: 900px) { .ecm-gpmap-custom-grid { grid-template-columns:repeat(2,1fr); } }
    @media (max-width: 560px) { .ecm-gpmap-custom-grid { grid-template-columns:1fr; } }
    .ecm-gpmap-custom-card { background: var(--ecm-bg-card); border:1px solid var(--ecm-border); border-radius: var(--ecm-radius-md); padding:24px; text-align:center; }
    .ecm-gpmap-custom-num { display:block; font-family:'Orbitron','Cairo',sans-serif; font-size:28px; font-weight:800; color: var(--ecm-green); text-shadow:0 0 24px var(--ecm-green-glow); margin-bottom:10px; }
    .ecm-gpmap-custom-card h4 { font-family:'Cairo',sans-serif; font-size:15px; font-weight:800; color: var(--ecm-white); margin:0 0 8px; }
    .ecm-gpmap-custom-card p { font-size:12.5px; color: var(--ecm-grey-mid); line-height:1.7; margin:0; }

    /* — مسار الوصول لشاشة التخصيص — */
    .ecm-gpmap-crumb { display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; }
    .ecm-gpmap-crumb span { display:inline-flex; align-items:center; gap:8px; background: var(--ecm-bg-card); border:1px solid var(--ecm-border); border-radius:30px; padding:10px 20px; font-size:14px; font-weight:700; color: var(--ecm-grey-light); }
    .ecm-gpmap-crumb .sep { background:none; border:none; padding:0; color: var(--ecm-grey-dark); font-size:18px; }
</style>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 40px 0;">
        <?php if ( function_exists( 'ecm_system_pages_nav' ) ) ecm_system_pages_nav(); ?>
    </div>

    <!-- Hero -->
    <section class="ecm-container" style="padding-block: 40px 24px; text-align: center;">
        <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:18px;">GAMEPAD MAP · خريطة الأزرار</span>
        <h1 class="ecm-section-title" style="margin-bottom:18px;">خريطة أزرار الجويستيك الافتراضية</h1>
        <p style="max-width:720px; margin:0 auto 26px; color:var(--ecm-grey-mid); font-size:17px; line-height:1.95;">
            كل زرار وستيك في دراع البلايستيشن (DS4) ومعاه أمره الافتراضي في نظام التحكم بكاميرا ECM —
            إيه بيعمل بالظبط، وينفع مع إيه، وفين تلاقي شاشة التخصيص لو عايز تغيّره.
        </p>
        <div class="ecm-gpmap-chips">
            <?php foreach ( $top_chips as $chip ) : ?>
                <span class="ecm-gpmap-chip"><?php echo esc_html( $chip ); ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Auto-bind note -->
    <section class="ecm-container" style="padding-bottom: 40px;">
        <div class="ecm-gpmap-note">
            <span class="ic">💡</span>
            <span><strong>ملاحظة:</strong> أول ما تشغّل التطبيق ودراعك متصل بالجهاز، بيترّبط لوحده تلقائيًا — مفيش داعي تدخل شاشة الربط كل مرة.</span>
        </div>
    </section>

    <!-- Diagram -->
    <section class="ecm-container" style="padding-bottom: 56px;">
        <div class="ecm-gpmap-diagram">
            <div class="ecm-gpmap-diagram-grid">
                <a href="#gp-l2" class="ecm-gpmap-pill" style="grid-area:l2;"><span class="k">L2</span><span class="t">مود الزوم ◀</span></a>
                <a href="#gp-r2" class="ecm-gpmap-pill" style="grid-area:r2;"><span class="k">R2</span><span class="t">مود الزوم ▶</span></a>
                <a href="#gp-l1" class="ecm-gpmap-pill" style="grid-area:l1;"><span class="k">L1</span><span class="t">فوكس ◀</span></a>
                <a href="#gp-r1" class="ecm-gpmap-pill" style="grid-area:r1;"><span class="k">R1</span><span class="t">فوكس ▶</span></a>

                <a href="#gp-l3" class="ecm-gpmap-pill" style="grid-area:lst;"><span class="k">L3</span><span class="t">ليمِت</span></a>

                <div class="ecm-gpmap-dpad">
                    <a href="#gp-haty" class="du" title="فوق">▲</a>
                    <a href="#gp-hatx" class="dl" title="شمال">◀</a>
                    <a href="#gp-hatx" class="dr" title="يمين">▶</a>
                    <a href="#gp-haty" class="dd" title="تحت">▼</a>
                </div>

                <div class="ecm-gpmap-mid">
                    <a href="#gp-select" class="ecm-gpmap-pill"><span class="k">SEL</span></a>
                    <a href="#gp-ps" class="ecm-gpmap-pill"><span class="k">PS</span></a>
                    <a href="#gp-start" class="ecm-gpmap-pill"><span class="k">OPT</span></a>
                </div>

                <div class="ecm-gpmap-face">
                    <a href="#gp-y" class="fy ecm-gpmap-pill"><span class="k">Y</span></a>
                    <a href="#gp-x" class="fx ecm-gpmap-pill"><span class="k">X</span></a>
                    <a href="#gp-b" class="fb ecm-gpmap-pill"><span class="k">B</span></a>
                    <a href="#gp-a" class="fa ecm-gpmap-pill"><span class="k">A</span></a>
                </div>

                <a href="#gp-r3" class="ecm-gpmap-pill" style="grid-area:rst;"><span class="k">R3</span><span class="t">زوم مود</span></a>
            </div>
            <p class="ecm-gpmap-caption">اضغط أي جزء في الرسم للانتقال لتفصيله تحت — الرسم شكل تخطيطي مبسّط مش صورة حقيقية للدراع.</p>
        </div>
    </section>

    <!-- Buttons -->
    <section class="ecm-container" style="padding-bottom: 24px;">
        <header style="text-align:center; margin-bottom:12px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">BUTTONS · 13 CONTROLS</span>
            <h2 class="ecm-section-title">الأزرار</h2>
            <p style="max-width:680px; margin:14px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.8;">
                كل زرار بياخد أمر واحد افتراضيًا. الأوامر «الفورية» بتتنفّذ لحظة الضغط، و«الضغط المستمر» بيفضل شغّال طول ما إيدك على الزرار.
            </p>
        </header>
    </section>

    <?php foreach ( $button_groups as $group_title => $buttons ) : ?>
        <section class="ecm-container" style="padding-bottom: 56px;">
            <h3 style="font-family:'Cairo',sans-serif; font-size:18px; font-weight:800; color:var(--ecm-white); margin:0 0 20px;"><?php echo esc_html( $group_title ); ?></h3>
            <div class="ecm-feat-grid-3">
                <?php foreach ( $buttons as $b ) : ?>
                    <div id="<?php echo esc_attr( $b['id'] ); ?>" class="ecm-gpmap-card<?php echo ! empty( $b['critical'] ) ? ' is-critical' : ''; ?>">
                        <div class="ecm-gpmap-card-head">
                            <span class="ecm-gp-key"><?php echo esc_html( $b['key'] ); ?></span>
                            <div>
                                <div class="ecm-gpmap-card-pos"><?php echo esc_html( $b['pos'] ); ?></div>
                                <?php if ( ! empty( $b['alt'] ) ) : ?><div class="ecm-gpmap-card-alt"><?php echo esc_html( $b['alt'] ); ?></div><?php endif; ?>
                            </div>
                        </div>
                        <h3><?php echo esc_html( $b['title'] ); ?></h3>
                        <p><?php echo esc_html( $b['desc'] ); ?></p>
                        <div class="ecm-gpmap-card-foot">
                            <span class="ecm-admin-badge <?php echo esc_attr( ecm_gpmap_tag_class( $b['tag'] ) ); ?>"><?php echo esc_html( $b['tag_label'] ); ?></span>
                            <?php if ( ! empty( $b['meta'] ) ) : ?><span class="ecm-gpmap-card-meta"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <!-- Analog sticks -->
    <section class="ecm-container" style="padding-bottom: 56px;">
        <header style="text-align:center; margin-bottom:12px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">ANALOG STICKS · 4 AXES</span>
            <h2 class="ecm-section-title">الستيكات التناظرية</h2>
            <p style="max-width:720px; margin:14px auto 32px; color:var(--ecm-grey-mid); font-size:15px; line-height:1.85;">
                مش تحكّم تناسبي بالسرعة — أول ما تعدّي ٢٠٪ من مدى الستيك (منطقة ميتة ثابتة) المحور يتحرك بسرعة ثابتة في الاتجاه، وترجيع الستيك للنص = وقوف. أي محور قابل للعكس (Invert) من صفحة التخصيص.
            </p>
        </header>
        <div class="ecm-gpmap-2col">
            <?php foreach ( $sticks as $stick ) : ?>
                <div class="ecm-gpmap-stick-card">
                    <header><h3><?php echo esc_html( $stick['name'] ); ?></h3><span><?php echo esc_html( $stick['en'] ); ?></span></header>
                    <?php foreach ( $stick['axes'] as $ax ) : ?>
                        <div class="ecm-gpmap-axis-row">
                            <div class="row-top">
                                <span class="ecm-gpmap-axis-code"><?php echo esc_html( $ax['code'] ); ?></span>
                                <span class="ecm-gpmap-axis-label"><?php echo esc_html( $ax['label'] ); ?></span>
                            </div>
                            <p><?php echo esc_html( $ax['desc'] ); ?></p>
                            <span class="ecm-gpmap-axis-note"><?php echo esc_html( $ax['note'] ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- D-Pad -->
    <section class="ecm-container" style="padding-bottom: 56px;">
        <header style="text-align:center; margin-bottom:32px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">D-PAD · 2 AXES</span>
            <h2 class="ecm-section-title">السهام</h2>
            <p style="max-width:680px; margin:14px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.8;">
                كل سهم من التنين ينفع ياخد أي من نفس الدورين — مش لازم يفضلوا زي الافتراضي.
            </p>
        </header>
        <div class="ecm-gpmap-2col">
            <?php foreach ( $dpad as $d ) : ?>
                <div id="<?php echo esc_attr( $d['id'] ); ?>" class="ecm-gpmap-dpad-card">
                    <span class="code"><?php echo esc_html( $d['code'] ); ?></span>
                    <h3><?php echo esc_html( $d['title'] ); ?></h3>
                    <p><?php echo esc_html( $d['desc'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Full custom mapping -->
    <section class="ecm-container" style="padding-bottom: 56px;">
        <header style="text-align:center; margin-bottom:32px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">FULL CUSTOM MAPPING</span>
            <h2 class="ecm-section-title">مساحة التخصيص الكاملة</h2>
            <p style="max-width:680px; margin:14px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.8;">
                الجدول فوق ده هو الافتراضي بس — أي عنصر في الدراع ينفع تدّيله أمر تاني من اللي التطبيق بيعرفهم.
            </p>
        </header>
        <div class="ecm-gpmap-custom-grid">
            <?php foreach ( $custom_stats as $cs ) : ?>
                <div class="ecm-gpmap-custom-card">
                    <span class="ecm-gpmap-custom-num"><?php echo esc_html( $cs['num'] ); ?></span>
                    <h4><?php echo esc_html( $cs['title'] ); ?></h4>
                    <p><?php echo esc_html( $cs['desc'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Where to find it -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:24px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">SETTINGS PATH</span>
            <h2 class="ecm-section-title" style="font-size:20px !important;">فين تلاقي شاشة التخصيص</h2>
        </header>
        <div class="ecm-gpmap-crumb">
            <span>⚙ الإعدادات</span>
            <span class="sep">‹</span>
            <span>الجويستيك</span>
            <span class="sep">‹</span>
            <span>تخصيص أزرار الذراع</span>
        </div>
    </section>

    <!-- CTA -->
    <section class="ecm-container" style="padding-bottom: 80px;">
        <div class="ecm-sys-cta">
            <h2 class="ecm-section-title" style="margin-bottom:14px;">جاهز تتحكم في الكاميرا بذراعك؟</h2>
            <p style="max-width:520px; margin:0 auto 26px; color:var(--ecm-grey-mid); font-size:16px; line-height:1.9;">
                ارجع لصفحة نظام التحكم في الكاميرا، أو تواصل معانا لو محتاج مساعدة في التخصيص.
            </p>
            <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                <?php if ( $camera_page ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $camera_page->ID ) ); ?>" class="ecm-btn-primary">🎥 التحكم في الكاميرا</a>
                <?php endif; ?>
                <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-ghost">تواصل معنا</a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
