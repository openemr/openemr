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
class ColorTable
{

	var $m_nColors;

	var $m_arColors;

	public function __construct()
	{
		unset($this->m_nColors);
		unset($this->m_arColors);
	}

	function load($lpData, $num)
	{
		$this->m_nColors = 0;
		$this->m_arColors = [];

		for ($i = 0; $i < $num; $i++) {
			$rgb = substr($lpData, $i * 3, 3);
			if (strlen($rgb) < 3) {
				return false;
			}

			$this->m_arColors[] = (ord($rgb[2]) << 16) + (ord($rgb[1]) << 8) + ord($rgb[0]);
			$this->m_nColors++;
		}

		return true;
	}

	function toString()
	{
		$ret = "";

		for ($i = 0; $i < $this->m_nColors; $i++) {
			$ret .=
				chr(($this->m_arColors[$i] & 0x000000FF)) . // R
				chr(($this->m_arColors[$i] & 0x0000FF00) >> 8) . // G
				chr(($this->m_arColors[$i] & 0x00FF0000) >> 16);  // B
		}

		return $ret;
	}

	function colorIndex($rgb)
	{
		$rgb = intval($rgb) & 0xFFFFFF;
		$r1 = ($rgb & 0x0000FF);
		$g1 = ($rgb & 0x00FF00) >> 8;
		$b1 = ($rgb & 0xFF0000) >> 16;
		$idx = -1;

		for ($i = 0; $i < $this->m_nColors; $i++) {
			$r2 = ($this->m_arColors[$i] & 0x000000FF);
			$g2 = ($this->m_arColors[$i] & 0x0000FF00) >> 8;
			$b2 = ($this->m_arColors[$i] & 0x00FF0000) >> 16;
			$d = abs($r2 - $r1) + abs($g2 - $g1) + abs($b2 - $b1);

			if (($idx == -1) || ($d < $dif)) {
				$idx = $i;
				$dif = $d;
			}
		}

		return $idx;
	}
}
