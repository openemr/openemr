<?php

namespace Mpdf\Tag;

class TBody extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->tablethead = 0;
		$this->mpdf->tabletfoot = 0;
		$this->mpdf->lastoptionaltag = 'TBODY'; // Save current HTML specified optional endtag
		$this->cssManager->tbCSSlvl++;
		$this->cssManager->MergeCSS('TABLE', 'TBODY', $attr);
	}

	public function close(&$ahtml, &$ihtml)
	{
		$this->mpdf->lastoptionaltag = '';
		unset($this->cssManager->tablecascadeCSS[$this->cssManager->tbCSSlvl]);
		$this->cssManager->tbCSSlvl--;
	}
}
