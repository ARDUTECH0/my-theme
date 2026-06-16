<?php
/**
 * ECM Theme — sidebar.php
 * السايدبار (الشريط الجانبي)
 *
 * @package ecm-theme
 */

if ( ! is_active_sidebar( 'ecm-sidebar' ) ) {
    return;
}
?>

<aside id="secondary" class="ecm-sidebar" role="complementary" style="
    padding: var(--ecm-space-lg);
    border: 1px solid var(--ecm-border);
    border-radius: var(--ecm-radius-md);
    background: var(--ecm-bg-card);
">
    <?php dynamic_sidebar( 'ecm-sidebar' ); ?>
</aside>
