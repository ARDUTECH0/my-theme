<?php
/**
 * ECM — تكرار الصفحات/المقالات (Duplicate)
 * بيضيف زرار «تكرار» جنب كل صفحة بينسخ كل حاجة:
 * المحتوى + تصميم Elementor + القالب + الإعدادات + التصنيفات + الصورة البارزة.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** أنواع المحتوى المسموح تكرارها */
function ecm_dup_types(): array {
    return apply_filters( 'ecm_duplicate_post_types', [ 'page', 'post' ] );
}

/** رابط التكرار لعنصر معيّن (مع nonce) */
function ecm_dup_link( int $post_id ): string {
    return wp_nonce_url(
        admin_url( 'admin-post.php?action=ecm_duplicate&post=' . $post_id ),
        'ecm_duplicate_' . $post_id
    );
}

/** زرار «تكرار» في صفّ كل عنصر بقائمة الصفحات/المقالات */
function ecm_dup_row_action( $actions, $post ) {
    if ( ! in_array( $post->post_type, ecm_dup_types(), true ) ) {
        return $actions;
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        return $actions;
    }
    $actions['ecm_duplicate'] = '<a href="' . esc_url( ecm_dup_link( $post->ID ) ) . '" title="' . esc_attr__( 'إنشاء نسخة من هذه الصفحة', 'ecm-theme' ) . '">' . esc_html__( '⧉ تكرار', 'ecm-theme' ) . '</a>';
    return $actions;
}
add_filter( 'page_row_actions', 'ecm_dup_row_action', 10, 2 );
add_filter( 'post_row_actions', 'ecm_dup_row_action', 10, 2 );

/** تنفيذ التكرار */
function ecm_handle_duplicate() {
    $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
    if ( ! $post_id ) {
        wp_die( esc_html__( 'لم يتم تحديد صفحة للتكرار.', 'ecm-theme' ) );
    }
    check_admin_referer( 'ecm_duplicate_' . $post_id );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( esc_html__( 'ليس لديك صلاحية للتكرار.', 'ecm-theme' ) );
    }

    $post = get_post( $post_id );
    if ( ! $post || ! in_array( $post->post_type, ecm_dup_types(), true ) ) {
        wp_die( esc_html__( 'الصفحة غير موجودة أو لا يمكن تكرارها.', 'ecm-theme' ) );
    }

    // ── إنشاء النسخة (مسودّة) ──
    $new_id = wp_insert_post( [
        'post_title'     => $post->post_title . ' - نسخة',
        'post_name'      => '', // WordPress يولّد slug جديد
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_status'    => 'draft',
        'post_type'      => $post->post_type,
        'post_author'    => get_current_user_id(),
        'post_parent'    => $post->post_parent,
        'menu_order'     => $post->menu_order,
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
    ], true );

    if ( is_wp_error( $new_id ) || ! $new_id ) {
        wp_die( esc_html__( 'فشل إنشاء النسخة.', 'ecm-theme' ) );
    }

    // ── نسخ التصنيفات (categories / tags / أي taxonomy) ──
    foreach ( get_object_taxonomies( $post->post_type ) as $tax ) {
        $terms = wp_get_object_terms( $post_id, $tax, [ 'fields' => 'slugs' ] );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            wp_set_object_terms( $new_id, $terms, $tax );
        }
    }

    // ── نسخ كل الـ meta (يشمل تصميم Elementor + القالب + الصورة البارزة) ──
    $skip = [ '_edit_lock', '_edit_last', '_wp_old_slug' ];
    $meta = get_post_meta( $post_id );
    foreach ( $meta as $key => $values ) {
        if ( in_array( $key, $skip, true ) ) {
            continue;
        }
        foreach ( $values as $value ) {
            // wp_slash للحفاظ على بيانات Elementor (JSON فيه علامات اقتباس وباك‌سلاش)
            add_post_meta( $new_id, $key, wp_slash( maybe_unserialize( $value ) ) );
        }
    }

    // ── تجديد كاش/CSS الخاص بـ Elementor للنسخة الجديدة ──
    if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
        if ( isset( \Elementor\Plugin::$instance->files_manager ) ) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
    }

    // ── رجوع لتعديل النسخة الجديدة ──
    wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
    exit;
}
add_action( 'admin_post_ecm_duplicate', 'ecm_handle_duplicate' );

/** زرار «تكرار» كمان فوق في شاشة تعديل الصفحة (في شريط الإجراءات) */
function ecm_dup_admin_bar( $bar ) {
    if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || 'post' !== $screen->base ) {
        return;
    }
    global $post;
    if ( ! $post || ! in_array( $post->post_type, ecm_dup_types(), true ) || ! current_user_can( 'edit_posts' ) ) {
        return;
    }
    $bar->add_node( [
        'id'    => 'ecm-duplicate',
        'title' => '⧉ ' . __( 'تكرار الصفحة', 'ecm-theme' ),
        'href'  => ecm_dup_link( $post->ID ),
    ] );
}
add_action( 'admin_bar_menu', 'ecm_dup_admin_bar', 90 );
