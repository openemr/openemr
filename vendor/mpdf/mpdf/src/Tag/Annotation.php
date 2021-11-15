<?php

namespace Mpdf\Tag;

class Annotation extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		//if (isset($attr['CONTENT']) && !$this->mpdf->writingHTMLheader && !$this->mpdf->writingHTMLfooter) {	// Stops annotations in FixedPos
		if (isset($attr['CONTENT'])) {
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
			$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES);
			$objattr['type'] = 'annot';
			$objattr['POPUP'] = '';
		} else {
			return;
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
		} elseif (isset($attr['TITLE'])) {
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
		} elseif ($this->mpdf->annotMargin) {
			$objattr['OPACITY'] = 1;
		} else {
			$objattr['OPACITY'] = $this->mpdf->annotOpacity;
		}
		if (isset($attr['COLOR'])) {
			$cor = $this->colorConverter->convert($attr['COLOR'], $this->mpdf->PDFAXwarnings);
			if ($cor) {
				$objattr['COLOR'] = $cor;
			} else {
				$objattr['COLOR'] = $this->colorConverter->convert('yellow', $this->mpdf->PDFAXwarnings);
			}
		} else {
			$objattr['COLOR'] = $this->colorConverter->convert('yellow', $this->mpdf->PDFAXwarnings);
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
			$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = [$e];
		} // *TABLES*
		else { // *TABLES*
			$this->mpdf->textbuffer[] = [$e];
		} // *TABLES*
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
