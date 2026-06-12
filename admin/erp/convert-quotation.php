<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('quotations');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$pdo->beginTransaction();
try{
    $stmt=$pdo->prepare('SELECT * FROM ' . table('quotations') . ' WHERE id=? LIMIT 1');
    $stmt->execute([$id]);
    $quote=$stmt->fetch();
    if(!$quote){throw new RuntimeException('Quotation not found.');}
    if(!empty($quote['converted_invoice_id'])){throw new RuntimeException('This quotation was already converted.');}
    if(($quote['status']??'')!=='accepted'){throw new RuntimeException('Only accepted quotations can be converted into invoices.');}

    enforceScopeAllowed($pdo,(int)($quote['company_id']??0),(int)($quote['branch_id']??0),(int)($quote['warehouse_id']??0),true);
    $quoteScope=['company_id'=>(int)($quote['company_id']??0),'branch_id'=>(int)($quote['branch_id']??0),'warehouse_id'=>(int)($quote['warehouse_id']??0),'location_id'=>(int)setting('default_location_id','0')];
    $invoiceNumber=nextScopedDocumentNumber($pdo,'invoice',(string)setting('invoice_prefix','INV'),$quoteScope);
        requireInvoiceCreationAllowed($pdo);
$invoice=$pdo->prepare('INSERT INTO ' . table('invoices') . ' (company_id,branch_id,warehouse_id,invoice_number,customer_id,customer_name,customer_email,customer_type,billing_address,subtotal,discount,tax,shipping,total,amount_paid,balance_due,status,sales_channel,due_date,approved_at,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,"draft","erp",?,?,?)');
    $invoice->execute([
        (int)($quote['company_id']??0) ?: null,
        (int)($quote['branch_id']??0) ?: null,
        (int)($quote['warehouse_id']??0) ?: null,
        $invoiceNumber,
        $quote['customer_id']?:null,
        $quote['customer_name'],
        $quote['customer_email'],
        $quote['customer_type'],
        $quote['billing_address'],
        $quote['subtotal'],
        $quote['discount'],
        $quote['tax'],
        $quote['shipping'],
        $quote['total'],
        0,
        $quote['total'],
        date('Y-m-d',strtotime('+7 days')),
        null,
        'Converted from quotation '.$quote['quotation_number'].'. '.$quote['notes'],
    ]);
    $invoiceId=(int)$pdo->lastInsertId();

    $items=$pdo->prepare('SELECT * FROM ' . table('quotation_items') . ' WHERE quotation_id=? ORDER BY id ASC');
    $items->execute([$id]);
    $insert=$pdo->prepare('INSERT INTO ' . table('invoice_items') . ' (invoice_id,item_type,product_id,description,quantity,unit_price,tax_rate,line_total) VALUES (?,?,?,?,?,?,?,?)');
    foreach($items->fetchAll() as $item){
        $insert->execute([$invoiceId,$item['item_type'],$item['product_id'],$item['description'],$item['quantity'],$item['unit_price'],$item['tax_rate'],$item['line_total']]);
    }

    $pdo->prepare('UPDATE ' . table('quotations') . ' SET status="converted",converted_invoice_id=? WHERE id=?')->execute([$invoiceId,$id]);
    logActivity($pdo,'Quotation','convert','Quotation '.$quote['quotation_number'].' converted into invoice '.$invoiceNumber.'.','invoice',$invoiceId);
    $pdo->commit();
    flash('success','Quotation converted into a draft invoice. Review and approve the invoice next.');
    redirect(ADMIN_URL.'/erp/view-invoice.php?id='.$invoiceId);
}catch(Throwable $e){
    $pdo->rollBack();
    flash('error',$e->getMessage());
    redirect(ADMIN_URL.'/erp/view-quotation.php?id='.$id);
}