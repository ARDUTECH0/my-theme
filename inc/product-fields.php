<?php
/**
 * ECM — حقول إضافية للمنتج
 * - صورة خاصة بالتطبيق (جودة عالية) غير صورة الموقع.
 * - شرح/توثيق المنتج (يدعم فيديوهات).
 * يظهر الشرح كتبويب في صفحة المنتج، والصورة + الوصف يرجعوا في الـ API.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** صورة التطبيق (جودة عالية) — أو صورة الموقع كبديل */
function ecm_product_app_image( int $product_id ): string {
    $id = (int) get_post_meta( $product_id, '_ecm_app_image', true );
    if ( $id ) {
        $url = wp_get_attachment_image_url( $id, 'full' );
        if ( $url ) {
            return $url;
        }
    }
    return get_the_post_thumbnail_url( $product_id, 'large' ) ?: '';
}

// ── ميتا بوكس في صفحة تعديل المنتج ────────────────────────────
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'ecm_product_fields', '📱 ' . __( 'إعدادات ECM — صورة التطبيق + الشرح', 'ecm-theme' ), 'ecm_product_fields_box', 'product', 'normal', 'high' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    global $post;
    if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) && isset( $post ) && 'product' === $post->post_type ) {
        wp_enqueue_media();
    }
} );

function ecm_product_fields_box( $post ) {
    wp_nonce_field( 'ecm_product_fields', 'ecm_product_fields_nonce' );
    $app_img = (int) get_post_meta( $post->ID, '_ecm_app_image', true );
    $doc     = get_post_meta( $post->ID, '_ecm_product_doc', true );
    $img_url = $app_img ? wp_get_attachment_image_url( $app_img, 'medium' ) : '';
    ?>
    <p><strong>🖼️ <?php esc_html_e( 'صورة التطبيق (جودة عالية — تظهر في التطبيق فقط)', 'ecm-theme' ); ?></strong></p>
    <div style="margin-bottom:6px;">
        <img id="ecm-app-img-prev" src="<?php echo esc_url( $img_url ); ?>"
            style="max-width:180px;border-radius:10px;margin-bottom:8px;display:<?php echo $img_url ? 'block' : 'none'; ?>;">
        <input type="hidden" name="ecm_app_image" id="ecm-app-image" value="<?php echo esc_attr( $app_img ); ?>">
        <br>
        <button type="button" class="button" id="ecm-app-img-btn"><?php esc_html_e( 'اختر/ارفع صورة', 'ecm-theme' ); ?></button>
        <button type="button" class="button" id="ecm-app-img-clear"><?php esc_html_e( 'إزالة', 'ecm-theme' ); ?></button>
        <p class="description"><?php esc_html_e( 'سيبها فاضية عشان يستخدم صورة الموقع.', 'ecm-theme' ); ?></p>
    </div>

    <p style="margin-top:18px;"><strong>📄 <?php esc_html_e( 'شرح المنتج (يدعم فيديو — حط رابط يوتيوب في سطر لوحده)', 'ecm-theme' ); ?></strong></p>
    <?php
    wp_editor( $doc, 'ecm_product_doc', [
        'textarea_name' => 'ecm_product_doc',
        'media_buttons' => true,
        'textarea_rows' => 8,
    ] );
    ?>
    <script>
    jQuery(function ($) {
        var frame;
        $('#ecm-app-img-btn').on('click', function (e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({ title: 'اختر صورة التطبيق', multiple: false, library: { type: 'image' } });
            frame.on('select', function () {
                var a = frame.state().get('selection').first().toJSON();
                $('#ecm-app-image').val(a.id);
                $('#ecm-app-img-prev').attr('src', a.url).show();
            });
            frame.open();
        });
        $('#ecm-app-img-clear').on('click', function (e) {
            e.preventDefault();
            $('#ecm-app-image').val('');
            $('#ecm-app-img-prev').hide();
        });
    });
    </script>
    <?php
}

// ── الحفظ ─────────────────────────────────────────────────────
add_action( 'save_post_product', function ( $post_id ) {
    if ( ! isset( $_POST['ecm_product_fields_nonce'] ) ||
        ! wp_verify_nonce( $_POST['ecm_product_fields_nonce'], 'ecm_product_fields' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    update_post_meta( $post_id, '_ecm_app_image', (int) ( $_POST['ecm_app_image'] ?? 0 ) );
    update_post_meta( $post_id, '_ecm_product_doc', wp_kses_post( wp_unslash( $_POST['ecm_product_doc'] ?? '' ) ) );
} );

// ── تبويب «الشرح» في صفحة المنتج ──────────────────────────────
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
    global $product;
    if ( ! $product ) {
        return $tabs;
    }
    $doc = get_post_meta( $product->get_id(), '_ecm_product_doc', true );
    if ( $doc ) {
        $tabs['ecm_doc'] = [
            'title'    => __( '📄 الشرح', 'ecm-theme' ),
            'priority' => 5,
            'callback' => function () use ( $doc ) {
                echo '<div class="ecm-doc-content">' . apply_filters( 'the_content', $doc ) . '</div>';
            },
        ];
    }
    return $tabs;
}, 20 );
