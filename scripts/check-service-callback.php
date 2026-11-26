<?php
/** Verify a saved public response without booting CMS or writing its database. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$file = $argv[1] ?? '';
if (!is_file($file)) { fwrite(STDERR, "CALLBACK_PUBLIC_FAILED=HTML file required\n"); exit(1); }
$html = file_get_contents($file);
try {
    $doc = new DOMDocument;
    $before = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
    libxml_clear_errors(); libxml_use_internal_errors($before);
    $x = new DOMXPath($doc);
    if (!preg_match('~<body\b[^>]*\bclass=["\'][^"\']*\bpage-phase-neutral\b~i', $html)) {
        // A service whose redesign has not been deployed will use the shared button as soon as it is activated.
        echo "CALLBACK_PAGE_NOT_REDESIGNED\n"; exit;
    }
    $buttons = $x->query('//a[contains(concat(" ",normalize-space(@class)," ")," home-callback ")]');
    if ($buttons->length !== 1) { throw new RuntimeException('Expected one fixed callback'); }
    $button = $buttons->item(0);
    if ($x->query('ancestor::*[contains(concat(" ",normalize-space(@class)," ")," modal-overlay ") or @id="modal"]', $button)->length) {
        throw new RuntimeException('Callback is inside the hidden modal wrapper');
    }
    if ($button->getAttribute('href') !== '#modal' || $button->getAttribute('data-src') !== '#modal' || !$button->hasAttribute('data-fancybox') || $button->getAttribute('aria-label') !== 'Заказать звонок') {
        throw new RuntimeException('Callback must open the existing accessible modal');
    }
    if ($x->query('//*[@id="modal"]//form')->length !== 1) { throw new RuntimeException('One existing callback form required'); }
    if ($x->query('//link[contains(@href,"/custom/energo/css/service-callback.css?v=")]')->length !== 1) { throw new RuntimeException('Versioned callback stylesheet required'); }
    if (strpos($html, '[ERROR#') !== false || strpos($html, '<?php') !== false) { throw new RuntimeException('CMS render failed'); }
    echo "CALLBACK_PUBLIC_OK\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'CALLBACK_PUBLIC_FAILED='.$error->getMessage()."\n"); exit(1);
}
