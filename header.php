<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111214">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class( 'ecm-dark-theme' ); ?>>
<?php wp_body_open(); ?>

<!-- ── SKIP TO CONTENT (إتاحة الوصول) ── -->
<a class="ecm-skip-link screen-reader-text" href="#ecm-main"><?php esc_html_e( 'تخطّ إلى المحتوى', 'ecm-theme' ); ?></a>

<!-- ── BACKDROP OVERLAY (mobile menu) ── -->
<div class="ecm-nav-overlay" id="ecm-nav-overlay" aria-hidden="true"></div>

<?php
// ── Get app download page link ──
$app_page_link = get_theme_mod( 'ecm_app_page_link', '' );

// لو مفيش رابط محدد، ابحث عن الصفحة بالتمبلت
if ( ! $app_page_link ) {
    $app_pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-download-app.php' ] );
    if ( ! empty( $app_pages ) ) {
        $app_page_link = get_permalink( $app_pages[0]->ID );
    }
}

// لو مفيش رابط بالتمبلت، ابحث بالعنوان
if ( ! $app_page_link ) {
    $app_page_obj = ecm_page_by_title( 'تحميل التطبيق' );
    if ( $app_page_obj ) {
        $app_page_link = get_permalink( $app_page_obj->ID );
    }
}

// Docs page link
$docs_page_link = '';
$docs_pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-documentation.php' ] );
if ( ! empty( $docs_pages ) ) {
    $docs_page_link = get_permalink( $docs_pages[0]->ID );
}
if ( ! $docs_page_link ) {
    $docs_page_obj = ecm_page_by_title( 'التوثيق والأدلة' );
    if ( $docs_page_obj ) {
        $docs_page_link = get_permalink( $docs_page_obj->ID );
    }
}
?>

<?php
// لو في هيدر متعمل في Elementor Pro (Theme Builder) → اعرضه؛ وإلا اعرض هيدر الثيم الأصلي
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) :
?>
<!-- ── HEADER / NAV ── -->
<header id="ecm-header" class="ecm-nav-wrap" role="banner">
    <div class="ecm-nav-inner">

        <!-- Logo -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ecm-nav-logo" rel="home" aria-label="<?php bloginfo( 'name' ); ?>">
            <?php if ( function_exists( 'ecm_has_3d_logo' ) && ecm_has_3d_logo() ) : ?>
                <?php echo ecm_render_3d_logo(); // اللوجو 3D بيحل محل النص ?>
            <?php elseif ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else :
                $logo_url = get_template_directory_uri() . '/assets/img/logo.png';
            ?>
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="ECM Logo" class="ecm-logo-img">
                <span class="ecm-logo-text">ECM</span>
            <?php endif; ?>
        </a>

        <!-- Desktop Navigation -->
        <nav class="ecm-nav-menu" aria-label="<?php esc_attr_e( 'القائمة الرئيسية', 'ecm-theme' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary-menu',
                'menu_class'     => 'ecm-nav-links',
                'container'      => false,
                'depth'          => 0,
                'fallback_cb'    => function () use ( $docs_page_link ) {
                    echo '<ul class="ecm-nav-links">';
                    for ( $i = 1; $i <= 6; $i++ ) {
                        $txt = get_theme_mod( "ecm_nav_btn{$i}_text" );
                        $lnk = get_theme_mod( "ecm_nav_btn{$i}_link" );
                        if ( $i === 5 && ! $lnk && $docs_page_link ) {
                            $lnk = $docs_page_link;
                        }
                        if ( ! $lnk ) $lnk = '#';
                        if ( strpos( $lnk, '#' ) === 0 || strpos( $lnk, '/' ) === 0 ) {
                            $lnk = home_url( $lnk );
                        }
                        if ( $txt ) {
                            echo '<li><a href="' . esc_url( $lnk ) . '">' . esc_html( $txt ) . '</a></li>';
                        }
                    }
                    echo '</ul>';
                },
            ] );
            ?>
        </nav>

        <!-- Desktop CTA — Download App Button (يختفي لو النص فاضي) -->
        <?php
        $ecm_cta_text = get_theme_mod( 'ecm_nav_cta_text', 'حمّل التطبيق' );
        if ( '' !== trim( (string) $ecm_cta_text ) ) :
            if ( $app_page_link ) : ?>
                <a href="<?php echo esc_url( $app_page_link ); ?>" class="ecm-nav-cta ecm-nav-cta-app">
                    <span class="ecm-nav-cta-icon">📱</span>
                    <span><?php echo esc_html( $ecm_cta_text ); ?></span>
                </a>
            <?php else : ?>
                <button
                    class="ecm-nav-cta"
                    onclick="document.getElementById('contact')?.scrollIntoView({behavior:'smooth'})"
                >
                    <?php echo esc_html( $ecm_cta_text ); ?>
                </button>
            <?php endif;
        endif; ?>

        <!-- Hamburger (mobile) -->
        <button
            class="ecm-hamburger"
            id="ecm-hamburger"
            aria-label="<?php esc_attr_e( 'فتح القائمة', 'ecm-theme' ); ?>"
            aria-expanded="false"
            aria-controls="ecm-mobile-drawer"
        >
            <span></span><span></span><span></span>
        </button>

    </div><!-- .ecm-nav-inner -->

    <!-- Mobile Drawer -->
    <div class="ecm-mobile-drawer" id="ecm-mobile-drawer" aria-hidden="true" role="dialog" aria-label="القائمة">
        <?php
        wp_nav_menu( [
            'theme_location' => 'primary-menu',
            'menu_class'     => 'ecm-mobile-links',
            'container'      => false,
            'depth'          => 0,
            'fallback_cb'    => function () use ( $docs_page_link ) {
                echo '<ul class="ecm-mobile-links">';
                $emojis = [ 1 => '🏠', 2 => '⚙️', 3 => '✨', 4 => '📊', 5 => '📖', 6 => '📞' ];
                for ( $i = 1; $i <= 6; $i++ ) {
                    $txt = get_theme_mod( "ecm_nav_btn{$i}_text" );
                    $lnk = get_theme_mod( "ecm_nav_btn{$i}_link" );
                    if ( $i === 5 && ! $lnk && $docs_page_link ) {
                        $lnk = $docs_page_link;
                    }
                    if ( ! $lnk ) $lnk = '#';
                    if ( strpos( $lnk, '#' ) === 0 || strpos( $lnk, '/' ) === 0 ) {
                        $lnk = home_url( $lnk );
                    }
                    $emoji = $emojis[$i] ?? '🔗';
                    if ( $txt ) {
                        echo '<li><a href="' . esc_url( $lnk ) . '">' . $emoji . ' ' . esc_html( $txt ) . '</a></li>';
                    }
                }
                echo '</ul>';
            },
        ] );
        ?>

        <!-- Mobile: App Download Button (يختفي لو النص فاضي) -->
        <?php if ( '' !== trim( (string) $ecm_cta_text ) ) : ?>
        <div class="ecm-mobile-cta">
            <?php if ( $app_page_link ) : ?>
                <a href="<?php echo esc_url( $app_page_link ); ?>" class="ecm-btn-primary ecm-mobile-app-btn">
                    📱 <?php echo esc_html( $ecm_cta_text ); ?>
                </a>
            <?php else : ?>
                <button class="ecm-btn-primary ecm-mobile-app-btn"
                    onclick="document.getElementById('contact')?.scrollIntoView({behavior:'smooth'})">
                    <?php echo esc_html( $ecm_cta_text ); ?>
                </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div><!-- .ecm-mobile-drawer -->

</header><!-- #ecm-header -->
<?php endif; // elementor header location ?>

<div id="page" class="site">
    <div id="content" class="site-content">