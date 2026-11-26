<?php
/** Presentation helpers for the inner pages that use the approved visual style. */
if (!defined('DIAFAN')) { exit; }

class NwdEditorialPages
{
	public static function isPrices($diafan)
	{
		$link = $diafan->_route->link((int) $diafan->_site->id);
		return trim((string) parse_url($link, PHP_URL_PATH), '/') === 'ceny';
	}

	public static function isEditorial($diafan)
	{
		return self::isAbout($diafan) || self::isPrices($diafan) || self::isContacts($diafan) || self::isResponsible($diafan) || self::isBlog($diafan) || self::isMaintenance($diafan) || self::isServices($diafan) || self::isPhaseNeutral($diafan) || self::isRcd($diafan) || self::isPowerTools($diafan) || self::isLaboratory($diafan) || self::isInsulation($diafan) || self::isGrounding($diafan) || self::isBreakers($diafan) || self::isCables($diafan) || self::isCommissioning($diafan) || self::isTehotchet($diafan) || self::isContinuity($diafan) || self::isLightning($diafan) || self::isCablefault($diafan) || self::isAvr($diafan) || self::isRelay($diafan) || self::isPhasing($diafan);
	}

    public static function isTehotchet($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/tekhnicheskiy-otchet-po-elektroizmereniyam';
    }

    public static function isPhasing($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/proverka-fazirovki';
    }

    public static function isRelay($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/proverka-releynoy-apparatury';
    }

    public static function isAvr($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/ispytanie-ustroystv-avr';
    }

    public static function isCablefault($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/poisk-mesta-povrezhdeniya-kabelya';
    }

    public static function isLightning($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/proverka-sistem-molniezaschity';
    }

    public static function isContinuity($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/zamer-metallosvyazi';
    }

    public static function isCommissioning($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/ekspluatatsionnye-i-priemo-sdatochnye-ispytaniya';
    }

    public static function isCables($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/ispytanie-silovykh-kabelnykh-liniy-napryazheniem';
    }

    public static function isBreakers($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/proverka-avtomaticheskikh-vyklyuchateley';
    }

    public static function isGrounding($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/proverka-soprotivleniya-zazemliteley';
    }

    public static function isInsulation($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if($link===''){$link=$diafan->_route->link((int)$diafan->_site->id);}
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/zamer-soprotivleniya-izolyatsii';
    }

    public static function isLaboratory($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if ($link==='') { $link=$diafan->_route->link((int)$diafan->_site->id); }
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/elektroizmeritelnaya-laboratoriya';
    }

    public static function isPowerTools($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if ($link==='') { $link=$diafan->_route->link((int)$diafan->_site->id); }
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/ispytanie-elektroinstrumenta';
    }

    public static function isRcd($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if ($link==='') { $link=$diafan->_route->link((int)$diafan->_site->id); }
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/proverka-uzo';
    }

	public static function isPhaseNeutral($diafan)
    {
        $link=(string)$diafan->_site->rewrite;
        if ($link==='') { $link=$diafan->_route->link((int)$diafan->_site->id); }
        return trim((string)parse_url($link,PHP_URL_PATH),'/')==='services/izmerenie-petli-faza-nul';
    }

	public static function isServices($diafan)
	{
		$link = (string) $diafan->_site->rewrite;
		if ($link === '') { $link = $diafan->_route->link((int) $diafan->_site->id); }
		return trim((string) parse_url($link, PHP_URL_PATH), '/') === 'services';
	}

	public static function isMaintenance($diafan)
	{
		$link = (string) $diafan->_site->rewrite;
		if ($link === '') { $link = $diafan->_route->link((int) $diafan->_site->id); }
		return trim((string) parse_url($link, PHP_URL_PATH), '/') === 'services/obsluzhivanie-elektroustanovok';
	}

	public static function isBlog($diafan)
	{
		$path=trim((string)parse_url((string)$diafan->_site->rewrite,PHP_URL_PATH),'/');
		if ($path==='') { $path=trim((string)parse_url($diafan->_route->link((int)$diafan->_site->id),PHP_URL_PATH),'/'); }
		return (bool)preg_match('~^articles(?:/|$)~',$path);
	}

	public static function isResponsible($diafan)
	{
		// A shop category belongs to /services/, but its active rewrite is the full service URL.
		$link = (string) $diafan->_site->rewrite;
		if ($link === '') { $link = $diafan->_route->link((int) $diafan->_site->id); }
		return trim((string) parse_url($link, PHP_URL_PATH), '/') === 'services/otvetstvennyy-za-elektrokhozyaystvo';
	}

	public static function isContacts($diafan)
	{
		$link = $diafan->_route->link((int) $diafan->_site->id);
		return trim((string) parse_url($link, PHP_URL_PATH), '/') === 'kontakty';
	}

	public static function isAbout($diafan)
	{
		$link = $diafan->_route->link((int) $diafan->_site->id);
		return trim((string) parse_url($link, PHP_URL_PATH), '/') === 'o-nas';
	}

	public static function aboutContent($html)
	{
		$replaced = false;
		return preg_replace_callback('~<img\b[^>]*>~i', function ($match) use (&$replaced) {
			if ($replaced || !preg_match('~\bsrc\s*=\s*(["\'])(.*?)\1~i', $match[0], $source)) {
				return $match[0];
			}
			$path = parse_url(html_entity_decode($source[2], ENT_QUOTES, 'UTF-8'), PHP_URL_PATH);
			if (ltrim((string) $path, '/') !== 'custom/energo/images/pic-single-article-2.jpg') {
				return $match[0];
			}
			$replaced = true;
			$image = htmlspecialchars(BASE_PATH . Custom::path('images/about-detail-editorial.png'), ENT_QUOTES, 'UTF-8');
			return '<figure class="about-visual"><img class="about-visual__image" src="' . $image . '" width="1086" height="1448" alt="Обслуживание электрооборудования" fetchpriority="high" decoding="async"></figure>';
		}, $html);
	}

	public static function priceContent($html)
	{
		if (!preg_match('~<ul\b[^>]*\bclass=["\'][^"\']*\bglobal-cats--price\b[^"\']*["\'][^>]*>.*?</ul>~is', $html, $navigation, PREG_OFFSET_CAPTURE)) {
			return $html;
		}
		$nav = $navigation[0][0];
		$nav = preg_replace_callback('~<a\b[^>]*>~i', function ($match) {
			if (!preg_match('~\bdata-tab=["\'](#[A-Za-z][A-Za-z0-9_-]*)["\']~', $match[0], $target)) {
				return $match[0];
			}
			return preg_replace('~\bhref=["\'][^"\']*["\']~i', 'href="' . $target[1] . '"', $match[0]);
		}, $nav);
		$offset = $navigation[0][1];
		$intro = substr($html, 0, $offset);
		$action = '<a class="price-intro__action button" href="#modal" data-fancybox data-src="#modal">Заявка на обслуживание<span aria-hidden="true">↗</span></a>';
		$intro = preg_replace('~</section>~i', $action . '</section>', $intro, 1);
		$tables = substr($html, $offset + strlen($navigation[0][0]));
		$tables = preg_replace('~<th(\s*>)~i', '<th scope="col"$1', $tables);
		return $intro . '<div class="price-catalog">' . $nav . $tables . '</div>';
	}
}
