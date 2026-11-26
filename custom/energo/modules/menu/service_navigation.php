<?php

if (!defined('DIAFAN')) {
    return;
}

/** Visibility in public navigation only; page routes and CMS records stay intact. */
final class NwdServiceNavigation
{
    const SERVICE_PATH = 'services/otvetstvennyy-za-elektrokhozyaystvo/';
    const MAINTENANCE_PATH = 'services/obsluzhivanie-elektroustanovok/';
    const LABORATORY_PATH = 'services/elektroizmeritelnaya-laboratoriya/';

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
        return self::isFeatured($row) || in_array(self::path($row), array(
            trim(self::MAINTENANCE_PATH, '/'),
            trim(self::LABORATORY_PATH, '/')
        ), true);
    }

    /** Header dropdown only; retain CMS labels except the requested laboratory label. */
    public static function headerRows(array $result)
    {
        $rows = array_filter($result['rows'][$result['parent_id']] ?? array(), function ($row) {
            return !self::isRoot($row) && (self::isVisible($row) || self::isHeaderService($row));
        });
        // Some local databases do not yet have these pages in the menu. Add them under
        // Services without changing CMS records or unrelated dropdowns.
        foreach ($result['rows'] as $group) {
            foreach ($group as $parent) {
                if ((string) $parent['id'] !== (string) $result['parent_id'] || !self::isRoot($parent)) { continue; }
                $required = array(
                    self::MAINTENANCE_PATH => 'Обслуживание электроустановок',
                    self::LABORATORY_PATH => 'Электроизмерительная лаборатория'
                );
                foreach ($required as $path => $label) {
                    $found = false;
                    foreach ($rows as $key => $row) {
                        if (self::path($row) !== trim($path, '/')) { continue; }
                        if ($found) { unset($rows[$key]); continue; }
                        $found = true;
                        if ($path === self::LABORATORY_PATH) {
                            $rows[$key]['name'] = $label;
                            $rows[$key]['othurl'] = '';
                            $rows[$key]['link'] = $path;
                        }
                    }
                    if ($found) { continue; }
                    $url = BASE_PATH_HREF . $path;
                    $rows[] = array(
                        'name' => $label,
                        'link' => $path,
                        'active' => trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/')
                            === trim((string) parse_url($url, PHP_URL_PATH), '/')
                    );
                }
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
