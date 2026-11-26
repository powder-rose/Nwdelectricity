<?php
/** Interpret heading line breaks consistently across libxml HTML parser versions. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function nwdInsulationHeadingText(DOMElement $heading) {
    // libxml 2.7.8 drops the whitespace-only node between <br> and <span>.
    // A line break still separates words in the browser. Read it as a space,
    // without changing the actual document or inserting spaces at inline tags.
    $copy = $heading->cloneNode(true);
    foreach ($copy->getElementsByTagName('br') as $br) {
        $br->parentNode->insertBefore($copy->ownerDocument->createTextNode(' '), $br);
    }
    return trim(preg_replace('/[\s\p{Z}]+/u', ' ', $copy->textContent));
}
