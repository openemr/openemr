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
class FileHeader
{

	var $m_lpVer;

	var $m_nWidth;

	var $m_nHeight;

	var $m_bGlobalClr;

	var $m_nColorRes;

	var $m_bSorted;

	var $m_nTableSize;

	var $m_nBgColor;

	var $m_nPixelRatio;

	/**
	 * @var \Mpdf\Gif\ColorTable
	 */
	var $m_colorTable;

	public function __construct()
	{
		unset($this->m_lpVer);
		unset($this->m_nWidth);
		unset($this->m_nHeight);
		unset($this->m_bGlobalClr);
		unset($this->m_nColorRes);
		unset($this->m_bSorted);
		unset($this->m_nTableSize);
		unset($this->m_nBgColor);
		unset($this->m_nPixelRatio);
		unset($this->m_colorTable);
	}

	function load($lpData, &$hdrLen)
	{
		$hdrLen = 0;

		$this->m_lpVer = substr($lpData, 0, 6);
		if (($this->m_lpVer <> "GIF87a") && ($this->m_lpVer <> "GIF89a")) {
			return false;
		}

		$this->m_nWidth = $this->w2i(substr($lpData, 6, 2));
		$this->m_nHeight = $this->w2i(substr($lpData, 8, 2));
		if (!$this->m_nWidth || !$this->m_nHeight) {
			return false;
		}

		$b = ord(substr($lpData, 10, 1));
		$this->m_bGlobalClr = ($b & 0x80) ? true : false;
		$this->m_nColorRes = ($b & 0x70) >> 4;
		$this->m_bSorted = ($b & 0x08) ? true : false;
		$this->m_nTableSize = 2 << ($b & 0x07);
		$this->m_nBgColor = ord(substr($lpData, 11, 1));
		$this->m_nPixelRatio = ord(substr($lpData, 12, 1));
		$hdrLen = 13;

		if ($this->m_bGlobalClr) {
			$this->m_colorTable = new ColorTable();
			if (!$this->m_colorTable->load(substr($lpData, $hdrLen), $this->m_nTableSize)) {
				return false;
			}
			$hdrLen += 3 * $this->m_nTableSize;
		}

		return true;
	}

	function w2i($str)
	{
		return ord(substr($str, 0, 1)) + (ord(substr($str, 1, 1)) << 8);
	}
}
