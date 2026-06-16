<?php
/**
 * ECM Theme — SEO Meta (خفيف)
 *
 * يضيف description + Open Graph + Twitter Card تلقائيًا لكل صفحة.
 * بيتوقف لوحده لو في بلجن SEO شغّال (Yoast / Rank Math / AIOSEO / SEO Framework)
 * عشان مايحصلش تكرار.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;


/** هل في بلجن SEO بيتولّى الميتا؟ */
function ecm_has_seo_plugin() {
    return defined( 'WPSEO_VERSION' )
        || defined( 'RANK_MATH_VERSION' )
        || defined( 'AIOSEO_VERSION' )
        || defined( 'SEOPRESS_VERSION' )
        || class_exists( 'The_SEO_Framework\\Load' );
}

/** رابط الصفحة الحالية */
function ecm_current_url() {
    if ( is_singular() ) {
        $id = get_queried_object_id();
        if ( $id ) {
            return get_permalink( $id );
        }
    }
    if ( is_front_page() || is_home() ) {
        return home_url( '/' );
    }
    global $wp;
    return home_url( add_query_arg( [], $wp->request ?? '' ) );
}

/** وصف الصفحة الحالية */
function ecm_meta_description() {
    $desc = '';

    if ( is_singular() ) {
        $post = get_queried_object();
        if ( $post ) {
            $desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( (string) $post->post_content );
        }
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $desc = wp_strip_all_tags( (string) term_description() );
    }

    if ( '' === trim( $desc ) ) {
        $desc = get_bloginfo( 'description' );
    }

    $desc = trim( preg_replace( '/\s+/', ' ', $desc ) );
    return wp_trim_words( $desc, 32, '' );
}

/** صورة المشاركة (OG image) */
function ecm_meta_image() {
    if ( is_singular() && has_post_thumbnail() ) {
        $img = get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
        if ( $img ) {
            return $img;
        }
    }
    $logo_id = get_theme_mod( 'custom_logo' );
    if ( $logo_id ) {
        $img = wp_get_attachment_image_url( $logo_id, 'large' );
        if ( $img ) {
            return $img;
        }
    }
    return '';
}

/** طباعة وسوم الميتا في الـ <head> */
function ecm_seo_meta() {
    if ( ecm_has_seo_plugin() ) {
        return;
    }

    $desc  = ecm_meta_description();
    $title = wp_get_document_title();
    $url   = ecm_current_url();
    $img   = ecm_meta_image();
    $type  = is_singular() && ! is_front_page() ? 'article' : 'website';
    $card  = $img ? 'summary_large_image' : 'summary';

    echo "\n<!-- ECM SEO -->\n";

    if ( $desc ) {
        printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
    }

    printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( $type ) );
    printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
    if ( $desc ) {
        printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $desc ) );
    }
    printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $url ) );
    printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( get_bloginfo( 'name' ) ) );
    printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( get_locale() ) );
    if ( $img ) {
        printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $img ) );
    }

    printf( "<meta name=\"twitter:card\" content=\"%s\">\n", esc_attr( $card ) );
    printf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $title ) );
    if ( $desc ) {
        printf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $desc ) );
    }
    if ( $img ) {
        printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $img ) );
    }

    echo "<!-- /ECM SEO -->\n";
}
add_action( 'wp_head', 'ecm_seo_meta', 5 );
