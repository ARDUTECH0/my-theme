<?php
/**
 * ECM Theme — GitHub Remote Updater
 *
 * بيخلّي الثيم يتحدّث من GitHub بضغطة زر (من شاشة المظهر > الثيمات) —
 * من غير ما ترفع الملفات يدويًا كل مرة.
 *
 * الإعداد من: لوحة ECM > 🔄 التحديثات (تكتب owner/repo + توكن اختياري).
 * بعدها لما تعمل Release جديد على GitHub بنسخة أعلى، ووردبريس بيقولك «تحديث متاح».
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;


// ── الإعدادات ────────────────────────────────────────────────
function ecm_gh_repo()  { return trim( (string) get_option( 'ecm_gh_repo', '' ) ); }   // owner/repo
function ecm_gh_token() { return trim( (string) get_option( 'ecm_gh_token', '' ) ); }   // اختياري (للخاص/الحدود)


// ── ترويسات GitHub (User-Agent + توكن) لأي طلب لـ GitHub ─────
function ecm_gh_request_args( $args, $url ) {
    if ( false !== strpos( (string) $url, 'github.com' ) ) {
        if ( empty( $args['headers'] ) || ! is_array( $args['headers'] ) ) {
            $args['headers'] = [];
        }
        $args['headers']['User-Agent'] = 'ECM-Theme-Updater';
        $token = ecm_gh_token();
        if ( $token ) {
            $args['headers']['Authorization'] = 'Bearer ' . $token;
        }
    }
    return $args;
}
add_filter( 'http_request_args', 'ecm_gh_request_args', 10, 2 );


// ── جلب آخر إصدار من GitHub (Release ثم Tags) — مع كاش ────────
function ecm_gh_fetch_latest( $force = false ) {
    $repo = ecm_gh_repo();
    if ( ! $repo ) {
        return null;
    }

    if ( ! $force ) {
        $cached = get_transient( 'ecm_gh_latest' );
        if ( false !== $cached ) {
            return $cached ?: null;
        }
    }

    $latest = null;

    // 1) آخر Release
    $res = wp_remote_get( "https://api.github.com/repos/{$repo}/releases/latest", [ 'timeout' => 15 ] );
    if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
        $d = json_decode( wp_remote_retrieve_body( $res ), true );
        if ( ! empty( $d['tag_name'] ) ) {
            $latest = [
                'version' => ltrim( $d['tag_name'], 'vV' ),
                'zip'     => $d['zipball_url'] ?? '',
                'url'     => $d['html_url'] ?? "https://github.com/{$repo}",
                'notes'   => $d['body'] ?? '',
            ];
        }
    }

    // 2) لو مفيش Release، آخر Tag
    if ( ! $latest ) {
        $res = wp_remote_get( "https://api.github.com/repos/{$repo}/tags", [ 'timeout' => 15 ] );
        if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
            $tags = json_decode( wp_remote_retrieve_body( $res ), true );
            if ( ! empty( $tags[0]['name'] ) ) {
                $latest = [
                    'version' => ltrim( $tags[0]['name'], 'vV' ),
                    'zip'     => $tags[0]['zipball_url'] ?? '',
                    'url'     => "https://github.com/{$repo}",
                    'notes'   => '',
                ];
            }
        }
    }

    set_transient( 'ecm_gh_latest', $latest ?: '', 6 * HOUR_IN_SECONDS );
    return $latest;
}


// ── حقن التحديث في ووردبريس ──────────────────────────────────
function ecm_gh_check_update( $transient ) {
    if ( empty( $transient ) || empty( $transient->checked ) ) {
        return $transient;
    }
    $latest = ecm_gh_fetch_latest();
    if ( ! $latest || empty( $latest['version'] ) || empty( $latest['zip'] ) ) {
        return $transient;
    }

    $slug    = get_template(); // اسم مجلد الثيم
    $current = wp_get_theme( $slug )->get( 'Version' );

    if ( version_compare( $latest['version'], $current, '>' ) ) {
        $transient->response[ $slug ] = [
            'theme'       => $slug,
            'new_version' => $latest['version'],
            'url'         => $latest['url'],
            'package'     => $latest['zip'],
        ];
    } else {
        unset( $transient->response[ $slug ] );
    }
    return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'ecm_gh_check_update' );


// ── تصحيح اسم المجلد بعد فك الـ zip (GitHub بيطلّع اسم غريب) ──
function ecm_gh_fix_source( $source, $remote_source, $upgrader, $hook_extra = [] ) {
    global $wp_filesystem;
    if ( empty( $hook_extra['theme'] ) || get_template() !== $hook_extra['theme'] ) {
        return $source;
    }
    if ( ! $wp_filesystem ) {
        return $source;
    }
    $desired = trailingslashit( $remote_source ) . get_template();
    if ( untrailingslashit( $source ) === untrailingslashit( $desired ) ) {
        return $source;
    }
    if ( $wp_filesystem->move( untrailingslashit( $source ), untrailingslashit( $desired ), true ) ) {
        return trailingslashit( $desired );
    }
    return $source;
}
add_filter( 'upgrader_source_selection', 'ecm_gh_fix_source', 10, 4 );


// ── صفحة الإعدادات في لوحة ECM ───────────────────────────────
function ecm_gh_menu() {
    add_submenu_page(
        'ecm-dashboard',
        'التحديث عن بُعد',
        '🔄 التحديثات',
        'manage_options',
        'ecm-updates',
        'ecm_gh_page'
    );
}
add_action( 'admin_menu', 'ecm_gh_menu', 20 );

function ecm_gh_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // حفظ الإعدادات
    if ( isset( $_POST['ecm_gh_save'] ) && check_admin_referer( 'ecm_gh_nonce' ) ) {
        update_option( 'ecm_gh_repo', sanitize_text_field( wp_unslash( $_POST['ecm_gh_repo'] ?? '' ) ) );
        update_option( 'ecm_gh_token', sanitize_text_field( wp_unslash( $_POST['ecm_gh_token'] ?? '' ) ) );
        delete_transient( 'ecm_gh_latest' );
        delete_site_transient( 'update_themes' );
        echo '<div class="ecm-admin-wrap"><div class="ecm-notice ecm-notice-success">✅ تم الحفظ والتحقق.</div></div>';
    }
    // فحص الآن
    if ( isset( $_POST['ecm_gh_check'] ) && check_admin_referer( 'ecm_gh_nonce' ) ) {
        delete_transient( 'ecm_gh_latest' );
        delete_site_transient( 'update_themes' );
        ecm_gh_fetch_latest( true );
    }

    $repo    = ecm_gh_repo();
    $theme   = wp_get_theme( get_template() );
    $current = $theme->get( 'Version' );
    $latest  = $repo ? ecm_gh_fetch_latest() : null;
    $has_new = $latest && ! empty( $latest['version'] ) && version_compare( $latest['version'], $current, '>' );
    ?>
    <div class="ecm-admin-wrap">
        <div class="ecm-admin-header">
            <div class="ecm-admin-logo">🔄</div>
            <div>
                <h1>التحديث عن بُعد — GitHub</h1>
                <p>حدّث الثيم بضغطة زر من GitHub — من غير رفع يدوي.</p>
            </div>
        </div>

        <!-- الحالة -->
        <h2 class="ecm-dash-section-title">📦 حالة التحديث</h2>
        <div class="ecm-admin-stats" style="grid-template-columns:repeat(3,1fr);">
            <div class="ecm-admin-stat is-ok">
                <span class="ecm-stat-num">v<?php echo esc_html( $current ); ?></span>
                <span class="ecm-stat-label">الإصدار الحالي</span>
            </div>
            <div class="ecm-admin-stat <?php echo $has_new ? 'is-warn' : 'is-ok'; ?>">
                <span class="ecm-stat-num"><?php echo $latest && ! empty( $latest['version'] ) ? 'v' . esc_html( $latest['version'] ) : '—'; ?></span>
                <span class="ecm-stat-label">آخر إصدار على GitHub</span>
            </div>
            <div class="ecm-admin-stat <?php echo $has_new ? 'is-warn' : 'is-ok'; ?>">
                <span class="ecm-stat-num"><?php echo $has_new ? '⬆️ متاح' : '✅ محدّث'; ?></span>
                <span class="ecm-stat-label">الحالة</span>
            </div>
        </div>

        <?php if ( $has_new ) : ?>
        <div class="ecm-notice ecm-notice-warn">
            ⬆️ <strong>تحديث جديد متاح (v<?php echo esc_html( $latest['version'] ); ?>).</strong>
            روح <a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>">المظهر &gt; الثيمات</a> واضغط «تحديث» تحت الثيم — أو من
            <a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>">التحديثات</a>.
        </div>
        <?php elseif ( $repo ) : ?>
        <div class="ecm-notice ecm-notice-success">✅ الثيم على آخر إصدار.</div>
        <?php endif; ?>

        <!-- الإعدادات -->
        <h2 class="ecm-dash-section-title">⚙️ إعداد المستودع</h2>
        <div class="ecm-admin-form">
            <form method="post">
                <?php wp_nonce_field( 'ecm_gh_nonce' ); ?>
                <label>مستودع GitHub (owner/repo)</label>
                <input type="text" name="ecm_gh_repo" value="<?php echo esc_attr( $repo ); ?>" placeholder="مثال: ecameraman/ecm-theme">
                <p style="margin:-8px 0 16px; color:#697086; font-size:12px;">اسم المستخدم/المنظمة ثم اسم الريبو — زي ما هو في رابط GitHub.</p>

                <label>توكن وصول (اختياري)</label>
                <input type="password" name="ecm_gh_token" value="<?php echo esc_attr( ecm_gh_token() ); ?>" placeholder="ghp_... (للمستودعات الخاصة أو تفادي حدود الطلبات)" autocomplete="off">
                <p style="margin:-8px 0 16px; color:#697086; font-size:12px;">سيبه فاضي لو الريبو عام (Public). للريبو الخاص: GitHub > Settings > Developer settings > Personal access tokens.</p>

                <button type="submit" name="ecm_gh_save" class="ecm-admin-btn" style="padding:11px 26px;">💾 حفظ وتحقّق</button>
                <button type="submit" name="ecm_gh_check" class="ecm-admin-btn ecm-admin-btn-ghost" style="padding:11px 26px;">🔄 فحص الآن</button>
            </form>
        </div>

        <div class="ecm-notice ecm-notice-info">
            💡 <strong>إزاي تنشر تحديث:</strong> ارفع الكود على GitHub → اعمل <strong>Release</strong> (أو Tag) بنسخة أعلى
            (مثلاً <code>v<?php echo esc_html( $current ); ?></code> → <code>v3.0.1</code>) — <strong>وافتكر تزوّد رقم الإصدار في style.css كمان</strong>.
            بعد دقايق هتلاقي «تحديث متاح» هنا وفي شاشة الثيمات.
        </div>
    </div>
    <?php
}
