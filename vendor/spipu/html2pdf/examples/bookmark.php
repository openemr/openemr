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

ob_start();
?>
<style type="text/css">
<!--
    table.page_header {width: 100%; border: none; background-color: #DDDDFF; border-bottom: solid 1mm #AAAADD; padding: 2mm }
    table.page_footer {width: 100%; border: none; background-color: #DDDDFF; border-top: solid 1mm #AAAADD; padding: 2mm}
    h1 {color: #000033}
    h2 {color: #000055}
    h3 {color: #000077}

    div.niveau
    {
        padding-left: 5mm;
    }
-->
</style>
<page backtop="14mm" backbottom="14mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
    <page_header>
        <table class="page_header">
            <tr>
                <td style="width: 100%; text-align: left">
                    Exemple d'utilisation des bookmarks
                </td>
            </tr>
        </table>
    </page_header>
    <page_footer>
        <table class="page_footer">
            <tr>
                <td style="width: 100%; text-align: right">
                    page [[page_cu]]/[[page_nb]]
                </td>
            </tr>
        </table>
    </page_footer>
    <bookmark title="Sommaire" level="0" ></bookmark>
</page>
<page pageset="old">
    <bookmark title="Chapitre 1" level="0" ></bookmark><h1>Chapitre 1</h1>
    <div class="niveau">
        Contenu du chapitre 1
    </div>
</page>
<page pageset="old">
    <bookmark title="Chapitre 2" level="0" ></bookmark><h1>Chapitre 2</h1>
    <div class="niveau">
        intro au chapitre 2
        <bookmark title="Chapitre 2.1" level="1" ></bookmark><h2>Chapitre 2.1</h2>
        <div class="niveau">
            Contenu du chapitre 2.1
        </div>
        <bookmark title="Chapitre 2.2" level="1" ></bookmark><h2>Chapitre 2.2</h2>
        <div class="niveau">
            Contenu du chapitre 2.2
        </div>
        <bookmark title="Chapitre 2.3" level="1" ></bookmark><h2>Chapitre 2.3</h2>
        <div class="niveau">
            Contenu du chapitre 2.3
        </div>
    </div>
</page>
<page pageset="old">
    <bookmark title="Chapitre 3" level="0" ></bookmark><h1>Chapitre 3</h1>
    <div class="niveau">
        intro au chapitre 3
        <bookmark title="Chapitre 3.1" level="1" ></bookmark><h2>Chapitre 3.1</h2>
        <div class="niveau">
            Contenu du chapitre 3.1
        </div>
        <bookmark title="Chapitre 3.2" level="1" ></bookmark><h2>Chapitre 3.2</h2>
        <div class="niveau">
            intro au chapitre 3.2
            <bookmark title="Chapitre 3.2.1" level="2" ></bookmark><h3>Chapitre 3.2.1</h3>
            <div class="niveau">
                Contenu du chapitre 3.2.1
            </div>
            <bookmark title="Chapitre 3.2.2" level="2" ></bookmark><h3>Chapitre 3.2.2</h3>
            <div class="niveau">
                Contenu du chapitre 3.2.2
            </div>
        </div>
    </div>
</page>
<?php
    $content = ob_get_clean();

    require_once(dirname(__FILE__).'/../vendor/autoload.php');
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
        $html2pdf->createIndex('Sommaire', 25, 12, false, true, 1);
        $html2pdf->Output('bookmark.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
