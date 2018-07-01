<?php

namespace Mpdf\Tag;

use Mpdf\Utils\NumericString;

class Hr extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{

		// Added mPDF 3.0 Float DIV - CLEAR
		if (isset($attr['STYLE'])) {
			$properties = $this->cssManager->readInlineCSS($attr['STYLE']);
			if (isset($properties['CLEAR'])) {
				$this->mpdf->ClearFloats(strtoupper($properties['CLEAR']), $this->mpdf->blklvl);
			} // *CSS-FLOAT*
		}

		$this->mpdf->ignorefollowingspaces = true;

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
		$properties = $this->cssManager->MergeCSS('', 'HR', $attr);
		if (isset($properties['MARGIN-TOP'])) {
			$objattr['margin_top'] = $this->sizeConverter->convert(
				$properties['MARGIN-TOP'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['MARGIN-BOTTOM'])) {
			$objattr['margin_bottom'] = $this->sizeConverter->convert(
				$properties['MARGIN-BOTTOM'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['WIDTH'])) {
			$objattr['width'] = $this->sizeConverter->convert($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
		} elseif (isset($attr['WIDTH']) && $attr['WIDTH'] != '') {
			$objattr['width'] = $this->sizeConverter->convert($attr['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
		}
		if (isset($properties['TEXT-ALIGN'])) {
			$objattr['align'] = self::ALIGN[strtolower($properties['TEXT-ALIGN'])];
		} elseif (isset($attr['ALIGN']) && $attr['ALIGN'] != '') {
			$objattr['align'] = self::ALIGN[strtolower($attr['ALIGN'])];
		}

		if (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT']) === 'auto') {
			$objattr['align'] = 'R';
		}
		if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT']) === 'auto') {
			$objattr['align'] = 'L';
			if (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT']) === 'auto'
				&& isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT']) === 'auto') {
				$objattr['align'] = 'C';
			}
		}
		if (isset($properties['COLOR'])) {
			$objattr['color'] = $this->colorConverter->convert($properties['COLOR'], $this->mpdf->PDFAXwarnings);
		} elseif (isset($attr['COLOR']) && $attr['COLOR'] != '') {
			$objattr['color'] = $this->colorConverter->convert($attr['COLOR'], $this->mpdf->PDFAXwarnings);
		}
		if (isset($properties['HEIGHT'])) {
			$objattr['linewidth'] = $this->sizeConverter->convert(
				$properties['HEIGHT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}


		/* -- TABLES -- */
		if ($this->mpdf->tableLevel) {
			$objattr['W-PERCENT'] = 100;
			if (isset($properties['WIDTH']) && NumericString::containsPercentChar($properties['WIDTH'])) {
				$properties['WIDTH'] = NumericString::removePercentChar($properties['WIDTH']); // make "90%" become simply "90"
				$objattr['W-PERCENT'] = $properties['WIDTH'];
			}
			if (isset($attr['WIDTH']) && NumericString::containsPercentChar($attr['WIDTH'])) {
				$attr['WIDTH'] = NumericString::removePercentChar($attr['WIDTH']); // make "90%" become simply "90"
				$objattr['W-PERCENT'] = $attr['WIDTH'];
			}
		}
		/* -- END TABLES -- */

		$objattr['type'] = 'hr';
		$objattr['height'] = $objattr['linewidth'] + $objattr['margin_top'] + $objattr['margin_bottom'];
		$e = "\xbb\xa4\xactype=image,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

		/* -- TABLES -- */
		// Output it to buffers
		if ($this->mpdf->tableLevel) {
			if ($this->mpdf->cell) {
				if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
				} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
				}
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0; // reset
				$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
			}
		} else {
			/* -- END TABLES -- */
			$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
		} // *TABLES*
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
