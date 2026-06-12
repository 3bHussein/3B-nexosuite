<?php
require_once __DIR__ . '/includes/functions.php';
websitePermissionGuard('website_cart');
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove'])) {
        unset($_SESSION['cart'][(int)$_POST['remove']]);
        flash('success', 'Item removed from cart.');
        redirect(SITE_URL . '/cart.php');
    }
    if (isset($_POST['clear'])) {
        unset($_SESSION['cart']);
        flash('success', 'Cart cleared.');
        redirect(SITE_URL . '/cart.php');
    }
    foreach (($_POST['qty'] ?? []) as $id => $qty) {
        $id = (int)$id;
        $qty = max(1, (int)$qty);
        $stockStmt = $pdo->prepare('SELECT stock FROM ' . table('products') . ' WHERE id=? AND active=1 LIMIT 1');
        $stockStmt->execute([$id]);
        $stock = (int)$stockStmt->fetchColumn();
        if ($stock <= 0) {
            unset($_SESSION['cart'][$id]);
            flash('error', 'An unavailable item was removed from your cart.');
            continue;
        }
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] = min($qty, $stock);
        }
    }
    flash('success', 'Cart updated.');
    redirect(SITE_URL . '/cart.php');
}

$items = cartItems();
$subtotal = cartSubtotal();
$freeThreshold = (float)setting('free_shipping_threshold', 500);
siteHeader('Cart', 'cart');
?>
<div class="section-heading mt-0">
  <div><h1>Your cart</h1><p>Review stock-aware quantities before checkout.</p></div>
  <?php if ($items): ?><form method="post"><button class="btn btn-soft" type="submit" name="clear" value="1">Clear cart</button></form><?php endif; ?>
</div>

<?php if (!$items): ?>
  <div class="empty-state">
    <h2 class="h4 fw-bold">Your cart is empty</h2>
    <p class="muted">Browse the catalogue and add products to start an ERP-connected order.</p>
    <a class="btn btn-brand" href="<?= esc(SITE_URL) ?>/products/index.php">Browse products</a>
  </div>
<?php else: ?>
  <div class="checkout-grid">
    <form method="post">
      <?php foreach ($items as $item):
        $mediaProduct = ['image' => $item['image'] ?? '', 'name' => $item['name'] ?? 'Product'];
      ?>
        <div class="cart-line">
          <img src="<?= esc(productImageUrl($mediaProduct)) ?>" alt="<?= esc(productName($item)) ?>">
          <div>
            <h2 class="h5 fw-bold mb-1"><?= esc(productName($item)) ?></h2>
            <div class="muted small"><?= esc($item['sku'] ?? '') ?></div>
          </div>
          <div class="cart-price">
            <small class="muted d-block">Unit</small>
            <strong><?= money($item['price']) ?></strong>
          </div>
          <div class="cart-qty">
            <small class="muted d-block">Quantity</small>
            <input class="form-control" type="number" min="1" name="qty[<?= (int)$item['id'] ?>]" value="<?= (int)$item['quantity'] ?>">
          </div>
          <div class="cart-remove">
            <button class="btn btn-outline-danger btn-sm" type="submit" name="remove" value="<?= (int)$item['id'] ?>">×</button>
          </div>
        </div>
      <?php endforeach; ?>
      <button class="btn btn-soft" type="submit">Update cart</button>
    </form>

    <aside class="summary-card">
      <h2 class="h4 fw-bold">Order summary</h2>
      <div class="d-flex justify-content-between py-2 border-bottom"><span>Items</span><strong><?= cartCount() ?></strong></div>
      <div class="d-flex justify-content-between py-2 border-bottom"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
      <div class="d-flex justify-content-between py-2 border-bottom"><span>Free shipping target</span><strong><?= money($freeThreshold) ?></strong></div>
      <p class="muted small mt-3 mb-3"><?= $subtotal >= $freeThreshold ? 'Free shipping threshold achieved where the shipping rule is enabled.' : 'Add ' . money(max(0, $freeThreshold - $subtotal)) . ' more to reach the configured threshold.' ?></p>
      <a class="btn btn-brand btn-lg w-100" href="<?= esc(SITE_URL) ?>/checkout.php">Proceed to checkout</a>
      <a class="btn btn-soft w-100 mt-2" href="<?= esc(SITE_URL) ?>/products/index.php">Continue shopping</a>
    </aside>
  </div>
<?php endif; ?>
<?php siteFooter(); ?>