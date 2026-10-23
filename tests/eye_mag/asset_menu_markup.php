<?php
// Render the actual menu link fragments with OpenEMR-equivalent escaping.
function attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES); }
function js_escape($value) { return json_encode($value); }
function xlj($value) {
    return js_escape(($GLOBALS['quotedTitle'] ?? false) ? 'Practice "Logo" & Provider\'s Signature' : $value);
}
function xlt($value) { return htmlspecialchars($value, ENT_NOQUOTES); }
$source = file_get_contents(__DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php');
$assetManagerUrl = '/openemr/interface/forms/eye_mag/asset_manager.php';
$providerId = 17;
$links = [];
foreach ([false, true] as $quotedTitle) {
    foreach (['Practice_Logo', 'My_Signature'] as $menu) {
        $start = strpos($source, '<li id="menu_' . $menu . '"');
        if ($start === false) { throw new RuntimeException('Missing asset menu link'); }
        $end = strpos($source, '</li>', $start) + strlen('</li>');
        ob_start();
        eval('?>' . substr($source, $start, $end - $start));
        $html = ob_get_clean();
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $link = $dom->getElementsByTagName('a')->item(0);
        $links[] = ['menu' => $menu, 'quoted' => $quotedTitle, 'onclick' => $link->getAttribute('onclick')];
    }
}
echo json_encode($links);
