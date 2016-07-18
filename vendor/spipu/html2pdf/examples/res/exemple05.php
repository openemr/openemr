<style type="text/css">
<!--
table
{
    width:  100%;
    border: solid 1px #5544DD;
}

th
{
    text-align: center;
    border: solid 1px #113300;
    background: #EEFFEE;
}

td
{
    text-align: left;
    border: solid 1px #55DD44;
}

td.col1
{
    border: solid 1px red;
    text-align: right;
}

end_last_page div
{
    border: solid 1mm red;
    height: 27mm;
    margin: 0;
    padding: 0;
    text-align: center;
    font-weight: bold;
}
-->
</style>
<span style="font-size: 20px; font-weight: bold">Démonstration des retour à la ligne automatique, ainsi que des sauts de page automatique<br></span>
<br>
<br>
<table>
    <col style="width: 5%" class="col1">
    <col style="width: 25%">
    <col style="width: 30%">
    <col style="width: 40%">
    <thead>
        <tr>
            <th rowspan="2">n°</th>
            <th colspan="3" style="font-size: 16px;">
                Titre du tableau
            </th>
        </tr>
        <tr>
            <th>Colonne 1</th>
            <th>Colonne 2</th>
            <th>Colonne 3</th>
        </tr>
    </thead>
<?php
    for ($k=0; $k<50; $k++) {
?>
    <tr>
        <td><?php echo $k; ?></td>
        <td>test de texte assez long pour engendrer des retours à la ligne automatique...</td>
        <td>test de texte assez long pour engendrer des retours à la ligne automatique...</td>
        <td>test de texte assez long pour engendrer des retours à la ligne automatique...</td>
    </tr>
<?php
    }
?>
    <tfoot>
        <tr>
            <th colspan="4" style="font-size: 16px;">
                bas du tableau
            </th>
        </tr>
    </tfoot>
</table>
Cool non ?<br>
<end_last_page end_height="30mm">
    <div>
        Ceci est un test de fin de page
    </div>
</end_last_page>