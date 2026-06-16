<?php
/**
 * Template Name: 🎨 ECM — صفحة Elementor (عرض كامل)
 *
 * قالب فاضي بعرض كامل — مثالي لبناء أي صفحة جديدة بـ Elementor
 * مع الحفاظ على الهيدر والفوتر. كل المحتوى قابل للتعديل بالكامل.
 *
 * @package ecm-theme
 */

get_header();
?>

<main id="ecm-main" class="ecm-main-content ecm-elementor-full" role="main">
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
</main>

<?php get_footer(); ?>
