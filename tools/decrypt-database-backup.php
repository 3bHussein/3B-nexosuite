<?php
/**
 * CLI decrypt tool for ERP encrypted database backups.
 *
 * Usage:
 * php tools/decrypt-database-backup.php backup.erpenc "your-key-or-passphrase" output.json
 */
if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}
if ($argc < 4) {
    exit("Usage: php tools/decrypt-database-backup.php backup.erpenc \"key\" output.json\n");
}
$input=$argv[1];
$key=$argv[2];
$output=$argv[3];

if (!is_file($input)) {
    exit("Input file not found.\n");
}
if (!extension_loaded('openssl')) {
    exit("OpenSSL extension is required.\n");
}
$payload=json_decode((string)file_get_contents($input), true);
if(!is_array($payload) || ($payload['format']??'')!=='ERPENC-1'){
    exit("Invalid encrypted backup format.\n");
}
$keyBytes=base64_decode($key,true);
if($keyBytes===false || strlen($keyBytes)<32){
    $keyBytes=hash('sha256',$key,true);
}else{
    $keyBytes=substr($keyBytes,0,32);
}
$iv=base64_decode((string)($payload['iv']??''),true);
$tag=base64_decode((string)($payload['tag']??''),true);
$data=base64_decode((string)($payload['data']??''),true);
$plain=openssl_decrypt($data,'aes-256-gcm',$keyBytes,OPENSSL_RAW_DATA,$iv,$tag);
if($plain===false){
    exit("Decryption failed. Check key.\n");
}
file_put_contents($output,$plain);
echo "Decrypted backup written to {$output}\n";