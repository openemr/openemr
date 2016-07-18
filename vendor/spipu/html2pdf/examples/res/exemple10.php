<style type="text/css">
<!--
div.minifiche
{
    position:    relative;
    overflow:    hidden;
    width:       454px;
    height:      138px;
    padding:     0;
    font-size:   11px;
    text-align:  left;
    font-weight: normal;
    background-image: url(./res/exemple10a.gif);
}
div.minifiche img.icone    { position: absolute; border: none; left: 5px;   top: 5px;  width: 240px; height: 128px;overflow: hidden; }
div.minifiche div.zone1    { position: absolute; border: none; left: 257px; top: 8px;  width: 188px; height: 14px; padding-top: 1px; overflow: hidden; text-align: center; font-weight: bold; }
div.minifiche div.zone2    { position: absolute; border: none; left: 315px; top: 28px; width: 131px; height: 14px; padding-top: 1px; overflow: hidden; text-align: left; font-weight: normal; }
div.minifiche div.zone3    { position: absolute; border: none; left: 315px; top: 48px; width: 131px; height: 14px; padding-top: 1px; overflow: hidden; text-align: left; font-weight: normal; }
div.minifiche div.zone4    { position: absolute; border: none; left: 315px; top: 68px; width: 131px; height: 14px; padding-top: 1px; overflow: hidden; text-align: left; font-weight: normal; }
div.minifiche div.zone5    { position: absolute; border: none; left: 315px; top: 88px; width: 131px; height: 14px; padding-top: 1px; overflow: hidden; text-align: left; font-weight: normal; }
div.minifiche div.download { position: absolute; border: none; left: 257px; top: 108px;width: 188px; height: 22px; overflow: hidden; text-align: center; font-weight: normal; }
-->
</style>
<page>
    <div style="position: absolute; width: 10mm; height: 10mm; left:    0; top:     0; border: solid 2px #0000EE; background: #AAAAEE"></div>
    <div style="position: absolute; width: 10mm; height: 10mm; right:    0; top:     0; border: solid 2px #00EE00; background: #AAEEAA"></div>
    <div style="position: absolute; width: 10mm; height: 10mm; left:    0; bottom:    0; border: solid 2px #EE0000; background: #EEAAAA"></div>
    <div style="position: absolute; width: 10mm; height: 10mm; right:    0; bottom:    0; border: solid 2px #EEEE00; background: #EEEEAA"></div>
    <table style="width: 100%">
        <tr>
            <td style="text-indent: 10mm; border: solid 1px #007700; width: 80%">
                <p>
                    Ligne dans un paragraphe,
                    test de texte assez long pour engendrer des retours à la ligne automatique... a b c d e f g h i j k l m n o p q r s t u v w x y z a b c d e f g h i j k l m n o p q r s t u v w x y z
                    test de texte assez long pour engendrer des retours à la ligne automatique... a b c d e f g h i j k l m n o p q r s t u v w x y z a b c d e f g h i j k l m n o p q r s t u v w x y z
                </p>
                <p>
                    Ligne dans un paragraphe,
                    test de texte assez long pour engendrer des retours à la ligne automatique... a b c d e f g h i j k l m n o p q r s t u v w x y z a b c d e f g h i j k l m n o p q r s t u v w x y z
                    test de texte assez long pour engendrer des retours à la ligne automatique... a b c d e f g h i j k l m n o p q r s t u v w x y z a b c d e f g h i j k l m n o p q r s t u v w x y z
                </p>
            </td>
            <td style="border: solid 1px #000077; width: 20%">
                Test de paragraphe :)
            </td>
        </tr>
    </table>
    <hr>
    <div class="minifiche" >
        <img class="icone"    src="./res/exemple10b.jpg" alt="HTML2PDF" >
        <div class="zone1">HTML2PDF</div>
        <div class="zone2">PHP</div>
        <div class="zone3">Utilitaire</div>
        <div class="zone4">1.00</div>
        <div class="zone5">01/01/1901</div>
        <div class="download"><img src="./res/exemple10c.gif" alt="" style="border: none;"></div>
    </div>
    <hr>
    <div style="border: solid 1px #000000; margin: 0; padding: 0; background: rgb(255, 255, 255); width: 400px; height: 300px; position: relative;">
        <div style="border-style: solid; border-color: transparent rgb(170, 34, 34) rgb(170, 34, 34) transparent; border-width: 39.5px 59px;    position: absolute; left: 101px; top: 52px; height: 0pt; width: 0pt;"></div>
        <div style="border-style: solid; border-color: rgb(34, 170, 34) rgb(34, 170, 34) transparent transparent; border-width: 59px 39.5px;    position: absolute; left: 101px; top: 131px; height: 0pt; width: 0pt;"></div>
        <div style="border-style: solid; border-color: rgb(34, 34, 170) transparent transparent rgb(34, 34, 170); border-width: 39.5px 59px;    position: absolute; left: 180px; top: 170px; height: 0pt; width: 0pt;"></div>
        <div style="border-style: solid; border-color: transparent transparent rgb(170, 170, 34) rgb(170, 170, 34); border-width: 59px 39.5px;    position: absolute; left: 219px; top: 52px; height: 0pt; width: 0pt;"></div>
        <div style="position: absolute; left: 10px; top: 10px; font-size: 20px; font-family: Arial;">Exemple</div>
    </div>
    <hr>
    <pre><?php
    ob_start();
    readfile(dirname(__FILE__).'/../exemple10.php');
    echo htmlentities(ob_get_clean());
    ?></pre>
</page>
<page orientation="paysage" >
<style type="text/css">
<!--

div.main
{
    padding:     0;
    margin:      0;
    position:    relative;
    left:        50%;
    margin-left: -80mm;
    width:       160mm;
    height:      100mm;
    text-align:  center;
    border:      solid 10px #111111;
    background:  #222222;
    color:       #FFFFFF;
    font-size:   10pt;
    font-weight: bold;
    text-align:  center;
}

div.main a
{
    text-decoration: none;
    color: #EEEEEE;
}

div.main a:hover
{
    text-decoration: underline;
    color: #FFFFFF;
}
-->
</style>
    <div class="main">
        <div style="position: absolute; top: 5mm; left: 5mm; font-size:12pt;text-align: left;">Spipu.net</div><br>
        <div style="position: absolute; bottom: 5mm; right: 5mm; font-size:12pt; text-align: right; ">(c)2015 Spipu</div>
        <br><br><br>
        <a href="http://cineblog.spipu.net/" >Cineblog by Spipu           </a><br><br>
        <a href="http://html2pdf.fr/"        >HTML2PDF                    </a><br><br>
        <a href="http://lambda.spipu.net/"   >Lambda Finder               </a><br><br>
        <a href="http://open.spipu.net/"     >Gestion des Opens - Yaronet </a><br><br>
        <a href="http://perso.spipu.net/"    >A propos de moi             </a><br><br>
        <a href="http://prgm.spipu.net/"     >Programmes by Spipu         </a><br><br>
        <br><br><br>
    </div>
</page>