<?php
/**
 * ECM — صورة المستخدم (أفاتار)
 * - يعرض صورة جوجل تلقائيًا بعد الدخول بجوجل.
 * - يسمح للمستخدم برفع صورته الخاصة من صفحة الحساب (لها الأولوية).
 * الترتيب: الصورة المرفوعة > صورة جوجل > Gravatar.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** يرجّع رابط أفاتار المستخدم المخصّص (مرفوع أو جوجل) أو '' */
function ecm_user_avatar_url( int $user_id ): string {
    if ( ! $user_id ) {
        return '';
    }
    $uploaded = get_user_meta( $user_id, 'ecm_avatar_url', true );
    if ( $uploaded ) {
        return $uploaded;
    }
    $google = get_user_meta( $user_id, 'ecm_google_avatar', true );
    if ( $google ) {
        return $google;
    }
    return '';
}

/** يستخرج user ID من المُدخل المتنوّع اللي بيجي لفلتر الأفاتار */
function ecm_avatar_resolve_user( $id_or_email ): int {
    if ( is_numeric( $id_or_email ) ) {
        return (int) $id_or_email;
    }
    if ( $id_or_email instanceof WP_User ) {
        return (int) $id_or_email->ID;
    }
    if ( $id_or_email instanceof WP_Post ) {
        return (int) $id_or_email->post_author;
    }
    if ( $id_or_email instanceof WP_Comment ) {
        if ( ! empty( $id_or_email->user_id ) ) {
            return (int) $id_or_email->user_id;
        }
        if ( ! empty( $id_or_email->comment_author_email ) ) {
            $u = get_user_by( 'email', $id_or_email->comment_author_email );
            return $u ? (int) $u->ID : 0;
        }
        return 0;
    }
    if ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $u = get_user_by( 'email', $id_or_email );
        return $u ? (int) $u->ID : 0;
    }
    return 0;
}

// ── استبدال الأفاتار بالصورة المخصّصة ─────────────────────────
add_filter( 'get_avatar_data', function ( $args, $id_or_email ) {
    $user_id = ecm_avatar_resolve_user( $id_or_email );
    if ( ! $user_id ) {
        return $args;
    }
    $url = ecm_user_avatar_url( $user_id );
    if ( $url ) {
        $args['url']          = $url;
        $args['found_avatar'] = true;
    }
    return $args;
}, 10, 2 );

// ── حقل رفع الصورة في صفحة الحساب (بيانات الحساب) ─────────────
add_action( 'woocommerce_edit_account_form_tag', function () {
    echo 'enctype="multipart/form-data"';
} );

add_action( 'woocommerce_edit_account_form', function () {
    $uid = get_current_user_id();
    $url = ecm_user_avatar_url( $uid );
    if ( ! $url ) {
        $url = get_avatar_url( $uid, [ 'size' => 96 ] );
    }
    ?>
    <fieldset class="ecm-avatar-field">
        <legend><?php esc_html_e( 'صورة الحساب', 'ecm-theme' ); ?></legend>
        <div class="ecm-avatar-row">
            <img src="<?php echo esc_url( $url ); ?>" alt="" class="ecm-avatar-preview" id="ecm-avatar-preview">
            <div class="ecm-avatar-actions">
                <label class="button ecm-avatar-btn" for="ecm_avatar">📷 <?php esc_html_e( 'اختر صورة', 'ecm-theme' ); ?></label>
                <input type="file" name="ecm_avatar" id="ecm_avatar" accept="image/*" style="display:none;">
                <p class="ecm-avatar-hint"><?php esc_html_e( 'JPG أو PNG — يفضّل صورة مربّعة.', 'ecm-theme' ); ?></p>
                <?php if ( get_user_meta( $uid, 'ecm_avatar_id', true ) ) : ?>
                    <label class="ecm-avatar-remove"><input type="checkbox" name="ecm_avatar_remove" value="1"> <?php esc_html_e( 'إزالة الصورة الحالية', 'ecm-theme' ); ?></label>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>
    <script>
    ( function () {
        var input = document.getElementById( 'ecm_avatar' );
        var prev  = document.getElementById( 'ecm-avatar-preview' );
        if ( input && prev ) {
            input.addEventListener( 'change', function () {
                if ( input.files && input.files[0] ) {
                    prev.src = URL.createObjectURL( input.files[0] );
                }
            } );
        }
    } )();
    </script>
    <?php
} );

// ── حفظ الصورة المرفوعة ───────────────────────────────────────
add_action( 'woocommerce_save_account_details', function ( $user_id ) {
    // إزالة الصورة الحالية
    if ( ! empty( $_POST['ecm_avatar_remove'] ) ) {
        $old = get_user_meta( $user_id, 'ecm_avatar_id', true );
        if ( $old ) {
            wp_delete_attachment( (int) $old, true );
        }
        delete_user_meta( $user_id, 'ecm_avatar_id' );
        delete_user_meta( $user_id, 'ecm_avatar_url' );
    }

    if ( empty( $_FILES['ecm_avatar'] ) || empty( $_FILES['ecm_avatar']['name'] ) ) {
        return;
    }

    // تأكّد إنها صورة
    $check = wp_check_filetype( $_FILES['ecm_avatar']['name'] );
    if ( strpos( (string) $check['type'], 'image/' ) !== 0 ) {
        wc_add_notice( __( 'الملف لازم يكون صورة (JPG/PNG).', 'ecm-theme' ), 'error' );
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attach_id = media_handle_upload( 'ecm_avatar', 0 );
    if ( is_wp_error( $attach_id ) ) {
        wc_add_notice( __( 'تعذّر رفع الصورة، حاول تاني.', 'ecm-theme' ), 'error' );
        return;
    }

    // امسح القديمة
    $old = get_user_meta( $user_id, 'ecm_avatar_id', true );
    if ( $old ) {
        wp_delete_attachment( (int) $old, true );
    }

    update_user_meta( $user_id, 'ecm_avatar_id', $attach_id );
    update_user_meta( $user_id, 'ecm_avatar_url', wp_get_attachment_url( $attach_id ) );
} );
