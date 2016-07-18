<page orientation="paysage" >
    <bookmark title="Document" level="0" ></bookmark>
    <a name="document_reprise"></a>
    <table cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 10%;">
                <img style="width: 100%" src="./res/logo.gif" alt="Logo HTML2PDF" >
            </td>
            <td style="width: 80%; text-align: center; text-decoration: underline; font-weight: bold; font-size: 20pt;">
                <span style="font-size: 10pt"><br></span>
                ACCORD DE RETOUR
            </td>
            <td style="width: 10%;">
            </td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 55% ">
                <table cellspacing="0" style="width: 100%; border: solid 2px #000000; ">
                    <tr>
                        <td style="width: 100%; font-size: 12pt;">
                            <span style="font-size: 15pt; font-weight: bold;">ADRESSE DE RETOUR<br></span>
                            <br>
                            <b>Entrepot des Bois</b><br>
                            sur une grande route<br>
                            00000 - Spipu Ville<br>
                            <br>
                            Date : <?php echo date('d/m/Y'); ?><br>
                            Dossier suivi par <b>Mle Jesuis CELIBATAIRE</b><br>
                            Tel : 33 (0) 1 00 00 00 00<br>
                            Email : on_va@chez.moi<br>
                        </td>
                    </tr>
                </table>
                <br>&nbsp;
            </td>
            <td style="width: 4%"></td>
            <td style="width: 37% ">
                <table cellspacing="0" style="width: 100%; border: solid 2px #000000; font-size: 12pt;">
                    <tr><td style="width: 40%;">Référence :        </td><td style="width: 60%;">71326</td></tr>
                    <tr><td style="width: 40%;">Client :        </td><td style="width: 60%;">M. Albert Dupont</td></tr>
                    <tr><td style="width: 40%;">Adresse :        </td><td style="width: 60%;">Résidence perdue<br>1, rue sans nom<br>00 000 - Pas de Ville</td></tr>
                    <tr><td style="width: 40%;">TEL :             </td><td style="width: 60%;">33 (0) 1 00 00 00 00</td></tr>
                    <tr><td style="width: 40%;">FAX :            </td><td style="width: 60%;">33 (0) 1 00 00 00 01</td></tr>
                    <tr><td style="width: 40%;">Code Client    :    </td><td style="width: 60%;">00C4520100A</td></tr>
                </table>
                <table cellspacing="0" style="width: 100%; border: solid 2px #000000">
                    <tr>
                        <th style="width: 40%;">Motif de la Reprise</th>
                        <td style="width: 60%;">Produit non Conforme</td>
                    </tr>
                </table>
                <br>
            </td>
            <td style="width: 4%"></td>
        </tr>
        <tr>
            <td style="width:55%;">
                <table cellspacing="0" style="padding: 1px; width: 100%; border: solid 2px #000000; font-size: 11pt; ">
                    <tr>
                        <th style="width: 100%; text-align: center; border: solid 1px #000000;" colspan="4">
                            Partie réservée à Spipu Corp
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 100%; text-align: center; border: solid 1px #000000;" colspan="4">
                            QUANTITE PREVUE AU CHARGEMENT
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 15%; border: solid 1px #000000;">Produit</th>
                        <th style="width: 55%; border: solid 1px #000000;">Designation</th>
                        <th style="width: 15%; border: solid 1px #000000;">Neuf</th>
                        <th style="width: 15%; border: solid 1px #000000;">Abîmé</th>
                    </tr>
<?php
$i=0;
foreach ($produits as $produit) {
    $i++;
?>
                    <tr>
                        <td style="width: 15%; border: solid 1px #000000;"><?php echo $produit[0];        ?></td>
                        <td style="width: 55%; border: solid 1px #000000;text-align: left;"><?php echo $produit[1];        ?></td>
                        <td style="width: 15%; border: solid 1px #000000;"><?php echo $produit[4];        ?></td>
                        <td style="width: 15%; border: solid 1px #000000;"><?php echo $produit[2]-$produit[4];        ?></td>
                    </tr>

<?php
}
for ($i; $i<12; $i++) {
?>
                    <tr>
                        <td style="width: 15%; border: solid 1px #000000;">&nbsp;</td>
                        <td style="width: 55%; border: solid 1px #000000;">&nbsp;</td>
                        <td style="width: 15%; border: solid 1px #000000;">&nbsp;</td>
                        <td style="width: 15%; border: solid 1px #000000;">&nbsp;</td>
                    </tr>
<?php
}
?>
                </table>
                <br>
                <table cellspacing="0" style="width: 100%; text-align: left; font-size: 8pt">
                    <tr>
                        <td style="width: 100%">
                            <b><u>Conditions des Retours</u></b><br>
                            1 - il faut des conditions<br>
                            2 - encore des conditions<br>
                            3 - toujours des conditions<br>
                        </td>
                    </tr>
                </table>
                <br>
                <table cellspacing="0" style="width: 100%; border: solid 2px #000000; text-align: center; font-size: 10pt">
                    <tr>
                        <td style="width: 30%"></td>
                        <td style="width: 40%">ACCORD SOCIETE</td>
                        <td style="width: 30%"></td>
                    </tr>
                    <tr>
                        <td style="width: 30%"><br><br>M. XX</td>
                        <td style="width: 40%"></td>
                        <td style="width: 30%"><br><br>Mme XY</td>
                    </tr>
                </table>
            </td>
            <td style="width: 4%"></td>
            <td style="width: 37%;">
                <table cellspacing="0" style="padding: 1px; width: 100%; border: solid 2px #000000; font-size: 11pt; ">
                    <tr>
                        <th style="width: 100%; text-align: center; border: solid 1px #000000;" colspan="2">
                            Partie réservée à l'entrepôt
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 100%; text-align: center; border: solid 1px #000000;" colspan="2">
                            QUANTITE PREVUE AU CHARGEMENT
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 50%; border: solid 1px #000000;">Produit neuf                </th>
                        <th style="width: 50%; border: solid 1px #000000;">Produit à reconditionner    </th>
                    </tr>
<?php
    for ($i=0; $i<12; $i++) {
?>
                    <tr>
                        <td style="width: 50%; border: solid 1px #000000;">&nbsp;</td>
                        <td style="width: 50%; border: solid 1px #000000;">&nbsp;</td>
                    </tr>
<?php
    }
?>
                </table>
                <br>
                <table cellspacing="0" style="width: 100%; border: solid 2px #000000; text-align: left; font-size: 10pt">
                    <tr>
                        <th style="width: 30%;">
                            Commentaire<br>
                            Retour :<br>
                            &nbsp;<br>
                            &nbsp;<br>
                        </th>
                        <td style="width: 70%;">
                        </td>
                    </tr>
                </table>
                <br>
                <br>
                <span style="font-size: 13pt"><b><u>A COLLER IMPERATIVEMENT SUR LES COLIS</u></b></span>
            </td>
            <td style="width: 4%"></td>
        </tr>
    </table>
</page>