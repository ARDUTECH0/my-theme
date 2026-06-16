<?php
/**
 * ECM Elementor Widget: 3D Slider
 * سلايدر تحطه في أي صفحة، كل شريحة فيها موديل 3D (.glb) أو صورة + نص + زر،
 * مع تحكم كامل في المقاسات والحركة.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

class ECM_Widget_Slider_3D extends \Elementor\Widget_Base {

    public function get_name()        { return 'ecm_slider_3d'; }
    public function get_title()       { return __( 'ECM — سلايدر 3D', 'ecm-theme' ); }
    public function get_icon()        { return 'eicon-slideshow'; }
    public function get_categories()  { return [ 'ecm-elements' ]; }
    public function get_keywords()    { return [ 'slider', '3d', 'glb', 'carousel', 'سلايدر', 'ecm' ]; }
    public function get_script_depends() { return [ 'ecm-slider-js' ]; }

    protected function register_controls(): void {

        /* ── الشرائح ── */
        $this->start_controls_section( 'slides_section', [
            'label' => __( '🎞️ الشرائح', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $rep = new \Elementor\Repeater();
        $rep->add_control( 'glb_url', [
            'label'       => __( 'ملف 3D (.glb)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label_block' => true,
            'placeholder' => 'https://موقعك/uploads/model.glb',
            'description' => __( 'سيبه فاضي لو هتستخدم صورة.', 'ecm-theme' ),
        ] );
        $rep->add_control( 'image', [
            'label' => __( 'صورة بديلة', 'ecm-theme' ),
            'type'  => \Elementor\Controls_Manager::MEDIA,
        ] );
        $rep->add_control( 'eyebrow', [
            'label'   => __( 'عنوان صغير', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'ECM',
        ] );
        $rep->add_control( 'title', [
            'label'       => __( 'العنوان', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label_block' => true,
            'default'     => 'عنوان الشريحة',
        ] );
        $rep->add_control( 'subtitle', [
            'label' => __( 'الوصف', 'ecm-theme' ),
            'type'  => \Elementor\Controls_Manager::TEXTAREA,
            'default' => 'اكتب وصف الشريحة هنا.',
        ] );
        $rep->add_control( 'btn_text', [
            'label' => __( 'نص الزر', 'ecm-theme' ),
            'type'  => \Elementor\Controls_Manager::TEXT,
        ] );
        $rep->add_control( 'btn_link', [
            'label'       => __( 'رابط الزر', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::URL,
            'placeholder' => 'https://',
        ] );

        $this->add_control( 'slides', [
            'label'       => __( 'الشرائح', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $rep->get_controls(),
            'default'     => [
                [ 'title' => 'الشريحة الأولى', 'eyebrow' => 'ECM', 'subtitle' => 'اكتب وصف هنا' ],
                [ 'title' => 'الشريحة الثانية', 'eyebrow' => 'ECM', 'subtitle' => 'اكتب وصف هنا' ],
            ],
            'title_field' => '{{{ title }}}',
        ] );

        $this->end_controls_section();

        /* ── المقاسات والإعدادات ── */
        $this->start_controls_section( 'settings_section', [
            'label' => __( '📐 المقاسات والإعدادات', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_responsive_control( 'height', [
            'label'      => __( 'ارتفاع السلايدر', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'vh' ],
            'range'      => [ 'px' => [ 'min' => 280, 'max' => 900 ], 'vh' => [ 'min' => 40, 'max' => 100 ] ],
            'default'    => [ 'size' => 520, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .ecm-slider-3d' => '--ecm-slider-h: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'autoplay', [
            'label'        => __( 'تشغيل تلقائي', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );
        $this->add_control( 'autoplay_delay', [
            'label'     => __( 'مدة الشريحة (ثانية)', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::NUMBER,
            'default'   => 6,
            'min'       => 2,
            'max'       => 20,
            'condition' => [ 'autoplay' => 'yes' ],
        ] );
        $this->add_control( 'arrows', [
            'label'        => __( 'أسهم التنقّل', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );
        $this->add_control( 'dots', [
            'label'        => __( 'نقط التنقّل', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->end_controls_section();

        /* ── الموديل 3D ── */
        $this->start_controls_section( 'model_section', [
            'label' => __( '🧊 الموديل 3D', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'auto_rotate', [
            'label'        => __( 'دوران تلقائي', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );
        $this->add_control( 'zoom', [
            'label'        => __( 'زوم بالماوس', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
        ] );
        $this->add_control( 'model_size', [
            'label'   => __( 'حجم الموديل داخل الإطار', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SLIDER,
            'range'   => [ 'px' => [ 'min' => 40, 'max' => 160, 'step' => 5 ] ],
            'default' => [ 'size' => 100 ],
        ] );
        $this->add_control( 'media_side', [
            'label'   => __( 'مكان الموديل', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'start',
            'options' => [
                'start' => __( 'جنب (مع نص)', 'ecm-theme' ),
                'bg'    => __( 'خلفية كاملة (نص فوقها)', 'ecm-theme' ),
            ],
        ] );

        $this->end_controls_section();
    }

    protected function render(): void {
        $s      = $this->get_settings_for_display();
        $slides = $s['slides'] ?? [];
        if ( empty( $slides ) ) {
            return;
        }

        $autoplay = ( 'yes' === $s['autoplay'] ) ? max( 2, (int) ( $s['autoplay_delay'] ?? 6 ) ) * 1000 : 0;
        $bg_mode  = ( 'bg' === ( $s['media_side'] ?? 'start' ) );
        $wrap_cls = 'ecm-slider-3d' . ( $bg_mode ? ' ecm-slider-3d--bg' : '' );

        echo '<div class="' . esc_attr( $wrap_cls ) . '" data-autoplay="' . esc_attr( $autoplay ) . '">';
        echo '<div class="ecm-slider-3d__viewport">';

        foreach ( $slides as $i => $slide ) {
            $active = 0 === $i ? ' is-active' : '';
            echo '<div class="ecm-slider-3d__slide' . $active . '">';

            // ── الميديا (موديل 3D أو صورة) ──
            echo '<div class="ecm-slider-3d__media">';
            $glb = isset( $slide['glb_url'] ) ? trim( $slide['glb_url'] ) : '';
            if ( '' !== $glb && function_exists( 'ecm_3d_model_markup' ) ) {
                echo ecm_3d_model_markup( [
                    'src'             => $glb,
                    'auto_rotate'     => 'yes' === $s['auto_rotate'],
                    'zoom'            => 'yes' === ( $s['zoom'] ?? '' ),
                    'camera_controls' => true,
                    'frame_zoom'      => isset( $s['model_size']['size'] ) ? (int) $s['model_size']['size'] : 100,
                    'class'           => 'ecm-3d-model',
                    'inline_size'     => false,
                    'loading'         => 'lazy',
                ] );
            } elseif ( ! empty( $slide['image']['url'] ) ) {
                echo '<img src="' . esc_url( $slide['image']['url'] ) . '" alt="' . esc_attr( $slide['title'] ?? '' ) . '" loading="lazy">';
            }
            echo '</div>';

            // ── المحتوى ──
            echo '<div class="ecm-slider-3d__content">';
            if ( ! empty( $slide['eyebrow'] ) ) {
                echo '<span class="ecm-eyebrow">' . esc_html( $slide['eyebrow'] ) . '</span>';
            }
            if ( ! empty( $slide['title'] ) ) {
                echo '<h2 class="ecm-slider-3d__title">' . esc_html( $slide['title'] ) . '</h2>';
            }
            if ( ! empty( $slide['subtitle'] ) ) {
                echo '<p class="ecm-slider-3d__sub">' . esc_html( $slide['subtitle'] ) . '</p>';
            }
            if ( ! empty( $slide['btn_text'] ) ) {
                $url = ! empty( $slide['btn_link']['url'] ) ? $slide['btn_link']['url'] : '#';
                $tgt = ! empty( $slide['btn_link']['is_external'] ) ? ' target="_blank" rel="noopener"' : '';
                echo '<a class="ecm-btn-primary ecm-slider-3d__btn" href="' . esc_url( $url ) . '"' . $tgt . '>' . esc_html( $slide['btn_text'] ) . '</a>';
            }
            echo '</div>';

            echo '</div>'; // slide
        }

        echo '</div>'; // viewport

        if ( 'yes' === $s['arrows'] && count( $slides ) > 1 ) {
            echo '<button type="button" class="ecm-slider-3d__nav ecm-slider-3d__prev" aria-label="' . esc_attr__( 'السابق', 'ecm-theme' ) . '">‹</button>';
            echo '<button type="button" class="ecm-slider-3d__nav ecm-slider-3d__next" aria-label="' . esc_attr__( 'التالي', 'ecm-theme' ) . '">›</button>';
        }

        if ( 'yes' === $s['dots'] && count( $slides ) > 1 ) {
            echo '<div class="ecm-slider-3d__dots">';
            foreach ( $slides as $i => $slide ) {
                echo '<button type="button" class="ecm-slider-3d__dot' . ( 0 === $i ? ' is-active' : '' ) . '" data-i="' . (int) $i . '" aria-label="' . esc_attr( sprintf( __( 'شريحة %d', 'ecm-theme' ), $i + 1 ) ) . '"></button>';
            }
            echo '</div>';
        }

        echo '</div>'; // slider
    }
}
