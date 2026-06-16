<?php
/**
 * ECM Theme — page-upload-software.php
 * Template Name: 🔧 صفحة رفع السوفت وير المباشر
 *
 * صفحة توجيهية وتعليمات لرفع وتحديث السوفت وير عبر المتصفح
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
$title       = esc_html( get_theme_mod( 'ecm_upload_page_title', 'أداة رفع وتحديث السوفت وير المباشر' ) );
$title_en    = esc_html( get_theme_mod( 'ecm_upload_page_title_en', 'Direct Firmware Flasher & Uploader' ) );
$desc        = esc_html( get_theme_mod( 'ecm_upload_page_desc', 'لتحديث السوفت وير أو رفعه مباشرة إلى جهازك، يرجى الانتقال إلى الموقع المخصص للرفع.' ) );
$desc_en     = esc_html( get_theme_mod( 'ecm_upload_page_desc_en', 'To flash or upload the software directly to your device, please proceed to the dedicated web utility.' ) );
$btn_text    = esc_html( get_theme_mod( 'ecm_upload_btn_text', 'انتقل إلى موقع الرفع والتحديث 🚀' ) );
$btn_text_en = esc_html( get_theme_mod( 'ecm_upload_btn_text_en', 'Go to Web Flasher Website 🚀' ) );
$target_url  = esc_url( get_theme_mod( 'ecm_upload_target_url', 'https://esp-web-tools.github.io/' ) );

$steps_ar = [
    'وصّل جهاز ECM بالكمبيوتر الخاص بك باستخدام كابل USB ملائم.',
    'تأكد من إغلاق أي برامج تحكم أخرى تستخدم نفس المنفذ.',
    'انقر فوق زر الانتقال إلى موقع الرفع الموضح أدناه.',
    'اختر منفذ الاتصال (COM Port) المناسب لجهازك وابدأ الرفع مباشرة.'
];

$steps_en = [
    'Connect your ECM device to your computer using a suitable USB cable.',
    'Make sure to close any other control software using the same serial port.',
    'Click the "Go to Web Flasher Website" button displayed below.',
    'Select the correct connection port (COM Port) and start uploading immediately.'
];
?>

<main id="ecm-main" class="ecm-main-content" role="main">

    <!-- ═══════════════════════════════════════
         UPLOAD HERO & REDIRECT CARD
         ═══════════════════════════════════════ -->
    <section class="ecm-section ecm-upload-section" style="padding-block: 140px 80px;">
        <div class="ecm-container">
            <div class="ecm-upload-card-wrapper">
                
                <!-- Main Glassmorphism Card -->
                <div class="ecm-upload-main-card">
                    <div class="ecm-upload-glow-effect"></div>
                    
                    <!-- Icon / Chip Animation Area -->
                    <div class="ecm-upload-icon-container">
                        <div class="ecm-upload-chip">
                            <span class="ecm-chip-icon">⚡</span>
                        </div>
                        <div class="ecm-upload-pulse-rings">
                            <span class="ring-1"></span>
                            <span class="ring-2"></span>
                        </div>
                    </div>

                    <!-- Bilingual Title -->
                    <h1 class="ecm-upload-main-title">
                        <span class="ecm-green"><?php echo $title; ?></span>
                        <small class="ecm-title-subtitle"><?php echo $title_en; ?></small>
                    </h1>

                    <!-- Bilingual Description -->
                    <p class="ecm-upload-main-desc">
                        <?php echo $desc; ?>
                        <span class="ecm-desc-sub"><?php echo $desc_en; ?></span>
                    </p>

                    <!-- Big CTA Button -->
                    <?php if ( $target_url ) : ?>
                        <div class="ecm-upload-cta-wrap">
                            <a href="<?php echo $target_url; ?>" target="_blank" rel="noopener noreferrer" class="ecm-btn-primary ecm-btn-lg ecm-upload-btn-glow">
                                <span class="ecm-btn-label-ar"><?php echo $btn_text; ?></span>
                                <span class="ecm-btn-label-en"><?php echo $btn_text_en; ?></span>
                            </a>
                        </div>
                    <?php else : ?>
                        <p class="ecm-sw-no-link">⚙️ يرجى إدخال رابط موقع الرفع من <strong>المظهر > تخصيص > السوفت وير</strong></p>
                    <?php endif; ?>

                    <!-- Visual Separator -->
                    <div class="ecm-upload-separator">
                        <span>💡 إرشادات الاتصال — Connection Guide</span>
                    </div>

                    <!-- Steps Section -->
                    <div class="ecm-upload-steps-grid">
                        
                        <!-- Arabic Steps -->
                        <div class="ecm-upload-steps-column ar-steps" dir="rtl">
                            <h3 class="ecm-steps-col-title">خطوات الرفع والتحديث:</h3>
                            <ol class="ecm-steps-list">
                                <?php foreach ( $steps_ar as $i => $step ) : ?>
                                    <li>
                                        <span class="step-num"><?php echo ($i + 1); ?></span>
                                        <p class="step-text"><?php echo $step; ?></p>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>

                        <!-- English Steps -->
                        <div class="ecm-upload-steps-column en-steps" dir="ltr">
                            <h3 class="ecm-steps-col-title">How to upload:</h3>
                            <ol class="ecm-steps-list">
                                <?php foreach ( $steps_en as $i => $step ) : ?>
                                    <li>
                                        <span class="step-num"><?php echo ($i + 1); ?></span>
                                        <p class="step-text"><?php echo $step; ?></p>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════
         TROUBLESHOOTING & SUPPORT
         ═══════════════════════════════════════ -->
    <section class="ecm-section" style="padding-top: 0;">
        <div class="ecm-container" style="text-align: center;">
            <div class="ecm-upload-support-box">
                <span class="ecm-eyebrow">SUPPORT & TROUBLESHOOTING</span>
                <h2 style="color: var(--ecm-white); font-size: 20px; margin-bottom: 12px;">هل تواجه مشكلة في الرفع؟ — Having Trouble?</h2>
                <p style="color: var(--ecm-grey-mid); font-size: 14px; max-width: 600px; margin: 0 auto 24px;">
                    في حال لم يتعرف المتصفح على الجهاز، تأكد من تثبيت تعريفات الـ USB (CH340 / CP210x Drivers) وجرب كابل بيانات آخر.
                    <span style="display: block; font-size: 12px; margin-top: 8px; opacity: 0.8;">
                        If the browser does not recognize the device, verify that you have USB drivers (CH340/CP210x) installed and try a different data cable.
                    </span>
                </p>
                
                <div class="ecm-cta-btns" style="justify-content: center;">
                    <?php $wa = get_theme_mod( 'ecm_whatsapp', '' ); if ( $wa ) : ?>
                        <a href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo urlencode('🚨 لدي مشكلة في رفع السوفت وير — Issue uploading firmware'); ?>" target="_blank" class="ecm-btn-primary">
                            💬 واتساب الدعم — WhatsApp Support
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( home_url('/') ); ?>" class="ecm-btn-ghost">
                        ← العودة للرئيسية — Home Page
                    </a>
                </div>
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
