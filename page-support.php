<?php
/**
 * Template Name: 🆘 ECM — صفحة الدعم
 *
 * صفحة الدعم الفني — Support
 * روابط الدعم (التوثيق / واتساب / يوتيوب / إيميل) قابلة للتعديل من التخصيص.
 *
 * @package ecm-theme
 */

get_header();

// ── Elementor Pro (Theme Builder): لو في قالب Single → اعرضه ──
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single' ) ) {
    get_footer();
    return;
}

// ── لو الصفحة اتبنت بـ Elementor → اعرض محتواها بعرض كامل ──
if ( function_exists( 'ecm_is_built_with_elementor' ) && ecm_is_built_with_elementor( get_queried_object_id() ) ) {
    ?>
    <main id="ecm-main" class="ecm-main-content" role="main">
        <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
    </main>
    <?php
    get_footer();
    return;
}

// ── روابط الدعم (من التخصيص) ──
$wa    = get_theme_mod( 'ecm_support_whatsapp', '' );
$email = get_theme_mod( 'ecm_support_email', '' );
$yt    = get_theme_mod( 'ecm_support_youtube', 'https://youtube.com/@ArduTechs' );

// رابط صفحة التوثيق
$docs_link = '';
$docs_pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-documentation.php', 'number' => 1 ] );
if ( ! empty( $docs_pages ) ) {
    $docs_link = get_permalink( $docs_pages[0]->ID );
}

$wa_url = $wa ? 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $wa ) : '';
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 72px 88px;">

        <!-- Header -->
        <header style="text-align: center; max-width: 640px; margin: 0 auto 56px;">
            <span class="ecm-eyebrow" style="display: inline-flex; margin-bottom: 18px;">SUPPORT · الدعم الفني</span>
            <h1 class="ecm-section-title" style="margin-bottom: 16px;">
                إزاي نقدر نساعدك؟
            </h1>
            <p style="color: var(--ecm-grey-mid); font-size: 16px; line-height: 1.8;">
                اختَر الطريقة اللي تناسبك — فريق ECM التقني موجود يساعدك في أي وقت.
                <br><span style="font-size: 14px; color: var(--ecm-grey-dark);">Choose the support channel that works best for you.</span>
            </p>
            <div style="width: 56px; height: 3px; background: var(--ecm-green); box-shadow: var(--ecm-shadow-green); margin: 24px auto 0; border-radius: 2px;"></div>
        </header>

        <!-- Support Cards -->
        <div class="ecm-feat-grid-3" style="margin-bottom: 56px;">

            <!-- Documentation -->
            <div class="ecm-feat-card" style="text-align: center;">
                <div style="font-size: 40px; margin-bottom: 16px;">📖</div>
                <h3 style="font-family: 'Cairo', sans-serif; font-size: 18px; font-weight: 700; color: var(--ecm-white); margin-bottom: 10px;">
                    التوثيق والأدلة
                </h3>
                <p class="ecm-feat-text" style="margin-bottom: 22px;">
                    اقرأ دليل ECM الكامل — الإعداد، الكاليبريشن، التحكم وحل المشاكل.
                    <br><span style="font-size: 12px; color: var(--ecm-grey-dark);">Read the full ECM guide</span>
                </p>
                <a href="<?php echo esc_url( $docs_link ?: home_url( '/' ) ); ?>" class="ecm-btn-primary" style="width: 100%; justify-content: center;">
                    عرض التوثيق
                </a>
            </div>

            <!-- WhatsApp -->
            <div class="ecm-feat-card" style="text-align: center;">
                <div style="font-size: 40px; margin-bottom: 16px;">💬</div>
                <h3 style="font-family: 'Cairo', sans-serif; font-size: 18px; font-weight: 700; color: var(--ecm-white); margin-bottom: 10px;">
                    دعم واتساب
                </h3>
                <p class="ecm-feat-text" style="margin-bottom: 22px;">
                    كلّم فريقنا التقني مباشرة واحصل على رد سريع.
                    <br><span style="font-size: 12px; color: var(--ecm-grey-dark);">Chat with our technical team</span>
                </p>
                <?php if ( $wa_url ) : ?>
                    <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="ecm-btn-primary" style="width: 100%; justify-content: center;">
                        افتح واتساب
                    </a>
                <?php else : ?>
                    <span class="ecm-btn-ghost" style="width: 100%; justify-content: center; opacity: 0.6; cursor: default;">
                        قريبًا
                    </span>
                <?php endif; ?>
            </div>

            <!-- YouTube -->
            <div class="ecm-feat-card" style="text-align: center;">
                <div style="font-size: 40px; margin-bottom: 16px;">▶️</div>
                <h3 style="font-family: 'Cairo', sans-serif; font-size: 18px; font-weight: 700; color: var(--ecm-white); margin-bottom: 10px;">
                    شروحات يوتيوب
                </h3>
                <p class="ecm-feat-text" style="margin-bottom: 22px;">
                    اتفرّج على فيديوهات الإعداد والاستخدام خطوة بخطوة.
                    <br><span style="font-size: 12px; color: var(--ecm-grey-dark);">Watch setup & usage videos</span>
                </p>
                <a href="<?php echo esc_url( $yt ); ?>" target="_blank" rel="noopener" class="ecm-btn-ghost" style="width: 100%; justify-content: center;">
                    قناة Ardu Tech
                </a>
            </div>

        </div>

        <!-- Email Section -->
        <div class="ecm-ctrl-card" style="text-align: center; padding: 40px 32px;">
            <span class="ecm-eyebrow" style="display: inline-flex; margin-bottom: 14px;">EMAIL · للاستفسارات الرسمية</span>
            <h2 style="font-family: 'Cairo', sans-serif; font-size: 22px; font-weight: 700; color: var(--ecm-white); margin-bottom: 12px;">
                تواصل بالإيميل
            </h2>
            <?php if ( $email ) : ?>
                <a href="mailto:<?php echo esc_attr( $email ); ?>" style="display: inline-block; font-family: 'Orbitron', 'Cairo', sans-serif; font-size: 18px; color: var(--ecm-green); letter-spacing: 1px;">
                    <?php echo esc_html( $email ); ?>
                </a>
            <?php else : ?>
                <p style="color: var(--ecm-grey-mid); font-size: 15px;">سيتم إضافة الإيميل قريبًا.</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php get_footer(); ?>
