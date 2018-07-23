<?php

namespace Mpdf\Barcode;

/**
 * UPC-Based Extentions
 * 2-Digit Ext.: Used to indicate magazines and newspaper issue numbers
 * 5-Digit Ext.: Used to mark suggested retail price of books
 */
class EanExt extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param int $length
	 * @param float $leftMargin
	 * @param float $rightMargin
	 * @param float $xDim
	 * @param float $barHeight
	 * @param float $separatorMargin
	 */
	public function __construct($code, $length, $leftMargin, $rightMargin, $xDim, $barHeight, $separatorMargin)
	{
		$this->init($code, $length);

		$this->data['lightmL'] = $leftMargin; // LEFT light margin =  x X-dim (http://www.gs1uk.org)
		$this->data['lightmR'] = $rightMargin; // RIGHT light margin =  x X-dim (http://www.gs1uk.org)
		$this->data['nom-X'] = $xDim; // Nominal value for X-dim in mm (http://www.gs1uk.org)
		$this->data['nom-H'] = $barHeight; // Nominal bar height in mm incl. numerals (http://www.gs1uk.org)
		$this->data['sepM'] = $separatorMargin; // SEPARATION margin =  x X-dim (http://web.archive.org/web/19990501035133/http://www.uc-council.org/d36-d.htm)
	}

	/**
	 * @param string $code
	 * @param int $length
	 */
	private function init($code, $length = 5)
	{
		// Padding
		$code = str_pad($code, $length, '0', STR_PAD_LEFT);

		// Calculate check digit
		if ($length == 2) {
			$r = $code % 4;
		} elseif ($length == 5) {
			$r = (3 * ($code{0} + $code{2} + $code{4})) + (9 * ($code{1} + $code{3}));
			$r %= 10;
		} else {
			throw new \Mpdf\Barcode\BarcodeException('Invalid EAN barcode value');
		}

		// Convert digits to bars
		$codes = [
			'A' => [ // left odd parity
				'0' => '0001101',
				'1' => '0011001',
				'2' => '0010011',
				'3' => '0111101',
				'4' => '0100011',
				'5' => '0110001',
				'6' => '0101111',
				'7' => '0111011',
				'8' => '0110111',
				'9' => '0001011'],
			'B' => [ // left even parity
				'0' => '0100111',
				'1' => '0110011',
				'2' => '0011011',
				'3' => '0100001',
				'4' => '0011101',
				'5' => '0111001',
				'6' => '0000101',
				'7' => '0010001',
				'8' => '0001001',
				'9' => '0010111']
		];
		$parities = [];
		$parities[2] = [
			'0' => ['A', 'A'],
			'1' => ['A', 'B'],
			'2' => ['B', 'A'],
			'3' => ['B', 'B']
		];
		$parities[5] = [
			'0' => ['B', 'B', 'A', 'A', 'A'],
			'1' => ['B', 'A', 'B', 'A', 'A'],
			'2' => ['B', 'A', 'A', 'B', 'A'],
			'3' => ['B', 'A', 'A', 'A', 'B'],
			'4' => ['A', 'B', 'B', 'A', 'A'],
			'5' => ['A', 'A', 'B', 'B', 'A'],
			'6' => ['A', 'A', 'A', 'B', 'B'],
			'7' => ['A', 'B', 'A', 'B', 'A'],
			'8' => ['A', 'B', 'A', 'A', 'B'],
			'9' => ['A', 'A', 'B', 'A', 'B']
		];
		$p = $parities[$length][$r];
		$seq = '1011'; // left guard bar
		$seq .= $codes[$p[0]][$code{0}];
		for ($i = 1; $i < $length; ++$i) {
			$seq .= '01'; // separator
			$seq .= $codes[$p[$i]][$code[$i]];
		}

		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => []];

		$this->data = $this->binseqToArray($seq, $bararray);
	}

	public function getType()
	{
		return 'EAN EXT';
	}

}
