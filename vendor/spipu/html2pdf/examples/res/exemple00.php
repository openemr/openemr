<style type="text/Css">
<!--
.test1
{
    border: solid 1px #FF0000;
    background: #FFFFFF;
    border-collapse: collapse;
}
-->
</style>
<page style="font-size: 14px">
    <span style="font-weight: bold; font-size: 18pt; color: #FF0000; font-family: Times">Bonjour, voici quelques exemples<br></span>
    <br>
    Retours à la ligne autorisés : &lt;br&gt;, &lt;br &gt;, &lt;br/&gt;, &lt;br /&gt; <br />
    <br>
    Barre horizontale &lt;hr&gt;<hr style="height: 4mm; background: #AA5500; border: solid 1mm #0055AA">
    Exemple de lien : <a href="http://html2pdf.fr/" >le site HTML2PDF</a><br>
    <br>
    Image : <img src="./res/logo.gif" alt="Logo" width=150 /><br>
    <br>
    Alignement horizontal des DIVs et TABLEs<br />
    <table style="text-align: center; border: solid 2px red; background: #FFEEEE;width: 40%" align="center"><tr><td style="width: 100%">Test 1</td></tr></table><br />
    <table style="text-align: center; border: solid 2px red; background: #FFEEEE;width: 40%; margin: auto"><tr><td style="width: 100%">Test 2</td></tr></table><br />
    <div style="text-align: center; border: solid 2px red; background: #FFEEEE;width: 40%; margin: auto">Test 3</div><br />
    test de tableau imbriqué :<br>
    <table border="1" bordercolor="#007" bgcolor="#AAAAAA" align="center">
        <tr>
            <td border="1">
                <table style="border: solid 1px #FF0000; background: #FFFFFF; width: 100%; text-align: center">
                    <tr>
                        <th style="border: solid 1px #007700;width: 50%">C1 € «</th>
                        <td style="border: solid 1px #007700;width: 50%">C2 € «</td>
                    </tr>
                    <tr>
                        <td style="border: solid 1px #007700;width: 50%">D1 &euro; &laquo;</td>
                        <th style="border: solid 1px #007700;width: 50%">D2 &euro; &laquo;</th>
                    </tr>
                </table>
            </td>
            <td border="1">A2</td>
            <td border="1">AAAAAAAA</td>
        </tr>
        <tr>
            <td border="1">B1</td>
            <td border="1" rowspan="2">
                <table class="test1">
                    <tr>
                        <td style="border: solid 2px #007700">E1</td>
                        <td style="border: solid 2px #000077; padding: 2mm">
                            <table style="border: solid 1px #445500">
                                <tr>
                                    <td>
                                        <img src="./res/logo.gif" alt="Logo" width=100 />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: solid 2px #770000">F1</td>
                        <td style="border: solid 2px #007777">F2</td>
                    </tr>
                </table>
            </td>
            <td border="1"><barcode type="EAN13" value="45" style="width: 30mm; height: 6mm; font-size: 4mm"></barcode></td>
        </tr>
        <tr>
            <td border="1"><barcode type="C39" value="HTML2PDF" label="none" style="width: 35mm; height: 8mm"></barcode></td>
            <td border="1">A2</td>
        </tr>
    </table>
    <br>
    Exemple avec border et padding : <br>
    <table style="border: solid 5mm #770000; padding: 5mm;" cellspacing="0" >
        <tr>
            <td style="border: solid 3mm #007700; padding: 2mm;"><img src="./res/off.png" alt="" style="width: 20mm"></td>
        </tr>
    </table>
    <img src="./res/off.png" style="width: 10mm;"><img src="./res/off.png" style="width: 10mm;"><img src="./res/off.png" style="width: 10mm;"><img src="./res/off.png" style="width: 10mm;"><img src="./res/off.png" style="width: 10mm;"><br>
    <br>
    <table style="border: solid 1px #440000; width: 150px"  cellspacing="0"><tr><td style="width: 100%">Largeur : 150px</td></tr></table><br>
    <table style="border: solid 1px #440000; width: 150pt"  cellspacing="0"><tr><td style="width: 100%">Largeur : 150pt</td></tr></table><br>
    <table style="border: solid 1px #440000; width: 100mm"  cellspacing="0"><tr><td style="width: 100%">Largeur : 100mm</td></tr></table><br>
    <table style="border: solid 1px #440000; width: 5in"    cellspacing="0"><tr><td style="width: 100%">Largeur : 5in</td></tr></table><br>
    <table style="border: solid 1px #440000; width: 80%"    cellspacing="0"><tr><td style="width: 100%">Largeur : 80% </td></tr></table><br>
</page>