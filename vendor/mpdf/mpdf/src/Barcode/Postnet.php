<?php

namespace Mpdf\Barcode;

/**
 * POSTNET and PLANET barcodes.
 * Used by U.S. Postal Service for automated mail sorting
 */
class Postnet extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param float $xDim
	 * @param float $gapWidth
	 * @param bool $planet
	 */
	public function __construct($code, $xDim, $gapWidth, $planet = false)
	{
		$this->init($code, $gapWidth, $planet);

		$this->data['nom-X'] = $xDim;
		$this->data['nom-H'] = 3.175; // Nominal value for Height of Full bar in mm (spec.)
		$this->data['quietL'] = 3.175; // LEFT Quiet margin =  mm (?spec.)
		$this->data['quietR'] = 3.175; // RIGHT Quiet margin =  mm (?spec.)
		$this->data['quietTB'] = 1.016; // TOP/BOTTOM Quiet margin =  mm (?spec.)
	}

	/**
	 * @param string $code
	 * @param float $gapWidth
	 * @param bool $planet
	 */
	private function init($code, $gapWidth, $planet)
	{
		// bar lenght
		if ($planet) {
			$barlen = [
				0 => [1, 1, 2, 2, 2],
				1 => [2, 2, 2, 1, 1],
				2 => [2, 2, 1, 2, 1],
				3 => [2, 2, 1, 1, 2],
				4 => [2, 1, 2, 2, 1],
				5 => [2, 1, 2, 1, 2],
				6 => [2, 1, 1, 2, 2],
				7 => [1, 2, 2, 2, 1],
				8 => [1, 2, 2, 1, 2],
				9 => [1, 2, 1, 2, 2]
			];
		} else {
			$barlen = [
				0 => [2, 2, 1, 1, 1],
				1 => [1, 1, 1, 2, 2],
				2 => [1, 1, 2, 1, 2],
				3 => [1, 1, 2, 2, 1],
				4 => [1, 2, 1, 1, 2],
				5 => [1, 2, 1, 2, 1],
				6 => [1, 2, 2, 1, 1],
				7 => [2, 1, 1, 1, 2],
				8 => [2, 1, 1, 2, 1],
				9 => [2, 1, 2, 1, 1]
			];
		}

		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 5, 'bcode' => []];

		$k = 0;
		$code = str_replace('-', '', $code);
		$code = str_replace(' ', '', $code);
		$len = strlen($code);

		// calculate checksum
		$sum = 0;
		for ($i = 0; $i < $len; ++$i) {
			$sum += (int) $code[$i];
		}

		$chkd = ($sum % 10);
		if ($chkd > 0) {
			$chkd = (10 - $chkd);
		}

		$code .= $chkd;
		$checkdigit = $chkd;
		$len = strlen($code);

		// start bar
		$bararray['bcode'][$k++] = ['t' => 1, 'w' => 1, 'h' => 5, 'p' => 0];
		$bararray['bcode'][$k++] = ['t' => 0, 'w' => $gapWidth, 'h' => 5, 'p' => 0];
		$bararray['maxw'] += (1 + $gapWidth);

		for ($i = 0; $i < $len; ++$i) {
			for ($j = 0; $j < 5; ++$j) {
				$bh = $barlen[$code[$i]][$j];
				if ($bh == 2) {
					$h = 5;
					$p = 0;
				} else {
					$h = 2;
					$p = 3;
				}
				$bararray['bcode'][$k++] = ['t' => 1, 'w' => 1, 'h' => $h, 'p' => $p];
				$bararray['bcode'][$k++] = ['t' => 0, 'w' => $gapWidth, 'h' => 2, 'p' => 0];
				$bararray['maxw'] += (1 + $gapWidth);
			}
		}

		// end bar
		$bararray['bcode'][$k++] = ['t' => 1, 'w' => 1, 'h' => 5, 'p' => 0];
		$bararray['maxw'] += 1;
		$bararray['checkdigit'] = $checkdigit;

		$this->data = $bararray;
	}

	/**
	 * @inheritdoc
	 */
	public function getType()
	{
		return 'POSTNET';
	}

}
