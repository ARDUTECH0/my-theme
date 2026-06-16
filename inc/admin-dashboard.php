<?php
/**
 * ECM Admin Dashboard — لوحة تحكم احترافية داخل ووردبريس
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

// ══════════════════════════════════════════════════
// §1  إضافة صفحة الأدمن
// ══════════════════════════════════════════════════
function ecm_admin_menu() {
    add_menu_page(
        'ECM Dashboard',
        '⚙️ ECM لوحة التحكم',
        'manage_options',
        'ecm-dashboard',
        'ecm_dashboard_page',
        'dashicons-camera-alt',
        3
    );

    add_submenu_page(
        'ecm-dashboard',
        'ECM — لوحة التحكم',
        '🏠 الرئيسية',
        'manage_options',
        'ecm-dashboard',
        'ecm_dashboard_page'
    );

    add_submenu_page(
        'ecm-dashboard',
        'إدارة التطبيقات — App Manager',
        '📱 التطبيقات',
        'manage_options',
        'ecm-apps',
        'ecm_apps_page'
    );

    add_submenu_page(
        'ecm-dashboard',
        'إدارة السوفت وير — Firmware',
        '🔧 السوفت وير',
        'manage_options',
        'ecm-firmware',
        'ecm_firmware_page'
    );
}
add_action( 'admin_menu', 'ecm_admin_menu' );


// ══════════════════════════════════════════════════
// §2  ستايل الأدمن
// ══════════════════════════════════════════════════
function ecm_admin_styles( $hook = '' ) {
    // admin_head مابيبعتش الـ hook suffix — نتأكد من الصفحة بطريقة موثوقة
    $screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    $screen_id = $screen ? $screen->id : '';
    $page      = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

    $is_ecm = ( $hook && strpos( $hook, 'ecm-' ) !== false )
        || ( $screen_id && strpos( $screen_id, 'ecm-' ) !== false )
        || ( $page && strpos( $page, 'ecm-' ) === 0 );

    if ( ! $is_ecm ) {
        return;
    }
    ?>
    <style>
        /* ═══ ECM Admin — Professional Dashboard ═══ */
        #wpcontent, #wpbody-content { background: #eef0f4; }
        .ecm-admin-wrap { max-width: 1200px; margin: 18px auto 44px; padding: 0 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Cairo', Tahoma, sans-serif; color: #1d2127; direction: rtl; }
        .ecm-admin-wrap * { box-sizing: border-box; }

        /* ── Header ── */
        .ecm-admin-header { position: relative; overflow: hidden; background: radial-gradient(135% 150% at 100% 0%, #1c2128 0%, #0e1013 62%); color: #fff; padding: 36px 38px; border-radius: 22px; margin-bottom: 26px; display: flex; align-items: center; gap: 24px; box-shadow: 0 22px 48px -20px rgba(8,10,13,0.6); border: 1px solid rgba(255,255,255,0.05); }
        .ecm-admin-header::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(156,255,0,0.045) 1px, transparent 1px), linear-gradient(90deg, rgba(156,255,0,0.045) 1px, transparent 1px); background-size: 28px 28px; pointer-events: none; }
        .ecm-admin-header::after { content: ''; position: absolute; inset-inline-end: -90px; top: -90px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(156,255,0,0.20), transparent 68%); pointer-events: none; }
        .ecm-admin-header > * { position: relative; z-index: 1; }
        .ecm-admin-header h1 { font-size: 25px; font-weight: 800; margin: 0; letter-spacing: -0.4px; line-height: 1.2; }
        .ecm-admin-header p { margin: 9px 0 0; color: #99a0b1; font-size: 14px; max-width: 640px; line-height: 1.65; }
        .ecm-admin-logo { font-size: 40px; width: 78px; height: 78px; display: flex; align-items: center; justify-content: center; background: linear-gradient(150deg, rgba(156,255,0,0.15), rgba(255,255,255,0.03)); border: 1px solid rgba(156,255,0,0.28); border-radius: 18px; flex-shrink: 0; box-shadow: inset 0 0 26px rgba(156,255,0,0.07); }
        .ecm-admin-chip { display: inline-block; margin-inline-start: 12px; padding: 4px 13px; border-radius: 30px; background: rgba(156,255,0,0.13); border: 1px solid rgba(156,255,0,0.40); color: #c2f56e; font-size: 12px; font-weight: 700; vertical-align: middle; letter-spacing: 0.3px; }

        /* ── Section titles ── */
        .ecm-dash-section-title { font-size: 13px; font-weight: 800; color: #11151b; text-transform: uppercase; letter-spacing: 1.3px; margin: 36px 0 16px; display: flex; align-items: center; gap: 11px; }
        .ecm-dash-section-title::before { content: ''; width: 4px; height: 18px; border-radius: 4px; background: linear-gradient(#9cff00, #5aad0a); box-shadow: 0 0 10px rgba(156,255,0,0.5); }
        .ecm-dash-section-title:first-of-type { margin-top: 6px; }

        /* ── Grid + Cards ── */
        .ecm-admin-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 8px; }
        .ecm-admin-card { position: relative; background: #fff; border: 1px solid #e7e9ef; border-radius: 16px; padding: 26px 24px; transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease; box-shadow: 0 1px 2px rgba(17,18,20,0.04); overflow: hidden; }
        .ecm-admin-card::before { content: ''; position: absolute; top: 0; inset-inline: 0; height: 3px; background: linear-gradient(90deg, #9cff00, #5aad0a); opacity: 0; transition: opacity 0.22s; }
        .ecm-admin-card:hover { transform: translateY(-4px); box-shadow: 0 18px 38px -14px rgba(17,18,20,0.18); border-color: #dfe2ea; }
        .ecm-admin-card:hover::before { opacity: 1; }
        .ecm-admin-card-icon { font-size: 24px; width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; background: linear-gradient(150deg, #f4fce6, #e9f7d2); border: 1px solid #dcefc0; border-radius: 14px; margin-bottom: 16px; }
        .ecm-admin-card h3 { font-size: 16px; font-weight: 700; color: #14181e; margin: 0 0 8px; }
        .ecm-admin-card p { font-size: 13px; color: #697086; margin: 0 0 18px; line-height: 1.7; }

        /* ── Buttons ── */
        .ecm-admin-btn { display: inline-flex; align-items: center; gap: 7px; background: #5aad0a; color: #fff !important; padding: 9px 20px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700; transition: all 0.2s; border: none; cursor: pointer; box-shadow: 0 6px 14px -7px rgba(90,173,10,0.75); }
        .ecm-admin-btn:hover { background: #4d9a06; transform: translateY(-1px); color: #fff !important; box-shadow: 0 11px 20px -8px rgba(90,173,10,0.85); }
        .ecm-admin-btn-ghost { background: #fff; border: 1px solid #e2e4ea; color: #1a1d22 !important; box-shadow: none; }
        .ecm-admin-btn-ghost:hover { border-color: #5aad0a; color: #3d7a06 !important; background: #f7fcef; box-shadow: none; }

        /* ── Tables ── */
        .ecm-admin-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border-radius: 14px; overflow: hidden; border: 1px solid #e7e9ef; box-shadow: 0 1px 2px rgba(17,18,20,0.04); }
        .ecm-admin-table th { background: #f7f8fa; padding: 14px 18px; text-align: right; font-size: 11px; color: #7a8090; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
        .ecm-admin-table td { padding: 14px 18px; border-top: 1px solid #eef0f4; font-size: 14px; color: #3a3f4a; }
        .ecm-admin-table tbody tr:hover td { background: #fafbfc; }

        /* ── Badges ── */
        .ecm-admin-badge { display: inline-block; padding: 4px 11px; border-radius: 30px; font-size: 11px; font-weight: 700; }
        .ecm-badge-green { background: #e9f7dc; color: #3d7a06; }
        .ecm-badge-orange { background: #fff2e0; color: #d9760a; }
        .ecm-badge-blue { background: #e6f1fe; color: #1668d6; }
        .ecm-badge-red { background: #fdeaea; color: #c0392b; }
        .ecm-badge-grey { background: #eef0f3; color: #6b7080; }

        /* ── Forms / panels ── */
        .ecm-admin-form { background: #fff; border: 1px solid #e7e9ef; border-radius: 16px; padding: 28px; margin-bottom: 22px; box-shadow: 0 1px 2px rgba(17,18,20,0.04); }
        .ecm-admin-form h2 { font-size: 18px; font-weight: 700; margin: 0 0 20px; color: #14181e; }
        .ecm-admin-form label { display: block; font-size: 13px; font-weight: 600; color: #3a3f4a; margin-bottom: 7px; }
        .ecm-admin-form input[type="text"],
        .ecm-admin-form input[type="url"],
        .ecm-admin-form input[type="date"],
        .ecm-admin-form input[type="number"],
        .ecm-admin-form textarea,
        .ecm-admin-form select { width: 100%; padding: 11px 14px; border: 1px solid #e2e4ea; border-radius: 10px; font-size: 14px; margin-bottom: 16px; background: #fbfcfd; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s; }
        .ecm-admin-form input:focus, .ecm-admin-form textarea:focus, .ecm-admin-form select:focus { border-color: #9cd24a; box-shadow: 0 0 0 3px rgba(156,255,0,0.16); outline: none; background: #fff; }
        .ecm-admin-form textarea { min-height: 84px; resize: vertical; }
        .ecm-admin-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .ecm-admin-section-title { position: relative; font-size: 16px; font-weight: 700; color: #14181e; margin: 24px 0 16px; padding-bottom: 11px; border-bottom: 2px solid #eef0f4; }
        .ecm-admin-section-title::after { content: ''; position: absolute; bottom: -2px; inset-inline-start: 0; width: 60px; height: 2px; background: linear-gradient(90deg, #9cff00, #5aad0a); }

        /* ── Notices ── */
        .ecm-notice { padding: 14px 18px; border-radius: 12px; margin-bottom: 14px; font-size: 13.5px; line-height: 1.75; border: 1px solid transparent; }
        .ecm-notice a { font-weight: 700; text-decoration: underline; }
        .ecm-notice-success { background: #f0fae6; color: #356d05; border-color: #d3ecbb; }
        .ecm-notice-info { background: #eef5fe; color: #1559b0; border-color: #cfe2fa; }
        .ecm-notice-warn { background: #fff6e8; color: #9c5a00; border-color: #fbe2b8; }

        /* ── Stats ── */
        .ecm-admin-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 8px; }
        .ecm-admin-stat { position: relative; background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; padding: 20px 22px 20px 26px; box-shadow: 0 1px 2px rgba(17,18,20,0.04); transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; }
        .ecm-admin-stat:hover { transform: translateY(-2px); box-shadow: 0 14px 28px -16px rgba(17,18,20,0.22); }
        .ecm-admin-stat::after { content: ''; position: absolute; inset-inline-start: 0; top: 14px; bottom: 14px; width: 3px; border-radius: 4px; background: #e2e4ea; }
        .ecm-admin-stat .ecm-stat-num { display: block; font-size: 24px; font-weight: 800; color: #14181e; margin-bottom: 4px; letter-spacing: -0.4px; }
        .ecm-admin-stat .ecm-stat-label { font-size: 12px; color: #697086; font-weight: 600; }
        .ecm-admin-stat.is-ok::after { background: linear-gradient(#9cff00, #5aad0a); }
        .ecm-admin-stat.is-ok .ecm-stat-num { color: #3d7a06; }
        .ecm-admin-stat.is-warn::after { background: #f0a728; }
        .ecm-admin-stat.is-warn .ecm-stat-num { color: #d98300; }

        /* ── Repeater rows (Apps / Firmware) ── */
        .ecm-repeater-row { position: relative; padding-inline-end: 64px; }
        .ecm-row-remove { position: absolute; top: 18px; inset-inline-end: 18px; width: 36px; height: 36px; border-radius: 10px; border: 1px solid #f3c9c9; background: #fdeeee; color: #c0392b; font-size: 15px; cursor: pointer; transition: all 0.2s; }
        .ecm-row-remove:hover { background: #f9d6d6; transform: translateY(-1px); }
        .ecm-admin-btn-add { display: block; width: 100%; background: #fbfcfd; border: 2px dashed #ccd2dc; color: #697086; padding: 15px; border-radius: 12px; text-align: center; cursor: pointer; font-size: 14px; font-weight: 700; margin-bottom: 20px; transition: all 0.2s; }
        .ecm-admin-btn-add:hover { border-color: #9cd24a; color: #3d7a06; background: #f6fcec; }

        /* ── Footer ── */
        .ecm-admin-footer { margin-top: 30px; padding: 18px 24px; background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 13px; color: #697086; box-shadow: 0 1px 2px rgba(17,18,20,0.04); }
        .ecm-admin-footer strong { color: #14181e; }
        .ecm-admin-footer a { color: #4d9a06; text-decoration: none; font-weight: 600; }
        .ecm-admin-footer a:hover { text-decoration: underline; }

        @media (max-width: 782px) {
            .ecm-admin-grid { grid-template-columns: 1fr; }
            .ecm-admin-row { grid-template-columns: 1fr; }
            .ecm-admin-stats { grid-template-columns: repeat(2, 1fr); }
            .ecm-admin-header { flex-direction: column; text-align: center; padding: 28px 22px; }
        }
    </style>
    <?php
}
add_action( 'admin_head', 'ecm_admin_styles' );


// ══════════════════════════════════════════════════
// §2.1  دوال مساعدة
// ══════════════════════════════════════════════════

/**
 * يحدّث theme_mod أو يحذفه لو القيمة فاضية — عشان القيم الافتراضية ترجع تظهر
 */
function ecm_sync_theme_mod( $key, $value ) {
    if ( $value === '' || $value === null ) {
        remove_theme_mod( $key );
    } else {
        set_theme_mod( $key, $value );
    }
}

/**
 * فوتر موحّد لكل صفحات لوحة التحكم
 */
function ecm_admin_footer() {
    $theme = wp_get_theme();
    ?>
    <div class="ecm-admin-footer">
        <span>🎬 <strong>ECM — E.Camera.Man</strong> · إصدار الثيم <?php echo esc_html( $theme->get( 'Version' ) ); ?></span>
        <span>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ecm-dashboard' ) ); ?>">لوحة التحكم</a> ·
            <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">تخصيص</a> ·
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">عرض الموقع ↗</a>
        </span>
    </div>
    <?php
}

/**
 * صف واحد في جدول إدارة التطبيقات
 */
function ecm_render_app_row( $app = [] ) {
    $app = wp_parse_args( $app, [
        'name'     => '',
        'version'  => '',
        'platform' => 'android',
        'size'     => '',
        'link'     => '',
        'notes'    => '',
        'status'   => 'stable',
    ] );
    ?>
    <div class="ecm-admin-form ecm-repeater-row">
        <button type="button" class="ecm-row-remove" title="حذف التطبيق">🗑</button>
        <div class="ecm-admin-row">
            <div>
                <label>اسم التطبيق — App Name</label>
                <input type="text" name="app_name[]" value="<?php echo esc_attr( $app['name'] ); ?>" placeholder="مثال: ECM Android App">
            </div>
            <div>
                <label>رقم الإصدار — Version</label>
                <input type="text" name="app_version[]" value="<?php echo esc_attr( $app['version'] ); ?>" placeholder="1.0.0">
            </div>
        </div>
        <div class="ecm-admin-row">
            <div>
                <label>المنصة — Platform</label>
                <select name="app_platform[]">
                    <option value="android" <?php selected( $app['platform'], 'android' ); ?>>🤖 Android (APK/AAB)</option>
                    <option value="ios" <?php selected( $app['platform'], 'ios' ); ?>>🍎 iOS (IPA)</option>
                    <option value="windows" <?php selected( $app['platform'], 'windows' ); ?>>💻 Windows (EXE)</option>
                    <option value="mac" <?php selected( $app['platform'], 'mac' ); ?>>🖥 macOS (DMG)</option>
                </select>
            </div>
            <div>
                <label>حجم الملف — File Size</label>
                <input type="text" name="app_size[]" value="<?php echo esc_attr( $app['size'] ); ?>" placeholder="45 MB">
            </div>
        </div>
        <div>
            <label>رابط التحميل — Download Link</label>
            <input type="url" name="app_link[]" value="<?php echo esc_attr( $app['link'] ); ?>" placeholder="https://yoursite.com/app.apk">
        </div>
        <div class="ecm-admin-row">
            <div>
                <label>ملاحظات — Notes</label>
                <textarea name="app_notes[]" placeholder="ملاحظات عن هذا الإصدار..."><?php echo esc_textarea( $app['notes'] ); ?></textarea>
            </div>
            <div>
                <label>الحالة — Status</label>
                <select name="app_status[]">
                    <option value="stable" <?php selected( $app['status'], 'stable' ); ?>>🟢 مستقر — Stable</option>
                    <option value="beta" <?php selected( $app['status'], 'beta' ); ?>>🟡 تجريبي — Beta</option>
                    <option value="coming" <?php selected( $app['status'], 'coming' ); ?>>🔵 قريباً — Coming Soon</option>
                    <option value="deprecated" <?php selected( $app['status'], 'deprecated' ); ?>>🔴 قديم — Deprecated</option>
                </select>
            </div>
        </div>
    </div>
    <?php
}

/**
 * صف واحد في جدول إدارة السوفت وير
 */
function ecm_render_fw_row( $fw = [], $label = '' ) {
    $fw = wp_parse_args( $fw, [
        'version' => '',
        'date'    => '',
        'size'    => '',
        'link'    => '',
        'notes'   => '',
        'status'  => 'stable',
    ] );
    ?>
    <div class="ecm-admin-form ecm-repeater-row">
        <button type="button" class="ecm-row-remove" title="حذف الإصدار">🗑</button>
        <?php if ( $label ) : ?><h2><?php echo esc_html( $label ); ?></h2><?php endif; ?>
        <div class="ecm-admin-row">
            <div>
                <label>رقم الإصدار — Version</label>
                <input type="text" name="fw_version[]" value="<?php echo esc_attr( $fw['version'] ); ?>" placeholder="2.0.0">
            </div>
            <div>
                <label>تاريخ الإصدار — Release Date</label>
                <input type="text" name="fw_date[]" value="<?php echo esc_attr( $fw['date'] ); ?>" placeholder="2024-06-01">
            </div>
        </div>
        <div class="ecm-admin-row">
            <div>
                <label>حجم الملف — File Size</label>
                <input type="text" name="fw_size[]" value="<?php echo esc_attr( $fw['size'] ); ?>" placeholder="128 MB">
            </div>
            <div>
                <label>الحالة — Status</label>
                <select name="fw_status[]">
                    <option value="stable" <?php selected( $fw['status'], 'stable' ); ?>>🟢 مستقر — Stable</option>
                    <option value="beta" <?php selected( $fw['status'], 'beta' ); ?>>🟡 تجريبي — Beta</option>
                    <option value="deprecated" <?php selected( $fw['status'], 'deprecated' ); ?>>🔴 قديم — Deprecated</option>
                </select>
            </div>
        </div>
        <div>
            <label>⬇️ رابط التحميل — Download Link</label>
            <input type="url" name="fw_link[]" value="<?php echo esc_attr( $fw['link'] ); ?>" placeholder="https://yoursite.com/firmware.bin">
        </div>
        <div>
            <label>ملاحظات الإصدار — Release Notes</label>
            <textarea name="fw_notes[]" placeholder="ما الجديد في هذا الإصدار..."><?php echo esc_textarea( $fw['notes'] ); ?></textarea>
        </div>
    </div>
    <?php
}

/**
 * سكريبت الإضافة/الحذف الديناميكي — مشترك بين صفحتي التطبيقات والسوفت وير
 */
function ecm_repeater_script( $list_id, $add_btn_id, $template_id ) {
    ?>
    <script>
    (function () {
        var list = document.getElementById('<?php echo esc_js( $list_id ); ?>');
        var addBtn = document.getElementById('<?php echo esc_js( $add_btn_id ); ?>');
        var tpl = document.getElementById('<?php echo esc_js( $template_id ); ?>');

        if ( addBtn && tpl && list ) {
            addBtn.addEventListener('click', function () {
                list.appendChild( tpl.content.cloneNode(true) );
            });
        }

        if ( list ) {
            list.addEventListener('click', function (e) {
                var btn = e.target.closest('.ecm-row-remove');
                if ( ! btn ) return;
                var row = btn.closest('.ecm-repeater-row');
                if ( row ) row.remove();
            });
        }
    })();
    </script>
    <?php
}


// ══════════════════════════════════════════════════
// §3  الداشبورد الرئيسي
// ══════════════════════════════════════════════════
function ecm_dashboard_page() {
    $theme = wp_get_theme();

    // حالة Elementor
    $elementor_active = did_action( 'elementor/loaded' );

    // إحصائيات سريعة
    $apps = get_option( 'ecm_apps_data', [] );
    $apps_count = 0;
    foreach ( $apps as $app ) {
        if ( ! empty( $app['name'] ) ) $apps_count++;
    }

    $fw_version = get_theme_mod( 'ecm_sw_version', '' );
    ?>
    <div class="ecm-admin-wrap">
        <div class="ecm-admin-header">
            <div class="ecm-admin-logo">📸</div>
            <div>
                <h1>ECM — E.Camera.Man <span class="ecm-admin-chip">v<?php echo esc_html( $theme->get( 'Version' ) ); ?></span></h1>
                <p>لوحة تحكم الثيم — Theme Dashboard · أدِر المحتوى والتطبيقات والسوفت وير من مكان واحد</p>
            </div>
        </div>

        <!-- نظرة سريعة -->
        <div class="ecm-admin-stats">
            <div class="ecm-admin-stat <?php echo $apps_count > 0 ? 'is-ok' : 'is-warn'; ?>">
                <span class="ecm-stat-num"><?php echo esc_html( $apps_count ); ?></span>
                <span class="ecm-stat-label">📱 تطبيقات مُضافة</span>
            </div>
            <div class="ecm-admin-stat <?php echo $fw_version ? 'is-ok' : 'is-warn'; ?>">
                <span class="ecm-stat-num"><?php echo $fw_version ? esc_html( 'v' . $fw_version ) : '—'; ?></span>
                <span class="ecm-stat-label">🔧 إصدار السوفت وير الحالي</span>
            </div>
            <div class="ecm-admin-stat <?php echo $elementor_active ? 'is-ok' : 'is-warn'; ?>">
                <span class="ecm-stat-num"><?php echo $elementor_active ? '✅ مفعّل' : '⚠️ غير مفعّل'; ?></span>
                <span class="ecm-stat-label">🧩 Elementor</span>
            </div>
            <div class="ecm-admin-stat is-ok">
                <span class="ecm-stat-num"><?php echo esc_html( $theme->get( 'Version' ) ); ?></span>
                <span class="ecm-stat-label">🎬 إصدار الثيم</span>
            </div>
        </div>

        <?php if ( ! $elementor_active ) : ?>
        <div class="ecm-notice ecm-notice-warn">
            🧩 <strong>Elementor غير مُفعّل حاليًا.</strong>
            لتقدر تفتح أي صفحة وتعدّلها بالسحب والإفراغ (Drag &amp; Drop) عن طريق "Edit with Elementor"،
            لازم تثبّت وتفعّل بلجن Elementor الأول.
            <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>">تثبيت Elementor الآن</a>
            — بعد التفعيل، روح لأي صفحة من <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">الصفحات</a>
            وهتلاقي زرار "Edit with Elementor" فوق الصفحة.
        </div>
        <?php else : ?>
        <div class="ecm-notice ecm-notice-success">
            ✅ <strong>Elementor مُفعّل.</strong> روح لأي صفحة من <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">الصفحات</a>
            واضغط "Edit with Elementor" لتعديلها بالكامل بالسحب والإفراغ.
        </div>
        <?php endif; ?>

        <?php
        // ── حالة زرع Elementor + إعادة البناء (تشخيص) ──
        if ( $elementor_active && function_exists( 'ecm_elementor_pages_status' ) ) :
            if ( isset( $_GET['ecm_reseeded'] ) ) {
                $rc = (int) get_transient( 'ecm_reseed_count' );
                delete_transient( 'ecm_reseed_count' );
                echo '<div class="ecm-notice ecm-notice-success">🔁 تمت إعادة بناء <strong>' . esc_html( $rc ) . '</strong> صفحة بـ Elementor. افتح أي صفحة بـ "تعديل بـ Elementor" للتأكد.</div>';
            }
            $status = ecm_elementor_pages_status();
        ?>
        <h2 class="ecm-dash-section-title">🧩 حالة صفحات Elementor</h2>
        <div class="ecm-admin-form">
            <p style="margin:0 0 16px; color:#6b7080; font-size:13px;">
                لو أي صفحة طالعة <strong>فاضية</strong>، اضغط «إعادة بناء» تحت — ده بيكتب تصميم الثيم
                جوه Elementor من جديد. <strong>تنبيه:</strong> الإعادة بتكتب فوق أي تعديل عملته في Elementor.
            </p>
            <table class="ecm-admin-table" style="margin-bottom:20px;">
                <thead>
                    <tr><th>الصفحة</th><th>الحالة</th><th>حجم البيانات</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    <?php foreach ( $status as $row ) :
                        list( $label, $pid, $built, $bytes, $edit ) = $row; ?>
                    <tr>
                        <td><strong><?php echo esc_html( $label ); ?></strong></td>
                        <td>
                            <?php if ( ! $pid ) : ?>
                                <span class="ecm-admin-badge ecm-badge-grey">الصفحة مش موجودة</span>
                            <?php elseif ( $built ) : ?>
                                <span class="ecm-admin-badge ecm-badge-green">✅ مزروعة بـ Elementor</span>
                            <?php else : ?>
                                <span class="ecm-admin-badge ecm-badge-orange">⚠️ فاضية / مش مزروعة</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $bytes ? esc_html( number_format( $bytes ) . ' B' ) : '—'; ?></td>
                        <td><?php if ( $pid && $edit ) : ?><a href="<?php echo esc_url( $edit ); ?>" class="ecm-admin-btn ecm-admin-btn-ghost" style="padding:5px 12px;">تعديل بـ Elementor</a><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post" onsubmit="return confirm('متأكد؟ ده هيكتب فوق أي تعديل عملته في Elementor للصفحات دي.');">
                <?php wp_nonce_field( 'ecm_reseed_nonce' ); ?>
                <button type="submit" name="ecm_reseed" value="1" class="ecm-admin-btn">🔁 إعادة بناء صفحات Elementor</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- حالة الصفحات + إعادة البناء -->
        <h2 class="ecm-dash-section-title">📄 حالة الصفحات</h2>
        <?php if ( isset( $_GET['ecm_pages_rebuilt'] ) ) : ?>
            <div class="ecm-notice ecm-notice-success">✅ تمت إعادة بناء الصفحات — اتعمل أي صفحة كانت ناقصة.</div>
        <?php endif; ?>
        <div class="ecm-admin-form">
            <p style="margin:0 0 16px; color:#697086; font-size:13px;">
                دي صفحات الموقع الأساسية. لو أي صفحة طالعة <strong>«غير موجودة»</strong> (اتحذفت مثلاً)،
                اضغط <strong>«إعادة بناء الصفحات»</strong> وهيتعمل الناقص تلقائيًا — <strong>مش بيلمس الصفحات الموجودة ولا محتواها</strong>.
            </p>
            <table class="ecm-admin-table" style="margin-bottom:20px;">
                <thead><tr><th>الصفحة</th><th>الحالة</th><th>إجراء</th></tr></thead>
                <tbody>
                    <?php foreach ( ecm_pages_status() as $row ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $row['title'] ); ?></strong></td>
                        <td>
                            <?php if ( $row['exists'] ) : ?>
                                <span class="ecm-admin-badge ecm-badge-green">✅ موجودة</span>
                            <?php else : ?>
                                <span class="ecm-admin-badge ecm-badge-orange">⚠️ غير موجودة</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $row['exists'] ) : ?>
                                <a href="<?php echo esc_url( $row['view'] ); ?>" target="_blank" rel="noopener" class="ecm-admin-btn ecm-admin-btn-ghost" style="padding:5px 12px;">عرض ↗</a>
                                <?php if ( $row['edit'] ) : ?>
                                    <a href="<?php echo esc_url( $row['edit'] ); ?>" class="ecm-admin-btn ecm-admin-btn-ghost" style="padding:5px 12px;">تعديل</a>
                                <?php endif; ?>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post">
                <?php wp_nonce_field( 'ecm_rebuild_pages_nonce' ); ?>
                <button type="submit" name="ecm_rebuild_pages" value="1" class="ecm-admin-btn">🔁 إعادة بناء الصفحات</button>
            </form>
        </div>

        <!-- المحتوى والتصميم -->
        <h2 class="ecm-dash-section-title">🎨 المحتوى والتصميم</h2>
        <div class="ecm-admin-grid">
            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">🎨</div>
                <h3>تخصيص المظهر — Customize</h3>
                <p>غيّر الألوان والخطوط واللوجو والسلايدر وكل عناصر الصفحة الرئيسية</p>
                <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="ecm-admin-btn">فتح المخصص</a>
            </div>

            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">📄</div>
                <h3>الصفحات — Pages</h3>
                <p>تعديل صفحة التحميل والتوثيق والسوفت وير، أو تعديلها بالكامل عن طريق Elementor</p>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="ecm-admin-btn ecm-admin-btn-ghost">عرض الصفحات</a>
            </div>

            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">📋</div>
                <h3>القوائم — Menus</h3>
                <p>تعديل قائمة النافيبار وإضافة روابط جديدة</p>
                <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="ecm-admin-btn ecm-admin-btn-ghost">تعديل القوائم</a>
            </div>
        </div>

        <!-- تركيب الموقع: الهيدر والفوتر -->
        <h2 class="ecm-dash-section-title">🧱 تركيب الموقع — الهيدر والفوتر</h2>

        <?php $ecm_pro = function_exists( 'elementor_theme_do_location' ); ?>
        <?php if ( $ecm_pro ) : ?>
        <div class="ecm-notice ecm-notice-success">
            🚀 <strong>Elementor Pro متاح — دعم كامل لكل القوالب بالسحب والإفلات!</strong>
            روح <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=elementor_library&tabs_group=theme' ) ); ?>"><strong>Templates &gt; Theme Builder</strong></a>
            واعمل أي قالب: <strong>Header · Footer · Single (مقال/صفحة) · Archive · Search · 404</strong>،
            ابنيه واضبط <em>Display Conditions</em>. هيحلّ مكان بتاع الثيم تلقائيًا.
            <br>👈 طول ما معملتش قالب لنوع معيّن، <strong>الشكل الأصلي بتاع الثيم بيفضل زي ما هو بالظبط</strong> — مفيش أي كسر.
        </div>
        <?php endif; ?>

        <div class="ecm-notice ecm-notice-info">
            🧭 <?php echo $ecm_pro ? 'أو من غير ما تبني في Elementor: ' : ''; ?>شكل الهيدر والفوتر ثابت زي التصميم الأصلي (بكل الحركات) — وتعدّل محتواهم من غير كود:
            <br>• <strong>روابط الهيدر</strong>: من <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">القوائم</a> «القائمة الرئيسية» — تسحب، ترتّب، تغيّر كل رابط يودّي فين.
            <br>• <strong>روابط الفوتر</strong>: من نفس مكان <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">القوائم</a> «قائمة الفوتر».
            <br>• <strong>اللوجو وزر التحميل والتاجلاين والحقوق</strong>: من <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">التخصيص</a>.
            <br>• <strong>أعمدة إضافية في الفوتر</strong> (اختياري): من <a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>">الودجِت</a> — سيبها فاضية لو عايز الشكل الافتراضي.
        </div>
        <div class="ecm-admin-grid">
            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">🔝</div>
                <h3>الهيدر — Header</h3>
                <p>عدّل روابط النافيبار وترتيبها ووجهتها من القوائم (مكان: «القائمة الرئيسية»)</p>
                <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="ecm-admin-btn">تعديل قائمة الهيدر</a>
            </div>
            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">🔻</div>
                <h3>الفوتر — Footer</h3>
                <p>ابنِ أعمدة الفوتر بالسحب والإفراغ (روابط، نص، قوائم...) من الودجِت</p>
                <a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>" class="ecm-admin-btn ecm-admin-btn-ghost">أعمدة الفوتر</a>
            </div>
            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">➕</div>
                <h3>صفحة جديدة — New Page</h3>
                <p>أضف أي صفحة وابنِها بـ Elementor (اختَر قالب «ECM — عرض كامل» للبناء الحر)</p>
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" class="ecm-admin-btn ecm-admin-btn-ghost">أضف صفحة</a>
            </div>
        </div>

        <div class="ecm-notice ecm-notice-info">
            🧩 <strong>قوالب صفحات جاهزة في Elementor:</strong> لما تعمل صفحة جديدة وتفتحها بـ
            <strong>Edit with Elementor</strong>، اضغط أيقونة المجلد <strong>«Add Template»</strong> →
            تبويب <strong>My Templates</strong> هتلاقي قوالب ECM جاهزة:
            <strong>سياسة الخصوصية · الشروط والأحكام · من نحن · اتصل بنا · صفحة احترافية · صور وشرح · معرض صور · عرض منتج 3D</strong> —
            تدخل أي واحد وتملا بياناتك بس.
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=elementor_library&elementor_library_type=page' ) ); ?>">عرض القوالب المحفوظة</a>
        </div>

        <div class="ecm-notice ecm-notice-info">
            🧊 <strong>لوجو ثلاثي الأبعاد (3D):</strong> ارفع ملف <code>.glb</code> من
            <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">الوسائط</a>، وبعدين حُط رابطه في
            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=ecm_logo_3d' ) ); ?>">التخصيص &gt; 🧊 لوجو ثلاثي الأبعاد</a>
            — هيظهر بدل اللوجو العادي في الهيدر مع دوران تلقائي.
        </div>

        <!-- التطبيقات والسوفت وير -->
        <h2 class="ecm-dash-section-title">📦 التطبيقات والسوفت وير</h2>
        <div class="ecm-admin-grid">
            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">📱</div>
                <h3>إدارة التطبيقات — Apps</h3>
                <p>أضف أو عدّل أو احذف تطبيقات APK / IPA وأدّر الإصدارات وروابط التحميل بدون أي حد أقصى</p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ecm-apps' ) ); ?>" class="ecm-admin-btn">إدارة التطبيقات</a>
            </div>

            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">🔧</div>
                <h3>السوفت وير — Firmware</h3>
                <p>ارفع تحديثات السوفت وير، أضف أو احذف إصدارات وأدّر الـ Changelog</p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ecm-firmware' ) ); ?>" class="ecm-admin-btn">إدارة السوفت وير</a>
            </div>

            <div class="ecm-admin-card">
                <div class="ecm-admin-card-icon">🖼</div>
                <h3>الوسائط — Media</h3>
                <p>ارفع صور السلايدر واللوجو وملفات التحميل (APK, IPA, AAB)</p>
                <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" class="ecm-admin-btn ecm-admin-btn-ghost">مكتبة الوسائط</a>
            </div>
        </div>

        <!-- روابط سريعة للتخصيص -->
        <h2 class="ecm-dash-section-title">⚡ روابط سريعة للتخصيص</h2>
        <div class="ecm-admin-form">
            <div class="ecm-admin-grid">
                <?php
                $links = [
                    [ '🎬 السلايدر', 'ecm_fp_slide_1', 'صور وعناوين السلايدر' ],
                    [ '📊 الإحصائيات', 'ecm_fp_stats', 'أرقام الأداء' ],
                    [ '⚙️ كروت التحكم', 'ecm_fp_controls', 'بيانات المحاور' ],
                    [ '✨ المميزات', 'ecm_fp_features', 'خصائص المنتج' ],
                    [ '📐 المواصفات', 'ecm_fp_specs', 'الجدول الفني' ],
                    [ '📖 التوثيق', 'ecm_fp_docs', 'كروت الأدلة' ],
                    [ '📞 قسم التواصل', 'ecm_fp_cta', 'أزرار CTA' ],
                    [ '🎥 الفيديوهات', 'ecm_videos', 'روابط YouTube' ],
                    [ '🔧 السوفت وير', 'ecm_software', 'إصدارات وروابط' ],
                ];
                foreach ( $links as $l ) :
                ?>
                <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=' . $l[1] ) ); ?>" style="text-decoration:none;">
                    <div class="ecm-admin-card" style="padding: 16px;">
                        <strong style="font-size: 14px;"><?php echo esc_html( $l[0] ); ?></strong>
                        <p style="margin: 4px 0 0; font-size: 12px;"><?php echo esc_html( $l[2] ); ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php ecm_admin_footer(); ?>
    </div>
    <?php
}


// ══════════════════════════════════════════════════
// §4  إدارة التطبيقات (إضافة / حذف ديناميكي)
// ══════════════════════════════════════════════════
function ecm_apps_page() {
    // Handle save
    if ( isset( $_POST['ecm_save_apps'] ) && check_admin_referer( 'ecm_apps_nonce' ) ) {
        $apps   = [];
        $names  = (array) ( $_POST['app_name'] ?? [] );
        $links  = (array) ( $_POST['app_link'] ?? [] );

        foreach ( $names as $i => $name ) {
            $name = sanitize_text_field( $name );
            $link = esc_url_raw( $links[$i] ?? '' );

            if ( $name === '' && $link === '' ) continue;

            $apps[] = [
                'name'     => $name,
                'version'  => sanitize_text_field( $_POST['app_version'][$i] ?? '' ),
                'platform' => sanitize_text_field( $_POST['app_platform'][$i] ?? 'android' ),
                'size'     => sanitize_text_field( $_POST['app_size'][$i] ?? '' ),
                'link'     => $link,
                'notes'    => sanitize_textarea_field( $_POST['app_notes'][$i] ?? '' ),
                'status'   => sanitize_text_field( $_POST['app_status'][$i] ?? 'stable' ),
            ];
        }

        update_option( 'ecm_apps_data', $apps );
        echo '<div class="ecm-notice ecm-notice-success">✅ تم حفظ التطبيقات بنجاح — Apps saved successfully!</div>';
    }

    $apps = get_option( 'ecm_apps_data', [] );

    // أول مرة — اعرض تطبيقين كمثال للبدء
    if ( empty( $apps ) ) {
        $apps = [
            [ 'name' => 'ECM Android App', 'version' => '1.0.0', 'platform' => 'android', 'size' => '45 MB', 'link' => '', 'notes' => 'التطبيق الرسمي لنظام ECM', 'status' => 'stable' ],
            [ 'name' => 'ECM iOS App', 'version' => '1.0.0', 'platform' => 'ios', 'size' => '38 MB', 'link' => '', 'notes' => '', 'status' => 'coming' ],
        ];
    }
    ?>
    <div class="ecm-admin-wrap">
        <div class="ecm-admin-header">
            <div class="ecm-admin-logo">📱</div>
            <div>
                <h1>إدارة التطبيقات — App Manager</h1>
                <p>أضف، عدّل أو احذف تطبيقات ECM لجميع المنصات — بدون أي حد أقصى لعددها</p>
            </div>
        </div>

        <div class="ecm-notice ecm-notice-info">
            💡 <strong>كيف ترفع تطبيق:</strong> روح <a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">مكتبة الوسائط</a> > ارفع ملف APK/IPA > انسخ الرابط > الصقه هنا.
            استخدم زرار <strong>"+ إضافة تطبيق جديد"</strong> لإضافة تطبيقات جديدة، أو 🗑 لحذف أي تطبيق.
        </div>

        <form method="post" id="ecm-apps-form">
            <?php wp_nonce_field( 'ecm_apps_nonce' ); ?>

            <div id="ecm-apps-list">
                <?php foreach ( $apps as $app ) : ecm_render_app_row( $app ); endforeach; ?>
            </div>

            <button type="button" class="ecm-admin-btn-add" id="ecm-add-app">+ إضافة تطبيق جديد — Add New App</button>

            <button type="submit" name="ecm_save_apps" class="ecm-admin-btn" style="padding: 12px 32px; font-size: 15px;">
                💾 حفظ التطبيقات — Save Apps
            </button>
        </form>

        <template id="ecm-app-row-template">
            <?php ecm_render_app_row(); ?>
        </template>

        <?php ecm_admin_footer(); ?>
    </div>
    <?php
    ecm_repeater_script( 'ecm-apps-list', 'ecm-add-app', 'ecm-app-row-template' );
}


// ══════════════════════════════════════════════════
// §5  إدارة السوفت وير (إضافة / حذف ديناميكي)
// ══════════════════════════════════════════════════
function ecm_firmware_page() {
    // Handle save
    if ( isset( $_POST['ecm_save_fw'] ) && check_admin_referer( 'ecm_fw_nonce' ) ) {
        $fw_list  = [];
        $versions = (array) ( $_POST['fw_version'] ?? [] );
        $links    = (array) ( $_POST['fw_link'] ?? [] );

        foreach ( $versions as $i => $version ) {
            $version = sanitize_text_field( $version );
            $link    = esc_url_raw( $links[$i] ?? '' );

            if ( $version === '' && $link === '' ) continue;

            $fw_list[] = [
                'version' => $version,
                'date'    => sanitize_text_field( $_POST['fw_date'][$i] ?? '' ),
                'size'    => sanitize_text_field( $_POST['fw_size'][$i] ?? '' ),
                'link'    => $link,
                'notes'   => sanitize_textarea_field( $_POST['fw_notes'][$i] ?? '' ),
                'status'  => sanitize_text_field( $_POST['fw_status'][$i] ?? 'stable' ),
            ];
        }

        update_option( 'ecm_firmware_data', $fw_list );

        // أول إصدار = الحالي، ثاني إصدار = السابق — دول بس اللي يظهروا في صفحة السوفت وير
        $current  = $fw_list[0] ?? null;
        $previous = $fw_list[1] ?? null;

        ecm_sync_theme_mod( 'ecm_sw_version',       $current['version'] ?? '' );
        ecm_sync_theme_mod( 'ecm_sw_date',          $current['date']    ?? '' );
        ecm_sync_theme_mod( 'ecm_sw_size',          $current['size']    ?? '' );
        ecm_sync_theme_mod( 'ecm_sw_download_link', $current['link']    ?? '' );
        ecm_sync_theme_mod( 'ecm_sw_prev_version',  $previous['version'] ?? '' );
        ecm_sync_theme_mod( 'ecm_sw_prev_link',     $previous['link']    ?? '' );

        echo '<div class="ecm-notice ecm-notice-success">✅ تم حفظ السوفت وير بنجاح — Firmware saved!</div>';
    }

    $fw_list = get_option( 'ecm_firmware_data', [] );

    if ( empty( $fw_list ) ) {
        $fw_list = [
            [ 'version' => '2.0.0', 'date' => '2024-06-01', 'size' => '128 MB', 'link' => '', 'notes' => 'إصدار مستقر — تحسينات الأداء', 'status' => 'stable' ],
            [ 'version' => '1.9.5', 'date' => '2024-05-01', 'size' => '125 MB', 'link' => '', 'notes' => 'الإصدار السابق المستقر', 'status' => 'stable' ],
        ];
    }
    ?>
    <div class="ecm-admin-wrap">
        <div class="ecm-admin-header">
            <div class="ecm-admin-logo">🔧</div>
            <div>
                <h1>إدارة السوفت وير — Firmware Manager</h1>
                <p>أضف، عدّل أو احذف إصدارات السوفت وير — البيانات بتتحدث في الموقع تلقائياً</p>
            </div>
        </div>

        <div class="ecm-notice ecm-notice-info">
            💡 <strong>ملاحظة:</strong> أول إصدار في القائمة = الإصدار الحالي (يظهر في الموقع كـ Latest).
            ثاني إصدار = الإصدار السابق (Fallback). أي إصدارات إضافية تُحفظ كأرشيف فقط ولا تظهر في صفحة السوفت وير حاليًا.
            استخدم 🗑 لحذف أي إصدار، أو "+ إضافة إصدار جديد" لإضافة إصدار.
        </div>

        <form method="post" id="ecm-fw-form">
            <?php wp_nonce_field( 'ecm_fw_nonce' ); ?>

            <div id="ecm-fw-list">
                <?php foreach ( $fw_list as $i => $f ) :
                    $label = $i === 0 ? '✅ الإصدار الحالي — Latest' : ( $i === 1 ? '📦 الإصدار السابق — Previous' : '📁 إصدار أرشيف — Archive' );
                    ecm_render_fw_row( $f, $label );
                endforeach; ?>
            </div>

            <button type="button" class="ecm-admin-btn-add" id="ecm-add-fw">+ إضافة إصدار جديد — Add New Version</button>

            <button type="submit" name="ecm_save_fw" class="ecm-admin-btn" style="padding: 12px 32px; font-size: 15px;">
                💾 حفظ السوفت وير — Save Firmware
            </button>
        </form>

        <template id="ecm-fw-row-template">
            <?php ecm_render_fw_row( [], '📁 إصدار أرشيف — Archive' ); ?>
        </template>

        <?php ecm_admin_footer(); ?>
    </div>
    <?php
    ecm_repeater_script( 'ecm-fw-list', 'ecm-add-fw', 'ecm-fw-row-template' );
}
