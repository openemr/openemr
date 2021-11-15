<?php

namespace Mpdf\Language;

use Mpdf\Ucdn;

class ScriptToLanguage implements \Mpdf\Language\ScriptToLanguageInterface
{

	private $scriptDelimiterMap = [
		'viet' => "\x{01A0}\x{01A1}\x{01AF}\x{01B0}\x{1EA0}-\x{1EF1}",
		'persian' => "\x{067E}\x{0686}\x{0698}\x{06AF}",
		'urdu' => "\x{0679}\x{0688}\x{0691}\x{06BA}\x{06BE}\x{06C1}\x{06D2}",
		'pashto' => "\x{067C}\x{0681}\x{0685}\x{0689}\x{0693}\x{0696}\x{069A}\x{06BC}\x{06D0}", // ? and U+06AB, U+06CD
		'sindhi' => "\x{067A}\x{067B}\x{067D}\x{067F}\x{0680}\x{0684}\x{068D}\x{068A}\x{068F}\x{068C}\x{0687}\x{0683}\x{0699}\x{06AA}\x{06A6}\x{06BB}\x{06B1}\x{06B3}",
	];

	private $scriptToLanguageMap = [
		/* European */
		Ucdn::SCRIPT_LATIN => 'und-Latn',
		Ucdn::SCRIPT_ARMENIAN => 'hy',
		Ucdn::SCRIPT_CYRILLIC => 'und-Cyrl',
		Ucdn::SCRIPT_GEORGIAN => 'ka',
		Ucdn::SCRIPT_GREEK => 'el',
		Ucdn::SCRIPT_COPTIC => 'cop',
		Ucdn::SCRIPT_GOTHIC => 'got',
		Ucdn::SCRIPT_CYPRIOT => 'und-Cprt',
		Ucdn::SCRIPT_GLAGOLITIC => 'und-Glag',
		Ucdn::SCRIPT_LINEAR_B => 'und-Linb',
		Ucdn::SCRIPT_OGHAM => 'und-Ogam',
		Ucdn::SCRIPT_OLD_ITALIC => 'und-Ital',
		Ucdn::SCRIPT_RUNIC => 'und-Runr',
		Ucdn::SCRIPT_SHAVIAN => 'und-Shaw',
		/* African */
		Ucdn::SCRIPT_ETHIOPIC => 'und-Ethi',
		Ucdn::SCRIPT_NKO => 'nqo',
		Ucdn::SCRIPT_BAMUM => 'bax',
		Ucdn::SCRIPT_VAI => 'vai',
		Ucdn::SCRIPT_EGYPTIAN_HIEROGLYPHS => 'und-Egyp',
		Ucdn::SCRIPT_MEROITIC_CURSIVE => 'und-Merc',
		Ucdn::SCRIPT_MEROITIC_HIEROGLYPHS => 'und-Mero',
		Ucdn::SCRIPT_OSMANYA => 'und-Osma',
		Ucdn::SCRIPT_TIFINAGH => 'und-Tfng',
		/* Middle Eastern */
		Ucdn::SCRIPT_ARABIC => 'und-Arab',
		Ucdn::SCRIPT_HEBREW => 'he',
		Ucdn::SCRIPT_SYRIAC => 'syr',
		Ucdn::SCRIPT_IMPERIAL_ARAMAIC => 'arc',
		Ucdn::SCRIPT_AVESTAN => 'ae',
		Ucdn::SCRIPT_CARIAN => 'xcr',
		Ucdn::SCRIPT_LYCIAN => 'xlc',
		Ucdn::SCRIPT_LYDIAN => 'xld',
		Ucdn::SCRIPT_MANDAIC => 'mid',
		Ucdn::SCRIPT_OLD_PERSIAN => 'peo',
		Ucdn::SCRIPT_PHOENICIAN => 'phn',
		Ucdn::SCRIPT_SAMARITAN => 'smp',
		Ucdn::SCRIPT_UGARITIC => 'uga',
		Ucdn::SCRIPT_CUNEIFORM => 'und-Xsux',
		Ucdn::SCRIPT_OLD_SOUTH_ARABIAN => 'und-Sarb',
		Ucdn::SCRIPT_INSCRIPTIONAL_PARTHIAN => 'und-Prti',
		Ucdn::SCRIPT_INSCRIPTIONAL_PAHLAVI => 'und-Phli',
		/* Central Asian */
		Ucdn::SCRIPT_MONGOLIAN => 'mn',
		Ucdn::SCRIPT_TIBETAN => 'bo',
		Ucdn::SCRIPT_OLD_TURKIC => 'und-Orkh',
		Ucdn::SCRIPT_PHAGS_PA => 'und-Phag',
		/* South Asian */
		Ucdn::SCRIPT_BENGALI => 'bn',
		Ucdn::SCRIPT_DEVANAGARI => 'hi',
		Ucdn::SCRIPT_GUJARATI => 'gu',
		Ucdn::SCRIPT_GURMUKHI => 'pa',
		Ucdn::SCRIPT_KANNADA => 'kn',
		Ucdn::SCRIPT_MALAYALAM => 'ml',
		Ucdn::SCRIPT_ORIYA => 'or',
		Ucdn::SCRIPT_SINHALA => 'si',
		Ucdn::SCRIPT_TAMIL => 'ta',
		Ucdn::SCRIPT_TELUGU => 'te',
		Ucdn::SCRIPT_CHAKMA => 'ccp',
		Ucdn::SCRIPT_LEPCHA => 'lep',
		Ucdn::SCRIPT_LIMBU => 'lif',
		Ucdn::SCRIPT_OL_CHIKI => 'sat',
		Ucdn::SCRIPT_SAURASHTRA => 'saz',
		Ucdn::SCRIPT_SYLOTI_NAGRI => 'syl',
		Ucdn::SCRIPT_TAKRI => 'dgo',
		Ucdn::SCRIPT_THAANA => 'dv',
		Ucdn::SCRIPT_BRAHMI => 'und-Brah',
		Ucdn::SCRIPT_KAITHI => 'und-Kthi',
		Ucdn::SCRIPT_KHAROSHTHI => 'und-Khar',
		Ucdn::SCRIPT_MEETEI_MAYEK => 'und-Mtei', /* or omp-Mtei */
		Ucdn::SCRIPT_SHARADA => 'und-Shrd',
		Ucdn::SCRIPT_SORA_SOMPENG => 'und-Sora',
		/* South East Asian */
		Ucdn::SCRIPT_KHMER => 'km',
		Ucdn::SCRIPT_LAO => 'lo',
		Ucdn::SCRIPT_MYANMAR => 'my',
		Ucdn::SCRIPT_THAI => 'th',
		Ucdn::SCRIPT_BALINESE => 'ban',
		Ucdn::SCRIPT_BATAK => 'bya',
		Ucdn::SCRIPT_BUGINESE => 'bug',
		Ucdn::SCRIPT_CHAM => 'cjm',
		Ucdn::SCRIPT_JAVANESE => 'jv',
		Ucdn::SCRIPT_KAYAH_LI => 'und-Kali',
		Ucdn::SCRIPT_REJANG => 'und-Rjng',
		Ucdn::SCRIPT_SUNDANESE => 'su',
		Ucdn::SCRIPT_TAI_LE => 'tdd',
		Ucdn::SCRIPT_TAI_THAM => 'und-Lana',
		Ucdn::SCRIPT_TAI_VIET => 'blt',
		Ucdn::SCRIPT_NEW_TAI_LUE => 'und-Talu',
		/* Phillipine */
		Ucdn::SCRIPT_BUHID => 'bku',
		Ucdn::SCRIPT_HANUNOO => 'hnn',
		Ucdn::SCRIPT_TAGALOG => 'tl',
		Ucdn::SCRIPT_TAGBANWA => 'tbw',
		/* East Asian */
		Ucdn::SCRIPT_HAN => 'und-Hans', // und-Hans (simplified) or und-Hant (Traditional)
		Ucdn::SCRIPT_HANGUL => 'ko',
		Ucdn::SCRIPT_HIRAGANA => 'ja',
		Ucdn::SCRIPT_KATAKANA => 'ja',
		Ucdn::SCRIPT_LISU => 'lis',
		Ucdn::SCRIPT_BOPOMOFO => 'und-Bopo', // zh-CN, zh-TW, zh-HK
		Ucdn::SCRIPT_MIAO => 'und-Plrd',
		Ucdn::SCRIPT_YI => 'und-Yiii',
		/* American */
		Ucdn::SCRIPT_CHEROKEE => 'chr',
		Ucdn::SCRIPT_CANADIAN_ABORIGINAL => 'cr',
		Ucdn::SCRIPT_DESERET => 'und-Dsrt',
		/* Other */
		Ucdn::SCRIPT_BRAILLE => 'und-Brai',
	];

	public function getLanguageByScript($script)
	{
		return isset($this->scriptToLanguageMap[$script]) ? $this->scriptToLanguageMap[$script] : null;
	}

	public function getLanguageDelimiters($language)
	{
		return isset($this->scriptDelimiterMap[$language]) ? $this->scriptDelimiterMap[$language] : null;
	}

}
