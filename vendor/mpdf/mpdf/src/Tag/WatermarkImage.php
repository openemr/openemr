<?php

namespace Mpdf\Tag;

class WatermarkImage extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$src = '';
		if (isset($attr['SRC'])) {
			$src = $attr['SRC'];
		}

		$alpha = -1;
		if (isset($attr['ALPHA']) && $attr['ALPHA'] > 0) {
			$alpha = $attr['ALPHA'];
		}

		$size = 'D';
		if (!empty($attr['SIZE'])) {
			$size = $attr['SIZE'];
			if (strpos($size, ',')) {
				$size = explode(',', $size);
			}
		}

		$pos = 'P';
		if (!empty($attr['POSITION'])) {  // mPDF 5.7.2
			$pos = $attr['POSITION'];
			if (strpos($pos, ',')) {
				$pos = explode(',', $pos);
			}
		}
		$this->mpdf->SetWatermarkImage($src, $alpha, $size, $pos);
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
