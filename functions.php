<?php
/**
 * ECM Theme Functions
 * E.Camera.Man — WordPress + Elementor Ready
 *
 * @package ecm-theme
 * @version 2.0.0
 */

// ── SECURITY ────────────────────────────────────────────────
defined( 'ABSPATH' ) || exit;


// ── بديل آمن لـ get_page_by_title (المهجورة منذ 6.2) ─────────
function ecm_page_by_title( $title ) {
    $q = new WP_Query( [
        'post_type'              => 'page',
        'post_status'            => 'any',
        'title'                  => $title,
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ] );
    return ! empty( $q->posts ) ? $q->posts[0] : null;
}


// ── AUTO-SETUP PAGES ────────────────────────────────────────
function ecm_ensure_pages() {
    // لو الأدمن مش داخل — لا تشغّل
    if ( ! is_admin() && ! wp_doing_cron() ) return;
    if ( get_option( 'ecm_pages_created_v6' ) ) return;

    // ── صفحة رئيسية ──
    $front_page = ecm_page_by_title( 'ECM Home' );
    if ( ! $front_page ) {
        $page_id = wp_insert_post( [
            'post_title'   => 'ECM Home',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
    } else {
        $page_id = $front_page->ID;
    }
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $page_id );

    // ── صفحة تحميل التطبيق ──
    $app_page = ecm_page_by_title( 'تحميل التطبيق' );
    if ( ! $app_page ) {
        $app_page_id = wp_insert_post( [
            'post_title'   => 'تحميل التطبيق',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
        if ( $app_page_id && ! is_wp_error( $app_page_id ) ) {
            update_post_meta( $app_page_id, '_wp_page_template', 'page-download-app.php' );
            set_theme_mod( 'ecm_app_page_link', get_permalink( $app_page_id ) );
        }
    } else {
        update_post_meta( $app_page->ID, '_wp_page_template', 'page-download-app.php' );
        set_theme_mod( 'ecm_app_page_link', get_permalink( $app_page->ID ) );
    }

    // ── صفحة التوثيق ──
    $docs_page = ecm_page_by_title( 'التوثيق والأدلة' );
    if ( ! $docs_page ) {
        $docs_page_id = wp_insert_post( [
            'post_title'   => 'التوثيق والأدلة',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
        if ( $docs_page_id && ! is_wp_error( $docs_page_id ) ) {
            update_post_meta( $docs_page_id, '_wp_page_template', 'page-documentation.php' );
        }
    } else {
        update_post_meta( $docs_page->ID, '_wp_page_template', 'page-documentation.php' );
    }

    // ── صفحة السوفت وير والتحديثات ──
    $sw_page = ecm_page_by_title( 'السوفت وير والتحديثات' );
    if ( ! $sw_page ) {
        $sw_page_id = wp_insert_post( [
            'post_title'   => 'السوفت وير والتحديثات',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
        if ( $sw_page_id && ! is_wp_error( $sw_page_id ) ) {
            update_post_meta( $sw_page_id, '_wp_page_template', 'page-software.php' );
        }
    } else {
        update_post_meta( $sw_page->ID, '_wp_page_template', 'page-software.php' );
    }

    // ── صفحة رفع السوفت وير ──
    $upload_page = ecm_page_by_title( 'رفع السوفت وير المباشر' );
    if ( ! $upload_page ) {
        $upload_page_id = wp_insert_post( [
            'post_title'   => 'رفع السوفت وير المباشر',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
        if ( $upload_page_id && ! is_wp_error( $upload_page_id ) ) {
            update_post_meta( $upload_page_id, '_wp_page_template', 'page-upload-software.php' );
        }
    } else {
        update_post_meta( $upload_page->ID, '_wp_page_template', 'page-upload-software.php' );
    }

    // ── صفحة الدعم ──
    $support_page = ecm_page_by_title( 'الدعم الفني' );
    if ( ! $support_page ) {
        $support_page_id = wp_insert_post( [
            'post_title'   => 'الدعم الفني',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
        if ( $support_page_id && ! is_wp_error( $support_page_id ) ) {
            update_post_meta( $support_page_id, '_wp_page_template', 'page-support.php' );
        }
    } else {
        update_post_meta( $support_page->ID, '_wp_page_template', 'page-support.php' );
    }

    // ── صفحات الأنظمة (الكاميرا / المركبة / المفاتيح) ──
    $system_pages = [
        'التحكم في الكاميرا'  => 'page-camera-control.php',
        'التحكم في المركبة'   => 'page-vehicle-control.php',
        'لوحة المفاتيح'       => 'page-relay-panel.php',
        'التحكم بذراع الألعاب' => 'page-gamepad.php',
    ];
    foreach ( $system_pages as $title => $template ) {
        $existing = ecm_page_by_title( $title );
        if ( ! $existing ) {
            $pid = wp_insert_post( [
                'post_title'   => $title,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ] );
            if ( $pid && ! is_wp_error( $pid ) ) {
                update_post_meta( $pid, '_wp_page_template', $template );
            }
        } else {
            update_post_meta( $existing->ID, '_wp_page_template', $template );
        }
    }

    update_option( 'ecm_pages_created_v6', true );
}
add_action( 'init', 'ecm_ensure_pages' );

// لو فعّل الثيم من جديد — أعد الإنشاء
function ecm_theme_activate() {
    delete_option( 'ecm_pages_created_v6' );
}
add_action( 'after_switch_theme', 'ecm_theme_activate' );

/**
 * قائمة الصفحات الأساسية المتوقّعة (للعرض في لوحة التحكم)
 * [ العنوان, القالب, هل هي الصفحة الرئيسية ]
 */
function ecm_get_expected_pages() {
    return [
        [ 'ECM Home',               '',                         true  ],
        [ 'تحميل التطبيق',          'page-download-app.php',     false ],
        [ 'التوثيق والأدلة',        'page-documentation.php',    false ],
        [ 'السوفت وير والتحديثات',   'page-software.php',         false ],
        [ 'رفع السوفت وير المباشر',  'page-upload-software.php',  false ],
        [ 'الدعم الفني',            'page-support.php',          false ],
        [ 'التحكم في الكاميرا',     'page-camera-control.php',   false ],
        [ 'التحكم في المركبة',      'page-vehicle-control.php',  false ],
        [ 'لوحة المفاتيح',          'page-relay-panel.php',      false ],
        [ 'التحكم بذراع الألعاب',   'page-gamepad.php',          false ],
    ];
}

/** قائمة تنقّل بين صفحات الأنظمة الثلاثة (تظهر أعلى كل صفحة نظام) */
function ecm_system_pages_nav() {
    $pages   = [
        'التحكم في الكاميرا'  => '🎥',
        'التحكم في المركبة'   => '🚗',
        'لوحة المفاتيح'       => '🔌',
        'التحكم بذراع الألعاب' => '🎮',
    ];
    $current = get_the_title( get_queried_object_id() );

    echo '<nav class="ecm-sys-nav" aria-label="أنظمة ECM">';
    foreach ( $pages as $title => $icon ) {
        $p = ecm_page_by_title( $title );
        if ( ! $p ) {
            continue;
        }
        $is = ( $title === $current ) ? ' is-current' : '';
        printf(
            '<a href="%1$s" class="ecm-sys-nav-item%2$s"><span>%3$s</span> %4$s</a>',
            esc_url( get_permalink( $p->ID ) ),
            esc_attr( $is ),
            esc_html( $icon ),
            esc_html( $title )
        );
    }
    echo '</nav>';
}

/**
 * كارت فيديو — لو فيه YouTube ID بيعرض الصورة المصغّرة الحقيقية ويفتح الفيديو،
 * ولو فاضي بيعرض خانة صورة جاهزة (placeholder) تحطها بعدين.
 */
function ecm_render_video_card( $title, $youtube_id = '', $duration = '' ) {
    $has   = '' !== $youtube_id;
    $url   = $has ? 'https://www.youtube.com/watch?v=' . rawurlencode( $youtube_id ) : '#';
    $thumb = $has ? 'https://img.youtube.com/vi/' . rawurlencode( $youtube_id ) . '/hqdefault.jpg' : '';
    ?>
    <a class="ecm-video-card" href="<?php echo esc_url( $url ); ?>"<?php echo $has ? ' target="_blank" rel="noopener"' : ''; ?>>
        <div class="ecm-video-thumb<?php echo $has ? '' : ' ecm-media-ph'; ?>">
            <?php if ( $has ) : ?>
                <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
            <?php else : ?>
                <span class="ecm-mph-label">صورة الفيديو</span>
            <?php endif; ?>
            <span class="ecm-video-play" aria-hidden="true">▶</span>
        </div>
        <h4 class="ecm-video-title"><?php echo esc_html( $title ); ?></h4>
        <?php if ( $duration ) : ?><span class="ecm-video-dur"><?php echo esc_html( $duration ); ?></span><?php endif; ?>
    </a>
    <?php
}

/** حالة كل صفحة (موجودة؟ + روابط) — للوحة التحكم */
function ecm_pages_status() {
    $out = [];
    foreach ( ecm_get_expected_pages() as $p ) {
        $page = ecm_page_by_title( $p[0] );
        $id   = $page ? (int) $page->ID : 0;
        $out[] = [
            'title'  => $p[0],
            'exists' => (bool) $id,
            'edit'   => $id ? get_edit_post_link( $id, 'raw' ) : '',
            'view'   => $id ? get_permalink( $id ) : '',
        ];
    }
    return $out;
}

/** معالج زر «إعادة بناء الصفحات» من لوحة التحكم */
function ecm_handle_rebuild_pages() {
    if ( empty( $_POST['ecm_rebuild_pages'] ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    check_admin_referer( 'ecm_rebuild_pages_nonce' );

    delete_option( 'ecm_pages_created_v6' );
    ecm_ensure_pages(); // بيعمل الناقص بس — مش بيلمس الموجود

    wp_safe_redirect( admin_url( 'admin.php?page=ecm-dashboard&ecm_pages_rebuilt=1' ) );
    exit;
}
add_action( 'admin_init', 'ecm_handle_rebuild_pages', 6 );


// ── ALLOW APK UPLOADS ───────────────────────────────────────
// يسمح برفع ملفات APK و IPA من خلال مكتبة الوسائط
function ecm_allow_app_uploads( $mimes ) {
    $mimes['apk']  = 'application/vnd.android.package-archive';
    $mimes['ipa']  = 'application/octet-stream';
    $mimes['aab']  = 'application/octet-stream';
    return $mimes;
}
add_filter( 'upload_mimes', 'ecm_allow_app_uploads' );

// Fix for WordPress file type checking
function ecm_check_filetype( $data, $file, $filename, $mimes ) {
    $ext = pathinfo( $filename, PATHINFO_EXTENSION );
    if ( in_array( $ext, [ 'apk', 'ipa', 'aab' ] ) ) {
        $data['ext']  = $ext;
        $data['type'] = $mimes[ $ext ] ?? 'application/octet-stream';
    }
    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'ecm_check_filetype', 10, 4 );


// ── THEME SETUP ─────────────────────────────────────────────
function ecm_theme_setup() {
    load_theme_textdomain( 'ecm-theme', get_template_directory() . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
    ] );
    add_theme_support( 'elementor' );
    add_theme_support( 'elementor-pro' );
    add_theme_support( 'wc-product-gallery-zoom' );

    add_theme_support( 'custom-logo', [
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    add_image_size( 'ecm-hero',    1920, 1080, true );
    add_image_size( 'ecm-card',     800,  600, true );
    add_image_size( 'ecm-feature',  600,  400, true );
    add_image_size( 'ecm-thumb',    400,  300, true );

    // Editor styles — محرر الكتل يعكس شكل الثيم
    add_theme_support( 'editor-styles' );
    add_editor_style( 'editor-style.css' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'ecm_theme_setup' );


// ── ENQUEUE STYLES & FONTS ──────────────────────────────────
function ecm_enqueue_assets() {

    // Google Fonts — Orbitron + Cairo
    wp_enqueue_style(
        'ecm-google-fonts',
        'https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Cairo:wght@300;400;600;700&display=swap',
        [],
        null
    );

    // Main Theme CSS
    wp_enqueue_style(
        'ecm-theme-style',
        get_stylesheet_uri(),
        [ 'ecm-google-fonts' ],
        ECM_VERSION
    );

    // Theme JS (no jQuery dependency needed)
    wp_enqueue_script(
        'ecm-theme-js',
        get_template_directory_uri() . '/js/ecm-theme.js',
        [],
        ECM_VERSION,
        true  // load in footer
    );

    // UX polish — شريط التقدّم + زر العودة للأعلى + نسخ الأكواد
    wp_enqueue_script(
        'ecm-ux-js',
        get_template_directory_uri() . '/js/ecm-ux.js',
        [],
        ECM_VERSION,
        true
    );

    // سلايدر 3D (ودجِت Elementor)
    wp_enqueue_script(
        'ecm-slider-js',
        get_template_directory_uri() . '/js/ecm-slider.js',
        [],
        ECM_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ecm_enqueue_assets' );


// ── THEME VERSION CONSTANT ───────────────────────────────────
define( 'ECM_VERSION', '3.0.64' );


// ── INCLUDE: FRONT PAGE CUSTOMIZER ──────────────────────────
require_once get_template_directory() . '/inc/customizer-frontpage.php';

// ── INCLUDE: APP DOWNLOAD PAGE CUSTOMIZER ───────────────────
require_once get_template_directory() . '/inc/customizer-app.php';

// ── INCLUDE: DOCS PAGE + VIDEOS CUSTOMIZER ──────────────────
require_once get_template_directory() . '/inc/customizer-docs.php';

// ── INCLUDE: SOFTWARE PAGE + NAV CUSTOMIZER ──────────────────
require_once get_template_directory() . '/inc/customizer-software.php';

// ── INCLUDE: SUPPORT PAGE CUSTOMIZER ─────────────────────────
require_once get_template_directory() . '/inc/customizer-support.php';

// ── INCLUDE: ADMIN DASHBOARD ─────────────────────────────────
require_once get_template_directory() . '/inc/admin-dashboard.php';

// ── INCLUDE: التحديث عن بُعد من GitHub ───────────────────────
require_once get_template_directory() . '/inc/github-updater.php';

// ── INCLUDE: ELEMENTOR AUTO-SEEDER ───────────────────────────
// يحوّل تصميم الصفحة الرئيسية لبلوكات Elementor قابلة للتعديل تلقائيًا
require_once get_template_directory() . '/inc/elementor-seed.php';

// ── INCLUDE: زرع صفحات الأنظمة في Elementor ──────────────────
require_once get_template_directory() . '/inc/elementor-seed-systems.php';

// ── INCLUDE: مرونة القالب (قوائم تلقائية + أعمدة فوتر) ────────
require_once get_template_directory() . '/inc/template-flex.php';

// ── INCLUDE: لوجو ثلاثي الأبعاد (.glb) ───────────────────────
require_once get_template_directory() . '/inc/logo-3d.php';

// ── INCLUDE: SEO META (Open Graph + Twitter) ─────────────────
require_once get_template_directory() . '/inc/seo.php';

// ── INCLUDE: قوالب صفحات جاهزة في Elementor ──────────────────
// (بعد elementor-seed عشان يستخدم نفس الـ helpers)
require_once get_template_directory() . '/inc/elementor-starter-templates.php';

// ── INCLUDE: تكرار الصفحات (Duplicate) ───────────────────────
require_once get_template_directory() . '/inc/duplicate-page.php';

// ── INCLUDE: متجر WooCommerce (منتجات رقمية) ─────────────────
require_once get_template_directory() . '/inc/woocommerce.php';

// ── INCLUDE: حقول المنتج (صورة التطبيق + الشرح) ──────────────
require_once get_template_directory() . '/inc/product-fields.php';

// ── INCLUDE: تحسينات الأداء ──────────────────────────────────
require_once get_template_directory() . '/inc/performance.php';

// ── INCLUDE: تقوية الأمان ────────────────────────────────────
require_once get_template_directory() . '/inc/security.php';

// ── INCLUDE: تسجيل الدخول بحساب Google ───────────────────────
require_once get_template_directory() . '/inc/google-login.php';

// ── INCLUDE: صورة المستخدم (أفاتار جوجل/مرفوع) ───────────────
require_once get_template_directory() . '/inc/user-avatar.php';

// ── INCLUDE: الفواتير الاحترافية ─────────────────────────────
require_once get_template_directory() . '/inc/invoice.php';

// ── INCLUDE: QR Code لروابط التحميل ──────────────────────────
require_once get_template_directory() . '/inc/download-qr.php';

// ── INCLUDE: حماية الأجهزة بالسيريال + ربط بالإيميل ──────────
require_once get_template_directory() . '/inc/serial-protection.php';

// ── INCLUDE: ربط التطبيق بجهاز واحد ─────────────────────────
require_once get_template_directory() . '/inc/app-binding.php';

// ── INCLUDE: التنزيل من التطبيق فقط ─────────────────────────
require_once get_template_directory() . '/inc/app-only-downloads.php';

// ── INCLUDE: إعداد البريد (SMTP) ────────────────────────────
require_once get_template_directory() . '/inc/email-smtp.php';

// ── INCLUDE: تصميم إيميلات ووكومرس ──────────────────────────
require_once get_template_directory() . '/inc/email-design.php';

// ── INCLUDE: صفحة الإعدادات الموحّدة ────────────────────────
require_once get_template_directory() . '/inc/admin-settings-hub.php';

// ── INCLUDE: النسخ الاحتياطي للإعدادات ──────────────────────
require_once get_template_directory() . '/inc/settings-backup.php';


// ── ELEMENTOR PRO — THEME BUILDER LOCATIONS ──────────────────
// بيخلّي قوالب الهيدر/الفوتر اللي تتعمل في Elementor Pro (Theme Builder)
// تحلّ مكان هيدر/فوتر الثيم. لو مفيش قالب، الثيم بيعرض شكله الأصلي.
function ecm_register_elementor_locations( $manager ) {

    if ( method_exists( $manager, 'register_location' ) ) {

        $manager->register_location( 'header' );
        $manager->register_location( 'footer' );
        $manager->register_location( 'single' );
        $manager->register_location( 'archive' );
    }
}
add_action( 'elementor/theme/register_locations', 'ecm_register_elementor_locations' );


// ── MENUS ────────────────────────────────────────────────────
function ecm_register_menus() {
    register_nav_menus( [
        'primary-menu' => __( 'القائمة الرئيسية', 'ecm-theme' ),
        'footer-menu'  => __( 'قائمة الفوتر',    'ecm-theme' ),
    ] );
}
add_action( 'init', 'ecm_register_menus' );


// ── SIDEBAR / WIDGET AREA ────────────────────────────────────
function ecm_register_sidebars() {
    register_sidebar( [
        'name'          => __( 'الشريط الجانبي', 'ecm-theme' ),
        'id'            => 'ecm-sidebar',
        'description'   => __( 'المنطقة الجانبية — تظهر في صفحات المقالات', 'ecm-theme' ),
        'before_widget' => '<div id="%1$s" class="ecm-widget %2$s" style="margin-bottom: 24px;">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 style="font-family:\'Orbitron\',monospace; font-size:12px; letter-spacing:2px; color:var(--ecm-white); text-transform:uppercase; margin-bottom:16px;">',
        'after_title'   => '</h4>',
    ] );
}
add_action( 'widgets_init', 'ecm_register_sidebars' );


// ── CUSTOM SEARCH FORM ───────────────────────────────────────
function ecm_search_form( $form ) {
    $form = '<form role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '" style="display:flex; gap:8px; max-width:480px; margin:0 auto;">
        <input type="search" name="s" placeholder="' . esc_attr__( 'ابحث...', 'ecm-theme' ) . '" value="' . esc_attr( get_search_query() ) . '" style="
            flex:1;
            background: var(--ecm-bg-panel);
            border: 1px solid var(--ecm-border);
            border-radius: var(--ecm-radius-sm);
            padding: 12px 16px;
            color: var(--ecm-grey-light);
            font-family: Cairo, sans-serif;
            font-size: 14px;
            outline: none;
        ">
        <button type="submit" class="ecm-btn-primary" style="padding: 12px 20px !important;">بحث</button>
    </form>';
    return $form;
}
add_filter( 'get_search_form', 'ecm_search_form' );


// ── ELEMENTOR CUSTOM CATEGORY ────────────────────────────────
function ecm_register_elementor_categories( $elements_manager ) {
    $elements_manager->add_category(
        'ecm-elements',
        [
            'title' => __( '🎬 ECM Elements', 'ecm-theme' ),
            'icon'  => 'fa fa-camera',
        ]
    );
}
add_action( 'elementor/elements/categories_registered', 'ecm_register_elementor_categories' );


// ── ELEMENTOR — خطوط الثيم في قائمة الاختيار ─────────────────
// بيخلّي Cairo و Orbitron يظهروا تحت مجموعة «خطوط ECM» في أول القائمة
function ecm_elementor_font_groups( $groups ) {
    $ecm = [ 'ecm' => __( '⭐ خطوط ECM', 'ecm-theme' ) ];
    return $ecm + $groups;
}
add_filter( 'elementor/fonts/groups', 'ecm_elementor_font_groups' );

function ecm_elementor_additional_fonts( $fonts ) {
    $fonts['Cairo']    = 'ecm'; // خط النصوص العربي
    $fonts['Orbitron'] = 'ecm'; // خط العناوين التقني
    return $fonts;
}
add_filter( 'elementor/fonts/additional_fonts', 'ecm_elementor_additional_fonts' );


// ── ELEMENTOR CUSTOM WIDGETS ─────────────────────────────────
function ecm_register_elementor_widgets() {
    $widgets = [
        'widget-stat-box',
        'widget-ctrl-card',
        'widget-feat-card',
        'widget-spec-row',
        'widget-eyebrow',
        'widget-logo-3d',
        'widget-slider-3d',
        'widget-control-grid',
    ];

    foreach ( $widgets as $widget ) {
        $file = get_template_directory() . '/elementor/' . $widget . '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }

    $classes = [
        'ECM_Widget_Stat_Box',
        'ECM_Widget_Ctrl_Card',
        'ECM_Widget_Feat_Card',
        'ECM_Widget_Spec_Row',
        'ECM_Widget_Eyebrow',
        'ECM_Widget_Logo_3D',
        'ECM_Widget_Slider_3D',
        'ECM_Widget_Control_Grid',
    ];

    foreach ( $classes as $class ) {
        if ( class_exists( $class ) ) {
            \Elementor\Plugin::instance()->widgets_manager->register( new $class() );
        }
    }
}
add_action( 'elementor/widgets/register', 'ecm_register_elementor_widgets' );


// ── CHECK IF PAGE IS BUILT WITH ELEMENTOR ────────────────────
// يستخدم في القوالب الكبيرة لمعرفة هل الصفحة مبنية بواسطة
// Elementor — لو كذلك نعرض محتوى Elementor بدل التصميم الجاهز
function ecm_is_built_with_elementor( $post_id ) {
    if ( ! $post_id || ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
        return false;
    }
    $document = \Elementor\Plugin::$instance->documents->get( $post_id );
    return $document && $document->is_built_with_elementor();
}


// ── REMOVE WORDPRESS DEFAULT STYLES ─────────────────────────
function ecm_remove_wp_block_styles() {
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-block-style' );
    wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'ecm_remove_wp_block_styles', 100 );


// ── CLEAN HEAD ───────────────────────────────────────────────
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_generator' );


// ── ALLOW SVG UPLOADS ────────────────────────────────────────
function ecm_allow_svg( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'ecm_allow_svg' );


// ── ACF FIELD GROUPS ─────────────────────────────────────────
if ( function_exists( 'acf_add_local_field_group' ) ) {
    acf_add_local_field_group( [
        'key'    => 'group_ecm_hero',
        'title'  => 'ECM — Hero Section',
        'fields' => [
            [
                'key'   => 'field_hero_eyebrow',
                'label' => 'Eyebrow Text',
                'name'  => 'hero_eyebrow',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_hero_title',
                'label' => 'Hero Title',
                'name'  => 'hero_title',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_hero_desc',
                'label' => 'Hero Description',
                'name'  => 'hero_desc',
                'type'  => 'textarea',
            ],
        ],
        'location' => [
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ] ],
        ],
    ] );
}


// ════════════════════════════════════════════════════════════
// THEME CUSTOMIZER
// ════════════════════════════════════════════════════════════
function ecm_customizer_settings( $wp_customize ) {

    // ── PANEL ──────────────────────────────────────────────
    $wp_customize->add_panel( 'ecm_panel', [
        'title'    => '🎬 ECM Theme Settings',
        'priority' => 10,
    ] );


    // ── SECTION: BRAND COLORS ──────────────────────────────
    $wp_customize->add_section( 'ecm_colors', [
        'title'       => 'ألوان الثيم',
        'description' => 'تخصيص ألوان الموقع — التغيير فوري في المعاينة',
        'panel'       => 'ecm_panel',
    ] );

    // Primary accent color
    $wp_customize->add_setting( 'ecm_green_color', [
        'default'           => '#9cff00',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_hex_color',
    ] );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ecm_green_color', [
        'label'       => 'اللون الرئيسي (Accent)',
        'description' => 'اللون الأخضر المستخدم في الأزرار والعناوين والعناصر المميزة',
        'section'     => 'ecm_colors',
    ] ) );

    // Background deep
    $wp_customize->add_setting( 'ecm_bg_deep', [
        'default'           => '#111214',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_hex_color',
    ] );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ecm_bg_deep', [
        'label'   => 'لون الخلفية الرئيسية',
        'section' => 'ecm_colors',
    ] ) );

    // Background card
    $wp_customize->add_setting( 'ecm_bg_card', [
        'default'           => '#1a1d22',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_hex_color',
    ] );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ecm_bg_card', [
        'label'   => 'لون خلفية الكروت',
        'section' => 'ecm_colors',
    ] ) );


    // ── SECTION: HEADER ────────────────────────────────────
    $wp_customize->add_section( 'ecm_header_section', [
        'title' => 'إعدادات الهيدر',
        'panel' => 'ecm_panel',
    ] );

    // Nav CTA text
    $wp_customize->add_setting( 'ecm_nav_cta_text', [
        'default'           => 'اطلب الآن',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'ecm_nav_cta_text', [
        'label'   => 'نص زر الهيدر',
        'section' => 'ecm_header_section',
        'type'    => 'text',
    ] );


    // ── SECTION: CONTACT ───────────────────────────────────
    $wp_customize->add_section( 'ecm_contact', [
        'title' => 'بيانات التواصل',
        'panel' => 'ecm_panel',
    ] );

    $wp_customize->add_setting( 'ecm_whatsapp', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'ecm_whatsapp', [
        'label'       => 'رقم WhatsApp (مع كود الدولة)',
        'description' => 'مثال: 201012345678',
        'section'     => 'ecm_contact',
        'type'        => 'text',
    ] );

    $wp_customize->add_setting( 'ecm_email', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_email',
    ] );
    $wp_customize->add_control( 'ecm_email', [
        'label'   => 'البريد الإلكتروني',
        'section' => 'ecm_contact',
        'type'    => 'email',
    ] );

    $wp_customize->add_setting( 'ecm_instagram', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'ecm_instagram', [
        'label'   => 'رابط Instagram',
        'section' => 'ecm_contact',
        'type'    => 'url',
    ] );

    $wp_customize->add_setting( 'ecm_youtube', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'ecm_youtube', [
        'label'   => 'رابط YouTube',
        'section' => 'ecm_contact',
        'type'    => 'url',
    ] );


    // ── SECTION: FOOTER ────────────────────────────────────
    $wp_customize->add_section( 'ecm_footer_section', [
        'title' => 'إعدادات الفوتر',
        'panel' => 'ecm_panel',
    ] );

    $wp_customize->add_setting( 'ecm_footer_copy', [
        'default'           => '© ' . gmdate( 'Y' ) . ' E.Camera.Man — جميع الحقوق محفوظة',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'wp_kses_post',
    ] );
    $wp_customize->add_control( 'ecm_footer_copy', [
        'label'   => 'نص حقوق النشر',
        'section' => 'ecm_footer_section',
        'type'    => 'textarea',
    ] );

    $wp_customize->add_setting( 'ecm_footer_tagline', [
        'default'           => 'PROFESSIONAL CAMERA CONTROL',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'ecm_footer_tagline', [
        'label'   => 'شعار الفوتر الصغير',
        'section' => 'ecm_footer_section',
        'type'    => 'text',
    ] );
}
add_action( 'customize_register', 'ecm_customizer_settings' );


// ── CUSTOMIZER LIVE PREVIEW JS ───────────────────────────────
function ecm_customizer_preview_js() {
    wp_enqueue_script(
        'ecm-customizer',
        get_template_directory_uri() . '/js/ecm-customizer.js',
        [ 'customize-preview' ],
        ECM_VERSION,
        true
    );
}
add_action( 'customize_preview_init', 'ecm_customizer_preview_js' );


// ── DYNAMIC INLINE CSS (من الـ Customizer) ───────────────────
function ecm_dynamic_css() {
    $green    = get_theme_mod( 'ecm_green_color', '#9cff00' );
    $bg_deep  = get_theme_mod( 'ecm_bg_deep',     '#111214' );
    $bg_card  = get_theme_mod( 'ecm_bg_card',     '#1a1d22' );

    $green_dim  = ecm_darken_hex( $green, 28 );
    $green_glow = ecm_hex_to_rgba( $green, 0.20 );
    $green_sub  = ecm_hex_to_rgba( $green, 0.07 );

    echo "<style id=\"ecm-dynamic-css\">\n";
    echo ":root {\n";
    echo "  --ecm-green:          {$green};\n";
    echo "  --ecm-green-dim:      {$green_dim};\n";
    echo "  --ecm-green-glow:     {$green_glow};\n";
    echo "  --ecm-green-subtle:   {$green_sub};\n";
    echo "  --ecm-bg-deep:        {$bg_deep};\n";
    echo "  --ecm-bg-card:        {$bg_card};\n";
    echo "}\n";
    echo "</style>\n";
}
add_action( 'wp_head', 'ecm_dynamic_css' );


// ── COLOR HELPER FUNCTIONS ───────────────────────────────────
/**
 * Convert HEX to rgba()
 */
function ecm_hex_to_rgba( string $hex, float $alpha = 1 ): string {
    $hex = ltrim( $hex, '#' );
    if ( strlen( $hex ) === 3 ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );
    return "rgba({$r},{$g},{$b},{$alpha})";
}

/**
 * Darken a HEX color by a given percentage
 */
function ecm_darken_hex( string $hex, int $percent ): string {
    $hex = ltrim( $hex, '#' );
    $r   = max( 0, hexdec( substr( $hex, 0, 2 ) ) - (int) round( 255 * $percent / 100 ) );
    $g   = max( 0, hexdec( substr( $hex, 2, 2 ) ) - (int) round( 255 * $percent / 100 ) );
    $b   = max( 0, hexdec( substr( $hex, 4, 2 ) ) - (int) round( 255 * $percent / 100 ) );
    return sprintf( '#%02x%02x%02x', $r, $g, $b );
}
