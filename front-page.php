<?php
/**
 * ECM Theme — front-page.php
 * الصفحة الرئيسية الكاملة — سلايدر + محتوى + Documentation + CTA
 *
 * @package ecm-theme
 */

get_header();

// ── Elementor: لو الصفحة مبنية بـ Elementor، اعرض محتواها بالكامل (Full Width) بدل التصميم الجاهز ──
if ( ecm_is_built_with_elementor( get_queried_object_id() ) ) {
    ?>
    <main id="ecm-main" class="ecm-main-content" role="main">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
    </main>
    <?php
    get_footer();
    return;
}

// ── Helper ──
if ( ! function_exists( '_ecm' ) ) {
    function _ecm( $key, $default = '' ) {
        return esc_html( get_theme_mod( $key, $default ) );
    }
}
?>

<main id="ecm-main" class="ecm-main-content" role="main">

<!-- ═══════════════════════════════════════
     HERO SLIDER
     ═══════════════════════════════════════ -->
<section class="ecm-slider" id="hero">
    <div class="ecm-slider-track" id="ecmSliderTrack">
        <?php
        $slides_defaults = [
            1 => [
                'eyebrow' => 'SYSTEM ACTIVE',
                'title_g' => 'التحكم',
                'title_w' => '— ECM',
                'sub'     => 'E.Camera.Man',
                'desc'    => 'نظام تحكم احترافي بالكاميرات — تحكم لاسلكي بدقة عالية وأداء لا مثيل له',
                'btn'     => 'اكتشف الآن',
                'link'    => '#features',
            ],
            2 => [
                'eyebrow' => 'PRECISION CONTROL',
                'title_g' => 'دقة',
                'title_w' => 'بلا حدود',
                'sub'     => '120 FPS — 4K READY',
                'desc'    => 'تحكم في كل محور بدقة عالية — Slider، Pan، Tilt، Zoom، Focus، Crane',
                'btn'     => 'شوف المواصفات',
                'link'    => '#specs',
            ],
            3 => [
                'eyebrow' => 'WIRELESS RANGE',
                'title_g' => '100 متر',
                'title_w' => 'لاسلكي',
                'sub'     => 'WiFi 6 + BLE 5.3',
                'desc'    => 'اتصال مستقر بتأخير أقل من 10ms — تحكم كامل عن بُعد بدون أسلاك',
                'btn'     => 'تواصل معنا',
                'link'    => '#contact',
            ],
        ];

        for ( $s = 1; $s <= 3; $s++ ) :
            $def = $slides_defaults[ $s ];
            $active = ( $s === 1 ) ? ' ecm-slide-active' : '';
            $slide_img = get_theme_mod( "ecm_slide_{$s}_image", '' );
            $overlay   = intval( get_theme_mod( "ecm_slide_{$s}_overlay", 60 ) );
        ?>
        <div class="ecm-slide<?php echo $active; ?>" data-slide="<?php echo $s; ?>">
            <?php if ( $slide_img ) : ?>
                <div class="ecm-slide-bg" style="background-image: url('<?php echo esc_url( $slide_img ); ?>');">
                    <div class="ecm-slide-overlay" style="opacity: <?php echo $overlay / 100; ?>;"></div>
                </div>
            <?php endif; ?>
            <div class="ecm-hero-inner">
                <span class="ecm-eyebrow"><?php echo _ecm( "ecm_slide_{$s}_eyebrow", $def['eyebrow'] ); ?></span>
                <h2 class="ecm-hero-title">
                    <span class="ecm-green"><?php echo _ecm( "ecm_slide_{$s}_title_g", $def['title_g'] ); ?></span>
                    <?php echo _ecm( "ecm_slide_{$s}_title_w", $def['title_w'] ); ?>
                </h2>
                <p class="ecm-hero-subtitle"><?php echo _ecm( "ecm_slide_{$s}_sub", $def['sub'] ); ?></p>
                <p class="ecm-hero-desc"><?php echo _ecm( "ecm_slide_{$s}_desc", $def['desc'] ); ?></p>
                <div class="ecm-hero-actions">
                    <a href="<?php echo esc_url( get_theme_mod( "ecm_slide_{$s}_link", $def['link'] ) ); ?>" class="ecm-btn-primary">
                        <?php echo _ecm( "ecm_slide_{$s}_btn", $def['btn'] ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Slider Dots -->
    <div class="ecm-slider-dots">
        <button class="ecm-dot ecm-dot-active" data-goto="1" aria-label="Slide 1"></button>
        <button class="ecm-dot" data-goto="2" aria-label="Slide 2"></button>
        <button class="ecm-dot" data-goto="3" aria-label="Slide 3"></button>
    </div>

    <!-- Slider Arrows -->
    <button class="ecm-slider-arrow ecm-arrow-prev" id="ecmPrev" aria-label="السابق">‹</button>
    <button class="ecm-slider-arrow ecm-arrow-next" id="ecmNext" aria-label="التالي">›</button>
</section>


<!-- ═══════════════════════════════════════
     STATS BAR
     ═══════════════════════════════════════ -->
<section class="ecm-stats-bar" id="stats">
    <div class="ecm-stats-grid">
        <?php
        $stat_defaults = [
            1 => [ '⚡', '120', 'FPS', 'إطارات في الثانية' ],
            2 => [ '📺', '4K', '', 'دقة التصوير' ],
            3 => [ '📡', '100m', '', 'نطاق التحكم' ],
            4 => [ '🔋', '8h', '+', 'عمر البطارية' ],
        ];
        foreach ( $stat_defaults as $i => $def ) :
            $icon   = _ecm( "ecm_stat_{$i}_icon",   $def[0] );
            $num    = _ecm( "ecm_stat_{$i}_num",     $def[1] );
            $suffix = _ecm( "ecm_stat_{$i}_suffix",  $def[2] );
            $label  = _ecm( "ecm_stat_{$i}_label",   $def[3] );
        ?>
        <div class="ecm-stat-box">
            <span class="ecm-stat-icon"><?php echo $icon; ?></span>
            <span class="ecm-stat-num"><?php echo $num; ?><?php if ( $suffix ) : ?><sup><?php echo $suffix; ?></sup><?php endif; ?></span>
            <span class="ecm-stat-label"><?php echo $label; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>


<!-- ═══════════════════════════════════════
     CONTROL CARDS
     ═══════════════════════════════════════ -->
<section class="ecm-section" id="controls">
    <div class="ecm-container">
        <span class="ecm-eyebrow"><?php echo _ecm( 'ecm_ctrl_eyebrow', '01 — CAMERA CONTROLS' ); ?></span>
        <h2 class="ecm-section-title"><?php echo _ecm( 'ecm_ctrl_title', 'لوحة التحكم' ); ?></h2>
        <p class="ecm-section-desc"><?php echo _ecm( 'ecm_ctrl_desc', 'تحكم كامل بجميع محاور الكاميرا بدقة متناهية' ); ?></p>

        <div class="ecm-ctrl-grid-4">
            <?php
            $card_defaults = [
                1 => [ '↔', 'SLIDER', '9', '52', '52', '105' ],
                2 => [ '↻', 'PAN',    '14', '78', '78', '180' ],
                3 => [ '↕', 'TILT',   '7', '45', '45', '90' ],
                4 => [ '◎', 'FOCUS',  '12', '65', '65', '100' ],
                5 => [ '🔍', 'ZOOM',  '5', '35', '35', '100' ],
                6 => [ '🎬', 'CRANE', '8', '60', '60', '120' ],
            ];
            foreach ( $card_defaults as $i => $def ) :
                $icon    = _ecm( "ecm_card_{$i}_icon",    $def[0] );
                $title   = _ecm( "ecm_card_{$i}_title",   $def[1] );
                $speed   = _ecm( "ecm_card_{$i}_speed",   $def[2] );
                $bar     = intval( get_theme_mod( "ecm_card_{$i}_bar", $def[3] ) );
                $current = _ecm( "ecm_card_{$i}_current", $def[4] );
                $target  = _ecm( "ecm_card_{$i}_target",  $def[5] );
            ?>
            <div class="ecm-ctrl-card">
                <div class="ecm-ctrl-icon"><?php echo $icon; ?></div>
                <div class="ecm-ctrl-title"><?php echo $title; ?></div>
                <span class="ecm-ctrl-speed-label">SPEED</span>
                <div class="ecm-ctrl-val"><?php echo $speed; ?></div>
                <div class="ecm-ctrl-bar"><div class="ecm-ctrl-bar-fill" style="width:<?php echo $bar; ?>%"></div></div>
                <div class="ecm-ctrl-footer">
                    <span>Current: <span><?php echo $current; ?></span></span>
                    <span>Target: <span><?php echo $target; ?></span></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════
     FEATURES
     ═══════════════════════════════════════ -->
<section class="ecm-section" id="features">
    <div class="ecm-container">
        <span class="ecm-eyebrow"><?php echo _ecm( 'ecm_feat_eyebrow', '02 — FEATURES' ); ?></span>
        <h2 class="ecm-section-title"><?php echo _ecm( 'ecm_feat_title', 'المميزات' ); ?></h2>
        <p class="ecm-section-desc"><?php echo _ecm( 'ecm_feat_desc', 'تقنيات متقدمة لتجربة تصوير احترافية بلا حدود' ); ?></p>

        <div class="ecm-feat-grid-3">
            <?php
            $feat_defaults = [
                1 => [ '📡', 'التحكم اللاسلكي', 'اتصال مستقر على مسافة تصل إلى 100 متر مع تأخير أقل من 10 مللي ثانية للتحكم الفوري.' ],
                2 => [ '🎥', 'بث مباشر', 'بث مباشر بدقة 4K مع مراقبة فورية وتحكم كامل في جميع إعدادات الكاميرا عن بُعد.' ],
                3 => [ '🎯', 'دقة عالية', 'محركات عالية الدقة مع نظام تثبيت متقدم يضمن حركة سلسة بدون أي اهتزاز.' ],
                4 => [ '🔋', 'بطارية طويلة', 'بطارية ليثيوم عالية الكفاءة تدوم حتى 8 ساعات متواصلة مع شحن سريع في 90 دقيقة.' ],
                5 => [ '📱', 'تطبيق ذكي', 'تطبيق متوافق مع iOS و Android للتحكم والمراقبة وبرمجة مسارات الكاميرا مسبقاً.' ],
                6 => [ '⚙️', 'برمجة المسارات', 'إمكانية برمجة مسارات حركة معقدة مسبقاً وتشغيلها بضغطة زر للقطات متكررة بدقة.' ],
            ];
            foreach ( $feat_defaults as $i => $def ) :
            ?>
            <div class="ecm-feat-card">
                <span class="ecm-feat-icon"><?php echo _ecm( "ecm_feat_{$i}_icon", $def[0] ); ?></span>
                <span class="ecm-feat-title"><?php echo _ecm( "ecm_feat_{$i}_title", $def[1] ); ?></span>
                <p class="ecm-feat-text"><?php echo _ecm( "ecm_feat_{$i}_text", $def[2] ); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════
     SPECS
     ═══════════════════════════════════════ -->
<section class="ecm-section" id="specs">
    <div class="ecm-container">
        <span class="ecm-eyebrow"><?php echo _ecm( 'ecm_spec_eyebrow', '03 — SPECIFICATIONS' ); ?></span>
        <h2 class="ecm-section-title"><?php echo _ecm( 'ecm_spec_title', 'المواصفات الفنية' ); ?></h2>
        <p class="ecm-section-desc"><?php echo _ecm( 'ecm_spec_desc', 'مواصفات تقنية احترافية لأفضل أداء' ); ?></p>

        <div class="ecm-specs-wrap">
            <?php
            $spec_defaults = [
                1 => [ 'الوزن', '450g', '' ],
                2 => [ 'الأبعاد', '280 × 85 × 45 mm', '' ],
                3 => [ 'نطاق التحكم', '100m', 'yes' ],
                4 => [ 'البطارية', 'Li-Po 5000mAh', '' ],
                5 => [ 'وقت الشحن', '90 دقيقة', '' ],
                6 => [ 'دقة الفيديو', '4K @ 120fps', 'yes' ],
                7 => [ 'درجة التشغيل', '-10°C ~ +50°C', '' ],
                8 => [ 'التوصيل', 'WiFi 6 + BLE 5.3', '' ],
                9 => [ 'التوافق', 'Canon / Sony / Nikon / RED', '' ],
            ];
            foreach ( $spec_defaults as $i => $def ) :
                $label = _ecm( "ecm_spec_{$i}_label", $def[0] );
                $val   = _ecm( "ecm_spec_{$i}_val",   $def[1] );
                $hl    = get_theme_mod( "ecm_spec_{$i}_hl", $def[2] );
                $class = ( $hl === 'yes' ) ? ' ecm-spec-hl' : '';
                if ( empty( $label ) && empty( $val ) ) continue;
            ?>
            <div class="ecm-spec-row<?php echo $class; ?>">
                <span class="ecm-spec-label"><?php echo $label; ?></span>
                <span class="ecm-spec-val"><?php echo $val; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════
     DOCUMENTATION
     ═══════════════════════════════════════ -->
<section class="ecm-section ecm-docs-section" id="docs">
    <div class="ecm-container">
        <span class="ecm-eyebrow"><?php echo _ecm( 'ecm_docs_eyebrow', '04 — DOCUMENTATION' ); ?></span>
        <h2 class="ecm-section-title"><?php echo _ecm( 'ecm_docs_title', 'التوثيق والأدلة' ); ?></h2>
        <p class="ecm-section-desc"><?php echo _ecm( 'ecm_docs_desc', 'كل ما تحتاجه لاستخدام وصيانة نظام ECM' ); ?></p>

        <div class="ecm-docs-grid">
            <?php
            $docs_defaults = [
                1 => [ '📘', 'دليل المستخدم', 'شرح شامل لكل وظائف النظام من البداية للاحتراف مع صور توضيحية.', 'PDF — 24 صفحة' ],
                2 => [ '🔧', 'دليل التركيب', 'خطوات تركيب وإعداد النظام على أي كاميرا مع إعدادات البلوتوث والواي فاي.', 'PDF — 12 صفحة' ],
                3 => [ '📱', 'دليل التطبيق', 'كيفية تحميل وإعداد تطبيق ECM على iOS و Android وربطه بالنظام.', 'PDF — 8 صفحات' ],
                4 => [ '⚡', 'الأسئلة الشائعة', 'إجابات لأكثر الأسئلة شيوعاً حول النظام والضمان والدعم الفني.', 'FAQ — محدّث' ],
                5 => [ '🎥', 'فيديو تعليمي', 'سلسلة فيديوهات تشرح كل خطوة من الإعداد للتشغيل في الميدان.', 'فيديو — 6 حلقات' ],
                6 => [ '🛡', 'الضمان والصيانة', 'شروط الضمان وجدول الصيانة الدورية وكيفية طلب قطع الغيار.', 'PDF — 4 صفحات' ],
            ];
            foreach ( $docs_defaults as $i => $def ) :
                $icon  = _ecm( "ecm_doc_{$i}_icon",  $def[0] );
                $title = _ecm( "ecm_doc_{$i}_title", $def[1] );
                $desc  = _ecm( "ecm_doc_{$i}_desc",  $def[2] );
                $meta  = _ecm( "ecm_doc_{$i}_meta",  $def[3] );
                $link  = get_theme_mod( "ecm_doc_{$i}_link", '' );
            ?>
            <a <?php if ( $link ) echo 'href="' . esc_url( $link ) . '" target="_blank"'; ?> class="ecm-doc-card">
                <div class="ecm-doc-icon"><?php echo $icon; ?></div>
                <div class="ecm-doc-body">
                    <h3 class="ecm-doc-title"><?php echo $title; ?></h3>
                    <p class="ecm-doc-desc"><?php echo $desc; ?></p>
                    <span class="ecm-doc-meta">
                        <span class="ecm-doc-badge"><?php echo $meta; ?></span>
                        <?php if ( $link ) : ?><span class="ecm-doc-dl">تحميل ↓</span><?php endif; ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════
     CTA SECTION
     ═══════════════════════════════════════ -->
<section class="ecm-cta-section" id="contact">
    <div class="ecm-cta-inner">
        <div class="ecm-cta-glow"></div>
        <span class="ecm-eyebrow"><?php echo _ecm( 'ecm_cta_eyebrow', 'READY?' ); ?></span>
        <h2 class="ecm-section-title" style="margin-bottom: var(--ecm-space-sm);">
            <?php echo _ecm( 'ecm_cta_title', 'جاهز تبدأ؟' ); ?>
        </h2>
        <p class="ecm-section-desc" style="margin-inline: auto; margin-bottom: var(--ecm-space-xl);">
            <?php echo _ecm( 'ecm_cta_desc', 'تواصل معنا الآن واحصل على نظام ECM لتحكم احترافي بالكاميرات' ); ?>
        </p>

        <div class="ecm-cta-btns">
            <?php
            $wa = get_theme_mod( 'ecm_whatsapp', '' );
            $email = get_theme_mod( 'ecm_email', 'info@ecameraman.com' );
            $btn_text = _ecm( 'ecm_cta_btn_text', 'تواصل معنا' );
            ?>
            <?php if ( $wa ) : ?>
                <a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" class="ecm-btn-primary ecm-btn-lg">
                    <span style="font-size:18px;">💬</span> <?php echo $btn_text; ?>
                </a>
            <?php endif; ?>
            <?php if ( $email ) : ?>
                <a href="mailto:<?php echo esc_attr( $email ); ?>" class="ecm-btn-ghost ecm-btn-lg">
                    <span style="font-size:18px;">✉️</span> أرسل إيميل
                </a>
            <?php endif; ?>
        </div>

        <!-- Social Links -->
        <?php
        $ig = get_theme_mod( 'ecm_instagram', '' );
        $yt = get_theme_mod( 'ecm_youtube', '' );
        if ( $ig || $yt || $wa ) :
        ?>
        <div class="ecm-cta-social">
            <?php if ( $wa ) : ?><a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank">WhatsApp</a><?php endif; ?>
            <?php if ( $ig ) : ?><a href="<?php echo esc_url( $ig ); ?>" target="_blank">Instagram</a><?php endif; ?>
            <?php if ( $yt ) : ?><a href="<?php echo esc_url( $yt ); ?>" target="_blank">YouTube</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

</main>

<?php get_footer(); ?>
