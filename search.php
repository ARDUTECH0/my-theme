<?php
/**
 * ECM Theme — search.php
 * قالب نتائج البحث
 *
 * @package ecm-theme
 */

get_header();

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) :
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 48px;">

        <!-- Search Header -->
        <header class="ecm-search-header" style="margin-bottom: 48px;">
            <span class="ecm-eyebrow" style="margin-bottom: 20px;">نتائج البحث</span>

            <h1 class="ecm-section-title" style="margin-bottom: 12px;">
                <?php printf( 'بحث عن: "%s"', esc_html( get_search_query() ) ); ?>
            </h1>

            <p style="color: var(--ecm-grey-mid); font-size: 14px;">
                <?php
                global $wp_query;
                printf( 'تم العثور على %d نتيجة', (int) $wp_query->found_posts );
                ?>
            </p>

            <div style="width: 48px; height: 2px; background: var(--ecm-green); box-shadow: var(--ecm-shadow-green); margin-top: 20px;"></div>
        </header>

        <!-- Results -->
        <?php if ( have_posts() ) : ?>
            <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 48px;">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="ecm-ctrl-card" style="display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap;">

                        <!-- Thumbnail -->
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" style="flex-shrink: 0;">
                                <?php the_post_thumbnail( 'ecm-thumb', [
                                    'style'   => 'width:160px; height:120px; object-fit:cover; border-radius: var(--ecm-radius-sm);',
                                    'loading' => 'lazy',
                                ] ); ?>
                            </a>
                        <?php endif; ?>

                        <!-- Text -->
                        <div style="flex: 1; min-width: 200px;">
                            <span style="
                                font-size: 10px;
                                font-family: 'Orbitron', 'Cairo', sans-serif;
                                letter-spacing: 2px;
                                color: var(--ecm-green);
                                text-transform: uppercase;
                                display: block;
                                margin-bottom: 6px;
                            "><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>

                            <a href="<?php the_permalink(); ?>" style="color: var(--ecm-white);">
                                <h3 style="
                                    font-family: 'Cairo', sans-serif;
                                    font-size: 16px;
                                    font-weight: 700;
                                    line-height: 1.4;
                                    margin-bottom: 8px;
                                "><?php the_title(); ?></h3>
                            </a>

                            <p style="
                                font-size: 14px;
                                color: var(--ecm-grey-mid);
                                line-height: 1.7;
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                            "><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
                        </div>

                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <nav style="
                display: flex;
                justify-content: center;
                gap: 8px;
                padding-top: 32px;
                border-top: 1px solid var(--ecm-border);
            ">
                <?php
                echo paginate_links( [
                    'prev_text' => '← السابق',
                    'next_text' => 'التالي →',
                ] );
                ?>
            </nav>

        <?php else : ?>
            <div style="text-align: center; padding: 80px 0;">
                <p style="color: var(--ecm-grey-mid); font-size: 18px; margin-bottom: 24px;">
                    مفيش نتائج مطابقة لـ "<?php echo esc_html( get_search_query() ); ?>"
                </p>
                <p style="color: var(--ecm-grey-dark); font-size: 14px; margin-bottom: 32px;">
                    جرّب كلمات بحث مختلفة
                </p>

                <!-- Search Form -->
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php endif; // elementor archive location (search) ?>

<?php get_footer(); ?>
