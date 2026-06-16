<?php
/**
 * Template Name: 🔌 ECM — لوحة المفاتيح (الريليهات)
 *
 * صفحة تعريفية احترافية بلوحة التحكم في المخارج الكهربائية (٨ مفاتيح).
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

// عيّنة مفاتيح للعرض (الاسم + هل هو شغّال)
$switches = [
    [ 'إضاءة رئيسية', true ],
    [ 'إضاءة جانبية', false ],
    [ 'المروحة',      true ],
    [ 'الشاحن',       false ],
    [ 'كاميرا 1',      true ],
    [ 'كاميرا 2',      false ],
    [ 'صافرة',        false ],
    [ 'احتياطي',      false ],
];

$features = [
    [ '🟢', 'حالة بصرية فورية', 'نقطة خضراء = شغّال، رمادية = مقفول، مع شريحة ON/OFF واضحة — تعرف حالة كل مفتاح بنظرة واحدة.' ],
    [ '👆', 'تشغيل/إيقاف بضغطة', 'اضغط أي مفتاح عشان تشغّله أو تقفله فورًا — استجابة لحظية للأجهزة المتوصّلة.' ],
    [ '✏️', 'أسماء قابلة للتعديل', 'سمِّ كل مفتاح باسم الجهاز المتوصّل بيه (إضاءة، مروحة، شاحن...) عشان متلخبطش أبدًا.' ],
    [ '⚡', 'تشغيل/إيقاف الكل', 'تحكم في كل المفاتيح دفعة واحدة بضغطة — مثالي لبداية ونهاية يوم التصوير.' ],
];

$stats = [
    [ '٨',  'مخارج كهربائية' ],
    [ '👆', 'تحكم فردي' ],
    [ '⚡', 'تحكم جماعي' ],
    [ '🟢', 'حالة فورية' ],
];

$steps = [
    [ 'وصّل أجهزتك', 'وصّل الإضاءة أو الأجهزة بالمخارج الثمانية للنظام.' ],
    [ 'سمِّ المفاتيح', 'غيّر اسم كل مفتاح لاسم الجهاز المتوصّل بيه عشان سهولة التحكم.' ],
    [ 'تحكّم بضغطة', 'شغّل أو اقفل أي جهاز — فردي أو الكل مرة واحدة — من أي مكان.' ],
];
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 40px 0;">
        <?php if ( function_exists( 'ecm_system_pages_nav' ) ) ecm_system_pages_nav(); ?>
    </div>

    <!-- Hero -->
    <section class="ecm-container" style="padding-block: 48px 32px; text-align: center;">
        <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:18px;">RELAY PANEL · لوحة المفاتيح</span>
        <h1 class="ecm-section-title" style="margin-bottom:18px;">تحكم في كل أجهزتك بضغطة</h1>
        <p style="max-width:680px; margin:0 auto 28px; color:var(--ecm-grey-mid); font-size:17px; line-height:1.95;">
            لوحة بسيطة فيها ٨ مفاتيح تتحكم بيها في الإضاءة والأجهزة والمعدّات المتوصّلة بالنظام —
            كل مفتاح بحالة واضحة واسم تختاره، وتشغّل أو تقفل أي حاجة في لحظة، فردي أو الكل مرة واحدة.
        </p>
        <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-primary">تواصل معنا</a>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost">كل الأنظمة</a>
        </div>
    </section>

    <!-- Stats -->
    <section class="ecm-container" style="padding-bottom: 56px;">
        <div class="ecm-sys-stats">
            <?php foreach ( $stats as $st ) : ?>
                <div class="ecm-sys-stat">
                    <span class="num"><?php echo esc_html( $st[0] ); ?></span>
                    <span class="lbl"><?php echo esc_html( $st[1] ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Switches grid -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">8 OUTPUTS · ثمانية مخارج</span>
            <h2 class="ecm-section-title">لوحة المفاتيح</h2>
        </header>
        <div class="ecm-feat-grid-3">
            <?php foreach ( $switches as $sw ) :
                $on = (bool) $sw[1];
            ?>
                <div class="ecm-ctrl-card" style="display:flex; align-items:center; gap:16px;">
                    <span style="flex-shrink:0; width:12px; height:12px; border-radius:50%; background:<?php echo $on ? 'var(--ecm-green)' : 'var(--ecm-grey-dark)'; ?>; box-shadow:<?php echo $on ? 'var(--ecm-shadow-green)' : 'none'; ?>;"></span>
                    <div style="flex:1; min-width:0;">
                        <div style="font-family:'Cairo',sans-serif; font-size:15px; font-weight:700; color:var(--ecm-white);"><?php echo esc_html( $sw[0] ); ?></div>
                    </div>
                    <span style="flex-shrink:0; font-family:'Orbitron',monospace; font-size:11px; font-weight:700; letter-spacing:1px; padding:5px 12px; border-radius:20px; <?php echo $on ? 'background:var(--ecm-green-subtle); color:var(--ecm-green);' : 'background:rgba(255,255,255,0.05); color:var(--ecm-grey-mid);'; ?>">
                        <?php echo $on ? 'ON' : 'OFF'; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <p style="text-align:center; color:var(--ecm-grey-dark); font-size:13px; margin-top:20px;">* الأسماء والحالات هنا للعرض فقط — تتحكم فيها بالكامل من التطبيق.</p>
    </section>

    <!-- Highlight row -->
    <section class="ecm-container" style="padding-bottom: 72px;">
        <div class="ecm-sys-row">
            <div class="ecm-media-ph" style="aspect-ratio:4/3;">
                <span class="ecm-mph-icon">🎛️</span>
                <span class="ecm-mph-label">صورة — لوحة المفاتيح</span>
            </div>
            <div>
                <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">SIMPLE · بساطة وقوة</span>
                <h3>كل أجهزتك في مكان واحد</h3>
                <p>بدل ما تلفّ على كل جهاز لوحده، تحكم في كل المعدّات من شاشة واحدة منظّمة — إضاءة، مراوح، شواحن، كاميرات... أي حاجة متوصّلة بالنظام، تشغّلها وتقفلها وانت قاعد مكانك.</p>
                <ul>
                    <li>✅ ٨ مخارج مستقلة</li>
                    <li>✅ تحكم فردي أو جماعي</li>
                    <li>✅ حالة كل مفتاح واضحة فورًا</li>
                    <li>✅ أسماء مخصّصة لكل جهاز</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">FEATURES · المزايا</span>
            <h2 class="ecm-section-title">بسيطة وقوية</h2>
        </header>
        <div class="ecm-feat-grid-3">
            <?php foreach ( $features as $f ) : ?>
                <div class="ecm-feat-card ecm-axis-card">
                    <div class="ecm-media-ph">
                        <span class="ecm-mph-icon"><?php echo esc_html( $f[0] ); ?></span>
                        <span class="ecm-mph-label">صورة توضيحية</span>
                    </div>
                    <h3><?php echo esc_html( $f[1] ); ?></h3>
                    <p class="ecm-feat-text"><?php echo esc_html( $f[2] ); ?></p>
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
        'تشغّل وتقفل ٨ أجهزة مستقلة',
        'تتحكم في كل جهاز لوحده',
        'تشغّل أو تقفل الكل دفعة واحدة',
        'تشوف حالة كل مفتاح فورًا',
        'تسمّي كل مفتاح باسم جهازه',
        'تتحكم من أي مكان لاسلكيًا',
    ];
    $tuts = [
        [ '🔌', 'توصيل الأجهزة', [ 'وصّل كل جهاز بمخرج من الثمانية', 'تأكد من التوصيل الصحيح', 'شغّل النظام' ] ],
        [ '✏️', 'تسمية المفاتيح', [ 'اضغط مطوّل على المفتاح', 'اكتب اسم الجهاز المتوصّل', 'احفظ الاسم' ] ],
        [ '👆', 'التحكم', [ 'اضغط المفتاح للتشغيل أو الإيقاف', 'أو شغّل/اقفل الكل مرة واحدة', 'تابع الحالة من الألوان' ] ],
    ];
    $videos = [
        [ 'شرح لوحة المفاتيح', '', '2:50' ],
        [ 'توصيل وتسمية الأجهزة', '', '3:30' ],
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
        </header>
        <div class="ecm-feat-grid-3">
            <?php foreach ( $videos as $v ) { ecm_render_video_card( $v[0], $v[1], $v[2] ); } ?>
        </div>
        <p style="text-align:center; color:var(--ecm-grey-dark); font-size:13px; margin-top:20px;">* أماكن الفيديوهات جاهزة — استبدلها بروابط YouTube بتاعتك.</p>
    </section>

    <!-- CTA -->
    <section class="ecm-container" style="padding-bottom: 80px;">
        <div class="ecm-sys-cta">
            <h2 class="ecm-section-title" style="margin-bottom:14px;">عايز تتحكم في كل معدّاتك بسهولة؟</h2>
            <p style="max-width:520px; margin:0 auto 26px; color:var(--ecm-grey-mid); font-size:16px; line-height:1.9;">
                تواصل معانا ونساعدك تجهّز لوحة المفاتيح المناسبة لمعدّاتك.
            </p>
            <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-primary">تواصل معنا</a>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost">الصفحة الرئيسية</a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
