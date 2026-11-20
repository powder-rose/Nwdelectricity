<?php

if (!defined('DIAFAN')) {
    return;
}

/** Visibility in public navigation only; page routes and CMS records stay intact. */
final class NwdServiceNavigation
{
    const SERVICE_PATH = 'services/otvetstvennyy-za-elektrokhozyaystvo/';
    const MAINTENANCE_PATH = 'services/obsluzhivanie-elektroustanovok/';

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

    public static function isHeaderService(array $row)
    {
        return self::isFeatured($row) || self::path($row) === trim(self::MAINTENANCE_PATH, '/');
    }

    /** Header dropdown only; retain CMS labels and avoid duplicating an existing item. */
    public static function headerRows(array $result)
    {
        $rows = array_filter($result['rows'][$result['parent_id']] ?? array(), function ($row) {
            return !self::isRoot($row) && (self::isVisible($row) || self::isHeaderService($row));
        });
        foreach ($rows as $row) {
            if (self::path($row) === trim(self::MAINTENANCE_PATH, '/')) { return $rows; }
        }
        // Some local databases do not yet have this page in the menu. Add it under
        // Services without changing CMS records or unrelated dropdowns.
        foreach ($result['rows'] as $group) {
            foreach ($group as $parent) {
                if ((string) $parent['id'] !== (string) $result['parent_id'] || !self::isRoot($parent)) { continue; }
                $url = BASE_PATH_HREF . self::MAINTENANCE_PATH;
                $rows[] = array(
                    'name' => 'Обслуживание электроустановок',
                    'link' => self::MAINTENANCE_PATH,
                    'active' => trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/')
                        === trim((string) parse_url($url, PHP_URL_PATH), '/')
                );
                return $rows;
            }
        }
        return $rows;
    }

    public static function url()
    {
        return BASE_PATH_HREF . self::SERVICE_PATH;
    }
}
