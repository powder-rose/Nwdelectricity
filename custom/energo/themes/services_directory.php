<?php
/** Reshape the existing CMS directory without changing its records, labels or destinations. */
if (!defined('DIAFAN')) { exit; }

final class NwdServicesDirectory
{
    private static function escape($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    private static function inner($node)
    {
        $html = '';
        foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); }
        return $html;
    }

    private static function plural($count)
    {
        $last = $count % 10;
        $teen = $count % 100;
        return $count . ' ' . ($last === 1 && $teen !== 11 ? 'услуга' : ($last >= 2 && $last <= 4 && ($teen < 12 || $teen > 14) ? 'услуги' : 'услуг'));
    }

    private static function featured($links)
    {
        $items = '';
        $featured = array(
            '/services/otvetstvennyy-za-elektrokhozyaystvo/' => 'Аутсорсинг специалиста',
            '/services/obsluzhivanie-elektroustanovok/' => 'Техническое обслуживание'
        );
        foreach ($featured as $path => $label) {
            if (!isset($links[$path])) { continue; }
            $link = $links[$path]->cloneNode(true);
            $link->setAttribute('class', 'service-directory-featured__link');
            $items .= '<div class="service-directory-featured__item"><span>' . $label . '</span>' . $link->ownerDocument->saveHTML($link) . '</div>';
        }
        if (!$items) { return ''; }
        $image = self::escape(BASE_PATH . Custom::path('images/maintenance/electrical-maintenance.webp'));
        $small = self::escape(BASE_PATH . Custom::path('images/maintenance/electrical-maintenance-small.webp'));
        return '<section class="service-directory-featured"><div class="service-directory-featured__copy">'
            . '<p class="service-directory-eyebrow">Обслуживание и ответственность</p><h2>Электрохозяйство<br><span>под контролем.</span></h2>'
            . '<div class="service-directory-featured__links">' . $items . '</div></div>'
            . '<figure class="service-directory-featured__photo"><img src="' . $image . '" srcset="' . $small . ' 800w, ' . $image . ' 1448w" '
            . 'sizes="(max-width: 800px) calc(100vw - 40px), 600px" width="1448" height="1086" '
            . 'alt="Обслуживание распределительного щита" decoding="async" fetchpriority="high"></figure></section>';
    }

    public static function render($html)
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $errors = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML('<?xml encoding="UTF-8"><div id="nwd-services-source">' . $html . '</div>');
        libxml_clear_errors(); libxml_use_internal_errors($errors);
        if (!$loaded) { return $html; }
        $xpath = new DOMXPath($document);
        $root = $xpath->query('//*[@id="nwd-services-source"]')->item(0);
        if (!$root) { return $html; }
        $section = $xpath->query('.//section[contains(concat(" ",normalize-space(@class)," ")," services ")]', $root)->item(0);
        if (!$section) { return $html; }
        $nav = $xpath->query('.//ul[contains(concat(" ",normalize-space(@class)," ")," global-cats ")]', $section)->item(0);
        if (!$nav) { return $html; }
        $groups = array(); $links = array(); $used = array(); $total = 0;
        foreach ($xpath->query('.//a[@data-tab]', $nav) as $tab) {
            $id = ltrim($tab->getAttribute('data-tab'), '#');
            if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $id) || isset($used[$id])) { continue; }
            $list = $xpath->query('.//ul[@id="' . $id . '"]', $section)->item(0);
            if (!$list) { continue; }
            $items = '';
            $count = 0;
            foreach ($xpath->query('./li', $list) as $item) {
                $items .= $document->saveHTML($item); $count++;
                foreach ($xpath->query('.//a[@href]', $item) as $link) {
                    $path = (string) parse_url(html_entity_decode($link->getAttribute('href'), ENT_QUOTES, 'UTF-8'), PHP_URL_PATH);
                    if (!isset($links[$path])) { $links[$path] = $link; }
                }
            }
            $groups[] = array('id' => $id, 'name' => $tab->textContent, 'items' => $items, 'count' => $count, 'node' => $list);
            $used[$id] = true; $total += $count;
        }
        if (!$groups) { return $html; }
        $heading = $xpath->query('.//h1', $section)->item(0);
        $title = $heading ? self::inner($heading) : 'Услуги';
        if ($heading) { $heading->parentNode->removeChild($heading); }
        $nav->parentNode->removeChild($nav);
        foreach ($groups as $group) { $group['node']->parentNode->removeChild($group['node']); }
        $extras = self::inner($section);
        $tabs = '';
        $anchors = ''; $catalog = '';
        foreach ($groups as $group) {
            $id = self::escape($group['id']); $name = self::escape($group['name']);
            $tabs .= '<button type="button" id="' . $id . '-tab" data-service-tab="' . $id . '" role="tab" aria-controls="' . $id . '" aria-selected="false" tabindex="-1">' . $name . '<span>' . $group['count'] . '</span></button>';
            $anchors .= '<a href="#' . $id . '">' . $name . '</a>';
            $catalog .= '<section class="service-directory-group" id="' . $id . '" data-service-group="' . $id . '">'
                . '<header><h2>' . $name . '</h2><p data-service-group-count>' . self::plural($group['count']) . '</p></header>'
                . '<ul class="service-directory-list">' . $group['items'] . '</ul></section>';
        }
        $markup = '<div class="service-directory"><header class="service-directory-hero">'
            . '<div><p class="service-directory-eyebrow">Спецэнергоразвитие</p><h1>' . $title . '</h1></div>'
            . '<div class="service-directory-hero__copy"><p>Обслуживание, испытания и документация<br> для вашего объекта.</p>'
            . '<div class="service-directory-actions"><a class="service-directory-button" href="#services-catalog">Выбрать услугу</a>'
            . '<a class="service-directory-text-link" href="#modal" data-fancybox data-src="#modal">Обсудить задачу</a></div></div></header>'
            . self::featured($links) . '<section class="service-directory-catalog" id="services-catalog">'
            . '<header class="service-directory-catalog__head"><div><p class="service-directory-eyebrow">От задачи — к решению</p><h2>Выберите <span>направление</span></h2></div>'
            . '<p class="service-directory-status" data-service-status aria-live="polite" aria-atomic="true">' . self::plural($total) . '</p></header>'
            . '<div class="service-directory-tools" data-service-tools hidden><div class="service-directory-tabs" role="tablist" aria-label="Направления услуг">' . $tabs . '</div>'
            . '<form class="service-directory-search" role="search"><label for="service-directory-search">Поиск в разделе</label>'
            . '<div><input id="service-directory-search" type="search" autocomplete="off" placeholder="Например, заземление" aria-controls="service-directory-results">'
            . '<button type="button" data-service-clear hidden aria-label="Очистить поиск">×</button></div></form></div>'
            . '<nav class="service-directory-anchors" aria-label="Направления услуг">' . $anchors . '</nav>'
            . '<div id="service-directory-results">' . $catalog . '</div>'
            . '<div class="service-directory-empty" data-service-empty hidden><h3>Услуга не найдена</h3><p>Попробуйте другое название или выберите другое направление.</p>'
            . '<button type="button" data-service-reset>Сбросить поиск</button></div></section>'
            . (trim($extras) !== '' ? '<div class="service-directory-extra">' . $extras . '</div>' : '')
            . '<section class="service-directory-contact"><div><p class="service-directory-eyebrow">Поможем разобраться</p><h2>Нужна помощь<br>с выбором услуги?</h2>'
            . '<p>Расскажите об объекте и задаче — обсудим, какие работы необходимы.</p></div>'
            . '<div class="service-directory-contact__actions"><a class="service-directory-button" href="#modal" data-fancybox data-src="#modal">Получить консультацию</a>'
            . '<a href="tel:+78002012069">8 800 201-20-69</a></div></section></div>';
        // Preserve any CMS content outside the known directory section as well.
        $placeholder = $document->createComment('NWD_SERVICES_DIRECTORY');
        $section->parentNode->replaceChild($placeholder, $section);
        return str_replace('<!--NWD_SERVICES_DIRECTORY-->', $markup, self::inner($root));
    }
}
