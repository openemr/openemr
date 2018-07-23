<?php

namespace Mpdf\Barcode;

/**
 * CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
 */
class Code39 extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param float $printRatio
	 * @param bool $extended
	 * @param bool $checksum
	 */
	public function __construct($code, $printRatio, $extended = false, $checksum = false)
	{
		$this->init($code, $printRatio, $extended, $checksum);

		$this->data['nom-X'] = 0.381; // Nominal value for X-dim (bar width) in mm (2 X min. spec.)
		$this->data['nom-H'] = 10;  // Nominal value for Height of Full bar in mm (non-spec.)
		$this->data['lightmL'] = 10; // LEFT light margin =  x X-dim (spec.)
		$this->data['lightmR'] = 10; // RIGHT light margin =  x X-dim (spec.)
		$this->data['lightTB'] = 0; // TOP/BOTTOM light margin =  x X-dim (non-spec.)
	}

	/**
	 * @param string $code
	 * @param float $printRatio
	 * @param bool $extended
	 * @param bool $checksum
	 *
	 * @return mixed[]
	 */
	private function init($code, $printRatio, $extended, $checksum)
	{
		$chr = [
			'0' => '111221211',
			'1' => '211211112',
			'2' => '112211112',
			'3' => '212211111',
			'4' => '111221112',
			'5' => '211221111',
			'6' => '112221111',
			'7' => '111211212',
			'8' => '211211211',
			'9' => '112211211',
			'A' => '211112112',
			'B' => '112112112',
			'C' => '212112111',
			'D' => '111122112',
			'E' => '211122111',
			'F' => '112122111',
			'G' => '111112212',
			'H' => '211112211',
			'I' => '112112211',
			'J' => '111122211',
			'K' => '211111122',
			'L' => '112111122',
			'M' => '212111121',
			'N' => '111121122',
			'O' => '211121121',
			'P' => '112121121',
			'Q' => '111111222',
			'R' => '211111221',
			'S' => '112111221',
			'T' => '111121221',
			'U' => '221111112',
			'V' => '122111112',
			'W' => '222111111',
			'X' => '121121112',
			'Y' => '221121111',
			'Z' => '122121111',
			'-' => '121111212',
			'.' => '221111211',
			' ' => '122111211',
			'$' => '121212111',
			'/' => '121211121',
			'+' => '121112121',
			'%' => '111212121',
			'*' => '121121211',
		];

		$code = strtoupper($code);
		$checkdigit = '';

		if ($extended) {
			// extended mode
			$code = $this->encodeExt($code);
		}

		if ($code === false) {
			throw new \Mpdf\Barcode\BarcodeException('Invalid CODE39 barcode value');
		}

		if ($checksum) {
			// checksum
			$checkdigit = $this->checksum($code);
			$code .= $checkdigit;
		}
		// add star$this->>datat and stop codes
		$code = '*' . $code . '*';

		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => []];
		$k = 0;
		$clen = strlen($code);
		for ($i = 0; $i < $clen; ++$i) {
			$char = $code[$i];
			if (!isset($chr[$char])) {
				// invalid character
				throw new \Mpdf\Barcode\BarcodeException('Invalid CODE39 barcode value');
			}
			for ($j = 0; $j < 9; ++$j) {
				if (($j % 2) == 0) {
					$t = true; // bar
				} else {
					$t = false; // space
				}
				$x = $chr[$char][$j];
				if ($x == 2) {
					$w = $printRatio;
				} else {
					$w = 1;
				}

				$bararray['bcode'][$k] = ['t' => $t, 'w' => $w, 'h' => 1, 'p' => 0];
				$bararray['maxw'] += $w;
				++$k;
			}
			$bararray['bcode'][$k] = ['t' => false, 'w' => 1, 'h' => 1, 'p' => 0];
			$bararray['maxw'] += 1;
			++$k;
		}

		$bararray['checkdigit'] = $checkdigit;

		$this->data = $bararray;
	}

	/**
	 * Encode a string to be used for CODE 39 Extended mode.
	 *
	 * @param string $code
	 * @return string
	 */
	protected function encodeExt($code)
	{
		$encode = [
			chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C',
			chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G',
			chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', chr(11) => '£K',
			chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O',
			chr(16) => '$P', chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S',
			chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W',
			chr(24) => '$X', chr(25) => '$Y', chr(26) => '$Z', chr(27) => '%A',
			chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E',
			chr(32) => ' ', chr(33) => '/A', chr(34) => '/B', chr(35) => '/C',
			chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G',
			chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K',
			chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O',
			chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3',
			chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7',
			chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F',
			chr(60) => '%G', chr(61) => '%H', chr(62) => '%I', chr(63) => '%J',
			chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C',
			chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G',
			chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K',
			chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
			chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S',
			chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W',
			chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K',
			chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O',
			chr(96) => '%W', chr(97) => '+A', chr(98) => '+B', chr(99) => '+C',
			chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G',
			chr(104) => '+H', chr(105) => '+I', chr(106) => '+J', chr(107) => '+K',
			chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O',
			chr(112) => '+P', chr(113) => '+Q', chr(114) => '+R', chr(115) => '+S',
			chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W',
			chr(120) => '+X', chr(121) => '+Y', chr(122) => '+Z', chr(123) => '%P',
			chr(124) => '%Q', chr(125) => '%R', chr(126) => '%S', chr(127) => '%T'
		];

		$code_ext = '';
		$clen = strlen($code);

		for ($i = 0; $i < $clen; ++$i) {

			if (ord($code[$i]) > 127) {
				throw new \Mpdf\Barcode\BarcodeException('Invalid CODE39 barcode value');
			}

			$code_ext .= $encode[$code[$i]];
		}

		return $code_ext;
	}

	/**
	 * Calculate CODE 39 checksum (modulo 43).
	 *
	 * @param string $code
	 * @return string mixed
	 */
	protected function checksum($code)
	{
		$chars = [
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%'
		];

		$sum = 0;
		$clen = strlen($code);

		for ($i = 0; $i < $clen; ++$i) {
			$k = array_keys($chars, $code[$i]);
			$sum += $k[0];
		}

		$j = ($sum % 43);

		return $chars[$j];
	}

	/**
	 * @inheritdoc
	 */
	public function getType()
	{
		return 'CODE39';
	}

}
