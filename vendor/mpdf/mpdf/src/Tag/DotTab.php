<?php

namespace Mpdf\Tag;

class DotTab extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$objattr = [];
		$objattr['type'] = 'dottab';
		$dots = str_repeat('.', 3) . '  '; // minimum number of dots
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

		$properties = $this->cssManager->MergeCSS('INLINE', 'DOTTAB', $attr);
		if (isset($properties['OUTDENT'])) {
			$objattr['outdent'] = $this->sizeConverter->convert(
				$properties['OUTDENT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		} elseif (isset($attr['OUTDENT'])) {
			$objattr['outdent'] = $this->sizeConverter->convert(
				$attr['OUTDENT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
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
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
