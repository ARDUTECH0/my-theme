    </div><!-- #content -->
</div><!-- #page -->

<?php
// لو في فوتر متعمل في Elementor Pro (Theme Builder) → اعرضه؛ وإلا اعرض فوتر الثيم الأصلي
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) :
?>
<!-- ── ECM FOOTER ── -->
<footer id="ecm-footer" class="ecm-footer" role="contentinfo">

    <?php if ( function_exists( 'ecm_has_footer_widgets' ) && ecm_has_footer_widgets() ) : ?>
    <!-- أعمدة الودجِت — قابلة للتعديل من المظهر > الودجِت -->
    <div class="ecm-footer-widgets">
        <div class="ecm-footer-widgets-inner">
            <?php for ( $col = 1; $col <= 4; $col++ ) :
                if ( is_active_sidebar( 'ecm-footer-' . $col ) ) : ?>
                    <div class="ecm-footer-col">
                        <?php dynamic_sidebar( 'ecm-footer-' . $col ); ?>
                    </div>
                <?php endif;
            endfor; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="ecm-footer-inner">

        <!-- Logo & Tagline -->
        <div class="ecm-footer-logo">
            <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                ECM
            <?php endif; ?>
            <small>
                <?php echo esc_html( get_theme_mod( 'ecm_footer_tagline', 'PROFESSIONAL CAMERA CONTROL' ) ); ?>
            </small>
        </div>

        <!-- Footer Nav -->
        <nav class="ecm-footer-nav" aria-label="<?php esc_attr_e( 'قائمة الفوتر', 'ecm-theme' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'footer-menu',
                'menu_class'     => 'ecm-footer-links',
                'container'      => false,
                'depth'          => 1,
                'fallback_cb'    => function () {
                    echo '<ul class="ecm-footer-links">
                        <li><a href="#">عن المنتج</a></li>
                        <li><a href="#">الدعم</a></li>
                        <li><a href="#">الخصوصية</a></li>
                    </ul>';
                },
            ] );
            ?>
        </nav>

        <!-- Copyright -->
        <div class="ecm-footer-copy">
            <?php echo wp_kses_post( get_theme_mod( 'ecm_footer_copy', '© ' . gmdate( 'Y' ) . ' E.Camera.Man — جميع الحقوق محفوظة' ) ); ?>
        </div>

    </div><!-- .ecm-footer-inner -->
</footer><!-- #ecm-footer -->
<?php endif; // elementor footer location ?>

<?php wp_footer(); ?>
</body>
</html>
