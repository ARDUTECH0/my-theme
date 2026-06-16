<?php
/**
 * ECM Elementor Widget: Spec Row
 * سطر المواصفة — Weight / Dimensions / Operating Temp
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

class ECM_Widget_Spec_Row extends \Elementor\Widget_Base {

    public function get_name()       { return 'ecm_spec_row'; }
    public function get_title()      { return __( 'ECM — Spec Row', 'ecm-theme' ); }
    public function get_icon()       { return 'eicon-bullet-list'; }
    public function get_categories() { return [ 'ecm-elements' ]; }
    public function get_keywords()   { return [ 'spec', 'row', 'specification', 'table', 'ecm' ]; }

    protected function register_controls(): void {

        /* ── CONTENT ── */
        $this->start_controls_section( 'content_section', [
            'label' => __( 'المحتوى', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'label', [
            'label'       => __( 'اسم المواصفة', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => 'WEIGHT',
            'description' => __( 'مثال: الوزن، الأبعاد، درجة التشغيل', 'ecm-theme' ),
        ] );

        $this->add_control( 'val', [
            'label'       => __( 'قيمة المواصفة', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => '450g',
            'description' => __( 'مثال: 450g، 120fps، -20°C ~ +60°C', 'ecm-theme' ),
        ] );

        $this->add_control( 'highlight', [
            'label'        => __( 'تمييز هذا السطر', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'نعم', 'ecm-theme' ),
            'label_off'    => __( 'لا', 'ecm-theme' ),
            'return_value' => 'yes',
            'default'      => 'no',
            'description'  => __( 'يضيف توهج خفيف لتمييز السطر المهم', 'ecm-theme' ),
        ] );

        $this->end_controls_section();

        /* ── STYLE ── */
        $this->start_controls_section( 'style_section', [
            'label' => __( 'التصميم', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'label_color', [
            'label'     => __( 'لون اسم المواصفة', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-spec-label' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'val_color', [
            'label'     => __( 'لون القيمة', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-spec-val' => 'color: {{VALUE}};' ],
        ] );

        $this->end_controls_section();
    }

    protected function render(): void {
        $s      = $this->get_settings_for_display();
        $is_hl  = 'yes' === $s['highlight'];
        $hl_style = $is_hl ? 'background: var(--ecm-green-subtle); padding-inline: 8px; border-color: rgba(156,255,0,0.2);' : '';
        ?>
        <div class="ecm-spec-row" style="<?php echo esc_attr( $hl_style ); ?>">
            <span class="ecm-spec-label"><?php echo esc_html( $s['label'] ); ?></span>
            <span class="ecm-spec-val"><?php echo esc_html( $s['val'] ); ?></span>
        </div>
        <?php
    }
}
