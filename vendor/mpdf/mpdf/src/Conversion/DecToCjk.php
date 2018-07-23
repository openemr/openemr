<?php

namespace Mpdf\Conversion;

use Mpdf\Utils\UtfString;

class DecToCjk
{

	public function convert($num)
	{
		$nstr = (string) $num;
		$rnum = '';
		$glyphs = [0x3007, 0x4E00, 0x4E8C, 0x4E09, 0x56DB, 0x4E94, 0x516D, 0x4E03, 0x516B, 0x4E5D];
		$len = strlen($nstr);
		for ($i = 0; $i < $len; $i++) {
			$rnum .= UtfString::code2utf($glyphs[(int) $nstr[$i]]);
		}
		return $rnum;
	}

}
