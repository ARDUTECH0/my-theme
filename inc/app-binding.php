<?php
/**
 * ECM — ربط التطبيق بجهاز واحد (Single-Device Binding)
 *
 * الحساب يتربط بأول جهاز يسجّل دخوله من التطبيق. أي جهاز تاني يترفض
 * لحد ما الأدمن يفك الربط من لوحة التحكم → الجهاز الجديد يقدر يدخل.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** بيانات الجهاز المربوط بالمستخدم (أو مصفوفة فاضية) */
function ecm_app_bound_device( int $user_id ): array {
    $d = get_user_meta( $user_id, 'ecm_app_device', true );
    return is_array( $d ) ? $d : [];
}

/** يربط الجهاز بالمستخدم (أول دخول) */
function ecm_app_bind_device( int $user_id, string $device_id, string $name, string $ip, string $ua ): void {
    $geo = ecm_ip_country( $ip );
    update_user_meta( $user_id, 'ecm_app_device', [
        'id'           => $device_id,
        'name'         => $name,
        'bound_at'     => time(),
        'ip'           => $ip,
        'ua'           => $ua,
        'country'      => $geo['name'] ?? '',
        'country_code' => $geo['code'] ?? '',
    ] );
}

/** يفك الربط + يبطّل توكن الجهاز القديم → جهاز جديد يقدر يدخل */
function ecm_app_unbind_device( int $user_id ): void {
    delete_user_meta( $user_id, 'ecm_app_device' );
    delete_user_meta( $user_id, 'ecm_app_token' ); // إبطال التوكن القديم فورًا
}

/** يولّد توكن جديد للمستخدم ويبطّل القديم (يُستدعى عند ربط جهاز جديد) */
function ecm_app_regenerate_token( int $user_id ): string {
    $t = wp_generate_password( 48, false );
    update_user_meta( $user_id, 'ecm_app_token', $t );
    return $t;
}

// ── تحديد الدولة من الـ IP (جوّه الوورد + كاش) ────────────────
/** هل الـ IP محلي/خاص؟ (مفيش معنى للتحديد الجغرافي) */
function ecm_ip_is_private( string $ip ): bool {
    return ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
}

/**
 * يرجّع دولة الـ IP ['code'=>'EG','name'=>'Egypt'] — أو مصفوفة فاضية.
 * بيستخدم ip-api.com (مجاني) مع كاش أسبوع لكل IP.
 */
function ecm_ip_country( string $ip ): array {
    $ip = trim( $ip );
    if ( '' === $ip || ecm_ip_is_private( $ip ) ) {
        return [];
    }
    $key    = 'ecm_geo_' . md5( $ip );
    $cached = get_transient( $key );
    if ( is_array( $cached ) ) {
        return $cached;
    }
    $res = wp_remote_get(
        'http://ip-api.com/json/' . rawurlencode( $ip ) . '?fields=status,country,countryCode',
        [ 'timeout' => 4 ]
    );
    $out = [];
    if ( ! is_wp_error( $res ) ) {
        $data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
        if ( is_array( $data ) && isset( $data['status'] ) && 'success' === $data['status'] ) {
            $out = [
                'code' => sanitize_text_field( (string) ( $data['countryCode'] ?? '' ) ),
                'name' => sanitize_text_field( (string) ( $data['country'] ?? '' ) ),
            ];
        }
    }
    // كاش حتى لو فشل (مصفوفة فاضية لمدة أقصر) عشان ما نكررش المحاولة كتير
    set_transient( $key, $out, $out ? WEEK_IN_SECONDS : HOUR_IN_SECONDS );
    return $out;
}

/** يحوّل codepoint لـ UTF-8 (بدون مكتبات خارجية) */
function ecm_cp_to_utf8( int $cp ): string {
    if ( $cp <= 0x7F ) {
        return chr( $cp );
    }
    if ( $cp <= 0x7FF ) {
        return chr( 0xC0 | ( $cp >> 6 ) ) . chr( 0x80 | ( $cp & 0x3F ) );
    }
    if ( $cp <= 0xFFFF ) {
        return chr( 0xE0 | ( $cp >> 12 ) ) . chr( 0x80 | ( ( $cp >> 6 ) & 0x3F ) ) . chr( 0x80 | ( $cp & 0x3F ) );
    }
    return chr( 0xF0 | ( $cp >> 18 ) ) . chr( 0x80 | ( ( $cp >> 12 ) & 0x3F ) )
        . chr( 0x80 | ( ( $cp >> 6 ) & 0x3F ) ) . chr( 0x80 | ( $cp & 0x3F ) );
}

/** علم الدولة (إيموجي) من كود الدولة (EG → 🇪🇬) */
function ecm_country_flag( string $code ): string {
    $code = strtoupper( trim( $code ) );
    if ( 2 !== strlen( $code ) || ! ctype_alpha( $code ) ) {
        return '';
    }
    return ecm_cp_to_utf8( 0x1F1E6 + ( ord( $code[0] ) - 65 ) )
        . ecm_cp_to_utf8( 0x1F1E6 + ( ord( $code[1] ) - 65 ) );
}

// ── صفحة الأدمن: الأجهزة المربوطة + فك الربط ──────────────────
add_action( 'admin_menu', function () {
    add_submenu_page(
        'ecm-serials',
        __( 'ربط التطبيقات', 'ecm-theme' ),
        __( '🔗 ربط التطبيقات', 'ecm-theme' ),
        'manage_options',
        'ecm-app-binding',
        'ecm_app_binding_page'
    );
}, 20 );

/** معالج فك الربط */
add_action( 'admin_post_ecm_unbind_device', function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'غير مصرّح', 'ecm-theme' ) );
    }
    check_admin_referer( 'ecm_unbind_device' );
    $uid = (int) ( $_POST['user_id'] ?? 0 );
    if ( $uid ) {
        ecm_app_unbind_device( $uid );
    }
    wp_safe_redirect( add_query_arg( 'unbound', '1', admin_url( 'admin.php?page=ecm-app-binding' ) ) );
    exit;
} );

/** صفحة عرض الأجهزة المربوطة */
function ecm_app_binding_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $users = get_users( [
        'meta_key'    => 'ecm_app_device',
        'number'      => 500,
        'count_total' => false,
    ] );
    ?>
    <div class="wrap">
        <h1>🔗 <?php esc_html_e( 'ربط التطبيقات بالأجهزة', 'ecm-theme' ); ?></h1>

        <?php if ( isset( $_GET['unbound'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>
                <?php esc_html_e( 'تم فك الربط ✅ — العميل يقدر يسجّل دخول من جهاز جديد دلوقتي.', 'ecm-theme' ); ?>
            </p></div>
        <?php endif; ?>

        <p class="description" style="max-width:760px;">
            <?php esc_html_e( 'كل حساب بيتربط بأول جهاز يسجّل دخوله من التطبيق. أي جهاز تاني بيترفض. لو العميل غيّر تليفونه، افك الربط من هنا عشان يقدر يدخل من الجهاز الجديد.', 'ecm-theme' ); ?>
        </p>

        <table class="widefat striped" style="margin-top:14px;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'العميل', 'ecm-theme' ); ?></th>
                    <th><?php esc_html_e( 'الإيميل', 'ecm-theme' ); ?></th>
                    <th><?php esc_html_e( 'الجهاز', 'ecm-theme' ); ?></th>
                    <th><?php esc_html_e( 'الدولة', 'ecm-theme' ); ?></th>
                    <th><?php esc_html_e( 'تاريخ الربط', 'ecm-theme' ); ?></th>
                    <th><?php esc_html_e( 'IP', 'ecm-theme' ); ?></th>
                    <th><?php esc_html_e( 'إجراء', 'ecm-theme' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $users ) ) : ?>
                <tr><td colspan="7"><?php esc_html_e( 'مفيش أجهزة مربوطة لسه.', 'ecm-theme' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $users as $u ) :
                    $d        = ecm_app_bound_device( $u->ID );
                    $bound_at = ! empty( $d['bound_at'] ) ? wp_date( 'Y-m-d H:i', (int) $d['bound_at'] ) : '—';

                    // تحديد الدولة — مع تعبئة السجلات القديمة اللي مفيهاش دولة
                    if ( empty( $d['country_code'] ) && ! empty( $d['ip'] ) ) {
                        $geo = ecm_ip_country( (string) $d['ip'] );
                        if ( ! empty( $geo['code'] ) ) {
                            $d['country']      = $geo['name'];
                            $d['country_code'] = $geo['code'];
                            update_user_meta( $u->ID, 'ecm_app_device', $d );
                        }
                    }
                    $flag    = ! empty( $d['country_code'] ) ? ecm_country_flag( (string) $d['country_code'] ) : '';
                    $country = ! empty( $d['country'] ) ? $d['country'] : '—';
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html( $u->display_name ); ?></strong></td>
                        <td><?php echo esc_html( $u->user_email ); ?></td>
                        <td>
                            <?php echo esc_html( $d['name'] ?? '—' ); ?>
                            <?php if ( ! empty( $d['id'] ) ) : ?>
                                <br><code style="font-size:11px;opacity:.6;"><?php echo esc_html( substr( (string) $d['id'], 0, 16 ) ); ?>…</code>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $flag ? esc_html( $flag . ' ' . $country ) : esc_html( $country ); ?></td>
                        <td><?php echo esc_html( $bound_at ); ?></td>
                        <td><?php echo esc_html( $d['ip'] ?? '—' ); ?></td>
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                                onsubmit="return confirm('متأكد إنك عايز تفك ربط الجهاز ده؟ العميل هيتسجّل خروجه من التطبيق.');">
                                <?php wp_nonce_field( 'ecm_unbind_device' ); ?>
                                <input type="hidden" name="action" value="ecm_unbind_device">
                                <input type="hidden" name="user_id" value="<?php echo esc_attr( $u->ID ); ?>">
                                <button type="submit" class="button button-link-delete">🔓 <?php esc_html_e( 'فك الربط', 'ecm-theme' ); ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
