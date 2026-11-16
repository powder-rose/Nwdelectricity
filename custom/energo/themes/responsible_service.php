<?php
/** Presentation only. All copy, links, tariffs and photograph sources come from the CMS. */
if (!defined('DIAFAN')) { exit; }

class NwdResponsibleService
{
    private static function hasClass($tag, $name)
    {
        return preg_match('~\bclass\s*=\s*(["\'])(.*?)\1~is', $tag, $match)
            && in_array($name, preg_split('~\s+~', trim($match[2])), true);
    }

    /** Extract only known, balanced presentation containers, preserving unknown HTML. */
    public static function blocks($html, $classes)
    {
        preg_match_all('~</?(?:div|section)\b[^>]*>~i', $html, $tags, PREG_OFFSET_CAPTURE);
        $blocks = array();
        $start = null;
        $depth = 0;
        $cursor = 0;
        $kind = '';
        $innerStart = 0;
        foreach ($tags[0] as $tag) {
            $closing = substr($tag[0], 0, 2) === '</';
            if ($start === null && !$closing) {
                foreach ($classes as $class) {
                    if (!self::hasClass($tag[0], $class)) { continue; }
                    if ($tag[1] > $cursor) {
                        $blocks[] = array('kind' => 'extra', 'html' => substr($html, $cursor, $tag[1] - $cursor));
                    }
                    $start = $tag[1];
                    $innerStart = $start + strlen($tag[0]);
                    $kind = $class;
                    $depth = 0;
                    break;
                }
            }
            if ($start === null) { continue; }
            $depth += $closing ? -1 : 1;
            if ($depth !== 0) { continue; }
            $end = $tag[1] + strlen($tag[0]);
            $blocks[] = array(
                'kind' => $kind,
                'html' => substr($html, $start, $end - $start),
                'inner' => substr($html, $innerStart, $tag[1] - $innerStart)
            );
            $cursor = $end;
            $start = null;
        }
        if ($start !== null) { $cursor = $start; }
        if ($cursor < strlen($html)) {
            $blocks[] = array('kind' => 'extra', 'html' => substr($html, $cursor));
        }
        return $blocks;
    }

    private static function take(&$html, $pattern)
    {
        if (!preg_match($pattern, $html, $match, PREG_OFFSET_CAPTURE)) { return ''; }
        $value = $match[0][0];
        $html = substr_replace($html, '', $match[0][1], strlen($value));
        return $value;
    }

    public static function photo($tag, $priority = false)
    {
        if (!$tag) { return ''; }
        $tag = preg_replace('~(\bsrc\s*=\s*["\'][^"\'?#]*/otv-energo\.jpg)(?=["\'#])~i', '$1?v=1ae7691d', $tag, 1);
        $tag = preg_replace('~\s+(?:class|style|width|height|loading|decoding|fetchpriority)\s*=\s*(["\']).*?\1~is', '', $tag);
        if (!preg_match('~\balt\s*=~i', $tag)) {
            $tag = preg_replace('~\s*/?>$~', ' alt="Инженер у электрооборудования">', $tag);
        }
        return preg_replace('~\s*/?>$~', ' class="service-photo" decoding="async" '
            . ($priority ? 'fetchpriority="high"' : 'loading="lazy"') . '>', $tag);
    }

    public static function hero($html)
    {
        $data = array('intro' => '', 'benefits' => '', 'actions' => '', 'extra' => '');
        foreach (self::blocks($html, array('fscreen__text', 'fscreen__advant', 'fscreen__btnWrap')) as $block) {
            if ($block['kind'] === 'fscreen__text') {
                $data['intro'] .= $block['inner'];
            } elseif ($block['kind'] === 'fscreen__btnWrap') {
                $actions = $block['inner'];
                if (preg_match('~^\s*(<a\b[^>]*>.*?</a>)\s*(<button\b[^>]*>.*?</button>)\s*$~is', $actions, $pair)) {
                    $actions = $pair[2] . $pair[1];
                }
                $data['actions'] .= $actions;
            } elseif ($block['kind'] === 'fscreen__advant') {
                preg_match_all('~<div\b[^>]*\bclass=["\'][^"\']*\bfscreen__advant__item__text\b[^"\']*["\'][^>]*>(.*?)</div>~is', $block['inner'], $items);
                if (!empty($items[1])) {
                    foreach ($items[1] as $item) { $data['benefits'] .= '<li>' . $item . '</li>'; }
                } else {
                    $data['extra'] .= $block['html'];
                }
            } else {
                $data['extra'] .= $block['html'];
            }
        }
        return $data;
    }

    private static function promise($inner, $image)
    {
        $heading = self::take($inner, '~<h2\b[^>]*>.*?</h2>~is');
        $heading = preg_replace('~(ответственность\s+наша)(\s*</h2>)~iu', '<span class="service-promise__accent">$1</span>$2', $heading);
        $lead = self::take($inner, '~<p\b[^>]*>.*?</p>~is');
        $list = self::take($inner, '~<ul\b[^>]*>.*?</ul>~is');
        return '<section class="service-promise"><div class="service-promise__panel"><header class="service-promise__header">'
            . '<div class="service-promise__heading">' . $heading . '<div class="service-promise__lead">' . $lead . '</div></div>'
            . ($image ? '<figure class="service-promise__photo">' . self::photo($image) . '</figure>' : '')
            . '</header><div class="service-promise__points">' . $list . '</div></div>'
            . '<div class="service-promise__foot">' . $inner . '</div></section>';
    }

    private static function expertise($inner, $image, $hasTariffs)
    {
        $heading = self::take($inner, '~<h2\b[^>]*>.*?</h2>~is');
        $note = '';
        preg_match_all('~<p\b[^>]*>.*?</p>~is', $inner, $paragraphs, PREG_OFFSET_CAPTURE);
        if (count($paragraphs[0]) > 1) {
            $last = $paragraphs[0][count($paragraphs[0]) - 1];
            $note = $last[0];
            $inner = substr_replace($inner, '', $last[1], strlen($last[0]));
        }
        // Emphasis wraps the CMS sentence, so the text stays editable in one place.
        $inner = preg_replace('~(<p\b[^>]*>\s*)([^<.!?]+[.!?])(?=\s+[^<])~iu',
            '$1<span class="service-expertise__opening">$2</span>', $inner, 1);
        if ($hasTariffs && $note !== '' && !preg_match('~<a\b~i', $note)) {
            $note = preg_replace('~(>\s*)(Стоимость\s+услуг)(?=\s)~iu', '$1<a href="#tarify">$2</a>', $note, 1);
        }
        return '<section class="service-expertise"><div class="service-expertise__copy">' . $heading . $inner
            . ($note ? '<div class="service-expertise__note">' . $note . '</div>' : '') . '</div>'
            . ($image ? '<figure class="service-expertise__photo">' . self::photo($image) . '</figure>' : '')
            . '</section>';
    }

    private static function trust($inner, $image)
    {
        $heading = self::take($inner, '~<h2\b[^>]*>.*?</h2>~is');
        $lead = self::take($inner, '~<p\b[^>]*>.*?</p>~is');
        $list = self::take($inner, '~<ul\b[^>]*>.*?</ul>~is');
        // Give the existing financial and tenure facts typographic emphasis.
        // Other wording, rich HTML or additional facts keep their ordinary list treatment.
        $fact = '<span class="service-stat"><span>$1</span><span class="service-stat__value">$2</span><span>$3</span></span>';
        $list = preg_replace('~(?<=>)(\s*Уставной\s+капитал\s+)([^<>]+?)(\s+рубл(?:ей|я|ь)[.!]?\s*)(?=</li>)~iu', $fact, $list, 1);
        $list = preg_replace('~(?<=>)(\s*Более\s+)([^<>]+?)(\s+на\s+рынке[.!]?\s*)(?=</li>)~iu', $fact, $list, 1);
        return '<section class="service-trust"><header class="service-trust__header">'
            . $heading . '<div class="service-trust__lead">' . $lead . '</div></header>'
            . '<div class="service-trust__main">'
            . ($image ? '<figure class="service-trust__photo">' . self::photo($image) . '</figure>' : '')
            . '<div class="service-trust__points">' . $list . $inner . '</div></div></section>';
    }

    public static function content($html)
    {
        $data = array('photo' => '', 'html' => '');
        $story = 0;
        $hasTariffs = (bool) preg_match('~\bid\s*=\s*(["\'])tarify\1~i', $html);
        foreach (self::blocks($html, array('gearText__wrap', 'textBlock', 'tech-price')) as $block) {
            if ($block['kind'] === 'extra') {
                if (trim($block['html']) !== '') { $data['html'] .= '<div class="service-cms-extra">' . $block['html'] . '</div>'; }
                continue;
            }
            if ($block['kind'] === 'gearText__wrap') {
                $data['html'] .= '<aside class="service-note">' . $block['inner'] . '</aside>';
                continue;
            }
            if ($block['kind'] === 'tech-price') {
                // Accessible links keep the exact existing labels and modal target.
                $data['html'] .= preg_replace_callback('~<a\b[^>]*\bclass=["\'][^"\']*\btech-price-item__link\b[^"\']*["\'][^>]*>~i', function ($match) {
                    return preg_match('~\bhref\s*=~i', $match[0]) ? $match[0] : substr($match[0], 0, -1) . ' href="#modal">';
                }, $block['html']);
                continue;
            }
            if (self::hasClass($block['html'], 'textBlock--wrap')) {
                $document = preg_replace('~(<h2\b[^>]*>\s*)(ОБРАЗЕЦ\s+ПРИКАЗА)(?=\s)~iu',
                    '$1<span class="service-document__name">$2</span>', $block['inner'], 1);
                $data['html'] .= '<section class="service-document">' . $document . '</section>';
                continue;
            }
            $inner = $block['inner'];
            $image = self::take($inner, '~<img\b[^>]*>~i');
            $story++;
            if ($story === 1) {
                $data['photo'] = self::photo($image, true);
                $heading = self::take($inner, '~<h2\b[^>]*>.*?</h2>~is');
                $heading = preg_replace('~(>\s*)(Аутсорсинг)(?=\s)~iu', '$1<span class="service-underline">$2</span>', $heading, 1);
                $lead = self::take($inner, '~<p\b[^>]*>.*?</p>~is');
                $tasks = '';
                $taskLead = '';
                $beforeTasks = $inner;
                $afterTasks = '';
                if (preg_match('~<ul\b[^>]*\bclass=["\'][^"\']*\blistElectro\b[^"\']*["\'][^>]*>.*?</ul>~is', $inner, $taskMatch, PREG_OFFSET_CAPTURE)) {
                    $tasks = $taskMatch[0][0];
                    $beforeTasks = substr($inner, 0, $taskMatch[0][1]);
                    $afterTasks = substr($inner, $taskMatch[0][1] + strlen($tasks));
                    // The CMS ends the preceding paragraph with the introduction to this list.
                    if (preg_match('~<(b|strong)\b[^>]*>([^<>]+:\s*)</\1>(\s*</p>\s*)$~iu', $beforeTasks, $intro, PREG_OFFSET_CAPTURE)) {
                        $taskLead = '<p class="service-scope__task-lead">' . $intro[2][0] . '</p>';
                        $beforeTasks = substr($beforeTasks, 0, $intro[0][1]) . $intro[3][0];
                    }
                }
                $background = $beforeTasks . $afterTasks;
                $data['html'] .= '<section class="service-scope"><header class="service-scope__intro">'
                    . $heading . '<div class="service-scope__lead">' . $lead . '</div></header>'
                    . ($tasks ? '<div class="service-scope__tasks">' . $taskLead . $tasks . '</div>' : '')
                    . (trim($background) !== '' ? '<details class="service-scope__details"><summary>Квалификация и требования</summary>'
                        . '<div class="service-scope__copy">' . $background . '</div></details>' : '')
                    . '</section>';
                continue;
            }
            if ($story === 2) {
                $data['html'] .= self::promise($inner, $image);
                continue;
            }
            if ($story === 3) {
                $data['html'] .= self::expertise($inner, $image, $hasTariffs);
                continue;
            }
            if ($story === 4) {
                $data['html'] .= self::trust($inner, $image);
                continue;
            }
            $side = $story % 2 === 0 ? ' service-story--photo-left' : '';
            $data['html'] .= '<section class="service-story' . $side . ($image ? '' : ' service-story--text-only') . '">'
                . ($image ? '<figure class="service-story__photo">' . self::photo($image) . '</figure>' : '')
                . '<div class="service-story__copy">' . $inner . '</div></section>';
        }
        return $data;
    }
}
