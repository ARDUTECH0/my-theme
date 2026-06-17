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

        $rep->add_control( 'media_type', [
            'label'   => __( 'نوع المحتوى', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => '3d',
            'options' => [
                '3d'    => __( 'موديل 3D', 'ecm-theme' ),
                'image' => __( 'صورة', 'ecm-theme' ),
            ],
        ] );

        $rep->add_control( 'glb_url', [
            'label'       => __( 'ملف 3D خاص بالخلية (اختياري)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( 'سيبه فاضي عشان يستخدم الملف المشترك', 'ecm-theme' ),
            'label_block' => true,
            'dynamic'     => [ 'active' => true ],
            'condition'   => [ 'media_type' => '3d' ],
        ] );

        $rep->add_control( 'image', [
            'label'     => __( 'الصورة', 'ecm-theme' ),
            'type'      => \Elementor\Controls_Manager::MEDIA,
            'condition' => [ 'media_type' => 'image' ],
        ] );

        $rep->add_control( 'popup_image', [
            'label'       => __( 'صورة التكبير (تفتح عند الضغط)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::MEDIA,
            'description' => __( 'تظهر مكبّرة لما الزائر يضغط على الخلية. سيبها فاضية = يستخدم نفس صورة الخلية.', 'ecm-theme' ),
        ] );

        $rep->add_control( 'arrow', [
            'label'   => __( 'اتجاه السهم', 'ecm-theme' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'up-down',
            'options' => $this->arrow_options(),
        ] );

        $rep->add_control( 'title', [
            'label'       => __( 'العنوان', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( 'اكتب العنوان…', 'ecm-theme' ),
            'label_block' => true,
        ] );

        $rep->add_control( 'desc', [
            'label'       => __( 'الوصف', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXTAREA,
            'rows'        => 3,
            'placeholder' => __( 'اكتب الوصف…', 'ecm-theme' ),
        ] );

        $rep->add_control( 'btn_text', [
            'label'       => __( 'نص الزر (اختياري)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( 'مثال: اعرف أكتر', 'ecm-theme' ),
            'label_block' => true,
        ] );

        $rep->add_control( 'btn_link', [
            'label'       => __( 'رابط الزر (الصفحة)', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::URL,
            'placeholder' => __( 'اختار صفحة أو الصق الرابط', 'ecm-theme' ),
            'autocomplete'=> true,
            'condition'   => [ 'btn_text!' => '' ],
        ] );

        $this->add_control( 'cells', [
            'label'       => __( 'الخلايا', 'ecm-theme' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $rep->get_controls(),
            'title_field' => '{{{ title || "خلية" }}}',
            'default'     => [
                [ 'arrow' => 'up-down' ],
                [ 'arrow' => 'left-right' ],
                [ 'arrow' => 'rotate-cw' ],
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

        $this->add_control( 'lightbox', [
            'label'        => __( 'تكبير الصورة عند الضغط', 'ecm-theme' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'description'  => __( 'الضغط على الخلية يفتح الصورة مكبّرة في نافذة.', 'ecm-theme' ),
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

        $lightbox = ( 'yes' === ( $s['lightbox'] ?? 'yes' ) );

        echo '<div class="ecm-cgrid">';

        foreach ( $cells as $cell ) {
            $type    = $cell['media_type'] ?? '3d';
            $is_img  = ( 'image' === $type );
            $img_url = ! empty( $cell['image']['url'] ) ? $cell['image']['url'] : '';
            $glb     = ! empty( $cell['glb_url'] ) ? trim( $cell['glb_url'] ) : $shared;
            $dir     = $cell['arrow'] ?? 'none';

            // صورة التكبير: المخصّصة أو صورة الخلية
            $popup = ! empty( $cell['popup_image']['url'] ) ? $cell['popup_image']['url'] : $img_url;
            $can_zoom = ( $lightbox && '' !== $popup );

            echo '<div class="' . esc_attr( $cell_cls ) . '">';

            // ── الإطار: المحتوى + السهم ──
            // الصورة: الإطار كله قابل للضغط. الـ 3D: التكبير من الأيقونة بس (عشان السحب يفضل يلفّ).
            $stage_clickable = ( $can_zoom && $is_img );
            $stage_cls  = 'ecm-cgrid__stage' . ( $can_zoom ? ' ecm-cgrid__stage--zoom' : '' );
            $stage_attr = $stage_clickable ? ' data-ecm-lb="' . esc_url( $popup ) . '" role="button" tabindex="0"' : '';
            echo '<div class="' . esc_attr( $stage_cls ) . '"' . $stage_attr . '>';

            if ( $is_img ) {
                if ( '' !== $img_url ) {
                    echo '<img class="ecm-cgrid__img" src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $cell['title'] ?? '' ) . '" loading="lazy">';
                } else {
                    echo '<div class="ecm-cgrid__empty">🖼️ ' . esc_html__( 'اختار صورة', 'ecm-theme' ) . '</div>';
                }
            } elseif ( '' !== $glb ) {
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
            if ( ! empty( $cell['btn_text'] ) ) {
                $url = ! empty( $cell['btn_link']['url'] ) ? $cell['btn_link']['url'] : '#';
                $tgt = ! empty( $cell['btn_link']['is_external'] ) ? ' target="_blank"' : '';
                $rel = ! empty( $cell['btn_link']['nofollow'] ) ? ' rel="nofollow"' : '';
                echo '<a class="ecm-btn-primary ecm-cgrid__btn" href="' . esc_url( $url ) . '"' . $tgt . $rel . '>' . esc_html( $cell['btn_text'] ) . '</a>';
            }
            echo '</div>';

            echo '</div>'; // cell
        }

        echo '</div>'; // grid
    }
}
