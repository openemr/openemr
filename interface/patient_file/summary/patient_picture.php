<?
require_once("../../globals.php");
require_once("$srcdir/sql.inc");
function pic_array() {
    $picture_directory = "Patient Photograph"; //change this if you want
    $pics = array();
    $sql_query = "select documents.url, documents.id from documents join categories_to_documents on documents.id = categories_to_documents.document_id join categories on categories.id = categories_to_documents.category_id where categories.name like '$picture_directory' and documents.foreign_id = ".$_SESSION['pid'];
    if ($query = sqlStatement($sql_query)) {
      $filename = '';
      while ($results = mysql_fetch_array($query)) {
        $expl = explode("/",$results['url']);
        $filename = $expl[sizeof($expl)-1];
        $doc_id = $results['id'];
        array_push($pics,"<iframe border='0' frameborder='0' style='border:none;' width='320' height='240' type='image/pjpeg' src='".$GLOBALS['webroot']."/controller.php?document&retrieve&patient_id=".$pid."&document_id=".$doc_id."&".$filename."&as_file=true'></iframe>");
      }
    }
    return $pics;
}
?>
