<?php
$pageTitle='Data Import / Export';
require_once dirname(__DIR__,2) . '/includes/functions.php';
erpGuard('data_import_export');
$pdo=getDB();
$scope=operationalScope($pdo);
$user=currentUser();

if(isset($_GET['sample'])){
  $sample=trim((string)$_GET['sample']);
  if($sample==='customers'){
    writeCsvResponse('customers-import-demo.csv',[
      ['customer_code'=>'CUST-DEMO-001','customer_type'=>'b2b','company_name'=>'Demo Garage LLC','contact_name'=>'Ahmed Khan','email'=>'accounts@demogarage.example','phone'=>'+971501234567','credit_limit'=>'15000'],
      ['customer_code'=>'CUST-DEMO-002','customer_type'=>'b2c','company_name'=>'','contact_name'=>'Sara Ali','email'=>'sara@example.com','phone'=>'+971551112233','credit_limit'=>'0'],
    ]);
  }
  if($sample==='suppliers'){
    writeCsvResponse('suppliers-import-demo.csv',[
      ['supplier_code'=>'SUP-DEMO-001','company_name'=>'Demo Parts Supplier LLC','contact_name'=>'Mohammed Faisal','email'=>'sales@demoparts.example','phone'=>'+971504445566'],
      ['supplier_code'=>'SUP-DEMO-002','company_name'=>'Demo Logistics FZE','contact_name'=>'Priya Menon','email'=>'ops@demologistics.example','phone'=>'+971526667788'],
    ]);
  }
  flash('error','Unknown sample CSV type.');redirect(ADMIN_URL.'/erp/data-import-export.php');
}

if(isset($_GET['export'])){
  $type=trim((string)$_GET['export']);
  if($type==='products'){
    $rows=$pdo->query('SELECT sku,name,price,cost_price,stock,active FROM '.table('products').' ORDER BY id DESC')->fetchAll();
    writeCsvResponse('products-export-'.date('Ymd-His').'.csv',$rows);
  }
  if($type==='customers'){
    $rows=$pdo->query('SELECT customer_code,customer_type,company_name,contact_name,email,phone,credit_limit,credit_status,status FROM '.table('customers').' ORDER BY id DESC')->fetchAll();
    writeCsvResponse('customers-export-'.date('Ymd-His').'.csv',$rows);
  }
  if($type==='suppliers'){
    $rows=$pdo->query('SELECT supplier_code,company_name,contact_name,email,phone,status FROM '.table('suppliers').' ORDER BY id DESC')->fetchAll();
    writeCsvResponse('suppliers-export-'.date('Ymd-His').'.csv',$rows);
  }
  if($type==='inventory_value'){writeCsvResponse('inventory-value-'.date('Ymd-His').'.csv',reportRows($pdo,'inventory_value',['limit'=>(int)setting('report_export_max_rows','5000')]));}
  flash('error','Unknown export type.');redirect(ADMIN_URL.'/erp/data-import-export.php');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $importType=trim((string)($_POST['import_type']??'customers'));
  $pdo->beginTransaction();
  try{
    $upload=uploadAdminDocument('csv_file','documents',10485760);
    if(!$upload){throw new RuntimeException('Upload a CSV file.');}
    $stored=ensureUploadDirectory('documents').'/'.$upload['stored_path'];
    $job=$pdo->prepare('INSERT INTO '.table('data_import_jobs').' (company_id,branch_id,import_type,file_name,status,created_by,started_at) VALUES (?,?,?,?, "processing", ?, NOW())');
    $job->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,$importType,$upload['file_name'],(int)($user['id']??0)?:null]);
    $jobId=(int)$pdo->lastInsertId();
    $handle=fopen($stored,'r');
    if(!$handle){throw new RuntimeException('Cannot read uploaded CSV.');}
    $firstLine=fgets($handle);
    if($firstLine===false || trim($firstLine)===''){throw new RuntimeException('CSV header row is required.');}
    $delimiters=[','=>substr_count($firstLine,','),';'=>substr_count($firstLine,';'),"\t"=>substr_count($firstLine,"\t")];
    arsort($delimiters);
    $delimiter=(string)array_key_first($delimiters);
    if(($delimiters[$delimiter]??0)<=0){$delimiter=',';}
    $headers=str_getcsv($firstLine,$delimiter);
    if(!$headers){throw new RuntimeException('CSV header row is required.');}
    $headers=array_map(static function($h){
      $h=preg_replace('/^\xEF\xBB\xBF/','',(string)$h);
      return strtolower(trim($h));
    },$headers);
    $headers=array_values(array_filter($headers,static fn($h)=>$h!==''));
    if(!$headers){throw new RuntimeException('CSV header row has no valid columns.');}
    $rowNo=1;$success=0;$failed=0;$rowInsert=$pdo->prepare('INSERT INTO '.table('data_import_rows').' (data_import_job_id,row_number,status,source_json,error_message) VALUES (?,?,?,?,?)');
    while(($data=fgetcsv($handle,0,$delimiter))!==false){
      $rowNo++;
      if(count($data)===1 && trim((string)($data[0]??''))===''){continue;}
      $values=array_slice(array_pad($data,count($headers),''),0,count($headers));
      $source=array_combine($headers,$values);
      if($source===false){$source=[];}
      $source=array_map(static fn($value)=>trim((string)$value),$source);
      try{
        if($importType==='customers'){
          $code=trim((string)($source['customer_code']??'')) ?: 'CUST-'.date('YmdHis').'-'.$rowNo;
          $stmt=$pdo->prepare('INSERT INTO '.table('customers').' (company_id,branch_id,customer_code,customer_type,company_name,contact_name,email,phone,credit_limit,status) VALUES (?,?,?,?,?,?,?,?,?,"active") ON DUPLICATE KEY UPDATE company_name=VALUES(company_name),contact_name=VALUES(contact_name),email=VALUES(email),phone=VALUES(phone),credit_limit=VALUES(credit_limit)');
          $stmt->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,$code,trim((string)($source['customer_type']??'b2b'))?:'b2b',trim((string)($source['company_name']??'')),trim((string)($source['contact_name']??$source['name']??'Customer')),trim((string)($source['email']??'')),trim((string)($source['phone']??'')),(float)($source['credit_limit']??0)]);
        }elseif($importType==='suppliers'){
          $code=trim((string)($source['supplier_code']??'')) ?: 'SUP-'.date('YmdHis').'-'.$rowNo;
          $stmt=$pdo->prepare('INSERT INTO '.table('suppliers').' (company_id,branch_id,supplier_code,company_name,contact_name,email,phone,status) VALUES (?,?,?,?,?,?,?,"active") ON DUPLICATE KEY UPDATE company_name=VALUES(company_name),contact_name=VALUES(contact_name),email=VALUES(email),phone=VALUES(phone)');
          $stmt->execute([(int)($scope['company_id']??0)?:null,(int)($scope['branch_id']??0)?:null,$code,trim((string)($source['company_name']??'')),trim((string)($source['contact_name']??'')),trim((string)($source['email']??'')),trim((string)($source['phone']??''))]);
        }else{
          throw new RuntimeException('Only customer and supplier CSV import are enabled in this screen.');
        }
        $success++;$rowInsert->execute([$jobId,$rowNo,'success',json_encode($source,JSON_UNESCAPED_SLASHES),'']);
      }catch(Throwable $rowError){
        $failed++;$rowInsert->execute([$jobId,$rowNo,'failed',json_encode($source,JSON_UNESCAPED_SLASHES),$rowError->getMessage()]);
      }
    }
    fclose($handle);
    $pdo->prepare('UPDATE '.table('data_import_jobs').' SET status=?,rows_total=?,rows_success=?,rows_failed=?,finished_at=NOW() WHERE id=?')->execute([$failed>0?'completed_with_errors':'completed',$success+$failed,$success,$failed,$jobId]);
    logActivity($pdo,'Data','import_job_completed','Data import job #'.$jobId.' completed: '.$success.' success, '.$failed.' failed.','data_import_job',$jobId);
    $pdo->commit();flash('success','Import completed: '.$success.' successful, '.$failed.' failed.');
  }catch(Throwable $e){
    if($pdo->inTransaction()){$pdo->rollBack();}
    flash('error',$e->getMessage());
  }
  redirect(ADMIN_URL.'/erp/data-import-export.php');
}
$jobs=$pdo->query('SELECT dij.*,u.email created_by_email FROM '.table('data_import_jobs').' dij LEFT JOIN '.table('users').' u ON u.id=dij.created_by ORDER BY dij.created_at DESC LIMIT 100')->fetchAll();
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><div class="erp-kicker">Data Operations</div><h2 class="h4 mb-1">Data Import / Export</h2><p class="text-secondary mb-0">Controlled CSV imports and exports for migration, BI, marketplace preparation, and audit review.</p></div></div>
<div class="row g-4 mb-4">
  <div class="col-xl-4"><form method="post" enctype="multipart/form-data" class="card-admin p-4"><div class="erp-kicker">CSV Import</div><h2 class="h5 mb-3">Import Master Data</h2><label class="form-label">Import Type</label><select class="form-select mb-3" name="import_type"><option value="customers">Customers</option><option value="suppliers">Suppliers</option></select><label class="form-label">CSV File</label><input class="form-control mb-3" type="file" name="csv_file" accept=".csv,text/csv,application/vnd.ms-excel" required><div class="small text-secondary mb-3">Upload a normal .csv file. Comma, semicolon, and tab-delimited CSV files are accepted. Extra columns are ignored and missing columns are filled blank.</div><div class="d-flex flex-wrap gap-2 mb-3"><a class="btn btn-sm btn-outline-primary" href="?sample=customers">Download Customer Demo CSV</a><a class="btn btn-sm btn-outline-primary" href="?sample=suppliers">Download Supplier Demo CSV</a></div><div class="small text-secondary mb-3"><strong>Customer columns:</strong> customer_code, customer_type, company_name, contact_name, email, phone, credit_limit.<br><strong>Supplier columns:</strong> supplier_code, company_name, contact_name, email, phone.</div><button class="btn btn-brand w-100">Upload & Process</button></form></div>
  <div class="col-xl-8"><div class="card-admin p-4"><div class="erp-kicker">CSV Export</div><h2 class="h5 mb-3">Export Live Data</h2><div class="row g-3"><div class="col-md-6"><a class="btn btn-outline-primary w-100" href="?export=products">Export Products</a></div><div class="col-md-6"><a class="btn btn-outline-primary w-100" href="?export=customers">Export Customers</a></div><div class="col-md-6"><a class="btn btn-outline-primary w-100" href="?export=suppliers">Export Suppliers</a></div><div class="col-md-6"><a class="btn btn-outline-primary w-100" href="?export=inventory_value">Export Inventory Valuation</a></div></div></div></div>
</div>
<div class="table-wrap table-responsive"><div class="table-toolbar"><div><div class="erp-kicker">Import History</div><h2 class="h5 mb-0">Recent Import Jobs</h2></div></div><table class="table align-middle"><thead><tr><th>Job</th><th>Type</th><th>File</th><th>Rows</th><th>Status</th><th>User</th><th>Date</th></tr></thead><tbody><?php foreach($jobs as $job): ?><tr><td>#<?php echo (int)$job['id']; ?></td><td><?php echo esc($job['import_type']); ?></td><td><?php echo esc($job['file_name']); ?></td><td><?php echo (int)$job['rows_success']; ?> success / <?php echo (int)$job['rows_failed']; ?> failed</td><td><span class="badge bg-<?php echo esc(statusTone($job['status'])); ?>"><?php echo esc(str_replace('_',' ',ucwords($job['status'],'_'))); ?></span></td><td><?php echo esc($job['created_by_email']?:'System'); ?></td><td><?php echo esc($job['created_at']); ?></td></tr><?php endforeach; ?><?php if(!$jobs): ?><tr><td colspan="7" class="text-secondary">No import jobs yet.</td></tr><?php endif; ?></tbody></table></div>
<?php include dirname(__DIR__).'/footer.php'; ?>