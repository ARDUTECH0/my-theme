<?php
/**
 * ECM — صفحة الإعدادات الموحّدة (Settings Hub)
 *
 * صفحة احترافية بتجمع كل إعدادات ECM في كروت منظّمة مع حالة كل قسم،
 * وبتوصّلك لكل قسم بضغطة. (submenu جوّه لوحة تحكم ECM الموجودة.)
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

// ── إضافة صفحة الإعدادات تحت قائمة ECM الرئيسية ───────────────
add_action( 'admin_menu', function () {
    add_submenu_page(
        'ecm-dashboard',
        __( 'إعدادات ECM', 'ecm-theme' ),
        '⚙️ ' . __( 'الإعدادات', 'ecm-theme' ),
        'manage_options',
        'ecm-settings',
        'ecm_settings_hub_page'
    );
}, 30 );

/** عدد الأجهزة المربوطة حاليًا */
function ecm_settings_bound_count(): int {
    $ids = get_users( [ 'meta_key' => 'ecm_app_device', 'fields' => 'ID', 'number' => 9999, 'count_total' => false ] );
    return is_array( $ids ) ? count( $ids ) : 0;
}

/** صفحة الإعدادات الموحّدة */
function ecm_settings_hub_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $smtp_on  = function_exists( 'ecm_smtp_opts' ) && ! empty( ecm_smtp_opts()['enabled'] );
    $smtp_st  = function_exists( 'ecm_smtp_status' ) ? ecm_smtp_status() : [];
    $smtp_ok  = ! empty( $smtp_st['ok'] );
    $bound    = ecm_settings_bound_count();

    // شارة البريد: متصل / مفعّل / متوقف
    if ( $smtp_ok ) {
        $smtp_badge = [ 'text' => '✅ متصل', 'ok' => true ];
    } elseif ( $smtp_on ) {
        $smtp_badge = [ 'text' => 'مفعّل (اختبر الاتصال)', 'ok' => true ];
    } else {
        $smtp_badge = [ 'text' => 'متوقف', 'ok' => false ];
    }

    $cards = [
        [
            'icon' => '🔗', 'title' => 'ربط التطبيق بالأجهزة',
            'desc' => 'كل حساب مربوط بجهاز واحد. افك الربط لتسجيل دخول من جهاز جديد.',
            'badge' => [ 'text' => $bound . ' جهاز مربوط', 'ok' => true ],
            'url'  => admin_url( 'admin.php?page=ecm-app-binding' ), 'cta' => 'إدارة الربط',
        ],
        [
            'icon' => '📧', 'title' => 'البريد (SMTP)',
            'desc' => 'إرسال الفواتير وإعادة تعيين كلمة المرور عبر إيميل حقيقي.',
            'badge' => $smtp_badge,
            'url'  => admin_url( 'admin.php?page=ecm-smtp' ), 'cta' => 'ضبط البريد',
        ],
        [
            'icon' => '🎨', 'title' => 'تصميم الإيميلات',
            'desc' => 'ألوان وهوية إيميلات ووكومرس (الهيدر، اللوجو، الأخضر).',
            'badge' => [ 'text' => 'بهوية ECM', 'ok' => true ],
            'url'  => admin_url( 'admin.php?page=wc-settings&tab=email' ), 'cta' => 'إعدادات الإيميلات',
        ],
        [
            'icon' => '🛡️', 'title' => 'الأجهزة والسيريالات',
            'desc' => 'إدارة سيريالات الأجهزة وحالتها وربطها بالعملاء.',
            'badge' => null,
            'url'  => admin_url( 'admin.php?page=ecm-serials' ), 'cta' => 'فتح',
        ],
        [
            'icon' => '🔌', 'title' => 'API والتوكن',
            'desc' => 'مفاتيح الـ API ووثائق نقاط الاتصال للتطبيق.',
            'badge' => null,
            'url'  => admin_url( 'admin.php?page=ecm-serials-api' ), 'cta' => 'فتح',
        ],
        [
            'icon' => '📲', 'title' => 'التنزيل من التطبيق',
            'desc' => 'التنزيل من داخل التطبيق فقط — الموقع بيوجّه العميل للتطبيق.',
            'badge' => [ 'text' => 'نشِط', 'ok' => true ],
            'url'  => null, 'cta' => null,
        ],
        [
            'icon' => '🔒', 'title' => 'الأمان',
            'desc' => 'حماية Brute-force، هيدرات أمان، منع حصر المستخدمين، روابط موقّعة.',
            'badge' => [ 'text' => 'مفعّل', 'ok' => true ],
            'url'  => null, 'cta' => null,
        ],
        [
            'icon' => '💾', 'title' => 'نسخة احتياطية',
            'desc' => 'احفظ كل إعداداتك في ملف واسترجعها وقت ما تحب.',
            'badge' => null,
            'url'  => admin_url( 'admin.php?page=ecm-backup' ), 'cta' => 'نسخ / استعادة',
        ],
    ];
    ?>
    <style>
        .ecm-hero{background:linear-gradient(135deg,#0e1a10,#16241a);border:1px solid #9CFF0033;
            border-radius:16px;padding:24px 28px;margin:18px 0 22px;color:#eafff0;
            display:flex;align-items:center;gap:18px;box-shadow:0 8px 30px rgba(0,0,0,.12);}
        .ecm-hero .logo{font-size:40px;line-height:1;}
        .ecm-hero h1{margin:0;color:#9CFF00;font-size:23px;}
        .ecm-hero p{margin:4px 0 0;opacity:.85;font-size:14px;}
        .ecm-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px;}
        .ecm-card{background:#fff;border:1px solid #e4e7e4;border-radius:14px;padding:20px;
            display:flex;flex-direction:column;transition:.18s;}
        .ecm-card:hover{border-color:#9CFF00;box-shadow:0 6px 22px rgba(0,0,0,.07);transform:translateY(-2px);}
        .ecm-card .top{display:flex;align-items:center;gap:12px;margin-bottom:10px;}
        .ecm-card .ico{font-size:28px;line-height:1;}
        .ecm-card h2{margin:0;font-size:16px;color:#0e1a10;}
        .ecm-card p{flex:1;color:#5a6160;font-size:13.5px;line-height:1.65;margin:0 0 14px;}
        .ecm-badge{display:inline-block;font-size:11.5px;font-weight:700;border-radius:999px;
            padding:3px 11px;margin-bottom:10px;width:fit-content;}
        .ecm-badge.ok{background:#9CFF00;color:#0e1a10;}
        .ecm-badge.off{background:#ffe2e2;color:#b40000;}
        .ecm-card .btn{display:inline-block;background:#0e1a10;color:#fff !important;text-decoration:none;
            text-align:center;border-radius:9px;padding:9px 14px;font-weight:700;font-size:13.5px;transition:.18s;}
        .ecm-card .btn:hover{background:#9CFF00;color:#0e1a10 !important;}
        .ecm-card .static{color:#8a8f8d;font-size:12.5px;font-weight:600;}
    </style>

    <div class="wrap">
        <div class="ecm-hero">
            <div class="logo">⚙️</div>
            <div>
                <h1><?php esc_html_e( 'إعدادات ECM', 'ecm-theme' ); ?></h1>
                <p><?php esc_html_e( 'كل إعدادات الموقع والتطبيق في مكان واحد.', 'ecm-theme' ); ?></p>
            </div>
        </div>

        <div class="ecm-cards">
            <?php foreach ( $cards as $c ) : ?>
                <div class="ecm-card">
                    <div class="top">
                        <span class="ico"><?php echo esc_html( $c['icon'] ); ?></span>
                        <h2><?php echo esc_html( $c['title'] ); ?></h2>
                    </div>
                    <?php if ( ! empty( $c['badge'] ) ) : ?>
                        <span class="ecm-badge <?php echo $c['badge']['ok'] ? 'ok' : 'off'; ?>">
                            <?php echo esc_html( $c['badge']['text'] ); ?>
                        </span>
                    <?php endif; ?>
                    <p><?php echo esc_html( $c['desc'] ); ?></p>
                    <?php if ( ! empty( $c['url'] ) ) : ?>
                        <a class="btn" href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['cta'] ); ?> ←</a>
                    <?php else : ?>
                        <span class="static">✔ <?php esc_html_e( 'شغّال أوتوماتيك', 'ecm-theme' ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
