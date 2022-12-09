<?php 
/** **************************************************************************
 *	LETTERS/ProgressWriter.PHP
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
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/tcpdf/tcpdf.php");
//require_once("{$GLOBALS['srcdir']}/tcpdf/fpdi.php");

if (!class_exists("ProgressPDF")) {
	/**
	 * The class LetterPDF is used to generate merge letter documents for
	 * the reporting engine. It utilizes the TCPDF library routines to 
	 * generate the PDF documents.
	 *
	 */
	class ProgressPDF extends TCPDF {
		/**
		 * Overrides the default header method to produce a custom document header.
		 * @return null
		 * 
		 */
		public function Header() {
			$page = $this->PageNo();
			if (strtotime($this->merge_data->date) === false) $this->merge_data->date = date('Y-m-d');
			$date = date('d F Y', strtotime($this->merge_data->date));
			$patient = $this->merge_data->pat_lname . ", ";
			$patient .= $this->merge_data->pat_fname . " ";
			$patient .= $this->merge_data->pat_mname;
			$patient = strtoupper($patient);
			$referrer = strtoupper($this->merge_data->referrer);
			$provider = strtoupper($this->merge_data->provider);
			$pages = $this->getAliasNbPages();
				
			$header = <<<EOD
<table style="width:100%"><tr>
<td style="text-align:left">
<span style="font-size:1.1em;font-weight:bold">$patient</span><br/>
Hillsboro Cardiology, PC<br/>
$date<br/>
Page $page of $pages
</td>
<td style="text-align:right">
<small>REFERRED: </small><span style="font-weight:bold">$referrer</span><br/>
<small>PROVIDER: </small><span style="font-weight:bold">$provider</span><br/>
</td></tr></table>
EOD;
			// add the header to the document
			$this->writeHTMLCell(0,0,60,'',$header,0,1,0,1,'C');
		} // end header

		
		/**
		 * Overrides the default footer method to produce a custom document footer.
		 * @return null
		 * 
		 */
		public function Footer() {
			$page = $this->PageNo();
			$footer = <<<EOD
<div style="text-align:center;text-style:italic">
<h4>
545-C S.E. Oak Street&nbsp;&nbsp;&bull;&nbsp;&nbsp;Hillsboro, OR 97123-4117&nbsp;&nbsp;&bull;&nbsp;&nbsp;Phone (503) 648-0731&nbsp;&nbsp;&bull;&nbsp;&nbsp;Fax (503) 640-2747
</h4>
</div>
EOD;
			$this->writeHTMLCell(0,0,60,'',$footer,0,1,0,1);
		} // end footer
	} // end LetterPDF class
} // end if exists

/**
 *
 * The makeProgress() creates a PDF requisition.
 *
 * 1. Create a PDF document
 * 2. Store the document in the repository
 * 4. Return a reference to the document
 *
 * @access public
 * @param object $merge_data data object containing merge data
 * @return string $document PDF document as string
 * 
 */
if (!function_exists("makeProgress")) {
	/**
	 * The makeLetter function is used to generate the letter for
	 * the report. It utilizes the TCPDF library routines to 
	 * generate the PDF document.
	 */
	function makeProgress(&$merge_data) {
		// retrieve verify valid date
		if (strtotime($merge_data->date) === false) $merge_data->date = date('Y-m-d');
				
		// create new PDF document
		$pdf = new ProgressPDF('P', 'pt', 'letter', true, 'UTF-8', false);

		// data sharing
		$pdf->merge_data = $merge_data;
		
		// set document information
		$pdf->SetCreator('OpenEMR PRO');
		$pdf->SetAuthor('Hillsboro Cardiology, P.C.');
		$pdf->SetTitle('Consultation Results Document');

		// set margins
		$pdf->SetMargins(60, 90, 60);
		$pdf->SetHeaderMargin(25);
		$pdf->SetFooterMargin(55);
	
		// set auto page breaks / bottom margin
		$pdf->SetAutoPageBreak(TRUE, 50);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setJPEGQuality ( 90 );

		// eliminate blank line before lists
		$tagvs = array('ul' => array(0 => array('h' => '', 'n' => 0), 1 => array('h'=> '', 'n' => 0)), 'ol' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' =>'', 'n' => 0)));
		$pdf->setHtmlVSpace($tagvs);

		// set fonts
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->setHeaderFont(Array('times', '', 10));
		$pdf->setFooterFont(Array('times', '', 10));
		$pdf->SetFont('times', '', 11);

		// start page
		$pdf->AddPage();

		/*
		 * ------------------------------------------------------------------- 
		 *  Patient Information
		 * ------------------------------------------------------------------- 
		 */
		$patient = $merge_data->pat_fname . " ";
		if ($merge_data->pat_mname) $patient .= $merge_data->pat_mname . " ";
		$patient .= $merge_data->pat_lname;
		$patient = strtoupper($patient);

		$dob = '';
		if ($merge_data->pat_dob) $dob = date('m/d/Y', strtotime($merge_data->pat_dob));
		
		if (strtotime($merge_data->date) === false) $merge_data->date = date('Y-m-d');
		$date = date('d F Y', strtotime($merge_data->date));
		
		ob_start(); 
?>
<table style="width:100%;font-weight:bold">
	<tr>
		<td colspan="3" style="text-align:center">
			<span style="font-weight:bold;font-size:1.2em">RE-EVALUATION OFFICE VISIT</span>
			<br/>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo $patient ?>
		</td>
		<td style="text-align:center">
			<?php if ($dob) echo "PATIENT DOB: ".$dob ?>
		</td>
		<td style="text-align:right">
			<?php if ($merge_data->pat_id) echo "PATIENT ID: ".$merge_data->pat_id ?>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(30);

		/*
		 * -------------------------------------------------------------------
		 *  Reason for Consultation
		 * -------------------------------------------------------------------
		 */
		
		ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:17%;border-bottom:1px solid black">
			<h4>DIAGNOSES</h4>
		</td>
		<td style="width:83%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo nl2br($merge_data->diagnoses); ?>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(15);		
		
		/*
		 * -------------------------------------------------------------------
		 *  Interval History
		 * -------------------------------------------------------------------
		 */
		
		ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:26%;border-bottom:1px solid black">
			<h4>INTERVAL HISTORY</h4>
		</td>
		<td style="width:74%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo nl2br($merge_data->hpi); ?>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(15);		
		
		/*
		 * -------------------------------------------------------------------
		 *  Allergies & Medications
		 * -------------------------------------------------------------------
		 */
		
		ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:15%;border-bottom:1px solid black">
			<h4>ALLERGIES</h4>
		</td>
		<td style="width:35%">&nbsp;</td>
		<td style="width:30%;border-bottom:1px solid black">
			<h4>CURRENT MEDICATIONS</h4>
		</td>
		<td style="width:20%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
<?php 		
		if (is_array($merge_data->allergy)) {
			echo "Allergies include:\n";
			echo "<ol>\n";
			foreach ($merge_data->allergy AS $allergy) { 
				echo "<li>" . $allergy . "</li>\n";
			}
			echo "</ol>\n";
		}
		else {
			echo "No allergies on file";
		}
?>
		</td>
		<td colspan="2">
<?php 		
		if (is_array($merge_data->meds)) {
			echo "Current medications include:\n";
			echo "<ol>\n";
			foreach ($merge_data->meds AS $drug) { 
				echo "<li>" . $drug . "</li>\n";
			}
			echo "</ol>\n";
		}
		else {
			echo "No current medications on file";
		}
?>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(15);
		
		/*
		 * -------------------------------------------------------------------
		 *  General Exam
		 * -------------------------------------------------------------------
		 */
		
		if ($merge_data->exam) {
			ob_start();
?>
<table style="width:100%">
	<tr>
		<td style="width:21%;border-bottom:1px solid black">
			<h4>GENERAL EXAM</h4>
		</td>
		<td style="width:78%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo $merge_data->exam; ?>
		</td>
	</tr>
</table>
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(15);		
		} // end exam
								
		/*
		 * -------------------------------------------------------------------
		 *  Assessment
		 * -------------------------------------------------------------------
		 */
		
		if ($merge_data->assessment) {
			ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:47%;border-bottom:1px solid black">
			<h4>ASSESSMENT &amp; RECOMMENDATIONS</h4>
				</td>
				<td style="width:53%">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php echo nl2br($merge_data->assessment); ?>
				</td>
			</tr>
		</table>
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(15);		
		} // end assessment
										
		// finish page
		$pdf->lastPage();

//		$TEST = true;
//		if ($TEST) {
//			$pdf->Output('label.pdf', 'I'); // force display download
//		}
//		else {
//			$document = $pdf->Output('requisition.pdf','S'); // return as variable
			
//			$CMDLINE = "lpr -P $printer ";
//			$pipe = popen("$CMDLINE" , 'w' );
//			if (!$pipe) {
//				echo "Label printing failed...";
//			}
//			else {
//				fputs($pipe, $label);
//				pclose($pipe);
//				echo "Labels printing at $printer ...";
//			}
//		}

		$document = $pdf->Output('order'.$order_data->order_number.'.pdf','S'); // return as variable
		return $document;

	} // end makeLetter
} // end if exists
