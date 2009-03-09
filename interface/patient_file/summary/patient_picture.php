<?
require_once("../../globals.php");
require_once("$srcdir/sql.inc");
function pic_array() {
    $picture_directory = "Patient Photograph"; //change this if you want
    $pics = array();
    $sql_query = "select documents.url from documents join categories_to_documents on documents.id = categories_to_documents.document_id join categories on categories.id = categories_to_documents.category_id where categories.name like '$picture_directory' and documents.foreign_id = ".$_SESSION['pid'];
    if ($query = sqlStatement($sql_query)) {
      $filename = '';
      while ($results = mysql_fetch_array($query)) {
        $tmp = explode("documents",$results['url']);
        if (isset($tmp[1])) {
          array_push($pics,"<div name='Patient Photograph' class='patient_pic'><img src='".$GLOBALS['webroot']."/documents".$tmp[1]."' alt='Patient Photograph'></div>\n");
        }
      }
    }
    return $pics;
}
?>
