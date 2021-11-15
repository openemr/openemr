<?php

namespace Mpdf\Barcode;

/**
 * CODE11 barcodes.
 * Used primarily for labeling telecommunications equipment
 */
class Code11 extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param float $printRatio
	 */
	public function __construct($code, $printRatio)
	{
		$this->init($code, $printRatio);

		$this->data['nom-X'] = 0.381; // Nominal value for X-dim (bar width) in mm (2 X min. spec.)
		$this->data['nom-H'] = 10;  // Nominal value for Height of Full bar in mm (non-spec.)
		$this->data['lightmL'] = 10; // LEFT light margin =  x X-dim (spec.)
		$this->data['lightmR'] = 10; // RIGHT light margin =  x X-dim (spec.)
		$this->data['lightTB'] = 0; // TOP/BOTTOM light margin =  x X-dim (non-spec.)
	}

	/**
	 * @param string $code
	 * @param float $printRatio
	 */
	private function init($code, $printRatio)
	{
		$chr = [
			'0' => '111121',
			'1' => '211121',
			'2' => '121121',
			'3' => '221111',
			'4' => '112121',
			'5' => '212111',
			'6' => '122111',
			'7' => '111221',
			'8' => '211211',
			'9' => '211111',
			'-' => '112111',
			'S' => '112211'
		];

		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => []];

		$k = 0;

		$len = strlen($code);
		// calculate check digit C

		$p = 1;
		$check = 0;

		for ($i = ($len - 1); $i >= 0; --$i) {
			$digit = $code[$i];
			if ($digit == '-') {
				$dval = 10;
			} else {
				$dval = (int) $digit;
			}
			$check += ($dval * $p);
			++$p;
			if ($p > 10) {
				$p = 1;
			}
		}

		$check %= 11;

		if ($check == 10) {
			$check = '-';
		}

		$code .= $check;
		$checkdigit = $check;

		if ($len > 10) {
			// calculate check digit K
			$p = 1;
			$check = 0;
			for ($i = $len; $i >= 0; --$i) {
				$digit = $code[$i];
				if ($digit == '-') {
					$dval = 10;
				} else {
					$dval = (int) $digit;
				}
				$check += ($dval * $p);
				++$p;
				if ($p > 9) {
					$p = 1;
				}
			}
			$check %= 11;
			$code .= $check;
			$checkdigit .= $check;
			++$len;
		}

		$code = 'S' . $code . 'S';
		$len += 3;

		for ($i = 0; $i < $len; ++$i) {

			if (!isset($chr[$code[$i]])) {
				throw new \Mpdf\Barcode\BarcodeException(sprintf('Invalid character "%s" in CODE11 barcode value "%s"', $code[$i], $code));
			}

			$seq = $chr[$code[$i]];

			for ($j = 0; $j < 6; ++$j) {

				$t = $j % 2 === 0;
				$x = $seq[$j];
				$w = ($x == 2) ? $printRatio : 1;

				$bararray['bcode'][$k] = ['t' => $t, 'w' => $w, 'h' => 1, 'p' => 0];
				$bararray['maxw'] += $w;

				++$k;
			}
		}

		$bararray['checkdigit'] = $checkdigit;

		$this->data = $bararray;
	}

	/**
	 * @inheritdoc
	 */
	public function getType()
	{
		return 'CODE11';
	}

}
