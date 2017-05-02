<?php

class Tag
{

	private $mpdf;

	public function __construct(mPDF $mpdf)
	{
		$this->mpdf = $mpdf;
	}

	function OpenTag($tag, $attr, &$ahtml, &$ihtml)
	{ // mPDF 6
		//Opening tag
		// mPDF 6
		// Correct for tags where HTML5 specifies optional end tags excluding table elements (cf WriteHTML() )
		if ($this->mpdf->allow_html_optional_endtags) {
			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['tag'])) {
				$closed = false;
				// li end tag may be omitted if immediately followed by another li element
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'LI' && $tag == 'LI') {
					$this->CloseTag('LI', $ahtml, $ihtml);
					$closed = true;
				}
				// dt end tag may be omitted if immediately followed by another dt element or a dd element
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'DT' && ($tag == 'DT' || $tag == 'DD')) {
					$this->CloseTag('DT', $ahtml, $ihtml);
					$closed = true;
				}
				// dd end tag may be omitted if immediately followed by another dd element or a dt element
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'DD' && ($tag == 'DT' || $tag == 'DD')) {
					$this->CloseTag('DD', $ahtml, $ihtml);
					$closed = true;
				}
				// p end tag may be omitted if immediately followed by an address, article, aside, blockquote, div, dl, fieldset, form,
				// h1, h2, h3, h4, h5, h6, hgroup, hr, main, nav, ol, p, pre, section, table, ul
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'P' && ($tag == 'P' || $tag == 'DIV' || $tag == 'H1' || $tag == 'H2' || $tag == 'H3' || $tag == 'H4' || $tag == 'H5' || $tag == 'H6' || $tag == 'UL' || $tag == 'OL' || $tag == 'TABLE' || $tag == 'PRE' || $tag == 'FORM' || $tag == 'ADDRESS' || $tag == 'BLOCKQUOTE' || $tag == 'CENTER' || $tag == 'DL' || $tag == 'HR' || $tag == 'ARTICLE' || $tag == 'ASIDE' || $tag == 'FIELDSET' || $tag == 'HGROUP' || $tag == 'MAIN' || $tag == 'NAV' || $tag == 'SECTION')) {
					$this->CloseTag('P', $ahtml, $ihtml);
					$closed = true;
				}
				// option end tag may be omitted if immediately followed by another option element (or if it is immediately followed by an optgroup element)
				if (!$closed && $this->mpdf->blk[$this->mpdf->blklvl]['tag'] == 'OPTION' && $tag == 'OPTION') {
					$this->CloseTag('OPTION', $ahtml, $ihtml);
					$closed = true;
				}
				// Table elements - see also WriteHTML()
				if (!$closed && ($tag == 'TD' || $tag == 'TH') && $this->mpdf->lastoptionaltag == 'TD') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
				if (!$closed && ($tag == 'TD' || $tag == 'TH') && $this->mpdf->lastoptionaltag == 'TH') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
				if (!$closed && $tag == 'TR' && $this->mpdf->lastoptionaltag == 'TR') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
				if (!$closed && $tag == 'TR' && $this->mpdf->lastoptionaltag == 'TD') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$this->CloseTag('TR', $ahtml, $ihtml);
					$this->CloseTag('THEAD', $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
				if (!$closed && $tag == 'TR' && $this->mpdf->lastoptionaltag == 'TH') {
					$this->CloseTag($this->mpdf->lastoptionaltag, $ahtml, $ihtml);
					$this->CloseTag('TR', $ahtml, $ihtml);
					$this->CloseTag('THEAD', $ahtml, $ihtml);
					$closed = true;
				} // *TABLES*
			}
		}

		$align = array('left' => 'L', 'center' => 'C', 'right' => 'R', 'top' => 'T', 'text-top' => 'TT', 'middle' => 'M', 'baseline' => 'BS', 'bottom' => 'B', 'text-bottom' => 'TB', 'justify' => 'J');

		switch ($tag) {

			case 'DOTTAB':
				$objattr = array();
				$objattr['type'] = 'dottab';
				$dots = str_repeat('.', 3) . "  "; // minimum number of dots
				$objattr['width'] = $this->mpdf->GetStringWidth($dots);
				$objattr['margin_top'] = 0;
				$objattr['margin_bottom'] = 0;
				$objattr['margin_left'] = 0;
				$objattr['margin_right'] = 0;
				$objattr['height'] = 0;
				$objattr['colorarray'] = $this->mpdf->colorarray;
				$objattr['border_top']['w'] = 0;
				$objattr['border_bottom']['w'] = 0;
				$objattr['border_left']['w'] = 0;
				$objattr['border_right']['w'] = 0;
				$objattr['vertical_align'] = 'BS'; // mPDF 6 DOTTAB

				$properties = $this->mpdf->cssmgr->MergeCSS('INLINE', $tag, $attr);
				if (isset($properties['OUTDENT'])) {
					$objattr['outdent'] = $this->mpdf->ConvertSize($properties['OUTDENT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				} else if (isset($attr['OUTDENT'])) {
					$objattr['outdent'] = $this->mpdf->ConvertSize($attr['OUTDENT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				} else {
					$objattr['outdent'] = 0;
				}

				$objattr['fontfamily'] = $this->mpdf->FontFamily;
				$objattr['fontsize'] = $this->mpdf->FontSizePt;

				$e = "\xbb\xa4\xactype=dottab,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
				/* -- TABLES -- */
				// Output it to buffers
				if ($this->mpdf->tableLevel) {
					if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
					} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
					}
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0; // reset
					$this->mpdf->_saveCellTextBuffer($e);
				} else {
					/* -- END TABLES -- */
					$this->mpdf->_saveTextBuffer($e);
				} // *TABLES*
				break;

			case 'PAGEHEADER':
			case 'PAGEFOOTER':
				$this->mpdf->ignorefollowingspaces = true;
				if ($attr['NAME']) {
					$pname = $attr['NAME'];
				} else {
					$pname = '_nonhtmldefault';
				} // mPDF 6

				$p = array(); // mPDF 6
				$p['L'] = array();
				$p['C'] = array();
				$p['R'] = array();
				$p['L']['font-style'] = '';
				$p['C']['font-style'] = '';
				$p['R']['font-style'] = '';

				if (isset($attr['CONTENT-LEFT'])) {
					$p['L']['content'] = $attr['CONTENT-LEFT'];
				}
				if (isset($attr['CONTENT-CENTER'])) {
					$p['C']['content'] = $attr['CONTENT-CENTER'];
				}
				if (isset($attr['CONTENT-RIGHT'])) {
					$p['R']['content'] = $attr['CONTENT-RIGHT'];
				}

				if (isset($attr['HEADER-STYLE']) || isset($attr['FOOTER-STYLE'])) { // font-family,size,weight,style,color
					if ($tag == 'PAGEHEADER') {
						$properties = $this->mpdf->cssmgr->readInlineCSS($attr['HEADER-STYLE']);
					} else {
						$properties = $this->mpdf->cssmgr->readInlineCSS($attr['FOOTER-STYLE']);
					}
					if (isset($properties['FONT-FAMILY'])) {
						$p['L']['font-family'] = $properties['FONT-FAMILY'];
						$p['C']['font-family'] = $properties['FONT-FAMILY'];
						$p['R']['font-family'] = $properties['FONT-FAMILY'];
					}
					if (isset($properties['FONT-SIZE'])) {
						$p['L']['font-size'] = $this->mpdf->ConvertSize($properties['FONT-SIZE']) * _MPDFK;
						$p['C']['font-size'] = $this->mpdf->ConvertSize($properties['FONT-SIZE']) * _MPDFK;
						$p['R']['font-size'] = $this->mpdf->ConvertSize($properties['FONT-SIZE']) * _MPDFK;
					}
					if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT'] == 'bold') {
						$p['L']['font-style'] = 'B';
						$p['C']['font-style'] = 'B';
						$p['R']['font-style'] = 'B';
					}
					if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE'] == 'italic') {
						$p['L']['font-style'] .= 'I';
						$p['C']['font-style'] .= 'I';
						$p['R']['font-style'] .= 'I';
					}
					if (isset($properties['COLOR'])) {
						$p['L']['color'] = $properties['COLOR'];
						$p['C']['color'] = $properties['COLOR'];
						$p['R']['color'] = $properties['COLOR'];
					}
				}
				if (isset($attr['HEADER-STYLE-LEFT']) || isset($attr['FOOTER-STYLE-LEFT'])) {
					if ($tag == 'PAGEHEADER') {
						$properties = $this->mpdf->cssmgr->readInlineCSS($attr['HEADER-STYLE-LEFT']);
					} else {
						$properties = $this->mpdf->cssmgr->readInlineCSS($attr['FOOTER-STYLE-LEFT']);
					}
					if (isset($properties['FONT-FAMILY'])) {
						$p['L']['font-family'] = $properties['FONT-FAMILY'];
					}
					if (isset($properties['FONT-SIZE'])) {
						$p['L']['font-size'] = $this->mpdf->ConvertSize($properties['FONT-SIZE']) * _MPDFK;
					}
					if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT'] == 'bold') {
						$p['L']['font-style'] = 'B';
					}
					if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE'] == 'italic') {
						$p['L']['font-style'] .='I';
					}
					if (isset($properties['COLOR'])) {
						$p['L']['color'] = $properties['COLOR'];
					}
				}
				if (isset($attr['HEADER-STYLE-CENTER']) || isset($attr['FOOTER-STYLE-CENTER'])) {
					if ($tag == 'PAGEHEADER') {
						$properties = $this->mpdf->cssmgr->readInlineCSS($attr['HEADER-STYLE-CENTER']);
					} else {
						$properties = $this->mpdf->cssmgr->readInlineCSS($attr['FOOTER-STYLE-CENTER']);
					}
					if (isset($properties['FONT-FAMILY'])) {
						$p['C']['font-family'] = $properties['FONT-FAMILY'];
					}
					if (isset($properties['FONT-SIZE'])) {
						$p['C']['font-size'] = $this->mpdf->ConvertSize($properties['FONT-SIZE']) * _MPDFK;
					}
					if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT'] == 'bold') {
						$p['C']['font-style'] = 'B';
					}
					if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE'] == 'italic') {
						$p['C']['font-style'] .= 'I';
					}
					if (isset($properties['COLOR'])) {
						$p['C']['color'] = $properties['COLOR'];
					}
				}
				if (isset($attr['HEADER-STYLE-RIGHT']) || isset($attr['FOOTER-STYLE-RIGHT'])) {
					if ($tag == 'PAGEHEADER') {
						$properties = $this->mpdf->cssmgr->readInlineCSS($attr['HEADER-STYLE-RIGHT']);
					} else {
						$properties = $this->mpdf->cssmgr->readInlineCSS($attr['FOOTER-STYLE-RIGHT']);
					}
					if (isset($properties['FONT-FAMILY'])) {
						$p['R']['font-family'] = $properties['FONT-FAMILY'];
					}
					if (isset($properties['FONT-SIZE'])) {
						$p['R']['font-size'] = $this->mpdf->ConvertSize($properties['FONT-SIZE']) * _MPDFK;
					}
					if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT'] == 'bold') {
						$p['R']['font-style'] = 'B';
					}
					if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE'] == 'italic') {
						$p['R']['font-style'] .= 'I';
					}
					if (isset($properties['COLOR'])) {
						$p['R']['color'] = $properties['COLOR'];
					}
				}
				if (isset($attr['LINE']) && $attr['LINE']) { // 0|1|on|off
					if ($attr['LINE'] == '1' || strtoupper($attr['LINE']) == 'ON') {
						$lineset = 1;
					} else {
						$lineset = 0;
					}
					$p['line'] = $lineset;
				}
				// mPDF 6
				if ($tag == 'PAGEHEADER') {
					$this->mpdf->DefHeaderByName($pname, $p);
				} else {
					$this->mpdf->DefFooterByName($pname, $p);
				}
				break;


			case 'SETPAGEHEADER': // mPDF 6
			case 'SETPAGEFOOTER':
			case 'SETHTMLPAGEHEADER':
			case 'SETHTMLPAGEFOOTER':
				$this->mpdf->ignorefollowingspaces = true;
				if (isset($attr['NAME']) && $attr['NAME']) {
					$pname = $attr['NAME'];
				} else if ($tag == 'SETPAGEHEADER' || $tag == 'SETPAGEFOOTER') {
					$pname = '_nonhtmldefault';
				} // mPDF 6
				else {
					$pname = '_default';
				}
				if (isset($attr['PAGE']) && $attr['PAGE']) {  // O|odd|even|E|ALL|[blank]
					if (strtoupper($attr['PAGE']) == 'O' || strtoupper($attr['PAGE']) == 'ODD') {
						$side = 'odd';
					} else if (strtoupper($attr['PAGE']) == 'E' || strtoupper($attr['PAGE']) == 'EVEN') {
						$side = 'even';
					} else if (strtoupper($attr['PAGE']) == 'ALL') {
						$side = 'both';
					} else {
						$side = 'odd';
					}
				} else {
					$side = 'odd';
				}
				if (isset($attr['VALUE']) && $attr['VALUE']) {  // -1|1|on|off
					if ($attr['VALUE'] == '1' || strtoupper($attr['VALUE']) == 'ON') {
						$set = 1;
					} else if ($attr['VALUE'] == '-1' || strtoupper($attr['VALUE']) == 'OFF') {
						$set = 0;
					} else {
						$set = 1;
					}
				} else {
					$set = 1;
				}
				if (isset($attr['SHOW-THIS-PAGE']) && $attr['SHOW-THIS-PAGE'] && ($tag == 'SETHTMLPAGEHEADER' || $tag == 'SETPAGEHEADER')) {
					$write = 1;
				} else {
					$write = 0;
				}
				if ($side == 'odd' || $side == 'both') {
					if ($set && ($tag == 'SETHTMLPAGEHEADER' || $tag == 'SETPAGEHEADER')) {
						$this->mpdf->SetHTMLHeader($this->mpdf->pageHTMLheaders[$pname], 'O', $write);
					} else if ($set && ($tag == 'SETHTMLPAGEFOOTER' || $tag == 'SETPAGEFOOTER')) {
						$this->mpdf->SetHTMLFooter($this->mpdf->pageHTMLfooters[$pname], 'O');
					} else if ($tag == 'SETHTMLPAGEHEADER' || $tag == 'SETPAGEHEADER') {
						$this->mpdf->SetHTMLHeader('', 'O');
					} else {
						$this->mpdf->SetHTMLFooter('', 'O');
					}
				}
				if ($side == 'even' || $side == 'both') {
					if ($set && ($tag == 'SETHTMLPAGEHEADER' || $tag == 'SETPAGEHEADER')) {
						$this->mpdf->SetHTMLHeader($this->mpdf->pageHTMLheaders[$pname], 'E', $write);
					} else if ($set && ($tag == 'SETHTMLPAGEFOOTER' || $tag == 'SETPAGEFOOTER')) {
						$this->mpdf->SetHTMLFooter($this->mpdf->pageHTMLfooters[$pname], 'E');
					} else if ($tag == 'SETHTMLPAGEHEADER' || $tag == 'SETPAGEHEADER') {
						$this->mpdf->SetHTMLHeader('', 'E');
					} else {
						$this->mpdf->SetHTMLFooter('', 'E');
					}
				}
				break;



			/* -- TOC -- */
			case 'TOC': //added custom-tag - set Marker for insertion later of ToC
				if (!class_exists('tocontents', false)) {
					include(_MPDF_PATH . 'classes/tocontents.php');
				}
				if (empty($this->mpdf->tocontents)) {
					$this->mpdf->tocontents = new tocontents($this);
				}
				$this->mpdf->tocontents->openTagTOC($attr);
				break;


			case 'TOCPAGEBREAK': // custom-tag - set Marker for insertion later of ToC AND adds PAGEBREAK
				if (!class_exists('tocontents', false)) {
					include(_MPDF_PATH . 'classes/tocontents.php');
				}
				if (empty($this->mpdf->tocontents)) {
					$this->mpdf->tocontents = new tocontents($this->mpdf);
				}
				list($isbreak, $toc_id) = $this->mpdf->tocontents->openTagTOCPAGEBREAK($attr);
				if ($isbreak)
					break;
				if (!isset($attr['RESETPAGENUM']) || $attr['RESETPAGENUM'] < 1) {
					$attr['RESETPAGENUM'] = 1;
				} // mPDF 6
			// No break - continues as PAGEBREAK...
			/* -- END TOC -- */


			case 'PAGE_BREAK': //custom-tag
			case 'PAGEBREAK': //custom-tag
			case 'NEWPAGE': //custom-tag
			case 'FORMFEED': //custom-tag

				if (isset($attr['SHEET-SIZE'])) {
					// Convert to same types as accepted in initial mPDF() A4, A4-L, or array(w,h)
					$prop = preg_split('/\s+/', trim($attr['SHEET-SIZE']));
					if (count($prop) == 2) {
						$newformat = array($this->mpdf->ConvertSize($prop[0]), $this->mpdf->ConvertSize($prop[1]));
					} else {
						$newformat = $attr['SHEET-SIZE'];
					}
				} else {
					$newformat = '';
				}

				$save_blklvl = $this->mpdf->blklvl;
				$save_blk = $this->mpdf->blk;
				$save_silp = $this->mpdf->saveInlineProperties();
				$save_ilp = $this->mpdf->InlineProperties;
				$save_bflp = $this->mpdf->InlineBDF;
				$save_bflpc = $this->mpdf->InlineBDFctr; // mPDF 6

				$mgr = $mgl = $mgt = $mgb = $mgh = $mgf = '';
				if (isset($attr['MARGIN-RIGHT'])) {
					$mgr = $this->mpdf->ConvertSize($attr['MARGIN-RIGHT'], $this->mpdf->w, $this->mpdf->FontSize, false);
				}
				if (isset($attr['MARGIN-LEFT'])) {
					$mgl = $this->mpdf->ConvertSize($attr['MARGIN-LEFT'], $this->mpdf->w, $this->mpdf->FontSize, false);
				}
				if (isset($attr['MARGIN-TOP'])) {
					$mgt = $this->mpdf->ConvertSize($attr['MARGIN-TOP'], $this->mpdf->w, $this->mpdf->FontSize, false);
				}
				if (isset($attr['MARGIN-BOTTOM'])) {
					$mgb = $this->mpdf->ConvertSize($attr['MARGIN-BOTTOM'], $this->mpdf->w, $this->mpdf->FontSize, false);
				}
				if (isset($attr['MARGIN-HEADER'])) {
					$mgh = $this->mpdf->ConvertSize($attr['MARGIN-HEADER'], $this->mpdf->w, $this->mpdf->FontSize, false);
				}
				if (isset($attr['MARGIN-FOOTER'])) {
					$mgf = $this->mpdf->ConvertSize($attr['MARGIN-FOOTER'], $this->mpdf->w, $this->mpdf->FontSize, false);
				}
				$ohname = $ehname = $ofname = $efname = '';
				if (isset($attr['ODD-HEADER-NAME'])) {
					$ohname = $attr['ODD-HEADER-NAME'];
				}
				if (isset($attr['EVEN-HEADER-NAME'])) {
					$ehname = $attr['EVEN-HEADER-NAME'];
				}
				if (isset($attr['ODD-FOOTER-NAME'])) {
					$ofname = $attr['ODD-FOOTER-NAME'];
				}
				if (isset($attr['EVEN-FOOTER-NAME'])) {
					$efname = $attr['EVEN-FOOTER-NAME'];
				}
				$ohvalue = $ehvalue = $ofvalue = $efvalue = 0;
				if (isset($attr['ODD-HEADER-VALUE']) && ($attr['ODD-HEADER-VALUE'] == '1' || strtoupper($attr['ODD-HEADER-VALUE']) == 'ON')) {
					$ohvalue = 1;
				} else if (isset($attr['ODD-HEADER-VALUE']) && ($attr['ODD-HEADER-VALUE'] == '-1' || strtoupper($attr['ODD-HEADER-VALUE']) == 'OFF')) {
					$ohvalue = -1;
				}
				if (isset($attr['EVEN-HEADER-VALUE']) && ($attr['EVEN-HEADER-VALUE'] == '1' || strtoupper($attr['EVEN-HEADER-VALUE']) == 'ON')) {
					$ehvalue = 1;
				} else if (isset($attr['EVEN-HEADER-VALUE']) && ($attr['EVEN-HEADER-VALUE'] == '-1' || strtoupper($attr['EVEN-HEADER-VALUE']) == 'OFF')) {
					$ehvalue = -1;
				}
				if (isset($attr['ODD-FOOTER-VALUE']) && ($attr['ODD-FOOTER-VALUE'] == '1' || strtoupper($attr['ODD-FOOTER-VALUE']) == 'ON')) {
					$ofvalue = 1;
				} else if (isset($attr['ODD-FOOTER-VALUE']) && ($attr['ODD-FOOTER-VALUE'] == '-1' || strtoupper($attr['ODD-FOOTER-VALUE']) == 'OFF')) {
					$ofvalue = -1;
				}
				if (isset($attr['EVEN-FOOTER-VALUE']) && ($attr['EVEN-FOOTER-VALUE'] == '1' || strtoupper($attr['EVEN-FOOTER-VALUE']) == 'ON')) {
					$efvalue = 1;
				} else if (isset($attr['EVEN-FOOTER-VALUE']) && ($attr['EVEN-FOOTER-VALUE'] == '-1' || strtoupper($attr['EVEN-FOOTER-VALUE']) == 'OFF')) {
					$efvalue = -1;
				}

				if (isset($attr['ORIENTATION']) && (strtoupper($attr['ORIENTATION']) == 'L' || strtoupper($attr['ORIENTATION']) == 'LANDSCAPE')) {
					$orient = 'L';
				} else if (isset($attr['ORIENTATION']) && (strtoupper($attr['ORIENTATION']) == 'P' || strtoupper($attr['ORIENTATION']) == 'PORTRAIT')) {
					$orient = 'P';
				} else {
					$orient = $this->mpdf->CurOrientation;
				}

				if (isset($attr['PAGE-SELECTOR']) && $attr['PAGE-SELECTOR']) {
					$pagesel = $attr['PAGE-SELECTOR'];
				} else {
					$pagesel = '';
				}

				// mPDF 6 pagebreaktype
				$pagebreaktype = $this->mpdf->defaultPagebreakType;
				if ($tag == 'FORMFEED') {
					$pagebreaktype = 'slice';
				} // can be overridden by PAGE-BREAK-TYPE
				$startpage = $this->mpdf->page;
				if (isset($attr['PAGE-BREAK-TYPE'])) {
					if (strtolower($attr['PAGE-BREAK-TYPE']) == 'cloneall' || strtolower($attr['PAGE-BREAK-TYPE']) == 'clonebycss' || strtolower($attr['PAGE-BREAK-TYPE']) == 'slice') {
						$pagebreaktype = strtolower($attr['PAGE-BREAK-TYPE']);
					}
				}
				if ($tag == 'TOCPAGEBREAK') {
					$pagebreaktype = 'cloneall';
				} else if ($this->mpdf->ColActive) {
					$pagebreaktype = 'cloneall';
				}
				// Any change in headers/footers (may need to _getHtmlHeight), or page size/orientation, @page selector, or margins - force cloneall
				else if ($mgr !== '' || $mgl !== '' || $mgt !== '' || $mgb !== '' || $mgh !== '' || $mgf !== '' ||
					$ohname !== '' || $ehname !== '' || $ofname !== '' || $efname !== '' ||
					$ohvalue || $ehvalue || $ofvalue || $efvalue ||
					$orient != $this->mpdf->CurOrientation || $newformat || $pagesel) {
					$pagebreaktype = 'cloneall';
				}

				// mPDF 6 pagebreaktype
				$this->mpdf->_preForcedPagebreak($pagebreaktype);

				$this->mpdf->ignorefollowingspaces = true;


				$resetpagenum = '';
				$pagenumstyle = '';
				$suppress = '';
				if (isset($attr['RESETPAGENUM'])) {
					$resetpagenum = $attr['RESETPAGENUM'];
				}
				if (isset($attr['PAGENUMSTYLE'])) {
					$pagenumstyle = $attr['PAGENUMSTYLE'];
				}
				if (isset($attr['SUPPRESS'])) {
					$suppress = $attr['SUPPRESS'];
				}

				if ($tag == 'TOCPAGEBREAK') {
					$type = 'NEXT-ODD';
				} else if (isset($attr['TYPE'])) {
					$type = strtoupper($attr['TYPE']);
				} else {
					$type = '';
				}

				if ($type == 'E' || $type == 'EVEN') {
					$this->mpdf->AddPage($orient, 'E', $resetpagenum, $pagenumstyle, $suppress, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
				} else if ($type == 'O' || $type == 'ODD') {
					$this->mpdf->AddPage($orient, 'O', $resetpagenum, $pagenumstyle, $suppress, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
				} else if ($type == 'NEXT-ODD') {
					$this->mpdf->AddPage($orient, 'NEXT-ODD', $resetpagenum, $pagenumstyle, $suppress, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
				} else if ($type == 'NEXT-EVEN') {
					$this->mpdf->AddPage($orient, 'NEXT-EVEN', $resetpagenum, $pagenumstyle, $suppress, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
				} else {
					$this->mpdf->AddPage($orient, '', $resetpagenum, $pagenumstyle, $suppress, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
				}

				/* -- TOC -- */
				if ($tag == 'TOCPAGEBREAK') {
					if ($toc_id) {
						$this->mpdf->tocontents->m_TOC[$toc_id]['TOCmark'] = $this->mpdf->page;
					} else {
						$this->mpdf->tocontents->TOCmark = $this->mpdf->page;
					}
				}
				/* -- END TOC -- */

				// mPDF 6 pagebreaktype
				$this->mpdf->_postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl);

				$this->mpdf->InlineProperties = $save_ilp;
				$this->mpdf->InlineBDF = $save_bflp;
				$this->mpdf->InlineBDFctr = $save_bflpc; // mPDF 6
				$this->mpdf->restoreInlineProperties($save_silp);

				break;


			/* -- TOC -- */
			case 'TOCENTRY':
				if (isset($attr['CONTENT']) && $attr['CONTENT']) {
					$objattr = array();
					$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES);
					$objattr['type'] = 'toc';
					$objattr['vertical-align'] = 'T';
					if (isset($attr['LEVEL']) && $attr['LEVEL']) {
						$objattr['toclevel'] = $attr['LEVEL'];
					} else {
						$objattr['toclevel'] = 0;
					}
					if (isset($attr['NAME']) && $attr['NAME']) {
						$objattr['toc_id'] = $attr['NAME'];
					} else {
						$objattr['toc_id'] = 0;
					}
					$e = "\xbb\xa4\xactype=toc,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
					if ($this->mpdf->tableLevel) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = array($e);
					} // *TABLES*
					else { // *TABLES*
						$this->mpdf->textbuffer[] = array($e);
					} // *TABLES*
				}
				break;
			/* -- END TOC -- */

			/* -- INDEX -- */
			case 'INDEXENTRY':
				if (isset($attr['CONTENT']) && $attr['CONTENT']) {
					if (isset($attr['XREF']) && $attr['XREF']) {
						$this->mpdf->IndexEntry(htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES), $attr['XREF']);
						break;
					}
					$objattr = array();
					$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES);
					$objattr['type'] = 'indexentry';
					$objattr['vertical-align'] = 'T';
					$e = "\xbb\xa4\xactype=indexentry,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
					if ($this->mpdf->tableLevel) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = array($e);
					}  // *TABLES*
					else { // *TABLES*
						$this->mpdf->textbuffer[] = array($e);
					} // *TABLES*
				}
				break;


			case 'INDEXINSERT':
				if (isset($attr['COLLATION'])) {
					$indexCollationLocale = $attr['COLLATION'];
				} else {
					$indexCollationLocale = '';
				}
				if (isset($attr['COLLATION-GROUP'])) {
					$indexCollationGroup = $attr['COLLATION-GROUP'];
				} else {
					$indexCollationGroup = '';
				}
				if (isset($attr['USEDIVLETTERS']) && (strtoupper($attr['USEDIVLETTERS']) == 'OFF' || $attr['USEDIVLETTERS'] == -1 || $attr['USEDIVLETTERS'] === '0')) {
					$usedivletters = 0;
				} else {
					$usedivletters = 1;
				}
				if (isset($attr['LINKS']) && (strtoupper($attr['LINKS']) == 'ON' || $attr['LINKS'] == 1)) {
					$links = true;
				} else {
					$links = false;
				}
				$this->mpdf->InsertIndex($usedivletters, $links, $indexCollationLocale, $indexCollationGroup);

				break;
			/* -- END INDEX -- */

			/* -- WATERMARK -- */

			case 'WATERMARKTEXT':
				if (isset($attr['CONTENT']) && $attr['CONTENT']) {
					$txt = htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES);
				} else {
					$txt = '';
				}
				if (isset($attr['ALPHA']) && $attr['ALPHA'] > 0) {
					$alpha = $attr['ALPHA'];
				} else {
					$alpha = -1;
				}
				$this->mpdf->SetWatermarkText($txt, $alpha);
				break;


			case 'WATERMARKIMAGE':
				if (isset($attr['SRC'])) {
					$src = $attr['SRC'];
				} else {
					$src = '';
				}
				if (isset($attr['ALPHA']) && $attr['ALPHA'] > 0) {
					$alpha = $attr['ALPHA'];
				} else {
					$alpha = -1;
				}
				if (isset($attr['SIZE']) && $attr['SIZE']) {
					$size = $attr['SIZE'];
					if (strpos($size, ',')) {
						$size = explode(',', $size);
					}
				} else {
					$size = 'D';
				}
				if (isset($attr['POSITION']) && $attr['POSITION']) {  // mPDF 5.7.2
					$pos = $attr['POSITION'];
					if (strpos($pos, ',')) {
						$pos = explode(',', $pos);
					}
				} else {
					$pos = 'P';
				}
				$this->mpdf->SetWatermarkImage($src, $alpha, $size, $pos);
				break;
			/* -- END WATERMARK -- */

			/* -- BOOKMARKS -- */
			case 'BOOKMARK':
				if (isset($attr['CONTENT'])) {
					$objattr = array();
					$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES);
					$objattr['type'] = 'bookmark';
					if (isset($attr['LEVEL']) && $attr['LEVEL']) {
						$objattr['bklevel'] = $attr['LEVEL'];
					} else {
						$objattr['bklevel'] = 0;
					}
					$e = "\xbb\xa4\xactype=bookmark,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
					if ($this->mpdf->tableLevel) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = array($e);
					} // *TABLES*
					else { // *TABLES*
						$this->mpdf->textbuffer[] = array($e);
					} // *TABLES*
				}
				break;
			/* -- END BOOKMARKS -- */

			/* -- ANNOTATIONS -- */
			case 'ANNOTATION':

				//if (isset($attr['CONTENT']) && !$this->mpdf->writingHTMLheader && !$this->mpdf->writingHTMLfooter) {	// Stops annotations in FixedPos
				if (isset($attr['CONTENT'])) {
					$objattr = array();
					$objattr['margin_top'] = 0;
					$objattr['margin_bottom'] = 0;
					$objattr['margin_left'] = 0;
					$objattr['margin_right'] = 0;
					$objattr['width'] = 0;
					$objattr['height'] = 0;
					$objattr['border_top']['w'] = 0;
					$objattr['border_bottom']['w'] = 0;
					$objattr['border_left']['w'] = 0;
					$objattr['border_right']['w'] = 0;
					$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES);
					$objattr['type'] = 'annot';
					$objattr['POPUP'] = '';
				} else {
					break;
				}
				if (isset($attr['POS-X'])) {
					$objattr['POS-X'] = $attr['POS-X'];
				} else {
					$objattr['POS-X'] = 0;
				}
				if (isset($attr['POS-Y'])) {
					$objattr['POS-Y'] = $attr['POS-Y'];
				} else {
					$objattr['POS-Y'] = 0;
				}
				if (isset($attr['ICON'])) {
					$objattr['ICON'] = $attr['ICON'];
				} else {
					$objattr['ICON'] = 'Note';
				}
				if (isset($attr['AUTHOR'])) {
					$objattr['AUTHOR'] = $attr['AUTHOR'];
				} else if (isset($attr['TITLE'])) {
					$objattr['AUTHOR'] = $attr['TITLE'];
				} else {
					$objattr['AUTHOR'] = '';
				}
				if (isset($attr['FILE'])) {
					$objattr['FILE'] = $attr['FILE'];
				} else {
					$objattr['FILE'] = '';
				}
				if (isset($attr['SUBJECT'])) {
					$objattr['SUBJECT'] = $attr['SUBJECT'];
				} else {
					$objattr['SUBJECT'] = '';
				}
				if (isset($attr['OPACITY']) && $attr['OPACITY'] > 0 && $attr['OPACITY'] <= 1) {
					$objattr['OPACITY'] = $attr['OPACITY'];
				} else if ($this->mpdf->annotMargin) {
					$objattr['OPACITY'] = 1;
				} else {
					$objattr['OPACITY'] = $this->mpdf->annotOpacity;
				}
				if (isset($attr['COLOR'])) {
					$cor = $this->mpdf->ConvertColor($attr['COLOR']);
					if ($cor) {
						$objattr['COLOR'] = $cor;
					} else {
						$objattr['COLOR'] = $this->mpdf->ConvertColor('yellow');
					}
				} else {
					$objattr['COLOR'] = $this->mpdf->ConvertColor('yellow');
				}

				if (isset($attr['POPUP']) && !empty($attr['POPUP'])) {
					$pop = preg_split('/\s+/', trim($attr['POPUP']));
					if (count($pop) > 1) {
						$objattr['POPUP'] = $pop;
					} else {
						$objattr['POPUP'] = true;
					}
				}
				$e = "\xbb\xa4\xactype=annot,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
				if ($this->mpdf->tableLevel) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = array($e);
				} // *TABLES*
				else { // *TABLES*
					$this->mpdf->textbuffer[] = array($e);
				} // *TABLES*
				break;
			/* -- END ANNOTATIONS -- */


			/* -- COLUMNS -- */
			case 'COLUMNS': //added custom-tag
				if (isset($attr['COLUMN-COUNT']) && ($attr['COLUMN-COUNT'] || $attr['COLUMN-COUNT'] === '0')) {
					// Close any open block tags
					for ($b = $this->mpdf->blklvl; $b > 0; $b--) {
						$this->CloseTag($this->mpdf->blk[$b]['tag'], $ahtml, $ihtml);
					}
					if (!empty($this->mpdf->textbuffer)) { //Output previously buffered content
						$this->mpdf->printbuffer($this->mpdf->textbuffer);
						$this->mpdf->textbuffer = array();
					}

					if (isset($attr['VALIGN']) && $attr['VALIGN']) {
						if ($attr['VALIGN'] == 'J') {
							$valign = 'J';
						} else {
							$valign = $align[$attr['VALIGN']];
						}
					} else {
						$valign = '';
					}
					if (isset($attr['COLUMN-GAP']) && $attr['COLUMN-GAP']) {
						$this->mpdf->SetColumns($attr['COLUMN-COUNT'], $valign, $attr['COLUMN-GAP']);
					} else {
						$this->mpdf->SetColumns($attr['COLUMN-COUNT'], $valign);
					}
				}
				$this->mpdf->ignorefollowingspaces = true;
				break;

			case 'COLUMN_BREAK': //custom-tag
			case 'COLUMNBREAK': //custom-tag
			case 'NEWCOLUMN': //custom-tag
				$this->mpdf->ignorefollowingspaces = true;
				$this->mpdf->NewColumn();
				$this->mpdf->ColumnAdjust = false; // disables all column height adjustment for the page.
				break;

			/* -- END COLUMNS -- */


			case 'TTZ':
				$this->mpdf->ttz = true;
				$this->mpdf->InlineProperties[$tag] = $this->mpdf->saveInlineProperties();
				$this->mpdf->setCSS(array('FONT-FAMILY' => 'czapfdingbats', 'FONT-WEIGHT' => 'normal', 'FONT-STYLE' => 'normal'), 'INLINE');
				break;

			case 'TTS':
				$this->mpdf->tts = true;
				$this->mpdf->InlineProperties[$tag] = $this->mpdf->saveInlineProperties();
				$this->mpdf->setCSS(array('FONT-FAMILY' => 'csymbol', 'FONT-WEIGHT' => 'normal', 'FONT-STYLE' => 'normal'), 'INLINE');
				break;

			case 'TTA':
				$this->mpdf->tta = true;
				$this->mpdf->InlineProperties[$tag] = $this->mpdf->saveInlineProperties();

				if (in_array($this->mpdf->FontFamily, $this->mpdf->mono_fonts)) {
					$this->mpdf->setCSS(array('FONT-FAMILY' => 'ccourier'), 'INLINE');
				} else if (in_array($this->mpdf->FontFamily, $this->mpdf->serif_fonts)) {
					$this->mpdf->setCSS(array('FONT-FAMILY' => 'ctimes'), 'INLINE');
				} else {
					$this->mpdf->setCSS(array('FONT-FAMILY' => 'chelvetica'), 'INLINE');
				}
				break;



			// INLINE PHRASES OR STYLES
			case 'SUB':
			case 'SUP':
			case 'ACRONYM':
			case 'BIG':
			case 'SMALL':
			case 'INS':
			case 'S':
			case 'STRIKE':
			case 'DEL':
			case 'STRONG':
			case 'CITE':
			case 'Q':
			case 'EM':
			case 'B':
			case 'I':
			case 'U':
			case 'SAMP':
			case 'CODE':
			case 'KBD':
			case 'TT':
			case 'VAR':
			case 'FONT':
			case 'MARK':
			case 'TIME':
			case 'BDO': // mPDF 6
			case 'BDI': // mPDF 6

			case 'SPAN':
				/* -- ANNOTATIONS -- */
				if ($this->mpdf->title2annots && isset($attr['TITLE'])) {
					$objattr = array();
					$objattr['margin_top'] = 0;
					$objattr['margin_bottom'] = 0;
					$objattr['margin_left'] = 0;
					$objattr['margin_right'] = 0;
					$objattr['width'] = 0;
					$objattr['height'] = 0;
					$objattr['border_top']['w'] = 0;
					$objattr['border_bottom']['w'] = 0;
					$objattr['border_left']['w'] = 0;
					$objattr['border_right']['w'] = 0;

					$objattr['CONTENT'] = $attr['TITLE'];
					$objattr['type'] = 'annot';
					$objattr['POS-X'] = 0;
					$objattr['POS-Y'] = 0;
					$objattr['ICON'] = 'Comment';
					$objattr['AUTHOR'] = '';
					$objattr['SUBJECT'] = '';
					$objattr['OPACITY'] = $this->mpdf->annotOpacity;
					$objattr['COLOR'] = $this->mpdf->ConvertColor('yellow');
					$annot = "\xbb\xa4\xactype=annot,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
				}
				/* -- END ANNOTATIONS -- */

				// mPDF 5.7.3 Inline tags
				if (!isset($this->mpdf->InlineProperties[$tag])) {
					$this->mpdf->InlineProperties[$tag] = array($this->mpdf->saveInlineProperties());
				} else {
					$this->mpdf->InlineProperties[$tag][] = $this->mpdf->saveInlineProperties();
				}
				if (isset($annot)) {  // *ANNOTATIONS*
					if (!isset($this->mpdf->InlineAnnots[$tag])) {
						$this->mpdf->InlineAnnots[$tag] = array($annot);
					} // *ANNOTATIONS*
					else {
						$this->mpdf->InlineAnnots[$tag][] = $annot;
					} // *ANNOTATIONS*
				} // *ANNOTATIONS*

				$properties = $this->mpdf->cssmgr->MergeCSS('INLINE', $tag, $attr);
				if (!empty($properties))
					$this->mpdf->setCSS($properties, 'INLINE');

				// mPDF 6 Bidirectional formatting for inline elements
				$bdf = false;
				$bdf2 = '';
				$popd = '';

				// Get current direction
				if (isset($this->mpdf->blk[$this->mpdf->blklvl]['direction'])) {
					$currdir = $this->mpdf->blk[$this->mpdf->blklvl]['direction'];
				} else {
					$currdir = 'ltr';
				}
				if ($this->mpdf->tableLevel && isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['direction']) && $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['direction'] == 'rtl') {
					$currdir = 'rtl';
				}
				if (isset($attr['DIR']) and $attr['DIR'] != '') {
					$currdir = strtolower($attr['DIR']);
				}
				if (isset($properties['DIRECTION'])) {
					$currdir = strtolower($properties['DIRECTION']);
				}

				// mPDF 6 bidi
				// cf. http://www.w3.org/TR/css3-writing-modes/#unicode-bidi
				if ($tag == 'BDO') {
					if (isset($attr['DIR']) and strtolower($attr['DIR']) == 'rtl') {
						$bdf = 0x202E;
						$popd = 'RLOPDF';
					} // U+202E RLO
					else if (isset($attr['DIR']) and strtolower($attr['DIR']) == 'ltr') {
						$bdf = 0x202D;
						$popd = 'LROPDF';
					} // U+202D LRO
				} else if ($tag == 'BDI') {
					if (isset($attr['DIR']) and strtolower($attr['DIR']) == 'rtl') {
						$bdf = 0x2067;
						$popd = 'RLIPDI';
					} // U+2067 RLI
					else if (isset($attr['DIR']) and strtolower($attr['DIR']) == 'ltr') {
						$bdf = 0x2066;
						$popd = 'LRIPDI';
					} // U+2066 LRI
					else {
						$bdf = 0x2068;
						$popd = 'FSIPDI';
					} // U+2068 FSI
				} else if (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) == 'bidi-override') {
					if ($currdir == 'rtl') {
						$bdf = 0x202E;
						$popd = 'RLOPDF';
					} // U+202E RLO
					else {
						$bdf = 0x202D;
						$popd = 'LROPDF';
					} // U+202D LRO
				} else if (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) == 'embed') {
					if ($currdir == 'rtl') {
						$bdf = 0x202B;
						$popd = 'RLEPDF';
					} // U+202B RLE
					else {
						$bdf = 0x202A;
						$popd = 'LREPDF';
					} // U+202A LRE
				} else if (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) == 'isolate') {
					if ($currdir == 'rtl') {
						$bdf = 0x2067;
						$popd = 'RLIPDI';
					} // U+2067 RLI
					else {
						$bdf = 0x2066;
						$popd = 'LRIPDI';
					} // U+2066 LRI
				} else if (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) == 'isolate-override') {
					if ($currdir == 'rtl') {
						$bdf = 0x2067;
						$bdf2 = 0x202E;
						$popd = 'RLIRLOPDFPDI';
					} // U+2067 RLI // U+202E RLO
					else {
						$bdf = 0x2066;
						$bdf2 = 0x202D;
						$popd = 'LRILROPDFPDI';
					} // U+2066 LRI  // U+202D LRO
				} else if (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) == 'plaintext') {
					$bdf = 0x2068;
					$popd = 'FSIPDI'; // U+2068 FSI
				} else {
					if (isset($attr['DIR']) and strtolower($attr['DIR']) == 'rtl') {
						$bdf = 0x202B;
						$popd = 'RLEPDF';
					} // U+202B RLE
					else if (isset($attr['DIR']) and strtolower($attr['DIR']) == 'ltr') {
						$bdf = 0x202A;
						$popd = 'LREPDF';
					} // U+202A LRE
				}

				if ($bdf) {
					// mPDF 5.7.3 Inline tags
					if (!isset($this->mpdf->InlineBDF[$tag])) {
						$this->mpdf->InlineBDF[$tag] = array(array($popd, $this->mpdf->InlineBDFctr));
					} else {
						$this->mpdf->InlineBDF[$tag][] = array($popd, $this->mpdf->InlineBDFctr);
					}
					$this->mpdf->InlineBDFctr++;
					if ($bdf2) {
						$bdf2 = code2utf($bdf);
					}
					$this->mpdf->OTLdata = array();
					if ($this->mpdf->tableLevel) {
						$this->mpdf->_saveCellTextBuffer(code2utf($bdf) . $bdf2);
					} else {
						$this->mpdf->_saveTextBuffer(code2utf($bdf) . $bdf2);
					}
					$this->mpdf->biDirectional = true;
				}

				break;


			case 'A':
				if (isset($attr['NAME']) and $attr['NAME'] != '') {
					$e = '';
					/* -- BOOKMARKS -- */
					if ($this->mpdf->anchor2Bookmark) {
						$objattr = array();
						$objattr['CONTENT'] = htmlspecialchars_decode($attr['NAME'], ENT_QUOTES);
						$objattr['type'] = 'bookmark';
						if (isset($attr['LEVEL']) && $attr['LEVEL']) {
							$objattr['bklevel'] = $attr['LEVEL'];
						} else {
							$objattr['bklevel'] = 0;
						}
						$e = "\xbb\xa4\xactype=bookmark,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
					}
					/* -- END BOOKMARKS -- */
					if ($this->mpdf->tableLevel) { // *TABLES*
						$this->mpdf->_saveCellTextBuffer($e, '', $attr['NAME']); // *TABLES*
					} // *TABLES*
					else { // *TABLES*
						$this->mpdf->_saveTextBuffer($e, '', $attr['NAME']); //an internal link (adds a space for recognition)
					} // *TABLES*
				}
				if (isset($attr['HREF'])) {
					$this->mpdf->InlineProperties['A'] = $this->mpdf->saveInlineProperties();
					$properties = $this->mpdf->cssmgr->MergeCSS('INLINE', $tag, $attr);
					if (!empty($properties))
						$this->mpdf->setCSS($properties, 'INLINE');
					$this->mpdf->HREF = $attr['HREF']; // mPDF 5.7.4 URLs
				}
				break;

			case 'LEGEND':
				$this->mpdf->InlineProperties['LEGEND'] = $this->mpdf->saveInlineProperties();
				$properties = $this->mpdf->cssmgr->MergeCSS('INLINE', $tag, $attr);
				if (!empty($properties))
					$this->mpdf->setCSS($properties, 'INLINE');
				break;



			case 'PROGRESS':
			case 'METER':
				$this->mpdf->inMeter = true;

				if (isset($attr['MAX']) && $attr['MAX']) {
					$max = $attr['MAX'];
				} else {
					$max = 1;
				}
				if (isset($attr['MIN']) && $attr['MIN'] && $tag == 'METER') {
					$min = $attr['MIN'];
				} else {
					$min = 0;
				}
				if ($max < $min) {
					$max = $min;
				}

				if (isset($attr['VALUE']) && ($attr['VALUE'] || $attr['VALUE'] === '0')) {
					$value = $attr['VALUE'];
					if ($value < $min) {
						$value = $min;
					} else if ($value > $max) {
						$value = $max;
					}
				} else {
					$value = '';
				}

				if (isset($attr['LOW']) && $attr['LOW']) {
					$low = $attr['LOW'];
				} else {
					$low = $min;
				}
				if ($low < $min) {
					$low = $min;
				} else if ($low > $max) {
					$low = $max;
				}
				if (isset($attr['HIGH']) && $attr['HIGH']) {
					$high = $attr['HIGH'];
				} else {
					$high = $max;
				}
				if ($high < $low) {
					$high = $low;
				} else if ($high > $max) {
					$high = $max;
				}
				if (isset($attr['OPTIMUM']) && $attr['OPTIMUM']) {
					$optimum = $attr['OPTIMUM'];
				} else {
					$optimum = $min + (($max - $min) / 2);
				}
				if ($optimum < $min) {
					$optimum = $min;
				} else if ($optimum > $max) {
					$optimum = $max;
				}
				if (isset($attr['TYPE']) && $attr['TYPE']) {
					$type = $attr['TYPE'];
				} else {
					$type = '';
				}
				$objattr = array();
				$objattr['margin_top'] = 0;
				$objattr['margin_bottom'] = 0;
				$objattr['margin_left'] = 0;
				$objattr['margin_right'] = 0;
				$objattr['padding_top'] = 0;
				$objattr['padding_bottom'] = 0;
				$objattr['padding_left'] = 0;
				$objattr['padding_right'] = 0;
				$objattr['width'] = 0;
				$objattr['height'] = 0;
				$objattr['border_top']['w'] = 0;
				$objattr['border_bottom']['w'] = 0;
				$objattr['border_left']['w'] = 0;
				$objattr['border_right']['w'] = 0;

				$properties = $this->mpdf->cssmgr->MergeCSS('INLINE', $tag, $attr);
				if (isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY']) == 'none') {
					return;
				}
				$objattr['visibility'] = 'visible';
				if (isset($properties['VISIBILITY'])) {
					$v = strtolower($properties['VISIBILITY']);
					if (($v == 'hidden' || $v == 'printonly' || $v == 'screenonly') && $this->mpdf->visibility == 'visible') {
						$objattr['visibility'] = $v;
					}
				}

				if (isset($properties['MARGIN-TOP'])) {
					$objattr['margin_top'] = $this->mpdf->ConvertSize($properties['MARGIN-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-BOTTOM'])) {
					$objattr['margin_bottom'] = $this->mpdf->ConvertSize($properties['MARGIN-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-LEFT'])) {
					$objattr['margin_left'] = $this->mpdf->ConvertSize($properties['MARGIN-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-RIGHT'])) {
					$objattr['margin_right'] = $this->mpdf->ConvertSize($properties['MARGIN-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}

				if (isset($properties['PADDING-TOP'])) {
					$objattr['padding_top'] = $this->mpdf->ConvertSize($properties['PADDING-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-BOTTOM'])) {
					$objattr['padding_bottom'] = $this->mpdf->ConvertSize($properties['PADDING-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-LEFT'])) {
					$objattr['padding_left'] = $this->mpdf->ConvertSize($properties['PADDING-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-RIGHT'])) {
					$objattr['padding_right'] = $this->mpdf->ConvertSize($properties['PADDING-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}

				if (isset($properties['BORDER-TOP'])) {
					$objattr['border_top'] = $this->mpdf->border_details($properties['BORDER-TOP']);
				}
				if (isset($properties['BORDER-BOTTOM'])) {
					$objattr['border_bottom'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
				}
				if (isset($properties['BORDER-LEFT'])) {
					$objattr['border_left'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
				}
				if (isset($properties['BORDER-RIGHT'])) {
					$objattr['border_right'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
				}

				if (isset($properties['VERTICAL-ALIGN'])) {
					$objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])];
				}
				$w = 0;
				$h = 0;
				if (isset($properties['WIDTH']))
					$w = $this->mpdf->ConvertSize($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				else if (isset($attr['WIDTH']))
					$w = $this->mpdf->ConvertSize($attr['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);

				if (isset($properties['HEIGHT']))
					$h = $this->mpdf->ConvertSize($properties['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				else if (isset($attr['HEIGHT']))
					$h = $this->mpdf->ConvertSize($attr['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);

				if (isset($properties['OPACITY']) && $properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) {
					$objattr['opacity'] = $properties['OPACITY'];
				}
				if ($this->mpdf->HREF) {
					if (strpos($this->mpdf->HREF, ".") === false && strpos($this->mpdf->HREF, "@") !== 0) {
						$href = $this->mpdf->HREF;
						while (array_key_exists($href, $this->mpdf->internallink))
							$href = "#" . $href;
						$this->mpdf->internallink[$href] = $this->mpdf->AddLink();
						$objattr['link'] = $this->mpdf->internallink[$href];
					} else {
						$objattr['link'] = $this->mpdf->HREF;
					}
				}
				$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
				$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

				// Image file
				if (!class_exists('meter', false)) {
					include(_MPDF_PATH . 'classes/meter.php');
				}
				$this->mpdf->meter = new meter();
				$svg = $this->mpdf->meter->makeSVG(strtolower($tag), $type, $value, $max, $min, $optimum, $low, $high);
				//Save to local file
				$srcpath = _MPDF_TEMP_PATH . '_tempSVG' . uniqid(rand(1, 100000), true) . '_' . strtolower($tag) . '.svg';
				file_put_contents($srcpath, $svg);
				$orig_srcpath = $srcpath;
				$this->mpdf->GetFullPath($srcpath);

				$info = $this->mpdf->_getImage($srcpath, true, true, $orig_srcpath);
				if (!$info) {
					$info = $this->mpdf->_getImage($this->mpdf->noImageFile);
					if ($info) {
						$srcpath = $this->mpdf->noImageFile;
						$w = ($info['w'] * (25.4 / $this->mpdf->dpi));
						$h = ($info['h'] * (25.4 / $this->mpdf->dpi));
					}
				}
				if (!$info)
					break;

				$objattr['file'] = $srcpath;
				//Default width and height calculation if needed
				if ($w == 0 and $h == 0) {
					// SVG units are pixels
					$w = $this->mpdf->FontSize / (10 / _MPDFK) * abs($info['w']) / _MPDFK;
					$h = $this->mpdf->FontSize / (10 / _MPDFK) * abs($info['h']) / _MPDFK;
				}
				// IF WIDTH OR HEIGHT SPECIFIED
				if ($w == 0)
					$w = abs($h * $info['w'] / $info['h']);
				if ($h == 0)
					$h = abs($w * $info['h'] / $info['w']);

				// Resize to maximum dimensions of page
				$maxWidth = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];
				$maxHeight = $this->mpdf->h - ($this->mpdf->tMargin + $this->mpdf->bMargin + 1);
				if ($this->mpdf->fullImageHeight) {
					$maxHeight = $this->mpdf->fullImageHeight;
				}
				if (($w + $extrawidth) > ($maxWidth + 0.0001)) { // mPDF 5.7.4  0.0001 to allow for rounding errors when w==maxWidth
					$w = $maxWidth - $extrawidth;
					$h = abs($w * $info['h'] / $info['w']);
				}

				if ($h + $extraheight > $maxHeight) {
					$h = $maxHeight - $extraheight;
					$w = abs($h * $info['w'] / $info['h']);
				}
				$objattr['type'] = 'image';
				$objattr['itype'] = $info['type'];

				$objattr['orig_h'] = $info['h'];
				$objattr['orig_w'] = $info['w'];
				$objattr['wmf_x'] = $info['x'];
				$objattr['wmf_y'] = $info['y'];
				$objattr['height'] = $h + $extraheight;
				$objattr['width'] = $w + $extrawidth;
				$objattr['image_height'] = $h;
				$objattr['image_width'] = $w;
				$e = "\xbb\xa4\xactype=image,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
				$properties = array();
				if ($this->mpdf->tableLevel) {
					$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
				} else {
					$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
				}

				break;


			case 'BR':
				// Added mPDF 3.0 Float DIV - CLEAR
				if (isset($attr['STYLE'])) {
					$properties = $this->mpdf->cssmgr->readInlineCSS($attr['STYLE']);
					if (isset($properties['CLEAR'])) {
						$this->mpdf->ClearFloats(strtoupper($properties['CLEAR']), $this->mpdf->blklvl);
					} // *CSS-FLOAT*
				}

				// mPDF 6 bidi
				// Inline
				// If unicode-bidi set, any embedding levels, isolates, or overrides started by the inline box are closed at the br and reopened on the other side
				$blockpre = '';
				$blockpost = '';
				if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bidicode'])) {
					$blockpre = $this->mpdf->_setBidiCodes('end', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
					$blockpost = $this->mpdf->_setBidiCodes('start', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
				}

				// Inline
				// If unicode-bidi set, any embedding levels, isolates, or overrides started by the inline box are closed at the br and reopened on the other side
				$inlinepre = '';
				$inlinepost = '';
				$iBDF = array();
				if (count($this->mpdf->InlineBDF)) {
					foreach ($this->mpdf->InlineBDF AS $k => $ib) {
						foreach ($ib AS $ib2) {
							$iBDF[$ib2[1]] = $ib2[0];
						}
					}
					if (count($iBDF)) {
						ksort($iBDF);
						for ($i = count($iBDF) - 1; $i >= 0; $i--) {
							$inlinepre .= $this->mpdf->_setBidiCodes('end', $iBDF[$i]);
						}
						for ($i = 0; $i < count($iBDF); $i++) {
							$inlinepost .= $this->mpdf->_setBidiCodes('start', $iBDF[$i]);
						}
					}
				}

				/* -- TABLES -- */
				if ($this->mpdf->tableLevel) {

					if ($this->mpdf->blockjustfinished) {
						$this->mpdf->_saveCellTextBuffer($blockpre . $inlinepre . "\n" . $inlinepost . $blockpost);
					}

					$this->mpdf->_saveCellTextBuffer($blockpre . $inlinepre . "\n" . $inlinepost . $blockpost);
					if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
					} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
					}
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0; // reset
				} else {
					/* -- END TABLES -- */
					if (count($this->mpdf->textbuffer)) {
						$this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0] = preg_replace('/ $/', '', $this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0]);
						if (!empty($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][18])) {
							$this->mpdf->otl->trimOTLdata($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][18], false, true);
						} // *OTL*
					}
					$this->mpdf->_saveTextBuffer($blockpre . $inlinepre . "\n" . $inlinepost . $blockpost);
				} // *TABLES*
				$this->mpdf->ignorefollowingspaces = true;
				$this->mpdf->blockjustfinished = false;

				$this->mpdf->linebreakjustfinished = true;
				break;


			// *********** BLOCKS  ********************


			case 'PRE':
				$this->mpdf->ispre = true; // ADDED - Prevents left trim of textbuffer in printbuffer()

			case 'DIV':
			case 'FORM':
			case 'CENTER':

			case 'BLOCKQUOTE':
			case 'ADDRESS':

			case 'CAPTION':
			case 'P':
			case 'H1':
			case 'H2':
			case 'H3':
			case 'H4':
			case 'H5':
			case 'H6':
			case 'DL':
			case 'DT':
			case 'DD':
			case 'UL': // mPDF 6  Lists
			case 'OL': // mPDF 6
			case 'LI': // mPDF 6
			case 'FIELDSET':
			case 'DETAILS':
			case 'SUMMARY':
			case 'ARTICLE':
			case 'ASIDE':
			case 'FIGURE':
			case 'FIGCAPTION':
			case 'FOOTER':
			case 'HEADER':
			case 'HGROUP':
			case 'NAV':
			case 'SECTION':
			case 'MAIN':
				// mPDF 6  Lists
				$this->mpdf->lastoptionaltag = '';

				// mPDF 6 bidi
				// Block
				// If unicode-bidi set on current clock, any embedding levels, isolates, or overrides are closed (not inherited)
				if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bidicode'])) {
					$blockpost = $this->mpdf->_setBidiCodes('end', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
					if ($blockpost) {
						$this->mpdf->OTLdata = array();
						if ($this->mpdf->tableLevel) {
							$this->mpdf->_saveCellTextBuffer($blockpost);
						} else {
							$this->mpdf->_saveTextBuffer($blockpost);
						}
					}
				}


				$p = $this->mpdf->cssmgr->PreviewBlockCSS($tag, $attr);
				if (isset($p['DISPLAY']) && strtolower($p['DISPLAY']) == 'none') {
					$this->mpdf->blklvl++;
					$this->mpdf->blk[$this->mpdf->blklvl]['hide'] = true;
					$this->mpdf->blk[$this->mpdf->blklvl]['tag'] = $tag;  // mPDF 6
					return;
				}
				if ($tag == 'CAPTION') {
					// position is written in AdjstHTML
					if (isset($attr['POSITION']) && strtolower($attr['POSITION']) == 'bottom') {
						$divpos = 'B';
					} else {
						$divpos = 'T';
					}
					if (isset($attr['ALIGN']) && strtolower($attr['ALIGN']) == 'bottom') {
						$cappos = 'B';
					} else if (isset($p['CAPTION-SIDE']) && strtolower($p['CAPTION-SIDE']) == 'bottom') {
						$cappos = 'B';
					} else {
						$cappos = 'T';
					}
					if (isset($attr['ALIGN'])) {
						unset($attr['ALIGN']);
					}
					if ($cappos != $divpos) {
						$this->mpdf->blklvl++;
						$this->mpdf->blk[$this->mpdf->blklvl]['hide'] = true;
						$this->mpdf->blk[$this->mpdf->blklvl]['tag'] = $tag;  // mPDF 6
						return;
					}
				}

				/* -- FORMS -- */
				if ($tag == 'FORM') {
					if (isset($attr['METHOD']) && strtolower($attr['METHOD']) == 'get') {
						$this->mpdf->mpdfform->formMethod = 'GET';
					} else {
						$this->mpdf->mpdfform->formMethod = 'POST';
					}
					if (isset($attr['ACTION'])) {
						$this->mpdf->mpdfform->formAction = $attr['ACTION'];
					} else {
						$this->mpdf->mpdfform->formAction = '';
					}
				}
				/* -- END FORMS -- */


				/* -- CSS-POSITION -- */
				if ((isset($p['POSITION']) && (strtolower($p['POSITION']) == 'fixed' || strtolower($p['POSITION']) == 'absolute')) && $this->mpdf->blklvl == 0) {
					if ($this->mpdf->inFixedPosBlock) {
						throw new MpdfException("Cannot nest block with position:fixed or position:absolute");
					}
					$this->mpdf->inFixedPosBlock = true;
					return;
				}
				/* -- END CSS-POSITION -- */
				// Start Block
				$this->mpdf->ignorefollowingspaces = true;

				if ($this->mpdf->blockjustfinished && !count($this->mpdf->textbuffer) && $this->mpdf->y != $this->mpdf->tMargin && $this->mpdf->collapseBlockMargins) {
					$lastbottommargin = $this->mpdf->lastblockbottommargin;
				} else {
					$lastbottommargin = 0;
				}
				$this->mpdf->lastblockbottommargin = 0;
				$this->mpdf->blockjustfinished = false;


				$this->mpdf->InlineBDF = array(); // mPDF 6
				$this->mpdf->InlineBDFctr = 0; // mPDF 6
				$this->mpdf->InlineProperties = array();
				$this->mpdf->divbegin = true;

				$this->mpdf->linebreakjustfinished = false;

				/* -- TABLES -- */
				if ($this->mpdf->tableLevel) {
					// If already something on the line
					if ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] > 0 && !$this->mpdf->nestedtablejustfinished) {
						$this->mpdf->_saveCellTextBuffer("\n");
						if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
							$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
						} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
							$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
						}
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0; // reset
					}
					// Cannot set block properties inside table - use Bold to indicate h1-h6
					if ($tag == 'CENTER' && $this->mpdf->tdbegin) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['a'] = $align['center'];
					}

					$this->mpdf->InlineProperties['BLOCKINTABLE'] = $this->mpdf->saveInlineProperties();
					$properties = $this->mpdf->cssmgr->MergeCSS('', $tag, $attr);
					if (!empty($properties))
						$this->mpdf->setCSS($properties, 'INLINE');

					// mPDF 6  Lists
					if ($tag == 'UL' || $tag == 'OL') {
						$this->mpdf->listlvl++;
						if (isset($attr['START'])) {
							$this->mpdf->listcounter[$this->mpdf->listlvl] = intval($attr['START']) - 1;
						} else {
							$this->mpdf->listcounter[$this->mpdf->listlvl] = 0;
						}
						$this->mpdf->listitem = array();
						if ($tag == 'OL')
							$this->mpdf->listtype[$this->mpdf->listlvl] = 'decimal';
						else if ($tag == 'UL') {
							if ($this->mpdf->listlvl % 3 == 1)
								$this->mpdf->listtype[$this->mpdf->listlvl] = 'disc';
							elseif ($this->mpdf->listlvl % 3 == 2)
								$this->mpdf->listtype[$this->mpdf->listlvl] = 'circle';
							else
								$this->mpdf->listtype[$this->mpdf->listlvl] = 'square';
						}
					}

					// mPDF 6  Lists - in Tables
					if ($tag == 'LI') {
						if ($this->mpdf->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
							$this->mpdf->listlvl++; // first depth level
							$this->mpdf->listcounter[$this->mpdf->listlvl] = 0;
						}
						$this->mpdf->listcounter[$this->mpdf->listlvl] ++;
						$this->mpdf->listitem = array();
						//if in table - output here as a tabletextbuffer
						//position:inside OR position:outside (always output in table as position:inside)
						switch ($this->mpdf->listtype[$this->mpdf->listlvl]) {
							case 'upper-alpha':
							case 'upper-latin':
							case 'A':
								$blt = $this->mpdf->dec2alpha($this->mpdf->listcounter[$this->mpdf->listlvl], true) . $this->mpdf->list_number_suffix;
								break;
							case 'lower-alpha':
							case 'lower-latin':
							case 'a':
								$blt = $this->mpdf->dec2alpha($this->mpdf->listcounter[$this->mpdf->listlvl], false) . $this->mpdf->list_number_suffix;
								break;
							case 'upper-roman':
							case 'I':
								$blt = $this->mpdf->dec2roman($this->mpdf->listcounter[$this->mpdf->listlvl], true) . $this->mpdf->list_number_suffix;
								break;
							case 'lower-roman':
							case 'i':
								$blt = $this->mpdf->dec2roman($this->mpdf->listcounter[$this->mpdf->listlvl], false) . $this->mpdf->list_number_suffix;
								break;
							case 'decimal':
							case '1':
								$blt = $this->mpdf->listcounter[$this->mpdf->listlvl] . $this->mpdf->list_number_suffix;
								break;
							default:
								if ($this->mpdf->listlvl % 3 == 1 && $this->mpdf->_charDefined($this->mpdf->CurrentFont['cw'], 8226)) {
									$blt = "\xe2\x80\xa2";
								}  // &#8226;
								else if ($this->mpdf->listlvl % 3 == 2 && $this->mpdf->_charDefined($this->mpdf->CurrentFont['cw'], 9900)) {
									$blt = "\xe2\x9a\xac";
								} // &#9900;
								else if ($this->mpdf->listlvl % 3 == 0 && $this->mpdf->_charDefined($this->mpdf->CurrentFont['cw'], 9642)) {
									$blt = "\xe2\x96\xaa";
								} // &#9642;
								else {
									$blt = '-';
								}
								break;
						}

						// change to &nbsp; spaces
						if ($this->mpdf->usingCoreFont) {
							$ls = str_repeat(chr(160) . chr(160), ($this->mpdf->listlvl - 1) * 2) . $blt . ' ';
						} else {
							$ls = str_repeat("\xc2\xa0\xc2\xa0", ($this->mpdf->listlvl - 1) * 2) . $blt . ' ';
						}
						$this->mpdf->_saveCellTextBuffer($ls);
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $this->mpdf->GetStringWidth($ls);
					}

					break;
				}
				/* -- END TABLES -- */

				if ($this->mpdf->lastblocklevelchange == 1) {
					$blockstate = 1;
				} // Top margins/padding only
				else if ($this->mpdf->lastblocklevelchange < 1) {
					$blockstate = 0;
				} // NO margins/padding
				$this->mpdf->printbuffer($this->mpdf->textbuffer, $blockstate);
				$this->mpdf->textbuffer = array();

				$save_blklvl = $this->mpdf->blklvl;
				$save_blk = $this->mpdf->blk;

				$this->mpdf->Reset();

				$pagesel = '';
				/* -- CSS-PAGE -- */
				if (isset($p['PAGE'])) {
					$pagesel = $p['PAGE'];
				}  // mPDF 6 (uses $p - preview of properties so blklvl can be incremented after page-break)
				/* -- END CSS-PAGE -- */

				// If page-box has changed AND/OR PAGE-BREAK-BEFORE
				// mPDF 6 (uses $p - preview of properties so blklvl can be imcremented after page-break)
				if (!$this->mpdf->tableLevel && (($pagesel && (!isset($this->mpdf->page_box['current']) || $pagesel != $this->mpdf->page_box['current'])) || (isset($p['PAGE-BREAK-BEFORE']) && $p['PAGE-BREAK-BEFORE']))) {
					// mPDF 6 pagebreaktype
					$startpage = $this->mpdf->page;
					$pagebreaktype = $this->mpdf->defaultPagebreakType;
					$this->mpdf->lastblocklevelchange = -1;
					if ($this->mpdf->ColActive) {
						$pagebreaktype = 'cloneall';
					}
					if ($pagesel && (!isset($this->mpdf->page_box['current']) || $pagesel != $this->mpdf->page_box['current'])) {
						$pagebreaktype = 'cloneall';
					}
					$this->mpdf->_preForcedPagebreak($pagebreaktype);

					if (isset($p['PAGE-BREAK-BEFORE'])) {
						if (strtoupper($p['PAGE-BREAK-BEFORE']) == 'RIGHT') {
							$this->mpdf->AddPage($this->mpdf->CurOrientation, 'NEXT-ODD', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0, $pagesel);
						} else if (strtoupper($p['PAGE-BREAK-BEFORE']) == 'LEFT') {
							$this->mpdf->AddPage($this->mpdf->CurOrientation, 'NEXT-EVEN', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0, $pagesel);
						} else if (strtoupper($p['PAGE-BREAK-BEFORE']) == 'ALWAYS') {
							$this->mpdf->AddPage($this->mpdf->CurOrientation, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0, $pagesel);
						} else if ($this->mpdf->page_box['current'] != $pagesel) {
							$this->mpdf->AddPage($this->mpdf->CurOrientation, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0, $pagesel);
						} // *CSS-PAGE*
					}
					/* -- CSS-PAGE -- */
					// Must Add new page if changed page properties
					else if (!isset($this->mpdf->page_box['current']) || $pagesel != $this->mpdf->page_box['current']) {
						$this->mpdf->AddPage($this->mpdf->CurOrientation, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0, $pagesel);
					}
					/* -- END CSS-PAGE -- */

					// mPDF 6 pagebreaktype
					$this->mpdf->_postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl);
				}

				// mPDF 6 pagebreaktype - moved after pagebreak
				$this->mpdf->blklvl++;
				$currblk = & $this->mpdf->blk[$this->mpdf->blklvl];
				$this->mpdf->initialiseBlock($currblk);
				$prevblk = & $this->mpdf->blk[$this->mpdf->blklvl - 1];
				$currblk['tag'] = $tag;
				$currblk['attr'] = $attr;

				$properties = $this->mpdf->cssmgr->MergeCSS('BLOCK', $tag, $attr); // mPDF 6 - moved to after page-break-before
				// mPDF 6 page-break-inside:avoid
				if (isset($properties['PAGE-BREAK-INSIDE']) && strtoupper($properties['PAGE-BREAK-INSIDE']) == 'AVOID' && !$this->mpdf->ColActive && !$this->mpdf->keep_block_together && !isset($attr['PAGEBREAKAVOIDCHECKED'])) { // avoid re-iterating using PAGEBREAKAVOIDCHECKED; set in CloseTag
					$currblk['keep_block_together'] = 1;
					$currblk['array_i'] = $ihtml; // mPDF 6
					$this->mpdf->kt_y00 = $this->mpdf->y;
					$this->mpdf->kt_p00 = $this->mpdf->page;
					$this->mpdf->keep_block_together = 1;
				}
				if ($lastbottommargin && isset($properties['MARGIN-TOP']) && $properties['MARGIN-TOP'] && empty($properties['FLOAT'])) {
					$currblk['lastbottommargin'] = $lastbottommargin;
				}

				if (isset($properties['Z-INDEX']) && $this->mpdf->current_layer == 0) {
					$v = intval($properties['Z-INDEX']);
					if ($v > 0) {
						$currblk['z-index'] = $v;
						$this->mpdf->BeginLayer($v);
					}
				}


				// mPDF 6  Lists
				// List-type set by attribute
				if ($tag == 'OL' || $tag == 'UL' || $tag == 'LI') {
					if (isset($attr['TYPE']) && $attr['TYPE']) {
						$listtype = $attr['TYPE'];
						switch ($listtype) {
							case 'A':
								$listtype = 'upper-latin';
								break;
							case 'a':
								$listtype = 'lower-latin';
								break;
							case 'I':
								$listtype = 'upper-roman';
								break;
							case 'i':
								$listtype = 'lower-roman';
								break;
							case '1':
								$listtype = 'decimal';
								break;
						}
						$currblk['list_style_type'] = $listtype;
					}
				}

				$this->mpdf->setCSS($properties, 'BLOCK', $tag); //name(id/class/style) found in the CSS array!
				$currblk['InlineProperties'] = $this->mpdf->saveInlineProperties();

				if (isset($properties['VISIBILITY'])) {
					$v = strtolower($properties['VISIBILITY']);
					if (($v == 'hidden' || $v == 'printonly' || $v == 'screenonly') && $this->mpdf->visibility == 'visible' && !$this->mpdf->tableLevel) {
						$currblk['visibility'] = $v;
						$this->mpdf->SetVisibility($v);
					}
				}

				// mPDF 6
				if (isset($attr['ALIGN']) && $attr['ALIGN']) {
					$currblk['block-align'] = $align[strtolower($attr['ALIGN'])];
				}


				if (isset($properties['HEIGHT'])) {
					$currblk['css_set_height'] = $this->mpdf->ConvertSize($properties['HEIGHT'], ($this->mpdf->h - $this->mpdf->tMargin - $this->mpdf->bMargin), $this->mpdf->FontSize, false);
					if (($currblk['css_set_height'] + $this->mpdf->y) > $this->mpdf->PageBreakTrigger && $this->mpdf->y > $this->mpdf->tMargin + 5 && $currblk['css_set_height'] < ($this->mpdf->h - ($this->mpdf->tMargin + $this->mpdf->bMargin))) {
						$this->mpdf->AddPage($this->mpdf->CurOrientation);
					}
				} else {
					$currblk['css_set_height'] = false;
				}


				// Added mPDF 3.0 Float DIV
				if (isset($prevblk['blockContext'])) {
					$currblk['blockContext'] = $prevblk['blockContext'];
				} // *CSS-FLOAT*

				if (isset($properties['CLEAR'])) {
					$this->mpdf->ClearFloats(strtoupper($properties['CLEAR']), $this->mpdf->blklvl - 1);
				} // *CSS-FLOAT*

				$container_w = $prevblk['inner_width'];
				$bdr = $currblk['border_right']['w'];
				$bdl = $currblk['border_left']['w'];
				$pdr = $currblk['padding_right'];
				$pdl = $currblk['padding_left'];

				if (isset($currblk['css_set_width'])) {
					$setwidth = $currblk['css_set_width'];
				} else {
					$setwidth = 0;
				}

				/* -- CSS-FLOAT -- */
				if (isset($properties['FLOAT']) && strtoupper($properties['FLOAT']) == 'RIGHT' && !$this->mpdf->ColActive) {
					// Cancel Keep-Block-together
					$currblk['keep_block_together'] = false;
					$this->mpdf->kt_y00 = '';
					$this->mpdf->keep_block_together = 0;

					$this->mpdf->blockContext++;
					$currblk['blockContext'] = $this->mpdf->blockContext;

					list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);

					// DIV is too narrow for text to fit!
					$maxw = $container_w - $l_width - $r_width;
					if (($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) < (2 * $this->mpdf->GetCharWidth('W', false))) {
						// Too narrow to fit - try to move down past L or R float
						if ($l_max < $r_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > (2 * $this->mpdf->GetCharWidth('W', false))) {
							$this->mpdf->ClearFloats('LEFT', $this->mpdf->blklvl - 1);
						} else if ($r_max < $l_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width) && (($container_w - $l_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > (2 * $this->mpdf->GetCharWidth('W', false))) {
							$this->mpdf->ClearFloats('RIGHT', $this->mpdf->blklvl - 1);
						} else {
							$this->mpdf->ClearFloats('BOTH', $this->mpdf->blklvl - 1);
						}
						list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);
					}

					if ($r_exists) {
						$currblk['margin_right'] += $r_width;
					}

					$currblk['float'] = 'R';
					$currblk['float_start_y'] = $this->mpdf->y;
					if ($currblk['css_set_width']) {
						$currblk['margin_left'] = $container_w - ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
						$currblk['float_width'] = ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
					} else {
						// *** If no width set - would need to buffer and keep track of max width, then Right-align if not full width
						// and do borders and backgrounds - For now - just set to maximum width left

						if ($l_exists) {
							$currblk['margin_left'] += $l_width;
						}
						$currblk['css_set_width'] = $container_w - ($currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr);

						$currblk['float_width'] = ($currblk['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
					}
				} else if (isset($properties['FLOAT']) && strtoupper($properties['FLOAT']) == 'LEFT' && !$this->mpdf->ColActive) {
					// Cancel Keep-Block-together
					$currblk['keep_block_together'] = false;
					$this->mpdf->kt_y00 = '';
					$this->mpdf->keep_block_together = 0;

					$this->mpdf->blockContext++;
					$currblk['blockContext'] = $this->mpdf->blockContext;

					list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);

					// DIV is too narrow for text to fit!
					$maxw = $container_w - $l_width - $r_width;
					if (($setwidth + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) < (2 * $this->mpdf->GetCharWidth('W', false))) {
						// Too narrow to fit - try to move down past L or R float
						if ($l_max < $r_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > (2 * $this->mpdf->GetCharWidth('W', false))) {
							$this->mpdf->ClearFloats('LEFT', $this->mpdf->blklvl - 1);
						} else if ($r_max < $l_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width) && (($container_w - $l_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > (2 * $this->mpdf->GetCharWidth('W', false))) {
							$this->mpdf->ClearFloats('RIGHT', $this->mpdf->blklvl - 1);
						} else {
							$this->mpdf->ClearFloats('BOTH', $this->mpdf->blklvl - 1);
						}
						list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);
					}

					if ($l_exists) {
						$currblk['margin_left'] += $l_width;
					}

					$currblk['float'] = 'L';
					$currblk['float_start_y'] = $this->mpdf->y;
					if ($setwidth) {
						$currblk['margin_right'] = $container_w - ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
						$currblk['float_width'] = ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
					} else {
						// *** If no width set - would need to buffer and keep track of max width, then Right-align if not full width
						// and do borders and backgrounds - For now - just set to maximum width left

						if ($r_exists) {
							$currblk['margin_right'] += $r_width;
						}
						$currblk['css_set_width'] = $container_w - ($currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr);

						$currblk['float_width'] = ($currblk['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
					}
				} else {
					// Don't allow overlap - if floats present - adjust padding to avoid overlap with Floats
					list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);
					$maxw = $container_w - $l_width - $r_width;
					if (($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) > $maxw || ($maxw - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) < (2 * $this->mpdf->GetCharWidth('W', false))) {
						// Too narrow to fit - try to move down past L or R float
						if ($l_max < $r_max && ($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width) && (($container_w - $r_width) - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) > (2 * $this->mpdf->GetCharWidth('W', false))) {
							$this->mpdf->ClearFloats('LEFT', $this->mpdf->blklvl - 1);
						} else if ($r_max < $l_max && ($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width) && (($container_w - $l_width) - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) > (2 * $this->mpdf->GetCharWidth('W', false))) {
							$this->mpdf->ClearFloats('RIGHT', $this->mpdf->blklvl - 1);
						} else {
							$this->mpdf->ClearFloats('BOTH', $this->mpdf->blklvl - 1);
						}
						list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);
					}
					if ($r_exists) {
						$currblk['padding_right'] = max(($r_width - $currblk['margin_right'] - $bdr), $pdr);
					}
					if ($l_exists) {
						$currblk['padding_left'] = max(($l_width - $currblk['margin_left'] - $bdl), $pdl);
					}
				}
				/* -- END CSS-FLOAT -- */


				/* -- BORDER-RADIUS -- */
				// Automatically increase padding if required for border-radius
				if ($this->mpdf->autoPadding && !$this->mpdf->ColActive) {
					if ($currblk['border_radius_TL_H'] > $currblk['padding_left'] && $currblk['border_radius_TL_V'] > $currblk['padding_top']) {
						if ($currblk['border_radius_TL_H'] > $currblk['border_radius_TL_V']) {
							$this->mpdf->_borderPadding($currblk['border_radius_TL_H'], $currblk['border_radius_TL_V'], $currblk['padding_left'], $currblk['padding_top']);
						} else {
							$this->mpdf->_borderPadding($currblk['border_radius_TL_V'], $currblk['border_radius_TL_H'], $currblk['padding_top'], $currblk['padding_left']);
						}
					}
					if ($currblk['border_radius_TR_H'] > $currblk['padding_right'] && $currblk['border_radius_TR_V'] > $currblk['padding_top']) {
						if ($currblk['border_radius_TR_H'] > $currblk['border_radius_TR_V']) {
							$this->mpdf->_borderPadding($currblk['border_radius_TR_H'], $currblk['border_radius_TR_V'], $currblk['padding_right'], $currblk['padding_top']);
						} else {
							$this->mpdf->_borderPadding($currblk['border_radius_TR_V'], $currblk['border_radius_TR_H'], $currblk['padding_top'], $currblk['padding_right']);
						}
					}
					if ($currblk['border_radius_BL_H'] > $currblk['padding_left'] && $currblk['border_radius_BL_V'] > $currblk['padding_bottom']) {
						if ($currblk['border_radius_BL_H'] > $currblk['border_radius_BL_V']) {
							$this->mpdf->_borderPadding($currblk['border_radius_BL_H'], $currblk['border_radius_BL_V'], $currblk['padding_left'], $currblk['padding_bottom']);
						} else {
							$this->mpdf->_borderPadding($currblk['border_radius_BL_V'], $currblk['border_radius_BL_H'], $currblk['padding_bottom'], $currblk['padding_left']);
						}
					}
					if ($currblk['border_radius_BR_H'] > $currblk['padding_right'] && $currblk['border_radius_BR_V'] > $currblk['padding_bottom']) {
						if ($currblk['border_radius_BR_H'] > $currblk['border_radius_BR_V']) {
							$this->mpdf->_borderPadding($currblk['border_radius_BR_H'], $currblk['border_radius_BR_V'], $currblk['padding_right'], $currblk['padding_bottom']);
						} else {
							$this->mpdf->_borderPadding($currblk['border_radius_BR_V'], $currblk['border_radius_BR_H'], $currblk['padding_bottom'], $currblk['padding_right']);
						}
					}
				}
				/* -- END BORDER-RADIUS -- */


				// Hanging indent - if negative indent: ensure padding is >= indent
				if (!isset($currblk['text_indent'])) {
					$currblk['text_indent'] = null;
				}
				if (!isset($currblk['inner_width'])) {
					$currblk['inner_width'] = null;
				}
				$cbti = $this->mpdf->ConvertSize($currblk['text_indent'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				if ($cbti < 0) {
					$hangind = -($cbti);
					if (isset($currblk['direction']) && $currblk['direction'] == 'rtl') { // *OTL*
						$currblk['padding_right'] = max($currblk['padding_right'], $hangind); // *OTL*
					} // *OTL*
					else { // *OTL*
						$currblk['padding_left'] = max($currblk['padding_left'], $hangind);
					} // *OTL*
				}

				if (isset($currblk['css_set_width'])) {
					if (isset($properties['MARGIN-LEFT']) && isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-LEFT']) == 'auto' && strtolower($properties['MARGIN-RIGHT']) == 'auto') {
						// Try to reduce margins to accomodate - if still too wide, set margin-right/left=0 (reduces width)
						$anyextra = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right']);
						if ($anyextra > 0) {
							$currblk['margin_left'] = $currblk['margin_right'] = $anyextra / 2;
						} else {
							$currblk['margin_left'] = $currblk['margin_right'] = 0;
						}
					} else if (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT']) == 'auto') {
						// Try to reduce margin-left to accomodate - if still too wide, set margin-left=0 (reduces width)
						$currblk['margin_left'] = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_right']);
						if ($currblk['margin_left'] < 0) {
							$currblk['margin_left'] = 0;
						}
					} else if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT']) == 'auto') {
						// Try to reduce margin-right to accomodate - if still too wide, set margin-right=0 (reduces width)
						$currblk['margin_right'] = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_left']);
						if ($currblk['margin_right'] < 0) {
							$currblk['margin_right'] = 0;
						}
					} else {
						if ($currblk['direction'] == 'rtl') { // *OTL*
							// Try to reduce margin-left to accomodate - if still too wide, set margin-left=0 (reduces width)
							$currblk['margin_left'] = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_right']); // *OTL*
							if ($currblk['margin_left'] < 0) { // *OTL*
								$currblk['margin_left'] = 0; // *OTL*
							} // *OTL*
						} // *OTL*
						else { // *OTL*
							// Try to reduce margin-right to accomodate - if still too wide, set margin-right=0 (reduces width)
							$currblk['margin_right'] = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_left']);
							if ($currblk['margin_right'] < 0) {
								$currblk['margin_right'] = 0;
							}
						} // *OTL*
					}
				}

				$currblk['outer_left_margin'] = $prevblk['outer_left_margin'] + $currblk['margin_left'] + $prevblk['border_left']['w'] + $prevblk['padding_left'];
				$currblk['outer_right_margin'] = $prevblk['outer_right_margin'] + $currblk['margin_right'] + $prevblk['border_right']['w'] + $prevblk['padding_right'];

				$currblk['width'] = $this->mpdf->pgwidth - ($currblk['outer_right_margin'] + $currblk['outer_left_margin']);
				$currblk['inner_width'] = $currblk['width'] - ($currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right']);

				// Check DIV is not now too narrow to fit text
				$mw = 2 * $this->mpdf->GetCharWidth('W', false);
				if ($currblk['inner_width'] < $mw) {
					$currblk['padding_left'] = 0;
					$currblk['padding_right'] = 0;
					$currblk['border_left']['w'] = 0.2;
					$currblk['border_right']['w'] = 0.2;
					$currblk['margin_left'] = 0;
					$currblk['margin_right'] = 0;
					$currblk['outer_left_margin'] = $prevblk['outer_left_margin'] + $currblk['margin_left'] + $prevblk['border_left']['w'] + $prevblk['padding_left'];
					$currblk['outer_right_margin'] = $prevblk['outer_right_margin'] + $currblk['margin_right'] + $prevblk['border_right']['w'] + $prevblk['padding_right'];
					$currblk['width'] = $this->mpdf->pgwidth - ($currblk['outer_right_margin'] + $currblk['outer_left_margin']);
					$currblk['inner_width'] = $this->mpdf->pgwidth - ($currblk['outer_right_margin'] + $currblk['outer_left_margin'] + $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right']);
//		if ($currblk['inner_width'] < $mw) { throw new MpdfException("DIV is too narrow for text to fit!"); }
				}

				$this->mpdf->x = $this->mpdf->lMargin + $currblk['outer_left_margin'];

				/* -- BACKGROUNDS -- */
				if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE'] && !$this->mpdf->kwt && !$this->mpdf->ColActive && !$this->mpdf->keep_block_together) {
					$ret = $this->mpdf->SetBackground($properties, $currblk['inner_width']);
					if ($ret) {
						$currblk['background-image'] = $ret;
					}
				}
				/* -- END BACKGROUNDS -- */

				/* -- TABLES -- */
				if ($this->mpdf->use_kwt && isset($attr['KEEP-WITH-TABLE']) && !$this->mpdf->ColActive && !$this->mpdf->keep_block_together) {
					$this->mpdf->kwt = true;
					$this->mpdf->kwt_y0 = $this->mpdf->y;
					//$this->mpdf->kwt_x0 = $this->mpdf->x;
					$this->mpdf->kwt_x0 = $this->mpdf->lMargin; // mPDF 6
					$this->mpdf->kwt_height = 0;
					$this->mpdf->kwt_buffer = array();
					$this->mpdf->kwt_Links = array();
					$this->mpdf->kwt_Annots = array();
					$this->mpdf->kwt_moved = false;
					$this->mpdf->kwt_saved = false;
					$this->mpdf->kwt_Reference = array();
					$this->mpdf->kwt_BMoutlines = array();
					$this->mpdf->kwt_toc = array();
				} else {
					/* -- END TABLES -- */
					$this->mpdf->kwt = false;
				} // *TABLES*
				//Save x,y coords in case we need to print borders...
				$currblk['y0'] = $this->mpdf->y;
				$currblk['initial_y0'] = $this->mpdf->y; // mPDF 6
				$currblk['x0'] = $this->mpdf->x;
				$currblk['initial_x0'] = $this->mpdf->x; // mPDF 6
				$currblk['initial_startpage'] = $this->mpdf->page;
				$currblk['startpage'] = $this->mpdf->page; // mPDF 6
				$this->mpdf->oldy = $this->mpdf->y;

				$this->mpdf->lastblocklevelchange = 1;


				// mPDF 6  Lists
				if ($tag == 'OL' || $tag == 'UL') {
					$this->mpdf->listlvl++;
					if (isset($attr['START']) && $attr['START']) {
						$this->mpdf->listcounter[$this->mpdf->listlvl] = intval($attr['START']) - 1;
					} else {
						$this->mpdf->listcounter[$this->mpdf->listlvl] = 0;
					}
					$this->mpdf->listitem = array();

					// List-type
					if (!isset($currblk['list_style_type']) || !$currblk['list_style_type']) {
						if ($tag == 'OL')
							$currblk['list_style_type'] = 'decimal';
						else if ($tag == 'UL') {
							if ($this->mpdf->listlvl % 3 == 1)
								$currblk['list_style_type'] = 'disc';
							elseif ($this->mpdf->listlvl % 3 == 2)
								$currblk['list_style_type'] = 'circle';
							else
								$currblk['list_style_type'] = 'square';
						}
					}

					// List-image
					if (!isset($currblk['list_style_image']) || !$currblk['list_style_image']) {
						$currblk['list_style_image'] = 'none';
					}

					// List-position
					if (!isset($currblk['list_style_position']) || !$currblk['list_style_position']) {
						$currblk['list_style_position'] = 'outside';
					}

					// Default indentation using padding
					if (strtolower($this->mpdf->list_auto_mode) == 'mpdf' && isset($currblk['list_style_position']) && $currblk['list_style_position'] == 'outside' && isset($currblk['list_style_image']) && $currblk['list_style_image'] == 'none' && (!isset($currblk['list_style_type']) || !preg_match('/U\+([a-fA-F0-9]+)/i', $currblk['list_style_type']))) {
						$autopadding = $this->mpdf->_getListMarkerWidth($currblk, $ahtml, $ihtml);
						if ($this->mpdf->listlvl > 1 || $this->mpdf->list_indent_first_level) {
							$autopadding += $this->mpdf->ConvertSize($this->mpdf->list_indent_default_mpdf, $currblk['inner_width'], $this->mpdf->FontSize, false);
						}
						// autopadding value is applied to left or right according
						// to dir of block. Once a CSS value is set for padding it overrides this default value.
						if (isset($properties['PADDING-RIGHT']) && $properties['PADDING-RIGHT'] == 'auto' && isset($currblk['direction']) && $currblk['direction'] == 'rtl') {
							$currblk['padding_right'] = $autopadding;
						} else if (isset($properties['PADDING-LEFT']) && $properties['PADDING-LEFT'] == 'auto') {
							$currblk['padding_left'] = $autopadding;
						}
					} else {
						// Initial default value is set by $this->mpdf->list_indent_default in config.php; this value is applied to left or right according
						// to dir of block. Once a CSS value is set for padding it overrides this default value.
						if (isset($properties['PADDING-RIGHT']) && $properties['PADDING-RIGHT'] == 'auto' && isset($currblk['direction']) && $currblk['direction'] == 'rtl') {
							$currblk['padding_right'] = $this->mpdf->ConvertSize($this->mpdf->list_indent_default, $currblk['inner_width'], $this->mpdf->FontSize, false);
						} else if (isset($properties['PADDING-LEFT']) && $properties['PADDING-LEFT'] == 'auto') {
							$currblk['padding_left'] = $this->mpdf->ConvertSize($this->mpdf->list_indent_default, $currblk['inner_width'], $this->mpdf->FontSize, false);
						}
					}
				}


				// mPDF 6  Lists
				if ($tag == 'LI') {
					if ($this->mpdf->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
						$this->mpdf->listlvl++; // first depth level
						$this->mpdf->listcounter[$this->mpdf->listlvl] = 0;
					}
					$this->mpdf->listcounter[$this->mpdf->listlvl] ++;
					$this->mpdf->listitem = array();

					// Listitem-type
					$this->mpdf->_setListMarker($currblk['list_style_type'], $currblk['list_style_image'], $currblk['list_style_position']);
				}

				// mPDF 6 Bidirectional formatting for block elements
				$bdf = false;
				$bdf2 = '';
				$popd = '';

				// Get current direction
				if (isset($currblk['direction'])) {
					$currdir = $currblk['direction'];
				} else {
					$currdir = 'ltr';
				}
				if (isset($attr['DIR']) and $attr['DIR'] != '') {
					$currdir = strtolower($attr['DIR']);
				}
				if (isset($properties['DIRECTION'])) {
					$currdir = strtolower($properties['DIRECTION']);
				}

				// mPDF 6 bidi
				// cf. http://www.w3.org/TR/css3-writing-modes/#unicode-bidi
				if (isset($properties ['UNICODE-BIDI']) && (strtolower($properties ['UNICODE-BIDI']) == 'bidi-override' || strtolower($properties ['UNICODE-BIDI']) == 'isolate-override')) {
					if ($currdir == 'rtl') {
						$bdf = 0x202E;
						$popd = 'RLOPDF';
					} // U+202E RLO
					else {
						$bdf = 0x202D;
						$popd = 'LROPDF';
					} // U+202D LRO
				} else if (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) == 'plaintext') {
					$bdf = 0x2068;
					$popd = 'FSIPDI'; // U+2068 FSI
				}
				if ($bdf) {
					if ($bdf2) {
						$bdf2 = code2utf($bdf);
					}
					$this->mpdf->OTLdata = array();
					if ($this->mpdf->tableLevel) {
						$this->mpdf->_saveCellTextBuffer(code2utf($bdf) . $bdf2);
					} else {
						$this->mpdf->_saveTextBuffer(code2utf($bdf) . $bdf2);
					}
					$this->mpdf->biDirectional = true;
					$currblk['bidicode'] = $popd;
				}

				break;

			case 'HR':
				// Added mPDF 3.0 Float DIV - CLEAR
				if (isset($attr['STYLE'])) {
					$properties = $this->mpdf->cssmgr->readInlineCSS($attr['STYLE']);
					if (isset($properties['CLEAR'])) {
						$this->mpdf->ClearFloats(strtoupper($properties['CLEAR']), $this->mpdf->blklvl);
					} // *CSS-FLOAT*
				}

				$this->mpdf->ignorefollowingspaces = true;

				$objattr = array();
				$objattr['margin_top'] = 0;
				$objattr['margin_bottom'] = 0;
				$objattr['margin_left'] = 0;
				$objattr['margin_right'] = 0;
				$objattr['width'] = 0;
				$objattr['height'] = 0;
				$objattr['border_top']['w'] = 0;
				$objattr['border_bottom']['w'] = 0;
				$objattr['border_left']['w'] = 0;
				$objattr['border_right']['w'] = 0;
				$properties = $this->mpdf->cssmgr->MergeCSS('', $tag, $attr);
				if (isset($properties['MARGIN-TOP'])) {
					$objattr['margin_top'] = $this->mpdf->ConvertSize($properties['MARGIN-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-BOTTOM'])) {
					$objattr['margin_bottom'] = $this->mpdf->ConvertSize($properties['MARGIN-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['WIDTH'])) {
					$objattr['width'] = $this->mpdf->ConvertSize($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
				} else if (isset($attr['WIDTH']) && $attr['WIDTH'] != '')
					$objattr['width'] = $this->mpdf->ConvertSize($attr['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
				if (isset($properties['TEXT-ALIGN'])) {
					$objattr['align'] = $align[strtolower($properties['TEXT-ALIGN'])];
				} else if (isset($attr['ALIGN']) && $attr['ALIGN'] != '')
					$objattr['align'] = $align[strtolower($attr['ALIGN'])];

				if (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT']) == 'auto') {
					$objattr['align'] = 'R';
				}
				if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT']) == 'auto') {
					$objattr['align'] = 'L';
					if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT']) == 'auto' && isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT']) == 'auto') {
						$objattr['align'] = 'C';
					}
				}
				if (isset($properties['COLOR'])) {
					$objattr['color'] = $this->mpdf->ConvertColor($properties['COLOR']);
				} else if (isset($attr['COLOR']) && $attr['COLOR'] != '')
					$objattr['color'] = $this->mpdf->ConvertColor($attr['COLOR']);
				if (isset($properties['HEIGHT'])) {
					$objattr['linewidth'] = $this->mpdf->ConvertSize($properties['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}


				/* -- TABLES -- */
				if ($this->mpdf->tableLevel) {
					$objattr['W-PERCENT'] = 100;
					if (isset($properties['WIDTH']) && stristr($properties['WIDTH'], '%')) {
						$properties['WIDTH'] += 0;  //make "90%" become simply "90"
						$objattr['W-PERCENT'] = $properties['WIDTH'];
					}
					if (isset($attr['WIDTH']) && stristr($attr['WIDTH'], '%')) {
						$attr['WIDTH'] += 0;  //make "90%" become simply "90"
						$objattr['W-PERCENT'] = $attr['WIDTH'];
					}
				}
				/* -- END TABLES -- */

				$objattr['type'] = 'hr';
				$objattr['height'] = $objattr['linewidth'] + $objattr['margin_top'] + $objattr['margin_bottom'];
				$e = "\xbb\xa4\xactype=image,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

				// Clear properties - tidy up
				$properties = array();

				/* -- TABLES -- */
				// Output it to buffers
				if ($this->mpdf->tableLevel) {
					if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
					} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
					}
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0; // reset
					$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
				} else {
					/* -- END TABLES -- */
					$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
				} // *TABLES*

				break;


			/* -- BARCODES -- */

			case 'BARCODE':
				$this->mpdf->ignorefollowingspaces = false;
				if (isset($attr['CODE']) && $attr['CODE']) {
					$objattr = array();
					$objattr['margin_top'] = 0;
					$objattr['margin_bottom'] = 0;
					$objattr['margin_left'] = 0;
					$objattr['margin_right'] = 0;
					$objattr['padding_top'] = 0;
					$objattr['padding_bottom'] = 0;
					$objattr['padding_left'] = 0;
					$objattr['padding_right'] = 0;
					$objattr['width'] = 0;
					$objattr['height'] = 0;
					$objattr['border_top']['w'] = 0;
					$objattr['border_bottom']['w'] = 0;
					$objattr['border_left']['w'] = 0;
					$objattr['border_right']['w'] = 0;
					$objattr['code'] = $attr['CODE'];

					if (isset($attr['TYPE'])) {
						$objattr['btype'] = trim(strtoupper($attr['TYPE']));
					} else {
						$objattr['btype'] = 'EAN13';
					} // default
					if (preg_match('/^(EAN13|ISBN|ISSN|EAN8|UPCA|UPCE)P([25])$/', $objattr['btype'], $m)) {
						$objattr['btype'] = $m[1];
						$objattr['bsupp'] = $m[2];
						if (preg_match('/^(\S+)\s+(.*)$/', $objattr['code'], $mm)) {
							$objattr['code'] = $mm[1];
							$objattr['bsupp_code'] = $mm[2];
						}
					} else {
						$objattr['bsupp'] = 0;
					}

					if (isset($attr['TEXT']) && $attr['TEXT'] == 1) {
						$objattr['showtext'] = 1;
					} else {
						$objattr['showtext'] = 0;
					}
					if (isset($attr['SIZE']) && $attr['SIZE'] > 0) {
						$objattr['bsize'] = $attr['SIZE'];
					} else {
						$objattr['bsize'] = 1;
					}
					if (isset($attr['HEIGHT']) && $attr['HEIGHT'] > 0) {
						$objattr['bheight'] = $attr['HEIGHT'];
					} else {
						$objattr['bheight'] = 1;
					}
					if (isset($attr['PR']) && $attr['PR'] > 0) {
						$objattr['pr_ratio'] = $attr['PR'];
					} else {
						$objattr['pr_ratio'] = '';
					}
					$properties = $this->mpdf->cssmgr->MergeCSS('', $tag, $attr);
					if (isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY']) == 'none') {
						return;
					}
					if (isset($properties['MARGIN-TOP'])) {
						$objattr['margin_top'] = $this->mpdf->ConvertSize($properties['MARGIN-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['MARGIN-BOTTOM'])) {
						$objattr['margin_bottom'] = $this->mpdf->ConvertSize($properties['MARGIN-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['MARGIN-LEFT'])) {
						$objattr['margin_left'] = $this->mpdf->ConvertSize($properties['MARGIN-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['MARGIN-RIGHT'])) {
						$objattr['margin_right'] = $this->mpdf->ConvertSize($properties['MARGIN-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}

					if (isset($properties['PADDING-TOP'])) {
						$objattr['padding_top'] = $this->mpdf->ConvertSize($properties['PADDING-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['PADDING-BOTTOM'])) {
						$objattr['padding_bottom'] = $this->mpdf->ConvertSize($properties['PADDING-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['PADDING-LEFT'])) {
						$objattr['padding_left'] = $this->mpdf->ConvertSize($properties['PADDING-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['PADDING-RIGHT'])) {
						$objattr['padding_right'] = $this->mpdf->ConvertSize($properties['PADDING-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}

					if (isset($properties['BORDER-TOP'])) {
						$objattr['border_top'] = $this->mpdf->border_details($properties['BORDER-TOP']);
					}
					if (isset($properties['BORDER-BOTTOM'])) {
						$objattr['border_bottom'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
					}
					if (isset($properties['BORDER-LEFT'])) {
						$objattr['border_left'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
					}
					if (isset($properties['BORDER-RIGHT'])) {
						$objattr['border_right'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
					}

					if (isset($properties['VERTICAL-ALIGN'])) {
						$objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])];
					}
					if (isset($properties['COLOR']) && $properties['COLOR'] != '') {
						$objattr['color'] = $this->mpdf->ConvertColor($properties['COLOR']);
					} else {
						$objattr['color'] = false;
					}
					if (isset($properties['BACKGROUND-COLOR']) && $properties['BACKGROUND-COLOR'] != '') {
						$objattr['bgcolor'] = $this->mpdf->ConvertColor($properties['BACKGROUND-COLOR']);
					} else {
						$objattr['bgcolor'] = false;
					}

					if (!class_exists('PDFBarcode', false)) {
						include(_MPDF_PATH . 'classes/barcode.php');
					}
					$this->mpdf->barcode = new PDFBarcode();

					if ($objattr['btype'] == 'EAN13' || $objattr['btype'] == 'ISBN' || $objattr['btype'] == 'ISSN' || $objattr['btype'] == 'UPCA' || $objattr['btype'] == 'UPCE' || $objattr['btype'] == 'EAN8') {
						$code = preg_replace('/\-/', '', $objattr['code']);
						if ($objattr['btype'] == 'ISSN' || $objattr['btype'] == 'ISBN') {
							$arrcode = $this->mpdf->barcode->getBarcodeArray($code, 'EAN13');
						} else {
							$arrcode = $this->mpdf->barcode->getBarcodeArray($code, $objattr['btype']);
						}
						if ($arrcode === false) {
							throw new MpdfException('Error in barcode string.');
						}

						if ($objattr['bsupp'] == 2 || $objattr['bsupp'] == 5) { // EAN-2 or -5 Supplement
							$supparrcode = $this->mpdf->barcode->getBarcodeArray($objattr['bsupp_code'], 'EAN' . $objattr['bsupp']);
							$w = ($arrcode["maxw"] + $arrcode['lightmL'] + $arrcode['lightmR'] + $supparrcode["maxw"] + $supparrcode['sepM']) * $arrcode['nom-X'] * $objattr['bsize'];
						} else {
							$w = ($arrcode["maxw"] + $arrcode['lightmL'] + $arrcode['lightmR']) * $arrcode['nom-X'] * $objattr['bsize'];
						}
						$h = $arrcode['nom-H'] * $objattr['bsize'] * $objattr['bheight'];
						// Add height for ISBN string + margin from top of bars
						if (($objattr['showtext'] && $objattr['btype'] == 'EAN13') || $objattr['btype'] == 'ISBN' || $objattr['btype'] == 'ISSN') {
							$tisbnm = 1.5 * $objattr['bsize']; // Top margin between TOP TEXT (isbn - if shown) & bars
							$isbn_fontsize = 2.1 * $objattr['bsize'];
							$h += $isbn_fontsize + $tisbnm;
						}
					}
					// QR-code
					else if ($objattr['btype'] == 'QR') {
						$w = $h = $objattr['bsize'] * 25; // Factor of 25mm (default)
						$objattr['errorlevel'] = 'L';
						if (isset($attr['ERROR'])) {
							$objattr['errorlevel'] = $attr['ERROR'];
						}
					} else if ($objattr['btype'] == 'IMB' || $objattr['btype'] == 'RM4SCC' || $objattr['btype'] == 'KIX' || $objattr['btype'] == 'POSTNET' || $objattr['btype'] == 'PLANET') {
						$arrcode = $this->mpdf->barcode->getBarcodeArray($objattr['code'], $objattr['btype']);
						if ($arrcode === false) {
							throw new MpdfException('Error in barcode string.');
						}
						$w = ($arrcode["maxw"] * $arrcode['nom-X'] * $objattr['bsize']) + $arrcode['quietL'] + $arrcode['quietR'];
						$h = ($arrcode['nom-H'] * $objattr['bsize']) + (2 * $arrcode['quietTB']);
					} else if (in_array($objattr['btype'], array('C128A', 'C128B', 'C128C', 'EAN128A', 'EAN128B', 'EAN128C', 'C39', 'C39+', 'C39E', 'C39E+', 'S25', 'S25+', 'I25', 'I25+', 'I25B', 'I25B+', 'C93', 'MSI', 'MSI+', 'CODABAR', 'CODE11'))) {
						$arrcode = $this->mpdf->barcode->getBarcodeArray($objattr['code'], $objattr['btype'], $objattr['pr_ratio']);
						if ($arrcode === false) {
							throw new MpdfException('Error in barcode string.');
						}
						$w = ($arrcode["maxw"] + $arrcode['lightmL'] + $arrcode['lightmR']) * $arrcode['nom-X'] * $objattr['bsize'];
						$h = ((2 * $arrcode['lightTB'] * $arrcode['nom-X']) + $arrcode['nom-H']) * $objattr['bsize'] * $objattr['bheight'];
					} else {
						break;
					}

					$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
					$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

					$objattr['type'] = 'barcode';
					$objattr['height'] = $h + $extraheight;
					$objattr['width'] = $w + $extrawidth;
					$objattr['barcode_height'] = $h;
					$objattr['barcode_width'] = $w;
					/* -- CSS-IMAGE-FLOAT -- */
					if (!$this->mpdf->ColActive && !$this->mpdf->tableLevel && !$this->mpdf->listlvl && !$this->mpdf->kwt) {
						if (isset($properties['FLOAT']) && (strtoupper($properties['FLOAT']) == 'RIGHT' || strtoupper($properties['FLOAT']) == 'LEFT')) {
							$objattr['float'] = substr(strtoupper($properties['FLOAT']), 0, 1);
						}
					}
					/* -- END CSS-IMAGE-FLOAT -- */

					$e = "\xbb\xa4\xactype=barcode,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

					// Clear properties - tidy up
					$properties = array();

					/* -- TABLES -- */
					// Output it to buffers
					if ($this->mpdf->tableLevel) {
						$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
					} else {
						/* -- END TABLES -- */
						$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
					} // *TABLES*
				}
				break;
			/* -- END BARCODES -- */


			// *********** FORM ELEMENTS ********************

			/* -- FORMS -- */
			case 'SELECT':
				$this->mpdf->lastoptionaltag = ''; // Save current HTML specified optional endtag
				$this->mpdf->InlineProperties[$tag] = $this->mpdf->saveInlineProperties();
				$properties = $this->mpdf->cssmgr->MergeCSS('', $tag, $attr);
				if (isset($properties['FONT-FAMILY'])) {
					$this->mpdf->SetFont($properties['FONT-FAMILY'], $this->mpdf->FontStyle, 0, false);
				}
				if (isset($properties['FONT-SIZE'])) {
					$mmsize = $this->mpdf->ConvertSize($properties['FONT-SIZE'], $this->mpdf->default_font_size / _MPDFK);
					$this->mpdf->SetFontSize($mmsize * _MPDFK, false);
				}
				if (isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK']) == 'true') {
					$this->mpdf->selectoption['SPELLCHECK'] = true;
				}

				if (isset($properties['COLOR'])) {
					$this->mpdf->selectoption['COLOR'] = $this->mpdf->ConvertColor($properties['COLOR']);
				}
				$this->mpdf->specialcontent = "type=select";
				if (isset($attr['DISABLED'])) {
					$this->mpdf->selectoption['DISABLED'] = $attr['DISABLED'];
				}
				if (isset($attr['READONLY'])) {
					$this->mpdf->selectoption['READONLY'] = $attr['READONLY'];
				}
				if (isset($attr['REQUIRED'])) {
					$this->mpdf->selectoption['REQUIRED'] = $attr['REQUIRED'];
				}
				if (isset($attr['EDITABLE'])) {
					$this->mpdf->selectoption['EDITABLE'] = $attr['EDITABLE'];
				}
				if (isset($attr['TITLE'])) {
					$this->mpdf->selectoption['TITLE'] = $attr['TITLE'];
				}
				if (isset($attr['MULTIPLE'])) {
					$this->mpdf->selectoption['MULTIPLE'] = $attr['MULTIPLE'];
				}
				if (isset($attr['SIZE']) && $attr['SIZE'] > 1) {
					$this->mpdf->selectoption['SIZE'] = $attr['SIZE'];
				}
				if ($this->mpdf->useActiveForms) {
					if (isset($attr['NAME'])) {
						$this->mpdf->selectoption['NAME'] = $attr['NAME'];
					}
					if (isset($attr['ONCHANGE'])) {
						$this->mpdf->selectoption['ONCHANGE'] = $attr['ONCHANGE'];
					}
				}

				$properties = array();
				break;

			case 'OPTION':
				$this->mpdf->lastoptionaltag = '';
				$this->mpdf->selectoption['ACTIVE'] = true;
				$this->mpdf->selectoption['currentSEL'] = false;
				if (empty($this->mpdf->selectoption)) {
					$this->mpdf->selectoption['MAXWIDTH'] = '';
					$this->mpdf->selectoption['SELECTED'] = '';
				}
				if (isset($attr['SELECTED'])) {
					$this->mpdf->selectoption['SELECTED'] = '';
					$this->mpdf->selectoption['currentSEL'] = true;
				}
				if (isset($attr['VALUE'])) {
					$attr['VALUE'] = strcode2utf($attr['VALUE']);
					$attr['VALUE'] = $this->mpdf->lesser_entity_decode($attr['VALUE']);
					if ($this->mpdf->onlyCoreFonts)
						$attr['VALUE'] = mb_convert_encoding($attr['VALUE'], $this->mpdf->mb_enc, 'UTF-8');
				}
				$this->mpdf->selectoption['currentVAL'] = $attr['VALUE'];
				break;

			case 'TEXTAREA':
				$objattr = array();
				$objattr['margin_top'] = 0;
				$objattr['margin_bottom'] = 0;
				$objattr['margin_left'] = 0;
				$objattr['margin_right'] = 0;
				$objattr['width'] = 0;
				$objattr['height'] = 0;
				$objattr['border_top']['w'] = 0;
				$objattr['border_bottom']['w'] = 0;
				$objattr['border_left']['w'] = 0;
				$objattr['border_right']['w'] = 0;
				if (isset($attr['DISABLED'])) {
					$objattr['disabled'] = true;
				}
				if (isset($attr['READONLY'])) {
					$objattr['readonly'] = true;
				}
				if (isset($attr['REQUIRED'])) {
					$objattr['required'] = true;
				}
				if (isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK']) == 'true') {
					$objattr['spellcheck'] = true;
				}
				if (isset($attr['TITLE'])) {
					$objattr['title'] = $attr['TITLE'];
					if ($this->mpdf->onlyCoreFonts)
						$objattr['title'] = mb_convert_encoding($objattr['title'], $this->mpdf->mb_enc, 'UTF-8');
				}
				if ($this->mpdf->useActiveForms) {
					if (isset($attr['NAME'])) {
						$objattr['fieldname'] = $attr['NAME'];
					}
					$this->mpdf->mpdfform->form_element_spacing['textarea']['outer']['v'] = 0;
					$this->mpdf->mpdfform->form_element_spacing['textarea']['inner']['v'] = 0;
					if (isset($attr['ONCALCULATE'])) {
						$objattr['onCalculate'] = $attr['ONCALCULATE'];
					} else if (isset($attr['ONCHANGE'])) {
						$objattr['onCalculate'] = $attr['ONCHANGE'];
					}
					if (isset($attr['ONVALIDATE'])) {
						$objattr['onValidate'] = $attr['ONVALIDATE'];
					}
					if (isset($attr['ONKEYSTROKE'])) {
						$objattr['onKeystroke'] = $attr['ONKEYSTROKE'];
					}
					if (isset($attr['ONFORMAT'])) {
						$objattr['onFormat'] = $attr['ONFORMAT'];
					}
				}
				$this->mpdf->InlineProperties[$tag] = $this->mpdf->saveInlineProperties();
				$properties = $this->mpdf->cssmgr->MergeCSS('', $tag, $attr);
				if (isset($properties['FONT-FAMILY'])) {
					$this->mpdf->SetFont($properties['FONT-FAMILY'], '', 0, false);
				}
				if (isset($properties['FONT-SIZE'])) {
					$mmsize = $this->mpdf->ConvertSize($properties['FONT-SIZE'], $this->mpdf->default_font_size / _MPDFK);
					$this->mpdf->SetFontSize($mmsize * _MPDFK, false);
				}
				if (isset($properties['COLOR'])) {
					$objattr['color'] = $this->mpdf->ConvertColor($properties['COLOR']);
				}
				$objattr['fontfamily'] = $this->mpdf->FontFamily;
				$objattr['fontsize'] = $this->mpdf->FontSizePt;
				if ($this->mpdf->useActiveForms) {
					if (isset($properties['TEXT-ALIGN'])) {
						$objattr['text_align'] = $align[strtolower($properties['TEXT-ALIGN'])];
					} else if (isset($attr['ALIGN'])) {
						$objattr['text_align'] = $align[strtolower($attr['ALIGN'])];
					}
					if (isset($properties['OVERFLOW']) && strtolower($properties['OVERFLOW']) == 'hidden') {
						$objattr['donotscroll'] = true;
					}
					if (isset($properties['BORDER-TOP-COLOR'])) {
						$objattr['border-col'] = $this->mpdf->ConvertColor($properties['BORDER-TOP-COLOR']);
					}
					if (isset($properties['BACKGROUND-COLOR'])) {
						$objattr['background-col'] = $this->mpdf->ConvertColor($properties['BACKGROUND-COLOR']);
					}
				}
				$this->mpdf->SetLineHeight('', $this->mpdf->mpdfform->textarea_lineheight);

				$w = 0;
				$h = 0;
				if (isset($properties['WIDTH']))
					$w = $this->mpdf->ConvertSize($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				if (isset($properties['HEIGHT']))
					$h = $this->mpdf->ConvertSize($properties['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				if (isset($properties['VERTICAL-ALIGN'])) {
					$objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])];
				}

				$colsize = 20; //HTML default value
				$rowsize = 2; //HTML default value
				if (isset($attr['COLS']))
					$colsize = intval($attr['COLS']);
				if (isset($attr['ROWS']))
					$rowsize = intval($attr['ROWS']);

				$charsize = $this->mpdf->GetCharWidth('w', false);
				if ($w) {
					$colsize = round(($w - ($this->mpdf->mpdfform->form_element_spacing['textarea']['outer']['h'] * 2) - ($this->mpdf->mpdfform->form_element_spacing['textarea']['inner']['h'] * 2)) / $charsize);
				}
				if ($h) {
					$rowsize = round(($h - ($this->mpdf->mpdfform->form_element_spacing['textarea']['outer']['v'] * 2) - ($this->mpdf->mpdfform->form_element_spacing['textarea']['inner']['v'] * 2)) / $this->mpdf->lineheight);
				}

				$objattr['type'] = 'textarea';
				$objattr['width'] = ($colsize * $charsize) + ($this->mpdf->mpdfform->form_element_spacing['textarea']['outer']['h'] * 2) + ($this->mpdf->mpdfform->form_element_spacing['textarea']['inner']['h'] * 2);
				$objattr['height'] = ($rowsize * $this->mpdf->lineheight) + ($this->mpdf->mpdfform->form_element_spacing['textarea']['outer']['v'] * 2) + ($this->mpdf->mpdfform->form_element_spacing['textarea']['inner']['v'] * 2);
				$objattr['rows'] = $rowsize;
				$objattr['cols'] = $colsize;

				$this->mpdf->specialcontent = serialize($objattr);

				if ($this->mpdf->tableLevel) { // *TABLES*
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width']; // *TABLES*
				} // *TABLES*
				// Clear properties - tidy up
				$properties = array();
				break;



			// *********** FORM - INPUT ********************

			case 'INPUT':
				$this->mpdf->ignorefollowingspaces = false;
				if (!isset($attr['TYPE']))
					$attr['TYPE'] == 'TEXT';
				$objattr = array();
				$objattr['margin_top'] = 0;
				$objattr['margin_bottom'] = 0;
				$objattr['margin_left'] = 0;
				$objattr['margin_right'] = 0;
				$objattr['width'] = 0;
				$objattr['height'] = 0;
				$objattr['border_top']['w'] = 0;
				$objattr['border_bottom']['w'] = 0;
				$objattr['border_left']['w'] = 0;
				$objattr['border_right']['w'] = 0;
				$objattr['type'] = 'input';
				if (isset($attr['DISABLED'])) {
					$objattr['disabled'] = true;
				}
				if (isset($attr['READONLY'])) {
					$objattr['readonly'] = true;
				}
				if (isset($attr['REQUIRED'])) {
					$objattr['required'] = true;
				}
				if (isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK']) == 'true') {
					$objattr['spellcheck'] = true;
				}
				if (isset($attr['TITLE'])) {
					$objattr['title'] = $attr['TITLE'];
				} else if (isset($attr['ALT'])) {
					$objattr['title'] = $attr['ALT'];
				} else
					$objattr['title'] = '';
				$objattr['title'] = strcode2utf($objattr['title']);
				$objattr['title'] = $this->mpdf->lesser_entity_decode($objattr['title']);
				if ($this->mpdf->onlyCoreFonts)
					$objattr['title'] = mb_convert_encoding($objattr['title'], $this->mpdf->mb_enc, 'UTF-8');
				if ($this->mpdf->useActiveForms) {
					if (isset($attr['NAME'])) {
						$objattr['fieldname'] = $attr['NAME'];
					}
				}
				if (isset($attr['VALUE'])) {
					$attr['VALUE'] = strcode2utf($attr['VALUE']);
					$attr['VALUE'] = $this->mpdf->lesser_entity_decode($attr['VALUE']);
					if ($this->mpdf->onlyCoreFonts)
						$attr['VALUE'] = mb_convert_encoding($attr['VALUE'], $this->mpdf->mb_enc, 'UTF-8');
					$objattr['value'] = $attr['VALUE'];
				}

				$this->mpdf->InlineProperties[$tag] = $this->mpdf->saveInlineProperties();
				$properties = $this->mpdf->cssmgr->MergeCSS('', $tag, $attr);
				$objattr['vertical-align'] = '';

				if (isset($properties['FONT-FAMILY'])) {
					$this->mpdf->SetFont($properties['FONT-FAMILY'], $this->mpdf->FontStyle, 0, false);
				}
				if (isset($properties['FONT-SIZE'])) {
					$mmsize = $this->mpdf->ConvertSize($properties['FONT-SIZE'], ($this->mpdf->default_font_size / _MPDFK));
					$this->mpdf->SetFontSize($mmsize * _MPDFK, false);
				}
				if (isset($properties['COLOR'])) {
					$objattr['color'] = $this->mpdf->ConvertColor($properties['COLOR']);
				}
				$objattr['fontfamily'] = $this->mpdf->FontFamily;
				$objattr['fontsize'] = $this->mpdf->FontSizePt;
				if ($this->mpdf->useActiveForms) {
					if (isset($attr['ALIGN'])) {
						$objattr['text_align'] = $align[strtolower($attr['ALIGN'])];
					} else if (isset($properties['TEXT-ALIGN'])) {
						$objattr['text_align'] = $align[strtolower($properties['TEXT-ALIGN'])];
					}
					if (isset($properties['BORDER-TOP-COLOR'])) {
						$objattr['border-col'] = $this->mpdf->ConvertColor($properties['BORDER-TOP-COLOR']);
					}
					if (isset($properties['BACKGROUND-COLOR'])) {
						$objattr['background-col'] = $this->mpdf->ConvertColor($properties['BACKGROUND-COLOR']);
					}
				}

				$type = '';
				$texto = '';
				$height = $this->mpdf->FontSize;
				$width = 0;
				$spacesize = $this->mpdf->GetCharWidth(' ', false);

				$w = 0;
				if (isset($properties['WIDTH']))
					$w = $this->mpdf->ConvertSize($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);

				if ($properties['VERTICAL-ALIGN']) {
					$objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])];
				}

				switch (strtoupper($attr['TYPE'])) {
					case 'HIDDEN':
						$this->mpdf->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
						if ($this->mpdf->useActiveForms) {
							$this->mpdf->mpdfform->SetFormText(0, 0, $objattr['fieldname'], $objattr['value'], $objattr['value'], '', 0, '', true);
						}
						if ($this->mpdf->InlineProperties[$tag]) {
							$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
						}
						unset($this->mpdf->InlineProperties[$tag]);
						break 2;
					case 'CHECKBOX': //Draw Checkbox
						$type = 'CHECKBOX';
						if (isset($attr['CHECKED'])) {
							$objattr['checked'] = true;
						} else {
							$objattr['checked'] = false;
						}
						$width = $this->mpdf->FontSize;
						$height = $this->mpdf->FontSize;
						break;

					case 'RADIO': //Draw Radio button
						$type = 'RADIO';
						if (isset($attr['CHECKED']))
							$objattr['checked'] = true;
						$width = $this->mpdf->FontSize;
						$height = $this->mpdf->FontSize;
						break;

					/* -- IMAGES-CORE -- */
					case 'IMAGE': // Draw an Image button
						if (isset($attr['SRC'])) {
							$type = 'IMAGE';
							$srcpath = $attr['SRC'];
							$orig_srcpath = $attr['ORIG_SRC'];
							// VSPACE and HSPACE converted to margins in MergeCSS
							if (isset($properties['MARGIN-TOP'])) {
								$objattr['margin_top'] = $this->mpdf->ConvertSize($properties['MARGIN-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
							}
							if (isset($properties['MARGIN-BOTTOM'])) {
								$objattr['margin_bottom'] = $this->mpdf->ConvertSize($properties['MARGIN-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
							}
							if (isset($properties['MARGIN-LEFT'])) {
								$objattr['margin_left'] = $this->mpdf->ConvertSize($properties['MARGIN-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
							}
							if (isset($properties['MARGIN-RIGHT'])) {
								$objattr['margin_right'] = $this->mpdf->ConvertSize($properties['MARGIN-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
							}


							if (isset($properties['BORDER-TOP'])) {
								$objattr['border_top'] = $this->mpdf->border_details($properties['BORDER-TOP']);
							}
							if (isset($properties['BORDER-BOTTOM'])) {
								$objattr['border_bottom'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
							}
							if (isset($properties['BORDER-LEFT'])) {
								$objattr['border_left'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
							}
							if (isset($properties['BORDER-RIGHT'])) {
								$objattr['border_right'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
							}

							$objattr['padding_top'] = 0;
							$objattr['padding_bottom'] = 0;
							$objattr['padding_left'] = 0;
							$objattr['padding_right'] = 0;

							if (isset($properties['VERTICAL-ALIGN'])) {
								$objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])];
							}

							$w = 0;
							$h = 0;
							if (isset($properties['WIDTH']))
								$w = $this->mpdf->ConvertSize($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
							if (isset($properties['HEIGHT']))
								$h = $this->mpdf->ConvertSize($properties['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);

							$extraheight = $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
							$extrawidth = $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

							// Image file
							$info = $this->mpdf->_getImage($srcpath, true, true, $orig_srcpath);
							if (!$info) {
								$info = $this->mpdf->_getImage($this->mpdf->noImageFile);
								if ($info) {
									$srcpath = $this->mpdf->noImageFile;
									$w = ($info['w'] * (25.4 / $this->mpdf->dpi));
									$h = ($info['h'] * (25.4 / $this->mpdf->dpi));
								}
							}
							if (!$info)
								break;
							if ($info['cs'] == 'Indexed') {
								$objattr['Indexed'] = true;
							}
							$objattr['file'] = $srcpath;
							//Default width and height calculation if needed
							if ($w == 0 and $h == 0) {
								/* -- IMAGES-WMF -- */
								if ($info['type'] == 'wmf') {
									// WMF units are twips (1/20pt)
									// divide by 20 to get points
									// divide by k to get user units
									$w = abs($info['w']) / (20 * _MPDFK);
									$h = abs($info['h']) / (20 * _MPDFK);
								} else
									/* -- END IMAGES-WMF -- */
									if ($info['type'] == 'svg') {
										// SVG units are pixels
										$w = abs($info['w']) / _MPDFK;
										$h = abs($info['h']) / _MPDFK;
									} else {
										//Put image at default image dpi
										$w = ($info['w'] / _MPDFK) * (72 / $this->mpdf->img_dpi);
										$h = ($info['h'] / _MPDFK) * (72 / $this->mpdf->img_dpi);
									}
								if (isset($properties['IMAGE-RESOLUTION'])) {
									if (preg_match('/from-image/i', $properties['IMAGE-RESOLUTION']) && isset($info['set-dpi']) && $info['set-dpi'] > 0) {
										$w *= $this->mpdf->img_dpi / $info['set-dpi'];
										$h *= $this->mpdf->img_dpi / $info['set-dpi'];
									} else if (preg_match('/(\d+)dpi/i', $properties['IMAGE-RESOLUTION'], $m)) {
										$dpi = $m[1];
										if ($dpi > 0) {
											$w *= $this->mpdf->img_dpi / $dpi;
											$h *= $this->mpdf->img_dpi / $dpi;
										}
									}
								}
							}
							// IF WIDTH OR HEIGHT SPECIFIED
							if ($w == 0)
								$w = $h * $info['w'] / $info['h'];
							if ($h == 0)
								$h = $w * $info['h'] / $info['w'];
							// Resize to maximum dimensions of page
							$maxWidth = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];
							$maxHeight = $this->mpdf->h - ($this->mpdf->tMargin + $this->mpdf->bMargin + 10);
							if ($this->mpdf->fullImageHeight) {
								$maxHeight = $this->mpdf->fullImageHeight;
							}
							if (($w + $extrawidth) > ($maxWidth + 0.0001)) { // mPDF 5.7.4  0.0001 to allow for rounding errors when w==maxWidth
								$w = $maxWidth - $extrawidth;
								$h = $w * $info['h'] / $info['w'];
							}
							if ($h + $extraheight > $maxHeight) {
								$h = $maxHeight - $extraheight;
								$w = $h * $info['w'] / $info['h'];
							}
							$height = $h + $extraheight;
							$width = $w + $extrawidth;
							$objattr['type'] = 'image';
							$objattr['itype'] = $info['type'];
							$objattr['orig_h'] = $info['h'];
							$objattr['orig_w'] = $info['w'];
							/* -- IMAGES-WMF -- */
							if ($info['type'] == 'wmf') {
								$objattr['wmf_x'] = $info['x'];
								$objattr['wmf_y'] = $info['y'];
							} else
								/* -- END IMAGES-WMF -- */
								if ($info['type'] == 'svg') {
									$objattr['wmf_x'] = $info['x'];
									$objattr['wmf_y'] = $info['y'];
								}
							$objattr['height'] = $h + $extraheight;
							$objattr['width'] = $w + $extrawidth;

							$objattr['image_height'] = $h;
							$objattr['image_width'] = $w;
							$objattr['ID'] = $info['i'];
							$texto = 'X';
							if ($this->mpdf->useActiveForms) {
								if (isset($attr['ONCLICK'])) {
									$objattr['onClick'] = $attr['ONCLICK'];
								}
								$objattr['type'] = 'input';
								$type = 'IMAGE';
							}
							break;
						}
					/* -- END IMAGES-CORE -- */

					case 'BUTTON': // Draw a button
					case 'SUBMIT':
					case 'RESET':
						$type = strtoupper($attr['TYPE']);
						if ($type == 'IMAGE') {
							$type = 'BUTTON';
						} // src path not found
						if (isset($attr['NOPRINT'])) {
							$objattr['noprint'] = true;
						}
						if (!isset($attr['VALUE'])) {
							$objattr['value'] = ucfirst(strtolower($type));
						}

						$texto = " " . $objattr['value'] . " ";
						$width = $this->mpdf->GetStringWidth($texto) + ($this->mpdf->mpdfform->form_element_spacing['button']['outer']['h'] * 2) + ($this->mpdf->mpdfform->form_element_spacing['button']['inner']['h'] * 2);
						$height = $this->mpdf->FontSize + ($this->mpdf->mpdfform->form_element_spacing['button']['outer']['v'] * 2) + ($this->mpdf->mpdfform->form_element_spacing['button']['inner']['v'] * 2);
						if ($this->mpdf->useActiveForms) {
							if (isset($attr['ONCLICK'])) {
								$objattr['onClick'] = $attr['ONCLICK'];
							}
						}
						break;

					case 'PASSWORD':
					case 'TEXT':
					default:
						if ($type == '') {
							$type = 'TEXT';
						}
						if (strtoupper($attr['TYPE']) == 'PASSWORD') {
							$type = 'PASSWORD';
						}
						if (isset($attr['VALUE'])) {
							if ($type == 'PASSWORD') {
								$num_stars = mb_strlen($attr['VALUE'], $this->mpdf->mb_enc);
								$texto = str_repeat('*', $num_stars);
							} else {
								$texto = $attr['VALUE'];
							}
						}
						$xw = ($this->mpdf->mpdfform->form_element_spacing['input']['outer']['h'] * 2) + ($this->mpdf->mpdfform->form_element_spacing['input']['inner']['h'] * 2);
						$xh = ($this->mpdf->mpdfform->form_element_spacing['input']['outer']['v'] * 2) + ($this->mpdf->mpdfform->form_element_spacing['input']['inner']['v'] * 2);
						if ($w) {
							$width = $w + $xw;
						} else {
							$width = (20 * $spacesize) + $xw;
						} // Default width in chars
						if (isset($attr['SIZE']) and ctype_digit($attr['SIZE']))
							$width = ($attr['SIZE'] * $spacesize) + $xw;
						$height = $this->mpdf->FontSize + $xh;
						if (isset($attr['MAXLENGTH']) and ctype_digit($attr['MAXLENGTH']))
							$objattr['maxlength'] = $attr['MAXLENGTH'];
						if ($this->mpdf->useActiveForms) {
							if (isset($attr['ONCALCULATE'])) {
								$objattr['onCalculate'] = $attr['ONCALCULATE'];
							} else if (isset($attr['ONCHANGE'])) {
								$objattr['onCalculate'] = $attr['ONCHANGE'];
							}
							if (isset($attr['ONVALIDATE'])) {
								$objattr['onValidate'] = $attr['ONVALIDATE'];
							}
							if (isset($attr['ONKEYSTROKE'])) {
								$objattr['onKeystroke'] = $attr['ONKEYSTROKE'];
							}
							if (isset($attr['ONFORMAT'])) {
								$objattr['onFormat'] = $attr['ONFORMAT'];
							}
						}
						break;
				}

				$objattr['subtype'] = $type;
				$objattr['text'] = $texto;
				$objattr['width'] = $width;
				$objattr['height'] = $height;
				$e = "\xbb\xa4\xactype=input,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

				// Clear properties - tidy up
				$properties = array();

				/* -- TABLES -- */
				// Output it to buffers
				if ($this->mpdf->tableLevel) {
					$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
				} else {
					/* -- END TABLES -- */
					$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
				} // *TABLES*

				if ($this->mpdf->InlineProperties[$tag]) {
					$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
				}
				unset($this->mpdf->InlineProperties[$tag]);

				break; // END of INPUT
			/* -- END FORMS -- */


			// *********** GRAPH  ********************
			case 'JPGRAPH':
				if (!$this->mpdf->useGraphs) {
					break;
				}
				if ($attr['TABLE']) {
					$gid = strtoupper($attr['TABLE']);
				} else {
					$gid = '0';
				}
				if (!is_array($this->mpdf->graphs[$gid]) || count($this->mpdf->graphs[$gid]) == 0) {
					break;
				}
				$this->mpdf->ignorefollowingspaces = false;
				include_once(_MPDF_PATH . 'graph.php');
				$this->mpdf->graphs[$gid]['attr'] = $attr;


				if (isset($this->mpdf->graphs[$gid]['attr']['WIDTH']) && $this->mpdf->graphs[$gid]['attr']['WIDTH']) {
					$this->mpdf->graphs[$gid]['attr']['cWIDTH'] = $this->mpdf->ConvertSize($this->mpdf->graphs[$gid]['attr']['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
				} // mm
				if (isset($this->mpdf->graphs[$gid]['attr']['HEIGHT']) && $this->mpdf->graphs[$gid]['attr']['HEIGHT']) {
					$this->mpdf->graphs[$gid]['attr']['cHEIGHT'] = $this->mpdf->ConvertSize($this->mpdf->graphs[$gid]['attr']['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
				}

				$graph_img = print_graph($this->mpdf->graphs[$gid], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
				if ($graph_img) {
					if (isset($attr['ROTATE'])) {
						if ($attr['ROTATE'] == 90 || $attr['ROTATE'] == -90) {
							$tmpw = $graph_img['w'];
							$graph_img['w'] = $graph_img['h'];
							$graph_img['h'] = $tmpw;
						}
					}
					$attr['SRC'] = $graph_img['file'];
					$attr['WIDTH'] = $graph_img['w'];
					$attr['HEIGHT'] = $graph_img['h'];
				} else {
					break;
				}

			// *********** IMAGE  ********************
			/* -- IMAGES-CORE -- */
			case 'IMG':
				$this->mpdf->ignorefollowingspaces = false;
				if ($this->mpdf->progressBar) {
					$this->mpdf->UpdateProgressBar(1, '', 'IMG');
				} // *PROGRESS-BAR*
				$objattr = array();
				$objattr['margin_top'] = 0;
				$objattr['margin_bottom'] = 0;
				$objattr['margin_left'] = 0;
				$objattr['margin_right'] = 0;
				$objattr['padding_top'] = 0;
				$objattr['padding_bottom'] = 0;
				$objattr['padding_left'] = 0;
				$objattr['padding_right'] = 0;
				$objattr['width'] = 0;
				$objattr['height'] = 0;
				$objattr['border_top']['w'] = 0;
				$objattr['border_bottom']['w'] = 0;
				$objattr['border_left']['w'] = 0;
				$objattr['border_right']['w'] = 0;
				if (isset($attr['SRC'])) {
					$srcpath = $attr['SRC'];
					$orig_srcpath = (isset($attr['ORIG_SRC']) ? $attr['ORIG_SRC'] : '');
					$properties = $this->mpdf->cssmgr->MergeCSS('', $tag, $attr);
					if (isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY']) == 'none') {
						return;
					}
					if (isset($properties['Z-INDEX']) && $this->mpdf->current_layer == 0) {
						$v = intval($properties['Z-INDEX']);
						if ($v > 0) {
							$objattr['z-index'] = $v;
						}
					}

					$objattr['visibility'] = 'visible';
					if (isset($properties['VISIBILITY'])) {
						$v = strtolower($properties['VISIBILITY']);
						if (($v == 'hidden' || $v == 'printonly' || $v == 'screenonly') && $this->mpdf->visibility == 'visible') {
							$objattr['visibility'] = $v;
						}
					}

					// VSPACE and HSPACE converted to margins in MergeCSS
					if (isset($properties['MARGIN-TOP'])) {
						$objattr['margin_top'] = $this->mpdf->ConvertSize($properties['MARGIN-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['MARGIN-BOTTOM'])) {
						$objattr['margin_bottom'] = $this->mpdf->ConvertSize($properties['MARGIN-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['MARGIN-LEFT'])) {
						$objattr['margin_left'] = $this->mpdf->ConvertSize($properties['MARGIN-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['MARGIN-RIGHT'])) {
						$objattr['margin_right'] = $this->mpdf->ConvertSize($properties['MARGIN-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}

					if (isset($properties['PADDING-TOP'])) {
						$objattr['padding_top'] = $this->mpdf->ConvertSize($properties['PADDING-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['PADDING-BOTTOM'])) {
						$objattr['padding_bottom'] = $this->mpdf->ConvertSize($properties['PADDING-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['PADDING-LEFT'])) {
						$objattr['padding_left'] = $this->mpdf->ConvertSize($properties['PADDING-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
					if (isset($properties['PADDING-RIGHT'])) {
						$objattr['padding_right'] = $this->mpdf->ConvertSize($properties['PADDING-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}

					if (isset($properties['BORDER-TOP'])) {
						$objattr['border_top'] = $this->mpdf->border_details($properties['BORDER-TOP']);
					}
					if (isset($properties['BORDER-BOTTOM'])) {
						$objattr['border_bottom'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
					}
					if (isset($properties['BORDER-LEFT'])) {
						$objattr['border_left'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
					}
					if (isset($properties['BORDER-RIGHT'])) {
						$objattr['border_right'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
					}

					if (isset($properties['VERTICAL-ALIGN'])) {
						$objattr['vertical-align'] = $align[strtolower($properties['VERTICAL-ALIGN'])];
					}
					$w = 0;
					$h = 0;
					if (isset($properties['WIDTH']))
						$w = $this->mpdf->ConvertSize($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					else if (isset($attr['WIDTH']))
						$w = $this->mpdf->ConvertSize($attr['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					if (isset($properties['HEIGHT']))
						$h = $this->mpdf->ConvertSize($properties['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					else if (isset($attr['HEIGHT']))
						$h = $this->mpdf->ConvertSize($attr['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					$maxw = $maxh = $minw = $minh = false;
					if (isset($properties['MAX-WIDTH']))
						$maxw = $this->mpdf->ConvertSize($properties['MAX-WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					else if (isset($attr['MAX-WIDTH']))
						$maxw = $this->mpdf->ConvertSize($attr['MAX-WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					if (isset($properties['MAX-HEIGHT']))
						$maxh = $this->mpdf->ConvertSize($properties['MAX-HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					else if (isset($attr['MAX-HEIGHT']))
						$maxh = $this->mpdf->ConvertSize($attr['MAX-HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					if (isset($properties['MIN-WIDTH']))
						$minw = $this->mpdf->ConvertSize($properties['MIN-WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					else if (isset($attr['MIN-WIDTH']))
						$minw = $this->mpdf->ConvertSize($attr['MIN-WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					if (isset($properties['MIN-HEIGHT']))
						$minh = $this->mpdf->ConvertSize($properties['MIN-HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					else if (isset($attr['MIN-HEIGHT']))
						$minh = $this->mpdf->ConvertSize($attr['MIN-HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);

					if (isset($properties['OPACITY']) && $properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) {
						$objattr['opacity'] = $properties['OPACITY'];
					}
					if ($this->mpdf->HREF) {
						if (strpos($this->mpdf->HREF, ".") === false && strpos($this->mpdf->HREF, "@") !== 0) {
							$href = $this->mpdf->HREF;
							while (array_key_exists($href, $this->mpdf->internallink))
								$href = "#" . $href;
							$this->mpdf->internallink[$href] = $this->mpdf->AddLink();
							$objattr['link'] = $this->mpdf->internallink[$href];
						} else {
							$objattr['link'] = $this->mpdf->HREF;
						}
					}
					$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
					$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

					// mPDF 5.7.3 TRANSFORMS
					if (isset($properties['BACKGROUND-COLOR']) && $properties['BACKGROUND-COLOR'] != '') {
						$objattr['bgcolor'] = $this->mpdf->ConvertColor($properties['BACKGROUND-COLOR']);
					}

					/* -- BACKGROUNDS -- */
					if (isset($properties['GRADIENT-MASK']) && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $properties['GRADIENT-MASK'])) {
						$objattr['GRADIENT-MASK'] = $properties['GRADIENT-MASK'];
					}
					/* -- END BACKGROUNDS -- */

					// mPDF 6
					$interpolation = false;
					if (isset($properties['IMAGE-RENDERING']) && $properties['IMAGE-RENDERING']) {
						if (strtolower($properties['IMAGE-RENDERING']) == 'crisp-edges') {
							$interpolation = false;
						} else if (strtolower($properties['IMAGE-RENDERING']) == 'optimizequality') {
							$interpolation = true;
						} else if (strtolower($properties['IMAGE-RENDERING']) == 'smooth') {
							$interpolation = true;
						} else if (strtolower($properties['IMAGE-RENDERING']) == 'auto') {
							$interpolation = $this->mpdf->interpolateImages;
						} else {
							$interpolation = false;
						}
						$info['interpolation'] = $interpolation;
					}

					// Image file
					$info = $this->mpdf->_getImage($srcpath, true, true, $orig_srcpath, $interpolation); // mPDF 6
					if (!$info) {
						$info = $this->mpdf->_getImage($this->mpdf->noImageFile);
						if ($info) {
							$srcpath = $this->mpdf->noImageFile;
							$w = ($info['w'] * (25.4 / $this->mpdf->dpi));
							$h = ($info['h'] * (25.4 / $this->mpdf->dpi));
						}
					}
					if (!$info)
						break;

					if (isset($attr['ROTATE'])) {
						$image_orientation = $attr['ROTATE'];
					} else if (isset($properties['IMAGE-ORIENTATION'])) {
						$image_orientation = $properties['IMAGE-ORIENTATION'];
					} else {
						$image_orientation = 0;
					}
					if ($image_orientation) {
						if ($image_orientation == 90 || $image_orientation == -90 || $image_orientation == 270) {
							$tmpw = $info['w'];
							$info['w'] = $info['h'];
							$info['h'] = $tmpw;
						}
						$objattr['ROTATE'] = $image_orientation;
					}

					$objattr['file'] = $srcpath;
					//Default width and height calculation if needed
					if ($w == 0 and $h == 0) {
						/* -- IMAGES-WMF -- */
						if ($info['type'] == 'wmf') {
							// WMF units are twips (1/20pt)
							// divide by 20 to get points
							// divide by k to get user units
							$w = abs($info['w']) / (20 * _MPDFK);
							$h = abs($info['h']) / (20 * _MPDFK);
						} else
							/* -- END IMAGES-WMF -- */
							if ($info['type'] == 'svg') {
								// SVG units are pixels
								$w = abs($info['w']) / _MPDFK;
								$h = abs($info['h']) / _MPDFK;
							} else {
								//Put image at default image dpi
								$w = ($info['w'] / _MPDFK) * (72 / $this->mpdf->img_dpi);
								$h = ($info['h'] / _MPDFK) * (72 / $this->mpdf->img_dpi);
							}
						if (isset($properties['IMAGE-RESOLUTION'])) {
							if (preg_match('/from-image/i', $properties['IMAGE-RESOLUTION']) && isset($info['set-dpi']) && $info['set-dpi'] > 0) {
								$w *= $this->mpdf->img_dpi / $info['set-dpi'];
								$h *= $this->mpdf->img_dpi / $info['set-dpi'];
							} else if (preg_match('/(\d+)dpi/i', $properties['IMAGE-RESOLUTION'], $m)) {
								$dpi = $m[1];
								if ($dpi > 0) {
									$w *= $this->mpdf->img_dpi / $dpi;
									$h *= $this->mpdf->img_dpi / $dpi;
								}
							}
						}
					}
					// IF WIDTH OR HEIGHT SPECIFIED
					if ($w == 0)
						$w = abs($h * $info['w'] / $info['h']);
					if ($h == 0)
						$h = abs($w * $info['h'] / $info['w']);

					if ($minw && $w < $minw) {
						$w = $minw;
						$h = abs($w * $info['h'] / $info['w']);
					}
					if ($maxw && $w > $maxw) {
						$w = $maxw;
						$h = abs($w * $info['h'] / $info['w']);
					}
					if ($minh && $h < $minh) {
						$h = $minh;
						$w = abs($h * $info['w'] / $info['h']);
					}
					if ($maxh && $h > $maxh) {
						$h = $maxh;
						$w = abs($h * $info['w'] / $info['h']);
					}

					// Resize to maximum dimensions of page
					$maxWidth = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];
					$maxHeight = $this->mpdf->h - ($this->mpdf->tMargin + $this->mpdf->bMargin + 1);
					if ($this->mpdf->fullImageHeight) {
						$maxHeight = $this->mpdf->fullImageHeight;
					}
					if (($w + $extrawidth) > ($maxWidth + 0.0001)) { // mPDF 5.7.4  0.0001 to allow for rounding errors when w==maxWidth
						$w = $maxWidth - $extrawidth;
						$h = abs($w * $info['h'] / $info['w']);
					}

					if ($h + $extraheight > $maxHeight) {
						$h = $maxHeight - $extraheight;
						$w = abs($h * $info['w'] / $info['h']);
					}
					$objattr['type'] = 'image';
					$objattr['itype'] = $info['type'];

					$objattr['orig_h'] = $info['h'];
					$objattr['orig_w'] = $info['w'];
					/* -- IMAGES-WMF -- */
					if ($info['type'] == 'wmf') {
						$objattr['wmf_x'] = $info['x'];
						$objattr['wmf_y'] = $info['y'];
					} else
						/* -- END IMAGES-WMF -- */
						if ($info['type'] == 'svg') {
							$objattr['wmf_x'] = $info['x'];
							$objattr['wmf_y'] = $info['y'];
						}
					$objattr['height'] = $h + $extraheight;
					$objattr['width'] = $w + $extrawidth;
					$objattr['image_height'] = $h;
					$objattr['image_width'] = $w;
					/* -- CSS-IMAGE-FLOAT -- */
					if (!$this->mpdf->ColActive && !$this->mpdf->tableLevel && !$this->mpdf->listlvl && !$this->mpdf->kwt) {
						if (isset($properties['FLOAT']) && (strtoupper($properties['FLOAT']) == 'RIGHT' || strtoupper($properties['FLOAT']) == 'LEFT')) {
							$objattr['float'] = substr(strtoupper($properties['FLOAT']), 0, 1);
						}
					}
					/* -- END CSS-IMAGE-FLOAT -- */
					// mPDF 5.7.3 TRANSFORMS
					if (isset($properties['TRANSFORM']) && !$this->mpdf->ColActive && !$this->mpdf->kwt) {
						$objattr['transform'] = $properties['TRANSFORM'];
					}

					$e = "\xbb\xa4\xactype=image,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

					// Clear properties - tidy up
					$properties = array();

					/* -- TABLES -- */
					// Output it to buffers
					if ($this->mpdf->tableLevel) {
						$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
						$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
					} else {
						/* -- END TABLES -- */
						$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
					} // *TABLES*
					/* -- ANNOTATIONS -- */
					if ($this->mpdf->title2annots && isset($attr['TITLE'])) {
						$objattr = array();
						$objattr['margin_top'] = 0;
						$objattr['margin_bottom'] = 0;
						$objattr['margin_left'] = 0;
						$objattr['margin_right'] = 0;
						$objattr['width'] = 0;
						$objattr['height'] = 0;
						$objattr['border_top']['w'] = 0;
						$objattr['border_bottom']['w'] = 0;
						$objattr['border_left']['w'] = 0;
						$objattr['border_right']['w'] = 0;
						$objattr['CONTENT'] = $attr['TITLE'];
						$objattr['type'] = 'annot';
						$objattr['POS-X'] = 0;
						$objattr['POS-Y'] = 0;
						$objattr['ICON'] = 'Comment';
						$objattr['AUTHOR'] = '';
						$objattr['SUBJECT'] = '';
						$objattr['OPACITY'] = $this->mpdf->annotOpacity;
						$objattr['COLOR'] = $this->mpdf->ConvertColor('yellow');
						$e = "\xbb\xa4\xactype=annot,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
						if ($this->mpdf->tableLevel) { // *TABLES*
							$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = array($e); // *TABLES*
						} // *TABLES*
						else { // *TABLES*
							$this->mpdf->textbuffer[] = array($e);
						} // *TABLES*
					}
					/* -- END ANNOTATIONS -- */
				}
				break;
			/* -- END IMAGES-CORE -- */


			// *********** CIRCULAR TEXT = TEXTCIRCLE  ********************
			case 'TEXTCIRCLE':
				$objattr = array();
				$objattr['margin_top'] = 0;
				$objattr['margin_bottom'] = 0;
				$objattr['margin_left'] = 0;
				$objattr['margin_right'] = 0;
				$objattr['padding_top'] = 0;
				$objattr['padding_bottom'] = 0;
				$objattr['padding_left'] = 0;
				$objattr['padding_right'] = 0;
				$objattr['width'] = 0;
				$objattr['height'] = 0;
				$objattr['border_top']['w'] = 0;
				$objattr['border_bottom']['w'] = 0;
				$objattr['border_left']['w'] = 0;
				$objattr['border_right']['w'] = 0;
				$objattr['top-text'] = '';
				$objattr['bottom-text'] = '';
				$objattr['r'] = 20; // radius (default value here for safety)
				$objattr['space-width'] = 120;
				$objattr['char-width'] = 100;

				$this->mpdf->InlineProperties[$tag] = $this->mpdf->saveInlineProperties();
				$properties = $this->mpdf->cssmgr->MergeCSS('INLINE', $tag, $attr);

				if (isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY']) == 'none') {
					return;
				}
				if (isset($attr['R'])) {
					$objattr['r'] = $this->mpdf->ConvertSize($attr['R'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($attr['TOP-TEXT'])) {
					$objattr['top-text'] = strcode2utf($attr['TOP-TEXT']);
					$objattr['top-text'] = $this->mpdf->lesser_entity_decode($objattr['top-text']);
					if ($this->mpdf->onlyCoreFonts)
						$objattr['top-text'] = mb_convert_encoding($objattr['top-text'], $this->mpdf->mb_enc, 'UTF-8');
				}
				if (isset($attr['BOTTOM-TEXT'])) {
					$objattr['bottom-text'] = strcode2utf($attr['BOTTOM-TEXT']);
					$objattr['bottom-text'] = $this->mpdf->lesser_entity_decode($objattr['bottom-text']);
					if ($this->mpdf->onlyCoreFonts)
						$objattr['bottom-text'] = mb_convert_encoding($objattr['bottom-text'], $this->mpdf->mb_enc, 'UTF-8');
				}
				if (isset($attr['SPACE-WIDTH']) && $attr['SPACE-WIDTH']) {
					$objattr['space-width'] = $attr['SPACE-WIDTH'];
				}
				if (isset($attr['CHAR-WIDTH']) && $attr['CHAR-WIDTH']) {
					$objattr['char-width'] = $attr['CHAR-WIDTH'];
				}

				// VISIBILITY
				$objattr['visibility'] = 'visible';
				if (isset($properties['VISIBILITY'])) {
					$v = strtolower($properties['VISIBILITY']);
					if (($v == 'hidden' || $v == 'printonly' || $v == 'screenonly') && $this->mpdf->visibility == 'visible') {
						$objattr['visibility'] = $v;
					}
				}
				if (isset($properties['FONT-SIZE'])) {
					if (strtolower($properties['FONT-SIZE']) == 'auto') {
						if ($objattr['top-text'] && $objattr['bottom-text']) {
							$objattr['fontsize'] = -2;
						} else {
							$objattr['fontsize'] = -1;
						}
					} else {
						$mmsize = $this->mpdf->ConvertSize($properties['FONT-SIZE'], ($this->mpdf->default_font_size / _MPDFK));
						$this->mpdf->SetFontSize($mmsize * _MPDFK, false);
						$objattr['fontsize'] = $this->mpdf->FontSizePt;
					}
				}
				if (isset($attr['DIVIDER'])) {
					$objattr['divider'] = strcode2utf($attr['DIVIDER']);
					$objattr['divider'] = $this->mpdf->lesser_entity_decode($objattr['divider']);
					if ($this->mpdf->onlyCoreFonts)
						$objattr['divider'] = mb_convert_encoding($objattr['divider'], $this->mpdf->mb_enc, 'UTF-8');
				}

				if (isset($properties['COLOR'])) {
					$objattr['color'] = $this->mpdf->ConvertColor($properties['COLOR']);
				}

				$objattr['fontstyle'] = '';
				if (isset($properties['FONT-WEIGHT'])) {
					if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD') {
						$objattr['fontstyle'] .= 'B';
					}
				}
				if (isset($properties['FONT-STYLE'])) {
					if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') {
						$objattr['fontstyle'] .= 'I';
					}
				}

				if (isset($properties['FONT-FAMILY'])) {
					$this->mpdf->SetFont($properties['FONT-FAMILY'], $this->mpdf->FontStyle, 0, false);
				}
				$objattr['fontfamily'] = $this->mpdf->FontFamily;

				// VSPACE and HSPACE converted to margins in MergeCSS
				if (isset($properties['MARGIN-TOP'])) {
					$objattr['margin_top'] = $this->mpdf->ConvertSize($properties['MARGIN-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-BOTTOM'])) {
					$objattr['margin_bottom'] = $this->mpdf->ConvertSize($properties['MARGIN-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-LEFT'])) {
					$objattr['margin_left'] = $this->mpdf->ConvertSize($properties['MARGIN-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-RIGHT'])) {
					$objattr['margin_right'] = $this->mpdf->ConvertSize($properties['MARGIN-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}

				if (isset($properties['PADDING-TOP'])) {
					$objattr['padding_top'] = $this->mpdf->ConvertSize($properties['PADDING-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-BOTTOM'])) {
					$objattr['padding_bottom'] = $this->mpdf->ConvertSize($properties['PADDING-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-LEFT'])) {
					$objattr['padding_left'] = $this->mpdf->ConvertSize($properties['PADDING-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-RIGHT'])) {
					$objattr['padding_right'] = $this->mpdf->ConvertSize($properties['PADDING-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}

				if (isset($properties['BORDER-TOP'])) {
					$objattr['border_top'] = $this->mpdf->border_details($properties['BORDER-TOP']);
				}
				if (isset($properties['BORDER-BOTTOM'])) {
					$objattr['border_bottom'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
				}
				if (isset($properties['BORDER-LEFT'])) {
					$objattr['border_left'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
				}
				if (isset($properties['BORDER-RIGHT'])) {
					$objattr['border_right'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
				}

				if (isset($properties['OPACITY']) && $properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) {
					$objattr['opacity'] = $properties['OPACITY'];
				}
				if (isset($properties['BACKGROUND-COLOR']) && $properties['BACKGROUND-COLOR'] != '') {
					$objattr['bgcolor'] = $this->mpdf->ConvertColor($properties['BACKGROUND-COLOR']);
				} else {
					$objattr['bgcolor'] = false;
				}
				if ($this->mpdf->HREF) {
					if (strpos($this->mpdf->HREF, ".") === false && strpos($this->mpdf->HREF, "@") !== 0) {
						$href = $this->mpdf->HREF;
						while (array_key_exists($href, $this->mpdf->internallink))
							$href = "#" . $href;
						$this->mpdf->internallink[$href] = $this->mpdf->AddLink();
						$objattr['link'] = $this->mpdf->internallink[$href];
					} else {
						$objattr['link'] = $this->mpdf->HREF;
					}
				}
				$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
				$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];


				$w = $objattr['r'] * 2;
				$h = $w;
				$objattr['height'] = $h + $extraheight;
				$objattr['width'] = $w + $extrawidth;
				$objattr['type'] = 'textcircle';

				$e = "\xbb\xa4\xactype=image,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

				// Clear properties - tidy up
				$properties = array();

				/* -- TABLES -- */
				// Output it to buffers
				if ($this->mpdf->tableLevel) {
					$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
				} else {
					/* -- END TABLES -- */
					$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
				} // *TABLES*

				if ($this->mpdf->InlineProperties[$tag]) {
					$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
				}
				unset($this->mpdf->InlineProperties[$tag]);

				break;


			/* -- TABLES -- */

			case 'TABLE': // TABLE-BEGIN
				$this->mpdf->tdbegin = false;
				$this->mpdf->lastoptionaltag = '';
				// Disable vertical justification in columns
				if ($this->mpdf->ColActive) {
					$this->mpdf->colvAlign = '';
				} // *COLUMNS*
				if ($this->mpdf->lastblocklevelchange == 1) {
					$blockstate = 1;
				} // Top margins/padding only
				else if ($this->mpdf->lastblocklevelchange < 1) {
					$blockstate = 0;
				} // NO margins/padding
				// called from block after new div e.g. <div> ... <table> ...    Outputs block top margin/border and padding
				if (count($this->mpdf->textbuffer) == 0 && $this->mpdf->lastblocklevelchange == 1 && !$this->mpdf->tableLevel && !$this->mpdf->kwt) {
					$this->mpdf->newFlowingBlock($this->mpdf->blk[$this->mpdf->blklvl]['width'], $this->mpdf->lineheight, '', false, 1, true, $this->mpdf->blk[$this->mpdf->blklvl]['direction']);
					$this->mpdf->finishFlowingBlock(true); // true = END of flowing block
				} else if (!$this->mpdf->tableLevel && count($this->mpdf->textbuffer)) {
					$this->mpdf->printbuffer($this->mpdf->textbuffer, $blockstate);
				}

				$this->mpdf->textbuffer = array();
				$this->mpdf->lastblocklevelchange = -1;



				if ($this->mpdf->tableLevel) { // i.e. now a nested table coming...
					// Save current level table
					$this->mpdf->cell['PARENTCELL'] = $this->mpdf->saveInlineProperties();
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['baseProperties'] = $this->mpdf->base_table_properties;
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cells'] = $this->mpdf->cell;
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['currrow'] = $this->mpdf->row;
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['currcol'] = $this->mpdf->col;
				}
				$this->mpdf->tableLevel++;
				$this->mpdf->cssmgr->tbCSSlvl++;

				if ($this->mpdf->tableLevel > 1) { // inherit table properties from cell in which nested
					//$this->mpdf->base_table_properties['FONT-KERNING'] = ($this->mpdf->textvar & FC_KERNING);	// mPDF 6
					$this->mpdf->base_table_properties['LETTER-SPACING'] = $this->mpdf->lSpacingCSS;
					$this->mpdf->base_table_properties['WORD-SPACING'] = $this->mpdf->wSpacingCSS;
					// mPDF 6
					$direction = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['direction'];
					$txta = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['a'];
					$cellLineHeight = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['cellLineHeight'];
					$cellLineStackingStrategy = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['cellLineStackingStrategy'];
					$cellLineStackingShift = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['cellLineStackingShift'];
				}

				if (isset($this->mpdf->tbctr[$this->mpdf->tableLevel])) {
					$this->mpdf->tbctr[$this->mpdf->tableLevel] ++;
				} else {
					$this->mpdf->tbctr[$this->mpdf->tableLevel] = 1;
				}

				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['level'] = $this->mpdf->tableLevel;
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['levelid'] = $this->mpdf->tbctr[$this->mpdf->tableLevel];

				if ($this->mpdf->tableLevel > $this->mpdf->innermostTableLevel) {
					$this->mpdf->innermostTableLevel = $this->mpdf->tableLevel;
				}
				if ($this->mpdf->tableLevel > 1) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nestedpos'] = array($this->mpdf->row, $this->mpdf->col, $this->mpdf->tbctr[($this->mpdf->tableLevel - 1)]);
				}
				//++++++++++++++++++++++++++++

				$this->mpdf->cell = array();
				$this->mpdf->col = -1; //int
				$this->mpdf->row = -1; //int
				$table = &$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]];

				// New table - any level
				$table['direction'] = $this->mpdf->directionality;
				$table['bgcolor'] = false;
				$table['va'] = false;
				$table['txta'] = false;
				$table['topntail'] = false;
				$table['thead-underline'] = false;
				$table['border'] = false;
				$table['border_details']['R']['w'] = 0;
				$table['border_details']['L']['w'] = 0;
				$table['border_details']['T']['w'] = 0;
				$table['border_details']['B']['w'] = 0;
				$table['border_details']['R']['style'] = '';
				$table['border_details']['L']['style'] = '';
				$table['border_details']['T']['style'] = '';
				$table['border_details']['B']['style'] = '';
				$table['max_cell_border_width']['R'] = 0;
				$table['max_cell_border_width']['L'] = 0;
				$table['max_cell_border_width']['T'] = 0;
				$table['max_cell_border_width']['B'] = 0;
				$table['padding']['L'] = false;
				$table['padding']['R'] = false;
				$table['padding']['T'] = false;
				$table['padding']['B'] = false;
				$table['margin']['L'] = false;
				$table['margin']['R'] = false;
				$table['margin']['T'] = false;
				$table['margin']['B'] = false;
				$table['a'] = false;
				$table['border_spacing_H'] = false;
				$table['border_spacing_V'] = false;
				$table['decimal_align'] = false;
				$this->mpdf->Reset();
				$this->mpdf->InlineProperties = array();
				$this->mpdf->InlineBDF = array(); // mPDF 6
				$this->mpdf->InlineBDFctr = 0; // mPDF 6
				$table['nc'] = $table['nr'] = 0;
				$this->mpdf->tablethead = 0;
				$this->mpdf->tabletfoot = 0;
				$this->mpdf->tabletheadjustfinished = false;

				// mPDF 6
				if ($this->mpdf->tableLevel > 1) { // inherit table properties from cell in which nested
					$table['direction'] = $direction;
					$table['txta'] = $txta;
					$table['cellLineHeight'] = $cellLineHeight;
					$table['cellLineStackingStrategy'] = $cellLineStackingStrategy;
					$table['cellLineStackingShift'] = $cellLineStackingShift;
				}


				if ($this->mpdf->blockjustfinished && !count($this->mpdf->textbuffer) && $this->mpdf->y != $this->mpdf->tMargin && $this->mpdf->collapseBlockMargins && $this->mpdf->tableLevel == 1) {
					$lastbottommargin = $this->mpdf->lastblockbottommargin;
				} else {
					$lastbottommargin = 0;
				}
				$this->mpdf->lastblockbottommargin = 0;
				$this->mpdf->blockjustfinished = false;

				if ($this->mpdf->tableLevel == 1) {
					$table['headernrows'] = 0;
					$table['footernrows'] = 0;
					$this->mpdf->base_table_properties = array();
				}

				// ADDED CSS FUNCIONS FOR TABLE
				if ($this->mpdf->cssmgr->tbCSSlvl == 1) {
					$properties = $this->mpdf->cssmgr->MergeCSS('TOPTABLE', $tag, $attr);
				} else {
					$properties = $this->mpdf->cssmgr->MergeCSS('TABLE', $tag, $attr);
				}

				$w = '';
				if (isset($properties['WIDTH'])) {
					$w = $properties['WIDTH'];
				} else if (isset($attr['WIDTH']) && $attr['WIDTH']) {
					$w = $attr['WIDTH'];
				}

				if (isset($attr['ALIGN']) && isset($align[strtolower($attr['ALIGN'])])) {
					$table['a'] = $align[strtolower($attr['ALIGN'])];
				}
				if (!$table['a']) {
					if ($table['direction'] == 'rtl') {
						$table['a'] = 'R';
					} else {
						$table['a'] = 'L';
					}
				}

				if (isset($properties['DIRECTION']) && $properties['DIRECTION']) {
					$table['direction'] = strtolower($properties['DIRECTION']);
				} else if (isset($attr['DIR']) && $attr['DIR']) {
					$table['direction'] = strtolower($attr['DIR']);
				} else if ($this->mpdf->tableLevel == 1) {
					$table['direction'] = $this->mpdf->blk[$this->mpdf->blklvl]['direction'];
				}

				if (isset($properties['BACKGROUND-COLOR'])) {
					$table['bgcolor'][-1] = $properties['BACKGROUND-COLOR'];
				} else if (isset($properties['BACKGROUND'])) {
					$table['bgcolor'][-1] = $properties['BACKGROUND'];
				} else if (isset($attr['BGCOLOR'])) {
					$table['bgcolor'][-1] = $attr['BGCOLOR'];
				}

				if (isset($properties['VERTICAL-ALIGN']) && isset($align[strtolower($properties['VERTICAL-ALIGN'])])) {
					$table['va'] = $align[strtolower($properties['VERTICAL-ALIGN'])];
				}
				if (isset($properties['TEXT-ALIGN']) && isset($align[strtolower($properties['TEXT-ALIGN'])])) {
					$table['txta'] = $align[strtolower($properties['TEXT-ALIGN'])];
				}

				if (isset($properties['AUTOSIZE']) && $properties['AUTOSIZE'] && $this->mpdf->tableLevel == 1) {
					$this->mpdf->shrink_this_table_to_fit = $properties['AUTOSIZE'];
					if ($this->mpdf->shrink_this_table_to_fit < 1) {
						$this->mpdf->shrink_this_table_to_fit = 0;
					}
				}
				if (isset($properties['ROTATE']) && $properties['ROTATE'] && $this->mpdf->tableLevel == 1) {
					$this->mpdf->table_rotate = $properties['ROTATE'];
				}
				if (isset($properties['TOPNTAIL'])) {
					$table['topntail'] = $properties['TOPNTAIL'];
				}
				if (isset($properties['THEAD-UNDERLINE'])) {
					$table['thead-underline'] = $properties['THEAD-UNDERLINE'];
				}

				if (isset($properties['BORDER'])) {
					$bord = $this->mpdf->border_details($properties['BORDER']);
					if ($bord['s']) {
						$table['border'] = _BORDER_ALL;
						$table['border_details']['R'] = $bord;
						$table['border_details']['L'] = $bord;
						$table['border_details']['T'] = $bord;
						$table['border_details']['B'] = $bord;
					}
				}
				if (isset($properties['BORDER-RIGHT'])) {
					if ($table['direction'] == 'rtl') {  // *OTL*
						$table['border_details']['R'] = $this->mpdf->border_details($properties['BORDER-LEFT']); // *OTL*
					} // *OTL*
					else { // *OTL*
						$table['border_details']['R'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
					} // *OTL*
					$this->mpdf->setBorder($table['border'], _BORDER_RIGHT, $table['border_details']['R']['s']);
				}
				if (isset($properties['BORDER-LEFT'])) {
					if ($table['direction'] == 'rtl') {  // *OTL*
						$table['border_details']['L'] = $this->mpdf->border_details($properties['BORDER-RIGHT']); // *OTL*
					} // *OTL*
					else { // *OTL*
						$table['border_details']['L'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
					} // *OTL*
					$this->mpdf->setBorder($table['border'], _BORDER_LEFT, $table['border_details']['L']['s']);
				}
				if (isset($properties['BORDER-BOTTOM'])) {
					$table['border_details']['B'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
					$this->mpdf->setBorder($table['border'], _BORDER_BOTTOM, $table['border_details']['B']['s']);
				}
				if (isset($properties['BORDER-TOP'])) {
					$table['border_details']['T'] = $this->mpdf->border_details($properties['BORDER-TOP']);
					$this->mpdf->setBorder($table['border'], _BORDER_TOP, $table['border_details']['T']['s']);
				}
				if ($table['border']) {
					$this->mpdf->table_border_css_set = 1;
				} else {
					$this->mpdf->table_border_css_set = 0;
				}

				// mPDF 6
				if (isset($properties['LANG']) && $properties['LANG']) {
					if ($this->mpdf->autoLangToFont && !$this->mpdf->usingCoreFont) {
						if ($properties['LANG'] != $this->mpdf->default_lang && $properties['LANG'] != 'UTF-8') {
							list ($coreSuitable, $mpdf_pdf_unifont) = GetLangOpts($properties['LANG'], $this->mpdf->useAdobeCJK, $this->mpdf->fontdata);
							if ($mpdf_pdf_unifont) {
								$properties['FONT-FAMILY'] = $mpdf_pdf_unifont;
							}
						}
					}
					$this->mpdf->currentLang = $properties['LANG'];
				}


				if (isset($properties['FONT-FAMILY'])) {
					$this->mpdf->default_font = $properties['FONT-FAMILY'];
					$this->mpdf->SetFont($this->mpdf->default_font, '', 0, false);
				}
				$this->mpdf->base_table_properties['FONT-FAMILY'] = $this->mpdf->FontFamily;

				if (isset($properties['FONT-SIZE'])) {
					if ($this->mpdf->tableLevel > 1) {
						$mmsize = $this->mpdf->ConvertSize($properties['FONT-SIZE'], $this->mpdf->base_table_properties['FONT-SIZE']);
					} else {
						$mmsize = $this->mpdf->ConvertSize($properties['FONT-SIZE'], $this->mpdf->default_font_size / _MPDFK);
					}
					if ($mmsize) {
						$this->mpdf->default_font_size = $mmsize * (_MPDFK);
						$this->mpdf->SetFontSize($this->mpdf->default_font_size, false);
					}
				}
				$this->mpdf->base_table_properties['FONT-SIZE'] = $this->mpdf->FontSize . 'mm';

				if (isset($properties['FONT-WEIGHT'])) {
					if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD') {
						$this->mpdf->base_table_properties['FONT-WEIGHT'] = 'BOLD';
					}
				}
				if (isset($properties['FONT-STYLE'])) {
					if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') {
						$this->mpdf->base_table_properties['FONT-STYLE'] = 'ITALIC';
					}
				}
				if (isset($properties['COLOR'])) {
					$this->mpdf->base_table_properties['COLOR'] = $properties['COLOR'];
				}
				if (isset($properties['FONT-KERNING'])) {
					$this->mpdf->base_table_properties['FONT-KERNING'] = $properties['FONT-KERNING'];
				}
				if (isset($properties['LETTER-SPACING'])) {
					$this->mpdf->base_table_properties['LETTER-SPACING'] = $properties['LETTER-SPACING'];
				}
				if (isset($properties['WORD-SPACING'])) {
					$this->mpdf->base_table_properties['WORD-SPACING'] = $properties['WORD-SPACING'];
				}
				// mPDF 6
				if (isset($properties['HYPHENS'])) {
					$this->mpdf->base_table_properties['HYPHENS'] = $properties['HYPHENS'];
				}
				if (isset($properties['LINE-HEIGHT']) && $properties['LINE-HEIGHT']) {
					$table['cellLineHeight'] = $this->mpdf->fixLineheight($properties['LINE-HEIGHT']);
				} else if ($this->mpdf->tableLevel == 1) {
					$table['cellLineHeight'] = $this->mpdf->blk[$this->mpdf->blklvl]['line_height'];
				}

				if (isset($properties['LINE-STACKING-STRATEGY']) && $properties['LINE-STACKING-STRATEGY']) {
					$table['cellLineStackingStrategy'] = strtolower($properties['LINE-STACKING-STRATEGY']);
				} else if ($this->mpdf->tableLevel == 1 && isset($this->mpdf->blk[$this->mpdf->blklvl]['line_stacking_strategy'])) {
					$table['cellLineStackingStrategy'] = $this->mpdf->blk[$this->mpdf->blklvl]['line_stacking_strategy'];
				} else {
					$table['cellLineStackingStrategy'] = 'inline-line-height';
				}

				if (isset($properties['LINE-STACKING-SHIFT']) && $properties['LINE-STACKING-SHIFT']) {
					$table['cellLineStackingShift'] = strtolower($properties['LINE-STACKING-SHIFT']);
				} else if ($this->mpdf->tableLevel == 1 && isset($this->mpdf->blk[$this->mpdf->blklvl]['line_stacking_shift'])) {
					$table['cellLineStackingShift'] = $this->mpdf->blk[$this->mpdf->blklvl]['line_stacking_shift'];
				} else {
					$table['cellLineStackingShift'] = 'consider-shifts';
				}

				if (isset($properties['PADDING-LEFT'])) {
					$table['padding']['L'] = $this->mpdf->ConvertSize($properties['PADDING-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-RIGHT'])) {
					$table['padding']['R'] = $this->mpdf->ConvertSize($properties['PADDING-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-TOP'])) {
					$table['padding']['T'] = $this->mpdf->ConvertSize($properties['PADDING-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-BOTTOM'])) {
					$table['padding']['B'] = $this->mpdf->ConvertSize($properties['PADDING-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}

				if (isset($properties['MARGIN-TOP'])) {
					if ($lastbottommargin) {
						$tmp = $this->mpdf->ConvertSize($properties['MARGIN-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
						if ($tmp > $lastbottommargin) {
							$properties['MARGIN-TOP'] -= $lastbottommargin;
						} else {
							$properties['MARGIN-TOP'] = 0;
						}
					}
					$table['margin']['T'] = $this->mpdf->ConvertSize($properties['MARGIN-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}

				if (isset($properties['MARGIN-BOTTOM'])) {
					$table['margin']['B'] = $this->mpdf->ConvertSize($properties['MARGIN-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-LEFT'])) {
					$table['margin']['L'] = $this->mpdf->ConvertSize($properties['MARGIN-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}

				if (isset($properties['MARGIN-RIGHT'])) {
					$table['margin']['R'] = $this->mpdf->ConvertSize($properties['MARGIN-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['MARGIN-LEFT']) && isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-LEFT']) == 'auto' && strtolower($properties['MARGIN-RIGHT']) == 'auto') {
					$table['a'] = 'C';
				} else if (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT']) == 'auto') {
					$table['a'] = 'R';
				} else if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT']) == 'auto') {
					$table['a'] = 'L';
				}

				if (isset($properties['BORDER-COLLAPSE']) && strtoupper($properties['BORDER-COLLAPSE']) == 'SEPARATE') {
					$table['borders_separate'] = true;
				} else {
					$table['borders_separate'] = false;
				}

				// mPDF 5.7.3

				if (isset($properties['BORDER-SPACING-H'])) {
					$table['border_spacing_H'] = $this->mpdf->ConvertSize($properties['BORDER-SPACING-H'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['BORDER-SPACING-V'])) {
					$table['border_spacing_V'] = $this->mpdf->ConvertSize($properties['BORDER-SPACING-V'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				// mPDF 5.7.3
				if (!$table['borders_separate']) {
					$table['border_spacing_H'] = $table['border_spacing_V'] = 0;
				}

				if (isset($properties['EMPTY-CELLS'])) {
					$table['empty_cells'] = strtolower($properties['EMPTY-CELLS']);  // 'hide'  or 'show'
				} else {
					$table['empty_cells'] = '';
				}

				if (isset($properties['PAGE-BREAK-INSIDE']) && strtoupper($properties['PAGE-BREAK-INSIDE']) == 'AVOID' && $this->mpdf->tableLevel == 1 && !$this->mpdf->writingHTMLfooter) {
					$this->mpdf->table_keep_together = true;
				} else if ($this->mpdf->tableLevel == 1) {
					$this->mpdf->table_keep_together = false;
				}
				if (isset($properties['PAGE-BREAK-AFTER']) && $this->mpdf->tableLevel == 1) {
					$table['page_break_after'] = strtoupper($properties['PAGE-BREAK-AFTER']);
				}

				/* -- BACKGROUNDS -- */
				if (isset($properties['BACKGROUND-GRADIENT']) && !$this->mpdf->kwt && !$this->mpdf->ColActive) {
					$table['gradient'] = $properties['BACKGROUND-GRADIENT'];
				}

				if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE'] && !$this->mpdf->kwt && !$this->mpdf->ColActive) {
					$ret = $this->mpdf->SetBackground($properties, $currblk['inner_width']);
					if ($ret) {
						$table['background-image'] = $ret;
					}
				}
				/* -- END BACKGROUNDS -- */

				if (isset($properties['OVERFLOW'])) {
					$table['overflow'] = strtolower($properties['OVERFLOW']);  // 'hidden' 'wrap' or 'visible' or 'auto'
					if (($this->mpdf->ColActive || $this->mpdf->tableLevel > 1) && $table['overflow'] == 'visible') {
						unset($table['overflow']);
					}
				}

				$properties = array();


				if (isset($attr['CELLPADDING'])) {
					$table['cell_padding'] = $attr['CELLPADDING'];
				} else {
					$table['cell_padding'] = false;
				}

				if (isset($attr['BORDER']) && $attr['BORDER'] == '1') {
					$this->mpdf->table_border_attr_set = 1;
					$bord = $this->mpdf->border_details('#000000 1px solid');
					if ($bord['s']) {
						$table['border'] = _BORDER_ALL;
						$table['border_details']['R'] = $bord;
						$table['border_details']['L'] = $bord;
						$table['border_details']['T'] = $bord;
						$table['border_details']['B'] = $bord;
					}
				} else {
					$this->mpdf->table_border_attr_set = 0;
				}

				if ($w) {
					$maxwidth = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];
					if ($table['borders_separate']) {
						$tblblw = $table['margin']['L'] + $table['margin']['R'] + $table['border_details']['L']['w'] / 2 + $table['border_details']['R']['w'] / 2;
					} else {
						$tblblw = $table['margin']['L'] + $table['margin']['R'] + $table['max_cell_border_width']['L'] / 2 + $table['max_cell_border_width']['R'] / 2;
					}
					if (strpos($w, '%') && $this->mpdf->tableLevel == 1 && !$this->mpdf->ignore_table_percents) {
						// % needs to be of inner box without table margins etc.
						$maxwidth -= $tblblw;
						$wmm = $this->mpdf->ConvertSize($w, $maxwidth, $this->mpdf->FontSize, false);
						$table['w'] = $wmm + $tblblw;
					}
					if (strpos($w, '%') && $this->mpdf->tableLevel > 1 && !$this->mpdf->ignore_table_percents && $this->mpdf->keep_table_proportions) {
						$table['wpercent'] = $w + 0;  // makes 80% -> 80
					}
					if (!strpos($w, '%') && !$this->mpdf->ignore_table_widths) {
						$wmm = $this->mpdf->ConvertSize($w, $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
						$table['w'] = $wmm + $tblblw;
					}
					if (!$this->mpdf->keep_table_proportions) {
						if (isset($table['w']) && $table['w'] > $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']) {
							$table['w'] = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];
						}
					}
				}

				if (isset($attr['AUTOSIZE']) && $this->mpdf->tableLevel == 1) {
					$this->mpdf->shrink_this_table_to_fit = $attr['AUTOSIZE'];
					if ($this->mpdf->shrink_this_table_to_fit < 1) {
						$this->mpdf->shrink_this_table_to_fit = 1;
					}
				}
				if (isset($attr['ROTATE']) && $this->mpdf->tableLevel == 1) {
					$this->mpdf->table_rotate = $attr['ROTATE'];
				}

				//++++++++++++++++++++++++++++
				if ($this->mpdf->table_rotate) {
					$this->mpdf->tbrot_Links = array();
					$this->mpdf->tbrot_Annots = array();
					$this->mpdf->tbrotForms = array();
					$this->mpdf->tbrot_BMoutlines = array();
					$this->mpdf->tbrot_toc = array();
				}

				if ($this->mpdf->kwt) {
					if ($this->mpdf->table_rotate) {
						$this->mpdf->table_keep_together = true;
					}
					$this->mpdf->kwt = false;
					$this->mpdf->kwt_saved = true;
				}

				if ($this->mpdf->tableLevel == 1 && $this->mpdf->useGraphs) {
					if (isset($attr['ID']) && $attr['ID']) {
						$this->mpdf->currentGraphId = strtoupper($attr['ID']);
					} else {
						$this->mpdf->currentGraphId = '0';
					}
					$this->mpdf->graphs[$this->mpdf->currentGraphId] = array();
				}
				//++++++++++++++++++++++++++++
				$this->mpdf->plainCell_properties = array();
				unset($table);
				break;

			case 'THEAD':
				$this->mpdf->lastoptionaltag = $tag; // Save current HTML specified optional endtag
				$this->mpdf->cssmgr->tbCSSlvl++;
				$this->mpdf->tablethead = 1;
				$this->mpdf->tabletfoot = 0;
				$properties = $this->mpdf->cssmgr->MergeCSS('TABLE', $tag, $attr);
				if (isset($properties['FONT-WEIGHT'])) {
					if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD') {
						$this->mpdf->thead_font_weight = 'B';
					} else {
						$this->mpdf->thead_font_weight = '';
					}
				}

				if (isset($properties['FONT-STYLE'])) {
					if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') {
						$this->mpdf->thead_font_style = 'I';
					} else {
						$this->mpdf->thead_font_style = '';
					}
				}
				if (isset($properties['FONT-VARIANT'])) {
					if (strtoupper($properties['FONT-VARIANT']) == 'SMALL-CAPS') {
						$this->mpdf->thead_font_smCaps = 'S';
					} else {
						$this->mpdf->thead_font_smCaps = '';
					}
				}

				if (isset($properties['VERTICAL-ALIGN'])) {
					$this->mpdf->thead_valign_default = $properties['VERTICAL-ALIGN'];
				}
				if (isset($properties['TEXT-ALIGN'])) {
					$this->mpdf->thead_textalign_default = $properties['TEXT-ALIGN'];
				}
				$properties = array();
				break;

			case 'TFOOT':
				$this->mpdf->lastoptionaltag = $tag; // Save current HTML specified optional endtag
				$this->mpdf->cssmgr->tbCSSlvl++;
				$this->mpdf->tabletfoot = 1;
				$this->mpdf->tablethead = 0;
				$properties = $this->mpdf->cssmgr->MergeCSS('TABLE', $tag, $attr);
				if (isset($properties['FONT-WEIGHT'])) {
					if (strtoupper($properties['FONT-WEIGHT']) == 'BOLD') {
						$this->mpdf->tfoot_font_weight = 'B';
					} else {
						$this->mpdf->tfoot_font_weight = '';
					}
				}

				if (isset($properties['FONT-STYLE'])) {
					if (strtoupper($properties['FONT-STYLE']) == 'ITALIC') {
						$this->mpdf->tfoot_font_style = 'I';
					} else {
						$this->mpdf->tfoot_font_style = '';
					}
				}
				if (isset($properties['FONT-VARIANT'])) {
					if (strtoupper($properties['FONT-VARIANT']) == 'SMALL-CAPS') {
						$this->mpdf->tfoot_font_smCaps = 'S';
					} else {
						$this->mpdf->tfoot_font_smCaps = '';
					}
				}

				if (isset($properties['VERTICAL-ALIGN'])) {
					$this->mpdf->tfoot_valign_default = $properties['VERTICAL-ALIGN'];
				}
				if (isset($properties['TEXT-ALIGN'])) {
					$this->mpdf->tfoot_textalign_default = $properties['TEXT-ALIGN'];
				}
				$properties = array();
				break;


			case 'TBODY':
				$this->mpdf->tablethead = 0;
				$this->mpdf->tabletfoot = 0;
				$this->mpdf->lastoptionaltag = $tag; // Save current HTML specified optional endtag
				$this->mpdf->cssmgr->tbCSSlvl++;
				$this->mpdf->cssmgr->MergeCSS('TABLE', $tag, $attr);
				break;


			case 'TR':
				$this->mpdf->lastoptionaltag = $tag; // Save current HTML specified optional endtag
				$this->mpdf->cssmgr->tbCSSlvl++;
				$this->mpdf->row++;
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nr'] ++;
				$this->mpdf->col = -1;
				$properties = $this->mpdf->cssmgr->MergeCSS('TABLE', $tag, $attr);

				if (!$this->mpdf->simpleTables && (!isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['borders_separate']) || !$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['borders_separate'])) {
					if (isset($properties['BORDER-LEFT']) && $properties['BORDER-LEFT']) {
						$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trborder-left'][$this->mpdf->row] = $properties['BORDER-LEFT'];
					}
					if (isset($properties['BORDER-RIGHT']) && $properties['BORDER-RIGHT']) {
						$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trborder-right'][$this->mpdf->row] = $properties['BORDER-RIGHT'];
					}
					if (isset($properties['BORDER-TOP']) && $properties['BORDER-TOP']) {
						$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trborder-top'][$this->mpdf->row] = $properties['BORDER-TOP'];
					}
					if (isset($properties['BORDER-BOTTOM']) && $properties['BORDER-BOTTOM']) {
						$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trborder-bottom'][$this->mpdf->row] = $properties['BORDER-BOTTOM'];
					}
				}

				if (isset($properties['BACKGROUND-COLOR'])) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'][$this->mpdf->row] = $properties['BACKGROUND-COLOR'];
				} else if (isset($attr['BGCOLOR']))
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'][$this->mpdf->row] = $attr['BGCOLOR'];

				/* -- BACKGROUNDS -- */
				if (isset($properties['BACKGROUND-GRADIENT']) && !$this->mpdf->kwt && !$this->mpdf->ColActive) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trgradients'][$this->mpdf->row] = $properties['BACKGROUND-GRADIENT'];
				}

				if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE'] && !$this->mpdf->kwt && !$this->mpdf->ColActive) {
					$ret = $this->mpdf->SetBackground($properties, $currblk['inner_width']);
					if ($ret) {
						$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trbackground-images'][$this->mpdf->row] = $ret;
					}
				}
				/* -- END BACKGROUNDS -- */




				if (isset($properties['TEXT-ROTATE'])) {
					$this->mpdf->trow_text_rotate = $properties['TEXT-ROTATE'];
				}
				if (isset($attr['TEXT-ROTATE']))
					$this->mpdf->trow_text_rotate = $attr['TEXT-ROTATE'];

				if ($this->mpdf->tablethead) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead'][$this->mpdf->row] = true;
				}
				if ($this->mpdf->tabletfoot) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot'][$this->mpdf->row] = true;
				}
				$properties = array();
				break;


			case 'TH':
			case 'TD':
				$this->mpdf->ignorefollowingspaces = true;
				$this->mpdf->lastoptionaltag = $tag; // Save current HTML specified optional endtag
				$this->mpdf->cssmgr->tbCSSlvl++;
				$this->mpdf->InlineProperties = array();
				$this->mpdf->InlineBDF = array(); // mPDF 6
				$this->mpdf->InlineBDFctr = 0; // mPDF 6
				$this->mpdf->tdbegin = true;
				$this->mpdf->col++;
				while (isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col])) {
					$this->mpdf->col++;
				}

				//Update number column
				if ($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'] < $this->mpdf->col + 1) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'] = $this->mpdf->col + 1;
				}

				$table = &$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]];

				$c = array('a' => false,
					'R' => false,
					'nowrap' => false,
					'bgcolor' => false,
					'padding' => array('L' => false,
						'R' => false,
						'T' => false,
						'B' => false
					)
				);

				if ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0) {
					$table['simple']['border'] = false;
					$table['simple']['border_details']['R']['w'] = 0;
					$table['simple']['border_details']['L']['w'] = 0;
					$table['simple']['border_details']['T']['w'] = 0;
					$table['simple']['border_details']['B']['w'] = 0;
					$table['simple']['border_details']['R']['style'] = '';
					$table['simple']['border_details']['L']['style'] = '';
					$table['simple']['border_details']['T']['style'] = '';
					$table['simple']['border_details']['B']['style'] = '';
				} else if (!$this->mpdf->simpleTables) {
					$c['border'] = false;
					$c['border_details']['R']['w'] = 0;
					$c['border_details']['L']['w'] = 0;
					$c['border_details']['T']['w'] = 0;
					$c['border_details']['B']['w'] = 0;
					$c['border_details']['mbw']['BL'] = 0;
					$c['border_details']['mbw']['BR'] = 0;
					$c['border_details']['mbw']['RT'] = 0;
					$c['border_details']['mbw']['RB'] = 0;
					$c['border_details']['mbw']['TL'] = 0;
					$c['border_details']['mbw']['TR'] = 0;
					$c['border_details']['mbw']['LT'] = 0;
					$c['border_details']['mbw']['LB'] = 0;
					$c['border_details']['R']['style'] = '';
					$c['border_details']['L']['style'] = '';
					$c['border_details']['T']['style'] = '';
					$c['border_details']['B']['style'] = '';
					$c['border_details']['R']['s'] = 0;
					$c['border_details']['L']['s'] = 0;
					$c['border_details']['T']['s'] = 0;
					$c['border_details']['B']['s'] = 0;
					$c['border_details']['R']['c'] = $this->mpdf->ConvertColor(0);
					$c['border_details']['L']['c'] = $this->mpdf->ConvertColor(0);
					$c['border_details']['T']['c'] = $this->mpdf->ConvertColor(0);
					$c['border_details']['B']['c'] = $this->mpdf->ConvertColor(0);
					$c['border_details']['R']['dom'] = 0;
					$c['border_details']['L']['dom'] = 0;
					$c['border_details']['T']['dom'] = 0;
					$c['border_details']['B']['dom'] = 0;
					$c['border_details']['cellposdom'] = 0;
				}


				if ($table['va']) {
					$c['va'] = $table['va'];
				}
				if ($table['txta']) {
					$c['a'] = $table['txta'];
				}
				if ($this->mpdf->table_border_attr_set) {
					if ($table['border_details']) {
						if (!$this->mpdf->simpleTables) {
							$c['border_details']['R'] = $table['border_details']['R'];
							$c['border_details']['L'] = $table['border_details']['L'];
							$c['border_details']['T'] = $table['border_details']['T'];
							$c['border_details']['B'] = $table['border_details']['B'];
							$c['border'] = $table['border'];
							$c['border_details']['L']['dom'] = 1;
							$c['border_details']['R']['dom'] = 1;
							$c['border_details']['T']['dom'] = 1;
							$c['border_details']['B']['dom'] = 1;
						} else if ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0) {
							$table['simple']['border_details']['R'] = $table['border_details']['R'];
							$table['simple']['border_details']['L'] = $table['border_details']['L'];
							$table['simple']['border_details']['T'] = $table['border_details']['T'];
							$table['simple']['border_details']['B'] = $table['border_details']['B'];
							$table['simple']['border'] = $table['border'];
						}
					}
				}
				// INHERITED THEAD CSS Properties
				if ($this->mpdf->tablethead) {
					if ($this->mpdf->thead_valign_default)
						$c['va'] = $align[strtolower($this->mpdf->thead_valign_default)];
					if ($this->mpdf->thead_textalign_default)
						$c['a'] = $align[strtolower($this->mpdf->thead_textalign_default)];
					if ($this->mpdf->thead_font_weight == 'B') {
						$this->mpdf->SetStyle('B', true);
					}
					if ($this->mpdf->thead_font_style == 'I') {
						$this->mpdf->SetStyle('I', true);
					}
					if ($this->mpdf->thead_font_smCaps == 'S') {
						$this->mpdf->textvar = ($this->mpdf->textvar | FC_SMALLCAPS);
					} // mPDF 5.7.1
				}

				// INHERITED TFOOT CSS Properties
				if ($this->mpdf->tabletfoot) {
					if ($this->mpdf->tfoot_valign_default)
						$c['va'] = $align[strtolower($this->mpdf->tfoot_valign_default)];
					if ($this->mpdf->tfoot_textalign_default)
						$c['a'] = $align[strtolower($this->mpdf->tfoot_textalign_default)];
					if ($this->mpdf->tfoot_font_weight == 'B') {
						$this->mpdf->SetStyle('B', true);
					}
					if ($this->mpdf->tfoot_font_style == 'I') {
						$this->mpdf->SetStyle('I', true);
					}
					if ($this->mpdf->tfoot_font_style == 'S') {
						$this->mpdf->textvar = ($this->mpdf->textvar | FC_SMALLCAPS);
					} // mPDF 5.7.1
				}


				if ($this->mpdf->trow_text_rotate) {
					$c['R'] = $this->mpdf->trow_text_rotate;
				}

				$this->mpdf->cell_border_dominance_L = 0;
				$this->mpdf->cell_border_dominance_R = 0;
				$this->mpdf->cell_border_dominance_T = 0;
				$this->mpdf->cell_border_dominance_B = 0;

				$properties = $this->mpdf->cssmgr->MergeCSS('TABLE', $tag, $attr);

				$properties = $this->mpdf->cssmgr->array_merge_recursive_unique($this->mpdf->base_table_properties, $properties);

				$this->mpdf->Reset(); // mPDF 6   ?????????????????????

				$this->mpdf->setCSS($properties, 'TABLECELL', $tag);

				$c['dfs'] = $this->mpdf->FontSize; // Default Font size


				if (isset($properties['BACKGROUND-COLOR'])) {
					$c['bgcolor'] = $properties['BACKGROUND-COLOR'];
				} else if (isset($properties['BACKGROUND'])) {
					$c['bgcolor'] = $properties['BACKGROUND'];
				} else if (isset($attr['BGCOLOR']))
					$c['bgcolor'] = $attr['BGCOLOR'];



				/* -- BACKGROUNDS -- */
				if (isset($properties['BACKGROUND-GRADIENT'])) {
					$c['gradient'] = $properties['BACKGROUND-GRADIENT'];
				} else {
					$c['gradient'] = false;
				}

				if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE'] && !$this->mpdf->keep_block_together) {
					$ret = $this->mpdf->SetBackground($properties, $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
					if ($ret) {
						$c['background-image'] = $ret;
					}
				}
				/* -- END BACKGROUNDS -- */
				if (isset($properties['VERTICAL-ALIGN'])) {
					$c['va'] = $align[strtolower($properties['VERTICAL-ALIGN'])];
				} else if (isset($attr['VALIGN']))
					$c['va'] = $align[strtolower($attr['VALIGN'])];


				if (isset($properties['TEXT-ALIGN']) && $properties['TEXT-ALIGN']) {
					if (substr($properties['TEXT-ALIGN'], 0, 1) == 'D') {
						$c['a'] = $properties['TEXT-ALIGN'];
					} else {
						$c['a'] = $align[strtolower($properties['TEXT-ALIGN'])];
					}
				}
				if (isset($attr['ALIGN']) && $attr['ALIGN']) {
					if (strtolower($attr['ALIGN']) == 'char') {
						if (isset($attr['CHAR']) && $attr['CHAR']) {
							$char = html_entity_decode($attr['CHAR']);
							$char = strcode2utf($char);
							$d = array_search($char, $this->mpdf->decimal_align);
							if ($d !== false) {
								$c['a'] = $d . 'R';
							}
						} else {
							$c['a'] = 'DPR';
						}
					} else {
						$c['a'] = $align[strtolower($attr['ALIGN'])];
					}
				}

				// mPDF 6
				$c['direction'] = $table['direction'];
				if (isset($attr['DIR']) and $attr['DIR'] != '') {
					$c['direction'] = strtolower($attr['DIR']);
				}
				if (isset($properties['DIRECTION'])) {
					$c['direction'] = strtolower($properties['DIRECTION']);
				}

				if (!$c['a']) {
					if (isset($c['direction']) && $c['direction'] == 'rtl') {
						$c['a'] = 'R';
					} else {
						$c['a'] = 'L';
					}
				}

				$c['cellLineHeight'] = $table['cellLineHeight'];
				if (isset($properties['LINE-HEIGHT'])) {
					$c['cellLineHeight'] = $this->mpdf->fixLineheight($properties['LINE-HEIGHT']);
				}

				$c['cellLineStackingStrategy'] = $table['cellLineStackingStrategy'];
				if (isset($properties['LINE-STACKING-STRATEGY'])) {
					$c['cellLineStackingStrategy'] = strtolower($properties['LINE-STACKING-STRATEGY']);
				}

				$c['cellLineStackingShift'] = $table['cellLineStackingShift'];
				if (isset($properties['LINE-STACKING-SHIFT'])) {
					$c['cellLineStackingShift'] = strtolower($properties['LINE-STACKING-SHIFT']);
				}

				if (isset($properties['TEXT-ROTATE']) && ($properties['TEXT-ROTATE'] || $properties['TEXT-ROTATE'] === "0")) {
					$c['R'] = $properties['TEXT-ROTATE'];
				}
				if (isset($properties['BORDER'])) {
					$bord = $this->mpdf->border_details($properties['BORDER']);
					if ($bord['s']) {
						if (!$this->mpdf->simpleTables) {
							$c['border'] = _BORDER_ALL;
							$c['border_details']['R'] = $bord;
							$c['border_details']['L'] = $bord;
							$c['border_details']['T'] = $bord;
							$c['border_details']['B'] = $bord;
							$c['border_details']['L']['dom'] = $this->mpdf->cell_border_dominance_L;
							$c['border_details']['R']['dom'] = $this->mpdf->cell_border_dominance_R;
							$c['border_details']['T']['dom'] = $this->mpdf->cell_border_dominance_T;
							$c['border_details']['B']['dom'] = $this->mpdf->cell_border_dominance_B;
						} else if ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0) {
							$table['simple']['border'] = _BORDER_ALL;
							$table['simple']['border_details']['R'] = $bord;
							$table['simple']['border_details']['L'] = $bord;
							$table['simple']['border_details']['T'] = $bord;
							$table['simple']['border_details']['B'] = $bord;
						}
					}
				}
				if (!$this->mpdf->simpleTables) {
					if (isset($properties['BORDER-RIGHT']) && $properties['BORDER-RIGHT']) {
						$c['border_details']['R'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
						$this->mpdf->setBorder($c['border'], _BORDER_RIGHT, $c['border_details']['R']['s']);
						$c['border_details']['R']['dom'] = $this->mpdf->cell_border_dominance_R;
					}
					if (isset($properties['BORDER-LEFT']) && $properties['BORDER-LEFT']) {
						$c['border_details']['L'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
						$this->mpdf->setBorder($c['border'], _BORDER_LEFT, $c['border_details']['L']['s']);
						$c['border_details']['L']['dom'] = $this->mpdf->cell_border_dominance_L;
					}
					if (isset($properties['BORDER-BOTTOM']) && $properties['BORDER-BOTTOM']) {
						$c['border_details']['B'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
						$this->mpdf->setBorder($c['border'], _BORDER_BOTTOM, $c['border_details']['B']['s']);
						$c['border_details']['B']['dom'] = $this->mpdf->cell_border_dominance_B;
					}
					if (isset($properties['BORDER-TOP']) && $properties['BORDER-TOP']) {
						$c['border_details']['T'] = $this->mpdf->border_details($properties['BORDER-TOP']);
						$this->mpdf->setBorder($c['border'], _BORDER_TOP, $c['border_details']['T']['s']);
						$c['border_details']['T']['dom'] = $this->mpdf->cell_border_dominance_T;
					}
				} else if ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0) {
					if (isset($properties['BORDER-LEFT']) && $properties['BORDER-LEFT']) {
						$bord = $this->mpdf->border_details($properties['BORDER-LEFT']);
						if ($bord['s']) {
							$table['simple']['border'] = _BORDER_ALL;
						} else {
							$table['simple']['border'] = 0;
						}
						$table['simple']['border_details']['R'] = $bord;
						$table['simple']['border_details']['L'] = $bord;
						$table['simple']['border_details']['T'] = $bord;
						$table['simple']['border_details']['B'] = $bord;
					}
				}

				if ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0 && !$table['borders_separate'] && $table['simple']['border']) {
					$table['border_details'] = $table['simple']['border_details'];
					$table['border'] = $table['simple']['border'];
				}

				// Border set on TR (if collapsed only)
				if (!$table['borders_separate'] && !$this->mpdf->simpleTables && isset($table['trborder-left'][$this->mpdf->row])) {
					if ($this->mpdf->col == 0) {
						$left = $this->mpdf->border_details($table['trborder-left'][$this->mpdf->row]);
						$c['border_details']['L'] = $left;
						$this->mpdf->setBorder($c['border'], _BORDER_LEFT, $c['border_details']['L']['s']);
					}
					$c['border_details']['B'] = $this->mpdf->border_details($table['trborder-bottom'][$this->mpdf->row]);
					$this->mpdf->setBorder($c['border'], _BORDER_BOTTOM, $c['border_details']['B']['s']);
					$c['border_details']['T'] = $this->mpdf->border_details($table['trborder-top'][$this->mpdf->row]);
					$this->mpdf->setBorder($c['border'], _BORDER_TOP, $c['border_details']['T']['s']);
				}

				if ($this->mpdf->packTableData && !$this->mpdf->simpleTables) {
					$c['borderbin'] = $this->mpdf->_packCellBorder($c);
					unset($c['border']);
					unset($c['border_details']);
				}

				if (isset($properties['PADDING-LEFT'])) {
					$c['padding']['L'] = $this->mpdf->ConvertSize($properties['PADDING-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-RIGHT'])) {
					$c['padding']['R'] = $this->mpdf->ConvertSize($properties['PADDING-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-BOTTOM'])) {
					$c['padding']['B'] = $this->mpdf->ConvertSize($properties['PADDING-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}
				if (isset($properties['PADDING-TOP'])) {
					$c['padding']['T'] = $this->mpdf->ConvertSize($properties['PADDING-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				}

				$w = '';
				if (isset($properties['WIDTH'])) {
					$w = $properties['WIDTH'];
				} else if (isset($attr['WIDTH'])) {
					$w = $attr['WIDTH'];
				}
				if ($w) {
					if (strpos($w, '%') && !$this->mpdf->ignore_table_percents) {
						$c['wpercent'] = $w + 0;
					} // makes 80% -> 80
					else if (!strpos($w, '%') && !$this->mpdf->ignore_table_widths) {
						$c['w'] = $this->mpdf->ConvertSize($w, $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
					}
				}

				if (isset($properties['HEIGHT']) && !strpos($properties['HEIGHT'], '%')) {
					$c['h'] = $this->mpdf->ConvertSize($properties['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
				} else if (isset($attr['HEIGHT']) && !strpos($attr['HEIGHT'], '%'))
					$c['h'] = $this->mpdf->ConvertSize($attr['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);

				if (isset($properties['WHITE-SPACE'])) {
					if (strtoupper($properties['WHITE-SPACE']) == 'NOWRAP') {
						$c['nowrap'] = 1;
					}
				}
				$properties = array();


				if (isset($attr['TEXT-ROTATE'])) {
					$c['R'] = $attr['TEXT-ROTATE'];
				}
				if (isset($attr['NOWRAP']) && $attr['NOWRAP'])
					$c['nowrap'] = 1;

				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col] = $c;
				unset($c);
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0;

				$cs = $rs = 1;
				if (isset($attr['COLSPAN']) && $attr['COLSPAN'] > 1)
					$cs = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['colspan'] = $attr['COLSPAN'];
				if ($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'] < $this->mpdf->col + $cs) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'] = $this->mpdf->col + $cs;
				} // following code moved outside if...
				for ($l = $this->mpdf->col; $l < $this->mpdf->col + $cs; $l++) {
					if ($l - $this->mpdf->col)
						$this->mpdf->cell[$this->mpdf->row][$l] = 0;
				}
				if (isset($attr['ROWSPAN']) && $attr['ROWSPAN'] > 1)
					$rs = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['rowspan'] = $attr['ROWSPAN'];
				for ($k = $this->mpdf->row; $k < $this->mpdf->row + $rs; $k++) {
					for ($l = $this->mpdf->col; $l < $this->mpdf->col + $cs; $l++) {
						if ($k - $this->mpdf->row || $l - $this->mpdf->col)
							$this->mpdf->cell[$k][$l] = 0;
					}
				}
				unset($table);
				break;
			/* -- END TABLES -- */
		}//end of switch
	}

	function CloseTag($tag, &$ahtml, &$ihtml)
	{ // mPDF 6
		//Closing tag
		if ($tag == 'OPTION') {
			$this->mpdf->selectoption['ACTIVE'] = false;
			$this->mpdf->lastoptionaltag = '';
		}

		if ($tag == 'TTS' or $tag == 'TTA' or $tag == 'TTZ') {
			if ($this->mpdf->InlineProperties[$tag]) {
				$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
			}
			unset($this->mpdf->InlineProperties[$tag]);
			$ltag = strtolower($tag);
			$this->mpdf->$ltag = false;
		}


		if ($tag == 'FONT' || $tag == 'SPAN' || $tag == 'CODE' || $tag == 'KBD' || $tag == 'SAMP' || $tag == 'TT' || $tag == 'VAR' || $tag == 'INS' || $tag == 'STRONG' || $tag == 'CITE' || $tag == 'SUB' || $tag == 'SUP' || $tag == 'S' || $tag == 'STRIKE' || $tag == 'DEL' || $tag == 'Q' || $tag == 'EM' || $tag == 'B' || $tag == 'I' || $tag == 'U' | $tag == 'SMALL' || $tag == 'BIG' || $tag == 'ACRONYM' || $tag == 'MARK' || $tag == 'TIME' || $tag == 'PROGRESS' || $tag == 'METER' || $tag == 'BDO' || $tag == 'BDI'
		) {

			$annot = false; // mPDF 6
			$bdf = false; // mPDF 6
			// mPDF 5.7.3 Inline tags
			if ($tag == 'PROGRESS' || $tag == 'METER') {
				if (isset($this->mpdf->InlineProperties[$tag]) && $this->mpdf->InlineProperties[$tag]) {
					$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
				}
				unset($this->mpdf->InlineProperties[$tag]);
				if (isset($this->mpdf->InlineAnnots[$tag]) && $this->mpdf->InlineAnnots[$tag]) {
					$annot = $this->mpdf->InlineAnnots[$tag];
				} // *ANNOTATIONS*
				unset($this->mpdf->InlineAnnots[$tag]); // *ANNOTATIONS*
			} else {
				if (isset($this->mpdf->InlineProperties[$tag]) && count($this->mpdf->InlineProperties[$tag])) {
					$tmpProps = array_pop($this->mpdf->InlineProperties[$tag]); // mPDF 5.7.4
					$this->mpdf->restoreInlineProperties($tmpProps);
				}
				if (isset($this->mpdf->InlineAnnots[$tag]) && count($this->mpdf->InlineAnnots[$tag])) {  // *ANNOTATIONS*
					$annot = array_pop($this->mpdf->InlineAnnots[$tag]);  // *ANNOTATIONS*
				} // *ANNOTATIONS*
				if (isset($this->mpdf->InlineBDF[$tag]) && count($this->mpdf->InlineBDF[$tag])) {  // mPDF 6
					$bdfarr = array_pop($this->mpdf->InlineBDF[$tag]);
					$bdf = $bdfarr[0];
				}
			}

			/* -- ANNOTATIONS -- */
			if ($annot) { // mPDF 6
				if ($this->mpdf->tableLevel) { // *TABLES*
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = array($annot); // *TABLES*
				} // *TABLES*
				else { // *TABLES*
					$this->mpdf->textbuffer[] = array($annot);
				} // *TABLES*
			}
			/* -- END ANNOTATIONS -- */

			// mPDF 6 bidi
			// mPDF 6 Bidirectional formatting for inline elements
			if ($bdf) {
				$popf = $this->mpdf->_setBidiCodes('end', $bdf);
				$this->mpdf->OTLdata = array();
				if ($this->mpdf->tableLevel) {
					$this->mpdf->_saveCellTextBuffer($popf);
				} else {
					$this->mpdf->_saveTextBuffer($popf);
				}
			}
		} // End of (most) Inline elements eg SPAN


		if ($tag == 'METER' || $tag == 'PROGRESS') {
			$this->mpdf->ignorefollowingspaces = false;
			$this->mpdf->inMeter = false;
		}


		if ($tag == 'A') {
			$this->mpdf->HREF = '';
			if (isset($this->mpdf->InlineProperties['A'])) {
				$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties['A']);
			}
			unset($this->mpdf->InlineProperties['A']);
		}

		if ($tag == 'LEGEND') {
			if (count($this->mpdf->textbuffer) && !$this->mpdf->tableLevel) {
				$leg = $this->mpdf->textbuffer[(count($this->mpdf->textbuffer) - 1)];
				unset($this->mpdf->textbuffer[(count($this->mpdf->textbuffer) - 1)]);
				$this->mpdf->textbuffer = array_values($this->mpdf->textbuffer);
				$this->mpdf->blk[$this->mpdf->blklvl]['border_legend'] = $leg;
				$this->mpdf->blk[$this->mpdf->blklvl]['margin_top'] += ($leg[11] / 2) / _MPDFK;
				$this->mpdf->blk[$this->mpdf->blklvl]['padding_top'] += ($leg[11] / 2) / _MPDFK;
			}
			if (isset($this->mpdf->InlineProperties['LEGEND'])) {
				$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties['LEGEND']);
			}
			unset($this->mpdf->InlineProperties['LEGEND']);
			$this->mpdf->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
		}

		/* -- FORMS -- */
		// *********** FORM ELEMENTS ********************

		if ($tag == 'TEXTAREA') {
			$this->mpdf->ignorefollowingspaces = false;
			$this->mpdf->specialcontent = '';
			if ($this->mpdf->InlineProperties[$tag]) {
				$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
			}
			unset($this->mpdf->InlineProperties[$tag]);
		}


		if ($tag == 'SELECT') {
			$this->mpdf->ignorefollowingspaces = false;
			$this->mpdf->lastoptionaltag = '';
			$texto = '';
			$OTLdata = false;
			if (isset($this->mpdf->selectoption['SELECTED'])) {
				$texto = $this->mpdf->selectoption['SELECTED'];
			}
			if (isset($this->mpdf->selectoption['SELECTED-OTLDATA'])) {
				$OTLdata = $this->mpdf->selectoption['SELECTED-OTLDATA'];
			}

			if ($this->mpdf->useActiveForms) {
				$w = $this->mpdf->selectoption['MAXWIDTH'];
			} else {
				$w = $this->mpdf->GetStringWidth($texto, true, $OTLdata);
			}
			if ($w == 0) {
				$w = 5;
			}
			$objattr['type'] = 'select';
			$objattr['text'] = $texto;
			$objattr['OTLdata'] = $OTLdata;
			if (isset($this->mpdf->selectoption['NAME'])) {
				$objattr['fieldname'] = $this->mpdf->selectoption['NAME'];
			}
			if (isset($this->mpdf->selectoption['READONLY'])) {
				$objattr['readonly'] = true;
			}
			if (isset($this->mpdf->selectoption['REQUIRED'])) {
				$objattr['required'] = true;
			}
			if (isset($this->mpdf->selectoption['SPELLCHECK'])) {
				$objattr['spellcheck'] = true;
			}
			if (isset($this->mpdf->selectoption['EDITABLE'])) {
				$objattr['editable'] = true;
			}
			if (isset($this->mpdf->selectoption['ONCHANGE'])) {
				$objattr['onChange'] = $this->mpdf->selectoption['ONCHANGE'];
			}
			if (isset($this->mpdf->selectoption['ITEMS'])) {
				$objattr['items'] = $this->mpdf->selectoption['ITEMS'];
			}
			if (isset($this->mpdf->selectoption['MULTIPLE'])) {
				$objattr['multiple'] = $this->mpdf->selectoption['MULTIPLE'];
			}
			if (isset($this->mpdf->selectoption['DISABLED'])) {
				$objattr['disabled'] = $this->mpdf->selectoption['DISABLED'];
			}
			if (isset($this->mpdf->selectoption['TITLE'])) {
				$objattr['title'] = $this->mpdf->selectoption['TITLE'];
			}
			if (isset($this->mpdf->selectoption['COLOR'])) {
				$objattr['color'] = $this->mpdf->selectoption['COLOR'];
			}
			if (isset($this->mpdf->selectoption['SIZE'])) {
				$objattr['size'] = $this->mpdf->selectoption['SIZE'];
			}
			if (isset($objattr['size']) && $objattr['size'] > 1) {
				$rows = $objattr['size'];
			} else {
				$rows = 1;
			}

			$objattr['fontfamily'] = $this->mpdf->FontFamily;
			$objattr['fontsize'] = $this->mpdf->FontSizePt;

			$objattr['width'] = $w + ($this->mpdf->mpdfform->form_element_spacing['select']['outer']['h'] * 2) + ($this->mpdf->mpdfform->form_element_spacing['select']['inner']['h'] * 2) + ($this->mpdf->FontSize * 1.4);
			$objattr['height'] = ($this->mpdf->FontSize * $rows) + ($this->mpdf->mpdfform->form_element_spacing['select']['outer']['v'] * 2) + ($this->mpdf->mpdfform->form_element_spacing['select']['inner']['v'] * 2);
			$e = "\xbb\xa4\xactype=select,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

			// Clear properties - tidy up
			$properties = array();

			// Output it to buffers
			if ($this->mpdf->tableLevel) { // *TABLES*
				$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width']; // *TABLES*
			} // *TABLES*
			else { // *TABLES*
				$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
			} // *TABLES*

			$this->mpdf->selectoption = array();
			$this->mpdf->specialcontent = '';

			if ($this->mpdf->InlineProperties[$tag]) {
				$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
			}
			unset($this->mpdf->InlineProperties[$tag]);
		}
		/* -- END FORMS -- */


		// *********** BLOCKS ********************
		// mPDF 6  Lists
		if ($tag == 'P' || $tag == 'DIV' || $tag == 'H1' || $tag == 'H2' || $tag == 'H3' || $tag == 'H4' || $tag == 'H5' || $tag == 'H6' || $tag == 'PRE' || $tag == 'FORM' || $tag == 'ADDRESS' || $tag == 'BLOCKQUOTE' || $tag == 'CENTER' || $tag == 'DT' || $tag == 'DD' || $tag == 'DL' || $tag == 'CAPTION' || $tag == 'FIELDSET' || $tag == 'UL' || $tag == 'OL' || $tag == 'LI' || $tag == 'ARTICLE' || $tag == 'ASIDE' || $tag == 'FIGURE' || $tag == 'FIGCAPTION' || $tag == 'FOOTER' || $tag == 'HEADER' || $tag == 'HGROUP' || $tag == 'MAIN' || $tag == 'NAV' || $tag == 'SECTION' || $tag == 'DETAILS' || $tag == 'SUMMARY'
		) {

			// mPDF 6 bidi
			// Block
			// If unicode-bidi set, any embedding levels, isolates, or overrides started by this box are closed
			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bidicode'])) {
				$blockpost = $this->mpdf->_setBidiCodes('end', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
				if ($blockpost) {
					$this->mpdf->OTLdata = array();
					if ($this->mpdf->tableLevel) {
						$this->mpdf->_saveCellTextBuffer($blockpost);
					} else {
						$this->mpdf->_saveTextBuffer($blockpost);
					}
				}
			}

			$this->mpdf->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
			$this->mpdf->blockjustfinished = true;

			$this->mpdf->lastblockbottommargin = $this->mpdf->blk[$this->mpdf->blklvl]['margin_bottom'];
			// mPDF 6  Lists
			if ($tag == 'UL' || $tag == 'OL') {
				if ($this->mpdf->listlvl > 0 && $this->mpdf->tableLevel) {
					if (isset($this->mpdf->listtype[$this->mpdf->listlvl]))
						unset($this->mpdf->listtype[$this->mpdf->listlvl]);
				}
				$this->mpdf->listlvl--;
				$this->mpdf->listitem = array();
			}
			if ($tag == 'LI') {
				$this->mpdf->listitem = array();
			}

			if (preg_match('/^H\d/', $tag) && !$this->mpdf->tableLevel && !$this->mpdf->writingToC) {
				if (isset($this->mpdf->h2toc[$tag]) || isset($this->mpdf->h2bookmarks[$tag])) {
					$content = '';
					if (count($this->mpdf->textbuffer) == 1) {
						$content = $this->mpdf->textbuffer[0][0];
					} else {
						for ($i = 0; $i < count($this->mpdf->textbuffer); $i++) {
							if (substr($this->mpdf->textbuffer[$i][0], 0, 3) != "\xbb\xa4\xac") { //inline object
								$content .= $this->mpdf->textbuffer[$i][0];
							}
						}
					}
					/* -- TOC -- */
					if (isset($this->mpdf->h2toc[$tag])) {
						$objattr = array();
						$objattr['type'] = 'toc';
						$objattr['toclevel'] = $this->mpdf->h2toc[$tag];
						$objattr['CONTENT'] = htmlspecialchars($content);
						$e = "\xbb\xa4\xactype=toc,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
						array_unshift($this->mpdf->textbuffer, array($e));
					}
					/* -- END TOC -- */
					/* -- BOOKMARKS -- */
					if (isset($this->mpdf->h2bookmarks[$tag])) {
						$objattr = array();
						$objattr['type'] = 'bookmark';
						$objattr['bklevel'] = $this->mpdf->h2bookmarks[$tag];
						$objattr['CONTENT'] = $content;
						$e = "\xbb\xa4\xactype=toc,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
						array_unshift($this->mpdf->textbuffer, array($e));
					}
					/* -- END BOOKMARKS -- */
				}
			}

			/* -- TABLES -- */
			if ($this->mpdf->tableLevel) {
				if ($this->mpdf->linebreakjustfinished) {
					$this->mpdf->blockjustfinished = false;
				}
				if (isset($this->mpdf->InlineProperties['BLOCKINTABLE'])) {
					if ($this->mpdf->InlineProperties['BLOCKINTABLE']) {
						$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties['BLOCKINTABLE']);
					}
					unset($this->mpdf->InlineProperties['BLOCKINTABLE']);
				}
				if ($tag == 'PRE') {
					$this->mpdf->ispre = false;
				}
				return;
			}
			/* -- END TABLES -- */
			$this->mpdf->lastoptionaltag = '';
			$this->mpdf->divbegin = false;

			$this->mpdf->linebreakjustfinished = false;

			$this->mpdf->x = $this->mpdf->lMargin + $this->mpdf->blk[$this->mpdf->blklvl]['outer_left_margin'];

			/* -- CSS-FLOAT -- */
			// If float contained in a float, need to extend bottom to allow for it
			$currpos = $this->mpdf->page * 1000 + $this->mpdf->y;
			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['float_endpos']) && $this->mpdf->blk[$this->mpdf->blklvl]['float_endpos'] > $currpos) {
				$old_page = $this->mpdf->page;
				$new_page = intval($this->mpdf->blk[$this->mpdf->blklvl]['float_endpos'] / 1000);
				if ($old_page != $new_page) {
					$s = $this->mpdf->PrintPageBackgrounds();
					// Writes after the marker so not overwritten later by page background etc.
					$this->mpdf->pages[$this->mpdf->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->mpdf->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->mpdf->pages[$this->mpdf->page]);
					$this->mpdf->pageBackgrounds = array();
					$this->mpdf->page = $new_page;
					$this->mpdf->ResetMargins();
					$this->mpdf->Reset();
					$this->mpdf->pageoutput[$this->mpdf->page] = array();
				}
				$this->mpdf->y = (($this->mpdf->blk[$this->mpdf->blklvl]['float_endpos'] * 1000) % 1000000) / 1000; // mod changes operands to integers before processing
			}
			/* -- END CSS-FLOAT -- */


			//Print content
			if ($this->mpdf->lastblocklevelchange == 1) {
				$blockstate = 3;
			} // Top & bottom margins/padding
			else if ($this->mpdf->lastblocklevelchange == -1) {
				$blockstate = 2;
			} // Bottom margins/padding only
			else {
				$blockstate = 0;
			}
			// called from after e.g. </table> </div> </div> ...    Outputs block margin/border and padding
			if (count($this->mpdf->textbuffer) && $this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1]) {
				if (substr($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0], 0, 3) != "\xbb\xa4\xac") { // not special content
					// Right trim last content and adjust OTLdata
					if (preg_match('/[ ]+$/', $this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0], $m)) {
						$strip = strlen($m[0]);
						$this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0] = substr($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0], 0, (strlen($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0]) - $strip));
						/* -- OTL -- */
						if (isset($this->mpdf->CurrentFont['useOTL']) && $this->mpdf->CurrentFont['useOTL']) {
							$this->mpdf->otl->trimOTLdata($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][18], false, true); // mPDF 6  ZZZ99K
						}
						/* -- END OTL -- */
					}
				}
			}

			if (count($this->mpdf->textbuffer) == 0 && $this->mpdf->lastblocklevelchange != 0) {
				//$this->mpdf->newFlowingBlock( $this->mpdf->blk[$this->mpdf->blklvl]['width'],$this->mpdf->lineheight,'',false,2,true, (isset($this->mpdf->blk[$this->mpdf->blklvl]['direction']) ? $this->mpdf->blk[$this->mpdf->blklvl]['direction'] : 'ltr'));
				$this->mpdf->newFlowingBlock($this->mpdf->blk[$this->mpdf->blklvl]['width'], $this->mpdf->lineheight, '', false, $blockstate, true, (isset($this->mpdf->blk[$this->mpdf->blklvl]['direction']) ? $this->mpdf->blk[$this->mpdf->blklvl]['direction'] : 'ltr'));
				$this->mpdf->finishFlowingBlock(true); // true = END of flowing block
				$this->mpdf->PaintDivBB('', $blockstate);
			} else {
				$this->mpdf->printbuffer($this->mpdf->textbuffer, $blockstate);
			}


			$this->mpdf->textbuffer = array();

			if ($this->mpdf->kwt) {
				$this->mpdf->kwt_height = $this->mpdf->y - $this->mpdf->kwt_y0;
			}

			/* -- CSS-IMAGE-FLOAT -- */
			$this->mpdf->printfloatbuffer();
			/* -- END CSS-IMAGE-FLOAT -- */

			if ($tag == 'PRE') {
				$this->mpdf->ispre = false;
			}

			/* -- CSS-FLOAT -- */
			if ($this->mpdf->blk[$this->mpdf->blklvl]['float'] == 'R') {
				// If width not set, here would need to adjust and output buffer
				$s = $this->mpdf->PrintPageBackgrounds();
				// Writes after the marker so not overwritten later by page background etc.
				$this->mpdf->pages[$this->mpdf->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->mpdf->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->mpdf->pages[$this->mpdf->page]);
				$this->mpdf->pageBackgrounds = array();
				$this->mpdf->Reset();
				$this->mpdf->pageoutput[$this->mpdf->page] = array();

				for ($i = ($this->mpdf->blklvl - 1); $i >= 0; $i--) {
					if (isset($this->mpdf->blk[$i]['float_endpos'])) {
						$this->mpdf->blk[$i]['float_endpos'] = max($this->mpdf->blk[$i]['float_endpos'], ($this->mpdf->page * 1000 + $this->mpdf->y));
					} else {
						$this->mpdf->blk[$i]['float_endpos'] = $this->mpdf->page * 1000 + $this->mpdf->y;
					}
				}

				$this->mpdf->floatDivs[] = array(
					'side' => 'R',
					'startpage' => $this->mpdf->blk[$this->mpdf->blklvl]['startpage'],
					'y0' => $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'],
					'startpos' => ($this->mpdf->blk[$this->mpdf->blklvl]['startpage'] * 1000 + $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y']),
					'endpage' => $this->mpdf->page,
					'y1' => $this->mpdf->y,
					'endpos' => ($this->mpdf->page * 1000 + $this->mpdf->y),
					'w' => $this->mpdf->blk[$this->mpdf->blklvl]['float_width'],
					'blklvl' => $this->mpdf->blklvl,
					'blockContext' => $this->mpdf->blk[$this->mpdf->blklvl - 1]['blockContext']
				);

				$this->mpdf->y = $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'];
				$this->mpdf->page = $this->mpdf->blk[$this->mpdf->blklvl]['startpage'];
				$this->mpdf->ResetMargins();
				$this->mpdf->pageoutput[$this->mpdf->page] = array();
			}
			if ($this->mpdf->blk[$this->mpdf->blklvl]['float'] == 'L') {
				// If width not set, here would need to adjust and output buffer
				$s = $this->mpdf->PrintPageBackgrounds();
				// Writes after the marker so not overwritten later by page background etc.
				$this->mpdf->pages[$this->mpdf->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->mpdf->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->mpdf->pages[$this->mpdf->page]);
				$this->mpdf->pageBackgrounds = array();
				$this->mpdf->Reset();
				$this->mpdf->pageoutput[$this->mpdf->page] = array();

				for ($i = ($this->mpdf->blklvl - 1); $i >= 0; $i--) {
					if (isset($this->mpdf->blk[$i]['float_endpos'])) {
						$this->mpdf->blk[$i]['float_endpos'] = max($this->mpdf->blk[$i]['float_endpos'], ($this->mpdf->page * 1000 + $this->mpdf->y));
					} else {
						$this->mpdf->blk[$i]['float_endpos'] = $this->mpdf->page * 1000 + $this->mpdf->y;
					}
				}

				$this->mpdf->floatDivs[] = array(
					'side' => 'L',
					'startpage' => $this->mpdf->blk[$this->mpdf->blklvl]['startpage'],
					'y0' => $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'],
					'startpos' => ($this->mpdf->blk[$this->mpdf->blklvl]['startpage'] * 1000 + $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y']),
					'endpage' => $this->mpdf->page,
					'y1' => $this->mpdf->y,
					'endpos' => ($this->mpdf->page * 1000 + $this->mpdf->y),
					'w' => $this->mpdf->blk[$this->mpdf->blklvl]['float_width'],
					'blklvl' => $this->mpdf->blklvl,
					'blockContext' => $this->mpdf->blk[$this->mpdf->blklvl - 1]['blockContext']
				);

				$this->mpdf->y = $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'];
				$this->mpdf->page = $this->mpdf->blk[$this->mpdf->blklvl]['startpage'];
				$this->mpdf->ResetMargins();
				$this->mpdf->pageoutput[$this->mpdf->page] = array();
			}
			/* -- END CSS-FLOAT -- */

			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['visibility']) && $this->mpdf->blk[$this->mpdf->blklvl]['visibility'] != 'visible') {
				$this->mpdf->SetVisibility('visible');
			}

			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['page_break_after'])) {
				$page_break_after = $this->mpdf->blk[$this->mpdf->blklvl]['page_break_after'];
			} else {
				$page_break_after = '';
			}

			//Reset values
			$this->mpdf->Reset();

			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['z-index']) && $this->mpdf->blk[$this->mpdf->blklvl]['z-index'] > 0) {
				$this->mpdf->EndLayer();
			}

			// mPDF 6 page-break-inside:avoid
			if ($this->mpdf->blk[$this->mpdf->blklvl]['keep_block_together']) {
				$movepage = false;
				// If page-break-inside:avoid section has broken to new page but fits on one side - then move:
				if (($this->mpdf->page - $this->mpdf->kt_p00) == 1 && $this->mpdf->y < $this->mpdf->kt_y00) {
					$movepage = true;
				}
				if (($this->mpdf->page - $this->mpdf->kt_p00) > 0) {
					for ($i = $this->mpdf->page; $i > $this->mpdf->kt_p00; $i--) {
						unset($this->mpdf->pages[$i]);
						if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bb_painted'][$i])) {
							unset($this->mpdf->blk[$this->mpdf->blklvl]['bb_painted'][$i]);
						}
						if (isset($this->mpdf->blk[$this->mpdf->blklvl]['marginCorrected'][$i])) {
							unset($this->mpdf->blk[$this->mpdf->blklvl]['marginCorrected'][$i]);
						}
						if (isset($this->mpdf->pageoutput[$i])) {
							unset($this->mpdf->pageoutput[$i]);
						}
					}
					$this->mpdf->page = $this->mpdf->kt_p00;
				}
				$this->mpdf->keep_block_together = 0;
				$this->mpdf->pageoutput[$this->mpdf->page] = array();

				$this->mpdf->y = $this->mpdf->kt_y00;
				$ihtml = $this->mpdf->blk[$this->mpdf->blklvl]['array_i'] - 1;

				$ahtml[$ihtml + 1] .= ' pagebreakavoidchecked="true";'; // avoid re-iterating; read in OpenTag()

				unset($this->mpdf->blk[$this->mpdf->blklvl]);
				$this->mpdf->blklvl--;

				for ($blklvl = 1; $blklvl <= $this->mpdf->blklvl; $blklvl++) {
					$this->mpdf->blk[$blklvl]['y0'] = $this->mpdf->blk[$blklvl]['initial_y0'];
					$this->mpdf->blk[$blklvl]['x0'] = $this->mpdf->blk[$blklvl]['initial_x0'];
					$this->mpdf->blk[$blklvl]['startpage'] = $this->mpdf->blk[$blklvl]['initial_startpage'];
				}

				if (isset($this->mpdf->blk[$this->mpdf->blklvl]['x0'])) {
					$this->mpdf->x = $this->mpdf->blk[$this->mpdf->blklvl]['x0'];
				} else {
					$this->mpdf->x = $this->mpdf->lMargin;
				}

				$this->mpdf->lastblocklevelchange = 0;
				$this->mpdf->ResetMargins();
				if ($movepage) {
					$this->mpdf->AddPage();
				}
				return;
			}

			if ($this->mpdf->blklvl > 0) { // ==0 SHOULDN'T HAPPEN - NOT XHTML
				if ($this->mpdf->blk[$this->mpdf->blklvl]['tag'] == $tag) {
					unset($this->mpdf->blk[$this->mpdf->blklvl]);
					$this->mpdf->blklvl--;
				}
				//else { echo $tag; exit; }	// debug - forces error if incorrectly nested html tags
			}

			$this->mpdf->lastblocklevelchange = -1;
			// Reset Inline-type properties
			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['InlineProperties'])) {
				$this->mpdf->restoreInlineProperties($this->mpdf->blk[$this->mpdf->blklvl]['InlineProperties']);
			}

			$this->mpdf->x = $this->mpdf->lMargin + $this->mpdf->blk[$this->mpdf->blklvl]['outer_left_margin'];

			if (!$this->mpdf->tableLevel && $page_break_after) {
				$save_blklvl = $this->mpdf->blklvl;
				$save_blk = $this->mpdf->blk;
				$save_silp = $this->mpdf->saveInlineProperties();
				$save_ilp = $this->mpdf->InlineProperties;
				$save_bflp = $this->mpdf->InlineBDF;
				$save_bflpc = $this->mpdf->InlineBDFctr; // mPDF 6
				// mPDF 6 pagebreaktype
				$startpage = $this->mpdf->page;
				$pagebreaktype = $this->mpdf->defaultPagebreakType;
				if ($this->mpdf->ColActive) {
					$pagebreaktype = 'cloneall';
				}

				// mPDF 6 pagebreaktype
				$this->mpdf->_preForcedPagebreak($pagebreaktype);

				if ($page_break_after == 'RIGHT') {
					$this->mpdf->AddPage($this->mpdf->CurOrientation, 'NEXT-ODD', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0);
				} else if ($page_break_after == 'LEFT') {
					$this->mpdf->AddPage($this->mpdf->CurOrientation, 'NEXT-EVEN', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0);
				} else {
					$this->mpdf->AddPage($this->mpdf->CurOrientation, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0);
				}

				// mPDF 6 pagebreaktype
				$this->mpdf->_postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl);

				$this->mpdf->InlineProperties = $save_ilp;
				$this->mpdf->InlineBDF = $save_bflp;
				$this->mpdf->InlineBDFctr = $save_bflpc; // mPDF 6
				$this->mpdf->restoreInlineProperties($save_silp);
			}
			// mPDF 6 bidi
			// Block
			// If unicode-bidi set, any embedding levels, isolates, or overrides reopened in the continuing block
			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bidicode'])) {
				$blockpre = $this->mpdf->_setBidiCodes('start', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
				if ($blockpre) {
					$this->mpdf->OTLdata = array();
					if ($this->mpdf->tableLevel) {
						$this->mpdf->_saveCellTextBuffer($blockpre);
					} else {
						$this->mpdf->_saveTextBuffer($blockpre);
					}
				}
			}
		}


		/* -- TABLES -- */

		if ($tag == 'TH')
			$this->mpdf->SetStyle('B', false);

		if (($tag == 'TH' or $tag == 'TD') && $this->mpdf->tableLevel) {
			$this->mpdf->lastoptionaltag = 'TR';
			unset($this->mpdf->cssmgr->tablecascadeCSS[$this->mpdf->cssmgr->tbCSSlvl]);
			$this->mpdf->cssmgr->tbCSSlvl--;
			if (!$this->mpdf->tdbegin) {
				return;
			}
			$this->mpdf->tdbegin = false;
			// Added for correct calculation of cell column width - otherwise misses the last line if not end </p> etc.
			if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
				if (!is_array($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col])) {
					throw new MpdfException("You may have an error in your HTML code e.g. &lt;/td&gt;&lt;/td&gt;");
				}
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
			} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
			}

			// Remove last <br> if at end of cell
			if (isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'])) {
				$ntb = count($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer']);
			} else {
				$ntb = 0;
			}
			if ($ntb > 1 && $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][$ntb - 1][0] == "\n") {
				unset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][$ntb - 1]);
			}

			if ($this->mpdf->tablethead) {
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead'][$this->mpdf->row] = true;
				if ($this->mpdf->tableLevel == 1) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['headernrows'] = max($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['headernrows'], ($this->mpdf->row + 1));
				}
			}
			if ($this->mpdf->tabletfoot) {
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot'][$this->mpdf->row] = true;
				if ($this->mpdf->tableLevel == 1) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['footernrows'] = max($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['footernrows'], ($this->mpdf->row + 1 - $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['headernrows']));
				}
			}
			$this->mpdf->Reset();
		}

		if ($tag == 'TR' && $this->mpdf->tableLevel) {
			// If Border set on TR - Update right border
			if (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trborder-left'][$this->mpdf->row])) {
				$c = & $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col];
				if ($c) {
					if ($this->mpdf->packTableData) {
						$cell = $this->mpdf->_unpackCellBorder($c['borderbin']);
					} else {
						$cell = $c;
					}
					$cell['border_details']['R'] = $this->mpdf->border_details($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trborder-right'][$this->mpdf->row]);
					$this->mpdf->setBorder($cell['border'], _BORDER_RIGHT, $cell['border_details']['R']['s']);
					if ($this->mpdf->packTableData) {
						$c['borderbin'] = $this->mpdf->_packCellBorder($cell);
						unset($c['border']);
						unset($c['border_details']);
					} else {
						$c = $cell;
					}
				}
			}
			$this->mpdf->lastoptionaltag = '';
			unset($this->mpdf->cssmgr->tablecascadeCSS[$this->mpdf->cssmgr->tbCSSlvl]);
			$this->mpdf->cssmgr->tbCSSlvl--;
			$this->mpdf->trow_text_rotate = '';
			$this->mpdf->tabletheadjustfinished = false;
		}

		if ($tag == 'TBODY') {
			$this->mpdf->lastoptionaltag = '';
			unset($this->mpdf->cssmgr->tablecascadeCSS[$this->mpdf->cssmgr->tbCSSlvl]);
			$this->mpdf->cssmgr->tbCSSlvl--;
		}

		if ($tag == 'THEAD') {
			$this->mpdf->lastoptionaltag = '';
			unset($this->mpdf->cssmgr->tablecascadeCSS[$this->mpdf->cssmgr->tbCSSlvl]);
			$this->mpdf->cssmgr->tbCSSlvl--;
			$this->mpdf->tablethead = 0;
			$this->mpdf->tabletheadjustfinished = true;
			$this->mpdf->ResetStyles();
			$this->mpdf->thead_font_weight = '';
			$this->mpdf->thead_font_style = '';
			$this->mpdf->thead_font_smCaps = '';

			$this->mpdf->thead_valign_default = '';
			$this->mpdf->thead_textalign_default = '';
		}

		if ($tag == 'TFOOT') {
			$this->mpdf->lastoptionaltag = '';
			unset($this->mpdf->cssmgr->tablecascadeCSS[$this->mpdf->cssmgr->tbCSSlvl]);
			$this->mpdf->cssmgr->tbCSSlvl--;
			$this->mpdf->tabletfoot = 0;
			$this->mpdf->ResetStyles();
			$this->mpdf->tfoot_font_weight = '';
			$this->mpdf->tfoot_font_style = '';
			$this->mpdf->tfoot_font_smCaps = '';

			$this->mpdf->tfoot_valign_default = '';
			$this->mpdf->tfoot_textalign_default = '';
		}

		if ($tag == 'TABLE') { // TABLE-END (
			if ($this->mpdf->progressBar) {
				$this->mpdf->UpdateProgressBar(1, '', 'TABLE');
			} // *PROGRESS-BAR*
			if ($this->mpdf->progressBar) {
				$this->mpdf->UpdateProgressBar(7, 0, '');
			} // *PROGRESS-BAR*
			$this->mpdf->lastoptionaltag = '';
			unset($this->mpdf->cssmgr->tablecascadeCSS[$this->mpdf->cssmgr->tbCSSlvl]);
			$this->mpdf->cssmgr->tbCSSlvl--;
			$this->mpdf->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
			// mPDF 5.7.3
			// In case a colspan (on a row after first row) exceeded number of columns in table
			for ($k = 0; $k < $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nr']; $k++) {
				for ($l = 0; $l < $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc']; $l++) {
					if (!isset($this->mpdf->cell[$k][$l])) {
						for ($n = $l - 1; $n >= 0; $n--) {
							if (isset($this->mpdf->cell[$k][$n]) && $this->mpdf->cell[$k][$n] != 0) {
								break;
							}
						}
						$this->mpdf->cell[$k][$l] = array(
							'a' => 'C',
							'va' => 'M',
							'R' => false,
							'nowrap' => false,
							'bgcolor' => false,
							'padding' => array('L' => false, 'R' => false, 'T' => false, 'B' => false),
							'gradient' => false,
							's' => 0,
							'maxs' => 0,
							'textbuffer' => array(),
							'dfs' => $this->mpdf->FontSize,
						);

						if (!$this->mpdf->simpleTables) {
							$this->mpdf->cell[$k][$l]['border'] = 0;
							$this->mpdf->cell[$k][$l]['border_details']['R'] = array('s' => 0, 'w' => 0, 'c' => false, 'style' => 'none', 'dom' => 0);
							$this->mpdf->cell[$k][$l]['border_details']['L'] = array('s' => 0, 'w' => 0, 'c' => false, 'style' => 'none', 'dom' => 0);
							$this->mpdf->cell[$k][$l]['border_details']['T'] = array('s' => 0, 'w' => 0, 'c' => false, 'style' => 'none', 'dom' => 0);
							$this->mpdf->cell[$k][$l]['border_details']['B'] = array('s' => 0, 'w' => 0, 'c' => false, 'style' => 'none', 'dom' => 0);
							$this->mpdf->cell[$k][$l]['border_details']['mbw'] = array('BL' => 0, 'BR' => 0, 'RT' => 0, 'RB' => 0, 'TL' => 0, 'TR' => 0, 'LT' => 0, 'LB' => 0);
							if ($this->mpdf->packTableData) {
								$this->mpdf->cell[$k][$l]['borderbin'] = $this->mpdf->_packCellBorder($this->mpdf->cell[$k][$l]);
								unset($this->mpdf->cell[$k][$l]['border']);
								unset($this->mpdf->cell[$k][$l]['border_details']);
							}
						}
					}
				}
			}
			$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cells'] = $this->mpdf->cell;
			$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['wc'] = array_pad(array(), $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'], array('miw' => 0, 'maw' => 0));
			$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['hr'] = array_pad(array(), $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nr'], 0);

			// Move table footer <tfoot> row to end of table
			if (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot']) && count($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot'])) {
				$tfrows = array();
				foreach ($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot'] AS $r => $val) {
					if ($val) {
						$tfrows[] = $r;
					}
				}
				$temp = array();
				$temptf = array();
				foreach ($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cells'] AS $k => $row) {
					if (in_array($k, $tfrows)) {
						$temptf[] = $row;
					} else {
						$temp[] = $row;
					}
				}
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot'] = array();
				for ($i = count($temp); $i < (count($temp) + count($temptf)); $i++) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot'][$i] = true;
				}
				// Update nestedpos row references
				if (isset($this->mpdf->table[($this->mpdf->tableLevel + 1)]) && count($this->mpdf->table[($this->mpdf->tableLevel + 1)])) {
					foreach ($this->mpdf->table[($this->mpdf->tableLevel + 1)] AS $nid => $nested) {
						$this->mpdf->table[($this->mpdf->tableLevel + 1)][$nid]['nestedpos'][0] -= count($temptf);
					}
				}
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cells'] = array_merge($temp, $temptf);

				// Update other arays set on row number
				// [trbackground-images] [trgradients]
				$temptrbgi = array();
				$temptrbgg = array();
				$temptrbgc = array();
				if (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'][-1])) {
					$temptrbgc[-1] = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'][-1];
				}
				for ($k = 0; $k < $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nr']; $k++) {
					if (!in_array($k, $tfrows)) {
						$temptrbgi[] = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trbackground-images'][$k]) ? $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trbackground-images'][$k] : null);
						$temptrbgg[] = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trgradients'][$k]) ? $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trgradients'][$k] : null);
						$temptrbgc[] = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'][$k]) ? $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'][$k] : null);
					}
				}
				for ($k = 0; $k < $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nr']; $k++) {
					if (in_array($k, $tfrows)) {
						$temptrbgi[] = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trbackground-images'][$k]) ? $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trbackground-images'][$k] : null);
						$temptrbgg[] = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trgradients'][$k]) ? $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trgradients'][$k] : null);
						$temptrbgc[] = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'][$k]) ? $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'][$k] : null);
					}
				}
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trbackground-images'] = $temptrbgi;
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trgradients'] = $temptrbgg;
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['bgcolor'] = $temptrbgc;
				// Should Update all other arays set on row number, but cell properties have been set so not needed
				// [bgcolor] [trborder-left] [trborder-right] [trborder-top] [trborder-bottom]
			}

			if ($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['direction'] == 'rtl') {
				$this->mpdf->_reverseTableDir($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]);
			}

			// Fix Borders *********************************************
			$this->mpdf->_fixTableBorders($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]);

			if ($this->mpdf->progressBar) {
				$this->mpdf->UpdateProgressBar(7, 10, ' ');
			} // *PROGRESS-BAR*

			if ($this->mpdf->ColActive) {
				$this->mpdf->table_rotate = 0;
			} // *COLUMNS*
			if ($this->mpdf->table_rotate <> 0) {
				$this->mpdf->tablebuffer = '';
				// Max width for rotated table
				$this->mpdf->tbrot_maxw = $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 1);
				$this->mpdf->tbrot_maxh = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];  // Max width for rotated table
				$this->mpdf->tbrot_align = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['a'];
			}
			$this->mpdf->shrin_k = 1;

			if ($this->mpdf->shrink_tables_to_fit < 1) {
				$this->mpdf->shrink_tables_to_fit = 1;
			}
			if (!$this->mpdf->shrink_this_table_to_fit) {
				$this->mpdf->shrink_this_table_to_fit = $this->mpdf->shrink_tables_to_fit;
			}

			if ($this->mpdf->tableLevel > 1) {
				// deal with nested table

				$this->mpdf->_tableColumnWidth($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]], true);

				$tmiw = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['miw'];
				$tmaw = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['maw'];
				$tl = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['tl'];

				// Go down to lower table level
				$this->mpdf->tableLevel--;

				// Reset lower level table
				$this->mpdf->base_table_properties = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['baseProperties'];
				// mPDF 5.7.3
				$this->mpdf->default_font = $this->mpdf->base_table_properties['FONT-FAMILY'];
				$this->mpdf->SetFont($this->mpdf->default_font, '', 0, false);
				$this->mpdf->default_font_size = $this->mpdf->ConvertSize($this->mpdf->base_table_properties['FONT-SIZE']) * (_MPDFK);
				$this->mpdf->SetFontSize($this->mpdf->default_font_size, false);

				$this->mpdf->cell = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cells'];
				if (isset($this->mpdf->cell['PARENTCELL'])) {
					if ($this->mpdf->cell['PARENTCELL']) {
						$this->mpdf->restoreInlineProperties($this->mpdf->cell['PARENTCELL']);
					}
					unset($this->mpdf->cell['PARENTCELL']);
				}
				$this->mpdf->row = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['currrow'];
				$this->mpdf->col = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['currcol'];
				$objattr = array();
				$objattr['type'] = 'nestedtable';
				$objattr['nestedcontent'] = $this->mpdf->tbctr[($this->mpdf->tableLevel + 1)];
				$objattr['table'] = $this->mpdf->tbctr[$this->mpdf->tableLevel];
				$objattr['row'] = $this->mpdf->row;
				$objattr['col'] = $this->mpdf->col;
				$objattr['level'] = $this->mpdf->tableLevel;
				$e = "\xbb\xa4\xactype=nestedtable,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
				$this->mpdf->_saveCellTextBuffer($e);
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $tl;
				if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
				} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
				}
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0; // reset
				if ((isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['nestedmaw']) && $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['nestedmaw'] < $tmaw) || !isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['nestedmaw'])) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['nestedmaw'] = $tmaw;
				}
				if ((isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['nestedmiw']) && $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['nestedmiw'] < $tmiw) || !isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['nestedmiw'])) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['nestedmiw'] = $tmiw;
				}
				$this->mpdf->tdbegin = true;
				$this->mpdf->nestedtablejustfinished = true;
				$this->mpdf->ignorefollowingspaces = true;
				return;
			}
			$this->mpdf->cMarginL = 0;
			$this->mpdf->cMarginR = 0;
			$this->mpdf->cMarginT = 0;
			$this->mpdf->cMarginB = 0;
			$this->mpdf->cellPaddingL = 0;
			$this->mpdf->cellPaddingR = 0;
			$this->mpdf->cellPaddingT = 0;
			$this->mpdf->cellPaddingB = 0;

			if (isset($this->mpdf->table[1][1]['overflow']) && $this->mpdf->table[1][1]['overflow'] == 'visible') {
				if ($this->mpdf->kwt || $this->mpdf->table_rotate || $this->mpdf->table_keep_together || $this->mpdf->ColActive) {
					$this->mpdf->kwt = false;
					$this->mpdf->table_rotate = 0;
					$this->mpdf->table_keep_together = false;
					//throw new MpdfException("mPDF Warning: You cannot use CSS overflow:visible together with any of these functions: 'Keep-with-table', rotated tables, page-break-inside:avoid, or columns");
				}
				$this->mpdf->_tableColumnWidth($this->mpdf->table[1][1], true);
				$this->mpdf->_tableWidth($this->mpdf->table[1][1]);
			} else {
				if (!$this->mpdf->kwt_saved) {
					$this->mpdf->kwt_height = 0;
				}

				list($check, $tablemiw) = $this->mpdf->_tableColumnWidth($this->mpdf->table[1][1], true);
				$save_table = $this->mpdf->table;
				$reset_to_minimum_width = false;
				$added_page = false;

				if ($check > 1) {
					if ($check > $this->mpdf->shrink_this_table_to_fit && $this->mpdf->table_rotate) {
						if ($this->mpdf->y != $this->mpdf->tMargin) {
							$this->mpdf->AddPage($this->mpdf->CurOrientation);
							$this->mpdf->kwt_moved = true;
						}
						$added_page = true;
						$this->mpdf->tbrot_maxw = $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height;
						//$check = $tablemiw/$this->mpdf->tbrot_maxw; 	// undo any shrink
						$check = 1;  // undo any shrink
					}
					$reset_to_minimum_width = true;
				}

				if ($reset_to_minimum_width) {
					$this->mpdf->shrin_k = $check;

					$this->mpdf->default_font_size /= $this->mpdf->shrin_k;
					$this->mpdf->SetFontSize($this->mpdf->default_font_size, false);

					$this->mpdf->shrinkTable($this->mpdf->table[1][1], $this->mpdf->shrin_k);

					$this->mpdf->_tableColumnWidth($this->mpdf->table[1][1], false); // repeat
					// Starting at $this->mpdf->innermostTableLevel
					// Shrink table values - and redo columnWidth
					for ($lvl = 2; $lvl <= $this->mpdf->innermostTableLevel; $lvl++) {
						for ($nid = 1; $nid <= $this->mpdf->tbctr[$lvl]; $nid++) {
							$this->mpdf->shrinkTable($this->mpdf->table[$lvl][$nid], $this->mpdf->shrin_k);
							$this->mpdf->_tableColumnWidth($this->mpdf->table[$lvl][$nid], false);
						}
					}
				}

				// Set table cell widths for top level table
				// Use $shrin_k to resize but don't change again
				$this->mpdf->SetLineHeight('', $this->mpdf->table[1][1]['cellLineHeight']);

				// Top level table
				$this->mpdf->_tableWidth($this->mpdf->table[1][1]);
			}

			// Now work through any nested tables setting child table[w'] = parent cell['w']
			// Now do nested tables _tableWidth
			for ($lvl = 2; $lvl <= $this->mpdf->innermostTableLevel; $lvl++) {
				for ($nid = 1; $nid <= $this->mpdf->tbctr[$lvl]; $nid++) {
					// HERE set child table width = cell width

					list($parentrow, $parentcol, $parentnid) = $this->mpdf->table[$lvl][$nid]['nestedpos'];

					$c = & $this->mpdf->table[($lvl - 1)][$parentnid]['cells'][$parentrow][$parentcol];

					if (isset($c['colspan']) && $c['colspan'] > 1) {
						$parentwidth = 0;
						for ($cs = 0; $cs < $c['colspan']; $cs++) {
							$parentwidth += $this->mpdf->table[($lvl - 1)][$parentnid]['wc'][$parentcol + $cs];
						}
					} else {
						$parentwidth = $this->mpdf->table[($lvl - 1)][$parentnid]['wc'][$parentcol];
					}


					//$parentwidth -= ALLOW FOR PADDING ETC.in parent cell
					if (!$this->mpdf->simpleTables) {
						if ($this->mpdf->packTableData) {
							list($bt, $br, $bb, $bl) = $this->mpdf->_getBorderWidths($c['borderbin']);
						} else {
							$br = $c['border_details']['R']['w'];
							$bl = $c['border_details']['L']['w'];
						}
						if ($this->mpdf->table[$lvl - 1][$parentnid]['borders_separate']) {
							$parentwidth -= $br + $bl + $c['padding']['L'] + $c['padding']['R'] + $this->mpdf->table[($lvl - 1)][$parentnid]['border_spacing_H'];
						} else {
							$parentwidth -= $br / 2 + $bl / 2 + $c['padding']['L'] + $c['padding']['R'];
						}
					} else if ($this->mpdf->simpleTables) {
						if ($this->mpdf->table[$lvl - 1][$parentnid]['borders_separate']) {
							$parentwidth -= $this->mpdf->table[($lvl - 1)][$parentnid]['simple']['border_details']['L']['w'] + $this->mpdf->table[($lvl - 1)][$parentnid]['simple']['border_details']['R']['w'] + $c['padding']['L'] + $c['padding']['R'] + $this->mpdf->table[($lvl - 1)][$parentnid]['border_spacing_H'];
						} else {
							$parentwidth -= $this->mpdf->table[($lvl - 1)][$parentnid]['simple']['border_details']['L']['w'] / 2 + $this->mpdf->table[($lvl - 1)][$parentnid]['simple']['border_details']['R']['w'] / 2 + $c['padding']['L'] + $c['padding']['R'];
						}
					}
					if (isset($this->mpdf->table[$lvl][$nid]['wpercent']) && $this->mpdf->table[$lvl][$nid]['wpercent'] && $lvl > 1) {
						$this->mpdf->table[$lvl][$nid]['w'] = $parentwidth;
					} else if ($parentwidth > $this->mpdf->table[$lvl][$nid]['maw']) {
						$this->mpdf->table[$lvl][$nid]['w'] = $this->mpdf->table[$lvl][$nid]['maw'];
					} else {
						$this->mpdf->table[$lvl][$nid]['w'] = $parentwidth;
					}
					unset($c);
					$this->mpdf->_tableWidth($this->mpdf->table[$lvl][$nid]);
				}
			}

			// Starting at $this->mpdf->innermostTableLevel
			// Cascade back up nested tables: setting heights back up the tree
			for ($lvl = $this->mpdf->innermostTableLevel; $lvl > 0; $lvl--) {
				for ($nid = 1; $nid <= $this->mpdf->tbctr[$lvl]; $nid++) {
					list($tableheight, $maxrowheight, $fullpage, $remainingpage, $maxfirstrowheight) = $this->mpdf->_tableHeight($this->mpdf->table[$lvl][$nid]);
				}
			}
			if ($this->mpdf->progressBar) {
				$this->mpdf->UpdateProgressBar(7, 20, ' ');
			} // *PROGRESS-BAR*
			if ($this->mpdf->table[1][1]['overflow'] == 'visible') {
				if ($maxrowheight > $fullpage) {
					throw new MpdfException("mPDF Warning: A Table row is greater than available height. You cannot use CSS overflow:visible");
				}
				if ($maxfirstrowheight > $remainingpage) {
					$this->mpdf->AddPage($this->mpdf->CurOrientation);
				}
				$r = 0;
				$c = 0;
				$p = 0;
				$y = 0;
				$finished = false;
				while (!$finished) {
					list($finished, $r, $c, $p, $y, $y0) = $this->mpdf->_tableWrite($this->mpdf->table[1][1], true, $r, $c, $p, $y);
					if (!$finished) {
						$this->mpdf->AddPage($this->mpdf->CurOrientation);
						// If printed something on first spread, set same y
						if ($r == 0 && $y0 > -1) {
							$this->mpdf->y = $y0;
						}
					}
				}
			} else {
				$recalculate = 1;
				$forcerecalc = false;
				// RESIZING ALGORITHM
				if ($maxrowheight > $fullpage) {
					$recalculate = $this->mpdf->tbsqrt($maxrowheight / $fullpage, 1);
					$forcerecalc = true;
				} else if ($this->mpdf->table_rotate) { // NB $remainingpage == $fullpage == the width of the page
					if ($tableheight > $remainingpage) {
						// If can fit on remainder of page whilst respecting autsize value..
						if (($this->mpdf->shrin_k * $this->mpdf->tbsqrt($tableheight / $remainingpage, 1)) <= $this->mpdf->shrink_this_table_to_fit) {
							$recalculate = $this->mpdf->tbsqrt($tableheight / $remainingpage, 1);
						} else if (!$added_page) {
							if ($this->mpdf->y != $this->mpdf->tMargin) {
								$this->mpdf->AddPage($this->mpdf->CurOrientation);
								$this->mpdf->kwt_moved = true;
							}
							$added_page = true;
							$this->mpdf->tbrot_maxw = $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height;
							// 0.001 to force it to recalculate
							$recalculate = (1 / $this->mpdf->shrin_k) + 0.001;  // undo any shrink
						}
					} else {
						$recalculate = 1;
					}
				} else if ($this->mpdf->table_keep_together || ($this->mpdf->table[1][1]['nr'] == 1 && !$this->mpdf->writingHTMLfooter)) {
					if ($tableheight > $fullpage) {
						if (($this->mpdf->shrin_k * $this->mpdf->tbsqrt($tableheight / $fullpage, 1)) <= $this->mpdf->shrink_this_table_to_fit) {
							$recalculate = $this->mpdf->tbsqrt($tableheight / $fullpage, 1);
						} else if ($this->mpdf->tableMinSizePriority) {
							$this->mpdf->table_keep_together = false;
							$recalculate = 1.001;
						} else {
							if ($this->mpdf->y != $this->mpdf->tMargin) {
								$this->mpdf->AddPage($this->mpdf->CurOrientation);
								$this->mpdf->kwt_moved = true;
							}
							$added_page = true;
							$this->mpdf->tbrot_maxw = $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height;
							$recalculate = $this->mpdf->tbsqrt($tableheight / $fullpage, 1);
						}
					} else if ($tableheight > $remainingpage) {
						// If can fit on remainder of page whilst respecting autsize value..
						if (($this->mpdf->shrin_k * $this->mpdf->tbsqrt($tableheight / $remainingpage, 1)) <= $this->mpdf->shrink_this_table_to_fit) {
							$recalculate = $this->mpdf->tbsqrt($tableheight / $remainingpage, 1);
						} else {
							if ($this->mpdf->y != $this->mpdf->tMargin) {
								// mPDF 6
								if ($this->mpdf->AcceptPageBreak()) {
									$this->mpdf->AddPage($this->mpdf->CurOrientation);
								} else if ($this->mpdf->ColActive && $tableheight > (($this->mpdf->h - $this->mpdf->bMargin) - $this->mpdf->y0)) {
									$this->mpdf->AddPage($this->mpdf->CurOrientation);
								}
								$this->mpdf->kwt_moved = true;
							}
							$added_page = true;
							$this->mpdf->tbrot_maxw = $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height;
							$recalculate = 1.001;
						}
					} else {
						$recalculate = 1;
					}
				} else {
					$recalculate = 1;
				}

				if ($recalculate > $this->mpdf->shrink_this_table_to_fit && !$forcerecalc) {
					$recalculate = $this->mpdf->shrink_this_table_to_fit;
				}

				$iteration = 1;

				// RECALCULATE
				while ($recalculate <> 1) {
					$this->mpdf->shrin_k1 = $recalculate;
					$this->mpdf->shrin_k *= $recalculate;
					$this->mpdf->default_font_size /= ($this->mpdf->shrin_k1);
					$this->mpdf->SetFontSize($this->mpdf->default_font_size, false);
					$this->mpdf->SetLineHeight('', $this->mpdf->table[1][1]['cellLineHeight']);
					$this->mpdf->table = $save_table;
					if ($this->mpdf->shrin_k <> 1) {
						$this->mpdf->shrinkTable($this->mpdf->table[1][1], $this->mpdf->shrin_k);
					}
					$this->mpdf->_tableColumnWidth($this->mpdf->table[1][1], false); // repeat
					// Starting at $this->mpdf->innermostTableLevel
					// Shrink table values - and redo columnWidth
					for ($lvl = 2; $lvl <= $this->mpdf->innermostTableLevel; $lvl++) {
						for ($nid = 1; $nid <= $this->mpdf->tbctr[$lvl]; $nid++) {
							if ($this->mpdf->shrin_k <> 1) {
								$this->mpdf->shrinkTable($this->mpdf->table[$lvl][$nid], $this->mpdf->shrin_k);
							}
							$this->mpdf->_tableColumnWidth($this->mpdf->table[$lvl][$nid], false);
						}
					}
					// Set table cell widths for top level table
					// Top level table
					$this->mpdf->_tableWidth($this->mpdf->table[1][1]);

					// Now work through any nested tables setting child table[w'] = parent cell['w']
					// Now do nested tables _tableWidth
					for ($lvl = 2; $lvl <= $this->mpdf->innermostTableLevel; $lvl++) {
						for ($nid = 1; $nid <= $this->mpdf->tbctr[$lvl]; $nid++) {
							// HERE set child table width = cell width

							list($parentrow, $parentcol, $parentnid) = $this->mpdf->table[$lvl][$nid]['nestedpos'];
							$c = & $this->mpdf->table[($lvl - 1)][$parentnid]['cells'][$parentrow][$parentcol];

							if (isset($c['colspan']) && $c['colspan'] > 1) {
								$parentwidth = 0;
								for ($cs = 0; $cs < $c['colspan']; $cs++) {
									$parentwidth += $this->mpdf->table[($lvl - 1)][$parentnid]['wc'][$parentcol + $cs];
								}
							} else {
								$parentwidth = $this->mpdf->table[($lvl - 1)][$parentnid]['wc'][$parentcol];
							}

							//$parentwidth -= ALLOW FOR PADDING ETC.in parent cell
							if (!$this->mpdf->simpleTables) {
								if ($this->mpdf->packTableData) {
									list($bt, $br, $bb, $bl) = $this->mpdf->_getBorderWidths($c['borderbin']);
								} else {
									$br = $c['border_details']['R']['w'];
									$bl = $c['border_details']['L']['w'];
								}
								if ($this->mpdf->table[$lvl - 1][$parentnid]['borders_separate']) {
									$parentwidth -= $br + $bl + $c['padding']['L'] + $c['padding']['R'] + $this->mpdf->table[($lvl - 1)][$parentnid]['border_spacing_H'];
								} else {
									$parentwidth -= $br / 2 + $bl / 2 + $c['padding']['L'] + $c['padding']['R'];
								}
							} else if ($this->mpdf->simpleTables) {
								if ($this->mpdf->table[$lvl - 1][$parentnid]['borders_separate']) {
									$parentwidth -= $this->mpdf->table[($lvl - 1)][$parentnid]['simple']['border_details']['L']['w'] + $this->mpdf->table[($lvl - 1)][$parentnid]['simple']['border_details']['R']['w'] + $c['padding']['L'] + $c['padding']['R'] + $this->mpdf->table[($lvl - 1)][$parentnid]['border_spacing_H'];
								} else {
									$parentwidth -= ($this->mpdf->table[($lvl - 1)][$parentnid]['simple']['border_details']['L']['w'] + $this->mpdf->table[($lvl - 1)][$parentnid]['simple']['border_details']['R']['w']) / 2 + $c['padding']['L'] + $c['padding']['R'];
								}
							}
							if (isset($this->mpdf->table[$lvl][$nid]['wpercent']) && $this->mpdf->table[$lvl][$nid]['wpercent'] && $lvl > 1) {
								$this->mpdf->table[$lvl][$nid]['w'] = $parentwidth;
							} else if ($parentwidth > $this->mpdf->table[$lvl][$nid]['maw']) {
								$this->mpdf->table[$lvl][$nid]['w'] = $this->mpdf->table[$lvl][$nid]['maw'];
							} else {
								$this->mpdf->table[$lvl][$nid]['w'] = $parentwidth;
							}
							unset($c);
							$this->mpdf->_tableWidth($this->mpdf->table[$lvl][$nid]);
						}
					}

					// Starting at $this->mpdf->innermostTableLevel
					// Cascade back up nested tables: setting heights back up the tree
					for ($lvl = $this->mpdf->innermostTableLevel; $lvl > 0; $lvl--) {
						for ($nid = 1; $nid <= $this->mpdf->tbctr[$lvl]; $nid++) {
							list($tableheight, $maxrowheight, $fullpage, $remainingpage, $maxfirstrowheight) = $this->mpdf->_tableHeight($this->mpdf->table[$lvl][$nid]);
						}
					}

					// RESIZING ALGORITHM

					if ($maxrowheight > $fullpage) {
						$recalculate = $this->mpdf->tbsqrt($maxrowheight / $fullpage, $iteration);
						$iteration++;
					} else if ($this->mpdf->table_rotate && $tableheight > $remainingpage && !$added_page) {
						// If can fit on remainder of page whilst respecting autosize value..
						if (($this->mpdf->shrin_k * $this->mpdf->tbsqrt($tableheight / $remainingpage, $iteration)) <= $this->mpdf->shrink_this_table_to_fit) {
							$recalculate = $this->mpdf->tbsqrt($tableheight / $remainingpage, $iteration);
							$iteration++;
						} else {
							if (!$added_page) {
								$this->mpdf->AddPage($this->mpdf->CurOrientation);
								$added_page = true;
								$this->mpdf->kwt_moved = true;
								$this->mpdf->tbrot_maxw = $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height;
							}
							// 0.001 to force it to recalculate
							$recalculate = (1 / $this->mpdf->shrin_k) + 0.001;  // undo any shrink
						}
					} else if ($this->mpdf->table_keep_together || ($this->mpdf->table[1][1]['nr'] == 1 && !$this->mpdf->writingHTMLfooter)) {
						if ($tableheight > $fullpage) {
							if (($this->mpdf->shrin_k * $this->mpdf->tbsqrt($tableheight / $fullpage, $iteration)) <= $this->mpdf->shrink_this_table_to_fit) {
								$recalculate = $this->mpdf->tbsqrt($tableheight / $fullpage, $iteration);
								$iteration++;
							} else if ($this->mpdf->tableMinSizePriority) {
								$this->mpdf->table_keep_together = false;
								$recalculate = (1 / $this->mpdf->shrin_k) + 0.001;
							} else {
								if (!$added_page && $this->mpdf->y != $this->mpdf->tMargin) {
									$this->mpdf->AddPage($this->mpdf->CurOrientation);
									$added_page = true;
									$this->mpdf->kwt_moved = true;
									$this->mpdf->tbrot_maxw = $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height;
								}
								$recalculate = $this->mpdf->tbsqrt($tableheight / $fullpage, $iteration);
								$iteration++;
							}
						} else if ($tableheight > $remainingpage) {
							// If can fit on remainder of page whilst respecting autosize value..
							if (($this->mpdf->shrin_k * $this->mpdf->tbsqrt($tableheight / $remainingpage, $iteration)) <= $this->mpdf->shrink_this_table_to_fit) {
								$recalculate = $this->mpdf->tbsqrt($tableheight / $remainingpage, $iteration);
								$iteration++;
							} else {
								if (!$added_page) {
									// mPDF 6
									if ($this->mpdf->AcceptPageBreak()) {
										$this->mpdf->AddPage($this->mpdf->CurOrientation);
									} else if ($this->mpdf->ColActive && $tableheight > (($this->mpdf->h - $this->mpdf->bMargin) - $this->mpdf->y0)) {
										$this->mpdf->AddPage($this->mpdf->CurOrientation);
									}
									$added_page = true;
									$this->mpdf->kwt_moved = true;
									$this->mpdf->tbrot_maxw = $this->mpdf->h - ($this->mpdf->y + $this->mpdf->bMargin + 5) - $this->mpdf->kwt_height;
								}

								//$recalculate = $this->mpdf->tbsqrt($tableheight / $fullpage, $iteration); $iteration++;
								$recalculate = (1 / $this->mpdf->shrin_k) + 0.001;  // undo any shrink
							}
						} else {
							$recalculate = 1;
						}
					} else {
						$recalculate = 1;
					}
				}


				if ($maxfirstrowheight > $remainingpage && !$added_page && !$this->mpdf->table_rotate && !$this->mpdf->ColActive && !$this->mpdf->table_keep_together && !$this->mpdf->writingHTMLheader && !$this->mpdf->writingHTMLfooter) {
					$this->mpdf->AddPage($this->mpdf->CurOrientation);
					$this->mpdf->kwt_moved = true;
				}

				// keep-with-table: if page has advanced, print out buffer now, else done in fn. _Tablewrite()
				if ($this->mpdf->kwt_saved && $this->mpdf->kwt_moved) {
					$this->mpdf->printkwtbuffer();
					$this->mpdf->kwt_moved = false;
					$this->mpdf->kwt_saved = false;
				}

				if ($this->mpdf->progressBar) {
					$this->mpdf->UpdateProgressBar(7, 30, ' ');
				} // *PROGRESS-BAR*
				// Recursively writes all tables starting at top level
				$this->mpdf->_tableWrite($this->mpdf->table[1][1]);

				if ($this->mpdf->table_rotate && $this->mpdf->tablebuffer) {
					$this->mpdf->PageBreakTrigger = $this->mpdf->h - $this->mpdf->bMargin;
					$save_tr = $this->mpdf->table_rotate;
					$save_y = $this->mpdf->y;
					$this->mpdf->table_rotate = 0;
					$this->mpdf->y = $this->mpdf->tbrot_y0;
					$h = $this->mpdf->tbrot_w;
					$this->mpdf->DivLn($h, $this->mpdf->blklvl, true);

					$this->mpdf->table_rotate = $save_tr;
					$this->mpdf->y = $save_y;

					$this->mpdf->printtablebuffer();
				}
				$this->mpdf->table_rotate = 0;
			}


			$this->mpdf->x = $this->mpdf->lMargin + $this->mpdf->blk[$this->mpdf->blklvl]['outer_left_margin'];

			$this->mpdf->maxPosR = max($this->mpdf->maxPosR, ($this->mpdf->x + $this->mpdf->table[1][1]['w']));

			$this->mpdf->blockjustfinished = true;
			$this->mpdf->lastblockbottommargin = $this->mpdf->table[1][1]['margin']['B'];
			//Reset values

			if (isset($this->mpdf->table[1][1]['page_break_after'])) {
				$page_break_after = $this->mpdf->table[1][1]['page_break_after'];
			} else {
				$page_break_after = '';
			}

			// Keep-with-table
			$this->mpdf->kwt = false;
			$this->mpdf->kwt_y0 = 0;
			$this->mpdf->kwt_x0 = 0;
			$this->mpdf->kwt_height = 0;
			$this->mpdf->kwt_buffer = array();
			$this->mpdf->kwt_Links = array();
			$this->mpdf->kwt_Annots = array();
			$this->mpdf->kwt_moved = false;
			$this->mpdf->kwt_saved = false;

			$this->mpdf->kwt_Reference = array();
			$this->mpdf->kwt_BMoutlines = array();
			$this->mpdf->kwt_toc = array();

			$this->mpdf->shrin_k = 1;
			$this->mpdf->shrink_this_table_to_fit = 0;

			unset($this->mpdf->table);
			$this->mpdf->table = array(); //array
			$this->mpdf->tableLevel = 0;
			$this->mpdf->tbctr = array();
			$this->mpdf->innermostTableLevel = 0;
			$this->mpdf->cssmgr->tbCSSlvl = 0;
			$this->mpdf->cssmgr->tablecascadeCSS = array();

			unset($this->mpdf->cell);
			$this->mpdf->cell = array(); //array

			$this->mpdf->col = -1; //int
			$this->mpdf->row = -1; //int
			$this->mpdf->Reset();

			$this->mpdf->cellPaddingL = 0;
			$this->mpdf->cellPaddingT = 0;
			$this->mpdf->cellPaddingR = 0;
			$this->mpdf->cellPaddingB = 0;
			$this->mpdf->cMarginL = 0;
			$this->mpdf->cMarginT = 0;
			$this->mpdf->cMarginR = 0;
			$this->mpdf->cMarginB = 0;
			$this->mpdf->default_font_size = $this->mpdf->original_default_font_size;
			$this->mpdf->default_font = $this->mpdf->original_default_font;
			$this->mpdf->SetFontSize($this->mpdf->default_font_size, false);
			$this->mpdf->SetFont($this->mpdf->default_font, '', 0, false);
			$this->mpdf->SetLineHeight();
			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['InlineProperties'])) {
				$this->mpdf->restoreInlineProperties($this->mpdf->blk[$this->mpdf->blklvl]['InlineProperties']);
			}
			if ($this->mpdf->progressBar) {
				$this->mpdf->UpdateProgressBar(7, 100, ' ');
			} // *PROGRESS-BAR*

			if ($page_break_after) {
				$save_blklvl = $this->mpdf->blklvl;
				$save_blk = $this->mpdf->blk;
				$save_silp = $this->mpdf->saveInlineProperties();
				$save_ilp = $this->mpdf->InlineProperties;
				$save_bflp = $this->mpdf->InlineBDF;
				$save_bflpc = $this->mpdf->InlineBDFctr; // mPDF 6
				// mPDF 6 pagebreaktype
				$startpage = $this->mpdf->page;
				$pagebreaktype = $this->mpdf->defaultPagebreakType;
				if ($this->mpdf->ColActive) {
					$pagebreaktype = 'cloneall';
				}

				// mPDF 6 pagebreaktype
				$this->mpdf->_preForcedPagebreak($pagebreaktype);

				if ($page_break_after == 'RIGHT') {
					$this->mpdf->AddPage($this->mpdf->CurOrientation, 'NEXT-ODD', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0);
				} else if ($page_break_after == 'LEFT') {
					$this->mpdf->AddPage($this->mpdf->CurOrientation, 'NEXT-EVEN', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0);
				} else {
					$this->mpdf->AddPage($this->mpdf->CurOrientation, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0);
				}

				// mPDF 6 pagebreaktype
				$this->mpdf->_postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl);

				$this->mpdf->InlineProperties = $save_ilp;
				$this->mpdf->InlineBDF = $save_bflp;
				$this->mpdf->InlineBDFctr = $save_bflpc; // mPDF 6
				$this->mpdf->restoreInlineProperties($save_silp);
			}
		}
		/* -- END TABLES -- */
	}

}
