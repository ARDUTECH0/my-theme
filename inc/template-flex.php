<?php
/**
 * ECM Theme — مرونة القالب (Template Flexibility)
 *
 * بيخلّي الثيم يتصرّف كتمبلت احترافي:
 *  • قوائم افتراضية تتعمل تلقائيًا (هيدر + فوتر) تقدر تعدّل روابطها وترتيبها
 *  • أعمدة ودجِت في الفوتر — تتحكم في تركيب الفوتر بالكامل بالسحب والإفراغ
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;


// ══════════════════════════════════════════════════
// §1  أعمدة الودجِت في الفوتر
// ══════════════════════════════════════════════════
function ecm_register_footer_widgets() {
    for ( $i = 1; $i <= 4; $i++ ) {
        register_sidebar( [
            'name'          => sprintf( __( 'الفوتر — عمود %d', 'ecm-theme' ), $i ),
            'id'            => 'ecm-footer-' . $i,
            'description'   => __( 'اسحب ودجِتس هنا (روابط، نص، قائمة...) لبناء عمود في الفوتر. سيب الأعمدة فاضية لو عايز شكل الفوتر الافتراضي.', 'ecm-theme' ),
            'before_widget' => '<div id="%1$s" class="ecm-footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="ecm-footer-widget-title">',
            'after_title'   => '</h4>',
        ] );
    }
}
add_action( 'widgets_init', 'ecm_register_footer_widgets' );

/**
 * هل في أي عمود فوتر فيه ودجِتس؟
 */
function ecm_has_footer_widgets() {
    for ( $i = 1; $i <= 4; $i++ ) {
        if ( is_active_sidebar( 'ecm-footer-' . $i ) ) {
            return true;
        }
    }
    return false;
}


// ══════════════════════════════════════════════════
// §2  إنشاء قوائم افتراضية تلقائيًا (تتصلّح نفسها)
// ══════════════════════════════════════════════════

/**
 * يضيف عنصر صفحة للقائمة (لو الصفحة موجودة)
 * بيستخدم ecm_find_page_by_template() المعرّفة في elementor-seed.php
 */
function ecm_add_page_menu_item( $menu_id, $template, $title ) {
    $page_id = function_exists( 'ecm_find_page_by_template' ) ? ecm_find_page_by_template( $template ) : 0;
    if ( ! $page_id ) {
        return;
    }
    wp_update_nav_menu_item( $menu_id, 0, [
        'menu-item-title'     => $title,
        'menu-item-object'    => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type'      => 'post_type',
        'menu-item-status'    => 'publish',
    ] );
}

/**
 * يضيف رابط مخصص للقائمة
 */
function ecm_add_link_menu_item( $menu_id, $title, $url ) {
    wp_update_nav_menu_item( $menu_id, 0, [
        'menu-item-title'  => $title,
        'menu-item-url'    => $url,
        'menu-item-status' => 'publish',
    ] );
}

/**
 * ينشئ القوائم الافتراضية ويربطها بأماكنها — لو مفيش قوائم متعيّنة بالفعل
 */
function ecm_create_default_menus() {
    $locations = get_theme_mod( 'nav_menu_locations', [] );
    if ( ! is_array( $locations ) ) {
        $locations = [];
    }

    // ── القائمة الرئيسية (الهيدر) ──
    if ( empty( $locations['primary-menu'] ) ) {
        $menu_name = __( 'القائمة الرئيسية', 'ecm-theme' );
        $menu_obj  = wp_get_nav_menu_object( $menu_name );
        $menu_id   = $menu_obj ? (int) $menu_obj->term_id : wp_create_nav_menu( $menu_name );

        if ( ! is_wp_error( $menu_id ) ) {
            $existing = wp_get_nav_menu_items( $menu_id );
            if ( empty( $existing ) ) {
                ecm_add_link_menu_item( $menu_id, __( 'الرئيسية', 'ecm-theme' ), home_url( '/' ) );
                ecm_add_page_menu_item( $menu_id, 'page-download-app.php',  __( 'تحميل التطبيق', 'ecm-theme' ) );
                ecm_add_page_menu_item( $menu_id, 'page-documentation.php', __( 'التوثيق', 'ecm-theme' ) );
                ecm_add_page_menu_item( $menu_id, 'page-software.php',      __( 'السوفت وير', 'ecm-theme' ) );
                ecm_add_link_menu_item( $menu_id, __( 'تواصل', 'ecm-theme' ), home_url( '/#contact' ) );
            }
            $locations['primary-menu'] = $menu_id;
        }
    }

    // ── قائمة الفوتر ──
    if ( empty( $locations['footer-menu'] ) ) {
        $menu_name = __( 'قائمة الفوتر', 'ecm-theme' );
        $menu_obj  = wp_get_nav_menu_object( $menu_name );
        $menu_id   = $menu_obj ? (int) $menu_obj->term_id : wp_create_nav_menu( $menu_name );

        if ( ! is_wp_error( $menu_id ) ) {
            $existing = wp_get_nav_menu_items( $menu_id );
            if ( empty( $existing ) ) {
                ecm_add_link_menu_item( $menu_id, __( 'عن المنتج', 'ecm-theme' ), home_url( '/#features' ) );
                ecm_add_page_menu_item( $menu_id, 'page-documentation.php', __( 'التوثيق', 'ecm-theme' ) );
                ecm_add_page_menu_item( $menu_id, 'page-support.php', __( 'الدعم', 'ecm-theme' ) );
                ecm_add_link_menu_item( $menu_id, __( 'الخصوصية', 'ecm-theme' ), home_url( '/' ) );
            }
            $locations['footer-menu'] = $menu_id;
        }
    }

    set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * التشغيل التلقائي — مرة واحدة، ويتصلّح نفسه حتى لو الثيم مفعّل بالفعل
 * (مايحتاجش تعيد تفعيل الثيم — بيتعمل أول ما تفتح لوحة التحكم)
 */
function ecm_maybe_create_default_menus() {
    if ( get_option( 'ecm_default_menus_done' ) ) {
        return;
    }
    // متشغّلش إلا لمستخدم عنده صلاحية إدارة القوائم
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }
    ecm_create_default_menus();
    update_option( 'ecm_default_menus_done', 1 );
}
add_action( 'admin_init', 'ecm_maybe_create_default_menus', 15 );

/** عند إعادة تفعيل الثيم — اعمل القوائم من جديد لو ناقصة */
function ecm_reset_default_menus() {
    delete_option( 'ecm_default_menus_done' );
}
add_action( 'after_switch_theme', 'ecm_reset_default_menus' );
