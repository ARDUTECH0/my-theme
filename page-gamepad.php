<?php
/**
 * Template Name: 🎮 ECM — التحكم بذراع الألعاب
 *
 * صفحة تشرح كل زر في ذراع التحكم (Gamepad) بيعمل إيه.
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

// مجموعات الأزرار: [ المفتاح, العنوان, الوصف ]
$btn_groups = [
    'أزرار التشغيل' => [
        [ 'Y', 'تشغيل', 'يشغّل المسار المسجّل ويبدأ الحركة تلقائيًا.' ],
        [ 'X', 'رجوع', 'يرجّع على نفس المسار لنقطة البداية.' ],
        [ 'A', 'إيقاف فوري', 'يوقّف كل الحركة في الحال مع صوت تنبيه.' ],
        [ 'B', 'تسجيل', 'يبدأ أو يوقف تسجيل خطوات المسار.' ],
    ],
    'العصي والأذرع' => [
        [ '🕹️', 'العصا اليسرى', 'تحرّك المركبة للأمام والخلف حسب اتجاه العصا.' ],
        [ '🕹️', 'العصا اليمنى', 'توجّه العجلات يمين وشمال.' ],
        [ 'L1 / R1', 'تبديل التوجيه', 'تنقّل بين أوضاع التوجيه الأربعة.' ],
        [ 'L2 / R2', 'السرعة', 'تزوّد أو تقلّل سرعة الحركة.' ],
    ],
    'الأسهم والأزرار الخاصة' => [
        [ '← →', 'تبديل المستخدم', 'تنقّل بين الأوضاع المحفوظة (المستخدمين).' ],
        [ '↑ ↓', 'الزاوية', 'تزوّد أو تقلّل زاوية الذراع/الرافعة.' ],
        [ 'START', 'تحميل الإعداد', 'يحمّل قيم المستخدم الحالي ويجهّزه.' ],
        [ 'SELECT', 'وضع الإعداد', 'يفعّل أو يعطّل وضع الإعداد (Config).' ],
        [ 'MODE', 'تبديل الشاشة', 'ينقلك لشاشة التحكم في الكاميرا.' ],
        [ '⊙', 'رجوع للصفر', 'الضغط على العصا يرجّع التوجيه/العجلات للصفر.' ],
    ],
];

$steps = [
    [ 'وصّل الذراع', 'اربط ذراع التحكم بالنظام — بيتعرّف عليه تلقائيًا.' ],
    [ 'اتأكد من الوضع', 'فعّل وضع الإعداد عشان تفتح كل أزرار التحكم.' ],
    [ 'تحكّم بحرية', 'استخدم العصي والأزرار للحركة والتوجيه والتسجيل بسلاسة.' ],
];

$videos = [
    [ 'شرح أزرار ذراع التحكم', '', '4:15' ],
    [ 'التحكم في المركبة بالذراع', '', '5:30' ],
];
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 40px 0;">
        <?php if ( function_exists( 'ecm_system_pages_nav' ) ) ecm_system_pages_nav(); ?>
    </div>

    <!-- Hero -->
    <section class="ecm-container" style="padding-block: 48px 32px; text-align: center;">
        <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:18px;">GAMEPAD · ذراع التحكم</span>
        <h1 class="ecm-section-title" style="margin-bottom:18px;">تحكم كامل بذراع الألعاب</h1>
        <p style="max-width:680px; margin:0 auto 28px; color:var(--ecm-grey-mid); font-size:17px; line-height:1.95;">
            استخدم ذراع تحكم احترافي عشان تتحكم في المركبة والكاميرا بإحساس طبيعي وسلس.
            كل زرار ليه وظيفة واضحة — حركة، توجيه، تشغيل، تسجيل — كله تحت إيدك في لحظة.
        </p>
        <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-primary">تواصل معنا</a>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost">كل الأنظمة</a>
        </div>
    </section>

    <section class="ecm-container" style="padding-bottom: 56px;">
        <div class="ecm-media-ph ecm-sys-hero-media">
            <span class="ecm-mph-icon">🎮</span>
            <span class="ecm-mph-label">صورة — ذراع التحكم وخريطة الأزرار</span>
        </div>
    </section>

    <!-- Button map -->
    <?php foreach ( $btn_groups as $group_title => $buttons ) : ?>
        <section class="ecm-container" style="padding-bottom: 56px;">
            <header style="text-align:center; margin-bottom:36px;">
                <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:12px;">MAP · خريطة الأزرار</span>
                <h2 class="ecm-section-title"><?php echo esc_html( $group_title ); ?></h2>
            </header>
            <div class="ecm-feat-grid-3">
                <?php foreach ( $buttons as $b ) : ?>
                    <div class="ecm-ctrl-card ecm-gp-card">
                        <span class="ecm-gp-key"><?php echo esc_html( $b[0] ); ?></span>
                        <div style="flex:1; min-width:0;">
                            <div class="gp-title"><?php echo esc_html( $b[1] ); ?></div>
                            <div class="gp-desc"><?php echo esc_html( $b[2] ); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <!-- How to connect -->
    <section class="ecm-container" style="padding-bottom: 64px;">
        <header style="text-align:center; margin-bottom:44px;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">SETUP · طريقة التوصيل</span>
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
            <h2 class="ecm-section-title" style="margin-bottom:14px;">جاهز تتحكم بذراعك؟</h2>
            <p style="max-width:520px; margin:0 auto 26px; color:var(--ecm-grey-mid); font-size:16px; line-height:1.9;">
                تواصل معانا ونساعدك تجهّز نظام التحكم بذراع الألعاب لمعدّاتك.
            </p>
            <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="ecm-btn-primary">تواصل معنا</a>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost">الصفحة الرئيسية</a>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
