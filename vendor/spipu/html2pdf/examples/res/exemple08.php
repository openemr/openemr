<style type="text/css">
<!--
table
{
    padding: 0;
    border: solid 1mm LawnGreen;
    font-size: 12pt;
    background: #FFFFFF;
    text-align: center;
    vertical-align: middle;
}

td
{
    padding: 1mm;
    border: solid 1mm black;
}

td.div
{
    width: 110px;
    height: 110px;
    text-align: left;
    padding: 0
}

td.div div
{
    margin: auto;
    background: yellow;
    border: solid 2px blue;
    color: red;
    width: 100px;
    height: 65px;
    text-align: center;
}

-->
</style>
<page backcolor="#AACCFF" backleft="5mm" backright="5mm" backtop="10mm" backbottom="10mm" >
    <table>
        <tr>
            <td class="div"><div style="rotate: 0;">Hello ! ceci <b>est</b> un test !<br><img src="./res/logo.png" style="width: 80px;" alt="logo"></div></td>
            <td class="div"><div style="rotate: 270;">Hello ! ceci <b>est</b> un test !<br><img src="./res/logo.png" style="width: 80px;" alt="logo"></div></td>
        </tr>
        <tr>
            <td class="div"><div style="rotate: 90;">Hello ! ceci <b>est</b> un test !<br><img src="./res/logo.png" style="width: 80px;" alt="logo"></div></td>
            <td class="div"><div style="rotate: 180;">Hello ! ceci <b>est</b> un test !<br><img src="./res/logo.png" style="width: 80px;" alt="logo"></div></td>
        </tr>
    </table>
    <br>
    <table cellspacing="4">
        <tr>
            <td>a A1</td>
            <td>aa A2</td>
            <td>aaa A3</td>
            <td>aaaa A4</td>
        </tr>
        <tr>
            <td rowspan="2">B1</td>
            <td style="font-size: 16pt">B2</td>
            <td colspan="2">B3</td>
        </tr>
        <tr>
            <td>C1</td>
            <td>C2</td>
            <td>C3</td>
        </tr>
        <tr>
            <td colspan="2">D1</td>
            <td colspan="2">D2</td>
        </tr>
    </table>
    <hr>
    <table>
        <tr>
            <td colspan="2">CoucouCoucou !</td>
            <td>B</td>
            <td>CC</td>
        </tr>
        <tr>
            <td>AA</td>
            <td colspan="2">CoucouCoucou !</td>
            <td>CC</td>
        </tr>
        <tr>
            <td>AA</td>
            <td>B</td>
            <td colspan="2">CoucouCoucou !</td>
        </tr>
    </table>
    <hr>
    <table style="background: #FFFFFF">
        <tr>
            <td>AA</td>
            <td>AA</td>
            <td>AA</td>
            <td rowspan="2">AA</td>
        </tr>
        <tr>
            <td>AA</td>
            <td rowspan="2" colspan="2" >CoucouCoucou !</td>
        </tr>
        <tr>
            <td>AA</td>
            <td>CC</td>
        </tr>
        <tr>
            <td colspan="2">D1</td>
            <td colspan="2">D2</td>
        </tr>
    </table>
    <hr>
</page>