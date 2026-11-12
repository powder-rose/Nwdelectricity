<?php
/**
 * Local configuration template for the supplied DIAFAN.CMS 6.0 site.
 * Copy to config.php only when configuring a separate local database.
 * NWD_DB_URL is an operating-system/container environment variable;
 * this project does not automatically load .env files.
 */
if (!defined('DIAFAN')) {
	header('HTTP/1.1 403 Forbidden');
	exit;
}

define('REVATIVE_PATH', '');
define('TIT1', 'Спецэнергоразвитие — локальная разработка');
define('DB_URL', getenv('NWD_DB_URL') ?: 'mysqli://nwd_local:change-me-local@127.0.0.1/nwdelectricity_local');
define('DB_PREFIX', 'diafan_');
define('DB_CHARSET', 'utf8mb4');
define('USERFILES', 'userfls');
define('VERSION_CMS', '6.0');
define('ADMIN_FOLDER', 'admin');
define('MOBILE_VERSION', false);
define('MOBILE_PATH', 'm');
define('MOBILE_SUBDOMAIN', false);
define('SOURCE_JS', 6);
define('IS_DEMO', false);

define('MOD_DEVELOPER', true);
define('MOD_DEVELOPER_ADMIN', false);
define('MOD_DEVELOPER_TECH', false);
define('MOD_DEVELOPER_MINIFY', false);
define('MOD_DEVELOPER_CACHE', true);
define('MOD_DEVELOPER_PROFILING', false);
define('MOD_DEVELOPER_PROFILER', false);
define('MOD_DEVELOPER_POST', false);
define('MOD_PROTECTED', false);

define('FTP_HOST', '');
define('FTP_DIR', '');
define('FTP_LOGIN', '');
define('FTP_PASSWORD', '');
define('CACHE_EXTREME', false);
define('CACHE_MEMCACHED', false);
define('CACHE_MEMCACHED_HOST', '127.0.0.1');
define('CACHE_MEMCACHED_PORT', '11211');
define('TIMEZONE', 'Europe/Moscow');
define('ROUTE_END', '/');
define('ROUTE_AUTO_MODULE', true);
define('LAST_1C_EXPORT', '');
define('NO_X_FRAME', false);
define('CUSTOM', 'energo');
