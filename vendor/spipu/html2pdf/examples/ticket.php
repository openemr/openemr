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
    $num = 'CMD01-'.date('ymd');
    $nom = 'DUPONT Alphonse';
    $date = '01/01/2012';
?>
<style type="text/css">
<!--
    div.zone { border: none; border-radius: 6mm; background: #FFFFFF; border-collapse: collapse; padding:3mm; font-size: 2.7mm;}
    h1 { padding: 0; margin: 0; color: #DD0000; font-size: 7mm; }
    h2 { padding: 0; margin: 0; color: #222222; font-size: 5mm; position: relative; }
-->
</style>
<page format="100x200" orientation="L" backcolor="#AAAACC" style="font: arial;">
    <div style="rotate: 90; position: absolute; width: 100mm; height: 4mm; left: 195mm; top: 0; font-style: italic; font-weight: normal; text-align: center; font-size: 2.5mm;">
        Ceci est votre e-ticket à présenter au contrôle d'accès -
        billet généré par <a href="http://html2pdf.fr/" style="color: #222222; text-decoration: none;">html2pdf</a>
    </div>
    <table style="width: 99%;border: none;" cellspacing="4mm" cellpadding="0">
        <tr>
            <td colspan="2" style="width: 100%">
                <div class="zone" style="height: 34mm;position: relative;font-size: 5mm;">
                    <div style="position: absolute; right: 3mm; top: 3mm; text-align: right; font-size: 4mm; ">
                        <b><?php echo $nom; ?></b><br>
                    </div>
                    <div style="position: absolute; right: 3mm; bottom: 3mm; text-align: right; font-size: 4mm; ">
                        <b>1</b> place <b>plein tarif</b><br>
                        Prix unitaire TTC : <b>45,00&euro;</b><br>
                        N° commande : <b><?php echo $num; ?></b><br>
                        Date d'achat : <b><?php echo date('d/m/Y à H:i:s'); ?></b><br>
                    </div>
                    <h1>Billet soirée spécial HTML2PDF</h1>
                    &nbsp;&nbsp;&nbsp;&nbsp;<b>Valable le <?php echo $date; ?> à 20h30</b><br>
                    <img src="./res/logo.gif" alt="logo" style="margin-top: 3mm; margin-left: 20mm">
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 25%;">
                <div class="zone" style="height: 40mm;vertical-align: middle;text-align: center;">
                    <qrcode value="<?php echo $num."\n".$nom."\n".$date; ?>" ec="Q" style="width: 37mm; border: none;" ></qrcode>
                </div>
            </td>
            <td style="width: 75%">
                <div class="zone" style="height: 40mm;vertical-align: middle; text-align: justify">
                    <b>Conditions d'utilisation du billet</b><br>
                    Le billet est soumis aux conditions générales de vente que vous avez
                    acceptées avant l'achat du billet. Le billet d'entrée est uniquement
                    valable s'il est imprimé sur du papier A4 blanc, vierge recto et verso.
                    L'entrée est soumise au contrôle de la validité de votre billet. Une bonne
                    qualité d'impression est nécessaire. Les billets partiellement imprimés,
                    souillés, endommagés ou illisibles ne seront pas acceptés et seront
                    considérés comme non valables. En cas d'incident ou de mauvaise qualité
                    d'impression, vous devez imprimer à nouveau votre fichier. Pour vérifier
                    la bonne qualité de l'impression, assurez-vous que les informations écrites
                    sur le billet, ainsi que les pictogrammes (code à barres 2D) sont bien
                    lisibles. Ce titre doit être conservé jusqu'à la fin de la manifestation.
                    Une pièce d'identité pourra être demandée conjointement à ce billet. En
                    cas de non respect de l'ensemble des règles précisées ci-dessus, ce billet
                    sera considéré comme non valable.<br>
                    <br>
                    <i>Ce billet est reconnu électroniquement lors de votre
                    arrivée sur site. A ce titre, il ne doit être ni dupliqué, ni photocopié.
                    Toute reproduction est frauduleuse et inutile.</i>
                </div>
            </td>
        </tr>
    </table>
</page>
<?php
     $content = ob_get_clean();

    // convert
    require_once(dirname(__FILE__).'/../vendor/autoload.php');
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
        $html2pdf->Output('ticket.pdf');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }

