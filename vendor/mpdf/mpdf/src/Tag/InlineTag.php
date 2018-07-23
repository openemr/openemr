<?php

namespace Mpdf\Tag;

use Mpdf\Utils\UtfString;

abstract class InlineTag extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();

		/* -- ANNOTATIONS -- */
		if ($this->mpdf->title2annots && isset($attr['TITLE'])) {
			$objattr = [];
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
			$objattr['COLOR'] = $this->colorConverter->convert('yellow', $this->mpdf->PDFAXwarnings);
			$annot = "\xbb\xa4\xactype=annot,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
		}
		/* -- END ANNOTATIONS -- */

		// mPDF 5.7.3 Inline tags
		if (!isset($this->mpdf->InlineProperties[$tag])) {
			$this->mpdf->InlineProperties[$tag] = [$this->mpdf->saveInlineProperties()];
		} else {
			$this->mpdf->InlineProperties[$tag][] = $this->mpdf->saveInlineProperties();
		}
		if (isset($annot)) {  // *ANNOTATIONS*
			if (!isset($this->mpdf->InlineAnnots[$tag])) {
				$this->mpdf->InlineAnnots[$tag] = [];
			} // *ANNOTATIONS*
			$this->mpdf->InlineAnnots[$tag][] = $annot;
		} // *ANNOTATIONS*

		$properties = $this->cssManager->MergeCSS('INLINE', $tag, $attr);
		if (!empty($properties)) {
			$this->mpdf->setCSS($properties, 'INLINE');
		}

		// mPDF 6 Bidirectional formatting for inline elements
		$bdf = false;
		$bdf2 = '';
		$popd = '';

		// Get current direction
		$currdir = 'ltr';
		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['direction'])) {
			$currdir = $this->mpdf->blk[$this->mpdf->blklvl]['direction'];
		}
		if ($this->mpdf->tableLevel
			&& isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['direction'])
			&& $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['direction'] === 'rtl') {
			$currdir = 'rtl';
		}
		if (isset($attr['DIR']) && $attr['DIR'] != '') {
			$currdir = strtolower($attr['DIR']);
		}
		if (isset($properties['DIRECTION'])) {
			$currdir = strtolower($properties['DIRECTION']);
		}

		// mPDF 6 bidi
		// cf. http://www.w3.org/TR/css3-writing-modes/#unicode-bidi
		if ($tag === 'BDO') {
			if (isset($attr['DIR']) && strtolower($attr['DIR']) === 'rtl') {
				$bdf = 0x202E;
				$popd = 'RLOPDF';
			} // U+202E RLO
			elseif (isset($attr['DIR']) && strtolower($attr['DIR']) === 'ltr') {
				$bdf = 0x202D;
				$popd = 'LROPDF';
			} // U+202D LRO
		} elseif ($tag === 'BDI') {
			if (isset($attr['DIR']) && strtolower($attr['DIR']) === 'rtl') {
				$bdf = 0x2067;
				$popd = 'RLIPDI';
			} // U+2067 RLI
			elseif (isset($attr['DIR']) && strtolower($attr['DIR']) === 'ltr') {
				$bdf = 0x2066;
				$popd = 'LRIPDI';
			} // U+2066 LRI
			else {
				$bdf = 0x2068;
				$popd = 'FSIPDI';
			} // U+2068 FSI
		} elseif (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) === 'bidi-override') {
			if ($currdir === 'rtl') {
				$bdf = 0x202E;
				$popd = 'RLOPDF';
			} // U+202E RLO
			else {
				$bdf = 0x202D;
				$popd = 'LROPDF';
			} // U+202D LRO
		} elseif (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) === 'embed') {
			if ($currdir === 'rtl') {
				$bdf = 0x202B;
				$popd = 'RLEPDF';
			} // U+202B RLE
			else {
				$bdf = 0x202A;
				$popd = 'LREPDF';
			} // U+202A LRE
		} elseif (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) === 'isolate') {
			if ($currdir === 'rtl') {
				$bdf = 0x2067;
				$popd = 'RLIPDI';
			} // U+2067 RLI
			else {
				$bdf = 0x2066;
				$popd = 'LRIPDI';
			} // U+2066 LRI
		} elseif (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) === 'isolate-override') {
			if ($currdir === 'rtl') {
				$bdf = 0x2067;
				$bdf2 = 0x202E;
				$popd = 'RLIRLOPDFPDI';
			} // U+2067 RLI // U+202E RLO
			else {
				$bdf = 0x2066;
				$bdf2 = 0x202D;
				$popd = 'LRILROPDFPDI';
			} // U+2066 LRI  // U+202D LRO
		} elseif (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) === 'plaintext') {
			$bdf = 0x2068;
			$popd = 'FSIPDI'; // U+2068 FSI
		} else {
			if (isset($attr['DIR']) && strtolower($attr['DIR']) === 'rtl') {
				$bdf = 0x202B;
				$popd = 'RLEPDF';
			} // U+202B RLE
			elseif (isset($attr['DIR']) && strtolower($attr['DIR']) === 'ltr') {
				$bdf = 0x202A;
				$popd = 'LREPDF';
			} // U+202A LRE
		}

		if ($bdf) {
			// mPDF 5.7.3 Inline tags
			if (!isset($this->mpdf->InlineBDF[$tag])) {
				$this->mpdf->InlineBDF[$tag] = [[$popd, $this->mpdf->InlineBDFctr]];
			} else {
				$this->mpdf->InlineBDF[$tag][] = [$popd, $this->mpdf->InlineBDFctr];
			}
			$this->mpdf->InlineBDFctr++;
			if ($bdf2) {
				$bdf2 = UtfString::code2utf($bdf);
			}
			$this->mpdf->OTLdata = [];
			if ($this->mpdf->tableLevel) {
				$this->mpdf->_saveCellTextBuffer(UtfString::code2utf($bdf) . $bdf2);
			} else {
				$this->mpdf->_saveTextBuffer(UtfString::code2utf($bdf) . $bdf2);
			}
			$this->mpdf->biDirectional = true;
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();

		$annot = false; // mPDF 6
		$bdf = false; // mPDF 6

		// mPDF 5.7.3 Inline tags
		if ($tag === 'PROGRESS' || $tag === 'METER') {
			if (!empty($this->mpdf->InlineProperties[$tag])) {
				$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
			}
			unset($this->mpdf->InlineProperties[$tag]);
			if (!empty($this->mpdf->InlineAnnots[$tag])) {
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
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = [$annot]; // *TABLES*
			} // *TABLES*
			else { // *TABLES*
				$this->mpdf->textbuffer[] = [$annot];
			} // *TABLES*
		}
		/* -- END ANNOTATIONS -- */

		// mPDF 6 bidi
		// mPDF 6 Bidirectional formatting for inline elements
		if ($bdf) {
			$popf = $this->mpdf->_setBidiCodes('end', $bdf);
			$this->mpdf->OTLdata = [];
			if ($this->mpdf->tableLevel) {
				$this->mpdf->_saveCellTextBuffer($popf);
			} else {
				$this->mpdf->_saveTextBuffer($popf);
			}
		}
	}
}
