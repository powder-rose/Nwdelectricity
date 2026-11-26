<?php
/** CLI-only update of the existing native cables record; no production page creation. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$options=getopt('',array('root:','check','backup:','rollback:','clear-cache','prepare-local-forms','bootstrap-local'));
$root=realpath($options['root'] ?? dirname(__DIR__));
if (!$root || !is_file($root.'/config.php')) { fwrite(STDERR,"CABLES_FAILED=Site config not found\n");exit(1); }
define('DIAFAN',true);define('NWD_PANEL',true);define('ABSOLUTE_PATH',$root.'/');
require_once $root.'/redaktsiya/lib/db.php';
$copy=require dirname(__DIR__).'/custom/energo/themes/cables_content.php';
require_once __DIR__.'/phase-neutral-schema.php';require_once __DIR__.'/cables-page.php';
$stage='initialization';
function fnCacheClear($root) {
    // DIAFAN file caches are generated PHP/serialized entries. Preserve access rules/placeholders.
    foreach (array('cache','tmp/cache') as $dir) {
        $path=$root.'/'.$dir;
        if (!is_dir($path) || is_link($path)) { continue; }
        $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if ($file->isLink()) { continue; }
            if ($file->isFile() && !in_array($file->getFilename(),array('.htaccess','.gitkeep','index.php'),true)) { if (!unlink($file->getPathname())) { throw new RuntimeException('Cache is not writable'); } }
        }
    }
}
function fnPrivateBackup($path,$root) {
    $dir=realpath(dirname($path));
    if (!$dir || strpos($dir.DIRECTORY_SEPARATOR,$root.DIRECTORY_SEPARATOR)===0 || is_link($path)) { throw new RuntimeException('Backup must be outside the document root'); }
    return $dir.'/'.basename($path);
}
try {
    if(isset($options['clear-cache'])){fnCacheClear($root);echo "CABLES_CACHE_OK\n";exit;}
    $db=panelDb();$lang=panelLanguage();
    if(isset($options['prepare-local-forms'])){nwdPrepareLocalFeedbackSchema($db,DB_PREFIX,getenv('NWD_LOCAL')==='1');exit;}
    if(defined('CACHE_MEMCACHED') && CACHE_MEMCACHED){throw new RuntimeException('Use the native cache reset before installing with Memcached');}
    $db->begin_transaction();
    if(isset($options['rollback'])){
        $file=fnPrivateBackup($options['rollback'],$root);$saved=json_decode(file_get_contents($file),true);
        if(!$saved){throw new RuntimeException('Invalid backup');}
        nwdCblRollback($copy,$saved);$db->commit();fnCacheClear($root);echo "CABLES_ROLLBACK_OK\n";exit;
    }
    $stage='native-form';$missing=nwdFeedbackSchemaMissing($db,DB_PREFIX);
    if($missing){throw new RuntimeException('Missing native form tables: '.implode(',',$missing));}
    $form=panelRow('SELECT id FROM '.panelTable('site')." WHERE id=27 AND module_name='feedback' AND trash='0'");
    $types=panelRows('SELECT type FROM '.panelTable('feedback_param')." WHERE site_id=27 AND trash='0'");
    if(!$form){throw new RuntimeException('Native feedback form 27 not found');}
    foreach(array('text','phone','email','textarea') as $type){if(!in_array($type,array_column($types,'type'),true)){throw new RuntimeException('Native form requires '.$type);}}
    $stage='related-links';nwdCblRelated($lang,getenv('NWD_LOCAL')==='1');
    $stage='existing-route';$record=nwdCblInstall($copy,$lang,isset($options['bootstrap-local']),isset($options['check']));
    if(isset($options['check'])){$db->rollback();echo "CABLES_PREFLIGHT_OK\n";exit;}
    if(empty($options['backup'])){throw new RuntimeException('Private --backup file is required');}
    $backup=fnPrivateBackup($options['backup'],$root);$handle=fopen($backup,'x');if(!$handle){throw new RuntimeException('Cannot create private backup');}chmod($backup,0600);
    $encoded=json_encode($record,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);$written=fwrite($handle,$encoded);fclose($handle);
    if($written!==strlen($encoded)){throw new RuntimeException('Cannot write complete rollback record');}
    $db->commit();$stage='cache';fnCacheClear($root);echo 'CABLES_PAGE_ID='.$record['id']."\nCABLES_DATABASE_OK\n";
} catch(Throwable $error){
    if(isset($db)){try{$db->rollback();}catch(Throwable $ignored){}}
    $message=$error instanceof mysqli_sql_exception ? 'Database operation failed MYSQL_CODE='.(int)$error->getCode():$error->getMessage();
    fwrite(STDERR,'CABLES_FAILED='.$message.' STAGE='.$stage."\n");exit(1);
}
