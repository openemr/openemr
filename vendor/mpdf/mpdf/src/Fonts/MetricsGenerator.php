<?php

namespace Mpdf\Fonts;

use Mpdf\TTFontFile;

class MetricsGenerator
{

	private $fontCache;

	private $fontDescriptor;

	public function __construct(FontCache $fontCache, $fontDescriptor)
	{
		$this->fontCache = $fontCache;
		$this->fontDescriptor = $fontDescriptor;
	}

	public function generateMetrics($ttffile, $ttfstat, $fontkey, $TTCfontID, $debugfonts, $BMPonly, $useOTL, $fontUseOTL)
	{
		$ttf = new TTFontFile($this->fontCache, $this->fontDescriptor);

		$ttf->getMetrics($ttffile, $fontkey, $TTCfontID, $debugfonts, $BMPonly, $useOTL); // mPDF 5.7.1
		$cw = $ttf->charWidths;

		$kerninfo = $ttf->kerninfo;

		$haskerninfo = false;
		if ($kerninfo) {
			$haskerninfo = true;
		}

		$haskernGPOS = $ttf->haskernGPOS;
		$hassmallcapsGSUB = $ttf->hassmallcapsGSUB;
		$name = preg_replace('/[ ()]/', '', $ttf->fullName);
		$sip = $ttf->sipset;
		$smp = $ttf->smpset;
		// mPDF 6
		$GSUBScriptLang = $ttf->GSUBScriptLang;
		$GSUBFeatures = $ttf->GSUBFeatures;
		$GSUBLookups = $ttf->GSUBLookups;
		$rtlPUAstr = $ttf->rtlPUAstr;
		$GPOSScriptLang = $ttf->GPOSScriptLang;
		$GPOSFeatures = $ttf->GPOSFeatures;
		$GPOSLookups = $ttf->GPOSLookups;
		$glyphIDtoUni = $ttf->glyphIDtoUni;

		$desc = [
			'CapHeight' => round($ttf->capHeight),
			'XHeight' => round($ttf->xHeight),
			'FontBBox' => '[' . round($ttf->bbox[0]) . " " . round($ttf->bbox[1]) . " " . round($ttf->bbox[2]) . " " . round($ttf->bbox[3]) . ']', /* FontBBox from head table */
			/* 		'MaxWidth' => round($ttf->advanceWidthMax),	// AdvanceWidthMax from hhea table	NB ArialUnicode MS = 31990 ! */
			'Flags' => $ttf->flags,
			'Ascent' => round($ttf->ascent),
			'Descent' => round($ttf->descent),
			'Leading' => round($ttf->lineGap),
			'ItalicAngle' => $ttf->italicAngle,
			'StemV' => round($ttf->stemV),
			'MissingWidth' => round($ttf->defaultWidth)
		];
		$panose = '';
		if (count($ttf->panose)) {
			$panoseArray = array_merge([$ttf->sFamilyClass, $ttf->sFamilySubClass], $ttf->panose);
			foreach ($panoseArray as $value) {
				$panose .= ' ' . dechex($value);
			}
		}
		$unitsPerEm = round($ttf->unitsPerEm);
		$up = round($ttf->underlinePosition);
		$ut = round($ttf->underlineThickness);
		$strp = round($ttf->strikeoutPosition); // mPDF 6
		$strs = round($ttf->strikeoutSize); // mPDF 6
		$originalsize = $ttfstat['size'] + 0;
		$type = 'TTF';
		//Generate metrics .php file
		$s = '<?php' . "\n";
		$s .= '$name=\'' . $name . "';\n";
		$s .= '$type=\'' . $type . "';\n";
		$s .= '$desc=' . var_export($desc, true) . ";\n";
		$s .= '$unitsPerEm=' . $unitsPerEm . ";\n";
		$s .= '$up=' . $up . ";\n";
		$s .= '$ut=' . $ut . ";\n";
		$s .= '$strp=' . $strp . ";\n"; // mPDF 6
		$s .= '$strs=' . $strs . ";\n"; // mPDF 6
		$s .= '$ttffile=\'' . $ttffile . "';\n";
		$s .= '$TTCfontID=\'' . $TTCfontID . "';\n";
		$s .= '$originalsize=' . $originalsize . ";\n";
		if ($sip) {
			$s .= '$sip=true;' . "\n";
		} else {
			$s .= '$sip=false;' . "\n";
		}
		if ($smp) {
			$s .= '$smp=true;' . "\n";
		} else {
			$s .= '$smp=false;' . "\n";
		}
		if ($BMPonly) {
			$s .= '$BMPselected=true;' . "\n";
		} else {
			$s .= '$BMPselected=false;' . "\n";
		}
		$s .= '$fontkey=\'' . $fontkey . "';\n";
		$s .= '$panose=\'' . $panose . "';\n";
		if ($haskerninfo) {
			$s .= '$haskerninfo=true;' . "\n";
		} else {
			$s .= '$haskerninfo=false;' . "\n";
		}
		if ($haskernGPOS) {
			$s .= '$haskernGPOS=true;' . "\n";
		} else {
			$s .= '$haskernGPOS=false;' . "\n";
		}
		if ($hassmallcapsGSUB) {
			$s .= '$hassmallcapsGSUB=true;' . "\n";
		} else {
			$s .= '$hassmallcapsGSUB=false;' . "\n";
		}
		$s .= '$fontmetrics=\'' . $this->fontDescriptor . "';\n"; // mPDF 6

		$s .= '// TypoAscender/TypoDescender/TypoLineGap = ' . round($ttf->typoAscender) . ', ' . round($ttf->typoDescender) . ', ' . round($ttf->typoLineGap) . "\n";
		$s .= '// usWinAscent/usWinDescent = ' . round($ttf->usWinAscent) . ', ' . round(-$ttf->usWinDescent) . "\n";
		$s .= '// hhea Ascent/Descent/LineGap = ' . round($ttf->hheaascent) . ', ' . round($ttf->hheadescent) . ', ' . round($ttf->hhealineGap) . "\n";

		//  mPDF 5.7.1
		if ($fontUseOTL) {
			$s .= '$useOTL=' . $fontUseOTL . ';' . "\n";
		} else {
			$s .= '$useOTL=0x0000;' . "\n";
		}
		if ($rtlPUAstr) {
			$s .= '$rtlPUAstr=\'' . $rtlPUAstr . "';\n";
		} else {
			$s .= '$rtlPUAstr=\'\';' . "\n";
		}
		if (count($GSUBScriptLang)) {
			$s .= '$GSUBScriptLang=' . var_export($GSUBScriptLang, true) . ";\n";
		}
		if (count($GSUBFeatures)) {
			$s .= '$GSUBFeatures=' . var_export($GSUBFeatures, true) . ";\n";
		}
		if (count($GSUBLookups)) {
			$s .= '$GSUBLookups=' . var_export($GSUBLookups, true) . ";\n";
		}
		if (count($GPOSScriptLang)) {
			$s .= '$GPOSScriptLang=' . var_export($GPOSScriptLang, true) . ";\n";
		}
		if (count($GPOSFeatures)) {
			$s .= '$GPOSFeatures=' . var_export($GPOSFeatures, true) . ";\n";
		}
		if (count($GPOSLookups)) {
			$s .= '$GPOSLookups=' . var_export($GPOSLookups, true) . ";\n";
		}
		if ($kerninfo) {
			$s .= '$kerninfo=' . var_export($kerninfo, true) . ";\n";
		}

		$this->fontCache->write($fontkey . '.mtx.php', $s);
		$this->fontCache->binaryWrite($fontkey . '.cw.dat', $cw);
		$this->fontCache->binaryWrite($fontkey . '.gid.dat', $glyphIDtoUni);

		if ($this->fontCache->has($fontkey . '.cgm')) {
			$this->fontCache->remove($fontkey . '.cgm');
		}

		if ($this->fontCache->has($fontkey . '.z')) {
			$this->fontCache->remove($fontkey . '.z');
		}

		if ($this->fontCache->has($fontkey . '.cw127.php')) {
			$this->fontCache->remove($fontkey . '.cw127.php');
		}

		if ($this->fontCache->has($fontkey . '.cw')) {
			$this->fontCache->remove($fontkey . '.cw');
		}

		unset($ttf);
	}
}
