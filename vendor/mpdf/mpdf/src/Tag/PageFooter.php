<?php

namespace Mpdf\Tag;

use Mpdf\Mpdf;

class PageFooter extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();
		$this->mpdf->ignorefollowingspaces = true;
		$pname = '_nonhtmldefault';
		if ($attr['NAME']) {
			$pname = $attr['NAME'];
		} // mPDF 6

		$p = []; // mPDF 6
		$p['L'] = [];
		$p['C'] = [];
		$p['R'] = [];
		$p['L']['font-style'] = '';
		$p['C']['font-style'] = '';
		$p['R']['font-style'] = '';

		if (isset($attr['CONTENT-LEFT'])) {
			$p['L']['content'] = $attr['CONTENT-LEFT'];
		}
		if (isset($attr['CONTENT-CENTER'])) {
			$p['C']['content'] = $attr['CONTENT-CENTER'];
		}
		if (isset($attr['CONTENT-RIGHT'])) {
			$p['R']['content'] = $attr['CONTENT-RIGHT'];
		}

		if (isset($attr['HEADER-STYLE']) || isset($attr['FOOTER-STYLE'])) { // font-family,size,weight,style,color
			if ($tag === 'PAGEHEADER') {
				$properties = $this->cssManager->readInlineCSS($attr['HEADER-STYLE']);
			} else {
				$properties = $this->cssManager->readInlineCSS($attr['FOOTER-STYLE']);
			}
			if (isset($properties['FONT-FAMILY'])) {
				$p['L']['font-family'] = $properties['FONT-FAMILY'];
				$p['C']['font-family'] = $properties['FONT-FAMILY'];
				$p['R']['font-family'] = $properties['FONT-FAMILY'];
			}
			if (isset($properties['FONT-SIZE'])) {
				$p['L']['font-size'] = $this->sizeConverter->convert($properties['FONT-SIZE']) * Mpdf::SCALE;
				$p['C']['font-size'] = $this->sizeConverter->convert($properties['FONT-SIZE']) * Mpdf::SCALE;
				$p['R']['font-size'] = $this->sizeConverter->convert($properties['FONT-SIZE']) * Mpdf::SCALE;
			}
			if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT'] === 'bold') {
				$p['L']['font-style'] = 'B';
				$p['C']['font-style'] = 'B';
				$p['R']['font-style'] = 'B';
			}
			if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE'] === 'italic') {
				$p['L']['font-style'] .= 'I';
				$p['C']['font-style'] .= 'I';
				$p['R']['font-style'] .= 'I';
			}
			if (isset($properties['COLOR'])) {
				$p['L']['color'] = $properties['COLOR'];
				$p['C']['color'] = $properties['COLOR'];
				$p['R']['color'] = $properties['COLOR'];
			}
		}
		if (isset($attr['HEADER-STYLE-LEFT']) || isset($attr['FOOTER-STYLE-LEFT'])) {
			if ($tag === 'PAGEHEADER') {
				$properties = $this->cssManager->readInlineCSS($attr['HEADER-STYLE-LEFT']);
			} else {
				$properties = $this->cssManager->readInlineCSS($attr['FOOTER-STYLE-LEFT']);
			}
			if (isset($properties['FONT-FAMILY'])) {
				$p['L']['font-family'] = $properties['FONT-FAMILY'];
			}
			if (isset($properties['FONT-SIZE'])) {
				$p['L']['font-size'] = $this->sizeConverter->convert($properties['FONT-SIZE']) * Mpdf::SCALE;
			}
			if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT'] === 'bold') {
				$p['L']['font-style'] = 'B';
			}
			if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE'] === 'italic') {
				$p['L']['font-style'] .='I';
			}
			if (isset($properties['COLOR'])) {
				$p['L']['color'] = $properties['COLOR'];
			}
		}
		if (isset($attr['HEADER-STYLE-CENTER']) || isset($attr['FOOTER-STYLE-CENTER'])) {
			if ($tag === 'PAGEHEADER') {
				$properties = $this->cssManager->readInlineCSS($attr['HEADER-STYLE-CENTER']);
			} else {
				$properties = $this->cssManager->readInlineCSS($attr['FOOTER-STYLE-CENTER']);
			}
			if (isset($properties['FONT-FAMILY'])) {
				$p['C']['font-family'] = $properties['FONT-FAMILY'];
			}
			if (isset($properties['FONT-SIZE'])) {
				$p['C']['font-size'] = $this->sizeConverter->convert($properties['FONT-SIZE']) * Mpdf::SCALE;
			}
			if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT'] === 'bold') {
				$p['C']['font-style'] = 'B';
			}
			if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE'] === 'italic') {
				$p['C']['font-style'] .= 'I';
			}
			if (isset($properties['COLOR'])) {
				$p['C']['color'] = $properties['COLOR'];
			}
		}
		if (isset($attr['HEADER-STYLE-RIGHT']) || isset($attr['FOOTER-STYLE-RIGHT'])) {
			if ($tag === 'PAGEHEADER') {
				$properties = $this->cssManager->readInlineCSS($attr['HEADER-STYLE-RIGHT']);
			} else {
				$properties = $this->cssManager->readInlineCSS($attr['FOOTER-STYLE-RIGHT']);
			}
			if (isset($properties['FONT-FAMILY'])) {
				$p['R']['font-family'] = $properties['FONT-FAMILY'];
			}
			if (isset($properties['FONT-SIZE'])) {
				$p['R']['font-size'] = $this->sizeConverter->convert($properties['FONT-SIZE']) * Mpdf::SCALE;
			}
			if (isset($properties['FONT-WEIGHT']) && $properties['FONT-WEIGHT'] === 'bold') {
				$p['R']['font-style'] = 'B';
			}
			if (isset($properties['FONT-STYLE']) && $properties['FONT-STYLE'] === 'italic') {
				$p['R']['font-style'] .= 'I';
			}
			if (isset($properties['COLOR'])) {
				$p['R']['color'] = $properties['COLOR'];
			}
		}
		if (!empty($attr['LINE'])) { // 0|1|on|off
			$lineset = 0;
			if ($attr['LINE'] == '1' || strtoupper($attr['LINE']) === 'ON') {
				$lineset = 1;
			}
			$p['line'] = $lineset;
		}
		// mPDF 6
		if ($tag === 'PAGEHEADER') {
			$this->mpdf->DefHeaderByName($pname, $p);
		} else {
			$this->mpdf->DefFooterByName($pname, $p);
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
