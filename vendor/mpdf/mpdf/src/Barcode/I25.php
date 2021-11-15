<?php

namespace Mpdf\Barcode;

/**
 * Interleaved 2 of 5 barcodes.
 * Compact numeric code, widely used in industry, air cargo
 * Contains digits (0 to 9) and encodes the data in the width of both bars and spaces.
 */
class I25 extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param float $topBottomMargin
	 * @param float $printRatio
	 * @param bool $checksum
	 */
	public function __construct($code, $topBottomMargin, $printRatio, $checksum = false)
	{
		$this->init($code, $printRatio, $checksum);

		$this->data['nom-X'] = 0.381; // Nominal value for X-dim (bar width) in mm (2 X min. spec.)
		$this->data['nom-H'] = 10;  // Nominal value for Height of Full bar in mm (non-spec.)
		$this->data['lightmL'] = 10; // LEFT light margin =  x X-dim (spec.)
		$this->data['lightmR'] = 10; // RIGHT light margin =  x X-dim (spec.)
		$this->data['lightTB'] = $topBottomMargin; // TOP/BOTTOM light margin =  x X-dim (non-spec.)
	}

	/**
	 * @param string $code
	 * @param float $printRatio
	 * @param bool $checksum
	 */
	private function init($code, $printRatio, $checksum)
	{
		$chr = [
			'0' => '11221',
			'1' => '21112',
			'2' => '12112',
			'3' => '22111',
			'4' => '11212',
			'5' => '21211',
			'6' => '12211',
			'7' => '11122',
			'8' => '21121',
			'9' => '12121',
			'A' => '11',
			'Z' => '21',
		];

		$checkdigit = '';
		if ($checksum) {
			// add checksum
			$checkdigit = $this->checksum($code);
			$code .= $checkdigit;
		}
		if ((strlen($code) % 2) != 0) {
			// add leading zero if code-length is odd
			$code = '0' . $code;
		}
		// add start and stop codes
		$code = 'AA' . strtolower($code) . 'ZA';

		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => []];
		$k = 0;
		$clen = strlen($code);
		for ($i = 0; $i < $clen; $i = ($i + 2)) {
			$charBar = $code[$i];
			$charSpace = $code[$i + 1];
			if ((!isset($chr[$charBar])) or (!isset($chr[$charSpace]))) {
				// invalid character
				throw new \Mpdf\Barcode\BarcodeException(sprintf('Invalid I25 barcode value "%s"', $code));
			}
			// create a bar-space sequence
			$seq = '';
			$chrlen = strlen($chr[$charBar]);
			for ($s = 0; $s < $chrlen; $s++) {
				$seq .= $chr[$charBar][$s] . $chr[$charSpace][$s];
			}
			$seqlen = strlen($seq);
			for ($j = 0; $j < $seqlen; ++$j) {
				if (($j % 2) == 0) {
					$t = true; // bar
				} else {
					$t = false; // space
				}
				$x = $seq[$j];
				if ($x == 2) {
					$w = $printRatio;
				} else {
					$w = 1;
				}

				$bararray['bcode'][$k] = ['t' => $t, 'w' => $w, 'h' => 1, 'p' => 0];
				$bararray['maxw'] += $w;
				++$k;
			}
		}
		$bararray['checkdigit'] = $checkdigit;

		$this->data = $bararray;
	}

	/**
	 * Checksum for standard 2 of 5 barcodes.
	 *
	 * @param string $code
	 * @return int
	 */
	private function checksum($code)
	{
		$len = strlen($code);
		$sum = 0;
		for ($i = 0; $i < $len; $i += 2) {
			$sum += $code[$i];
		}
		$sum *= 3;
		for ($i = 1; $i < $len; $i += 2) {
			$sum += ($code[$i]);
		}
		$r = $sum % 10;
		if ($r > 0) {
			$r = (10 - $r);
		}
		return $r;
	}

	/**
	 * @inheritdoc
	 */
	public function getType()
	{
		return 'I25';
	}

}
