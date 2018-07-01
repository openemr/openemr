<?php

namespace Mpdf\Tag;

class SetHtmlPageFooter extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();
		$this->mpdf->ignorefollowingspaces = true;

		$pname = '_default';
		if (!empty($attr['NAME'])) {
			$pname = $attr['NAME'];
		} elseif ($tag === 'SETPAGEHEADER' || $tag === 'SETPAGEFOOTER') {
			$pname = '_nonhtmldefault';
		} // mPDF 6

		if (!empty($attr['PAGE'])) {  // O|odd|even|E|ALL|[blank]
			$side = 'odd';
			if (strtoupper($attr['PAGE']) === 'O' || strtoupper($attr['PAGE']) === 'ODD') {
				$side = 'odd';
			} elseif (strtoupper($attr['PAGE']) === 'E' || strtoupper($attr['PAGE']) === 'EVEN') {
				$side = 'even';
			} elseif (strtoupper($attr['PAGE']) === 'ALL') {
				$side = 'both';
			}
		} else {
			$side = 'odd';
		}
		if (!empty($attr['VALUE'])) {  // -1|1|on|off
			$set = 1;
			if ($attr['VALUE'] == '1' || strtoupper($attr['VALUE']) === 'ON') {
				$set = 1;
			} elseif ($attr['VALUE'] == '-1' || strtoupper($attr['VALUE']) === 'OFF') {
				$set = 0;
			}
		} else {
			$set = 1;
		}
		$write = 0;
		if (!empty($attr['SHOW-THIS-PAGE']) && ($tag === 'SETHTMLPAGEHEADER' || $tag === 'SETPAGEHEADER')) {
			$write = 1;
		}
		if ($side === 'odd' || $side === 'both') {
			if ($set && ($tag === 'SETHTMLPAGEHEADER' || $tag === 'SETPAGEHEADER')) {
				$this->mpdf->SetHTMLHeader($this->mpdf->pageHTMLheaders[$pname], 'O', $write);
			} elseif ($set && ($tag === 'SETHTMLPAGEFOOTER' || $tag === 'SETPAGEFOOTER')) {
				$this->mpdf->SetHTMLFooter($this->mpdf->pageHTMLfooters[$pname], 'O');
			} elseif ($tag === 'SETHTMLPAGEHEADER' || $tag === 'SETPAGEHEADER') {
				$this->mpdf->SetHTMLHeader('', 'O');
			} else {
				$this->mpdf->SetHTMLFooter('', 'O');
			}
		}
		if ($side === 'even' || $side === 'both') {
			if ($set && ($tag === 'SETHTMLPAGEHEADER' || $tag === 'SETPAGEHEADER')) {
				$this->mpdf->SetHTMLHeader($this->mpdf->pageHTMLheaders[$pname], 'E', $write);
			} elseif ($set && ($tag === 'SETHTMLPAGEFOOTER' || $tag === 'SETPAGEFOOTER')) {
				$this->mpdf->SetHTMLFooter($this->mpdf->pageHTMLfooters[$pname], 'E');
			} elseif ($tag === 'SETHTMLPAGEHEADER' || $tag === 'SETPAGEHEADER') {
				$this->mpdf->SetHTMLHeader('', 'E');
			} else {
				$this->mpdf->SetHTMLFooter('', 'E');
			}
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
