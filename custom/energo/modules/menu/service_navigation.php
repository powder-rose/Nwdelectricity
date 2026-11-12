<?php

if (!defined('DIAFAN')) {
    return;
}

/** Visibility in public navigation only; page routes and CMS records stay intact. */
final class NwdServiceNavigation
{
    const SERVICE_PATH = 'services/otvetstvennyy-za-elektrokhozyaystvo/';

    public static function path(array $row)
    {
        $url = !empty($row['othurl']) ? $row['othurl'] : ($row['link'] ?? '');
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $base = trim((string) parse_url(BASE_PATH_HREF, PHP_URL_PATH), '/');
        if ($base !== '' && strpos($path, $base . '/') === 0) {
            $path = substr($path, strlen($base) + 1);
        }
        return $path;
    }

    public static function isRoot(array $row)
    {
        return self::path($row) === 'services';
    }

    public static function isFeatured(array $row)
    {
        return self::path($row) === trim(self::SERVICE_PATH, '/');
    }

    public static function isVisible(array $row)
    {
        return strpos(self::path($row), 'services/') !== 0 || self::isFeatured($row);
    }

    public static function url()
    {
        return BASE_PATH_HREF . self::SERVICE_PATH;
    }
}
