<?php

namespace Mpdf\Writer;

use Mpdf\Strict;

use Mpdf\Mpdf;
use Mpdf\Pdf\Protection;

final class BaseWriter
{

	use Strict;

	/**
	 * @var \Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \Mpdf\Pdf\Protection
	 */
	private $protection;

	public function __construct(Mpdf $mpdf, Protection $protection)
	{
		$this->mpdf = $mpdf;
		$this->protection = $protection;
	}

	public function write($s, $ln = true)
	{
		if ($this->mpdf->state === 2) {
			$this->endPage($s, $ln);
		} else {
			$this->mpdf->buffer .= $s . ($ln ? "\n" : '');
		}
	}

	public function string($s)
	{
		if ($this->mpdf->encrypted) {
			$s = $this->protection->rc4($this->protection->objectKey($this->mpdf->currentObjectNumber), $s);
		}

		return '(' . $this->escape($s) . ')';
	}

	public function object($obj_id = false, $onlynewobj = false)
	{
		if (!$obj_id) {
			$obj_id = ++$this->mpdf->n;
		}

		// Begin a new object
		if (!$onlynewobj) {
			$this->mpdf->offsets[$obj_id] = strlen($this->mpdf->buffer);
			$this->write($obj_id . ' 0 obj');
			$this->mpdf->currentObjectNumber = $obj_id; // for later use with encryption
		}
	}

	public function stream($s)
	{
		if ($this->mpdf->encrypted) {
			$s = $this->protection->rc4($this->protection->objectKey($this->mpdf->currentObjectNumber), $s);
		}

		$this->write('stream');
		$this->write($s);
		$this->write('endstream');
	}

	public function utf16BigEndianTextString($s) // _UTF16BEtextstring
	{
		$s = $this->utf8ToUtf16BigEndian($s, true);
		if ($this->mpdf->encrypted) {
			$s = $this->protection->rc4($this->protection->objectKey($this->mpdf->currentObjectNumber), $s);
		}

		return '(' . $this->escape($s) . ')';
	}

	// Converts UTF-8 strings to UTF16-BE.
	public function utf8ToUtf16BigEndian($str, $setbom = true) // UTF8ToUTF16BE
	{
		if ($this->mpdf->checkSIP && preg_match("/([\x{20000}-\x{2FFFF}])/u", $str)) {
			if (!in_array($this->mpdf->currentfontfamily, ['gb', 'big5', 'sjis', 'uhc', 'gbB', 'big5B', 'sjisB', 'uhcB', 'gbI', 'big5I', 'sjisI', 'uhcI',
				'gbBI', 'big5BI', 'sjisBI', 'uhcBI'])) {
				$str = preg_replace("/[\x{20000}-\x{2FFFF}]/u", chr(0), $str);
			}
		}
		if ($this->mpdf->checkSMP && preg_match("/([\x{10000}-\x{1FFFF}])/u", $str)) {
			$str = preg_replace("/[\x{10000}-\x{1FFFF}]/u", chr(0), $str);
		}

		$outstr = ''; // string to be returned
		if ($setbom) {
			$outstr .= "\xFE\xFF"; // Byte Order Mark (BOM)
		}

		$outstr .= mb_convert_encoding($str, 'UTF-16BE', 'UTF-8');

		return $outstr;
	}

	public function escape($s) // _escape
	{
		return strtr($s, [')' => '\\)', '(' => '\\(', '\\' => '\\\\', chr(13) => '\r']);
	}

	public function escapeSlashes($s) // _escapeName
	{
		return strtr($s, ['/' => '#2F']);
	}

	/**
	 * Un-escapes a PDF string
	 *
	 * @param string $s
	 * @return string
	 */
	public function unescape($s)
	{
		$out = '';
		for ($count = 0, $n = strlen($s); $count < $n; $count++) {
			if ($count === $n - 1 || $s[$count] !== '\\') {
				$out .= $s[$count];
			} else {
				switch ($s[++$count]) {
					case ')':
					case '(':
					case '\\':
						$out .= $s[$count];
						break;
					case 'f':
						$out .= chr(0x0C);
						break;
					case 'b':
						$out .= chr(0x08);
						break;
					case 't':
						$out .= chr(0x09);
						break;
					case 'r':
						$out .= chr(0x0D);
						break;
					case 'n':
						$out .= chr(0x0A);
						break;
					case "\r":
						if ($count !== $n - 1 && $s[$count + 1] === "\n") {
							$count++;
						}
						break;
					case "\n":
						break;
					default:
						// Octal-Values
						$ord = ord($s[$count]);
						if ($ord >= ord('0') && $ord <= ord('9')) {
							$oct = ''. $s[$count];
							$ord = ord($s[$count + 1]);
							if ($ord >= ord('0') && $ord <= ord('9')) {
								$oct .= $s[++$count];
								$ord = ord($s[$count + 1]);
								if ($ord >= ord('0') && $ord <= ord('9')) {
									$oct .= $s[++$count];
								}
							}
							$out .= chr(octdec($oct));
						} else {
							$out .= $s[$count];
						}
				}
			}
		}

		return $out;
	}

	private function endPage($s, $ln)
	{
		if ($this->mpdf->bufferoutput) {

			$this->mpdf->headerbuffer.= $s . "\n";

		} elseif ($this->mpdf->ColActive && !$this->mpdf->processingHeader && !$this->mpdf->processingFooter) {

			// Captures everything in buffer for columns; Almost everything is sent from fn. Cell() except:
			// Images sent from Image() or
			// later sent as write($textto) in printbuffer
			// Line()

			if (preg_match('/q \d+\.\d\d+ 0 0 (\d+\.\d\d+) \d+\.\d\d+ \d+\.\d\d+ cm \/(I|FO)\d+ Do Q/', $s, $m)) { // Image data

				$h = ($m[1] / Mpdf::SCALE);
				// Update/overwrite the lowest bottom of printing y value for a column
				$this->mpdf->ColDetails[$this->mpdf->CurrCol]['bottom_margin'] = $this->mpdf->y + $h;

			} elseif ($this->mpdf->tableLevel > 0 && preg_match('/\d+\.\d\d+ \d+\.\d\d+ \d+\.\d\d+ ([\-]{0,1}\d+\.\d\d+) re/', $s, $m)) { // Rect in table

				$h = ($m[1] / Mpdf::SCALE);
				// Update/overwrite the lowest bottom of printing y value for a column
				$this->mpdf->ColDetails[$this->mpdf->CurrCol]['bottom_margin'] = max($this->mpdf->ColDetails[$this->mpdf->CurrCol]['bottom_margin'], $this->mpdf->y + $h);

			} elseif (isset($this->mpdf->ColDetails[$this->mpdf->CurrCol]['bottom_margin'])) {

				$h = $this->mpdf->ColDetails[$this->mpdf->CurrCol]['bottom_margin'] - $this->mpdf->y;

			} else {

				$h = 0;

			}

			if ($h < 0) {
				$h = -$h;
			}

			$this->mpdf->columnbuffer[] = [
				's' => $s, // Text string to output
				'col' => $this->mpdf->CurrCol, // Column when printed
				'x' => $this->mpdf->x, // x when printed
				'y' => $this->mpdf->y, // this->y when printed (after column break)
				'h' => $h        // actual y at bottom when printed = y+h
			];

		} elseif ($this->mpdf->table_rotate && !$this->mpdf->processingHeader && !$this->mpdf->processingFooter) {

			// Captures eveything in buffer for rotated tables;
			$this->mpdf->tablebuffer .= $s . "\n";

		} elseif ($this->mpdf->kwt && !$this->mpdf->processingHeader && !$this->mpdf->processingFooter) {

			// Captures eveything in buffer for keep-with-table (h1-6);
			$this->mpdf->kwt_buffer[] = [
				's' => $s, // Text string to output
				'x' => $this->mpdf->x, // x when printed
				'y' => $this->mpdf->y, // y when printed
			];

		} elseif ($this->mpdf->keep_block_together && !$this->mpdf->processingHeader && !$this->mpdf->processingFooter) {
			// do nothing
		} else {
			$this->mpdf->pages[$this->mpdf->page] .= $s . ($ln ? "\n" : '');
		}
	}

}
