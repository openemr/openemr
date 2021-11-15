<?php

namespace Mpdf\Tag;

use Mpdf\Mpdf;

class TextArea extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
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
		if (isset($attr['DISABLED'])) {
			$objattr['disabled'] = true;
		}
		if (isset($attr['READONLY'])) {
			$objattr['readonly'] = true;
		}
		if (isset($attr['REQUIRED'])) {
			$objattr['required'] = true;
		}
		if (isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK']) === 'true') {
			$objattr['spellcheck'] = true;
		}
		if (isset($attr['TITLE'])) {
			$objattr['title'] = $attr['TITLE'];
			if ($this->mpdf->onlyCoreFonts) {
				$objattr['title'] = mb_convert_encoding($objattr['title'], $this->mpdf->mb_enc, 'UTF-8');
			}
		}
		if ($this->mpdf->useActiveForms) {
			if (isset($attr['NAME'])) {
				$objattr['fieldname'] = $attr['NAME'];
			}
			$this->form->form_element_spacing['textarea']['outer']['v'] = 0;
			$this->form->form_element_spacing['textarea']['inner']['v'] = 0;
			if (isset($attr['ONCALCULATE'])) {
				$objattr['onCalculate'] = $attr['ONCALCULATE'];
			} elseif (isset($attr['ONCHANGE'])) {
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
		$this->mpdf->InlineProperties['TEXTAREA'] = $this->mpdf->saveInlineProperties();
		$properties = $this->cssManager->MergeCSS('', 'TEXTAREA', $attr);
		if (isset($properties['FONT-FAMILY'])) {
			$this->mpdf->SetFont($properties['FONT-FAMILY'], '', 0, false);
		}
		if (isset($properties['FONT-SIZE'])) {
			$mmsize = $this->sizeConverter->convert($properties['FONT-SIZE'], $this->mpdf->default_font_size / Mpdf::SCALE);
			$this->mpdf->SetFontSize($mmsize * Mpdf::SCALE, false);
		}
		if (isset($properties['COLOR'])) {
			$objattr['color'] = $this->colorConverter->convert($properties['COLOR'], $this->mpdf->PDFAXwarnings);
		}
		$objattr['fontfamily'] = $this->mpdf->FontFamily;
		$objattr['fontsize'] = $this->mpdf->FontSizePt;
		if ($this->mpdf->useActiveForms) {
			if (isset($properties['TEXT-ALIGN'])) {
				$objattr['text_align'] = $this->getAlign($properties['TEXT-ALIGN']);
			} elseif (isset($attr['ALIGN'])) {
				$objattr['text_align'] = $this->getAlign($attr['ALIGN']);
			}
			if (isset($properties['OVERFLOW']) && strtolower($properties['OVERFLOW']) === 'hidden') {
				$objattr['donotscroll'] = true;
			}
			if (isset($properties['BORDER-TOP-COLOR'])) {
				$objattr['border-col'] = $this->colorConverter->convert($properties['BORDER-TOP-COLOR'], $this->mpdf->PDFAXwarnings);
			}
			if (isset($properties['BACKGROUND-COLOR'])) {
				$objattr['background-col'] = $this->colorConverter->convert($properties['BACKGROUND-COLOR'], $this->mpdf->PDFAXwarnings);
			}
		}
		$this->mpdf->SetLineHeight('', $this->form->textarea_lineheight);

		$w = 0;
		$h = 0;
		if (isset($properties['WIDTH'])) {
			$w = $this->sizeConverter->convert(
				$properties['WIDTH'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['HEIGHT'])) {
			$h = $this->sizeConverter->convert(
				$properties['HEIGHT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['VERTICAL-ALIGN'])) {
			$objattr['vertical-align'] = $this->getAlign($properties['VERTICAL-ALIGN']);
		}

		$colsize = 20; //HTML default value
		$rowsize = 2; //HTML default value
		if (isset($attr['COLS'])) {
			$colsize = (int) $attr['COLS'];
		}
		if (isset($attr['ROWS'])) {
			$rowsize = (int) $attr['ROWS'];
		}

		$charsize = $this->mpdf->GetCharWidth('w', false);
		if ($w) {
			$colsize = round(($w - ($this->form->form_element_spacing['textarea']['outer']['h'] * 2)
					- ($this->form->form_element_spacing['textarea']['inner']['h'] * 2)) / $charsize);
		}
		if ($h) {
			$rowsize = round(($h - ($this->form->form_element_spacing['textarea']['outer']['v'] * 2)
					- ($this->form->form_element_spacing['textarea']['inner']['v'] * 2)) / $this->mpdf->lineheight);
		}

		$objattr['type'] = 'textarea';
		$objattr['width'] = ($colsize * $charsize) + ($this->form->form_element_spacing['textarea']['outer']['h'] * 2)
			+ ($this->form->form_element_spacing['textarea']['inner']['h'] * 2);

		$objattr['height'] = ($rowsize * $this->mpdf->lineheight)
			+ ($this->form->form_element_spacing['textarea']['outer']['v'] * 2)
			+ ($this->form->form_element_spacing['textarea']['inner']['v'] * 2);

		$objattr['rows'] = $rowsize;
		$objattr['cols'] = $colsize;

		$this->mpdf->specialcontent = serialize($objattr);

		if ($this->mpdf->tableLevel) { // *TABLES*
			$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width']; // *TABLES*
		} // *TABLES*
	}

	public function close(&$ahtml, &$ihtml)
	{
		$this->mpdf->ignorefollowingspaces = false;
		$this->mpdf->specialcontent = '';
		if ($this->mpdf->InlineProperties['TEXTAREA']) {
			$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties['TEXTAREA']);
		}
		unset($this->mpdf->InlineProperties['TEXTAREA']);
	}
}
