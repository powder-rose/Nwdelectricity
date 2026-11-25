<?php
/** CLI-only setup for local databases restored from backups predating the lead queues. */
if (PHP_SAPI!=='cli' || !defined('NWD_PANEL')) { http_response_code(404);exit; }

function nwdLocalFeedbackSchemaDefinitions() {
    return array(
        'nwd_feedback_request'=>array(
            'columns'=>array('request_id','digest','feedback_id','mail_id','created'),
            'definition'=>"request_id CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY, digest CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, feedback_id INT UNSIGNED NOT NULL DEFAULT 0, mail_id VARCHAR(255) NULL DEFAULT NULL, created INT UNSIGNED NOT NULL, KEY created (created)"
        ),
        'boykov_lead_outbox'=>array(
            'columns'=>array('event_id','payload','payload_hash','queued_at'),
            'definition'=>"event_id VARCHAR(96) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY, payload MEDIUMTEXT NOT NULL, payload_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, queued_at INT UNSIGNED NOT NULL, KEY queued_at (queued_at)"
        )
    );
}

function nwdFeedbackSchemaTable($prefix,$name) {
    if (!preg_match('/^[A-Za-z0-9_]*$/D',$prefix) || !isset(nwdLocalFeedbackSchemaDefinitions()[$name])) {
        throw new RuntimeException('Invalid feedback schema identifier');
    }
    return '`'.$prefix.$name.'`';
}

/** Inspects columns without reading lead rows or changing the database. */
function nwdFeedbackSchemaMissing($db,$prefix) {
    $missing=array();
    foreach (nwdLocalFeedbackSchemaDefinitions() as $name=>$spec) {
        $table=nwdFeedbackSchemaTable($prefix,$name);
        try { $result=$db->query('SELECT '.implode(',',$spec['columns']).' FROM '.$table.' LIMIT 0');$result->free(); }
        catch (mysqli_sql_exception $error) {
            if ((int)$error->getCode()===1146) { $missing[]=$name;continue; }
            throw new RuntimeException('Feedback table check failed: '.$name.' MYSQL_CODE='.(int)$error->getCode(),(int)$error->getCode());
        }
    }
    return $missing;
}

/** Missing local tables only: no ALTER, DROP, TRUNCATE, DELETE or external delivery. */
function nwdPrepareLocalFeedbackSchema($db,$prefix,$isLocal) {
    if (!$isLocal) { throw new RuntimeException('Local schema preparation requires NWD_LOCAL=1'); }
    $missing=nwdFeedbackSchemaMissing($db,$prefix);
    $definitions=nwdLocalFeedbackSchemaDefinitions();
    foreach ($missing as $name) {
        $db->query('CREATE TABLE IF NOT EXISTS '.nwdFeedbackSchemaTable($prefix,$name).' ('.$definitions[$name]['definition'].') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        echo 'PHASE_NEUTRAL_LOCAL_TABLE_READY='.$name."\n";
    }
    if (nwdFeedbackSchemaMissing($db,$prefix)) { throw new RuntimeException('Local feedback schema preparation is incomplete'); }
    echo "PHASE_NEUTRAL_LOCAL_SCHEMA_OK\n";
}
