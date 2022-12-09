<?php
$style_base = $GLOBALS['webroot'].'/library/wmt-v2/';
$hdr = explode(DIRECTORY_SEPARATOR, $GLOBALS['css_header']);
$file = array_pop($hdr);
$base = explode('.', $file);
$css = $base[0];
$wmt_xref = ListLook($css,'css_map','');
$style_override = checkSettingMode('global:css_header');
if($style_override) $wmt_xref = ListLook(substr($style_override,0,-4),'css_map','');
$wmt_css = ($wmt_xref) ? $wmt_xref : 'yellow';

$wmt_style = $style_base . 'old' . $wmt_css . '.css';
$wmt_style2 = $style_base . 'wmt' . $wmt_css . '.bkk.css';
?>
<link rel="stylesheet" href="<?php echo $wmt_style; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $wmt_style2; ?>" type="text/css">
