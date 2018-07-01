<?php

namespace Mpdf\Barcode;

/**
 * Standard 2 of 5 barcodes.
 * Used in airline ticket marking, photofinishing
 * Contains digits (0 to 9) and encodes the data only in the width of bars.
 */
class S25 extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param bool $checksum
	 */
	public function __construct($code, $checksum = false)
	{
		$this->init($code, $checksum);

		$this->data['nom-X'] = 0.381; // Nominal value for X-dim (bar width) in mm (2 X min. spec.)
		$this->data['nom-H'] = 10;  // Nominal value for Height of Full bar in mm (non-spec.)
		$this->data['lightmL'] = 10; // LEFT light margin =  x X-dim (spec.)
		$this->data['lightmR'] = 10; // RIGHT light margin =  x X-dim (spec.)
		$this->data['lightTB'] = 0; // TOP/BOTTOM light margin =  x X-dim (non-spec.)
	}

	/**
	 * @param string $code
	 * @param bool $checksum
	 */
	private function init($code, $checksum)
	{
		$chr = [
			'0' => '10101110111010',
			'1' => '11101010101110',
			'2' => '10111010101110',
			'3' => '11101110101010',
			'4' => '10101110101110',
			'5' => '11101011101010',
			'6' => '10111011101010',
			'7' => '10101011101110',
			'8' => '10101110111010',
			'9' => '10111010111010',
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

		$seq = '11011010';
		$clen = strlen($code);
		for ($i = 0; $i < $clen; ++$i) {
			$digit = $code[$i];
			if (!isset($chr[$digit])) {
				// invalid character
				throw new \Mpdf\Barcode\BarcodeException(sprintf('Invalid character "%s" in S25 barcode value', $digit));
			}
			$seq .= $chr[$digit];
		}

		$seq .= '1101011';
		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => []];
		$bararray['checkdigit'] = $checkdigit;

		$this->data = $this->binseqToArray($seq, $bararray);
	}

	/**
	 * Checksum for standard 2 of 5 barcodes.
	 *
	 * @param string $code
	 *
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
		return 'S25';
	}

}
