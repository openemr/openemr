<?php

namespace Mpdf\Tag;

use Mpdf\Utils\UtfString;

class Option extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
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
			$attr['VALUE'] = UtfString::strcode2utf($attr['VALUE']);
			$attr['VALUE'] = $this->mpdf->lesser_entity_decode($attr['VALUE']);
			if ($this->mpdf->onlyCoreFonts) {
				$attr['VALUE'] = mb_convert_encoding($attr['VALUE'], $this->mpdf->mb_enc, 'UTF-8');
			}
		}
		$this->mpdf->selectoption['currentVAL'] = $attr['VALUE'];
	}

	public function close(&$ahtml, &$ihtml)
	{
		$this->mpdf->selectoption['ACTIVE'] = false;
		$this->mpdf->lastoptionaltag = '';
	}
}
