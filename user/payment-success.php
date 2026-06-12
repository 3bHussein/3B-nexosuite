<?php
require_once dirname(__DIR__) . '/includes/functions.php';
userGuard();
$user = currentUser();
$pdo = getDB();
$orderId = (int)($_GET['order'] ?? 0);

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT * FROM ' . table('orders') . ' WHERE id=? AND user_id=? LIMIT 1 FOR UPDATE');
    $stmt->execute([$orderId, (int)$user['id']]);
    $order = $stmt->fetch();
    if (!$order) {
        throw new RuntimeException('Order not found.');
    }
    $pdo->prepare('UPDATE ' . table('orders') . ' SET payment_status="paid", order_status="processing" WHERE id=?')->execute([$orderId]);
    $invoiceId = !empty($order['erp_invoice_id']) ? (int)$order['erp_invoice_id'] : createErpInvoiceFromOrder($pdo, $orderId, false);
    $invoiceStmt = $pdo->prepare('SELECT * FROM ' . table('invoices') . ' WHERE id=? LIMIT 1 FOR UPDATE');
    $invoiceStmt->execute([$invoiceId]);
    $invoice = $invoiceStmt->fetch();
    if ($invoice) {
        $balance = max(0, (float)$invoice['total'] - (float)$invoice['amount_paid']);
        if ($balance > 0) {
            $paymentScope=[
                'company_id'=>(int)($order['company_id']??0),
                'branch_id'=>(int)($order['branch_id']??0),
                'warehouse_id'=>(int)($order['warehouse_id']??0),
                'location_id'=>(int)setting('default_location_id','0'),
            ];
            $payment = $pdo->prepare('INSERT INTO ' . table('payments') . ' (company_id,branch_id,payment_number,invoice_id,customer_id,amount,method,reference,status,paid_at,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $payment->execute([
                (int)($order['company_id']??0) ?: null,
                (int)($order['branch_id']??0) ?: null,
                nextScopedDocumentNumber($pdo, 'payment', (string)setting('payment_prefix', 'PAY'), $paymentScope),
                $invoiceId,
                (int)$invoice['customer_id'],
                $balance,
                'demo_card',
                (string)$order['order_number'],
                'received',
                date('Y-m-d H:i:s'),
                'Demo payment confirmed from website payment-success flow.',
            ]);
            $pdo->prepare('UPDATE ' . table('invoices') . ' SET amount_paid=total,balance_due=0,status="paid",paid_at=?,approved_at=COALESCE(approved_at,?) WHERE id=?')->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $invoiceId]);
        }
    }
    $pdo->commit();
    flash('success', 'Payment marked successful and ERP invoice updated as paid in demo mode.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash('error', $e->getMessage());
}
redirect(SITE_URL . '/user/orders.php');