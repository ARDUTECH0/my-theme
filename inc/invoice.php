<?php
/**
 * ECM — فاتورة احترافية (Invoice)
 * - تعرض فاتورة بتصميم احترافي قابلة للطباعة/الحفظ PDF.
 * - زر تحميل في الحساب وصفحة الطلب.
 * - لينك الفاتورة في إيميل الطلب.
 *
 * @package ecm-theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

/** رابط فاتورة الطلب (مع مفتاح الأمان) */
function ecm_invoice_url( $order ): string {
    return add_query_arg(
        [
            'ecm_invoice' => $order->get_id(),
            'key'         => $order->get_order_key(),
        ],
        home_url( '/' )
    );
}

// ── عرض الفاتورة عند فتح الرابط ───────────────────────────────
add_action( 'template_redirect', function () {
    if ( empty( $_GET['ecm_invoice'] ) ) {
        return;
    }
    $order = wc_get_order( absint( $_GET['ecm_invoice'] ) );
    if ( ! $order ) {
        wp_die( esc_html__( 'الفاتورة غير موجودة.', 'ecm-theme' ) );
    }

    // التحقق: مفتاح الطلب أو صاحب الطلب أو مدير
    $key     = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
    $allowed = ( $key && hash_equals( $order->get_order_key(), $key ) )
        || ( is_user_logged_in() && (int) $order->get_customer_id() === get_current_user_id() )
        || current_user_can( 'manage_woocommerce' );

    if ( ! $allowed ) {
        wp_die( esc_html__( 'غير مصرح بعرض هذه الفاتورة.', 'ecm-theme' ) );
    }

    ecm_render_invoice_html( $order );
    exit;
} );

/** HTML الفاتورة (صفحة مستقلة قابلة للطباعة) */
function ecm_render_invoice_html( $order ): void {
    $store_name = get_bloginfo( 'name' );
    $store_url  = home_url( '/' );
    $admin_email = get_option( 'admin_email' );
    $number     = $order->get_order_number();
    $date       = wc_format_datetime( $order->get_date_created(), 'Y/m/d' );
    $green      = '#9cff00';

    $bill_name  = trim( $order->get_formatted_billing_full_name() );
    if ( '' === $bill_name ) {
        $bill_name = $order->get_billing_email();
    }

    header( 'Content-Type: text/html; charset=utf-8' );
    ?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php printf( esc_html__( 'فاتورة #%s', 'ecm-theme' ), esc_html( $number ) ); ?></title>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; background: #f4f5f7; color: #1c2129; font-family: 'Tahoma','Cairo',sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .ecm-inv { max-width: 820px; margin: 30px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 18px 50px rgba(0,0,0,0.12); }
  .ecm-inv-top { background: #0d0e11; color: #fff; padding: 34px 40px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; }
  .ecm-inv-brand { font-size: 26px; font-weight: 800; letter-spacing: 1px; }
  .ecm-inv-brand span { color: <?php echo $green; ?>; }
  .ecm-inv-brand small { display: block; font-size: 12px; color: #9aa0a6; font-weight: 400; margin-top: 4px; letter-spacing: 0; }
  .ecm-inv-title { text-align: left; }
  .ecm-inv-title h1 { margin: 0; font-size: 30px; letter-spacing: 3px; color: <?php echo $green; ?>; }
  .ecm-inv-title p { margin: 4px 0 0; font-size: 13px; color: #c4c8cc; }
  .ecm-inv-body { padding: 32px 40px; }
  .ecm-inv-cols { display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 26px; }
  .ecm-inv-box h3 { margin: 0 0 8px; font-size: 12px; letter-spacing: 1px; color: #8a9099; text-transform: uppercase; }
  .ecm-inv-box p { margin: 2px 0; font-size: 14px; line-height: 1.7; }
  table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  thead th { background: <?php echo $green; ?>; color: #0d0e11; text-align: start; padding: 12px 14px; font-size: 13px; }
  tbody td { padding: 12px 14px; border-bottom: 1px solid #eceef0; font-size: 14px; }
  tbody tr:nth-child(even) { background: #fafbfc; }
  .ecm-inv-num { text-align: center; width: 70px; }
  .ecm-inv-amt { text-align: start; white-space: nowrap; }
  .ecm-inv-totals { margin-top: 22px; margin-inline-start: auto; width: 320px; max-width: 100%; }
  .ecm-inv-totals .row { display: flex; justify-content: space-between; padding: 9px 4px; font-size: 14px; border-bottom: 1px solid #eceef0; }
  .ecm-inv-totals .grand { font-size: 18px; font-weight: 800; color: #0d0e11; border-bottom: none; border-top: 2px solid #0d0e11; margin-top: 4px; padding-top: 12px; }
  .ecm-inv-foot { background: #f7f8fa; padding: 22px 40px; text-align: center; color: #6b7178; font-size: 13px; border-top: 1px solid #eceef0; }
  .ecm-inv-foot strong { color: #1c2129; }
  .ecm-print-bar { max-width: 820px; margin: 18px auto 0; text-align: center; }
  .ecm-print-btn { background: <?php echo $green; ?>; color: #0d0e11; border: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; }
  .ecm-print-btn:hover { opacity: .9; }
  @media print {
    body { background: #fff; }
    .ecm-inv { box-shadow: none; margin: 0; border-radius: 0; max-width: 100%; }
    .ecm-print-bar { display: none; }
  }
</style>
</head>
<body>
  <div class="ecm-inv">
    <div class="ecm-inv-top">
      <div class="ecm-inv-brand">
        <?php echo esc_html( $store_name ); ?>
        <small><?php echo esc_html( wp_parse_url( $store_url, PHP_URL_HOST ) ); ?></small>
      </div>
      <div class="ecm-inv-title">
        <h1><?php esc_html_e( 'فاتورة', 'ecm-theme' ); ?></h1>
        <p><?php printf( esc_html__( 'رقم: #%s', 'ecm-theme' ), esc_html( $number ) ); ?></p>
        <p><?php printf( esc_html__( 'التاريخ: %s', 'ecm-theme' ), esc_html( $date ) ); ?></p>
      </div>
    </div>

    <div class="ecm-inv-body">
      <div class="ecm-inv-cols">
        <div class="ecm-inv-box">
          <h3><?php esc_html_e( 'فاتورة إلى', 'ecm-theme' ); ?></h3>
          <p><strong><?php echo esc_html( $bill_name ); ?></strong></p>
          <p><?php echo esc_html( $order->get_billing_email() ); ?></p>
          <?php if ( $order->get_billing_phone() ) : ?>
            <p><?php echo esc_html( $order->get_billing_phone() ); ?></p>
          <?php endif; ?>
        </div>
        <div class="ecm-inv-box" style="text-align:start;">
          <h3><?php esc_html_e( 'تفاصيل الطلب', 'ecm-theme' ); ?></h3>
          <p><?php esc_html_e( 'الحالة:', 'ecm-theme' ); ?> <strong><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></strong></p>
          <p><?php esc_html_e( 'طريقة الدفع:', 'ecm-theme' ); ?> <?php echo esc_html( $order->get_payment_method_title() ); ?></p>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th class="ecm-inv-num">#</th>
            <th><?php esc_html_e( 'المنتج', 'ecm-theme' ); ?></th>
            <th class="ecm-inv-num"><?php esc_html_e( 'الكمية', 'ecm-theme' ); ?></th>
            <th class="ecm-inv-amt"><?php esc_html_e( 'الإجمالي', 'ecm-theme' ); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; foreach ( $order->get_items() as $item ) : ?>
          <tr>
            <td class="ecm-inv-num"><?php echo (int) $i++; ?></td>
            <td><?php echo esc_html( $item->get_name() ); ?></td>
            <td class="ecm-inv-num"><?php echo (int) $item->get_quantity(); ?></td>
            <td class="ecm-inv-amt"><?php echo wp_kses_post( wc_price( $order->get_line_total( $item, true ), [ 'currency' => $order->get_currency() ] ) ); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="ecm-inv-totals">
        <?php foreach ( $order->get_order_item_totals() as $total ) : ?>
          <div class="row <?php echo ( false !== strpos( $total['label'], 'Total' ) || mb_strpos( $total['label'], 'الإجمالي' ) !== false ) ? 'grand' : ''; ?>">
            <span><?php echo esc_html( $total['label'] ); ?></span>
            <span><?php echo wp_kses_post( $total['value'] ); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="ecm-inv-foot">
      <p><strong><?php esc_html_e( 'شكرًا لشرائك من', 'ecm-theme' ); ?> <?php echo esc_html( $store_name ); ?>!</strong></p>
      <p><?php esc_html_e( 'لأي استفسار تواصل معنا على:', 'ecm-theme' ); ?> <?php echo esc_html( $admin_email ); ?></p>
    </div>
  </div>

  <div class="ecm-print-bar">
    <button class="ecm-print-btn" onclick="window.print()">🖨️ <?php esc_html_e( 'طباعة / حفظ PDF', 'ecm-theme' ); ?></button>
  </div>
</body>
</html>
    <?php
}

// ── زر الفاتورة في قائمة الطلبات (الحساب) ─────────────────────
add_filter( 'woocommerce_my_account_my_orders_actions', function ( $actions, $order ) {
    $actions['ecm_invoice'] = [
        'url'  => ecm_invoice_url( $order ),
        'name' => __( '🧾 الفاتورة', 'ecm-theme' ),
    ];
    return $actions;
}, 10, 2 );

// ── زر الفاتورة في صفحة الطلب / تأكيد الشراء ───────────────────
add_action( 'woocommerce_order_details_after_order_table', function ( $order ) {
    echo '<p class="ecm-invoice-btn-wrap"><a class="button" href="' . esc_url( ecm_invoice_url( $order ) ) . '" target="_blank" rel="noopener">🧾 ' . esc_html__( 'تحميل الفاتورة', 'ecm-theme' ) . '</a></p>';
} );

// ── لينك الفاتورة في إيميل الطلب ───────────────────────────────
add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain_text ) {
    $url = ecm_invoice_url( $order );
    if ( $plain_text ) {
        echo "\n" . esc_html__( 'تحميل الفاتورة:', 'ecm-theme' ) . ' ' . esc_url_raw( $url ) . "\n";
    } else {
        echo '<p style="margin:18px 0;"><a href="' . esc_url( $url ) . '" style="background:#9cff00;color:#0d0e11;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;">🧾 ' . esc_html__( 'تحميل الفاتورة', 'ecm-theme' ) . '</a></p>';
    }
}, 20, 3 );
