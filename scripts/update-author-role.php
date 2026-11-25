<?php
/** CLI-only replacement of the author's role in public copy and article drafts. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
error_reporting(E_ALL & ~E_DEPRECATED);
$options=getopt('',array('root:','check','backup:','rollback:','clear-cache'));
$root=realpath($options['root'] ?? dirname(__DIR__));
if (!$root || !is_file($root.'/config.php')) { fwrite(STDERR,"AUTHOR_POLISH_FAILED=Site config not found\n");exit(1); }
define('DIAFAN',true);define('NWD_PANEL',true);define('ABSOLUTE_PATH',$root.'/');
require_once $root.'/redaktsiya/lib/db.php';
require_once dirname(__DIR__).'/redaktsiya/lib/author-role.php';
function rolePublicTables() { return array('site','shop','shop_category','news','news_category','clauses','clauses_category','bs','bs_elements','menu','images','panel_article'); }
function roleField($table,$field) {
    return $table==='panel_article' ? in_array($field,array('title','payload','revision'),true) : (bool)preg_match('/^(?:name|title|title_meta|text|anons|descr|description|keywords|alt)[0-9]*$/D',$field);
}
function roleBackupPath($path,$root) {
    $dir=realpath(dirname($path));
    if (!$dir || strpos($dir.DIRECTORY_SEPARATOR,$root.DIRECTORY_SEPARATOR)===0 || is_link($path)) { throw new RuntimeException('Backup must be outside the document root'); }
    return $dir.'/'.basename($path);
}
function roleCache($root) {
    if (defined('CACHE_MEMCACHED') && CACHE_MEMCACHED) { throw new RuntimeException('Native Memcached cache reset is required'); }
    foreach (array('cache','tmp/cache') as $dir) {
        $path=$root.'/'.$dir;if (!is_dir($path) || is_link($path)) { continue; }
        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path,FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) { if ($file->isFile() && !$file->isLink() && !in_array($file->getFilename(),array('.htaccess','.gitkeep','index.php'),true) && !unlink($file->getPathname())) { throw new RuntimeException('Cache is not writable'); } }
    }
}
function roleApply(array $row,$reverse=false) {
    if (!in_array($row['table'],rolePublicTables(),true)) { throw new RuntimeException('Invalid backup table'); }
    $before=$reverse ? $row['after'] : $row['before'];$after=$reverse ? $row['before'] : $row['after'];
    $set=array();$where=array('id=?');$values=array();$old=array($row['id']);
    foreach ($after as $field=>$value) {
        if (!roleField($row['table'],$field) || !array_key_exists($field,$before)) { throw new RuntimeException('Invalid backup field'); }
        $set[]='`'.$field.'`=?';$values[]=$value;
        $where[]='BINARY `'.$field.'` <=> BINARY ?';$old[]=$before[$field];
    }
    $stmt=panelQuery('UPDATE '.panelTable($row['table']).' SET '.implode(',',$set).' WHERE '.implode(' AND ',$where),array_merge($values,$old));
    if ($stmt->affected_rows!==1) { throw new RuntimeException('Content changed during update: '.$row['table']); }
}
$stage='initialization';
try {
    if (isset($options['clear-cache'])) { roleCache($root);echo "AUTHOR_POLISH_CACHE_OK\n";exit; }
    $db=panelDb();
    if (defined('CACHE_MEMCACHED') && CACHE_MEMCACHED) { throw new RuntimeException('Native Memcached cache reset is required'); }
    $db->begin_transaction();
    if (isset($options['rollback'])) {
        $stage='rollback';$path=roleBackupPath($options['rollback'],$root);
        $saved=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
        if (($saved['feature'] ?? '')!=='author-polish' || !is_array($saved['changes'] ?? null)) { throw new RuntimeException('Invalid rollback file'); }
        foreach (array_reverse($saved['changes']) as $row) { roleApply($row,true); }
        $db->commit();roleCache($root);echo "AUTHOR_POLISH_ROLLBACK_OK\n";exit;
    }
    $stage='schema';$tables=array();
    foreach (panelRows('SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()') as $table) { $tables[$table['TABLE_NAME']]=$table['ENGINE']; }
    if (!isset($tables[DB_PREFIX.'site'])) { throw new RuntimeException('DIAFAN site table not found'); }
    $changes=array();$scanned=0;$seo=0;
    foreach (rolePublicTables() as $table) {
        if (!isset($tables[DB_PREFIX.$table])) { continue; }
        $columns=array();$hasId=false;
        foreach (panelRows('SHOW COLUMNS FROM '.panelTable($table)) as $col) {
            if ($col['Field']==='id') { $hasId=true; }
            if (roleField($table,$col['Field'])) { $columns[$col['Field']]=$col['Type']; }
        }
        if (!$hasId || !$columns) { continue; }
        $stage='scan-'.$table;$scanned++;
        $names=array_keys($columns);$sql='SELECT id,`'.implode('`,`',$names).'` FROM '.panelTable($table).' FOR UPDATE';
        foreach (panelRows($sql) as $row) {
            $before=array();$after=array();
            foreach ($columns as $field=>$type) {
                if ($row[$field]===null || $field==='revision') { continue; }
                $value=$field==='payload' ? nwdAuthorRolePayload($row[$field]) : nwdReplaceAuthorRole($row[$field]);
                if ($value===$row[$field]) { continue; }
                if (preg_match('/^(?:var)?char\((\d+)\)/i',$type,$limit) && mb_strlen($value,'UTF-8')>(int)$limit[1]) { throw new RuntimeException('Replacement exceeds field limit: '.$table.'.'.$field); }
                $before[$field]=$row[$field];$after[$field]=$value;
                if (strpos($field,'title_meta')===0) { $seo++; }
            }
            if (!$after) { continue; }
            if (strtoupper($tables[DB_PREFIX.$table])!=='INNODB') { throw new RuntimeException('Transactional table is required: '.$table); }
            if ($table==='panel_article' && isset($row['revision'])) { $before['revision']=(int)$row['revision'];$after['revision']=(int)$row['revision']+1; }
            $changes[]=array('table'=>$table,'id'=>$row['id'],'before'=>$before,'after'=>$after);
        }
    }
    echo 'AUTHOR_POLISH_MATCHED_ROWS='.count($changes).' SEO_TITLES='.$seo.' TABLES='.$scanned."\n";
    if (isset($options['check'])) { $db->rollback();echo "AUTHOR_POLISH_PREFLIGHT_OK\n";exit; }
    if (empty($options['backup'])) { throw new RuntimeException('Private --backup file is required'); }
    $stage='backup';$path=roleBackupPath($options['backup'],$root);
    $saved=json_encode(array('feature'=>'author-polish','created_at'=>gmdate('c'),'changes'=>$changes),JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    $file=fopen($path,'x');if (!$file) { throw new RuntimeException('Cannot create private backup'); }
    chmod($path,0600);$written=fwrite($file,$saved);fclose($file);if ($written!==strlen($saved)) { throw new RuntimeException('Cannot write private backup'); }
    $stage='update';foreach ($changes as $change) { roleApply($change); }
    $db->commit();$stage='cache';roleCache($root);
    echo 'AUTHOR_POLISH_DATABASE_OK='.count($changes)."\n";
} catch (Throwable $e) {
    if (isset($db)) { try { $db->rollback(); } catch (Throwable $ignored) {} }
    $message=$e instanceof mysqli_sql_exception ? 'Database operation failed MYSQL_CODE='.(int)$e->getCode() : $e->getMessage();
    fwrite(STDERR,'AUTHOR_POLISH_FAILED='.$message.' STAGE='.$stage."\n");exit(1);
}
