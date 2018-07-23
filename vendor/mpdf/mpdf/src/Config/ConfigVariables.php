<?php

namespace Mpdf\Config;

use Mpdf\Css\DefaultCss;

use Mpdf\Language\LanguageToFont;
use Mpdf\Language\ScriptToLanguage;

use Mpdf\Ucdn;

class ConfigVariables
{

	private $defaults;

	public function __construct()
	{

		$this->defaults = [

			// PAGING
			'mirrorMargins' => 0,
			'forcePortraitMargins' => false,
			'displayDefaultOrientation' => false,

			// Adds date and page info for printer when using @page and "marks:crop,"
			'printers_info' => false,
			'bleedMargin' => 5,

			// Distance of cross mark from margin in mm
			'crossMarkMargin' => 5,
			// Distance of crop mark from margin in mm
			'cropMarkMargin' => 8,
			// Default length in mm of crop line
			'cropMarkLength' => 18,
			// Non-printable border at edge of paper sheet in mm
			'nonPrintMargin' => 8,

			// 'slice' or 'cloneall' or 'clonebycss' - for forced pagebreaks using <pagebreak />
			// Automatic pagebreaks (flow in text) are always 'slice'
			'defaultPagebreakType' => 'cloneall',

			// Avoid just the border/background-color of the end of a block being moved on to next page
			// Allows an (empty) end of block to extend beyond the bottom margin by this amount (mm)
			'margBuffer' => 2,

			// PAGE NUMBERING
			'pagenumPrefix' => '',
			'pagenumSuffix' => '',
			'nbpgPrefix' => '',
			'nbpgSuffix' => '',
			// 1:Decimal, A:uppercase alphabetic etc. (as for list-style shorthands)
			'defaultPageNumStyle' => '1',

			// PAGE NUMBER ALIASES
			'aliasNbPg' => '{nb}',
			'aliasNbPgGp' => '{nbpg}',

			// FONTS, LANGUAGES & CHARACTER SETS
			// Set maximum size of TTF font file to allow non-subsets - in kB
			// Used to avoid a font e.g. Arial Unicode MS (perhaps used for substitutions) ever being fully embedded
			// NB Free serif is 1.5MB, most files are <= 600kB (most 200-400KB)
			'maxTTFFilesize' => 2000,

			// this value determines whether to subset or not
			// 0 - 100' => percent characters
			// i.e. if ==40, mPDF will embed whole font if >40% characters in that font
			// or embed subset if <40% characters
			// 0 will force whole file to be embedded (NO subsetting)
			// 100 will force always to subset
			// This value is overridden if you set new mPDF('s')
			// and/or Can set at runtime
			'percentSubset' => 30,

			// Uses Adobe CJK fonts for CJK languages
			// default TRUE, only set false if you have defined some available fonts that support CJK
			// If true this will not stop use of other CJK fonts if specified by font-family:
			// and vice versa i.e. only dictates behaviour when specified by lang="" incl. AutoFont()
			'useAdobeCJK' => false,

			// When embedding full TTF font files, remakes the font file using only core tables
			// May improve function with some PostScript printers (GhostScript/GSView)
			// Does not work with TTC font collections
			// Slightly smaller file, increased processing time
			'repackageTTF' => false,

			// Allows automatic character set conversion if "charset=xxx" detected in html header (WriteHTML() )
			'allow_charset_conversion' => true,
			// Automatically determine BIDI text in LTR page
			'biDirectional' => false,

			// AUTOMATIC FONT SELECTION
			// Based on script and/or language
			// mPDF 6.0 (similar to previously using function SetAutoFont() )
			'autoScriptToLang' => false,
			'baseScript' => Ucdn::SCRIPT_LATIN,
			'autoVietnamese' => true,
			'autoArabic' => true,
			// mPDF 6.0 (similar to old useLang)
			'autoLangToFont' => false,

			// Substitute missing characters in UTF-8(multibyte) documents - from other fonts
			'useSubstitutions' => false,
			// Weight for bold text when using an artificial (outline) bold, value 0 (off) - 10 (rec. max)
			'falseBoldWeight' => 5,

			// CONFIGURATION
			'allow_output_buffering' => false,

			// Adding mPDFI functions
			'enableImports' => false,

			// Allows top and bottom margins to collapse between block elements
			'collapseBlockMargins' => true,

			// To interpret "px" pixel values in HTML/CSS (see img_dpi below)
			'dpi' => 96,

			// Automatically correct for tags where HTML specifies optional end tags e.g. P,LI,DD,TD
			// If you are confident input html is valid XHTML, turning this off may make it more reliable(?)
			'allow_html_optional_endtags' => true,

			'ignore_invalid_utf8' => false,
			// Converts all entities in Text inputs to UTF-8 before encoding
			'text_input_as_HTML' => false,

			// When writing a block element with position:fixed and overflow:auto, mPDF scales it down to fit in the space
			// by repeatedly rewriting it and making adjustments. These values give the adjustments used, depending how far out
			// the previous guess was. The lower the number, the quicker it will finish, but the less accurate the fit may be.
			// FPR1 is for coarse adjustments, and FPR4 for fine adjustments when it is getting closer.
			'incrementFPR1' => 10, // i.e. will alter by 1/[10]th of width and try again until within closer limits
			'incrementFPR2' => 20,
			'incrementFPR3' => 30,
			'incrementFPR4' => 50, // i.e. will alter by 1/[50]th of width and try again when it nearly fits

			// COLORSPACE
			// 1 - allow GRAYSCALE only [convert CMYK/RGB->gray]
			// 2 - allow RGB / SPOT COLOR / Grayscale [convert CMYK->RGB]
			// 3 - allow CMYK / SPOT COLOR / Grayscale [convert RGB->CMYK]
			'restrictColorSpace' => 0,

			// PDFX/1-a Compliant files
			// true=Forces compliance with PDFX-1a spec
			// Cannot be used with 'restrictColorSpace' (i.e. no RGB)
			'PDFX' => false,
			// Overrides warnings making changes when possible to force PDFX1-a compliance
			'PDFXauto' => false,

			// PDFA1-b Compliant files
			// true=Forces compliance with PDFA-1b spec
			// Can use with 'restrictColorSpace'=3 (for a CMYK file)
			// Any other settings, uses RGB profile
			'PDFA' => false,
			// Overrides warnings making changes when possible to force PDFA1-b compliance
			'PDFAauto' => false,

			// Colour profile OutputIntent
			// sRGB_IEC61966-2-1 (=default if blank and PDFA), or other added .icc profile
			// Must be CMYK for PDFX, or appropriate type for PDFA(RGB or CMYK)
			'ICCProfile' => '',

			'spotColors' => [],
			'spotColorIDs' => [],

			// DEBUGGING & DEVELOPERS
			'debug' => false,
			// Checks and reports on errors when parsing TTF files - adds significantly to processing time
			'debugfonts' => false,
			'showImageErrors' => false,
			// Die and report error if table is too wide to contain whole words
			'table_error_report' => false,
			// Parameter which can be passed to show in error report i.e. chapter number being processed
			'table_error_report_param' => '',

			'title2annots' => false, // Automatically convert title="" properties in tags, to annotations
			'annotSize' => 0.5, // default mm for Adobe annotations - nominal
			'annotMargin' => null, // default position for Annotations
			'annotOpacity' => 0.5, // default opacity for Annotations

			// BOOKMARKS
			// makes <a name=""> into a bookmark as well as internal link target, 1' => just name, 2' => name (p.34)
			// Set an optional array to specify appearance of Bookmarks (by level)
			// Default values are Black and normal style
			'anchor2Bookmark' => 0,

			/*
				Example:
				'bookmarkStyles' => array(
					0 => array('color'=> array(0,64,128), 'style'=>'B'),
					1 => array('color'=> array(128,0,0), 'style'=>''),
					2 => array('color'=> array(0,128,0), 'style'=>'I'),
				),
			*/
			'bookmarkStyles' => [],

			// Specify whether to automatically generate bookmarks from h1 - h6 tags
			/*
				Define arrays with e.g. the tag=>Bookmark-level
				Remember bookmark levels start at 0
				(does not work inside tables)
				H1 - H6 must be uppercase
				'h2bookmarks' => array('H1'=>0, 'H2'=>1, 'H3'=>2),
			*/
			'h2bookmarks' => [],

			// TABLE OF CONTENTS

			// Specify whether to automatically generate ToC entries from h1 - h6 tags
			/*
				Define arrays with e.g. the tag=>ToC-level
				Remember ToC levels start at 0
				(does not work inside tables)
				Only the default ToC will be used if > 1 ToCs are defined for the document
				H1 - H6 must be uppercase
				'h2toc' => array('H1'=>0, 'H2'=>1, 'H3'=>2),
			*/
			'h2toc' => [],

			// INDEX
			/* Specifies whether to repeat the main entry for each subEntry (true suppresses this)
				e.g. Mammal:dog   ...   Mammal:elephant ->
				[true]
				Mammal
				- dog
				- elephant
				[false]
				Mammal, dog
				Mammal, elephant
			*/
			'indexUseSubentries' => true,

			// CSS & STYLES
			// screen, print, or any other CSS @media type (except "all")
			'CSSselectMedia' => 'print',

			// PAGE HEADERS & FOOTERS
			'forcePortraitHeaders' => false,

			// Values used if simple FOOTER/HEADER given i.e. not array
			'defaultheaderfontsize' => 8, // pt
			'defaultheaderfontstyle' => 'BI', // '', or 'B' or 'I' or 'BI'
			'defaultheaderline' => 1, // 1 or 0 - line under the header
			'defaultfooterfontsize' => 8, // pt
			'defaultfooterfontstyle' => 'BI', // '', or 'B' or 'I' or 'BI'
			'defaultfooterline' => 1, // 1 or 0 - line over the footer

			// spacing between bottom of header and line (if present) - function of fontsize
			'header_line_spacing' => 0.25,
			// spacing between bottom of header and line (if present) - function of fontsize
			'footer_line_spacing' => 0.25,
			// If 'pad' margin-top sets fixed distance in mm (padding) between bottom of header and top of text.
			// If 'stretch' margin-top sets a minimum distance in mm between top of page and top of text, which expands if header is too large to fit.
			'setAutoTopMargin' => false,
			'setAutoBottomMargin' => false,
			// distance in mm used as padding if 'stretch' mode is used
			'autoMarginPadding' => 2,

			// TABLES
			// Forces all cells to have same border, background etc. Improves performance
			'simpleTables' => false,
			// Reduce memory usage processing tables (but with increased processing time)
			'packTableData' => false,

			'ignore_table_percents' => false,
			'ignore_table_widths' => false,
			// If table width set > page width, force resizing but keep relative sizes
			// Also forces respect of cell widths set by %
			'keep_table_proportions' => true,
			// automatically reduce fontsize in table if words would have to split ( not in CJK)
			// 0 or false to disable, value (if set) gives maximum factor to reduce fontsize
			'shrink_tables_to_fit' => 1.4,

			// If page-break-inside:avoid but cannot fit on full page without
			// exceeding autosize, setting this value to true will force respect for autosize, and disable the page-break-inside:avoid
			'tableMinSizePriority' => false,

			// "Keep-with-table" Attempts to keep a <h1> to <h6> tagged heading together with a table which comes immediately after it.
			'use_kwt' => false,
			// Set to TRUE to use table Head iteration counter
			'iterationCounter' => false,
			// Use table border (using this width in mm) when table breaks across pages
			// Recommended to use small value e.g. 0.01
			'splitTableBorderWidth' => 0,

			// Allowed characters for text alignment on decimal marks. Additional codes must start with D
			// DM - middot U+00B7
			// DA - arabic decimal mark U+066B
			'decimal_align' => ['DP' => '.', 'DC' => ',', 'DM' => "\xc2\xb7", 'DA' => "\xd9\xab", 'DD' => '-'],

			// IMAGES
			// if image-rendering=='auto', this defines value for image-rendering
			// if true, image interpolation shall be performed by a conforming reader
			'interpolateImages' => false,
			// Default dpi to output images if size not defined
			// See also above "dpi"
			'img_dpi' => 96,

			// TEXT SPACING & JUSTIFICATION

			// Specify whether kerning should be used when CSS font-kerning="auto" used for HTML,
			// Also whether kerning should be used in any direct writing e.g. $mpdf->Text(),
			'useKerning' => false,
			// In justified text, <BR> does not cause the preceding text to be justified in browsers
			// Change to true to force justification (as in MS Word)
			'justifyB4br' => false,

			// Number of spaces to replace for a TAB in <pre> sections
			// Notepad uses 6, HTML specification recommends 8
			'tabSpaces' => 8,
			// Proportion (/1) of space (when justifying margins) to allocate to Word vs. Character
			'jSWord' => 0.4,
			// Maximum spacing to allocate to character spacing. (0' => no maximum)
			'jSmaxChar' => 2,

			// Maximum character spacing allowed (carried over) when finishing a last line
			'jSmaxCharLast' => 1,
			// Maximum word spacing allowed (carried over) when finishing a last line
			'jSmaxWordLast' => 2,

			// LINE SPACING & TEXT BASELINE
			// Use the fixed factor ('normalLineheight') when line-height:normal
			// Compatible with mPDF versions < 6
			'useFixedNormalLineHeight' => false,

			// Use a fixed ratio ('baselineC') to set the text baseline
			// Compatible with mPDF versions < 6
			'useFixedTextBaseline' => false,

			// Default Value used for line-height when CSS specified as 'normal' (default)
			'normalLineheight' => 1.33,

			// Correction factor applied to lineheight values derived from 'win', 'mac', 'winTypo'
			'adjustFontDescLineheight' => 1.14,

			// Small Caps
			// Factor of 1 to scale capital letters
			'smCapsScale' => 0.75,
			// % to stretch small caps horizontally (i.e. 100' => no stretch)
			'smCapsStretch' => 110,

			// Line-breaking
			// The alternative to these next 2 is the use of U+200B Zero-width space
			// These are only effective if using OTL for the fonts
			// Use the dictionaries to determine line-breaking in Lao, Khmer and Thai
			'useDictionaryLBR' => true,
			// Use the inbuilt algorithm to determine line-breaking in Tibetan
			'useTibetanLBR' => true,

			// CJK Line-breaking
			// FALSE=always wrap to next line, TRUE=squeeze or overflow
			'allowCJKorphans' => true,
			// FALSE=squeeze, TRUE=overflow (only some characters, and disabled in tables)
			'allowCJKoverflow' => false,
			// Forces overflowng punctuation to hang outside right margin mPDF 5.6.40
			'CJKforceend' => false,

			// COLUMNS
			'keepColumns' => false, // Set to go to the second column only when the first is full of text etc.
			'max_colH_correction' => 1.15, // Maximum ratio to adjust column height when justifying - too large a value can give ugly results
			'ColGap' => 5,

			// LISTS
			// mPDF 6
			// 'mpdf' or 'browser' - Specify whether to use mPDF custom method of automatic
			'list_auto_mode' => 'browser',
			// indentation of lists, or standard browser-compatible
			// custom mPDF method is ignored if list-style-position: inside, or image used for marker (or custom U+)
			// List Indentation when set to 'auto' if using standard browser-compatible method
			'list_indent_default' => '40px',
			// List Indentation when set to 'auto' if using mPDF custom method
			'list_indent_default_mpdf' => '0em',
			// 1/0 yes/no to indent first level of list, if using mPDF custom method
			'list_indent_first_level' => 0,

			// Content to follow a numbered list marker e.g. '.' gives 1. or IV., ')' gives 1) or a)
			'list_number_suffix' => '.',

			// To specify a bullet size and offset proportional to the list item's font size:
			// Browsers use a fixed bullet size and offset
			// Offset (CSS length) of list marker bullets (disc/circle/square)
			'list_marker_offset' => '5.5pt',
			// Size (CSS) of list marker bullets (disc/circle/square)
			'list_symbol_size' => '3.6pt',

			// Hyphenation
			'SHYlanguages' => ['en', 'de', 'es', 'fi', 'fr', 'it', 'nl', 'pl', 'ru', 'sv'], // existing defined patterns
			'SHYlang' => "en", // 'en','de','es','fi','fr','it','nl','pl','ru','sv'
			'SHYleftmin' => 2,
			'SHYrightmin' => 2,
			'SHYcharmin' => 2,
			'SHYcharmax' => 10,

			// ACTIVE FORMS
			'useActiveForms' => false,

			// WATERMARKS
			'watermarkImgBehind' => false,
			'showWatermarkText' => 0,
			'showWatermarkImage' => 0,
			'watermarkText' => '',
			'watermarkAngle' => 45,
			'watermarkImage' => '',
			'watermark_font' => '',
			'watermarkTextAlpha' => 0.2,
			'watermarkImageAlpha' => 0.2,

			// Accepts any PDF spec. value: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, HardLight, SoftLight, Difference, Exclusion
			// "Multiply" works well for watermark image on top
			'watermarkImgAlphaBlend' => 'Normal',

			// BORDERS
			'autoPadding' => false, // Automatically increases padding in block elements when border-radius set - if required

			// SVG

			// If you wish to use Automatic Font selection within SVG's. change this definition to true.
			// This selects different fonts for different scripts used in text.
			// This can be enabled/disabled independently of the use of Automatic Font selection within mPDF generally.
			// Choice of font is determined by the LangToFont and ScriptToLang classes, the same as for mPDF generally.
			'svgAutoFont' => false,

			// Enable a limited use of classes within SVG <text> elements by setting this to true.
			// This allows recognition of a "class" attribute on a <text> element.
			// The CSS style for that class should be outside the SVG, and cannot use any other selectors (i.e. only .class {} can be defined)
			// <style> definitions within the SVG code will be recognised if the SVG is included as an inline item within the HTML code passed to mPDF.
			// The style property should be pertinent to SVG e.g. use fill:red rather than color:red
			// Only the properties currently supported for SVG text can be specified:
			// fill, fill-opacity, stroke, stroke-opacity, stroke-linecap, stroke-linejoin, stroke-width, stroke-dasharray, stroke-dashoffset
			// font-family, font-size, font-weight, font-variant, font-style, opacity, text-anchor
			'svgClasses' => false,

			// Default values if no style sheet offered	(cf. http://www.w3.org/TR/CSS21/sample.html)
			'defaultCSS' => DefaultCss::$definition,
			'defaultCssFile' => __DIR__ . '/../../data/mpdf.css',

			'customProperties' => [],

			'languageToFont' => new LanguageToFont(),
			'scriptToLanguage' => new ScriptToLanguage(),

			//////////////////////////////////////////////////
			// VALUES ONLY LIKELY TO BE CHANGED BY DEVELOPERS
			//////////////////////////////////////////////////

			'pdf_version' => '1.4',

			'fontDir' => [
				__DIR__ . '/../../ttfonts'
			],

			'tempDir' => __DIR__ . '/../../tmp',

			'allowAnnotationFiles' => false,

			'hyphenationDictionaryFile' => __DIR__ . '/../../data/patterns/dictionary.txt',

			'default_lineheight_correction' => 1.2, // Value 1 sets lineheight=fontsize height,
			// Value used if line-height not set by CSS (usually is)

			'fontsizes' => ['XX-SMALL' => 0.7, 'X-SMALL' => 0.77, 'SMALL' => 0.86, 'MEDIUM' => 1, 'LARGE' => 1.2, 'X-LARGE' => 1.5, 'XX-LARGE' => 2],

			// CHARACTER PATTERN MATCHES TO DETECT LANGUAGES
			// pattern used to detect RTL characters -> force RTL
			'pregRTLchars' => "\x{0590}-\x{06FF}\x{0700}-\x{085F}\x{FB00}-\x{FDFD}\x{FE70}-\x{FEFF}", // 085F to include Mandaic
			// Chars which distinguish CJK but not between different
			'pregCJKchars' => "\x{1100}-\x{11FF}\x{2E80}-\x{A4CF}\x{A800}-\x{D7AF}\x{F900}-\x{FAFF}\x{FE30}-\x{FE6F}\x{FF00}-\x{FFEF}\x{20000}-\x{2FA1F}",

			/**
			 * References for CJK line-breaking
			 *
			 * http://en.wikipedia.org/wiki/Line_breaking_rules_in_East_Asian_languages
			 * http://msdn.microsoft.com/en-us/goglobal/bb688158.aspx - listed using charsets
			 * Word wrapping in other langauges - http://msdn.microsoft.com/en-us/goglobal/bb688158.aspx
			 * Word wrapping in Japanese/Korean - http://en.wikipedia.org/wiki/Kinsoku_shori
			 * Unicode character types: http://unicode.org/reports/tr14/
			 * http://xml.ascc.net/en/utf-8/faq/zhl10n-faq-xsl.html#qb1
			 * ECMA-376 4th edition Part 1
			 * http://www.ecma-international.org/publications/standards/Ecma-376.htm
			 */

			// Leading characters - Not allowed at end of line
			'CJKleading' => "\$\(\*\[\{\x{00a3}\x{00a5}\x{00ab}\x{00b7}\x{2018}\x{201c}\x{2035}\x{3005}\x{3007}\x{3008}\x{300a}\x{300c}\x{300e}\x{3010}\x{3014}\x{3016}\x{3018}\x{301d}\x{fe34}\x{fe35}\x{fe37}\x{fe39}\x{fe3b}\x{fe3d}\x{fe3f}\x{fe41}\x{fe43}\x{fe57}\x{fe59}\x{fe5b}\x{fe5d}\x{ff04}\x{ff08}\x{ff0e}\x{ff3b}\x{ff5b}\x{ff5f}\x{ff62}\x{ffe1}\x{ffe5}\x{ffe6}",

			// Following characters - Not allowed at start
			'CJKfollowing' => "!%\),\.:,>\?\]\}\x{00a2}\x{00a8}\x{00b0}\x{00b7}\x{00bb}\x{02c7}\x{02c9}\x{2010}\x{2013}-\x{2016}\x{2019}\x{201d}-\x{201f}\x{2020}-\x{2022}\x{2025}-\x{2027}\x{2030}\x{2032}\x{2033}\x{203a}\x{203c}\x{2047}-\x{2049}\x{2103}\x{2236}\x{2574}\x{3001}-\x{3003}\x{3005}\x{3006}\x{3009}\x{300b}\x{300d}\x{300f}\x{3011}\x{3015}\x{3017}\x{3019}\x{301c}\x{301e}\x{301f}\x{303b}\x{3041}\x{3043}\x{3045}\x{3047}\x{3049}\x{3063}\x{3083}\x{3085}\x{3087}\x{308e}\x{3095}\x{3096}\x{309b}-\x{309e}\x{30a0}\x{30a1}\x{30a3}\x{30a5}\x{30a7}\x{30a9}\x{30c3}\x{30e3}\x{30e5}\x{30e7}\x{30ee}\x{30f5}\x{30f6}\x{30fb}-\x{30fd}\x{30fe}\x{31f0}-\x{31ff}\x{fe30}\x{fe31}-\x{fe34}\x{fe36}\x{fe38}\x{fe3a}\x{fe3c}\x{fe3e}\x{fe40}\x{fe42}\x{fe44}\x{fe4f}\x{fe50}-\x{fe58}\x{fe5a}\x{fe5c}-\x{fe5e}\x{ff01}\x{ff02}\x{ff05}\x{ff07}\x{ff09}\x{ff0c}\x{ff0e}\x{ff1a}\x{ff1b}\x{ff1f}\x{ff3d}\x{ff40}\x{ff5c}-\x{ff5e}\x{ff60}\x{ff61}\x{ff63}-\x{ff65}\x{ff9e}\x{ff9f}\x{ffe0}",

			// Characters which are allowed to overflow the right margin (from CSS3 http://www.w3.org/TR/2012/WD-css3-text-20120814/#hanging-punctuation)
			'CJKoverflow' => "\.,\x{ff61}\x{ff64}\x{3001}\x{3002}\x{fe50}-\x{fe52}\x{ff0c}\x{ff0e}",

			// Used for preventing letter-spacing in cursive scripts
			// NB The following scripts in Unicode 6 are considered to be cursive scripts,
			// and do not allow expansion opportunities between their letters:
			// Arabic, Syriac, Mandaic, Mongolian, N'Ko, Phags Pa
			'pregCURSchars' => "\x{0590}-\x{083E}\x{0900}-\x{0DFF}\x{FB00}-\x{FDFD}\x{FE70}-\x{FEFF}",

			'allowedCSStags' => 'DIV|P|H1|H2|H3|H4|H5|H6|FORM|IMG|A|BODY|TABLE|HR|THEAD|TFOOT|TBODY|TH|TR|TD|UL|OL|LI|PRE|BLOCKQUOTE|ADDRESS|DL|DT|DD'
				. '|ARTICLE|ASIDE|FIGURE|FIGCAPTION|FOOTER|HEADER|HGROUP|NAV|SECTION|MAIN|MARK|DETAILS|SUMMARY|METER|PROGRESS|TIME'
				. '|SPAN|TT|I|B|BIG|SMALL|EM|STRONG|DFN|CODE|SAMP|KBD|VAR|CITE|ABBR|ACRONYM|STRIKE|S|U|DEL|INS|Q|FONT'
				. '|SELECT|INPUT|TEXTAREA|CAPTION|FIELDSET|LEGEND'
				. '|TEXTCIRCLE|DOTTAB|BDO|BDI',

			'outerblocktags' => ['DIV', 'FORM', 'CENTER', 'DL', 'FIELDSET', 'ARTICLE', 'ASIDE', 'FIGURE', 'FIGCAPTION', 'FOOTER', 'HEADER', 'HGROUP', 'MAIN', 'NAV', 'SECTION', 'DETAILS', 'SUMMARY', 'UL', 'OL', 'LI'],
			'innerblocktags' => ['P', 'BLOCKQUOTE', 'ADDRESS', 'PRE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'DT', 'DD', 'CAPTION'],

			// cURL options
			'curlFollowLocation' => false,
			'curlAllowUnsafeSslRequests' => false,
			'curlTimeout' => 5,
		];
	}

	public function getDefaults()
	{
		return $this->defaults;
	}
}
