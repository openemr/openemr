<?php
/**
 * Patient transactions.
 *
 */


include_once "../../globals.php";
include_once "$srcdir/transactions.inc";
require_once "$srcdir/options.inc.php";


$include_standard_style_js = ['common.js'];

ob_start();
require_once "{$GLOBALS['srcdir']}/templates/standard_header_template.php";
$header = ob_get_contents();

$result = getTransByPid($pid);
foreach ($result as $item) {
    if (!array_key_exists('body', $item)) {
        $item['body'] = '';
    }

    $item['refer_date'] = oeFormatShortDate($item['refer_date']);
    $item['title_field'] = generate_display_field(['data_type' => 1, 'list_id' => 'transactions'], $item['title']);
}

$viewArgs['transactions'] = $result;
$viewArgs['header'] = $header;

$GLOBALS['twigLoader']->addPath($GLOBALS['template_dir']);
echo $GLOBALS['twig']->render('/patient_file/transactions/list.html.twig', $viewArgs);
