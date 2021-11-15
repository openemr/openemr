<?php

namespace Mpdf\Tag;

class BarCode extends Tag
{

	/**
	 * @var \Mpdf\Barcode
	 */
	protected $barcode;

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->ignorefollowingspaces = false;
		if (!empty($attr['CODE'])) {
			$objattr = [];
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
				$objattr['btype'] = strtoupper(trim($attr['TYPE']));
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
			$properties = $this->cssManager->MergeCSS('', 'BARCODE', $attr);
			if (isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY']) === 'none') {
				return;
			}
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
			if (isset($properties['MARGIN-LEFT'])) {
				$objattr['margin_left'] = $this->sizeConverter->convert(
					$properties['MARGIN-LEFT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			if (isset($properties['MARGIN-RIGHT'])) {
				$objattr['margin_right'] = $this->sizeConverter->convert(
					$properties['MARGIN-RIGHT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}

			if (isset($properties['PADDING-TOP'])) {
				$objattr['padding_top'] = $this->sizeConverter->convert(
					$properties['PADDING-TOP'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			if (isset($properties['PADDING-BOTTOM'])) {
				$objattr['padding_bottom'] = $this->sizeConverter->convert(
					$properties['PADDING-BOTTOM'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			if (isset($properties['PADDING-LEFT'])) {
				$objattr['padding_left'] = $this->sizeConverter->convert(
					$properties['PADDING-LEFT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			if (isset($properties['PADDING-RIGHT'])) {
				$objattr['padding_right'] = $this->sizeConverter->convert(
					$properties['PADDING-RIGHT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
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
				$objattr['vertical-align'] = $this->getAlign($properties['VERTICAL-ALIGN']);
			}
			if (isset($properties['COLOR']) && $properties['COLOR'] != '') {
				$objattr['color'] = $this->colorConverter->convert($properties['COLOR'], $this->mpdf->PDFAXwarnings);
			} else {
				$objattr['color'] = false;
			}
			if (isset($properties['BACKGROUND-COLOR']) && $properties['BACKGROUND-COLOR'] != '') {
				$objattr['bgcolor'] = $this->colorConverter->convert($properties['BACKGROUND-COLOR'], $this->mpdf->PDFAXwarnings);
			} else {
				$objattr['bgcolor'] = false;
			}

			$this->barcode = new \Mpdf\Barcode();

			if (in_array($objattr['btype'], ['EAN13', 'ISBN', 'ISSN', 'UPCA', 'UPCE', 'EAN8'])) {

				$code = preg_replace('/\-/', '', $objattr['code']);
				$arrcode = $this->barcode->getBarcodeArray($code, $objattr['btype']);

				if ($objattr['bsupp'] == 2 || $objattr['bsupp'] == 5) { // EAN-2 or -5 Supplement
					$supparrcode = $this->barcode->getBarcodeArray($objattr['bsupp_code'], 'EAN' . $objattr['bsupp']);
					$w = ($arrcode['maxw'] + $arrcode['lightmL'] + $arrcode['lightmR']
							+ $supparrcode['maxw'] + $supparrcode['sepM']) * $arrcode['nom-X'] * $objattr['bsize'];
				} else {
					$w = ($arrcode['maxw'] + $arrcode['lightmL'] + $arrcode['lightmR']) * $arrcode['nom-X'] * $objattr['bsize'];
				}

				$h = $arrcode['nom-H'] * $objattr['bsize'] * $objattr['bheight'];
				// Add height for ISBN string + margin from top of bars
				if (($objattr['showtext'] && $objattr['btype'] === 'EAN13') || $objattr['btype'] === 'ISBN' || $objattr['btype'] === 'ISSN') {
					$tisbnm = 1.5 * $objattr['bsize']; // Top margin between TOP TEXT (isbn - if shown) & bars
					$isbn_fontsize = 2.1 * $objattr['bsize'];
					$h += $isbn_fontsize + $tisbnm;
				}

			} elseif ($objattr['btype'] === 'QR') { // QR-code
				$w = $h = $objattr['bsize'] * 25; // Factor of 25mm (default)
				$objattr['errorlevel'] = 'L';
				if (isset($attr['ERROR'])) {
					$objattr['errorlevel'] = $attr['ERROR'];
				}
				$objattr['disableborder'] = false;
				if (isset($attr['DISABLEBORDER'])) {
					$objattr['disableborder'] = (bool) $attr['DISABLEBORDER'];
				}

			} elseif (in_array($objattr['btype'], ['IMB', 'RM4SCC', 'KIX', 'POSTNET', 'PLANET'])) {

				$arrcode = $this->barcode->getBarcodeArray($objattr['code'], $objattr['btype']);

				$w = ($arrcode['maxw'] * $arrcode['nom-X'] * $objattr['bsize']) + $arrcode['quietL'] + $arrcode['quietR'];
				$h = ($arrcode['nom-H'] * $objattr['bsize']) + (2 * $arrcode['quietTB']);

			} elseif (in_array($objattr['btype'], ['C128A', 'C128B', 'C128C', 'C128RAW', 'EAN128A', 'EAN128B', 'EAN128C',
				'C39', 'C39+', 'C39E', 'C39E+', 'S25', 'S25+', 'I25', 'I25+', 'I25B',
				'I25B+', 'C93', 'MSI', 'MSI+', 'CODABAR', 'CODE11'])) {

				$arrcode = $this->barcode->getBarcodeArray($objattr['code'], $objattr['btype'], $objattr['pr_ratio']);
				$w = ($arrcode['maxw'] + $arrcode['lightmL'] + $arrcode['lightmR']) * $arrcode['nom-X'] * $objattr['bsize'];
				$h = ((2 * $arrcode['lightTB'] * $arrcode['nom-X']) + $arrcode['nom-H']) * $objattr['bsize'] * $objattr['bheight'];

			} else {
				return;
			}

			$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top']
				+ $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
			$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left']
				+ $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

			$objattr['type'] = 'barcode';
			$objattr['height'] = $h + $extraheight;
			$objattr['width'] = $w + $extrawidth;
			$objattr['barcode_height'] = $h;
			$objattr['barcode_width'] = $w;

			/* -- CSS-IMAGE-FLOAT -- */
			if (!$this->mpdf->ColActive && !$this->mpdf->tableLevel && !$this->mpdf->listlvl && !$this->mpdf->kwt) {
				if (isset($properties['FLOAT']) && (strtoupper($properties['FLOAT']) === 'RIGHT' || strtoupper($properties['FLOAT']) === 'LEFT')) {
					$objattr['float'] = strtoupper(substr($properties['FLOAT'], 0, 1));
				}
			}
			/* -- END CSS-IMAGE-FLOAT -- */

			$e = "\xbb\xa4\xactype=barcode,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

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
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
