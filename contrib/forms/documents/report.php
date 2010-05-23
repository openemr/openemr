<?php
# for reporting the files scanned on the forms
# this is for scanned docs.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");



function documents_report( $pid, $encounter, $cols, $id) {

	$row = formFetch("form_documents", $id);

	if ($row) {
		// render 
		$doc_path=stripslashes ($row['document_path']);
		$doc_image=stripslashes ($row['document_image']);
		$doc_source=trim ( stripslashes ($row['document_source']));
		$doc_description=trim ( stripslashes ($row['document_description']));
		$doc_date=$row['date'];
		$doc_date=substr ($doc_date,5,2)."/".substr ($doc_date,8,2)."/".substr ($doc_date,0,4);

		echo ("<table>");
		echo ("<tr><td>Document Scanned on: $doc_date</td></tr>");
		if ($doc_source)
			echo ("<tr><td>Received from: $doc_source </td></tr>");
		if ($doc_description)
			echo ("<tr><td>Description: $doc_description </td></tr>");
		echo ("<tr><td><img src=\"../../forms/documents/scanned/${pid}/${doc_image}\"></td></tr>");
		echo ("</table>");
		// eof render
	}
}
?> 
