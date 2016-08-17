<style type="text/css">
<!--
    table.page_header {width: 100%; border: none; background-color: #DDDDFF; border-bottom: solid 1mm #AAAADD; padding: 2mm }
    table.page_footer {width: 100%; border: none; background-color: #DDDDFF; border-top: solid 1mm #AAAADD; padding: 2mm}
    div.note {border: solid 1mm #DDDDDD;background-color: #EEEEEE; padding: 2mm; border-radius: 2mm; width: 100%; }
    ul.main { width: 95%; list-style-type: square; }
    ul.main li { padding-bottom: 2mm; }
    h1 { text-align: center; font-size: 20mm}
    h3 { text-align: center; font-size: 14mm}
-->
</style>
<page backtop="14mm" backbottom="14mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
    <page_header>
        <table class="page_header">
            <tr>
                <td style="width: 50%; text-align: left">
                    A propos de ...
                </td>
                <td style="width: 50%; text-align: right">
                    HTML2PDF v<?php echo $html2pdf->getVersion(); ?>
                </td>
            </tr>
        </table>
    </page_header>
    <page_footer>
        <table class="page_footer">
            <tr>
                <td style="width: 33%; text-align: left;">
                    http://html2pdf.fr/
                </td>
                <td style="width: 34%; text-align: center">
                    page [[page_cu]]/[[page_nb]]
                </td>
                <td style="width: 33%; text-align: right">
                    &copy;Spipu 2008-2015
                </td>
            </tr>
        </table>
    </page_footer>
    <bookmark title="Présentation" level="0" ></bookmark>
    <br><br><br><br><br><br><br><br>
    <h1>HTML2PDF</h1>
    <h3>v<?php echo $html2pdf->getVersion(); ?></h3><br>
    <br><br><br><br><br>
    <div style="text-align: center; width: 100%;">
        <br>
        <img src="./res/logo.png" alt="Logo HTML2PDF" style="width: 150mm">
        <br>
    </div>
    <br><br><br><br><br>
    <div class="note">
        HTML2PDF est un convertisseur de code HTML vers PDF écrit en PHP5, utilisant la librairie <a href="http://tcpdf.org">TCPDF.</a><br>
        <br>
        Il permet la conversion d'HTML et d'xHTML valide au format PDF, et est distribué sous licence LGPL.<br>
        <br>
        Cette librairie a été conçue pour gérer principalement les TABLE imbriquées afin de générer des factures, bon de livraison, et autres documents officiels.<br>
        <br>
        Vous pouvez télécharger la dernière version de HTML2PDF ici : <a href="http://html2pdf.fr/">http://html2pdf.fr/</a>.<br>
    </div>
</page>
<page pageset="old">
    <bookmark title="Sommaire" level="0" ></bookmark>
    <!-- here will be the automatic index -->
</page>
<page pageset="old">
    <bookmark title="Compatibilité" level="0" ></bookmark>
    <bookmark title="Balises HTML" level="1" ></bookmark>
    <bookmark title="Balises classiques" level="2" ></bookmark>
    <div class="note">
        La liste des balises HTML utilisables est la suivante :<br>
    </div>
    <br>
    <ul class="main">
        <li>&lt;a&gt; : Ceci est un lien vers <a href="http://html2pdf.fr">le site de HTML2PDF</a></li>
        <li>&lt;b&gt;, &lt;strong&gt; : Ecrire en <b>gras</b>.</li>
        <li>&lt;big&gt; : Ecrire plus <big>gros</big>.</li>
        <li>&lt;br&gt; : Permet d'aller à la ligne</li>
        <li>&lt;cite&gt; : <cite>Ceci est une citation</cite></li>
        <li>&lt;code&gt;, &lt;pre&gt;</li>
        <li>&lt;div&gt; :&nbsp;<div style="border: solid 1px #AADDAA; background: #DDFFDD; text-align: center; width: 50mm">exemple de DIV</div></li>
        <li>&lt;em&gt;, &lt;i&gt;, &lt;samp&gt; : Ecrire en <em>italique</em>.</li>
        <li>&lt;font&gt;, &lt;span&gt; : <font style="color: #000066; font-family: times">Exemple d'utilisation</font></li>
        <li>&lt;h1&gt;, &lt;h2&gt;, &lt;h3&gt;, &lt;h4&gt;, &lt;h5&gt;, &lt;h6&gt;</li>
        <li>&lt;hr&gt; : barre horizontale</li>
        <li>&lt;img&gt; : <img src="./res/tcpdf_logo.jpg" style="width: 10mm"></li>
        <li>&lt;p&gt; : Ecrire dans un paragraphe</li>
        <li>&lt;s&gt; : Texte <s>barré</s></li>
        <li>&lt;small&gt; : Ecrire plus <small>petit</small>.</li>
        <li>&lt;style&gt;</li>
        <li>&lt;sup&gt; : Exemple<sup>haut</sup>.</li>
        <li>&lt;sub&gt; : Exemple<sub>bas</sub>.</li>
        <li>&lt;u&gt; : Texte <u>souligné</u></li>
        <li>&lt;table&gt;, &lt;td&gt;, &lt;th&gt;, &lt;tr&gt;, &lt;thead&gt;, &lt;tbody&gt;, &lt;tfoot&gt;, &lt;col&gt; </li>
        <li>&lt;ol&gt;, &lt;ul&gt;, &lt;li&gt;</li>
        <li>&lt;form&gt;, &lt;input&gt;, &lt;textarea&gt;, &lt;select&gt;, &lt;option&gt;</li>
        <li>&lt;fieldset&gt;, &lt;legend&gt;</li>
        <li>&lt;del&gt;, &lt;ins&gt;</li>
        <li>&lt;draw&gt;, &lt;line&gt;, &lt;rect&gt;, &lt;circle&gt;, &lt;ellipse&gt;, &lt;polygone&gt;, &lt;polyline&gt;, &lt;path&gt;</li>
    </ul>
    <bookmark title="Balises spécifiques" level="2" ></bookmark>
    <div class="note">
        Les balises spécifiques suivantes ont été ajoutées :<br>
    </div>
    <br>
    <ul class="main" >
        <li>&lt;page&gt;</li>
        <li>&lt;page_header&gt;</li>
        <li>&lt;page_footer&gt;</li>
        <li>&lt;end_last_page&gt;</li>
        <li>&lt;nobreak&gt;</li>
        <li>&lt;barcode&gt;</li>
        <li>&lt;bookmark&gt;</li>
        <li>&lt;qrcode&gt;</li>
    </ul>
</page>
<page pageset="old">
    <bookmark title="Styles CSS" level="1" ></bookmark>
    <div class="note">
        La liste des styles CSS utilisables est la suivante :<br>
    </div>
    <br>
    <table style="width: 100%">
        <tr style="vertical-align: top">
            <td style="width: 50%">
                <ul class="main">
                    <li>color</li>
                    <li>font-family</li>
                    <li>font-weight</li>
                    <li>font-style</li>
                    <li>font-size</li>
                    <li>text-decoration</li>
                    <li>text-indent</li>
                    <li>text-align</li>
                    <li>text-transform</li>
                    <li>vertical-align</li>
                    <li>width</li>
                    <li>height</li>
                    <li>line-height</li>
                    <li>padding</li>
                    <li>padding-top</li>
                    <li>padding-right</li>
                    <li>padding-bottom</li>
                    <li>padding-left</li>
                    <li>margin</li>
                    <li>margin-top</li>
                    <li>margin-right</li>
                    <li>margin-bottom</li>
                    <li>margin-left</li>
                    <li>position</li>
                    <li>top</li>
                    <li>bottom</li>
                    <li>left</li>
                    <li>right</li>
                    <li>float</li>
                    <li>rotate</li>
                    <li>background</li>
                    <li>background-color</li>
                    <li>background-image</li>
                    <li>background-position</li>
                    <li>background-repeat</li>
                </ul>
            </td>
            <td style="width: 50%">
                <ul class="main">
                    <li>border</li>
                    <li>border-style</li>
                    <li>border-color</li>
                    <li>border-width</li>
                    <li>border-collapse</li>
                    <li>border-top</li>
                    <li>border-top-style</li>
                    <li>border-top-color</li>
                    <li>border-top-width</li>
                    <li>border-right</li>
                    <li>border-right-style</li>
                    <li>border-right-color</li>
                    <li>border-right-width</li>
                    <li>border-bottom</li>
                    <li>border-bottom-style</li>
                    <li>border-bottom-color</li>
                    <li>border-bottom-width</li>
                    <li>border-left</li>
                    <li>border-left-style</li>
                    <li>border-left-color</li>
                    <li>border-left-width</li>
                    <li>border-radius</li>
                    <li>border-top-left-radius</li>
                    <li>border-top-right-radius</li>
                    <li>border-bottom-left-radius</li>
                    <li>border-bottom-right-radius</li>
                    <li>list-style</li>
                    <li>list-style-type</li>
                    <li>list-style-image</li>
                </ul>
            </td>
        </tr>
    </table>
</page>
<page pageset="old">
    <bookmark title="Propriétés" level="1" ></bookmark>
    <div class="note">
        La liste des propriétés utilisables est la suivante :<br>
    </div>
    <br>
    <table style="width: 100%">
        <tr style="vertical-align: top">
            <td style="width: 50%">
                <ul class="main">
                    <li>cellpadding</li>
                    <li>cellspacing</li>
                    <li>colspan</li>
                    <li>rowspan</li>
                    <li>width</li>
                    <li>height</li>
                </ul>
            </td>
            <td style="width: 50%">
                <ul class="main">
                    <li>align</li>
                    <li>valign</li>
                    <li>bgcolor</li>
                    <li>bordercolor</li>
                    <li>border</li>
                    <li>type</li>
                    <li>value</li>
                </ul>
            </td>
        </tr>
    </table>
    <bookmark title="Limitations" level="0" ></bookmark>
    <div class="note">
        Cette librairie comporte des limitations :<br>
    </div>
    <br>
    <ul class="main">
        <li>Les float ne sont gérés que pour la balise IMG.</li>
        <li>Elle ne permet généralement pas la conversion directe d'une page HTML en PDF, ni la conversion du résultat d'un WYSIWYG en PDF.</li>
        <li>Cette librairie est là pour faciliter la génération de documents PDF, pas pour convertir n'importe quelle page HTML.</li>
        <li>Les formulaires ne marchent pas avec tous les viewers PDFs...</li>
        <li>Lisez bien le wiki : <a href="http://wiki.spipu.net/doku.php?id=html2pdf:Accueil">http://wiki.spipu.net/doku.php?id=html2pdf:Accueil</a>.</li>
    </ul>
</page>