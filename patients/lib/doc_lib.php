<?php
/** 
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
$ignoreAuth = true;
//require_once ( dirname( __file__ ) . "/../verify_session.php" );
session_start();
if ( isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite']) ) {
    $pid = $_SESSION['pid'];
    $ignoreAuth = true;
    $sanitize_all_escapes = true;
    $fake_register_globals = false;
    require_once ( dirname( __FILE__ ) . "/../../interface/globals.php" );
}
else {
    session_destroy();
    $ignoreAuth = false;
    $sanitize_all_escapes = true;
    $fake_register_globals = false;
    require_once ( dirname( __FILE__ ) . "/../../interface/globals.php" );
    if ( ! isset($_SESSION['authUserID']) ){
        $landingpage = "index.php";
        header('Location: '.$landingpage);
        exit;
    }
}

require_once ( "$srcdir/classes/Document.class.php" );
require_once ( "$srcdir/classes/Note.class.php" );
require_once ( "$srcdir/formatting.inc.php" );
require_once ( "$srcdir/htmlspecialchars.inc.php" );
require_once ( "$srcdir/html2pdf/vendor/autoload.php" );
require_once (dirname( __FILE__ )."/appsql.class.php" );

$logit = new ApplicationTable();
$htmlin = $_REQUEST['content'];
$dispose = $_POST['handler'];

try{
    $form_filename = $_REQUEST['docid'] . '_' . $GLOBALS['pid'] . '.pdf';
    $templatedir = $GLOBALS['OE_SITE_DIR'] . "/../../patients/patient_documents";
    $templatepath = "$templatedir/$form_filename";
    $htmlout = '';
    $pdf = new HTML2PDF( $GLOBALS['pdf_layout'], $GLOBALS['pdf_size'], $GLOBALS['pdf_language'], true,
            'UTF-8', array ($GLOBALS['pdf_left_margin'],$GLOBALS['pdf_top_margin'],$GLOBALS['pdf_right_margin'],$GLOBALS['pdf_bottom_margin']
    ) );
    $pdf->writeHtml( $htmlin, false );
    if( $dispose == 'download' ){
        header( 'Content-type: application/pdf' );
        header( 'Content-Disposition: attachment; filename=$form_filename' );
        $pdf->Output( $form_filename, 'D' );
        $logit->portalLog('download document',$_SESSION['pid'],('document:'.$form_filename));
    }
    if( $dispose == 'view' ){
        Header( "Content-type: application/pdf" );
        $pdf->Output( $templatepath, 'I' );
    }
    if( $dispose == 'chart' ){
        $data = $pdf->Output( $form_filename, 'S' );
        ob_start();
        $d = new Document();
        $rc = $d->createDocument( $GLOBALS['pid'], 4, $form_filename, 'application/pdf', $data );
        ob_clean();
        echo $rc;
        $logit->portalLog('chart document',$_SESSION['pid'],('document:'.$form_filename));
        /* if(isset($_SERVER["HTTP_REFERER"])){
          header("Location: {$_SERVER["HTTP_REFERER"]}");
        } */
    // $data = file_get_contents('out.pdf', $binary);
    // readfile("$templatepath"); /**/
    exit(0);
    };
}
catch(Exception $e){
    echo 'Message: ' .$e->getMessage();
    die(xlt("no signature in document"));
}
function doc_toDoc( $htmlin ){
    header( "Content-type: application/vnd.oasis.opendocument.text" );
    header( "Content-Disposition: attachment;Filename=document_name.html" );
    echo "<html>";
    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
    echo "<body>";
    echo $htmlin;
    echo "</body>";
    echo "</html>";
    ob_clean();
    flush();
    readfile( $fname );
};

?>