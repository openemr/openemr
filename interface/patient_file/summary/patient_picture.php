<?php



require_once("../../globals.php");
function pic_array()
{
    $picture_directory = "Patient Photograph"; //change this if you want
    $pics = array();
    $sql_query = "select documents.id from documents join categories_to_documents on documents.id = categories_to_documents.document_id join categories on categories.id = categories_to_documents.category_id where categories.name like ? and documents.foreign_id = ?";
    if ($query = sqlStatement($sql_query, array($picture_directory, $_SESSION['pid']))) {
        while ($results = sqlFetchArray($query)) {
            $tmp = $results['id'];
            if (isset($tmp)) {
                array_push($pics, "<div name='Patient Photograph' class='patient_pic'><img src='".$GLOBALS['webroot']."/controller.php?document&retrieve&patient_id=".htmlspecialchars($_SESSION['pid'], ENT_QUOTES)."&document_id=".htmlspecialchars($tmp, ENT_QUOTES)."&as_file=false' alt='Patient Photograph'></div>\n");
            }
        }
    }

    return $pics;
}
