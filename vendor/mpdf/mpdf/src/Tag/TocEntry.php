<?php

namespace Mpdf\Tag;

class TocEntry extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		if (!empty($attr['CONTENT'])) {
			$objattr = [];
			$objattr['CONTENT'] = htmlspecialchars_decode($attr['CONTENT'], ENT_QUOTES);
			$objattr['type'] = 'toc';
			$objattr['vertical-align'] = 'T';
			if (!empty($attr['LEVEL'])) {
				$objattr['toclevel'] = $attr['LEVEL'];
			} else {
				$objattr['toclevel'] = 0;
			}
			if (!empty($attr['NAME'])) {
				$objattr['toc_id'] = $attr['NAME'];
			} else {
				$objattr['toc_id'] = 0;
			}
			$e = "\xbb\xa4\xactype=toc,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
			if ($this->mpdf->tableLevel) {
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = [$e];
			} // *TABLES*
			else { // *TABLES*
				$this->mpdf->textbuffer[] = [$e];
			} // *TABLES*
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
