<?php
/**
 * ECM — QR Code لروابط التحميل
 * بيضيف QR جنب كل منتج رقمي اشتراه المستخدم في «حسابي → التحميلات»،
 * يحتوي رابط التحميل — يتمسح بالتطبيق لتحميل المنتج.
 * الـ QR بيتولّد محليًا في المتصفح (الرابط السري ما يتبعتش لأي خادم خارجي).
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

// ── عمود QR في جدول التحميلات ─────────────────────────────────
add_filter( 'woocommerce_account_downloads_columns', function ( $columns ) {
    $new = [];
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'download-file' === $key ) {
            $new['download-qr'] = __( 'QR للتطبيق', 'ecm-theme' );
        }
    }
    // لو ماكانش فيه عمود download-file، ضيفه في الآخر
    if ( ! isset( $new['download-qr'] ) ) {
        $new['download-qr'] = __( 'QR للتطبيق', 'ecm-theme' );
    }
    return $new;
} );

// ── محتوى عمود الـ QR ─────────────────────────────────────────
add_action( 'woocommerce_account_downloads_column_download-qr', function ( $download ) {
    if ( empty( $download['download_url'] ) ) {
        echo '—';
        return;
    }
    // السيريال (جهاز المستخدم المفعّل) + اسم المنتج جوه الـ QR
    $serial  = function_exists( 'ecm_user_primary_serial' ) ? ecm_user_primary_serial( get_current_user_id() ) : '';
    $product = $download['product_name'] ?? '';
    $url     = $download['download_url'];
    if ( $serial ) {
        $url = add_query_arg( 'serial', rawurlencode( $serial ), $url );
    }
    if ( $product ) {
        $url = add_query_arg( 'product', rawurlencode( $product ), $url );
    }

    $caption = '📱 ' . ( $product ? $product : __( 'امسح بالتطبيق', 'ecm-theme' ) );
    if ( $serial ) {
        $caption .= ' · ' . $serial;
    }

    echo '<div class="ecm-qr-cell">';
    echo '<div class="ecm-qr" data-qr="' . esc_attr( $url ) . '"></div>';
    echo '<span class="ecm-qr-cap">' . esc_html( $caption ) . '</span>';
    echo '</div>';
} );

// ── تحميل مكتبة QR + توليد الأكواد (في صفحة الحساب فقط) ────────
add_action( 'wp_enqueue_scripts', function () {
    if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
        return;
    }
    wp_enqueue_script(
        'ecm-qrcode',
        'https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js',
        [],
        '1.0.0',
        true
    );
    $init = <<<JS
( function () {
    function build() {
        if ( ! window.QRCode ) { return; }
        var nodes = document.querySelectorAll( '.ecm-qr[data-qr]' );
        Array.prototype.forEach.call( nodes, function ( el ) {
            if ( el.dataset.done ) { return; }
            el.dataset.done = '1';
            new QRCode( el, {
                text: el.getAttribute( 'data-qr' ),
                width: 104,
                height: 104,
                colorDark: '#0d0e11',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            } );
        } );
    }
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', build );
    } else {
        build();
    }
} )();
JS;
    wp_add_inline_script( 'ecm-qrcode', $init );
} );
