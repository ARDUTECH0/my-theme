<?php
/**
 * Template Name: 🎥 ECM — التحكم في الكاميرا
 *
 * صفحة تعريفية احترافية بنظام التحكم في حركة الكاميرا (٨ محاور).
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

$axes = [
    [ '🔄', 'بان (Pan) — تدوير أفقي', 'تدوير الكاميرا يمين وشمال بحركة ناعمة وثابتة — مثالي لتتبّع الأشخاص أو استعراض المشهد. تتحكم في السرعة عشان تطلع لقطة انسيابية من غير أي اهتزاز.' ],
    [ '↕️', 'تيلت (Tilt) — تدوير رأسي', 'رفع وخفض زاوية الكاميرا لفوق ولتحت — للقطات من زوايا مختلفة بثبات تام، مع نعومة كاملة في بداية ونهاية الحركة.' ],
    [ '🧭', 'الرأس (Head)', 'محور إضافي بيدّي رأس الكاميرا دوران مستقل — يفتح لك حركات إبداعية وزوايا مش متاحة في الأنظمة العادية.' ],
    [ '🎞️', 'الميل (Roll)', 'يميل الكاميرا جنبًا للحصول على لقطات سينمائية درامية وانتقالات مبتكرة بين المشاهد.' ],
    [ '🔍', 'الزووم (Zoom)', 'تحكم دقيق وناعم في تقريب وتبعيد العدسة — قرّب على أدق التفاصيل أو وسّع للمشهد الكامل بسلاسة احترافية.' ],
    [ '🎯', 'الفوكس (Focus)', 'ضبط التركيز البؤري بدقة عالية لحظة بلحظة — حافظ على وضوح اللقطة وانقل التركيز بين العناصر باحتراف (Focus Pull).' ],
    [ '🏗️', 'الونش (Crane)', 'يرفع ويخفض الكاميرا على الرافعة لمدى واسع، وفيه معايرة خاصة تضبط نقطة المنتصف بدقة عشان حركة متزنة وآمنة.' ],
    [ '↔️', 'السلايدر (Slider)', 'يحرّك الكاميرا على القضيب الانزلاقي بنعومة للقطات Dolly احترافية، وتقدر تتحكم فيه بذراع التحكم مباشرة أثناء التصوير.' ],
];

$stats = [
    [ '٨',     'محاور حركة' ],
    [ '<50ms', 'زمن الاستجابة' ],
    [ '٦',     'أوضاع محفوظة' ],
    [ '100%',  'تحكم لاسلكي' ],
];

$steps = [
    [ 'وصّل', 'ركّب الكاميرا على النظام ووصّل المحاور — كل محور بيتعرّف تلقائيًا وجاهز للتحكم.' ],
    [ 'تحكّم', 'حرّك أي محور من الشاشة أو بذراع التحكم، واضبط السرعة والنعومة حسب اللقطة.' ],
    [ 'احفظ وكرّر', 'خزّن أوضاعك المفضّلة في الأوضاع الجاهزة، وارجعلها أو كرّر الحركة بضغطة.' ],
];
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 40px 0;">
        <?php if ( function_exists( 'ecm_system_pages_nav' ) ) ecm_system_pages_nav(); ?>
    </div>

    <!-- Hero -->
    <section class="ecm-container" style="padding-block: 48px 32px; text-align: center;">
        <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:18px;">CAMERA CONTROL · التحكم في الكاميرا</span>
        <h1 class="ecm-section-title" style="margin-bottom:18px;">تحكم احترافي كامل في حركة الكاميرا</h1>
        <p style="max-width:680px; margin:0 auto 28px; color:var(--ecm-grey-mid); font-size:17px; line-height:1.95;">
            نظام ECM بيدّيك السيطرة الكاملة على كل حركات الكاميرا — لاسلكيًا ومن مكان واحد.
            ٨ محاور مستقلة، تحكم دقيق في السرعة والنعومة، وأوضاع جاهزة تحفظ مشاهدك وترجعلها في أي وقت.
        </p>
        <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-primary">تواصل معنا</a>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost">كل الأنظمة</a>
        </div>
    </section>

    <section class="ecm-container" style="padding-bottom: 48px;">
        <div class="ecm-media-ph ecm-sys-hero-media">
            <span class="ecm-mph-icon">🎥</span>
            <span class="ecm-mph-label">صورة الغلاف — شاشة التحكم</span>
        </div>
    </section>

    <!-- Stats -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <div class="ecm-sys-stats">
            <?php foreach ( $stats as $st ) : ?>
                <div class="ecm-sys-stat">
                    <span class="num"><?php echo esc_html( $st[0] ); ?></span>
                    <span class="lbl"><?php echo esc_html( $st[1] ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Highlight row 1 -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <div class="ecm-sys-row">
            <div>
                <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">PRECISION · دقة</span>
                <h3>تحكم دقيق في كل حركة</h3>
                <p>كل محور ليه كنترول مستقل بالكامل — تظبط الاتجاه والسرعة والتسارع (النعومة) بما يناسب كل لقطة، فتطلع حركة سينمائية متزنة من غير اهتزاز أو تقطيع.</p>
                <ul>
                    <li>✅ تحكم في السرعة لكل محور على حدة</li>
                    <li>✅ نعومة (تسارع/تباطؤ) لحركة احترافية</li>
                    <li>✅ حدود حركة آمنة تحمي معدّاتك</li>
                    <li>✅ حركة فورية باستجابة أقل من جزء من الثانية</li>
                </ul>
            </div>
            <div class="ecm-media-ph" style="aspect-ratio:4/3;">
                <span class="ecm-mph-icon">🎚️</span>
                <span class="ecm-mph-label">صورة — التحكم الدقيق</span>
            </div>
        </div>
    </section>

    <!-- Highlight row 2 (reversed) -->
    <section class="ecm-container" style="padding-bottom: 72px;">
        <div class="ecm-sys-row">
            <div class="ecm-media-ph" style="aspect-ratio:4/3;">
                <span class="ecm-mph-icon">💾</span>
                <span class="ecm-mph-label">صورة — الأوضاع الجاهزة</span>
            </div>
            <div>
                <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">PRESETS · أوضاع جاهزة</span>
                <h3>احفظ مشاهدك واسترجعها فورًا</h3>
                <p>احفظ لحد ٦ أوضاع كاملة لكل محاور الكاميرا، وبدّل بينها في ثانية. كرّر نفس الحركة بدقة في كل تصوير، وارجع لوضع البداية أو أوقف كل حاجة فورًا بضغطة واحدة.</p>
                <ul>
                    <li>✅ ٦ أوضاع جاهزة قابلة للحفظ والاستدعاء</li>
                    <li>✅ رجوع لوضع الصفر بضغطة</li>
                    <li>✅ إيقاف فوري لكل المحاور للأمان</li>
                    <li>✅ معايرة دقيقة لمحور الونش</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Axes -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">8 AXES · المحاور الثمانية</span>
            <h2 class="ecm-section-title">كل زاوية للكاميرا تحت أصابعك</h2>
            <p style="max-width:600px; margin:14px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.8;">
                ثمانية محاور حركة مستقلة، كل واحد متخصّص في نوع حركة — مع بعض بيدّوك حرية كاملة في تحريك الكاميرا زي ما تتخيّل.
            </p>
        </header>

        <div class="ecm-feat-grid-3">
            <?php foreach ( $axes as $i => $ax ) : ?>
                <div class="ecm-feat-card ecm-axis-card">
                    <div class="ecm-media-ph">
                        <span class="ecm-mph-icon"><?php echo esc_html( $ax[0] ); ?></span>
                        <span class="ecm-mph-label">صورة توضيحية</span>
                    </div>
                    <span class="ecm-axis-num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
                    <h3><?php echo esc_html( $ax[1] ); ?></h3>
                    <p class="ecm-feat-text"><?php echo esc_html( $ax[2] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- How it works -->
    <section class="ecm-container" style="padding-bottom: 72px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">HOW IT WORKS · كيف يعمل</span>
            <h2 class="ecm-section-title">جاهز في ٣ خطوات</h2>
        </header>
        <div class="ecm-sys-steps">
            <?php foreach ( $steps as $i => $stp ) : ?>
                <div class="ecm-sys-step">
                    <span class="step-num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
                    <h4><?php echo esc_html( $stp[0] ); ?></h4>
                    <p><?php echo esc_html( $stp[1] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php
    $caps = [
        'تحرّك الكاميرا في ٨ اتجاهات مستقلة',
        'تتبّع الأشخاص والحركة بسلاسة احترافية',
        'تنفّذ حركات سينمائية (بان · تيلت · ميل)',
        'تتحكم في الزووم والفوكس بدقة عالية',
        'تحفظ وتسترجع مشاهد كاملة بضغطة',
        'تتحكم بذراع الألعاب (Gamepad)',
        'تضبط معايرة دقيقة لمحور الرافعة',
        'توقف كل الحركة فورًا للأمان الكامل',
    ];
    $tuts = [
        [ '🔌', 'أول تشغيل', [ 'وصّل الكاميرا والمحاور بالنظام', 'شغّل النظام واستنى مؤشر الجاهزية', 'افتح الشاشة واتأكد من الاتصال' ] ],
        [ '🕹️', 'تحريك المحاور', [ 'اختَر المحور اللي عايز تحرّكه', 'اضغط مستمر للحركة (أمام/خلف)', 'اضبط السرعة والنعومة المناسبة' ] ],
        [ '💾', 'حفظ وضع جاهز', [ 'اضبط كل المحاور للوضع المطلوب', 'اختَر رقم وضع من ١ لـ ٦', 'احفظه واسترجعه بضغطة في أي وقت' ] ],
    ];
    $videos = [
        [ 'نظرة عامة على نظام الكاميرا', '', '3:20' ],
        [ 'إزاي تحرّك الكاميرا خطوة بخطوة', '', '5:10' ],
        [ 'حفظ المشاهد والأوضاع الجاهزة', '', '4:00' ],
    ];
    ?>

    <!-- What you can do -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <div class="ecm-ctrl-card" style="padding:40px 36px;">
            <header style="margin-bottom:28px;">
                <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:12px;">CAPABILITIES · إيه اللي تقدر تعمله</span>
                <h2 style="font-family:'Cairo',sans-serif; font-size:24px; font-weight:800; color:var(--ecm-white);">إمكانيات النظام</h2>
            </header>
            <div class="ecm-use-list">
                <?php foreach ( $caps as $cap ) : ?>
                    <div class="use-item"><span class="ic">✓</span><span><?php echo esc_html( $cap ); ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Usage tutorials -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">USAGE · طرق الاستخدام</span>
            <h2 class="ecm-section-title">دليل الاستخدام خطوة بخطوة</h2>
        </header>
        <div class="ecm-feat-grid-3">
            <?php foreach ( $tuts as $t ) : ?>
                <div class="ecm-tut-card">
                    <div class="tut-ico"><?php echo esc_html( $t[0] ); ?></div>
                    <h4><?php echo esc_html( $t[1] ); ?></h4>
                    <ol>
                        <?php foreach ( $t[2] as $step ) : ?><li><?php echo esc_html( $step ); ?></li><?php endforeach; ?>
                    </ol>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Videos -->
    <section class="ecm-container" style="padding-bottom: 72px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">VIDEOS · فيديوهات شرح</span>
            <h2 class="ecm-section-title">اتفرّج وتعلّم</h2>
            <p style="max-width:560px; margin:14px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.8;">شروحات بالفيديو تمشّيك خطوة بخطوة في كل تفاصيل النظام.</p>
        </header>
        <div class="ecm-feat-grid-3">
            <?php foreach ( $videos as $v ) { ecm_render_video_card( $v[0], $v[1], $v[2] ); } ?>
        </div>
        <p style="text-align:center; color:var(--ecm-grey-dark); font-size:13px; margin-top:20px;">* أماكن الفيديوهات جاهزة — استبدلها بروابط YouTube بتاعتك.</p>
    </section>

    <!-- CTA -->
    <section class="ecm-container" style="padding-bottom: 80px;">
        <div class="ecm-sys-cta">
            <h2 class="ecm-section-title" style="margin-bottom:14px;">جاهز تتحكم باحتراف؟</h2>
            <p style="max-width:520px; margin:0 auto 26px; color:var(--ecm-grey-mid); font-size:16px; line-height:1.9;">
                اكتشف باقي أنظمة ECM، أو تواصل معانا عشان نساعدك تختار الإعداد المناسب لشغلك.
            </p>
            <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-primary">تواصل معنا</a>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost">الصفحة الرئيسية</a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
