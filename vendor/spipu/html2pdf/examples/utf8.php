<?php
/**
 * HTML2PDF Library - example
 *
 * HTML => PDF convertor
 * distributed under the LGPL License
 *
 * @package   Html2pdf
 * @author    Laurent MINGUET <webmaster@html2pdf.fr>
 * @copyright 2016 Laurent MINGUET
 *
 * isset($_GET['vuehtml']) is not mandatory
 * it allow to display the result in the HTML format
 */

    require_once(dirname(__FILE__).'/../vendor/autoload.php');

    // get the HTML
    $content = file_get_contents(K_PATH_MAIN.'examples/data/utf8test.txt');
    $content = '<page style="font-family: freeserif"><br />'.nl2br($content).'</page>';

    // convert to PDF
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
        $html2pdf->pdf->SetDisplayMode('real');
        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
        $html2pdf->Output('utf8.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
