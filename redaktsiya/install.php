<?php
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
define('NWD_PANEL',true);
foreach (array('db','schema','content','publication','scheduling','articles') as $file) { require __DIR__.'/lib/'.$file.'.php'; }
try {
    if (in_array('--sample',$argv,true) && getenv('NWD_LOCAL')!=='1') {
        panelFail('Демонстрационная статья предназначена для локальной копии.');
    }
    panelDb();
    if (in_array('--statistics', $argv, true)) {
        panelInstall(true);
        fwrite(STDOUT, "STATISTICS_READY\n");
        exit;
    }
    panelInstall();
    $state=panelTable('panel_state');
    $password=null;
    if (!panelRow("SELECT id FROM $state WHERE id=1") || in_array('--reset-password',$argv,true)) {
        $candidate=bin2hex(random_bytes(12));
        $hash=password_hash($candidate,PASSWORD_DEFAULT);
        $generation=bin2hex(random_bytes(16));
        if (in_array('--reset-password',$argv,true)) {
            panelQuery("INSERT INTO $state (id,password_hash,generation) VALUES (1,?,?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),generation=VALUES(generation)",array($hash,$generation));
            $password=$candidate;
        } else {
            $insert=panelQuery("INSERT IGNORE INTO $state (id,password_hash,generation) VALUES (1,?,?)",array($hash,$generation));
            if ($insert->affected_rows===1) { $password=$candidate; }
        }
    }
    if ($password!==null) { fwrite(STDOUT,"EDITOR_PASSWORD=".$password."\n"); }
    if (in_array('--sample',$argv,true)) {
        $key='75024c68-350b-46dc-9536-957bcf25cd48';
        if (!panelRow('SELECT id FROM '.panelTable('panel_article').' WHERE request_id=?',array($key))) {
            $form=require __DIR__.'/lib/sample.php';
            panelSave(array('form'=>$form,'requestId'=>$key));
        }
    }
    fwrite(STDOUT,"PANEL_READY\n");
} catch (Throwable $e) {
    fwrite(STDERR,($e instanceof PanelError?$e->getMessage():'Panel setup failed ('.get_class($e).' '.(int)$e->getCode().').')."\n");exit(1);
}
