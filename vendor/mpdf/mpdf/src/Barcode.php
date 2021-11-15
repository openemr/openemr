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
		switch (strtoupper($type)) {
			case 'ISBN':
			case 'ISSN':
			case 'EAN13': // EAN 13
				return new Barcode\EanUpc($code, 13, 11, 7, 0.33, 25.93);

			case 'UPCA': // UPC-A
				return new Barcode\EanUpc($code, 12, 9, 9, 0.33, 25.91);

			case 'UPCE': // UPC-E
				return new Barcode\EanUpc($code, 6, 9, 7, 0.33, 25.93);

			case 'EAN8': // EAN 8
				return new Barcode\EanUpc($code, 8, 7, 7, 0.33, 21.64);

			case 'EAN2': // 2-Digits UPC-Based Extention
				return new Barcode\EanExt($code, 2, 7, 7, 0.33, 20, 9);

			case 'EAN5': // 5-Digits UPC-Based Extention
				return new Barcode\EanExt($code, 5, 7, 7, 0.33, 20, 9);

			case 'IMB': // IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				return new Barcode\Imb($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, ['D' => 2, 'A' => 2, 'F' => 3, 'T' => 1]);

			case 'RM4SCC': // RM4SCC (Royal Mail 4-state Customer Code) - CBC (Customer Bar Code)
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				return new Barcode\Rm4Scc($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, ['D' => 5, 'A' => 5, 'F' => 8, 'T' => 2]);

			case 'KIX': // KIX (Klant index - Customer index)
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				return new Barcode\Rm4Scc($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, ['D' => 5, 'A' => 5, 'F' => 8, 'T' => 2], true);

			case 'POSTNET': // POSTNET
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				return new Barcode\Postnet($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, false);

			case 'PLANET': // PLANET
				$xdim = 0.508; // Nominal value for X-dim (bar width) in mm (spec.)
				$bpi = 22; // Bars per inch
				return new Barcode\Postnet($code, $xdim, ((25.4 / $bpi) - $xdim) / $xdim, true);

			case 'C93': // CODE 93 - USS-93
				return new Barcode\Code93($code);

			case 'CODE11': // CODE 11
				return new Barcode\Code11($code, ($pr > 0) ? $pr : 3);

			case 'MSI':  // MSI (Variation of Plessey code)
				return new Barcode\Msi($code, false);

			case 'MSI+': // MSI + CHECKSUM (modulo 11)
				return new Barcode\Msi($code, true);

			case 'CODABAR': // CODABAR
				return new Barcode\Codabar($code, ($pr > 0) ? $pr : 2.5);

			case 'C128A': // CODE 128 A
				return new Barcode\Code128($code, 'A');

			case 'C128B': // CODE 128 B
				return new Barcode\Code128($code, 'B');

			case 'C128C':  // CODE 128 C
				return new Barcode\Code128($code, 'C');

			case 'C128RAW':  // CODE 128 RAW -- code is a space separated list of codes with startcode but without checkdigit,stop,end ex: "105 12 34"
				return new Barcode\Code128($code, 'RAW');

			case 'EAN128A':  // EAN 128 A
				return new Barcode\Code128($code, 'A', true);

			case 'EAN128B':  // EAN 128 B
				return new Barcode\Code128($code, 'B', true);

			case 'EAN128C': // EAN 128 C
				return new Barcode\Code128($code, 'C', true);

			case 'C39':  // CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
				return new Barcode\Code39($this->sanitizeCode($code), ($pr > 0) ? $pr : 2.5, false, false);

			case 'C39+': // CODE 39 with checksum
				return new Barcode\Code39($this->sanitizeCode($code), ($pr > 0) ? $pr : 2.5, false, true);

			case 'C39E': // CODE 39 EXTENDED
				return new Barcode\Code39($this->sanitizeCode($code), ($pr > 0) ? $pr : 2.5, true, false);

			case 'C39E+': // CODE 39 EXTENDED + CHECKSUM
				return new Barcode\Code39($this->sanitizeCode($code), ($pr > 0) ? $pr : 2.5, true, true);

			case 'S25':  // Standard 2 of 5
				return new Barcode\S25($code, false);

			case 'S25+': // Standard 2 of 5 + CHECKSUM
				return new Barcode\S25($code, true);

			case 'I25':  // Interleaved 2 of 5
				return new Barcode\I25($code, 0, ($pr > 0) ? $pr : 2.5, false);

			case 'I25+': // Interleaved 2 of 5 + CHECKSUM
				return new Barcode\I25($code, 0, ($pr > 0) ? $pr : 2.5, true);

			case 'I25B':  // Interleaved 2 of 5 + Bearer bars
				return new Barcode\I25($code, 2, ($pr > 0) ? $pr : 2.5, false);

			case 'I25B+': // Interleaved 2 of 5 + CHECKSUM + Bearer bars
				return new Barcode\I25($code, 2, ($pr > 0) ? $pr : 2.5, true);
		}

		return false;
	}

	private function sanitizeCode($code)
	{
		$code = str_replace(chr(194) . chr(160), ' ', $code); // mPDF 5.3.95  (for utf-8 encoded)
		$code = str_replace(chr(160), ' ', $code); // mPDF 5.3.95	(for win-1252)

		return $code;
	}

}
