<?php

namespace Mpdf\Conversion;

class DecToAlpha
{

	public function convert($valor, $toUpper = true)
	{
		// returns a string from A-Z to AA-ZZ to AAA-ZZZ
		// OBS: A = 65 ASCII TABLE VALUE
		if (($valor < 1) || ($valor > 18278)) {
			return '?'; //supports 'only' up to 18278
		}

		$c2 = $c3 = '';

		$c1 = 64 + $valor; // 1 letter (up to 26)
		if ($valor > 702) { // 3 letters (up to 18278)
			$c1 = 65 + floor(($valor - 703) / 676);
			$c2 = 65 + floor((($valor - 703) % 676) / 26);
			$c3 = 65 + floor((($valor - 703) % 676) % 26);
		} elseif ($valor > 26) { // 2 letters (up to 702)
			$c1 = (64 + (int) (($valor - 1) / 26));
			$c2 = (64 + ($valor % 26));
			if ($c2 === 64) {
				$c2 += 26;
			}
		}

		$alpha = chr($c1);

		if ($c2 !== '') {
			$alpha .= chr($c2);
		}

		if ($c3 !== '') {
			$alpha .= chr($c3);
		}

		if (!$toUpper) {
			$alpha = strtolower($alpha);
		}

		return $alpha;
	}

}
