<?php
/** Presentation of the existing maintenance copy. No database writes or price overrides. */
if (!defined('DIAFAN')) { exit; }

class NwdMaintenanceService
{
    private static function take(&$html, $pattern)
    {
        if (!preg_match($pattern, $html, $match, PREG_OFFSET_CAPTURE)) { return ''; }
        $html = substr_replace($html, '', $match[0][1], strlen($match[0][0]));
        return $match[0][0];
    }

    public static function photo()
    {
        $base = htmlspecialchars(BASE_PATH . Custom::path('images/maintenance/electrical-maintenance.webp'), ENT_QUOTES, 'UTF-8');
        $small = htmlspecialchars(BASE_PATH . Custom::path('images/maintenance/electrical-maintenance-small.webp'), ENT_QUOTES, 'UTF-8');
        return '<img src="' . $base . '" srcset="' . $small . ' 800w, ' . $base . ' 1448w" '
            . 'sizes="(max-width: 1000px) calc(100vw - 40px), (max-width: 1320px) 50vw, 650px" '
            . 'width="1448" height="1086" alt="Электромонтёр обслуживает распределительный щит" '
            . 'decoding="async" fetchpriority="high">';
    }

    private static function title($heading, $word)
    {
        return preg_replace('~(' . preg_quote($word, '~') . ')~iu', '<span class="maintenance-underline">$1</span>', $heading, 1);
    }

    private static function inspections($html)
    {
        $label = self::take($html, '~<p\b[^>]*>.*?</p>~is');
        // The original CMS nests an ol directly in ul. Repair that wrapper without changing its text.
        $html = preg_replace('~</li>(\s*<ol\b.*?</ol>)~is', '$1</li>', $html, 1);
        $list = self::take($html, '~^\s*<ul\b[^>]*>~i');
        if (!$list) { return '<div class="maintenance-copy">' . $label . $html . '</div>'; }
        $html = preg_replace('~</ul>\s*$~i', '', $html, 1);
        $html = preg_replace_callback('~<li\b[^>]*>(.*?)(?=</li>)~is', function ($match) {
            // Only top-level items begin with these existing CMS labels; nested steps remain a list.
            return preg_replace('~^(<li\b[^>]*>)(Плановый осмотр\.|Аварийное обслуживание и ремонт\.|Капитальный ремонт\.)~u',
                '$1<strong>$2</strong>', $match[0]);
        }, $html);
        return '<div class="maintenance-inspections">' . $label . '<ul class="maintenance-inspections__grid">' . $html . '</ul></div>';
    }

    private static function categories($heading, $body)
    {
        require_once ABSOLUTE_PATH . Custom::path('themes/responsible_service.php');
        $details = '';
        $extra = '';
        $count = 0;
        foreach (NwdResponsibleService::blocks($body, array('serv-description__item')) as $block) {
            if ($block['kind'] !== 'serv-description__item') { $extra .= $block['html']; continue; }
            $inner = $block['inner'];
            $title = self::take($inner, '~<h3\b[^>]*>.*?</h3>~is');
            if (!$title) { $extra .= $block['html']; continue; }
            $count++;
            $details .= '<details class="maintenance-category"' . ($count === 1 ? ' open' : '') . '><summary>' . $title
                . '<span class="maintenance-plus" aria-hidden="true"></span></summary><div class="maintenance-category__body">'
                . $inner . '</div></details>';
        }
        if (!$details) { return '<section class="maintenance-section maintenance-copy">' . $heading . $body . '</section>'; }
        // Wrappers left by the old two-column layout have no content; retain any real additions.
        $extra = trim(strip_tags($extra)) === '' ? '' : '<div class="maintenance-copy">' . $extra . '</div>';
        return '<section id="maintenance-categories" class="maintenance-section maintenance-categories" data-maintenance-reveal>'
            . '<header>' . self::title($heading, 'сложности') . '<a class="maintenance-link" href="#maintenance-cost">Получить консультацию</a></header>'
            . '<div class="maintenance-categories__list">' . $details . $extra . '</div></section>';
    }

    private static function enterprise($heading, $body)
    {
        $lead = self::take($body, '~<p\b[^>]*>.*?</p>~is');
        $note = '';
        if (preg_match_all('~<p\b[^>]*>.*?</p>~is', $body, $paragraphs, PREG_OFFSET_CAPTURE) && count($paragraphs[0]) >= 2) {
            $last = array_slice($paragraphs[0], -2);
            $note = $last[0][0] . $last[1][0];
            $body = substr_replace($body, '', $last[0][1], $last[1][1] + strlen($last[1][0]) - $last[0][1]);
        }
        return '<section class="maintenance-section maintenance-enterprise" data-maintenance-reveal><header>' . $heading . '</header>'
            . '<div class="maintenance-enterprise__body"><div class="maintenance-lead">' . $lead . '</div>'
            . '<details class="maintenance-more"><summary>Требования к эксплуатации и персоналу<span class="maintenance-plus" aria-hidden="true"></span></summary>'
            . '<div class="maintenance-copy">' . $body . '</div></details>'
            . ($note ? '<aside class="maintenance-document-note">' . $note . '</aside>' : '') . '</div></section>';
    }

    private static function prices($heading, $body)
    {
        if (!preg_match('~<table\b[^>]*>.*?</table>~is', $body, $table)) {
            return '<section id="maintenance-prices" class="maintenance-section maintenance-copy">' . $heading . $body . '</section>';
        }
        preg_match_all('~<tr\b[^>]*>(.*?)</tr>~is', $table[0], $rows);
        $plans = '';
        $extras = '';
        $labels = '';
        foreach ($rows[1] as $row) {
            if (preg_match('~<th\b~i', $row)) {
                $labels = '<p class="maintenance-price-labels">' . strip_tags(preg_replace('~</th>\s*<th\b[^>]*>~i', ' · ', $row)) . '</p>';
                continue;
            }
            preg_match_all('~<td\b[^>]*>(.*?)</td>~is', $row, $cells);
            if (count($cells[1]) !== 3) { return '<section id="maintenance-prices" class="maintenance-section maintenance-copy">' . $heading . '<div class="maintenance-table-scroll">' . $body . '</div></section>'; }
            list($description, $unit, $price) = $cells[1];
            if (preg_match('~«(Базовый|Стандартный|Профессиональный)»~u', strip_tags($description), $plan)) {
                $plans .= '<article class="maintenance-plan"><h3>' . $plan[1] . '</h3><p class="maintenance-plan__description">' . $description
                    . '</p><div class="maintenance-plan__rate"><p class="maintenance-plan__price">' . $price . '</p><p class="maintenance-plan__unit">' . $unit
                    . '</p></div><a class="maintenance-button" href="#modal" data-fancybox data-src="#modal">Заказать обслуживание</a></article>';
            } else {
                $extras .= '<div class="maintenance-extra-rate"><p>' . $description . '</p><div><span>' . $price . '</span><small>' . $unit . '</small></div></div>';
            }
        }
        $remaining = str_replace($table[0], '', $body);
        return '<section id="maintenance-prices" class="maintenance-section maintenance-prices" data-maintenance-reveal><header class="maintenance-section-head">'
            . self::title($heading, 'Цена') . '<a class="maintenance-link" href="#maintenance-cost">Узнать точную стоимость</a></header>'
            . $labels . '<div class="maintenance-plans">' . $plans . '</div>'
            . ($extras ? '<details class="maintenance-more maintenance-extra-prices"><summary>Представление интересов заказчика<span class="maintenance-plus" aria-hidden="true"></span></summary>' . $extras . '</details>' : '')
            . $remaining . '</section>';
    }

    public static function content($html)
    {
        $data = array('intro' => '', 'lead' => '', 'html' => '');
        // Replace only the requested low-resolution photograph. Additional editor media stay intact.
        $html = preg_replace('~<img\b[^>]*\bsrc=["\'][^"\']*/obsluzhivanie-elektroustanovok\.png(?:\?[^"\']*)?["\'][^>]*>~i', '', $html);
        preg_match_all('~<h2\b[^>]*>.*?</h2>~is', $html, $headings, PREG_OFFSET_CAPTURE);
        $first = !empty($headings[0]) ? $headings[0][0][1] : strlen($html);
        $intro = substr($html, 0, $first);
        $firstParagraph = self::take($intro, '~<p\b[^>]*>.*?</p>~is');
        if (preg_match('~^(<p\b[^>]*>)(.*?\sМО\.)(\s.*)(</p>)$~us', $firstParagraph, $parts)) {
            $data['lead'] = $parts[1] . $parts[2] . $parts[4];
            $data['intro'] = $parts[1] . ltrim($parts[3]) . $parts[4] . $intro;
        } else {
            $data['lead'] = $firstParagraph;
            $data['intro'] = $intro;
        }
        $sections = array();
        foreach ($headings[0] as $index => $match) {
            $start = $match[1] + strlen($match[0]);
            $end = isset($headings[0][$index + 1]) ? $headings[0][$index + 1][1] : strlen($html);
            $sections[] = array('heading' => $match[0], 'name' => trim(strip_tags($match[0])), 'body' => substr($html, $start, $end - $start));
        }
        $inspections = '';
        foreach ($sections as &$section) {
            if (mb_strpos($section['name'], 'Что входит') !== 0) { continue; }
            if (preg_match('~<p\b[^>]*>\s*Виды осмотра:\s*</p>~u', $section['body'], $match, PREG_OFFSET_CAPTURE)) {
                $inspections = substr($section['body'], $match[0][1]);
                $section['body'] = substr($section['body'], 0, $match[0][1]);
            }
        }
        unset($section);
        // Equipment first, then types of maintenance. All remaining CMS sections retain their order.
        $scope = '';
        foreach ($sections as $section) {
            $heading = $section['heading'];
            $body = $section['body'];
            $name = $section['name'];
            if (mb_strpos($name, 'Что входит') === 0) {
                $scope .= '<section id="maintenance-scope" class="maintenance-section maintenance-scope" data-maintenance-reveal><header>'
                    . self::title($heading, 'обслуживание') . '</header><div class="maintenance-scope__body">' . $body . '</div></section>';
            } elseif (mb_strpos($name, 'Виды обслуживания') === 0) {
                $data['html'] .= '<section id="maintenance-types" class="maintenance-section maintenance-types" data-maintenance-reveal><div class="maintenance-types__intro"><header>'
                    . $heading . '</header><div class="maintenance-copy">' . $body . '</div></div>' . self::inspections($inspections) . '</section>';
            } elseif (mb_strpos($name, 'Категории сложности') === 0) {
                $data['html'] .= self::categories($heading, $body);
            } elseif (mb_strpos($name, 'Периодичность испытаний') === 0) {
                $data['html'] .= '<section class="maintenance-periodicity" data-maintenance-reveal><header>' . $heading . '</header><div>' . $body . '</div></section>';
            } elseif (mb_strpos($name, 'Техобслуживание электроустановок') === 0) {
                $data['html'] .= self::enterprise($heading, $body);
            } elseif (mb_strpos($name, 'Цена обслуживания') === 0) {
                $data['html'] .= self::prices($heading, $body);
            } else {
                $data['html'] .= '<section class="maintenance-section maintenance-copy">' . $heading . $body . '</section>';
            }
        }
        $data['html'] = $scope . $data['html'];
        return $data;
    }
}
