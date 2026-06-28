<?php
/**
 * ECM — إعداد البريد (SMTP)
 *
 * بيخلّي ووردبريس يبعت كل الإيميلات (الفواتير، إعادة تعيين كلمة المرور،
 * إشعارات الطلبات...) عبر سيرفر SMTP حقيقي عشان توصل وما تروحش Spam.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** إعدادات SMTP المحفوظة */
function ecm_smtp_opts(): array {
    $o = get_option( 'ecm_smtp', [] );
    return is_array( $o ) ? $o : [];
}

/** آخر حالة اتصال محفوظة ['ok'=>bool,'msg'=>string,'time'=>int] */
function ecm_smtp_status(): array {
    $s = get_option( 'ecm_smtp_status', [] );
    return is_array( $s ) ? $s : [];
}

/**
 * اختبار الاتصال الحقيقي بسيرفر SMTP (يتصل + يعمل Authentication بدون إرسال).
 * بيرجّع ['ok'=>bool,'msg'=>string] وبيحفظ الحالة.
 */
function ecm_smtp_test_connection(): array {
    $o = ecm_smtp_opts();
    if ( empty( $o['host'] ) ) {
        return [ 'ok' => false, 'msg' => 'مفيش خادم (Host) متحدّد.' ];
    }

    // تحميل PHPMailer بتاع ووردبريس
    if ( ! class_exists( '\PHPMailer\PHPMailer\PHPMailer' ) ) {
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer( true );
    $mail->isSMTP();
    $mail->Host    = $o['host'];
    $mail->Port    = (int) ( $o['port'] ?? 587 );
    $mail->Timeout = 8;

    $enc = $o['encryption'] ?? 'tls';
    if ( 'none' !== $enc ) {
        $mail->SMTPSecure = $enc;
    } else {
        $mail->SMTPSecure  = '';
        $mail->SMTPAutoTLS = false;
    }
    if ( ! empty( $o['user'] ) ) {
        $mail->SMTPAuth = true;
        $mail->Username = $o['user'];
        $mail->Password = $o['pass'] ?? '';
    }

    $result = [ 'ok' => false, 'msg' => '' ];
    try {
        if ( $mail->smtpConnect() ) {
            $mail->getSMTPInstance()->quit();
            $result = [ 'ok' => true, 'msg' => 'تم الاتصال والتحقق بنجاح ✅' ];
        } else {
            $result['msg'] = 'فشل الاتصال بالخادم.';
        }
    } catch ( \Throwable $e ) {
        $result['msg'] = $e->getMessage();
    }

    update_option( 'ecm_smtp_status', [
        'ok'   => $result['ok'],
        'msg'  => $result['msg'],
        'time' => time(),
    ] );
    return $result;
}

// ── توجيه ووردبريس لإرسال عبر SMTP ────────────────────────────
add_action( 'phpmailer_init', function ( $phpmailer ) {
    $o = ecm_smtp_opts();
    if ( empty( $o['enabled'] ) || empty( $o['host'] ) ) {
        return;
    }
    $phpmailer->isSMTP();
    $phpmailer->Host    = $o['host'];
    $phpmailer->Port    = (int) ( $o['port'] ?? 587 );
    $phpmailer->CharSet = 'UTF-8';

    $enc = $o['encryption'] ?? 'tls';
    if ( 'none' !== $enc ) {
        $phpmailer->SMTPSecure = $enc; // 'tls' أو 'ssl'
    } else {
        $phpmailer->SMTPSecure = '';
        $phpmailer->SMTPAutoTLS = false;
    }

    if ( ! empty( $o['user'] ) ) {
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $o['user'];
        $phpmailer->Password = $o['pass'] ?? '';
    }
} );

// ── ضبط عنوان واسم المُرسِل (مهم لتقليل السبام) ────────────────
add_filter( 'wp_mail_from', function ( $email ) {
    $o = ecm_smtp_opts();
    return ! empty( $o['from_email'] ) ? $o['from_email'] : $email;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
    $o = ecm_smtp_opts();
    return ! empty( $o['from_name'] ) ? $o['from_name'] : $name;
} );

// ── ضمان تفعيل إيميلات ووكومرس المهمة (إعادة التعيين/حساب جديد) ─
// ووكومرس بياخد مسار إعادة تعيين كلمة المرور؛ لو الإيميل متوقّف
// مفيش رسالة بتتبعت. بنفعّلهم برمجيًا للتأكيد.
add_filter( 'woocommerce_email_enabled_customer_reset_password', '__return_true', 99 );
add_filter( 'woocommerce_email_enabled_customer_new_account', '__return_true', 99 );

// ── تسجيل آخر خطأ بريد (للتشخيص) ──────────────────────────────
add_action( 'wp_mail_failed', function ( $wp_error ) {
    if ( is_wp_error( $wp_error ) ) {
        update_option( 'ecm_mail_last_error', [
            'msg'  => $wp_error->get_error_message(),
            'time' => time(),
        ], false );
    }
} );

// ── صفحة الإعدادات في لوحة التحكم ─────────────────────────────
add_action( 'admin_menu', function () {
    add_submenu_page(
        'ecm-serials',
        __( 'البريد (SMTP)', 'ecm-theme' ),
        __( '📧 البريد (SMTP)', 'ecm-theme' ),
        'manage_options',
        'ecm-smtp',
        'ecm_smtp_page'
    );
}, 20 );

function ecm_smtp_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $notice = '';

    // ── حفظ الإعدادات ──
    if ( isset( $_POST['ecm_smtp_save'] ) && check_admin_referer( 'ecm_smtp' ) ) {
        $opts = [
            'enabled'    => ! empty( $_POST['enabled'] ) ? 1 : 0,
            'host'       => sanitize_text_field( wp_unslash( $_POST['host'] ?? '' ) ),
            'port'       => (int) ( $_POST['port'] ?? 587 ),
            'encryption' => in_array( $_POST['encryption'] ?? 'tls', [ 'tls', 'ssl', 'none' ], true ) ? $_POST['encryption'] : 'tls',
            'user'       => sanitize_text_field( wp_unslash( $_POST['user'] ?? '' ) ),
            'from_email' => sanitize_email( wp_unslash( $_POST['from_email'] ?? '' ) ),
            'from_name'  => sanitize_text_field( wp_unslash( $_POST['from_name'] ?? '' ) ),
        ];
        // كلمة المرور: خزّنها كما هي (ممكن تحتوي رموز) — وسيبها لو فاضية
        $pass = trim( (string) wp_unslash( $_POST['pass'] ?? '' ) );
        $old  = ecm_smtp_opts();
        $opts['pass'] = ( '' !== $pass ) ? $pass : ( $old['pass'] ?? '' );

        update_option( 'ecm_smtp', $opts );
        $notice = '<div class="notice notice-success"><p>' . esc_html__( 'تم الحفظ ✅', 'ecm-theme' ) . '</p></div>';
    }

    // ── إرسال إيميل تجريبي ──
    if ( isset( $_POST['ecm_smtp_test'] ) && check_admin_referer( 'ecm_smtp' ) ) {
        $to  = sanitize_email( wp_unslash( $_POST['test_to'] ?? '' ) );
        if ( ! $to ) {
            $to = wp_get_current_user()->user_email;
        }
        $err = '';
        $cap = function ( $wp_error ) use ( &$err ) { $err = $wp_error->get_error_message(); };
        add_action( 'wp_mail_failed', $cap );

        $ok = wp_mail(
            $to,
            'ECM — اختبار البريد',
            "لو وصلتك الرسالة دي يبقى إعداد البريد تمام ✅\n\n— E.Camera.Man"
        );
        remove_action( 'wp_mail_failed', $cap );

        if ( $ok ) {
            update_option( 'ecm_smtp_status', [ 'ok' => true, 'msg' => 'تم الإرسال بنجاح', 'time' => time() ] );
            $notice = '<div class="notice notice-success"><p>' . sprintf(
                /* translators: %s: email */
                esc_html__( 'تم إرسال إيميل تجريبي إلى %s — اتأكد إنه وصل (وبص في Spam).', 'ecm-theme' ),
                esc_html( $to )
            ) . '</p></div>';
        } else {
            $notice = '<div class="notice notice-error"><p><strong>' . esc_html__( 'فشل الإرسال:', 'ecm-theme' ) . '</strong> ' . esc_html( $err ?: __( 'تأكد من بيانات SMTP.', 'ecm-theme' ) ) . '</p></div>';
        }
    }

    // ── اختبار الاتصال بالخادم (بدون إرسال) ──
    if ( isset( $_POST['ecm_smtp_verify'] ) && check_admin_referer( 'ecm_smtp' ) ) {
        $r = ecm_smtp_test_connection();
        if ( $r['ok'] ) {
            $notice = '<div class="notice notice-success"><p>🔌 <strong>' . esc_html__( 'الاتصال سليم:', 'ecm-theme' ) . '</strong> ' . esc_html( $r['msg'] ) . '</p></div>';
        } else {
            $notice = '<div class="notice notice-error"><p>🔌 <strong>' . esc_html__( 'فشل الاتصال:', 'ecm-theme' ) . '</strong> ' . esc_html( $r['msg'] ) . '</p></div>';
        }
    }

    // ── شريط الحالة الحالية ──
    $st = ecm_smtp_status();
    if ( ! empty( $st ) ) {
        $when    = ! empty( $st['time'] ) ? wp_date( 'Y-m-d H:i', (int) $st['time'] ) : '';
        $is_ok   = ! empty( $st['ok'] );
        $bg      = $is_ok ? '#e7f9e0' : '#ffe2e2';
        $bd      = $is_ok ? '#9CFF00' : '#ffb0b0';
        $label   = $is_ok ? '✅ ' . __( 'الإيميل متصل وشغّال', 'ecm-theme' ) : '⚠️ ' . __( 'الإيميل مش متصل', 'ecm-theme' );
        $notice .= '<div style="background:' . $bg . ';border:1px solid ' . $bd . ';border-radius:10px;padding:12px 16px;margin:10px 0;">'
            . '<strong>' . esc_html( $label ) . '</strong>'
            . ( $when ? ' <span style="opacity:.7;">— ' . esc_html__( 'آخر فحص', 'ecm-theme' ) . ': ' . esc_html( $when ) . '</span>' : '' )
            . '</div>';
    }

    // ── آخر خطأ بريد (لو فيه) ──
    $last_err = get_option( 'ecm_mail_last_error', [] );
    if ( ! empty( $last_err['msg'] ) ) {
        $ewhen   = ! empty( $last_err['time'] ) ? wp_date( 'Y-m-d H:i', (int) $last_err['time'] ) : '';
        $notice .= '<div style="background:#fff7e6;border:1px solid #ffd591;border-radius:10px;padding:12px 16px;margin:10px 0;">'
            . '<strong>⚠️ ' . esc_html__( 'آخر فشل في إرسال بريد:', 'ecm-theme' ) . '</strong> '
            . esc_html( $last_err['msg'] )
            . ( $ewhen ? ' <span style="opacity:.7;">(' . esc_html( $ewhen ) . ')</span>' : '' )
            . '</div>';
    }

    $o = ecm_smtp_opts();
    $v = function ( $k, $d = '' ) use ( $o ) {
        return esc_attr( $o[ $k ] ?? $d );
    };
    $enc = $o['encryption'] ?? 'tls';
    ?>
    <div class="wrap">
        <h1>📧 <?php esc_html_e( 'إعداد البريد (SMTP)', 'ecm-theme' ); ?></h1>
        <?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput ?>

        <p class="description" style="max-width:760px;">
            <?php esc_html_e( 'اربط إيميل حقيقي عشان كل رسائل الموقع (الفواتير، إعادة تعيين كلمة المرور، إشعارات الطلبات) توصل بشكل سليم.', 'ecm-theme' ); ?>
        </p>

        <form method="post" style="max-width:640px;margin-top:14px;">
            <?php wp_nonce_field( 'ecm_smtp' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'تفعيل SMTP', 'ecm-theme' ); ?></th>
                    <td><label><input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $o['enabled'] ) ); ?>>
                        <?php esc_html_e( 'ابعت كل الإيميلات عبر SMTP', 'ecm-theme' ); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ecm-host"><?php esc_html_e( 'الخادم (Host)', 'ecm-theme' ); ?></label></th>
                    <td><input name="host" id="ecm-host" type="text" class="regular-text" value="<?php echo $v( 'host' ); ?>" placeholder="smtp.gmail.com"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ecm-port"><?php esc_html_e( 'المنفذ (Port)', 'ecm-theme' ); ?></label></th>
                    <td><input name="port" id="ecm-port" type="number" value="<?php echo esc_attr( $o['port'] ?? 587 ); ?>" style="width:120px;">
                        <span class="description"><?php esc_html_e( '587 لـ TLS · 465 لـ SSL', 'ecm-theme' ); ?></span></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'التشفير', 'ecm-theme' ); ?></th>
                    <td>
                        <select name="encryption">
                            <option value="tls" <?php selected( $enc, 'tls' ); ?>>TLS</option>
                            <option value="ssl" <?php selected( $enc, 'ssl' ); ?>>SSL</option>
                            <option value="none" <?php selected( $enc, 'none' ); ?>><?php esc_html_e( 'بدون', 'ecm-theme' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ecm-user"><?php esc_html_e( 'اسم المستخدم', 'ecm-theme' ); ?></label></th>
                    <td><input name="user" id="ecm-user" type="text" class="regular-text" value="<?php echo $v( 'user' ); ?>" placeholder="you@gmail.com" autocomplete="off"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ecm-pass"><?php esc_html_e( 'كلمة المرور', 'ecm-theme' ); ?></label></th>
                    <td>
                        <input name="pass" id="ecm-pass" type="password" class="regular-text" value="" placeholder="<?php echo ! empty( $o['pass'] ) ? '••••••••• (محفوظة — سيبها فاضية لو مش هتغيّرها)' : ''; ?>" autocomplete="new-password">
                        <p class="description"><?php esc_html_e( 'لـ Gmail استخدم «App Password» مش كلمة مرور حسابك العادية.', 'ecm-theme' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ecm-from-email"><?php esc_html_e( 'إيميل المُرسِل', 'ecm-theme' ); ?></label></th>
                    <td><input name="from_email" id="ecm-from-email" type="email" class="regular-text" value="<?php echo $v( 'from_email', get_option( 'admin_email' ) ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ecm-from-name"><?php esc_html_e( 'اسم المُرسِل', 'ecm-theme' ); ?></label></th>
                    <td><input name="from_name" id="ecm-from-name" type="text" class="regular-text" value="<?php echo $v( 'from_name', get_bloginfo( 'name' ) ); ?>"></td>
                </tr>
            </table>
            <p><button type="submit" name="ecm_smtp_save" value="1" class="button button-primary"><?php esc_html_e( 'حفظ الإعدادات', 'ecm-theme' ); ?></button></p>
        </form>

        <hr style="margin:24px 0;">

        <h2>🔌 <?php esc_html_e( 'اختبار الاتصال', 'ecm-theme' ); ?></h2>
        <form method="post" style="max-width:640px;">
            <?php wp_nonce_field( 'ecm_smtp' ); ?>
            <button type="submit" name="ecm_smtp_verify" value="1" class="button button-secondary"><?php esc_html_e( 'افحص الاتصال بالخادم', 'ecm-theme' ); ?></button>
            <p class="description"><?php esc_html_e( 'بيتصل بسيرفر البريد ويتأكد من البيانات بدون ما يبعت إيميل.', 'ecm-theme' ); ?></p>
        </form>

        <h2 style="margin-top:22px;">✉️ <?php esc_html_e( 'اختبار الإرسال', 'ecm-theme' ); ?></h2>
        <form method="post" style="max-width:640px;">
            <?php wp_nonce_field( 'ecm_smtp' ); ?>
            <input name="test_to" type="email" class="regular-text" placeholder="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>">
            <button type="submit" name="ecm_smtp_test" value="1" class="button"><?php esc_html_e( 'ابعت إيميل تجريبي', 'ecm-theme' ); ?></button>
            <p class="description"><?php esc_html_e( 'احفظ الإعدادات الأول، بعدين جرّب الإرسال.', 'ecm-theme' ); ?></p>
        </form>

        <hr style="margin:24px 0;">
        <h3>ℹ️ <?php esc_html_e( 'إعدادات Gmail الجاهزة', 'ecm-theme' ); ?></h3>
        <p class="description" style="max-width:760px;">
            <?php esc_html_e( 'الخادم: smtp.gmail.com · المنفذ: 587 · التشفير: TLS · المستخدم: إيميلك · كلمة المرور: App Password من إعدادات أمان حساب جوجل (محتاج تفعيل التحقق بخطوتين).', 'ecm-theme' ); ?>
        </p>
    </div>
    <?php
}
