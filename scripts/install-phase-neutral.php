<?php
/** CLI-only, additive native DIAFAN page registration. No existing page content is replaced. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$options=getopt('',array('root:','check','backup:','rollback:','clear-cache','prepare-local-forms'));
$root=realpath($options['root'] ?? dirname(__DIR__));
if (!$root || !is_file($root.'/config.php')) { fwrite(STDERR,"PHASE_NEUTRAL_FAILED=Site config not found\n");exit(1); }
define('DIAFAN',true);define('NWD_PANEL',true);define('ABSOLUTE_PATH',$root.'/');
require_once $root.'/redaktsiya/lib/db.php';
$copy=require dirname(__DIR__).'/custom/energo/themes/phase_neutral_content.php';
require_once __DIR__.'/phase-neutral-schema.php';
$stage='initialization';
function fnPageFingerprint(array $row, $lang) {
    $values=array();
    foreach (array('theme','name'.$lang,'title_meta'.$lang,'descr'.$lang,'text'.$lang,'canonical'.$lang,'act'.$lang,'trash','timeedit') as $key) { $values[$key]=$row[$key] ?? null; }
    return hash('sha256',json_encode($values,JSON_UNESCAPED_UNICODE));
}
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
    if (isset($options['clear-cache'])) { fnCacheClear($root);echo "PHASE_NEUTRAL_CACHE_OK\n";exit; }
    $stage='database-connection';$db=panelDb();
    if (isset($options['prepare-local-forms'])) {
        if (isset($options['check']) || isset($options['rollback'])) { throw new RuntimeException('Run local preparation as a separate command'); }
        $stage='local-feedback-schema';nwdPrepareLocalFeedbackSchema($db,DB_PREFIX,getenv('NWD_LOCAL')==='1');exit;
    }
    $stage='language';$lang=panelLanguage();
    $stage='feedback-schema';$missing=nwdFeedbackSchemaMissing($db,DB_PREFIX);
    if ($missing && !isset($options['rollback'])) { throw new RuntimeException('Required feedback tables missing: '.implode(',',$missing).'. Prepare the local form schema before installing.'); }
    $stage='native-form';
    $form=panelRow('SELECT id FROM '.panelTable('site')." WHERE id=27 AND module_name='feedback' AND trash='0'");
    $types=panelRows('SELECT type FROM '.panelTable('feedback_param')." WHERE site_id=27 AND trash='0'");
    foreach (array('text','phone','email','textarea') as $type) { if (!in_array($type,array_column($types,'type'),true)) { throw new RuntimeException('Native feedback form 27 requires '.$type); } }
    if (!$form) { throw new RuntimeException('Native feedback form 27 not found'); }
    if (defined('CACHE_MEMCACHED') && CACHE_MEMCACHED) { throw new RuntimeException('Memcached is enabled; use the native cache reset before installation'); }
    $db->begin_transaction();
    if (isset($options['rollback'])) {
        $file=fnPrivateBackup($options['rollback'],$root);
        $saved=json_decode(file_get_contents($file),true);
        if (!$saved || ($saved['path'] ?? '')!==$copy['path']) { throw new RuntimeException('Invalid rollback file'); }
        if (!empty($saved['created'])) {
            $id=(int)$saved['id'];$row=panelRow('SELECT * FROM '.panelTable('site').' WHERE id=? FOR UPDATE',array($id));
            if ($row && (!isset($saved['fingerprint']) || !hash_equals($saved['fingerprint'],fnPageFingerprint($row,$lang)))) { throw new RuntimeException('Page changed after installation'); }
            panelQuery('DELETE FROM '.panelTable('map_index')." WHERE module_name='site' AND element_type='element' AND element_id=?",array($id));
            panelQuery('DELETE FROM '.panelTable('rewrite')." WHERE module_name='site' AND element_type='element' AND element_id=? AND rewrite=?",array($id,$copy['path']));
            panelQuery('DELETE FROM '.panelTable('site_parents').' WHERE element_id=?',array($id));
            panelQuery('DELETE FROM '.panelTable('site').' WHERE id=?',array($id));
        }
        $db->commit();fnCacheClear($root);echo "PHASE_NEUTRAL_ROLLBACK_OK\n";exit;
    }
    $stage='route-preflight';
    $parent=panelRow('SELECT s.id FROM '.panelTable('site').' s JOIN '.panelTable('rewrite')." r ON r.element_id=s.id AND r.module_name='site' AND r.element_type='element' WHERE r.rewrite='services' AND s.trash='0' FOR UPDATE");
    if (!$parent) { throw new RuntimeException('Services parent route not found'); }
    $existing=panelRow('SELECT r.module_name,r.element_type,r.element_id,s.* FROM '.panelTable('rewrite').' r LEFT JOIN '.panelTable('site')." s ON s.id=r.element_id AND r.module_name='site' WHERE r.rewrite=? FOR UPDATE",array($copy['path']));
    $redirect=panelRow('SELECT id FROM '.panelTable('redirect').' WHERE redirect IN (?,?) LIMIT 1',array($copy['path'],$copy['path'].'/'));
    if ($redirect) { throw new RuntimeException('Route conflicts with an existing redirect'); }
    if ($existing && ($existing['theme']!=='phase_neutral.php' || $existing['element_type']!=='element' || $existing['trash']!=='0' || $existing['name'.$lang]!==panelHtml($copy['name']) || $existing['title_meta'.$lang]!==panelHtml($copy['seoTitle']) || $existing['descr'.$lang]!==panelHtml($copy['description']) || $existing['act'.$lang]!=='1' || $existing['canonical'.$lang]!==$copy['path'].'/' || $existing['noindex']!=='0' || $existing['map_no_show']!=='0' || $existing['access']!=='0')) { throw new RuntimeException('Route is occupied or page was edited in DIAFAN'); }
    if (isset($options['check'])) { $db->rollback();echo "PHASE_NEUTRAL_PREFLIGHT_OK\n";exit; }
    if (empty($options['backup'])) { throw new RuntimeException('Private --backup file is required'); }
    $backup=fnPrivateBackup($options['backup'],$root);
    if (file_exists($backup)) { throw new RuntimeException('Backup file already exists'); }
    $id=$existing ? (int)$existing['element_id'] : 0;
    $stage='page-registration';
    if (!$id) {
        panelQuery('INSERT INTO '.panelTable('site')." SET parent_id=?,theme='phase_neutral.php',name$lang=?,title_meta$lang=?,descr$lang=?,text$lang='',act$lang='1',noindex='0',map_no_show='0',access='0',canonical$lang=?,timeedit=?,changefreq='monthly'",array((int)$parent['id'],panelHtml($copy['name']),panelHtml($copy['seoTitle']),panelHtml($copy['description']),$copy['path'].'/',time()));
        $id=$db->insert_id;
        panelQuery('INSERT INTO '.panelTable('rewrite')." (rewrite,module_name,element_id,element_type) VALUES (?,'site',?,'element')",array($copy['path'],$id));
        panelQuery('INSERT INTO '.panelTable('site_parents').' (element_id,parent_id) VALUES (?,?)',array($id,(int)$parent['id']));
        // Include all ancestors, as the native site tree expects.
        foreach (panelRows('SELECT parent_id FROM '.panelTable('site_parents').' WHERE element_id=?',array((int)$parent['id'])) as $ancestor) {
            if ((int)$ancestor['parent_id']!==(int)$parent['id']) { panelQuery('INSERT INTO '.panelTable('site_parents').' (element_id,parent_id) VALUES (?,?)',array($id,(int)$ancestor['parent_id'])); }
        }
        panelQuery('INSERT INTO '.panelTable('map_index')." (url,module_name,element_id,element_type,timeedit,changefreq,priority,date_start,date_finish) VALUES (?,'site',?,'element',?,'monthly','0.7',0,0)",array($copy['path'].'ROUTE_END',$id,time()));
    }
    $registered=panelRow('SELECT * FROM '.panelTable('site').' WHERE id=?',array($id));
    $record=array('path'=>$copy['path'],'id'=>$id,'created'=>!$existing,'fingerprint'=>fnPageFingerprint($registered,$lang),'created_at'=>gmdate('c'));
    $handle=fopen($backup,'x');if (!$handle) { throw new RuntimeException('Cannot create private backup'); }
    chmod($backup,0600);$written=fwrite($handle,json_encode($record,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));fclose($handle);
    if (!$written) { throw new RuntimeException('Cannot write rollback record'); }
    $db->commit();$stage='cache';fnCacheClear($root);
    echo 'PHASE_NEUTRAL_PAGE_ID='.$id."\nPHASE_NEUTRAL_DATABASE_OK\n";
} catch (Throwable $error) {
    if (isset($db)) { try { $db->rollback(); } catch (Throwable $ignored) {} }
    // Keep DSNs, credentials and SQL details out of console output.
    $message=$error instanceof mysqli_sql_exception ? 'Database operation failed MYSQL_CODE='.(int)$error->getCode() : $error->getMessage();
    fwrite(STDERR,'PHASE_NEUTRAL_FAILED='.$message.' STAGE='.$stage."\n");exit(1);
}
