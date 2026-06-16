<?php
/**
 * ECM Theme — single.php
 * قالب عرض المقال/البوست الواحد
 *
 * @package ecm-theme
 */

get_header();

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) :
?>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div class="ecm-container" style="padding-block: 48px;">

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class( 'ecm-article ecm-single' ); ?>>

                <!-- Post Header -->
                <header class="ecm-entry-header" style="margin-bottom: 32px;">
                    <!-- Category eyebrow -->
                    <?php
                    $categories = get_the_category();
                    if ( ! empty( $categories ) ) :
                    ?>
                        <span class="ecm-eyebrow" style="margin-bottom: 20px;">
                            <?php echo esc_html( $categories[0]->name ); ?>
                        </span>
                    <?php endif; ?>

                    <h1 class="ecm-section-title" style="margin-bottom: 16px;">
                        <?php the_title(); ?>
                    </h1>

                    <!-- Meta -->
                    <div style="
                        display: flex;
                        gap: 20px;
                        flex-wrap: wrap;
                        font-size: 12px;
                        color: var(--ecm-grey-mid);
                        font-family: 'Orbitron', 'Cairo', sans-serif;
                        letter-spacing: 1px;
                    ">
                        <span><?php echo get_the_date(); ?></span>
                        <span>•</span>
                        <span><?php echo esc_html( get_the_author() ); ?></span>
                        <?php if ( get_comments_number() > 0 ) : ?>
                            <span>•</span>
                            <span><?php printf( '%d تعليق', get_comments_number() ); ?></span>
                        <?php endif; ?>
                    </div>

                    <div style="width: 48px; height: 2px; background: var(--ecm-green); box-shadow: var(--ecm-shadow-green); margin-top: 24px;"></div>
                </header>

                <!-- Featured Image -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="ecm-post-thumbnail" style="
                        margin-bottom: 36px;
                        border-radius: var(--ecm-radius-md);
                        overflow: hidden;
                        border: 1px solid var(--ecm-border);
                    ">
                        <?php the_post_thumbnail( 'large', [
                            'loading' => 'eager',
                            'style'   => 'width:100%; height:auto; display:block;',
                        ] ); ?>
                    </div>
                <?php endif; ?>

                <!-- Content -->
                <div class="ecm-entry-content" style="
                    font-size: var(--ecm-text-base);
                    line-height: 1.9;
                    color: var(--ecm-grey-light);
                    max-width: 780px;
                ">
                    <?php
                    the_content();
                    wp_link_pages( [
                        'before' => '<div class="page-links" style="margin-top:24px;">' . esc_html__( 'الصفحات:', 'ecm-theme' ),
                        'after'  => '</div>',
                    ] );
                    ?>
                </div>

                <!-- Tags -->
                <?php $tags = get_the_tags(); if ( $tags ) : ?>
                    <div style="
                        margin-top: 40px;
                        padding-top: 24px;
                        border-top: 1px solid var(--ecm-border);
                        display: flex;
                        gap: 8px;
                        flex-wrap: wrap;
                    ">
                        <?php foreach ( $tags as $tag ) : ?>
                            <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" style="
                                font-size: 11px;
                                font-family: 'Orbitron', 'Cairo', sans-serif;
                                letter-spacing: 1px;
                                padding: 5px 14px;
                                border: 1px solid var(--ecm-border);
                                border-radius: var(--ecm-radius-sm);
                                color: var(--ecm-grey-mid);
                                transition: border-color 0.2s, color 0.2s;
                            " onmouseover="this.style.borderColor='var(--ecm-green)';this.style.color='var(--ecm-green)'"
                               onmouseout="this.style.borderColor='var(--ecm-border)';this.style.color='var(--ecm-grey-mid)'">
                                #<?php echo esc_html( $tag->name ); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Post Nav -->
                <nav style="
                    margin-top: 48px;
                    padding-top: 32px;
                    border-top: 1px solid var(--ecm-border);
                    display: flex;
                    justify-content: space-between;
                    gap: 24px;
                    flex-wrap: wrap;
                ">
                    <div>
                        <?php
                        $prev_post = get_previous_post();
                        if ( $prev_post ) :
                        ?>
                            <span style="font-size: 10px; letter-spacing: 2px; color: var(--ecm-grey-mid); text-transform: uppercase; font-family: 'Orbitron', 'Cairo', sans-serif; display: block; margin-bottom: 6px;">← السابق</span>
                            <a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>" style="color: var(--ecm-grey-light); font-size: 14px;">
                                <?php echo esc_html( $prev_post->post_title ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: left;">
                        <?php
                        $next_post = get_next_post();
                        if ( $next_post ) :
                        ?>
                            <span style="font-size: 10px; letter-spacing: 2px; color: var(--ecm-grey-mid); text-transform: uppercase; font-family: 'Orbitron', 'Cairo', sans-serif; display: block; margin-bottom: 6px;">التالي →</span>
                            <a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" style="color: var(--ecm-grey-light); font-size: 14px;">
                                <?php echo esc_html( $next_post->post_title ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </nav>

            </article>

            <?php if ( comments_open() || get_comments_number() ) : ?>
                <div class="ecm-comments-wrap" style="margin-top: 56px; padding-top: 40px; border-top: 1px solid var(--ecm-border);">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>

        <?php endwhile; endif; ?>

    </div>
</main>

<?php endif; // elementor single location ?>

<?php get_footer(); ?>
