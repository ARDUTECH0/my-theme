<?php
/**
 * Template Name: 🚗 ECM — التحكم في المركبة
 *
 * صفحة تعريفية احترافية بنظام التحكم في حركة المركبة وتوجيهها وتسجيل المسارات.
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

$groups = [
    [ '🛞', 'العجلات', 'حركة المركبة للأمام والخلف بنعومة، مع مسافات جاهزة (٥٠ / ١٠٠ / ١٥٠ / ٢٥٠ سم) للوصول الدقيق، وتحكم كامل في السرعة على ٦ مستويات.' ],
    [ '🧭', 'التوجيه', 'توجيه دقيق للعجلات بأربعة أوضاع مختلفة تناسب كل موقف — من الالتفاف بكل العجلات للدوران في المكان، مع معايرة كاملة للتوجيه.' ],
    [ '📐', 'الزاوية', 'تحكم في زاوية الذراع/الرافعة بخمس درجات جاهزة، لوضعية تصوير مثالية في كل لقطة — تتفعّل في أوضاع التوجيه ذات الاتجاه الواحد.' ],
];

$steering = [
    [ '🔄', 'كل العجلات', 'الأربع عجلات بتتوجّه مع بعض — أقصى مرونة وأصغر دائرة التفاف.' ],
    [ '⬆️', 'توجيه أمامي', 'العجلات الأمامية بس — للقيادة العادية المستقرة على الخط المستقيم.' ],
    [ '⬇️', 'توجيه خلفي', 'العجلات الخلفية — للمناورات الدقيقة في الأماكن الضيقة.' ],
    [ '🌀', 'دوران مكاني', 'المركبة بتلفّ حول نفسها في مكانها — لتغيير الاتجاه بالكامل من غير ما تتحرك.' ],
];

$run = [
    [ '▶️', 'تشغيل', 'يشغّل المسار المسجّل ويحرّك المركبة تلقائيًا خطوة بخطوة بدقة.' ],
    [ '🏠', 'رجوع', 'يرجّع المركبة على نفس المسار لنقطة البداية بأمان.' ],
    [ '⏹️', 'إيقاف فوري', 'يوقّف كل الحركة في الحال مع تنبيه — للأمان الكامل.' ],
];

$stats = [
    [ '٤',  'أوضاع توجيه' ],
    [ '٦',  'مستخدمين' ],
    [ '١٠', 'خطوات لكل مسار' ],
    [ '🎮', 'دعم جيمباد' ],
];

$steps = [
    [ 'اختَر الوضع', 'فعّل وضع الإعداد واختَر وضع التوجيه والسرعة المناسبين لشغلك.' ],
    [ 'سجّل المسار', 'سجّل حركتك خطوة بخطوة — لحد ١٠ خطوات لكل مستخدم.' ],
    [ 'شغّل وكرّر', 'اضغط تشغيل عشان المركبة تعيد نفس المسار بدقة، أو ترجع منه.' ],
];
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 40px 0;">
        <?php if ( function_exists( 'ecm_system_pages_nav' ) ) ecm_system_pages_nav(); ?>
    </div>

    <!-- Hero -->
    <section class="ecm-container" style="padding-block: 48px 32px; text-align: center;">
        <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:18px;">VEHICLE CONTROL · التحكم في المركبة</span>
        <h1 class="ecm-section-title" style="margin-bottom:18px;">قيادة وتوجيه وتسجيل مسارات</h1>
        <p style="max-width:700px; margin:0 auto 28px; color:var(--ecm-grey-mid); font-size:17px; line-height:1.95;">
            تحكم كامل في حركة المركبة وتوجيهها — قُد للأمام والخلف، وجّه بأربعة أوضاع، اضبط الزاوية،
            وسجّل مسار حركة كامل وشغّله بضغطة. كل ده من شاشة واحدة بسيطة وبذراع تحكم احترافي.
        </p>
        <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-primary">تواصل معنا</a>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost">كل الأنظمة</a>
        </div>
    </section>

    <section class="ecm-container" style="padding-bottom: 48px;">
        <div class="ecm-media-ph ecm-sys-hero-media">
            <span class="ecm-mph-icon">🚗</span>
            <span class="ecm-mph-label">صورة الغلاف — شاشة التحكم في المركبة</span>
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

    <!-- Control groups -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">CONTROL · مجموعات التحكم</span>
            <h2 class="ecm-section-title">ثلاث مجموعات تحكم متكاملة</h2>
            <p style="max-width:600px; margin:14px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.8;">
                مجموعة واحدة بتشتغل في كل مرة عشان تحكم واضح ومنظّم — تنقّل بينها بسهولة حسب اللي محتاجه.
            </p>
        </header>
        <div class="ecm-feat-grid-3">
            <?php foreach ( $groups as $g ) : ?>
                <div class="ecm-feat-card ecm-axis-card">
                    <div class="ecm-media-ph">
                        <span class="ecm-mph-icon"><?php echo esc_html( $g[0] ); ?></span>
                        <span class="ecm-mph-label">صورة توضيحية</span>
                    </div>
                    <h3><?php echo esc_html( $g[1] ); ?></h3>
                    <p class="ecm-feat-text"><?php echo esc_html( $g[2] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Steering modes -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">STEERING · أوضاع التوجيه</span>
            <h2 class="ecm-section-title">أربعة أوضاع لكل موقف</h2>
        </header>
        <div class="ecm-feat-grid-3">
            <?php foreach ( $steering as $st ) : ?>
                <div class="ecm-ctrl-card" style="text-align:center;">
                    <div style="font-size:36px; margin-bottom:14px;"><?php echo esc_html( $st[0] ); ?></div>
                    <h3 style="font-family:'Cairo',sans-serif; font-size:16px; font-weight:700; color:var(--ecm-white); margin-bottom:8px;"><?php echo esc_html( $st[1] ); ?></h3>
                    <p class="ecm-feat-text"><?php echo esc_html( $st[2] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Recording highlight -->
    <section class="ecm-container" style="padding-bottom: 72px;">
        <div class="ecm-sys-row">
            <div>
                <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">RECORD · تسجيل المسارات</span>
                <h3>سجّل حركتك وشغّلها تلقائيًا</h3>
                <p>سجّل مسار حركة كامل — خطوة بخطوة — وخزّنه لكل مستخدم على حدة (لحد ٦ مستخدمين، ١٠ خطوات لكل واحد). بعد كده شغّله بضغطة عشان المركبة تعيد نفس الحركة بدقة في كل تصوير.</p>
                <ul>
                    <li>✅ تسجيل مسارات متعددة لكل مستخدم</li>
                    <li>✅ تشغيل تلقائي دقيق ومتكرّر</li>
                    <li>✅ رجوع آمن على نفس المسار</li>
                    <li>✅ تحكم كامل بذراع الألعاب (Gamepad)</li>
                </ul>
            </div>
            <div class="ecm-media-ph" style="aspect-ratio:4/3;">
                <span class="ecm-mph-icon">⏺️</span>
                <span class="ecm-mph-label">صورة — تسجيل المسار</span>
            </div>
        </div>
    </section>

    <!-- Run modes -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">MODES · أوضاع التشغيل</span>
            <h2 class="ecm-section-title">تحكم كامل بضغطة</h2>
        </header>
        <div class="ecm-feat-grid-3">
            <?php foreach ( $run as $r ) : ?>
                <div class="ecm-ctrl-card" style="text-align:center;">
                    <div style="font-size:36px; margin-bottom:14px;"><?php echo esc_html( $r[0] ); ?></div>
                    <h3 style="font-family:'Cairo',sans-serif; font-size:16px; font-weight:700; color:var(--ecm-white); margin-bottom:8px;"><?php echo esc_html( $r[1] ); ?></h3>
                    <p class="ecm-feat-text"><?php echo esc_html( $r[2] ); ?></p>
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
        'تقود المركبة للأمام والخلف بدقة',
        'تتحرك مسافات ثابتة جاهزة (٥٠–٢٥٠ سم)',
        'توجّه بأربعة أوضاع مختلفة',
        'تلفّ المركبة حول نفسها في مكانها',
        'تضبط زاوية الذراع بخمس درجات',
        'تسجّل مسار حركة كامل وتشغّله تلقائيًا',
        'تحفظ إعدادات لـ ٦ مستخدمين',
        'تتحكم بالكامل بذراع الألعاب وتعاير التوجيه',
    ];
    $tuts = [
        [ '🎚️', 'تجهيز الإعداد', [ 'فعّل وضع الإعداد (Config)', 'اختَر وضع التوجيه المناسب', 'اختَر السرعة المناسبة لشغلك' ] ],
        [ '⏺️', 'تسجيل مسار', [ 'اضغط «ابدأ التسجيل»', 'سجّل حركتك خطوة بخطوة', 'أنهِ المسار لما تخلّص' ] ],
        [ '▶️', 'تشغيل المسار', [ 'اختَر المستخدم المحفوظ', 'اضغط «تشغيل» لإعادة الحركة', 'استخدم «رجوع» أو «إيقاف» وقت الحاجة' ] ],
    ];
    $videos = [
        [ 'جولة في نظام التحكم في المركبة', '', '4:30' ],
        [ 'إزاي تسجّل وتشغّل مسار حركة', '', '6:00' ],
        [ 'أوضاع التوجيه ومتى تستخدم كل واحد', '', '3:45' ],
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
            <h2 class="ecm-section-title" style="margin-bottom:14px;">عايز تحكم أدق في مركبتك؟</h2>
            <p style="max-width:520px; margin:0 auto 26px; color:var(--ecm-grey-mid); font-size:16px; line-height:1.9;">
                تواصل معانا ونساعدك تجهّز نظام التحكم الأنسب لمركبتك وطريقة شغلك.
            </p>
            <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-primary">تواصل معنا</a>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost">الصفحة الرئيسية</a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
