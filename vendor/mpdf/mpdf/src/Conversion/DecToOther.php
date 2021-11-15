<?php

namespace Mpdf\Conversion;

use Mpdf\Mpdf;
use Mpdf\Utils\UtfString;

class DecToOther
{

	/**
	 * @var \Mpdf\Mpdf
	 */
	private $mpdf;

	public function __construct(Mpdf $mpdf)
	{
		$this->mpdf = $mpdf;
	}

	public function convert($num, $cp, $check = true)
	{
		// From printlistbuffer: font is set, so check if character is available
		// From docPageNum: font is not set, so no check
		$nstr = (string) $num;
		$rnum = '';
		$len = strlen($nstr);

		for ($i = 0; $i < $len; $i++) {
			if (!$check || $this->mpdf->_charDefined($this->mpdf->CurrentFont['cw'], $cp + ((int) $nstr[$i]))) {
				$rnum .= UtfString::code2utf($cp + (int) $nstr[$i]);
			} else {
				$rnum .= $nstr[$i];
			}
		}

		return $rnum;
	}

	/**
	 * @param string $script
	 * @return int
	 */
	public function getCodePage($script)
	{
		$codePages = [
			'arabic-indic' => 0x0660,
			'persian' => 0x06F0,
			'urdu' => 0x06F0,
			'bengali' => 0x09E6,
			'devanagari' => 0x0966,
			'gujarati' => 0x0AE6,
			'gurmukhi' => 0x0A66,
			'kannada' => 0x0CE6,
			'malayalam' => 0x0D66,
			'oriya' => 0x0B66,
			'telugu' => 0x0C66,
			'tamil' => 0x0BE6,
			'thai' => 0x0E50,
			'khmer' => 0x17E0,
			'cambodian' => 0x17E0,
			'lao' => 0x0ED0,
			'myanmar' => 0x1040
		];

		return isset($codePages[$script]) ? $codePages[$script] : 0;
	}

}
