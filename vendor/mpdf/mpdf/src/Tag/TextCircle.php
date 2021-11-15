<?php

namespace Mpdf\Tag;

use Mpdf\Mpdf;
use Mpdf\Utils\UtfString;

class TextCircle extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
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
		$objattr['top-text'] = '';
		$objattr['bottom-text'] = '';
		$objattr['r'] = 20; // radius (default value here for safety)
		$objattr['space-width'] = 120;
		$objattr['char-width'] = 100;

		$this->mpdf->InlineProperties['TEXTCIRCLE'] = $this->mpdf->saveInlineProperties();
		$properties = $this->cssManager->MergeCSS('INLINE', 'TEXTCIRCLE', $attr);

		if (isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY']) === 'none') {
			return;
		}
		if (isset($attr['R'])) {
			$objattr['r'] = $this->sizeConverter->convert(
				$attr['R'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($attr['TOP-TEXT'])) {
			$objattr['top-text'] = UtfString::strcode2utf($attr['TOP-TEXT']);
			$objattr['top-text'] = $this->mpdf->lesser_entity_decode($objattr['top-text']);
			if ($this->mpdf->onlyCoreFonts) {
				$objattr['top-text'] = mb_convert_encoding($objattr['top-text'], $this->mpdf->mb_enc, 'UTF-8');
			}
		}
		if (isset($attr['BOTTOM-TEXT'])) {
			$objattr['bottom-text'] = UtfString::strcode2utf($attr['BOTTOM-TEXT']);
			$objattr['bottom-text'] = $this->mpdf->lesser_entity_decode($objattr['bottom-text']);
			if ($this->mpdf->onlyCoreFonts) {
				$objattr['bottom-text'] = mb_convert_encoding($objattr['bottom-text'], $this->mpdf->mb_enc, 'UTF-8');
			}
		}
		if (!empty($attr['SPACE-WIDTH'])) {
			$objattr['space-width'] = $attr['SPACE-WIDTH'];
		}
		if (!empty($attr['CHAR-WIDTH'])) {
			$objattr['char-width'] = $attr['CHAR-WIDTH'];
		}

		// VISIBILITY
		$objattr['visibility'] = 'visible';
		if (isset($properties['VISIBILITY'])) {
			$v = strtolower($properties['VISIBILITY']);
			if (($v === 'hidden' || $v === 'printonly' || $v === 'screenonly') && $this->mpdf->visibility === 'visible') {
				$objattr['visibility'] = $v;
			}
		}
		if (isset($properties['FONT-SIZE'])) {
			if (strtolower($properties['FONT-SIZE']) === 'auto') {
				if ($objattr['top-text'] && $objattr['bottom-text']) {
					$objattr['fontsize'] = -2;
				} else {
					$objattr['fontsize'] = -1;
				}
			} else {
				$mmsize = $this->sizeConverter->convert($properties['FONT-SIZE'], $this->mpdf->default_font_size / Mpdf::SCALE);
				$this->mpdf->SetFontSize($mmsize * Mpdf::SCALE, false);
				$objattr['fontsize'] = $this->mpdf->FontSizePt;
			}
		}
		if (isset($attr['DIVIDER'])) {
			$objattr['divider'] = UtfString::strcode2utf($attr['DIVIDER']);
			$objattr['divider'] = $this->mpdf->lesser_entity_decode($objattr['divider']);
			if ($this->mpdf->onlyCoreFonts) {
				$objattr['divider'] = mb_convert_encoding($objattr['divider'], $this->mpdf->mb_enc, 'UTF-8');
			}
		}

		if (isset($properties['COLOR'])) {
			$objattr['color'] = $this->colorConverter->convert($properties['COLOR'], $this->mpdf->PDFAXwarnings);
		}

		$objattr['fontstyle'] = '';
		if (isset($properties['FONT-WEIGHT'])) {
			if (strtoupper($properties['FONT-WEIGHT']) === 'BOLD') {
				$objattr['fontstyle'] .= 'B';
			}
		}
		if (isset($properties['FONT-STYLE'])) {
			if (strtoupper($properties['FONT-STYLE']) === 'ITALIC') {
				$objattr['fontstyle'] .= 'I';
			}
		}

		if (isset($properties['FONT-FAMILY'])) {
			$this->mpdf->SetFont($properties['FONT-FAMILY'], $this->mpdf->FontStyle, 0, false);
		}
		$objattr['fontfamily'] = $this->mpdf->FontFamily;

		// VSPACE and HSPACE converted to margins in MergeCSS
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

		if (isset($properties['OPACITY']) && $properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) {
			$objattr['opacity'] = $properties['OPACITY'];
		}
		if (isset($properties['BACKGROUND-COLOR']) && $properties['BACKGROUND-COLOR'] != '') {
			$objattr['bgcolor'] = $this->colorConverter->convert($properties['BACKGROUND-COLOR'], $this->mpdf->PDFAXwarnings);
		} else {
			$objattr['bgcolor'] = false;
		}
		if ($this->mpdf->HREF) {
			if (strpos($this->mpdf->HREF, '.') === false && strpos($this->mpdf->HREF, '@') !== 0) {
				$href = $this->mpdf->HREF;
				while (array_key_exists($href, $this->mpdf->internallink)) {
					$href = '#' . $href;
				}
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

		/* -- TABLES -- */
		// Output it to buffers
		if ($this->mpdf->tableLevel) {
			$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
			$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
		} else {
			/* -- END TABLES -- */
			$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
		} // *TABLES*

		if ($this->mpdf->InlineProperties['TEXTCIRCLE']) {
			$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties['TEXTCIRCLE']);
		}
		unset($this->mpdf->InlineProperties['TEXTCIRCLE']);
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
