<?php

namespace Mpdf\Barcode;

/**
 * CODABAR barcodes.
 * Older code often used in library systems, sometimes in blood banks
 */
class Codabar extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param float $printRatio
	 */
	public function __construct($code, $printRatio)
	{
		$this->init($code, $printRatio);

		$this->data['nom-X'] = 0.381; // Nominal value for X-dim (bar width) in mm (2 X min. spec.)
		$this->data['nom-H'] = 10; // Nominal value for Height of Full bar in mm (non-spec.)
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
			'0' => '11111221',
			'1' => '11112211',
			'2' => '11121121',
			'3' => '22111111',
			'4' => '11211211',
			'5' => '21111211',
			'6' => '12111121',
			'7' => '12112111',
			'8' => '12211111',
			'9' => '21121111',
			'-' => '11122111',
			'$' => '11221111',
			':' => '21112121',
			'/' => '21211121',
			'.' => '21212111',
			'+' => '11222221',
			'A' => '11221211',
			'B' => '12121121',
			'C' => '11121221',
			'D' => '11122211'
		];

		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => []];
		$k = 0;

		$code = strtoupper($code);
		$len = strlen($code);

		for ($i = 0; $i < $len; ++$i) {

			if (!isset($chr[$code[$i]])) {
				throw new \Mpdf\Barcode\BarcodeException(sprintf('Invalid character "%s" in CODABAR barcode value "%s"', $code[$i], $code));
			}

			$seq = $chr[$code[$i]];

			for ($j = 0; $j < 8; ++$j) {
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

		$this->data = $bararray;
	}

	public function getType()
	{
		return 'CODABAR';
	}

}
