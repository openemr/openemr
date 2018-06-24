<?php

namespace Mpdf\Tag;

class Tta extends SubstituteTag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->tta = true;
		$this->mpdf->InlineProperties['TTA'] = $this->mpdf->saveInlineProperties();

		if (in_array($this->mpdf->FontFamily, $this->mpdf->mono_fonts)) {
			$this->mpdf->setCSS(['FONT-FAMILY' => 'ccourier'], 'INLINE');
		} elseif (in_array($this->mpdf->FontFamily, $this->mpdf->serif_fonts)) {
			$this->mpdf->setCSS(['FONT-FAMILY' => 'ctimes'], 'INLINE');
		} else {
			$this->mpdf->setCSS(['FONT-FAMILY' => 'chelvetica'], 'INLINE');
		}
	}

}
