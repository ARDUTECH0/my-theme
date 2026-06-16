<?php
/**
 * ECM Theme — 404.php
 * صفحة الخطأ 404 — الصفحة غير موجودة
 *
 * @package ecm-theme
 */

get_header();

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) :
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 120px; text-align: center;">

        <!-- Eyebrow -->
        <span class="ecm-eyebrow" style="margin-bottom: 24px;">
            ERROR 404
        </span>

        <!-- Big Number -->
        <h1 style="
            font-family: 'Orbitron', 'Cairo', sans-serif;
            font-size: clamp(80px, 15vw, 180px);
            font-weight: 900;
            color: var(--ecm-white);
            line-height: 1;
            margin-bottom: 16px;
            opacity: 0.15;
        ">404</h1>

        <!-- Message -->
        <h2 class="ecm-section-title" style="margin-bottom: 16px;">
            الصفحة غير موجودة
        </h2>
        <p style="
            max-width: 480px;
            margin: 0 auto 40px;
            color: var(--ecm-grey-mid);
            font-size: 16px;
            line-height: 1.8;
        ">
            الصفحة اللي بتدوّر عليها مش موجودة — ممكن تكون اتنقلت أو اتحذفت.
        </p>

        <!-- Actions -->
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-primary">
                الصفحة الرئيسية
            </a>
            <button class="ecm-btn-ghost" onclick="history.back()">
                ارجع للخلف
            </button>
        </div>

    </div>
</main>

<?php endif; // elementor single location (404) ?>

<?php get_footer(); ?>
