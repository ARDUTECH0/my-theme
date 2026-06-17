<?php
/**
 * ECM — WooCommerce wrapper template
 * بيلفّ كل صفحات WooCommerce (المتجر/المنتج/السلة/الدفع/الحساب) بهيدر وفوتر الثيم.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="ecm-woo" role="main">
    <div class="ecm-woo-inner">
        <?php woocommerce_content(); ?>
    </div>
</main>

<?php
get_footer();
