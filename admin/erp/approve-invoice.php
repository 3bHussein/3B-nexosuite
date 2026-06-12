<?php
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('invoices');
$pdo=getDB();
$id=(int)($_GET['id']??0);
$pdo->beginTransaction();
try{
  $stmt=$pdo->prepare('SELECT * FROM ' . table('invoices') . ' WHERE id=? LIMIT 1 FOR UPDATE');$stmt->execute([$id]);$invoice=$stmt->fetch();
  if(!$invoice){throw new RuntimeException('Invoice not found.');}
  enforceScopeAllowed($pdo,(int)($invoice['company_id']??0),(int)($invoice['branch_id']??0),(int)($invoice['warehouse_id']??0),true);
  if(!in_array((string)$invoice['status'],['draft','pending_approval'],true)){throw new RuntimeException('Only draft or pending approval invoices can be approved.');}
  $active=activeApprovalRequest($pdo,'invoice',$id,'approve');
  if($active){
      throw new RuntimeException('Invoice approval is already waiting in the approval center: '.$active['request_number'].'.');
  }
  $request=createApprovalRequestForDocument($pdo,'invoice',$id,'approve','Invoice approval / discount review request.');
  if($request){
      $pdo->prepare('UPDATE '.table('invoices').' SET status="pending_approval" WHERE id=?')->execute([$id]);
      $pdo->commit();
      flash('success','Invoice sent to the approval center: '.$request['request_number'].'.');
      redirect(ADMIN_URL.'/erp/view-invoice.php?id='.$id);
  }
  executeInvoiceApproval($pdo,$id);
  $pdo->commit();
  flash('success','Invoice approved and stock ledger updated.');
}catch(Throwable $e){
  if($pdo->inTransaction()){$pdo->rollBack();}
  flash('error',$e->getMessage());
}
redirect(ADMIN_URL.'/erp/view-invoice.php?id='.$id);