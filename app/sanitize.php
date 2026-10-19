<?php
declare(strict_types=1);

/**
 * Allowlist HTML sanitizer for rich text written in the admin editor.
 * Keeps basic formatting, links, images, tables and YouTube iframes; strips everything else.
 */
function clean_html(?string $html): string
{
    $html = trim((string)$html);
    if ($html === '') return '';

    $allowed = [
        'p' => [], 'br' => [], 'b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [], 's' => [],
        'h2' => ['id'], 'h3' => ['id'], 'h4' => [], 'blockquote' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'target', 'rel'], 'img' => ['src', 'alt', 'width', 'height'],
        'figure' => [], 'figcaption' => [], 'hr' => [], 'span' => [], 'div' => [], 'sub' => [], 'sup' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
        'iframe' => ['src', 'width', 'height', 'allowfullscreen', 'title'],
    ];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('__root');
    if (!$root) return '';

    $walk = function (DOMNode $node) use (&$walk, $allowed, $doc) {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta'], true)) {
                    $node->removeChild($child);
                    continue;
                }
                if (!isset($allowed[$tag])) {
                    $walk($child);
                    while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                    $node->removeChild($child);
                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    if (!in_array($name, $allowed[$tag], true)) {
                        $child->removeAttribute($attr->name);
                        continue;
                    }
                    if (in_array($name, ['href', 'src'], true)) {
                        $v = trim($attr->value);
                        $ok = preg_match('#^(https?://|/|mailto:|tel:|\#|uploads/)#i', $v);
                        if ($tag === 'iframe') $ok = preg_match('#^https://(www\.)?(youtube\.com|youtube-nocookie\.com|www\.google\.com/maps)/#i', $v);
                        if (!$ok) $child->removeAttribute($attr->name);
                    }
                }
                if ($tag === 'a' && $child->getAttribute('target') === '_blank') $child->setAttribute('rel', 'noopener');
                if ($tag === 'iframe' && !$child->hasAttribute('src')) {
                    $node->removeChild($child);
                    continue;
                }
                $walk($child);
            } elseif ($child instanceof DOMComment) {
                $node->removeChild($child);
            }
        }
    };
    $walk($root);

    $out = '';
    foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    return $out;
}
