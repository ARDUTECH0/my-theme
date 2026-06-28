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

    // إجبار المُرسِل = حساب SMTP (بيتخطّى أي From بيحطّه ووكومرس/إضافات).
    // ده آخر نقطة قبل الإرسال → بيضمن السيرفر ميرفضش الرسالة.
    $from = ecm_smtp_from_address();
    if ( $from && is_email( $from ) ) {
        $phpmailer->From   = $from;
        $phpmailer->Sender = $from; // Return-Path / envelope sender
    }
} );

/** نفس الدومين؟ (للمقارنة بين المُرسِل وحساب SMTP) */
function ecm_same_mail_domain( string $a, string $b ): bool {
    $da = strtolower( substr( strrchr( $a, '@' ) ?: '', 1 ) );
    $db = strtolower( substr( strrchr( $b, '@' ) ?: '', 1 ) );
    return '' !== $da && $da === $db;
}

/**
 * عنوان المُرسِل الصحيح: لازم يكون نفس حساب الـ SMTP (السيرفر بيرفض غيره).
 * لو حدّدت from_email على نفس دومين الحساب → بنسمح بيه.
 */
function ecm_smtp_from_address(): string {
    $o    = ecm_smtp_opts();
    $user = $o['user'] ?? '';
    if ( '' !== $user && is_email( $user ) ) {
        if ( ! empty( $o['from_email'] ) && ecm_same_mail_domain( $o['from_email'], $user ) ) {
            return $o['from_email'];
        }
        return $user; // أأمن خيار — يطابق الحساب المُصادَق عليه
    }
    return ! empty( $o['from_email'] ) ? $o['from_email'] : '';
}

// عنوان المُرسِل للكور
add_filter( 'wp_mail_from', function ( $email ) {
    $from = ecm_smtp_from_address();
    return $from ?: $email;
} );
// عنوان المُرسِل لإيميلات ووكومرس (بتحطّه بنفسها فلازم نظبّطه هنا كمان)
add_filter( 'woocommerce_email_from_address', function ( $email ) {
    $from = ecm_smtp_from_address();
    return $from ?: $email;
}, 99 );
add_filter( 'wp_mail_from_name', function ( $name ) {
    $o = ecm_smtp_opts();
    return ! empty( $o['from_name'] ) ? $o['from_name'] : $name;
} );
add_filter( 'woocommerce_email_from_name', function ( $name ) {
    $o = ecm_smtp_opts();
    return ! empty( $o['from_name'] ) ? $o['from_name'] : $name;
}, 99 );

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

// ── سجل كل الرسائل الصادرة (يبيّن هل الريستارت بيتحاول إرساله) ─
add_filter( 'wp_mail', function ( $atts ) {
    $log = get_option( 'ecm_mail_log', [] );
    if ( ! is_array( $log ) ) {
        $log = [];
    }
    $to = $atts['to'] ?? '';
    if ( is_array( $to ) ) {
        $to = implode( ', ', $to );
    }
    array_unshift( $log, [
        'time'    => time(),
        'to'      => is_string( $to ) ? $to : '',
        'subject' => $atts['subject'] ?? '',
    ] );
    update_option( 'ecm_mail_log', array_slice( $log, 0, 15 ), false );
    return $atts;
}, 1 );

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
            delete_option( 'ecm_mail_last_error' );
            $notice = '<div class="notice notice-success"><p>' . sprintf(
                /* translators: %s: email */
                esc_html__( 'تم إرسال إيميل تجريبي إلى %s — اتأكد إنه وصل (وبص في Spam).', 'ecm-theme' ),
                esc_html( $to )
            ) . '</p></div>';
        } else {
            $notice = '<div class="notice notice-error"><p><strong>' . esc_html__( 'فشل الإرسال:', 'ecm-theme' ) . '</strong> ' . esc_html( $err ?: __( 'تأكد من بيانات SMTP.', 'ecm-theme' ) ) . '</p></div>';
        }
    }

    // ── إرسال رابط إعادة تعيين كلمة المرور لأي عميل (تشخيص + حل بديل) ──
    if ( isset( $_POST['ecm_send_reset'] ) && check_admin_referer( 'ecm_smtp' ) ) {
        $who  = sanitize_text_field( wp_unslash( $_POST['reset_user'] ?? '' ) );
        $user = is_email( $who ) ? get_user_by( 'email', $who ) : get_user_by( 'login', $who );

        if ( ! $user ) {
            $notice = '<div class="notice notice-error"><p>' . esc_html__( 'مفيش مستخدم بالإيميل/الاسم ده.', 'ecm-theme' ) . '</p></div>';
        } else {
            $key = get_password_reset_key( $user );
            if ( is_wp_error( $key ) ) {
                $notice = '<div class="notice notice-error"><p>' . esc_html( $key->get_error_message() ) . '</p></div>';
            } else {
                $reset_url = network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user->user_login ), 'login' );
                $site      = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
                $msg       = "مرحبًا " . $user->display_name . "،\n\n"
                    . "وصلنا طلب لإعادة تعيين كلمة المرور لحسابك في {$site}.\n"
                    . "اضغط الرابط ده عشان تختار كلمة مرور جديدة:\n\n{$reset_url}\n\n"
                    . "لو مش إنت اللي طلبت، تجاهل الرسالة دي.\n\n— {$site}";
                $err = '';
                $cap = function ( $e ) use ( &$err ) { $err = $e->get_error_message(); };
                add_action( 'wp_mail_failed', $cap );
                $ok = wp_mail( $user->user_email, "إعادة تعيين كلمة المرور — {$site}", $msg );
                remove_action( 'wp_mail_failed', $cap );

                if ( $ok ) {
                    $notice = '<div class="notice notice-success"><p>' . sprintf(
                        /* translators: %s: email */
                        esc_html__( 'تم إرسال رابط إعادة التعيين إلى %s ✅', 'ecm-theme' ),
                        esc_html( $user->user_email )
                    ) . '</p></div>';
                } else {
                    $notice = '<div class="notice notice-error"><p><strong>' . esc_html__( 'فشل الإرسال:', 'ecm-theme' ) . '</strong> ' . esc_html( $err ?: __( 'تأكد من بيانات SMTP.', 'ecm-theme' ) ) . '</p></div>';
                }
            }
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
                    <td>
                        <input name="from_email" id="ecm-from-email" type="email" class="regular-text" value="<?php echo $v( 'from_email', get_option( 'admin_email' ) ); ?>">
                        <p class="description" style="color:#b45309;">
                            ⚠️ <?php esc_html_e( 'لازم يكون نفس حساب SMTP (أو على نفس الدومين)، وإلا السيرفر بيرفض الإرسال. لو سيبته مختلف، هنستخدم حساب SMTP تلقائيًا.', 'ecm-theme' ); ?>
                        </p>
                    </td>
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

        <h2>🔑 <?php esc_html_e( 'إرسال رابط إعادة تعيين كلمة المرور', 'ecm-theme' ); ?></h2>
        <form method="post" style="max-width:640px;">
            <?php wp_nonce_field( 'ecm_smtp' ); ?>
            <input name="reset_user" type="text" class="regular-text" placeholder="<?php esc_attr_e( 'إيميل أو اسم مستخدم العميل', 'ecm-theme' ); ?>" required>
            <button type="submit" name="ecm_send_reset" value="1" class="button button-primary"><?php esc_html_e( 'ابعت رابط إعادة التعيين', 'ecm-theme' ); ?></button>
            <p class="description"><?php esc_html_e( 'بيبعت رابط إعادة تعيين حقيقي للعميل — استخدمه لو العميل مش بيوصله الإيميل تلقائيًا.', 'ecm-theme' ); ?></p>
        </form>

        <hr style="margin:24px 0;">

        <h2>📜 <?php esc_html_e( 'سجل آخر الرسائل', 'ecm-theme' ); ?></h2>
        <?php $log = get_option( 'ecm_mail_log', [] ); ?>
        <?php if ( empty( $log ) ) : ?>
            <p class="description"><?php esc_html_e( 'لسه مفيش رسائل اتبعتت. اطلب إعادة تعيين الباسورد وارجع شوف هنا هل ظهرت محاولة ولا لأ.', 'ecm-theme' ); ?></p>
        <?php else : ?>
            <table class="widefat striped" style="max-width:760px;">
                <thead><tr>
                    <th><?php esc_html_e( 'الوقت', 'ecm-theme' ); ?></th>
                    <th><?php esc_html_e( 'إلى', 'ecm-theme' ); ?></th>
                    <th><?php esc_html_e( 'الموضوع', 'ecm-theme' ); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ( $log as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( ! empty( $row['time'] ) ? wp_date( 'Y-m-d H:i', (int) $row['time'] ) : '' ); ?></td>
                        <td><?php echo esc_html( $row['to'] ?? '' ); ?></td>
                        <td><?php echo esc_html( $row['subject'] ?? '' ); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="description"><?php esc_html_e( 'لو طلبت إعادة تعيين الباسورد ومظهرتش محاولة هنا → الوورد مش بيبعت الطلب أصلاً (مش مشكلة SMTP).', 'ecm-theme' ); ?></p>
        <?php endif; ?>

        <hr style="margin:24px 0;">
        <h3>ℹ️ <?php esc_html_e( 'إعدادات Gmail الجاهزة', 'ecm-theme' ); ?></h3>
        <p class="description" style="max-width:760px;">
            <?php esc_html_e( 'الخادم: smtp.gmail.com · المنفذ: 587 · التشفير: TLS · المستخدم: إيميلك · كلمة المرور: App Password من إعدادات أمان حساب جوجل (محتاج تفعيل التحقق بخطوتين).', 'ecm-theme' ); ?>
        </p>
    </div>
    <?php
}
