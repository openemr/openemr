<?php

require_once __DIR__ . '/../MpdfException.php';

/* * *****************************************************************************
 * otl_dump class                                                             *
 * ***************************************************************************** */

// Define the value used in the "head" table of a created TTF file
// 0x74727565 "true" for Mac
// 0x00010000 for Windows
// Either seems to work for a font embedded in a PDF file
// when read by Adobe Reader on a Windows PC(!)
if (!defined('_TTF_MAC_HEADER'))
	define("_TTF_MAC_HEADER", false);

// Recalculate correct metadata/profiles when making subset fonts (not SIP/SMP)
// e.g. xMin, xMax, maxNContours
if (!defined('_RECALC_PROFILE'))
	define("_RECALC_PROFILE", false);

// TrueType Font Glyph operators
define("GF_WORDS", (1 << 0));
define("GF_SCALE", (1 << 3));
define("GF_MORE", (1 << 5));
define("GF_XYSCALE", (1 << 6));
define("GF_TWOBYTWO", (1 << 7));

// mPDF 5.7.1
if (!function_exists('unicode_hex')) {

	function unicode_hex($unicode_dec)
	{
		return (sprintf("%05s", strtoupper(dechex($unicode_dec))));
	}

}

class OTLdump
{

	var $GPOSFeatures; // mPDF 5.7.1

	var $GPOSLookups;  // mPDF 5.7.1

	var $GPOSScriptLang; // mPDF 5.7.1

	var $ignoreStrings; // mPDF 5.7.1

	var $MarkAttachmentType; // mPDF 5.7.1

	var $MarkGlyphSets; // mPDF 7.5.1

	var $GlyphClassMarks; // mPDF 5.7.1

	var $GlyphClassLigatures; // mPDF 5.7.1

	var $GlyphClassBases; // mPDF 5.7.1

	var $GlyphClassComponents; // mPDF 5.7.1

	var $GSUBScriptLang; // mPDF 5.7.1

	var $rtlPUAstr; // mPDF 5.7.1

	var $rtlPUAarr; // mPDF 5.7.1

	var $fontkey; // mPDF 5.7.1

	var $useOTL; // mPDF 5.7.1

	var $panose;

	var $maxUni;

	var $sFamilyClass;

	var $sFamilySubClass;

	var $sipset;

	var $smpset;

	var $_pos;

	var $numTables;

	var $searchRange;

	var $entrySelector;

	var $rangeShift;

	var $tables;

	var $otables;

	var $filename;

	var $fh;

	var $glyphPos;

	var $charToGlyph;

	var $ascent;

	var $descent;

	var $name;

	var $familyName;

	var $styleName;

	var $fullName;

	var $uniqueFontID;

	var $unitsPerEm;

	var $bbox;

	var $capHeight;

	var $stemV;

	var $italicAngle;

	var $flags;

	var $underlinePosition;

	var $underlineThickness;

	var $charWidths;

	var $defaultWidth;

	var $maxStrLenRead;

	var $numTTCFonts;

	var $TTCFonts;

	var $maxUniChar;

	var $kerninfo;

	public function __construct(mPDF $mpdf)
	{
		$this->mpdf = $mpdf;
		$this->maxStrLenRead = 200000; // Maximum size of glyf table to read in as string (otherwise reads each glyph from file)
	}

	function getMetrics($file, $fontkey, $TTCfontID = 0, $debug = false, $BMPonly = false, $kerninfo = false, $useOTL = 0, $mode)
	{
		// mPDF 5.7.1
		$this->mode = $mode;
		$this->useOTL = $useOTL; // mPDF 5.7.1
		$this->fontkey = $fontkey; // mPDF 5.7.1
		$this->filename = $file;
		$this->fh = fopen($file, 'rb');

		if (!$this->fh) {
			throw new MpdfException('Can\'t open file ' . $file);
		}

		$this->_pos = 0;
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->kerninfo = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->version = $version = $this->read_ulong();
		$this->panose = array();

		if ($version == 0x4F54544F) {
			throw new MpdfException("Postscript outlines are not supported");
		}

		if ($version == 0x74746366 && !$TTCfontID) {
			throw new MpdfException("ERROR - You must define the TTCfontID for a TrueType Collection in config_fonts.php (" . $file . ")");
		}

		if (!in_array($version, array(0x00010000, 0x74727565)) && !$TTCfontID) {
			throw new MpdfException("Not a TrueType font: version=" . $version);
		}

		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong(); // TTC Header version now
			if (!in_array($version, array(0x00010000, 0x00020000))) {
				throw new MpdfException("ERROR - Error parsing TrueType Collection: version=" . $version . " - " . $file);
			}
			$this->numTTCFonts = $this->read_ulong();
			for ($i = 1; $i <= $this->numTTCFonts; $i++) {
				$this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong(); // TTFont version again now
		}
		$this->readTableDirectory($debug);
		$this->extractInfo($debug, $BMPonly, $kerninfo, $useOTL);
		fclose($this->fh);
	}

	function readTableDirectory($debug = false)
	{
		$this->numTables = $this->read_ushort();
		$this->searchRange = $this->read_ushort();
		$this->entrySelector = $this->read_ushort();
		$this->rangeShift = $this->read_ushort();
		$this->tables = array();
		for ($i = 0; $i < $this->numTables; $i++) {
			$record = array();
			$record['tag'] = $this->read_tag();
			$record['checksum'] = array($this->read_ushort(), $this->read_ushort());
			$record['offset'] = $this->read_ulong();
			$record['length'] = $this->read_ulong();
			$this->tables[$record['tag']] = $record;
		}
		if ($debug)
			$this->checksumTables();
	}

	function checksumTables()
	{
		// Check the checksums for all tables
		foreach ($this->tables AS $t) {
			if ($t['length'] > 0 && $t['length'] < $this->maxStrLenRead) { // 1.02
				$table = $this->get_chunk($t['offset'], $t['length']);
				$checksum = $this->calcChecksum($table);
				if ($t['tag'] == 'head') {
					$up = unpack('n*', substr($table, 8, 4));
					$adjustment[0] = $up[1];
					$adjustment[1] = $up[2];
					$checksum = $this->sub32($checksum, $adjustment);
				}
				$xchecksum = $t['checksum'];
				if ($xchecksum != $checksum) {
					throw new MpdfException(sprintf('TTF file "%s": invalid checksum %s table: %s (expected %s)', $this->filename, dechex($checksum[0]) . dechex($checksum[1]), $t['tag'], dechex($xchecksum[0]) . dechex($xchecksum[1])));
				}
			}
		}
	}

	function sub32($x, $y)
	{
		$xlo = $x[1];
		$xhi = $x[0];
		$ylo = $y[1];
		$yhi = $y[0];
		if ($ylo > $xlo) {
			$xlo += 1 << 16;
			$yhi += 1;
		}
		$reslo = $xlo - $ylo;
		if ($yhi > $xhi) {
			$xhi += 1 << 16;
		}
		$reshi = $xhi - $yhi;
		$reshi = $reshi & 0xFFFF;
		return array($reshi, $reslo);
	}

	function calcChecksum($data)
	{
		if (strlen($data) % 4) {
			$data .= str_repeat("\0", (4 - (strlen($data) % 4)));
		}
		$len = strlen($data);
		$hi = 0x0000;
		$lo = 0x0000;
		for ($i = 0; $i < $len; $i+=4) {
			$hi += (ord($data[$i]) << 8) + ord($data[$i + 1]);
			$lo += (ord($data[$i + 2]) << 8) + ord($data[$i + 3]);
			$hi += ($lo >> 16) & 0xFFFF;
			$lo = $lo & 0xFFFF;
		}
		return array($hi, $lo);
	}

	function get_table_pos($tag)
	{
		$offset = $this->tables[$tag]['offset'];
		$length = $this->tables[$tag]['length'];
		return array($offset, $length);
	}

	function seek($pos)
	{
		$this->_pos = $pos;
		fseek($this->fh, $this->_pos);
	}

	function skip($delta)
	{
		$this->_pos = $this->_pos + $delta;
		fseek($this->fh, $delta, SEEK_CUR);
	}

	function seek_table($tag, $offset_in_table = 0)
	{
		$tpos = $this->get_table_pos($tag);
		$this->_pos = $tpos[0] + $offset_in_table;
		fseek($this->fh, $this->_pos);
		return $this->_pos;
	}

	function read_tag()
	{
		$this->_pos += 4;
		return fread($this->fh, 4);
	}

	function read_short()
	{
		$this->_pos += 2;
		$s = fread($this->fh, 2);
		$a = (ord($s[0]) << 8) + ord($s[1]);
		if ($a & (1 << 15)) {
			$a = ($a - (1 << 16));
		}
		return $a;
	}

	function unpack_short($s)
	{
		$a = (ord($s[0]) << 8) + ord($s[1]);
		if ($a & (1 << 15)) {
			$a = ($a - (1 << 16));
		}
		return $a;
	}

	function read_ushort()
	{
		$this->_pos += 2;
		$s = fread($this->fh, 2);
		return (ord($s[0]) << 8) + ord($s[1]);
	}

	function read_ulong()
	{
		$this->_pos += 4;
		$s = fread($this->fh, 4);
		// if large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0]) * 16777216) + (ord($s[1]) << 16) + (ord($s[2]) << 8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function get_ushort($pos)
	{
		fseek($this->fh, $pos);
		$s = fread($this->fh, 2);
		return (ord($s[0]) << 8) + ord($s[1]);
	}

	function get_ulong($pos)
	{
		fseek($this->fh, $pos);
		$s = fread($this->fh, 4);
		// iF large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0]) * 16777216) + (ord($s[1]) << 16) + (ord($s[2]) << 8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function pack_short($val)
	{
		if ($val < 0) {
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		return pack("n", $val);
	}

	function splice($stream, $offset, $value)
	{
		return substr($stream, 0, $offset) . $value . substr($stream, $offset + strlen($value));
	}

	function _set_ushort($stream, $offset, $value)
	{
		$up = pack("n", $value);
		return $this->splice($stream, $offset, $up);
	}

	function _set_short($stream, $offset, $val)
	{
		if ($val < 0) {
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		$up = pack("n", $val);
		return $this->splice($stream, $offset, $up);
	}

	function get_chunk($pos, $length)
	{
		fseek($this->fh, $pos);
		if ($length < 1) {
			return '';
		}
		return (fread($this->fh, $length));
	}

	function get_table($tag)
	{
		list($pos, $length) = $this->get_table_pos($tag);
		if ($length == 0) {
			return '';
		}
		fseek($this->fh, $pos);
		return (fread($this->fh, $length));
	}

	function add($tag, $data)
	{
		if ($tag == 'head') {
			$data = $this->splice($data, 8, "\0\0\0\0");
		}
		$this->otables[$tag] = $data;
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////

	function extractInfo($debug = false, $BMPonly = false, $kerninfo = false, $useOTL = 0)
	{
		$this->panose = array();
		$this->sFamilyClass = 0;
		$this->sFamilySubClass = 0;
		///////////////////////////////////
		// name - Naming table
		///////////////////////////////////
		$name_offset = $this->seek_table("name");
		$format = $this->read_ushort();
		if ($format != 0 && $format != 1) {
			throw new MpdfException("Unknown name table format " . $format);
		}
		$numRecords = $this->read_ushort();
		$string_data_offset = $name_offset + $this->read_ushort();
		$names = array(1 => '', 2 => '', 3 => '', 4 => '', 6 => '');
		$K = array_keys($names);
		$nameCount = count($names);
		for ($i = 0; $i < $numRecords; $i++) {
			$platformId = $this->read_ushort();
			$encodingId = $this->read_ushort();
			$languageId = $this->read_ushort();
			$nameId = $this->read_ushort();
			$length = $this->read_ushort();
			$offset = $this->read_ushort();
			if (!in_array($nameId, $K))
				continue;
			$N = '';
			if ($platformId == 3 && $encodingId == 1 && $languageId == 0x409) { // Microsoft, Unicode, US English, PS Name
				$opos = $this->_pos;
				$this->seek($string_data_offset + $offset);
				if ($length % 2 != 0) {
					throw new MpdfException("PostScript name is UTF-16BE string of odd length");
				}
				$length /= 2;
				$N = '';
				while ($length > 0) {
					$char = $this->read_ushort();
					$N .= (chr($char));
					$length -= 1;
				}
				$this->_pos = $opos;
				$this->seek($opos);
			} else if ($platformId == 1 && $encodingId == 0 && $languageId == 0) { // Macintosh, Roman, English, PS Name
				$opos = $this->_pos;
				$N = $this->get_chunk($string_data_offset + $offset, $length);
				$this->_pos = $opos;
				$this->seek($opos);
			}
			if ($N && $names[$nameId] == '') {
				$names[$nameId] = $N;
				$nameCount -= 1;
				if ($nameCount == 0)
					break;
			}
		}
		if ($names[6])
			$psName = $names[6];
		else if ($names[4])
			$psName = preg_replace('/ /', '-', $names[4]);
		else if ($names[1])
			$psName = preg_replace('/ /', '-', $names[1]);
		else
			$psName = '';
		if (!$psName) {
			throw new MpdfException("Could not find PostScript font name: " . $this->filename);
		}
		if ($debug) {
			for ($i = 0; $i < count($psName); $i++) {
				$c = $psName[$i];
				$oc = ord($c);
				if ($oc > 126 || strpos(' [](){}<>/%', $c) !== false) {
					throw new MpdfException("psName=" . $psName . " contains invalid character " . $c . " ie U+" . ord(c));
				}
			}
		}
		$this->name = $psName;
		if ($names[1]) {
			$this->familyName = $names[1];
		} else {
			$this->familyName = $psName;
		}
		if ($names[2]) {
			$this->styleName = $names[2];
		} else {
			$this->styleName = 'Regular';
		}
		if ($names[4]) {
			$this->fullName = $names[4];
		} else {
			$this->fullName = $psName;
		}
		if ($names[3]) {
			$this->uniqueFontID = $names[3];
		} else {
			$this->uniqueFontID = $psName;
		}

		if ($names[6]) {
			$this->fullName = $names[6];
		}

		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		if ($debug) {
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1) {
				throw new MpdfException('Unknown head table version ' . $ver_maj . '.' . $ver_min);
			}
			$this->fontRevision = $this->read_ushort() . $this->read_ushort();

			$this->skip(4);
			$magic = $this->read_ulong();
			if ($magic != 0x5F0F3CF5) {
				throw new MpdfException('Invalid head table magic ' . $magic);
			}
			$this->skip(2);
		}
		else {
			$this->skip(18);
		}
		$this->unitsPerEm = $unitsPerEm = $this->read_ushort();
		$scale = 1000 / $unitsPerEm;
		$this->skip(16);
		$xMin = $this->read_short();
		$yMin = $this->read_short();
		$xMax = $this->read_short();
		$yMax = $this->read_short();
		$this->bbox = array(($xMin * $scale), ($yMin * $scale), ($xMax * $scale), ($yMax * $scale));
		$this->skip(3 * 2);
		$indexToLocFormat = $this->read_ushort();
		$glyphDataFormat = $this->read_ushort();
		if ($glyphDataFormat != 0) {
			throw new MpdfException('Unknown glyph data format ' . $glyphDataFormat);
		}

		///////////////////////////////////
		// hhea metrics table
		///////////////////////////////////
		// ttf2t1 seems to use this value rather than the one in OS/2 - so put in for compatibility
		if (isset($this->tables["hhea"])) {
			$this->seek_table("hhea");
			$this->skip(4);
			$hheaAscender = $this->read_short();
			$hheaDescender = $this->read_short();
			$this->ascent = ($hheaAscender * $scale);
			$this->descent = ($hheaDescender * $scale);
		}

		///////////////////////////////////
		// OS/2 - OS/2 and Windows metrics table
		///////////////////////////////////
		if (isset($this->tables["OS/2"])) {
			$this->seek_table("OS/2");
			$version = $this->read_ushort();
			$this->skip(2);
			$usWeightClass = $this->read_ushort();
			$this->skip(2);
			$fsType = $this->read_ushort();
			if ($fsType == 0x0002 || ($fsType & 0x0300) != 0) {
				global $overrideTTFFontRestriction;
				if (!$overrideTTFFontRestriction) {
					throw new MpdfException('ERROR - Font file ' . $this->filename . ' cannot be embedded due to copyright restrictions.');
				}
				$this->restrictedUse = true;
			}
			$this->skip(20);
			$sF = $this->read_short();
			$this->sFamilyClass = ($sF >> 8);
			$this->sFamilySubClass = ($sF & 0xFF);
			$this->_pos += 10;  //PANOSE = 10 byte length
			$panose = fread($this->fh, 10);
			$this->panose = array();
			for ($p = 0; $p < strlen($panose); $p++) {
				$this->panose[] = ord($panose[$p]);
			}
			$this->skip(26);
			$sTypoAscender = $this->read_short();
			$sTypoDescender = $this->read_short();
			if (!$this->ascent)
				$this->ascent = ($sTypoAscender * $scale);
			if (!$this->descent)
				$this->descent = ($sTypoDescender * $scale);
			if ($version > 1) {
				$this->skip(16);
				$sCapHeight = $this->read_short();
				$this->capHeight = ($sCapHeight * $scale);
			} else {
				$this->capHeight = $this->ascent;
			}
		} else {
			$usWeightClass = 500;
			if (!$this->ascent)
				$this->ascent = ($yMax * $scale);
			if (!$this->descent)
				$this->descent = ($yMin * $scale);
			$this->capHeight = $this->ascent;
		}
		$this->stemV = 50 + intval(pow(($usWeightClass / 65.0), 2));

		///////////////////////////////////
		// post - PostScript table
		///////////////////////////////////
		$this->seek_table("post");
		if ($debug) {
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj < 1 || $ver_maj > 4) {
				throw new MpdfException('Unknown post table version ' . $ver_maj);
			}
		}
		else {
			$this->skip(4);
		}
		$this->italicAngle = $this->read_short() + $this->read_ushort() / 65536.0;
		$this->underlinePosition = $this->read_short() * $scale;
		$this->underlineThickness = $this->read_short() * $scale;
		$isFixedPitch = $this->read_ulong();

		$this->flags = 4;

		if ($this->italicAngle != 0)
			$this->flags = $this->flags | 64;
		if ($usWeightClass >= 600)
			$this->flags = $this->flags | 262144;
		if ($isFixedPitch)
			$this->flags = $this->flags | 1;

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		if ($debug) {
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1) {
				throw new MpdfException('Unknown hhea table version ' . $ver_maj);
			}
			$this->skip(28);
		}
		else {
			$this->skip(32);
		}
		$metricDataFormat = $this->read_ushort();
		if ($metricDataFormat != 0) {
			throw new MpdfException('Unknown horizontal metric data format ' . $metricDataFormat);
		}
		$numberOfHMetrics = $this->read_ushort();
		if ($numberOfHMetrics == 0) {
			throw new MpdfException('Number of horizontal metrics is 0');
		}

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		if ($debug) {
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1) {
				throw new MpdfException('Unknown maxp table version ' . $ver_maj);
			}
		}
		else {
			$this->skip(4);
		}
		$numGlyphs = $this->read_ushort();


		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i = 0; $i < $cmapTableCount; $i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if (($platformID == 3 && $encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					if (!$unicode_cmap_offset)
						$unicode_cmap_offset = $cmap_offset + $offset;
					if ($BMPonly)
						break;
				}
			}
			// Microsoft, Unicode Format 12 table HKCS
			else if ((($platformID == 3 && $encodingID == 10) || $platformID == 0) && !$BMPonly) {
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 12) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos);
		}

		if (!$unicode_cmap_offset) {
			throw new MpdfException('Font (' . $this->filename . ') does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');
		}


		$sipset = false;
		$smpset = false;

		// mPDF 5.7.1
		$this->GSUBScriptLang = array();
		$this->rtlPUAstr = '';
		$this->rtlPUAarr = array();
		$this->GSUBFeatures = array();
		$this->GSUBLookups = array();
		$this->GPOSScriptLang = array();
		$this->GPOSFeatures = array();
		$this->GPOSLookups = array();
		$this->glyphIDtoUni = '';

		// Format 12 CMAP does characters above Unicode BMP i.e. some HKCS characters U+20000 and above
		if ($format == 12 && !$BMPonly) {
			$this->maxUniChar = 0;
			$this->seek($unicode_cmap_offset + 4);
			$length = $this->read_ulong();
			$limit = $unicode_cmap_offset + $length;
			$this->skip(4);

			$nGroups = $this->read_ulong();

			$glyphToChar = array();
			$charToGlyph = array();
			for ($i = 0; $i < $nGroups; $i++) {
				$startCharCode = $this->read_ulong();
				$endCharCode = $this->read_ulong();
				$startGlyphCode = $this->read_ulong();
				if ($endCharCode > 0x20000 && $endCharCode < 0x2FFFF) {
					$sipset = true;
				} else if ($endCharCode > 0x10000 && $endCharCode < 0x1FFFF) {
					$smpset = true;
				}
				$offset = 0;
				for ($unichar = $startCharCode; $unichar <= $endCharCode; $unichar++) {
					$glyph = $startGlyphCode + $offset;
					$offset++;
					if ($unichar < 0x30000) {
						$charToGlyph[$unichar] = $glyph;
						$this->maxUniChar = max($unichar, $this->maxUniChar);
						$glyphToChar[$glyph][] = $unichar;
					}
				}
			}
		} else {

			$glyphToChar = array();
			$charToGlyph = array();
			$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph);
		}
		$this->sipset = $sipset;
		$this->smpset = $smpset;


		///////////////////////////////////
		// mPDF 5.7.1
		// Map Unmapped glyphs - from $numGlyphs
		if ($this->useOTL) {
			$bctr = 0xE000;
			for ($gid = 1; $gid < $numGlyphs; $gid++) {
				if (!isset($glyphToChar[$gid])) {
					while (isset($charToGlyph[$bctr])) {
						$bctr++;
					} // Avoid overwriting a glyph already mapped in PUA
					if (($bctr > 0xF8FF) && ($bctr < 0x2CEB0)) {
						if (!$BMPonly) {
							$bctr = 0x2CEB0;  // Use unassigned area 0x2CEB0 to 0x2F7FF (space for 10,000 characters)
							$this->sipset = $sipset = true; // forces subsetting; also ensure charwidths are saved
							while (isset($charToGlyph[$bctr])) {
								$bctr++;
							}
						} else {
							throw new MpdfException($names[1] . " : WARNING - The font does not have enough space to map all (unmapped) included glyphs into Private Use Area U+E000 - U+F8FF");
						}
					}
					$glyphToChar[$gid][] = $bctr;
					$charToGlyph[$bctr] = $gid;
					$this->maxUniChar = max($bctr, $this->maxUniChar);
					$bctr++;
				}
			}
		}
		$this->glyphToChar = $glyphToChar;
		$this->charToGlyph = $charToGlyph;
		///////////////////////////////////
		// mPDF 5.7.1	OpenType Layout tables
		$this->GSUBScriptLang = array();
		$this->rtlPUAstr = '';
		$this->rtlPUAarr = array();
		if ($useOTL) {
			$this->_getGDEFtables();
			list($this->GSUBScriptLang, $this->GSUBFeatures, $this->GSUBLookups, $this->rtlPUAstr, $this->rtlPUAarr) = $this->_getGSUBtables();
			list($this->GPOSScriptLang, $this->GPOSFeatures, $this->GPOSLookups) = $this->_getGPOStables();
			$this->glyphIDtoUni = str_pad('', 256 * 256 * 3, "\x00");
			foreach ($glyphToChar AS $gid => $arr) {
				if (isset($glyphToChar[$gid][0])) {
					$char = $glyphToChar[$gid][0];
					if ($char != 0 && $char != 65535) {
						$this->glyphIDtoUni[$gid * 3] = chr($char >> 16);
						$this->glyphIDtoUni[$gid * 3 + 1] = chr(($char >> 8) & 0xFF);
						$this->glyphIDtoUni[$gid * 3 + 2] = chr($char & 0xFF);
					}
				}
			}
		}
		///////////////////////////////////
		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$this->getHMTX($numberOfHMetrics, $numGlyphs, $glyphToChar, $scale);

		///////////////////////////////////
		// kern - Kerning pair table
		///////////////////////////////////
		if ($kerninfo) {
			// Recognises old form of Kerning table - as required by Windows - Format 0 only
			$kern_offset = $this->seek_table("kern");
			$version = $this->read_ushort();
			$nTables = $this->read_ushort();
			// subtable header
			$sversion = $this->read_ushort();
			$slength = $this->read_ushort();
			$scoverage = $this->read_ushort();
			$format = $scoverage >> 8;
			if ($kern_offset && $version == 0 && $format == 0) {
				// Format 0
				$nPairs = $this->read_ushort();
				$this->skip(6);
				for ($i = 0; $i < $nPairs; $i++) {
					$left = $this->read_ushort();
					$right = $this->read_ushort();
					$val = $this->read_short();
					if (count($glyphToChar[$left]) == 1 && count($glyphToChar[$right]) == 1) {
						if ($left != 32 && $right != 32) {
							$this->kerninfo[$glyphToChar[$left][0]][$glyphToChar[$right][0]] = intval($val * $scale);
						}
					}
				}
			}
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	function _getGDEFtables()
	{
		///////////////////////////////////
		// GDEF - Glyph Definition
		///////////////////////////////////
		// http://www.microsoft.com/typography/otspec/gdef.htm
		if (isset($this->tables["GDEF"])) {
			if ($this->mode == 'summary') {
				$this->mpdf->WriteHTML('<h1>GDEF table</h1>');
			}
			$gdef_offset = $this->seek_table("GDEF");
			// ULONG Version of the GDEF table-currently 0x00010000
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			// Version 0x00010002 of GDEF header contains additional Offset to a list defining mark glyph set definitions (MarkGlyphSetDef)
			$GlyphClassDef_offset = $this->read_ushort();
			$AttachList_offset = $this->read_ushort();
			$LigCaretList_offset = $this->read_ushort();
			$MarkAttachClassDef_offset = $this->read_ushort();
			if ($ver_min == 2) {
				$MarkGlyphSetsDef_offset = $this->read_ushort();
			}

			// GlyphClassDef
			$this->seek($gdef_offset + $GlyphClassDef_offset);
			/*
			  1	Base glyph (single character, spacing glyph)
			  2	Ligature glyph (multiple character, spacing glyph)
			  3	Mark glyph (non-spacing combining glyph)
			  4	Component glyph (part of single character, spacing glyph)
			 */
			$GlyphByClass = $this->_getClassDefinitionTable();

			if ($this->mode == 'summary') {
				$this->mpdf->WriteHTML('<h2>Glyph classes</h2>');
			}

			if (isset($GlyphByClass[1]) && count($GlyphByClass[1]) > 0) {
				$this->GlyphClassBases = $this->formatClassArr($GlyphByClass[1]);
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h3>Glyph class 1</h3>');
					$this->mpdf->WriteHTML('<h5>Base glyph (single character, spacing glyph)</h5>');
					$html = '';
					$html .= '<div class="glyphs">';
					foreach ($GlyphByClass[1] AS $g) {
						$html .= '&#x' . $g . '; ';
					}
					$html .= '</div>';
					$this->mpdf->WriteHTML($html);
				}
			} else {
				$this->GlyphClassBases = '';
			}
			if (isset($GlyphByClass[2]) && count($GlyphByClass[2]) > 0) {
				$this->GlyphClassLigatures = $this->formatClassArr($GlyphByClass[2]);
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h3>Glyph class 2</h3>');
					$this->mpdf->WriteHTML('<h5>Ligature glyph (multiple character, spacing glyph)</h5>');
					$html = '';
					$html .= '<div class="glyphs">';
					foreach ($GlyphByClass[2] AS $g) {
						$html .= '&#x' . $g . '; ';
					}
					$html .= '</div>';
					$this->mpdf->WriteHTML($html);
				}
			} else {
				$this->GlyphClassLigatures = '';
			}
			if (isset($GlyphByClass[3]) && count($GlyphByClass[3]) > 0) {
				$this->GlyphClassMarks = $this->formatClassArr($GlyphByClass[3]);
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h3>Glyph class 3</h3>');
					$this->mpdf->WriteHTML('<h5>Mark glyph (non-spacing combining glyph)</h5>');
					$html = '';
					$html .= '<div class="glyphs">';
					foreach ($GlyphByClass[3] AS $g) {
						$html .= '&#x25cc;&#x' . $g . '; ';
					}
					$html .= '</div>';
					$this->mpdf->WriteHTML($html);
				}
			} else {
				$this->GlyphClassMarks = '';
			}
			if (isset($GlyphByClass[4]) && count($GlyphByClass[4]) > 0) {
				$this->GlyphClassComponents = $this->formatClassArr($GlyphByClass[4]);
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h3>Glyph class 4</h3>');
					$this->mpdf->WriteHTML('<h5>Component glyph (part of single character, spacing glyph)</h5>');
					$html = '';
					$html .= '<div class="glyphs">';
					foreach ($GlyphByClass[4] AS $g) {
						$html .= '&#x' . $g . '; ';
					}
					$html .= '</div>';
					$this->mpdf->WriteHTML($html);
				}
			} else {
				$this->GlyphClassComponents = '';
			}

			$Marks = $GlyphByClass[3]; // to use for MarkAttachmentType


			/* Required for GPOS
			  // Attachment List
			  if ($AttachList_offset) {
			  $this->seek($gdef_offset+$AttachList_offset );
			  }
			  The Attachment Point List table (AttachmentList) identifies all the attachment points defined in the GPOS table and their associated glyphs so a client can quickly access coordinates for each glyph's attachment points. As a result, the client can cache coordinates for attachment points along with glyph bitmaps and avoid recalculating the attachment points each time it displays a glyph. Without this table, processing speed would be slower because the client would have to decode the GPOS lookups that define attachment points and compile the points in a list.

			  The Attachment List table (AttachList) may be used to cache attachment point coordinates along with glyph bitmaps.

			  The table consists of an offset to a Coverage table (Coverage) listing all glyphs that define attachment points in the GPOS table, a count of the glyphs with attachment points (GlyphCount), and an array of offsets to AttachPoint tables (AttachPoint). The array lists the AttachPoint tables, one for each glyph in the Coverage table, in the same order as the Coverage Index.
			  AttachList table
			  Type 	Name 	Description
			  Offset 	Coverage 	Offset to Coverage table - from beginning of AttachList table
			  uint16 	GlyphCount 	Number of glyphs with attachment points
			  Offset 	AttachPoint[GlyphCount] 	Array of offsets to AttachPoint tables-from beginning of AttachList table-in Coverage Index order

			  An AttachPoint table consists of a count of the attachment points on a single glyph (PointCount) and an array of contour indices of those points (PointIndex), listed in increasing numerical order.

			  AttachPoint table
			  Type 	Name 	Description
			  uint16 	PointCount 	Number of attachment points on this glyph
			  uint16 	PointIndex[PointCount] 	Array of contour point indices -in increasing numerical order

			  See Example 3 - http://www.microsoft.com/typography/otspec/gdef.htm
			 */


			// Ligature Caret List
			// The Ligature Caret List table (LigCaretList) defines caret positions for all the ligatures in a font.
			// Not required for mDPF
			// MarkAttachmentType
			if ($MarkAttachClassDef_offset) {
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h1>Mark Attachment Types</h1>');
				}
				$this->seek($gdef_offset + $MarkAttachClassDef_offset);
				$MarkAttachmentTypes = $this->_getClassDefinitionTable();
				foreach ($MarkAttachmentTypes AS $class => $glyphs) {

					if (is_array($Marks) && count($Marks)) {
						$mat = array_diff($Marks, $MarkAttachmentTypes[$class]);
						sort($mat, SORT_STRING);
					} else {
						$mat = array();
					}

					$this->MarkAttachmentType[$class] = $this->formatClassArr($mat);

					if ($this->mode == 'summary') {
						$this->mpdf->WriteHTML('<h3>Mark Attachment Type: ' . $class . '</h3>');
						$html = '';
						$html .= '<div class="glyphs">';
						foreach ($glyphs AS $g) {
							$html .= '&#x25cc;&#x' . $g . '; ';
						}
						$html .= '</div>';
						$this->mpdf->WriteHTML($html);
					}
				}
			} else {
				$this->MarkAttachmentType = array();
			}


			// MarkGlyphSets only in Version 0x00010002 of GDEF
			if ($ver_min == 2 && $MarkGlyphSetsDef_offset) {
				if ($this->mode == 'summary') {
					$this->mpdf->WriteHTML('<h1>Mark Glyph Sets</h1>');
				}
				$this->seek($gdef_offset + $MarkGlyphSetsDef_offset);
				$MarkSetTableFormat = $this->read_ushort();
				$MarkSetCount = $this->read_ushort();
				$MarkSetOffset = array();
				for ($i = 0; $i < $MarkSetCount; $i++) {
					$MarkSetOffset[] = $this->read_ulong();
				}
				for ($i = 0; $i < $MarkSetCount; $i++) {
					$this->seek($MarkSetOffset[$i]);
					$glyphs = $this->_getCoverage();
					$this->MarkGlyphSets[$i] = $this->formatClassArr($glyphs);
					if ($this->mode == 'summary') {
						$this->mpdf->WriteHTML('<h3>Mark Glyph Set class: ' . $i . '</h3>');
						$html = '';
						$html .= '<div class="glyphs">';
						foreach ($glyphs AS $g) {
							$html .= '&#x25cc;&#x' . $g . '; ';
						}
						$html .= '</div>';
						$this->mpdf->WriteHTML($html);
					}
				}
			} else {
				$this->MarkGlyphSets = array();
			}
		} else {
			$this->mpdf->WriteHTML('<div>GDEF table not defined</div>');
		}


//echo $this->GlyphClassMarks ; exit;
//print_r($GlyphClass); exit;
//print_r($GlyphByClass); exit;
	}

	function _getClassDefinitionTable($offset = 0)
	{

		if ($offset > 0) {
			$this->seek($offset);
		}

		// NB Any glyph not included in the range of covered GlyphIDs automatically belongs to Class 0. This is not returned by this function
		$ClassFormat = $this->read_ushort();
		$GlyphByClass = array();
		if ($ClassFormat == 1) {
			$StartGlyph = $this->read_ushort();
			$GlyphCount = $this->read_ushort();
			for ($i = 0; $i < $GlyphCount; $i++) {
				$gid = $StartGlyph + $i;
				$class = $this->read_ushort();
				$GlyphByClass[$class][] = unicode_hex($this->glyphToChar[$gid][0]);
			}
		} else if ($ClassFormat == 2) {
			$tableCount = $this->read_ushort();
			for ($i = 0; $i < $tableCount; $i++) {
				$startGlyphID = $this->read_ushort();
				$endGlyphID = $this->read_ushort();
				$class = $this->read_ushort();
				for ($gid = $startGlyphID; $gid <= $endGlyphID; $gid++) {
					$GlyphByClass[$class][] = unicode_hex($this->glyphToChar[$gid][0]);
				}
			}
		}
		ksort($GlyphByClass);
		return $GlyphByClass;
	}

	function _getGSUBtables()
	{
		///////////////////////////////////
		// GSUB - Glyph Substitution
		///////////////////////////////////
		if (isset($this->tables["GSUB"])) {
			$this->mpdf->WriteHTML('<h1>GSUB Tables</h1>');
			$ffeats = array();
			$gsub_offset = $this->seek_table("GSUB");
			$this->skip(4);
			$ScriptList_offset = $gsub_offset + $this->read_ushort();
			$FeatureList_offset = $gsub_offset + $this->read_ushort();
			$LookupList_offset = $gsub_offset + $this->read_ushort();

			// ScriptList
			$this->seek($ScriptList_offset);
			$ScriptCount = $this->read_ushort();
			for ($i = 0; $i < $ScriptCount; $i++) {
				$ScriptTag = $this->read_tag(); // = "beng", "deva" etc.
				$ScriptTableOffset = $this->read_ushort();
				$ffeats[$ScriptTag] = $ScriptList_offset + $ScriptTableOffset;
			}

			// Script Table
			foreach ($ffeats AS $t => $o) {
				$ls = array();
				$this->seek($o);
				$DefLangSys_offset = $this->read_ushort();
				if ($DefLangSys_offset > 0) {
					$ls['DFLT'] = $DefLangSys_offset + $o;
				}
				$LangSysCount = $this->read_ushort();
				for ($i = 0; $i < $LangSysCount; $i++) {
					$LangTag = $this->read_tag(); // =
					$LangTableOffset = $this->read_ushort();
					$ls[$LangTag] = $o + $LangTableOffset;
				}
				$ffeats[$t] = $ls;
			}
//print_r($ffeats); exit;
			// Get FeatureIndexList
			// LangSys Table - from first listed langsys
			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $o) {
					$FeatureIndex = array();
					$langsystable_offset = $o;
					$this->seek($langsystable_offset);
					$LookUpOrder = $this->read_ushort(); //==NULL
					$ReqFeatureIndex = $this->read_ushort();
					if ($ReqFeatureIndex != 0xFFFF) {
						$FeatureIndex[] = $ReqFeatureIndex;
					}
					$FeatureCount = $this->read_ushort();
					for ($i = 0; $i < $FeatureCount; $i++) {
						$FeatureIndex[] = $this->read_ushort(); // = index of feature
					}
					$ffeats[$st][$t] = $FeatureIndex;
				}
			}
//print_r($ffeats); exit;
			// Feauture List => LookupListIndex es
			$this->seek($FeatureList_offset);
			$FeatureCount = $this->read_ushort();
			$Feature = array();
			for ($i = 0; $i < $FeatureCount; $i++) {
				$Feature[$i] = array('tag' => $this->read_tag());
				$Feature[$i]['offset'] = $FeatureList_offset + $this->read_ushort();
			}
			for ($i = 0; $i < $FeatureCount; $i++) {
				$this->seek($Feature[$i]['offset']);
				$this->read_ushort(); // null
				$Feature[$i]['LookupCount'] = $Lookupcount = $this->read_ushort();
				$Feature[$i]['LookupListIndex'] = array();
				for ($c = 0; $c < $Lookupcount; $c++) {
					$Feature[$i]['LookupListIndex'][] = $this->read_ushort();
				}
			}


			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $o) {
					$FeatureIndex = $ffeats[$st][$t];
					foreach ($FeatureIndex AS $k => $fi) {
						$ffeats[$st][$t][$k] = $Feature[$fi];
					}
				}
			}
			//=====================================================================================
			$gsub = array();
			$GSUBScriptLang = array();
			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $langsys) {
					$lg = array();
					foreach ($langsys AS $ft) {
						$lg[$ft['LookupListIndex'][0]] = $ft;
					}
					// list of Lookups in order they need to be run i.e. order listed in Lookup table
					ksort($lg);
					foreach ($lg AS $ft) {
						$gsub[$st][$t][$ft['tag']] = $ft['LookupListIndex'];
					}
					if (!isset($GSUBScriptLang[$st])) {
						$GSUBScriptLang[$st] = '';
					}
					$GSUBScriptLang[$st] .= $t . ' ';
				}
			}

//print_r($gsub); exit;

			if ($this->mode == 'summary') {
				$this->mpdf->WriteHTML('<h3>GSUB Scripts &amp; Languages</h3>');
				$this->mpdf->WriteHTML('<div class="glyphs">');
				$html = '';
				if (count($gsub)) {
					foreach ($gsub AS $st => $g) {
						$html .= '<h5>' . $st . '</h5>';
						foreach ($g AS $l => $t) {
							$html .= '<div><a href="font_dump_OTL.php?script=' . $st . '&lang=' . $l . '">' . $l . '</a></b>: ';
							foreach ($t AS $tag => $o) {
								$html .= $tag . ' ';
							}
							$html .= '</div>';
						}
					}
				} else {
					$html .= '<div>No entries in GSUB table.</div>';
				}
				$this->mpdf->WriteHTML($html);
				$this->mpdf->WriteHTML('</div>');
				return 0;
			}



			//=====================================================================================
			// Get metadata and offsets for whole Lookup List table
			$this->seek($LookupList_offset);
			$LookupCount = $this->read_ushort();
			$GSLookup = array();
			$Offsets = array();
			$SubtableCount = array();
			for ($i = 0; $i < $LookupCount; $i++) {
				$Offsets[$i] = $LookupList_offset + $this->read_ushort();
			}
			for ($i = 0; $i < $LookupCount; $i++) {
				$this->seek($Offsets[$i]);
				$GSLookup[$i]['Type'] = $this->read_ushort();
				$GSLookup[$i]['Flag'] = $flag = $this->read_ushort();
				$GSLookup[$i]['SubtableCount'] = $SubtableCount[$i] = $this->read_ushort();
				for ($c = 0; $c < $SubtableCount[$i]; $c++) {
					$GSLookup[$i]['Subtables'][$c] = $Offsets[$i] + $this->read_ushort();
				}
				// MarkFilteringSet = Index (base 0) into GDEF mark glyph sets structure
				if (($flag & 0x0010) == 0x0010) {
					$GSLookup[$i]['MarkFilteringSet'] = $this->read_ushort();
				}
				// else { $GSLookup[$i]['MarkFilteringSet'] = ''; }
				// Lookup Type 7: Extension
				if ($GSLookup[$i]['Type'] == 7) {
					// Overwrites new offset (32-bit) for each subtable, and a new lookup Type
					for ($c = 0; $c < $SubtableCount[$i]; $c++) {
						$this->seek($GSLookup[$i]['Subtables'][$c]);
						$ExtensionPosFormat = $this->read_ushort();
						$type = $this->read_ushort();
						$GSLookup[$i]['Subtables'][$c] = $GSLookup[$i]['Subtables'][$c] + $this->read_ulong();
					}
					$GSLookup[$i]['Type'] = $type;
				}
			}

//print_r($GSLookup); exit;
			//=====================================================================================
			// Process Whole LookupList - Get LuCoverage = Lookup coverage just for first glyph
			$this->GSLuCoverage = array();
			for ($i = 0; $i < $LookupCount; $i++) {
				for ($c = 0; $c < $GSLookup[$i]['SubtableCount']; $c++) {

					$this->seek($GSLookup[$i]['Subtables'][$c]);
					$PosFormat = $this->read_ushort();

					if ($GSLookup[$i]['Type'] == 5 && $PosFormat == 3) {
						$this->skip(4);
					} else if ($GSLookup[$i]['Type'] == 6 && $PosFormat == 3) {
						$BacktrackGlyphCount = $this->read_ushort();
						$this->skip(2 * $BacktrackGlyphCount + 2);
					}
					// NB Coverage only looks at glyphs for position 1 (i.e. 5.3 and 6.3)	// NEEDS TO READ ALL ********************
					$Coverage = $GSLookup[$i]['Subtables'][$c] + $this->read_ushort();
					$this->seek($Coverage);
					$glyphs = $this->_getCoverage();
					$this->GSLuCoverage[$i][$c] = implode('|', $glyphs);
				}
			}

// $this->GSLuCoverage and $GSLookup
			//=====================================================================================
			$s = '<?php
$GSLuCoverage = ' . var_export($this->GSLuCoverage, true) . ';
?>';


			//=====================================================================================
			$s = '<?php
$GlyphClassBases = \'' . $this->GlyphClassBases . '\';
$GlyphClassMarks = \'' . $this->GlyphClassMarks . '\';
$GlyphClassLigatures = \'' . $this->GlyphClassLigatures . '\';
$GlyphClassComponents = \'' . $this->GlyphClassComponents . '\';
$MarkGlyphSets = ' . var_export($this->MarkGlyphSets, true) . ';
$MarkAttachmentType = ' . var_export($this->MarkAttachmentType, true) . ';
?>';


			//=====================================================================================
			//=====================================================================================
			//=====================================================================================
// Now repeats as original to get Substitution rules
			//=====================================================================================
			//=====================================================================================
			//=====================================================================================
			// Get metadata and offsets for whole Lookup List table
			$this->seek($LookupList_offset);
			$LookupCount = $this->read_ushort();
			$Lookup = array();
			for ($i = 0; $i < $LookupCount; $i++) {
				$Lookup[$i]['offset'] = $LookupList_offset + $this->read_ushort();
			}
			for ($i = 0; $i < $LookupCount; $i++) {
				$this->seek($Lookup[$i]['offset']);
				$Lookup[$i]['Type'] = $this->read_ushort();
				$Lookup[$i]['Flag'] = $flag = $this->read_ushort();
				$Lookup[$i]['SubtableCount'] = $this->read_ushort();
				for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {
					$Lookup[$i]['Subtable'][$c]['Offset'] = $Lookup[$i]['offset'] + $this->read_ushort();
				}
				// MarkFilteringSet = Index (base 0) into GDEF mark glyph sets structure
				if (($flag & 0x0010) == 0x0010) {
					$Lookup[$i]['MarkFilteringSet'] = $this->read_ushort();
				} else {
					$Lookup[$i]['MarkFilteringSet'] = '';
				}

				// Lookup Type 7: Extension
				if ($Lookup[$i]['Type'] == 7) {
					// Overwrites new offset (32-bit) for each subtable, and a new lookup Type
					for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {
						$this->seek($Lookup[$i]['Subtable'][$c]['Offset']);
						$ExtensionPosFormat = $this->read_ushort();
						$type = $this->read_ushort();
						$Lookup[$i]['Subtable'][$c]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ulong();
					}
					$Lookup[$i]['Type'] = $type;
				}
			}

//print_r($Lookup); exit;
			//=====================================================================================
			// Process (1) Whole LookupList
			for ($i = 0; $i < $LookupCount; $i++) {
				for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {

					$this->seek($Lookup[$i]['Subtable'][$c]['Offset']);
					$SubstFormat = $this->read_ushort();
					$Lookup[$i]['Subtable'][$c]['Format'] = $SubstFormat;

					/*
					  Lookup['Type'] Enumeration table for glyph substitution
					  Value	Type	Description
					  1	Single	Replace one glyph with one glyph
					  2	Multiple	Replace one glyph with more than one glyph
					  3	Alternate	Replace one glyph with one of many glyphs
					  4	Ligature	Replace multiple glyphs with one glyph
					  5	Context	Replace one or more glyphs in context
					  6	Chaining Context	Replace one or more glyphs in chained context
					  7	Extension Substitution	Extension mechanism for other substitutions (i.e. this excludes the Extension type substitution itself)
					  8	Reverse chaining context single 	Applied in reverse order, replace single glyph in chaining context
					 */

					// LookupType 1: Single Substitution Subtable
					if ($Lookup[$i]['Type'] == 1) {
						$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
						if ($SubstFormat == 1) { // Calculated output glyph indices
							$Lookup[$i]['Subtable'][$c]['DeltaGlyphID'] = $this->read_short();
						} else if ($SubstFormat == 2) { // Specified output glyph indices
							$GlyphCount = $this->read_ushort();
							for ($g = 0; $g < $GlyphCount; $g++) {
								$Lookup[$i]['Subtable'][$c]['Glyphs'][] = $this->read_ushort();
							}
						}
					}
					// LookupType 2: Multiple Substitution Subtable
					else if ($Lookup[$i]['Type'] == 2) {
						$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
						$Lookup[$i]['Subtable'][$c]['SequenceCount'] = $SequenceCount = $this->read_short();
						for ($s = 0; $s < $SequenceCount; $s++) {
							$Lookup[$i]['Subtable'][$c]['Sequences'][$s]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_short();
						}
						for ($s = 0; $s < $SequenceCount; $s++) {
							// Sequence Tables
							$this->seek($Lookup[$i]['Subtable'][$c]['Sequences'][$s]['Offset']);
							$Lookup[$i]['Subtable'][$c]['Sequences'][$s]['GlyphCount'] = $this->read_short();
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['Sequences'][$s]['GlyphCount']; $g++) {
								$Lookup[$i]['Subtable'][$c]['Sequences'][$s]['SubstituteGlyphID'][] = $this->read_ushort();
							}
						}
					}
					// LookupType 3: Alternate Forms
					else if ($Lookup[$i]['Type'] == 3) {
						$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
						$Lookup[$i]['Subtable'][$c]['AlternateSetCount'] = $AlternateSetCount = $this->read_short();
						for ($s = 0; $s < $AlternateSetCount; $s++) {
							$Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_short();
						}

						for ($s = 0; $s < $AlternateSetCount; $s++) {
							// AlternateSet Tables
							$this->seek($Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['Offset']);
							$Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['GlyphCount'] = $this->read_short();
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['GlyphCount']; $g++) {
								$Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['SubstituteGlyphID'][] = $this->read_ushort();
							}
						}
					}
					// LookupType 4: Ligature Substitution Subtable
					else if ($Lookup[$i]['Type'] == 4) {
						$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
						$Lookup[$i]['Subtable'][$c]['LigSetCount'] = $LigSetCount = $this->read_short();
						for ($s = 0; $s < $LigSetCount; $s++) {
							$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_short();
						}
						for ($s = 0; $s < $LigSetCount; $s++) {
							// LigatureSet Tables
							$this->seek($Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Offset']);
							$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigCount'] = $this->read_short();
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigCount']; $g++) {
								$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigatureOffset'][$g] = $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Offset'] + $this->read_ushort();
							}
						}
						for ($s = 0; $s < $LigSetCount; $s++) {
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigCount']; $g++) {
								// Ligature tables
								$this->seek($Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigatureOffset'][$g]);
								$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['LigGlyph'] = $this->read_ushort();
								$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['CompCount'] = $this->read_ushort();
								for ($l = 1; $l < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['CompCount']; $l++) {
									$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['GlyphID'][$l] = $this->read_ushort();
								}
							}
						}
					}

					// LookupType 5: Contextual Substitution Subtable
					else if ($Lookup[$i]['Type'] == 5) {
						// Format 1: Context Substitution
						if ($SubstFormat == 1) {
							$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['SubRuleSetCount'] = $SubRuleSetCount = $this->read_short();
							for ($s = 0; $s < $SubRuleSetCount; $s++) {
								$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_short();
							}
							for ($s = 0; $s < $SubRuleSetCount; $s++) {
								// SubRuleSet Tables
								$this->seek($Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['Offset']);
								$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleCount'] = $this->read_short();
								for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleCount']; $g++) {
									$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleOffset'][$g] = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['Offset'] + $this->read_ushort();
								}
							}
							for ($s = 0; $s < $SubRuleSetCount; $s++) {
								// SubRule Tables
								for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleCount']; $g++) {
									// Ligature tables
									$this->seek($Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleOffset'][$g]);

									$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['GlyphCount'] = $this->read_ushort();
									$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['SubstCount'] = $this->read_ushort();
									// "Input"::[GlyphCount - 1]::Array of input GlyphIDs-start with second glyph
									for ($l = 1; $l < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['GlyphCount']; $l++) {
										$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['Input'][$l] = $this->read_ushort();
									}
									// "SubstLookupRecord"::[SubstCount]::Array of SubstLookupRecords-in design order
									for ($l = 0; $l < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['SubstCount']; $l++) {
										$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['SubstLookupRecord'][$l]['SequenceIndex'] = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['SubstLookupRecord'][$l]['LookupListIndex'] = $this->read_ushort();
									}
								}
							}
						}
						// Format 2: Class-based Context Glyph Substitution
						else if ($SubstFormat == 2) {
							$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['ClassDefOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['SubClassSetCnt'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['SubClassSetCnt']; $b++) {
								$offset = $this->read_ushort();
								if ($offset == 0x0000) {
									$Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][] = 0;
								} else {
									$Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $offset;
								}
							}
						} else {
							throw new MpdfException("GPOS Lookup Type " . $Lookup[$i]['Type'] . ", Format " . $SubstFormat . " not supported (ttfontsuni.php).");
						}
					}

					// LookupType 6: Chaining Contextual Substitution Subtable
					else if ($Lookup[$i]['Type'] == 6) {
						// Format 1: Simple Chaining Context Glyph Substitution  p255
						if ($SubstFormat == 1) {
							$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['ChainSubRuleSetCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['ChainSubRuleSetCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['ChainSubRuleSetOffset'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							}
						}
						// Format 2: Class-based Chaining Context Glyph Substitution  p257
						else if ($SubstFormat == 2) {
							$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['BacktrackClassDefOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['InputClassDefOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['LookaheadClassDefOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['ChainSubClassSetCnt'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['ChainSubClassSetCnt']; $b++) {
								$offset = $this->read_ushort();
								if ($offset == 0x0000) {
									$Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][] = $offset;
								} else {
									$Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $offset;
								}
							}
						}
						// Format 3: Coverage-based Chaining Context Glyph Substitution  p259
						else if ($SubstFormat == 3) {
							$Lookup[$i]['Subtable'][$c]['BacktrackGlyphCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['BacktrackGlyphCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['CoverageBacktrack'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							}
							$Lookup[$i]['Subtable'][$c]['InputGlyphCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['InputGlyphCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['CoverageInput'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							}
							$Lookup[$i]['Subtable'][$c]['LookaheadGlyphCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['LookaheadGlyphCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['CoverageLookahead'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							}
							$Lookup[$i]['Subtable'][$c]['SubstCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['SubstCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['SubstLookupRecord'][$b]['SequenceIndex'] = $this->read_ushort();
								$Lookup[$i]['Subtable'][$c]['SubstLookupRecord'][$b]['LookupListIndex'] = $this->read_ushort();
								/*
								  Substitution Lookup Record
								  All contextual substitution subtables specify the substitution data in a Substitution Lookup Record (SubstLookupRecord). Each record contains a SequenceIndex, which indicates the position where the substitution will occur in the glyph sequence. In addition, a LookupListIndex identifies the lookup to be applied at the glyph position specified by the SequenceIndex.
								 */
							}
						}
					} else {
						throw new MpdfException("Lookup Type " . $Lookup[$i]['Type'] . " not supported.");
					}
				}
			}
//print_r($Lookup); exit;
			//=====================================================================================
			// Process (2) Whole LookupList
			// Get Coverage tables and prepare preg_replace
			for ($i = 0; $i < $LookupCount; $i++) {
				for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {
					$SubstFormat = $Lookup[$i]['Subtable'][$c]['Format'];

					// LookupType 1: Single Substitution Subtable 1 => 1
					if ($Lookup[$i]['Type'] == 1) {
						$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
						$glyphs = $this->_getCoverage(false);
						for ($g = 0; $g < count($glyphs); $g++) {
							$replace = array();
							$substitute = array();
							$replace[] = unicode_hex($this->glyphToChar[$glyphs[$g]][0]);
							// Flag = Ignore
							if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $replace[0], $Lookup[$i]['MarkFilteringSet'])) {
								continue;
							}
							if (isset($Lookup[$i]['Subtable'][$c]['DeltaGlyphID'])) { // Format 1
								$substitute[] = unicode_hex($this->glyphToChar[($glyphs[$g] + $Lookup[$i]['Subtable'][$c]['DeltaGlyphID'])][0]);
							} else { // Format 2
								$substitute[] = unicode_hex($this->glyphToChar[($Lookup[$i]['Subtable'][$c]['Glyphs'][$g])][0]);
							}
							$Lookup[$i]['Subtable'][$c]['subs'][] = array('Replace' => $replace, 'substitute' => $substitute);
						}
					}

					// LookupType 2: Multiple Substitution Subtable 1 => n
					else if ($Lookup[$i]['Type'] == 2) {
						$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
						$glyphs = $this->_getCoverage();
						for ($g = 0; $g < count($glyphs); $g++) {
							$replace = array();
							$substitute = array();
							$replace[] = $glyphs[$g];
							// Flag = Ignore
							if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $replace[0], $Lookup[$i]['MarkFilteringSet'])) {
								continue;
							}
							if (!isset($Lookup[$i]['Subtable'][$c]['Sequences'][$g]['SubstituteGlyphID']) || count($Lookup[$i]['Subtable'][$c]['Sequences'][$g]['SubstituteGlyphID']) == 0) {
								continue;
							} // Illegal for GlyphCount to be 0; either error in font, or something has gone wrong - lets carry on for now!
							foreach ($Lookup[$i]['Subtable'][$c]['Sequences'][$g]['SubstituteGlyphID'] AS $sub) {
								$substitute[] = unicode_hex($this->glyphToChar[$sub][0]);
							}
							$Lookup[$i]['Subtable'][$c]['subs'][] = array('Replace' => $replace, 'substitute' => $substitute);
						}
					}
					// LookupType 3: Alternate Forms 1 => 1 (only first alternate form is used)
					else if ($Lookup[$i]['Type'] == 3) {
						$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
						$glyphs = $this->_getCoverage();
						for ($g = 0; $g < count($glyphs); $g++) {
							$replace = array();
							$substitute = array();
							$replace[] = $glyphs[$g];
							// Flag = Ignore
							if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $replace[0], $Lookup[$i]['MarkFilteringSet'])) {
								continue;
							}

							for ($gl = 0; $gl < $Lookup[$i]['Subtable'][$c]['AlternateSets'][$g]['GlyphCount']; $gl++) {
								$gid = $Lookup[$i]['Subtable'][$c]['AlternateSets'][$g]['SubstituteGlyphID'][$gl];
								$substitute[] = unicode_hex($this->glyphToChar[$gid][0]);
							}

							//$gid = $Lookup[$i]['Subtable'][$c]['AlternateSets'][$g]['SubstituteGlyphID'][0];
							//$substitute[] = unicode_hex($this->glyphToChar[$gid][0]);

							$Lookup[$i]['Subtable'][$c]['subs'][] = array('Replace' => $replace, 'substitute' => $substitute);
						}
						if ($i == 166) {
							print_r($Lookup[$i]['Subtable']);
							exit;
						}
					}
					// LookupType 4: Ligature Substitution Subtable n => 1
					else if ($Lookup[$i]['Type'] == 4) {
						$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
						$glyphs = $this->_getCoverage();
						$LigSetCount = $Lookup[$i]['Subtable'][$c]['LigSetCount'];
						for ($s = 0; $s < $LigSetCount; $s++) {
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigCount']; $g++) {
								$replace = array();
								$substitute = array();
								$replace[] = $glyphs[$s];
								// Flag = Ignore
								if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $replace[0], $Lookup[$i]['MarkFilteringSet'])) {
									continue;
								}
								for ($l = 1; $l < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['CompCount']; $l++) {
									$gid = $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['GlyphID'][$l];
									$rpl = unicode_hex($this->glyphToChar[$gid][0]);
									// Flag = Ignore
									if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $rpl, $Lookup[$i]['MarkFilteringSet'])) {
										continue 2;
									}
									$replace[] = $rpl;
								}
								$gid = $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['LigGlyph'];
								$substitute[] = unicode_hex($this->glyphToChar[$gid][0]);
								$Lookup[$i]['Subtable'][$c]['subs'][] = array('Replace' => $replace, 'substitute' => $substitute, 'CompCount' => $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['CompCount']);
							}
						}
					}

					// LookupType 5: Contextual Substitution Subtable
					else if ($Lookup[$i]['Type'] == 5) {
						// Format 1: Context Substitution
						if ($SubstFormat == 1) {
							$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
							$Lookup[$i]['Subtable'][$c]['CoverageGlyphs'] = $CoverageGlyphs = $this->_getCoverage();

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['SubRuleSetCount']; $s++) {
								$SubRuleSet = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s];
								$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['FirstGlyph'] = $CoverageGlyphs[$s];
								for ($r = 0; $r < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleCount']; $r++) {
									$GlyphCount = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$r]['GlyphCount'];
									for ($g = 1; $g < $GlyphCount; $g++) {
										$glyphID = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$r]['Input'][$g];
										$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$r]['InputGlyphs'][$g] = unicode_hex($this->glyphToChar[$glyphID][0]);
									}
								}
							}
						}
						// Format 2: Class-based Context Glyph Substitution
						else if ($SubstFormat == 2) {
							$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
							$Lookup[$i]['Subtable'][$c]['CoverageGlyphs'] = $CoverageGlyphs = $this->_getCoverage();

							$InputClasses = $this->_getClasses($Lookup[$i]['Subtable'][$c]['ClassDefOffset']);
							$Lookup[$i]['Subtable'][$c]['InputClasses'] = $InputClasses;

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['SubClassSetCnt']; $s++) {
								if ($Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][$s] > 0) {
									$this->seek($Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][$s]);
									$Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRuleCnt'] = $SubClassRuleCnt = $this->read_ushort();
									$SubClassRule = array();
									for ($b = 0; $b < $SubClassRuleCnt; $b++) {
										$SubClassRule[$b] = $Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][$s] + $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRule'][$b] = $SubClassRule[$b];
									}
								}
							}

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['SubClassSetCnt']; $s++) {
								$SubClassRuleCnt = $Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRuleCnt'];
								for ($b = 0; $b < $SubClassRuleCnt; $b++) {
									if ($Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][$s] > 0) {
										$this->seek($Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRule'][$b]);
										$Rule = array();
										$Rule['InputGlyphCount'] = $this->read_ushort();
										$Rule['SubstCount'] = $this->read_ushort();
										for ($r = 1; $r < $Rule['InputGlyphCount']; $r++) {
											$Rule['Input'][$r] = $this->read_ushort();
										}
										for ($r = 0; $r < $Rule['SubstCount']; $r++) {
											$Rule['SequenceIndex'][$r] = $this->read_ushort();
											$Rule['LookupListIndex'][$r] = $this->read_ushort();
										}

										$Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRule'][$b] = $Rule;
									}
								}
							}
						}
						// Format 3: Coverage-based Context Glyph Substitution
						else if ($SubstFormat == 3) {
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['InputGlyphCount']; $b++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['CoverageInput'][$b]);
								$glyphs = $this->_getCoverage();
								$Lookup[$i]['Subtable'][$c]['CoverageInputGlyphs'][] = implode("|", $glyphs);
							}
							throw new MpdfException("Lookup Type 5, SubstFormat 3 not tested. Please report this with the name of font used - " . $this->fontkey);
						}
					}

					// LookupType 6: Chaining Contextual Substitution Subtable
					else if ($Lookup[$i]['Type'] == 6) {
						// Format 1: Simple Chaining Context Glyph Substitution  p255
						if ($SubstFormat == 1) {
							$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
							$Lookup[$i]['Subtable'][$c]['CoverageGlyphs'] = $CoverageGlyphs = $this->_getCoverage();

							$ChainSubRuleSetCnt = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSetCount'];

							for ($s = 0; $s < $ChainSubRuleSetCnt; $s++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['ChainSubRuleSetOffset'][$s]);
								$ChainSubRuleCnt = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRuleCount'] = $this->read_ushort();
								for ($r = 0; $r < $ChainSubRuleCnt; $r++) {
									$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRuleOffset'][$r] = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSetOffset'][$s] + $this->read_ushort();
								}
							}
							for ($s = 0; $s < $ChainSubRuleSetCnt; $s++) {
								$ChainSubRuleCnt = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRuleCount'];
								for ($r = 0; $r < $ChainSubRuleCnt; $r++) {
									// ChainSubRule
									$this->seek($Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRuleOffset'][$r]);

									$BacktrackGlyphCount = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['BacktrackGlyphCount'] = $this->read_ushort();
									for ($g = 0; $g < $BacktrackGlyphCount; $g++) {
										$glyphID = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['BacktrackGlyphs'][$g] = unicode_hex($this->glyphToChar[$glyphID][0]);
									}

									$InputGlyphCount = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['InputGlyphCount'] = $this->read_ushort();
									for ($g = 1; $g < $InputGlyphCount; $g++) {
										$glyphID = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['InputGlyphs'][$g] = unicode_hex($this->glyphToChar[$glyphID][0]);
									}


									$LookaheadGlyphCount = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['LookaheadGlyphCount'] = $this->read_ushort();
									for ($g = 0; $g < $LookaheadGlyphCount; $g++) {
										$glyphID = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['LookaheadGlyphs'][$g] = unicode_hex($this->glyphToChar[$glyphID][0]);
									}

									$SubstCount = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['SubstCount'] = $this->read_ushort();
									for ($lu = 0; $lu < $SubstCount; $lu++) {
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['SequenceIndex'][$lu] = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['LookupListIndex'][$lu] = $this->read_ushort();
									}
								}
							}
						}
						// Format 2: Class-based Chaining Context Glyph Substitution  p257
						else if ($SubstFormat == 2) {
							$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
							$Lookup[$i]['Subtable'][$c]['CoverageGlyphs'] = $CoverageGlyphs = $this->_getCoverage();

							$BacktrackClasses = $this->_getClasses($Lookup[$i]['Subtable'][$c]['BacktrackClassDefOffset']);
							$Lookup[$i]['Subtable'][$c]['BacktrackClasses'] = $BacktrackClasses;

							$InputClasses = $this->_getClasses($Lookup[$i]['Subtable'][$c]['InputClassDefOffset']);
							$Lookup[$i]['Subtable'][$c]['InputClasses'] = $InputClasses;

							$LookaheadClasses = $this->_getClasses($Lookup[$i]['Subtable'][$c]['LookaheadClassDefOffset']);
							$Lookup[$i]['Subtable'][$c]['LookaheadClasses'] = $LookaheadClasses;

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['ChainSubClassSetCnt']; $s++) {
								if ($Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][$s] > 0) {
									$this->seek($Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][$s]);
									$Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRuleCnt'] = $ChainSubClassRuleCnt = $this->read_ushort();
									$ChainSubClassRule = array();
									for ($b = 0; $b < $ChainSubClassRuleCnt; $b++) {
										$ChainSubClassRule[$b] = $Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][$s] + $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRule'][$b] = $ChainSubClassRule[$b];
									}
								}
							}

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['ChainSubClassSetCnt']; $s++) {
								$ChainSubClassRuleCnt = $Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRuleCnt'];
								for ($b = 0; $b < $ChainSubClassRuleCnt; $b++) {
									if ($Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][$s] > 0) {
										$this->seek($Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRule'][$b]);
										$Rule = array();
										$Rule['BacktrackGlyphCount'] = $this->read_ushort();
										for ($r = 0; $r < $Rule['BacktrackGlyphCount']; $r++) {
											$Rule['Backtrack'][$r] = $this->read_ushort();
										}
										$Rule['InputGlyphCount'] = $this->read_ushort();
										for ($r = 1; $r < $Rule['InputGlyphCount']; $r++) {
											$Rule['Input'][$r] = $this->read_ushort();
										}
										$Rule['LookaheadGlyphCount'] = $this->read_ushort();
										for ($r = 0; $r < $Rule['LookaheadGlyphCount']; $r++) {
											$Rule['Lookahead'][$r] = $this->read_ushort();
										}
										$Rule['SubstCount'] = $this->read_ushort();
										for ($r = 0; $r < $Rule['SubstCount']; $r++) {
											$Rule['SequenceIndex'][$r] = $this->read_ushort();
											$Rule['LookupListIndex'][$r] = $this->read_ushort();
										}

										$Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRule'][$b] = $Rule;
									}
								}
							}
						}
						// Format 3: Coverage-based Chaining Context Glyph Substitution  p259
						else if ($SubstFormat == 3) {
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['BacktrackGlyphCount']; $b++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['CoverageBacktrack'][$b]);
								$glyphs = $this->_getCoverage();
								$Lookup[$i]['Subtable'][$c]['CoverageBacktrackGlyphs'][] = implode("|", $glyphs);
							}
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['InputGlyphCount']; $b++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['CoverageInput'][$b]);
								$glyphs = $this->_getCoverage();
								$Lookup[$i]['Subtable'][$c]['CoverageInputGlyphs'][] = implode("|", $glyphs);
								// Don't use above value as these are ordered numerically not as need to process
							}
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['LookaheadGlyphCount']; $b++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['CoverageLookahead'][$b]);
								$glyphs = $this->_getCoverage();
								$Lookup[$i]['Subtable'][$c]['CoverageLookaheadGlyphs'][] = implode("|", $glyphs);
							}
						}
					}
				}
			}


			//=====================================================================================
			//=====================================================================================
			//=====================================================================================



			$st = $this->mpdf->OTLscript;
			$t = $this->mpdf->OTLlang;
			$langsys = $gsub[$st][$t];


			$lul = array(); // array of LookupListIndexes
			$tags = array(); // corresponding array of feature tags e.g. 'ccmp'
			foreach ($langsys AS $tag => $ft) {
				foreach ($ft AS $ll) {
					$lul[$ll] = $tag;
				}
			}
			ksort($lul); // Order the Lookups in the order they are in the GUSB table, regardless of Feature order
			$this->_getGSUBarray($Lookup, $lul, $st);
//print_r($lul); exit;
		}
//print_r($Lookup); exit;

		return array($GSUBScriptLang, $gsub, $GSLookup, $rtlPUAstr, $rtlPUAarr);
	}

/////////////////////////////////////////////////////////////////////////////////////////
	// GSUB functions
	function _getGSUBarray(&$Lookup, &$lul, $scripttag, $level = 1, $coverage = '', $exB = '', $exL = '')
	{
		// Process (3) LookupList for specific Script-LangSys
		// Generate preg_replace
		$html = '';
		if ($level == 1) {
			$html .= '<bookmark level="0" content="GSUB features">';
		}
		foreach ($lul AS $i => $tag) {
			$html .= '<div class="level' . $level . '">';
			$html .= '<h5 class="level' . $level . '">';
			if ($level == 1) {
				$html .= '<bookmark level="1" content="' . $tag . ' [#' . $i . ']">';
			}
			$html .= 'Lookup #' . $i . ' [tag: <span style="color:#000066;">' . $tag . '</span>]</h5>';
			$ignore = $this->_getGSUBignoreString($Lookup[$i]['Flag'], $Lookup[$i]['MarkFilteringSet']);
			if ($ignore) {
				$html .= '<div class="ignore">Ignoring: ' . $ignore . '</div> ';
			}

			$Type = $Lookup[$i]['Type'];
			$Flag = $Lookup[$i]['Flag'];
			if (($Flag & 0x0001) == 1) {
				$dir = 'RTL';
			} else {
				$dir = 'LTR';
			}

			for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {
				$html .= '<div class="subtable">Subtable #' . $c;
				if ($level == 1) {
					$html .= '<bookmark level="2" content="Subtable #' . $c . '">';
				}
				$html .= '</div>';

				$SubstFormat = $Lookup[$i]['Subtable'][$c]['Format'];

				// LookupType 1: Single Substitution Subtable
				if ($Lookup[$i]['Type'] == 1) {
					$html .= '<div class="lookuptype">LookupType 1: Single Substitution Subtable</div>';
					for ($s = 0; $s < count($Lookup[$i]['Subtable'][$c]['subs']); $s++) {
						$inputGlyphs = $Lookup[$i]['Subtable'][$c]['subs'][$s]['Replace'];
						$substitute = $Lookup[$i]['Subtable'][$c]['subs'][$s]['substitute'][0];
						if ($level == 2 && strpos($coverage, $inputGlyphs[0]) === false) {
							continue;
						}
						$html .= '<div class="substitution">';
						$html .= '<span class="unicode">' . $this->formatUni($inputGlyphs[0]) . '&nbsp;</span> ';
						if ($level == 2 && $exB) {
							$html .= $exB;
						}
						$html .= '<span class="unchanged">&nbsp;' . $this->formatEntity($inputGlyphs[0]) . '</span>';
						if ($level == 2 && $exL) {
							$html .= $exL;
						}
						$html .= '&nbsp; &raquo; &raquo; &nbsp;';
						if ($level == 2 && $exB) {
							$html .= $exB;
						}
						$html .= '<span class="changed">&nbsp;' . $this->formatEntity($substitute) . '</span>';
						if ($level == 2 && $exL) {
							$html .= $exL;
						}
						$html .= '&nbsp; <span class="unicode">' . $this->formatUni($substitute) . '</span> ';
						$html .= '</div>';
					}
				}
				// LookupType 2: Multiple Substitution Subtable
				else if ($Lookup[$i]['Type'] == 2) {
					$html .= '<div class="lookuptype">LookupType 2: Multiple Substitution Subtable</div>';
					for ($s = 0; $s < count($Lookup[$i]['Subtable'][$c]['subs']); $s++) {
						$inputGlyphs = $Lookup[$i]['Subtable'][$c]['subs'][$s]['Replace'];
						$substitute = $Lookup[$i]['Subtable'][$c]['subs'][$s]['substitute'];
						if ($level == 2 && strpos($coverage, $inputGlyphs[0]) === false) {
							continue;
						}
						$html .= '<div class="substitution">';
						$html .= '<span class="unicode">' . $this->formatUni($inputGlyphs[0]) . '&nbsp;</span> ';
						if ($level == 2 && $exB) {
							$html .= $exB;
						}
						$html .= '<span class="unchanged">&nbsp;' . $this->formatEntity($inputGlyphs[0]) . '</span>';
						if ($level == 2 && $exL) {
							$html .= $exL;
						}
						$html .= '&nbsp; &raquo; &raquo; &nbsp;';
						if ($level == 2 && $exB) {
							$html .= $exB;
						}
						$html .= '<span class="changed">&nbsp;' . $this->formatEntityArr($substitute) . '</span>';
						if ($level == 2 && $exL) {
							$html .= $exL;
						}
						$html .= '&nbsp; <span class="unicode">' . $this->formatUniArr($substitute) . '</span> ';
						$html .= '</div>';
					}
				}
				// LookupType 3: Alternate Forms
				else if ($Lookup[$i]['Type'] == 3) {
					$html .= '<div class="lookuptype">LookupType 3: Alternate Forms</div>';
					for ($s = 0; $s < count($Lookup[$i]['Subtable'][$c]['subs']); $s++) {
						$inputGlyphs = $Lookup[$i]['Subtable'][$c]['subs'][$s]['Replace'];
						$substitute = $Lookup[$i]['Subtable'][$c]['subs'][$s]['substitute'][0];
						if ($level == 2 && strpos($coverage, $inputGlyphs[0]) === false) {
							continue;
						}
						$html .= '<div class="substitution">';
						$html .= '<span class="unicode">' . $this->formatUni($inputGlyphs[0]) . '&nbsp;</span> ';
						if ($level == 2 && $exB) {
							$html .= $exB;
						}
						$html .= '<span class="unchanged">&nbsp;' . $this->formatEntity($inputGlyphs[0]) . '</span>';
						if ($level == 2 && $exL) {
							$html .= $exL;
						}
						$html .= '&nbsp; &raquo; &raquo; &nbsp;';
						if ($level == 2 && $exB) {
							$html .= $exB;
						}
						$html .= '<span class="changed">&nbsp;' . $this->formatEntity($substitute) . '</span>';
						if ($level == 2 && $exL) {
							$html .= $exL;
						}
						$html .= '&nbsp; <span class="unicode">' . $this->formatUni($substitute) . '</span> ';
						if (count($Lookup[$i]['Subtable'][$c]['subs'][$s]['substitute']) > 1) {
							for ($alt = 1; $alt < count($Lookup[$i]['Subtable'][$c]['subs'][$s]['substitute']); $alt++) {
								$substitute = $Lookup[$i]['Subtable'][$c]['subs'][$s]['substitute'][$alt];
								$html .= '&nbsp; | &nbsp; ALT #' . $alt . ' &nbsp; ';
								$html .= '<span class="changed">&nbsp;' . $this->formatEntity($substitute) . '</span>';
								$html .= '&nbsp; <span class="unicode">' . $this->formatUni($substitute) . '</span> ';
							}
						}
						$html .= '</div>';
					}
				}
				// LookupType 4: Ligature Substitution Subtable
				else if ($Lookup[$i]['Type'] == 4) {
					$html .= '<div class="lookuptype">LookupType 4: Ligature Substitution Subtable</div>';
					for ($s = 0; $s < count($Lookup[$i]['Subtable'][$c]['subs']); $s++) {
						$inputGlyphs = $Lookup[$i]['Subtable'][$c]['subs'][$s]['Replace'];
						$substitute = $Lookup[$i]['Subtable'][$c]['subs'][$s]['substitute'][0];
						if ($level == 2 && strpos($coverage, $inputGlyphs[0]) === false) {
							continue;
						}
						$html .= '<div class="substitution">';
						$html .= '<span class="unicode">' . $this->formatUniArr($inputGlyphs) . '&nbsp;</span> ';
						if ($level == 2 && $exB) {
							$html .= $exB;
						}
						$html .= '<span class="unchanged">&nbsp;' . $this->formatEntityArr($inputGlyphs) . '</span>';
						if ($level == 2 && $exL) {
							$html .= $exL;
						}
						$html .= '&nbsp; &raquo; &raquo; &nbsp;';
						if ($level == 2 && $exB) {
							$html .= $exB;
						}
						$html .= '<span class="changed">&nbsp;' . $this->formatEntity($substitute) . '</span>';
						if ($level == 2 && $exL) {
							$html .= $exL;
						}
						$html .= '&nbsp; <span class="unicode">' . $this->formatUni($substitute) . '</span> ';
						$html .= '</div>';
					}
				}

				// LookupType 5: Contextual Substitution Subtable
				else if ($Lookup[$i]['Type'] == 5) {
					$html .= '<div class="lookuptype">LookupType 5: Contextual Substitution Subtable</div>';
					// Format 1: Context Substitution
					if ($SubstFormat == 1) {
						$html .= '<div class="lookuptypesub">Format 1: Context Substitution</div>';
						for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['SubRuleSetCount']; $s++) {
							// SubRuleSet
							$subRule = array();
							$html .= '<div class="rule">Subrule Set: ' . $s . '</div>';
							foreach ($Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'] AS $rctr => $rule) {
								// SubRule
								$html .= '<div class="rule">SubRule: ' . $rctr . '</div>';
								$inputGlyphs = array();
								if ($rule['GlyphCount'] > 1) {
									$inputGlyphs = $rule['InputGlyphs'];
								}
								$inputGlyphs[0] = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['FirstGlyph'];
								ksort($inputGlyphs);
								$nInput = count($inputGlyphs);


								$exampleI = array();
								$html .= '<div class="context">CONTEXT: ';
								for ($ff = 0; $ff < count($inputGlyphs); $ff++) {
									$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;' . $this->formatEntityStr($inputGlyphs[$ff]) . '&nbsp;</span></div>';
									$exampleI[] = $this->formatEntityFirst($inputGlyphs[$ff]);
								}
								$html .= '</div>';


								for ($b = 0; $b < $rule['SubstCount']; $b++) {
									$lup = $rule['SubstLookupRecord'][$b]['LookupListIndex'];
									$seqIndex = $rule['SubstLookupRecord'][$b]['SequenceIndex'];

									// GENERATE exampleI[<seqIndex] .... exampleI[>seqIndex]
									$exB = '';
									$exL = '';
									if ($seqIndex > 0) {
										$exB .= '<span class="inputother">';
										for ($ip = 0; $ip < $seqIndex; $ip++) {
											$exB .= $this->formatEntity($inputGlyphs[$ip]) . '&#x200d;';
										}
										$exB .= '</span>';
									}
									if (count($inputGlyphs) > ($seqIndex + 1)) {
										$exL .= '<span class="inputother">';
										for ($ip = $seqIndex + 1; $ip < count($inputGlyphs); $ip++) {
											$exL .= $this->formatEntity($inputGlyphs[$ip]) . '&#x200d;';
										}
										$exL .= '</span>';
									}
									$html .= '<div class="sequenceIndex">Substitution Position: ' . $seqIndex . '</div>';

									$lul2 = array($lup => $tag);

									// Only apply if the (first) 'Replace' glyph from the
									// Lookup list is in the [inputGlyphs] at ['SequenceIndex']
									// Pass $inputGlyphs[$seqIndex] e.g. 00636|00645|00656
									// to level 2 and only apply if first Replace glyph is in this list
									$html .= $this->_getGSUBarray($Lookup, $lul2, $scripttag, 2, $inputGlyphs[$seqIndex], $exB, $exL);
								}


								if (count($subRule['rules']))
									$volt[] = $subRule;
							}
						}
					}
					// Format 2: Class-based Context Glyph Substitution
					else if ($SubstFormat == 2) {
						$html .= '<div class="lookuptypesub">Format 2: Class-based Context Glyph Substitution</div>';
						foreach ($Lookup[$i]['Subtable'][$c]['SubClassSet'] AS $inputClass => $cscs) {
							$html .= '<div class="rule">Input Class: ' . $inputClass . '</div>';
							for ($cscrule = 0; $cscrule < $cscs['SubClassRuleCnt']; $cscrule++) {
								$html .= '<div class="rule">Rule: ' . $cscrule . '</div>';
								$rule = $cscs['SubClassRule'][$cscrule];

								$inputGlyphs = array();

								$inputGlyphs[0] = $Lookup[$i]['Subtable'][$c]['InputClasses'][$inputClass];

								if ($rule['InputGlyphCount'] > 1) {
									//  NB starts at 1
									for ($gcl = 1; $gcl < $rule['InputGlyphCount']; $gcl++) {
										$classindex = $rule['Input'][$gcl];
										$inputGlyphs[$gcl] = $Lookup[$i]['Subtable'][$c]['InputClasses'][$classindex];
									}
								}

								// Class 0 contains all the glyphs NOT in the other classes
								$class0excl = implode('|', $Lookup[$i]['Subtable'][$c]['InputClasses']);

								$exampleI = array();
								$html .= '<div class="context">CONTEXT: ';
								for ($ff = 0; $ff < count($inputGlyphs); $ff++) {

									if (!$inputGlyphs[$ff]) {

										$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;[NOT ' . $this->formatEntityStr($class0excl) . ']&nbsp;</span></div>';
										$exampleI[] = '[NOT ' . $this->formatEntityFirst($class0excl) . ']';
									} else {
										$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;' . $this->formatEntityStr($inputGlyphs[$ff]) . '&nbsp;</span></div>';
										$exampleI[] = $this->formatEntityFirst($inputGlyphs[$ff]);
									}
								}
								$html .= '</div>';


								for ($b = 0; $b < $rule['SubstCount']; $b++) {
									$lup = $rule['LookupListIndex'][$b];
									$seqIndex = $rule['SequenceIndex'][$b];

									// GENERATE exampleI[<seqIndex] .... exampleI[>seqIndex]
									$exB = '';
									$exL = '';

									if ($seqIndex > 0) {
										$exB .= '<span class="inputother">';
										for ($ip = 0; $ip < $seqIndex; $ip++) {
											if (!$inputGlyphs[$ip]) {
												$exB .= '[*]';
											} else {
												$exB .= $this->formatEntityFirst($inputGlyphs[$ip]) . '&#x200d;';
											}
										}
										$exB .= '</span>';
									}

									if (count($inputGlyphs) > ($seqIndex + 1)) {
										$exL .= '<span class="inputother">';
										for ($ip = $seqIndex + 1; $ip < count($inputGlyphs); $ip++) {
											if (!$inputGlyphs[$ip]) {
												$exL .= '[*]';
											} else {
												$exL .= $this->formatEntityFirst($inputGlyphs[$ip]) . '&#x200d;';
											}
										}
										$exL .= '</span>';
									}

									$html .= '<div class="sequenceIndex">Substitution Position: ' . $seqIndex . '</div>';

									$lul2 = array($lup => $tag);

									// Only apply if the (first) 'Replace' glyph from the
									// Lookup list is in the [inputGlyphs] at ['SequenceIndex']
									// Pass $inputGlyphs[$seqIndex] e.g. 00636|00645|00656
									// to level 2 and only apply if first Replace glyph is in this list
									$html .= $this->_getGSUBarray($Lookup, $lul2, $scripttag, 2, $inputGlyphs[$seqIndex], $exB, $exL);
								}
								if (count($subRule['rules']))
									$volt[] = $subRule;
							}
						}
					}
					// Format 3: Coverage-based Context Glyph Substitution  p259
					else if ($SubstFormat == 3) {
						$html .= '<div class="lookuptypesub">Format 3: Coverage-based Context Glyph Substitution  </div>';
						// IgnoreMarks flag set on main Lookup table
						$inputGlyphs = $Lookup[$i]['Subtable'][$c]['CoverageInputGlyphs'];
						$CoverageInputGlyphs = implode('|', $inputGlyphs);
						$nInput = $Lookup[$i]['Subtable'][$c]['InputGlyphCount'];

						$exampleI = array();
						$html .= '<div class="context">CONTEXT: ';
						for ($ff = 0; $ff < count($inputGlyphs); $ff++) {
							$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;' . $this->formatEntityStr($inputGlyphs[$ff]) . '&nbsp;</span></div>';
							$exampleI[] = $this->formatEntityFirst($inputGlyphs[$ff]);
						}
						$html .= '</div>';


						for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['SubstCount']; $b++) {
							$lup = $Lookup[$i]['Subtable'][$c]['SubstLookupRecord'][$b]['LookupListIndex'];
							$seqIndex = $Lookup[$i]['Subtable'][$c]['SubstLookupRecord'][$b]['SequenceIndex'];
							// GENERATE exampleI[<seqIndex] .... exampleI[>seqIndex]
							$exB = '';
							$exL = '';
							if ($seqIndex > 0) {
								$exB .= '<span class="inputother">';
								for ($ip = 0; $ip < $seqIndex; $ip++) {
									$exB .= $exampleI[$ip] . '&#x200d;';
								}
								$exB .= '</span>';
							}

							if (count($inputGlyphs) > ($seqIndex + 1)) {
								$exL .= '<span class="inputother">';
								for ($ip = $seqIndex + 1; $ip < count($inputGlyphs); $ip++) {
									$exL .= $exampleI[$ip] . '&#x200d;';
								}
								$exL .= '</span>';
							}

							$html .= '<div class="sequenceIndex">Substitution Position: ' . $seqIndex . '</div>';

							$lul2 = array($lup => $tag);

							// Only apply if the (first) 'Replace' glyph from the
							// Lookup list is in the [inputGlyphs] at ['SequenceIndex']
							// Pass $inputGlyphs[$seqIndex] e.g. 00636|00645|00656
							// to level 2 and only apply if first Replace glyph is in this list
							$html .= $this->_getGSUBarray($Lookup, $lul2, $scripttag, 2, $inputGlyphs[$seqIndex], $exB, $exL);
						}
						if (count($subRule['rules']))
							$volt[] = $subRule;
					}

//print_r($Lookup[$i]);
//print_r($volt[(count($volt)-1)]); exit;
				}
				// LookupType 6: Chaining Contextual Substitution Subtable
				else if ($Lookup[$i]['Type'] == 6) {
					$html .= '<div class="lookuptype">LookupType 6: Chaining Contextual Substitution Subtable</div>';
					// Format 1: Simple Chaining Context Glyph Substitution  p255
					if ($SubstFormat == 1) {
						$html .= '<div class="lookuptypesub">Format 1: Simple Chaining Context Glyph Substitution  </div>';
						for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['ChainSubRuleSetCount']; $s++) {
							// ChainSubRuleSet
							$subRule = array();
							$html .= '<div class="rule">Subrule Set: ' . $s . '</div>';
							$firstInputGlyph = $Lookup[$i]['Subtable'][$c]['CoverageGlyphs'][$s]; // First input gyyph
							foreach ($Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'] AS $rctr => $rule) {
								$html .= '<div class="rule">SubRule: ' . $rctr . '</div>';
								// ChainSubRule
								$inputGlyphs = array();
								if ($rule['InputGlyphCount'] > 1) {
									$inputGlyphs = $rule['InputGlyphs'];
								}
								$inputGlyphs[0] = $firstInputGlyph;
								ksort($inputGlyphs);
								$nInput = count($inputGlyphs);

								if ($rule['BacktrackGlyphCount']) {
									$backtrackGlyphs = $rule['BacktrackGlyphs'];
								} else {
									$backtrackGlyphs = array();
								}

								if ($rule['LookaheadGlyphCount']) {
									$lookaheadGlyphs = $rule['LookaheadGlyphs'];
								} else {
									$lookaheadGlyphs = array();
								}


								$exampleB = array();
								$exampleI = array();
								$exampleL = array();
								$html .= '<div class="context">CONTEXT: ';
								for ($ff = count($backtrackGlyphs) - 1; $ff >= 0; $ff--) {
									$html .= '<div>Backtrack #' . $ff . ': <span class="unicode">' . $this->formatUniStr($backtrackGlyphs[$ff]) . '</span></div>';
									$exampleB[] = $this->formatEntityFirst($backtrackGlyphs[$ff]);
								}
								for ($ff = 0; $ff < count($inputGlyphs); $ff++) {
									$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;' . $this->formatEntityStr($inputGlyphs[$ff]) . '&nbsp;</span></div>';
									$exampleI[] = $this->formatEntityFirst($inputGlyphs[$ff]);
								}
								for ($ff = 0; $ff < count($lookaheadGlyphs); $ff++) {
									$html .= '<div>Lookahead #' . $ff . ': <span class="unicode">' . $this->formatUniStr($lookaheadGlyphs[$ff]) . '</span></div>';
									$exampleL[] = $this->formatEntityFirst($lookaheadGlyphs[$ff]);
								}
								$html .= '</div>';


								for ($b = 0; $b < $rule['SubstCount']; $b++) {
									$lup = $rule['LookupListIndex'][$b];
									$seqIndex = $rule['SequenceIndex'][$b];

									// GENERATE exampleB[n] exampleI[<seqIndex] .... exampleI[>seqIndex] exampleL[n]
									$exB = '';
									$exL = '';
									if (count($exampleB)) {
										$exB .= '<span class="backtrack">' . implode('&#x200d;', $exampleB) . '</span>';
									}

									if ($seqIndex > 0) {
										$exB .= '<span class="inputother">';
										for ($ip = 0; $ip < $seqIndex; $ip++) {
											$exB .= $this->formatEntity($inputGlyphs[$ip]) . '&#x200d;';
										}
										$exB .= '</span>';
									}

									if (count($inputGlyphs) > ($seqIndex + 1)) {
										$exL .= '<span class="inputother">';
										for ($ip = $seqIndex + 1; $ip < count($inputGlyphs); $ip++) {
											$exL .= $this->formatEntity($inputGlyphs[$ip]) . '&#x200d;';
										}
										$exL .= '</span>';
									}

									if (count($exampleL)) {
										$exL .= '<span class="lookahead">' . implode('&#x200d;', $exampleL) . '</span>';
									}

									$html .= '<div class="sequenceIndex">Substitution Position: ' . $seqIndex . '</div>';

									$lul2 = array($lup => $tag);

									// Only apply if the (first) 'Replace' glyph from the
									// Lookup list is in the [inputGlyphs] at ['SequenceIndex']
									// Pass $inputGlyphs[$seqIndex] e.g. 00636|00645|00656
									// to level 2 and only apply if first Replace glyph is in this list
									$html .= $this->_getGSUBarray($Lookup, $lul2, $scripttag, 2, $inputGlyphs[$seqIndex], $exB, $exL);
								}


								if (count($subRule['rules']))
									$volt[] = $subRule;
							}
						}
					}
					// Format 2: Class-based Chaining Context Glyph Substitution  p257
					else if ($SubstFormat == 2) {
						$html .= '<div class="lookuptypesub">Format 2: Class-based Chaining Context Glyph Substitution  </div>';
						foreach ($Lookup[$i]['Subtable'][$c]['ChainSubClassSet'] AS $inputClass => $cscs) {
							$html .= '<div class="rule">Input Class: ' . $inputClass . '</div>';
							for ($cscrule = 0; $cscrule < $cscs['ChainSubClassRuleCnt']; $cscrule++) {
								$html .= '<div class="rule">Rule: ' . $cscrule . '</div>';
								$rule = $cscs['ChainSubClassRule'][$cscrule];

								// These contain classes of glyphs as strings
								// $Lookup[$i]['Subtable'][$c]['InputClasses'][(class)] e.g. 02E6|02E7|02E8
								// $Lookup[$i]['Subtable'][$c]['LookaheadClasses'][(class)]
								// $Lookup[$i]['Subtable'][$c]['BacktrackClasses'][(class)]
								// These contain arrays of classIndexes
								// [Backtrack] [Lookahead] and [Input] (Input is from the second position only)

								$inputGlyphs = array();

								$inputGlyphs[0] = $Lookup[$i]['Subtable'][$c]['InputClasses'][$inputClass];
								if ($rule['InputGlyphCount'] > 1) {
									//  NB starts at 1
									for ($gcl = 1; $gcl < $rule['InputGlyphCount']; $gcl++) {
										$classindex = $rule['Input'][$gcl];
										$inputGlyphs[$gcl] = $Lookup[$i]['Subtable'][$c]['InputClasses'][$classindex];
									}
								}
								// Class 0 contains all the glyphs NOT in the other classes
								$class0excl = implode('|', $Lookup[$i]['Subtable'][$c]['InputClasses']);

								$nInput = $rule['InputGlyphCount'];

								if ($rule['BacktrackGlyphCount']) {
									for ($gcl = 0; $gcl < $rule['BacktrackGlyphCount']; $gcl++) {
										$classindex = $rule['Backtrack'][$gcl];
										$backtrackGlyphs[$gcl] = $Lookup[$i]['Subtable'][$c]['BacktrackClasses'][$classindex];
									}
								} else {
									$backtrackGlyphs = array();
								}

								if ($rule['LookaheadGlyphCount']) {
									for ($gcl = 0; $gcl < $rule['LookaheadGlyphCount']; $gcl++) {
										$classindex = $rule['Lookahead'][$gcl];
										$lookaheadGlyphs[$gcl] = $Lookup[$i]['Subtable'][$c]['LookaheadClasses'][$classindex];
									}
								} else {
									$lookaheadGlyphs = array();
								}


								$exampleB = array();
								$exampleI = array();
								$exampleL = array();
								$html .= '<div class="context">CONTEXT: ';
								for ($ff = count($backtrackGlyphs) - 1; $ff >= 0; $ff--) {
									if (!$backtrackGlyphs[$ff]) {
										$html .= '<div>Backtrack #' . $ff . ': <span class="unchanged">&nbsp;[NOT ' . $this->formatEntityStr($class0excl) . ']&nbsp;</span></div>';
										$exampleB[] = '[NOT ' . $this->formatEntityFirst($class0excl) . ']';
									} else {
										$html .= '<div>Backtrack #' . $ff . ': <span class="unicode">' . $this->formatUniStr($backtrackGlyphs[$ff]) . '</span></div>';
										$exampleB[] = $this->formatEntityFirst($backtrackGlyphs[$ff]);
									}
								}
								for ($ff = 0; $ff < count($inputGlyphs); $ff++) {
									if (!$inputGlyphs[$ff]) {
										$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;[NOT ' . $this->formatEntityStr($class0excl) . ']&nbsp;</span></div>';
										$exampleI[] = '[NOT ' . $this->formatEntityFirst($class0excl) . ']';
									} else {
										$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;' . $this->formatEntityStr($inputGlyphs[$ff]) . '&nbsp;</span></div>';
										$exampleI[] = $this->formatEntityFirst($inputGlyphs[$ff]);
									}
								}
								for ($ff = 0; $ff < count($lookaheadGlyphs); $ff++) {
									if (!$lookaheadGlyphs[$ff]) {
										$html .= '<div>Lookahead #' . $ff . ': <span class="unchanged">&nbsp;[NOT ' . $this->formatEntityStr($class0excl) . ']&nbsp;</span></div>';
										$exampleL[] = '[NOT ' . $this->formatEntityFirst($class0excl) . ']';
									} else {
										$html .= '<div>Lookahead #' . $ff . ': <span class="unicode">' . $this->formatUniStr($lookaheadGlyphs[$ff]) . '</span></div>';
										$exampleL[] = $this->formatEntityFirst($lookaheadGlyphs[$ff]);
									}
								}
								$html .= '</div>';


								for ($b = 0; $b < $rule['SubstCount']; $b++) {
									$lup = $rule['LookupListIndex'][$b];
									$seqIndex = $rule['SequenceIndex'][$b];

									// GENERATE exampleB[n] exampleI[<seqIndex] .... exampleI[>seqIndex] exampleL[n]
									$exB = '';
									$exL = '';
									if (count($exampleB)) {
										$exB .= '<span class="backtrack">' . implode('&#x200d;', $exampleB) . '</span>';
									}

									if ($seqIndex > 0) {
										$exB .= '<span class="inputother">';
										for ($ip = 0; $ip < $seqIndex; $ip++) {
											if (!$inputGlyphs[$ip]) {
												$exB .= '[*]';
											} else {
												$exB .= $this->formatEntityFirst($inputGlyphs[$ip]) . '&#x200d;';
											}
										}
										$exB .= '</span>';
									}

									if (count($inputGlyphs) > ($seqIndex + 1)) {
										$exL .= '<span class="inputother">';
										for ($ip = $seqIndex + 1; $ip < count($inputGlyphs); $ip++) {
											if (!$inputGlyphs[$ip]) {
												$exL .= '[*]';
											} else {
												$exL .= $this->formatEntityFirst($inputGlyphs[$ip]) . '&#x200d;';
											}
										}
										$exL .= '</span>';
									}

									if (count($exampleL)) {
										$exL .= '<span class="lookahead">' . implode('&#x200d;', $exampleL) . '</span>';
									}

									$html .= '<div class="sequenceIndex">Substitution Position: ' . $seqIndex . '</div>';

									$lul2 = array($lup => $tag);

									// Only apply if the (first) 'Replace' glyph from the
									// Lookup list is in the [inputGlyphs] at ['SequenceIndex']
									// Pass $inputGlyphs[$seqIndex] e.g. 00636|00645|00656
									// to level 2 and only apply if first Replace glyph is in this list
									$html .= $this->_getGSUBarray($Lookup, $lul2, $scripttag, 2, $inputGlyphs[$seqIndex], $exB, $exL);
								}
							}
						}


//print_r($Lookup[$i]['Subtable'][$c]); exit;
					}
					// Format 3: Coverage-based Chaining Context Glyph Substitution  p259
					else if ($SubstFormat == 3) {
						$html .= '<div class="lookuptypesub">Format 3: Coverage-based Chaining Context Glyph Substitution  </div>';
						// IgnoreMarks flag set on main Lookup table
						$inputGlyphs = $Lookup[$i]['Subtable'][$c]['CoverageInputGlyphs'];
						$CoverageInputGlyphs = implode('|', $inputGlyphs);
						$nInput = $Lookup[$i]['Subtable'][$c]['InputGlyphCount'];

						if ($Lookup[$i]['Subtable'][$c]['BacktrackGlyphCount']) {
							$backtrackGlyphs = $Lookup[$i]['Subtable'][$c]['CoverageBacktrackGlyphs'];
						} else {
							$backtrackGlyphs = array();
						}

						if ($Lookup[$i]['Subtable'][$c]['LookaheadGlyphCount']) {
							$lookaheadGlyphs = $Lookup[$i]['Subtable'][$c]['CoverageLookaheadGlyphs'];
						} else {
							$lookaheadGlyphs = array();
						}


						$exampleB = array();
						$exampleI = array();
						$exampleL = array();
						$html .= '<div class="context">CONTEXT: ';
						for ($ff = count($backtrackGlyphs) - 1; $ff >= 0; $ff--) {
							$html .= '<div>Backtrack #' . $ff . ': <span class="unicode">' . $this->formatUniStr($backtrackGlyphs[$ff]) . '</span></div>';
							$exampleB[] = $this->formatEntityFirst($backtrackGlyphs[$ff]);
						}
						for ($ff = 0; $ff < count($inputGlyphs); $ff++) {
							$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;' . $this->formatEntityStr($inputGlyphs[$ff]) . '&nbsp;</span></div>';
							$exampleI[] = $this->formatEntityFirst($inputGlyphs[$ff]);
						}
						for ($ff = 0; $ff < count($lookaheadGlyphs); $ff++) {
							$html .= '<div>Lookahead #' . $ff . ': <span class="unicode">' . $this->formatUniStr($lookaheadGlyphs[$ff]) . '</span></div>';
							$exampleL[] = $this->formatEntityFirst($lookaheadGlyphs[$ff]);
						}
						$html .= '</div>';


						for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['SubstCount']; $b++) {
							$lup = $Lookup[$i]['Subtable'][$c]['SubstLookupRecord'][$b]['LookupListIndex'];
							$seqIndex = $Lookup[$i]['Subtable'][$c]['SubstLookupRecord'][$b]['SequenceIndex'];


							// GENERATE exampleB[n] exampleI[<seqIndex] .... exampleI[>seqIndex] exampleL[n]
							$exB = '';
							$exL = '';
							if (count($exampleB)) {
								$exB .= '<span class="backtrack">' . implode('&#x200d;', $exampleB) . '</span>';
							}

							if ($seqIndex > 0) {
								$exB .= '<span class="inputother">';
								for ($ip = 0; $ip < $seqIndex; $ip++) {
									$exB .= $exampleI[$ip] . '&#x200d;';
								}
								$exB .= '</span>';
							}

							if (count($inputGlyphs) > ($seqIndex + 1)) {
								$exL .= '<span class="inputother">';
								for ($ip = $seqIndex + 1; $ip < count($inputGlyphs); $ip++) {
									$exL .= $exampleI[$ip] . '&#x200d;';
								}
								$exL .= '</span>';
							}

							if (count($exampleL)) {
								$exL .= '<span class="lookahead">' . implode('&#x200d;', $exampleL) . '</span>';
							}

							$html .= '<div class="sequenceIndex">Substitution Position: ' . $seqIndex . '</div>';

							$lul2 = array($lup => $tag);

							// Only apply if the (first) 'Replace' glyph from the
							// Lookup list is in the [inputGlyphs] at ['SequenceIndex']
							// Pass $inputGlyphs[$seqIndex] e.g. 00636|00645|00656
							// to level 2 and only apply if first Replace glyph is in this list
							$html .= $this->_getGSUBarray($Lookup, $lul2, $scripttag, 2, $inputGlyphs[$seqIndex], $exB, $exL);
						}
					}
				}
			}
			$html .= '</div>';
		}
		if ($level == 1) {
			$this->mpdf->WriteHTML($html);
		} else {
			return $html;
		}
//print_r($Lookup); exit;
	}

	//=====================================================================================
	//=====================================================================================
	// mPDF 5.7.1
	function _checkGSUBignore($flag, $glyph, $MarkFilteringSet)
	{
		$ignore = false;
		// Flag & 0x0008 = Ignore Marks
		if ((($flag & 0x0008) == 0x0008) && strpos($this->GlyphClassMarks, $glyph)) {
			$ignore = true;
		}
		if ((($flag & 0x0004) == 0x0004) && strpos($this->GlyphClassLigatures, $glyph)) {
			$ignore = true;
		}
		if ((($flag & 0x0002) == 0x0002) && strpos($this->GlyphClassBases, $glyph)) {
			$ignore = true;
		}
		// Flag & 0xFF?? = MarkAttachmentType
		if (($flag & 0xFF00) && strpos($this->MarkAttachmentType[($flag >> 8)], $glyph)) {
			$ignore = true;
		}
		// Flag & 0x0010 = UseMarkFilteringSet
		if (($flag & 0x0010) && strpos($this->MarkGlyphSets[$MarkFilteringSet], $glyph)) {
			$ignore = true;
		}
		return $ignore;
	}

	function _getGSUBignoreString($flag, $MarkFilteringSet)
	{
		// If ignoreFlag set, combine all ignore glyphs into -> "((?:(?: FBA1| FBA2| FBA3))*)"
		// else "()"
		// for Input - set on secondary Lookup table if in Context, and set Backtrack and Lookahead on Context Lookup
		$str = "";
		$ignoreflag = 0;

		// Flag & 0xFF?? = MarkAttachmentType
		if ($flag & 0xFF00) {
			$MarkAttachmentType = $flag >> 8;
			$ignoreflag = $flag;
			//$str = $this->MarkAttachmentType[$MarkAttachmentType];
			$str = "MarkAttachmentType[" . $MarkAttachmentType . "] ";
		}

		// Flag & 0x0010 = UseMarkFilteringSet
		if ($flag & 0x0010) {
			throw new MpdfException("This font " . $this->fontkey . " contains MarkGlyphSets");
			$str = "Mark Glyph Set: ";
			$str .= $this->MarkGlyphSets[$MarkFilteringSet];
		}

		// If Ignore Marks set, supercedes any above
		// Flag & 0x0008 = Ignore Marks
		if (($flag & 0x0008) == 0x0008) {
			$ignoreflag = 8;
			//$str = $this->GlyphClassMarks;
			$str = "Mark Glyphs ";
		}

		// Flag & 0x0004 = Ignore Ligatures
		if (($flag & 0x0004) == 0x0004) {
			$ignoreflag += 4;
			if ($str) {
				$str .= "|";
			}
			//$str .= $this->GlyphClassLigatures;
			$str .= "Ligature Glyphs ";
		}
		// Flag & 0x0002 = Ignore BaseGlyphs
		if (($flag & 0x0002) == 0x0002) {
			$ignoreflag += 2;
			if ($str) {
				$str .= "|";
			}
			//$str .= $this->GlyphClassBases;
			$str .= "Base Glyphs ";
		}
		if ($str) {
			return $str;
		} else
			return "";
	}

	// GSUB Patterns

	/*
	  BACKTRACK                        INPUT                   LOOKAHEAD
	  ==================================  ==================  ==================================
	  (FEEB|FEEC)(ign) ¦(FD12|FD13)(ign) ¦(0612)¦(ign) (0613)¦(ign) (FD12|FD13)¦(ign) (FEEB|FEEC)
	  ----------------  ----------------  -----  ------------  ---------------   ---------------
	  Backtrack 1       Backtrack 2     Input 1   Input 2       Lookahead 1      Lookahead 2
	  --------   ---    ---------  ---    ----   ---   ----   ---   ---------   ---    -------
	  \${1}  \${2}     \${3}   \${4}                      \${5+}  \${6+}    \${7+}  \${8+}

	  nBacktrack = 2               nInput = 2                 nLookahead = 2

	  nBsubs = 2xnBack          nIsubs = (nBsubs+)    nLsubs = (nBsubs+nIsubs+) 2xnLookahead
	  "\${1}\${2} "                 (nInput*2)-1               "\${5+} \${6+}"
	  "REPL"

	  ¦\${1}\${2} ¦\${3}\${4} ¦REPL¦\${5+} \${6+}¦\${7+} \${8+}¦


	  INPUT nInput = 5
	  ============================================================
	  ¦(0612)¦(ign) (0613)¦(ign) (0614)¦(ign) (0615)¦(ign) (0615)¦
	  \${1}  \${2}  \${3}  \${4} \${5} \${6}  \${7} \${8}  \${9} (All backreference numbers are + nBsubs)
	  -----  ------------ ------------ ------------ ------------
	  Input 1   Input 2      Input 3      Input 4      Input 5

	  A======  SequenceIndex=1 ; Lookup match nGlyphs=1
	  B===================  SequenceIndex=1 ; Lookup match nGlyphs=2
	  C===============================  SequenceIndex=1 ; Lookup match nGlyphs=3
	  D=======================  SequenceIndex=2 ; Lookup match nGlyphs=2
	  E=====================================  SequenceIndex=2 ; Lookup match nGlyphs=3
	  F======================  SequenceIndex=4 ; Lookup match nGlyphs=2

	  All backreference numbers are + nBsubs
	  A - "REPL\${2} \${3}\${4} \${5}\${6} \${7}\${8} \${9}"
	  B - "REPL\${2}\${4} \${5}\${6} \${7}\${8} \${9}"
	  C - "REPL\${2}\${4}\${6} \${7}\${8} \${9}"
	  D - "\${1} REPL\${2}\${4}\${6} \${7}\${8} \${9}"
	  E - "\${1} REPL\${2}\${4}\${6}\${8} \${9}"
	  F - "\${1}\${2} \${3}\${4} \${5} REPL\${6}\${8}"
	 */

	function _makeGSUBcontextInputMatch($inputGlyphs, $ignore, $lookupGlyphs, $seqIndex)
	{
		// $ignore = "((?:(?: FBA1| FBA2| FBA3))*)" or "()"
		// Returns e.g. ¦(0612)¦(ignore) (0613)¦(ignore) (0614)¦
		// $inputGlyphs = array of glyphs(glyphstrings) making up Input sequence in Context
		// $lookupGlyphs = array of glyphs (single Glyphs) making up Lookup Input sequence
		$mLen = count($lookupGlyphs);  // nGlyphs in the secondary Lookup match
		$nInput = count($inputGlyphs); // nGlyphs in the Primary Input sequence
		$str = "";
		for ($i = 0; $i < $nInput; $i++) {
			if ($i > 0) {
				$str .= $ignore . " ";
			}
			if ($i >= $seqIndex && $i < ($seqIndex + $mLen)) {
				$str .= "" . $lookupGlyphs[($i - $seqIndex)] . "";
			} else {
				$str .= "" . $inputGlyphs[($i)] . "";
			}
		}
		return $str;
	}

	function _makeGSUBinputMatch($inputGlyphs, $ignore)
	{
		// $ignore = "((?:(?: FBA1| FBA2| FBA3))*)" or "()"
		// Returns e.g. ¦(0612)¦(ignore) (0613)¦(ignore) (0614)¦
		// $inputGlyphs = array of glyphs(glyphstrings) making up Input sequence in Context
		// $lookupGlyphs = array of glyphs making up Lookup Input sequence - if applicable
		$str = "";
		for ($i = 1; $i <= count($inputGlyphs); $i++) {
			if ($i > 1) {
				$str .= $ignore . " ";
			}
			$str .= "" . $inputGlyphs[($i - 1)] . "";
		}
		return $str;
	}

	function _makeGSUBbacktrackMatch($backtrackGlyphs, $ignore)
	{
		// $ignore = "((?:(?: FBA1| FBA2| FBA3))*)" or "()"
		// Returns e.g. ¦(FEEB|FEEC)(ignore) ¦(FD12|FD13)(ignore) ¦
		// $backtrackGlyphs = array of glyphstrings making up Backtrack sequence
		// 3  2  1  0
		// each item being e.g. E0AD|E0AF|F1FD
		$str = "";
		for ($i = (count($backtrackGlyphs) - 1); $i >= 0; $i--) {
			$str .= "" . $backtrackGlyphs[$i] . " " . $ignore . " ";
		}
		return $str;
	}

	function _makeGSUBlookaheadMatch($lookaheadGlyphs, $ignore)
	{
		// $ignore = "((?:(?: FBA1| FBA2| FBA3))*)" or "()"
		// Returns e.g. ¦(ignore) (FD12|FD13)¦(ignore) (FEEB|FEEC)¦
		// $lookaheadGlyphs = array of glyphstrings making up Lookahead sequence
		// 0  1  2  3
		// each item being e.g. E0AD|E0AF|F1FD
		$str = "";
		for ($i = 0; $i < count($lookaheadGlyphs); $i++) {
			$str .= $ignore . " " . $lookaheadGlyphs[$i] . "";
		}
		return $str;
	}

	function _makeGSUBinputReplacement($nInput, $REPL, $ignore, $nBsubs, $mLen, $seqIndex)
	{
		// Returns e.g. "REPL\${6}\${8}" or "\${1}\${2} \${3} REPL\${4}\${6}\${8} \${9}"
		// $nInput	nGlyphs in the Primary Input sequence
		// $REPL 	replacement glyphs from secondary lookup
		// $ignore = "((?:(?: FBA1| FBA2| FBA3))*)" or "()"
		// $nBsubs	Number of Backtrack substitutions (= 2x Number of Backtrack glyphs)
		// $mLen 	nGlyphs in the secondary Lookup match - if no secondary lookup, should=$nInput
		// $seqIndex	Sequence Index to apply the secondary match
		if ($ignore == "()") {
			$ign = false;
		} else {
			$ign = true;
		}
		$str = "";
		if ($nInput == 1) {
			$str = $REPL;
		} else if ($nInput > 1) {
			if ($mLen == $nInput) { // whole string replaced
				$str = $REPL;
				if ($ign) {
					// for every nInput over 1, add another replacement backreference, to move IGNORES after replacement
					for ($x = 2; $x <= $nInput; $x++) {
						$str .= '\\' . ($nBsubs + (2 * ($x - 1)));
					}
				}
			} else { // if only part of string replaced:
				for ($x = 1; $x < ($seqIndex + 1); $x++) {
					if ($x == 1) {
						$str .= '\\' . ($nBsubs + 1);
					} else {
						if ($ign) {
							$str .= '\\' . ($nBsubs + (2 * ($x - 1)));
						}
						$str .= ' \\' . ($nBsubs + 1 + (2 * ($x - 1)));
					}
				}
				if ($seqIndex > 0) {
					$str .= " ";
				}
				$str .= $REPL;
				if ($ign) {
					for ($x = (max(($seqIndex + 1), 2)); $x < ($seqIndex + 1 + $mLen); $x++) { //  move IGNORES after replacement
						$str .= '\\' . ($nBsubs + (2 * ($x - 1)));
					}
				}
				for ($x = ($seqIndex + 1 + $mLen); $x <= $nInput; $x++) {
					if ($ign) {
						$str .= '\\' . ($nBsubs + (2 * ($x - 1)));
					}
					$str .= ' \\' . ($nBsubs + 1 + (2 * ($x - 1)));
				}
			}
		}
		return $str;
	}

	//////////////////////////////////////////////////////////////////////////////////
	function _getCoverage($convert2hex = true)
	{
		$g = array();
		$CoverageFormat = $this->read_ushort();
		if ($CoverageFormat == 1) {
			$CoverageGlyphCount = $this->read_ushort();
			for ($gid = 0; $gid < $CoverageGlyphCount; $gid++) {
				$glyphID = $this->read_ushort();
				if ($convert2hex) {
					$g[] = unicode_hex($this->glyphToChar[$glyphID][0]);
				} else {
					$g[] = $glyphID;
				}
			}
		}
		if ($CoverageFormat == 2) {
			$RangeCount = $this->read_ushort();
			for ($r = 0; $r < $RangeCount; $r++) {
				$start = $this->read_ushort();
				$end = $this->read_ushort();
				$StartCoverageIndex = $this->read_ushort(); // n/a
				for ($gid = $start; $gid <= $end; $gid++) {
					$glyphID = $gid;
					if ($convert2hex) {
						$g[] = unicode_hex($this->glyphToChar[$glyphID][0]);
					} else {
						$g[] = $glyphID;
					}
				}
			}
		}
		return $g;
	}

	//////////////////////////////////////////////////////////////////////////////////
	function _getClasses($offset)
	{
		$this->seek($offset);
		$ClassFormat = $this->read_ushort();
		$GlyphByClass = array();
		if ($ClassFormat == 1) {
			$StartGlyph = $this->read_ushort();
			$GlyphCount = $this->read_ushort();
			for ($i = 0; $i < $GlyphCount; $i++) {
				$startGlyphID = $StartGlyph + $i;
				$endGlyphID = $StartGlyph + $i;
				$class = $this->read_ushort();
				for ($g = $startGlyphID; $g <= $endGlyphID; $g++) {
					if ($this->glyphToChar[$g][0]) {
						$GlyphByClass[$class][] = unicode_hex($this->glyphToChar[$g][0]);
					}
				}
			}
		} else if ($ClassFormat == 2) {
			$tableCount = $this->read_ushort();
			for ($i = 0; $i < $tableCount; $i++) {
				$startGlyphID = $this->read_ushort();
				$endGlyphID = $this->read_ushort();
				$class = $this->read_ushort();
				for ($g = $startGlyphID; $g <= $endGlyphID; $g++) {
					if ($this->glyphToChar[$g][0]) {
						$GlyphByClass[$class][] = unicode_hex($this->glyphToChar[$g][0]);
					}
				}
			}
		}
		$gbc = array();
		foreach ($GlyphByClass AS $class => $garr) {
			$gbc[$class] = implode('|', $garr);
		}
		return $gbc;
	}

	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	function _getGPOStables()
	{
		///////////////////////////////////
		// GPOS - Glyph Positioning
		///////////////////////////////////
		if (isset($this->tables["GPOS"])) {
			$this->mpdf->WriteHTML('<h1>GPOS Tables</h1>');
			$ffeats = array();
			$gpos_offset = $this->seek_table("GPOS");
			$this->skip(4);
			$ScriptList_offset = $gpos_offset + $this->read_ushort();
			$FeatureList_offset = $gpos_offset + $this->read_ushort();
			$LookupList_offset = $gpos_offset + $this->read_ushort();

			// ScriptList
			$this->seek($ScriptList_offset);
			$ScriptCount = $this->read_ushort();
			for ($i = 0; $i < $ScriptCount; $i++) {
				$ScriptTag = $this->read_tag(); // = "beng", "deva" etc.
				$ScriptTableOffset = $this->read_ushort();
				$ffeats[$ScriptTag] = $ScriptList_offset + $ScriptTableOffset;
			}

			// Script Table
			foreach ($ffeats AS $t => $o) {
				$ls = array();
				$this->seek($o);
				$DefLangSys_offset = $this->read_ushort();
				if ($DefLangSys_offset > 0) {
					$ls['DFLT'] = $DefLangSys_offset + $o;
				}
				$LangSysCount = $this->read_ushort();
				for ($i = 0; $i < $LangSysCount; $i++) {
					$LangTag = $this->read_tag(); // =
					$LangTableOffset = $this->read_ushort();
					$ls[$LangTag] = $o + $LangTableOffset;
				}
				$ffeats[$t] = $ls;
			}


			// Get FeatureIndexList
			// LangSys Table - from first listed langsys
			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $o) {
					$FeatureIndex = array();
					$langsystable_offset = $o;
					$this->seek($langsystable_offset);
					$LookUpOrder = $this->read_ushort(); //==NULL
					$ReqFeatureIndex = $this->read_ushort();
					if ($ReqFeatureIndex != 0xFFFF) {
						$FeatureIndex[] = $ReqFeatureIndex;
					}
					$FeatureCount = $this->read_ushort();
					for ($i = 0; $i < $FeatureCount; $i++) {
						$FeatureIndex[] = $this->read_ushort(); // = index of feature
					}
					$ffeats[$st][$t] = $FeatureIndex;
				}
			}
//print_r($ffeats); exit;
			// Feauture List => LookupListIndex es
			$this->seek($FeatureList_offset);
			$FeatureCount = $this->read_ushort();
			$Feature = array();
			for ($i = 0; $i < $FeatureCount; $i++) {
				$Feature[$i] = array('tag' => $this->read_tag());
				$Feature[$i]['offset'] = $FeatureList_offset + $this->read_ushort();
			}
			for ($i = 0; $i < $FeatureCount; $i++) {
				$this->seek($Feature[$i]['offset']);
				$this->read_ushort(); // null
				$Feature[$i]['LookupCount'] = $Lookupcount = $this->read_ushort();
				$Feature[$i]['LookupListIndex'] = array();
				for ($c = 0; $c < $Lookupcount; $c++) {
					$Feature[$i]['LookupListIndex'][] = $this->read_ushort();
				}
			}


			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $o) {
					$FeatureIndex = $ffeats[$st][$t];
					foreach ($FeatureIndex AS $k => $fi) {
						$ffeats[$st][$t][$k] = $Feature[$fi];
					}
				}
			}
//print_r($ffeats); exit;
			//=====================================================================================
			$gpos = array();
			$GPOSScriptLang = array();
			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $langsys) {
					$lg = array();
					foreach ($langsys AS $ft) {
						$lg[$ft['LookupListIndex'][0]] = $ft;
					}
					// list of Lookups in order they need to be run i.e. order listed in Lookup table
					ksort($lg);
					foreach ($lg AS $ft) {
						$gpos[$st][$t][$ft['tag']] = $ft['LookupListIndex'];
					}
					if (!isset($GPOSScriptLang[$st])) {
						$GPOSScriptLang[$st] = '';
					}
					$GPOSScriptLang[$st] .= $t . ' ';
				}
			}
			if ($this->mode == 'summary') {
				$this->mpdf->WriteHTML('<h3>GPOS Scripts &amp; Languages</h3>');
				$html = '';
				if (count($gpos)) {
					foreach ($gpos AS $st => $g) {
						$html .= '<h5>' . $st . '</h5>';
						foreach ($g AS $l => $t) {
							$html .= '<div><a href="font_dump_OTL.php?script=' . $st . '&lang=' . $l . '">' . $l . '</a></b>: ';
							foreach ($t AS $tag => $o) {
								$html .= $tag . ' ';
							}
							$html .= '</div>';
						}
					}
				} else {
					$html .= '<div>No entries in GPOS table.</div>';
				}
				$this->mpdf->WriteHTML($html);
				$this->mpdf->WriteHTML('</div>');
				return 0;
			}



			//=====================================================================================
			// Get metadata and offsets for whole Lookup List table
			$this->seek($LookupList_offset);
			$LookupCount = $this->read_ushort();
			$Lookup = array();
			$Offsets = array();
			$SubtableCount = array();
			for ($i = 0; $i < $LookupCount; $i++) {
				$Offsets[$i] = $LookupList_offset + $this->read_ushort();
			}
			for ($i = 0; $i < $LookupCount; $i++) {
				$this->seek($Offsets[$i]);
				$Lookup[$i]['Type'] = $this->read_ushort();
				$Lookup[$i]['Flag'] = $flag = $this->read_ushort();
				$Lookup[$i]['SubtableCount'] = $SubtableCount[$i] = $this->read_ushort();
				for ($c = 0; $c < $SubtableCount[$i]; $c++) {
					$Lookup[$i]['Subtables'][$c] = $Offsets[$i] + $this->read_ushort();
				}
				// MarkFilteringSet = Index (base 0) into GDEF mark glyph sets structure
				if (($flag & 0x0010) == 0x0010) {
					$Lookup[$i]['MarkFilteringSet'] = $this->read_ushort();
				}
				// else { $Lookup[$i]['MarkFilteringSet'] = ''; }
				// Lookup Type 9: Extension
				if ($Lookup[$i]['Type'] == 9) {
					// Overwrites new offset (32-bit) for each subtable, and a new lookup Type
					for ($c = 0; $c < $SubtableCount[$i]; $c++) {
						$this->seek($Lookup[$i]['Subtables'][$c]);
						$ExtensionPosFormat = $this->read_ushort();
						$type = $this->read_ushort();
						$Lookup[$i]['Subtables'][$c] = $Lookup[$i]['Subtables'][$c] + $this->read_ulong();
					}
					$Lookup[$i]['Type'] = $type;
				}
			}


			//=====================================================================================

			$st = $this->mpdf->OTLscript;
			$t = $this->mpdf->OTLlang;
			$langsys = $gpos[$st][$t];


			$lul = array(); // array of LookupListIndexes
			$tags = array(); // corresponding array of feature tags e.g. 'ccmp'
			if (count($langsys)) {
				foreach ($langsys AS $tag => $ft) {
					foreach ($ft AS $ll) {
						$lul[$ll] = $tag;
					}
				}
			}
			ksort($lul); // Order the Lookups in the order they are in the GUSB table, regardless of Feature order
			$this->_getGPOSarray($Lookup, $lul, $st);
//print_r($lul); exit;


			return array($GPOSScriptLang, $gpos, $Lookup);
		} // end if GPOS
	}

	//////////////////////////////////////////////////////////////////////////////////
	//=====================================================================================
	//=====================================================================================
	//=====================================================================================
/////////////////////////////////////////////////////////////////////////////////////////
	// GPOS functions
	function _getGPOSarray(&$Lookup, $lul, $scripttag, $level = 1, $lcoverage = '', $exB = '', $exL = '')
	{
		// Process (3) LookupList for specific Script-LangSys
		$html = '';
		if ($level == 1) {
			$html .= '<bookmark level="0" content="GPOS features">';
		}
		foreach ($lul AS $luli => $tag) {
			$html .= '<div class="level' . $level . '">';
			$html .= '<h5 class="level' . $level . '">';
			if ($level == 1) {
				$html .= '<bookmark level="1" content="' . $tag . ' [#' . $luli . ']">';
			}
			$html .= 'Lookup #' . $luli . ' [tag: <span style="color:#000066;">' . $tag . '</span>]</h5>';
			$ignore = $this->_getGSUBignoreString($Lookup[$luli]['Flag'], $Lookup[$luli]['MarkFilteringSet']);
			if ($ignore) {
				$html .= '<div class="ignore">Ignoring: ' . $ignore . '</div> ';
			}

			$Type = $Lookup[$luli]['Type'];
			$Flag = $Lookup[$luli]['Flag'];
			if (($Flag & 0x0001) == 1) {
				$dir = 'RTL';
			} else {
				$dir = 'LTR';
			}

			for ($c = 0; $c < $Lookup[$luli]['SubtableCount']; $c++) {
				$html .= '<div class="subtable">Subtable #' . $c;
				if ($level == 1) {
					$html .= '<bookmark level="2" content="Subtable #' . $c . '">';
				}
				$html .= '</div>';

				// Lets start
				$subtable_offset = $Lookup[$luli]['Subtables'][$c];
				$this->seek($subtable_offset);
				$PosFormat = $this->read_ushort();

				////////////////////////////////////////////////////////////////////////////////
				// LookupType 1: Single adjustment 	Adjust position of a single glyph (e.g. SmallCaps/Sups/Subs)
				////////////////////////////////////////////////////////////////////////////////
				if ($Lookup[$luli]['Type'] == 1) {
					$html .= '<div class="lookuptype">LookupType 1: Single adjustment [Format ' . $PosFormat . ']</div>';
					//===========
					// Format 1:
					//===========
					if ($PosFormat == 1) {
						$Coverage = $subtable_offset + $this->read_ushort();
						$ValueFormat = $this->read_ushort();
						$Value = $this->_getValueRecord($ValueFormat);

						$this->seek($Coverage);
						$glyphs = $this->_getCoverage(); // Array of Hex Glyphs
						for ($g = 0; $g < count($glyphs); $g++) {
							if ($level == 2 && strpos($lcoverage, $glyphs[$g]) === false) {
								continue;
							}

							$html .= '<div class="substitution">';
							$html .= '<span class="unicode">' . $this->formatUni($glyphs[$g]) . '&nbsp;</span> ';
							if ($level == 2 && $exB) {
								$html .= $exB;
							}
							$html .= '<span class="unchanged">&nbsp;' . $this->formatEntity($glyphs[$g]) . '</span>';
							if ($level == 2 && $exL) {
								$html .= $exL;
							}
							$html .= '&nbsp; &raquo; &raquo; &nbsp;';
							if ($level == 2 && $exB) {
								$html .= $exB;
							}
							$html .= '<span class="changed" style="font-feature-settings:\'' . $tag . '\' 1;">&nbsp;' . $this->formatEntity($glyphs[$g]) . '</span>';
							if ($level == 2 && $exL) {
								$html .= $exL;
							}
							$html .= ' <span class="unicode">';
							if ($Value['XPlacement']) {
								$html .= ' Xpl: ' . $Value['XPlacement'] . ';';
							}
							if ($Value['YPlacement']) {
								$html .= ' YPl: ' . $Value['YPlacement'] . ';';
							}
							if ($Value['XAdvance']) {
								$html .= ' Xadv: ' . $Value['XAdvance'];
							}
							$html .= '</span>';
							$html .= '</div>';
						}
					}
					//===========
					// Format 2:
					//===========
					else if ($PosFormat == 2) {
						$Coverage = $subtable_offset + $this->read_ushort();
						$ValueFormat = $this->read_ushort();
						$ValueCount = $this->read_ushort();
						$Values = array();
						for ($v = 0; $v < $ValueCount; $v++) {
							$Values[] = $this->_getValueRecord($ValueFormat);
						}

						$this->seek($Coverage);
						$glyphs = $this->_getCoverage(); // Array of Hex Glyphs

						for ($g = 0; $g < count($glyphs); $g++) {
							if ($level == 2 && strpos($lcoverage, $glyphs[$g]) === false) {
								continue;
							}
							$Value = $Values[$g];

							$html .= '<div class="substitution">';
							$html .= '<span class="unicode">' . $this->formatUni($glyphs[$g]) . '&nbsp;</span> ';
							if ($level == 2 && $exB) {
								$html .= $exB;
							}
							$html .= '<span class="unchanged">&nbsp;' . $this->formatEntity($glyphs[$g]) . '</span>';
							if ($level == 2 && $exL) {
								$html .= $exL;
							}
							$html .= '&nbsp; &raquo; &raquo; &nbsp;';
							if ($level == 2 && $exB) {
								$html .= $exB;
							}
							$html .= '<span class="changed" style="font-feature-settings:\'' . $tag . '\' 1;">&nbsp;' . $this->formatEntity($glyphs[$g]) . '</span>';
							if ($level == 2 && $exL) {
								$html .= $exL;
							}
							$html .= ' <span class="unicode">';
							if ($Value['XPlacement']) {
								$html .= ' Xpl: ' . $Value['XPlacement'] . ';';
							}
							if ($Value['YPlacement']) {
								$html .= ' YPl: ' . $Value['YPlacement'] . ';';
							}
							if ($Value['XAdvance']) {
								$html .= ' Xadv: ' . $Value['XAdvance'];
							}
							$html .= '</span>';
							$html .= '</div>';
						}
					}
				}
				////////////////////////////////////////////////////////////////////////////////
				// LookupType 2: Pair adjustment 	Adjust position of a pair of glyphs (Kerning)
				////////////////////////////////////////////////////////////////////////////////
				else if ($Lookup[$luli]['Type'] == 2) {
					$html .= '<div class="lookuptype">LookupType 2: Pair adjustment e.g. Kerning [Format ' . $PosFormat . ']</div>';
					$Coverage = $subtable_offset + $this->read_ushort();
					$ValueFormat1 = $this->read_ushort();
					$ValueFormat2 = $this->read_ushort();
					//===========
					// Format 1:
					//===========
					if ($PosFormat == 1) {
						$PairSetCount = $this->read_ushort();
						$PairSetOffset = array();
						for ($p = 0; $p < $PairSetCount; $p++) {
							$PairSetOffset[] = $subtable_offset + $this->read_ushort();
						}
						$this->seek($Coverage);
						$glyphs = $this->_getCoverage(); // Array of Hex Glyphs
						for ($p = 0; $p < $PairSetCount; $p++) {
							if ($level == 2 && strpos($lcoverage, $glyphs[$p]) === false) {
								continue;
							}
							$this->seek($PairSetOffset[$p]);
							// First Glyph = $glyphs[$p]
// Takes too long e.g. Calibri font - just list kerning pairs with this:
							$html .= '<div class="glyphs">';
							$html .= '<span class="unchanged">&nbsp;' . $this->formatEntity($glyphs[$p]) . ' </span>';

							//PairSet table
							$PairValueCount = $this->read_ushort();
							for ($pv = 0; $pv < $PairValueCount; $pv++) {
								//PairValueRecord
								$gid = $this->read_ushort();
								$SecondGlyph = unicode_hex($this->glyphToChar[$gid][0]);
								$Value1 = $this->_getValueRecord($ValueFormat1);
								$Value2 = $this->_getValueRecord($ValueFormat2);

								// If RTL pairs, GPOS declares a XPlacement e.g. -180 for an XAdvance of -180 to take
								// account of direction. mPDF does not need the XPlacement adjustment
								if ($dir == 'RTL' && $Value1['XPlacement']) {
									$Value1['XPlacement'] -= $Value1['XAdvance'];
								}

								if ($ValueFormat2) {
									// If RTL pairs, GPOS declares a XPlacement e.g. -180 for an XAdvance of -180 to take
									// account of direction. mPDF does not need the XPlacement adjustment
									if ($dir == 'RTL' && $Value2['XPlacement'] && $Value2['XAdvance']) {
										$Value2['XPlacement'] -= $Value2['XAdvance'];
									}
								}

								$html .= ' ' . $this->formatEntity($SecondGlyph) . ' ';

								/*
								  $html .= '<div class="substitution">';
								  $html .= '<span class="unicode">'.$this->formatUni($glyphs[$p]).'&nbsp;</span> ';
								  if ($level==2 && $exB) { $html .= $exB; }
								  $html .= '<span class="unchanged">&nbsp;'.$this->formatEntity($glyphs[$p]).$this->formatEntity($SecondGlyph).'</span>';
								  if ($level==2 && $exL) { $html .= $exL; }
								  $html .= '&nbsp; &raquo; &raquo; &nbsp;';
								  if ($level==2 && $exB) { $html .= $exB; }
								  $html .= '<span class="changed" style="font-feature-settings:\''.$tag.'\' 1;">&nbsp;'.$this->formatEntity($glyphs[$p]).$this->formatEntity($SecondGlyph).'</span>';
								  if ($level==2 && $exL) { $html .= $exL; }
								  $html .= ' <span class="unicode">';
								  if ($Value1['XPlacement']) { $html .= ' Xpl[1]: '.$Value1['XPlacement'].';'; }
								  if ($Value1['YPlacement']) { $html .= ' YPl[1]: '.$Value1['YPlacement'].';'; }
								  if ($Value1['XAdvance']) { $html .= ' Xadv[1]: '.$Value1['XAdvance']; }
								  if ($Value2['XPlacement']) { $html .= ' Xpl[2]: '.$Value2['XPlacement'].';'; }
								  if ($Value2['YPlacement']) { $html .= ' YPl[2]: '.$Value2['YPlacement'].';'; }
								  if ($Value2['XAdvance']) { $html .= ' Xadv[2]: '.$Value2['XAdvance']; }
								  $html .= '</span>';
								  $html .= '</div>';
								 */
							}
							$html .= '</div>';
						}
					}
					//===========
					// Format 2:
					//===========
					else if ($PosFormat == 2) {
						$ClassDef1 = $subtable_offset + $this->read_ushort();
						$ClassDef2 = $subtable_offset + $this->read_ushort();
						$Class1Count = $this->read_ushort();
						$Class2Count = $this->read_ushort();

						$sizeOfPair = ( 2 * $this->count_bits($ValueFormat1) ) + ( 2 * $this->count_bits($ValueFormat2) );
						$sizeOfValueRecords = $Class1Count * $Class2Count * $sizeOfPair;


						// NB Class1Count includes Class 0 even though it is not defined by $ClassDef1
						// i.e. Class1Count = 5; Class1 will contain array(indices 1-4);
						$Class1 = $this->_getClassDefinitionTable($ClassDef1);
						$Class2 = $this->_getClassDefinitionTable($ClassDef2);

						$this->seek($subtable_offset + 16);

						for ($i = 0; $i < $Class1Count; $i++) {
							for ($j = 0; $j < $Class2Count; $j++) {
								$Value1 = $this->_getValueRecord($ValueFormat1);
								$Value2 = $this->_getValueRecord($ValueFormat2);

								// If RTL pairs, GPOS declares a XPlacement e.g. -180 for an XAdvance of -180
								// of direction. mPDF does not need the XPlacement adjustment
								if ($dir == 'RTL' && $Value1['XPlacement'] && $Value1['XAdvance']) {
									$Value1['XPlacement'] -= $Value1['XAdvance'];
								}
								if ($ValueFormat2) {
									if ($dir == 'RTL' && $Value2['XPlacement'] && $Value2['XAdvance']) {
										$Value2['XPlacement'] -= $Value2['XAdvance'];
									}
								}


								for ($c1 = 0; $c1 < count($Class1[$i]); $c1++) {

									$FirstGlyph = $Class1[$i][$c1];
									if ($level == 2 && strpos($lcoverage, $FirstGlyph) === false) {
										continue;
									}


									for ($c2 = 0; $c2 < count($Class2[$j]); $c2++) {
										$SecondGlyph = $Class2[$j][$c2];


										if (!$Value1['XPlacement'] && !$Value1['YPlacement'] && !$Value1['XAdvance'] && !$Value2['XPlacement'] && !$Value2['YPlacement'] && !$Value2['XAdvance']) {
											continue;
										}


										$html .= '<div class="substitution">';
										$html .= '<span class="unicode">' . $this->formatUni($FirstGlyph) . '&nbsp;</span> ';
										if ($level == 2 && $exB) {
											$html .= $exB;
										}
										$html .= '<span class="unchanged">&nbsp;' . $this->formatEntity($FirstGlyph) . $this->formatEntity($SecondGlyph) . '</span>';
										if ($level == 2 && $exL) {
											$html .= $exL;
										}
										$html .= '&nbsp; &raquo; &raquo; &nbsp;';
										if ($level == 2 && $exB) {
											$html .= $exB;
										}
										$html .= '<span class="changed" style="font-feature-settings:\'' . $tag . '\' 1;">&nbsp;' . $this->formatEntity($FirstGlyph) . $this->formatEntity($SecondGlyph) . '</span>';
										if ($level == 2 && $exL) {
											$html .= $exL;
										}
										$html .= ' <span class="unicode">';
										if ($Value1['XPlacement']) {
											$html .= ' Xpl[1]: ' . $Value1['XPlacement'] . ';';
										}
										if ($Value1['YPlacement']) {
											$html .= ' YPl[1]: ' . $Value1['YPlacement'] . ';';
										}
										if ($Value1['XAdvance']) {
											$html .= ' Xadv[1]: ' . $Value1['XAdvance'];
										}
										if ($Value2['XPlacement']) {
											$html .= ' Xpl[2]: ' . $Value2['XPlacement'] . ';';
										}
										if ($Value2['YPlacement']) {
											$html .= ' YPl[2]: ' . $Value2['YPlacement'] . ';';
										}
										if ($Value2['XAdvance']) {
											$html .= ' Xadv[2]: ' . $Value2['XAdvance'];
										}
										$html .= '</span>';
										$html .= '</div>';
									}
								}
							}
						}
					}
				}
				////////////////////////////////////////////////////////////////////////////////
				// LookupType 3: Cursive attachment 	Attach cursive glyphs
				////////////////////////////////////////////////////////////////////////////////
				else if ($Lookup[$luli]['Type'] == 3) {
					$html .= '<div class="lookuptype">LookupType 3: Cursive attachment </div>';
					$Coverage = $subtable_offset + $this->read_ushort();
					$EntryExitCount = $this->read_ushort();
					$EntryAnchors = array();
					$ExitAnchors = array();
					for ($i = 0; $i < $EntryExitCount; $i++) {
						$EntryAnchors[$i] = $this->read_ushort();
						$ExitAnchors[$i] = $this->read_ushort();
					}

					$this->seek($Coverage);
					$Glyphs = $this->_getCoverage();
					for ($i = 0; $i < $EntryExitCount; $i++) {
						// Need default XAdvance for glyph
						$pdfWidth = $this->mpdf->_getCharWidth($this->mpdf->fonts[$this->fontkey]['cw'], hexdec($Glyphs[$i]));
						$EntryAnchor = $EntryAnchors[$i];
						$ExitAnchor = $ExitAnchors[$i];
						$html .= '<div class="glyphs">';
						$html .= '<span class="unchanged">' . $this->formatEntity($Glyphs[$i]) . ' </span> ';
						$html .= '<span class="unicode"> ' . $this->formatUni($Glyphs[$i]) . ' => ';

						if ($EntryAnchor != 0) {
							$EntryAnchor += $subtable_offset;
							list($x, $y) = $this->_getAnchorTable($EntryAnchor);
							if ($dir == 'RTL') {
								if (round($pdfWidth) == round($x * 1000 / $this->mpdf->fonts[$this->fontkey]['desc']['unitsPerEm'])) {
									$x = 0;
								} else {
									$x = $x - ($pdfWidth * $this->mpdf->fonts[$this->fontkey]['desc']['unitsPerEm'] / 1000);
								}
							}
							$html .= " Entry X: " . $x . " Y: " . $y . "; ";
						}
						if ($ExitAnchor != 0) {
							$ExitAnchor += $subtable_offset;
							list($x, $y) = $this->_getAnchorTable($ExitAnchor);
							if ($dir == 'LTR') {
								if (round($pdfWidth) == round($x * 1000 / $this->mpdf->fonts[$this->fontkey]['desc']['unitsPerEm'])) {
									$x = 0;
								} else {
									$x = $x - ($pdfWidth * $this->mpdf->fonts[$this->fontkey]['desc']['unitsPerEm'] / 1000);
								}
							}
							$html .= " Exit X: " . $x . " Y: " . $y . "; ";
						}


						$html .= '</span></div>';
					}
				}
				////////////////////////////////////////////////////////////////////////////////
				// LookupType 4: MarkToBase attachment 	Attach a combining mark to a base glyph
				////////////////////////////////////////////////////////////////////////////////
				else if ($Lookup[$luli]['Type'] == 4) {
					$html .= '<div class="lookuptype">LookupType 4: MarkToBase attachment </div>';
					$MarkCoverage = $subtable_offset + $this->read_ushort();
					$BaseCoverage = $subtable_offset + $this->read_ushort();

					$this->seek($MarkCoverage);
					$MarkGlyphs = $this->_getCoverage();

					$this->seek($BaseCoverage);
					$BaseGlyphs = $this->_getCoverage();

					$firstMark = '';
					$html .= '<div class="glyphs">Marks: ';
					for ($i = 0; $i < count($MarkGlyphs); $i++) {
						if ($level == 2 && strpos($lcoverage, $MarkGlyphs[$i]) === false) {
							continue;
						} else {
							if (!$firstMark) {
								$firstMark = $MarkGlyphs[$i];
							}
						}
						$html .= ' ' . $this->formatEntity($MarkGlyphs[$i]) . ' ';
					}
					$html .= '</div>';
					if (!$firstMark) {
						return;
					}

					$html .= '<div class="glyphs">Bases: ';
					for ($j = 0; $j < count($BaseGlyphs); $j++) {
						$html .= ' ' . $this->formatEntity($BaseGlyphs[$j]) . ' ';
					}
					$html .= '</div>';

					// Example
					$html .= '<div class="glyphs" style="font-feature-settings:\'' . $tag . '\' 1;">Example(s): ';
					for ($j = 0; $j < min(count($BaseGlyphs), 20); $j++) {
						$html .= ' ' . $this->formatEntity($BaseGlyphs[$j]) . $this->formatEntity($firstMark, true) . ' &nbsp; ';
					}
					$html .= '</div>';
				}
				////////////////////////////////////////////////////////////////////////////////
				// LookupType 5: MarkToLigature attachment 	Attach a combining mark to a ligature
				////////////////////////////////////////////////////////////////////////////////
				else if ($Lookup[$luli]['Type'] == 5) {
					$html .= '<div class="lookuptype">LookupType 5: MarkToLigature attachment </div>';
					$MarkCoverage = $subtable_offset + $this->read_ushort();
					//$MarkCoverage is already set in $lcoverage 00065|00073 etc
					$LigatureCoverage = $subtable_offset + $this->read_ushort();
					$ClassCount = $this->read_ushort(); // Number of classes defined for marks = Number of mark glyphs in the MarkCoverage table
					$MarkArray = $subtable_offset + $this->read_ushort(); // Offset to MarkArray table
					$LigatureArray = $subtable_offset + $this->read_ushort(); // Offset to LigatureArray table

					$this->seek($MarkCoverage);
					$MarkGlyphs = $this->_getCoverage();
					$this->seek($LigatureCoverage);
					$LigatureGlyphs = $this->_getCoverage();

					$firstMark = '';
					$html .= '<div class="glyphs">Marks: <span class="unchanged">';
					$MarkRecord = array();
					for ($i = 0; $i < count($MarkGlyphs); $i++) {
						if ($level == 2 && strpos($lcoverage, $MarkGlyphs[$i]) === false) {
							continue;
						} else {
							if (!$firstMark) {
								$firstMark = $MarkGlyphs[$i];
							}
						}
						// Get the relevant MarkRecord
						$MarkRecord[$i] = $this->_getMarkRecord($MarkArray, $i);
						//Mark Class is = $MarkRecord[$i]['Class']
						$html .= ' ' . $this->formatEntity($MarkGlyphs[$i]) . ' ';
					}
					$html .= '</span></div>';
					if (!$firstMark) {
						return;
					}

					$this->seek($LigatureArray);
					$LigatureCount = $this->read_ushort();
					$LigatureAttach = array();
					$html .= '<div class="glyphs">Ligatures: <span class="unchanged">';
					for ($j = 0; $j < count($LigatureGlyphs); $j++) {
						// Get the relevant LigatureRecord
						$LigatureAttach[$j] = $LigatureArray + $this->read_ushort();
						$html .= ' ' . $this->formatEntity($LigatureGlyphs[$j]) . ' ';
					}
					$html .= '</span></div>';

					/*
					  for ($i=0;$i<count($MarkGlyphs);$i++) {
					  $html .= '<div class="glyphs">';
					  $html .= '<span class="unchanged">'.$this->formatEntity($MarkGlyphs[$i]).'</span>';

					  for ($j=0;$j<count($LigatureGlyphs);$j++) {
					  $this->seek($LigatureAttach[$j]);
					  $ComponentCount = $this->read_ushort();
					  $html .= '<span class="unchanged">'.$this->formatEntity($LigatureGlyphs[$j]).'</span>';
					  $offsets = array();
					  for ($comp=0;$comp<$ComponentCount;$comp++) {
					  // ComponentRecords
					  for ($class=0;$class<$ClassCount;$class++) {
					  $offset = $this->read_ushort();
					  if ($offset!= 0 && $class == $MarkRecord[$i]['Class']) {

					  $html .= ' ['.$comp.'] ';

					  }
					  }
					  }
					  }
					  $html .= '</span></div>';
					  }
					 */
				}
				////////////////////////////////////////////////////////////////////////////////
				// LookupType 6: MarkToMark attachment 	Attach a combining mark to another mark
				////////////////////////////////////////////////////////////////////////////////
				else if ($Lookup[$luli]['Type'] == 6) {
					$html .= '<div class="lookuptype">LookupType 6: MarkToMark attachment </div>';
					$Mark1Coverage = $subtable_offset + $this->read_ushort(); // Combining Mark
					//$Mark1Coverage is already set in $LuCoverage 0065|0073 etc
					$Mark2Coverage = $subtable_offset + $this->read_ushort(); // Base Mark
					$ClassCount = $this->read_ushort(); // Number of classes defined for marks = No. of Combining mark1 glyphs in the MarkCoverage table
					$this->seek($Mark1Coverage);
					$Mark1Glyphs = $this->_getCoverage();
					$this->seek($Mark2Coverage);
					$Mark2Glyphs = $this->_getCoverage();


					$firstMark = '';
					$html .= '<div class="glyphs">Marks: <span class="unchanged">';
					for ($i = 0; $i < count($Mark1Glyphs); $i++) {
						if ($level == 2 && strpos($lcoverage, $Mark1Glyphs[$i]) === false) {
							continue;
						} else {
							if (!$firstMark) {
								$firstMark = $Mark1Glyphs[$i];
							}
						}
						$html .= ' ' . $this->formatEntity($Mark1Glyphs[$i]) . ' ';
					}
					$html .= '</span></div>';

					if ($firstMark) {

						$html .= '<div class="glyphs">Bases: <span class="unchanged">';
						for ($j = 0; $j < count($Mark2Glyphs); $j++) {
							$html .= ' ' . $this->formatEntity($Mark2Glyphs[$j]) . ' ';
						}
						$html .= '</span></div>';

						// Example
						$html .= '<div class="glyphs" style="font-feature-settings:\'' . $tag . '\' 1;">Example(s): <span class="changed">';
						for ($j = 0; $j < min(count($Mark2Glyphs), 20); $j++) {
							$html .= ' ' . $this->formatEntity($Mark2Glyphs[$j]) . $this->formatEntity($firstMark, true) . ' &nbsp; ';
						}
						$html .= '</span></div>';
					}
				}
				////////////////////////////////////////////////////////////////////////////////
				// LookupType 7: Context positioning 	Position one or more glyphs in context
				////////////////////////////////////////////////////////////////////////////////
				else if ($Lookup[$luli]['Type'] == 7) {
					$html .= '<div class="lookuptype">LookupType 7: Context positioning [Format ' . $PosFormat . ']</div>';
					//===========
					// Format 1:
					//===========
					if ($PosFormat == 1) {
						throw new MpdfException("GPOS Lookup Type " . $Type . " Format " . $PosFormat . " not YET TESTED.");
					}
					//===========
					// Format 2:
					//===========
					else if ($PosFormat == 2) {
						throw new MpdfException("GPOS Lookup Type " . $Type . " Format " . $PosFormat . " not YET TESTED.");
					}
					//===========
					// Format 3:
					//===========
					else if ($PosFormat == 3) {
						throw new MpdfException("GPOS Lookup Type " . $Type . " Format " . $PosFormat . " not YET TESTED.");
					} else {
						throw new MpdfException("GPOS Lookup Type " . $Type . ", Format " . $PosFormat . " not supported.");
					}
				}
				////////////////////////////////////////////////////////////////////////////////
				// LookupType 8: Chained Context positioning 	Position one or more glyphs in chained context
				////////////////////////////////////////////////////////////////////////////////
				else if ($Lookup[$luli]['Type'] == 8) {
					$html .= '<div class="lookuptype">LookupType 8: Chained Context positioning [Format ' . $PosFormat . ']</div>';
					//===========
					// Format 1:
					//===========
					if ($PosFormat == 1) {
						throw new MpdfException("GPOS Lookup Type " . $Type . " Format " . $PosFormat . " not TESTED YET.");
					}
					//===========
					// Format 2:
					//===========
					else if ($PosFormat == 2) {
						$html .= '<div>GPOS Lookup Type 8: Format 2 not yet supported in OTL dump</div>';
						continue;
						/* NB When developing - cf. GSUB 6.2 */
						throw new MpdfException("GPOS Lookup Type " . $Type . " Format " . $PosFormat . " not TESTED YET.");
					}
					//===========
					// Format 3:
					//===========
					else if ($PosFormat == 3) {
						$BacktrackGlyphCount = $this->read_ushort();
						$CoverageBacktrackOffset = array();
						for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
							$CoverageBacktrackOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
						}
						$InputGlyphCount = $this->read_ushort();
						$CoverageInputOffset = array();
						for ($b = 0; $b < $InputGlyphCount; $b++) {
							$CoverageInputOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
						}
						$LookaheadGlyphCount = $this->read_ushort();
						$CoverageLookaheadOffset = array();
						for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
							$CoverageLookaheadOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
						}
						$PosCount = $this->read_ushort();

						$PosLookupRecord = array();
						for ($p = 0; $p < $PosCount; $p++) {
							// PosLookupRecord
							$PosLookupRecord[$p]['SequenceIndex'] = $this->read_ushort();
							$PosLookupRecord[$p]['LookupListIndex'] = $this->read_ushort();
						}

						$backtrackGlyphs = array();
						for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
							$this->seek($CoverageBacktrackOffset[$b]);
							$backtrackGlyphs[$b] = implode('|', $this->_getCoverage());
						}
						$inputGlyphs = array();
						for ($b = 0; $b < $InputGlyphCount; $b++) {
							$this->seek($CoverageInputOffset[$b]);
							$inputGlyphs[$b] = implode('|', $this->_getCoverage());
						}
						$lookaheadGlyphs = array();
						for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
							$this->seek($CoverageLookaheadOffset[$b]);
							$lookaheadGlyphs[$b] = implode('|', $this->_getCoverage());
						}

						$exampleB = array();
						$exampleI = array();
						$exampleL = array();
						$html .= '<div class="context">CONTEXT: ';
						for ($ff = count($backtrackGlyphs) - 1; $ff >= 0; $ff--) {
							$html .= '<div>Backtrack #' . $ff . ': <span class="unicode">' . $this->formatUniStr($backtrackGlyphs[$ff]) . '</span></div>';
							$exampleB[] = $this->formatEntityFirst($backtrackGlyphs[$ff]);
						}
						for ($ff = 0; $ff < count($inputGlyphs); $ff++) {
							$html .= '<div>Input #' . $ff . ': <span class="unchanged">&nbsp;' . $this->formatEntityStr($inputGlyphs[$ff]) . '&nbsp;</span></div>';
							$exampleI[] = $this->formatEntityFirst($inputGlyphs[$ff]);
						}
						for ($ff = 0; $ff < count($lookaheadGlyphs); $ff++) {
							$html .= '<div>Lookahead #' . $ff . ': <span class="unicode">' . $this->formatUniStr($lookaheadGlyphs[$ff]) . '</span></div>';
							$exampleL[] = $this->formatEntityFirst($lookaheadGlyphs[$ff]);
						}
						$html .= '</div>';


						for ($p = 0; $p < $PosCount; $p++) {
							$lup = $PosLookupRecord[$p]['LookupListIndex'];
							$seqIndex = $PosLookupRecord[$p]['SequenceIndex'];

							// GENERATE exampleB[n] exampleI[<seqIndex] .... exampleI[>seqIndex] exampleL[n]
							$exB = '';
							$exL = '';
							if (count($exampleB)) {
								$exB .= '<span class="backtrack">' . implode('&#x200d;', $exampleB) . '</span>';
							}

							if ($seqIndex > 0) {
								$exB .= '<span class="inputother">';
								for ($ip = 0; $ip < $seqIndex; $ip++) {
									$exB .= $exampleI[$ip] . '&#x200d;';
								}
								$exB .= '</span>';
							}

							if (count($inputGlyphs) > ($seqIndex + 1)) {
								$exL .= '<span class="inputother">';
								for ($ip = $seqIndex + 1; $ip < count($inputGlyphs); $ip++) {
									$exL .= '&#x200d;' . $exampleI[$ip];
								}
								$exL .= '</span>';
							}

							if (count($exampleL)) {
								$exL .= '<span class="lookahead">' . implode('&#x200d;', $exampleL) . '</span>';
							}

							$html .= '<div class="sequenceIndex">Substitution Position: ' . $seqIndex . '</div>';

							$lul2 = array($lup => $tag);

							// Only apply if the (first) 'Replace' glyph from the
							// Lookup list is in the [inputGlyphs] at ['SequenceIndex']
							// Pass $inputGlyphs[$seqIndex] e.g. 00636|00645|00656
							// to level 2 and only apply if first Replace glyph is in this list
							$html .= $this->_getGPOSarray($Lookup, $lul2, $scripttag, 2, $inputGlyphs[$seqIndex], $exB, $exL);
						}
					}
				}
			}
			$html .= '</div>';
		}
		if ($level == 1) {
			$this->mpdf->WriteHTML($html);
		} else {
			return $html;
		}
//print_r($Lookup); exit;
	}

	//=====================================================================================
	//=====================================================================================
	// GPOS FUNCTIONS
	//=====================================================================================

	function count_bits($n)
	{
		for ($c = 0; $n; $c++) {
			$n &= $n - 1; // clear the least significant bit set
		}
		return $c;
	}

	function _getValueRecord($ValueFormat)
	{ // Common ValueRecord for GPOS
		// Only returns 3 possible: $vra['XPlacement'] $vra['YPlacement'] $vra['XAdvance']
		$vra = array();
		// Horizontal adjustment for placement-in design units
		if (($ValueFormat & 0x0001) == 0x0001) {
			$vra['XPlacement'] = $this->read_short();
		}
		// Vertical adjustment for placement-in design units
		if (($ValueFormat & 0x0002) == 0x0002) {
			$vra['YPlacement'] = $this->read_short();
		}
		// Horizontal adjustment for advance-in design units (only used for horizontal writing)
		if (($ValueFormat & 0x0004) == 0x0004) {
			$vra['XAdvance'] = $this->read_short();
		}
		// Vertical adjustment for advance-in design units (only used for vertical writing)
		if (($ValueFormat & 0x0008) == 0x0008) {
			$this->read_short();
		}
		// Offset to Device table for horizontal placement-measured from beginning of PosTable (may be NULL)
		if (($ValueFormat & 0x0010) == 0x0010) {
			$this->read_ushort();
		}
		// Offset to Device table for vertical placement-measured from beginning of PosTable (may be NULL)
		if (($ValueFormat & 0x0020) == 0x0020) {
			$this->read_ushort();
		}
		// Offset to Device table for horizontal advance-measured from beginning of PosTable (may be NULL)
		if (($ValueFormat & 0x0040) == 0x0040) {
			$this->read_ushort();
		}
		// Offset to Device table for vertical advance-measured from beginning of PosTable (may be NULL)
		if (($ValueFormat & 0x0080) == 0x0080) {
			$this->read_ushort();
		}
		return $vra;
	}

	function _getAnchorTable($offset = 0)
	{
		if ($offset) {
			$this->seek($offset);
		}
		$AnchorFormat = $this->read_ushort();
		$XCoordinate = $this->read_short();
		$YCoordinate = $this->read_short();
		// Format 2 specifies additional link to contour point; Format 3 additional Device table
		return array($XCoordinate, $YCoordinate);
	}

	function _getMarkRecord($offset, $MarkPos)
	{
		$this->seek($offset);
		$MarkCount = $this->read_ushort();
		$this->skip($MarkPos * 4);
		$Class = $this->read_ushort();
		$MarkAnchor = $offset + $this->read_ushort();  // = Offset to anchor table
		list($x, $y) = $this->_getAnchorTable($MarkAnchor);
		$MarkRecord = array('Class' => $Class, 'AnchorX' => $x, 'AnchorY' => $y);
		return $MarkRecord;
	}

	//////////////////////////////////////////////////////////////////////////////////
	// Recursively get composite glyph data
	function getGlyphData($originalGlyphIdx, &$maxdepth, &$depth, &$points, &$contours)
	{
		$depth++;
		$maxdepth = max($maxdepth, $depth);
		if (count($this->glyphdata[$originalGlyphIdx]['compGlyphs'])) {
			foreach ($this->glyphdata[$originalGlyphIdx]['compGlyphs'] AS $glyphIdx) {
				$this->getGlyphData($glyphIdx, $maxdepth, $depth, $points, $contours);
			}
		} else if (($this->glyphdata[$originalGlyphIdx]['nContours'] > 0) && $depth > 0) { // simple
			$contours += $this->glyphdata[$originalGlyphIdx]['nContours'];
			$points += $this->glyphdata[$originalGlyphIdx]['nPoints'];
		}
		$depth--;
	}

	//////////////////////////////////////////////////////////////////////////////////
	// Recursively get composite glyphs
	function getGlyphs($originalGlyphIdx, &$start, &$glyphSet, &$subsetglyphs)
	{
		$glyphPos = $this->glyphPos[$originalGlyphIdx];
		$glyphLen = $this->glyphPos[$originalGlyphIdx + 1] - $glyphPos;
		if (!$glyphLen) {
			return;
		}
		$this->seek($start + $glyphPos);
		$numberOfContours = $this->read_short();
		if ($numberOfContours < 0) {
			$this->skip(8);
			$flags = GF_MORE;
			while ($flags & GF_MORE) {
				$flags = $this->read_ushort();
				$glyphIdx = $this->read_ushort();
				if (!isset($glyphSet[$glyphIdx])) {
					$glyphSet[$glyphIdx] = count($subsetglyphs); // old glyphID to new glyphID
					$subsetglyphs[$glyphIdx] = true;
				}
				$savepos = ftell($this->fh);
				$this->getGlyphs($glyphIdx, $start, $glyphSet, $subsetglyphs);
				$this->seek($savepos);
				if ($flags & GF_WORDS)
					$this->skip(4);
				else
					$this->skip(2);
				if ($flags & GF_SCALE)
					$this->skip(2);
				else if ($flags & GF_XYSCALE)
					$this->skip(4);
				else if ($flags & GF_TWOBYTWO)
					$this->skip(8);
			}
		}
	}

	//////////////////////////////////////////////////////////////////////////////////

	function getHMTX($numberOfHMetrics, $numGlyphs, &$glyphToChar, $scale)
	{
		$start = $this->seek_table("hmtx");
		$aw = 0;
		$this->charWidths = str_pad('', 256 * 256 * 2, "\x00");
		if ($this->maxUniChar > 65536) {
			$this->charWidths .= str_pad('', 256 * 256 * 2, "\x00");
		} // Plane 1 SMP
		if ($this->maxUniChar > 131072) {
			$this->charWidths .= str_pad('', 256 * 256 * 2, "\x00");
		} // Plane 2 SMP
		$nCharWidths = 0;
		if (($numberOfHMetrics * 4) < $this->maxStrLenRead) {
			$data = $this->get_chunk($start, ($numberOfHMetrics * 4));
			$arr = unpack("n*", $data);
		} else {
			$this->seek($start);
		}
		for ($glyph = 0; $glyph < $numberOfHMetrics; $glyph++) {
			if (($numberOfHMetrics * 4) < $this->maxStrLenRead) {
				$aw = $arr[($glyph * 2) + 1];
			} else {
				$aw = $this->read_ushort();
				$lsb = $this->read_ushort();
			}
			if (isset($glyphToChar[$glyph]) || $glyph == 0) {

				if ($aw >= (1 << 15)) {
					$aw = 0;
				} // 1.03 Some (arabic) fonts have -ve values for width
				// although should be unsigned value - comes out as e.g. 65108 (intended -50)
				if ($glyph == 0) {
					$this->defaultWidth = $scale * $aw;
					continue;
				}
				foreach ($glyphToChar[$glyph] AS $char) {
					//$this->charWidths[$char] = intval(round($scale*$aw));
					if ($char != 0 && $char != 65535) {
						$w = intval(round($scale * $aw));
						if ($w == 0) {
							$w = 65535;
						}
						if ($char < 196608) {
							$this->charWidths[$char * 2] = chr($w >> 8);
							$this->charWidths[$char * 2 + 1] = chr($w & 0xFF);
							$nCharWidths++;
						}
					}
				}
			}
		}
		$data = $this->get_chunk(($start + $numberOfHMetrics * 4), ($numGlyphs * 2));
		$arr = unpack("n*", $data);
		$diff = $numGlyphs - $numberOfHMetrics;
		$w = intval(round($scale * $aw));
		if ($w == 0) {
			$w = 65535;
		}
		for ($pos = 0; $pos < $diff; $pos++) {
			$glyph = $pos + $numberOfHMetrics;
			if (isset($glyphToChar[$glyph])) {
				foreach ($glyphToChar[$glyph] AS $char) {
					if ($char != 0 && $char != 65535) {
						if ($char < 196608) {
							$this->charWidths[$char * 2] = chr($w >> 8);
							$this->charWidths[$char * 2 + 1] = chr($w & 0xFF);
							$nCharWidths++;
						}
					}
				}
			}
		}
		// NB 65535 is a set width of 0
		// First bytes define number of chars in font
		$this->charWidths[0] = chr($nCharWidths >> 8);
		$this->charWidths[1] = chr($nCharWidths & 0xFF);
	}

	function getHMetric($numberOfHMetrics, $gid)
	{
		$start = $this->seek_table("hmtx");
		if ($gid < $numberOfHMetrics) {
			$this->seek($start + ($gid * 4));
			$hm = fread($this->fh, 4);
		} else {
			$this->seek($start + (($numberOfHMetrics - 1) * 4));
			$hm = fread($this->fh, 2);
			$this->seek($start + ($numberOfHMetrics * 2) + ($gid * 2));
			$hm .= fread($this->fh, 2);
		}
		return $hm;
	}

	function getLOCA($indexToLocFormat, $numGlyphs)
	{
		$start = $this->seek_table('loca');
		$this->glyphPos = array();
		if ($indexToLocFormat == 0) {
			$data = $this->get_chunk($start, ($numGlyphs * 2) + 2);
			$arr = unpack("n*", $data);
			for ($n = 0; $n <= $numGlyphs; $n++) {
				$this->glyphPos[] = ($arr[$n + 1] * 2);
			}
		} else if ($indexToLocFormat == 1) {
			$data = $this->get_chunk($start, ($numGlyphs * 4) + 4);
			$arr = unpack("N*", $data);
			for ($n = 0; $n <= $numGlyphs; $n++) {
				$this->glyphPos[] = ($arr[$n + 1]);
			}
		} else {
			throw new MpdfException('Unknown location table format ' . $indexToLocFormat);
		}
	}

	// CMAP Format 4
	function getCMAP4($unicode_cmap_offset, &$glyphToChar, &$charToGlyph)
	{
		$this->maxUniChar = 0;
		$this->seek($unicode_cmap_offset + 2);
		$length = $this->read_ushort();
		$limit = $unicode_cmap_offset + $length;
		$this->skip(2);

		$segCount = $this->read_ushort() / 2;
		$this->skip(6);
		$endCount = array();
		for ($i = 0; $i < $segCount; $i++) {
			$endCount[] = $this->read_ushort();
		}
		$this->skip(2);
		$startCount = array();
		for ($i = 0; $i < $segCount; $i++) {
			$startCount[] = $this->read_ushort();
		}
		$idDelta = array();
		for ($i = 0; $i < $segCount; $i++) {
			$idDelta[] = $this->read_short();
		}  // ???? was unsigned short
		$idRangeOffset_start = $this->_pos;
		$idRangeOffset = array();
		for ($i = 0; $i < $segCount; $i++) {
			$idRangeOffset[] = $this->read_ushort();
		}

		for ($n = 0; $n < $segCount; $n++) {
			$endpoint = ($endCount[$n] + 1);
			for ($unichar = $startCount[$n]; $unichar < $endpoint; $unichar++) {
				if ($idRangeOffset[$n] == 0)
					$glyph = ($unichar + $idDelta[$n]) & 0xFFFF;
				else {
					$offset = ($unichar - $startCount[$n]) * 2 + $idRangeOffset[$n];
					$offset = $idRangeOffset_start + 2 * $n + $offset;
					if ($offset >= $limit)
						$glyph = 0;
					else {
						$glyph = $this->get_ushort($offset);
						if ($glyph != 0)
							$glyph = ($glyph + $idDelta[$n]) & 0xFFFF;
					}
				}
				$charToGlyph[$unichar] = $glyph;
				if ($unichar < 196608) {
					$this->maxUniChar = max($unichar, $this->maxUniChar);
				}
				$glyphToChar[$glyph][] = $unichar;
			}
		}
	}

	function formatUni($char)
	{
		$x = preg_replace('/^[0]*/', '', $char);
		$x = str_pad($x, 4, '0', STR_PAD_LEFT);
		$d = hexdec($x);
		if (($d > 57343 && $d < 63744) || ($d > 122879 && $d < 126977)) {
			$id = 'M';
		} // E000 - F8FF, 1E000-1F000
		else {
			$id = 'U';
		}
		return $id . '+' . $x;
	}

	function formatEntity($char, $allowjoining = false)
	{
		$char = preg_replace('/^[0]/', '', $char);
		$x = '&#x' . $char . ';';
		if (strpos($this->GlyphClassMarks, $char) !== false) {
			if (!$allowjoining) {
				$x = '&#x25cc;' . $x;
			}
		}
		return $x;
	}

	function formatUniArr($arr)
	{
		$s = array();
		foreach ($arr AS $c) {
			$x = preg_replace('/^[0]*/', '', $c);
			$d = hexdec($x);
			if (($d > 57343 && $d < 63744) || ($d > 122879 && $d < 126977)) {
				$id = 'M';
			} // E000 - F8FF, 1E000-1F000
			else {
				$id = 'U';
			}
			$s[] = $id . '+' . str_pad($x, 4, '0', STR_PAD_LEFT);
		}
		return implode(', ', $s);
	}

	function formatEntityArr($arr)
	{
		$s = array();
		foreach ($arr AS $c) {
			$c = preg_replace('/^[0]/', '', $c);
			$x = '&#x' . $c . ';';
			if (strpos($this->GlyphClassMarks, $c) !== false) {
				$x = '&#x25cc;' . $x;
			}
			$s[] = $x;
		}
		return implode(' ', $s); // ZWNJ? &#x200d;
	}

	function formatClassArr($arr)
	{
		$s = array();
		foreach ($arr AS $c) {
			$x = preg_replace('/^[0]*/', '', $c);
			$d = hexdec($x);
			if (($d > 57343 && $d < 63744) || ($d > 122879 && $d < 126977)) {
				$id = 'M';
			} // E000 - F8FF, 1E000-1F000
			else {
				$id = 'U';
			}
			$s[] = $id . '+' . str_pad($x, 4, '0', STR_PAD_LEFT);
		}
		return implode(', ', $s);
	}

	function formatUniStr($str)
	{
		$s = array();
		$arr = explode('|', $str);
		foreach ($arr AS $c) {
			$x = preg_replace('/^[0]*/', '', $c);
			$d = hexdec($x);
			if (($d > 57343 && $d < 63744) || ($d > 122879 && $d < 126977)) {
				$id = 'M';
			} // E000 - F8FF, 1E000-1F000
			else {
				$id = 'U';
			}
			$s[] = $id . '+' . str_pad($x, 4, '0', STR_PAD_LEFT);
		}
		return implode(', ', $s);
	}

	function formatEntityStr($str)
	{
		$s = array();
		$arr = explode('|', $str);
		foreach ($arr AS $c) {
			$c = preg_replace('/^[0]/', '', $c);
			$x = '&#x' . $c . ';';
			if (strpos($this->GlyphClassMarks, $c) !== false) {
				$x = '&#x25cc;' . $x;
			}
			$s[] = $x;
		}
		return implode(' ', $s); // ZWNJ? &#x200d;
	}

	function formatEntityFirst($str)
	{
		$arr = explode('|', $str);
		$char = preg_replace('/^[0]/', '', $arr[0]);
		$x = '&#x' . $char . ';';
		if (strpos($this->GlyphClassMarks, $char) !== false) {
			$x = '&#x25cc;' . $x;
		}
		return $x;
	}

}
