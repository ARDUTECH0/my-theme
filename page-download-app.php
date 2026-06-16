<?php
/**
 * ECM Theme — page-download-app.php
 * Template Name: 📱 صفحة تحميل التطبيق
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

// App settings from Customizer
$app_name    = esc_html( get_theme_mod( 'ecm_app_name', 'ECM Controller' ) );
$app_desc    = esc_html( get_theme_mod( 'ecm_app_desc', 'تطبيق التحكم الاحترافي بالكاميرات — تحكم لاسلكي، برمجة مسارات، ومراقبة مباشرة.' ) );
$app_version = esc_html( get_theme_mod( 'ecm_app_version', '2.0.0' ) );
$app_size    = esc_html( get_theme_mod( 'ecm_app_size', '45 MB' ) );

// Fetch apps managed in Admin Dashboard
$admin_apps = get_option( 'ecm_apps_data', [] );

$features = [
    [ '📡', 'تحكم لاسلكي — Wireless Control', 'تحكم كامل بالكاميرا عبر WiFi أو Bluetooth بتأخير أقل من 10ms.' ],
    [ '🎬', 'برمجة المسارات — Track Programming', 'سجّل مسارات حركة وشغّلها بضغطة زر مع تحكم في السرعة.' ],
    [ '📺', 'بث مباشر — Live Feed', 'شاهد ما تصوره الكاميرا مباشرة على شاشة جهازك.' ],
    [ '🔋', 'مراقبة البطارية — Battery Monitor', 'متابعة مستوى بطارية الجهاز والكاميرا في الوقت الحقيقي.' ],
    [ '⚙️', 'إعدادات متقدمة — Advanced Settings', 'تحكم في كل إعداد — ISO، White Balance، Shutter Speed.' ],
    [ '☁️', 'نسخ احتياطي — Cloud Backup', 'حفظ إعداداتك ومساراتك على السحابة واسترجاعها في أي وقت.' ],
];
?>

<main id="ecm-main" class="ecm-main-content" role="main">

    <!-- ═══════════════════════════════════════
         APP HERO
         ═══════════════════════════════════════ -->
    <section class="ecm-app-hero">
        <div class="ecm-container" style="padding-block: 140px 60px; text-align: center;">
            <span class="ecm-eyebrow">DOWNLOAD APPLICATION</span>

            <!-- App Icon -->
            <div class="ecm-app-icon-wrap">
                <div class="ecm-app-icon">📱</div>
            </div>

            <h1 class="ecm-hero-title" style="font-size: clamp(32px, 5vw, 56px);">
                <span class="ecm-green"><?php echo $app_name; ?></span>
            </h1>

            <p class="ecm-hero-desc" style="max-width: 560px; margin: 16px auto 0;">
                <?php echo $app_desc; ?>
            </p>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
         DOWNLOAD GRID (DYNAMIC FROM ADMIN PANEL)
         ═══════════════════════════════════════ -->
    <section class="ecm-section" id="app-downloads" style="padding-top: 0;">
        <div class="ecm-container">
            <span class="ecm-eyebrow">CHOOSE YOUR PLATFORM</span>
            <h2 class="ecm-section-title">تحميل التطبيق لجميع الأجهزة</h2>
            <p class="ecm-section-desc" style="margin-bottom: 48px;">اختر المنصة الخاصة بك للتحميل المباشر أو الانتقال للمتجر</p>

            <div class="ecm-app-download-grid">
                <?php
                $has_visible_apps = false;
                if ( ! empty( $admin_apps ) ) :
                    foreach ( $admin_apps as $i => $app ) :
                        if ( empty( $app['name'] ) ) continue;
                        $has_visible_apps = true;

                        // Platform details
                        $platform_icon = '📱';
                        $platform_label = 'App';
                        if ( $app['platform'] === 'android' ) {
                            $platform_icon = '🤖';
                            $platform_label = 'Android (APK)';
                        } elseif ( $app['platform'] === 'ios' ) {
                            $platform_icon = '🍎';
                            $platform_label = 'iOS (IPA)';
                        } elseif ( $app['platform'] === 'windows' ) {
                            $platform_icon = '💻';
                            $platform_label = 'Windows (EXE)';
                        } elseif ( $app['platform'] === 'mac' ) {
                            $platform_icon = '🖥';
                            $platform_label = 'macOS (DMG)';
                        }

                        // Status badge style
                        $status_class = 'ecm-badge-green';
                        $status_text = '🟢 مستقر — Stable';
                        if ( $app['status'] === 'beta' ) {
                            $status_class = 'ecm-badge-orange';
                            $status_text = '🟡 تجريبي — Beta';
                        } elseif ( $app['status'] === 'coming' ) {
                            $status_class = 'ecm-badge-blue';
                            $status_text = '🔵 قريباً — Coming Soon';
                        } elseif ( $app['status'] === 'deprecated' ) {
                            $status_class = 'ecm-badge-red';
                            $status_text = '🔴 قديم — Deprecated';
                        }
                ?>
                        <div class="ecm-app-dl-card <?php echo $app['status'] === 'coming' ? 'ecm-app-coming' : ''; ?>">
                            <div class="ecm-app-card-top">
                                <div class="ecm-app-platform-icon"><?php echo $platform_icon; ?></div>
                                <div class="ecm-app-details">
                                    <h3 class="ecm-app-card-name"><?php echo esc_html( $app['name'] ); ?></h3>
                                    <span class="ecm-app-platform-lbl"><?php echo $platform_label; ?></span>
                                </div>
                            </div>

                            <div class="ecm-app-card-meta">
                                <?php if ( ! empty( $app['version'] ) ) : ?>
                                    <span class="ecm-app-meta-item">📦 v<?php echo esc_html( $app['version'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $app['size'] ) ) : ?>
                                    <span class="ecm-app-meta-item">💾 <?php echo esc_html( $app['size'] ); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="ecm-app-status-wrap">
                                <span class="ecm-admin-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </div>

                            <?php if ( ! empty( $app['notes'] ) ) : ?>
                                <p class="ecm-app-notes"><?php echo esc_html( $app['notes'] ); ?></p>
                            <?php endif; ?>

                            <div class="ecm-app-action-btn">
                                <?php if ( $app['status'] === 'coming' ) : ?>
                                    <button class="ecm-btn-ghost ecm-btn-block" disabled style="opacity: 0.6; cursor: not-allowed;">
                                        قريباً — Coming Soon
                                    </button>
                                <?php elseif ( ! empty( $app['link'] ) ) : ?>
                                    <a href="<?php echo esc_url( $app['link'] ); ?>" target="_blank" rel="noopener noreferrer" class="ecm-btn-primary ecm-btn-block" download>
                                        ⬇️ تحميل التطبيق — Download App
                                    </a>
                                <?php else : ?>
                                    <button class="ecm-btn-ghost ecm-btn-block" disabled style="opacity: 0.5;">
                                        الرابط غير متوفر — No Link
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                <?php
                    endforeach;
                endif;

                if ( ! $has_visible_apps ) :
                ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; border: 1px dashed var(--ecm-border); border-radius: 12px;">
                        <p style="color: var(--ecm-grey-mid); font-size: 15px;">
                            ⚙️ لم يتم العثور على تطبيقات مضافة. يرجى إضافة التطبيقات من لوحة التحكم.
                        </p>
                        <p style="font-family: 'Orbitron', 'Cairo', sans-serif; font-size: 11px; color: var(--ecm-grey-dark);">
                            Please configure apps in the WordPress Admin Panel > ⚙️ ECM Dashboard > App Manager.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
         APP FEATURES
         ═══════════════════════════════════════ -->
    <section class="ecm-section" id="app-features" style="border-top: 1px solid var(--ecm-border);">
        <div class="ecm-container">
            <span class="ecm-eyebrow">APP FEATURES</span>
            <h2 class="ecm-section-title">مميزات التطبيق — Key Features</h2>
            <p class="ecm-section-desc">كل ما تحتاجه للتحكم الاحترافي في تطبيق واحد</p>

            <div class="ecm-feat-grid-3">
                <?php foreach ( $features as $f ) : ?>
                    <div class="ecm-feat-card">
                        <span class="ecm-feat-icon"><?php echo $f[0]; ?></span>
                        <span class="ecm-feat-title"><?php echo esc_html( $f[1] ); ?></span>
                        <p class="ecm-feat-text"><?php echo esc_html( $f[2] ); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
         REQUIREMENTS
         ═══════════════════════════════════════ -->
    <section class="ecm-section" style="border-top: 1px solid var(--ecm-border);">
        <div class="ecm-container">
            <span class="ecm-eyebrow">SYSTEM REQUIREMENTS</span>
            <h2 class="ecm-section-title">المتطلبات — Requirements</h2>

            <div class="ecm-specs-wrap" style="max-width: 600px; margin-inline: auto;">
                <div class="ecm-spec-row"><span class="ecm-spec-label">🤖 Android</span><span class="ecm-spec-val">Android 8.0 or higher</span></div>
                <div class="ecm-spec-row"><span class="ecm-spec-label">🍎 iOS</span><span class="ecm-spec-val">iOS 14.0 or higher</span></div>
                <div class="ecm-spec-row"><span class="ecm-spec-label">📡 Connection</span><span class="ecm-spec-val">WiFi 5Ghz or Bluetooth 5.0+</span></div>
                <div class="ecm-spec-row ecm-spec-hl"><span class="ecm-spec-label">💵 Price</span><span class="ecm-spec-val">Free / مجاني</span></div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
         BOTTOM CTA
         ═══════════════════════════════════════ -->
    <section class="ecm-cta-section">
        <div class="ecm-cta-inner">
            <div class="ecm-cta-glow"></div>
            <span class="ecm-eyebrow">START NOW</span>
            <h2 class="ecm-section-title" style="margin-bottom: var(--ecm-space-md);">ابدأ التحكم في كاميرتك الآن</h2>
            <p class="ecm-section-desc" style="margin-inline: auto; margin-bottom: var(--ecm-space-xl);">
                حمّل التطبيق الرسمي واستمتع بحرية الإبداع اللاسلكي
            </p>
            <div class="ecm-cta-btns">
                <a href="<?php echo esc_url( home_url('/') ); ?>" class="ecm-btn-ghost ecm-btn-lg">
                    ← الرجوع للرئيسية — Home Page
                </a>
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
