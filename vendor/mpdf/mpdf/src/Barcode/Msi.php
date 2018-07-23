<?php

namespace Mpdf\Barcode;

/**
 * MSI - Variation of Plessey code, with similar applications
 * Contains digits (0 to 9) and encodes the data only in the width of bars.
 */
class Msi extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param int $code
	 * @param bool $checksum
	 */
	public function __construct($code, $checksum = false)
	{
		$this->init($code, $checksum);

		$this->data['nom-X'] = 0.381; // Nominal value for X-dim (bar width) in mm (2 X min. spec.)
		$this->data['nom-H'] = 10;  // Nominal value for Height of Full bar in mm (non-spec.)
		$this->data['lightmL'] = 12; // LEFT light margin =  x X-dim (spec.)
		$this->data['lightmR'] = 12; // RIGHT light margin =  x X-dim (spec.)
		$this->data['lightTB'] = 0; // TOP/BOTTOM light margin =  x X-dim (non-spec.)
	}

	/**
	 * @param int $code
	 * @param bool $checksum
	 */
	private function init($code, $checksum)
	{
		$chr = [
			'0' => '100100100100',
			'1' => '100100100110',
			'2' => '100100110100',
			'3' => '100100110110',
			'4' => '100110100100',
			'5' => '100110100110',
			'6' => '100110110100',
			'7' => '100110110110',
			'8' => '110100100100',
			'9' => '110100100110',
			'A' => '110100110100',
			'B' => '110100110110',
			'C' => '110110100100',
			'D' => '110110100110',
			'E' => '110110110100',
			'F' => '110110110110',
		];

		$checkdigit = '';

		if ($checksum) {
			// add checksum
			$clen = strlen($code);
			$p = 2;
			$check = 0;
			for ($i = ($clen - 1); $i >= 0; --$i) {
				$check += (hexdec($code[$i]) * $p);
				++$p;
				if ($p > 7) {
					$p = 2;
				}
			}
			$check %= 11;
			if ($check > 0) {
				$check = 11 - $check;
			}
			$code .= $check;
			$checkdigit = $check;
		}
		$seq = '110'; // left guard
		$clen = strlen($code);
		for ($i = 0; $i < $clen; ++$i) {
			$digit = $code[$i];
			if (!isset($chr[$digit])) {
				// invalid character
				throw new \Mpdf\Barcode\BarcodeException(sprintf('Invalid character "%s" in MSI barcode value', $digit));
			}
			$seq .= $chr[$digit];
		}
		$seq .= '1001'; // right guard
		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => []];
		$bararray['checkdigit'] = $checkdigit;

		$this->data = $this->binseqToArray($seq, $bararray);
	}

	/**
	 * @inheritdoc
	 */
	public function getType()
	{
		return 'MSI';
	}

}
