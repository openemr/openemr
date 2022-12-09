<?php 
/** **************************************************************************
 *	LETTERS/LETTER_GENERATE.PHP
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

if (!class_exists("LetterPDF")) {
	/**
	 * The class LetterPDF is used to generate merge letter documents for
	 * the reporting engine. It utilizes the TCPDF library routines to 
	 * generate the PDF documents.
	 *
	 */
	class LetterPDF extends TCPDF {
		/**
		 * Overrides the default header method to produce a custom document header.
		 * @return null
		 * 
		 */
		public function Header() {
			$page = $this->PageNo();
			if ($page > 1) { // starting on second page
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
			} // end if second page
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
 * The makeLetter() creates a PDF requisition.
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
if (!function_exists("makeLetter")) {
	/**
	 * The makeLetter function is used to generate the letter for
	 * the report. It utilizes the TCPDF library routines to 
	 * generate the PDF document.
	 */
	function makeLetter(&$merge_data) {
		// retrieve verify valid date
		if (strtotime($merge_data->date) === false) $merge_data->date = date('Y-m-d');
				
		// create new PDF document
		$pdf = new LetterPDF('P', 'pt', 'letter', true, 'UTF-8', false);

		// data sharing
		$pdf->merge_data = $merge_data;
		
		// set document information
		$pdf->SetCreator('OpenEMR PRO');
		$pdf->SetAuthor('Hillsboro Cardiology, P.C.');
		$pdf->SetTitle('Consultation Results Document');

		// set margins
		$pdf->SetMargins(60, 80, 60);
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
		 *  Letter Head
		 * ------------------------------------------------------------------- 
		 */
		ob_start(); 
?>
<table style="width:100%">
	<tr>
		<td style="width:50%;border-bottom:1px solid black">
			<h1 style="font-style:italic"><span style="font-size:1.5em">H</span>illsboro <span style="font-size:1.5em">C</span>ardiology, <span style="font-size:1.5em">P</span>.<span style="font-size:1.5em">C</span>.</h1>
		</td>
		<td style="width:50%">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td>
			<br/><br/>
			<span style="font-style:italic;font-size:0.8em">Physicians, Board Certified in Cardiovascular Diseases</span>
		</td>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td style="text-align:right;font-style:italic">
			Steven D. Promisloff, MD, FACC<br/>
			Daniel W. Isenbarger, MC, FACC<br/>
			M. Darren Mitchell, MD, MPH<br/>
			Kevin J. Woolf, MD, FACC
		</td>
	</tr>
	<tr>
		<td>
			<span style="font-size:1.2em"><?php echo date('d F Y',strtotime($merge_data->date)); ?></span>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td style="text-align:right;font-style:italic;font-size:1.2em;">
			<br/><br/>
			<span style="font-size:0.8em">Referring Physician:&nbsp; </span>Edward F. Clarke, MD
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
		<td style="width:35%;border-bottom:1px solid black">
			<h4>REASON FOR CONSULTATION</h4>
		</td>
		<td style="width:65%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo nl2br($merge_data->reason); ?>
		</td>
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(15);		
		
		/*
		 * -------------------------------------------------------------------
		 *  History of Present Illness
		 * -------------------------------------------------------------------
		 */
		
		ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:38%;border-bottom:1px solid black">
			<h4>HISTORY OF PRESENT ILLNESS</h4>
		</td>
		<td style="width:62%">&nbsp;</td>
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
		 *  Past Medical History
		 * -------------------------------------------------------------------
		 */
		
		ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:30%;border-bottom:1px solid black">
			<h4>PAST MEDICAL HISTORY</h4>
		</td>
		<td style="width:70%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
<?php 		
		if (is_array($merge_data->history)) {
			echo "Significant for:\n";
			echo "<ol style=\"margin:0;\">\n";
			foreach ($merge_data->history AS $history) { 
				echo "<li>" . $history . "</li>\n";
			}
			echo "</ol>\n";
		}
		else {
			echo "No medical history on file";
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
		 *  Allergies
		 * -------------------------------------------------------------------
		 */
		
		ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:15%;border-bottom:1px solid black">
			<h4>ALLERGIES</h4>
		</td>
		<td style="width:85%">&nbsp;</td>
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
	</tr>
</table>
<?php 
		$output = ob_get_clean(); 
		$pdf->writeHTMLCell(0,0,'','',$output,0,1);
		$pdf->ln(15);
		
		/*
		 * -------------------------------------------------------------------
		 *  Current Medications
		 * -------------------------------------------------------------------
		 */
		
		ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:30%;border-bottom:1px solid black">
			<h4>CURRENT MEDICATIONS</h4>
		</td>
		<td style="width:70%">&nbsp;</td>
	</tr>
	<tr>
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
		 *  Social History
		 * -------------------------------------------------------------------
		 */
		
		if ($merge_data->social) {
			ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:21%;border-bottom:1px solid black">
			<h4>SOCIAL HISTORY</h4>
		</td>
		<td style="width:79%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo nl2br($merge_data->social); ?>
		</td>
	</tr>
</table>
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(15);		
		} // end social history
		
		/*
		 * -------------------------------------------------------------------
		 *  Family History
		 * -------------------------------------------------------------------
		 */
		
		if ($merge_data->family) {
			ob_start();
?>
<table nobr="true" style="width:100%">
	<tr>
		<td style="width:22%;border-bottom:1px solid black">
			<h4>FAMILY HISTORY</h4>
		</td>
		<td style="width:78%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo nl2br($merge_data->family); ?>
		</td>
	</tr>
</table>
<?php 
			$output = ob_get_clean();
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(15);		
		} // end family history

		/*
		 * -------------------------------------------------------------------
		 *  Review of Systems
		 * -------------------------------------------------------------------
		 */
		
		if ($merge_data->ros) {
			ob_start();
?>
<table style="width:100%">
	<tr>
		<td style="width:26%;border-bottom:1px solid black">
			<h4>REVIEW OF SYSTEMS</h4>
		</td>
		<td style="width:74%">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo $merge_data->ros; ?>
		</td>
	</tr>
</table>
<?php 
			$output = ob_get_clean(); 
			$pdf->writeHTMLCell(0,0,'','',$output,0,1);
			$pdf->ln(15);		
		} // end ros

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
