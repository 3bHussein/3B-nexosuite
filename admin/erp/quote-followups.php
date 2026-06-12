<?php
$pageTitle='Quote Follow-ups';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('quote_followups');
$pdo=getDB();
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $action=(string)($_POST['action']??'');
    if($action==='generate'){$n=generateQuoteFollowups($pdo);flash('success','Generated '.$n.' quote follow-ups.');}
    elseif($action==='update'){
      $status=trim((string)$_POST['status']);
      $pdo->prepare('UPDATE '.table('crm_quote_followups').' SET status=?,followup_stage=?,next_follow_up=?,last_follow_up_at=NOW(),notes=CONCAT(COALESCE(notes,""),"\n",?) WHERE id=?')->execute([$status,trim((string)$_POST['followup_stage']),trim((string)$_POST['next_follow_up'])?:null,trim((string)$_POST['notes']),(int)$_POST['id']]);
      flash('success','Quote follow-up updated.');
    }
  }catch(Throwable $e){recordSystemError($pdo,$e,['page'=>'quote-followups']);flash('error',$e->getMessage());}
  redirect(ADMIN_URL.'/erp/quote-followups.php');
}
$rows=$pdo->query('SELECT f.*,q.quotation_number,q.status quote_status,q.valid_until,c.company_name,c.contact_name FROM '.table('crm_quote_followups').' f LEFT JOIN '.table('quotations').' q ON q.id=f.quotation_id LEFT JOIN '.table('customers').' c ON c.id=f.customer_id ORDER BY FIELD(f.status,"open","waiting","won","lost"),COALESCE(f.next_follow_up,"9999-12-31") ASC,f.created_at DESC LIMIT 250')->fetchAll();
$openQuotes=$pdo->query('SELECT COUNT(*) FROM '.table('quotations').' WHERE status IN ("sent","draft","pending") AND converted_invoice_id IS NULL')->fetchColumn();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Quotation Recovery</div><h2 class="h4 mb-1">Quote Follow-ups</h2><p class="text-secondary mb-0">Automatically create and manage follow-ups for open quotations before they go cold.</p></div><form method="post"><input type="hidden" name="action" value="generate"><button class="btn btn-brand">Generate from Open Quotes (<?php echo (int)$openQuotes; ?>)</button></form></div>
<div class="table-wrap table-responsive"><table class="table align-middle"><thead><tr><th>Follow-up</th><th>Quotation</th><th>Customer</th><th>Total</th><th>Next</th><th>Status</th><th>Update</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><strong><?php echo esc($r['followup_number']); ?></strong><div class="small text-secondary"><?php echo esc($r['followup_stage']); ?></div></td><td><?php echo esc($r['quotation_number']); ?><div class="small text-secondary"><?php echo esc($r['quote_status'].' · Valid '.$r['valid_until']); ?></div></td><td><?php echo esc($r['company_name']?:$r['contact_name']); ?><div class="small"><?php echo esc($r['customer_email']); ?></div></td><td><?php echo money($r['quotation_total']); ?></td><td><?php echo esc($r['next_follow_up']); ?></td><td><span class="badge bg-<?php echo esc(statusTone($r['status'])); ?>"><?php echo esc($r['status']); ?></span></td><td><form method="post" class="row g-1"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>"><div class="col-6"><select class="form-select form-select-sm" name="status"><option>open</option><option>waiting</option><option>won</option><option>lost</option></select></div><div class="col-6"><input class="form-control form-control-sm" name="followup_stage" value="<?php echo esc($r['followup_stage']); ?>"></div><div class="col-6"><input class="form-control form-control-sm" type="date" name="next_follow_up" value="<?php echo esc($r['next_follow_up']); ?>"></div><div class="col-6"><input class="form-control form-control-sm" name="notes" placeholder="Notes"></div><div class="col-12"><button class="btn btn-sm btn-outline-primary">Save</button></div></form></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="text-secondary">No quote follow-ups yet. Click generate.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>