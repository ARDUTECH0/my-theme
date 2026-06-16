<?php
/**
 * ECM Theme — archive.php
 * قالب الأرشيف — التصنيفات والوسوم وصفحات التاريخ
 *
 * @package ecm-theme
 */

get_header();

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) :
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 48px;">

        <!-- Archive Header -->
        <header class="ecm-archive-header" style="margin-bottom: 48px;">
            <span class="ecm-eyebrow" style="margin-bottom: 20px;">
                <?php
                if ( is_category() )    echo 'تصنيف';
                elseif ( is_tag() )     echo 'وسم';
                elseif ( is_author() )  echo 'كاتب';
                elseif ( is_date() )    echo 'أرشيف';
                else                    echo 'مقالات';
                ?>
            </span>

            <h1 class="ecm-section-title" style="margin-bottom: 12px;">
                <?php the_archive_title(); ?>
            </h1>

            <?php if ( get_the_archive_description() ) : ?>
                <p style="max-width: 640px; color: var(--ecm-grey-mid); font-size: 15px; line-height: 1.8;">
                    <?php echo wp_kses_post( get_the_archive_description() ); ?>
                </p>
            <?php endif; ?>

            <div style="width: 48px; height: 2px; background: var(--ecm-green); box-shadow: var(--ecm-shadow-green); margin-top: 24px;"></div>
        </header>

        <!-- Posts Grid -->
        <?php if ( have_posts() ) : ?>
            <div class="ecm-feat-grid-3" style="margin-bottom: 48px;">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="ecm-feat-card" style="padding: 0; overflow: hidden;">

                        <!-- Thumbnail -->
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" style="display: block; overflow: hidden;">
                                <?php the_post_thumbnail( 'ecm-card', [
                                    'style'   => 'width:100%; height:200px; object-fit:cover; display:block; transition: transform 0.4s;',
                                    'loading' => 'lazy',
                                ] ); ?>
                            </a>
                        <?php endif; ?>

                        <!-- Content -->
                        <div style="padding: 24px;">
                            <!-- Category -->
                            <?php $cats = get_the_category(); if ( ! empty( $cats ) ) : ?>
                                <span style="
                                    font-size: 10px;
                                    font-family: 'Orbitron', 'Cairo', sans-serif;
                                    letter-spacing: 2px;
                                    color: var(--ecm-green);
                                    text-transform: uppercase;
                                    display: block;
                                    margin-bottom: 10px;
                                "><?php echo esc_html( $cats[0]->name ); ?></span>
                            <?php endif; ?>

                            <a href="<?php the_permalink(); ?>" style="color: var(--ecm-white);">
                                <h3 style="
                                    font-family: 'Cairo', sans-serif;
                                    font-size: 16px;
                                    font-weight: 700;
                                    line-height: 1.4;
                                    margin-bottom: 10px;
                                "><?php the_title(); ?></h3>
                            </a>

                            <p class="ecm-feat-text" style="
                                display: -webkit-box;
                                -webkit-line-clamp: 3;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                                margin-bottom: 16px;
                            "><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>

                            <span style="
                                font-size: 11px;
                                color: var(--ecm-grey-mid);
                                font-family: 'Orbitron', 'Cairo', sans-serif;
                                letter-spacing: 1px;
                            "><?php echo get_the_date(); ?></span>
                        </div>

                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <nav class="ecm-pagination" style="
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
                    'type'      => 'list',
                ] );
                ?>
            </nav>

        <?php else : ?>
            <div style="text-align: center; padding: 80px 0;">
                <p style="color: var(--ecm-grey-mid); font-size: 18px;">لا توجد مقالات في هذا القسم.</p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-btn-ghost" style="margin-top: 24px; display: inline-flex;">
                    الصفحة الرئيسية
                </a>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php endif; // elementor archive location ?>

<?php get_footer(); ?>
