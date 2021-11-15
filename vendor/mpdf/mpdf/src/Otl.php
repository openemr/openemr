<?php

namespace Mpdf;

use Mpdf\Strict;

use Mpdf\Css\TextVars;
use Mpdf\Fonts\FontCache;

use Mpdf\Shaper\Indic;
use Mpdf\Shaper\Myanmar;
use Mpdf\Shaper\Sea;

use Mpdf\Utils\UtfString;

class Otl
{

	use Strict;

	const _OTL_OLD_SPEC_COMPAT_1 = true;
	const _DICT_NODE_TYPE_SPLIT = 0x01;
	const _DICT_NODE_TYPE_LINEAR = 0x02;
	const _DICT_INTERMEDIATE_MATCH = 0x03;
	const _DICT_FINAL_MATCH = 0x04;

	private $mpdf;

	private $fontCache;

	var $arabLeftJoining;

	var $arabRightJoining;

	var $arabTransparentJoin;

	var $arabTransparent;

	var $GSUBdata;

	var $GPOSdata;

	var $GSUBfont;

	var $fontkey;

	var $ttfOTLdata;

	var $glyphIDtoUni;

	var $_pos;

	var $GSUB_offset;

	var $GPOS_offset;

	var $MarkAttachmentType;

	var $MarkGlyphSets;

	var $GlyphClassMarks;

	var $GlyphClassLigatures;

	var $GlyphClassBases;

	var $GlyphClassComponents;

	var $Ignores;

	var $LuCoverage;

	var $OTLdata;

	var $assocLigs;

	var $assocMarks;

	var $shaper;

	var $restrictToSyllable;

	var $lbdicts; // Line-breaking dictionaries

	var $LuDataCache;

	var $arabGlyphs;

	var $current_fh;

	var $Entry;

	var $Exit;

	var $GDEFdata;

	var $GPOSLookups;

	var $GSLuCoverage;

	var $GSUB_length;

	var $GSUBLookups;

	var $schOTLdata;

	var $lastBidiStrongType;

	var $debugOTL = false;

	public function __construct(Mpdf $mpdf, FontCache $fontCache)
	{
		$this->mpdf = $mpdf;
		$this->fontCache = $fontCache;

		$this->current_fh = '';

		$this->lbdicts = [];
		$this->LuDataCache = [];
	}

	function applyOTL($str, $useOTL)
	{
		if (!$this->arabLeftJoining) {
			$this->arabic_initialise();
		}

		$this->OTLdata = [];
		if (trim($str) == '') {
			return $str;
		}
		if (!$useOTL) {
			return $str;
		}

		// 1. Load GDEF data
		//==============================
		$this->fontkey = $this->mpdf->CurrentFont['fontkey'];
		$this->glyphIDtoUni = $this->mpdf->CurrentFont['glyphIDtoUni'];
		$fontCacheFilename = $this->fontkey . '.GDEFdata.json';
		if (!isset($this->GDEFdata[$this->fontkey]) && $this->fontCache->jsonHas($fontCacheFilename)) {
			$font = $this->fontCache->jsonLoad($fontCacheFilename);

			$this->GSUB_offset = $this->GDEFdata[$this->fontkey]['GSUB_offset'] = $font['GSUB_offset'];
			$this->GPOS_offset = $this->GDEFdata[$this->fontkey]['GPOS_offset'] = $font['GPOS_offset'];
			$this->GSUB_length = $this->GDEFdata[$this->fontkey]['GSUB_length'] = $font['GSUB_length'];
			$this->MarkAttachmentType = $this->GDEFdata[$this->fontkey]['MarkAttachmentType'] = $font['MarkAttachmentType'];
			$this->MarkGlyphSets = $this->GDEFdata[$this->fontkey]['MarkGlyphSets'] = $font['MarkGlyphSets'];
			$this->GlyphClassMarks = $this->GDEFdata[$this->fontkey]['GlyphClassMarks'] = $font['GlyphClassMarks'];
			$this->GlyphClassLigatures = $this->GDEFdata[$this->fontkey]['GlyphClassLigatures'] = $font['GlyphClassLigatures'];
			$this->GlyphClassComponents = $this->GDEFdata[$this->fontkey]['GlyphClassComponents'] = $font['GlyphClassComponents'];
			$this->GlyphClassBases = $this->GDEFdata[$this->fontkey]['GlyphClassBases'] = $font['GlyphClassBases'];
		} else {
			$this->GSUB_offset = $this->GDEFdata[$this->fontkey]['GSUB_offset'];
			$this->GPOS_offset = $this->GDEFdata[$this->fontkey]['GPOS_offset'];
			$this->GSUB_length = $this->GDEFdata[$this->fontkey]['GSUB_length'];
			$this->MarkAttachmentType = $this->GDEFdata[$this->fontkey]['MarkAttachmentType'];
			$this->MarkGlyphSets = $this->GDEFdata[$this->fontkey]['MarkGlyphSets'];
			$this->GlyphClassMarks = $this->GDEFdata[$this->fontkey]['GlyphClassMarks'];
			$this->GlyphClassLigatures = $this->GDEFdata[$this->fontkey]['GlyphClassLigatures'];
			$this->GlyphClassComponents = $this->GDEFdata[$this->fontkey]['GlyphClassComponents'];
			$this->GlyphClassBases = $this->GDEFdata[$this->fontkey]['GlyphClassBases'];
		}

		// 2. Prepare string as HEX string and Analyse character properties
		//=================================================================
		$earr = $this->mpdf->UTF8StringToArray($str, false);

		$scriptblock = 0;
		$scriptblocks = [];
		$scriptblocks[0] = 0;
		$vstr = '';
		$OTLdata = [];
		$subchunk = 0;
		$charctr = 0;
		foreach ($earr as $char) {
			$ucd_record = Ucdn::get_ucd_record($char);
			$sbl = $ucd_record[6];

			// Special case - Arabic End of Ayah
			if ($char == 1757) {
				$sbl = Ucdn::SCRIPT_ARABIC;
			}

			if ($sbl && $sbl != 40 && $sbl != 102) {
				if ($scriptblock == 0) {
					$scriptblock = $sbl;
					$scriptblocks[$subchunk] = $scriptblock;
				} elseif ($scriptblock > 0 && $scriptblock != $sbl) {
					// *************************************************
					// NEW (non-common) Script encountered in this chunk. Start a new subchunk
					$subchunk++;
					$scriptblock = $sbl;
					$charctr = 0;
					$scriptblocks[$subchunk] = $scriptblock;
				}
			}

			$OTLdata[$subchunk][$charctr]['general_category'] = $ucd_record[0];
			$OTLdata[$subchunk][$charctr]['bidi_type'] = $ucd_record[2];

			//$OTLdata[$subchunk][$charctr]['combining_class'] = $ucd_record[1];
			//$OTLdata[$subchunk][$charctr]['bidi_type'] = $ucd_record[2];
			//$OTLdata[$subchunk][$charctr]['mirrored'] = $ucd_record[3];
			//$OTLdata[$subchunk][$charctr]['east_asian_width'] = $ucd_record[4];
			//$OTLdata[$subchunk][$charctr]['normalization_check'] = $ucd_record[5];
			//$OTLdata[$subchunk][$charctr]['script'] = $ucd_record[6];

			$charasstr = $this->unicode_hex($char);

			if (strpos($this->GlyphClassMarks, $charasstr) !== false) {
				$OTLdata[$subchunk][$charctr]['group'] = 'M';
			} elseif ($char == 32 || $char == 12288) {
				$OTLdata[$subchunk][$charctr]['group'] = 'S';
			} // 12288 = 0x3000 = CJK space
			else {
				$OTLdata[$subchunk][$charctr]['group'] = 'C';
			}

			$OTLdata[$subchunk][$charctr]['uni'] = $char;
			$OTLdata[$subchunk][$charctr]['hex'] = $charasstr;
			$charctr++;
		}

		/* PROCESS EACH SUBCHUNK WITH DIFFERENT SCRIPTS */
		for ($sch = 0; $sch <= $subchunk; $sch++) {
			$this->OTLdata = $OTLdata[$sch];
			$scriptblock = $scriptblocks[$sch];

			// 3. Get Appropriate Scripts, and Shaper engine from analysing text and list of available scripts/langsys in font
			//==============================
			// Based on actual script block of text, select shaper (and line-breaking dictionaries)
			if (Ucdn::SCRIPT_DEVANAGARI <= $scriptblock && $scriptblock <= Ucdn::SCRIPT_MALAYALAM) {
				$this->shaper = "I";
			} // INDIC shaper
			elseif ($scriptblock == Ucdn::SCRIPT_ARABIC || $scriptblock == Ucdn::SCRIPT_SYRIAC) {
				$this->shaper = "A";
			} // ARABIC shaper
			elseif ($scriptblock == Ucdn::SCRIPT_NKO || $scriptblock == Ucdn::SCRIPT_MANDAIC) {
				$this->shaper = "A";
			} // ARABIC shaper
			elseif ($scriptblock == Ucdn::SCRIPT_KHMER) {
				$this->shaper = "K";
			} // KHMER shaper
			elseif ($scriptblock == Ucdn::SCRIPT_THAI) {
				$this->shaper = "T";
			} // THAI shaper
			elseif ($scriptblock == Ucdn::SCRIPT_LAO) {
				$this->shaper = "L";
			} // LAO shaper
			elseif ($scriptblock == Ucdn::SCRIPT_SINHALA) {
				$this->shaper = "S";
			} // SINHALA shaper
			elseif ($scriptblock == Ucdn::SCRIPT_MYANMAR) {
				$this->shaper = "M";
			} // MYANMAR shaper
			elseif ($scriptblock == Ucdn::SCRIPT_NEW_TAI_LUE) {
				$this->shaper = "E";
			} // SEA South East Asian shaper
			elseif ($scriptblock == Ucdn::SCRIPT_CHAM) {
				$this->shaper = "E";
			} // SEA South East Asian shaper
			elseif ($scriptblock == Ucdn::SCRIPT_TAI_THAM) {
				$this->shaper = "E";
			} // SEA South East Asian shaper
			else {
				$this->shaper = "";
			}
			// Get scripttag based on actual text script
			$scripttag = Ucdn::$uni_scriptblock[$scriptblock];

			$GSUBscriptTag = '';
			$GSUBlangsys = '';
			$GPOSscriptTag = '';
			$GPOSlangsys = '';
			$is_old_spec = false;

			$ScriptLang = $this->mpdf->CurrentFont['GSUBScriptLang'];
			if (count($ScriptLang)) {
				list($GSUBscriptTag, $is_old_spec) = $this->_getOTLscriptTag($ScriptLang, $scripttag, $scriptblock, $this->shaper, $useOTL, 'GSUB');
				if ($this->mpdf->fontLanguageOverride && strpos($ScriptLang[$GSUBscriptTag], $this->mpdf->fontLanguageOverride) !== false) {
					$GSUBlangsys = str_pad($this->mpdf->fontLanguageOverride, 4);
				} elseif ($GSUBscriptTag && isset($ScriptLang[$GSUBscriptTag]) && $ScriptLang[$GSUBscriptTag] != '') {
					$GSUBlangsys = $this->_getOTLLangTag($this->mpdf->currentLang, $ScriptLang[$GSUBscriptTag]);
				}
			}
			$ScriptLang = $this->mpdf->CurrentFont['GPOSScriptLang'];

			// NB If after GSUB, the same script/lang exist for GPOS, just use these...
			if ($GSUBscriptTag && $GSUBlangsys && isset($ScriptLang[$GSUBscriptTag]) && strpos($ScriptLang[$GSUBscriptTag], $GSUBlangsys) !== false) {
				$GPOSlangsys = $GSUBlangsys;
				$GPOSscriptTag = $GSUBscriptTag;
			} // else repeat for GPOS
			// [Font XBRiyaz has GSUB tables for latn, but not GPOS for latn]
			elseif (count($ScriptLang)) {
				list($GPOSscriptTag, $dummy) = $this->_getOTLscriptTag($ScriptLang, $scripttag, $scriptblock, $this->shaper, $useOTL, 'GPOS');
				if ($GPOSscriptTag && $this->mpdf->fontLanguageOverride && strpos($ScriptLang[$GPOSscriptTag], $this->mpdf->fontLanguageOverride) !== false) {
					$GPOSlangsys = str_pad($this->mpdf->fontLanguageOverride, 4);
				} elseif ($GPOSscriptTag && isset($ScriptLang[$GPOSscriptTag]) && $ScriptLang[$GPOSscriptTag] != '') {
					$GPOSlangsys = $this->_getOTLLangTag($this->mpdf->currentLang, $ScriptLang[$GPOSscriptTag]);
				}
			}

			// This is just for the font_dump_OTL utility to set script and langsys override
			// $mpdf->overrideOTLsettings does not exist, this is never called
			/*if (isset($this->mpdf->overrideOTLsettings) && isset($this->mpdf->overrideOTLsettings[$this->fontkey])) {
				$GSUBscriptTag = $GPOSscriptTag = $this->mpdf->overrideOTLsettings[$this->fontkey]['script'];
				$GSUBlangsys = $GPOSlangsys = $this->mpdf->overrideOTLsettings[$this->fontkey]['lang'];
			}*/

			if (!$GSUBscriptTag && !$GSUBlangsys && !$GPOSscriptTag && !$GPOSlangsys) {
				// Remove ZWJ and ZWNJ
				for ($i = 0; $i < count($this->OTLdata); $i++) {
					if ($this->OTLdata[$i]['uni'] == 8204 || $this->OTLdata[$i]['uni'] == 8205) {
						array_splice($this->OTLdata, $i, 1);
					}
				}
				$this->schOTLdata[$sch] = $this->OTLdata;
				$this->OTLdata = [];
				continue;
			}

			// Don't use MYANMAR shaper unless using v2 scripttag
			if ($this->shaper == 'M' && $GSUBscriptTag != 'mym2') {
				$this->shaper = '';
			}

			$GSUBFeatures = (isset($this->mpdf->CurrentFont['GSUBFeatures'][$GSUBscriptTag][$GSUBlangsys]) ? $this->mpdf->CurrentFont['GSUBFeatures'][$GSUBscriptTag][$GSUBlangsys] : false);
			$GPOSFeatures = (isset($this->mpdf->CurrentFont['GPOSFeatures'][$GPOSscriptTag][$GPOSlangsys]) ? $this->mpdf->CurrentFont['GPOSFeatures'][$GPOSscriptTag][$GPOSlangsys] : false);

			$this->assocLigs = []; // Ligatures[$posarr lpos] => nc
			$this->assocMarks = [];  // assocMarks[$posarr mpos] => array(compID, ligPos)

			if (!isset($this->GDEFdata[$this->fontkey]['GSUBGPOStables'])) {
				$this->ttfOTLdata = $this->GDEFdata[$this->fontkey]['GSUBGPOStables'] = $this->fontCache->load($this->fontkey . '.GSUBGPOStables.dat', 'rb');
				if (!$this->ttfOTLdata) {
					throw new \Mpdf\MpdfException('Can\'t open file ' . $this->fontCache->tempFilename($this->fontkey . '.GSUBGPOStables.dat'));
				}
			} else {
				$this->ttfOTLdata = $this->GDEFdata[$this->fontkey]['GSUBGPOStables'];
			}

			if ($this->debugOTL) {
				$this->_dumpproc('BEGIN', '-', '-', '-', '-', -1, '-', 0);
			}

			////////////////////////////////////////////////////////////////
			/////////  LINE BREAKING FOR KHMER, THAI + LAO /////////////////
			////////////////////////////////////////////////////////////////
			// Insert U+200B at word boundaries using dictionaries
			if ($this->mpdf->useDictionaryLBR && ($this->shaper == "K" || $this->shaper == "T" || $this->shaper == "L")) {
				// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
				$this->seaLineBreaking();
			} // Insert U+200B at word boundaries for Tibetan
			elseif ($this->mpdf->useTibetanLBR && $scriptblock == Ucdn::SCRIPT_TIBETAN) {
				// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
				$this->tibetanLineBreaking();
			}


			////////////////////////////////////////////////////////////////
			//////////       GSUB          /////////////////////////////////
			////////////////////////////////////////////////////////////////
			if (($useOTL & 0xFF) && $GSUBscriptTag && $GSUBlangsys && $GSUBFeatures) {
				// 4. Load GSUB data, Coverage & Lookups
				//=================================================================

				$this->GSUBfont = $this->fontkey . '.GSUB.' . $GSUBscriptTag . '.' . $GSUBlangsys;

				if (!isset($this->GSUBdata[$this->GSUBfont])) {
					$fontCacheFilename = $this->GSUBfont . '.json';
					if ($this->fontCache->jsonHas($fontCacheFilename)) {
						$font = $this->fontCache->jsonLoad($fontCacheFilename);

						$this->GSUBdata[$this->GSUBfont]['rtlSUB'] = $font['rtlSUB'];
						$this->GSUBdata[$this->GSUBfont]['finals'] = $font['finals'];
						if ($this->shaper == 'I') {
							$this->GSUBdata[$this->GSUBfont]['rphf'] = $font['rphf'];
							$this->GSUBdata[$this->GSUBfont]['half'] = $font['half'];
							$this->GSUBdata[$this->GSUBfont]['pref'] = $font['pref'];
							$this->GSUBdata[$this->GSUBfont]['blwf'] = $font['blwf'];
							$this->GSUBdata[$this->GSUBfont]['pstf'] = $font['pstf'];
						}
					} else {
						$this->GSUBdata[$this->GSUBfont] = ['rtlSUB' => [], 'rphf' => [], 'rphf' => [],
							'pref' => [], 'blwf' => [], 'pstf' => [], 'finals' => ''
						];
					}
				}

				$fontCacheFilename = $this->fontkey . '.GSUBdata.json';
				if (!isset($this->GSUBdata[$this->fontkey]) && $this->fontCache->jsonHas($fontCacheFilename)) {
					$this->GSLuCoverage = $this->GSUBdata[$this->fontkey]['GSLuCoverage'] = $this->fontCache->jsonLoad($fontCacheFilename);
				} else {
					$this->GSLuCoverage = $this->GSUBdata[$this->fontkey]['GSLuCoverage'];
				}

				$this->GSUBLookups = $this->mpdf->CurrentFont['GSUBLookups'];


				// 5(A). GSUB - Shaper - ARABIC
				//==============================
				if ($this->shaper == 'A') {
					//-----------------------------------------------------------------------------------
					// a. Apply initial GSUB Lookups (in order specified in lookup list but only selecting from certain tags)
					//-----------------------------------------------------------------------------------
					$tags = 'locl ccmp';
					$omittags = '';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, true);
					}
					$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);

					//-----------------------------------------------------------------------------------
					// b. Apply context-specific forms GSUB Lookups (initial, isolated, medial, final)
					//-----------------------------------------------------------------------------------
					// Arab and Syriac are the only scripts requiring the special joining - which takes the place of
					// isol fina medi init rules in GSUB (+ fin2 fin3 med2 in Syriac syrc)
					$tags = 'isol fina fin2 fin3 medi med2 init';
					$omittags = '';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, true);
					}

					$this->arabGlyphs = $this->GSUBdata[$this->GSUBfont]['rtlSUB'];

					$gcms = explode("| ", $this->GlyphClassMarks);
					$gcm = [];
					foreach ($gcms as $g) {
						$gcm[hexdec($g)] = 1;
					}
					$this->arabTransparentJoin = $this->arabTransparent + $gcm;
					$this->arabic_shaper($usetags, $GSUBscriptTag);

					//-----------------------------------------------------------------------------------
					// c. Set Kashida points (after joining occurred - medi, fina, init) but before other substitutions
					//-----------------------------------------------------------------------------------
					//if ($scriptblock == Ucdn::SCRIPT_ARABIC ) {
					for ($i = 0; $i < count($this->OTLdata); $i++) {
						// Put the kashida marker on the character BEFORE which is inserted the kashida
						// Kashida marker is inverse of priority i.e. Priority 1 => 7, Priority 7 => 1.
						// Priority 1   User-inserted Kashida 0640 = Tatweel
						// The user entered a Kashida in a position
						// Position: Before the user-inserted kashida
						if ($this->OTLdata[$i]['uni'] == 0x0640) {
							$this->OTLdata[$i]['GPOSinfo']['kashida'] = 8; // Put before the next character
						} // Priority 2   Seen (0633)  FEB3, FEB4; Sad (0635)  FEBB, FEBC
						// Initial or medial form
						// Connecting to the next character
						// Position: After the character
						elseif ($this->OTLdata[$i]['uni'] == 0xFEB3 || $this->OTLdata[$i]['uni'] == 0xFEB4 || $this->OTLdata[$i]['uni'] == 0xFEBB || $this->OTLdata[$i]['uni'] == 0xFEBC) {
							$checkpos = $i + 1;
							while (isset($this->OTLdata[$checkpos]) && strpos($this->GlyphClassMarks, $this->OTLdata[$checkpos]['hex']) !== false) {
								$checkpos++;
							}
							if (isset($this->OTLdata[$checkpos])) {
								$this->OTLdata[$checkpos]['GPOSinfo']['kashida'] = 7; // Put after marks on next character
							}
						} // Priority 3   Taa Marbutah (0629) FE94; Haa (062D) FEA2; Dal (062F) FEAA
						// Final form
						// Connecting to previous character
						// Position: Before the character
						elseif ($this->OTLdata[$i]['uni'] == 0xFE94 || $this->OTLdata[$i]['uni'] == 0xFEA2 || $this->OTLdata[$i]['uni'] == 0xFEAA) {
							$this->OTLdata[$i]['GPOSinfo']['kashida'] = 6;
						} // Priority 4   Alef (0627) FE8E; Tah (0637) FEC2; Lam (0644) FEDE; Kaf (0643)  FEDA; Gaf (06AF) FB93
						// Final form
						// Connecting to previous character
						// Position: Before the character
						elseif ($this->OTLdata[$i]['uni'] == 0xFE8E || $this->OTLdata[$i]['uni'] == 0xFEC2 || $this->OTLdata[$i]['uni'] == 0xFEDE || $this->OTLdata[$i]['uni'] == 0xFEDA || $this->OTLdata[$i]['uni'] == 0xFB93) {
							$this->OTLdata[$i]['GPOSinfo']['kashida'] = 5;
						} // Priority 5   RA (0631) FEAE; Ya (064A)  FEF2 FEF4; Alef Maqsurah (0649) FEF0 FBE9
						// Final or Medial form
						// Connected to preceding medial BAA (0628) = FE92
						// Position: Before preceding medial Baa
						// Although not mentioned in spec, added Farsi Yeh (06CC) FBFD FBFF; equivalent to 064A or 0649
						elseif ($this->OTLdata[$i]['uni'] == 0xFEAE || $this->OTLdata[$i]['uni'] == 0xFEF2 || $this->OTLdata[$i]['uni'] == 0xFEF0 || $this->OTLdata[$i]['uni'] == 0xFEF4 || $this->OTLdata[$i]['uni'] == 0xFBE9 || $this->OTLdata[$i]['uni'] == 0xFBFD || $this->OTLdata[$i]['uni'] == 0xFBFF
						) {
							$checkpos = $i - 1;
							while (isset($this->OTLdata[$checkpos]) && strpos($this->GlyphClassMarks, $this->OTLdata[$checkpos]['hex']) !== false) {
								$checkpos--;
							}
							if (isset($this->OTLdata[$checkpos]) && $this->OTLdata[$checkpos]['uni'] == 0xFE92) {
								$this->OTLdata[$checkpos]['GPOSinfo']['kashida'] = 4; // ******* Before preceding BAA
							}
						} // Priority 6   WAW (0648) FEEE; Ain (0639) FECA; Qaf (0642) FED6; Fa (0641) FED2
						// Final form
						// Connecting to previous character
						// Position: Before the character
						elseif ($this->OTLdata[$i]['uni'] == 0xFEEE || $this->OTLdata[$i]['uni'] == 0xFECA || $this->OTLdata[$i]['uni'] == 0xFED6 || $this->OTLdata[$i]['uni'] == 0xFED2) {
							$this->OTLdata[$i]['GPOSinfo']['kashida'] = 3;
						}

						// Priority 7   Other connecting characters
						// Final form
						// Connecting to previous character
						// Position: Before the character
						/* This isn't in the spec, but using MS WORD as a basis, give a lower priority to the 3 characters already checked
						  in (5) above. Test case:
						  &#x62e;&#x652;&#x631;&#x64e;&#x649;&#x670;
						  &#x641;&#x64e;&#x62a;&#x64f;&#x630;&#x64e;&#x643;&#x651;&#x650;&#x631;
						 */

						if (!isset($this->OTLdata[$i]['GPOSinfo']['kashida'])) {
							if (strpos($this->GSUBdata[$this->GSUBfont]['finals'], $this->OTLdata[$i]['hex']) !== false) { // ANY OTHER FINAL FORM
								$this->OTLdata[$i]['GPOSinfo']['kashida'] = 2;
							} elseif (strpos('0FEAE 0FEF0 0FEF2', $this->OTLdata[$i]['hex']) !== false) { // not already included in 5 above
								$this->OTLdata[$i]['GPOSinfo']['kashida'] = 1;
							}
						}
					}

					//-----------------------------------------------------------------------------------
					// d. Apply Presentation Forms GSUB Lookups (+ any discretionary) - Apply one at a time in Feature order
					//-----------------------------------------------------------------------------------
					$tags = 'rlig calt liga clig mset';

					$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}

					$ts = explode(' ', $usetags);
					foreach ($ts as $ut) { //  - Apply one at a time in Feature order
						$this->_applyGSUBrules($ut, $GSUBscriptTag, $GSUBlangsys);
					}
					//-----------------------------------------------------------------------------------
					// e. NOT IN SPEC
					// If space precedes a mark -> substitute a &nbsp; before the Mark, to prevent line breaking Test:
					//-----------------------------------------------------------------------------------
					for ($ptr = 1; $ptr < count($this->OTLdata); $ptr++) {
						if ($this->OTLdata[$ptr]['general_category'] == Ucdn::UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK && $this->OTLdata[$ptr - 1]['uni'] == 32) {
							$this->OTLdata[$ptr - 1]['uni'] = 0xa0;
							$this->OTLdata[$ptr - 1]['hex'] = '000A0';
						}
					}
				} // 5(I). GSUB - Shaper - INDIC and SINHALA and KHMER
				//===================================
				elseif ($this->shaper == 'I' || $this->shaper == 'K' || $this->shaper == 'S') {
					$this->restrictToSyllable = true;
					//-----------------------------------------------------------------------------------
					// a. First decompose/compose split mattras
					// (normalize) ??????? Nukta/Halant order etc ??????????????????????????????????????????????????????????????????????????
					//-----------------------------------------------------------------------------------
					for ($ptr = 0; $ptr < count($this->OTLdata); $ptr++) {
						$char = $this->OTLdata[$ptr]['uni'];
						$sub = Indic::decompose_indic($char);
						if ($sub) {
							$newinfo = [];
							for ($i = 0; $i < count($sub); $i++) {
								$newinfo[$i] = [];
								$ucd_record = Ucdn::get_ucd_record($sub[$i]);
								$newinfo[$i]['general_category'] = $ucd_record[0];
								$newinfo[$i]['bidi_type'] = $ucd_record[2];
								$charasstr = $this->unicode_hex($sub[$i]);
								if (strpos($this->GlyphClassMarks, $charasstr) !== false) {
									$newinfo[$i]['group'] = 'M';
								} else {
									$newinfo[$i]['group'] = 'C';
								}
								$newinfo[$i]['uni'] = $sub[$i];
								$newinfo[$i]['hex'] = $charasstr;
							}
							array_splice($this->OTLdata, $ptr, 1, $newinfo);
							$ptr += count($sub) - 1;
						}
						/* Only Composition-exclusion exceptions that we want to recompose. */
						if ($this->shaper == 'I') {
							if ($char == 0x09AF && isset($this->OTLdata[$ptr + 1]) && $this->OTLdata[$ptr + 1]['uni'] == 0x09BC) {
								$sub = 0x09DF;
								$newinfo = [];
								$newinfo[0] = [];
								$ucd_record = Ucdn::get_ucd_record($sub);
								$newinfo[0]['general_category'] = $ucd_record[0];
								$newinfo[0]['bidi_type'] = $ucd_record[2];
								$newinfo[0]['group'] = 'C';
								$newinfo[0]['uni'] = $sub;
								$newinfo[0]['hex'] = $this->unicode_hex($sub);
								array_splice($this->OTLdata, $ptr, 2, $newinfo);
							}
						}
					}
					//-----------------------------------------------------------------------------------
					// b. Analyse characters - group as syllables/clusters (Indic); invalid diacritics; add dotted circle
					//-----------------------------------------------------------------------------------
					$indic_category_string = '';
					foreach ($this->OTLdata as $eid => $c) {
						Indic::set_indic_properties($this->OTLdata[$eid], $scriptblock); // sets ['indic_category'] and ['indic_position']
						//$c['general_category']
						//$c['combining_class']
						//$c['uni'] =  $char;

						$indic_category_string .= Indic::$indic_category_char[$this->OTLdata[$eid]['indic_category']];
					}

					$broken_syllables = false;
					if ($this->shaper == 'I') {
						Indic::set_syllables($this->OTLdata, $indic_category_string, $broken_syllables);
					} elseif ($this->shaper == 'S') {
						Indic::set_syllables_sinhala($this->OTLdata, $indic_category_string, $broken_syllables);
					} elseif ($this->shaper == 'K') {
						Indic::set_syllables_khmer($this->OTLdata, $indic_category_string, $broken_syllables);
					}
					$indic_category_string = '';

					//-----------------------------------------------------------------------------------
					// c. Initial Re-ordering (Indic / Khmer / Sinhala)
					//-----------------------------------------------------------------------------------
					// Find base consonant
					// Decompose/compose and reorder Matras
					// Reorder marks to canonical order

					$indic_config = Indic::$indic_configs[$scriptblock];
					$dottedcircle = false;
					if ($broken_syllables) {
						if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'], 0x25CC)) {
							$dottedcircle = [];
							$ucd_record = Ucdn::get_ucd_record(0x25CC);
							$dottedcircle[0]['general_category'] = $ucd_record[0];
							$dottedcircle[0]['bidi_type'] = $ucd_record[2];
							$dottedcircle[0]['group'] = 'C';
							$dottedcircle[0]['uni'] = 0x25CC;
							$dottedcircle[0]['indic_category'] = Indic::OT_DOTTEDCIRCLE;
							$dottedcircle[0]['indic_position'] = Indic::POS_BASE_C;

							$dottedcircle[0]['hex'] = '025CC';  // TEMPORARY *****
						}
					}
					Indic::initial_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $indic_config, $scriptblock, $is_old_spec, $dottedcircle);

					//-----------------------------------------------------------------------------------
					// d. Apply initial and basic shaping forms GSUB Lookups (one at a time)
					//-----------------------------------------------------------------------------------
					if ($this->shaper == 'I' || $this->shaper == 'S') {
						$tags = 'locl ccmp nukt akhn rphf rkrf pref blwf half pstf vatu cjct';
					} elseif ($this->shaper == 'K') {
						$tags = 'locl ccmp pref blwf abvf pstf cfar';
					}
					$this->_applyGSUBrulesIndic($tags, $GSUBscriptTag, $GSUBlangsys, $is_old_spec);

					//-----------------------------------------------------------------------------------
					// e. Final Re-ordering (Indic / Khmer / Sinhala)
					//-----------------------------------------------------------------------------------
					// Reorder matras
					// Reorder reph
					// Reorder pre-base reordering consonants:

					Indic::final_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $indic_config, $scriptblock, $is_old_spec);

					//-----------------------------------------------------------------------------------
					// f. Apply 'init' feature to first syllable in word (indicated by ['mask']) Indic::FLAG(Indic::INIT);
					//-----------------------------------------------------------------------------------
					if ($this->shaper == 'I' || $this->shaper == 'S') {
						$tags = 'init';
						$this->_applyGSUBrulesIndic($tags, $GSUBscriptTag, $GSUBlangsys, $is_old_spec);
					}

					//-----------------------------------------------------------------------------------
					// g. Apply Presentation Forms GSUB Lookups (+ any discretionary)
					//-----------------------------------------------------------------------------------
					$tags = 'pres abvs blws psts haln rlig calt liga clig mset';

					$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}
					if ($this->shaper == 'K') {  // Features are applied one at a time, working through each codepoint
						$this->_applyGSUBrulesSingly($usetags, $GSUBscriptTag, $GSUBlangsys);
					} else {
						$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
					}
					$this->restrictToSyllable = false;
				} // 5(M). GSUB - Shaper - MYANMAR (ONLY mym2)
				//==============================
				// NB Old style 'mymr' is left to go through the default shaper
				elseif ($this->shaper == 'M') {
					$this->restrictToSyllable = true;
					//-----------------------------------------------------------------------------------
					// a. Analyse characters - group as syllables/clusters (Myanmar); invalid diacritics; add dotted circle
					//-----------------------------------------------------------------------------------
					$myanmar_category_string = '';
					foreach ($this->OTLdata as $eid => $c) {
						Myanmar::set_myanmar_properties($this->OTLdata[$eid]); // sets ['myanmar_category'] and ['myanmar_position']
						$myanmar_category_string .= Myanmar::$myanmar_category_char[$this->OTLdata[$eid]['myanmar_category']];
					}
					$broken_syllables = false;
					Myanmar::set_syllables($this->OTLdata, $myanmar_category_string, $broken_syllables);
					$myanmar_category_string = '';

					//-----------------------------------------------------------------------------------
					// b. Re-ordering (Myanmar mym2)
					//-----------------------------------------------------------------------------------
					$dottedcircle = false;
					if ($broken_syllables) {
						if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'], 0x25CC)) {
							$dottedcircle = [];
							$ucd_record = Ucdn::get_ucd_record(0x25CC);
							$dottedcircle[0]['general_category'] = $ucd_record[0];
							$dottedcircle[0]['bidi_type'] = $ucd_record[2];
							$dottedcircle[0]['group'] = 'C';
							$dottedcircle[0]['uni'] = 0x25CC;
							$dottedcircle[0]['myanmar_category'] = Myanmar::OT_DOTTEDCIRCLE;
							$dottedcircle[0]['myanmar_position'] = Myanmar::POS_BASE_C;
							$dottedcircle[0]['hex'] = '025CC';
						}
					}
					Myanmar::reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $dottedcircle);

					//-----------------------------------------------------------------------------------
					// c. Apply initial and basic shaping forms GSUB Lookups (one at a time)
					//-----------------------------------------------------------------------------------

					$tags = 'locl ccmp rphf pref blwf pstf';
					$this->_applyGSUBrulesMyanmar($tags, $GSUBscriptTag, $GSUBlangsys);

					//-----------------------------------------------------------------------------------
					// d. Apply Presentation Forms GSUB Lookups (+ any discretionary)
					//-----------------------------------------------------------------------------------
					$tags = 'pres abvs blws psts haln rlig calt liga clig mset';
					$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}
					$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
					$this->restrictToSyllable = false;
				} // 5(E). GSUB - Shaper - SEA South East Asian (New Tai Lue, Cham, Tai Tam)
				//==============================
				elseif ($this->shaper == 'E') {
					/* HarfBuzz says: If the designer designed the font for the 'DFLT' script,
					 * use the default shaper.  Otherwise, use the SEA shaper.
					 * Note that for some simple scripts, there may not be *any*
					 * GSUB/GPOS needed, so there may be no scripts found! */

					$this->restrictToSyllable = true;
					//-----------------------------------------------------------------------------------
					// a. Analyse characters - group as syllables/clusters (Indic); invalid diacritics; add dotted circle
					//-----------------------------------------------------------------------------------
					$sea_category_string = '';
					foreach ($this->OTLdata as $eid => $c) {
						Sea::set_sea_properties($this->OTLdata[$eid], $scriptblock); // sets ['sea_category'] and ['sea_position']
						//$c['general_category']
						//$c['combining_class']
						//$c['uni'] =  $char;

						$sea_category_string .= Sea::$sea_category_char[$this->OTLdata[$eid]['sea_category']];
					}

					$broken_syllables = false;
					Sea::set_syllables($this->OTLdata, $sea_category_string, $broken_syllables);
					$sea_category_string = '';

					//-----------------------------------------------------------------------------------
					// b. Apply locl and ccmp shaping forms - before initial re-ordering; GSUB Lookups (one at a time)
					//-----------------------------------------------------------------------------------
					$tags = 'locl ccmp';
					$this->_applyGSUBrulesSingly($tags, $GSUBscriptTag, $GSUBlangsys);

					//-----------------------------------------------------------------------------------
					// c. Initial Re-ordering
					//-----------------------------------------------------------------------------------
					// Find base consonant
					// Decompose/compose and reorder Matras
					// Reorder marks to canonical order

					$dottedcircle = false;
					if ($broken_syllables) {
						if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'], 0x25CC)) {
							$dottedcircle = [];
							$ucd_record = Ucdn::get_ucd_record(0x25CC);
							$dottedcircle[0]['general_category'] = $ucd_record[0];
							$dottedcircle[0]['bidi_type'] = $ucd_record[2];
							$dottedcircle[0]['group'] = 'C';
							$dottedcircle[0]['uni'] = 0x25CC;
							$dottedcircle[0]['sea_category'] = Sea::OT_GB;
							$dottedcircle[0]['sea_position'] = Sea::POS_BASE_C;

							$dottedcircle[0]['hex'] = '025CC';  // TEMPORARY *****
						}
					}
					Sea::initial_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $scriptblock, $dottedcircle);

					//-----------------------------------------------------------------------------------
					// d. Apply basic shaping forms GSUB Lookups (one at a time)
					//-----------------------------------------------------------------------------------
					$tags = 'pref abvf blwf pstf';
					$this->_applyGSUBrulesSingly($tags, $GSUBscriptTag, $GSUBlangsys);

					//-----------------------------------------------------------------------------------
					// e. Final Re-ordering
					//-----------------------------------------------------------------------------------

					Sea::final_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $scriptblock);

					//-----------------------------------------------------------------------------------
					// f. Apply Presentation Forms GSUB Lookups (+ any discretionary)
					//-----------------------------------------------------------------------------------
					$tags = 'pres abvs blws psts';

					$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}
					$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
					$this->restrictToSyllable = false;
				} // 5(D). GSUB - Shaper - DEFAULT (including THAI and LAO and MYANMAR v1 [mymr] and TIBETAN)
				//==============================
				else { // DEFAULT
					//-----------------------------------------------------------------------------------
					// a. First decompose/compose in Thai / Lao - Tibetan
					//-----------------------------------------------------------------------------------
					// Decomposition for THAI or LAO
					/* This function implements the shaping logic documented here:
					 *
					 *   http://linux.thai.net/~thep/th-otf/shaping.html
					 *
					 * The first shaping rule listed there is needed even if the font has Thai
					 * OpenType tables.
					 *
					 *
					 * The following is NOT specified in the MS OT Thai spec, however, it seems
					 * to be what Uniscribe and other engines implement.  According to Eric Muller:
					 *
					 * When you have a SARA AM, decompose it in NIKHAHIT + SARA AA, *and* move the
					 * NIKHAHIT backwards over any tone mark (0E48-0E4B).
					 *
					 * <0E14, 0E4B, 0E33> -> <0E14, 0E4D, 0E4B, 0E32>
					 *
					 * This reordering is legit only when the NIKHAHIT comes from a SARA AM, not
					 * when it's there to start with. The string <0E14, 0E4B, 0E4D> is probably
					 * not what a user wanted, but the rendering is nevertheless nikhahit above
					 * chattawa.
					 *
					 * Same for Lao.
					 *
					 *          Thai        Lao
					 * SARA AM:     U+0E33  U+0EB3
					 * SARA AA:     U+0E32  U+0EB2
					 * Nikhahit:    U+0E4D  U+0ECD
					 *
					 * Testing shows that Uniscribe reorder the following marks:
					 * Thai:    <0E31,0E34..0E37,0E47..0E4E>
					 * Lao: <0EB1,0EB4..0EB7,0EC7..0ECE>
					 *
					 * Lao versions are the same as Thai + 0x80.
					 */
					if ($this->shaper == 'T' || $this->shaper == 'L') {
						for ($ptr = 0; $ptr < count($this->OTLdata); $ptr++) {
							$char = $this->OTLdata[$ptr]['uni'];
							if (($char & ~0x0080) == 0x0E33) { // if SARA_AM (U+0E33 or U+0EB3)
								$NIKHAHIT = $char + 0x1A;
								$SARA_AA = $char - 1;
								$sub = [$SARA_AA, $NIKHAHIT];

								$newinfo = [];
								$ucd_record = Ucdn::get_ucd_record($sub[0]);
								$newinfo[0]['general_category'] = $ucd_record[0];
								$newinfo[0]['bidi_type'] = $ucd_record[2];
								$charasstr = $this->unicode_hex($sub[0]);
								if (strpos($this->GlyphClassMarks, $charasstr) !== false) {
									$newinfo[0]['group'] = 'M';
								} else {
									$newinfo[0]['group'] = 'C';
								}
								$newinfo[0]['uni'] = $sub[0];
								$newinfo[0]['hex'] = $charasstr;
								$this->OTLdata[$ptr] = $newinfo[0]; // Substitute SARA_AM => SARA_AA

								$ntones = 0; // number of (preceding) tone marks
								// IS_TONE_MARK ((x) & ~0x0080, 0x0E34 - 0x0E37, 0x0E47 - 0x0E4E, 0x0E31)
								while (isset($this->OTLdata[$ptr - 1 - $ntones]) && (
								($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) == 0x0E31 ||
								(($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) >= 0x0E34 &&
								($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) <= 0x0E37) ||
								(($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) >= 0x0E47 &&
								($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) <= 0x0E4E)
								)
								) {
									$ntones++;
								}

								$newinfo = [];
								$ucd_record = Ucdn::get_ucd_record($sub[1]);
								$newinfo[0]['general_category'] = $ucd_record[0];
								$newinfo[0]['bidi_type'] = $ucd_record[2];
								$charasstr = $this->unicode_hex($sub[1]);
								if (strpos($this->GlyphClassMarks, $charasstr) !== false) {
									$newinfo[0]['group'] = 'M';
								} else {
									$newinfo[0]['group'] = 'C';
								}
								$newinfo[0]['uni'] = $sub[1];
								$newinfo[0]['hex'] = $charasstr;
								// Insert NIKAHIT
								array_splice($this->OTLdata, $ptr - $ntones, 0, $newinfo);

								$ptr++;
							}
						}
					}

					if ($scriptblock == Ucdn::SCRIPT_TIBETAN) {
						// =========================
						// Reordering TIBETAN
						// =========================
						// Tibetan does not need to need a shaper generally, as long as characters are presented in the correct order
						// so we will do one minor change here:
						// From ICU: If the present character is a number, and the next character is a pre-number combining mark
						// then the two characters are reordered
						// From MS OTL spec the following are Digit modifiers (Md): 0F18–0F19, 0F3E–0F3F
						// Digits: 0F20–0F33
						// On testing only 0x0F3F (pre-based mark) seems to need re-ordering
						for ($ptr = 0; $ptr < count($this->OTLdata) - 1; $ptr++) {
							if (Indic::in_range($this->OTLdata[$ptr]['uni'], 0x0F20, 0x0F33) && $this->OTLdata[$ptr + 1]['uni'] == 0x0F3F) {
								$tmp = $this->OTLdata[$ptr + 1];
								$this->OTLdata[$ptr + 1] = $this->OTLdata[$ptr];
								$this->OTLdata[$ptr] = $tmp;
							}
						}


						// =========================
						// Decomposition for TIBETAN
						// =========================
						/* Recommended, but does not seem to change anything...
						  for($ptr=0; $ptr<count($this->OTLdata); $ptr++) {
						  $char = $this->OTLdata[$ptr]['uni'];
						  $sub = Indic::decompose_indic($char);
						  if ($sub) {
						  $newinfo = array();
						  for($i=0;$i<count($sub);$i++) {
						  $newinfo[$i] = array();
						  $ucd_record = Ucdn::get_ucd_record($sub[$i]);
						  $newinfo[$i]['general_category'] = $ucd_record[0];
						  $newinfo[$i]['bidi_type'] = $ucd_record[2];
						  $charasstr = $this->unicode_hex($sub[$i]);
						  if (strpos($this->GlyphClassMarks, $charasstr)!==false) { $newinfo[$i]['group'] =  'M'; }
						  else { $newinfo[$i]['group'] =  'C'; }
						  $newinfo[$i]['uni'] =  $sub[$i];
						  $newinfo[$i]['hex'] =  $charasstr;
						  }
						  array_splice($this->OTLdata, $ptr, 1, $newinfo);
						  $ptr += count($sub)-1;
						  }
						  }
						 */
					}


					//-----------------------------------------------------------------------------------
					// b. Apply all GSUB Lookups (in order specified in lookup list)
					//-----------------------------------------------------------------------------------
					$tags = 'locl ccmp pref blwf abvf pstf pres abvs blws psts haln rlig calt liga clig mset  RQD';
					// pref blwf abvf pstf required for Tibetan
					// " RQD" is a non-standard tag in Garuda font - presumably intended to be used by default ? "ReQuireD"
					// Being a 3 letter tag is non-standard, and does not allow it to be set by font-feature-settings


					/* ?Add these until shapers witten?
					  Hangul:   ljmo vjmo tjmo
					 */

					$omittags = '';
					$useGSUBtags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$useGSUBtags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}
					// APPLY GSUB rules (as long as not Latin + SmallCaps - but not OTL smcp)
					if (!(($this->mpdf->textvar & TextVars::FC_SMALLCAPS) && $scriptblock == Ucdn::SCRIPT_LATIN && strpos($useGSUBtags, 'smcp') === false)) {
						$this->_applyGSUBrules($useGSUBtags, $GSUBscriptTag, $GSUBlangsys);
					}
				}
			}

			// Shapers - KHMER & THAI & LAO - Replace Word boundary marker with U+200B
			// Also TIBETAN (no shaper)
			//=======================================================
			if (($this->shaper == "K" || $this->shaper == "T" || $this->shaper == "L") || $scriptblock == Ucdn::SCRIPT_TIBETAN) {
				// Set up properties to insert a U+200B character
				$newinfo = [];
				//$newinfo[0] = array('general_category' => 1, 'bidi_type' => 14, 'group' => 'S', 'uni' => 0x200B, 'hex' => '0200B');
				$newinfo[0] = [
					'general_category' => Ucdn::UNICODE_GENERAL_CATEGORY_FORMAT,
					'bidi_type' => Ucdn::BIDI_CLASS_BN,
					'group' => 'S', 'uni' => 0x200B, 'hex' => '0200B'];
				// Then insert U+200B at (after) all word end boundaries
				for ($i = count($this->OTLdata) - 1; $i > 0; $i--) {
					// Make sure after GSUB that wordend has not been moved - check next char is not in the same syllable
					if (isset($this->OTLdata[$i]['wordend']) && $this->OTLdata[$i]['wordend'] &&
						isset($this->OTLdata[$i + 1]['uni']) && (!isset($this->OTLdata[$i + 1]['syllable']) || !isset($this->OTLdata[$i + 1]['syllable']) || $this->OTLdata[$i + 1]['syllable'] != $this->OTLdata[$i]['syllable'])) {
						array_splice($this->OTLdata, $i + 1, 0, $newinfo);
						$this->_updateLigatureMarks($i, 1);
					} elseif ($this->OTLdata[$i]['uni'] == 0x2e) { // Word end if Full-stop.
						array_splice($this->OTLdata, $i + 1, 0, $newinfo);
						$this->_updateLigatureMarks($i, 1);
					}
				}
			}


			// Shapers - INDIC & ARABIC & KHMER & SINHALA  & MYANMAR - Remove ZWJ and ZWNJ
			//=======================================================
			if ($this->shaper == 'I' || $this->shaper == 'S' || $this->shaper == 'A' || $this->shaper == 'K' || $this->shaper == 'M') {
				// Remove ZWJ and ZWNJ
				for ($i = 0; $i < count($this->OTLdata); $i++) {
					if ($this->OTLdata[$i]['uni'] == 8204 || $this->OTLdata[$i]['uni'] == 8205) {
						array_splice($this->OTLdata, $i, 1);
						$this->_updateLigatureMarks($i, -1);
					}
				}
			}


			////////////////////////////////////////////////////////////////
			////////////////////////////////////////////////////////////////
			//////////       GPOS          /////////////////////////////////
			////////////////////////////////////////////////////////////////
			////////////////////////////////////////////////////////////////
			if (($useOTL & 0xFF) && $GPOSscriptTag && $GPOSlangsys && $GPOSFeatures) {
				$this->Entry = [];
				$this->Exit = [];

				// 6. Load GPOS data, Coverage & Lookups
				//=================================================================
				$fontCacheFilename = $this->mpdf->CurrentFont['fontkey'] . '.GPOSdata.json';
				if (!isset($this->GPOSdata[$this->fontkey]) && $this->fontCache->jsonHas($fontCacheFilename)) {
					$this->LuCoverage = $this->GPOSdata[$this->fontkey]['LuCoverage'] = $this->fontCache->jsonLoad($fontCacheFilename);
				} else {
					$this->LuCoverage = $this->GPOSdata[$this->fontkey]['LuCoverage'];
				}

				$this->GPOSLookups = $this->mpdf->CurrentFont['GPOSLookups'];


				// 7. Select Feature tags to use (incl optional)
				//==============================
				$tags = 'abvm blwm mark mkmk curs cpsp dist requ'; // Default set
				// 'requ' is not listed in the Microsoft registry of Feature tags
				// Found in Arial Unicode MS, it repositions the baseline for punctuation in Kannada script

				// ZZZ96
				// Set kern to be included by default in non-Latin script (? just when shapers used)
				// Kern is used in some fonts to reposition marks etc. and is essential for correct display
				//if ($this->shaper) {$tags .= ' kern'; }
				if ($scriptblock != Ucdn::SCRIPT_LATIN) {
					$tags .= ' kern';
				}

				$omittags = '';
				$usetags = $tags;
				if (!empty($this->mpdf->OTLtags)) {
					$usetags = $this->_applyTagSettings($tags, $GPOSFeatures, $omittags, false);
				}



				// 8. Get GPOS LookupList from Feature tags
				//==============================
				$LookupList = [];
				foreach ($GPOSFeatures as $tag => $arr) {
					if (strpos($usetags, $tag) !== false) {
						foreach ($arr as $lu) {
							$LookupList[$lu] = $tag;
						}
					}
				}
				ksort($LookupList);


				// 9. Apply GPOS Lookups (in order specified in lookup list but selecting from specified tags)
				//==============================
				// APPLY THE GPOS RULES (as long as not Latin + SmallCaps - but not OTL smcp)
				if (!(($this->mpdf->textvar & TextVars::FC_SMALLCAPS) && $scriptblock == Ucdn::SCRIPT_LATIN && strpos($useGSUBtags, 'smcp') === false)) {
					$this->_applyGPOSrules($LookupList, $is_old_spec);
					// (sets: $this->OTLdata[n]['GPOSinfo'] XPlacement YPlacement XAdvance Entry Exit )
				}

				// 10. Process cursive text
				//==============================
				if (count($this->Entry) || count($this->Exit)) {
					// RTL
					$incurs = false;
					for ($i = (count($this->OTLdata) - 1); $i >= 0; $i--) {
						if (isset($this->Entry[$i]) && isset($this->Entry[$i]['Y']) && $this->Entry[$i]['dir'] == 'RTL') {
							$nextbase = $i - 1; // Set as next base ignoring marks (next base reading RTL in logical oder
							while (isset($this->OTLdata[$nextbase]['hex']) && strpos($this->GlyphClassMarks, $this->OTLdata[$nextbase]['hex']) !== false) {
								$nextbase--;
							}
							if (isset($this->Exit[$nextbase]) && isset($this->Exit[$nextbase]['Y'])) {
								$diff = $this->Entry[$i]['Y'] - $this->Exit[$nextbase]['Y'];
								if ($incurs === false) {
									$incurs = $diff;
								} else {
									$incurs += $diff;
								}
								for ($j = ($i - 1); $j >= $nextbase; $j--) {
									if (isset($this->OTLdata[$j]['GPOSinfo']['YPlacement'])) {
										$this->OTLdata[$j]['GPOSinfo']['YPlacement'] += $incurs;
									} else {
										$this->OTLdata[$j]['GPOSinfo']['YPlacement'] = $incurs;
									}
								}
								if (isset($this->Exit[$i]['X']) && isset($this->Entry[$nextbase]['X'])) {
									$adj = -($this->Entry[$i]['X'] - $this->Exit[$nextbase]['X']);
									// If XAdvance is aplied - in order for PDF to position the Advance correctly need to place it on:
									// in RTL - the current glyph or the last of any associated marks
									if (isset($this->OTLdata[$nextbase + 1]['GPOSinfo']['XAdvance'])) {
										$this->OTLdata[$nextbase + 1]['GPOSinfo']['XAdvance'] += $adj;
									} else {
										$this->OTLdata[$nextbase + 1]['GPOSinfo']['XAdvance'] = $adj;
									}
								}
							} else {
								$incurs = false;
							}
						} elseif (strpos($this->GlyphClassMarks, $this->OTLdata[$i]['hex']) !== false) {
							continue;
						} // ignore Marks
						else {
							$incurs = false;
						}
					}
					// LTR
					$incurs = false;
					for ($i = 0; $i < count($this->OTLdata); $i++) {
						if (isset($this->Exit[$i]) && isset($this->Exit[$i]['Y']) && $this->Exit[$i]['dir'] == 'LTR') {
							$nextbase = $i + 1; // Set as next base ignoring marks
							while (strpos($this->GlyphClassMarks, $this->OTLdata[$nextbase]['hex']) !== false) {
								$nextbase++;
							}
							if (isset($this->Entry[$nextbase]) && isset($this->Entry[$nextbase]['Y'])) {
								$diff = $this->Exit[$i]['Y'] - $this->Entry[$nextbase]['Y'];
								if ($incurs === false) {
									$incurs = $diff;
								} else {
									$incurs += $diff;
								}
								for ($j = ($i + 1); $j <= $nextbase; $j++) {
									if (isset($this->OTLdata[$j]['GPOSinfo']['YPlacement'])) {
										$this->OTLdata[$j]['GPOSinfo']['YPlacement'] += $incurs;
									} else {
										$this->OTLdata[$j]['GPOSinfo']['YPlacement'] = $incurs;
									}
								}
								if (isset($this->Exit[$i]['X']) && isset($this->Entry[$nextbase]['X'])) {
									$adj = -($this->Exit[$i]['X'] - $this->Entry[$nextbase]['X']);
									// If XAdvance is aplied - in order for PDF to position the Advance correctly need to place it on:
									// in LTR - the next glyph, ignoring marks
									if (isset($this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'])) {
										$this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'] += $adj;
									} else {
										$this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'] = $adj;
									}
								}
							} else {
								$incurs = false;
							}
						} elseif (strpos($this->GlyphClassMarks, $this->OTLdata[$i]['hex']) !== false) {
							continue;
						} // ignore Marks
						else {
							$incurs = false;
						}
					}
				}
			} // end GPOS

			if ($this->debugOTL) {
				$this->_dumpproc('END', '-', '-', '-', '-', 0, '-', 0);
				exit;
			}

			$this->schOTLdata[$sch] = $this->OTLdata;
			$this->OTLdata = [];
		} // END foreach subchunk
		// 11. Re-assemble and return text string
		//==============================
		$newGPOSinfo = [];
		$newOTLdata = [];
		$newchar_data = [];
		$newgroup = '';
		$e = '';
		$ectr = 0;

		for ($sch = 0; $sch <= $subchunk; $sch++) {
			for ($i = 0; $i < count($this->schOTLdata[$sch]); $i++) {
				if (isset($this->schOTLdata[$sch][$i]['GPOSinfo'])) {
					$newGPOSinfo[$ectr] = $this->schOTLdata[$sch][$i]['GPOSinfo'];
				}
				$newchar_data[$ectr] = ['bidi_class' => $this->schOTLdata[$sch][$i]['bidi_type'], 'uni' => $this->schOTLdata[$sch][$i]['uni']];
				$newgroup .= $this->schOTLdata[$sch][$i]['group'];
				$e .= UtfString::code2utf($this->schOTLdata[$sch][$i]['uni']);
				if (isset($this->mpdf->CurrentFont['subset'])) {
					$this->mpdf->CurrentFont['subset'][$this->schOTLdata[$sch][$i]['uni']] = $this->schOTLdata[$sch][$i]['uni'];
				}
				$ectr++;
			}
		}
		$this->OTLdata['GPOSinfo'] = $newGPOSinfo;
		$this->OTLdata['char_data'] = $newchar_data;
		$this->OTLdata['group'] = $newgroup;

		// This leaves OTLdata::GPOSinfo, ::bidi_type, & ::group

		return $e;
	}

	function _applyTagSettings($tags, $Features, $omittags = '', $onlytags = false)
	{
		if (empty($this->mpdf->OTLtags['Plus']) && empty($this->mpdf->OTLtags['Minus']) && empty($this->mpdf->OTLtags['FFPlus']) && empty($this->mpdf->OTLtags['FFMinus'])) {
			return $tags;
		}

		// Use $tags as starting point
		$usetags = $tags;

		// Only set / unset tags which are in the font
		// Ignore tags which are in $omittags
		// If $onlytags, then just unset tags which are already in the Tag list

		$fp = $fm = $ffp = $ffm = '';

		// Font features to enable - set by font-variant-xx
		if (isset($this->mpdf->OTLtags['Plus'])) {
			$fp = $this->mpdf->OTLtags['Plus'];
		}
		preg_match_all('/([a-zA-Z0-9]{4})/', $fp, $m);
		for ($i = 0; $i < count($m[0]); $i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if (isset($Features[$t]) && strpos($omittags, $t) === false && (!$onlytags || strpos($tags, $t) !== false )) {
				$usetags .= ' ' . $t;
			}
		}

		// Font features to disable - set by font-variant-xx
		if (isset($this->mpdf->OTLtags['Minus'])) {
			$fm = $this->mpdf->OTLtags['Minus'];
		}
		preg_match_all('/([a-zA-Z0-9]{4})/', $fm, $m);
		for ($i = 0; $i < count($m[0]); $i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if (isset($Features[$t]) && strpos($omittags, $t) === false && (!$onlytags || strpos($tags, $t) !== false )) {
				$usetags = str_replace($t, '', $usetags);
			}
		}

		// Font features to enable - set by font-feature-settings
		if (isset($this->mpdf->OTLtags['FFPlus'])) {
			$ffp = $this->mpdf->OTLtags['FFPlus']; // Font Features - may include integer: salt4
		}
		preg_match_all('/([a-zA-Z0-9]{4})([\d+]*)/', $ffp, $m);
		for ($i = 0; $i < count($m[0]); $i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if (isset($Features[$t]) && strpos($omittags, $t) === false && (!$onlytags || strpos($tags, $t) !== false )) {
				$usetags .= ' ' . $m[0][$i];  //  - may include integer: salt4
			}
		}

		// Font features to disable - set by font-feature-settings
		if (isset($this->mpdf->OTLtags['FFMinus'])) {
			$ffm = $this->mpdf->OTLtags['FFMinus'];
		}
		preg_match_all('/([a-zA-Z0-9]{4})/', $ffm, $m);
		for ($i = 0; $i < count($m[0]); $i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if (isset($Features[$t]) && strpos($omittags, $t) === false && (!$onlytags || strpos($tags, $t) !== false )) {
				$usetags = str_replace($t, '', $usetags);
			}
		}
		return $usetags;
	}

	function _applyGSUBrules($usetags, $scriptTag, $langsys)
	{
		// Features from all Tags are applied together, in Lookup List order.
		// For Indic - should be applied one syllable at a time
		// - Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
		// if $this->restrictToSyllable is true

		$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];
		$LookupList = [];
		foreach ($GSUBFeatures as $tag => $arr) {
			if (strpos($usetags, $tag) !== false) {
				foreach ($arr as $lu) {
					$LookupList[$lu] = $tag;
				}
			}
		}
		ksort($LookupList);

		foreach ($LookupList as $lu => $tag) {
			$Type = $this->GSUBLookups[$lu]['Type'];
			$Flag = $this->GSUBLookups[$lu]['Flag'];
			$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
			$tagInt = 1;
			if (preg_match('/' . $tag . '([0-9]{1,2})/', $usetags, $m)) {
				$tagInt = $m[1];
			}
			$ptr = 0;
			// Test each glyph sequentially
			while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
				$currGlyph = $this->OTLdata[$ptr]['hex'];
				$currGID = $this->OTLdata[$ptr]['uni'];
				$shift = 1;
				foreach ($this->GSUBLookups[$lu]['Subtables'] as $c => $subtable_offset) {
					// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
					if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
						// Get rules from font GSUB subtable
						$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $tag, 0, $tagInt);

						if ($shift) {
							break;
						}
					}
				}
				if ($shift == 0) {
					$shift = 1;
				}
				$ptr += $shift;
			}
		}
	}

	function _applyGSUBrulesSingly($usetags, $scriptTag, $langsys)
	{
		// Features are applied one at a time, working through each codepoint

		$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

		$tags = explode(' ', $usetags);
		foreach ($tags as $usetag) {
			$LookupList = [];
			foreach ($GSUBFeatures as $tag => $arr) {
				if (strpos($usetags, $tag) !== false) {
					foreach ($arr as $lu) {
						$LookupList[$lu] = $tag;
					}
				}
			}
			ksort($LookupList);

			$ptr = 0;
			// Test each glyph sequentially
			while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
				$currGlyph = $this->OTLdata[$ptr]['hex'];
				$currGID = $this->OTLdata[$ptr]['uni'];
				$shift = 1;

				foreach ($LookupList as $lu => $tag) {
					$Type = $this->GSUBLookups[$lu]['Type'];
					$Flag = $this->GSUBLookups[$lu]['Flag'];
					$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
					$tagInt = 1;
					if (preg_match('/' . $tag . '([0-9]{1,2})/', $usetags, $m)) {
						$tagInt = $m[1];
					}

					foreach ($this->GSUBLookups[$lu]['Subtables'] as $c => $subtable_offset) {
						// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
						if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
							// Get rules from font GSUB subtable
							$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $tag, 0, $tagInt);

							if ($shift) {
								break 2;
							}
						}
					}
				}
				if ($shift == 0) {
					$shift = 1;
				}
				$ptr += $shift;
			}
		}
	}

	function _applyGSUBrulesMyanmar($usetags, $scriptTag, $langsys)
	{
		// $usetags = locl ccmp rphf pref blwf pstf';
		// applied to all characters

		$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

		// ALL should be applied one syllable at a time
		// Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
		$tags = explode(' ', $usetags);
		foreach ($tags as $usetag) {
			$LookupList = [];
			foreach ($GSUBFeatures as $tag => $arr) {
				if ($tag == $usetag) {
					foreach ($arr as $lu) {
						$LookupList[$lu] = $tag;
					}
				}
			}
			ksort($LookupList);

			foreach ($LookupList as $lu => $tag) {
				$Type = $this->GSUBLookups[$lu]['Type'];
				$Flag = $this->GSUBLookups[$lu]['Flag'];
				$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
				$tagInt = 1;
				if (preg_match('/' . $tag . '([0-9]{1,2})/', $usetags, $m)) {
					$tagInt = $m[1];
				}

				$ptr = 0;
				// Test each glyph sequentially
				while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
					$currGlyph = $this->OTLdata[$ptr]['hex'];
					$currGID = $this->OTLdata[$ptr]['uni'];
					$shift = 1;
					foreach ($this->GSUBLookups[$lu]['Subtables'] as $c => $subtable_offset) {
						// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
						if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
							// Get rules from font GSUB subtable
							$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $usetag, 0, $tagInt);

							if ($shift) {
								break;
							}
						}
					}
					if ($shift == 0) {
						$shift = 1;
					}
					$ptr += $shift;
				}
			}
		}
	}

	function _applyGSUBrulesIndic($usetags, $scriptTag, $langsys, $is_old_spec)
	{
		// $usetags = 'locl ccmp nukt akhn rphf rkrf pref blwf half pstf vatu cjct'; then later - init
		// rphf, pref, blwf, half, abvf, pstf, and init are only applied where ['mask'] indicates:  Indic::FLAG(Indic::RPHF);
		// The rest are applied to all characters

		$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

		// ALL should be applied one syllable at a time
		// Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
		$tags = explode(' ', $usetags);
		foreach ($tags as $usetag) {
			$LookupList = [];
			foreach ($GSUBFeatures as $tag => $arr) {
				if ($tag == $usetag) {
					foreach ($arr as $lu) {
						$LookupList[$lu] = $tag;
					}
				}
			}
			ksort($LookupList);

			foreach ($LookupList as $lu => $tag) {
				$Type = $this->GSUBLookups[$lu]['Type'];
				$Flag = $this->GSUBLookups[$lu]['Flag'];
				$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
				$tagInt = 1;
				if (preg_match('/' . $tag . '([0-9]{1,2})/', $usetags, $m)) {
					$tagInt = $m[1];
				}

				$ptr = 0;
				// Test each glyph sequentially
				while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
					$currGlyph = $this->OTLdata[$ptr]['hex'];
					$currGID = $this->OTLdata[$ptr]['uni'];
					$shift = 1;
					foreach ($this->GSUBLookups[$lu]['Subtables'] as $c => $subtable_offset) {
						// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
						if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
							if (strpos('rphf pref blwf half pstf cfar init', $usetag) !== false) { // only apply when mask indicates
								$mask = 0;
								switch ($usetag) {
									case 'rphf':
										$mask = (1 << (Indic::RPHF));
										break;
									case 'pref':
										$mask = (1 << (Indic::PREF));
										break;
									case 'blwf':
										$mask = (1 << (Indic::BLWF));
										break;
									case 'half':
										$mask = (1 << (Indic::HALF));
										break;
									case 'pstf':
										$mask = (1 << (Indic::PSTF));
										break;
									case 'cfar':
										$mask = (1 << (Indic::CFAR));
										break;
									case 'init':
										$mask = (1 << (Indic::INIT));
										break;
								}
								if (!($this->OTLdata[$ptr]['mask'] & $mask)) {
									continue;
								}
							}
							// Get rules from font GSUB subtable
							$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $usetag, $is_old_spec, $tagInt);

							if ($shift) {
								break;
							}
						} // Special case for Indic  ZZZ99S
						// Check to substitute Halant-Consonant in PREF, BLWF or PSTF
						// i.e. new spec but GSUB tables have Consonant-Halant in Lookups e.g. FreeSerif, which
						// incorrectly just moved old spec tables to new spec. Uniscribe seems to cope with this
						// See also ttffontsuni.php
						// First check if current glyph is a Halant/Virama
						elseif (static::_OTL_OLD_SPEC_COMPAT_1 && $Type == 4 && !$is_old_spec && strpos('0094D 009CD 00A4D 00ACD 00B4D 00BCD 00C4D 00CCD 00D4D', $currGlyph) !== false) {
							// only apply when 'pref blwf pstf' tags, and when mask indicates
							if (strpos('pref blwf pstf', $usetag) !== false) {
								$mask = 0;
								switch ($usetag) {
									case 'pref':
										$mask = (1 << (Indic::PREF));
										break;
									case 'blwf':
										$mask = (1 << (Indic::BLWF));
										break;
									case 'pstf':
										$mask = (1 << (Indic::PSTF));
										break;
								}
								if (!($this->OTLdata[$ptr]['mask'] & $mask)) {
									continue;
								}

								$nextGlyph = $this->OTLdata[$ptr + 1]['hex'];
								$nextGID = $this->OTLdata[$ptr + 1]['uni'];
								if (isset($this->GSLuCoverage[$lu][$c][$nextGID])) {
									// Get rules from font GSUB subtable
									$shift = $this->_applyGSUBsubtableSpecial($lu, $c, $ptr, $currGlyph, $currGID, $nextGlyph, $nextGID, ($subtable_offset - $this->GSUB_offset), $Type, $this->GSLuCoverage[$lu][$c]);

									if ($shift) {
										break;
									}
								}
							}
						}
					}
					if ($shift == 0) {
						$shift = 1;
					}
					$ptr += $shift;
				}
			}
		}
	}

	function _applyGSUBsubtableSpecial($lookupID, $subtable, $ptr, $currGlyph, $currGID, $nextGlyph, $nextGID, $subtable_offset, $Type, $LuCoverage)
	{

		// Special case for Indic
		// Check to substitute Halant-Consonant in PREF, BLWF or PSTF
		// i.e. new spec but GSUB tables have Consonant-Halant in Lookups e.g. FreeSerif, which
		// incorrectly just moved old spec tables to new spec. Uniscribe seems to cope with this
		// See also ttffontsuni.php

		$this->seek($subtable_offset);
		$SubstFormat = $this->read_ushort();

		// Subtable contains Consonant - Halant
		// Text string contains Halant ($CurrGlyph) - Consonant ($nextGlyph)
		// Halant has already been matched, and already checked that $nextGID is in Coverage table
		////////////////////////////////////////////////////////////////////////////////
		// Only does: LookupType 4: Ligature Substitution Subtable : n to 1
		////////////////////////////////////////////////////////////////////////////////
		$Coverage = $subtable_offset + $this->read_ushort();
		$NextGlyphPos = $LuCoverage[$nextGID];
		$LigSetCount = $this->read_short();

		$this->skip($NextGlyphPos * 2);
		$LigSet = $subtable_offset + $this->read_short();

		$this->seek($LigSet);
		$LigCount = $this->read_short();
		// LigatureSet i.e. all starting with the same Glyph $nextGlyph [Consonant]
		$LigatureOffset = [];
		for ($g = 0; $g < $LigCount; $g++) {
			$LigatureOffset[$g] = $LigSet + $this->read_ushort();
		}
		for ($g = 0; $g < $LigCount; $g++) {
			// Ligature tables
			$this->seek($LigatureOffset[$g]);
			$LigGlyph = $this->read_ushort();
			$substitute = $this->glyphToChar($LigGlyph);
			$CompCount = $this->read_ushort();

			if ($CompCount != 2) {
				return 0;
			} // Only expecting to work with 2:1 (and no ignore characters in between)


			$gid = $this->read_ushort();
			$checkGlyph = $this->glyphToChar($gid); // Other component/input Glyphs starting at position 2 (arrayindex 1)

			if ($currGID == $checkGlyph) {
				$match = true;
			} else {
				$match = false;
				break;
			}

			$GlyphPos = [];
			$GlyphPos[] = $ptr;
			$GlyphPos[] = $ptr + 1;


			if ($match) {
				$shift = $this->GSUBsubstitute($ptr, $substitute, 4, $GlyphPos); // GlyphPos contains positions to set null
				if ($shift) {
					return 1;
				}
			}
		}
		return 0;
	}

	function _applyGSUBsubtable($lookupID, $subtable, $ptr, $currGlyph, $currGID, $subtable_offset, $Type, $Flag, $MarkFilteringSet, $LuCoverage, $level, $currentTag, $is_old_spec, $tagInt)
	{
		$ignore = $this->_getGCOMignoreString($Flag, $MarkFilteringSet);

		// Lets start
		$this->seek($subtable_offset);
		$SubstFormat = $this->read_ushort();

		////////////////////////////////////////////////////////////////////////////////
		// LookupType 1: Single Substitution Subtable : 1 to 1
		////////////////////////////////////////////////////////////////////////////////
		if ($Type == 1) {
			// Flag = Ignore
			if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) {
				return 0;
			}
			$CoverageOffset = $subtable_offset + $this->read_ushort();
			$GlyphPos = $LuCoverage[$currGID];
			//===========
			// Format 1:
			//===========
			if ($SubstFormat == 1) { // Calculated output glyph indices
				$DeltaGlyphID = $this->read_short();
				$this->seek($CoverageOffset);
				$glyphs = $this->_getCoverageGID();
				$GlyphID = $glyphs[$GlyphPos] + $DeltaGlyphID;
			} //===========
			// Format 2:
			//===========
			elseif ($SubstFormat == 2) { // Specified output glyph indices
				$GlyphCount = $this->read_ushort();
				$this->skip($GlyphPos * 2);
				$GlyphID = $this->read_ushort();
			}

			$substitute = $this->glyphToChar($GlyphID);
			$shift = $this->GSUBsubstitute($ptr, $substitute, $Type);
			if ($this->debugOTL && $shift) {
				$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
			}
			if ($shift) {
				return 1;
			}
			return 0;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 2: Multiple Substitution Subtable : 1 to n
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 2) {
			// Flag = Ignore
			if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) {
				return 0;
			}
			$Coverage = $subtable_offset + $this->read_ushort();
			$GlyphPos = $LuCoverage[$currGID];
			$this->skip(2);
			$this->skip($GlyphPos * 2);
			$Sequences = $subtable_offset + $this->read_short();

			$this->seek($Sequences);
			$GlyphCount = $this->read_short();
			$SubstituteGlyphs = [];
			for ($g = 0; $g < $GlyphCount; $g++) {
				$sgid = $this->read_ushort();
				$SubstituteGlyphs[] = $this->glyphToChar($sgid);
			}

			$shift = $this->GSUBsubstitute($ptr, $SubstituteGlyphs, $Type);
			if ($this->debugOTL && $shift) {
				$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
			}
			if ($shift) {
				return $shift;
			}
			return 0;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 3: Alternate Forms : 1 to 1(n)
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 3) {
			// Flag = Ignore
			if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) {
				return 0;
			}
			$Coverage = $subtable_offset + $this->read_ushort();
			$AlternateSetCount = $this->read_short();
			///////////////////////////////////////////////////////////////////////////////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			// Need to set alternate IF set by CSS3 font-feature for a tag
			// i.e. if this is 'salt' alternate may be set to 2
			// default value will be $alt=1 ( === index of 0 in list of alternates)
			$alt = 1; // $alt=1 points to Alternative[0]
			if ($tagInt > 1) {
				$alt = $tagInt;
			}
			///////////////////////////////////////////////////////////////////////////////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			if ($alt == 0) {
				return 0;
			} // If specified alternate not present, cancel [ or could default $alt = 1 ?]

			$GlyphPos = $LuCoverage[$currGID];
			$this->skip($GlyphPos * 2);

			$AlternateSets = $subtable_offset + $this->read_short();
			$this->seek($AlternateSets);

			$AlternateGlyphCount = $this->read_short();
			if ($alt > $AlternateGlyphCount) {
				return 0;
			} // If specified alternate not present, cancel [ or could default $alt = 1 ?]

			$this->skip(($alt - 1) * 2);
			$GlyphID = $this->read_ushort();

			$substitute = $this->glyphToChar($GlyphID);
			$shift = $this->GSUBsubstitute($ptr, $substitute, $Type);
			if ($this->debugOTL && $shift) {
				$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
			}
			if ($shift) {
				return 1;
			}
			return 0;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 4: Ligature Substitution Subtable : n to 1
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 4) {
			// Flag = Ignore
			if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) {
				return 0;
			}
			$Coverage = $subtable_offset + $this->read_ushort();
			$FirstGlyphPos = $LuCoverage[$currGID];

			$LigSetCount = $this->read_short();

			$this->skip($FirstGlyphPos * 2);
			$LigSet = $subtable_offset + $this->read_short();

			$this->seek($LigSet);
			$LigCount = $this->read_short();
			// LigatureSet i.e. all starting with the same first Glyph $currGlyph
			$LigatureOffset = [];
			for ($g = 0; $g < $LigCount; $g++) {
				$LigatureOffset[$g] = $LigSet + $this->read_ushort();
			}
			for ($g = 0; $g < $LigCount; $g++) {
				// Ligature tables
				$this->seek($LigatureOffset[$g]);
				$LigGlyph = $this->read_ushort(); // Output Ligature GlyphID
				$substitute = $this->glyphToChar($LigGlyph);
				$CompCount = $this->read_ushort();

				$spos = $ptr;
				$match = true;
				$GlyphPos = [];
				$GlyphPos[] = $spos;
				for ($l = 1; $l < $CompCount; $l++) {
					$gid = $this->read_ushort();
					$checkGlyph = $this->glyphToChar($gid); // Other component/input Glyphs starting at position 2 (arrayindex 1)

					$spos++;
					//while $this->OTLdata[$spos]['uni'] is an "ignore" =>  spos++
					while (isset($this->OTLdata[$spos]) && strpos($ignore, $this->OTLdata[$spos]['hex']) !== false) {
						$spos++;
					}

					if (isset($this->OTLdata[$spos]) && $this->OTLdata[$spos]['uni'] == $checkGlyph) {
						$GlyphPos[] = $spos;
					} else {
						$match = false;
						break;
					}
				}


				if ($match) {
					$shift = $this->GSUBsubstitute($ptr, $substitute, $Type, $GlyphPos); // GlyphPos contains positions to set null
					if ($this->debugOTL && $shift) {
						$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
					}
					if ($shift) {
						return ($spos - $ptr + 1 - ($CompCount - 1));
					}
				}
			}
			return 0;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 5: Contextual Substitution Subtable
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 5) {
			//===========
			// Format 1: Simple Context Glyph Substitution
			//===========
			if ($SubstFormat == 1) {
				$CoverageTableOffset = $subtable_offset + $this->read_ushort();
				$SubRuleSetCount = $this->read_ushort();
				$SubRuleSetOffset = [];
				for ($b = 0; $b < $SubRuleSetCount; $b++) {
					$offset = $this->read_ushort();
					if ($offset == 0x0000) {
						$SubRuleSetOffset[] = $offset;
					} else {
						$SubRuleSetOffset[] = $subtable_offset + $offset;
					}
				}

				// SubRuleSet tables: All contexts beginning with the same glyph
				// Select the SubRuleSet required using the position of the glyph in the coverage table
				$GlyphPos = $LuCoverage[$currGID];
				if ($SubRuleSetOffset[$GlyphPos] > 0) {
					$this->seek($SubRuleSetOffset[$GlyphPos]);
					$SubRuleCnt = $this->read_ushort();
					$SubRule = [];
					for ($b = 0; $b < $SubRuleCnt; $b++) {
						$SubRule[$b] = $SubRuleSetOffset[$GlyphPos] + $this->read_ushort();
					}
					for ($b = 0; $b < $SubRuleCnt; $b++) {  // EACH RULE
						$this->seek($SubRule[$b]);
						$InputGlyphCount = $this->read_ushort();
						$SubstCount = $this->read_ushort();

						$Backtrack = [];
						$Lookahead = [];
						$Input = [];
						$Input[0] = $this->OTLdata[$ptr]['uni'];
						for ($r = 1; $r < $InputGlyphCount; $r++) {
							$gid = $this->read_ushort();
							$Input[$r] = $this->glyphToChar($gid);
						}
						$matched = $this->checkContextMatch($Input, $Backtrack, $Lookahead, $ignore, $ptr);
						if ($matched) {
							if ($this->debugOTL) {
								$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
							}
							for ($p = 0; $p < $SubstCount; $p++) { // EACH LOOKUP
								$SequenceIndex[$p] = $this->read_ushort();
								$LookupListIndex[$p] = $this->read_ushort();
							}

							for ($p = 0; $p < $SubstCount; $p++) {
								// Apply  $LookupListIndex  at   $SequenceIndex
								if ($SequenceIndex[$p] >= $InputGlyphCount) {
									continue;
								}
								$lu = $LookupListIndex[$p];
								$luType = $this->GSUBLookups[$lu]['Type'];
								$luFlag = $this->GSUBLookups[$lu]['Flag'];
								$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

								$luptr = $matched[$SequenceIndex[$p]];
								$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
								$lucurrGID = $this->OTLdata[$luptr]['uni'];

								foreach ($this->GSUBLookups[$lu]['Subtables'] as $luc => $lusubtable_offset) {
									$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
									if ($shift) {
										break;
									}
								}
							}

							if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
								return $shift;
							} /* OTL_FIX_3 */
							else {
								return $InputGlyphCount; // should be + matched ignores in Input Sequence
							}
						}
					}
				}
				return 0;
			} //===========
			// Format 2:
			//===========
			// Format 2: Class-based Context Glyph Substitution
			elseif ($SubstFormat == 2) {
				$CoverageTableOffset = $subtable_offset + $this->read_ushort();
				$InputClassDefOffset = $subtable_offset + $this->read_ushort();
				$SubClassSetCnt = $this->read_ushort();
				$SubClassSetOffset = [];
				for ($b = 0; $b < $SubClassSetCnt; $b++) {
					$offset = $this->read_ushort();
					if ($offset == 0x0000) {
						$SubClassSetOffset[] = $offset;
					} else {
						$SubClassSetOffset[] = $subtable_offset + $offset;
					}
				}

				$InputClasses = $this->_getClasses($InputClassDefOffset);

				for ($s = 0; $s < $SubClassSetCnt; $s++) { // $SubClassSet is ordered by input class-may be NULL
					// Select $SubClassSet if currGlyph is in First Input Class
					if ($SubClassSetOffset[$s] > 0 && isset($InputClasses[$s][$currGID])) {
						$this->seek($SubClassSetOffset[$s]);
						$SubClassRuleCnt = $this->read_ushort();
						$SubClassRule = [];
						for ($b = 0; $b < $SubClassRuleCnt; $b++) {
							$SubClassRule[$b] = $SubClassSetOffset[$s] + $this->read_ushort();
						}

						for ($b = 0; $b < $SubClassRuleCnt; $b++) {  // EACH RULE
							$this->seek($SubClassRule[$b]);
							$InputGlyphCount = $this->read_ushort();
							$SubstCount = $this->read_ushort();
							$Input = [];
							for ($r = 1; $r < $InputGlyphCount; $r++) {
								$Input[$r] = $this->read_ushort();
							}

							$inputClass = $s;

							$inputGlyphs = [];
							$inputGlyphs[0] = $InputClasses[$inputClass];

							if ($InputGlyphCount > 1) {
								//  NB starts at 1
								for ($gcl = 1; $gcl < $InputGlyphCount; $gcl++) {
									$classindex = $Input[$gcl];
									if (isset($InputClasses[$classindex])) {
										$inputGlyphs[$gcl] = $InputClasses[$classindex];
									} else {
										$inputGlyphs[$gcl] = '';
									}
								}
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$class0excl = [];
							for ($gc = 1; $gc <= count($InputClasses); $gc++) {
								if (is_array($InputClasses[$gc])) {
									$class0excl = $class0excl + $InputClasses[$gc];
								}
							}

							$backtrackGlyphs = [];
							$lookaheadGlyphs = [];

							$matched = $this->checkContextMatchMultipleUni($inputGlyphs, $backtrackGlyphs, $lookaheadGlyphs, $ignore, $ptr, $class0excl);
							if ($matched) {
								if ($this->debugOTL) {
									$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
								}
								for ($p = 0; $p < $SubstCount; $p++) { // EACH LOOKUP
									$SequenceIndex[$p] = $this->read_ushort();
									$LookupListIndex[$p] = $this->read_ushort();
								}

								for ($p = 0; $p < $SubstCount; $p++) {
									// Apply  $LookupListIndex  at   $SequenceIndex
									if ($SequenceIndex[$p] >= $InputGlyphCount) {
										continue;
									}
									$lu = $LookupListIndex[$p];
									$luType = $this->GSUBLookups[$lu]['Type'];
									$luFlag = $this->GSUBLookups[$lu]['Flag'];
									$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

									$luptr = $matched[$SequenceIndex[$p]];
									$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
									$lucurrGID = $this->OTLdata[$luptr]['uni'];

									foreach ($this->GSUBLookups[$lu]['Subtables'] as $luc => $lusubtable_offset) {
										$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
										if ($shift) {
											break;
										}
									}
								}

								if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
									return $shift;
								} /* OTL_FIX_3 */
								else {
									return $InputGlyphCount; // should be + matched ignores in Input Sequence
								}
							}
						}
					}
				}

				return 0;
			} //===========
			// Format 3:
			//===========
			// Format 3: Coverage-based Context Glyph Substitution
			elseif ($SubstFormat == 3) {
				throw new \Mpdf\MpdfException("GSUB Lookup Type " . $Type . " Format " . $SubstFormat . " not TESTED YET.");
			}
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 6: Chaining Contextual Substitution Subtable
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 6) {
			//===========
			// Format 1:
			//===========
			// Format 1: Simple Chaining Context Glyph Substitution
			if ($SubstFormat == 1) {
				$Coverage = $subtable_offset + $this->read_ushort();
				$GlyphPos = $LuCoverage[$currGID];
				$ChainSubRuleSetCount = $this->read_ushort();
				// All of the ChainSubRule tables defining contexts that begin with the same first glyph are grouped together and defined in a ChainSubRuleSet table
				$this->skip($GlyphPos * 2);
				$ChainSubRuleSet = $subtable_offset + $this->read_ushort();
				$this->seek($ChainSubRuleSet);
				$ChainSubRuleCount = $this->read_ushort();

				for ($s = 0; $s < $ChainSubRuleCount; $s++) {
					$ChainSubRule[$s] = $ChainSubRuleSet + $this->read_ushort();
				}

				for ($s = 0; $s < $ChainSubRuleCount; $s++) {
					$this->seek($ChainSubRule[$s]);

					$BacktrackGlyphCount = $this->read_ushort();
					$Backtrack = [];
					for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
						$gid = $this->read_ushort();
						$Backtrack[] = $this->glyphToChar($gid);
					}
					$Input = [];
					$Input[0] = $this->OTLdata[$ptr]['uni'];
					$InputGlyphCount = $this->read_ushort();
					for ($b = 1; $b < $InputGlyphCount; $b++) {
						$gid = $this->read_ushort();
						$Input[$b] = $this->glyphToChar($gid);
					}
					$LookaheadGlyphCount = $this->read_ushort();
					$Lookahead = [];
					for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
						$gid = $this->read_ushort();
						$Lookahead[] = $this->glyphToChar($gid);
					}

					$matched = $this->checkContextMatch($Input, $Backtrack, $Lookahead, $ignore, $ptr);
					if ($matched) {
						if ($this->debugOTL) {
							$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
						}
						$SubstCount = $this->read_ushort();
						for ($p = 0; $p < $SubstCount; $p++) {
							// SubstLookupRecord
							$SubstLookupRecord[$p]['SequenceIndex'] = $this->read_ushort();
							$SubstLookupRecord[$p]['LookupListIndex'] = $this->read_ushort();
						}
						for ($p = 0; $p < $SubstCount; $p++) {
							// Apply  $SubstLookupRecord[$p]['LookupListIndex']  at   $SubstLookupRecord[$p]['SequenceIndex']
							if ($SubstLookupRecord[$p]['SequenceIndex'] >= $InputGlyphCount) {
								continue;
							}
							$lu = $SubstLookupRecord[$p]['LookupListIndex'];
							$luType = $this->GSUBLookups[$lu]['Type'];
							$luFlag = $this->GSUBLookups[$lu]['Flag'];
							$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

							$luptr = $matched[$SubstLookupRecord[$p]['SequenceIndex']];
							$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
							$lucurrGID = $this->OTLdata[$luptr]['uni'];

							foreach ($this->GSUBLookups[$lu]['Subtables'] as $luc => $lusubtable_offset) {
								$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
								if ($shift) {
									break;
								}
							}
						}
						if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
							return $shift;
						} /* OTL_FIX_3 */
						else {
							return $InputGlyphCount; // should be + matched ignores in Input Sequence
						}
					}
				}
				return 0;
			} //===========
			// Format 2:
			//===========
			// Format 2: Class-based Chaining Context Glyph Substitution  p257
			elseif ($SubstFormat == 2) {
				// NB Format 2 specifies fixed class assignments (identical for each position in the backtrack, input, or lookahead sequence) and exclusive classes (a glyph cannot be in more than one class at a time)

				$CoverageTableOffset = $subtable_offset + $this->read_ushort();
				$BacktrackClassDefOffset = $subtable_offset + $this->read_ushort();
				$InputClassDefOffset = $subtable_offset + $this->read_ushort();
				$LookaheadClassDefOffset = $subtable_offset + $this->read_ushort();
				$ChainSubClassSetCnt = $this->read_ushort();
				$ChainSubClassSetOffset = [];
				for ($b = 0; $b < $ChainSubClassSetCnt; $b++) {
					$offset = $this->read_ushort();
					if ($offset == 0x0000) {
						$ChainSubClassSetOffset[] = $offset;
					} else {
						$ChainSubClassSetOffset[] = $subtable_offset + $offset;
					}
				}

				$BacktrackClasses = $this->_getClasses($BacktrackClassDefOffset);
				$InputClasses = $this->_getClasses($InputClassDefOffset);
				$LookaheadClasses = $this->_getClasses($LookaheadClassDefOffset);

				for ($s = 0; $s < $ChainSubClassSetCnt; $s++) { // $ChainSubClassSet is ordered by input class-may be NULL
					// Select $ChainSubClassSet if currGlyph is in First Input Class
					if ($ChainSubClassSetOffset[$s] > 0 && isset($InputClasses[$s][$currGID])) {
						$this->seek($ChainSubClassSetOffset[$s]);
						$ChainSubClassRuleCnt = $this->read_ushort();
						$ChainSubClassRule = [];
						for ($b = 0; $b < $ChainSubClassRuleCnt; $b++) {
							$ChainSubClassRule[$b] = $ChainSubClassSetOffset[$s] + $this->read_ushort();
						}

						for ($b = 0; $b < $ChainSubClassRuleCnt; $b++) {  // EACH RULE
							$this->seek($ChainSubClassRule[$b]);
							$BacktrackGlyphCount = $this->read_ushort();
							for ($r = 0; $r < $BacktrackGlyphCount; $r++) {
								$Backtrack[$r] = $this->read_ushort();
							}
							$InputGlyphCount = $this->read_ushort();
							for ($r = 1; $r < $InputGlyphCount; $r++) {
								$Input[$r] = $this->read_ushort();
							}
							$LookaheadGlyphCount = $this->read_ushort();
							for ($r = 0; $r < $LookaheadGlyphCount; $r++) {
								$Lookahead[$r] = $this->read_ushort();
							}


							// These contain classes of glyphs as arrays
							// $InputClasses[(class)] e.g. 0x02E6,0x02E7,0x02E8
							// $LookaheadClasses[(class)]
							// $BacktrackClasses[(class)]
							// These contain arrays of classIndexes
							// [Backtrack] [Lookahead] and [Input] (Input is from the second position only)


							$inputClass = $s; //???

							$inputGlyphs = [];
							$inputGlyphs[0] = $InputClasses[$inputClass];

							if ($InputGlyphCount > 1) {
								//  NB starts at 1
								for ($gcl = 1; $gcl < $InputGlyphCount; $gcl++) {
									$classindex = $Input[$gcl];
									if (isset($InputClasses[$classindex])) {
										$inputGlyphs[$gcl] = $InputClasses[$classindex];
									} else {
										$inputGlyphs[$gcl] = '';
									}
								}
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$class0excl = [];
							for ($gc = 1; $gc <= count($InputClasses); $gc++) {
								if (isset($InputClasses[$gc])) {
									$class0excl = $class0excl + $InputClasses[$gc];
								}
							}

							if ($BacktrackGlyphCount) {
								for ($gcl = 0; $gcl < $BacktrackGlyphCount; $gcl++) {
									$classindex = $Backtrack[$gcl];
									if (isset($BacktrackClasses[$classindex])) {
										$backtrackGlyphs[$gcl] = $BacktrackClasses[$classindex];
									} else {
										$backtrackGlyphs[$gcl] = '';
									}
								}
							} else {
								$backtrackGlyphs = [];
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$bclass0excl = [];
							for ($gc = 1; $gc <= count($BacktrackClasses); $gc++) {
								if (isset($BacktrackClasses[$gc])) {
									$bclass0excl = $bclass0excl + $BacktrackClasses[$gc];
								}
							}


							if ($LookaheadGlyphCount) {
								for ($gcl = 0; $gcl < $LookaheadGlyphCount; $gcl++) {
									$classindex = $Lookahead[$gcl];
									if (isset($LookaheadClasses[$classindex])) {
										$lookaheadGlyphs[$gcl] = $LookaheadClasses[$classindex];
									} else {
										$lookaheadGlyphs[$gcl] = '';
									}
								}
							} else {
								$lookaheadGlyphs = [];
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$lclass0excl = [];
							for ($gc = 1; $gc <= count($LookaheadClasses); $gc++) {
								if (isset($LookaheadClasses[$gc])) {
									$lclass0excl = $lclass0excl + $LookaheadClasses[$gc];
								}
							}


							$matched = $this->checkContextMatchMultipleUni($inputGlyphs, $backtrackGlyphs, $lookaheadGlyphs, $ignore, $ptr, $class0excl, $bclass0excl, $lclass0excl);
							if ($matched) {
								if ($this->debugOTL) {
									$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
								}
								$SubstCount = $this->read_ushort();
								for ($p = 0; $p < $SubstCount; $p++) { // EACH LOOKUP
									$SequenceIndex[$p] = $this->read_ushort();
									$LookupListIndex[$p] = $this->read_ushort();
								}

								for ($p = 0; $p < $SubstCount; $p++) {
									// Apply  $LookupListIndex  at   $SequenceIndex
									if ($SequenceIndex[$p] >= $InputGlyphCount) {
										continue;
									}
									$lu = $LookupListIndex[$p];
									$luType = $this->GSUBLookups[$lu]['Type'];
									$luFlag = $this->GSUBLookups[$lu]['Flag'];
									$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

									$luptr = $matched[$SequenceIndex[$p]];
									$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
									$lucurrGID = $this->OTLdata[$luptr]['uni'];

									foreach ($this->GSUBLookups[$lu]['Subtables'] as $luc => $lusubtable_offset) {
										$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
										if ($shift) {
											break;
										}
									}
								}

								if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
									return $shift;
								} /* OTL_FIX_3 */
								else {
									return $InputGlyphCount; // should be + matched ignores in Input Sequence
								}
							}
						}
					}
				}

				return 0;
			} //===========
			// Format 3:
			//===========
			// Format 3: Coverage-based Chaining Context Glyph Substitution  p259
			elseif ($SubstFormat == 3) {
				$BacktrackGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
					$CoverageBacktrackOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$InputGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $InputGlyphCount; $b++) {
					$CoverageInputOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$LookaheadGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
					$CoverageLookaheadOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$SubstCount = $this->read_ushort();
				$save_pos = $this->_pos; // Save the point just after PosCount

				$CoverageBacktrackGlyphs = [];
				for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
					$this->seek($CoverageBacktrackOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageBacktrackGlyphs[$b] = implode("|", $glyphs);
				}
				$CoverageInputGlyphs = [];
				for ($b = 0; $b < $InputGlyphCount; $b++) {
					$this->seek($CoverageInputOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageInputGlyphs[$b] = implode("|", $glyphs);
				}
				$CoverageLookaheadGlyphs = [];
				for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
					$this->seek($CoverageLookaheadOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageLookaheadGlyphs[$b] = implode("|", $glyphs);
				}

				$matched = $this->checkContextMatchMultiple($CoverageInputGlyphs, $CoverageBacktrackGlyphs, $CoverageLookaheadGlyphs, $ignore, $ptr);
				if ($matched) {
					if ($this->debugOTL) {
						$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
					}

					$this->seek($save_pos); // Return to just after PosCount
					for ($p = 0; $p < $SubstCount; $p++) {
						// SubstLookupRecord
						$SubstLookupRecord[$p]['SequenceIndex'] = $this->read_ushort();
						$SubstLookupRecord[$p]['LookupListIndex'] = $this->read_ushort();
					}
					for ($p = 0; $p < $SubstCount; $p++) {
						// Apply  $SubstLookupRecord[$p]['LookupListIndex']  at   $SubstLookupRecord[$p]['SequenceIndex']
						if ($SubstLookupRecord[$p]['SequenceIndex'] >= $InputGlyphCount) {
							continue;
						}
						$lu = $SubstLookupRecord[$p]['LookupListIndex'];
						$luType = $this->GSUBLookups[$lu]['Type'];
						$luFlag = $this->GSUBLookups[$lu]['Flag'];
						$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

						$luptr = $matched[$SubstLookupRecord[$p]['SequenceIndex']];
						$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
						$lucurrGID = $this->OTLdata[$luptr]['uni'];

						foreach ($this->GSUBLookups[$lu]['Subtables'] as $luc => $lusubtable_offset) {
							$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
							if ($shift) {
								break;
							}
						}
					}
					if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
						return (isset($shift) ? $shift : 0);
					} /* OTL_FIX_3 */
					else {
						return $InputGlyphCount; // should be + matched ignores in Input Sequence
					}
				}

				return 0;
			}
		} else {
			throw new \Mpdf\MpdfException("GSUB Lookup Type " . $Type . " not supported.");
		}
	}

	function _updateLigatureMarks($pos, $n)
	{
		if ($n > 0) {
			// Update position of Ligatures and associated Marks
			// Foreach lig/assocMarks
			// Any position lpos or mpos > $pos + count($substitute)
			//  $this->assocMarks = array();    // assocMarks[$pos mpos] => array(compID, ligPos)
			//  $this->assocLigs = array(); // Ligatures[$pos lpos] => nc
			for ($p = count($this->OTLdata) - 1; $p >= ($pos + $n); $p--) {
				if (isset($this->assocLigs[$p])) {
					$tmp = $this->assocLigs[$p];
					unset($this->assocLigs[$p]);
					$this->assocLigs[($p + $n)] = $tmp;
				}
			}
			for ($p = count($this->OTLdata) - 1; $p >= 0; $p--) {
				if (isset($this->assocMarks[$p])) {
					if ($this->assocMarks[$p]['ligPos'] >= ($pos + $n)) {
						$this->assocMarks[$p]['ligPos'] += $n;
					}
					if ($p >= ($pos + $n)) {
						$tmp = $this->assocMarks[$p];
						unset($this->assocMarks[$p]);
						$this->assocMarks[($p + $n)] = $tmp;
					}
				}
			}
		} elseif ($n < 1) { // glyphs removed
			$nrem = -$n;
			// Update position of pre-existing Ligatures and associated Marks
			for ($p = ($pos + 1); $p < count($this->OTLdata); $p++) {
				if (isset($this->assocLigs[$p])) {
					$tmp = $this->assocLigs[$p];
					unset($this->assocLigs[$p]);
					$this->assocLigs[($p - $nrem)] = $tmp;
				}
			}
			for ($p = 0; $p < count($this->OTLdata); $p++) {
				if (isset($this->assocMarks[$p])) {
					if ($this->assocMarks[$p]['ligPos'] >= ($pos)) {
						$this->assocMarks[$p]['ligPos'] -= $nrem;
					}
					if ($p > $pos) {
						$tmp = $this->assocMarks[$p];
						unset($this->assocMarks[$p]);
						$this->assocMarks[($p - $nrem)] = $tmp;
					}
				}
			}
		}
	}

	function GSUBsubstitute($pos, $substitute, $Type, $GlyphPos = null)
	{

		// LookupType 1: Simple Substitution Subtable : 1 to 1
		// LookupType 3: Alternate Forms : 1 to 1(n)
		if ($Type == 1 || $Type == 3) {
			$this->OTLdata[$pos]['uni'] = $substitute;
			$this->OTLdata[$pos]['hex'] = $this->unicode_hex($substitute);
			return 1;
		} // LookupType 2: Multiple Substitution Subtable : 1 to n
		elseif ($Type == 2) {
			for ($i = 0; $i < count($substitute); $i++) {
				$uni = $substitute[$i];
				$newOTLdata[$i] = [];
				$newOTLdata[$i]['uni'] = $uni;
				$newOTLdata[$i]['hex'] = $this->unicode_hex($uni);


				// Get types of new inserted chars - or replicate type of char being replaced
				//  $bt = Ucdn::get_bidi_class($uni);
				//  if (!$bt) {
				$bt = $this->OTLdata[$pos]['bidi_type'];
				//  }

				if (strpos($this->GlyphClassMarks, $newOTLdata[$i]['hex']) !== false) {
					$gp = 'M';
				} elseif ($uni == 32) {
					$gp = 'S';
				} else {
					$gp = 'C';
				}

				// Need to update matra_type ??? of new glyphs inserted ???????????????????????????????????????

				$newOTLdata[$i]['bidi_type'] = $bt;
				$newOTLdata[$i]['group'] = $gp;

				// Need to update details of new glyphs inserted
				$newOTLdata[$i]['general_category'] = $this->OTLdata[$pos]['general_category'];

				if ($this->shaper == 'I' || $this->shaper == 'K' || $this->shaper == 'S') {
					$newOTLdata[$i]['indic_category'] = $this->OTLdata[$pos]['indic_category'];
					$newOTLdata[$i]['indic_position'] = $this->OTLdata[$pos]['indic_position'];
				} elseif ($this->shaper == 'M') {
					$newOTLdata[$i]['myanmar_category'] = $this->OTLdata[$pos]['myanmar_category'];
					$newOTLdata[$i]['myanmar_position'] = $this->OTLdata[$pos]['myanmar_position'];
				}
				if (isset($this->OTLdata[$pos]['mask'])) {
					$newOTLdata[$i]['mask'] = $this->OTLdata[$pos]['mask'];
				}
				if (isset($this->OTLdata[$pos]['syllable'])) {
					$newOTLdata[$i]['syllable'] = $this->OTLdata[$pos]['syllable'];
				}
			}
			if ($this->shaper == 'K' || $this->shaper == 'T' || $this->shaper == 'L') {
				if ($this->OTLdata[$pos]['wordend']) {
					$newOTLdata[count($substitute) - 1]['wordend'] = true;
				}
			}

			array_splice($this->OTLdata, $pos, 1, $newOTLdata); // Replace 1 with n
			// Update position of Ligatures and associated Marks
			// count($substitute)-1  is the number of glyphs added
			$nadd = count($substitute) - 1;
			$this->_updateLigatureMarks($pos, $nadd);
			return count($substitute);
		} // LookupType 4: Ligature Substitution Subtable : n to 1
		elseif ($Type == 4) {
			// Create Ligatures and associated Marks
			$firstGlyph = $this->OTLdata[$pos]['hex'];

			// If all components of the ligature are marks (and in the same syllable), we call this a mark ligature.
			$contains_marks = false;
			$contains_nonmarks = false;
			if (isset($this->OTLdata[$pos]['syllable'])) {
				$current_syllable = $this->OTLdata[$pos]['syllable'];
			} else {
				$current_syllable = 0;
			}
			for ($i = 0; $i < count($GlyphPos); $i++) {
				// If subsequent components are not Marks as well - don't ligate
				$unistr = $this->OTLdata[$GlyphPos[$i]]['hex'];
				if ($this->restrictToSyllable && isset($this->OTLdata[$GlyphPos[$i]]['syllable']) && $this->OTLdata[$GlyphPos[$i]]['syllable'] != $current_syllable) {
					return 0;
				}
				if (strpos($this->GlyphClassMarks, $unistr) !== false) {
					$contains_marks = true;
				} else {
					$contains_nonmarks = true;
				}
			}
			if ($contains_marks && !$contains_nonmarks) {
				// Mark Ligature (all components are Marks)
				$firstMarkAssoc = '';
				if (isset($this->assocMarks[$pos])) {
					$firstMarkAssoc = $this->assocMarks[$pos];
				}
				// If all components of the ligature are marks, we call this a mark ligature.
				for ($i = 1; $i < count($GlyphPos); $i++) {
					// If subsequent components are not Marks as well - don't ligate
					//      $unistr = $this->OTLdata[$GlyphPos[$i]]['hex'];
					//      if (strpos($this->GlyphClassMarks, $unistr )===false) { return; }

					$nextMarkAssoc = '';
					if (isset($this->assocMarks[$GlyphPos[$i]])) {
						$nextMarkAssoc = $this->assocMarks[$GlyphPos[$i]];
					}
					// If first component was attached to a previous ligature component,
					// all subsequent components should be attached to the same ligature
					// component, otherwise we shouldn't ligate them.
					// If first component was NOT attached to a previous ligature component,
					// all subsequent components should also NOT be attached to any ligature component,
					if ($firstMarkAssoc != $nextMarkAssoc) {
						// unless they are attached to the first component itself!
						//          if (!is_array($nextMarkAssoc) || $nextMarkAssoc['ligPos']!= $pos) { return; }
						// Update/Edit - In test with myanmartext font
						// &#x1004;&#x103a;&#x1039;&#x1000;&#x1039;&#x1000;&#x103b;&#x103c;&#x103d;&#x1031;&#x102d;
						// => Lookup 17  E003 E066B E05A 102D
						// E003 and 102D should form a mark ligature, but 102D is already associated with (non-mark) ligature E05A
						// So instead of disallowing the mark ligature to form, just dissociate...
						if (!is_array($nextMarkAssoc) || $nextMarkAssoc['ligPos'] != $pos) {
							unset($this->assocMarks[$GlyphPos[$i]]);
						}
					}
				}

				/*
				 * - If it *is* a mark ligature, we don't allocate a new ligature id, and leave
				 *   the ligature to keep its old ligature id.  This will allow it to attach to
				 *   a base ligature in GPOS.  Eg. if the sequence is: LAM,LAM,SHADDA,FATHA,HEH,
				 *   and LAM,LAM,HEH form a ligature, they will leave SHADDA and FATHA wit a
				 *   ligature id and component value of 2.  Then if SHADDA,FATHA form a ligature
				 *   later, we don't want them to lose their ligature id/component, otherwise
				 *   GPOS will fail to correctly position the mark ligature on top of the
				 *   LAM,LAM,HEH ligature.
				 */
				// So if is_array($firstMarkAssoc) - the new (Mark) ligature should keep this association

				$lastPos = $GlyphPos[(count($GlyphPos) - 1)];
			} else {
				/*
				 * - Ligatures cannot be formed across glyphs attached to different components
				 *   of previous ligatures.  Eg. the sequence is LAM,SHADDA,LAM,FATHA,HEH, and
				 *   LAM,LAM,HEH form a ligature, leaving SHADDA,FATHA next to eachother.
				 *   However, it would be wrong to ligate that SHADDA,FATHA sequence.
				 *   There is an exception to this: If a ligature tries ligating with marks that
				 *   belong to it itself, go ahead, assuming that the font designer knows what
				 *   they are doing (otherwise it can break Indic stuff when a matra wants to
				 *   ligate with a conjunct...)
				 */

				/*
				 * - If a ligature is formed of components that some of which are also ligatures
				 *   themselves, and those ligature components had marks attached to *their*
				 *   components, we have to attach the marks to the new ligature component
				 *   positions!  Now *that*'s tricky!  And these marks may be following the
				 *   last component of the whole sequence, so we should loop forward looking
				 *   for them and update them.
				 *
				 *   Eg. the sequence is LAM,LAM,SHADDA,FATHA,HEH, and the font first forms a
				 *   'calt' ligature of LAM,HEH, leaving the SHADDA and FATHA with a ligature
				 *   id and component == 1.  Now, during 'liga', the LAM and the LAM-HEH ligature
				 *   form a LAM-LAM-HEH ligature.  We need to reassign the SHADDA and FATHA to
				 *   the new ligature with a component value of 2.
				 *
				 *   This in fact happened to a font...  See:
				 *   https://bugzilla.gnome.org/show_bug.cgi?id=437633
				 */

				$currComp = 0;
				for ($i = 0; $i < count($GlyphPos); $i++) {
					if ($i > 0 && isset($this->assocLigs[$GlyphPos[$i]])) { // One of the other components is already a ligature
						$nc = $this->assocLigs[$GlyphPos[$i]];
					} else {
						$nc = 1;
					}
					// While next char to right is a mark (but not the next matched glyph)
					// ?? + also include a Mark Ligature here
					$ic = 1;
					while ((($i == count($GlyphPos) - 1) || (isset($GlyphPos[$i + 1]) && ($GlyphPos[$i] + $ic) < $GlyphPos[$i + 1])) && isset($this->OTLdata[($GlyphPos[$i] + $ic)]) && strpos($this->GlyphClassMarks, $this->OTLdata[($GlyphPos[$i] + $ic)]['hex']) !== false) {
						$newComp = $currComp;
						if (isset($this->assocMarks[$GlyphPos[$i] + $ic])) { // One of the inbetween Marks is already associated with a Lig
							// OK as long as it is associated with the current Lig
							//      if ($this->assocMarks[($GlyphPos[$i]+$ic)]['ligPos'] != ($GlyphPos[$i]+$ic)) { die("Problem #1"); }
							$newComp += $this->assocMarks[($GlyphPos[$i] + $ic)]['compID'];
						}
						$this->assocMarks[($GlyphPos[$i] + $ic)] = ['compID' => $newComp, 'ligPos' => $pos];
						$ic++;
					}
					$currComp += $nc;
				}
				$lastPos = $GlyphPos[(count($GlyphPos) - 1)] + $ic - 1;
				$this->assocLigs[$pos] = $currComp; // Number of components in new Ligature
			}

			// Now remove the unwanted glyphs and associated metadata
			$newOTLdata[0] = [];

			// Get types of new inserted chars - or replicate type of char being replaced
			//  $bt = Ucdn::get_bidi_class($substitute);
			//  if (!$bt) {
			$bt = $this->OTLdata[$pos]['bidi_type'];
			//  }

			if (strpos($this->GlyphClassMarks, $this->unicode_hex($substitute)) !== false) {
				$gp = 'M';
			} elseif ($substitute == 32) {
				$gp = 'S';
			} else {
				$gp = 'C';
			}

			// Need to update details of new glyphs inserted
			$newOTLdata[0]['general_category'] = $this->OTLdata[$pos]['general_category'];

			$newOTLdata[0]['bidi_type'] = $bt;
			$newOTLdata[0]['group'] = $gp;

			// KASHIDA: If forming a ligature when the last component was identified as a kashida point (final form)
			// If previous/first component of ligature is a medial form, then keep this as a kashida point
			// TEST (Arabic Typesetting) &#x64a;&#x64e;&#x646;&#x62a;&#x64f;&#x645;
			$ka = 0;
			if (isset($this->OTLdata[$GlyphPos[(count($GlyphPos) - 1)]]['GPOSinfo']['kashida'])) {
				$ka = $this->OTLdata[$GlyphPos[(count($GlyphPos) - 1)]]['GPOSinfo']['kashida'];
			}
			if ($ka == 1 && isset($this->OTLdata[$pos]['form']) && $this->OTLdata[$pos]['form'] == 3) {
				$newOTLdata[0]['GPOSinfo']['kashida'] = $ka;
			}

			$newOTLdata[0]['uni'] = $substitute;
			$newOTLdata[0]['hex'] = $this->unicode_hex($substitute);

			if ($this->shaper == 'I' || $this->shaper == 'K' || $this->shaper == 'S') {
				$newOTLdata[0]['indic_category'] = $this->OTLdata[$pos]['indic_category'];
				$newOTLdata[0]['indic_position'] = $this->OTLdata[$pos]['indic_position'];
			} elseif ($this->shaper == 'M') {
				$newOTLdata[0]['myanmar_category'] = $this->OTLdata[$pos]['myanmar_category'];
				$newOTLdata[0]['myanmar_position'] = $this->OTLdata[$pos]['myanmar_position'];
			}
			if (isset($this->OTLdata[$pos]['mask'])) {
				$newOTLdata[0]['mask'] = $this->OTLdata[$pos]['mask'];
			}
			if (isset($this->OTLdata[$pos]['syllable'])) {
				$newOTLdata[0]['syllable'] = $this->OTLdata[$pos]['syllable'];
			}

			$newOTLdata[0]['is_ligature'] = true;


			array_splice($this->OTLdata, $pos, 1, $newOTLdata);

			// GlyphPos contains array of arr_pos to set null - not necessarily contiguous
			// +- Remove any assocMarks or assocLigs from the main components (the ones that are deleted)
			for ($i = count($GlyphPos) - 1; $i > 0; $i--) {
				$gpos = $GlyphPos[$i];
				array_splice($this->OTLdata, $gpos, 1);
				unset($this->assocLigs[$gpos]);
				unset($this->assocMarks[$gpos]);
			}
			//  $this->assocLigs = array(); // Ligatures[$posarr lpos] => nc
			//  $this->assocMarks = array();    // assocMarks[$posarr mpos] => array(compID, ligPos)
			// Update position of pre-existing Ligatures and associated Marks
			// Start after first GlyphPos
			// count($GlyphPos)-1  is the number of glyphs removed from string
			for ($p = ($GlyphPos[0] + 1); $p < (count($this->OTLdata) + count($GlyphPos) - 1); $p++) {
				$nrem = 0; // Number of Glyphs removed at this point in the string
				for ($i = 0; $i < count($GlyphPos); $i++) {
					if ($i > 0 && $p > $GlyphPos[$i]) {
						$nrem++;
					}
				}
				if (isset($this->assocLigs[$p])) {
					$tmp = $this->assocLigs[$p];
					unset($this->assocLigs[$p]);
					$this->assocLigs[($p - $nrem)] = $tmp;
				}
				if (isset($this->assocMarks[$p])) {
					$tmp = $this->assocMarks[$p];
					unset($this->assocMarks[$p]);
					if ($tmp['ligPos'] > $GlyphPos[0]) {
						$tmp['ligPos'] -= $nrem;
					}
					$this->assocMarks[($p - $nrem)] = $tmp;
				}
			}
			return 1;
		} else {
			return 0;
		}
	}

	////////////////////////////////////////////////////////////////
	//////////       ARABIC        /////////////////////////////////
	////////////////////////////////////////////////////////////////
	private function arabic_initialise()
	{
		// cf. http://unicode.org/Public/UNIDATA/ArabicShaping.txt
		// http://unicode.org/Public/UNIDATA/extracted/DerivedJoiningType.txt
		// JOIN TO FOLLOWING LETTER IN LOGICAL ORDER (i.e. AS INITIAL/MEDIAL FORM) = Unicode Left-Joining (+ Dual-Joining + Join_Causing 00640)
		$this->arabLeftJoining = [
			0x0620 => 1, 0x0626 => 1, 0x0628 => 1, 0x062A => 1, 0x062B => 1, 0x062C => 1, 0x062D => 1, 0x062E => 1,
			0x0633 => 1, 0x0634 => 1, 0x0635 => 1, 0x0636 => 1, 0x0637 => 1, 0x0638 => 1, 0x0639 => 1, 0x063A => 1,
			0x063B => 1, 0x063C => 1, 0x063D => 1, 0x063E => 1, 0x063F => 1, 0x0640 => 1, 0x0641 => 1, 0x0642 => 1,
			0x0643 => 1, 0x0644 => 1, 0x0645 => 1, 0x0646 => 1, 0x0647 => 1, 0x0649 => 1, 0x064A => 1, 0x066E => 1,
			0x066F => 1, 0x0678 => 1, 0x0679 => 1, 0x067A => 1, 0x067B => 1, 0x067C => 1, 0x067D => 1, 0x067E => 1,
			0x067F => 1, 0x0680 => 1, 0x0681 => 1, 0x0682 => 1, 0x0683 => 1, 0x0684 => 1, 0x0685 => 1, 0x0686 => 1,
			0x0687 => 1, 0x069A => 1, 0x069B => 1, 0x069C => 1, 0x069D => 1, 0x069E => 1, 0x069F => 1, 0x06A0 => 1,
			0x06A1 => 1, 0x06A2 => 1, 0x06A3 => 1, 0x06A4 => 1, 0x06A5 => 1, 0x06A6 => 1, 0x06A7 => 1, 0x06A8 => 1,
			0x06A9 => 1, 0x06AA => 1, 0x06AB => 1, 0x06AC => 1, 0x06AD => 1, 0x06AE => 1, 0x06AF => 1, 0x06B0 => 1,
			0x06B1 => 1, 0x06B2 => 1, 0x06B3 => 1, 0x06B4 => 1, 0x06B5 => 1, 0x06B6 => 1, 0x06B7 => 1, 0x06B8 => 1,
			0x06B9 => 1, 0x06BA => 1, 0x06BB => 1, 0x06BC => 1, 0x06BD => 1, 0x06BE => 1, 0x06BF => 1, 0x06C1 => 1,
			0x06C2 => 1, 0x06CC => 1, 0x06CE => 1, 0x06D0 => 1, 0x06D1 => 1, 0x06FA => 1, 0x06FB => 1, 0x06FC => 1,
			0x06FF => 1,
			/* Arabic Supplement */
			0x0750 => 1, 0x0751 => 1, 0x0752 => 1, 0x0753 => 1, 0x0754 => 1, 0x0755 => 1, 0x0756 => 1, 0x0757 => 1,
			0x0758 => 1, 0x075C => 1, 0x075D => 1, 0x075E => 1, 0x075F => 1, 0x0760 => 1, 0x0761 => 1, 0x0762 => 1,
			0x0763 => 1, 0x0764 => 1, 0x0765 => 1, 0x0766 => 1, 0x0767 => 1, 0x0768 => 1, 0x0769 => 1, 0x076A => 1,
			0x076D => 1, 0x076E => 1, 0x076F => 1, 0x0770 => 1, 0x0772 => 1, 0x0775 => 1, 0x0776 => 1, 0x0777 => 1,
			0x077A => 1, 0x077B => 1, 0x077C => 1, 0x077D => 1, 0x077E => 1, 0x077F => 1,
			/* Extended Arabic */
			0x08A0 => 1, 0x08A2 => 1, 0x08A3 => 1, 0x08A4 => 1, 0x08A5 => 1, 0x08A6 => 1, 0x08A7 => 1, 0x08A8 => 1,
			0x08A9 => 1,
			/* 'syrc' Syriac */
			0x0712 => 1, 0x0713 => 1, 0x0714 => 1, 0x071A => 1, 0x071B => 1, 0x071C => 1, 0x071D => 1, 0x071F => 1,
			0x0720 => 1, 0x0721 => 1, 0x0722 => 1, 0x0723 => 1, 0x0724 => 1, 0x0725 => 1, 0x0726 => 1, 0x0727 => 1,
			0x0729 => 1, 0x072B => 1, 0x072D => 1, 0x072E => 1, 0x074E => 1, 0x074F => 1,
			/* N'Ko */
			0x07CA => 1, 0x07CB => 1, 0x07CC => 1, 0x07CD => 1, 0x07CE => 1, 0x07CF => 1, 0x07D0 => 1, 0x07D1 => 1,
			0x07D2 => 1, 0x07D3 => 1, 0x07D4 => 1, 0x07D5 => 1, 0x07D6 => 1, 0x07D7 => 1, 0x07D8 => 1, 0x07D9 => 1,
			0x07DA => 1, 0x07DB => 1, 0x07DC => 1, 0x07DD => 1, 0x07DE => 1, 0x07DF => 1, 0x07E0 => 1, 0x07E1 => 1,
			0x07E2 => 1, 0x07E3 => 1, 0x07E4 => 1, 0x07E5 => 1, 0x07E6 => 1, 0x07E7 => 1, 0x07E8 => 1, 0x07E9 => 1,
			0x07EA => 1, 0x07FA => 1,
			/* Mandaic */
			0x0841 => 1, 0x0842 => 1, 0x0843 => 1, 0x0844 => 1, 0x0845 => 1, 0x0847 => 1, 0x0848 => 1, 0x084A => 1,
			0x084B => 1, 0x084C => 1, 0x084D => 1, 0x084E => 1, 0x0850 => 1, 0x0851 => 1, 0x0852 => 1, 0x0853 => 1,
			0x0855 => 1,
			/* ZWJ U+200D */
			0x0200D => 1];

		/* JOIN TO PREVIOUS LETTER IN LOGICAL ORDER (i.e. AS FINAL/MEDIAL FORM) = Unicode Right-Joining (+ Dual-Joining + Join_Causing) */
		$this->arabRightJoining = [
			0x0620 => 1, 0x0622 => 1, 0x0623 => 1, 0x0624 => 1, 0x0625 => 1, 0x0626 => 1, 0x0627 => 1, 0x0628 => 1,
			0x0629 => 1, 0x062A => 1, 0x062B => 1, 0x062C => 1, 0x062D => 1, 0x062E => 1, 0x062F => 1, 0x0630 => 1,
			0x0631 => 1, 0x0632 => 1, 0x0633 => 1, 0x0634 => 1, 0x0635 => 1, 0x0636 => 1, 0x0637 => 1, 0x0638 => 1,
			0x0639 => 1, 0x063A => 1, 0x063B => 1, 0x063C => 1, 0x063D => 1, 0x063E => 1, 0x063F => 1, 0x0640 => 1,
			0x0641 => 1, 0x0642 => 1, 0x0643 => 1, 0x0644 => 1, 0x0645 => 1, 0x0646 => 1, 0x0647 => 1, 0x0648 => 1,
			0x0649 => 1, 0x064A => 1, 0x066E => 1, 0x066F => 1, 0x0671 => 1, 0x0672 => 1, 0x0673 => 1, 0x0675 => 1,
			0x0676 => 1, 0x0677 => 1, 0x0678 => 1, 0x0679 => 1, 0x067A => 1, 0x067B => 1, 0x067C => 1, 0x067D => 1,
			0x067E => 1, 0x067F => 1, 0x0680 => 1, 0x0681 => 1, 0x0682 => 1, 0x0683 => 1, 0x0684 => 1, 0x0685 => 1,
			0x0686 => 1, 0x0687 => 1, 0x0688 => 1, 0x0689 => 1, 0x068A => 1, 0x068B => 1, 0x068C => 1, 0x068D => 1,
			0x068E => 1, 0x068F => 1, 0x0690 => 1, 0x0691 => 1, 0x0692 => 1, 0x0693 => 1, 0x0694 => 1, 0x0695 => 1,
			0x0696 => 1, 0x0697 => 1, 0x0698 => 1, 0x0699 => 1, 0x069A => 1, 0x069B => 1, 0x069C => 1, 0x069D => 1,
			0x069E => 1, 0x069F => 1, 0x06A0 => 1, 0x06A1 => 1, 0x06A2 => 1, 0x06A3 => 1, 0x06A4 => 1, 0x06A5 => 1,
			0x06A6 => 1, 0x06A7 => 1, 0x06A8 => 1, 0x06A9 => 1, 0x06AA => 1, 0x06AB => 1, 0x06AC => 1, 0x06AD => 1,
			0x06AE => 1, 0x06AF => 1, 0x06B0 => 1, 0x06B1 => 1, 0x06B2 => 1, 0x06B3 => 1, 0x06B4 => 1, 0x06B5 => 1,
			0x06B6 => 1, 0x06B7 => 1, 0x06B8 => 1, 0x06B9 => 1, 0x06BA => 1, 0x06BB => 1, 0x06BC => 1, 0x06BD => 1,
			0x06BE => 1, 0x06BF => 1, 0x06C0 => 1, 0x06C1 => 1, 0x06C2 => 1, 0x06C3 => 1, 0x06C4 => 1, 0x06C5 => 1,
			0x06C6 => 1, 0x06C7 => 1, 0x06C8 => 1, 0x06C9 => 1, 0x06CA => 1, 0x06CB => 1, 0x06CC => 1, 0x06CD => 1,
			0x06CE => 1, 0x06CF => 1, 0x06D0 => 1, 0x06D1 => 1, 0x06D2 => 1, 0x06D3 => 1, 0x06D5 => 1, 0x06EE => 1,
			0x06EF => 1, 0x06FA => 1, 0x06FB => 1, 0x06FC => 1, 0x06FF => 1,
			/* Arabic Supplement */
			0x0750 => 1, 0x0751 => 1, 0x0752 => 1, 0x0753 => 1, 0x0754 => 1, 0x0755 => 1, 0x0756 => 1, 0x0757 => 1,
			0x0758 => 1, 0x0759 => 1, 0x075A => 1, 0x075B => 1, 0x075C => 1, 0x075D => 1, 0x075E => 1, 0x075F => 1,
			0x0760 => 1, 0x0761 => 1, 0x0762 => 1, 0x0763 => 1, 0x0764 => 1, 0x0765 => 1, 0x0766 => 1, 0x0767 => 1,
			0x0768 => 1, 0x0769 => 1, 0x076A => 1, 0x076B => 1, 0x076C => 1, 0x076D => 1, 0x076E => 1, 0x076F => 1,
			0x0770 => 1, 0x0771 => 1, 0x0772 => 1, 0x0773 => 1, 0x0774 => 1, 0x0775 => 1, 0x0776 => 1, 0x0777 => 1,
			0x0778 => 1, 0x0779 => 1, 0x077A => 1, 0x077B => 1, 0x077C => 1, 0x077D => 1, 0x077E => 1, 0x077F => 1,
			/* Extended Arabic */
			0x08A0 => 1, 0x08A2 => 1, 0x08A3 => 1, 0x08A4 => 1, 0x08A5 => 1, 0x08A6 => 1, 0x08A7 => 1, 0x08A8 => 1,
			0x08A9 => 1, 0x08AA => 1, 0x08AB => 1, 0x08AC => 1,
			/* 'syrc' Syriac */
			0x0710 => 1, 0x0712 => 1, 0x0713 => 1, 0x0714 => 1, 0x0715 => 1, 0x0716 => 1, 0x0717 => 1, 0x0718 => 1,
			0x0719 => 1, 0x071A => 1, 0x071B => 1, 0x071C => 1, 0x071D => 1, 0x071E => 1, 0x071F => 1, 0x0720 => 1,
			0x0721 => 1, 0x0722 => 1, 0x0723 => 1, 0x0724 => 1, 0x0725 => 1, 0x0726 => 1, 0x0727 => 1, 0x0728 => 1,
			0x0729 => 1, 0x072A => 1, 0x072B => 1, 0x072C => 1, 0x072D => 1, 0x072E => 1, 0x072F => 1, 0x074D => 1,
			0x074E => 1, 0x074F,
			/* N'Ko */
			0x07CA => 1, 0x07CB => 1, 0x07CC => 1, 0x07CD => 1, 0x07CE => 1, 0x07CF => 1, 0x07D0 => 1, 0x07D1 => 1,
			0x07D2 => 1, 0x07D3 => 1, 0x07D4 => 1, 0x07D5 => 1, 0x07D6 => 1, 0x07D7 => 1, 0x07D8 => 1, 0x07D9 => 1,
			0x07DA => 1, 0x07DB => 1, 0x07DC => 1, 0x07DD => 1, 0x07DE => 1, 0x07DF => 1, 0x07E0 => 1, 0x07E1 => 1,
			0x07E2 => 1, 0x07E3 => 1, 0x07E4 => 1, 0x07E5 => 1, 0x07E6 => 1, 0x07E7 => 1, 0x07E8 => 1, 0x07E9 => 1,
			0x07EA => 1, 0x07FA => 1,
			/* Mandaic */
			0x0841 => 1, 0x0842 => 1, 0x0843 => 1, 0x0844 => 1, 0x0845 => 1, 0x0847 => 1, 0x0848 => 1, 0x084A => 1,
			0x084B => 1, 0x084C => 1, 0x084D => 1, 0x084E => 1, 0x0850 => 1, 0x0851 => 1, 0x0852 => 1, 0x0853 => 1,
			0x0855 => 1,
			0x0840 => 1, 0x0846 => 1, 0x0849 => 1, 0x084F => 1, 0x0854 => 1, /* Right joining */
			/* ZWJ U+200D */
			0x0200D => 1];

		/* VOWELS = TRANSPARENT-JOINING = Unicode Transparent-Joining type (not just vowels) */
		$this->arabTransparent = [
			0x0610 => 1, 0x0611 => 1, 0x0612 => 1, 0x0613 => 1, 0x0614 => 1, 0x0615 => 1, 0x0616 => 1, 0x0617 => 1,
			0x0618 => 1, 0x0619 => 1, 0x061A => 1, 0x064B => 1, 0x064C => 1, 0x064D => 1, 0x064E => 1, 0x064F => 1,
			0x0650 => 1, 0x0651 => 1, 0x0652 => 1, 0x0653 => 1, 0x0654 => 1, 0x0655 => 1, 0x0656 => 1, 0x0657 => 1,
			0x0658 => 1, 0x0659 => 1, 0x065A => 1, 0x065B => 1, 0x065C => 1, 0x065D => 1, 0x065E => 1, 0x065F => 1,
			0x0670 => 1, 0x06D6 => 1, 0x06D7 => 1, 0x06D8 => 1, 0x06D9 => 1, 0x06DA => 1, 0x06DB => 1, 0x06DC => 1,
			0x06DF => 1, 0x06E0 => 1, 0x06E1 => 1, 0x06E2 => 1, 0x06E3 => 1, 0x06E4 => 1, 0x06E7 => 1, 0x06E8 => 1,
			0x06EA => 1, 0x06EB => 1, 0x06EC => 1, 0x06ED => 1,
			/* Extended Arabic */
			0x08E4 => 1, 0x08E5 => 1, 0x08E6 => 1, 0x08E7 => 1, 0x08E8 => 1, 0x08E9 => 1, 0x08EA => 1, 0x08EB => 1,
			0x08EC => 1, 0x08ED => 1, 0x08EE => 1, 0x08EF => 1, 0x08F0 => 1, 0x08F1 => 1, 0x08F2 => 1, 0x08F3 => 1,
			0x08F4 => 1, 0x08F5 => 1, 0x08F6 => 1, 0x08F7 => 1, 0x08F8 => 1, 0x08F9 => 1, 0x08FA => 1, 0x08FB => 1,
			0x08FC => 1, 0x08FD => 1, 0x08FE => 1,
			/* Arabic ligatures in presentation form (converted in 'ccmp' in e.g. Arial and Times ? need to add others in this range) */
			0xFC5E => 1, 0xFC5F => 1, 0xFC60 => 1, 0xFC61 => 1, 0xFC62 => 1,
			/*  'syrc' Syriac */
			0x070F => 1, 0x0711 => 1, 0x0730 => 1, 0x0731 => 1, 0x0732 => 1, 0x0733 => 1, 0x0734 => 1, 0x0735 => 1,
			0x0736 => 1, 0x0737 => 1, 0x0738 => 1, 0x0739 => 1, 0x073A => 1, 0x073B => 1, 0x073C => 1, 0x073D => 1,
			0x073E => 1, 0x073F => 1, 0x0740 => 1, 0x0741 => 1, 0x0742 => 1, 0x0743 => 1, 0x0744 => 1, 0x0745 => 1,
			0x0746 => 1, 0x0747 => 1, 0x0748 => 1, 0x0749 => 1, 0x074A => 1,
			/* N'Ko */
			0x07EB => 1, 0x07EC => 1, 0x07ED => 1, 0x07EE => 1, 0x07EF => 1, 0x07F0 => 1, 0x07F1 => 1, 0x07F2 => 1,
			0x07F3 => 1,
			/* Mandaic */
			0x0859 => 1, 0x085A => 1, 0x085B => 1,
		];
	}

	private function arabic_shaper($usetags, $scriptTag)
	{
		$chars = [];
		for ($i = 0; $i < count($this->OTLdata); $i++) {
			$chars[] = $this->OTLdata[$i]['hex'];
		}

		$crntChar = null;
		$prevChar = null;
		$nextChar = null;
		$output = [];
		$max = count($chars);
		for ($i = $max - 1; $i >= 0; $i--) {
			$crntChar = $chars[$i];
			if ($i > 0) {
				$prevChar = hexdec($chars[$i - 1]);
			} else {
				$prevChar = null;
			}
			if ($prevChar && isset($this->arabTransparentJoin[$prevChar]) && isset($chars[$i - 2])) {
				$prevChar = hexdec($chars[$i - 2]);
				if ($prevChar && isset($this->arabTransparentJoin[$prevChar]) && isset($chars[$i - 3])) {
					$prevChar = hexdec($chars[$i - 3]);
					if ($prevChar && isset($this->arabTransparentJoin[$prevChar]) && isset($chars[$i - 4])) {
						$prevChar = hexdec($chars[$i - 4]);
					}
				}
			}
			if ($crntChar && isset($this->arabTransparentJoin[hexdec($crntChar)])) {
				// If next_char = RightJoining && prev_char = LeftJoining:
				if (isset($chars[$i + 1]) && $chars[$i + 1] && isset($this->arabRightJoining[hexdec($chars[$i + 1])]) && $prevChar && isset($this->arabLeftJoining[$prevChar])) {
					$output[] = $this->get_arab_glyphs($crntChar, 1, $chars, $i, $scriptTag, $usetags); // <final> form
				} else {
					$output[] = $this->get_arab_glyphs($crntChar, 0, $chars, $i, $scriptTag, $usetags);  // <isolated> form
				}
				continue;
			}
			if (hexdec($crntChar) < 128) {
				$output[] = [$crntChar, 0];
				$nextChar = $crntChar;
				continue;
			}
			// 0=ISOLATED FORM :: 1=FINAL :: 2=INITIAL :: 3=MEDIAL
			$form = 0;
			if ($prevChar && isset($this->arabLeftJoining[$prevChar])) {
				$form++;
			}
			if ($nextChar && isset($this->arabRightJoining[hexdec($nextChar)])) {
				$form += 2;
			}
			$output[] = $this->get_arab_glyphs($crntChar, $form, $chars, $i, $scriptTag, $usetags);
			$nextChar = $crntChar;
		}
		$ra = array_reverse($output);
		for ($i = 0; $i < count($this->OTLdata); $i++) {
			$this->OTLdata[$i]['uni'] = hexdec($ra[$i][0]);
			$this->OTLdata[$i]['hex'] = $ra[$i][0];
			$this->OTLdata[$i]['form'] = $ra[$i][1]; // Actaul form substituted 0=ISOLATED FORM :: 1=FINAL :: 2=INITIAL :: 3=MEDIAL
		}
	}

	private function get_arab_glyphs($char, $type, &$chars, $i, $scriptTag, $usetags)
	{
		// Optional Feature settings    // doesn't control Syriac at present
		if (($type === 0 && strpos($usetags, 'isol') === false) || ($type === 1 && strpos($usetags, 'fina') === false) || ($type === 2 && strpos($usetags, 'init') === false) || ($type === 3 && strpos($usetags, 'medi') === false)) {
			return [$char, 0];
		}

		// 0=ISOLATED FORM :: 1=FINAL :: 2=INITIAL :: 3=MEDIAL (:: 4=MED2 :: 5=FIN2 :: 6=FIN3)
		$retk = -1;
		// Alaph 00710 in Syriac
		if ($scriptTag == 'syrc' && $char == '00710') {
			// if there is a preceding (base?) character *** should search back to previous base - ignoring vowels and change $n
			// set $n as the position of the last base; for now we'll just do this:
			$n = $i - 1;
			// if the preceding (base) character cannot be joined to
			// not in $this->arabLeftJoining i.e. not a char which can join to the next one
			if (isset($chars[$n]) && isset($this->arabLeftJoining[hexdec($chars[$n])])) {
				// if in the middle of Syriac words
				if (isset($chars[$i + 1]) && preg_match('/[\x{0700}-\x{0745}]/u', UtfString::code2utf(hexdec($chars[$n]))) && preg_match('/[\x{0700}-\x{0745}]/u', UtfString::code2utf(hexdec($chars[$i + 1]))) && isset($this->arabGlyphs[$char][4])) {
					$retk = 4;
				} // if at the end of Syriac words
				elseif (!isset($chars[$i + 1]) || !preg_match('/[\x{0700}-\x{0745}]/u', UtfString::code2utf(hexdec($chars[$i + 1])))) {
					// if preceding base character IS (00715|00716|0072A)
					if (strpos('0715|0716|072A', $chars[$n]) !== false && isset($this->arabGlyphs[$char][6])) {
						$retk = 6;
					} // elseif preceding base character is NOT (00715|00716|0072A)
					elseif (isset($this->arabGlyphs[$char][5])) {
						$retk = 5;
					}
				}
			}
			if ($retk != -1) {
				return [$this->arabGlyphs[$char][$retk], $retk];
			} else {
				return [$char, 0];
			}
		}

		if (($type > 0 || $type === 0) && isset($this->arabGlyphs[$char][$type])) {
			$retk = $type;
		} elseif ($type == 3 && isset($this->arabGlyphs[$char][1])) { // if <medial> not defined, but <final>, return <final>
			$retk = 1;
		} elseif ($type == 2 && isset($this->arabGlyphs[$char][0])) { // if <initial> not defined, but <isolated>, return <isolated>
			$retk = 0;
		}
		if ($retk != -1) {
			$match = true;
			// If GSUB includes a Backtrack or Lookahead condition (e.g. font ArabicTypesetting)
			if (isset($this->arabGlyphs[$char]['prel'][$retk]) && $this->arabGlyphs[$char]['prel'][$retk]) {
				$ig = 1;
				foreach ($this->arabGlyphs[$char]['prel'][$retk] as $k => $v) { // $k starts 0, 1...
					if (!isset($chars[$i - $ig - $k])) {
						$match = false;
					} elseif (strpos($v, $chars[$i - $ig - $k]) === false) {
						while (strpos($this->arabGlyphs[$char]['ignore'][$retk], $chars[$i - $ig - $k]) !== false) {  // ignore
							$ig++;
						}
						if (!isset($chars[$i - $ig - $k])) {
							$match = false;
						} elseif (strpos($v, $chars[$i - $ig - $k]) === false) {
							$match = false;
						}
					}
				}
			}
			if (isset($this->arabGlyphs[$char]['postl'][$retk]) && $this->arabGlyphs[$char]['postl'][$retk]) {
				$ig = 1;
				foreach ($this->arabGlyphs[$char]['postl'][$retk] as $k => $v) { // $k starts 0, 1...
					if (!isset($chars[$i + $ig + $k])) {
						$match = false;
					} elseif (strpos($v, $chars[$i + $ig + $k]) === false) {
						while (strpos($this->arabGlyphs[$char]['ignore'][$retk], $chars[$i + $ig + $k]) !== false) {  // ignore
							$ig++;
						}
						if (!isset($chars[$i + $ig + $k])) {
							$match = false;
						} elseif (strpos($v, $chars[$i + $ig + $k]) === false) {
							$match = false;
						}
					}
				}
			}
			if ($match) {
				return [$this->arabGlyphs[$char][$retk], $retk];
			} else {
				return [$char, 0];
			}
		} else {
			return [$char, 0];
		}
	}

	////////////////////////////////////////////////////////////////
	/////////////////       LINE BREAKING    ///////////////////////
	////////////////////////////////////////////////////////////////
	/////////////       TIBETAN LINE BREAKING    ///////////////////
	////////////////////////////////////////////////////////////////
	// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
	private function tibetanLineBreaking()
	{
		for ($ptr = 0; $ptr < count($this->OTLdata); $ptr++) {
			// Break opportunities at U+0F0B Tsheg or U=0F0D
			if (isset($this->OTLdata[$ptr]['uni']) && ($this->OTLdata[$ptr]['uni'] == 0x0F0B || $this->OTLdata[$ptr]['uni'] == 0x0F0D)) {
				if (isset($this->OTLdata[$ptr + 1]['uni']) && ($this->OTLdata[$ptr + 1]['uni'] == 0x0F0D || $this->OTLdata[$ptr + 1]['uni'] == 0xF0E)) {
					continue;
				}
				// Set end of word marker in OTLdata at matchpos
				$this->OTLdata[$ptr]['wordend'] = true;
			}
		}
	}

	/**
	 * South East Asian Linebreaking (Thai, Khmer and Lao) using dictionary of words
	 *
	 * Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
	 */
	private function seaLineBreaking()
	{
		// Load Line-breaking dictionary
		if (!isset($this->lbdicts[$this->shaper]) && file_exists(__DIR__ . '/../data/linebrdict' . $this->shaper . '.dat')) {
			$this->lbdicts[$this->shaper] = file_get_contents(__DIR__ . '/../data/linebrdict' . $this->shaper . '.dat');
		}

		$dict = &$this->lbdicts[$this->shaper];

		// Find all word boundaries and mark end of word $this->OTLdata[$i]['wordend']=true on last character
		// If Thai, allow for possible suffixes (not in Lao or Khmer)
		// repeater/ellision characters
		// (0x0E2F);        // Ellision character THAI_PAIYANNOI 0x0E2F  UTF-8 0xE0 0xB8 0xAF
		// (0x0E46);        // Repeat character THAI_MAIYAMOK 0x0E46   UTF-8 0xE0 0xB9 0x86
		// (0x0EC6);        // Repeat character LAO   UTF-8 0xE0 0xBB 0x86

		$rollover = [];
		$ptr = 0;

		while ($ptr < count($this->OTLdata) - 3) {
			if (count($rollover)) {
				$matches = $rollover;
				$rollover = [];
			} else {
				$matches = $this->checkwordmatch($dict, $ptr);
			}
			if (count($matches) == 1) {
				$matchpos = $matches[0];
				// Check for repeaters - if so $matchpos++
				if (isset($this->OTLdata[$matchpos + 1]['uni']) && ($this->OTLdata[$matchpos + 1]['uni'] == 0x0E2F || $this->OTLdata[$matchpos + 1]['uni'] == 0x0E46 || $this->OTLdata[$matchpos + 1]['uni'] == 0x0EC6)) {
					$matchpos++;
				}
				// Set end of word marker in OTLdata at matchpos
				$this->OTLdata[$matchpos]['wordend'] = true;
				$ptr = $matchpos + 1;
			} elseif (empty($matches)) {
				$ptr++;
				// Move past any ASCII characters
				while (isset($this->OTLdata[$ptr]['uni']) && ($this->OTLdata[$ptr]['uni'] >> 8) == 0) {
					$ptr++;
				}
			} else { // Multiple matches
				$secondmatch = false;
				for ($m = count($matches) - 1; $m >= 0; $m--) {
					//for ($m=0;$m<count($matches);$m++) {
					$firstmatch = $matches[$m];
					$matches2 = $this->checkwordmatch($dict, $firstmatch + 1);
					if (count($matches2)) {
						// Set end of word marker in OTLdata at matchpos
						$this->OTLdata[$firstmatch]['wordend'] = true;
						$ptr = $firstmatch + 1;
						$rollover = $matches2;
						$secondmatch = true;
						break;
					}
				}
				if (!$secondmatch) {
					// Set end of word marker in OTLdata at end of longest first match
					$this->OTLdata[$matches[count($matches) - 1]]['wordend'] = true;
					$ptr = $matches[count($matches) - 1] + 1;
					// Move past any ASCII characters
					while (isset($this->OTLdata[$ptr]['uni']) && ($this->OTLdata[$ptr]['uni'] >> 8) == 0) {
						$ptr++;
					}
				}
			}
		}
	}

	private function checkwordmatch(&$dict, $ptr)
	{
		/*
		  Node type: Split.
		  Divide at < 98 >= 98
		  Offset for >= 98 == 79    (long 4-byte unsigned)

		  Node type: Linear match.
		  Char = 97

		  Intermediate match

		  Final match
		 */

		$dictptr = 0;
		$ok = true;
		$matches = [];
		while ($ok) {
			$x = ord($dict[$dictptr]);
			$c = $this->OTLdata[$ptr]['uni'] & 0xFF;
			if ($x == static::_DICT_INTERMEDIATE_MATCH) {
//echo "DICT_INTERMEDIATE_MATCH: ".dechex($c).'<br />';
				// Do not match if next character in text is a Mark
				if (isset($this->OTLdata[$ptr]['uni']) && strpos($this->GlyphClassMarks, $this->OTLdata[$ptr]['hex']) === false) {
					$matches[] = $ptr - 1;
				}
				$dictptr++;
			} elseif ($x == static::_DICT_FINAL_MATCH) {
//echo "DICT_FINAL_MATCH: ".dechex($c).'<br />';
				// Do not match if next character in text is a Mark
				if (isset($this->OTLdata[$ptr]['uni']) && strpos($this->GlyphClassMarks, $this->OTLdata[$ptr]['hex']) === false) {
					$matches[] = $ptr - 1;
				}
				return $matches;
			} elseif ($x == static::_DICT_NODE_TYPE_LINEAR) {
//echo "DICT_NODE_TYPE_LINEAR: ".dechex($c).'<br />';
				$dictptr++;
				$m = ord($dict[$dictptr]);
				if ($c == $m) {
					$ptr++;
					if ($ptr > count($this->OTLdata) - 1) {
						$next = ord($dict[$dictptr + 1]);
						if ($next == static::_DICT_INTERMEDIATE_MATCH || $next == static::_DICT_FINAL_MATCH) {
							// Do not match if next character in text is a Mark
							if (isset($this->OTLdata[$ptr]['uni']) && strpos($this->GlyphClassMarks, $this->OTLdata[$ptr]['hex']) === false) {
								$matches[] = $ptr - 1;
							}
						}
						return $matches;
					}
					$dictptr++;
					continue;
				} else {
//echo "DICT_NODE_TYPE_LINEAR NOT: ".dechex($c).'<br />';
					return $matches;
				}
			} elseif ($x == static::_DICT_NODE_TYPE_SPLIT) {
//echo "DICT_NODE_TYPE_SPLIT ON ".dechex($d).": ".dechex($c).'<br />';
				$dictptr++;
				$d = ord($dict[$dictptr]);
				if ($c < $d) {
					$dictptr += 5;
				} else {
					$dictptr++;
					// Unsigned long 32-bit offset
					$offset = (ord($dict[$dictptr]) * 16777216) + (ord($dict[$dictptr + 1]) << 16) + (ord($dict[$dictptr + 2]) << 8) + ord($dict[$dictptr + 3]);
					$dictptr = $offset;
				}
			} else {
//echo "PROBLEM: ".($x).'<br />';
				$ok = false; // Something has gone wrong
			}
		}

		return $matches;
	}

	////////////////////////////////////////////////////////////////
	//////////       GPOS    ///////////////////////////////////////
	////////////////////////////////////////////////////////////////
	private function _applyGPOSrules($LookupList, $is_old_spec = false)
	{
		foreach ($LookupList as $lu => $tag) {
			$Type = $this->GPOSLookups[$lu]['Type'];
			$Flag = $this->GPOSLookups[$lu]['Flag'];
			$MarkFilteringSet = '';
			if (isset($this->GPOSLookups[$lu]['MarkFilteringSet'])) {
				$MarkFilteringSet = $this->GPOSLookups[$lu]['MarkFilteringSet'];
			}
			$ptr = 0;
			// Test each glyph sequentially
			while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
				$currGlyph = $this->OTLdata[$ptr]['hex'];
				$currGID = $this->OTLdata[$ptr]['uni'];
				$shift = 1;
				foreach ($this->GPOSLookups[$lu]['Subtables'] as $c => $subtable_offset) {
					// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
					if (isset($this->LuCoverage[$lu][$c][$currGID])) {
						// Get rules from font GPOS subtable
						if (isset($this->OTLdata[$ptr]['bidi_type'])) {  // No need to check bidi_type - just a check that it exists
							$shift = $this->_applyGPOSsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GPOS_offset + $this->GSUB_length), $Type, $Flag, $MarkFilteringSet, $this->LuCoverage[$lu][$c], $tag, 0, $is_old_spec);
							if ($shift) {
								break;
							}
						}
					}
				}
				if ($shift == 0) {
					$shift = 1;
				}
				$ptr += $shift;
			}
		}
	}

	//////////////////////////////////////////////////////////////////////////////////
	// GPOS Types
	// Lookup Type 1: Single Adjustment Positioning Subtable        Adjust position of a single glyph
	// Lookup Type 2: Pair Adjustment Positioning Subtable      Adjust position of a pair of glyphs
	// Lookup Type 3: Cursive Attachment Positioning Subtable       Attach cursive glyphs
	// Lookup Type 4: MarkToBase Attachment Positioning Subtable    Attach a combining mark to a base glyph
	// Lookup Type 5: MarkToLigature Attachment Positioning Subtable    Attach a combining mark to a ligature
	// Lookup Type 6: MarkToMark Attachment Positioning Subtable    Attach a combining mark to another mark
	// Lookup Type 7: Contextual Positioning Subtables          Position one or more glyphs in context
	// Lookup Type 8: Chaining Contextual Positioning Subtable      Position one or more glyphs in chained context
	// Lookup Type 9: Extension positioning
	//////////////////////////////////////////////////////////////////////////////////
	private function _applyGPOSvaluerecord($basepos, $Value)
	{

		// If current glyph is a mark with a defined width, any XAdvance is considered to REPLACE the character Advance Width
		// Test case <div style="font-family:myanmartext">&#x1004;&#x103a;&#x1039;&#x1000;&#x1039;&#x1000;&#x103b;&#x103c;&#x103d;&#x1031;&#x102d;</div>
		if (strpos($this->GlyphClassMarks, $this->OTLdata[$basepos]['hex']) !== false) {
			$cw = round($this->mpdf->_getCharWidth($this->mpdf->CurrentFont['cw'], $this->OTLdata[$basepos]['uni']) * $this->mpdf->CurrentFont['unitsPerEm'] / 1000); // convert back to font design units
		} else {
			$cw = 0;
		}

		$apos = $this->_getXAdvancePos($basepos);

		if (isset($Value['XAdvance']) && ($Value['XAdvance'] - $cw) != 0) {
			// However DON'T REPLACE the character Advance Width if Advance Width is negative
			// Test case <div style="font-family: dejavusansmono">&#x440;&#x443;&#x301;&#x441;&#x441;&#x43a;&#x438;&#x439;</div>
			if ($Value['XAdvance'] < 0) {
				$cw = 0;
			}

			// For LTR apply XAdvanceL to the last mark following the base = at $apos
			// For RTL apply XAdvanceR to base = at $basepos
			if (isset($this->OTLdata[$apos]['GPOSinfo']['XAdvanceL'])) {
				$this->OTLdata[$apos]['GPOSinfo']['XAdvanceL'] += $Value['XAdvance'] - $cw;
			} else {
				$this->OTLdata[$apos]['GPOSinfo']['XAdvanceL'] = $Value['XAdvance'] - $cw;
			}
			if (isset($this->OTLdata[$basepos]['GPOSinfo']['XAdvanceR'])) {
				$this->OTLdata[$basepos]['GPOSinfo']['XAdvanceR'] += $Value['XAdvance'] - $cw;
			} else {
				$this->OTLdata[$basepos]['GPOSinfo']['XAdvanceR'] = $Value['XAdvance'] - $cw;
			}
		}

		// Any XPlacement (? and Y Placement) apply to base and marks (from basepos to apos)
		for ($a = $basepos; $a <= $apos; $a++) {
			if (isset($Value['XPlacement'])) {
				if (isset($this->OTLdata[$a]['GPOSinfo']['XPlacement'])) {
					$this->OTLdata[$a]['GPOSinfo']['XPlacement'] += $Value['XPlacement'];
				} else {
					$this->OTLdata[$a]['GPOSinfo']['XPlacement'] = $Value['XPlacement'];
				}
			}
			if (isset($Value['YPlacement'])) {
				if (isset($this->OTLdata[$a]['GPOSinfo']['YPlacement'])) {
					$this->OTLdata[$a]['GPOSinfo']['YPlacement'] += $Value['YPlacement'];
				} else {
					$this->OTLdata[$a]['GPOSinfo']['YPlacement'] = $Value['YPlacement'];
				}
			}
		}
	}

	// If XAdvance is aplied to $ptr - in order for PDF to position the Advance correctly need to place it on
	// the last of any Marks which immediately follow the current glyph
	private function _getXAdvancePos($pos)
	{
		// NB Not all fonts have all marks specified in GlyphClassMarks
		// If the current glyph is not a base (but a mark) then ignore this, and apply to the current position
		if (strpos($this->GlyphClassMarks, $this->OTLdata[$pos]['hex']) !== false) {
			return $pos;
		}

		while (isset($this->OTLdata[$pos + 1]['hex']) && strpos($this->GlyphClassMarks, $this->OTLdata[$pos + 1]['hex']) !== false) {
			$pos++;
		}
		return $pos;
	}

	private function _applyGPOSsubtable($lookupID, $subtable, $ptr, $currGlyph, $currGID, $subtable_offset, $Type, $Flag, $MarkFilteringSet, $LuCoverage, $tag, $level, $is_old_spec)
	{
		if (($Flag & 0x0001) == 1) {
			$dir = 'RTL';
		} else { // only used for Type 3
			$dir = 'LTR';
		}

		$ignore = $this->_getGCOMignoreString($Flag, $MarkFilteringSet);

		// Lets start
		$this->seek($subtable_offset);
		$PosFormat = $this->read_ushort();

		////////////////////////////////////////////////////////////////////////////////
		// LookupType 1: Single adjustment  Adjust position of a single glyph (e.g. SmallCaps/Sups/Subs)
		////////////////////////////////////////////////////////////////////////////////
		if ($Type == 1) {
			//===========
			// Format 1:
			//===========
			if ($PosFormat == 1) {
				$Coverage = $subtable_offset + $this->read_ushort();
				$ValueFormat = $this->read_ushort();
				$Value = $this->_getValueRecord($ValueFormat);
			} //===========
			// Format 2:
			//===========
			elseif ($PosFormat == 2) {
				$Coverage = $subtable_offset + $this->read_ushort();
				$ValueFormat = $this->read_ushort();
				$ValueCount = $this->read_ushort();
				$GlyphPos = $LuCoverage[$currGID];
				$this->skip($GlyphPos * 2 * $this->count_bits($ValueFormat));
				$Value = $this->_getValueRecord($ValueFormat);
			}
			$this->_applyGPOSvaluerecord($ptr, $Value);
			if ($this->debugOTL) {
				$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
			}
			return 1;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 2: Pair adjustment    Adjust position of a pair of glyphs (Kerning)
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 2) {
			$Coverage = $subtable_offset + $this->read_ushort();
			$ValueFormat1 = $this->read_ushort();
			$ValueFormat2 = $this->read_ushort();
			$sizeOfPair = ( 2 * $this->count_bits($ValueFormat1) ) + ( 2 * $this->count_bits($ValueFormat2) );
			//===========
			// Format 1:
			//===========
			if ($PosFormat == 1) {
				$PairSetCount = $this->read_ushort();
				$PairSetOffset = [];
				for ($p = 0; $p < $PairSetCount; $p++) {
					$PairSetOffset[] = $subtable_offset + $this->read_ushort();
				}
				for ($p = 0; $p < $PairSetCount; $p++) {
					if (isset($LuCoverage[$currGID]) && $LuCoverage[$currGID] == $p) {
						$this->seek($PairSetOffset[$p]);
						//PairSet table
						$PairValueCount = $this->read_ushort();
						for ($pv = 0; $pv < $PairValueCount; $pv++) {
							//PairValueRecord
							$gid = $this->read_ushort();
							$SecondGlyph = $this->glyphToChar($gid);
							$FirstGlyph = $this->OTLdata[$ptr]['uni'];

							$checkpos = $ptr;
							$checkpos++;
							while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
								$checkpos++;
							}
							if (isset($this->OTLdata[$checkpos]) && $this->OTLdata[$checkpos]['uni'] == $SecondGlyph) {
								$matchedpos = $checkpos;
							} else {
								$matchedpos = false;
							}

							if ($matchedpos !== false) {
								$Value1 = $this->_getValueRecord($ValueFormat1);
								$Value2 = $this->_getValueRecord($ValueFormat2);
								if ($ValueFormat1) {
									$this->_applyGPOSvaluerecord($ptr, $Value1);
								}
								if ($ValueFormat2) {
									$this->_applyGPOSvaluerecord($matchedpos, $Value2);
									if ($this->debugOTL) {
										$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
									}
									return $matchedpos - $ptr + 1;
								}
								if ($this->debugOTL) {
									$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
								}
								return $matchedpos - $ptr;
							} else {
								$this->skip($sizeOfPair);
							}
						}
					}
				}
				return 0;
			} //===========
			// Format 2:
			//===========
			elseif ($PosFormat == 2) {
				$ClassDef1 = $subtable_offset + $this->read_ushort();
				$ClassDef2 = $subtable_offset + $this->read_ushort();
				$Class1Count = $this->read_ushort();
				$Class2Count = $this->read_ushort();

				$sizeOfValueRecords = $Class1Count * $Class2Count * $sizeOfPair;

				//$this->skip($sizeOfValueRecords );  ???? NOT NEEDED
				// NB Class1Count includes Class 0 even though it is not defined by $ClassDef1
				// i.e. Class1Count = 5; Class1 will contain array(indices 1-4);
				$Class1 = $this->_getClassDefinitionTable($ClassDef1);
				$Class2 = $this->_getClassDefinitionTable($ClassDef2);
				$FirstGlyph = $this->OTLdata[$ptr]['uni'];
				$checkpos = $ptr;
				$checkpos++;
				while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
					$checkpos++;
				}
				if (isset($this->OTLdata[$checkpos])) {
					$matchedpos = $checkpos;
				} else {
					return 0;
				}

				$SecondGlyph = $this->OTLdata[$matchedpos]['uni'];
				for ($i = 0; $i < $Class1Count; $i++) {
					if (isset($Class1[$i]) && count($Class1[$i])) {
						$FirstClassPos = array_search($FirstGlyph, $Class1[$i]);
						if ($FirstClassPos === false) {
							continue;
						} else {
							for ($j = 0; $j < $Class2Count; $j++) {
								if (isset($Class2[$j]) && count($Class2[$j])) {
									$SecondClassPos = array_search($SecondGlyph, $Class2[$j]);
									if ($SecondClassPos === false) {
										continue;
									}

									// Get ValueRecord[$i][$j]
									$offs = ($i * $Class2Count * $sizeOfPair) + ($j * $sizeOfPair);
									$this->seek($subtable_offset + 16 + $offs);

									$Value1 = $this->_getValueRecord($ValueFormat1);
									$Value2 = $this->_getValueRecord($ValueFormat2);
									if ($ValueFormat1) {
										$this->_applyGPOSvaluerecord($ptr, $Value1);
									}
									if ($ValueFormat2) {
										$this->_applyGPOSvaluerecord($matchedpos, $Value2);
										if ($this->debugOTL) {
											$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
										}
										return $matchedpos - $ptr + 1;
									}
									if ($this->debugOTL) {
										$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
									}
									return $matchedpos - $ptr;
								}
							}
						}
					}
				}
				return 0;
			}
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 3: Cursive attachment     Attach cursive glyphs
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 3) {
			$this->skip(4);
			// Need default XAdvance for glyph
			$pdfWidth = $this->mpdf->_getCharWidth($this->mpdf->CurrentFont['cw'], hexdec($currGlyph)); // DON'T convert back to design units

			$CPos = $LuCoverage[$currGID];
			$this->skip($CPos * 4);
			$EntryAnchor = $this->read_ushort();
			$ExitAnchor = $this->read_ushort();
			if ($EntryAnchor != 0) {
				$EntryAnchor += $subtable_offset;
				list($x, $y) = $this->_getAnchorTable($EntryAnchor);
				if ($dir == 'RTL') {
					if (round($pdfWidth) == round($x * 1000 / $this->mpdf->CurrentFont['unitsPerEm'])) {
						$x = 0;
					} else {
						$x = $x - ($pdfWidth * $this->mpdf->CurrentFont['unitsPerEm'] / 1000);
					}
				}

				$this->Entry[$ptr] = ['X' => $x, 'Y' => $y, 'dir' => $dir];
			}
			if ($ExitAnchor != 0) {
				$ExitAnchor += $subtable_offset;
				list($x, $y) = $this->_getAnchorTable($ExitAnchor);
				if ($dir == 'LTR') {
					if (round($pdfWidth) == round($x * 1000 / $this->mpdf->CurrentFont['unitsPerEm'])) {
						$x = 0;
					} else {
						$x = $x - ($pdfWidth * $this->mpdf->CurrentFont['unitsPerEm'] / 1000);
					}
				}
				$this->Exit[$ptr] = ['X' => $x, 'Y' => $y, 'dir' => $dir];
			}
			if ($this->debugOTL) {
				$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
			}
			return 1;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 4: MarkToBase attachment  Attach a combining mark to a base glyph
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 4) {
			$MarkCoverage = $subtable_offset + $this->read_ushort();
			//$MarkCoverage is already set in $LuCoverage 00065|00073 etc
			$BaseCoverage = $subtable_offset + $this->read_ushort();
			$ClassCount = $this->read_ushort(); // Number of classes defined for marks = Number of mark glyphs in the MarkCoverage table
			$MarkArray = $subtable_offset + $this->read_ushort(); // Offset to MarkArray table
			$BaseArray = $subtable_offset + $this->read_ushort(); // Offset to BaseArray table

			$this->seek($BaseCoverage);
			$BaseGlyphs = implode('|', $this->_getCoverage());

			$checkpos = $ptr;
			$checkpos--;

			// ZZZ93
			// In Lohit-Kannada font (old-spec), rules specify a Type 4 GPOS to attach below-forms to base glyph
			// the repositioning does not happen in MS Word, and shouldn't happen comparing with other fonts
			// ?Why not
			// This Fix blocks the GPOS rule if the "mark" is not actually classified as a mark in the GlyphClasses of GDEF
			// but only in Indic old-spec.
			// Test cases: &#xca8;&#xccd;&#xca8;&#xcc1; and &#xc95;&#xccd;&#xcb0;&#xccc;
			if ($this->shaper == 'I' && $is_old_spec && strpos($this->GlyphClassMarks, $this->OTLdata[$ptr]['hex']) === false) {
				return;
			}


			// "To identify the base glyph that combines with a mark, the text-processing client must look backward in the glyph string from the mark to the preceding base glyph."
			while (isset($this->OTLdata[$checkpos]) && strpos($this->GlyphClassMarks, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos--;
			}

			if (isset($this->OTLdata[$checkpos]) && strpos($BaseGlyphs, $this->OTLdata[$checkpos]['hex']) !== false) {
				$matchedpos = $checkpos;
			} else {
				$matchedpos = false;
			}

			if ($matchedpos !== false) {
				// Get the relevant MarkRecord
				$MarkPos = $LuCoverage[$currGID];
				$MarkRecord = $this->_getMarkRecord($MarkArray, $MarkPos); // e.g. Array ( [Class] => 0 [AnchorX] => -549 [AnchorY] => 1548 )
				//Mark Class is = $MarkRecord['Class']
				// Get the relevant BaseRecord
				$this->seek($BaseArray);
				$BaseCount = $this->read_ushort();
				$BasePos = strpos($BaseGlyphs, $this->OTLdata[$matchedpos]['hex']) / 6;

				// Move to the BaseRecord we want
				$nSkip = (2 * $BasePos * $ClassCount );
				$this->skip($nSkip);

				// Read BaseRecord we want for appropriate Class
				$nSkip = 2 * $MarkRecord['Class'];
				$this->skip($nSkip);
				$BaseRecordOffset = $BaseArray + $this->read_ushort();
				list($x, $y) = $this->_getAnchorTable($BaseRecordOffset);
				$BaseRecord = ['AnchorX' => $x, 'AnchorY' => $y]; // e.g. Array ( [AnchorX] => 660 [AnchorY] => 1556 )
				// Need default XAdvance for Base glyph
				$BaseWidth = $this->mpdf->_getCharWidth($this->mpdf->CurrentFont['cw'], $this->OTLdata[$matchedpos]['uni']) * $this->mpdf->CurrentFont['unitsPerEm'] / 1000; // convert back to font design units
				$this->OTLdata[$ptr]['GPOSinfo']['BaseWidth'] = $BaseWidth;
				// And any intervening (ignored) characters
				if (($ptr - $matchedpos) > 1) {
					for ($i = $matchedpos + 1; $i < $ptr; $i++) {
						$BaseWidthExtra = $this->mpdf->_getCharWidth($this->mpdf->CurrentFont['cw'], $this->OTLdata[$i]['uni']) * $this->mpdf->CurrentFont['unitsPerEm'] / 1000; // convert back to font design units
						$this->OTLdata[$ptr]['GPOSinfo']['BaseWidth'] += $BaseWidthExtra;
					}
				}

				// Align to previous Glyph by attachment - so need to add to previous placement values
				$prevXPlacement = (isset($this->OTLdata[$matchedpos]['GPOSinfo']['XPlacement']) ? $this->OTLdata[$matchedpos]['GPOSinfo']['XPlacement'] : 0);
				$prevYPlacement = (isset($this->OTLdata[$matchedpos]['GPOSinfo']['YPlacement']) ? $this->OTLdata[$matchedpos]['GPOSinfo']['YPlacement'] : 0);

				$this->OTLdata[$ptr]['GPOSinfo']['XPlacement'] = $prevXPlacement + $BaseRecord['AnchorX'] - $MarkRecord['AnchorX'];
				$this->OTLdata[$ptr]['GPOSinfo']['YPlacement'] = $prevYPlacement + $BaseRecord['AnchorY'] - $MarkRecord['AnchorY'];
				if ($this->debugOTL) {
					$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
				}
				return 1;
			}
			return 0;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 5: MarkToLigature attachment  Attach a combining mark to a ligature
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 5) {
			$MarkCoverage = $subtable_offset + $this->read_ushort();
			//$MarkCoverage is already set in $LuCoverage 00065|00073 etc
			$LigatureCoverage = $subtable_offset + $this->read_ushort();
			$ClassCount = $this->read_ushort(); // Number of classes defined for marks = Number of mark glyphs in the MarkCoverage table
			$MarkArray = $subtable_offset + $this->read_ushort(); // Offset to MarkArray table
			$LigatureArray = $subtable_offset + $this->read_ushort(); // Offset to LigatureArray table

			$this->seek($LigatureCoverage);
			$LigatureGlyphs = implode('|', $this->_getCoverage());


			$checkpos = $ptr;
			$checkpos--;

			// "To position a combining mark using a MarkToLigature attachment subtable, the text-processing client must work backward from the mark to the preceding ligature glyph."
			while (isset($this->OTLdata[$checkpos]) && strpos($this->GlyphClassMarks, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos--;
			}

			if (isset($this->OTLdata[$checkpos]) && strpos($LigatureGlyphs, $this->OTLdata[$checkpos]['hex']) !== false) {
				$matchedpos = $checkpos;
			} else {
				$matchedpos = false;
			}

			if ($matchedpos !== false) {
				// Get the relevant MarkRecord
				$MarkPos = $LuCoverage[$currGID];
				$MarkRecord = $this->_getMarkRecord($MarkArray, $MarkPos); // e.g. Array ( [Class] => 0 [AnchorX] => -549 [AnchorY] => 1548 )
				//Mark Class is = $MarkRecord['Class']
				// Get the relevant LigatureRecord
				$this->seek($LigatureArray);
				$LigatureCount = $this->read_ushort();
				$LigaturePos = strpos($LigatureGlyphs, $this->OTLdata[$matchedpos]['hex']) / 6;

				// Move to the LigatureAttach table Record we want
				$nSkip = (2 * $LigaturePos);
				$this->skip($nSkip);
				$LigatureAttachOffset = $LigatureArray + $this->read_ushort();
				$this->seek($LigatureAttachOffset);
				$ComponentCount = $this->read_ushort();
				$offsets = [];
				for ($comp = 0; $comp < $ComponentCount; $comp++) {
					// ComponentRecords
					for ($class = 0; $class < $ClassCount; $class++) {
						$offsets[$comp][$class] = $this->read_ushort();
					}
				}

				// Get the specific component for this mark attachment
				if (isset($this->assocLigs[$matchedpos]) && isset($this->assocMarks[$ptr]['ligPos']) && $this->assocMarks[$ptr]['ligPos'] == $matchedpos) {
					$component = $this->assocMarks[$ptr]['compID'];
				} else {
					$component = $ComponentCount - 1;
				}

				$offset = $offsets[$component][$MarkRecord['Class']];
				if ($offset != 0) {
					$LigatureRecordOffset = $offset + $LigatureAttachOffset;
					list($x, $y) = $this->_getAnchorTable($LigatureRecordOffset);
					$LigatureRecord = ['AnchorX' => $x, 'AnchorY' => $y];

					// Need default XAdvance for Ligature glyph
					$LigatureWidth = $this->mpdf->_getCharWidth($this->mpdf->CurrentFont['cw'], $this->OTLdata[$matchedpos]['uni']) * $this->mpdf->CurrentFont['unitsPerEm'] / 1000; // convert back to font design units
					$this->OTLdata[$ptr]['GPOSinfo']['BaseWidth'] = $LigatureWidth;
					// And any intervening (ignored)characters
					if (($ptr - $matchedpos) > 1) {
						for ($i = $matchedpos + 1; $i < $ptr; $i++) {
							$LigatureWidthExtra = $this->mpdf->_getCharWidth($this->mpdf->CurrentFont['cw'], $this->OTLdata[$i]['uni']) * $this->mpdf->CurrentFont['unitsPerEm'] / 1000; // convert back to font design units
							$this->OTLdata[$ptr]['GPOSinfo']['BaseWidth'] += $LigatureWidthExtra;
						}
					}

					// Align to previous Ligature by attachment - so need to add to previous placement values
					if (isset($this->OTLdata[$matchedpos]['GPOSinfo']['XPlacement'])) {
						$prevXPlacement = $this->OTLdata[$matchedpos]['GPOSinfo']['XPlacement'];
					} else {
						$prevXPlacement = 0;
					}
					if (isset($this->OTLdata[$matchedpos]['GPOSinfo']['YPlacement'])) {
						$prevYPlacement = $this->OTLdata[$matchedpos]['GPOSinfo']['YPlacement'];
					} else {
						$prevYPlacement = 0;
					}

					$this->OTLdata[$ptr]['GPOSinfo']['XPlacement'] = $prevXPlacement + $LigatureRecord['AnchorX'] - $MarkRecord['AnchorX'];
					$this->OTLdata[$ptr]['GPOSinfo']['YPlacement'] = $prevYPlacement + $LigatureRecord['AnchorY'] - $MarkRecord['AnchorY'];
					if ($this->debugOTL) {
						$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
					}
					return 1;
				}
			}
			return 0;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 6: MarkToMark attachment  Attach a combining mark to another mark
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 6) {
			$Mark1Coverage = $subtable_offset + $this->read_ushort(); // Combining Mark
			//$Mark1Coverage is already set in $LuCoverage 0065|0073 etc
			$Mark2Coverage = $subtable_offset + $this->read_ushort(); // Base Mark
			$ClassCount = $this->read_ushort(); // Number of classes defined for marks = No. of Combining mark1 glyphs in the MarkCoverage table
			$Mark1Array = $subtable_offset + $this->read_ushort(); // Offset to MarkArray table
			$Mark2Array = $subtable_offset + $this->read_ushort(); // Offset to Mark2Array table
			$this->seek($Mark2Coverage);
			$Mark2Glyphs = implode('|', $this->_getCoverage());
			$checkpos = $ptr;
			$checkpos--;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos--;
			}
			if (isset($this->OTLdata[$checkpos]) && strpos($Mark2Glyphs, $this->OTLdata[$checkpos]['hex']) !== false) {
				$matchedpos = $checkpos;
			} else {
				$matchedpos = false;
			}

			if ($matchedpos !== false) {
				// Get the relevant MarkRecord
				$Mark1Pos = $LuCoverage[$currGID];
				$Mark1Record = $this->_getMarkRecord($Mark1Array, $Mark1Pos); // e.g. Array ( [Class] => 0 [AnchorX] => -549 [AnchorY] => 1548 )
				//Mark Class is = $Mark1Record['Class']
				// Get the relevant Mark2Record
				$this->seek($Mark2Array);
				$Mark2Count = $this->read_ushort();
				$Mark2Pos = strpos($Mark2Glyphs, $this->OTLdata[$matchedpos]['hex']) / 6;

				// Move to the Mark2Record we want
				$nSkip = (2 * $Mark2Pos * $ClassCount );
				$this->skip($nSkip);

				// Read Mark2Record we want for appropriate Class
				$nSkip = 2 * $Mark1Record['Class'];
				$this->skip($nSkip);
				$Mark2RecordOffset = $Mark2Array + $this->read_ushort();
				list($x, $y) = $this->_getAnchorTable($Mark2RecordOffset);
				$Mark2Record = ['AnchorX' => $x, 'AnchorY' => $y]; // e.g. Array ( [AnchorX] => 660 [AnchorY] => 1556 )
				// Need default XAdvance for Mark2 glyph
				$Mark2Width = $this->mpdf->_getCharWidth($this->mpdf->CurrentFont['cw'], $this->OTLdata[$matchedpos]['uni']) * $this->mpdf->CurrentFont['unitsPerEm'] / 1000; // convert back to font design units
				// IF combining marks are set on different components of a ligature glyph, do not apply this rule
				// Test: arabictypesetting: &#x625;&#x650;&#x644;&#x64e;&#x649;&#x670;&#x653;
				// Test: arabictypesetting: &#x628;&#x651;&#x64e;&#x64a;&#x652;&#x646;&#x64e;&#x643;&#x64f;&#x645;&#x652;
				$prevLig = -1;
				$thisLig = -1;
				$prevComp = -1;
				$thisComp = -1;
				if (isset($this->assocMarks[$matchedpos])) {
					$prevLig = $this->assocMarks[$matchedpos]['ligPos'];
					$prevComp = $this->assocMarks[$matchedpos]['compID'];
				}
				if (isset($this->assocMarks[$ptr])) {
					$thisLig = $this->assocMarks[$ptr]['ligPos'];
					$thisComp = $this->assocMarks[$ptr]['compID'];
				}

				// However IF Mark2 (first in logical order, i.e. being attached to) is not associated with a base, carry on
				// This happens in Indic when the Mark being attached to e.g. [Halant Ma lig] -> MatraU,  [U+0B4D + U+B2E as E0F5]-> U+0B41 become E135
				if (!defined("OMIT_OTL_FIX_1") || OMIT_OTL_FIX_1 != 1) {
					/* OTL_FIX_1 */
					if (isset($this->assocMarks[$matchedpos]) && ($prevLig != $thisLig || $prevComp != $thisComp )) {
						return 0;
					}
				} else {
					/* Original code */
					if ($prevLig != $thisLig || $prevComp != $thisComp) {
						return 0;
					}
				}


				if (!defined("OMIT_OTL_FIX_2") || OMIT_OTL_FIX_2 != 1) {
					/* OTL_FIX_2 */
					if (!isset($this->OTLdata[$matchedpos]['GPOSinfo']['BaseWidth']) || !$this->OTLdata[$matchedpos]['GPOSinfo']['BaseWidth']) {
						$this->OTLdata[$ptr]['GPOSinfo']['BaseWidth'] = $Mark2Width;
					}
				}

				// ZZZ99Q - Test Case font-family: garuda &#xe19;&#xe49;&#xe33;
				if (isset($this->OTLdata[$matchedpos]['GPOSinfo']['BaseWidth']) && $this->OTLdata[$matchedpos]['GPOSinfo']['BaseWidth']) {
					$this->OTLdata[$ptr]['GPOSinfo']['BaseWidth'] = $this->OTLdata[$matchedpos]['GPOSinfo']['BaseWidth'];
				}

				// Align to previous Mark by attachment - so need to add the previous placement values
				$prevXPlacement = (isset($this->OTLdata[$matchedpos]['GPOSinfo']['XPlacement']) ? $this->OTLdata[$matchedpos]['GPOSinfo']['XPlacement'] : 0);
				$prevYPlacement = (isset($this->OTLdata[$matchedpos]['GPOSinfo']['YPlacement']) ? $this->OTLdata[$matchedpos]['GPOSinfo']['YPlacement'] : 0);
				$this->OTLdata[$ptr]['GPOSinfo']['XPlacement'] = $prevXPlacement + $Mark2Record['AnchorX'] - $Mark1Record['AnchorX'];
				$this->OTLdata[$ptr]['GPOSinfo']['YPlacement'] = $prevYPlacement + $Mark2Record['AnchorY'] - $Mark1Record['AnchorY'];
				if ($this->debugOTL) {
					$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
				}
				return 1;
			}
			return 0;
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 7: Context positioning    Position one or more glyphs in context
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 7) {
			//===========
			// Format 1:
			//===========
			if ($PosFormat == 1) {
				throw new \Mpdf\MpdfException("GPOS Lookup Type " . $Type . " Format " . $PosFormat . " not TESTED YET.");
			} //===========
			// Format 2:
			//===========
			elseif ($PosFormat == 2) {
				$CoverageTableOffset = $subtable_offset + $this->read_ushort();
				$InputClassDefOffset = $subtable_offset + $this->read_ushort();
				$PosClassSetCnt = $this->read_ushort();
				$PosClassSetOffset = [];
				for ($b = 0; $b < $PosClassSetCnt; $b++) {
					$offset = $this->read_ushort();
					if ($offset == 0x0000) {
						$PosClassSetOffset[] = $offset;
					} else {
						$PosClassSetOffset[] = $subtable_offset + $offset;
					}
				}

				$InputClasses = $this->_getClasses($InputClassDefOffset);

				for ($s = 0; $s < $PosClassSetCnt; $s++) { // $ChainPosClassSet is ordered by input class-may be NULL
					// Select $PosClassSet if currGlyph is in First Input Class
					if ($PosClassSetOffset[$s] > 0 && isset($InputClasses[$s][$currGID])) {
						$this->seek($PosClassSetOffset[$s]);
						$PosClassRuleCnt = $this->read_ushort();
						$PosClassRule = [];
						for ($b = 0; $b < $PosClassRuleCnt; $b++) {
							$PosClassRule[$b] = $PosClassSetOffset[$s] + $this->read_ushort();
						}

						for ($b = 0; $b < $PosClassRuleCnt; $b++) {  // EACH RULE
							$this->seek($PosClassRule[$b]);
							$InputGlyphCount = $this->read_ushort();
							$PosCount = $this->read_ushort();

							$Input = [];
							for ($r = 1; $r < $InputGlyphCount; $r++) {
								$Input[$r] = $this->read_ushort();
							}
							$inputClass = $s;

							$inputGlyphs = [];
							$inputGlyphs[0] = $InputClasses[$inputClass];

							if ($InputGlyphCount > 1) {
								//  NB starts at 1
								for ($gcl = 1; $gcl < $InputGlyphCount; $gcl++) {
									$classindex = $Input[$gcl];
									if (isset($InputClasses[$classindex])) {
										$inputGlyphs[$gcl] = $InputClasses[$classindex];
									} else {
										$inputGlyphs[$gcl] = '';
									}
								}
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$class0excl = [];
							for ($gc = 1; $gc <= count($InputClasses); $gc++) {
								if (is_array($InputClasses[$gc])) {
									$class0excl = $class0excl + $InputClasses[$gc];
								}
							}

							$backtrackGlyphs = [];
							$lookaheadGlyphs = [];

							$matched = $this->checkContextMatchMultipleUni($inputGlyphs, $backtrackGlyphs, $lookaheadGlyphs, $ignore, $ptr, $class0excl);
							if ($matched) {
								for ($p = 0; $p < $PosCount; $p++) { // EACH LOOKUP
									$SequenceIndex[$p] = $this->read_ushort();
									$LookupListIndex[$p] = $this->read_ushort();
								}

								for ($p = 0; $p < $PosCount; $p++) {
									// Apply  $LookupListIndex  at   $SequenceIndex
									if ($SequenceIndex[$p] >= $InputGlyphCount) {
										continue;
									}
									$lu = $LookupListIndex[$p];
									$luType = $this->GPOSLookups[$lu]['Type'];
									$luFlag = $this->GPOSLookups[$lu]['Flag'];
									$luMarkFilteringSet = $this->GPOSLookups[$lu]['MarkFilteringSet'];

									$luptr = $matched[$SequenceIndex[$p]];
									$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
									$lucurrGID = $this->OTLdata[$luptr]['uni'];

									foreach ($this->GPOSLookups[$lu]['Subtables'] as $luc => $lusubtable_offset) {
										$shift = $this->_applyGPOSsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GPOS_offset + $this->GSUB_length), $luType, $luFlag, $luMarkFilteringSet, $this->LuCoverage[$lu][$luc], $tag, 1, $is_old_spec);
										if ($this->debugOTL && $shift) {
											$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
										}
										if ($shift) {
											break;
										}
									}
								}

								if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
									return $shift;
								} /* OTL_FIX_3 */
								else {
									return $InputGlyphCount; // should be + matched ignores in Input Sequence
								}
							}
						}
					}
				}

				return 0;
			} //===========
			// Format 3:
			//===========
			elseif ($PosFormat == 3) {
				throw new \Mpdf\MpdfException("GPOS Lookup Type " . $Type . " Format " . $PosFormat . " not TESTED YET.");
			} else {
				throw new \Mpdf\MpdfException("GPOS Lookup Type " . $Type . ", Format " . $PosFormat . " not supported.");
			}
		} ////////////////////////////////////////////////////////////////////////////////
		// LookupType 8: Chained Context positioning    Position one or more glyphs in chained context
		////////////////////////////////////////////////////////////////////////////////
		elseif ($Type == 8) {
			//===========
			// Format 1:
			//===========
			if ($PosFormat == 1) {
				throw new \Mpdf\MpdfException("GPOS Lookup Type " . $Type . " Format " . $PosFormat . " not TESTED YET.");
				return 0;
			} //===========
			// Format 2:
			//===========
			elseif ($PosFormat == 2) {
				$CoverageTableOffset = $subtable_offset + $this->read_ushort();
				$BacktrackClassDefOffset = $subtable_offset + $this->read_ushort();
				$InputClassDefOffset = $subtable_offset + $this->read_ushort();
				$LookaheadClassDefOffset = $subtable_offset + $this->read_ushort();
				$ChainPosClassSetCnt = $this->read_ushort();
				$ChainPosClassSetOffset = [];
				for ($b = 0; $b < $ChainPosClassSetCnt; $b++) {
					$offset = $this->read_ushort();
					if ($offset == 0x0000) {
						$ChainPosClassSetOffset[] = $offset;
					} else {
						$ChainPosClassSetOffset[] = $subtable_offset + $offset;
					}
				}

				$BacktrackClasses = $this->_getClasses($BacktrackClassDefOffset);
				$InputClasses = $this->_getClasses($InputClassDefOffset);
				$LookaheadClasses = $this->_getClasses($LookaheadClassDefOffset);

				for ($s = 0; $s < $ChainPosClassSetCnt; $s++) { // $ChainPosClassSet is ordered by input class-may be NULL
					// Select $ChainPosClassSet if currGlyph is in First Input Class
					if ($ChainPosClassSetOffset[$s] > 0 && isset($InputClasses[$s][$currGID])) {
						$this->seek($ChainPosClassSetOffset[$s]);
						$ChainPosClassRuleCnt = $this->read_ushort();
						$ChainPosClassRule = [];
						for ($b = 0; $b < $ChainPosClassRuleCnt; $b++) {
							$ChainPosClassRule[$b] = $ChainPosClassSetOffset[$s] + $this->read_ushort();
						}

						for ($b = 0; $b < $ChainPosClassRuleCnt; $b++) {  // EACH RULE
							$this->seek($ChainPosClassRule[$b]);
							$BacktrackGlyphCount = $this->read_ushort();
							$Backtrack = [];
							for ($r = 0; $r < $BacktrackGlyphCount; $r++) {
								$Backtrack[$r] = $this->read_ushort();
							}
							$InputGlyphCount = $this->read_ushort();
							$Input = [];
							for ($r = 1; $r < $InputGlyphCount; $r++) {
								$Input[$r] = $this->read_ushort();
							}
							$LookaheadGlyphCount = $this->read_ushort();
							$Lookahead = [];
							for ($r = 0; $r < $LookaheadGlyphCount; $r++) {
								$Lookahead[$r] = $this->read_ushort();
							}

							$inputClass = $s; //???

							$inputGlyphs = [];
							$inputGlyphs[0] = $InputClasses[$inputClass];

							if ($InputGlyphCount > 1) {
								//  NB starts at 1
								for ($gcl = 1; $gcl < $InputGlyphCount; $gcl++) {
									$classindex = $Input[$gcl];
									if (isset($InputClasses[$classindex])) {
										$inputGlyphs[$gcl] = $InputClasses[$classindex];
									} else {
										$inputGlyphs[$gcl] = '';
									}
								}
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$class0excl = [];
							for ($gc = 1; $gc <= count($InputClasses); $gc++) {
								if (isset($InputClasses[$gc]) && is_array($InputClasses[$gc])) {
									$class0excl = $class0excl + $InputClasses[$gc];
								}
							}

							if ($BacktrackGlyphCount) {
								$backtrackGlyphs = [];
								for ($gcl = 0; $gcl < $BacktrackGlyphCount; $gcl++) {
									$classindex = $Backtrack[$gcl];
									if (isset($BacktrackClasses[$classindex])) {
										$backtrackGlyphs[$gcl] = $BacktrackClasses[$classindex];
									} else {
										$backtrackGlyphs[$gcl] = '';
									}
								}
							} else {
								$backtrackGlyphs = [];
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$bclass0excl = [];
							for ($gc = 1; $gc <= count($BacktrackClasses); $gc++) {
								if (isset($BacktrackClasses[$gc]) && is_array($BacktrackClasses[$gc])) {
									$bclass0excl = $bclass0excl + $BacktrackClasses[$gc];
								}
							}

							if ($LookaheadGlyphCount) {
								$lookaheadGlyphs = [];
								for ($gcl = 0; $gcl < $LookaheadGlyphCount; $gcl++) {
									$classindex = $Lookahead[$gcl];
									if (isset($LookaheadClasses[$classindex])) {
										$lookaheadGlyphs[$gcl] = $LookaheadClasses[$classindex];
									} else {
										$lookaheadGlyphs[$gcl] = '';
									}
								}
							} else {
								$lookaheadGlyphs = [];
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$lclass0excl = [];
							for ($gc = 1; $gc <= count($LookaheadClasses); $gc++) {
								if (isset($LookaheadClasses[$gc]) && is_array($LookaheadClasses[$gc])) {
									$lclass0excl = $lclass0excl + $LookaheadClasses[$gc];
								}
							}

							$matched = $this->checkContextMatchMultipleUni($inputGlyphs, $backtrackGlyphs, $lookaheadGlyphs, $ignore, $ptr, $class0excl, $bclass0excl, $lclass0excl);
							if ($matched) {
								$PosCount = $this->read_ushort();
								$SequenceIndex = [];
								$LookupListIndex = [];
								for ($p = 0; $p < $PosCount; $p++) { // EACH LOOKUP
									$SequenceIndex[$p] = $this->read_ushort();
									$LookupListIndex[$p] = $this->read_ushort();
								}

								for ($p = 0; $p < $PosCount; $p++) {
									// Apply  $LookupListIndex  at   $SequenceIndex
									if ($SequenceIndex[$p] >= $InputGlyphCount) {
										continue;
									}
									$lu = $LookupListIndex[$p];
									$luType = $this->GPOSLookups[$lu]['Type'];
									$luFlag = $this->GPOSLookups[$lu]['Flag'];
									$luMarkFilteringSet = $this->GPOSLookups[$lu]['MarkFilteringSet'];

									$luptr = $matched[$SequenceIndex[$p]];
									$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
									$lucurrGID = $this->OTLdata[$luptr]['uni'];

									foreach ($this->GPOSLookups[$lu]['Subtables'] as $luc => $lusubtable_offset) {
										$shift = $this->_applyGPOSsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GPOS_offset + $this->GSUB_length), $luType, $luFlag, $luMarkFilteringSet, $this->LuCoverage[$lu][$luc], $tag, 1, $is_old_spec);
										if ($this->debugOTL && $shift) {
											$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
										}
										if ($shift) {
											break;
										}
									}
								}

								if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
									return $shift;
								} /* OTL_FIX_3 */
								else {
									return $InputGlyphCount; // should be + matched ignores in Input Sequence
								}
							}
						}
					}
				}

				return 0;
			} //===========
			// Format 3:
			//===========
			elseif ($PosFormat == 3) {
				$BacktrackGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
					$CoverageBacktrackOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$InputGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $InputGlyphCount; $b++) {
					$CoverageInputOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$LookaheadGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
					$CoverageLookaheadOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$PosCount = $this->read_ushort();
				$save_pos = $this->_pos; // Save the point just after PosCount

				$CoverageBacktrackGlyphs = [];
				for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
					$this->seek($CoverageBacktrackOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageBacktrackGlyphs[$b] = implode("|", $glyphs);
				}
				$CoverageInputGlyphs = [];
				for ($b = 0; $b < $InputGlyphCount; $b++) {
					$this->seek($CoverageInputOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageInputGlyphs[$b] = implode("|", $glyphs);
				}
				$CoverageLookaheadGlyphs = [];
				for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
					$this->seek($CoverageLookaheadOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageLookaheadGlyphs[$b] = implode("|", $glyphs);
				}
				$matched = $this->checkContextMatchMultiple($CoverageInputGlyphs, $CoverageBacktrackGlyphs, $CoverageLookaheadGlyphs, $ignore, $ptr);
				if ($matched) {
					$this->seek($save_pos); // Return to just after PosCount
					for ($p = 0; $p < $PosCount; $p++) {
						// PosLookupRecord
						$PosLookupRecord[$p]['SequenceIndex'] = $this->read_ushort();
						$PosLookupRecord[$p]['LookupListIndex'] = $this->read_ushort();
					}
					for ($p = 0; $p < $PosCount; $p++) {
						// Apply  $PosLookupRecord[$p]['LookupListIndex']  at   $PosLookupRecord[$p]['SequenceIndex']
						if ($PosLookupRecord[$p]['SequenceIndex'] >= $InputGlyphCount) {
							continue;
						}
						$lu = $PosLookupRecord[$p]['LookupListIndex'];
						$luType = $this->GPOSLookups[$lu]['Type'];
						$luFlag = $this->GPOSLookups[$lu]['Flag'];
						if (isset($this->GPOSLookups[$lu]['MarkFilteringSet'])) {
							$luMarkFilteringSet = $this->GPOSLookups[$lu]['MarkFilteringSet'];
						} else {
							$luMarkFilteringSet = '';
						}

						$luptr = $matched[$PosLookupRecord[$p]['SequenceIndex']];
						$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
						$lucurrGID = $this->OTLdata[$luptr]['uni'];

						foreach ($this->GPOSLookups[$lu]['Subtables'] as $luc => $lusubtable_offset) {
							$shift = $this->_applyGPOSsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GPOS_offset + $this->GSUB_length), $luType, $luFlag, $luMarkFilteringSet, $this->LuCoverage[$lu][$luc], $tag, 1, $is_old_spec);
							if ($this->debugOTL && $shift) {
								$this->_dumpproc('GPOS', $lookupID, $subtable, $Type, $PosFormat, $ptr, $currGlyph, $level);
							}
							if ($shift) {
								break;
							}
						}
					}
				}
			} else {
				throw new \Mpdf\MpdfException("GPOS Lookup Type " . $Type . ", Format " . $PosFormat . " not supported.");
			}
		} else {
			throw new \Mpdf\MpdfException("GPOS Lookup Type " . $Type . " not supported.");
		}
	}

	//////////////////////////////////////////////////////////////////////////////////
	// GPOS / GSUB / GCOM (common) functions
	//////////////////////////////////////////////////////////////////////////////////
	private function checkContextMatch($Input, $Backtrack, $Lookahead, $ignore, $ptr)
	{
		// Input etc are single numbers - GSUB Format 6.1
		// Input starts with (1=>xxx)
		// return false if no match, else an array of ptr for matches (0=>0, 1=>3,...)

		$current_syllable = (isset($this->OTLdata[$ptr]['syllable']) ? $this->OTLdata[$ptr]['syllable'] : 0);

		// BACKTRACK
		$checkpos = $ptr;
		for ($i = 0; $i < count($Backtrack); $i++) {
			$checkpos--;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos--;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} elseif (!isset($this->OTLdata[$checkpos]) || $this->OTLdata[$checkpos]['uni'] != $Backtrack[$i]) {
				return false;
			}
		}

		// INPUT
		$matched = [0 => $ptr];
		$checkpos = $ptr;
		for ($i = 1; $i < count($Input); $i++) {
			$checkpos++;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos++;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} elseif (isset($this->OTLdata[$checkpos]) && $this->OTLdata[$checkpos]['uni'] == $Input[$i]) {
				$matched[] = $checkpos;
			} else {
				return false;
			}
		}

		// LOOKAHEAD
		for ($i = 0; $i < count($Lookahead); $i++) {
			$checkpos++;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos++;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} elseif (!isset($this->OTLdata[$checkpos]) || $this->OTLdata[$checkpos]['uni'] != $Lookahead[$i]) {
				return false;
			}
		}

		return $matched;
	}

	private function checkContextMatchMultiple($Input, $Backtrack, $Lookahead, $ignore, $ptr, $class0excl = '', $bclass0excl = '', $lclass0excl = '')
	{
		// Input etc are string/array of glyph strings  - GSUB Format 5.2, 5.3, 6.2, 6.3, GPOS Format 7.2, 7.3, 8.2, 8.3
		// Input starts with (1=>xxx)
		// return false if no match, else an array of ptr for matches (0=>0, 1=>3,...)
		// $class0excl is the string of glyphs in all classes except Class 0 (GSUB 5.2, 6.2, GPOS 7.2, 8.2)
		// $bclass0excl & $lclass0excl are the same for lookahead and backtrack (GSUB 6.2, GPOS 8.2)

		$current_syllable = (isset($this->OTLdata[$ptr]['syllable']) ? $this->OTLdata[$ptr]['syllable'] : 0);

		// BACKTRACK
		$checkpos = $ptr;
		for ($i = 0; $i < count($Backtrack); $i++) {
			$checkpos--;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos--;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} // If Class 0 specified, matches anything NOT in $bclass0excl
			elseif (!$Backtrack[$i] && isset($this->OTLdata[$checkpos]) && strpos($bclass0excl, $this->OTLdata[$checkpos]['hex']) !== false) {
				return false;
			} elseif (!isset($this->OTLdata[$checkpos]) || strpos($Backtrack[$i], $this->OTLdata[$checkpos]['hex']) === false) {
				return false;
			}
		}

		// INPUT
		$matched = [0 => $ptr];
		$checkpos = $ptr;
		for ($i = 1; $i < count($Input); $i++) { // Start at 1 - already matched the first InputGlyph
			$checkpos++;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos++;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} // If Input Class 0 specified, matches anything NOT in $class0excl
			elseif (!$Input[$i] && isset($this->OTLdata[$checkpos]) && strpos($class0excl, $this->OTLdata[$checkpos]['hex']) === false) {
				$matched[] = $checkpos;
			} elseif (isset($this->OTLdata[$checkpos]) && strpos($Input[$i], $this->OTLdata[$checkpos]['hex']) !== false) {
				$matched[] = $checkpos;
			} else {
				return false;
			}
		}

		// LOOKAHEAD
		for ($i = 0; $i < count($Lookahead); $i++) {
			$checkpos++;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos++;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} // If Class 0 specified, matches anything NOT in $lclass0excl
			elseif (!$Lookahead[$i] && isset($this->OTLdata[$checkpos]) && strpos($lclass0excl, $this->OTLdata[$checkpos]['hex']) !== false) {
				return false;
			} elseif (!isset($this->OTLdata[$checkpos]) || strpos($Lookahead[$i], $this->OTLdata[$checkpos]['hex']) === false) {
				return false;
			}
		}
		return $matched;
	}

	private function checkContextMatchMultipleUni($Input, $Backtrack, $Lookahead, $ignore, $ptr, $class0excl = [], $bclass0excl = [], $lclass0excl = [])
	{
		// Input etc are array of glyphs - GSUB Format 5.2, 5.3, 6.2, 6.3, GPOS Format 7.2, 7.3, 8.2, 8.3
		// Input starts with (1=>xxx)
		// return false if no match, else an array of ptr for matches (0=>0, 1=>3,...)
		// $class0excl is array of glyphs in all classes except Class 0 (GSUB 5.2, 6.2, GPOS 7.2, 8.2)
		// $bclass0excl & $lclass0excl are the same for lookahead and backtrack (GSUB 6.2, GPOS 8.2)

		$current_syllable = (isset($this->OTLdata[$ptr]['syllable']) ? $this->OTLdata[$ptr]['syllable'] : 0);

		// BACKTRACK
		$checkpos = $ptr;
		for ($i = 0; $i < count($Backtrack); $i++) {
			$checkpos--;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos--;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} // If Class 0 specified, matches anything NOT in $bclass0excl
			elseif (!$Backtrack[$i] && isset($this->OTLdata[$checkpos]) && isset($bclass0excl[$this->OTLdata[$checkpos]['uni']])) {
				return false;
			} elseif (!isset($this->OTLdata[$checkpos]) || !isset($Backtrack[$i][$this->OTLdata[$checkpos]['uni']])) {
				return false;
			}
		}

		// INPUT
		$matched = [0 => $ptr];
		$checkpos = $ptr;
		for ($i = 1; $i < count($Input); $i++) { // Start at 1 - already matched the first InputGlyph
			$checkpos++;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos++;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} // If Input Class 0 specified, matches anything NOT in $class0excl
			elseif (!$Input[$i] && isset($this->OTLdata[$checkpos]) && !isset($class0excl[$this->OTLdata[$checkpos]['uni']])) {
				$matched[] = $checkpos;
			} elseif (isset($this->OTLdata[$checkpos]) && isset($Input[$i][$this->OTLdata[$checkpos]['uni']])) {
				$matched[] = $checkpos;
			} else {
				return false;
			}
		}

		// LOOKAHEAD
		for ($i = 0; $i < count($Lookahead); $i++) {
			$checkpos++;
			while (isset($this->OTLdata[$checkpos]) && strpos($ignore, $this->OTLdata[$checkpos]['hex']) !== false) {
				$checkpos++;
			}
			// If outside scope of current syllable - return no match
			if ($this->restrictToSyllable && isset($this->OTLdata[$checkpos]['syllable']) && $this->OTLdata[$checkpos]['syllable'] != $current_syllable) {
				return false;
			} // If Class 0 specified, matches anything NOT in $lclass0excl
			elseif (!$Lookahead[$i] && isset($this->OTLdata[$checkpos]) && isset($lclass0excl[$this->OTLdata[$checkpos]['uni']])) {
				return false;
			} elseif (!isset($this->OTLdata[$checkpos]) || !isset($Lookahead[$i][$this->OTLdata[$checkpos]['uni']])) {
				return false;
			}
		}
		return $matched;
	}

	private function _getClassDefinitionTable($offset)
	{
		if (isset($this->LuDataCache[$this->fontkey][$offset])) {
			$GlyphByClass = $this->LuDataCache[$this->fontkey][$offset];
		} else {
			$this->seek($offset);
			$ClassFormat = $this->read_ushort();
			$GlyphClass = [];
			//      $GlyphByClass = array(0=>array());  // NB This forces an index[0]
			if ($ClassFormat == 1) {
				$StartGlyph = $this->read_ushort();
				$GlyphCount = $this->read_ushort();
				for ($i = 0; $i < $GlyphCount; $i++) {
					$GlyphClass[$i]['startGlyphID'] = $StartGlyph + $i;
					$GlyphClass[$i]['endGlyphID'] = $StartGlyph + $i;
					$GlyphClass[$i]['class'] = $this->read_ushort();
					for ($g = $GlyphClass[$i]['startGlyphID']; $g <= $GlyphClass[$i]['endGlyphID']; $g++) {
						$GlyphByClass[$GlyphClass[$i]['class']][] = $this->glyphToChar($g);
					}
				}
			} elseif ($ClassFormat == 2) {
				$tableCount = $this->read_ushort();
				for ($i = 0; $i < $tableCount; $i++) {
					$GlyphClass[$i]['startGlyphID'] = $this->read_ushort();
					$GlyphClass[$i]['endGlyphID'] = $this->read_ushort();
					$GlyphClass[$i]['class'] = $this->read_ushort();
					for ($g = $GlyphClass[$i]['startGlyphID']; $g <= $GlyphClass[$i]['endGlyphID']; $g++) {
						$GlyphByClass[$GlyphClass[$i]['class']][] = $this->glyphToChar($g);
					}
				}
			}
			ksort($GlyphByClass);
			$this->LuDataCache[$this->fontkey][$offset] = $GlyphByClass;
		}
		return $GlyphByClass;
	}

	private function count_bits($n)
	{
		for ($c = 0; $n; $c++) {
			$n &= $n - 1; // clear the least significant bit set
		}
		return $c;
	}

	private function _getValueRecord($ValueFormat)
	{
	// Common ValueRecord for GPOS
		// Only returns 3 possible: $vra['XPlacement'] $vra['YPlacement'] $vra['XAdvance']
		$vra = [];
		// Horizontal adjustment for placement - in design units
		if (($ValueFormat & 0x0001) == 0x0001) {
			$vra['XPlacement'] = $this->read_short();
		}
		// Vertical adjustment for placement - in design units
		if (($ValueFormat & 0x0002) == 0x0002) {
			$vra['YPlacement'] = $this->read_short();
		}
		// Horizontal adjustment for advance - in design units (only used for horizontal writing)
		if (($ValueFormat & 0x0004) == 0x0004) {
			$vra['XAdvance'] = $this->read_short();
		}
		// Vertical adjustment for advance - in design units (only used for vertical writing)
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

	private function _getAnchorTable($offset = 0)
	{
		if ($offset) {
			$this->seek($offset);
		}
		$AnchorFormat = $this->read_ushort();
		$XCoordinate = $this->read_short();
		$YCoordinate = $this->read_short();
		// Format 2 specifies additional link to contour point; Format 3 additional Device table
		return [$XCoordinate, $YCoordinate];
	}

	private function _getMarkRecord($offset, $MarkPos)
	{
		$this->seek($offset);
		$MarkCount = $this->read_ushort();
		$this->skip($MarkPos * 4);
		$Class = $this->read_ushort();
		$MarkAnchor = $offset + $this->read_ushort();  // = Offset to anchor table
		list($x, $y) = $this->_getAnchorTable($MarkAnchor);
		$MarkRecord = ['Class' => $Class, 'AnchorX' => $x, 'AnchorY' => $y];
		return $MarkRecord;
	}

	private function _getGCOMignoreString($flag, $MarkFilteringSet)
	{
		// If ignoreFlag set, combine all ignore glyphs into -> "(?:( 0FBA1| 0FBA2| 0FBA3)*)"
		// else "()"
		// for Input - set on secondary Lookup table if in Context, and set Backtrack and Lookahead on Context Lookup
		$str = "";
		$ignoreflag = 0;

		// Flag & 0xFF?? = MarkAttachmentType
		if ($flag & 0xFF00) {
			// "a lookup must ignore any mark glyphs that are not in the specified mark attachment class"
			// $this->MarkAttachmentType is already adjusted for this i.e. contains all Marks except those in the MarkAttachmentClassDef table
			$MarkAttachmentType = $flag >> 8;
			$ignoreflag = $flag;
			$str = $this->MarkAttachmentType[$MarkAttachmentType];
		}

		// Flag & 0x0010 = UseMarkFilteringSet
		if ($flag & 0x0010) {
			throw new \Mpdf\MpdfException("This font [" . $this->fontkey . "] contains MarkGlyphSets - Not tested yet");
			// Change also in ttfontsuni.php
			if ($MarkFilteringSet == '') {
				throw new \Mpdf\MpdfException("This font [" . $this->fontkey . "] contains MarkGlyphSets - but MarkFilteringSet not set");
			}
			$str = $this->MarkGlyphSets[$MarkFilteringSet];
		}

		// If Ignore Marks set, supercedes any above
		// Flag & 0x0008 = Ignore Marks - (unless already done with MarkAttachmentType)
		if (($flag & 0x0008) == 0x0008 && ($flag & 0xFF00) == 0) {
			$ignoreflag = 8;
			$str = $this->GlyphClassMarks;
		}

		// Flag & 0x0004 = Ignore Ligatures
		if (($flag & 0x0004) == 0x0004) {
			$ignoreflag += 4;
			if ($str) {
				$str .= "|";
			}
			$str .= $this->GlyphClassLigatures;
		}
		// Flag & 0x0002 = Ignore BaseGlyphs
		if (($flag & 0x0002) == 0x0002) {
			$ignoreflag += 2;
			if ($str) {
				$str .= "|";
			}
			$str .= $this->GlyphClassBases;
		}
		if ($str) {
			return "((?:(?:" . $str . "))*)";
		} else {
			return "()";
		}
	}

	private function _checkGCOMignore($flag, $glyph, $MarkFilteringSet)
	{
		$ignore = false;
		// Flag & 0x0008 = Ignore Marks - (unless already done with MarkAttachmentType)
		if (($flag & 0x0008 && ($flag & 0xFF00) == 0) && strpos($this->GlyphClassMarks, $glyph)) {
			$ignore = true;
		}
		if (($flag & 0x0004) && strpos($this->GlyphClassLigatures, $glyph)) {
			$ignore = true;
		}
		if (($flag & 0x0002) && strpos($this->GlyphClassBases, $glyph)) {
			$ignore = true;
		}
		// Flag & 0xFF?? = MarkAttachmentType
		if ($flag & 0xFF00) {
			// "a lookup must ignore any mark glyphs that are not in the specified mark attachment class"
			// $this->MarkAttachmentType is already adjusted for this i.e. contains all Marks except those in the MarkAttachmentClassDef table
			if (strpos($this->MarkAttachmentType[($flag >> 8)], $glyph)) {
				$ignore = true;
			}
		}
		// Flag & 0x0010 = UseMarkFilteringSet
		if (($flag & 0x0010) && strpos($this->MarkGlyphSets[$MarkFilteringSet], $glyph)) {
			$ignore = true;
		}
		return $ignore;
	}

	/**
	 * Bidi algorithm
	 *
	 * These functions are called from mpdf after GSUB/GPOS has taken place
	 * At this stage the bidi-type is in string form
	 *
	 * Bidirectional Character Types
	 * =============================
	 * Type  Description     General Scope
	 * Strong
	 * L     Left-to-Right       LRM, most alphabetic, syllabic, Han ideographs, non-European or non-Arabic digits, ...
	 * LRE   Left-to-Right Embedding LRE
	 * LRO   Left-to-Right Override  LRO
	 * R     Right-to-Left       RLM, Hebrew alphabet, and related punctuation
	 * AL    Right-to-Left Arabic    Arabic, Thaana, and Syriac alphabets, most punctuation specific to those scripts, ...
	 * RLE   Right-to-Left Embedding RLE
	 * RLO   Right-to-Left Override  RLO
	 * Weak
	 * PDF   Pop Directional Format      PDF
	 * EN    European Number             European digits, Eastern Arabic-Indic digits, ...
	 * ES    European Number Separator   Plus sign, minus sign
	 * ET    European Number Terminator  Degree sign, currency symbols, ...
	 * AN    Arabic Number           Arabic-Indic digits, Arabic decimal and thousands separators, ...
	 * CS    Common Number Separator     Colon, comma, full stop (period), No-break space, ...
	 * NSM   Nonspacing Mark             Characters marked Mn (Nonspacing_Mark) and Me (Enclosing_Mark) in the Unicode Character Database
	 * BN    Boundary Neutral            Default ignorables, non-characters, and control characters, other than those explicitly given other types.
	 * Neutral
	 * B     Paragraph Separator     Paragraph separator, appropriate Newline Functions, higher-level protocol paragraph determination
	 * S     Segment Separator   Tab
	 * WS    Whitespace          Space, figure space, line separator, form feed, General Punctuation spaces, ...
	 * ON    Other Neutrals      All other characters, including OBJECT REPLACEMENT CHARACTER
	 */
	public function bidiSort($ta, $str, $dir, &$chunkOTLdata, $useGPOS)
	{

		$pel = 0; // paragraph embedding level
		$maxlevel = 0;
		$numchars = count($chunkOTLdata['char_data']);

		// Set the initial paragraph embedding level
		if ($dir == 'rtl') {
			$pel = 1;
		} else {
			$pel = 0;
		}

		// X1. Begin by setting the current embedding level to the paragraph embedding level. Set the directional override status to neutral.
		// Current Embedding Level
		$cel = $pel;
		// directional override status (-1 is Neutral)
		$dos = -1;
		$remember = [];

		// Array of characters data
		$chardata = [];

		// Process each character iteratively, applying rules X2 through X9. Only embedding levels from 0 to 61 are valid in this phase.
		// In the resolution of levels in rules I1 and I2, the maximum embedding level of 62 can be reached.
		for ($i = 0; $i < $numchars; ++$i) {
			if ($chunkOTLdata['char_data'][$i]['uni'] == 8235) { // RLE
				// X2. With each RLE, compute the least greater odd embedding level.
				//  a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to neutral.
				//  b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
				$next_level = $cel + ($cel % 2) + 1;
				if ($next_level < 62) {
					$remember[] = ['num' => 8235, 'cel' => $cel, 'dos' => $dos];
					$cel = $next_level;
					$dos = -1;
				}
			} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8234) { // LRE
				// X3. With each LRE, compute the least greater even embedding level.
				//  a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to neutral.
				//  b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
				$next_level = $cel + 2 - ($cel % 2);
				if ($next_level < 62) {
					$remember[] = ['num' => 8234, 'cel' => $cel, 'dos' => $dos];
					$cel = $next_level;
					$dos = -1;
				}
			} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8238) { // RLO
				// X4. With each RLO, compute the least greater odd embedding level.
				//  a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to right-to-left.
				//  b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
				$next_level = $cel + ($cel % 2) + 1;
				if ($next_level < 62) {
					$remember[] = ['num' => 8238, 'cel' => $cel, 'dos' => $dos];
					$cel = $next_level;
					$dos = Ucdn::BIDI_CLASS_R;
				}
			} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8237) { // LRO
				// X5. With each LRO, compute the least greater even embedding level.
				//  a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to left-to-right.
				//  b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
				$next_level = $cel + 2 - ($cel % 2);
				if ($next_level < 62) {
					$remember[] = ['num' => 8237, 'cel' => $cel, 'dos' => $dos];
					$cel = $next_level;
					$dos = Ucdn::BIDI_CLASS_L;
				}
			} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8236) { // PDF
				// X7. With each PDF, determine the matching embedding or override code. If there was a valid matching code, restore (pop) the last remembered (pushed) embedding level and directional override.
				if (count($remember)) {
					$last = count($remember) - 1;
					if (($remember[$last]['num'] == 8235) || ($remember[$last]['num'] == 8234) || ($remember[$last]['num'] == 8238) ||
						($remember[$last]['num'] == 8237)) {
						$match = array_pop($remember);
						$cel = $match['cel'];
						$dos = $match['dos'];
					}
				}
			} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 10) { // NEW LINE
				// Reset to start values
				$cel = $pel;
				$dos = -1;
				$remember = [];
			} else {
				// X6. For all types besides RLE, LRE, RLO, LRO, and PDF:
				//  a. Set the level of the current character to the current embedding level.
				//  b. When the directional override status is not neutral, reset the current character type to directional override status.
				if ($dos != -1) {
					$chardir = $dos;
				} else {
					$chardir = $chunkOTLdata['char_data'][$i]['bidi_class'];
				}
				// stores string characters and other information
				if (isset($chunkOTLdata['GPOSinfo'][$i])) {
					$gpos = $chunkOTLdata['GPOSinfo'][$i];
				} else {
					$gpos = '';
				}
				$chardata[] = ['char' => $chunkOTLdata['char_data'][$i]['uni'], 'level' => $cel, 'type' => $chardir, 'group' => $chunkOTLdata['group'][$i], 'GPOSinfo' => $gpos];
			}
		}

		$numchars = count($chardata);

		// X8. All explicit directional embeddings and overrides are completely terminated at the end of each paragraph.
		// Paragraph separators are not included in the embedding.
		// X9. Remove all RLE, LRE, RLO, LRO, and PDF codes.
		// This is effectively done by only saving other codes to chardata
		// X10. Determine the start-of-sequence (sor) and end-of-sequence (eor) types, either L or R, for each isolating run sequence. These depend on the higher of the two levels on either side of the sequence boundary:
		// For sor, compare the level of the first character in the sequence with the level of the character preceding it in the paragraph or if there is none, with the paragraph embedding level.
		// For eor, compare the level of the last character in the sequence with the level of the character following it in the paragraph or if there is none, with the paragraph embedding level.
		// If the higher level is odd, the sor or eor is R; otherwise, it is L.

		$prelevel = $pel;
		$postlevel = $pel;
		$cel = $prelevel; // current embedding level
		for ($i = 0; $i < $numchars; ++$i) {
			$level = $chardata[$i]['level'];
			if ($i == 0) {
				$left = $prelevel;
			} else {
				$left = $chardata[$i - 1]['level'];
			}
			if ($i == ($numchars - 1)) {
				$right = $postlevel;
			} else {
				$right = $chardata[$i + 1]['level'];
			}
			$chardata[$i]['sor'] = max($left, $level) % 2 ? Ucdn::BIDI_CLASS_R : Ucdn::BIDI_CLASS_L;
			$chardata[$i]['eor'] = max($right, $level) % 2 ? Ucdn::BIDI_CLASS_R : Ucdn::BIDI_CLASS_L;
		}



		// 3.3.3 Resolving Weak Types
		// Weak types are now resolved one level run at a time. At level run boundaries where the type of the character on the other side of the boundary is required, the type assigned to sor or eor is used.
		// Nonspacing marks are now resolved based on the previous characters.
		// W1. Examine each nonspacing mark (NSM) in the level run, and change the type of the NSM to the type of the previous character. If the NSM is at the start of the level run, it will get the type of sor.
		for ($i = 0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_NSM) {
				if ($i == 0 || $chardata[$i]['level'] != $chardata[$i - 1]['level']) {
					$chardata[$i]['type'] = $chardata[$i]['sor'];
				} else {
					$chardata[$i]['type'] = $chardata[($i - 1)]['type'];
				}
			}
		}

		// W2. Search backward from each instance of a European number until the first strong type (R, L, AL, or sor) is found. If an AL is found, change the type of the European number to Arabic number.
		$prevlevel = -1;
		$levcount = 0;
		for ($i = 0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN) {
				$found = false;
				for ($j = $levcount; $j >= 0; $j--) {
					if ($chardata[$j]['type'] == Ucdn::BIDI_CLASS_AL) {
						$chardata[$i]['type'] = Ucdn::BIDI_CLASS_AN;
						$found = true;
						break;
					} elseif (($chardata[$j]['type'] == Ucdn::BIDI_CLASS_L) || ($chardata[$j]['type'] == Ucdn::BIDI_CLASS_R)) {
						$found = true;
						break;
					}
				}
			}
			if ($chardata[$i]['level'] != $prevlevel) {
				$levcount = 0;
			} else {
				++$levcount;
			}
			$prevlevel = $chardata[$i]['level'];
		}

		// W3. Change all ALs to R.
		for ($i = 0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_AL) {
				$chardata[$i]['type'] = Ucdn::BIDI_CLASS_R;
			}
		}

		// W4. A single European separator between two European numbers changes to a European number. A single common separator between two numbers of the same type changes to that type.
		for ($i = 1; $i < $numchars; ++$i) {
			if (($i + 1) < $numchars && $chardata[($i)]['level'] == $chardata[($i + 1)]['level'] && $chardata[($i)]['level'] == $chardata[($i - 1)]['level']) {
				if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ES && $chardata[($i - 1)]['type'] == Ucdn::BIDI_CLASS_EN && $chardata[($i + 1)]['type'] == Ucdn::BIDI_CLASS_EN) {
					$chardata[$i]['type'] = Ucdn::BIDI_CLASS_EN;
				} elseif ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_CS && $chardata[($i - 1)]['type'] == Ucdn::BIDI_CLASS_EN && $chardata[($i + 1)]['type'] == Ucdn::BIDI_CLASS_EN) {
					$chardata[$i]['type'] = Ucdn::BIDI_CLASS_EN;
				} elseif ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_CS && $chardata[($i - 1)]['type'] == Ucdn::BIDI_CLASS_AN && $chardata[($i + 1)]['type'] == Ucdn::BIDI_CLASS_AN) {
					$chardata[$i]['type'] = Ucdn::BIDI_CLASS_AN;
				}
			}
		}

		// W5. A sequence of European terminators adjacent to European numbers changes to all European numbers.
		for ($i = 0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ET) {
				if ($i > 0 && $chardata[($i - 1)]['type'] == Ucdn::BIDI_CLASS_EN && $chardata[($i)]['level'] == $chardata[($i - 1)]['level']) {
					$chardata[$i]['type'] = Ucdn::BIDI_CLASS_EN;
				} else {
					$j = $i + 1;
					while ($j < $numchars && $chardata[$j]['level'] == $chardata[$i]['level']) {
						if ($chardata[$j]['type'] == Ucdn::BIDI_CLASS_EN) {
							$chardata[$i]['type'] = Ucdn::BIDI_CLASS_EN;
							break;
						} elseif ($chardata[$j]['type'] != Ucdn::BIDI_CLASS_ET) {
							break;
						}
						++$j;
					}
				}
			}
		}

		// W6. Otherwise, separators and terminators change to Other Neutral.
		for ($i = 0; $i < $numchars; ++$i) {
			if (($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ET) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ES) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_CS)) {
				$chardata[$i]['type'] = Ucdn::BIDI_CLASS_ON;
			}
		}

		//W7. Search backward from each instance of a European number until the first strong type (R, L, or sor) is found. If an L is found, then change the type of the European number to L.
		for ($i = 0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN) {
				if ($i == 0) { // Start of Level run
					if ($chardata[$i]['sor'] == Ucdn::BIDI_CLASS_L) {
						$chardata[$i]['type'] = $chardata[$i]['sor'];
					}
				} else {
					for ($j = $i - 1; $j >= 0; $j--) {
						if ($chardata[$j]['level'] != $chardata[$i]['level']) { // Level run boundary
							if ($chardata[$j + 1]['sor'] == Ucdn::BIDI_CLASS_L) {
								$chardata[$i]['type'] = $chardata[$j + 1]['sor'];
							}
							break;
						} elseif ($chardata[$j]['type'] == Ucdn::BIDI_CLASS_L) {
							$chardata[$i]['type'] = Ucdn::BIDI_CLASS_L;
							break;
						} elseif ($chardata[$j]['type'] == Ucdn::BIDI_CLASS_R) {
							break;
						}
					}
				}
			}
		}

		// N1. A sequence of neutrals takes the direction of the surrounding strong text if the text on both sides has the same direction. European and Arabic numbers act as if they were R in terms of their influence on neutrals. Start-of-level-run (sor) and end-of-level-run (eor) are used at level run boundaries.
		for ($i = 0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ON || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_WS) {
				$left = -1;
				// LEFT
				if ($i == 0) {  // first char
					$left = $chardata[($i)]['sor'];
				} elseif ($chardata[($i - 1)]['level'] != $chardata[($i)]['level']) {  // run boundary
					$left = $chardata[($i)]['sor'];
				} elseif ($chardata[($i - 1)]['type'] == Ucdn::BIDI_CLASS_L) {
					$left = Ucdn::BIDI_CLASS_L;
				} elseif ($chardata[($i - 1)]['type'] == Ucdn::BIDI_CLASS_R || $chardata[($i - 1)]['type'] == Ucdn::BIDI_CLASS_EN || $chardata[($i - 1)]['type'] == Ucdn::BIDI_CLASS_AN) {
					$left = Ucdn::BIDI_CLASS_R;
				}
				// RIGHT
				$right = -1;
				$j = $i;
				// move to the right of any following neutrals OR hit a run boundary
				while (($chardata[$j]['type'] == Ucdn::BIDI_CLASS_ON || $chardata[$j]['type'] == Ucdn::BIDI_CLASS_WS) && $j <= ($numchars - 1)) {
					if ($j == ($numchars - 1)) {  // last char
						$right = $chardata[($j)]['eor'];
						break;
					} elseif ($chardata[($j + 1)]['level'] != $chardata[($j)]['level']) {  // run boundary
						$right = $chardata[($j)]['eor'];
						break;
					} elseif ($chardata[($j + 1)]['type'] == Ucdn::BIDI_CLASS_L) {
						$right = Ucdn::BIDI_CLASS_L;
						break;
					} elseif ($chardata[($j + 1)]['type'] == Ucdn::BIDI_CLASS_R || $chardata[($j + 1)]['type'] == Ucdn::BIDI_CLASS_EN || $chardata[($j + 1)]['type'] == Ucdn::BIDI_CLASS_AN) {
						$right = Ucdn::BIDI_CLASS_R;
						break;
					}
					$j++;
				}
				if ($left > -1 && $left == $right) {
					$chardata[$i]['orig_type'] = $chardata[$i]['type']; // Need to store the original 'WS' for reference in L1 below
					$chardata[$i]['type'] = $left;
				}
			}
		}

		// N2. Any remaining neutrals take the embedding direction
		for ($i = 0; $i < $numchars; ++$i) {
			if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ON || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_WS) {
				$chardata[$i]['type'] = ($chardata[$i]['level'] % 2) ? Ucdn::BIDI_CLASS_R : Ucdn::BIDI_CLASS_L;
				$chardata[$i]['orig_type'] = $chardata[$i]['type']; // Need to store the original 'WS' for reference in L1 below
			}
		}

		// I1. For all characters with an even (left-to-right) embedding direction, those of type R go up one level and those of type AN or EN go up two levels.
		// I2. For all characters with an odd (right-to-left) embedding direction, those of type L, EN or AN go up one level.
		for ($i = 0; $i < $numchars; ++$i) {
			$odd = $chardata[$i]['level'] % 2;
			if ($odd) {
				if (($chardata[$i]['type'] == Ucdn::BIDI_CLASS_L) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_AN) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN)) {
					$chardata[$i]['level'] += 1;
				}
			} else {
				if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_R) {
					$chardata[$i]['level'] += 1;
				} elseif (($chardata[$i]['type'] == Ucdn::BIDI_CLASS_AN) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN)) {
					$chardata[$i]['level'] += 2;
				}
			}
			$maxlevel = max($chardata[$i]['level'], $maxlevel);
		}

		// NB
		//  Separate into lines at this point************
		//
		// L1. On each line, reset the embedding level of the following characters to the paragraph embedding level:
		//  1. Segment separators (Tab) 'S',
		//  2. Paragraph separators 'B',
		//  3. Any sequence of whitespace characters 'WS' preceding a segment separator or paragraph separator, and
		//  4. Any sequence of whitespace characters 'WS' at the end of the line.
		//  The types of characters used here are the original types, not those modified by the previous phase cf N1 and N2*******
		//  Because a Paragraph Separator breaks lines, there will be at most one per line, at the end of that line.

		for ($i = ($numchars - 1); $i > 0; $i--) {
			if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_WS || (isset($chardata[$i]['orig_type']) && $chardata[$i]['orig_type'] == Ucdn::BIDI_CLASS_WS)) {
				$chardata[$i]['level'] = $pel;
			} else {
				break;
			}
		}


		// L2. From the highest level found in the text to the lowest odd level on each line, including intermediate levels not actually present in the text, reverse any contiguous sequence of characters that are at that level or higher.
		for ($j = $maxlevel; $j > 0; $j--) {
			$ordarray = [];
			$revarr = [];
			$onlevel = false;
			for ($i = 0; $i < $numchars; ++$i) {
				if ($chardata[$i]['level'] >= $j) {
					$onlevel = true;

					// L4. A character is depicted by a mirrored glyph if and only if (a) the resolved directionality of that character is R, and (b) the Bidi_Mirrored property value of that character is true.
					if (isset(Ucdn::$mirror_pairs[$chardata[$i]['char']]) && $chardata[$i]['type'] == Ucdn::BIDI_CLASS_R) {
						$chardata[$i]['char'] = Ucdn::$mirror_pairs[$chardata[$i]['char']];
					}

					$revarr[] = $chardata[$i];
				} else {
					if ($onlevel) {
						$revarr = array_reverse($revarr);
						$ordarray = array_merge($ordarray, $revarr);
						$revarr = [];
						$onlevel = false;
					}
					$ordarray[] = $chardata[$i];
				}
			}
			if ($onlevel) {
				$revarr = array_reverse($revarr);
				$ordarray = array_merge($ordarray, $revarr);
			}
			$chardata = $ordarray;
		}

		$group = '';
		$e = '';
		$GPOS = [];
		$cctr = 0;
		$rtl_content = 0x0;
		foreach ($chardata as $cd) {
			$e .= UtfString::code2utf($cd['char']);
			$group .= $cd['group'];
			if ($useGPOS && is_array($cd['GPOSinfo'])) {
				$GPOS[$cctr] = $cd['GPOSinfo'];
				$GPOS[$cctr]['wDir'] = ($cd['level'] % 2) ? 'RTL' : 'LTR';
			}
			if ($cd['type'] == Ucdn::BIDI_CLASS_L) {
				$rtl_content |= 1;
			} elseif ($cd['type'] == Ucdn::BIDI_CLASS_R) {
				$rtl_content |= 2;
			}
			$cctr++;
		}


		$chunkOTLdata['group'] = $group;
		if ($useGPOS) {
			$chunkOTLdata['GPOSinfo'] = $GPOS;
		}

		return [$e, $rtl_content];
	}

	/**
	 * The following versions for BidiSort work on amalgamated chunks to process the whole paragraph
	 *
	 * Firstly set the level in the OTLdata - called from fn printbuffer() [_bidiPrepare]
	 * Secondly re-order - called from fn writeFlowingBlock and FinishFlowingBlock, when already divided into lines. [_bidiReorder]
	 */
	public function bidiPrepare(&$para, $dir)
	{

		// Set the initial paragraph embedding level
		$pel = 0; // paragraph embedding level
		if ($dir == 'rtl') {
			$pel = 1;
		}

		// X1. Begin by setting the current embedding level to the paragraph embedding level. Set the directional override status to neutral.
		// Current Embedding Level
		$cel = $pel;
		// directional override status (-1 is Neutral)
		$dos = -1;
		$remember = [];
		$controlchars = false;
		$strongrtl = false;
		$diid = 0; // direction isolate ID
		$dictr = 0; // direction isolate counter
		// Process each character iteratively, applying rules X2 through X9. Only embedding levels from 0 to 61 are valid in this phase.
		// In the resolution of levels in rules I1 and I2, the maximum embedding level of 62 can be reached.
		$numchunks = count($para);
		for ($nc = 0; $nc < $numchunks; $nc++) {
			$chunkOTLdata = & $para[$nc][18];

			$numchars = count($chunkOTLdata['char_data']);
			for ($i = 0; $i < $numchars; ++$i) {
				if ($chunkOTLdata['char_data'][$i]['uni'] == 8235) { // RLE
					// X2. With each RLE, compute the least greater odd embedding level.
					//  a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to neutral.
					//  b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
					$next_level = $cel + ($cel % 2) + 1;
					if ($next_level < 62) {
						$remember[] = ['num' => 8235, 'cel' => $cel, 'dos' => $dos];
						$cel = $next_level;
						$dos = -1;
						$controlchars = true;
					}
				} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8234) { // LRE
					// X3. With each LRE, compute the least greater even embedding level.
					//  a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to neutral.
					//  b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
					$next_level = $cel + 2 - ($cel % 2);
					if ($next_level < 62) {
						$remember[] = ['num' => 8234, 'cel' => $cel, 'dos' => $dos];
						$cel = $next_level;
						$dos = -1;
						$controlchars = true;
					}
				} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8238) { // RLO
					// X4. With each RLO, compute the least greater odd embedding level.
					//  a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to right-to-left.
					//  b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
					$next_level = $cel + ($cel % 2) + 1;
					if ($next_level < 62) {
						$remember[] = ['num' => 8238, 'cel' => $cel, 'dos' => $dos];
						$cel = $next_level;
						$dos = Ucdn::BIDI_CLASS_R;
						$controlchars = true;
					}
				} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8237) { // LRO
					// X5. With each LRO, compute the least greater even embedding level.
					//  a. If this new level would be valid, then this embedding code is valid. Remember (push) the current embedding level and override status. Reset the current level to this new level, and reset the override status to left-to-right.
					//  b. If the new level would not be valid, then this code is invalid. Do not change the current level or override status.
					$next_level = $cel + 2 - ($cel % 2);
					if ($next_level < 62) {
						$remember[] = ['num' => 8237, 'cel' => $cel, 'dos' => $dos];
						$cel = $next_level;
						$dos = Ucdn::BIDI_CLASS_L;
						$controlchars = true;
					}
				} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8236) { // PDF
					// X7. With each PDF, determine the matching embedding or override code. If there was a valid matching code, restore (pop) the last remembered (pushed) embedding level and directional override.
					if (count($remember)) {
						$last = count($remember) - 1;
						if (($remember[$last]['num'] == 8235) || ($remember[$last]['num'] == 8234) || ($remember[$last]['num'] == 8238) ||
							($remember[$last]['num'] == 8237)) {
							$match = array_pop($remember);
							$cel = $match['cel'];
							$dos = $match['dos'];
						}
					}
				} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8294 || $chunkOTLdata['char_data'][$i]['uni'] == 8295 ||
					$chunkOTLdata['char_data'][$i]['uni'] == 8296) { // LRI // RLI // FSI
					// X5a. With each RLI:
					// X5b. With each LRI:
					// X5c. With each FSI, apply rules P2 and P3 for First Strong character
					//  Set the RLI/LRI/FSI embedding level to the embedding level of the last entry on the directional status stack.
					if ($dos != -1) {
						$chardir = $dos;
					} else {
						$chardir = $chunkOTLdata['char_data'][$i]['bidi_class'];
					}
					$chunkOTLdata['char_data'][$i]['level'] = $cel;
					$chunkOTLdata['char_data'][$i]['type'] = $chardir;
					$chunkOTLdata['char_data'][$i]['diid'] = $diid;

					$fsi = '';
					// X5c. With each FSI, apply rules P2 and P3 within the isolate run for First Strong character
					if ($chunkOTLdata['char_data'][$i]['uni'] == 8296) { // FSI
						$lvl = 0;
						$nc2 = $nc;
						$i2 = $i;
						while (!($nc2 == ($numchunks - 1) && $i2 == ((count($para[$nc2][18]['char_data'])) - 1))) {  // while not at end of last chunk
							$i2++;
							if ($i2 >= count($para[$nc2][18]['char_data'])) {
								$nc2++;
								$i2 = 0;
							}
							if ($lvl > 0) {
								continue;
							}
							if ($para[$nc2][18]['char_data'][$i2]['uni'] == 8294 || $para[$nc2][18]['char_data'][$i2]['uni'] == 8295 || $para[$nc2][18]['char_data'][$i2]['uni'] == 8296) {
								$lvl++;
								continue;
							}
							if ($para[$nc2][18]['char_data'][$i2]['uni'] == 8297) {
								$lvl--;
								if ($lvl < 0) {
									break;
								}
							}
							if ($para[$nc2][18]['char_data'][$i2]['bidi_class'] === Ucdn::BIDI_CLASS_L || $para[$nc2][18]['char_data'][$i2]['bidi_class'] == Ucdn::BIDI_CLASS_AL || $para[$nc2][18]['char_data'][$i2]['bidi_class'] === Ucdn::BIDI_CLASS_R) {
								$fsi = $para[$nc2][18]['char_data'][$i2]['bidi_class'];
								break;
							}
						}
						// if fsi not found, fsi is same as paragraph embedding level
						if (!$fsi && $fsi !== 0) {
							if ($pel == 1) {
								$fsi = Ucdn::BIDI_CLASS_R;
							} else {
								$fsi = Ucdn::BIDI_CLASS_L;
							}
						}
					}

					if ($chunkOTLdata['char_data'][$i]['uni'] == 8294 || $fsi === Ucdn::BIDI_CLASS_L) { // LRI or FSI-L
						//  Compute the least even embedding level greater than the embedding level of the last entry on the directional status stack.
						$next_level = $cel + 2 - ($cel % 2);
					} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8295 || $fsi == Ucdn::BIDI_CLASS_R || $fsi == Ucdn::BIDI_CLASS_AL) { // RLI or FSI-R
						//  Compute the least odd embedding level greater than the embedding level of the last entry on the directional status stack.
						$next_level = $cel + ($cel % 2) + 1;
					}


					//  Increment the isolate count by one, and push an entry consisting of the new embedding level,
					//  neutral directional override status, and true directional isolate status onto the directional status stack.
					$remember[] = ['num' => $chunkOTLdata['char_data'][$i]['uni'], 'cel' => $cel, 'dos' => $dos, 'diid' => $diid];
					$cel = $next_level;
					$dos = -1;
					$diid = ++$dictr; // Set new direction isolate ID after incrementing direction isolate counter

					$controlchars = true;
				} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 8297) { // PDI
					// X6a. With each PDI, perform the following steps:
					//  Pop the last entry from the directional status stack and decrement the isolate count by one.
					while (count($remember)) {
						$last = count($remember) - 1;
						if (($remember[$last]['num'] == 8294) || ($remember[$last]['num'] == 8295) || ($remember[$last]['num'] == 8296)) {
							$match = array_pop($remember);
							$cel = $match['cel'];
							$dos = $match['dos'];
							$diid = $match['diid'];
							break;
						} // End/close any open embedding states not explicitly closed during the isolate
						elseif (($remember[$last]['num'] == 8235) || ($remember[$last]['num'] == 8234) || ($remember[$last]['num'] == 8238) ||
							($remember[$last]['num'] == 8237)) {
							$match = array_pop($remember);
						}
					}
					//  In all cases, set the PDI’s level to the embedding level of the last entry on the directional status stack left after the steps above.
					//  NB The level assigned to an isolate initiator is always the same as that assigned to the matching PDI.
					if ($dos != -1) {
						$chardir = $dos;
					} else {
						$chardir = $chunkOTLdata['char_data'][$i]['bidi_class'];
					}
					$chunkOTLdata['char_data'][$i]['level'] = $cel;
					$chunkOTLdata['char_data'][$i]['type'] = $chardir;
					$chunkOTLdata['char_data'][$i]['diid'] = $diid;
					$controlchars = true;
				} elseif ($chunkOTLdata['char_data'][$i]['uni'] == 10) { // NEW LINE
					// Reset to start values
					$cel = $pel;
					$dos = -1;
					$remember = [];
				} else {
					// X6. For all types besides RLE, LRE, RLO, LRO, and PDF:
					//  a. Set the level of the current character to the current embedding level.
					//  b. When the directional override status is not neutral, reset the current character type to directional override status.
					if ($dos != -1) {
						$chardir = $dos;
					} else {
						$chardir = $chunkOTLdata['char_data'][$i]['bidi_class'];
						if ($chardir == Ucdn::BIDI_CLASS_R || $chardir == Ucdn::BIDI_CLASS_AL) {
							$strongrtl = true;
						}
					}
					$chunkOTLdata['char_data'][$i]['level'] = $cel;
					$chunkOTLdata['char_data'][$i]['type'] = $chardir;
					$chunkOTLdata['char_data'][$i]['diid'] = $diid;
				}
			}
			// X8. All explicit directional embeddings and overrides are completely terminated at the end of each paragraph.
			// Paragraph separators are not included in the embedding.
			// X9. Remove all RLE, LRE, RLO, LRO, and PDF codes.
			if ($controlchars) {
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x80\xaa");
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x80\xab");
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x80\xac");
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x80\xad");
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x80\xae");
				preg_replace("/\x{202a}-\x{202e}/u", '', $para[$nc][0]);
			}
		}

		// Remove any blank chunks made by removing directional codes
		$numchunks = count($para);
		for ($nc = ($numchunks - 1); $nc >= 0; $nc--) {
			if (count($para[$nc][18]['char_data']) == 0) {
				array_splice($para, $nc, 1);
			}
		}
		if ($dir != 'rtl' && !$strongrtl && !$controlchars) {
			return;
		}

		$numchunks = count($para);

		// X10. Determine the start-of-sequence (sor) and end-of-sequence (eor) types, either L or R, for each isolating run sequence. These depend on the higher of the two levels on either side of the sequence boundary:
		// For sor, compare the level of the first character in the sequence with the level of the character preceding it in the paragraph or if there is none, with the paragraph embedding level.
		// For eor, compare the level of the last character in the sequence with the level of the character following it in the paragraph or if there is none, with the paragraph embedding level.
		// If the higher level is odd, the sor or eor is R; otherwise, it is L.

		for ($ir = 0; $ir <= $dictr; $ir++) {
			$prelevel = $pel;
			$postlevel = $pel;
			$firstchar = true;
			for ($nc = 0; $nc < $numchunks; $nc++) {
				$chardata = & $para[$nc][18]['char_data'];
				$numchars = count($chardata);
				for ($i = 0; $i < $numchars; ++$i) {
					if (!isset($chardata[$i]['diid']) || $chardata[$i]['diid'] != $ir) {
						continue;
					} // Ignore characters in a different isolate run
					$right = $postlevel;
					$nc2 = $nc;
					$i2 = $i;
					while (!($nc2 == ($numchunks - 1) && $i2 == ((count($para[$nc2][18]['char_data'])) - 1))) {  // while not at end of last chunk
						$i2++;
						if ($i2 >= count($para[$nc2][18]['char_data'])) {
							$nc2++;
							$i2 = 0;
						}

						if (isset($para[$nc2][18]['char_data'][$i2]['diid']) && $para[$nc2][18]['char_data'][$i2]['diid'] == $ir) {
							$right = $para[$nc2][18]['char_data'][$i2]['level'];
							break;
						}
					}

					$level = $chardata[$i]['level'];
					if ($firstchar || $level != $prelevel) {
						$chardata[$i]['sor'] = max($prelevel, $level) % 2 ? Ucdn::BIDI_CLASS_R : Ucdn::BIDI_CLASS_L;
					}
					if (($nc == ($numchunks - 1) && $i == ($numchars - 1)) || $level != $right) {
						$chardata[$i]['eor'] = max($right, $level) % 2 ? Ucdn::BIDI_CLASS_R : Ucdn::BIDI_CLASS_L;
					}
					$prelevel = $level;
					$firstchar = false;
				}
			}
		}


		// 3.3.3 Resolving Weak Types
		// Weak types are now resolved one level run at a time. At level run boundaries where the type of the character on the other side of the boundary is required, the type assigned to sor or eor is used.
		// Nonspacing marks are now resolved based on the previous characters.
		// W1. Examine each nonspacing mark (NSM) in the level run, and change the type of the NSM to the type of the previous character. If the NSM is at the start of the level run, it will get the type of sor.
		for ($ir = 0; $ir <= $dictr; $ir++) {
			$prevtype = 0;
			for ($nc = 0; $nc < $numchunks; $nc++) {
				$chardata = & $para[$nc][18]['char_data'];
				$numchars = count($chardata);
				for ($i = 0; $i < $numchars; ++$i) {
					if (!isset($chardata[$i]['diid']) || $chardata[$i]['diid'] != $ir) {
						continue;
					} // Ignore characters in a different isolate run
					if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_NSM) {
						if (isset($chardata[$i]['sor'])) {
							$chardata[$i]['type'] = $chardata[$i]['sor'];
						} else {
							$chardata[$i]['type'] = $prevtype;
						}
					}
					$prevtype = $chardata[$i]['type'];
				}
			}
		}

		// W2. Search backward from each instance of a European number until the first strong type (R, L, AL or sor) is found. If an AL is found, change the type of the European number to Arabic number.
		for ($ir = 0; $ir <= $dictr; $ir++) {
			$laststrongtype = -1;
			for ($nc = 0; $nc < $numchunks; $nc++) {
				$chardata = & $para[$nc][18]['char_data'];
				$numchars = count($chardata);
				for ($i = 0; $i < $numchars; ++$i) {
					if (!isset($chardata[$i]['diid']) || $chardata[$i]['diid'] != $ir) {
						continue;
					} // Ignore characters in a different isolate run
					if (isset($chardata[$i]['sor'])) {
						$laststrongtype = $chardata[$i]['sor'];
					}
					if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN && $laststrongtype == Ucdn::BIDI_CLASS_AL) {
						$chardata[$i]['type'] = Ucdn::BIDI_CLASS_AN;
					}
					if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_L || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_R || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_AL) {
						$laststrongtype = $chardata[$i]['type'];
					}
				}
			}
		}


		// W3. Change all ALs to R.
		for ($nc = 0; $nc < $numchunks; $nc++) {
			$chardata = & $para[$nc][18]['char_data'];
			$numchars = count($chardata);
			for ($i = 0; $i < $numchars; ++$i) {
				if (isset($chardata[$i]['type']) && $chardata[$i]['type'] == Ucdn::BIDI_CLASS_AL) {
					$chardata[$i]['type'] = Ucdn::BIDI_CLASS_R;
				}
			}
		}


		// W4. A single European separator between two European numbers changes to a European number. A single common separator between two numbers of the same type changes to that type.
		for ($ir = 0; $ir <= $dictr; $ir++) {
			$prevtype = -1;
			$nexttype = -1;
			for ($nc = 0; $nc < $numchunks; $nc++) {
				$chardata = & $para[$nc][18]['char_data'];
				$numchars = count($chardata);
				for ($i = 0; $i < $numchars; ++$i) {
					if (!isset($chardata[$i]['diid']) || $chardata[$i]['diid'] != $ir) {
						continue;
					} // Ignore characters in a different isolate run
					// Get next type
					$nexttype = -1;
					$nc2 = $nc;
					$i2 = $i;
					while (!($nc2 == ($numchunks - 1) && $i2 == ((count($para[$nc2][18]['char_data'])) - 1))) {  // while not at end of last chunk
						$i2++;
						if ($i2 >= count($para[$nc2][18]['char_data'])) {
							$nc2++;
							$i2 = 0;
						}

						if (isset($para[$nc2][18]['char_data'][$i2]['diid']) && $para[$nc2][18]['char_data'][$i2]['diid'] == $ir) {
							$nexttype = $para[$nc2][18]['char_data'][$i2]['type'];
							break;
						}
					}

					if (!isset($chardata[$i]['sor']) && !isset($chardata[$i]['eor'])) {
						if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ES && $prevtype == Ucdn::BIDI_CLASS_EN && $nexttype == Ucdn::BIDI_CLASS_EN) {
							$chardata[$i]['type'] = Ucdn::BIDI_CLASS_EN;
						} elseif ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_CS && $prevtype == Ucdn::BIDI_CLASS_EN && $nexttype == Ucdn::BIDI_CLASS_EN) {
							$chardata[$i]['type'] = Ucdn::BIDI_CLASS_EN;
						} elseif ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_CS && $prevtype == Ucdn::BIDI_CLASS_AN && $nexttype == Ucdn::BIDI_CLASS_AN) {
							$chardata[$i]['type'] = Ucdn::BIDI_CLASS_AN;
						}
					}
					$prevtype = $chardata[$i]['type'];
				}
			}
		}

		// W5. A sequence of European terminators adjacent to European numbers changes to all European numbers.
		for ($ir = 0; $ir <= $dictr; $ir++) {
			$prevtype = -1;
			$nexttype = -1;
			for ($nc = 0; $nc < $numchunks; $nc++) {
				$chardata = & $para[$nc][18]['char_data'];
				$numchars = count($chardata);
				for ($i = 0; $i < $numchars; ++$i) {
					if (!isset($chardata[$i]['diid']) || $chardata[$i]['diid'] != $ir) {
						continue;
					} // Ignore characters in a different isolate run
					if (isset($chardata[$i]['sor'])) {
						$prevtype = $chardata[$i]['sor'];
					}

					if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ET) {
						if ($prevtype == Ucdn::BIDI_CLASS_EN) {
							$chardata[$i]['type'] = Ucdn::BIDI_CLASS_EN;
						} elseif (!isset($chardata[$i]['eor'])) {
							$nexttype = -1;
							$nc2 = $nc;
							$i2 = $i;
							while (!($nc2 == ($numchunks - 1) && $i2 == ((count($para[$nc2][18]['char_data'])) - 1))) { // while not at end of last chunk
								$i2++;
								if ($i2 >= count($para[$nc2][18]['char_data'])) {
									$nc2++;
									$i2 = 0;
								}
								if (!isset($para[$nc2][18]['char_data'][$i2]['diid']) || $para[$nc2][18]['char_data'][$i2]['diid'] != $ir) {
									continue;
								}
								$nexttype = $para[$nc2][18]['char_data'][$i2]['type'];
								if (isset($para[$nc2][18]['char_data'][$i2]['sor'])) {
									break;
								}
								if ($nexttype == Ucdn::BIDI_CLASS_EN) {
									$chardata[$i]['type'] = Ucdn::BIDI_CLASS_EN;
									break;
								} elseif ($nexttype != Ucdn::BIDI_CLASS_ET) {
									break;
								}
							}
						}
					}
					$prevtype = $chardata[$i]['type'];
				}
			}
		}

		// W6. Otherwise, separators and terminators change to Other Neutral.
		for ($nc = 0; $nc < $numchunks; $nc++) {
			$chardata = & $para[$nc][18]['char_data'];
			$numchars = count($chardata);
			for ($i = 0; $i < $numchars; ++$i) {
				if (isset($chardata[$i]['type']) && (($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ET) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ES) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_CS))) {
					$chardata[$i]['type'] = Ucdn::BIDI_CLASS_ON;
				}
			}
		}

		//W7. Search backward from each instance of a European number until the first strong type (R, L, or sor) is found. If an L is found, then change the type of the European number to L.
		for ($ir = 0; $ir <= $dictr; $ir++) {
			$laststrongtype = -1;
			for ($nc = 0; $nc < $numchunks; $nc++) {
				$chardata = & $para[$nc][18]['char_data'];
				$numchars = count($chardata);
				for ($i = 0; $i < $numchars; ++$i) {
					if (!isset($chardata[$i]['diid']) || $chardata[$i]['diid'] != $ir) {
						continue;
					} // Ignore characters in a different isolate run
					if (isset($chardata[$i]['sor'])) {
						$laststrongtype = $chardata[$i]['sor'];
					}
					if (isset($chardata[$i]['type']) && $chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN && $laststrongtype == Ucdn::BIDI_CLASS_L) {
						$chardata[$i]['type'] = Ucdn::BIDI_CLASS_L;
					}
					if (isset($chardata[$i]['type']) && ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_L || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_R || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_AL)) {
						$laststrongtype = $chardata[$i]['type'];
					}
				}
			}
		}

		// N1. A sequence of neutrals takes the direction of the surrounding strong text if the text on both sides has the same direction. European and Arabic numbers act as if they were R in terms of their influence on neutrals. Start-of-level-run (sor) and end-of-level-run (eor) are used at level run boundaries.
		for ($ir = 0; $ir <= $dictr; $ir++) {
			$laststrongtype = -1;
			for ($nc = 0; $nc < $numchunks; $nc++) {
				$chardata = & $para[$nc][18]['char_data'];
				$numchars = count($chardata);
				for ($i = 0; $i < $numchars; ++$i) {
					if (!isset($chardata[$i]['diid']) || $chardata[$i]['diid'] != $ir) {
						continue;
					} // Ignore characters in a different isolate run
					if (isset($chardata[$i]['sor'])) {
						$laststrongtype = $chardata[$i]['sor'];
					}
					if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ON || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_WS) {
						$left = -1;
						// LEFT
						if ($laststrongtype == Ucdn::BIDI_CLASS_R || $laststrongtype == Ucdn::BIDI_CLASS_EN || $laststrongtype == Ucdn::BIDI_CLASS_AN) {
							$left = Ucdn::BIDI_CLASS_R;
						} elseif ($laststrongtype == Ucdn::BIDI_CLASS_L) {
							$left = Ucdn::BIDI_CLASS_L;
						}
						// RIGHT
						$right = -1;
						// move to the right of any following neutrals OR hit a run boundary

						if (isset($chardata[$i]['eor'])) {
							$right = $chardata[$i]['eor'];
						} else {
							$nexttype = -1;
							$nc2 = $nc;
							$i2 = $i;
							while (!($nc2 == ($numchunks - 1) && $i2 == ((count($para[$nc2][18]['char_data'])) - 1))) { // while not at end of last chunk
								$i2++;
								if ($i2 >= count($para[$nc2][18]['char_data'])) {
									$nc2++;
									$i2 = 0;
								}
								if (!isset($para[$nc2][18]['char_data'][$i2]['diid']) || $para[$nc2][18]['char_data'][$i2]['diid'] != $ir) {
									continue;
								}
								$nexttype = $para[$nc2][18]['char_data'][$i2]['type'];
								if ($nexttype == Ucdn::BIDI_CLASS_R || $nexttype == Ucdn::BIDI_CLASS_EN || $nexttype == Ucdn::BIDI_CLASS_AN) {
									$right = Ucdn::BIDI_CLASS_R;
									break;
								} elseif ($nexttype == Ucdn::BIDI_CLASS_L) {
									$right = Ucdn::BIDI_CLASS_L;
									break;
								} elseif (isset($para[$nc2][18]['char_data'][$i2]['eor'])) {
									$right = $para[$nc2][18]['char_data'][$i2]['eor'];
									break;
								}
							}
						}

						if ($left > -1 && $left == $right) {
							$chardata[$i]['orig_type'] = $chardata[$i]['type']; // Need to store the original 'WS' for reference in L1 below
							$chardata[$i]['type'] = $left;
						}
					} elseif ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_L || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_R || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_AN) {
						$laststrongtype = $chardata[$i]['type'];
					}
				}
			}
		}

		// N2. Any remaining neutrals take the embedding direction
		for ($nc = 0; $nc < $numchunks; $nc++) {
			$chardata = & $para[$nc][18]['char_data'];
			$numchars = count($chardata);
			for ($i = 0; $i < $numchars; ++$i) {
				if (isset($chardata[$i]['type']) && ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_ON || $chardata[$i]['type'] == Ucdn::BIDI_CLASS_WS)) {
					$chardata[$i]['orig_type'] = $chardata[$i]['type']; // Need to store the original 'WS' for reference in L1 below
					$chardata[$i]['type'] = ($chardata[$i]['level'] % 2) ? Ucdn::BIDI_CLASS_R : Ucdn::BIDI_CLASS_L;
				}
			}
		}

		// I1. For all characters with an even (left-to-right) embedding direction, those of type R go up one level and those of type AN or EN go up two levels.
		// I2. For all characters with an odd (right-to-left) embedding direction, those of type L, EN or AN go up one level.
		for ($nc = 0; $nc < $numchunks; $nc++) {
			$chardata = & $para[$nc][18]['char_data'];
			$numchars = count($chardata);
			for ($i = 0; $i < $numchars; ++$i) {
				if (isset($chardata[$i]['level'])) {
					$odd = $chardata[$i]['level'] % 2;
					if ($odd) {
						if (($chardata[$i]['type'] == Ucdn::BIDI_CLASS_L) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_AN) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN)) {
							$chardata[$i]['level'] += 1;
						}
					} else {
						if ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_R) {
							$chardata[$i]['level'] += 1;
						} elseif (($chardata[$i]['type'] == Ucdn::BIDI_CLASS_AN) || ($chardata[$i]['type'] == Ucdn::BIDI_CLASS_EN)) {
							$chardata[$i]['level'] += 2;
						}
					}
				}
			}
		}

		// Remove Isolate formatters
		$numchunks = count($para);
		if ($controlchars) {
			for ($nc = 0; $nc < $numchunks; $nc++) {
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x81\xa6");
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x81\xa7");
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x81\xa8");
				$this->removeChar($para[$nc][0], $para[$nc][18], "\xe2\x81\xa9");
				preg_replace("/\x{2066}-\x{2069}/u", '', $para[$nc][0]);
			}
			// Remove any blank chunks made by removing directional codes
			for ($nc = ($numchunks - 1); $nc >= 0; $nc--) {
				if (count($para[$nc][18]['char_data']) == 0) {
					array_splice($para, $nc, 1);
				}
			}
		}
	}

	/**
	 * Reorder, once divided into lines
	 */
	public function bidiReorder(&$chunkorder, &$content, &$cOTLdata, $blockdir)
	{

		$bidiData = [];

		// First combine into one array (and get the highest level in use)
		$numchunks = count($content);
		$maxlevel = 0;
		for ($nc = 0; $nc < $numchunks; $nc++) {
			$numchars = isset($cOTLdata[$nc]['char_data']) ? count($cOTLdata[$nc]['char_data']) : 0;
			for ($i = 0; $i < $numchars; ++$i) {
				$carac = [];
				if (isset($cOTLdata[$nc]['GPOSinfo'][$i])) {
					$carac['GPOSinfo'] = $cOTLdata[$nc]['GPOSinfo'][$i];
				}
				$carac['uni'] = $cOTLdata[$nc]['char_data'][$i]['uni'];
				if (isset($cOTLdata[$nc]['char_data'][$i]['type'])) {
					$carac['type'] = $cOTLdata[$nc]['char_data'][$i]['type'];
				}
				if (isset($cOTLdata[$nc]['char_data'][$i]['level'])) {
					$carac['level'] = $cOTLdata[$nc]['char_data'][$i]['level'];
				}
				if (isset($cOTLdata[$nc]['char_data'][$i]['orig_type'])) {
					$carac['orig_type'] = $cOTLdata[$nc]['char_data'][$i]['orig_type'];
				}
				$carac['group'] = $cOTLdata[$nc]['group'][$i];
				$carac['chunkid'] = $chunkorder[$nc]; // gives font id and/or object ID

				$maxlevel = max((isset($carac['level']) ? $carac['level'] : 0), $maxlevel);
				$bidiData[] = $carac;
			}
		}
		if ($maxlevel == 0) {
			return;
		}

		$numchars = count($bidiData);

		// L1. On each line, reset the embedding level of the following characters to the paragraph embedding level:
		//  1. Segment separators (Tab) 'S',
		//  2. Paragraph separators 'B',
		//  3. Any sequence of whitespace characters 'WS' preceding a segment separator or paragraph separator, and
		//  4. Any sequence of whitespace characters 'WS' at the end of the line.
		//  The types of characters used here are the original types, not those modified by the previous phase cf N1 and N2*******
		//  Because a Paragraph Separator breaks lines, there will be at most one per line, at the end of that line.
		// Set the initial paragraph embedding level
		if ($blockdir == 'rtl') {
			$pel = 1;
		} else {
			$pel = 0;
		}

		for ($i = ($numchars - 1); $i > 0; $i--) {
			if ($bidiData[$i]['type'] == Ucdn::BIDI_CLASS_WS || (isset($bidiData[$i]['orig_type']) && $bidiData[$i]['orig_type'] == Ucdn::BIDI_CLASS_WS)) {
				$bidiData[$i]['level'] = $pel;
			} else {
				break;
			}
		}

		// L2. From the highest level found in the text to the lowest odd level on each line, including intermediate levels not actually present in the text, reverse any contiguous sequence of characters that are at that level or higher.
		for ($j = $maxlevel; $j > 0; $j--) {
			$ordarray = [];
			$revarr = [];
			$onlevel = false;
			for ($i = 0; $i < $numchars; ++$i) {
				if ($bidiData[$i]['level'] >= $j) {
					$onlevel = true;
					// L4. A character is depicted by a mirrored glyph if and only if (a) the resolved directionality of that character is R, and (b) the Bidi_Mirrored property value of that character is true.
					if (isset(Ucdn::$mirror_pairs[$bidiData[$i]['uni']]) && $bidiData[$i]['type'] == Ucdn::BIDI_CLASS_R) {
						$bidiData[$i]['uni'] = Ucdn::$mirror_pairs[$bidiData[$i]['uni']];
					}

					$revarr[] = $bidiData[$i];
				} else {
					if ($onlevel) {
						$revarr = array_reverse($revarr);
						$ordarray = array_merge($ordarray, $revarr);
						$revarr = [];
						$onlevel = false;
					}
					$ordarray[] = $bidiData[$i];
				}
			}
			if ($onlevel) {
				$revarr = array_reverse($revarr);
				$ordarray = array_merge($ordarray, $revarr);
			}
			$bidiData = $ordarray;
		}

		$content = [];
		$cOTLdata = [];
		$chunkorder = [];

		$nc = -1; // New chunk order ID
		$chunkid = -1;

		foreach ($bidiData as $carac) {
			if ($carac['chunkid'] != $chunkid) {
				$nc++;
				$chunkorder[$nc] = $carac['chunkid'];
				$cctr = 0;
				$content[$nc] = '';
				$cOTLdata[$nc]['group'] = '';
			}
			if ($carac['uni'] != 0xFFFC) {   // Object replacement character (65532)
				$content[$nc] .= UtfString::code2utf($carac['uni']);
				$cOTLdata[$nc]['group'] .= $carac['group'];
				if (!empty($carac['GPOSinfo'])) {
					if (isset($carac['GPOSinfo'])) {
						$cOTLdata[$nc]['GPOSinfo'][$cctr] = $carac['GPOSinfo'];
					}
					$cOTLdata[$nc]['GPOSinfo'][$cctr]['wDir'] = ($carac['level'] % 2) ? 'RTL' : 'LTR';
				}
			}
			$chunkid = $carac['chunkid'];
			$cctr++;
		}
	}

	public function splitOTLdata(&$cOTLdata, $OTLcutoffpos, $OTLrestartpos = '')
	{
		if (!$OTLrestartpos) {
			$OTLrestartpos = $OTLcutoffpos;
		}
		$newOTLdata = ['GPOSinfo' => [], 'char_data' => []];
		$newOTLdata['group'] = substr($cOTLdata['group'], $OTLrestartpos);
		$cOTLdata['group'] = substr($cOTLdata['group'], 0, $OTLcutoffpos);

		if (isset($cOTLdata['GPOSinfo']) && $cOTLdata['GPOSinfo']) {
			foreach ($cOTLdata['GPOSinfo'] as $k => $val) {
				if ($k >= $OTLrestartpos) {
					$newOTLdata['GPOSinfo'][($k - $OTLrestartpos)] = $val;
				}
				if ($k >= $OTLcutoffpos) {
					unset($cOTLdata['GPOSinfo'][$k]);
					//$cOTLdata['GPOSinfo'][$k] = array();
				}
			}
		}
		if (isset($cOTLdata['char_data'])) {
			$newOTLdata['char_data'] = array_slice($cOTLdata['char_data'], $OTLrestartpos);
			array_splice($cOTLdata['char_data'], $OTLcutoffpos);
		}

		// Not necessary - easier to debug
		if (isset($cOTLdata['GPOSinfo'])) {
			ksort($cOTLdata['GPOSinfo']);
		}
		if (isset($newOTLdata['GPOSinfo'])) {
			ksort($newOTLdata['GPOSinfo']);
		}

		return $newOTLdata;
	}

	public function sliceOTLdata($OTLdata, $pos, $len)
	{
		$newOTLdata = ['GPOSinfo' => [], 'char_data' => []];
		$newOTLdata['group'] = substr($OTLdata['group'], $pos, $len);

		if ($OTLdata['GPOSinfo']) {
			foreach ($OTLdata['GPOSinfo'] as $k => $val) {
				if ($k >= $pos && $k < ($pos + $len)) {
					$newOTLdata['GPOSinfo'][($k - $pos)] = $val;
				}
			}
		}

		if (isset($OTLdata['char_data'])) {
			$newOTLdata['char_data'] = array_slice($OTLdata['char_data'], $pos, $len);
		}

		// Not necessary - easier to debug
		if ($newOTLdata['GPOSinfo']) {
			ksort($newOTLdata['GPOSinfo']);
		}

		return $newOTLdata;
	}

	/**
	 * Remove one or more occurrences of $char (single character) from $txt and adjust OTLdata
	 */
	public function removeChar(&$txt, &$cOTLdata, $char)
	{
		while (mb_strpos($txt, $char, 0, $this->mpdf->mb_enc) !== false) {
			$pos = mb_strpos($txt, $char, 0, $this->mpdf->mb_enc);
			$newGPOSinfo = [];
			$cOTLdata['group'] = substr_replace($cOTLdata['group'], '', $pos, 1);
			if ($cOTLdata['GPOSinfo']) {
				foreach ($cOTLdata['GPOSinfo'] as $k => $val) {
					if ($k > $pos) {
						$newGPOSinfo[($k - 1)] = $val;
					} elseif ($k != $pos) {
						$newGPOSinfo[$k] = $val;
					}
				}
				$cOTLdata['GPOSinfo'] = $newGPOSinfo;
			}
			if (isset($cOTLdata['char_data'])) {
				array_splice($cOTLdata['char_data'], $pos, 1);
			}

			$txt = preg_replace("/" . $char . "/", '', $txt, 1);
		}
	}

	/**
	 * Remove one or more occurrences of $char (single character) from $txt and adjust OTLdata
	 */
	public function replaceSpace(&$txt, &$cOTLdata)
	{
		$char = chr(194) . chr(160); // NBSP
		while (mb_strpos($txt, $char, 0, $this->mpdf->mb_enc) !== false) {
			$pos = mb_strpos($txt, $char, 0, $this->mpdf->mb_enc);
			if ($cOTLdata['char_data'][$pos]['uni'] == 160) {
				$cOTLdata['char_data'][$pos]['uni'] = 32;
			}
			$txt = preg_replace("/" . $char . "/", ' ', $txt, 1);
		}
	}

	public function trimOTLdata(&$cOTLdata, $Left = true, $Right = true)
	{
		$len = (!is_array($cOTLdata) || $cOTLdata['char_data'] === null) ? 0 : count($cOTLdata['char_data']);
		$nLeft = 0;
		$nRight = 0;
		for ($i = 0; $i < $len; $i++) {
			if ($cOTLdata['char_data'][$i]['uni'] == 32 || $cOTLdata['char_data'][$i]['uni'] == 12288) {
				$nLeft++;
			} // 12288 = 0x3000 = CJK space
			else {
				break;
			}
		}
		for ($i = ($len - 1); $i >= 0; $i--) {
			if ($cOTLdata['char_data'][$i]['uni'] == 32 || $cOTLdata['char_data'][$i]['uni'] == 12288) {
				$nRight++;
			} // 12288 = 0x3000 = CJK space
			else {
				break;
			}
		}

		// Trim Right
		if ($Right && $nRight) {
			$cOTLdata['group'] = substr($cOTLdata['group'], 0, strlen($cOTLdata['group']) - $nRight);
			if ($cOTLdata['GPOSinfo']) {
				foreach ($cOTLdata['GPOSinfo'] as $k => $val) {
					if ($k >= $len - $nRight) {
						unset($cOTLdata['GPOSinfo'][$k]);
					}
				}
			}
			if (isset($cOTLdata['char_data'])) {
				for ($i = 0; $i < $nRight; $i++) {
					array_pop($cOTLdata['char_data']);
				}
			}
		}
		// Trim Left
		if ($Left && $nLeft) {
			$cOTLdata['group'] = substr($cOTLdata['group'], $nLeft);
			if ($cOTLdata['GPOSinfo']) {
				$newPOSinfo = [];
				foreach ($cOTLdata['GPOSinfo'] as $k => $val) {
					if ($k >= $nLeft) {
						$newPOSinfo[$k - $nLeft] = $cOTLdata['GPOSinfo'][$k];
					}
				}
				$cOTLdata['GPOSinfo'] = $newPOSinfo;
			}
			if (isset($cOTLdata['char_data'])) {
				for ($i = 0; $i < $nLeft; $i++) {
					array_shift($cOTLdata['char_data']);
				}
			}
		}
	}

	////////////////////////////////////////////////////////////////
	//////////         GENERAL OTL FUNCTIONS       /////////////////
	////////////////////////////////////////////////////////////////

	private function glyphToChar($gid)
	{
		return (ord($this->glyphIDtoUni[$gid * 3]) << 16) + (ord($this->glyphIDtoUni[$gid * 3 + 1]) << 8) + ord($this->glyphIDtoUni[$gid * 3 + 2]);
	}

	private function unicode_hex($unicode_dec)
	{
		return (str_pad(strtoupper(dechex($unicode_dec)), 5, '0', STR_PAD_LEFT));
	}

	private function seek($pos)
	{
		$this->_pos = $pos;
	}

	private function skip($delta)
	{
		$this->_pos += $delta;
	}

	private function read_short()
	{
		$a = (ord($this->ttfOTLdata[$this->_pos]) << 8) + ord($this->ttfOTLdata[$this->_pos + 1]);
		if ($a & (1 << 15)) {
			$a = ($a - (1 << 16));
		}
		$this->_pos += 2;
		return $a;
	}

	private function read_ushort()
	{
		$a = (ord($this->ttfOTLdata[$this->_pos]) << 8) + ord($this->ttfOTLdata[$this->_pos + 1]);
		$this->_pos += 2;
		return $a;
	}

	private function _getCoverageGID()
	{
		// Called from Lookup Type 1, Format 1 - returns glyphIDs rather than hexstrings
		// Need to do this separately to cache separately
		// Otherwise the same as fn below _getCoverage
		$offset = $this->_pos;
		if (isset($this->LuDataCache[$this->fontkey]['GID'][$offset])) {
			$g = $this->LuDataCache[$this->fontkey]['GID'][$offset];
		} else {
			$g = [];
			$CoverageFormat = $this->read_ushort();
			if ($CoverageFormat == 1) {
				$CoverageGlyphCount = $this->read_ushort();
				for ($gid = 0; $gid < $CoverageGlyphCount; $gid++) {
					$glyphID = $this->read_ushort();
					$g[] = $glyphID;
				}
			}
			if ($CoverageFormat == 2) {
				$RangeCount = $this->read_ushort();
				for ($r = 0; $r < $RangeCount; $r++) {
					$start = $this->read_ushort();
					$end = $this->read_ushort();
					$StartCoverageIndex = $this->read_ushort(); // n/a
					for ($glyphID = $start; $glyphID <= $end; $glyphID++) {
						$g[] = $glyphID;
					}
				}
			}
			$this->LuDataCache[$this->fontkey]['GID'][$offset] = $g;
		}
		return $g;
	}

	private function _getCoverage()
	{
		$offset = $this->_pos;
		if (isset($this->LuDataCache[$this->fontkey][$offset])) {
			$g = $this->LuDataCache[$this->fontkey][$offset];
		} else {
			$g = [];
			$CoverageFormat = $this->read_ushort();
			if ($CoverageFormat == 1) {
				$CoverageGlyphCount = $this->read_ushort();
				for ($gid = 0; $gid < $CoverageGlyphCount; $gid++) {
					$glyphID = $this->read_ushort();
					$g[] = $this->unicode_hex($this->glyphToChar($glyphID));
				}
			}
			if ($CoverageFormat == 2) {
				$RangeCount = $this->read_ushort();
				for ($r = 0; $r < $RangeCount; $r++) {
					$start = $this->read_ushort();
					$end = $this->read_ushort();
					$StartCoverageIndex = $this->read_ushort(); // n/a
					for ($glyphID = $start; $glyphID <= $end; $glyphID++) {
						$g[] = $this->unicode_hex($this->glyphToChar($glyphID));
					}
				}
			}
			$this->LuDataCache[$this->fontkey][$offset] = $g;
		}
		return $g;
	}

	private function _getClasses($offset)
	{
		if (isset($this->LuDataCache[$this->fontkey][$offset])) {
			$GlyphByClass = $this->LuDataCache[$this->fontkey][$offset];
		} else {
			$this->seek($offset);
			$ClassFormat = $this->read_ushort();
			$GlyphByClass = [];
			if ($ClassFormat == 1) {
				$StartGlyph = $this->read_ushort();
				$GlyphCount = $this->read_ushort();
				for ($i = 0; $i < $GlyphCount; $i++) {
					$startGlyphID = $StartGlyph + $i;
					$endGlyphID = $StartGlyph + $i;
					$class = $this->read_ushort();
					// Note: Font FreeSerif , tag "blws"
					// $BacktrackClasses[0] is defined ? a mistake in the font ???
					// Let's ignore for now
					if ($class > 0) {
						for ($g = $startGlyphID; $g <= $endGlyphID; $g++) {
							if ($this->glyphToChar($g)) {
								$GlyphByClass[$class][$this->glyphToChar($g)] = 1;
							}
						}
					}
				}
			} elseif ($ClassFormat == 2) {
				$tableCount = $this->read_ushort();
				for ($i = 0; $i < $tableCount; $i++) {
					$startGlyphID = $this->read_ushort();
					$endGlyphID = $this->read_ushort();
					$class = $this->read_ushort();
					// Note: Font FreeSerif , tag "blws"
					// $BacktrackClasses[0] is defined ? a mistake in the font ???
					// Let's ignore for now
					if ($class > 0) {
						for ($g = $startGlyphID; $g <= $endGlyphID; $g++) {
							if ($this->glyphToChar($g)) {
								$GlyphByClass[$class][$this->glyphToChar($g)] = 1;
							}
						}
					}
				}
			}
			$this->LuDataCache[$this->fontkey][$offset] = $GlyphByClass;
		}
		return $GlyphByClass;
	}

	private function _getOTLscriptTag($ScriptLang, $scripttag, $scriptblock, $shaper, $useOTL, $mode)
	{
		// ScriptLang is the array of available script/lang tags supported by the font
		// $scriptblock is the (number/code) for the script of the actual text string based on Unicode properties (Ucdn::$uni_scriptblock)
		// $scripttag is the default tag derived from $scriptblock
		/*
		  http://www.microsoft.com/typography/otspec/ttoreg.htm
		  http://www.microsoft.com/typography/otspec/scripttags.htm

		  Values for useOTL

		  Bit   dn  hn  Value
		  1 1   0x0001  GSUB/GPOS - Latin scripts
		  2 2   0x0002  GSUB/GPOS - Cyrillic scripts
		  3 4   0x0004  GSUB/GPOS - Greek scripts
		  4 8   0x0008  GSUB/GPOS - CJK scripts (excluding Hangul-Jamo)
		  5 16  0x0010  (Reserved)
		  6 32  0x0020  (Reserved)
		  7 64  0x0040  (Reserved)
		  8 128 0x0080  GSUB/GPOS - All other scripts (including all RTL scripts, complex scripts with shapers etc)

		  NB If change for RTL - cf. function magic_reverse_dir in mpdf.php to update

		 */


		if ($scriptblock == Ucdn::SCRIPT_LATIN) {
			if (!($useOTL & 0x01)) {
				return ['', false];
			}
		} elseif ($scriptblock == Ucdn::SCRIPT_CYRILLIC) {
			if (!($useOTL & 0x02)) {
				return ['', false];
			}
		} elseif ($scriptblock == Ucdn::SCRIPT_GREEK) {
			if (!($useOTL & 0x04)) {
				return ['', false];
			}
		} elseif ($scriptblock >= Ucdn::SCRIPT_HIRAGANA && $scriptblock <= Ucdn::SCRIPT_YI) {
			if (!($useOTL & 0x08)) {
				return ['', false];
			}
		} else {
			if (!($useOTL & 0x80)) {
				return ['', false];
			}
		}

		//  If availabletags includes scripttag - choose
		if (isset($ScriptLang[$scripttag])) {
			return [$scripttag, false];
		}

		//  If INDIC (or Myanmar) and available tag not includes new version, check if includes old version & choose old version
		if ($shaper) {
			switch ($scripttag) {
				case 'bng2':
					if (isset($ScriptLang['beng'])) {
						return ['beng', true];
					}
					// fallthrough
				case 'dev2':
					if (isset($ScriptLang['deva'])) {
						return ['deva', true];
					}
					// fallthrough
				case 'gjr2':
					if (isset($ScriptLang['gujr'])) {
						return ['gujr', true];
					}
					// fallthrough
				case 'gur2':
					if (isset($ScriptLang['guru'])) {
						return ['guru', true];
					}
					// fallthrough
				case 'knd2':
					if (isset($ScriptLang['knda'])) {
						return ['knda', true];
					}
					// fallthrough
				case 'mlm2':
					if (isset($ScriptLang['mlym'])) {
						return ['mlym', true];
					}
					// fallthrough
				case 'ory2':
					if (isset($ScriptLang['orya'])) {
						return ['orya', true];
					}
					// fallthrough
				case 'tml2':
					if (isset($ScriptLang['taml'])) {
						return ['taml', true];
					}
					// fallthrough
				case 'tel2':
					if (isset($ScriptLang['telu'])) {
						return ['telu', true];
					}
					// fallthrough
				case 'mym2':
					if (isset($ScriptLang['mymr'])) {
						return ['mymr', true];
					}
			}
		}

		//  choose DFLT if present
		if (isset($ScriptLang['DFLT'])) {
			return ['DFLT', false];
		}
		//  else choose dflt if present
		if (isset($ScriptLang['dflt'])) {
			return ['dflt', false];
		}
		//  else return no scriptTag
		if (isset($ScriptLang['latn'])) {
			return ['latn', false];
		}
		//  else return no scriptTag
		return ['', false];
	}

	// LangSys tags
	private function _getOTLLangTag($ietf, $available)
	{
		// http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
		// http://www.microsoft.com/typography/otspec/languagetags.htm
		// IETF tag = e.g. en-US, und-Arab, sr-Cyrl cf. class LangToFont
		if ($available == '') {
			return '';
		}
		$tags = preg_split('/-/', $ietf);
		$lang = '';
		$country = '';
		$script = '';
		$lang = strtolower($tags[0]);
		if (isset($tags[1]) && $tags[1]) {
			if (strlen($tags[1]) == 2) {
				$country = strtolower($tags[1]);
			}
		}
		if (isset($tags[2]) && $tags[2]) {
			$country = strtolower($tags[2]);
		}

		if ($lang != '' && isset(Ucdn::$ot_languages[$lang])) {
			$langsys = Ucdn::$ot_languages[$lang];
		} elseif ($lang != '' && $country != '' && isset(Ucdn::$ot_languages[$lang . '' . $country])) {
			$langsys = Ucdn::$ot_languages[$lang . '' . $country];
		} else {
			$langsys = "DFLT";
		}
		if (strpos($available, $langsys) === false) {
			if (strpos($available, "DFLT") !== false) {
				return "DFLT";
			} else {
				return '';
			}
		}
		return $langsys;
	}

	private function _dumpproc($GPOSSUB, $lookupID, $subtable, $Type, $Format, $ptr, $currGlyph, $level)
	{
		echo '<div style="padding-left: ' . ($level * 2) . 'em;">';
		echo $GPOSSUB . ' LookupID #' . $lookupID . ' Subtable#' . $subtable . ' Type: ' . $Type . ' Format: ' . $Format . '<br />';
		echo '<div style="font-family:monospace">';
		echo 'Glyph position: ' . $ptr . ' Current Glyph: ' . $currGlyph . '<br />';

		for ($i = 0; $i < count($this->OTLdata); $i++) {
			if ($i == $ptr) {
				echo '<b>';
			}
			echo $this->OTLdata[$i]['hex'] . ' ';
			if ($i == $ptr) {
				echo '</b>';
			}
		}
		echo '<br />';

		for ($i = 0; $i < count($this->OTLdata); $i++) {
			if ($i == $ptr) {
				echo '<b>';
			}
			echo str_pad($this->OTLdata[$i]['uni'], 5) . ' ';
			if ($i == $ptr) {
				echo '</b>';
			}
		}
		echo '<br />';

		if ($GPOSSUB == 'GPOS') {
			for ($i = 0; $i < count($this->OTLdata); $i++) {
				if (!empty($this->OTLdata[$i]['GPOSinfo'])) {
					echo $this->OTLdata[$i]['hex'] . ' &#x' . $this->OTLdata[$i]['hex'] . '; ';
					print_r($this->OTLdata[$i]['GPOSinfo']);
					echo ' ';
				}
			}
		}

		echo '</div>';
		echo '</div>';
	}
}
