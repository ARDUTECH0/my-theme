<?php
/**
 * ECM — التنزيل من التطبيق فقط
 *
 * بيمنع تنزيل المنتجات الرقمية من الموقع، ويوجّه العميل لتنزيلها من
 * داخل تطبيق ECM. (تنزيل التطبيق نفسه شغّال عبر endpoint منفصل
 * ecm/v1/app/download — مش متأثّر بالحاجات دي.)
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

/** رسالة توجيه العميل لتنزيل مشترياته من التطبيق */
function ecm_app_download_message_html(): string {
    ob_start();
    ?>
    <style>
        .ecm-app-dl-box{background:#0e1a10;border:1px solid #9CFF0044;border-radius:16px;
            padding:26px 22px;text-align:center;color:#eafff0;margin-bottom:18px;}
        .ecm-app-dl-box .ico{font-size:46px;line-height:1;margin-bottom:8px;}
        .ecm-app-dl-box h3{margin:6px 0 8px;color:#9CFF00;font-size:20px;}
        .ecm-app-dl-box p{margin:0 auto;max-width:520px;font-size:15px;line-height:1.7;opacity:.92;}
        .ecm-app-dl-list{list-style:none;margin:14px 0 0;padding:0;}
        .ecm-app-dl-list li{display:flex;align-items:center;gap:10px;justify-content:space-between;
            background:#fff;border:1px solid #e6e6e6;border-radius:12px;padding:12px 16px;margin-bottom:8px;}
        .ecm-app-dl-badge{background:#9CFF00;color:#0e1a10;font-weight:700;font-size:12px;
            border-radius:999px;padding:4px 12px;white-space:nowrap;}
    </style>
    <div class="ecm-app-dl-box">
        <div class="ico">📲</div>
        <h3><?php esc_html_e( 'نزّل مشترياتك من التطبيق', 'ecm-theme' ); ?></h3>
        <p><?php esc_html_e( 'اذهب إلى «المتجر» داخل تطبيق ECM، وهتلاقي الحاجة اللي اشتريتها جاهزة — اضغط على «تثبيت / Install».', 'ecm-theme' ); ?></p>
    </div>
    <?php
    return (string) ob_get_clean();
}

// ── 1) استبدال صفحة «التنزيلات» في حسابي بالكامل ──────────────
add_action( 'init', function () {
    remove_action( 'woocommerce_account_downloads_endpoint', 'woocommerce_account_downloads' );
    add_action( 'woocommerce_account_downloads_endpoint', 'ecm_app_downloads_endpoint' );
}, 20 );

function ecm_app_downloads_endpoint() {
    echo ecm_app_download_message_html(); // phpcs:ignore WordPress.Security.EscapeOutput

    $user_id = get_current_user_id();
    $items   = ( $user_id && function_exists( 'wc_get_customer_available_downloads' ) )
        ? wc_get_customer_available_downloads( $user_id ) : [];

    if ( empty( $items ) ) {
        return;
    }
    $seen = [];
    echo '<ul class="ecm-app-dl-list">';
    foreach ( $items as $it ) {
        $pid = (int) ( $it['product_id'] ?? 0 );
        if ( $pid && isset( $seen[ $pid ] ) ) {
            continue;
        }
        $seen[ $pid ] = 1;
        $name = $it['product_name'] ?? ( $it['download_name'] ?? '' );
        echo '<li><span>✅ ' . esc_html( wp_strip_all_tags( (string) $name ) ) . '</span>'
            . '<span class="ecm-app-dl-badge">' . esc_html__( 'متاح في التطبيق', 'ecm-theme' ) . '</span></li>';
    }
    echo '</ul>';
}

// ── 2) إخفاء قسم التنزيلات من تفاصيل الطلب + الإيميلات ─────────
add_filter( 'woocommerce_order_get_downloadable_items', '__return_empty_array' );

// ── 3) منع رابط التنزيل المباشر على الموقع (الناتج من ووكومرس) ─
add_action( 'init', function () {
    if ( ! empty( $_GET['download_file'] ) && function_exists( 'wc_get_account_endpoint_url' ) ) {
        wp_safe_redirect( wc_get_account_endpoint_url( 'downloads' ) );
        exit;
    }
}, 1 );
