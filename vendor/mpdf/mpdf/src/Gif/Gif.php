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
class Gif
{

	var $m_gfh;

	var $m_lpData;

	var $m_img;

	var $m_bLoaded;

	public function __construct()
	{
		$this->m_gfh = new FileHeader();
		$this->m_img = new Image();
		$this->m_lpData = '';
		$this->m_bLoaded = false;
	}

	function ClearData()
	{
		$this->m_lpData = '';
		unset($this->m_img->m_data);
		unset($this->m_img->m_lzw->Next);
		unset($this->m_img->m_lzw->Vals);
		unset($this->m_img->m_lzw->Stack);
		unset($this->m_img->m_lzw->Buf);
	}

	function loadFile(&$data, $iIndex)
	{
		if ($iIndex < 0) {
			return false;
		}
		$this->m_lpData = $data;

		// GET FILE HEADER
		$len = 0;
		if (!$this->m_gfh->load($this->m_lpData, $len)) {
			return false;
		}

		$this->m_lpData = substr($this->m_lpData, $len);

		do {
			$imgLen = 0;
			if (!$this->m_img->load($this->m_lpData, $imgLen)) {
				return false;
			}
			$this->m_lpData = substr($this->m_lpData, $imgLen);
		} while ($iIndex-- > 0);

		$this->m_bLoaded = true;
		return true;
	}
}
