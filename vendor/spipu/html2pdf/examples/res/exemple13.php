<style type="text/css">
<!--
table.tableau { text-align: left; }
table.tableau td { width: 15mm; font-family: courier; }
table.tableau th { width: 15mm; font-family: courier; }

.ul1
{
    list-style-image: url(./res/puce2.gif);
}
.ul1 li
{
    color:#F19031;
}
.ul2
{
    list-style: square;
}
.ul2 li
{
    color:#31F190;
}
.ul3
{
    list-style: none;
}
.ul3 li
{
    color:#9031F1;
}
-->
</style>
Exemple de liste avec puce personnalisée :<br>
<table style="width: 100%;" >
    <tr>
        <td style="width: 33%;">
            <ul class="ul1">
                <li>Votre ligne 1</li>
                <li>Votre ligne 2</li>
                <li>Votre ligne 3</li>
            </ul>
        </td>
        <td style="width: 34%;">
            <ul class="ul2">
                <li>Votre ligne 1</li>
                <li>Votre ligne 2</li>
                <li>Votre ligne 3</li>
            </ul>
        </td>
        <td style="width: 33%;">
            <ul class="ul3">
                <li>Votre ligne 1</li>
                <li>Votre ligne 2</li>
                <li>Votre ligne 3</li>
            </ul>
        </td>
    </tr>
</table>
Exemple de caracteres :<br>
<table class="tableau" >
    <tr><th>0</th><th>a</th><th>e</th><th>i</th><th>o</th><th>u</th></tr>
    <tr><th>1</th><td>&agrave;</td><td>&egrave;</td><td>&igrave;</td><td>&ograve;</td><td>&ugrave;</td></tr>
    <tr><th>2</th><td>&aacute;</td><td>&eacute;</td><td>&iacute;</td><td>&oacute;</td><td>&uacute;</td></tr>
    <tr><th>3</th><td>&acirc;</td><td>&ecirc;</td><td>&icirc;</td><td>&ocirc;</td><td>&ucirc;</td></tr>
    <tr><th>4</th><td>&auml;</td><td>&euml;</td><td>&iuml;</td><td>&ouml;</td><td>&uuml;</td></tr>
    <tr><th>5</th><td>&atilde;</td><td> </td><td> </td><td>&otilde;</td><td> </td></tr>
    <tr><th>6</th><td>&aring;</td><td> </td><td> </td><td> </td><td> </td></tr>
    <tr><th>7</th><td>&euro;</td><td>&laquo;</td><td> </td><td>&oslash;</td><td> </td></tr>
</table>
<br>
<?php
    $phrase = "ceci est un exemple avec <b>du gras</b>, ";
    $phrase.= "<i>de l'italique</i>, ";
    $phrase.= "<u>du souligné</u>, ";
    $phrase.= "<u><i><b>et une image</b></i></u> : ";
    $phrase.= "<img src='./res/logo.gif' alt='logo' style='width: 15mm'>";
?>
Table :<br>
<table style="border: solid 1px red; width: 105mm">
    <tr><td style="width: 100%; border: solid 1px green; text-align: left; "><?php echo $phrase; ?></td></tr>
    <tr><td style="width: 100%; border: solid 1px green; text-align: center;"><?php echo $phrase; ?></td></tr>
    <tr><td style="width: 100%; border: solid 1px green; text-align: right; "><?php echo $phrase; ?></td></tr>
</table>
<br>
Div :<br>
<div style="width: 103mm; border: solid 1px green; text-align: left; margin: 1mm 0 1mm 0;padding: 1mm;"><?php echo $phrase; ?></div>
<div style="width: 103mm; border: solid 1px green; text-align: center;margin: 1mm 0 1mm 0;padding: 1mm;"><?php echo $phrase; ?></div>
<div style="width: 103mm; border: solid 1px green; text-align: right; margin: 1mm 0 1mm 0;padding: 1mm;"><?php echo $phrase; ?></div>