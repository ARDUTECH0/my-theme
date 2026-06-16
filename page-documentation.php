<?php
/**
 * ECM Theme — page-documentation.php
 * Template Name: 📖 صفحة التوثيق (BetterDocs)
 *
 * متوافقة مع بلاجن BetterDocs — لو مش مثبّت يعرض محتوى بديل
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

$is_betterdocs = class_exists( 'WPDeveloper\BetterDocs\Plugin' ) || defined( 'JEsuspended_BETTERDOCS_VERSION' ) || post_type_exists( 'docs' );
?>

<main id="ecm-main" class="ecm-main-content" role="main">

    <!-- ═══════════════════════════════════════
         DOCS HERO
         ═══════════════════════════════════════ -->
    <section class="ecm-app-hero">
        <div class="ecm-container" style="padding-block: 120px 60px; text-align: center;">
            <span class="ecm-eyebrow">DOCUMENTATION</span>
            <h1 class="ecm-hero-title" style="font-size: clamp(28px, 5vw, 48px);">
                <span class="ecm-green"><?php echo esc_html( get_theme_mod( 'ecm_docspage_title', 'التوثيق والأدلة' ) ); ?></span>
            </h1>
            <p class="ecm-hero-desc" style="max-width: 560px; margin: 16px auto 0;">
                <?php echo esc_html( get_theme_mod( 'ecm_docspage_desc', 'كل ما تحتاجه لاستخدام نظام ECM — أدلة شاملة، شروحات مصورة، وأسئلة شائعة' ) ); ?>
            </p>

            <?php if ( $is_betterdocs ) : ?>
                <!-- BetterDocs Search -->
                <div class="ecm-docs-search-wrap">
                    <?php echo do_shortcode( '[betterdocs_search_form]' ); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>


    <?php if ( $is_betterdocs ) : ?>
    <!-- ═══════════════════════════════════════
         BETTERDOCS CATEGORIES
         ═══════════════════════════════════════ -->
    <section class="ecm-section ecm-betterdocs-section">
        <div class="ecm-container">
            <div class="ecm-betterdocs-grid">
                <?php echo do_shortcode( '[betterdocs_category_grid title_tag="h3" posts_per_page="-1"]' ); ?>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
         BETTERDOCS CATEGORY BOX (Alternative)
         ═══════════════════════════════════════ -->
    <section class="ecm-section">
        <div class="ecm-container">
            <span class="ecm-eyebrow">BROWSE DOCS</span>
            <h2 class="ecm-section-title">تصفح حسب التصنيف</h2>
            <p class="ecm-section-desc">اختر التصنيف اللي يناسبك للوصول للمقالات</p>

            <div class="ecm-betterdocs-boxes">
                <?php echo do_shortcode( '[betterdocs_category_box title_tag="h3"]' ); ?>
            </div>
        </div>
    </section>

    <?php else : ?>
    <!-- ═══════════════════════════════════════
         FALLBACK: لو BetterDocs مش مثبّت
         ═══════════════════════════════════════ -->
    <section class="ecm-section">
        <div class="ecm-container">
            <div class="ecm-docs-install-notice">
                <div class="ecm-doc-icon" style="font-size: 48px; width: 80px; height: 80px; margin: 0 auto 24px;">📖</div>
                <h2 class="ecm-section-title" style="margin-bottom: 16px;">ثبّت BetterDocs</h2>
                <p style="color: var(--ecm-grey-mid); font-size: 15px; line-height: 1.7; max-width: 500px; margin: 0 auto 32px;">
                    هذه الصفحة تعمل مع بلاجن
                    <strong style="color: var(--ecm-green);">BetterDocs</strong>
                    لعرض التوثيق بشكل احترافي.<br>
                    ثبّته من <strong>الإضافات > أضف جديد</strong> وابحث عن "BetterDocs".
                </p>
                <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=betterdocs&tab=search&type=term' ) ); ?>" class="ecm-btn-primary ecm-btn-lg">
                    ⬇️ تثبيت BetterDocs
                </a>
            </div>

            <!-- Manual docs fallback -->
            <div style="margin-top: 64px;">
                <span class="ecm-eyebrow">MANUAL DOCS</span>
                <h2 class="ecm-section-title" style="margin-bottom: 32px;">الأدلة المتوفرة</h2>

                <div class="ecm-docs-grid">
                    <?php
                    $docs_defaults = [
                        1 => [ '📘', 'دليل المستخدم', 'شرح شامل لكل وظائف النظام من البداية للاحتراف.', 'PDF — 24 صفحة' ],
                        2 => [ '🔧', 'دليل التركيب', 'خطوات تركيب وإعداد النظام على أي كاميرا.', 'PDF — 12 صفحة' ],
                        3 => [ '📱', 'دليل التطبيق', 'كيفية تحميل وإعداد تطبيق ECM.', 'PDF — 8 صفحات' ],
                        4 => [ '⚡', 'الأسئلة الشائعة', 'إجابات لأكثر الأسئلة شيوعاً.', 'FAQ — محدّث' ],
                        5 => [ '🎥', 'فيديو تعليمي', 'سلسلة فيديوهات تعليمية.', 'فيديو — 6 حلقات' ],
                        6 => [ '🛡', 'الضمان والصيانة', 'شروط الضمان وجدول الصيانة.', 'PDF — 4 صفحات' ],
                    ];
                    foreach ( $docs_defaults as $i => $def ) :
                        $icon  = esc_html( get_theme_mod( "ecm_doc_{$i}_icon",  $def[0] ) );
                        $title = esc_html( get_theme_mod( "ecm_doc_{$i}_title", $def[1] ) );
                        $desc  = esc_html( get_theme_mod( "ecm_doc_{$i}_desc",  $def[2] ) );
                        $meta  = esc_html( get_theme_mod( "ecm_doc_{$i}_meta",  $def[3] ) );
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
        </div>
    </section>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════
         VIDEO TUTORIALS
         ═══════════════════════════════════════ -->
    <?php
    // تحقق لو في فيديوهات مضافة
    $has_videos = false;
    for ( $v = 1; $v <= 6; $v++ ) {
        if ( get_theme_mod( "ecm_vid_{$v}_url", '' ) ) { $has_videos = true; break; }
    }

    $vid_defaults = [
        1 => [ 'مقدمة عن نظام ECM', '' ],
        2 => [ 'طريقة التركيب والإعداد', '' ],
        3 => [ 'التحكم بالسلايدر', '' ],
        4 => [ 'برمجة مسارات الكاميرا', '' ],
        5 => [ 'ربط التطبيق بالنظام', '' ],
        6 => [ 'نصائح للتصوير الاحترافي', '' ],
    ];

    if ( $has_videos ) :
    ?>
    <section class="ecm-section ecm-videos-section" id="videos">
        <div class="ecm-container">
            <span class="ecm-eyebrow">🎥 VIDEO TUTORIALS</span>
            <h2 class="ecm-section-title"><?php echo esc_html( get_theme_mod( 'ecm_vid_section_title', 'فيديوهات تعليمية' ) ); ?></h2>
            <p class="ecm-section-desc"><?php echo esc_html( get_theme_mod( 'ecm_vid_section_desc', 'شروحات مصورة خطوة بخطوة لاحتراف استخدام نظام ECM' ) ); ?></p>

            <div class="ecm-video-grid">
                <?php
                for ( $v = 1; $v <= 6; $v++ ) :
                    $url   = get_theme_mod( "ecm_vid_{$v}_url", '' );
                    $title = esc_html( get_theme_mod( "ecm_vid_{$v}_title", $vid_defaults[$v][0] ) );
                    if ( ! $url ) continue;

                    // Extract YouTube ID
                    $vid_id = '';
                    if ( preg_match( '/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
                        $vid_id = $m[1];
                    }
                    if ( ! $vid_id ) continue;
                ?>
                <div class="ecm-video-card">
                    <div class="ecm-video-wrap">
                        <div class="ecm-video-thumb" data-vid="<?php echo esc_attr( $vid_id ); ?>">
                            <img src="https://img.youtube.com/vi/<?php echo esc_attr( $vid_id ); ?>/maxresdefault.jpg"
                                 alt="<?php echo $title; ?>"
                                 loading="lazy"
                                 onerror="this.src='https://img.youtube.com/vi/<?php echo esc_attr( $vid_id ); ?>/hqdefault.jpg'">
                            <div class="ecm-video-play">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="24" fill="rgba(0,0,0,0.6)"/><path d="M19 15L35 24L19 33V15Z" fill="#9cff00"/></svg>
                            </div>
                        </div>
                    </div>
                    <h3 class="ecm-video-title"><?php echo $title; ?></h3>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════
         NEED HELP CTA
         ═══════════════════════════════════════ -->
    <section class="ecm-cta-section">
        <div class="ecm-cta-inner">
            <div class="ecm-cta-glow"></div>
            <span class="ecm-eyebrow">NEED HELP?</span>
            <h2 class="ecm-section-title" style="margin-bottom: var(--ecm-space-md);">محتاج مساعدة؟</h2>
            <p class="ecm-section-desc" style="margin-inline: auto; margin-bottom: var(--ecm-space-xl);">
                لو ما لقيتش اللي بتدور عليه، تواصل مع فريق الدعم الفني
            </p>
            <div class="ecm-cta-btns">
                <?php $wa = get_theme_mod( 'ecm_whatsapp', '' ); if ( $wa ) : ?>
                    <a href="https://wa.me/<?php echo esc_attr( $wa ); ?>" target="_blank" class="ecm-btn-primary ecm-btn-lg">💬 واتساب الدعم</a>
                <?php endif; ?>
                <a href="<?php echo esc_url( home_url('/') ); ?>" class="ecm-btn-ghost ecm-btn-lg">← الرئيسية</a>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════
         POST CONTENT — محتوى إضافي قابل للتعديل من المحرّر
         يظهر فقط لو فيه محتوى مكتوب — ويُحرّر بالكامل عبر Elementor
         ═══════════════════════════════════════ -->
    <?php while ( have_posts() ) : the_post(); if ( trim( get_the_content() ) !== '' ) : ?>
        <section class="ecm-section">
            <div class="ecm-container ecm-entry-content" style="font-size: var(--ecm-text-base); line-height: 1.85; color: var(--ecm-grey-light);">
                <?php the_content(); ?>
            </div>
        </section>
    <?php endif; endwhile; ?>

</main>

<?php get_footer(); ?>
