<?php
/**
 * ECM Theme — index.php
 * القالب الافتراضي — لو الصفحة الرئيسية، يعرض الـ front-page
 *
 * @package ecm-theme
 */

// ── لو دي الصفحة الرئيسية → نعرض front-page.php مباشرة ──
if ( is_front_page() || is_home() ) {
    include get_template_directory() . '/front-page.php';
    return;
}

get_header();

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) :
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 48px;">

        <?php if ( have_posts() ) : ?>
            <div class="ecm-feat-grid-3" style="margin-bottom: 48px;">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article <?php post_class( 'ecm-feat-card' ); ?> style="padding: 0; overflow: hidden;">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'ecm-card', [
                                    'style'   => 'width:100%; height:200px; object-fit:cover; display:block;',
                                    'loading' => 'lazy',
                                ] ); ?>
                            </a>
                        <?php endif; ?>
                        <div style="padding: 24px;">
                            <?php $cats = get_the_category(); if ( ! empty( $cats ) ) : ?>
                                <span style="font-size:10px; font-family:'Orbitron',monospace; letter-spacing:2px; color:var(--ecm-green); text-transform:uppercase; display:block; margin-bottom:10px;">
                                    <?php echo esc_html( $cats[0]->name ); ?>
                                </span>
                            <?php endif; ?>
                            <a href="<?php the_permalink(); ?>" style="color: var(--ecm-white);">
                                <h3 style="font-family:'Cairo',sans-serif; font-size:16px; font-weight:700; line-height:1.4; margin-bottom:10px;"><?php the_title(); ?></h3>
                            </a>
                            <p class="ecm-feat-text" style="display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                                <?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?>
                            </p>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <nav style="display:flex; justify-content:center; gap:8px; padding-top:32px; border-top:1px solid var(--ecm-border);">
                <?php echo paginate_links( [ 'prev_text' => '← السابق', 'next_text' => 'التالي →' ] ); ?>
            </nav>
        <?php else : ?>
            <div style="text-align:center; padding:80px 0;">
                <span class="ecm-eyebrow">لا يوجد محتوى</span>
                <p style="color:var(--ecm-grey-mid); font-size:18px; margin-top:16px;">ابدأ بإنشاء أول صفحة</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php endif; // elementor archive location ?>

<?php get_footer(); ?>
