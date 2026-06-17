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
        $avatar  = get_avatar( $current->ID, 30, '', $current->display_name, [ 'class' => 'ecm-woo-avatar' ] );
        $items  .= '<li class="menu-item ecm-woo-menu ecm-woo-account"><a href="' . esc_url( $account_url ) . '" title="' . esc_attr( $current->display_name ) . '">' . $avatar . '<span>' . esc_html__( 'حسابي', 'ecm-theme' ) . '</span></a></li>';
        $items  .= '<li class="menu-item ecm-woo-menu ecm-woo-logout"><a href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '"><span class="ecm-woo-ic">↩</span>' . esc_html__( 'خروج', 'ecm-theme' ) . '</a></li>';
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

// ── شارة "منتج رقمي" فوق عنوان المنتج (للمنتجات القابلة للتحميل) ─
add_action( 'woocommerce_single_product_summary', function () {
    global $product;
    if ( $product && $product->is_downloadable() ) {
        echo '<span class="ecm-single-digital">⬇ ' . esc_html__( 'منتج رقمي', 'ecm-theme' ) . '</span>';
    }
}, 4 );

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
