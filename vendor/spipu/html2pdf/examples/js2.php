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

    // get the HTML
     ob_start();
?>
<page>
    <h1>Test de JavaScript 2</h1><br>
    <br>
    Normalement une alerte devrait apparaitre, indiquant "coucou"
</page>
<?php
    $content = ob_get_clean();

    // convert to PDF
    require_once(dirname(__FILE__).'/../vendor/autoload.php');
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'fr');
        $html2pdf->pdf->IncludeJS("app.alert('coucou');");
        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
        $html2pdf->Output('js2.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
