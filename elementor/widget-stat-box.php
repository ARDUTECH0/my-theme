<?php
/**
 * ECM Elementor Widget: Stat Box
 * صندوق الإحصائيات — 120 FPS / 4K Resolution / 100m Range
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

class ECM_Widget_Stat_Box extends \Elementor\Widget_Base {

    public function get_name()       { return 'ecm_stat_box'; }
    public function get_title()      { return __( 'ECM — Stat Box', 'ecm-theme' ); }
    public function get_icon()       { return 'eicon-counter-block'; }
    public function get_categories() { return [ 'ecm-elements' ]; }
    public function get_keywords()   { return [ 'stat', 'box', 'number', 'counter', 'ecm' ]; }

    protected function register_controls(): void {

        /* ── CONTENT ── */
        $this->start_controls_section( 'content_section', [
            'label' => __( 'المحتوى', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'number', [
            'label'       => __( 'الرقم / القيمة', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => '120',
            'description' => __( 'مثال: 4K أو 120 أو 100m', 'ecm-theme' ),
        ] );

        $this->add_control( 'suffix', [
            'label'       => __( 'اللاحقة (اختياري)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => '',
            'description' => __( 'مثال: + أو %', 'ecm-theme' ),
            'placeholder' => '+',
        ] );

        $this->add_control( 'label', [
            'label'   => __( 'التسمية / العنوان الفرعي', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'FPS RATE',
        ] );

        $this->end_controls_section();

        /* ── STYLE ── */
        $this->start_controls_section( 'style_section', [
            'label' => __( 'التصميم', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'num_color', [
            'label'     => __( 'لون الرقم', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#9cff00',
            'selectors' => [ '{{WRAPPER}} .ecm-stat-num' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'num_glow', [
            'label'        => __( 'توهج الرقم', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'مفعّل', 'ecm-theme' ),
            'label_off'    => __( 'مُعطَّل', 'ecm-theme' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'label_color', [
            'label'     => __( 'لون التسمية', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#7a7f90',
            'selectors' => [ '{{WRAPPER}} .ecm-stat-label' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'num_size', [
            'label'      => __( 'حجم الرقم', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 20, 'max' => 80, 'step' => 2 ] ],
            'default'    => [ 'size' => 38, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .ecm-stat-num' => 'font-size: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();
    }

    protected function render(): void {
        $s    = $this->get_settings_for_display();
        $glow = 'yes' === $s['num_glow']
            ? 'text-shadow: 0 0 16px var(--ecm-green-glow);'
            : '';
        ?>
        <div class="ecm-stat-box">
            <span class="ecm-stat-num" style="<?php echo esc_attr( $glow ); ?>">
                <?php echo esc_html( $s['number'] ); ?>
                <?php if ( ! empty( $s['suffix'] ) ) : ?>
                    <sup style="font-size:0.55em; vertical-align: super;"><?php echo esc_html( $s['suffix'] ); ?></sup>
                <?php endif; ?>
            </span>
            <span class="ecm-stat-label"><?php echo esc_html( $s['label'] ); ?></span>
        </div>
        <?php
    }
}
