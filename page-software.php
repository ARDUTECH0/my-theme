<?php
/**
 * ECM Theme — page-software.php
 * Template Name: 🔧 صفحة السوفت وير والتحديثات
 *
 * صفحة تحميل السوفت وير + التحديثات + استكشاف الأخطاء
 *
 * @package ecm-theme
 */

get_header();

// ── Elementor: لو الصفحة مبنية بـ Elementor، اعرض محتواها بالكامل (Full Width) بدل التصميم الجاهز ──
if ( ecm_is_built_with_elementor( get_queried_object_id() ) ) {
    ?>
    <main id="ecm-main" class="ecm-main-content" role="main">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </main>
    <?php
    get_footer();
    return;
}

// Customizer values
$sw_title      = esc_html( get_theme_mod( 'ecm_sw_title', 'السوفت وير والتحديثات' ) );
$sw_desc       = esc_html( get_theme_mod( 'ecm_sw_desc', 'حمّل آخر إصدار من السوفت وير — تحديثات الأداء وإصلاح المشاكل' ) );
$sw_version    = esc_html( get_theme_mod( 'ecm_sw_version', '2.0.0' ) );
$sw_date       = esc_html( get_theme_mod( 'ecm_sw_date', '2024-06-01' ) );
$sw_size       = esc_html( get_theme_mod( 'ecm_sw_size', '128 MB' ) );
$sw_link       = get_theme_mod( 'ecm_sw_download_link', '' );
$sw_prev_link  = get_theme_mod( 'ecm_sw_prev_link', '' );
$sw_prev_ver   = esc_html( get_theme_mod( 'ecm_sw_prev_version', '1.9.5' ) );

// Changelog
$changelog = [
    1 => get_theme_mod( 'ecm_sw_log_1', 'تحسين استجابة التحكم اللاسلكي — تأخير أقل من 8ms' ),
    2 => get_theme_mod( 'ecm_sw_log_2', 'إصلاح مشكلة انقطاع البلوتوث عند المسافات البعيدة' ),
    3 => get_theme_mod( 'ecm_sw_log_3', 'إضافة وضع التصوير البطيء Slow Motion 240fps' ),
    4 => get_theme_mod( 'ecm_sw_log_4', 'تحسين عمر البطارية بنسبة 15%' ),
    5 => get_theme_mod( 'ecm_sw_log_5', 'دعم كاميرات Sony A7S III و Canon R5 C' ),
    6 => get_theme_mod( 'ecm_sw_log_6', '' ),
];

// Troubleshooting
$troubles = [
    [
        'q' => 'الجهاز مش بيتعرف على الكاميرا',
        'a' => 'تأكد إن الكاميرا في وضع Remote Control. أعد تشغيل الجهازين. لو المشكلة مستمرة، حدّث السوفت وير لآخر إصدار.',
    ],
    [
        'q' => 'السوفت وير وقع أثناء التشغيل',
        'a' => 'حمّل آخر إصدار من السوفت وير من هذه الصفحة. لو المشكلة تكررت، حمّل الإصدار السابق المستقر واتواصل مع الدعم الفني.',
    ],
    [
        'q' => 'التحديث فشل أو توقف في النص',
        'a' => 'لا تفصل الجهاز! اضغط مع الاستمرار على زر Reset لمدة 10 ثواني. الجهاز هيرجع للإصدار السابق تلقائياً.',
    ],
    [
        'q' => 'البلوتوث أو الواي فاي مش شغال بعد التحديث',
        'a' => 'روح إعدادات الجهاز > إعادة ضبط الشبكة. لو ما اشتغلش، حمّل الإصدار السابق من السوفت وير.',
    ],
];
?>

<main id="ecm-main" class="ecm-main-content" role="main">

    <!-- ═══════════════════════════════════════
         SOFTWARE HERO
         ═══════════════════════════════════════ -->
    <section class="ecm-app-hero ecm-sw-hero">
        <div class="ecm-container" style="padding-block: 120px 60px; text-align: center;">
            <span class="ecm-eyebrow">⚙️ FIRMWARE & SOFTWARE</span>
            <h1 class="ecm-hero-title" style="font-size: clamp(28px, 5vw, 48px);">
                <span class="ecm-green"><?php echo $sw_title; ?></span>
            </h1>
            <p class="ecm-hero-desc" style="max-width: 560px; margin: 16px auto 0;">
                <?php echo $sw_desc; ?>
            </p>

            <!-- Version Badge -->
            <div class="ecm-sw-version-badge">
                <span class="ecm-sw-ver-label">LATEST VERSION</span>
                <span class="ecm-sw-ver-num"><?php echo $sw_version; ?></span>
                <span class="ecm-sw-ver-dot"></span>
                <span class="ecm-sw-ver-status">STABLE</span>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
         DOWNLOAD SECTION
         ═══════════════════════════════════════ -->
    <section class="ecm-section" id="sw-download">
        <div class="ecm-container">
            <div class="ecm-sw-download-grid">

                <!-- Latest Version Card -->
                <div class="ecm-sw-card ecm-sw-card-main">
                    <div class="ecm-sw-card-header">
                        <span class="ecm-sw-card-badge ecm-sw-badge-latest">✅ آخر إصدار — Latest</span>
                    </div>
                    <h2 class="ecm-sw-card-title">ECM Firmware v<?php echo $sw_version; ?></h2>
                    <div class="ecm-sw-card-meta">
                        <span>📅 <?php echo $sw_date; ?></span>
                        <span>📦 <?php echo $sw_size; ?></span>
                        <span>🟢 مستقر — Stable</span>
                    </div>

                    <?php if ( $sw_link ) : ?>
                        <a href="<?php echo esc_url( $sw_link ); ?>" class="ecm-btn-primary ecm-btn-lg ecm-sw-dl-btn" download>
                            ⬇️ تحميل السوفت وير — Download
                        </a>
                    <?php else : ?>
                        <p class="ecm-sw-no-link">⚙️ حدّد رابط التحميل من <strong>المظهر > تخصيص > 🔧 السوفت وير</strong></p>
                    <?php endif; ?>

                    <!-- Changelog -->
                    <div class="ecm-sw-changelog">
                        <h3 class="ecm-sw-changelog-title">📋 ما الجديد — Changelog</h3>
                        <ul class="ecm-sw-changelog-list">
                            <?php foreach ( $changelog as $log ) :
                                if ( empty( $log ) ) continue;
                            ?>
                                <li><?php echo esc_html( $log ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Previous Version Card -->
                <div class="ecm-sw-card ecm-sw-card-prev">
                    <div class="ecm-sw-card-header">
                        <span class="ecm-sw-card-badge ecm-sw-badge-prev">📦 الإصدار السابق — Previous</span>
                    </div>
                    <h3 class="ecm-sw-card-title" style="font-size: 18px;">ECM Firmware v<?php echo $sw_prev_ver; ?></h3>
                    <p style="color: var(--ecm-grey-mid); font-size: 13px; line-height: 1.7; margin: 12px 0 24px;">
                        لو الإصدار الجديد فيه مشكلة — حمّل الإصدار السابق المستقر<br>
                        <span style="font-family: 'Orbitron', 'Cairo', sans-serif; font-size: 11px; color: var(--ecm-grey-dark);">
                            If the latest version has issues — use this stable fallback
                        </span>
                    </p>
                    <?php if ( $sw_prev_link ) : ?>
                        <a href="<?php echo esc_url( $sw_prev_link ); ?>" class="ecm-btn-ghost ecm-btn-lg ecm-sw-dl-btn" download>
                            ⬇️ تحميل v<?php echo $sw_prev_ver; ?>
                        </a>
                    <?php else : ?>
                        <p class="ecm-sw-no-link">⚙️ حدّد رابط الإصدار السابق من التخصيص</p>
                    <?php endif; ?>

                    <!-- How to flash -->
                    <div class="ecm-sw-howto">
                        <h4 style="color: var(--ecm-white); font-size: 14px; margin-bottom: 12px;">🔄 طريقة التحديث — How to Update</h4>
                        <ol class="ecm-sw-steps">
                            <li>حمّل ملف السوفت وير على الكمبيوتر</li>
                            <li>وصّل جهاز ECM بكابل USB</li>
                            <li>انقل الملف لمجلد <code>UPDATE</code> على الجهاز</li>
                            <li>افصل الكابل وأعد تشغيل الجهاز</li>
                            <li>التحديث هيبدأ تلقائياً — لا تفصل الجهاز!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
         TROUBLESHOOTING
         ═══════════════════════════════════════ -->
    <section class="ecm-section" id="sw-troubleshoot">
        <div class="ecm-container">
            <span class="ecm-eyebrow">🛠 TROUBLESHOOTING</span>
            <h2 class="ecm-section-title">استكشاف الأخطاء وإصلاحها</h2>
            <p class="ecm-section-desc">لو واجهت أي مشكلة — هنا الحلول الشائعة</p>

            <div class="ecm-sw-faq">
                <?php foreach ( $troubles as $idx => $t ) : ?>
                <div class="ecm-sw-faq-item" data-faq="<?php echo $idx; ?>">
                    <button class="ecm-sw-faq-q" aria-expanded="false">
                        <span class="ecm-sw-faq-icon">❓</span>
                        <span><?php echo esc_html( $t['q'] ); ?></span>
                        <span class="ecm-sw-faq-arrow">▼</span>
                    </button>
                    <div class="ecm-sw-faq-a">
                        <p><?php echo esc_html( $t['a'] ); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
         EMERGENCY CONTACT
         ═══════════════════════════════════════ -->
    <section class="ecm-cta-section">
        <div class="ecm-cta-inner">
            <div class="ecm-cta-glow"></div>
            <span class="ecm-eyebrow">🚨 EMERGENCY SUPPORT</span>
            <h2 class="ecm-section-title" style="margin-bottom: var(--ecm-space-md);">محتاج مساعدة فورية؟</h2>
            <p class="ecm-section-desc" style="margin-inline: auto; margin-bottom: var(--ecm-space-xl);">
                لو السوفت وير وقع أو الجهاز مش شغال — تواصل مع الدعم الفني فوراً
            </p>
            <div class="ecm-cta-btns">
                <?php $wa = get_theme_mod( 'ecm_whatsapp', '' ); if ( $wa ) : ?>
                    <a href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo urlencode('🚨 مشكلة في السوفت وير — ECM Firmware Issue'); ?>" target="_blank" class="ecm-btn-primary ecm-btn-lg">
                        💬 واتساب الدعم الفني — Technical Support
                    </a>
                <?php endif; ?>
                <?php $email = get_theme_mod( 'ecm_email', '' ); if ( $email ) : ?>
                    <a href="mailto:<?php echo esc_attr( $email ); ?>?subject=ECM Firmware Issue" class="ecm-btn-ghost ecm-btn-lg">
                        ✉️ إرسال تقرير — Send Report
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════
         POST CONTENT — محتوى إضافي قابل للتعديل من المحرّر
         يظهر فقط لو فيه محتوى مكتوب — ويُحرّر بالكامل عبر Elementor
         ═══════════════════════════════════════ -->
    <?php while ( have_posts() ) : the_post(); if ( trim( get_the_content() ) !== '' ) : ?>
        <section class="ecm-section">
            <div class="ecm-container ecm-entry-content" style="font-size: var(--ecm-text-base); line-height: 1.85; color: var(--ecm-grey-light);">
                <?php the_content(); ?>
            </div>
        </section>
    <?php endif; endwhile; ?>

</main>

<?php get_footer(); ?>
