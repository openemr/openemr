<?php
/** **************************************************************************
 *	PDF.CLASS.PHP
 *
 *	Copyright (c)2016 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage pdf
 *  @version 1.0.0
 *  @category PDF Document Class
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

// Base PDF generation library
require_once($GLOBALS['srcdir']."/tcpdf/tcpdf.php");

/**
 * Provides standardized processing for PDF document generation.
 *
 * @package wmt
 * @subpackage pdf
 */
class PDF extends \TCPDF {
	/**
	 * Overrides the default header method to produce a custom document header.
	 * @return null
	 *
	 */
	public function Header() {
			
		// CUSTOM HEADER CODE

	} // end header
	
	/**
	 * Overrides the default footer method to produce a custom document footer.
	 * @return null
	 *
	 */
	public function Footer() {
			
		// CUSTOM FOOTER CODE
			
	} // end footer
		
	/**
	 * Provides a standardized setup function
	 * @param title - document title
	 */
	public function __construct($title = 'PDF DOCUMENT') {
		// create new PDF document
		parent::__construct('P', 'pt', 'letter', true, 'UTF-8', false);

		// set document information
		$this->SetCreator('OpenEMR');
		$this->SetAuthor('Williams Medical Technologies, Inc.');
		$this->SetTitle($title);

		// set header and footer fonts
		//$this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		//$this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// remove default header/footer
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
		
		// set default monospaced font
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set auto page breaks / bottom margin
		$this->SetAutoPageBreak(TRUE, 65);

		// set image scale factor
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$this->setJPEGQuality ( 90 );

		// set font
		$this->SetFont('helvetica', '', 10);
			
		// set margins
		$this->SetMargins(30, PDF_MARGIN_TOP, 30);
		$this->SetHeaderMargin(15);
		$this->SetFooterMargin(90);

	}
	
	/**
	 * Provides a standardized write function
	 * @param content - html section markup
	 */
	public function wmtWrite($content) {
		// write section (html, add new line, no background, no height reset, no padding, alignment)
		$this->writeHTML($content,true,false,false,false,'L');
	}
}
