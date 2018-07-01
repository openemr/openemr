<?php

namespace Mpdf\Tag;

use Mpdf\Mpdf;

class Select extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->lastoptionaltag = ''; // Save current HTML specified optional endtag
		$this->mpdf->InlineProperties['SELECT'] = $this->mpdf->saveInlineProperties();
		$properties = $this->cssManager->MergeCSS('', 'SELECT', $attr);
		if (isset($properties['FONT-FAMILY'])) {
			$this->mpdf->SetFont($properties['FONT-FAMILY'], $this->mpdf->FontStyle, 0, false);
		}
		if (isset($properties['FONT-SIZE'])) {
			$mmsize = $this->sizeConverter->convert($properties['FONT-SIZE'], $this->mpdf->default_font_size / Mpdf::SCALE);
			$this->mpdf->SetFontSize($mmsize * Mpdf::SCALE, false);
		}
		if (isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK']) === 'true') {
			$this->mpdf->selectoption['SPELLCHECK'] = true;
		}

		if (isset($properties['COLOR'])) {
			$this->mpdf->selectoption['COLOR'] = $this->colorConverter->convert($properties['COLOR'], $this->mpdf->PDFAXwarnings);
		}
		$this->mpdf->specialcontent = 'type=select';
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
	}

	public function close(&$ahtml, &$ihtml)
	{
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
		$rows = 1;
		if (isset($objattr['size']) && $objattr['size'] > 1) {
			$rows = $objattr['size'];
		}

		$objattr['fontfamily'] = $this->mpdf->FontFamily;
		$objattr['fontsize'] = $this->mpdf->FontSizePt;

		$objattr['width'] = $w + ($this->form->form_element_spacing['select']['outer']['h'] * 2)
			+ ($this->form->form_element_spacing['select']['inner']['h'] * 2) + ($this->mpdf->FontSize * 1.4);

		$objattr['height'] = ($this->mpdf->FontSize * $rows) + ($this->form->form_element_spacing['select']['outer']['v'] * 2)
			+ ($this->form->form_element_spacing['select']['inner']['v'] * 2);

		$e = "\xbb\xa4\xactype=select,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

		// Output it to buffers
		if ($this->mpdf->tableLevel) { // *TABLES*
			$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
			$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width']; // *TABLES*
		} // *TABLES*
		else { // *TABLES*
			$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
		} // *TABLES*

		$this->mpdf->selectoption = [];
		$this->mpdf->specialcontent = '';

		if ($this->mpdf->InlineProperties['SELECT']) {
			$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties['SELECT']);
		}
		unset($this->mpdf->InlineProperties['SELECT']);
	}
}
