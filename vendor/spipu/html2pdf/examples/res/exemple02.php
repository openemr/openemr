<?php
$content = 'A Test overflow<br>A Test overflow<br>A Test overflow<br>
<img src="./res/logo.gif" alt="logo" style="width: XXXmm"><br>
B Test overflow<br>B Test overflow<br>B Test overflow<br>
<img src="./res/logo.gif" alt="logo" style="width: XXXmm"><br>
C Test overflow<br>C Test overflow<br>C Test overflow<br>';
?>
<style type="text/css">
<!--
div.zone
{
    border: solid 2mm #66AACC;
    border-radius: 3mm;
    padding: 1mm;
    background-color: #FFEEEE;
    color: #440000;
}
div.zone_over
{
    width: 30mm;
    height: 35mm;
    overflow: hidden;
}

-->
</style>
<page style="font-size: 10pt">
    <span style="font-size: 16pt ; font-weight: bold">Démonstration des images</span><br>
    <br>
    <b>Dans un tableau :</b><br>
    <table style="width: 50%;border: solid 3px #5544DD" align="center">
        <tr>
            <td style="width: 30%; text-align: left; ">Text à gauche<br>avec retour à<br>la ligne</td>
            <td style="width: 40%; text-align: center;"><img src="./res/logo.gif" alt="" ><br><i>légende</i></td>
            <td style="width: 30%; text-align: right; ">Texte à droite</td>
        </tr>
    </table>
    <br>
    Texte <span style="text-decoration: underline">souligné</span>,
    texte <span style="text-decoration: overline">surligné</span>,
    texte <span style="text-decoration: line-through">barré</span>,
    texte <span style="text-decoration: underline overline line-through">avec les trois</span>.<br>
    <br>
    <b>Dans un texte :</b><br>
    texte à la suite d'une image, <img src="./res/logo.gif" alt="" style="height: 10mm">
    texte à la suite d'une image, répétitif car besoin d'un retour à la ligne
    texte à la suite d'une image, répétitif car besoin d'un retour à la ligne
    texte à la suite d'une image, répétitif car besoin d'un retour à la ligne
    texte à la suite d'une image, répétitif car besoin d'un retour à la ligne<br>
    <br>
    <br>
    Test différentes tailles texte
    <span style="font-size: 18pt;">Test Size</span>
    <span style="font-size: 16pt;">Test Size</span>
    <span style="font-size: 14pt;">Test Size</span>
    <span style="font-size: 12pt;">Test Size</span>
    Test différentes tailles texte, répétitif car besoin d'un retour à la ligne
    Test différentes tailles texte, répétitif car besoin d'un retour à la ligne
    Test différentes tailles texte, répétitif car besoin d'un retour à la ligne
    Test différentes tailles texte, répétitif car besoin d'un retour à la ligne
    <br>
    <br>
    <b>Exemple de couleur : </b><br>
    <span style="color: RGB(255, 0, 0)">Texte de couleur</span><br>
    <span style="color: RGB(0., 1., 0.)">Texte de couleur</span><br>
    <span style="color: RGB(0, 0, 100%)">Texte de couleur</span><br>
    <span style="color: CMYK(255, 0, 0, 0)">Texte de couleur</span><br>
    <span style="color: CMYK(0., 1., 0., 0.)">Texte de couleur</span><br>
    <span style="color: CMYK(0, 0, 100%, 0)">Texte de couleur</span><br>
    <span style="color: CMYK(0, 0, 0, 255)">Texte de couleur</span><br>
    <br>
    <table>
        <tr style="vertical-align: top">
            <td>
                <u>Exemple 0 :</u><br><br>
                <div class="zone" ><?php echo str_replace('XXX', '40', $content); ?></div>
                sans overflow
            </td>
            <td>
                <u>Exemple 1 :</u><br><br>
                <div class="zone zone_over" style="text-align: left; vertical-align: top; "><?php echo str_replace('XXX', '40', $content); ?></div>
                hidden left top
            </td>
            <td>
                <u>Exemple 2 :</u><br><br>
                <div class="zone zone_over" style="text-align: center; vertical-align: middle;"><?php echo str_replace('XXX', '40', $content); ?></div>
                hidden center middle
            </td>
            <td>
                <u>Exemple 3 :</u><br><br>
                <div class="zone zone_over" style="text-align: right; vertical-align: bottom;"><?php echo str_replace('XXX', '40', $content); ?></div>
                hidden right bottom
            </td>
        </tr>
        <tr style="vertical-align: top">
            <td>
                <u>Exemple 0 :</u><br><br>
                <div class="zone" ><?php echo str_replace('XXX', '20', $content); ?></div>
                sans overflow
            </td>
            <td>
                <u>Exemple 1 :</u><br><br>
                <div class="zone zone_over" style="text-align: left; vertical-align: top; "><?php echo str_replace('XXX', '20', $content); ?></div>
                hidden left top
            </td>
            <td>
                <u>Exemple 2 :</u><br><br>
                <div class="zone zone_over" style="text-align: center; vertical-align: middle;"><?php echo str_replace('XXX', '20', $content); ?></div>
                hidden center middle
            </td>
            <td>
                <u>Exemple 3 :</u><br><br>
                <div class="zone zone_over" style="text-align: right; vertical-align: bottom;"><?php echo str_replace('XXX', '20', $content); ?></div>
                hidden right bottom
            </td>
        </tr>
    </table>
</page>