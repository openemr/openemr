<?php

namespace Mpdf\Barcode;

use Mpdf\Utils\UtfString;

/**
 * C128 barcodes.
 * Very capable code, excellent density, high reliability; in very wide use world-wide
 */
class Code128 extends \Mpdf\Barcode\AbstractBarcode implements \Mpdf\Barcode\BarcodeInterface
{

	/**
	 * @param string $code
	 * @param string $type
	 * @param bool $ean
	 */
	public function __construct($code, $type = 'B', $ean = false)
	{
		$this->init($code, $type, $ean);

		$this->data['nom-X'] = 0.381; // Nominal value for X-dim (bar width) in mm (2 X min. spec.)
		$this->data['nom-H'] = 10;  // Nominal value for Height of Full bar in mm (non-spec.)
		$this->data['lightmL'] = 10; // LEFT light margin =  x X-dim (spec.)
		$this->data['lightmR'] = 10; // RIGHT light margin =  x X-dim (spec.)
		$this->data['lightTB'] = 0; // TOP/BOTTOM light margin =  x X-dim (non-spec.)
	}

	/**
	 * @param string $code
	 * @param string $type
	 * @param bool $ean
	 */
	protected function init($code, $type, $ean)
	{
		$code = UtfString::strcode2utf($code); // mPDF 5.7.1 Allows e.g. <barcode code="5432&#013;1068" type="C128A" />

		$chr = [
			'212222', /* 00 */
			'222122', /* 01 */
			'222221', /* 02 */
			'121223', /* 03 */
			'121322', /* 04 */
			'131222', /* 05 */
			'122213', /* 06 */
			'122312', /* 07 */
			'132212', /* 08 */
			'221213', /* 09 */
			'221312', /* 10 */
			'231212', /* 11 */
			'112232', /* 12 */
			'122132', /* 13 */
			'122231', /* 14 */
			'113222', /* 15 */
			'123122', /* 16 */
			'123221', /* 17 */
			'223211', /* 18 */
			'221132', /* 19 */
			'221231', /* 20 */
			'213212', /* 21 */
			'223112', /* 22 */
			'312131', /* 23 */
			'311222', /* 24 */
			'321122', /* 25 */
			'321221', /* 26 */
			'312212', /* 27 */
			'322112', /* 28 */
			'322211', /* 29 */
			'212123', /* 30 */
			'212321', /* 31 */
			'232121', /* 32 */
			'111323', /* 33 */
			'131123', /* 34 */
			'131321', /* 35 */
			'112313', /* 36 */
			'132113', /* 37 */
			'132311', /* 38 */
			'211313', /* 39 */
			'231113', /* 40 */
			'231311', /* 41 */
			'112133', /* 42 */
			'112331', /* 43 */
			'132131', /* 44 */
			'113123', /* 45 */
			'113321', /* 46 */
			'133121', /* 47 */
			'313121', /* 48 */
			'211331', /* 49 */
			'231131', /* 50 */
			'213113', /* 51 */
			'213311', /* 52 */
			'213131', /* 53 */
			'311123', /* 54 */
			'311321', /* 55 */
			'331121', /* 56 */
			'312113', /* 57 */
			'312311', /* 58 */
			'332111', /* 59 */
			'314111', /* 60 */
			'221411', /* 61 */
			'431111', /* 62 */
			'111224', /* 63 */
			'111422', /* 64 */
			'121124', /* 65 */
			'121421', /* 66 */
			'141122', /* 67 */
			'141221', /* 68 */
			'112214', /* 69 */
			'112412', /* 70 */
			'122114', /* 71 */
			'122411', /* 72 */
			'142112', /* 73 */
			'142211', /* 74 */
			'241211', /* 75 */
			'221114', /* 76 */
			'413111', /* 77 */
			'241112', /* 78 */
			'134111', /* 79 */
			'111242', /* 80 */
			'121142', /* 81 */
			'121241', /* 82 */
			'114212', /* 83 */
			'124112', /* 84 */
			'124211', /* 85 */
			'411212', /* 86 */
			'421112', /* 87 */
			'421211', /* 88 */
			'212141', /* 89 */
			'214121', /* 90 */
			'412121', /* 91 */
			'111143', /* 92 */
			'111341', /* 93 */
			'131141', /* 94 */
			'114113', /* 95 */
			'114311', /* 96 */
			'411113', /* 97 */
			'411311', /* 98 */
			'113141', /* 99 */
			'114131', /* 100 */
			'311141', /* 101 */
			'411131', /* 102 */
			'211412', /* 103 START A */
			'211214', /* 104 START B  */
			'211232', /* 105 START C  */
			'233111', /* STOP */
			'200000' /* END */
		];

		switch (strtoupper($type)) {
			case 'A':
				$startid = 103;
				$keys = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';
				for ($i = 0; $i < 32; ++$i) {
					$keys .= chr($i);
				}
				break;
			case 'B':
				$startid = 104;
				$keys = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~' . chr(127);
				break;
			case 'C':
				$startid = 105;
				$keys = '';
				if ((strlen($code) % 2) != 0) {
					// The length of barcode value must be even ($code). You must pad the number with zeros
					throw new \Mpdf\Barcode\BarcodeException('Invalid CODE128C barcode value');
				}
				for ($i = 0; $i <= 99; ++$i) {
					$keys .= chr($i);
				}
				$newCode = '';
				$hclen = (strlen($code) / 2);
				for ($i = 0; $i < $hclen; ++$i) {
					$newCode .= chr((int) ($code{(2 * $i)} . $code{(2 * $i + 1)}));
				}
				$code = $newCode;
				break;
			default:
				throw new \Mpdf\Barcode\BarcodeException('Invalid CODE128 barcode type');
		}

		// calculate check character
		$sum = $startid;

		// Add FNC 1 - which identifies it as EAN-128
		if ($ean) {
			$code = chr(102) . $code;
		}
		$clen = strlen($code);
		for ($i = 0; $i < $clen; ++$i) {
			if ($ean && $i == 0) {
				$sum += 102;
			} else {
				$sum += (strpos($keys, $code[$i]) * ($i + 1));
			}
		}
		$check = ($sum % 103);
		$checkdigit = $check;

		// add start, check and stop codes
		$code = chr($startid) . $code . chr($check) . chr(106) . chr(107);
		$bararray = ['code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => []];
		$k = 0;
		$len = strlen($code);

		for ($i = 0; $i < $len; ++$i) {

			$ck = strpos($keys, $code[$i]);
			if (($i == 0) || ($ean && $i == 1) | ($i > ($len - 4))) {
				$char_num = ord($code[$i]);
				$seq = $chr[$char_num];
			} elseif (($ck >= 0) && isset($chr[$ck])) {
				$seq = $chr[$ck];
			} else {
				// invalid character
				throw new \Mpdf\Barcode\BarcodeException(sprintf('Invalid character "%s" in CODE128C barcode value', $code[$i]));
			}
			for ($j = 0; $j < 6; ++$j) {
				if (($j % 2) == 0) {
					$t = true; // bar
				} else {
					$t = false; // space
				}
				$w = $seq[$j];
				$bararray['bcode'][$k] = ['t' => $t, 'w' => $w, 'h' => 1, 'p' => 0];
				$bararray['maxw'] += $w;
				++$k;
			}
		}

		$bararray['checkdigit'] = $checkdigit;
		$this->data = $bararray;
	}

	public function getType()
	{
		return 'CODE128';
	}

}
