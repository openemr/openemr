<?php

namespace Mpdf\Barcode;

/**
 * IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200
 *
 * (requires PHP bcmath extension)
 *
 * Intelligent Mail barcode is a 65-bar code for use on mail in the United States.
 * The fields are described as follows:
 *
 *   - The Barcode Identifier shall be assigned by USPS to encode the presort identification that is currently
 *     printed in human readable form on the optional endorsement line (OEL) as well as for future USPS use. This
 *     shall be two digits, with the second digit in the range of 0-4. The allowable encoding ranges shall be 00-04,
 *     10-14, 20-24, 30-34, 40-44, 50-54, 60-64, 70-74, 80-84, and 90-94.
 *
 *   - The Service Type Identifier shall be assigned by USPS for any combination of services requested on the mailpiece.
 *     The allowable encoding range shall be 000-999. Each 3-digit value shall correspond to a particular mail class
 *     with a particular combination of service(s). Each service program, such as OneCode Confirm and OneCode ACS,
 *     shall provide the list of Service Type Identifier values.
 *
 *   - The Mailer or Customer Identifier shall be assigned by USPS as a unique, 6 or 9 digit number that identifies
 *     a business entity. The allowable encoding range for the 6 digit Mailer ID shall be 000000- 899999, while the
 *     allowable encoding range for the 9 digit Mailer ID shall be 900000000-999999999.
 *
 *   - The Serial or Sequence Number shall be assigned by the mailer for uniquely identifying and tracking mailpieces.
 *     The allowable encoding range shall be 000000000-999999999 when used with a 6 digit Mailer ID and 000000-999999
 *     when used with a 9 digit Mailer ID. e. The Delivery Point ZIP Code shall be assigned by the mailer for routing
 *     the mailpiece. This shall replace POSTNET for routing the mailpiece to its final delivery point. The length may
 *     be 0, 5, 9, or 11 digits. The allowable encoding ranges shall be no ZIP Code, 00000-99999,  000000000-999999999,
 *     and 00000000000-99999999999.
 */
class Imb extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param float $xDim
	 * @param float $gapWidth
	 * @param int[] $daft
	 */
	public function __construct($code, $xDim, $gapWidth, $daft)
	{
		if (!function_exists('bcadd')) {
			throw new \Mpdf\Barcode\BarcodeException('IMB barcodes require bcmath extension to be loaded.');
		}

		$this->init($code, $gapWidth, $daft);

		$this->data['nom-X'] = $xDim;
		$this->data['nom-H'] = 3.68; // Nominal value for Height of Full bar in mm (spec.)

		// USPS-B-3200 Revision C = 4.623
		// USPS-B-3200 Revision E = 3.68
		$this->data['quietL'] = 3.175; // LEFT Quiet margin =  mm (spec.)
		$this->data['quietR'] = 3.175; // RIGHT Quiet margin =  mm (spec.)
		$this->data['quietTB'] = 0.711; // TOP/BOTTOM Quiet margin =  mm (spec.)
	}

	/**
	 * @param string $code
	 * @param float $gapWidth
	 * @param int[] $daft
	 */
	private function init($code, $gapWidth, $daft)
	{
		$asc_chr = [
			4, 0, 2, 6, 3, 5, 1, 9, 8, 7, 1, 2, 0, 6, 4, 8, 2, 9, 5, 3, 0, 1, 3, 7, 4, 6, 8, 9, 2, 0, 5, 1, 9, 4,
			3, 8, 6, 7, 1, 2, 4, 3, 9, 5, 7, 8, 3, 0, 2, 1, 4, 0, 9, 1, 7, 0, 2, 4, 6, 3, 7, 1, 9, 5, 8
		];

		$dsc_chr = [
			7, 1, 9, 5, 8, 0, 2, 4, 6, 3, 5, 8, 9, 7, 3, 0, 6, 1, 7, 4, 6, 8, 9, 2, 5, 1, 7, 5, 4, 3, 8, 7, 6, 0, 2,
			5, 4, 9, 3, 0, 1, 6, 8, 2, 0, 4, 5, 9, 6, 7, 5, 2, 6, 3, 8, 5, 1, 9, 8, 7, 4, 0, 2, 6, 3
		];

		$asc_pos = [
			3, 0, 8, 11, 1, 12, 8, 11, 10, 6, 4, 12, 2, 7, 9, 6, 7, 9, 2, 8, 4, 0, 12, 7, 10, 9, 0, 7, 10, 5, 7, 9, 6,
			8, 2, 12, 1, 4, 2, 0, 1, 5, 4, 6, 12, 1, 0, 9, 4, 7, 5, 10, 2, 6, 9, 11, 2, 12, 6, 7, 5, 11, 0, 3, 2
		];

		$dsc_pos = [
			2, 10, 12, 5, 9, 1, 5, 4, 3, 9, 11, 5, 10, 1, 6, 3, 4, 1, 10, 0, 2, 11, 8, 6, 1, 12, 3, 8, 6, 4, 4, 11, 0,
			6, 1, 9, 11, 5, 3, 7, 3, 10, 7, 11, 8, 2, 10, 3, 5, 8, 0, 3, 12, 11, 8, 4, 5, 1, 3, 0, 7, 12, 9, 8, 10
		];

		$codeArray = explode('-', $code);
		$trackingNumber = $codeArray[0];

		$routingCode = '';
		if (isset($codeArray[1])) {
			$routingCode = $codeArray[1];
		}

		// Conversion of Routing Code
		switch (strlen($routingCode)) {
			case 0:
				$binaryCode = 0;
				break;
			case 5:
				$binaryCode = bcadd($routingCode, '1');
				break;
			case 9:
				$binaryCode = bcadd($routingCode, '100001');
				break;
			case 11:
				$binaryCode = bcadd($routingCode, '1000100001');
				break;
			default:
				throw new \Mpdf\Barcode\BarcodeException(sprintf('Invalid MSI routing code "%s"', $routingCode));
		}

		$binaryCode = bcmul($binaryCode, 10);
		$binaryCode = bcadd($binaryCode, $trackingNumber{0});
		$binaryCode = bcmul($binaryCode, 5);
		$binaryCode = bcadd($binaryCode, $trackingNumber{1});

		$binaryCode .= substr($trackingNumber, 2, 18);

		// convert to hexadecimal
		$binaryCode = $this->decToHex($binaryCode);

		// pad to get 13 bytes
		$binaryCode = str_pad($binaryCode, 26, '0', STR_PAD_LEFT);

		// convert string to array of bytes
		$binaryCodeArray = chunk_split($binaryCode, 2, "\r");
		$binaryCodeArray = substr($binaryCodeArray, 0, -1);
		$binaryCodeArray = explode("\r", $binaryCodeArray);

		// calculate frame check sequence
		$fcs = $this->imbCrc11Fcs($binaryCodeArray);

		// exclude first 2 bits from first byte
		$first_byte = sprintf('%2s', dechex((hexdec($binaryCodeArray[0]) << 2) >> 2));
		$binaryCode102bit = $first_byte . substr($binaryCode, 2);

		// convert binary data to codewords
		$codewords = [];
		$data = $this->hexToDec($binaryCode102bit);
		$codewords[0] = bcmod($data, 636) * 2;
		$data = bcdiv($data, 636);

		for ($i = 1; $i < 9; ++$i) {
			$codewords[$i] = bcmod($data, 1365);
			$data = bcdiv($data, 1365);
		}

		$codewords[9] = $data;
		if (($fcs >> 10) == 1) {
			$codewords[9] += 659;
		}

		// generate lookup tables
		$table2of13 = $this->imbTables(2, 78);
		$table5of13 = $this->imbTables(5, 1287);

		// convert codewords to characters
		$characters = [];
		$bitmask = 512;

		foreach ($codewords as $k => $val) {
			if ($val <= 1286) {
				$chrcode = $table5of13[$val];
			} else {
				$chrcode = $table2of13[($val - 1287)];
			}
			if (($fcs & $bitmask) > 0) {
				// bitwise invert
				$chrcode = ((~$chrcode) & 8191);
			}
			$characters[] = $chrcode;
			$bitmask /= 2;
		}

		$characters = array_reverse($characters);

		// build bars
		$k = 0;
		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => $daft['F'], 'bcode' => []];
		for ($i = 0; $i < 65; ++$i) {
			$asc = (($characters[$asc_chr[$i]] & pow(2, $asc_pos[$i])) > 0);
			$dsc = (($characters[$dsc_chr[$i]] & pow(2, $dsc_pos[$i])) > 0);
			if ($asc and $dsc) {
				// full bar (F)
				$p = 0;
				$h = $daft['F'];
			} elseif ($asc) {
				// ascender (A)
				$p = 0;
				$h = $daft['A'];
			} elseif ($dsc) {
				// descender (D)
				$p = $daft['F'] - $daft['D'];
				$h = $daft['D'];
			} else {
				// tracker (T)
				$p = ($daft['F'] - $daft['T']) / 2;
				$h = $daft['T'];
			}
			$bararray['bcode'][$k++] = ['t' => 1, 'w' => 1, 'h' => $h, 'p' => $p];
			// Gap
			$bararray['bcode'][$k++] = ['t' => 0, 'w' => $gapWidth, 'h' => 1, 'p' => 0];
			$bararray['maxw'] += (1 + $gapWidth);
		}

		unset($bararray['bcode'][($k - 1)]);
		$bararray['maxw'] -= $gapWidth;

		$this->data = $bararray;
	}

	/**
	 * Intelligent Mail Barcode calculation of Frame Check Sequence
	 *
	 * @param string[] $codeArray
	 * @return int
	 */
	private function imbCrc11Fcs($codeArray)
	{
		$genpoly = 0x0F35; // generator polynomial
		$fcs = 0x07FF; // Frame Check Sequence

		// do most significant byte skipping the 2 most significant bits
		$data = hexdec($codeArray[0]) << 5;
		for ($bit = 2; $bit < 8; ++$bit) {
			if (($fcs ^ $data) & 0x400) {
				$fcs = ($fcs << 1) ^ $genpoly;
			} else {
				$fcs = ($fcs << 1);
			}
			$fcs &= 0x7FF;
			$data <<= 1;
		}
		// do rest of bytes
		for ($byte = 1; $byte < 13; ++$byte) {
			$data = hexdec($codeArray[$byte]) << 3;
			for ($bit = 0; $bit < 8; ++$bit) {
				if (($fcs ^ $data) & 0x400) {
					$fcs = ($fcs << 1) ^ $genpoly;
				} else {
					$fcs = ($fcs << 1);
				}
				$fcs &= 0x7FF;
				$data <<= 1;
			}
		}
		return $fcs;
	}

	/**
	 * Reverse unsigned short value
	 *
	 * @param int $num
	 * @return int
	 */
	private function imbReverseUs($num)
	{
		$rev = 0;
		for ($i = 0; $i < 16; ++$i) {
			$rev <<= 1;
			$rev |= ($num & 1);
			$num >>= 1;
		}
		return $rev;
	}

	/**
	 * Generate Nof13 tables used for Intelligent Mail Barcode
	 *
	 * @param int $n
	 * @param int $size
	 *
	 * @return mixed[]
	 */
	private function imbTables($n, $size)
	{
		$table = [];
		$lli = 0; // LUT lower index
		$lui = $size - 1; // LUT upper index
		for ($count = 0; $count < 8192; ++$count) {

			$bitCount = 0;
			for ($bit_index = 0; $bit_index < 13; ++$bit_index) {
				$bitCount += (int) (($count & (1 << $bit_index)) != 0);
			}

			// if we don't have the right number of bits on, go on to the next value
			if ($bitCount == $n) {
				$reverse = ($this->imbReverseUs($count) >> 3);
				// if the reverse is less than count, we have already visited this pair before
				if ($reverse >= $count) {
					// If count is symmetric, place it at the first free slot from the end of the list.
					// Otherwise, place it at the first free slot from the beginning of the list AND place $reverse ath the next free slot from the beginning of the list
					if ($reverse == $count) {
						$table[$lui] = $count;
						--$lui;
					} else {
						$table[$lli] = $count;
						++$lli;
						$table[$lli] = $reverse;
						++$lli;
					}
				}
			}
		}

		return $table;
	}

	/**
	 * Convert large integer number to hexadecimal representation.
	 *
	 * @param int $number
	 * @return string
	 */
	private function decToHex($number)
	{
		$hex = [];

		if ($number == 0) {
			return '00';
		}

		while ($number > 0) {
			if ($number == 0) {
				array_push($hex, '0');
			} else {
				array_push($hex, strtoupper(dechex(bcmod($number, '16'))));
				$number = bcdiv($number, '16', 0);
			}
		}

		$hex = array_reverse($hex);
		return implode($hex);
	}

	/**
	 * Convert large hexadecimal number to decimal representation (string).
	 * (requires PHP bcmath extension)
	 *
	 * @param string $hex
	 * @return int
	 */
	private function hexToDec($hex)
	{
		$dec = 0;
		$bitval = 1;
		$len = strlen($hex);
		for ($pos = ($len - 1); $pos >= 0; --$pos) {
			$dec = bcadd($dec, bcmul(hexdec($hex[$pos]), $bitval));
			$bitval = bcmul($bitval, 16);
		}
		return $dec;
	}

	/**
	 * @inheritdoc
	 */
	public function getType()
	{
		return 'IMB';
	}

}
