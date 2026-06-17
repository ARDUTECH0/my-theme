<?php
/**
 * ECM Elementor Widget: 3D Control / Movement Grid
 * شبكة تشرح حركات الموديل 3D — كل خلية: موديل + سهم اتجاه + عنوان + وصف.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

class ECM_Widget_Control_Grid extends \Elementor\Widget_Base {

    public function get_name()       { return 'ecm_control_grid'; }
    public function get_title()      { return __( 'ECM — شبكة تحكّم 3D', 'ecm-theme' ); }
    public function get_icon()       { return 'eicon-gallery-grid'; }
    public function get_categories() { return [ 'ecm-elements' ]; }
    public function get_keywords()   { return [ '3d', 'glb', 'grid', 'control', 'arrows', 'ecm' ]; }

    /** أنواع الأسهم المتاحة لكل خلية */
    private function arrow_options(): array {
        return [
            'up'         => __( '⬆️ فوق', 'ecm-theme' ),
            'down'       => __( '⬇️ تحت', 'ecm-theme' ),
            'left'       => __( '⬅️ شمال', 'ecm-theme' ),
            'right'      => __( '➡️ يمين', 'ecm-theme' ),
            'up-down'    => __( '↕️ فوق وتحت', 'ecm-theme' ),
            'left-right' => __( '↔️ يمين وشمال', 'ecm-theme' ),
            'rotate-cw'  => __( '↻ تدوير يمين', 'ecm-theme' ),
            'rotate-ccw' => __( '↺ تدوير شمال', 'ecm-theme' ),
            'none'       => __( 'بدون سهم', 'ecm-theme' ),
        ];
    }

    protected function register_controls(): void {

        /* ── الخلايا ── */
        $this->start_controls_section( 'cells_section', [
            'label' => __( '🎛️ الخلايا', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'shared_glb', [
            'label'       => __( 'ملف 3D مشترك (.glb)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'https://موقعك/wp-content/uploads/camera.glb',
            'description' => __( 'الملف ده هيتستخدم في كل الخلايا. تقدر تغيّره لخلية معيّنة من جواها.', 'ecm-theme' ),
            'label_block' => true,
            'dynamic'     => [ 'active' => true ],
        ] );

        $rep = new \Elementor\Repeater();

        $rep->add_control( 'arrow', [
            'label'   => __( 'اتجاه السهم', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'up-down',
            'options' => $this->arrow_options(),
        ] );

        $rep->add_control( 'title', [
            'label'       => __( 'العنوان', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => __( 'تحريك', 'ecm-theme' ),
            'label_block' => true,
        ] );

        $rep->add_control( 'desc', [
            'label'   => __( 'الوصف', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::TEXTAREA,
            'rows'    => 3,
            'default' => __( 'اكتب شرح الحركة دي هنا.', 'ecm-theme' ),
        ] );

        $rep->add_control( 'glb_url', [
            'label'       => __( 'ملف 3D خاص بالخلية (اختياري)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( 'سيبه فاضي عشان يستخدم الملف المشترك', 'ecm-theme' ),
            'label_block' => true,
            'dynamic'     => [ 'active' => true ],
        ] );

        $this->add_control( 'cells', [
            'label'       => __( 'الخلايا', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $rep->get_controls(),
            'title_field' => '{{{ title }}}',
            'default'     => [
                [ 'arrow' => 'up-down',    'title' => __( 'تحريك رأسي', 'ecm-theme' ),   'desc' => __( 'يحرّك الكاميرا لفوق ولتحت.', 'ecm-theme' ) ],
                [ 'arrow' => 'left-right', 'title' => __( 'تحريك أفقي', 'ecm-theme' ),   'desc' => __( 'يحرّك الكاميرا يمين وشمال.', 'ecm-theme' ) ],
                [ 'arrow' => 'rotate-cw',  'title' => __( 'تدوير', 'ecm-theme' ),        'desc' => __( 'يلفّ الكاميرا حوالين المحور.', 'ecm-theme' ) ],
                [ 'arrow' => 'up',         'title' => __( 'رفع', 'ecm-theme' ),          'desc' => __( 'يرفع الكاميرا لأعلى.', 'ecm-theme' ) ],
                [ 'arrow' => 'left',       'title' => __( 'إمالة شمال', 'ecm-theme' ),   'desc' => __( 'يميل الكاميرا ناحية الشمال.', 'ecm-theme' ) ],
                [ 'arrow' => 'right',      'title' => __( 'إمالة يمين', 'ecm-theme' ),   'desc' => __( 'يميل الكاميرا ناحية اليمين.', 'ecm-theme' ) ],
            ],
        ] );

        $this->end_controls_section();

        /* ── الشبكة ── */
        $this->start_controls_section( 'grid_section', [
            'label' => __( '📐 الشبكة', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_responsive_control( 'columns', [
            'label'          => __( 'عدد الأعمدة', 'ecm-theme' ),
            'type'           => \Elementor\Controls_Manager::SELECT,
            'default'        => '3',
            'tablet_default' => '2',
            'mobile_default' => '1',
            'options'        => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
            'selectors'      => [ '{{WRAPPER}} .ecm-cgrid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);' ],
        ] );

        $this->add_responsive_control( 'cell_height', [
            'label'      => __( 'ارتفاع الموديل', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 120, 'max' => 460 ] ],
            'default'    => [ 'size' => 230 ],
            'selectors'  => [ '{{WRAPPER}} .ecm-cgrid__stage' => 'height: {{SIZE}}px;' ],
        ] );

        $this->add_responsive_control( 'gap', [
            'label'      => __( 'المسافة بين الخلايا', 'ecm-theme' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'default'    => [ 'size' => 22 ],
            'selectors'  => [ '{{WRAPPER}} .ecm-cgrid' => 'gap: {{SIZE}}px;' ],
        ] );

        $this->add_control( 'auto_rotate', [
            'label'        => __( 'دوران تلقائي للموديل', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
        ] );

        $this->add_control( 'model_fill', [
            'label'       => __( 'حجم الموديل (ملء الإطار %)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::SLIDER,
            'range'       => [ 'px' => [ 'min' => 50, 'max' => 100, 'step' => 5 ] ],
            'default'     => [ 'size' => 85 ],
            'description' => __( 'أكبر = الموديل يملأ الإطار أكثر (بدون قص).', 'ecm-theme' ),
        ] );

        $this->end_controls_section();

        /* ── التصميم ── */
        $this->start_controls_section( 'style_section', [
            'label' => __( '🎨 التصميم', 'ecm-theme' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'card_style', [
            'label'   => __( 'شكل الكارت', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'card',
            'options' => [
                'plain' => __( 'بدون خلفية', 'ecm-theme' ),
                'card'  => __( 'كارت (خلفية + حدود)', 'ecm-theme' ),
                'glow'  => __( 'كارت + توهّج عند المرور', 'ecm-theme' ),
            ],
        ] );

        $this->add_control( 'arrow_color', [
            'label'     => __( 'لون السهم', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ff4d2e',
            'selectors' => [ '{{WRAPPER}} .ecm-cgrid__arrow' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'title_color', [
            'label'     => __( 'لون العنوان', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-cgrid__title' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'desc_color', [
            'label'     => __( 'لون الوصف', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .ecm-cgrid__desc' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'text_align', [
            'label'     => __( 'محاذاة النص', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::CHOOSE,
            'options'   => [
                'start'  => [ 'title' => __( 'بداية', 'ecm-theme' ), 'icon' => 'eicon-text-align-right' ],
                'center' => [ 'title' => __( 'وسط', 'ecm-theme' ),   'icon' => 'eicon-text-align-center' ],
            ],
            'default'   => 'center',
            'selectors' => [ '{{WRAPPER}} .ecm-cgrid__body' => 'text-align: {{VALUE}};' ],
        ] );

        $this->end_controls_section();
    }

    /** SVG لكل اتجاه سهم */
    private function arrow_svg( string $dir ): string {
        $svgs = [
            // أسهم مستقيمة مفردة
            'up'    => '<svg viewBox="0 0 40 60"><path d="M20 2 L38 26 H27 V58 H13 V26 H2 Z"/></svg>',
            'down'  => '<svg viewBox="0 0 40 60"><path d="M20 58 L2 34 H13 V2 H27 V34 H38 Z"/></svg>',
            'left'  => '<svg viewBox="0 0 60 40"><path d="M2 20 L26 2 V13 H58 V27 H26 V38 Z"/></svg>',
            'right' => '<svg viewBox="0 0 60 40"><path d="M58 20 L34 38 V27 H2 V13 H34 V2 Z"/></svg>',
            // أسهم مزدوجة
            'up-down'    => '<svg viewBox="0 0 40 84"><path d="M20 2 L35 22 H26 V62 H35 L20 82 L5 62 H14 V22 H5 Z"/></svg>',
            'left-right' => '<svg viewBox="0 0 84 40"><path d="M2 20 L22 5 V14 H62 V5 L82 20 L62 35 V26 H22 V35 Z"/></svg>',
            // أسهم دائرية (تدوير)
            'rotate-cw'  => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"><path d="M50 22 A22 22 0 1 0 54 40"/><path d="M38 18 L52 20 L50 34"/></svg>',
            'rotate-ccw' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 22 A22 22 0 1 1 10 40"/><path d="M26 18 L12 20 L14 34"/></svg>',
        ];
        return $svgs[ $dir ] ?? '';
    }

    protected function render(): void {
        if ( ! function_exists( 'ecm_3d_model_markup' ) ) {
            return;
        }
        $s     = $this->get_settings_for_display();
        $cells = $s['cells'] ?? [];
        if ( empty( $cells ) ) {
            return;
        }

        $shared = isset( $s['shared_glb'] ) ? trim( $s['shared_glb'] ) : '';
        if ( '' === $shared ) {
            $shared = get_theme_mod( 'ecm_logo_glb', '' );
        }

        $fill  = isset( $s['model_fill']['size'] ) ? (int) $s['model_fill']['size'] : 85;
        $fill  = min( 100, max( 40, $fill ) );
        $frame = (int) round( 10000 / $fill );
        $rotate = ( 'yes' === ( $s['auto_rotate'] ?? '' ) );

        $style = $s['card_style'] ?? 'card';
        $cell_cls = 'ecm-cgrid__cell';
        if ( 'card' === $style ) { $cell_cls .= ' ecm-cgrid__cell--card'; }
        if ( 'glow' === $style ) { $cell_cls .= ' ecm-cgrid__cell--card ecm-cgrid__cell--glow'; }

        echo '<div class="ecm-cgrid">';

        foreach ( $cells as $cell ) {
            $glb = ! empty( $cell['glb_url'] ) ? trim( $cell['glb_url'] ) : $shared;
            $dir = $cell['arrow'] ?? 'none';

            echo '<div class="' . esc_attr( $cell_cls ) . '">';

            // ── الإطار: الموديل + السهم ──
            echo '<div class="ecm-cgrid__stage">';
            if ( '' !== $glb ) {
                echo ecm_3d_model_markup( [
                    'src'             => $glb,
                    'auto_rotate'     => $rotate,
                    'camera_controls' => true,
                    'zoom'            => false,
                    'frame_zoom'      => $frame,
                    'class'           => 'ecm-3d-model',
                    'inline_size'     => false,
                    'loading'         => 'lazy',
                ] );
            } else {
                echo '<div class="ecm-cgrid__empty">⬆️ ' . esc_html__( 'حُط رابط ملف .glb', 'ecm-theme' ) . '</div>';
            }
            if ( 'none' !== $dir ) {
                $svg = $this->arrow_svg( $dir );
                if ( $svg ) {
                    echo '<span class="ecm-cgrid__arrow ecm-cgrid__arrow--' . esc_attr( $dir ) . '">' . $svg . '</span>';
                }
            }
            echo '</div>';

            // ── الشرح ──
            echo '<div class="ecm-cgrid__body">';
            if ( ! empty( $cell['title'] ) ) {
                echo '<h3 class="ecm-cgrid__title">' . esc_html( $cell['title'] ) . '</h3>';
            }
            if ( ! empty( $cell['desc'] ) ) {
                echo '<p class="ecm-cgrid__desc">' . nl2br( esc_html( $cell['desc'] ) ) . '</p>';
            }
            echo '</div>';

            echo '</div>'; // cell
        }

        echo '</div>'; // grid
    }
}
