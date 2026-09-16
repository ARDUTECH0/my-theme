<?php
/**
 * Template Name: 🗑️ ECM — حذف الحساب والبيانات (AR/EN)
 *
 * صفحة ثنائية اللغة لطلب حذف الحساب والبيانات — مخصّصة للاستخدام كرابط
 * "Account deletion" في قسم Data Safety بـ Google Play Console.
 * تفتح افتراضيًا بالإنجليزي (حتى بدون JavaScript، عن طريق CSS).
 *
 * @package ecm-theme
 */

get_header();

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single' ) ) {
    get_footer();
    return;
}
if ( function_exists( 'ecm_is_built_with_elementor' ) && ecm_is_built_with_elementor( get_queried_object_id() ) ) {
    ?>
    <main id="ecm-main" class="ecm-main-content" role="main">
        <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
    </main>
    <?php
    get_footer();
    return;
}

$contact_email = 'privacy@ecameraman.com';

$steps = [
    [
        'ar' => [ 'title' => 'أرسل طلبك عبر البريد الإلكتروني', 'body' =>
            'من نفس البريد الإلكتروني المسجَّل به حسابك في تطبيق ECM، أرسل رسالة تحتوي على العنوان: «Account Deletion Request».' ],
        'en' => [ 'title' => 'Send your request by email', 'body' =>
            'From the same email address registered on your ECM app account, send a message with the subject: "Account Deletion Request".' ],
    ],
    [
        'ar' => [ 'title' => 'التحقق من هويتك', 'body' =>
            'قد نطلب منك تأكيد بريدك الإلكتروني أو اسم المستخدم المسجَّل، للتأكد أن الطلب صادر منك فعلًا وحماية حسابك من الحذف غير المصرَّح به.' ],
        'en' => [ 'title' => 'Identity verification', 'body' =>
            'We may ask you to confirm your registered email address or username, to make sure the request actually comes from you and to protect your account from unauthorized deletion.' ],
    ],
    [
        'ar' => [ 'title' => 'تنفيذ الحذف', 'body' =>
            'سيتم حذف حسابك وبياناتك المرتبطة به خلال 30 يومًا من تأكيد الطلب، وسنرسل لك رسالة تأكيد بعد إتمام الحذف.' ],
        'en' => [ 'title' => 'Deletion is carried out', 'body' =>
            'Your account and its associated data will be deleted within 30 days of confirming the request, and we will send you a confirmation message once the deletion is complete.' ],
    ],
];

$table_rows = [
    [
        'ar' => [ 'type' => 'البريد الإلكتروني وكلمة المرور', 'status' => 'يُحذف نهائيًا' ],
        'en' => [ 'type' => 'Email address & password', 'status' => 'Deleted permanently' ],
        'kept' => false,
    ],
    [
        'ar' => [ 'type' => 'الاسم المعروض ومعرّف الجهاز (device ID)', 'status' => 'يُحذف نهائيًا' ],
        'en' => [ 'type' => 'Display name & device ID', 'status' => 'Deleted permanently' ],
        'kept' => false,
    ],
    [
        'ar' => [ 'type' => 'رمز الدخول (token) المخزَّن على جهازك', 'status' => 'يُحذف فور تسجيل الخروج/حذف الحساب' ],
        'en' => [ 'type' => 'Login token stored on your device', 'status' => 'Deleted immediately on sign-out / account deletion' ],
        'kept' => false,
    ],
    [
        'ar' => [ 'type' => 'سجلات الفواتير/المعاملات المالية', 'status' => 'يُحتفظ بها بصيغة غير مرتبطة باسمك لمدة تصل إلى 5 سنوات، للالتزام بالمتطلبات المحاسبية والقانونية' ],
        'en' => [ 'type' => 'Billing / transaction records', 'status' => 'Retained in a form not linked to your name for up to 5 years, to comply with accounting and legal requirements' ],
        'kept' => true,
    ],
];
?>

<style>
    /* ══ حذف الحساب — نفس آلية اللغة المستخدمة في صفحة الخصوصية ══ */
    #ecm-del-wrap[data-lang="en"] .lang-ar { display: none; }
    #ecm-del-wrap[data-lang="ar"] .lang-en { display: none; }

    .ecm-priv-toggle { display: inline-flex; gap: 4px; background: var(--ecm-bg-card); border: 1px solid var(--ecm-border); border-radius: 30px; padding: 4px; direction: ltr; }
    .ecm-priv-toggle button { border: none; background: transparent; color: var(--ecm-grey-light); font-family: 'Cairo', sans-serif; font-size: 13px; font-weight: 700; padding: 8px 18px; border-radius: 24px; cursor: pointer; transition: background var(--ecm-transition), color var(--ecm-transition); }
    .ecm-priv-toggle button.active { background: var(--ecm-green); color: #0d0e11; }
    .ecm-priv-toggle button:not(.active):hover { color: var(--ecm-white); }

    .ecm-priv-card { background: var(--ecm-bg-card); border: 1px solid var(--ecm-border); border-radius: var(--ecm-radius-lg); padding: 8px 32px; }
    .ecm-priv-email { display: inline-block; margin-top: 4px; font-family: 'Orbitron', 'Cairo', sans-serif; font-size: 15px; font-weight: 700; color: var(--ecm-green); direction: ltr; text-decoration: none; }
    .ecm-priv-email:hover { text-decoration: underline; }

    /* خطوات مرقّمة */
    .ecm-del-steps { display: flex; flex-direction: column; }
    .ecm-del-step { display: flex; gap: 20px; padding: 26px 0; border-top: 1px solid var(--ecm-border); }
    .ecm-del-step:first-child { border-top: none; padding-top: 4px; }
    .ecm-del-step-num {
        flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: var(--ecm-green-subtle); border: 1px solid var(--ecm-border);
        font-family: 'Orbitron', 'Cairo', sans-serif; font-size: 16px; font-weight: 800; color: var(--ecm-green);
    }
    .ecm-del-step-body h3 { font-family: 'Cairo', sans-serif; font-size: 16.5px; font-weight: 800; color: var(--ecm-white); margin: 4px 0 8px; }
    .ecm-del-step-body p { font-size: 14.5px; line-height: 1.85; color: var(--ecm-grey-mid); margin: 0; }

    /* جدول البيانات */
    .ecm-del-table-wrap { overflow-x: auto; margin: 8px 0 4px; }
    .ecm-del-table { width: 100%; border-collapse: collapse; min-width: 480px; }
    .ecm-del-table th { text-align: start; font-family: 'Cairo', sans-serif; font-size: 12.5px; color: var(--ecm-grey-mid); text-transform: uppercase; letter-spacing: .5px; padding: 10px 14px; border-bottom: 1px solid var(--ecm-border); }
    .ecm-del-table td { padding: 14px; border-bottom: 1px solid var(--ecm-border); font-size: 14px; line-height: 1.75; vertical-align: top; }
    .ecm-del-table tr:last-child td { border-bottom: none; }
    .ecm-del-table td:first-child { font-weight: 700; color: var(--ecm-white); white-space: nowrap; }
    .ecm-del-status { display: inline-flex; align-items: center; gap: 6px; color: var(--ecm-grey-mid); }
    .ecm-del-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; background: var(--ecm-red); }
    tr.is-kept .ecm-del-status::before { background: var(--ecm-amber); }

    .ecm-del-note {
        display: flex; gap: 12px; align-items: flex-start; margin-top: 28px;
        background: var(--ecm-bg-panel); border: 1px solid var(--ecm-border); border-inline-start: 3px solid var(--ecm-green);
        border-radius: var(--ecm-radius-md); padding: 16px 20px; font-size: 14px; line-height: 1.85; color: var(--ecm-grey-light);
    }
    .ecm-del-note .ic { font-size: 20px; flex-shrink: 0; }

    [lang="en"] .ecm-priv-card { direction: ltr; }

    @media (max-width: 640px) {
        .ecm-priv-card { padding: 8px 20px; }
        .ecm-del-step { flex-direction: column; gap: 10px; }
    }
</style>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div id="ecm-del-wrap" data-lang="en">

        <!-- Hero + language toggle -->
        <section class="ecm-container" style="padding-block: 44px 20px; text-align: center;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:16px;">ACCOUNT DELETION · حذف الحساب</span>
            <h1 class="ecm-section-title lang-en" style="margin-bottom:6px;">Account &amp; Data Deletion</h1>
            <h1 class="ecm-section-title lang-ar" style="margin-bottom:6px;">حذف الحساب والبيانات</h1>

            <div class="ecm-priv-toggle" role="group" aria-label="Language / اللغة">
                <button type="button" class="active" data-lang="en">English</button>
                <button type="button" data-lang="ar">العربية</button>
            </div>

            <p class="lang-en" style="max-width:640px; margin:22px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.9;">
                If you use the ECM app (E-Camera-Man) and have a registered account, you can request permanent deletion of your account and all associated data through the steps below.
            </p>
            <p class="lang-ar" style="max-width:640px; margin:22px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.9;">
                إذا كنت تستخدم تطبيق ECM (E-Camera-Man) ولديك حساب مسجَّل، يمكنك طلب حذف حسابك وكل البيانات المرتبطة به نهائيًا من خلال الخطوات التالية.
            </p>
        </section>

        <!-- Steps -->
        <section class="ecm-container" style="padding-bottom: 48px;">
            <div class="ecm-priv-card lang-en" lang="en" dir="ltr">
                <div class="ecm-del-steps">
                    <?php foreach ( $steps as $i => $s ) : ?>
                        <div class="ecm-del-step">
                            <span class="ecm-del-step-num"><?php echo (int) ( $i + 1 ); ?></span>
                            <div class="ecm-del-step-body">
                                <h3><?php echo esc_html( $s['en']['title'] ); ?></h3>
                                <p><?php echo esc_html( $s['en']['body'] ); ?></p>
                                <?php if ( 0 === $i ) : ?>
                                    <a class="ecm-priv-email" href="mailto:<?php echo esc_attr( $contact_email ); ?>">✉ <?php echo esc_html( $contact_email ); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="ecm-priv-card lang-ar" lang="ar" dir="rtl">
                <div class="ecm-del-steps">
                    <?php foreach ( $steps as $i => $s ) : ?>
                        <div class="ecm-del-step">
                            <span class="ecm-del-step-num"><?php echo (int) ( $i + 1 ); ?></span>
                            <div class="ecm-del-step-body">
                                <h3><?php echo esc_html( $s['ar']['title'] ); ?></h3>
                                <p><?php echo esc_html( $s['ar']['body'] ); ?></p>
                                <?php if ( 0 === $i ) : ?>
                                    <a class="ecm-priv-email" href="mailto:<?php echo esc_attr( $contact_email ); ?>">✉ <?php echo esc_html( $contact_email ); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- What gets deleted vs retained -->
        <section class="ecm-container" style="padding-bottom: 64px;">
            <div class="ecm-priv-card lang-en" lang="en" dir="ltr">
                <h2 style="font-family:'Cairo',sans-serif; font-size:18px; font-weight:800; color:var(--ecm-white); margin:18px 0 4px;">What gets deleted, and what is retained</h2>
                <div class="ecm-del-table-wrap">
                    <table class="ecm-del-table">
                        <thead><tr><th>Data Type</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach ( $table_rows as $r ) : ?>
                                <tr class="<?php echo $r['kept'] ? 'is-kept' : ''; ?>">
                                    <td><?php echo esc_html( $r['en']['type'] ); ?></td>
                                    <td><span class="ecm-del-status"><?php echo esc_html( $r['en']['status'] ); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="ecm-del-note">
                    <span class="ic">ℹ️</span>
                    <span>Signing in inside the app is required only for the "Purchases" screen — the rest of the app's screens (camera/gimbal control) work without any account, and deleting your account does not prevent you from using them.</span>
                </div>
            </div>
            <div class="ecm-priv-card lang-ar" lang="ar" dir="rtl">
                <h2 style="font-family:'Cairo',sans-serif; font-size:18px; font-weight:800; color:var(--ecm-white); margin:18px 0 4px;">ما الذي يُحذف، وما الذي يُحتفظ به</h2>
                <div class="ecm-del-table-wrap">
                    <table class="ecm-del-table">
                        <thead><tr><th>نوع البيانات</th><th>الحالة</th></tr></thead>
                        <tbody>
                            <?php foreach ( $table_rows as $r ) : ?>
                                <tr class="<?php echo $r['kept'] ? 'is-kept' : ''; ?>">
                                    <td><?php echo esc_html( $r['ar']['type'] ); ?></td>
                                    <td><span class="ecm-del-status"><?php echo esc_html( $r['ar']['status'] ); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="ecm-del-note">
                    <span class="ic">ℹ️</span>
                    <span>تسجيل الدخول جوه التطبيق مطلوب فقط لشاشة «المشتريات» — باقي شاشات التطبيق (التحكم في الكاميرا/الجيمبال) بتشتغل بدون أي حساب، وحذف الحساب لا يمنعك من استخدامها.</span>
                </div>
            </div>
        </section>

    </div><!-- #ecm-del-wrap -->
</main>

<script>
(function () {
    var wrap = document.getElementById('ecm-del-wrap');
    if (!wrap) return;
    var btns = wrap.querySelectorAll('.ecm-priv-toggle button');

    function setLang(lang) {
        wrap.setAttribute('data-lang', lang);
        btns.forEach(function (b) { b.classList.toggle('active', b.getAttribute('data-lang') === lang); });
        try { history.replaceState(null, '', lang === 'ar' ? '#ar' : '#en'); } catch (e) {}
    }

    btns.forEach(function (b) {
        b.addEventListener('click', function () { setLang(b.getAttribute('data-lang')); });
    });

    if (location.hash === '#ar') { setLang('ar'); }
})();
</script>

<?php get_footer(); ?>
