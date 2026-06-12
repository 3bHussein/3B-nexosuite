<?php
$pageTitle='Encrypted Database Tools';
require_once dirname(__DIR__,2) . '/includes/functions.php';
adminGuard();

if (defined('DATABASE_ENCRYPTION_TOOLS_ENABLED') && DATABASE_ENCRYPTION_TOOLS_ENABLED !== '1' && setting('database_encryption_tools_enabled','1') !== '1') {
    flash('error','Encrypted database tools are disabled.');
    redirect(ADMIN_URL.'/erp/data-import-export.php');
}

$pdo=getDB();
$user=currentUser();

function encryptedDbTableNames(PDO $pdo): array
{
    $stmt=$pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE ? ORDER BY TABLE_NAME');
    $stmt->execute([DB_PREFIX.'%']);
    return array_map('strval',$stmt->fetchAll(PDO::FETCH_COLUMN));
}

function encryptedDbBuildPlainBackup(PDO $pdo): string
{
    $tables=encryptedDbTableNames($pdo);
    $backup=[
        'format'=>'ERP-DB-JSON-1',
        'site'=>SHOP_URL,
        'db_name'=>DB_NAME,
        'db_prefix'=>DB_PREFIX,
        'created_at'=>date('c'),
        'tables'=>[],
    ];
    foreach($tables as $table){
        $rows=$pdo->query('SELECT * FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
        $backup['tables'][$table]=$rows;
    }
    return json_encode($backup, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function encryptedDbJsonToSql(string $json): string
{
    $backup=json_decode($json,true);
    if(!is_array($backup) || ($backup['format']??'')!=='ERP-DB-JSON-1'){
        throw new RuntimeException('Invalid decrypted database backup JSON.');
    }
    $sql=[];
    $sql[]='-- Encrypted ERP backup decrypted export';
    $sql[]='-- Created: '.($backup['created_at']??'');
    $sql[]='SET FOREIGN_KEY_CHECKS=0;';
    foreach(($backup['tables']??[]) as $table=>$rows){
        $table=preg_replace('/[^a-zA-Z0-9_]/','',(string)$table);
        if(!$table){continue;}
        $sql[]='TRUNCATE TABLE `'.$table.'`;';
        foreach((array)$rows as $row){
            if(!is_array($row) || !$row){continue;}
            $cols=array_keys($row);
            $colSql='`'.implode('`,`',array_map(static fn($c)=>str_replace('`','',trim((string)$c)),$cols)).'`';
            $vals=[];
            foreach($cols as $col){
                $value=$row[$col];
                if($value===null){$vals[]='NULL';}
                else{$vals[]=getDB()->quote((string)$value);}
            }
            $sql[]='INSERT INTO `'.$table.'` ('.$colSql.') VALUES ('.implode(',',$vals).');';
        }
    }
    $sql[]='SET FOREIGN_KEY_CHECKS=1;';
    return implode("\n",$sql)."\n";
}

if(isset($_GET['action']) && $_GET['action']==='export'){
    try{
        $plain=encryptedDbBuildPlainBackup($pdo);
        $encrypted=encryptPayloadString($plain);
        $file='encrypted-database-backup-'.date('Ymd-His').'.erpenc';
        logActivity($pdo,'database_encryption','export_encrypted_backup','Encrypted database backup exported.',null,null);
        writeDownloadResponse($file,$encrypted,'application/octet-stream');
    }catch(Throwable $e){
        recordSystemError($pdo,$e,['page'=>'encrypted-database-tools','action'=>'export']);
        flash('error',$e->getMessage());
        redirect(ADMIN_URL.'/erp/encrypted-database-tools.php');
    }
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        if(empty($_FILES['encrypted_file']) || (int)($_FILES['encrypted_file']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK){
            throw new RuntimeException('Upload an .erpenc encrypted backup file.');
        }
        $content=file_get_contents((string)$_FILES['encrypted_file']['tmp_name']);
        if($content===false || trim($content)===''){
            throw new RuntimeException('Cannot read uploaded encrypted file.');
        }
        $key=trim((string)($_POST['decrypt_key']??''));
        $plain=decryptPayloadString($content,$key!==''?$key:null);
        $format=(string)($_POST['output_format']??'json');
        if($format==='sql'){
            $output=encryptedDbJsonToSql($plain);
            writeDownloadResponse('decrypted-database-'.date('Ymd-His').'.sql',$output,'application/sql');
        }
        writeDownloadResponse('decrypted-database-'.date('Ymd-His').'.json',$plain,'application/json');
    }catch(Throwable $e){
        recordSystemError($pdo,$e,['page'=>'encrypted-database-tools','action'=>'decrypt']);
        flash('error',$e->getMessage());
        redirect(ADMIN_URL.'/erp/encrypted-database-tools.php');
    }
}

$tables=encryptedDbTableNames($pdo);
include dirname(__DIR__).'/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <div class="erp-kicker">Encrypted Backup</div>
    <h2 class="h4 mb-1">Encrypted Database Tools</h2>
    <p class="text-secondary mb-0">Export encrypted database backups and decrypt them when moving to hosting or restoring offline.</p>
  </div>
  <a class="btn btn-brand" href="<?php echo esc(ADMIN_URL); ?>/erp/encrypted-database-tools.php?action=export">Export Encrypted Backup</a>
</div>

<div class="alert alert-warning border-0 shadow-sm">
  <strong>Important:</strong> Keep your APP_ENCRYPTION_KEY private. Anyone with the encrypted file and key can decrypt the backup.
</div>

<div class="row g-4">
  <div class="col-xl-5">
    <div class="card-admin p-4 mb-4">
      <h2 class="h5 mb-3">Decrypt Backup File</h2>
      <form method="post" enctype="multipart/form-data">
        <label class="form-label">Encrypted .erpenc File</label>
        <input class="form-control mb-3" type="file" name="encrypted_file" required>
        <label class="form-label">Decrypt Key / Passphrase</label>
        <input class="form-control mb-3" name="decrypt_key" placeholder="Leave blank to use APP_ENCRYPTION_KEY from config.php">
        <label class="form-label">Output Format</label>
        <select class="form-select mb-3" name="output_format">
          <option value="json">Decrypted JSON backup</option>
          <option value="sql">SQL restore script</option>
        </select>
        <button class="btn btn-outline-primary w-100">Decrypt & Download</button>
      </form>
    </div>
    <div class="card-admin p-4">
      <h2 class="h5">Encryption Details</h2>
      <p class="text-secondary mb-2">Algorithm: AES-256-GCM</p>
      <p class="text-secondary mb-0">Tables included: <?php echo count($tables); ?> prefixed tables.</p>
    </div>
  </div>
  <div class="col-xl-7">
    <div class="table-wrap table-responsive">
      <h2 class="h5 mb-3">Tables Included in Encrypted Export</h2>
      <table class="table table-sm">
        <thead><tr><th>#</th><th>Table</th></tr></thead>
        <tbody>
          <?php foreach($tables as $i=>$table): ?>
            <tr><td><?php echo (int)$i+1; ?></td><td><code><?php echo esc($table); ?></code></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include dirname(__DIR__).'/footer.php'; ?>