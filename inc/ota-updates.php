<?php
/**
 * ECM — نظام التحديث عن بُعد للأجهزة (ESP32 / ESP8266 OTA)
 *
 * - رفع ملفات الفيرموير (.bin) في مجلد محمي — مش قابل للتنزيل المباشر.
 * - قنوات إصدار (stable / beta / dev) + طرح تدريجي (rollout %) + تحديث إجباري.
 * - كل جهاز بيسجّل نفسه (serial + chip id) وبيقول إصداره الحالي وحالته.
 * - تثبيت إصدار معيّن لجهاز بعينه (pin) للتجارب أو الرجوع لنسخة أقدم.
 * - روابط تنزيل موقّعة بـ HMAC وبتنتهي بعد مدة — مربوطة بالجهاز نفسه.
 *
 * Endpoints (ecm/v1):
 *   GET  /ota/check     — الجهاز يسأل: فيه تحديث؟
 *   GET  /ota/download  — تنزيل الـ .bin برابط موقّع
 *   POST /ota/report    — الجهاز يبلّغ بنتيجة التحديث
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

// ════════════════════════════════════════════════════════════
// §1  الجداول والتخزين
// ════════════════════════════════════════════════════════════

/** جدول إصدارات الفيرموير */
function ecm_ota_releases_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'ecm_ota_releases';
}

/** جدول الأجهزة (تليمتري الأسطول) */
function ecm_ota_devices_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'ecm_ota_devices';
}

/** إنشاء/تحديث الجداول */
function ecm_ota_install() {
    if ( get_option( 'ecm_ota_db_v1' ) ) {
        return;
    }
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $rel = ecm_ota_releases_table();
    dbDelta( "CREATE TABLE {$rel} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        version VARCHAR(32) NOT NULL,
        model VARCHAR(64) NOT NULL DEFAULT 'default',
        channel VARCHAR(16) NOT NULL DEFAULT 'stable',
        file_name VARCHAR(191) NOT NULL DEFAULT '',
        file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
        md5 CHAR(32) NOT NULL DEFAULT '',
        sha256 CHAR(64) NOT NULL DEFAULT '',
        notes TEXT NULL,
        min_version VARCHAR(32) NOT NULL DEFAULT '',
        rollout TINYINT UNSIGNED NOT NULL DEFAULT 100,
        mandatory TINYINT(1) NOT NULL DEFAULT 0,
        active TINYINT(1) NOT NULL DEFAULT 1,
        downloads BIGINT UNSIGNED NOT NULL DEFAULT 0,
        success_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
        fail_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY rel (model,channel,version),
        KEY lookup (model,channel,active)
    ) {$charset};" );

    $dev = ecm_ota_devices_table();
    dbDelta( "CREATE TABLE {$dev} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        serial VARCHAR(100) NOT NULL,
        chip_id VARCHAR(64) NOT NULL DEFAULT '',
        mac VARCHAR(32) NOT NULL DEFAULT '',
        model VARCHAR(64) NOT NULL DEFAULT 'default',
        channel VARCHAR(16) NOT NULL DEFAULT 'stable',
        fw_version VARCHAR(32) NOT NULL DEFAULT '',
        pin_version VARCHAR(32) NOT NULL DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'idle',
        last_error VARCHAR(191) NOT NULL DEFAULT '',
        ip VARCHAR(45) NOT NULL DEFAULT '',
        rssi INT NOT NULL DEFAULT 0,
        uptime BIGINT UNSIGNED NOT NULL DEFAULT 0,
        checks BIGINT UNSIGNED NOT NULL DEFAULT 0,
        first_seen DATETIME NOT NULL,
        last_seen DATETIME NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY serial (serial),
        KEY last_seen (last_seen),
        KEY model (model,channel)
    ) {$charset};" );

    update_option( 'ecm_ota_db_v1', 1 );
}
add_action( 'admin_init', 'ecm_ota_install' );
add_action( 'init', 'ecm_ota_install' );

/** مجلد تخزين الفيرموير — محمي من التنزيل المباشر */
function ecm_ota_dir(): array {
    $slug = (string) get_option( 'ecm_ota_dir_slug', '' );
    if ( '' === $slug ) {
        $slug = 'ecm-firmware-' . wp_generate_password( 12, false );
        update_option( 'ecm_ota_dir_slug', $slug );
    }
    $up   = wp_upload_dir();
    $path = trailingslashit( $up['basedir'] ) . $slug;

    if ( ! file_exists( $path ) ) {
        wp_mkdir_p( $path );
    }
    // منع التصفّح/التنزيل المباشر (Apache) — وعلى nginx الاسم عشوائي والتنزيل بيعدّي على REST
    if ( ! file_exists( $path . '/.htaccess' ) ) {
        file_put_contents( $path . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
    }
    if ( ! file_exists( $path . '/index.php' ) ) {
        file_put_contents( $path . '/index.php', "<?php // Silence is golden." );
    }
    return [ 'path' => $path, 'slug' => $slug ];
}

/** المسار الكامل لملف إصدار — أو '' لو الملف مش موجود/بره المجلد */
function ecm_ota_file_path( string $file_name ): string {
    $file_name = basename( $file_name );
    if ( '' === $file_name ) {
        return '';
    }
    $dir  = ecm_ota_dir();
    $real = realpath( trailingslashit( $dir['path'] ) . $file_name );
    $base = realpath( $dir['path'] );
    if ( ! $real || ! $base || strpos( $real, $base ) !== 0 ) {
        return '';
    }
    return $real;
}


// ════════════════════════════════════════════════════════════
// §2  إعدادات + دوال مساعدة
// ════════════════════════════════════════════════════════════

/** إعدادات الـ OTA */
function ecm_ota_opts(): array {
    return wp_parse_args( (array) get_option( 'ecm_ota_opts', [] ), [
        'enabled'       => 1,
        'require_auth'  => 1,      // لازم توكن الجهاز من جدول السيريالات
        'require_known' => 0,      // لازم السيريال يكون مسجّل كأصلي
        'check_in'      => 21600,  // الجهاز يسأل كل قد إيه (ثواني) — 6 ساعات
        'link_ttl'      => 900,    // صلاحية رابط التنزيل (ثواني)
        'offline_after' => 172800, // يُعتبر أوفلاين بعد (ثواني) — يومين
    ] );
}

/** القنوات المتاحة */
function ecm_ota_channels(): array {
    return [
        'stable' => __( 'مستقر — Stable', 'ecm-theme' ),
        'beta'   => __( 'تجريبي — Beta', 'ecm-theme' ),
        'dev'    => __( 'تطوير — Dev', 'ecm-theme' ),
    ];
}

/** سر توقيع روابط التنزيل */
function ecm_ota_secret(): string {
    $s = (string) get_option( 'ecm_ota_secret', '' );
    if ( '' === $s ) {
        $s = wp_generate_password( 64, true, true );
        update_option( 'ecm_ota_secret', $s );
    }
    return $s;
}

/** تنضيف رقم إصدار (1.2.3 / v1.2.3) */
function ecm_ota_clean_version( string $v ): string {
    $v = ltrim( trim( $v ), 'vV' );
    return (string) preg_replace( '/[^0-9A-Za-z.+-]/', '', $v );
}

/** تنضيف اسم موديل/قناة */
function ecm_ota_slug( string $s, string $fallback = 'default' ): string {
    $s = (string) preg_replace( '/[^a-z0-9_-]/', '', strtolower( trim( $s ) ) );
    return '' !== $s ? substr( $s, 0, 64 ) : $fallback;
}

/** IP الطالب */
function ecm_ota_ip(): string {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}

/**
 * خنق بسيط لمنع إغراق الـ endpoints (جهاز عيّان بيسأل في لوب، أو حد بيلعب).
 * يرجّع true لو الطلب مسموح.
 */
function ecm_ota_throttle( string $bucket, int $max = 30, int $window = 600 ): bool {
    $ip = ecm_ota_ip();
    if ( '' === $ip ) {
        return true;
    }
    $key   = 'ecm_ota_rl_' . md5( $bucket . '|' . $ip );
    $count = (int) get_transient( $key );
    if ( $count >= $max ) {
        return false;
    }
    set_transient( $key, $count + 1, $window );
    return true;
}

/**
 * هل الجهاز داخل نسبة الطرح التدريجي؟
 * توزيع ثابت لكل (سيريال + إصدار) — الجهاز مايتنطّطش بين مسموح وممنوع كل مرة.
 */
function ecm_ota_in_rollout( string $serial, int $release_id, int $rollout ): bool {
    $rollout = max( 0, min( 100, $rollout ) );
    if ( $rollout >= 100 ) {
        return true;
    }
    if ( $rollout <= 0 ) {
        return false;
    }
    $bucket = hexdec( substr( md5( $serial . '|' . $release_id ), 0, 8 ) ) % 100;
    return $bucket < $rollout;
}

/** أحدث إصدار فعّال لموديل/قناة (أو null) */
function ecm_ota_latest_release( string $model, string $channel ) {
    global $wpdb;
    $rows = $wpdb->get_results( $wpdb->prepare(
        'SELECT * FROM ' . ecm_ota_releases_table() . ' WHERE model = %s AND channel = %s AND active = 1',
        $model,
        $channel
    ) );
    if ( ! $rows ) {
        return null;
    }
    usort( $rows, function ( $a, $b ) {
        return version_compare( $a->version, $b->version );
    } );
    return end( $rows );
}

/** إصدار محدّد لموديل (أيًا كانت القناة) */
function ecm_ota_find_release( string $model, string $version ) {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare(
        'SELECT * FROM ' . ecm_ota_releases_table() . ' WHERE model = %s AND version = %s ORDER BY id DESC LIMIT 1',
        $model,
        $version
    ) );
}

/** إحصائيات سريعة للأسطول */
function ecm_ota_stats(): array {
    global $wpdb;
    $dev  = ecm_ota_devices_table();
    $rel  = ecm_ota_releases_table();
    $opts = ecm_ota_opts();
    // last_seen متخزّن بتوقيت الموقع (current_time) — نقارن بنفس التوقيت
    $off  = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - (int) $opts['offline_after'] ); // phpcs:ignore

    return [
        'devices'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$dev}" ),
        'online'   => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$dev} WHERE last_seen > %s", $off ) ),
        'updating' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$dev} WHERE status = 'updating'" ),
        'failed'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$dev} WHERE status = 'failed'" ),
        'releases' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$rel} WHERE active = 1" ),
    ];
}


// ════════════════════════════════════════════════════════════
// §3  هوية الجهاز + الروابط الموقّعة
// ════════════════════════════════════════════════════════════

/**
 * التحقق من هوية الجهاز الطالب.
 * يرجّع [ 'ok' => bool, 'serial' => string, 'error' => string ]
 *
 * الترتيب: توكن الجهاز من جدول السيريالات (الأقوى) ← السيريال لوحده (لو الأمان مخفّف).
 */
function ecm_ota_authenticate( $request ): array {
    $opts   = ecm_ota_opts();
    $serial = function_exists( 'ecm_serial_normalize' )
        ? ecm_serial_normalize( (string) $request->get_param( 'serial' ) )
        : strtoupper( trim( (string) $request->get_param( 'serial' ) ) );

    $token = sanitize_text_field( (string) $request->get_param( 'token' ) );
    if ( '' === $token && ! empty( $_SERVER['HTTP_X_ECM_DEVICE_TOKEN'] ) ) {
        $token = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_ECM_DEVICE_TOKEN'] ) );
    }

    // (1) توكن الجهاز — بيحدّد السيريال لوحده، فمش محتاجين نثق في اللي الجهاز بعته
    if ( '' !== $token && function_exists( 'ecm_serial_find_by_token' ) ) {
        $row = ecm_serial_find_by_token( $token );
        if ( $row ) {
            return [ 'ok' => true, 'serial' => (string) $row->serial, 'error' => '' ];
        }
    }

    if ( ! empty( $opts['require_auth'] ) ) {
        return [ 'ok' => false, 'serial' => $serial, 'error' => 'unauthorized' ];
    }

    // (2) سيريال بس — الوضع المخفّف
    if ( '' === $serial ) {
        return [ 'ok' => false, 'serial' => '', 'error' => 'missing_serial' ];
    }
    if ( ! empty( $opts['require_known'] ) && function_exists( 'ecm_serial_find' ) && ! ecm_serial_find( $serial ) ) {
        return [ 'ok' => false, 'serial' => $serial, 'error' => 'unknown_serial' ];
    }

    return [ 'ok' => true, 'serial' => $serial, 'error' => '' ];
}

/** توقيع رابط تنزيل مربوط بجهاز وإصدار وبينتهي بعد TTL */
function ecm_ota_sign_download( int $release_id, string $serial, int $ttl = 0 ): string {
    $opts    = ecm_ota_opts();
    $ttl     = $ttl > 0 ? $ttl : (int) $opts['link_ttl'];
    $expires = time() + max( 60, $ttl );
    $payload = $release_id . '|' . $serial . '|' . $expires;
    $sig     = hash_hmac( 'sha256', $payload, ecm_ota_secret() );

    return add_query_arg( [
        'r' => $release_id,
        's' => rawurlencode( $serial ),
        'e' => $expires,
        'k' => $sig,
    ], rest_url( 'ecm/v1/ota/download' ) );
}

/** التحقق من رابط التنزيل — يرجّع السيريال أو null */
function ecm_ota_verify_download( $request ): ?array {
    $release_id = (int) $request->get_param( 'r' );
    $serial     = sanitize_text_field( rawurldecode( (string) $request->get_param( 's' ) ) );
    $expires    = (int) $request->get_param( 'e' );
    $sig        = (string) $request->get_param( 'k' );

    if ( ! $release_id || '' === $sig || $expires < time() ) {
        return null;
    }
    $payload = $release_id . '|' . $serial . '|' . $expires;
    if ( ! hash_equals( hash_hmac( 'sha256', $payload, ecm_ota_secret() ), $sig ) ) {
        return null;
    }
    return [ 'release_id' => $release_id, 'serial' => $serial ];
}

/** تسجيل/تحديث الجهاز في الأسطول */
function ecm_ota_touch_device( string $serial, array $data = [] ) {
    if ( '' === $serial ) {
        return;
    }
    global $wpdb;
    $table = ecm_ota_devices_table();
    $now   = current_time( 'mysql' );

    $fields = array_intersect_key( $data, array_flip( [
        'chip_id', 'mac', 'model', 'channel', 'fw_version', 'status', 'last_error', 'ip', 'rssi', 'uptime',
    ] ) );
    $fields['last_seen'] = $now;

    $id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE serial = %s", $serial ) );
    if ( $id ) {
        $wpdb->update( $table, $fields, [ 'id' => $id ] );
        return;
    }
    $wpdb->insert( $table, array_merge( [
        'serial'     => $serial,
        'first_seen' => $now,
    ], $fields ) );
}


// ════════════════════════════════════════════════════════════
// §4  REST API — الأجهزة
// ════════════════════════════════════════════════════════════

add_action( 'rest_api_init', function () {

    register_rest_route( 'ecm/v1', '/ota/check', [
        'methods'             => 'GET',
        'callback'            => 'ecm_rest_ota_check',
        'permission_callback' => '__return_true',
    ] );

    register_rest_route( 'ecm/v1', '/ota/download', [
        'methods'             => [ 'GET', 'HEAD' ],
        'callback'            => 'ecm_rest_ota_download',
        'permission_callback' => '__return_true',
    ] );

    register_rest_route( 'ecm/v1', '/ota/report', [
        'methods'             => 'POST',
        'callback'            => 'ecm_rest_ota_report',
        'permission_callback' => '__return_true',
    ] );

    // للوحة الأدمن/التطبيق — يحتاج توكن الـ API
    register_rest_route( 'ecm/v1', '/ota/fleet', [
        'methods'             => 'GET',
        'callback'            => 'ecm_rest_ota_fleet',
        'permission_callback' => function () {
            return function_exists( 'ecm_api_auth' ) ? ecm_api_auth() : current_user_can( 'manage_options' );
        },
    ] );
} );

/**
 * GET /ecm/v1/ota/check
 * params: serial, token, version, model, channel, chip, mac, rssi, uptime
 */
function ecm_rest_ota_check( $request ) {
    $opts = ecm_ota_opts();
    if ( empty( $opts['enabled'] ) ) {
        return new WP_REST_Response( [ 'ok' => false, 'update' => false, 'message' => 'OTA disabled' ], 503 );
    }

    if ( ! ecm_ota_throttle( 'check', 40, 600 ) ) {
        return new WP_REST_Response( [ 'ok' => false, 'update' => false, 'error' => 'too_many_requests' ], 429 );
    }

    $auth = ecm_ota_authenticate( $request );
    if ( ! $auth['ok'] ) {
        return new WP_REST_Response( [ 'ok' => false, 'update' => false, 'error' => $auth['error'] ], 401 );
    }
    $serial = $auth['serial'];

    $current = ecm_ota_clean_version( (string) $request->get_param( 'version' ) );
    $model   = ecm_ota_slug( (string) $request->get_param( 'model' ) );
    $channel = ecm_ota_slug( (string) $request->get_param( 'channel' ), 'stable' );
    if ( ! isset( ecm_ota_channels()[ $channel ] ) ) {
        $channel = 'stable';
    }

    global $wpdb;
    $dev_table = ecm_ota_devices_table();
    $device    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$dev_table} WHERE serial = %s", $serial ) );

    // القناة المثبّتة من اللوحة بتكسب اللي الجهاز طلبه
    if ( $device && '' !== (string) $device->channel ) {
        $channel = (string) $device->channel;
    }

    ecm_ota_touch_device( $serial, [
        'chip_id'    => sanitize_text_field( (string) $request->get_param( 'chip' ) ),
        'mac'        => sanitize_text_field( (string) $request->get_param( 'mac' ) ),
        'model'      => $model,
        'channel'    => $channel,
        'fw_version' => $current,
        'ip'         => ecm_ota_ip(),
        'rssi'       => (int) $request->get_param( 'rssi' ),
        'uptime'     => max( 0, (int) $request->get_param( 'uptime' ) ),
    ] );
    $wpdb->query( $wpdb->prepare( "UPDATE {$dev_table} SET checks = checks + 1 WHERE serial = %s", $serial ) );

    $no_update = [
        'ok'       => true,
        'update'   => false,
        'version'  => $current,
        'check_in' => (int) $opts['check_in'],
    ];

    // إصدار مثبّت لجهاز بعينه — بيتجاوز القناة والطرح التدريجي (بيسمح كمان بالرجوع لنسخة أقدم)
    $pinned = $device && '' !== (string) $device->pin_version;
    $rel    = $pinned
        ? ecm_ota_find_release( $model, (string) $device->pin_version )
        : ecm_ota_latest_release( $model, $channel );

    if ( ! $rel ) {
        return rest_ensure_response( $no_update + [ 'message' => 'لا يوجد إصدار متاح لهذا الموديل' ] );
    }

    if ( ! $pinned ) {
        // نفس الإصدار أو أحدث — مفيش حاجة
        if ( '' !== $current && version_compare( $current, $rel->version, '>=' ) ) {
            return rest_ensure_response( $no_update + [ 'latest' => $rel->version, 'message' => 'الجهاز على أحدث إصدار' ] );
        }
        // حد أدنى للإصدار (تحديث على مرحلتين)
        if ( '' !== (string) $rel->min_version && '' !== $current
            && version_compare( $current, $rel->min_version, '<' ) ) {
            return rest_ensure_response( $no_update + [
                'latest'  => $rel->version,
                'message' => 'لازم تحدّث للإصدار ' . $rel->min_version . ' الأول',
            ] );
        }
        if ( ! ecm_ota_in_rollout( $serial, (int) $rel->id, (int) $rel->rollout ) ) {
            return rest_ensure_response( $no_update + [ 'latest' => $rel->version, 'message' => 'خارج نسبة الطرح الحالية' ] );
        }
    } elseif ( $current !== '' && $current === $rel->version ) {
        return rest_ensure_response( $no_update + [ 'latest' => $rel->version, 'message' => 'الجهاز على الإصدار المثبّت' ] );
    }

    if ( '' === ecm_ota_file_path( (string) $rel->file_name ) ) {
        return rest_ensure_response( $no_update + [ 'message' => 'ملف الإصدار مفقود على السيرفر' ] );
    }

    return rest_ensure_response( [
        'ok'        => true,
        'update'    => true,
        'version'   => $rel->version,
        'url'       => ecm_ota_sign_download( (int) $rel->id, $serial ),
        'md5'       => $rel->md5,
        'sha256'    => $rel->sha256,
        'size'      => (int) $rel->file_size,
        'mandatory' => (bool) $rel->mandatory,
        'notes'     => (string) $rel->notes,
        'channel'   => $rel->channel,
        'pinned'    => (bool) $pinned,
        'check_in'  => (int) $opts['check_in'],
        'message'   => 'تحديث متاح',
    ] );
}

/**
 * GET /ecm/v1/ota/download?r=..&s=..&e=..&k=..
 * بيرمي الملف نفسه مع هيدر x-MD5 اللي مكتبة httpUpdate بتتحقق بيه.
 */
function ecm_rest_ota_download( $request ) {
    $v = ecm_ota_verify_download( $request );
    if ( ! $v ) {
        return new WP_REST_Response( [ 'ok' => false, 'error' => 'invalid_or_expired_link' ], 403 );
    }

    global $wpdb;
    $rel = $wpdb->get_row( $wpdb->prepare(
        'SELECT * FROM ' . ecm_ota_releases_table() . ' WHERE id = %d',
        $v['release_id']
    ) );
    if ( ! $rel || ! (int) $rel->active ) {
        return new WP_REST_Response( [ 'ok' => false, 'error' => 'release_not_found' ], 404 );
    }

    $path = ecm_ota_file_path( (string) $rel->file_name );
    if ( '' === $path ) {
        return new WP_REST_Response( [ 'ok' => false, 'error' => 'file_missing' ], 404 );
    }

    ecm_ota_touch_device( $v['serial'], [ 'status' => 'updating', 'ip' => ecm_ota_ip() ] );
    $wpdb->query( $wpdb->prepare(
        'UPDATE ' . ecm_ota_releases_table() . ' SET downloads = downloads + 1 WHERE id = %d',
        (int) $rel->id
    ) );

    nocache_headers();
    header( 'Content-Type: application/octet-stream' );
    header( 'Content-Length: ' . filesize( $path ) );
    header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
    header( 'x-MD5: ' . $rel->md5 );          // ESP8266/ESP32 httpUpdate بيقرا الهيدر ده
    header( 'x-ECM-Version: ' . $rel->version );

    if ( 'HEAD' === $request->get_method() ) {
        exit;
    }

    if ( function_exists( 'set_time_limit' ) ) {
        @set_time_limit( 0 );
    }
    while ( ob_get_level() ) {
        ob_end_clean();
    }
    readfile( $path );
    exit;
}

/**
 * POST /ecm/v1/ota/report
 * params: serial, token, version, status (success|failed|updating), error
 */
function ecm_rest_ota_report( $request ) {
    if ( ! ecm_ota_throttle( 'report', 60, 600 ) ) {
        return new WP_REST_Response( [ 'ok' => false, 'error' => 'too_many_requests' ], 429 );
    }
    $auth = ecm_ota_authenticate( $request );
    if ( ! $auth['ok'] ) {
        return new WP_REST_Response( [ 'ok' => false, 'error' => $auth['error'] ], 401 );
    }

    $status = sanitize_key( (string) $request->get_param( 'status' ) );
    if ( ! in_array( $status, [ 'success', 'failed', 'updating', 'idle' ], true ) ) {
        $status = 'idle';
    }
    $version = ecm_ota_clean_version( (string) $request->get_param( 'version' ) );
    $error   = sanitize_text_field( (string) $request->get_param( 'error' ) );

    $fields = [
        'status'     => $status,
        'last_error' => 'failed' === $status ? substr( $error, 0, 190 ) : '',
        'ip'         => ecm_ota_ip(),
    ];
    if ( '' !== $version ) {
        $fields['fw_version'] = $version;
    }
    ecm_ota_touch_device( $auth['serial'], $fields );

    // عدّاد نجاح/فشل الإصدار + فكّ التثبيت بعد ما الجهاز يوصل للإصدار المثبّت
    if ( '' !== $version && in_array( $status, [ 'success', 'failed' ], true ) ) {
        global $wpdb;
        $col   = 'success' === $status ? 'success_count' : 'fail_count';
        $model = ecm_ota_slug( (string) $request->get_param( 'model' ) );
        if ( 'default' === $model ) {
            // مابعتش موديل — ناخده من سجل الجهاز نفسه عشان مانعدّش على إصدار موديل تاني
            $known = (string) $wpdb->get_var( $wpdb->prepare(
                'SELECT model FROM ' . ecm_ota_devices_table() . ' WHERE serial = %s',
                $auth['serial']
            ) );
            if ( '' !== $known ) {
                $model = $known;
            }
        }
        $wpdb->query( $wpdb->prepare(
            'UPDATE ' . ecm_ota_releases_table() . " SET {$col} = {$col} + 1 WHERE version = %s AND model = %s",
            $version,
            $model
        ) );
        if ( 'success' === $status ) {
            $wpdb->query( $wpdb->prepare(
                'UPDATE ' . ecm_ota_devices_table() . " SET pin_version = '' WHERE serial = %s AND pin_version = %s",
                $auth['serial'],
                $version
            ) );
        }
    }

    return rest_ensure_response( [ 'ok' => true, 'status' => $status ] );
}

/** GET /ecm/v1/ota/fleet — للوحة/التطبيق */
function ecm_rest_ota_fleet( $request ) {
    global $wpdb;
    $limit = min( 500, max( 1, (int) $request->get_param( 'limit' ) ?: 100 ) );
    $rows  = $wpdb->get_results( $wpdb->prepare(
        'SELECT serial, model, channel, fw_version, pin_version, status, last_error, rssi, last_seen, checks
         FROM ' . ecm_ota_devices_table() . ' ORDER BY last_seen DESC LIMIT %d',
        $limit
    ), ARRAY_A );

    return rest_ensure_response( [
        'ok'      => true,
        'stats'   => ecm_ota_stats(),
        'devices' => $rows ?: [],
    ] );
}
