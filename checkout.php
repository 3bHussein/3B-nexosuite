<?php
require_once __DIR__ . '/includes/functions.php';
userGuard();
websitePermissionGuard('website_checkout');
$items = cartItems();
if (!$items) {
    flash('error', 'Your cart is empty.');
    redirect(SITE_URL . '/cart.php');
}
$user = currentUser();
$pdo = getDB();
$freeThreshold = (float)setting('free_shipping_threshold', 500);
$taxRate = (float)setting('tax_rate', 5);
$checkoutProvider = setting('checkout_payment_provider', 'paypal_me');
$payLaterEnabled = setting('checkout_pay_later_enabled', '1') === '1' && in_array($checkoutProvider, ['paypal_pay_later','paypal_me'], true);
$payLaterLabel = trim((string)setting('checkout_pay_later_label', 'Pay Later')) ?: 'Pay Later';
$payLaterNote = trim((string)setting('checkout_pay_later_note', 'Submit the order now and our team will confirm payment later.'));
$paypalBusinessEmail = trim((string)setting('paypal_business_email', 'paypal.me/EcuWarrior')) ?: 'paypal.me/EcuWarrior';
$paypalMode = setting('paypal_mode', 'live') === 'sandbox' ? 'sandbox' : 'live';
$paypalCurrency = strtoupper(trim((string)setting('paypal_currency', 'USD')) ?: 'USD');
$paypalBaseUrl = $paypalMode === 'live' ? setting('paypal_live_url', 'https://paypal.me/EcuWarrior') : setting('paypal_sandbox_url', 'https://paypal.me/EcuWarrior');
$paypalMeLink = trim((string)setting('paypal_me_link', 'https://paypal.me/EcuWarrior')) ?: 'https://paypal.me/EcuWarrior';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireOrderCreationAllowed($pdo);
    $name = trim((string)($_POST['customer_name'] ?? ($user['first_name'] . ' ' . $user['last_name'])));
    $email = trim((string)($_POST['customer_email'] ?? $user['email']));
    $phone = trim((string)($_POST['customer_phone'] ?? $user['phone'] ?? ''));
    $shippingAddress = trim((string)($_POST['shipping_address'] ?? ''));
    $billingAddress = trim((string)($_POST['billing_address'] ?? $shippingAddress));
    $requestedMethod = (string)($_POST['payment_method'] ?? 'paypal');
    $paymentMethod = ($payLaterEnabled && $requestedMethod === 'pay_later') ? 'pay_later' : 'paypal';
    $notes = trim((string)($_POST['notes'] ?? ''));

    if ($name === '' || $email === '' || $shippingAddress === '') {
        flash('error', 'Name, email, and shipping address are required.');
        redirect(SITE_URL . '/checkout.php');
    }

    try {
        $pdo->beginTransaction();
        $freshItems = [];
        $subtotal = 0.0;

        foreach ($items as $cartItem) {
            $productStmt = $pdo->prepare('SELECT * FROM ' . table('products') . ' WHERE id=? AND active=1 FOR UPDATE');
            $productStmt->execute([(int)$cartItem['id']]);
            $product = $productStmt->fetch();
            if (!$product) {
                throw new RuntimeException('A product in your cart is no longer available.');
            }
            $quantity = max(1, (int)($cartItem['quantity'] ?? 1));
            if ((int)$product['stock'] < $quantity) {
                throw new RuntimeException('Insufficient stock for ' . $product['name'] . '. Available: ' . (int)$product['stock']);
            }
            $price = (float)$product['price'];
            $lineTotal = $price * $quantity;
            $subtotal += $lineTotal;
            $freshItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $lineTotal,
            ];
        }

        $shipping = $subtotal >= $freeThreshold ? 0.0 : (float)setting('shipping_cost', 0);
        $tax = round($subtotal * ($taxRate / 100), 2);
        $total = round($subtotal + $shipping + $tax, 2);
        $scope=operationalScope($pdo,$user);
        $orderNumber = nextScopedDocumentNumber($pdo, 'order', 'ORD', $scope);
        $order = $pdo->prepare('INSERT INTO ' . table('orders') . ' (company_id,branch_id,warehouse_id,order_number,user_id,customer_name,customer_email,customer_phone,shipping_address,billing_address,subtotal,discount,shipping_cost,tax,total,payment_method,payment_status,order_status,inventory_reserved,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $order->execute([
            (int)($scope['company_id']??0) ?: null,
            (int)($scope['branch_id']??0) ?: null,
            (int)($scope['warehouse_id']??0) ?: null,
            $orderNumber,
            (int)$user['id'],
            $name,
            $email,
            $phone,
            $shippingAddress,
            $billingAddress,
            $subtotal,
            0,
            $shipping,
            $tax,
            $total,
            $paymentMethod,
            'pending',
            'new',
            1,
            $notes,
        ]);
        $orderId = (int)$pdo->lastInsertId();

        $lineStmt = $pdo->prepare('INSERT INTO ' . table('order_items') . ' (order_id,product_id,product_name,quantity,price,total) VALUES (?,?,?,?,?,?)');
        foreach ($freshItems as $item) {
            $product = $item['product'];
            $productId = (int)$product['id'];
            $qty = (int)$item['quantity'];
            $lineStmt->execute([$orderId, $productId, productName($product), $qty, $item['price'], $item['total']]);
            if(warehouseAvailableQuantity($pdo,$productId,$scope)<$qty){throw new RuntimeException('Insufficient warehouse stock for '.$product['name'].'.');}
            adjustWarehouseStock($pdo,$productId,-$qty,$scope,'website_order',$orderId,'Reserved by website checkout ' . $orderNumber);
        }

        $invoiceId = createErpInvoiceFromOrder($pdo, $orderId, false);
        $_SESSION['last_order_id'] = $orderId;
        $_SESSION['last_invoice_id'] = $invoiceId;
        unset($_SESSION['cart']);
        $pdo->commit();

        if ($paymentMethod === 'pay_later') {
            flash('success', 'Order placed successfully. Payment is pending and our team will contact you for confirmation.');
            redirect(SITE_URL . '/user/order-details.php?id=' . $orderId);
        }

        flash('success', 'Order created. Continue securely with PayPal.Me to complete payment.');
        $paypalAmount = number_format($total, 2, '.', '');
        $paypalRedirect = rtrim($paypalMeLink, '/');
        if ($paypalAmount !== '' && (float)$paypalAmount > 0) {
            $paypalRedirect .= '/' . rawurlencode($paypalAmount);
        }
        $paypalRedirect .= '?currency=' . rawurlencode($paypalCurrency);
        redirect($paypalRedirect);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', $e->getMessage());
        redirect(SITE_URL . '/checkout.php');
    }
}

$subtotal = cartSubtotal();
$shipping = $subtotal >= $freeThreshold ? 0.0 : (float)setting('shipping_cost', 0);
$tax = round($subtotal * ($taxRate / 100), 2);
$total = round($subtotal + $shipping + $tax, 2);
siteHeader('Checkout', 'checkout');
?>
<style>
.payment-method-box{border:1px solid #e6eaf2;border-radius:18px;padding:14px;background:#fff;display:flex;gap:12px;align-items:flex-start;cursor:pointer;transition:.2s ease;height:100%}
.payment-method-box:hover{border-color:#cbd5e1;box-shadow:0 12px 30px rgba(15,23,42,.06)}
.payment-method-box input{margin-top:4px}
.payment-method-box strong{display:block;color:#0f172a}
.payment-method-box small{display:block;color:#64748b;line-height:1.45}
</style>
<div class="section-heading mt-0">
  <div><h1>Checkout</h1><p>Website order, stock reservation, and ERP sales linkage occur here.</p></div>
</div>

<form method="post" class="checkout-grid">
  <section class="surface-card">
    <h2 class="h4 fw-bold mb-3">Customer details</h2>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="customer_name" value="<?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="customer_email" value="<?= esc($user['email'] ?? '') ?>" required></div>
      <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="customer_phone" value="<?= esc($user['phone'] ?? '') ?>"></div>
      <div class="col-md-6">
        <label class="form-label">Payment method</label>
        <div class="row g-2">
          <div class="<?php echo $payLaterEnabled ? 'col-12' : 'col-12'; ?>">
            <label class="payment-method-box">
              <input type="radio" name="payment_method" value="paypal" checked>
              <span><strong>PayPal.Me</strong><small><?= esc($paypalMeLink) ?><br>Redirect to PayPal.Me after order submission.</small></span>
            </label>
          </div>
          <?php if($payLaterEnabled): ?>
          <div class="col-12">
            <label class="payment-method-box">
              <input type="radio" name="payment_method" value="pay_later">
              <span><strong><?= esc($payLaterLabel) ?></strong><small><?= esc($payLaterNote) ?><br>Order completes now, payment status remains pending.</small></span>
            </label>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-12"><label class="form-label">Shipping address</label><textarea class="form-control" name="shipping_address" rows="3" required><?= esc($user['address'] ?? '') ?></textarea></div>
      <div class="col-12"><label class="form-label">Billing address</label><textarea class="form-control" name="billing_address" rows="3"><?= esc($user['address'] ?? '') ?></textarea></div>
      <div class="col-12"><label class="form-label">Order notes</label><textarea class="form-control" name="notes" rows="3" placeholder="Workshop, purchase order, or delivery note"></textarea></div>
    </div>
  </section>

  <aside class="summary-card">
    <h2 class="h4 fw-bold">Commercial summary</h2>
    <?php foreach ($items as $item): ?>
      <div class="d-flex justify-content-between gap-2 py-2 border-bottom small">
        <span><?= esc(productName($item)) ?> × <?= (int)$item['quantity'] ?></span>
        <strong><?= money((float)$item['price'] * (int)$item['quantity']) ?></strong>
      </div>
    <?php endforeach; ?>
    <div class="d-flex justify-content-between py-2"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
    <div class="d-flex justify-content-between py-2"><span>Shipping</span><strong><?= money($shipping) ?></strong></div>
    <div class="d-flex justify-content-between py-2"><span>Tax <?= number_format($taxRate, 2) ?>%</span><strong><?= money($tax) ?></strong></div>
    <div class="d-flex justify-content-between py-3 border-top h5"><span>Total</span><strong><?= money($total) ?></strong></div>
    <p class="muted small">At submission, stock is reserved and an ERP invoice is created. Pay Later keeps payment pending.</p>
    <button class="btn btn-brand btn-lg w-100" type="submit">Place Order</button>
  </aside>
</form>
<?php siteFooter(); ?>