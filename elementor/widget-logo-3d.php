<?php
/**
 * ECM Elementor Widget: 3D Logo / Model (.glb)
 * يعرض موديل 3D في أي مكان بالسحب والإفلات — مع تحكم كامل في الحركة.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

class ECM_Widget_Logo_3D extends \Elementor\Widget_Base {

    public function get_name()       { return 'ecm_logo_3d'; }
    public function get_title()      { return __( 'ECM — لوجو/موديل 3D', 'ecm-theme' ); }
    public function get_icon()       { return 'eicon-cube'; }
    public function get_categories() { return [ 'ecm-elements' ]; }
    public function get_keywords()   { return [ '3d', 'glb', 'logo', 'model', 'ecm' ]; }

    protected function register_controls(): void {

        /* ── المحتوى ── */
        $this->start_controls_section( 'content_section', [
            'label' => __( 'الموديل 3D', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'glb_url', [
            'label'       => __( 'رابط ملف .glb', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'https://موقعك/wp-content/uploads/model.glb',
            'description' => __( 'ارفع الملف من «الوسائط» والصق رابطه هنا. سيبه فاضي عشان يستخدم لوجو الهيدر 3D.', 'ecm-theme' ),
            'label_block' => true,
            'dynamic'     => [ 'active' => true ],
        ] );

        $this->add_control( 'play_animation', [
            'label'        => __( 'تشغيل الأنيميشن المدمج في الملف', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
            'description'  => __( 'لو الـ glb فيه حركة جاهزة (Animation) هتشتغل تلقائيًا.', 'ecm-theme' ),
        ] );

        $this->end_controls_section();

        /* ── الحركة ── */
        $this->start_controls_section( 'motion_section', [
            'label' => __( 'الحركة', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'auto_rotate', [
            'label'        => __( 'دوران تلقائي', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'rotate_speed', [
            'label'     => __( 'سرعة الدوران (درجة/ثانية)', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 5, 'max' => 180, 'step' => 5 ] ],
            'default'   => [ 'size' => 30 ],
            'condition' => [ 'auto_rotate' => 'yes' ],
        ] );

        $this->add_control( 'camera_controls', [
            'label'        => __( 'تدوير باليد (سحب بالماوس)', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'zoom', [
            'label'        => __( 'تكبير/تصغير (زوم بالعجلة)', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'description'  => __( 'الزائر يقدر يقرّب ويبعّد ويحرّك الموديل.', 'ecm-theme' ),
        ] );

        $this->add_control( 'fullscreen', [
            'label'        => __( 'زر ملء الشاشة', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'effect', [
            'label'   => __( 'حركة إضافية', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'none',
            'options' => [
                'none'  => __( 'بدون', 'ecm-theme' ),
                'float' => __( 'تعويم (فوق وتحت)', 'ecm-theme' ),
                'pulse' => __( 'نبض', 'ecm-theme' ),
                'swing' => __( 'تأرجح', 'ecm-theme' ),
                'tilt'  => __( 'ميل عند المرور', 'ecm-theme' ),
            ],
        ] );

        $this->end_controls_section();

        /* ── التصميم ── */
        $this->start_controls_section( 'style_section', [
            'label' => __( 'المقاس والمحاذاة', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'height', [
            'label'      => __( 'الارتفاع', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 60, 'max' => 700 ] ],
            'default'    => [ 'size' => 240 ],
            'selectors'  => [ '{{WRAPPER}} model-viewer' => 'height: {{SIZE}}px;' ],
        ] );

        $this->add_responsive_control( 'width', [
            'label'      => __( 'أقصى عرض للحاوية', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [ 'px' => [ 'min' => 120, 'max' => 900 ], '%' => [ 'min' => 20, 'max' => 100 ] ],
            'default'    => [ 'size' => 100, 'unit' => '%' ],
            'selectors'  => [ '{{WRAPPER}} .ecm-3d-stage' => 'max-width: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'frame_zoom', [
            'label'       => __( 'حجم الموديل (ملء الإطار %)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::SLIDER,
            'range'       => [ 'px' => [ 'min' => 50, 'max' => 100, 'step' => 5 ] ],
            'default'     => [ 'size' => 90 ],
            'description' => __( 'أكبر = الموديل يملأ الإطار أكثر. 100% = أقصى حجم بدون قص (يفضل دايمًا متسنتر).', 'ecm-theme' ),
        ] );

        $this->add_responsive_control( 'align', [
            'label'     => __( 'المحاذاة', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::CHOOSE,
            'options'   => [
                'flex-start' => [ 'title' => __( 'يسار', 'ecm-theme' ), 'icon' => 'eicon-text-align-left' ],
                'center'     => [ 'title' => __( 'وسط', 'ecm-theme' ),  'icon' => 'eicon-text-align-center' ],
                'flex-end'   => [ 'title' => __( 'يمين', 'ecm-theme' ),  'icon' => 'eicon-text-align-right' ],
            ],
            'default'   => 'center',
            'selectors' => [ '{{WRAPPER}} .ecm-3d-wrap' => 'display:flex; justify-content: {{VALUE}};' ],
        ] );

        $this->end_controls_section();

        /* ── الحاوية (Stage) ── */
        $this->start_controls_section( 'stage_section', [
            'label' => __( 'الحاوية والإضاءة', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'studio_light', [
            'label'        => __( 'إضاءة استوديو', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'description'  => __( 'إضاءة وانعكاسات واقعية للموديل.', 'ecm-theme' ),
        ] );

        $this->add_control( 'stage_style', [
            'label'   => __( 'شكل الحاوية', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'card',
            'options' => [
                'plain' => __( 'بدون خلفية', 'ecm-theme' ),
                'card'  => __( 'كارت (خلفية + حدود)', 'ecm-theme' ),
                'glow'  => __( 'كارت + توهج أخضر', 'ecm-theme' ),
            ],
        ] );

        $this->add_control( 'stage_bg', [
            'label'     => __( 'لون الخلفية', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-3d-stage' => 'background: {{VALUE}};' ],
            'condition' => [ 'stage_style!' => 'plain' ],
        ] );

        $this->add_control( 'stage_radius', [
            'label'      => __( 'استدارة الحواف', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default'    => [ 'size' => 14 ],
            'selectors'  => [ '{{WRAPPER}} .ecm-3d-stage' => 'border-radius: {{SIZE}}px;' ],
        ] );

        $this->add_responsive_control( 'stage_padding', [
            'label'      => __( 'الحشو الداخلي', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
            'default'    => [ 'size' => 24 ],
            'selectors'  => [ '{{WRAPPER}} .ecm-3d-stage' => 'padding: {{SIZE}}px;' ],
        ] );

        $this->end_controls_section();

        /* ── الإضاءة (Lighting) ── */
        $this->start_controls_section( 'light_section', [
            'label' => __( '💡 الإضاءة والسطوع', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'exposure', [
            'label'       => __( 'السطوع / البرايتنس', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::SLIDER,
            'range'       => [ 'px' => [ 'min' => 0, 'max' => 2, 'step' => 0.05 ] ],
            'default'     => [ 'size' => 1 ],
            'description' => __( 'شدة الإضاءة العامة — أعلى = أفتح.', 'ecm-theme' ),
        ] );

        $this->add_control( 'shadow', [
            'label'   => __( 'شدة الظل', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SLIDER,
            'range'   => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ] ],
            'default' => [ 'size' => 0.4 ],
        ] );

        $this->add_control( 'shadow_softness', [
            'label'     => __( 'نعومة الظل', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ] ],
            'default'   => [ 'size' => 1 ],
            'condition' => [ 'shadow[size]!' => 0 ],
        ] );

        $this->add_control( 'tone_mapping', [
            'label'   => __( 'نمط الألوان', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'neutral',
            'options' => [
                ''         => __( 'تلقائي', 'ecm-theme' ),
                'neutral'  => __( 'محايد (طبيعي)', 'ecm-theme' ),
                'commerce' => __( 'تجاري (ألوان زاهية)', 'ecm-theme' ),
                'aces'     => __( 'سينمائي (ACES)', 'ecm-theme' ),
            ],
        ] );

        $this->end_controls_section();
    }

    /** "ملء الإطار %" (50–100) → radius% للكاميرا. مايقلّش عن 100% عشان الموديل مايتقصّش. */
    private function ecm_fill_to_frame( $s ): int {
        $fill = isset( $s['frame_zoom']['size'] ) ? (int) $s['frame_zoom']['size'] : 90;
        $fill = min( 100, max( 40, $fill ) );
        return (int) round( 10000 / $fill );
    }

    protected function render(): void {
        if ( ! function_exists( 'ecm_3d_model_markup' ) ) {
            return;
        }
        $s   = $this->get_settings_for_display();
        $src = trim( $s['glb_url'] );
        if ( '' === $src ) {
            $src = get_theme_mod( 'ecm_logo_glb', '' );
        }
        if ( '' === $src ) {
            echo '<div class="ecm-3d-wrap"><div class="ecm-3d-stage" style="opacity:.6;font-family:sans-serif;font-size:13px;padding:24px;text-align:center;">⬆️ حُط رابط ملف .glb في إعدادات الودجِت</div></div>';
            return;
        }

        $markup = ecm_3d_model_markup( [
            'src'             => $src,
            'auto_rotate'     => 'yes' === $s['auto_rotate'],
            'rotate_speed'    => isset( $s['rotate_speed']['size'] ) ? (int) $s['rotate_speed']['size'] : 30,
            'camera_controls' => 'yes' === $s['camera_controls'],
            'zoom'            => 'yes' === ( $s['zoom'] ?? 'yes' ),
            'fullscreen'      => 'yes' === ( $s['fullscreen'] ?? 'yes' ),
            'play_animation'  => 'yes' === $s['play_animation'],
            'effect'          => $s['effect'],
            'environment'     => ( 'yes' === ( $s['studio_light'] ?? 'yes' ) ) ? 'neutral' : 'none',
            'exposure'        => isset( $s['exposure']['size'] ) ? (float) $s['exposure']['size'] : 1.0,
            'shadow'          => isset( $s['shadow']['size'] ) ? (float) $s['shadow']['size'] : 0.4,
            'shadow_softness' => isset( $s['shadow_softness']['size'] ) ? (float) $s['shadow_softness']['size'] : 1.0,
            'tone_mapping'    => $s['tone_mapping'] ?? 'neutral',
            'frame_zoom'      => $this->ecm_fill_to_frame( $s ),
            'class'           => 'ecm-3d-model',
            'loading'          => 'lazy',
            'inline_size'     => false, // المقاس من تحكّمات Elementor (selectors)
        ] );

        $stage = 'ecm-3d-stage';
        $style = $s['stage_style'] ?? 'card';
        if ( 'card' === $style ) { $stage .= ' ecm-3d-stage--card'; }
        if ( 'glow' === $style ) { $stage .= ' ecm-3d-stage--card ecm-3d-stage--glow'; }

        echo '<div class="ecm-3d-wrap"><div class="' . esc_attr( $stage ) . '">' . $markup . '</div></div>';
    }
}
