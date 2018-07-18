<?php

namespace Mpdf;

class Barcode
{

	public function getBarcodeArray($code, $type, $pr = '')
	{
		$barcode = $this->getBarcode($code, $type, $pr);
		return $barcode ? $barcode->getData() : false;
	}

	public function getChecksum($code, $type)
	{
		$barcode = $this->getBarcode($code, $type);
		return $barcode ? $barcode->getChecksum() : false;
	}

	/**
	 * @param string $code
	 * @param string $type
	 * @param float $pr
	 *
	 * @return \Mpdf\Barcode\BarcodeInterface
	 */
	public function getBarcode($code, $type, $pr = 0.0)
	{
		$barcode = false;

		switch (strtoupper($type)) {
			case 'ISBN':
			case 'ISSN':
			case 'EAN13': // EAN 13
				$barcode = new Barcode\EanUpc($code, 13, 11, 7, 0.33, 25.93);
				break;

			case 'UPCA': // UPC-A
				$barcode = new Barcode\EanUpc($code, 12, 9, 9, 0.33, 25.91);
				break;

			case 'UPCE': // UPC-E
				$barcode = new Barcode\EanUpc($code, 6, 9, 7, 0.33, 25.93);
				break;

			case 'EAN8': // EAN 8
				$barcode = new Barcode\EanUpc($code, 8, 7, 7, 0.33, 21.64);
				break;

			case 'EAN2': // 2-Digits UPC-Based Extention
				$barcode = new Barcode\EanExt($code, 2, 7, 7, 0.33, 20, 9);
				break;

			case 'EAN5': // 5-Digits UPC-Based Extention
				$barcode = new Barcode\EanExt($code, 5, 7, 7, 0.33, 20, 9);
				break;

			case 'IMB': // IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				$barcode = new Barcode\Imb($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, ['D' => 2, 'A' => 2, 'F' => 3, 'T' => 1]);
				break;

			case 'RM4SCC': // RM4SCC (Royal Mail 4-state Customer Code) - CBC (Customer Bar Code)
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				$barcode = new Barcode\Rm4Scc($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, ['D' => 5, 'A' => 5, 'F' => 8, 'T' => 2]);
				break;

			case 'KIX': // KIX (Klant index - Customer index)
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				$barcode = new Barcode\Rm4Scc($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, ['D' => 5, 'A' => 5, 'F' => 8, 'T' => 2], true);
				break;

			case 'POSTNET': // POSTNET
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				$barcode = new Barcode\Postnet($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, false);
				break;

			case 'PLANET': // PLANET
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				$barcode = new Barcode\Postnet($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, true);
				break;

			case 'C93': // CODE 93 - USS-93
				$barcode = new Barcode\Code93($code);
				break;

			case 'CODE11': // CODE 11
				$barcode = new Barcode\Code11($code, ($pr > 0) ? $pr : 3);
				break;

			case 'MSI':  // MSI (Variation of Plessey code)
				$barcode = new Barcode\Msi($code, false);
				break;

			case 'MSI+': // MSI + CHECKSUM (modulo 11)
				$barcode = new Barcode\Msi($code, true);
				break;

			case 'CODABAR': // CODABAR
				$barcode = new Barcode\Codabar($code, ($pr > 0) ? $pr : 2.5);
				break;

			case 'C128A': // CODE 128 A
				$barcode = new Barcode\Code128($code, 'A');
				break;

			case 'C128B': // CODE 128 B
				$barcode = new Barcode\Code128($code, 'B');
				break;

			case 'C128C':  // CODE 128 C
				$barcode = new Barcode\Code128($code, 'C');
				break;

			case 'EAN128A':  // EAN 128 A
				$barcode = new Barcode\Code128($code, 'A', true);
				break;

			case 'EAN128B':  // EAN 128 B
				$barcode = new Barcode\Code128($code, 'B', true);
				break;

			case 'EAN128C': // EAN 128 C
				$barcode = new Barcode\Code128($code, 'C', true);
				break;

			case 'C39':  // CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
				$barcode = new Barcode\Code39($this->sanitizeCode($code), ($pr > 0) ? $pr : 2.5, false, false);
				break;

			case 'C39+': // CODE 39 with checksum
				$barcode = new Barcode\Code39($this->sanitizeCode($code), ($pr > 0) ? $pr : 2.5, false, true);
				break;

			case 'C39E': // CODE 39 EXTENDED
				$barcode = new Barcode\Code39($this->sanitizeCode($code), ($pr > 0) ? $pr : 2.5, true, false);
				break;

			case 'C39E+': // CODE 39 EXTENDED + CHECKSUM
				$barcode = new Barcode\Code39($this->sanitizeCode($code), ($pr > 0) ? $pr : 2.5, true, true);
				break;

			case 'S25':  // Standard 2 of 5
				$barcode = new Barcode\S25($code, false);
				break;

			case 'S25+': // Standard 2 of 5 + CHECKSUM
				$barcode = new Barcode\S25($code, true);
				break;

			case 'I25':  // Interleaved 2 of 5
				$barcode = new Barcode\I25($code, 0, ($pr > 0) ? $pr : 2.5, false);
				break;

			case 'I25+': // Interleaved 2 of 5 + CHECKSUM
				$barcode = new Barcode\I25($code, 0, ($pr > 0) ? $pr : 2.5, true);
				break;

			case 'I25B':  // Interleaved 2 of 5 + Bearer bars
				$barcode = new Barcode\I25($code, 2, ($pr > 0) ? $pr : 2.5, false);
				break;

			case 'I25B+': // Interleaved 2 of 5 + CHECKSUM + Bearer bars
				$barcode = new Barcode\I25($code, 2, ($pr > 0) ? $pr : 2.5, true);
				break;
		}

		return $barcode;
	}

	private function sanitizeCode($code)
	{
		$code = str_replace(chr(194) . chr(160), ' ', $code); // mPDF 5.3.95  (for utf-8 encoded)
		$code = str_replace(chr(160), ' ', $code); // mPDF 5.3.95	(for win-1252)

		return $code;
	}

}
