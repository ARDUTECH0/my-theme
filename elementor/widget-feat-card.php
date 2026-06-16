<?php
/**
 * ECM Elementor Widget: Feature Card
 * كارت الميزة — تحكم لاسلكي / برمجة المسارات ...
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

class ECM_Widget_Feat_Card extends \Elementor\Widget_Base {

    public function get_name()       { return 'ecm_feat_card'; }
    public function get_title()      { return __( 'ECM — Feature Card', 'ecm-theme' ); }
    public function get_icon()       { return 'eicon-icon-box'; }
    public function get_categories() { return [ 'ecm-elements' ]; }
    public function get_keywords()   { return [ 'feature', 'card', 'icon', 'ecm' ]; }

    protected function register_controls(): void {

        /* ── CONTENT ── */
        $this->start_controls_section( 'content_section', [
            'label' => __( 'المحتوى', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'icon', [
            'label'       => __( 'الأيقونة (Emoji)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => '📡',
            'description' => __( 'مثل: 🎥 📡 🎚️ 🔭', 'ecm-theme' ),
        ] );

        $this->add_control( 'title', [
            'label'   => __( 'العنوان', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'تحكم لاسلكي',
        ] );

        $this->add_control( 'text', [
            'label'   => __( 'الوصف', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXTAREA,
            'default' => 'اتصال مستقر على مسافة تصل إلى 100 متر مع تأخير أقل من 10 مللي ثانية.',
            'rows'    => 4,
        ] );

        $this->add_control( 'image', [
            'label'       => __( 'صورة (اختياري — تحل محل الأيقونة)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::MEDIA,
            'description' => __( 'إذا اخترت صورة، ستختفي الأيقونة', 'ecm-theme' ),
        ] );

        $this->end_controls_section();

        /* ── STYLE ── */
        $this->start_controls_section( 'style_section', [
            'label' => __( 'التصميم', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'card_bg', [
            'label'     => __( 'لون الخلفية', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-feat-card' => 'background: {{VALUE}};' ],
        ] );

        $this->add_control( 'accent_color', [
            'label'     => __( 'لون الشريط العلوي', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-feat-card::before' => 'background: {{VALUE}};' ],
        ] );

        $this->add_control( 'title_color', [
            'label'     => __( 'لون العنوان', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-feat-title' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'text_color', [
            'label'     => __( 'لون الوصف', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-feat-text' => 'color: {{VALUE}};' ],
        ] );

        $this->add_responsive_control( 'padding', [
            'label'      => __( 'المساحة الداخلية', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [ '{{WRAPPER}} .ecm-feat-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();
        ?>
        <div class="ecm-feat-card">
            <?php if ( ! empty( $s['image']['url'] ) ) : ?>
                <img
                    src="<?php echo esc_url( $s['image']['url'] ); ?>"
                    alt="<?php echo esc_attr( $s['title'] ); ?>"
                    style="width:100%;border-radius:6px;margin-bottom:16px;"
                    loading="lazy"
                >
            <?php else : ?>
                <span class="ecm-feat-icon" aria-hidden="true"><?php echo esc_html( $s['icon'] ); ?></span>
            <?php endif; ?>

            <span class="ecm-feat-title"><?php echo esc_html( $s['title'] ); ?></span>
            <p class="ecm-feat-text"><?php echo esc_html( $s['text'] ); ?></p>
        </div>
        <?php
    }
}
