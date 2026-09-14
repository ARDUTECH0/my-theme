<?php
/**
 * ECM — لوحة تحكم التحديث عن بُعد (OTA)
 *
 * رفع إصدارات الفيرموير، إدارة القنوات والطرح التدريجي، ومتابعة أسطول الأجهزة.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

// ── القائمة ───────────────────────────────────────────────────
add_action( 'admin_menu', function () {
    add_submenu_page(
        'ecm-dashboard',
        __( 'التحديث عن بُعد — OTA', 'ecm-theme' ),
        '📡 ' . __( 'التحديث عن بُعد', 'ecm-theme' ),
        'manage_options',
        'ecm-ota',
        'ecm_ota_admin_page'
    );
}, 20 );

/** أقصى حجم لملف الفيرموير (بايت) */
function ecm_ota_max_upload(): int {
    return (int) apply_filters( 'ecm_ota_max_upload', 16 * 1024 * 1024 );
}

/** صياغة الحجم */
function ecm_ota_fmt_size( $bytes ): string {
    return size_format( (int) $bytes, 1 ) ?: '—';
}

/** "من ساعتين" */
function ecm_ota_ago( $mysql_date ): string {
    $ts = $mysql_date ? strtotime( $mysql_date ) : 0;
    if ( ! $ts ) {
        return '—';
    }
    return sprintf( __( 'من %s', 'ecm-theme' ), human_time_diff( $ts, current_time( 'timestamp' ) ) );
}

/** معالجة رفع ملف فيرموير — يرجّع [ok, message] */
function ecm_ota_handle_upload(): array {
    if ( empty( $_FILES['ecm_ota_file']['name'] ) ) {
        return [ false, __( 'اختر ملف .bin الأول.', 'ecm-theme' ) ];
    }
    $file = $_FILES['ecm_ota_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

    if ( ! empty( $file['error'] ) ) {
        return [ false, __( 'فشل رفع الملف — جرّب تاني أو صغّر حجمه.', 'ecm-theme' ) ];
    }
    if ( ! is_uploaded_file( $file['tmp_name'] ) ) {
        return [ false, __( 'ملف غير صالح.', 'ecm-theme' ) ];
    }
    if ( (int) $file['size'] > ecm_ota_max_upload() ) {
        return [ false, sprintf( __( 'الملف أكبر من الحد المسموح (%s).', 'ecm-theme' ), ecm_ota_fmt_size( ecm_ota_max_upload() ) ) ];
    }
    $ext = strtolower( pathinfo( sanitize_file_name( $file['name'] ), PATHINFO_EXTENSION ) );
    if ( 'bin' !== $ext ) {
        return [ false, __( 'الملف لازم يكون امتداده .bin', 'ecm-theme' ) ];
    }

    $version = ecm_ota_clean_version( (string) wp_unslash( $_POST['ecm_ota_version'] ?? '' ) );
    $model   = ecm_ota_slug( (string) wp_unslash( $_POST['ecm_ota_model'] ?? '' ) );
    $channel = ecm_ota_slug( (string) wp_unslash( $_POST['ecm_ota_channel'] ?? '' ), 'stable' );

    if ( '' === $version ) {
        return [ false, __( 'اكتب رقم الإصدار (مثلاً 1.2.0).', 'ecm-theme' ) ];
    }
    if ( ! isset( ecm_ota_channels()[ $channel ] ) ) {
        $channel = 'stable';
    }

    global $wpdb;
    $table = ecm_ota_releases_table();
    $dup   = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$table} WHERE model = %s AND channel = %s AND version = %s",
        $model,
        $channel,
        $version
    ) );
    if ( $dup ) {
        return [ false, sprintf( __( 'الإصدار %s موجود بالفعل في نفس الموديل والقناة — امسحه الأول أو غيّر الرقم.', 'ecm-theme' ), $version ) ];
    }

    $dir       = ecm_ota_dir();
    $file_name = $model . '-' . $channel . '-' . $version . '-' . wp_generate_password( 6, false ) . '.bin';
    $dest      = trailingslashit( $dir['path'] ) . $file_name;

    if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) {
        return [ false, __( 'مقدرتش أحفظ الملف — اتأكد من صلاحيات مجلد uploads.', 'ecm-theme' ) ];
    }
    @chmod( $dest, 0644 );

    $inserted = $wpdb->insert( $table, [
        'version'     => $version,
        'model'       => $model,
        'channel'     => $channel,
        'file_name'   => $file_name,
        'file_size'   => (int) filesize( $dest ),
        'md5'         => (string) md5_file( $dest ),
        'sha256'      => (string) hash_file( 'sha256', $dest ),
        'notes'       => sanitize_textarea_field( (string) wp_unslash( $_POST['ecm_ota_notes'] ?? '' ) ),
        'min_version' => ecm_ota_clean_version( (string) wp_unslash( $_POST['ecm_ota_min'] ?? '' ) ),
        'rollout'     => max( 0, min( 100, (int) ( $_POST['ecm_ota_rollout'] ?? 100 ) ) ),
        'mandatory'   => empty( $_POST['ecm_ota_mandatory'] ) ? 0 : 1,
        'active'      => empty( $_POST['ecm_ota_active'] ) ? 0 : 1,
        'created_at'  => current_time( 'mysql' ),
    ] );

    if ( ! $inserted ) {
        @unlink( $dest );
        return [ false, __( 'فشل حفظ بيانات الإصدار في قاعدة البيانات.', 'ecm-theme' ) ];
    }

    return [ true, sprintf( __( 'تم رفع الإصدار %1$s (%2$s) بنجاح ✅', 'ecm-theme' ), $version, ecm_ota_fmt_size( filesize( $dest ) ) ) ];
}

/** الصفحة */
function ecm_ota_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    global $wpdb;
    $rel_table = ecm_ota_releases_table();
    $dev_table = ecm_ota_devices_table();
    $notices   = [];

    // ── رفع إصدار جديد ──
    if ( isset( $_POST['ecm_ota_upload'] ) && check_admin_referer( 'ecm_ota' ) ) {
        list( $ok, $msg ) = ecm_ota_handle_upload();
        $notices[] = [ $ok ? 'success' : 'error', $msg ];
    }

    // ── حفظ الإعدادات ──
    if ( isset( $_POST['ecm_ota_save_opts'] ) && check_admin_referer( 'ecm_ota' ) ) {
        update_option( 'ecm_ota_opts', [
            'enabled'       => empty( $_POST['ota_enabled'] ) ? 0 : 1,
            'require_auth'  => empty( $_POST['ota_require_auth'] ) ? 0 : 1,
            'require_known' => empty( $_POST['ota_require_known'] ) ? 0 : 1,
            'check_in'      => max( 60, (int) ( $_POST['ota_check_in'] ?? 21600 ) ),
            'link_ttl'      => max( 60, (int) ( $_POST['ota_link_ttl'] ?? 900 ) ),
            'app_link_ttl'  => max( 300, (int) ( $_POST['ota_app_link_ttl'] ?? 86400 ) ),
            'offline_after' => max( 300, (int) ( $_POST['ota_offline_after'] ?? 172800 ) ),
            'catalog_depth' => max( 1, min( 20, (int) ( $_POST['ota_catalog_depth'] ?? 5 ) ) ),
        ] );
        $notices[] = [ 'success', __( 'تم حفظ إعدادات التحديث ✅', 'ecm-theme' ) ];
    }

    // ── تعديل إصدار (تفعيل/إيقاف + نسبة الطرح + إجباري) ──
    if ( isset( $_POST['ecm_ota_update_rel'], $_POST['rel_id'] ) && check_admin_referer( 'ecm_ota' ) ) {
        $wpdb->update( $rel_table, [
            'active'    => empty( $_POST['rel_active'] ) ? 0 : 1,
            'rollout'   => max( 0, min( 100, (int) ( $_POST['rel_rollout'] ?? 100 ) ) ),
            'mandatory' => empty( $_POST['rel_mandatory'] ) ? 0 : 1,
        ], [ 'id' => (int) $_POST['rel_id'] ] );
        $notices[] = [ 'success', __( 'تم تحديث الإصدار.', 'ecm-theme' ) ];
    }

    // ── حذف إصدار (+ ملفه) ──
    if ( isset( $_GET['del_rel'] ) && check_admin_referer( 'ecm_ota_del_' . (int) $_GET['del_rel'] ) ) {
        $id  = (int) $_GET['del_rel'];
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$rel_table} WHERE id = %d", $id ) );
        if ( $row ) {
            $path = ecm_ota_file_path( (string) $row->file_name );
            if ( '' !== $path ) {
                @unlink( $path );
            }
            $wpdb->delete( $rel_table, [ 'id' => $id ] );
            $notices[] = [ 'success', __( 'تم حذف الإصدار وملفه.', 'ecm-theme' ) ];
        }
    }

    // ── تثبيت إصدار لجهاز / فكّ التثبيت / تغيير القناة ──
    if ( isset( $_POST['ecm_ota_pin'], $_POST['dev_id'] ) && check_admin_referer( 'ecm_ota' ) ) {
        $channel = ecm_ota_slug( (string) wp_unslash( $_POST['dev_channel'] ?? '' ), 'stable' );
        $wpdb->update( $dev_table, [
            'pin_version' => ecm_ota_clean_version( (string) wp_unslash( $_POST['dev_pin'] ?? '' ) ),
            'channel'     => isset( ecm_ota_channels()[ $channel ] ) ? $channel : 'stable',
        ], [ 'id' => (int) $_POST['dev_id'] ] );
        $notices[] = [ 'success', __( 'تم تحديث إعدادات الجهاز.', 'ecm-theme' ) ];
    }

    // ── حذف جهاز من الأسطول ──
    if ( isset( $_GET['del_dev'] ) && check_admin_referer( 'ecm_ota_dev_' . (int) $_GET['del_dev'] ) ) {
        $wpdb->delete( $dev_table, [ 'id' => (int) $_GET['del_dev'] ] );
        $notices[] = [ 'success', __( 'تم حذف الجهاز من قائمة الأسطول.', 'ecm-theme' ) ];
    }

    $opts     = ecm_ota_opts();
    $stats    = ecm_ota_stats();
    $channels = ecm_ota_channels();
    $releases = $wpdb->get_results( "SELECT * FROM {$rel_table} ORDER BY model ASC, channel ASC, id DESC LIMIT 200" );
    $devices  = $wpdb->get_results( "SELECT * FROM {$dev_table} ORDER BY last_seen DESC LIMIT 200" );
    $offline  = current_time( 'timestamp' ) - (int) $opts['offline_after'];
    $models   = $wpdb->get_col( "SELECT DISTINCT model FROM {$rel_table}" ) ?: [ 'default' ];
    ?>
    <style>
        .ecm-ota { max-width: 1180px; }
        .ecm-ota h1 { display:flex; align-items:center; gap:8px; }
        .ecm-ota-stats { display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin:18px 0 26px; }
        .ecm-ota-card { background:#fff; border:1px solid #e2e4e7; border-radius:12px; padding:18px 20px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .ecm-ota-card .n { font-size:30px; font-weight:800; line-height:1; }
        .ecm-ota-card .l { color:#646970; font-size:13px; margin-top:6px; }
        .ecm-ota-card.green .n{color:#1a7f37;} .ecm-ota-card.blue .n{color:#2271b1;}
        .ecm-ota-card.orange .n{color:#bd8600;} .ecm-ota-card.red .n{color:#b32d2e;} .ecm-ota-card.grey .n{color:#3c434a;}
        .ecm-ota-box { background:#fff; border:1px solid #e2e4e7; border-radius:12px; padding:22px 24px; margin-bottom:22px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .ecm-ota-box h2 { margin-top:0; }
        .ecm-ota-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; }
        .ecm-ota-grid label { display:block; font-weight:600; margin-bottom:4px; font-size:13px; }
        .ecm-ota-grid input[type=text], .ecm-ota-grid input[type=number], .ecm-ota-grid select, .ecm-ota-box textarea { width:100%; }
        .ecm-ota-table { width:100%; border-collapse:collapse; }
        .ecm-ota-table th { text-align:start; background:#f6f7f7; padding:11px 12px; font-size:12px; color:#646970; border-bottom:1px solid #e2e4e7; }
        .ecm-ota-table td { padding:10px 12px; border-bottom:1px solid #f0f0f1; font-size:13px; vertical-align:middle; }
        .ecm-ota-table tr:hover td { background:#fafafa; }
        .ecm-pill { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .ecm-pill.on { background:#e6f4ea; color:#1a7f37; } .ecm-pill.off { background:#eef0f2; color:#646970; }
        .ecm-pill.warn { background:#fcf3d7; color:#8a6100; } .ecm-pill.bad { background:#fce8e9; color:#b32d2e; }
        .ecm-pill.beta { background:#e7f0fb; color:#2271b1; }
        .ecm-ota-ep code { display:block; background:#f6f7f7; border:1px solid #e2e4e7; border-radius:6px; padding:8px 12px; margin:6px 0; font-size:12.5px; color:#1d2327; word-break:break-all; }
        .ecm-ota-mini { width:74px; }
        @media (max-width:900px){ .ecm-ota-stats{grid-template-columns:repeat(2,1fr);} }
    </style>

    <div class="wrap ecm-ota">
        <h1>📡 <?php esc_html_e( 'التحديث عن بُعد للأجهزة — OTA', 'ecm-theme' ); ?></h1>
        <p class="description"><?php esc_html_e( 'ارفع فيرموير ESP32/ESP8266، حدّد القناة ونسبة الطرح، وتابع البوردات وهي بتتحدّث.', 'ecm-theme' ); ?></p>

        <div class="notice notice-info inline" style="margin:14px 0;">
            <p style="margin:8px 0;">
                <strong><?php esc_html_e( 'إزاي التحديث بيوصل للبورده؟', 'ecm-theme' ); ?></strong><br>
                <?php esc_html_e( 'البوردات شغّالة على شبكة داخلية من غير إنترنت — التطبيق هو الوسيط:', 'ecm-theme' ); ?>
            </p>
            <p style="margin:8px 0;font-family:monospace;direction:ltr;text-align:left;">
                [<?php esc_html_e( 'الموقع', 'ecm-theme' ); ?>] ──<?php esc_html_e( 'إنترنت', 'ecm-theme' ); ?>──&gt; [<?php esc_html_e( 'التطبيق', 'ecm-theme' ); ?>] ──<?php esc_html_e( 'شبكة داخلية', 'ecm-theme' ); ?>──&gt; [<?php esc_html_e( 'البورده', 'ecm-theme' ); ?>]
            </p>
            <p style="margin:8px 0;">
                <?php esc_html_e( 'التطبيق بينزّل الـ .bin ويخزّنه عنده، يدخل على شبكة البورده ويرفعه عليها محليًا، وبعدين يرجع يبلّغنا بالنتيجة. يعني البيانات اللي تحت بتتحدّث لما التطبيق يبلّغ — مش لحظيًا من البورده.', 'ecm-theme' ); ?>
            </p>
        </div>

        <?php foreach ( $notices as $n ) : ?>
            <div class="notice notice-<?php echo esc_attr( $n[0] ); ?>"><p><?php echo esc_html( $n[1] ); ?></p></div>
        <?php endforeach; ?>

        <?php if ( empty( $opts['enabled'] ) ) : ?>
            <div class="notice notice-warning"><p><?php esc_html_e( 'نظام التحديث متوقف حاليًا — الأجهزة مش هتستقبل تحديثات.', 'ecm-theme' ); ?></p></div>
        <?php endif; ?>

        <div class="ecm-ota-stats">
            <div class="ecm-ota-card grey"><div class="n"><?php echo (int) $stats['devices']; ?></div><div class="l"><?php esc_html_e( 'إجمالي الأجهزة', 'ecm-theme' ); ?></div></div>
            <div class="ecm-ota-card green"><div class="n"><?php echo (int) $stats['online']; ?></div><div class="l"><?php esc_html_e( 'جالها بلاغ مؤخرًا', 'ecm-theme' ); ?></div></div>
            <div class="ecm-ota-card blue"><div class="n"><?php echo (int) $stats['updating']; ?></div><div class="l"><?php esc_html_e( 'بتتحدّث دلوقتي', 'ecm-theme' ); ?></div></div>
            <div class="ecm-ota-card red"><div class="n"><?php echo (int) $stats['failed']; ?></div><div class="l"><?php esc_html_e( 'تحديث فشل', 'ecm-theme' ); ?></div></div>
            <div class="ecm-ota-card orange"><div class="n"><?php echo (int) $stats['releases']; ?></div><div class="l"><?php esc_html_e( 'إصدارات فعّالة', 'ecm-theme' ); ?></div></div>
        </div>

        <!-- ══ رفع إصدار جديد ══ -->
        <div class="ecm-ota-box">
            <h2>⬆️ <?php esc_html_e( 'رفع إصدار فيرموير جديد', 'ecm-theme' ); ?></h2>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'ecm_ota' ); ?>
                <div class="ecm-ota-grid">
                    <div>
                        <label><?php esc_html_e( 'ملف الفيرموير (.bin)', 'ecm-theme' ); ?></label>
                        <input type="file" name="ecm_ota_file" accept=".bin" required>
                        <p class="description"><?php echo esc_html( sprintf( __( 'أقصى حجم: %s', 'ecm-theme' ), ecm_ota_fmt_size( ecm_ota_max_upload() ) ) ); ?></p>
                    </div>
                    <div>
                        <label><?php esc_html_e( 'رقم الإصدار', 'ecm-theme' ); ?></label>
                        <input type="text" name="ecm_ota_version" placeholder="1.2.0" required>
                    </div>
                    <div>
                        <label><?php esc_html_e( 'الموديل', 'ecm-theme' ); ?></label>
                        <input type="text" name="ecm_ota_model" value="default" list="ecm-ota-models">
                        <datalist id="ecm-ota-models">
                            <?php foreach ( $models as $m ) : ?>
                                <option value="<?php echo esc_attr( $m ); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div>
                        <label><?php esc_html_e( 'القناة', 'ecm-theme' ); ?></label>
                        <select name="ecm_ota_channel">
                            <?php foreach ( $channels as $key => $label ) : ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label><?php esc_html_e( 'نسبة الطرح %', 'ecm-theme' ); ?></label>
                        <input type="number" name="ecm_ota_rollout" value="100" min="0" max="100">
                        <p class="description"><?php esc_html_e( 'ابدأ بـ 10% وراقب قبل ما توصل 100%.', 'ecm-theme' ); ?></p>
                    </div>
                    <div>
                        <label><?php esc_html_e( 'أقل إصدار مسموح (اختياري)', 'ecm-theme' ); ?></label>
                        <input type="text" name="ecm_ota_min" placeholder="1.0.0">
                        <p class="description"><?php esc_html_e( 'الأجهزة الأقدم من كده لازم تعدّي على إصدار وسيط الأول.', 'ecm-theme' ); ?></p>
                    </div>
                </div>
                <p style="margin-top:14px;">
                    <label><?php esc_html_e( 'ملاحظات الإصدار', 'ecm-theme' ); ?></label>
                    <textarea name="ecm_ota_notes" rows="2" style="width:100%;max-width:760px;" placeholder="<?php esc_attr_e( 'إيه اللي اتغيّر في الإصدار ده؟', 'ecm-theme' ); ?>"></textarea>
                </p>
                <p>
                    <label><input type="checkbox" name="ecm_ota_active" value="1" checked> <?php esc_html_e( 'فعّال (الأجهزة تشوفه)', 'ecm-theme' ); ?></label>
                    &nbsp;&nbsp;
                    <label><input type="checkbox" name="ecm_ota_mandatory" value="1"> <?php esc_html_e( 'تحديث إجباري', 'ecm-theme' ); ?></label>
                </p>
                <p><button class="button button-primary" name="ecm_ota_upload" value="1">⬆️ <?php esc_html_e( 'رفع الإصدار', 'ecm-theme' ); ?></button></p>
            </form>
        </div>

        <!-- ══ الإصدارات ══ -->
        <div class="ecm-ota-box">
            <h2>📦 <?php esc_html_e( 'الإصدارات', 'ecm-theme' ); ?></h2>
            <?php if ( ! $releases ) : ?>
                <p><?php esc_html_e( 'لسه مفيش إصدارات مرفوعة.', 'ecm-theme' ); ?></p>
            <?php else : ?>
            <table class="ecm-ota-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'الإصدار', 'ecm-theme' ); ?></th>
                        <th><?php esc_html_e( 'الموديل / القناة', 'ecm-theme' ); ?></th>
                        <th><?php esc_html_e( 'الحجم / MD5', 'ecm-theme' ); ?></th>
                        <th><?php esc_html_e( 'التحكم', 'ecm-theme' ); ?></th>
                        <th><?php esc_html_e( 'النتائج', 'ecm-theme' ); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $releases as $r ) :
                    $missing = ( '' === ecm_ota_file_path( (string) $r->file_name ) );
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( $r->version ); ?></strong>
                            <?php if ( $r->mandatory ) : ?><span class="ecm-pill warn"><?php esc_html_e( 'إجباري', 'ecm-theme' ); ?></span><?php endif; ?>
                            <?php if ( $missing ) : ?><br><span class="ecm-pill bad"><?php esc_html_e( 'الملف مفقود!', 'ecm-theme' ); ?></span><?php endif; ?>
                            <div style="color:#646970;font-size:12px;"><?php echo esc_html( ecm_ota_ago( $r->created_at ) ); ?></div>
                            <?php if ( $r->notes ) : ?>
                                <div style="color:#646970;font-size:12px;max-width:260px;"><?php echo esc_html( wp_trim_words( $r->notes, 14 ) ); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo esc_html( $r->model ); ?></code><br>
                            <span class="ecm-pill <?php echo 'stable' === $r->channel ? 'on' : 'beta'; ?>"><?php echo esc_html( $channels[ $r->channel ] ?? $r->channel ); ?></span>
                            <?php if ( $r->min_version ) : ?>
                                <div style="color:#646970;font-size:12px;"><?php echo esc_html( sprintf( __( 'من %s فأحدث', 'ecm-theme' ), $r->min_version ) ); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html( ecm_ota_fmt_size( $r->file_size ) ); ?>
                            <div style="color:#646970;font-size:11px;word-break:break-all;max-width:180px;"><?php echo esc_html( $r->md5 ); ?></div>
                        </td>
                        <td>
                            <form method="post" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                <?php wp_nonce_field( 'ecm_ota' ); ?>
                                <input type="hidden" name="rel_id" value="<?php echo (int) $r->id; ?>">
                                <input class="ecm-ota-mini" type="number" name="rel_rollout" value="<?php echo (int) $r->rollout; ?>" min="0" max="100" title="<?php esc_attr_e( 'نسبة الطرح %', 'ecm-theme' ); ?>">%
                                <label title="<?php esc_attr_e( 'فعّال', 'ecm-theme' ); ?>"><input type="checkbox" name="rel_active" value="1" <?php checked( (int) $r->active, 1 ); ?>> <?php esc_html_e( 'فعّال', 'ecm-theme' ); ?></label>
                                <label title="<?php esc_attr_e( 'إجباري', 'ecm-theme' ); ?>"><input type="checkbox" name="rel_mandatory" value="1" <?php checked( (int) $r->mandatory, 1 ); ?>> <?php esc_html_e( 'إجباري', 'ecm-theme' ); ?></label>
                                <button class="button button-small" name="ecm_ota_update_rel" value="1"><?php esc_html_e( 'حفظ', 'ecm-theme' ); ?></button>
                            </form>
                        </td>
                        <td>
                            ⬇️ <?php echo (int) $r->downloads; ?> ·
                            <span style="color:#1a7f37;">✔ <?php echo (int) $r->success_count; ?></span> ·
                            <span style="color:#b32d2e;">✖ <?php echo (int) $r->fail_count; ?></span>
                        </td>
                        <td>
                            <a class="button button-small" style="color:#b32d2e;"
                               href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ecm-ota&del_rel=' . (int) $r->id ), 'ecm_ota_del_' . (int) $r->id ) ); ?>"
                               onclick="return confirm('<?php echo esc_js( __( 'حذف الإصدار وملفه نهائيًا؟', 'ecm-theme' ) ); ?>');">🗑</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- ══ الأسطول ══ -->
        <div class="ecm-ota-box">
            <h2>🛰️ <?php esc_html_e( 'أسطول الأجهزة', 'ecm-theme' ); ?></h2>
            <?php if ( ! $devices ) : ?>
                <p><?php esc_html_e( 'لسه مفيش أجهزة اتصلت بالسيرفر. أول ما جهاز يسأل عن تحديث هيظهر هنا.', 'ecm-theme' ); ?></p>
            <?php else : ?>
            <table class="ecm-ota-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'الجهاز', 'ecm-theme' ); ?></th>
                        <th><?php esc_html_e( 'الإصدار الحالي', 'ecm-theme' ); ?></th>
                        <th><?php esc_html_e( 'الحالة', 'ecm-theme' ); ?></th>
                        <th><?php esc_html_e( 'آخر بلاغ', 'ecm-theme' ); ?></th>
                        <th><?php esc_html_e( 'القناة / تثبيت إصدار', 'ecm-theme' ); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $devices as $d ) :
                    $seen_ts = $d->last_seen ? strtotime( $d->last_seen ) : 0;
                    $is_on   = $seen_ts > $offline;
                    $st_map  = [
                        'success'  => [ 'on',   __( 'تم التحديث', 'ecm-theme' ) ],
                        'updating' => [ 'beta', __( 'بيتحدّث', 'ecm-theme' ) ],
                        'failed'   => [ 'bad',  __( 'فشل', 'ecm-theme' ) ],
                        'idle'     => [ 'off',  __( 'عادي', 'ecm-theme' ) ],
                    ];
                    $st = $st_map[ $d->status ] ?? [ 'off', $d->status ];
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( $d->serial ); ?></strong>
                            <div style="color:#646970;font-size:12px;">
                                <code><?php echo esc_html( $d->model ); ?></code>
                                <?php if ( $d->mac ) : ?> · <?php echo esc_html( $d->mac ); ?><?php endif; ?>
                                <?php if ( $d->local_ip ) : ?> · <?php echo esc_html( $d->local_ip ); ?><?php endif; ?>
                                <?php if ( $d->rssi ) : ?> · <?php echo (int) $d->rssi; ?>dBm<?php endif; ?>
                            </div>
                            <?php
                            if ( 'app' === $d->via ) {
                                $who = $d->app_user_id ? get_userdata( (int) $d->app_user_id ) : null;
                                printf(
                                    '<span class="ecm-pill beta">📱 %s</span>',
                                    esc_html( $who ? sprintf( __( 'عن طريق %s', 'ecm-theme' ), $who->display_name ) : __( 'عن طريق التطبيق', 'ecm-theme' ) )
                                );
                            } else {
                                echo '<span class="ecm-pill off">🌐 ' . esc_html__( 'اتصال مباشر', 'ecm-theme' ) . '</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo $d->fw_version ? esc_html( $d->fw_version ) : '—'; ?>
                            <?php if ( $d->pin_version ) : ?>
                                <br><span class="ecm-pill warn">📌 <?php echo esc_html( $d->pin_version ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="ecm-pill <?php echo esc_attr( $st[0] ); ?>"><?php echo esc_html( $st[1] ); ?></span>
                            <?php if ( 'failed' === $d->status && $d->last_error ) : ?>
                                <div style="color:#b32d2e;font-size:11px;max-width:200px;"><?php echo esc_html( $d->last_error ); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="ecm-pill <?php echo $is_on ? 'on' : 'off'; ?>"><?php echo $is_on ? esc_html__( 'متابَعة', 'ecm-theme' ) : esc_html__( 'مالهاش بلاغ من زمان', 'ecm-theme' ); ?></span>
                            <div style="color:#646970;font-size:12px;"><?php echo esc_html( ecm_ota_ago( $d->last_seen ) ); ?></div>
                        </td>
                        <td>
                            <form method="post" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                <?php wp_nonce_field( 'ecm_ota' ); ?>
                                <input type="hidden" name="dev_id" value="<?php echo (int) $d->id; ?>">
                                <select name="dev_channel">
                                    <?php foreach ( $channels as $key => $label ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $d->channel, $key ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="dev_pin" value="<?php echo esc_attr( $d->pin_version ); ?>" placeholder="<?php esc_attr_e( 'إصدار مثبّت', 'ecm-theme' ); ?>" style="width:110px;">
                                <button class="button button-small" name="ecm_ota_pin" value="1"><?php esc_html_e( 'حفظ', 'ecm-theme' ); ?></button>
                            </form>
                        </td>
                        <td>
                            <a class="button button-small" style="color:#b32d2e;"
                               href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ecm-ota&del_dev=' . (int) $d->id ), 'ecm_ota_dev_' . (int) $d->id ) ); ?>"
                               onclick="return confirm('<?php echo esc_js( __( 'حذف الجهاز من القائمة؟', 'ecm-theme' ) ); ?>');">🗑</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="description" style="margin-top:12px;">
                📌 <?php esc_html_e( 'تثبيت إصدار = الجهاز ده هياخد الإصدار ده بالظبط (حتى لو أقدم) ويتجاهل القناة ونسبة الطرح. بيتفكّ تلقائيًا أول ما الجهاز يبلّغ بنجاح التحديث.', 'ecm-theme' ); ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- ══ الإعدادات ══ -->
        <div class="ecm-ota-box">
            <h2>⚙️ <?php esc_html_e( 'إعدادات التحديث', 'ecm-theme' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field( 'ecm_ota' ); ?>
                <p>
                    <label><input type="checkbox" name="ota_enabled" value="1" <?php checked( (int) $opts['enabled'], 1 ); ?>> <strong><?php esc_html_e( 'تشغيل نظام التحديث عن بُعد', 'ecm-theme' ); ?></strong></label>
                </p>
                <p>
                    <label><input type="checkbox" name="ota_require_auth" value="1" <?php checked( (int) $opts['require_auth'], 1 ); ?>> <?php esc_html_e( 'لازم توكن الجهاز (من صفحة السيريالات) — مستحسن جدًا', 'ecm-theme' ); ?></label>
                </p>
                <p>
                    <label><input type="checkbox" name="ota_require_known" value="1" <?php checked( (int) $opts['require_known'], 1 ); ?>> <?php esc_html_e( 'لو التوكن مقفول: اقبل السيريالات المسجّلة كأصلية بس', 'ecm-theme' ); ?></label>
                </p>
                <div class="ecm-ota-grid" style="max-width:900px;">
                    <div>
                        <label><?php esc_html_e( 'كل قد إيه التطبيق يسأل (ثانية)', 'ecm-theme' ); ?></label>
                        <input type="number" name="ota_check_in" value="<?php echo (int) $opts['check_in']; ?>" min="60">
                    </div>
                    <div>
                        <label><?php esc_html_e( 'صلاحية رابط التنزيل للتطبيق (ثانية)', 'ecm-theme' ); ?></label>
                        <input type="number" name="ota_app_link_ttl" value="<?php echo (int) $opts['app_link_ttl']; ?>" min="300">
                        <p class="description"><?php esc_html_e( 'خليها طويلة — التطبيق ممكن ينزّل النهاردة ويرفع على البورده بكرة.', 'ecm-theme' ); ?></p>
                    </div>
                    <div>
                        <label><?php esc_html_e( 'عدد الإصدارات في الكتالوج (لكل قناة)', 'ecm-theme' ); ?></label>
                        <input type="number" name="ota_catalog_depth" value="<?php echo (int) $opts['catalog_depth']; ?>" min="1" max="20">
                        <p class="description"><?php esc_html_e( 'التطبيق بينزّلهم عنده عشان يشتغل أوفلاين ويقدر يرجّع نسخة أقدم.', 'ecm-theme' ); ?></p>
                    </div>
                    <div>
                        <label><?php esc_html_e( 'صلاحية الرابط للبورده المباشرة (ثانية)', 'ecm-theme' ); ?></label>
                        <input type="number" name="ota_link_ttl" value="<?php echo (int) $opts['link_ttl']; ?>" min="60">
                    </div>
                    <div>
                        <label><?php esc_html_e( 'تُعتبر أوفلاين بعد (ثانية)', 'ecm-theme' ); ?></label>
                        <input type="number" name="ota_offline_after" value="<?php echo (int) $opts['offline_after']; ?>" min="300">
                    </div>
                </div>
                <p><button class="button button-primary" name="ecm_ota_save_opts" value="1">💾 <?php esc_html_e( 'حفظ الإعدادات', 'ecm-theme' ); ?></button></p>
            </form>
        </div>

        <!-- ══ نقاط الاتصال ══ -->
        <div class="ecm-ota-box ecm-ota-ep">
            <h2>🔌 <?php esc_html_e( 'نقاط الاتصال — للمبرمج', 'ecm-theme' ); ?></h2>
            <p><strong><?php esc_html_e( 'التطبيق (المسار الأساسي)', 'ecm-theme' ); ?></strong> — <?php esc_html_e( 'التوثيق بتوكن المستخدم اللي بيرجع من /app/login، أو هيدر X-ECM-App-Token.', 'ecm-theme' ); ?></p>
            <code>GET <?php echo esc_html( rest_url( 'ecm/v1/ota/app/catalog' ) ); ?>?token=APP_TOKEN&amp;model=default</code>
            <code>GET <?php echo esc_html( rest_url( 'ecm/v1/ota/app/check' ) ); ?>?token=APP_TOKEN&amp;serial=SERIAL&amp;version=1.0.0&amp;model=default</code>
            <code>POST <?php echo esc_html( rest_url( 'ecm/v1/ota/app/report' ) ); ?> — token, serial, version, status=success|failed, error, local_ip</code>

            <p style="margin-top:16px;"><strong><?php esc_html_e( 'بورده ليها إنترنت (احتياطي)', 'ecm-theme' ); ?></strong> — <?php esc_html_e( 'بتوكن الجهاز من صفحة السيريالات.', 'ecm-theme' ); ?></p>
            <code>GET <?php echo esc_html( rest_url( 'ecm/v1/ota/check' ) ); ?>?serial=SERIAL&amp;token=DEVICE_TOKEN&amp;version=1.0.0</code>
            <code>POST <?php echo esc_html( rest_url( 'ecm/v1/ota/report' ) ); ?> — serial, token, version, status, error</code>

            <p style="margin-top:16px;"><strong><?php esc_html_e( 'للوحة', 'ecm-theme' ); ?></strong></p>
            <code>GET <?php echo esc_html( rest_url( 'ecm/v1/ota/fleet' ) ); ?> — <?php esc_html_e( 'يحتاج هيدر X-ECM-Token (توكن الـ API)', 'ecm-theme' ); ?></code>

            <p class="description" style="margin-top:14px;">
                <?php esc_html_e( 'روابط التنزيل بترجع موقّعة ومربوطة بالحساب وبتنتهي بعد المدة المضبوطة، وبتدعم الاستكمال (Range) عشان شبكة الموبايل — مفيش رابط ثابت للفيرموير.', 'ecm-theme' ); ?><br>
                <?php esc_html_e( 'كود التطبيق وكود البورده الكامل في:', 'ecm-theme' ); ?>
                <code style="display:inline;">docs/ota-app-flow.md</code> ·
                <code style="display:inline;">docs/esp32-ota-client.md</code>
            </p>
        </div>

        <?php if ( function_exists( 'ecm_admin_footer' ) ) { ecm_admin_footer(); } ?>
    </div>
    <?php
}
