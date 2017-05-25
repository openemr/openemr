<?php
/**
 * Patient transactions.
 *
 */


include_once "../../globals.php";
include_once "$srcdir/transactions.inc";
require_once "$srcdir/options.inc.php";


$include_standard_style_js = ['common.js'];

/*
 * This code kind of makes me nervous - it forces in the output of the standard
 * header into a variable so we can pass it to the twig twmplate.
 *
 * I'm nervous because I don't have a lot of experience with ob_* functions.
 * Ideally, once we get the assets helper class up and running we will use that
 * instead. See https://stackoverflow.com/questions/2830366/require-once-to-variable
 * for info on this method - RD
 *
 * @TODO migrate to asset helper class 2017-05-24 RD
 */
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
