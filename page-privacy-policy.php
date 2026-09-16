<?php
/**
 * Template Name: 🔒 ECM — سياسة الخصوصية (AR/EN)
 *
 * صفحة سياسة خصوصية ثنائية اللغة (عربي/إنجليزي) لتطبيق ECM — مخصّصة للاستخدام
 * كرابط Privacy Policy في Google Play Console. تفتح افتراضيًا بالإنجليزي
 * (حتى بدون JavaScript، عن طريق CSS)، ومعاها زرار تبديل للعربي.
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

$effective_date = [ 'ar' => '17 سبتمبر 2026', 'en' => 'September 17, 2026' ];
$version        = '1.0';
$contact_email  = 'privacy@ecameraman.com';

// ── الأقسام: كل قسم بعنوان ومحتوى بالعربي والإنجليزي ──
$sections = [
    [
        'id' => '01',
        'ar' => [ 'title' => 'من نحن', 'body' =>
            '<p>تطبيق ECM (المعروف أيضًا باسم E-Camera-Man) مطوَّر بواسطة ArduTech، ويُستخدم للتحكم في أجهزة كاميرا/جيمبال ECM عبر Wi-Fi أو Bluetooth، ولإدارة حساب المستخدم ومشترياته عبر موقع ecameraman.com. باستخدامك للتطبيق فإنك توافق على جمع البيانات ومعالجتها كما هو موضح هنا.</p>',
        ],
        'en' => [ 'title' => 'Who We Are', 'body' =>
            '<p>The ECM app (also known as E-Camera-Man) is developed by ArduTech, and is used to control ECM camera/gimbal devices over Wi-Fi or Bluetooth, and to manage your user account and purchases through ecameraman.com. By using the app, you agree to the collection and processing of data as described in this policy.</p>',
        ],
    ],
    [
        'id' => '02',
        'ar' => [ 'title' => 'البيانات التي نجمعها', 'body' =>
            '<h4>2.1 معلومات الحساب</h4>
            <p>عند تسجيل الدخول من شاشة المشتريات داخل التطبيق، نجمع: البريد الإلكتروني، وكلمة المرور (تُرسل مشفّرة عبر HTTPS للتحقق فقط ولا تُخزَّن في التطبيق)، واسم العرض المرتبط بالحساب.</p>
            <h4>2.2 معرّف الجهاز</h4>
            <p>عند تسجيل الدخول، يرسل التطبيق معرّف الجهاز واسمه إلى خوادمنا لربط المشتريات والتراخيص بجهازك، ومنع الاستخدام غير المصرّح به للحساب.</p>
            <h4>2.3 بيانات مخزَّنة على جهازك فقط</h4>
            <p>بعد تسجيل الدخول، يحتفظ التطبيق برمز الدخول (token) واسم المستخدم محليًا على جهازك (عبر SharedPreferences) لإبقائك مسجَّلًا للدخول، ولا تُشارك هذه البيانات مع أي طرف آخر. أي ملفات تشتريها وتُحمّلها تُحفظ في مجلد خاص بالتطبيق على جهازك.</p>
            <h4>2.4 بيانات تقنية للجهاز الصلب</h4>
            <p>أثناء الاتصال بجهاز ECM الخاص بك (الهَب/الجيمبال) عبر الشبكة المحلية، يتبادل التطبيق بيانات تحكم وقياس عن بُعد (مثل حالة المحركات ومستشعرات الحركة) مباشرة مع جهازك — هذه البيانات لا تُرسَل إلى خوادمنا إطلاقًا.</p>',
        ],
        'en' => [ 'title' => 'Data We Collect', 'body' =>
            '<h4>2.1 Account Information</h4>
            <p>When you sign in from the purchases screen inside the app, we collect: your email address, and your password (sent encrypted over HTTPS for verification only and is not stored in the app), and the display name linked to your account.</p>
            <h4>2.2 Device Identifier</h4>
            <p>When you sign in, the app sends your device identifier and device name to our servers to link purchases and licenses to your device, and to prevent unauthorized use of the account.</p>
            <h4>2.3 Data Stored on Your Device Only</h4>
            <p>After signing in, the app keeps your login token and username stored locally on your device (via SharedPreferences) to keep you signed in, and this data is not shared with any other party. Any files you purchase and download are saved in a private, app-specific folder on your device.</p>
            <h4>2.4 Hardware Technical Data</h4>
            <p>While connected to your ECM device (hub/gimbal) over your local network, the app exchanges control and telemetry data (such as motor status and motion-sensor readings) directly with your device — this data is never sent to our servers.</p>',
        ],
    ],
    [
        'id' => '03',
        'ar' => [ 'title' => 'أذونات التطبيق', 'body' =>
            '<p>يطلب التطبيق الأذونات التالية على أندرويد، ولكل منها غرض محدد فقط:</p>
            <div class="ecm-priv-perms">
                <div class="ecm-priv-perm"><code>INTERNET</code><p>الاتصال بخوادمنا لتسجيل الدخول والمشتريات، وبالهَب المحلي عبر شبكة Wi-Fi.</p></div>
                <div class="ecm-priv-perm"><code>ACCESS_WIFI_STATE</code> <code>CHANGE_WIFI_STATE</code><p>اكتشاف شبكة Wi-Fi التي ينشئها جهاز ECM والاتصال بها.</p></div>
                <div class="ecm-priv-perm"><code>BLUETOOTH</code> <code>BLUETOOTH_SCAN</code> <code>BLUETOOTH_CONNECT</code><p>اكتشاف أجهزة ECM القريبة والاقتران بها والتحكم فيها.</p></div>
                <div class="ecm-priv-perm"><code>ACCESS_FINE_LOCATION</code> <code>ACCESS_COARSE_LOCATION</code><p>يفرضه نظام أندرويد كشرط تقني لمسح شبكات Bluetooth/Wi-Fi فقط — التطبيق لا يستخدم موقعك الجغرافي ولا يخزّنه ولا يرسله لأي مكان.</p></div>
            </div>
            <p>لا يطلب التطبيق أي إذن للوصول إلى كل ملفات جهازك؛ الملفات التي تُنزّلها تُحفظ داخل مجلد خاص بالتطبيق فقط.</p>',
        ],
        'en' => [ 'title' => 'App Permissions', 'body' =>
            '<p>The app requests the following permissions on Android, each for one specific purpose only:</p>
            <div class="ecm-priv-perms">
                <div class="ecm-priv-perm"><code>INTERNET</code><p>Connect to our servers for sign-in and purchases, and to the local hub over Wi-Fi.</p></div>
                <div class="ecm-priv-perm"><code>ACCESS_WIFI_STATE</code> <code>CHANGE_WIFI_STATE</code><p>Discover and connect to the Wi-Fi network created by your ECM device.</p></div>
                <div class="ecm-priv-perm"><code>BLUETOOTH</code> <code>BLUETOOTH_SCAN</code> <code>BLUETOOTH_CONNECT</code><p>Discover, pair with, and control nearby ECM devices.</p></div>
                <div class="ecm-priv-perm"><code>ACCESS_FINE_LOCATION</code> <code>ACCESS_COARSE_LOCATION</code><p>Required by Android as a technical condition for scanning Bluetooth/Wi-Fi networks only — the app does not use, store, or send your geographic location anywhere.</p></div>
            </div>
            <p>The app does not request access to all files on your device; files you download are saved only inside a private, app-specific folder.</p>',
        ],
    ],
    [
        'id' => '04',
        'ar' => [ 'title' => 'كيف نستخدم بياناتك', 'body' =>
            '<ul class="ecm-priv-list">
                <li>إنشاء حسابك وتسجيل دخولك والتحقق من هويتك.</li>
                <li>ربط المشتريات والمحتوى المرخّص بجهازك.</li>
                <li>عرض المنتجات والطلبات الخاصة بحسابك داخل التطبيق.</li>
                <li>تمكين الاتصال والتحكم بجهاز ECM الخاص بك.</li>
            </ul>
            <p>لا نستخدم بياناتك في الإعلانات، ولا في بناء ملف تعريف تسويقي عنك.</p>',
        ],
        'en' => [ 'title' => 'How We Use Your Data', 'body' =>
            '<ul class="ecm-priv-list">
                <li>Create your account, sign you in, and verify your identity.</li>
                <li>Link purchases and licensed content to your device.</li>
                <li>Display your account\'s products and orders inside the app.</li>
                <li>Enable connecting to and controlling your ECM device.</li>
            </ul>
            <p>We do not use your data for advertising, nor to build a marketing profile about you.</p>',
        ],
    ],
    [
        'id' => '05',
        'ar' => [ 'title' => 'مشاركة البيانات', 'body' =>
            '<p>لا نبيع بياناتك الشخصية لأي طرف ثالث. التطبيق لا يتضمن أي أدوات تحليلات (Analytics) أو إعلانات أو تتبّع من طرف ثالث. تُشارَك بيانات الحساب فقط مع خوادمنا الخاصة على ecameraman.com لغرض تشغيل التطبيق، وقد نُفصح عن بيانات إذا طُلب ذلك قانونيًا.</p>',
        ],
        'en' => [ 'title' => 'Data Sharing', 'body' =>
            '<p>We do not sell your personal data to any third party. The app does not include any third-party analytics, advertising, or tracking tools. Account data is shared only with our own servers on ecameraman.com to operate the app, and we may disclose data if legally required to do so.</p>',
        ],
    ],
    [
        'id' => '06',
        'ar' => [ 'title' => 'الاتصال بالجهاز محليًا', 'body' =>
            '<p>يتصل التطبيق بجهاز ECM الخاص بك مباشرة عبر شبكة Wi-Fi المحلية التي ينشئها الجهاز (لا تخرج هذه البيانات إلى الإنترنت). هذا الاتصال يُستخدم فقط للتحكم بالكاميرا/الجيمبال وقراءة حالته، ولا علاقة له ببيانات حسابك أو مشترياتك.</p>',
        ],
        'en' => [ 'title' => 'Connecting to Your Device Locally', 'body' =>
            '<p>The app connects to your ECM device directly over the local Wi-Fi network created by the device (this data never reaches the internet). This connection is used only to control the camera/gimbal and read its status, and is unrelated to your account or purchase data.</p>',
        ],
    ],
    [
        'id' => '07',
        'ar' => [ 'title' => 'الأمان والاحتفاظ بالبيانات', 'body' =>
            '<p>تُرسَل بيانات الحساب عبر اتصال مشفّر (HTTPS). نحتفظ ببيانات حسابك طالما كان حسابك نشطًا، ويمكنك طلب حذفه في أي وقت (راجع «حقوقك» أدناه).</p>',
        ],
        'en' => [ 'title' => 'Security & Data Retention', 'body' =>
            '<p>Account data is sent over an encrypted connection (HTTPS). We retain your account data for as long as your account remains active, and you may request its deletion at any time (see "Your Rights" below).</p>',
        ],
    ],
    [
        'id' => '08',
        'ar' => [ 'title' => 'خصوصية الأطفال', 'body' =>
            '<p>هذا التطبيق موجَّه لمستخدمين بالغين لأغراض تشغيلية/تجارية (التحكم بمعدات كاميرا)، وليس موجَّهًا للأطفال دون 13 عامًا، ولا نجمع بياناتهم عن قصد.</p>',
        ],
        'en' => [ 'title' => "Children's Privacy", 'body' =>
            '<p>This app is intended for adult users for operational/commercial purposes (controlling camera equipment), and is not directed at children under the age of 13. We do not knowingly collect data from children.</p>',
        ],
    ],
    [
        'id' => '09',
        'ar' => [ 'title' => 'حقوقك', 'body' =>
            '<p>يمكنك في أي وقت طلب الاطّلاع على بياناتك، تصحيحها، أو حذف حسابك بالكامل من خوادمنا، بالتواصل معنا عبر البيانات أدناه.</p>',
        ],
        'en' => [ 'title' => 'Your Rights', 'body' =>
            '<p>You may at any time request to access your data, correct it, or delete your account entirely from our servers, by contacting us using the details below.</p>',
        ],
    ],
    [
        'id' => '10',
        'ar' => [ 'title' => 'التغييرات على هذه السياسة', 'body' =>
            '<p>قد نُحدّث هذه السياسة من وقت لآخر. سيظهر تاريخ آخر تحديث أعلى الصفحة، وننصح بمراجعتها دوريًا.</p>',
        ],
        'en' => [ 'title' => 'Changes to This Policy', 'body' =>
            '<p>We may update this policy from time to time. The last-updated date will appear at the top of the page, and we recommend reviewing it periodically.</p>',
        ],
    ],
    [
        'id' => '11',
        'ar' => [ 'title' => 'تواصل معنا', 'body' =>
            '<p>لأي استفسار متعلق بالخصوصية، راسلنا على:</p>',
        ],
        'en' => [ 'title' => 'Contact Us', 'body' =>
            '<p>For any privacy-related inquiry, contact us at:</p>',
        ],
    ],
];

/** يطبع قسم رقم داخل بطاقة — نفس الشكل للغتين */
function ecm_priv_render_section( array $sec, string $lang, string $contact_email ): void {
    $d = $sec[ $lang ];
    ?>
    <div id="sec-<?php echo esc_attr( $sec['id'] . '-' . $lang ); ?>" class="ecm-priv-sec">
        <span class="ecm-priv-num"><?php echo esc_html( $sec['id'] ); ?></span>
        <div class="ecm-priv-sec-body">
            <h2><?php echo esc_html( $d['title'] ); ?></h2>
            <?php echo $d['body']; // phpcs:ignore -- محتوى ثابت من هذا الملف فقط ?>
            <?php if ( '11' === $sec['id'] ) : ?>
                <a class="ecm-priv-email" href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>

<style>
    /* ══ سياسة الخصوصية — ثنائية اللغة ══ */
    #ecm-priv-wrap[data-lang="en"] .lang-ar { display: none; }
    #ecm-priv-wrap[data-lang="ar"] .lang-en { display: none; }

    .ecm-priv-toggle { display: inline-flex; gap: 4px; background: var(--ecm-bg-card); border: 1px solid var(--ecm-border); border-radius: 30px; padding: 4px; direction: ltr; }
    .ecm-priv-toggle button { border: none; background: transparent; color: var(--ecm-grey-light); font-family: 'Cairo', sans-serif; font-size: 13px; font-weight: 700; padding: 8px 18px; border-radius: 24px; cursor: pointer; transition: background var(--ecm-transition), color var(--ecm-transition); }
    .ecm-priv-toggle button.active { background: var(--ecm-green); color: #0d0e11; }
    .ecm-priv-toggle button:not(.active):hover { color: var(--ecm-white); }

    .ecm-priv-meta { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin: 18px 0; }
    .ecm-priv-meta span { background: var(--ecm-bg-card); border: 1px solid var(--ecm-border); border-radius: 30px; padding: 7px 16px; font-size: 12.5px; color: var(--ecm-grey-mid); font-family: 'Cairo', sans-serif; }
    .ecm-priv-meta strong { color: var(--ecm-green); font-weight: 800; }

    .ecm-priv-toc { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; max-width: 900px; margin: 0 auto; padding: 0; list-style: none; }
    .ecm-priv-toc li a {
        display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--ecm-grey-light);
        text-decoration: none; padding: 8px 16px; border: 1px solid var(--ecm-border); border-radius: 24px; transition: all var(--ecm-transition);
    }
    .ecm-priv-toc li a:hover { color: var(--ecm-white); border-color: var(--ecm-grey-dark); }
    .ecm-priv-toc li a b { color: var(--ecm-green); font-family: 'Orbitron', 'Cairo', sans-serif; }

    .ecm-priv-sec { scroll-margin-top: calc(var(--ecm-nav-height) + 24px); display: flex; gap: 22px; padding: 30px 0; border-top: 1px solid var(--ecm-border); }
    .ecm-priv-sec:first-of-type { border-top: none; padding-top: 8px; }
    .ecm-priv-num { flex-shrink: 0; width: 52px; font-family: 'Orbitron', 'Cairo', sans-serif; font-size: 22px; font-weight: 800; color: var(--ecm-green); text-shadow: 0 0 20px var(--ecm-green-glow); }
    .ecm-priv-sec-body { flex: 1; min-width: 0; }
    .ecm-priv-sec-body h2 { font-family: 'Cairo', sans-serif; font-size: 19px; font-weight: 800; color: var(--ecm-white); margin: 0 0 12px; }
    .ecm-priv-sec-body h4 { font-family: 'Cairo', sans-serif; font-size: 14.5px; font-weight: 800; color: var(--ecm-white); margin: 16px 0 6px; }
    .ecm-priv-sec-body h4:first-child { margin-top: 0; }
    .ecm-priv-sec-body p { font-size: 14.5px; line-height: 1.9; color: var(--ecm-grey-mid); margin: 0 0 10px; }
    .ecm-priv-sec-body p:last-child { margin-bottom: 0; }

    .ecm-priv-list { margin: 0 0 10px; padding-inline-start: 20px; }
    .ecm-priv-list li { font-size: 14.5px; line-height: 1.9; color: var(--ecm-grey-mid); margin-bottom: 6px; }

    .ecm-priv-perms { display: flex; flex-direction: column; gap: 12px; margin: 14px 0; }
    .ecm-priv-perm { background: var(--ecm-bg-panel); border: 1px solid var(--ecm-border); border-radius: var(--ecm-radius-md); padding: 14px 18px; }
    .ecm-priv-perm code { display: inline-block; direction: ltr; font-family: 'Orbitron', 'Cairo', sans-serif; font-size: 11.5px; color: var(--ecm-green); background: var(--ecm-green-subtle); border-radius: 4px; padding: 3px 9px; margin: 0 4px 6px 0; }
    .ecm-priv-perm p { margin: 6px 0 0 !important; }

    .ecm-priv-email { display: inline-block; margin-top: 8px; font-family: 'Orbitron', 'Cairo', sans-serif; font-size: 15px; font-weight: 700; color: var(--ecm-green); direction: ltr; text-decoration: none; }
    .ecm-priv-email:hover { text-decoration: underline; }

    .ecm-priv-card { background: var(--ecm-bg-card); border: 1px solid var(--ecm-border); border-radius: var(--ecm-radius-lg); padding: 8px 32px; }

    [lang="en"] .ecm-priv-card, [lang="en"] .ecm-priv-toc, [lang="en"] .ecm-priv-num { direction: ltr; }

    @media (max-width: 640px) {
        .ecm-priv-sec { flex-direction: column; gap: 8px; }
        .ecm-priv-card { padding: 8px 20px; }
    }
</style>

<main id="ecm-main" class="ecm-main-content" role="main">
    <div id="ecm-priv-wrap" data-lang="en">

        <!-- Hero + language toggle (يظهر بلغة واحدة بس مش مكرر) -->
        <section class="ecm-container" style="padding-block: 44px 20px; text-align: center;">
            <span class="ecm-eyebrow" style="display:inline-flex; margin-bottom:16px;">PRIVACY · سياسة الخصوصية</span>
            <h1 class="ecm-section-title lang-en" style="margin-bottom:6px;">Privacy Policy</h1>
            <h1 class="ecm-section-title lang-ar" style="margin-bottom:6px;">سياسة الخصوصية</h1>

            <div class="ecm-priv-meta">
                <span class="lang-en">Effective: <strong><?php echo esc_html( $effective_date['en'] ); ?></strong></span>
                <span class="lang-ar">سارية المفعول: <strong><?php echo esc_html( $effective_date['ar'] ); ?></strong></span>
                <span class="lang-en">Version <strong><?php echo esc_html( $version ); ?></strong></span>
                <span class="lang-ar">الإصدار <strong><?php echo esc_html( $version ); ?></strong></span>
            </div>

            <div class="ecm-priv-toggle" role="group" aria-label="Language / اللغة">
                <button type="button" class="active" data-lang="en">English</button>
                <button type="button" data-lang="ar">العربية</button>
            </div>

            <p class="lang-en" style="max-width:640px; margin:22px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.9;">
                This page explains what data the ECM app collects, why we collect it, and how we use and protect it, when you use the app to control your ECM devices (gimbal / camera).
            </p>
            <p class="lang-ar" style="max-width:640px; margin:22px auto 0; color:var(--ecm-grey-mid); font-size:15px; line-height:1.9;">
                توضح هذه الصفحة البيانات التي يجمعها تطبيق ECM، وأسباب جمعها، وكيفية استخدامها وحمايتها، عند استخدامك للتطبيق للتحكم في أجهزة ECM (الجيمبال / الكاميرا) الخاصة بك.
            </p>
        </section>

        <!-- Table of contents -->
        <section class="ecm-container" style="padding-bottom: 40px;">
            <ul class="ecm-priv-toc lang-en">
                <?php foreach ( $sections as $s ) : ?>
                    <li><a href="#sec-<?php echo esc_attr( $s['id'] . '-en' ); ?>"><b><?php echo esc_html( $s['id'] ); ?></b> <?php echo esc_html( $s['en']['title'] ); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <ul class="ecm-priv-toc lang-ar">
                <?php foreach ( $sections as $s ) : ?>
                    <li><a href="#sec-<?php echo esc_attr( $s['id'] . '-ar' ); ?>"><b><?php echo esc_html( $s['id'] ); ?></b> <?php echo esc_html( $s['ar']['title'] ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- Sections -->
        <section class="ecm-container" style="padding-bottom: 64px;">
            <div class="ecm-priv-card lang-en" lang="en" dir="ltr">
                <?php foreach ( $sections as $s ) { ecm_priv_render_section( $s, 'en', $contact_email ); } ?>
            </div>
            <div class="ecm-priv-card lang-ar" lang="ar" dir="rtl">
                <?php foreach ( $sections as $s ) { ecm_priv_render_section( $s, 'ar', $contact_email ); } ?>
            </div>
        </section>

    </div><!-- #ecm-priv-wrap -->
</main>

<script>
(function () {
    var wrap = document.getElementById('ecm-priv-wrap');
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

    // رابط مباشر بلغة معينة — مثال: page-url/#ar
    if (location.hash === '#ar') { setLang('ar'); }
})();
</script>

<?php get_footer(); ?>
