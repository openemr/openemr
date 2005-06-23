<?
# print scanned documents

include("../../../library/api.inc");

formHeader("Patient's Scanned Documents");

$row = formFetch('form_documents', $_GET['id']);

$doc=$row["document_path"]."/".$row["document_image"];
$thatfile=$row["document_image"];
$localpath="../../forms/documents/scanned/".$GLOBALS['pid'];
$relink=$localpath."/".$thatfile;

echo ("<span class=text>Document path on server is: $doc ");
echo ("<br>");
echo ("Document Source is: ");
echo $row["document_source"];
echo ("<br>");
echo $row["document_description"];
echo ("<br>");
echo ("<IMG SRC=\"$relink\" ALT=\"$relink\">");

?>
<hr>
<a href="../../patient_file/encounter/patient_encounter.php">Done</a>

<?php
formFooter();
?>
