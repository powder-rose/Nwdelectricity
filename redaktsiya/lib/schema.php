<?php
if (!defined('NWD_PANEL') || PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function panelInstall($statisticsOnly = false) {
    $schema = array(
        'panel_state' => 'id TINYINT PRIMARY KEY, password_hash VARCHAR(255) NOT NULL, generation CHAR(32) NOT NULL, journal_id INT UNSIGNED NULL',
        'panel_attempt' => 'client CHAR(64) PRIMARY KEY, started INT UNSIGNED NOT NULL, attempts INT UNSIGNED NOT NULL',
        'panel_article' => "id CHAR(36) PRIMARY KEY, request_id CHAR(36) NOT NULL UNIQUE, request_hash CHAR(64) NOT NULL, payload MEDIUMTEXT NOT NULL, title VARCHAR(240) NOT NULL, category VARCHAR(80) NOT NULL, slug VARCHAR(75) NOT NULL UNIQUE, status VARCHAR(12) NOT NULL, revision INT UNSIGNED NOT NULL, site_id INT UNSIGNED UNIQUE, created_at INT UNSIGNED NOT NULL, updated_at INT UNSIGNED NOT NULL, published_at INT UNSIGNED NULL, deleted TINYINT NOT NULL DEFAULT 0, KEY listing (deleted,status,updated_at)",
        'panel_schedule' => "article_id CHAR(36) PRIMARY KEY, token CHAR(32) NOT NULL, scheduled_at INT UNSIGNED NOT NULL, state VARCHAR(12) NOT NULL, created_at INT UNSIGNED NOT NULL, started_at INT UNSIGNED NULL, finished_at INT UNSIGNED NULL, error VARCHAR(500) NOT NULL DEFAULT '', KEY due (state,scheduled_at)",
        'panel_schedule_worker' => "id TINYINT PRIMARY KEY, checked_at INT UNSIGNED NOT NULL, error VARCHAR(500) NOT NULL DEFAULT ''",
        'panel_slug' => 'slug VARCHAR(75) PRIMARY KEY, article_id CHAR(36) NOT NULL, KEY article_id (article_id)',
        'panel_image' => 'id CHAR(32) PRIMARY KEY, mime VARCHAR(20) NOT NULL, width INT NOT NULL, height INT NOT NULL, bytes MEDIUMBLOB NOT NULL',
        'panel_image_ref' => 'image_id CHAR(32) NOT NULL, article_id CHAR(36) NOT NULL, PRIMARY KEY(image_id,article_id), KEY article_id (article_id)'
    );
    $traffic = array(
        'panel_traffic_session' => 'token BINARY(16) PRIMARY KEY, seen_at INT UNSIGNED NOT NULL, KEY recent (seen_at)',
        'panel_traffic_view' => "event_id BINARY(16) PRIMARY KEY, viewed_at INT UNSIGNED NOT NULL, path VARCHAR(512) NOT NULL, article_site_id INT UNSIGNED NOT NULL DEFAULT 0, entry TINYINT UNSIGNED NOT NULL DEFAULT 0, source VARCHAR(128) NOT NULL DEFAULT '', KEY period (viewed_at)"
    );
    $schema = $statisticsOnly ? $traffic : $schema + $traffic;
    foreach ($schema as $name=>$definition) {
        panelDb()->query('CREATE TABLE IF NOT EXISTS '.panelTable($name)." ($definition) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    if ($statisticsOnly) { return; }
    foreach (array('site','rewrite','map_index','redirect','site_parents') as $name) {
        $row = panelRow('SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',array(DB_PREFIX.$name));
        if (!$row || strtoupper($row['engine']) !== 'INNODB') { panelFail('Для публикации нужна таблица InnoDB '. $name .'.', 503); }
    }
    panelLanguage();
}
