<?php
/** Presentation helpers for the inner pages that use the approved visual style. */
if (!defined('DIAFAN')) { exit; }

class NwdEditorialPages
{
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
}
