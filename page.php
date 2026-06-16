<?php
/**
 * ECM Theme — page.php
 * Template for displaying standard static pages.
 *
 * متوافق بالكامل مع Elementor:
 *  - لو الصفحة مبنية بـ Elementor → يعرض المحتوى Full Width بدون قيود
 *  - غير كده → تصميم احترافي افتراضي مع Post Content ( the_content )
 *
 * @package ecm-theme
 */

get_header();

// ── Elementor Pro (Theme Builder): لو في قالب Single للصفحة → اعرضه ──
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single' ) ) {
    get_footer();
    return;
}

// ── Elementor: عرض المحتوى بعرض كامل ──
if ( function_exists( 'ecm_is_built_with_elementor' ) && ecm_is_built_with_elementor( get_queried_object_id() ) ) {
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
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 64px 80px;">

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'ecm-article ecm-page' ); ?>>

                <!-- Page Header -->
                <header class="ecm-entry-header" style="margin-bottom: 40px;">
                    <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:14px;">PAGE</span>
                    <h1 class="ecm-entry-title ecm-section-title" style="margin-bottom: 18px; text-align: start;">
                        <?php the_title(); ?>
                    </h1>
                    <div style="width: 56px; height: 3px; background: var(--ecm-green); border-radius: 2px; box-shadow: var(--ecm-shadow-green);"></div>
                </header>

                <!-- Featured Image -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="ecm-post-thumbnail" style="margin-bottom: 40px; border-radius: var(--ecm-radius-md); overflow: hidden; border: 1px solid var(--ecm-border); box-shadow: var(--ecm-shadow-md);">
                        <?php the_post_thumbnail( 'large', [ 'loading' => 'eager', 'style' => 'width:100%; height:auto; display:block;' ] ); ?>
                    </div>
                <?php endif; ?>

                <!-- Content ( Post Content / Elementor ) -->
                <div class="ecm-entry-content" style="
                    background: var(--ecm-bg-card);
                    border: 1px solid var(--ecm-border);
                    border-radius: var(--ecm-radius-md);
                    padding: clamp(24px, 4vw, 48px);
                    font-size: var(--ecm-text-base);
                    line-height: 1.85;
                    color: var(--ecm-grey-light);
                ">
                    <?php
                    the_content();
                    wp_link_pages( [
                        'before' => '<div class="page-links" style="margin-top:24px; font-family: Orbitron, monospace; font-size: 12px; letter-spacing: 2px;">' . esc_html__( 'الصفحات:', 'ecm-theme' ),
                        'after'  => '</div>',
                    ] );
                    ?>
                </div>

            </article>

            <?php if ( comments_open() || get_comments_number() ) : ?>
                <div class="ecm-comments-wrap" style="margin-top: 56px; padding-top: 40px; border-top: 1px solid var(--ecm-border);">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>

        <?php endwhile; endif; ?>

    </div><!-- .ecm-container -->
</main><!-- #ecm-main -->

<?php get_footer(); ?>
