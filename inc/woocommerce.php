<?php
/**
 * ECM — WooCommerce Integration
 * دعم متجر احترافي (منتجات رقمية/ديجيتال) بستايل ECM:
 * تسجيل دخول/خروج · حساب · سلة · صفحات منتجات وعروض احترافية.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

// لو WooCommerce مش مفعّل — لا تكمل
if ( ! class_exists( 'WooCommerce' ) ) {
    // تنبيه بسيط في لوحة التحكم
    add_action( 'admin_notices', function () {
        if ( current_user_can( 'install_plugins' ) && ! class_exists( 'WooCommerce' ) ) {
            echo '<div class="notice notice-info"><p>🛒 <strong>ECM:</strong> ثبّت وفعّل إضافة <strong>WooCommerce</strong> عشان تشتغل صفحات المتجر والحساب والسلة بستايل الثيم.</p></div>';
        }
    } );
    return;
}

// ── دعم الثيم لـ WooCommerce ──────────────────────────────────
add_action( 'after_setup_theme', function () {
    add_theme_support( 'woocommerce', [
        'thumbnail_image_width' => 600,
        'single_image_width'    => 900,
        'product_grid'          => [ 'default_columns' => 3, 'min_columns' => 1, 'max_columns' => 4 ],
    ] );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    // ملاحظة: شيلنا دعم الـ slider عشان الصورة تظهر دايمًا بثبات (من غير flexslider)
} );

// ── إزالة الشريط الجانبي للمتجر (تصميم عرض كامل) ──────────────
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// ── تنظيف الـ wrappers (الثيم بيوفّر #content أصلًا) ──────────
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', function () {
    echo '<div class="ecm-woo-container">';
}, 10 );
add_action( 'woocommerce_after_main_content', function () {
    echo '</div>';
}, 10 );

// ── عدد المنتجات في الصف والصفحة ──────────────────────────────
add_filter( 'loop_shop_columns', function () { return 3; }, 20 );
add_filter( 'loop_shop_per_page', function () { return 12; }, 20 );

// ── شريط بحث المنتجات فوق المتجر ──────────────────────────────
add_action( 'woocommerce_before_shop_loop', 'ecm_shop_search_bar', 4 );
function ecm_shop_search_bar() {
    if ( ! ( is_shop() || is_product_category() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) ) {
        return;
    }
    $q = get_search_query();
    echo '<form role="search" method="get" class="ecm-shop-search" action="' . esc_url( home_url( '/' ) ) . '">';
    echo '<span class="ecm-shop-search-ic" aria-hidden="true">🔍</span>';
    echo '<input type="search" name="s" value="' . esc_attr( $q ) . '" placeholder="' . esc_attr__( 'ابحث عن منتج…', 'ecm-theme' ) . '" aria-label="' . esc_attr__( 'بحث عن منتج', 'ecm-theme' ) . '">';
    echo '<input type="hidden" name="post_type" value="product">';
    echo '<button type="submit">' . esc_html__( 'بحث', 'ecm-theme' ) . '</button>';
    echo '</form>';
}

// ── شريط التصنيفات فوق المتجر (احترافي) ───────────────────────
add_action( 'woocommerce_before_shop_loop', 'ecm_shop_category_nav', 5 );
function ecm_shop_category_nav() {
    if ( ! ( is_shop() || is_product_category() ) ) {
        return;
    }
    $cats = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0 ] );
    if ( is_wp_error( $cats ) || empty( $cats ) ) {
        return;
    }
    $current = is_product_category() ? get_queried_object_id() : 0;

    echo '<nav class="ecm-shop-cats" aria-label="' . esc_attr__( 'تصنيفات المنتجات', 'ecm-theme' ) . '">';
    echo '<a class="ecm-shop-cat' . ( $current ? '' : ' is-active' ) . '" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( '🗂️ الكل', 'ecm-theme' ) . '</a>';
    foreach ( $cats as $cat ) {
        $active = ( (int) $cat->term_id === (int) $current ) ? ' is-active' : '';
        echo '<a class="ecm-shop-cat' . $active . '" href="' . esc_url( get_term_link( $cat ) ) . '">'
            . esc_html( $cat->name ) . ' <span>' . (int) $cat->count . '</span></a>';
    }
    echo '</nav>';
}

// ── لوحة احترافية في رئيسية الحساب ────────────────────────────
add_action( 'woocommerce_account_dashboard', 'ecm_account_dashboard_cards', 5 );
function ecm_account_dashboard_cards() {
    $u = wp_get_current_user();

    // بيانات
    $orders_count = function_exists( 'wc_get_customer_order_count' ) ? (int) wc_get_customer_order_count( $u->ID ) : 0;
    $downloads    = function_exists( 'wc_get_customer_available_downloads' ) ? (array) wc_get_customer_available_downloads( $u->ID ) : [];
    $devices      = function_exists( 'ecm_user_devices' ) ? ecm_user_devices( $u->ID ) : [];
    $avatar       = get_avatar( $u->ID, 64, '', $u->display_name, [ 'class' => 'ecm-dash-avatar' ] );

    echo '<div class="ecm-dash">';

    // الرأس
    echo '<div class="ecm-dash-head">' . $avatar . '<div class="ecm-dash-meta"><h3>' . esc_html__( 'أهلاً', 'ecm-theme' ) . ' ' . esc_html( $u->display_name ) . ' 👋</h3><span>' . esc_html( $u->user_email ) . '</span></div></div>';

    // إحصائيات
    echo '<div class="ecm-dash-stats">';
    echo '<div class="ecm-dash-stat"><span class="n">' . $orders_count . '</span><span class="l">' . esc_html__( 'طلب', 'ecm-theme' ) . '</span></div>';
    echo '<div class="ecm-dash-stat"><span class="n">' . count( $devices ) . '</span><span class="l">' . esc_html__( 'جهاز مفعّل', 'ecm-theme' ) . '</span></div>';
    echo '<div class="ecm-dash-stat"><span class="n">' . count( $downloads ) . '</span><span class="l">' . esc_html__( 'منتج رقمي', 'ecm-theme' ) . '</span></div>';
    echo '</div>';

    // اختصارات
    $links = [
        [ 'orders', '🧾', __( 'الطلبات', 'ecm-theme' ) ],
        [ 'downloads', '⬇', __( 'التحميلات', 'ecm-theme' ) ],
        [ 'my-devices', '🛡️', __( 'أجهزتي', 'ecm-theme' ) ],
        [ 'edit-account', '⚙', __( 'بيانات الحساب', 'ecm-theme' ) ],
    ];
    echo '<div class="ecm-dash-grid">';
    foreach ( $links as $l ) {
        echo '<a class="ecm-dash-card" href="' . esc_url( wc_get_account_endpoint_url( $l[0] ) ) . '"><span class="ic">' . $l[1] . '</span><span class="t">' . esc_html( $l[2] ) . '</span></a>';
    }
    echo '</div>';

    // ── منتجاتك الرقمية ──
    echo '<h3 class="ecm-dash-section">📦 ' . esc_html__( 'منتجاتك الرقمية', 'ecm-theme' ) . '</h3>';
    if ( $downloads ) {
        $seen = [];
        echo '<div class="ecm-dash-list">';
        foreach ( $downloads as $d ) {
            $pid = $d['product_id'] ?? 0;
            if ( $pid && isset( $seen[ $pid ] ) ) {
                continue;
            }
            $seen[ $pid ] = 1;
            echo '<div class="ecm-dash-item">';
            echo '<span class="ecm-dash-item-name">🎬 ' . esc_html( $d['product_name'] ?? '' ) . '</span>';
            echo '<a class="ecm-dash-item-btn" href="' . esc_url( $d['download_url'] ?? '#' ) . '">⬇ ' . esc_html__( 'تحميل', 'ecm-theme' ) . '</a>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p class="ecm-dash-empty">' . esc_html__( 'لسه مشتريتش منتجات.', 'ecm-theme' ) . ' <a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'روح للمتجر ←', 'ecm-theme' ) . '</a></p>';
    }

    // ── أجهزتك ──
    echo '<h3 class="ecm-dash-section">🛡️ ' . esc_html__( 'أجهزتك المفعّلة', 'ecm-theme' ) . '</h3>';
    if ( $devices ) {
        echo '<div class="ecm-dash-list">';
        foreach ( $devices as $r ) {
            $warr_m   = (int) ( $r->warranty_months ?? 12 );
            $act_ts   = $r->activated_at ? strtotime( $r->activated_at ) : time();
            $warr_end = strtotime( '+' . $warr_m . ' months', $act_ts );
            $valid    = ( current_time( 'timestamp' ) < $warr_end );
            echo '<div class="ecm-dash-item">';
            echo '<span class="ecm-dash-item-name">🔒 ' . esc_html( $r->serial ) . '</span>';
            echo '<span class="ecm-dash-badge ' . ( $valid ? 'ok' : 'end' ) . '">' . ( $valid ? esc_html__( 'ضمان ساري', 'ecm-theme' ) : esc_html__( 'ضمان منتهي', 'ecm-theme' ) ) . '</span>';
            echo '</div>';
        }
        echo '</div>';
        echo '<p class="ecm-dash-more"><a href="' . esc_url( wc_get_account_endpoint_url( 'my-devices' ) ) . '">' . esc_html__( 'تفاصيل كل الأجهزة ←', 'ecm-theme' ) . '</a></p>';
    } else {
        $act_url = function_exists( 'ecm_activation_page_url' ) ? ecm_activation_page_url() : '#';
        echo '<p class="ecm-dash-empty">' . esc_html__( 'لسه مفعّلتش أي جهاز.', 'ecm-theme' ) . ' <a href="' . esc_url( $act_url ) . '">' . esc_html__( 'فعّل جهازك ←', 'ecm-theme' ) . '</a></p>';
    }

    echo '</div>';
}

// ── دخول/خروج · حساب · سلة في القائمة الرئيسية ────────────────
function ecm_woo_menu_items( $items, $args ) {
    if ( empty( $args->theme_location ) || 'primary-menu' !== $args->theme_location ) {
        return $items;
    }
    if ( ! function_exists( 'wc_get_cart_url' ) ) {
        return $items;
    }

    $account_url = get_permalink( wc_get_page_id( 'myaccount' ) );

    if ( is_user_logged_in() ) {
        $current = wp_get_current_user();
        $avatar  = get_avatar( $current->ID, 32, '', $current->display_name, [ 'class' => 'ecm-woo-avatar' ] );
        $fname   = $current->first_name ? $current->first_name : $current->display_name;

        // منيو منسدلة من صورة الحساب (بتستخدم ستايل الدروب‌داون بتاع الثيم)
        $sub  = '<ul class="sub-menu ecm-account-sub">';
        $sub .= '<li class="menu-item"><a href="' . esc_url( $account_url ) . '">🏠 ' . esc_html__( 'حسابي', 'ecm-theme' ) . '</a></li>';
        $sub .= '<li class="menu-item"><a href="' . esc_url( wc_get_account_endpoint_url( 'orders' ) ) . '">🧾 ' . esc_html__( 'طلباتي', 'ecm-theme' ) . '</a></li>';
        $sub .= '<li class="menu-item"><a href="' . esc_url( wc_get_account_endpoint_url( 'downloads' ) ) . '">⬇ ' . esc_html__( 'تحميلاتي', 'ecm-theme' ) . '</a></li>';
        $sub .= '<li class="menu-item"><a href="' . esc_url( wc_get_account_endpoint_url( 'edit-account' ) ) . '">⚙ ' . esc_html__( 'تعديل الحساب', 'ecm-theme' ) . '</a></li>';
        $sub .= '<li class="menu-item ecm-sub-logout"><a href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '">↩ ' . esc_html__( 'تسجيل الخروج', 'ecm-theme' ) . '</a></li>';
        $sub .= '</ul>';

        $items .= '<li class="menu-item menu-item-has-children ecm-woo-menu ecm-woo-account">'
            . '<a href="' . esc_url( $account_url ) . '" title="' . esc_attr( $current->display_name ) . '">'
            . $avatar . '<span class="ecm-woo-name">' . esc_html( $fname ) . '</span></a>'
            . $sub . '</li>';
    } else {
        $items .= '<li class="menu-item ecm-woo-menu ecm-woo-login"><a href="' . esc_url( $account_url ) . '"><span class="ecm-woo-ic">👤</span>' . esc_html__( 'دخول', 'ecm-theme' ) . '</a></li>';
    }

    $count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
    $items .= '<li class="menu-item ecm-woo-menu ecm-woo-cart"><a href="' . esc_url( wc_get_cart_url() ) . '" aria-label="' . esc_attr__( 'السلة', 'ecm-theme' ) . '"><span class="ecm-woo-ic">🛒</span><span class="ecm-cart-count">' . esc_html( $count ) . '</span></a></li>';

    return $items;
}
add_filter( 'wp_nav_menu_items', 'ecm_woo_menu_items', 10, 2 );

// ── صفحة متجر احترافية جاهزة (عروض + أحدث + فئات) ──────────────
function ecm_woo_create_shop_page() {
    if ( get_option( 'ecm_woo_shop_page_v2' ) ) {
        return;
    }
    if ( ! function_exists( 'ecm_page_by_title' ) || ! function_exists( 'wc_get_page_permalink' ) ) {
        return;
    }

    $shop_url = wc_get_page_permalink( 'shop' );

    $content  = '<div class="ecm-shop-page">';
    $content .= '<p class="ecm-shop-lead" style="text-align:center;">كل منتجات ECM الرقمية في مكان واحد — اشترِ وحمّل فورًا.</p>';

    $content .= '<h2 class="ecm-shop-h">⚡ عروض وخصومات</h2>';
    $content .= '[sale_products limit="4" columns="4"]';

    $content .= '<h2 class="ecm-shop-h">🆕 أحدث المنتجات</h2>';
    $content .= '[products limit="8" columns="4" orderby="date" order="DESC" paginate="false"]';

    $content .= '<h2 class="ecm-shop-h">📂 تصفّح حسب الفئة</h2>';
    $content .= '[product_categories number="6" columns="3" parent="0"]';

    $content .= '<p class="ecm-shop-all" style="text-align:center;"><a class="button" href="' . esc_url( $shop_url ) . '">شوف كل المنتجات</a></p>';
    $content .= '</div>';

    $existing = ecm_page_by_title( 'متجر ECM' );
    if ( $existing ) {
        wp_update_post( [ 'ID' => $existing->ID, 'post_content' => $content, 'post_status' => 'publish' ] );
        $page_id = $existing->ID;
    } else {
        $page_id = wp_insert_post( [
            'post_title'   => 'متجر ECM',
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
    }

    update_option( 'ecm_woo_shop_page_v2', 1 );
}
add_action( 'admin_init', 'ecm_woo_create_shop_page', 40 );

// ── تحديث عدّاد السلة بالـ AJAX (Add to cart) ─────────────────
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
    $count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
    $fragments['span.ecm-cart-count'] = '<span class="ecm-cart-count">' . esc_html( $count ) . '</span>';
    return $fragments;
} );

// ── متجر رقمي بالكامل: لا شحن ولا عنوان شحن ───────────────────
// (يقدر يتعطّل بفلتر ecm_woo_digital_only لو احتجت تبيع منتجات فيزيائية)
if ( apply_filters( 'ecm_woo_digital_only', true ) ) {
    add_filter( 'woocommerce_cart_needs_shipping', '__return_false' );
    add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
}

// ── شارة "رقمي" على كروت المنتجات القابلة للتحميل ─────────────
add_action( 'woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if ( $product && $product->is_downloadable() ) {
        echo '<span class="ecm-woo-digital-badge">⬇ ' . esc_html__( 'رقمي', 'ecm-theme' ) . '</span>';
    }
}, 9 );

// ── إشعار "أنت مشتري المنتج ده بالفعل" في صفحة المنتج ──────────
add_action( 'woocommerce_single_product_summary', function () {
    if ( ! is_user_logged_in() || ! function_exists( 'wc_customer_bought_product' ) ) {
        return;
    }
    global $product;
    if ( ! $product ) {
        return;
    }
    $user = wp_get_current_user();
    if ( ! wc_customer_bought_product( $user->user_email, $user->ID, $product->get_id() ) ) {
        return;
    }

    echo '<div class="ecm-bought-notice">';
    echo '<span class="ecm-bought-ic">✅</span>';
    echo '<div class="ecm-bought-text">';
    echo '<strong>' . esc_html__( 'أنت مشترٍ المنتج ده بالفعل', 'ecm-theme' ) . '</strong>';
    echo '<span>' . esc_html__( 'تقدر ترجع له وتحمّله أي وقت من حسابك.', 'ecm-theme' ) . '</span>';
    if ( $product->is_downloadable() ) {
        echo '<a href="' . esc_url( wc_get_account_endpoint_url( 'downloads' ) ) . '" class="ecm-bought-link">⬇ ' . esc_html__( 'روح لتحميلاتي', 'ecm-theme' ) . '</a>';
    }
    echo '</div></div>';
}, 25 );

// ── شارة "منتج رقمي" فوق عنوان المنتج (للمنتجات القابلة للتحميل) ─
add_action( 'woocommerce_single_product_summary', function () {
    global $product;
    if ( $product && $product->is_downloadable() ) {
        echo '<span class="ecm-single-digital">⬇ ' . esc_html__( 'منتج رقمي', 'ecm-theme' ) . '</span>';
    }
}, 4 );

// ── صفّ الثقة (Trust badges) تحت زر الشراء ───────────────────
add_action( 'woocommerce_single_product_summary', function () {
    $items = [
        [ '🔒', __( 'دفع آمن', 'ecm-theme' ), __( 'معاملات مشفّرة 100%', 'ecm-theme' ) ],
        [ '⚡', __( 'تحميل فوري', 'ecm-theme' ), __( 'بعد الدفع مباشرة', 'ecm-theme' ) ],
        [ '🛡️', __( 'منتج أصلي', 'ecm-theme' ), __( 'مضمون ومُفعّل', 'ecm-theme' ) ],
        [ '💬', __( 'دعم فني', 'ecm-theme' ), __( 'على مدار الأسبوع', 'ecm-theme' ) ],
    ];
    echo '<div class="ecm-trust">';
    foreach ( $items as $it ) {
        echo '<div class="ecm-trust-item"><span class="ecm-trust-ic">' . $it[0] . '</span><div class="ecm-trust-txt"><strong>' . esc_html( $it[1] ) . '</strong><small>' . esc_html( $it[2] ) . '</small></div></div>';
    }
    echo '</div>';
}, 35 );

// ── نص زر "أضف للسلة" أوضح للمنتجات الرقمية ───────────────────
add_filter( 'woocommerce_product_single_add_to_cart_text', function ( $text, $product ) {
    if ( $product && $product->is_downloadable() ) {
        return __( '🛒 أضف للسلة', 'ecm-theme' );
    }
    return $text;
}, 10, 2 );

// ── عدد أعمدة معرض صور المنتج المصغّرة ────────────────────────
add_filter( 'woocommerce_product_thumbnails_columns', function () { return 4; } );

// ── تحسين رسائل WooCommerce (Notices) بستايل الثيم — كلاس إضافي ─
add_filter( 'woocommerce_breadcrumb_defaults', function ( $defaults ) {
    $defaults['delimiter']   = ' <span class="ecm-bc-sep">/</span> ';
    $defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb ecm-woo-breadcrumb">';
    return $defaults;
} );


// ════════════════════════════════════════════════════════════
//  الحساب: إجبار تسجيل الدخول قبل الشراء + تسجيل مستخدم جديد
// ════════════════════════════════════════════════════════════

// ── إعداد WooCommerce لأول مرة: تفعيل التسجيل + إلغاء شراء الضيف ─
function ecm_woo_setup_options() {
    if ( get_option( 'ecm_woo_setup_done' ) ) {
        return;
    }
    update_option( 'woocommerce_enable_myaccount_registration', 'yes' );          // تسجيل من صفحة الحساب
    update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );   // تسجيل من الدفع
    update_option( 'woocommerce_enable_guest_checkout', 'no' );                    // لا شراء كضيف
    update_option( 'woocommerce_registration_generate_username', 'yes' );          // يوزر تلقائي
    update_option( 'woocommerce_registration_generate_password', 'no' );           // المستخدم يحط باسوورد بنفسه
    update_option( 'ecm_woo_setup_done', 1 );
}
add_action( 'admin_init', 'ecm_woo_setup_options' );

// ── اطلب تسجيل الدخول قبل الوصول لصفحة الدفع ──────────────────
add_action( 'template_redirect', function () {
    if ( ! function_exists( 'is_checkout' ) ) {
        return;
    }
    if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) && ! is_user_logged_in() ) {
        wc_add_notice( __( '🔒 سجّل دخولك أو أنشئ حساب جديد لإتمام عملية الشراء.', 'ecm-theme' ), 'notice' );
        wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( wc_get_checkout_url() ), wc_get_page_permalink( 'myaccount' ) ) );
        exit;
    }
} );

// ── بعد الدخول/التسجيل: رجّعه لمكان كان رايحه (الدفع مثلًا) ────
function ecm_woo_after_auth_redirect( $redirect ) {
    if ( ! empty( $_GET['redirect_to'] ) ) {
        return esc_url_raw( wp_unslash( $_GET['redirect_to'] ) );
    }
    return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'ecm_woo_after_auth_redirect', 10, 1 );
add_filter( 'woocommerce_registration_redirect', 'ecm_woo_after_auth_redirect', 10, 1 );

// ── رأس احترافي فوق صفحة الدخول/التسجيل ───────────────────────
add_action( 'woocommerce_before_customer_login_form', function () {
    if ( is_user_logged_in() ) {
        return;
    }
    echo '<div class="ecm-account-hero">'
        . '<span class="ecm-eyebrow"><span class="ecm-eyebrow-dot"></span>ACCOUNT · الحساب</span>'
        . '<h1 class="ecm-account-title">سجّل دخولك أو أنشئ حساب</h1>'
        . '<p class="ecm-account-sub">سجّل دخولك عشان تكمّل الشراء وتوصل لمنتجاتك الرقمية في أي وقت — أو اعمل حساب جديد في ثواني.</p>'
        . '</div>';
} );

// ── تلميح فوق قسم التحميلات (المنتجات الرقمية اللي اشتراها) ────
add_action( 'woocommerce_before_account_downloads', function () {
    echo '<p class="ecm-downloads-hint">⬇ دي كل منتجاتك الرقمية — تقدر تحمّلها في أي وقت من هنا، حتى لو حمّلتها قبل كده.</p>';
} );

// ── لو مفيش تحميلات: رسالة واضحة + زر للمتجر ──────────────────
add_filter( 'woocommerce_account_downloads_columns', function ( $columns ) {
    return $columns; // نسيب الأعمدة زي ما هي
} );


// ════════════════════════════════════════════════════════════
//  تعريب مصطلحات WooCommerce (دخول/حساب/سلة/دفع/منتج/تقييمات)
// ════════════════════════════════════════════════════════════
function ecm_woo_arabic_strings(): array {
    return [
        // تسجيل الدخول / الحساب
        'Login'                              => 'تسجيل الدخول',
        'Log in'                             => 'تسجيل الدخول',
        'Log out'                            => 'تسجيل الخروج',
        'Register'                           => 'إنشاء حساب',
        'Username or email address'          => 'اسم المستخدم أو البريد الإلكتروني',
        'Username or email'                  => 'اسم المستخدم أو البريد الإلكتروني',
        'Username'                           => 'اسم المستخدم',
        'Email address'                      => 'البريد الإلكتروني',
        'Email'                              => 'البريد الإلكتروني',
        'Password'                           => 'كلمة المرور',
        'Remember me'                        => 'تذكّرني',
        'Lost your password?'                => 'نسيت كلمة المرور؟',
        'Reset password'                     => 'إعادة تعيين كلمة المرور',
        'A link to set a new password will be sent to your email address.' => 'هيتبعتلك رابط على بريدك لتعيين كلمة مرور جديدة.',
        'Register an account?'               => 'إنشاء حساب جديد؟',

        // قائمة الحساب
        'My account'                         => 'حسابي',
        'Dashboard'                          => 'الرئيسية',
        'Orders'                             => 'الطلبات',
        'Downloads'                          => 'التحميلات',
        'Addresses'                          => 'العناوين',
        'Account details'                    => 'بيانات الحساب',
        'Payment methods'                    => 'طرق الدفع',
        'First name'                         => 'الاسم الأول',
        'Last name'                          => 'الاسم الأخير',
        'Display name'                       => 'الاسم الظاهر',
        'Current password'                   => 'كلمة المرور الحالية',
        'New password'                       => 'كلمة المرور الجديدة',
        'Confirm new password'               => 'تأكيد كلمة المرور الجديدة',
        'Save changes'                       => 'حفظ التغييرات',

        // المتجر / المنتج
        'Add to cart'                        => 'أضف إلى السلة',
        'Read more'                          => 'اقرأ المزيد',
        'Select options'                     => 'اختر الخيارات',
        'Sale!'                              => 'تخفيض!',
        'In stock'                           => 'متوفر',
        'Out of stock'                       => 'غير متوفر',
        'Category:'                          => 'الفئة:',
        'Categories:'                        => 'الفئات:',
        'Tag:'                               => 'الوسم:',
        'Tags:'                              => 'الوسوم:',
        'SKU:'                               => 'رقم المنتج:',
        'Description'                        => 'الوصف',
        'Additional information'             => 'معلومات إضافية',
        'Related products'                   => 'منتجات ذات صلة',
        'You may also like&hellip;'          => 'قد يعجبك أيضًا…',
        'Quantity'                           => 'الكمية',

        // التقييمات
        'Reviews'                            => 'التقييمات',
        'Your rating'                        => 'تقييمك',
        'Your review'                        => 'مراجعتك',
        'There are no reviews yet.'          => 'لا توجد تقييمات بعد.',
        'Submit'                             => 'إرسال',
        'Name'                               => 'الاسم',

        // السلة
        'Cart'                               => 'السلة',
        'Cart totals'                        => 'إجمالي السلة',
        'Product'                            => 'المنتج',
        'Price'                              => 'السعر',
        'Subtotal'                           => 'المجموع الفرعي',
        'Total'                              => 'الإجمالي',
        'Update cart'                        => 'تحديث السلة',
        'Apply coupon'                       => 'تطبيق الكوبون',
        'Coupon code'                        => 'كود الخصم',
        'Proceed to checkout'                => 'إتمام الشراء',
        'Return to shop'                     => 'العودة للمتجر',
        'Your cart is currently empty.'      => 'سلتك فارغة حاليًا.',
        'Remove this item'                   => 'إزالة هذا المنتج',

        // الدفع
        'Checkout'                           => 'الدفع',
        'Billing details'                    => 'بيانات الفاتورة',
        'Your order'                         => 'طلبك',
        'Place order'                        => 'تأكيد الطلب',
        'Phone'                              => 'رقم الهاتف',
        'Order notes'                        => 'ملاحظات الطلب',
        'Have a coupon?'                     => 'عندك كوبون؟',
        'Click here to enter your code'      => 'اضغط هنا لإدخال الكود',

        // عام
        'Search'                             => 'بحث',
        'Search&hellip;'                     => 'بحث…',
        'Continue'                           => 'متابعة',
    ];
}

// تطبيق الترجمة على نصوص WooCommerce
add_filter( 'gettext', function ( $translated, $text, $domain ) {
    if ( 'woocommerce' !== $domain ) {
        return $translated;
    }
    static $map = null;
    if ( null === $map ) {
        $map = ecm_woo_arabic_strings();
    }
    return $map[ $text ] ?? $translated;
}, 20, 3 );

// نصوص فيها سياق (gettext_with_context)
add_filter( 'gettext_with_context', function ( $translated, $text, $context, $domain ) {
    if ( 'woocommerce' !== $domain ) {
        return $translated;
    }
    static $map = null;
    if ( null === $map ) {
        $map = ecm_woo_arabic_strings();
    }
    return $map[ $text ] ?? $translated;
}, 20, 4 );
