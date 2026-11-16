<?php
/** Presentation of the original homepage dynamic block; no CMS data changes. */
if (!defined('DIAFAN')) { exit; }

class NwdHomeHero
{
    public static function present($html)
    {
        $html = preg_replace_callback('~(<div\b[^>]*\bclass=["\'][^"\']*\bfscreen__heading\b[^"\']*["\'][^>]*>)(\s*<span\b[^>]*>.*?</span>)(\s*[-–—]\s*)(.*?)(</div>)~isu', function ($match) {
            $offer = preg_replace('~<br\s*/?>~i', ' ', $match[4]);
            $offer = preg_replace('~^(\s*)(обслуживание)~iu', '$1<span class="hero-offer__lead">$2</span>', $offer, 1);
            return $match[1] . '<span class="hero-brand-line">' . $match[2]
                . '<span class="hero-brand-separator">' . $match[3] . '</span></span>'
                . '<span class="hero-offer">' . $offer . '</span>' . $match[5];
        }, $html, 1);
        return preg_replace_callback('~(<div\b[^>]*\bclass=["\'][^"\']*\bfscreen__btnWrap\b[^"\']*["\'][^>]*>)(.*?)(</div>)~is', function ($match) {
            return $match[1] . '<a class="hero-inquiry button" href="#modal" data-fancybox data-src="#modal">Заявка на обслуживание<span aria-hidden="true">↗</span></a>'
                . $match[2] . $match[3];
        }, $html, 1);
    }
}
