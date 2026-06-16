<?php
/**
 * ECM Elementor Widget: Eyebrow Label
 * العنوان الفرعي الصغير — SYSTEM ACTIVE / 01 CAMERA CONTROLS
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

class ECM_Widget_Eyebrow extends \Elementor\Widget_Base {

    public function get_name()       { return 'ecm_eyebrow'; }
    public function get_title()      { return __( 'ECM — Eyebrow Label', 'ecm-theme' ); }
    public function get_icon()       { return 'eicon-heading'; }
    public function get_categories() { return [ 'ecm-elements' ]; }
    public function get_keywords()   { return [ 'eyebrow', 'label', 'sub', 'tagline', 'ecm' ]; }

    protected function register_controls(): void {

        /* ── CONTENT ── */
        $this->start_controls_section( 'content_section', [
            'label' => __( 'المحتوى', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'text', [
            'label'       => __( 'النص', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => 'SYSTEM ACTIVE',
            'description' => __( 'مثال: 01 — CAMERA CONTROLS أو SYSTEM ACTIVE', 'ecm-theme' ),
        ] );

        $this->add_control( 'show_dot', [
            'label'        => __( 'إظهار نقطة النبض', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'نعم', 'ecm-theme' ),
            'label_off'    => __( 'لا', 'ecm-theme' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'html_tag', [
            'label'   => __( 'وسم HTML', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'span',
            'options' => [
                'span' => 'span',
                'p'    => 'p',
                'div'  => 'div',
            ],
        ] );

        $this->end_controls_section();

        /* ── STYLE ── */
        $this->start_controls_section( 'style_section', [
            'label' => __( 'التصميم', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'color', [
            'label'     => __( 'لون النص', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#9cff00',
            'selectors' => [ '{{WRAPPER}} .ecm-eyebrow' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'spacing', [
            'label'      => __( 'تباعد الأحرف', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'default'    => [ 'size' => 5 ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 20 ] ],
            'selectors'  => [ '{{WRAPPER}} .ecm-eyebrow' => 'letter-spacing: {{SIZE}}px;' ],
        ] );

        $this->add_control( 'font_size', [
            'label'      => __( 'حجم الخط', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'default'    => [ 'size' => 11 ],
            'range'      => [ 'px' => [ 'min' => 8, 'max' => 18 ] ],
            'selectors'  => [ '{{WRAPPER}} .ecm-eyebrow' => 'font-size: {{SIZE}}px;' ],
        ] );

        $this->add_responsive_control( 'margin_bottom', [
            'label'      => __( 'المسافة السفلية', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'default'    => [ 'size' => 16 ],
            'selectors'  => [ '{{WRAPPER}} .ecm-eyebrow' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();
    }

    protected function render(): void {
        $s   = $this->get_settings_for_display();
        $tag = in_array( $s['html_tag'], [ 'span', 'p', 'div' ], true ) ? $s['html_tag'] : 'span';
        $dot = 'yes' === $s['show_dot'] ? '' : ' style="--ecm-eyebrow-dot: none"';

        // Suppress dot via inline style if disabled
        $no_dot_style = 'yes' !== $s['show_dot']
            ? ' style="display:inline-flex; align-items:center; gap:8px;"'
            : '';
        ?>
        <<?php echo $tag; ?> class="ecm-eyebrow"<?php echo $no_dot_style; ?>>
            <?php if ( 'yes' !== $s['show_dot'] ) : ?>
                <style>.elementor-element-<?php echo $this->get_id(); ?> .ecm-eyebrow::before { display: none; }</style>
            <?php endif; ?>
            <?php echo esc_html( $s['text'] ); ?>
        </<?php echo $tag; ?>>
        <?php
    }
}
