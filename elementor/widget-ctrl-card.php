<?php
/**
 * ECM Elementor Widget: Control Card
 * كارت التحكم — Slider / Crane / Tilt / Pan / Zoom / Focus
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

class ECM_Widget_Ctrl_Card extends \Elementor\Widget_Base {

    public function get_name()       { return 'ecm_ctrl_card'; }
    public function get_title()      { return __( 'ECM — Control Card', 'ecm-theme' ); }
    public function get_icon()       { return 'eicon-slider-device'; }
    public function get_categories() { return [ 'ecm-elements' ]; }
    public function get_keywords()   { return [ 'control', 'card', 'slider', 'camera', 'ecm' ]; }

    protected function register_controls(): void {

        /* ── CONTENT ── */
        $this->start_controls_section( 'content_section', [
            'label' => __( 'المحتوى', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'icon', [
            'label'       => __( 'الأيقونة (Emoji أو رمز)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => '↔',
            'description' => __( 'يمكن استخدام emoji مثل 🎥 أو رمز نصي مثل ↕', 'ecm-theme' ),
        ] );

        $this->add_control( 'title', [
            'label'   => __( 'اسم وحدة التحكم', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'SLIDER',
        ] );

        $this->add_control( 'speed_label', [
            'label'   => __( 'تسمية السرعة', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'SPEED',
        ] );

        $this->add_control( 'speed_val', [
            'label'   => __( 'قيمة السرعة', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 9,
            'min'     => 0,
            'max'     => 100,
        ] );

        $this->add_control( 'bar_pct', [
            'label'   => __( 'نسبة الشريط (%)', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SLIDER,
            'default' => [ 'size' => 52 ],
            'range'   => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
        ] );

        $this->add_control( 'current_label', [
            'label'   => __( 'تسمية "الحالي"', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'Current',
        ] );

        $this->add_control( 'current_val', [
            'label'   => __( 'القيمة الحالية', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 52,
        ] );

        $this->add_control( 'target_label', [
            'label'   => __( 'تسمية "الهدف"', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'Target',
        ] );

        $this->add_control( 'target_val', [
            'label'   => __( 'القيمة المستهدفة', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 105,
        ] );

        $this->end_controls_section();

        /* ── STYLE ── */
        $this->start_controls_section( 'style_section', [
            'label' => __( 'التصميم', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'card_bg', [
            'label'     => __( 'لون خلفية الكارت', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-ctrl-card' => 'background: {{VALUE}};' ],
        ] );

        $this->add_control( 'accent_color', [
            'label'     => __( 'لون الـ Accent (القيم والشريط)', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ecm-ctrl-val'          => 'color: {{VALUE}};',
                '{{WRAPPER}} .ecm-ctrl-bar-fill'     => 'background: {{VALUE}};',
                '{{WRAPPER}} .ecm-ctrl-footer span > span' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'title_color', [
            'label'     => __( 'لون العنوان', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-ctrl-title' => 'color: {{VALUE}};' ],
        ] );

        $this->end_controls_section();
    }

    protected function render(): void {
        $s   = $this->get_settings_for_display();
        $pct = isset( $s['bar_pct']['size'] ) ? (int) $s['bar_pct']['size'] : 52;
        ?>
        <div class="ecm-ctrl-card">
            <div class="ecm-ctrl-icon"><?php echo esc_html( $s['icon'] ); ?></div>
            <div class="ecm-ctrl-title"><?php echo esc_html( $s['title'] ); ?></div>

            <span class="ecm-ctrl-speed-label"><?php echo esc_html( $s['speed_label'] ); ?></span>
            <div class="ecm-ctrl-val"><?php echo esc_html( $s['speed_val'] ); ?></div>

            <div class="ecm-ctrl-bar">
                <div class="ecm-ctrl-bar-fill" style="width:<?php echo $pct; ?>%"></div>
            </div>

            <div class="ecm-ctrl-footer">
                <span><?php echo esc_html( $s['current_label'] ); ?>: <span><?php echo esc_html( $s['current_val'] ); ?></span></span>
                <span><?php echo esc_html( $s['target_label'] ); ?>: <span><?php echo esc_html( $s['target_val'] ); ?></span></span>
            </div>
        </div>
        <?php
    }
}
