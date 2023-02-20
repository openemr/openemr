<?php 
/** **************************************************************************
 *	LETTERS/SINGLE.PHP
 *
 *	Copyright (c)2014 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package mdts
 *  @subpackage letters
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *  @uses generate.inc.php
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/tcpdf/tcpdf.php");
require_once("{$GLOBALS['srcdir']}/tcpdf/fpdi/fpdi.php");
require_once("{$GLOBALS['srcdir']}/classes/Document.class.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.class.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmtstandard.inc");

// get print selection
if ($_REQUEST['id']) 
	$print_id = trim($_REQUEST['id']);
else 
	die('FATAL ERROR: missing form identifier for letter print routine...');

// check if document exists
$doc_file = '';
$form_data = new wmtForm('ext_exam2',$print_id);

// do we already have the letter document?
if ($doc_id = $form_data->referral_docid) {
	$d = new Document($doc_id);
	$doc_file = $d->get_url_filepath();
	if (!file_exists($doc_file)) $doc_file = ''; // missing file
}

// need to generate the letter document
if (!$doc_file) { 
	require_once("{$GLOBALS['srcdir']}/wmt/letters/LetterWriter.php");
	require_once("{$GLOBALS['srcdir']}/wmt/letters/ProgressWriter.php");
	require_once("{$GLOBALS['include_root']}/forms/ext_exam2/letters_main.php");
	
	$doc_file = letters_main($print_id, true);
}
	
// establish document generation class
class Pdf_concat extends FPDI {
	public function Footer() {}
	public function Header() {}
	
	var $files = array();
	 
     function setFiles($files) {
          $this->files = $files;
     }
	 
     function concat() {
     	foreach($this->files AS $file) {
			$pagecount = $this->setSourceFile($file);
            for ($i = 1; $i <= $pagecount; $i++) {
	        	$tplidx = $this->ImportPage($i);
	            $s = $this->getTemplatesize($tplidx);
	            $this->AddPage('P', array($s['w'], $s['h']));
	            $this->useTemplate($tplidx);
			}
		}	
	}
}
	
// create new FPDI object
$fpdi = new Pdf_concat();
	
// generate an output file		
$pagecount = $fpdi->setSourceFile($doc_file);
for ($i = 1; $i <= $pagecount; $i++) {
	$tplidx = $fpdi->ImportPage($i);
	$s = $fpdi->getTemplatesize($tplidx);
	$fpdi->AddPage('P', array($s['w'], $s['h']));
	$fpdi->useTemplate($tplidx);
}
	
// return the merged PDF output document
$fpdi->Output("test.pdf", "I");
	
exit;
