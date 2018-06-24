<?php

namespace Mpdf\Gif;

/**
 * GIF Util - (C) 2003 Yamasoft (S/C)
 *
 * All Rights Reserved
 *
 * This file can be freely copied, distributed, modified, updated by anyone under the only
 * condition to leave the original address (Yamasoft, http://www.yamasoft.com) and this header.
 *
 * @link http://www.yamasoft.com
 */
class Image
{

	var $m_disp;

	var $m_bUser;

	var $m_bTrans;

	var $m_nDelay;

	var $m_nTrans;

	var $m_lpComm;

	var $m_gih;

	var $m_data;

	var $m_lzw;

	public function __construct()
	{
		unset($this->m_disp);
		unset($this->m_bUser);
		unset($this->m_bTrans);
		unset($this->m_nDelay);
		unset($this->m_nTrans);
		unset($this->m_lpComm);
		unset($this->m_data);
		$this->m_gih = new ImageHeader();
		$this->m_lzw = new Lzw();
	}

	function load($data, &$datLen)
	{
		$datLen = 0;

		while (true) {
			$b = ord($data[0]);
			$data = substr($data, 1);
			$datLen++;

			switch ($b) {
				case 0x21: // Extension
					$len = 0;
					if (!$this->skipExt($data, $len)) {
						return false;
					}
					$datLen += $len;
					break;

				case 0x2C: // Image
					// LOAD HEADER & COLOR TABLE
					$len = 0;
					if (!$this->m_gih->load($data, $len)) {
						return false;
					}
					$data = substr($data, $len);
					$datLen += $len;

					// ALLOC BUFFER
					$len = 0;

					if (!($this->m_data = $this->m_lzw->deCompress($data, $len))) {
						return false;
					}

					$data = substr($data, $len);
					$datLen += $len;

					if ($this->m_gih->m_bInterlace) {
						$this->deInterlace();
					}

					return true;

				case 0x3B: // EOF
				default:
					return false;
			}
		}
		return false;
	}

	function skipExt(&$data, &$extLen)
	{
		$extLen = 0;

		$b = ord($data[0]);
		$data = substr($data, 1);
		$extLen++;

		switch ($b) {
			case 0xF9: // Graphic Control
				$b = ord($data[1]);
				$this->m_disp = ($b & 0x1C) >> 2;
				$this->m_bUser = ($b & 0x02) ? true : false;
				$this->m_bTrans = ($b & 0x01) ? true : false;
				$this->m_nDelay = $this->w2i(substr($data, 2, 2));
				$this->m_nTrans = ord($data[4]);
				break;

			case 0xFE: // Comment
				$this->m_lpComm = substr($data, 1, ord($data[0]));
				break;

			case 0x01: // Plain text
				break;

			case 0xFF: // Application
				break;
		}

		// SKIP DEFAULT AS DEFS MAY CHANGE
		$b = ord($data[0]);
		$data = substr($data, 1);
		$extLen++;
		while ($b > 0) {
			$data = substr($data, $b);
			$extLen += $b;
			$b = ord($data[0]);
			$data = substr($data, 1);
			$extLen++;
		}
		return true;
	}

	function w2i($str)
	{
		return ord(substr($str, 0, 1)) + (ord(substr($str, 1, 1)) << 8);
	}

	function deInterlace()
	{
		$data = $this->m_data;

		for ($i = 0; $i < 4; $i++) {
			switch ($i) {
				case 0:
					$s = 8;
					$y = 0;
					break;

				case 1:
					$s = 8;
					$y = 4;
					break;

				case 2:
					$s = 4;
					$y = 2;
					break;

				case 3:
					$s = 2;
					$y = 1;
					break;
			}

			for (; $y < $this->m_gih->m_nHeight; $y += $s) {
				$lne = substr($this->m_data, 0, $this->m_gih->m_nWidth);
				$this->m_data = substr($this->m_data, $this->m_gih->m_nWidth);

				$data = substr($data, 0, $y * $this->m_gih->m_nWidth) .
					$lne .
					substr($data, ($y + 1) * $this->m_gih->m_nWidth);
			}
		}

		$this->m_data = $data;
	}
}
