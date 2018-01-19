<?php

// PAGING
$this->mirrorMargins = 0;
$this->forcePortraitMargins = false;
$this->displayDefaultOrientation = false;
$this->printers_info = false; // Adds date and page info for printer when using @page and "marks:crop;"
$this->bleedMargin = 5;
$this->crossMarkMargin = 5; // Distance of cross mark from margin in mm
$this->cropMarkMargin = 8; // Distance of crop mark from margin in mm
$this->cropMarkLength = 18; // Default length in mm of crop line
$this->nonPrintMargin = 8; // Non-printable border at edge of paper sheet in mm
$this->defaultPagebreakType = 'cloneall'; // 'slice' or 'cloneall' or 'clonebycss' - for forced pagebreaks using <pagebreak />
// Automatic pagebreaks (flow in text) are always 'slice'

// Avoid just the border/background-color of the end of a block being moved on to next page
$this->margBuffer = 2; // Allows an (empty) end of block to extend beyond the bottom margin by this amount (mm)

// PAGE NUMBERING
$this->pagenumPrefix = '';
$this->pagenumSuffix = '';
$this->nbpgPrefix = '';
$this->nbpgSuffix = '';
$this->defaultPageNumStyle = '1'; // 1:Decimal, A:uppercase alphabetic etc. (as for list-style shorthands)

// FONTS, LANGUAGES & CHARACTER SETS
// Set maximum size of TTF font file to allow non-subsets - in kB
// Used to avoid a font e.g. Arial Unicode MS (perhaps used for substitutions) ever being fully embedded
// NB Free serif is 1.5MB, most files are <= 600kB (most 200-400KB)
$this->maxTTFFilesize = 2000;

// this value determines whether to subset or not
// 0 - 100 = percent characters
// i.e. if ==40, mPDF will embed whole font if >40% characters in that font
// or embed subset if <40% characters
// 0 will force whole file to be embedded (NO subsetting)
// 100 will force always to subset
// This value is overridden if you set new mPDF('s')
// and/or Can set at runtime
$this->percentSubset = 30;

$this->useAdobeCJK = false; // Uses Adobe CJK fonts for CJK languages
// default TRUE; only set false if you have defined some available fonts that support CJK
// If true this will not stop use of other CJK fonts if specified by font-family:
// and vice versa i.e. only dictates behaviour when specified by lang="" incl. AutoFont()

// // When embedding full TTF font files, remakes the font file using only core tables
// May improve function with some PostScript printers (GhostScript/GSView)
// Does not work with TTC font collections
// Slightly smaller file; increased processing time
$this->repackageTTF = false;

// Allows automatic character set conversion if "charset=xxx" detected in html header (WriteHTML() )
$this->allow_charset_conversion = true;
$this->biDirectional = false; // automatically determine BIDI text in LTR page

// AUTOMATIC FONT SELECTION
// Based on script and/or language
$this->autoScriptToLang = false; // mPDF 6.0 (similar to previously using function SetAutoFont() )
$this->baseScript = 1;	// =Latin; to set another base script see constants in classes/ucdn.php
$this->autoVietnamese = true;
$this->autoArabic = true;

$this->autoLangToFont = false; // mPDF 6.0 (similar to old useLang)

$this->useSubstitutions = false; // Substitute missing characters in UTF-8(multibyte) documents - from other fonts
$this->falseBoldWeight = 5; // Weight for bold text when using an artificial (outline) bold; value 0 (off) - 10 (rec. max)

// CONFIGURATION
$this->allow_output_buffering = false;

$this->enableImports = false; // Adding mPDFI functions

$this->collapseBlockMargins = true; // Allows top and bottom margins to collapse between block elements
$this->progressBar = 0;	// Shows progress-bars whilst generating file 0 off, 1 simple, 2 advanced
$this->progbar_heading = 'mPDF file progress';

$this->progbar_altHTML = ''; // Should include <html> and <body> but NOT end tags
// Can include <head> and link to stylesheet etc.
// e.g. '<html><body><p><img src="loading.gif" /> Creating PDF file. Please wait...</p>';

$this->dpi = 96; // To interpret "px" pixel values in HTML/CSS (see img_dpi below)
// Automatically correct for tags where HTML specifies optional end tags e.g. P,LI,DD,TD
// If you are confident input html is valid XHTML, turning this off may make it more reliable(?)
$this->allow_html_optional_endtags = true;

$this->ignore_invalid_utf8 = false;
$this->text_input_as_HTML = false; // Converts all entities in Text inputs to UTF-8 before encoding
$this->useGraphs = false;


// When writing a block element with position:fixed and overflow:auto, mPDF scales it down to fit in the space
// by repeatedly rewriting it and making adjustments. These values give the adjustments used, depending how far out
// the previous guess was. The lower the number, the quicker it will finish, but the less accurate the fit may be.
// FPR1 is for coarse adjustments, and FPR4 for fine adjustments when it is getting closer.
$this->incrementFPR1 = 10; // i.e. will alter by 1/[10]th of width and try again until within closer limits
$this->incrementFPR2 = 20;
$this->incrementFPR3 = 30;
$this->incrementFPR4 = 50; // i.e. will alter by 1/[50]th of width and try again when it nearly fits

// COLORSPACE
// 1 - allow GRAYSCALE only [convert CMYK/RGB->gray]
// 2 - allow RGB / SPOT COLOR / Grayscale [convert CMYK->RGB]
// 3 - allow CMYK / SPOT COLOR / Grayscale [convert RGB->CMYK]
$this->restrictColorSpace = 0;

// PDFX/1-a Compliant files
$this->PDFX = false;	// true=Forces compliance with PDFX-1a spec
// Cannot be used with $this->restrictColorSpace (i.e. no RGB)
$this->PDFXauto = false; // Overrides warnings making changes when possible to force PDFX1-a compliance

// PDFA1-b Compliant files
$this->PDFA = false;	// true=Forces compliance with PDFA-1b spec
// Can use with $this->restrictColorSpace=3 (for a CMYK file)
// Any other settings, uses RGB profile
$this->PDFAauto = false; // Overrides warnings making changes when possible to force PDFA1-b compliance
$this->ICCProfile = '';	// Colour profile OutputIntent
// sRGB_IEC61966-2-1 (=default if blank and PDFA),  or other added .icc profile
// Must be CMYK for PDFX, or appropriate type for PDFA(RGB or CMYK)

// DEBUGGING & DEVELOPERS
$this->showStats = false;
$this->debug = false;
$this->debugfonts = false; // Checks and reports on errors when parsing TTF files - adds significantly to processing time
$this->showImageErrors = false;
$this->table_error_report = false; // Die and report error if table is too wide to contain whole words
$this->table_error_report_param = ''; // Parameter which can be passed to show in error report i.e. chapter number being processed//

$this->title2annots = false; // Automatically convert title="" properties in tags, to annotations
$this->annotSize = 0.5; // default mm for Adobe annotations - nominal
$this->annotMargin; // default position for Annotations
$this->annotOpacity = 0.5; // default opacity for Annotations

// BOOKMARKS
$this->anchor2Bookmark = 0; // makes <a name=""> into a bookmark as well as internal link target; 1 = just name; 2 = name (p.34)
// Set an optional array to specify appearance of Bookmarks (by level)
// Default values are Black and normal style
/*
  Example:
  $this->bookmarkStyles = array(
  0 => array('color'=> array(0,64,128), 'style'=>'B'),
  1 => array('color'=> array(128,0,0), 'style'=>''),
  2 => array('color'=> array(0,128,0), 'style'=>'I'),
  );
 */
$this->bookmarkStyles = array();

// Specify whether to automatically generate bookmarks from h1 - h6 tags
$this->h2bookmarks = array();
/* Define arrays with e.g. the tag=>Bookmark-level
  Remember bookmark levels start at 0
  (does not work inside tables)
  H1 - H6 must be uppercase
  $this->h2bookmarks = array('H1'=>0, 'H2'=>1, 'H3'=>2);
 */


// TABLE OF CONTENTS
// Specify whether to automatically generate ToC entries from h1 - h6 tags
$this->h2toc = array();
/* Define arrays with e.g. the tag=>ToC-level
  Remember ToC levels start at 0
  (does not work inside tables)
  Only the default ToC will be used if > 1 ToCs are defined for the document
  H1 - H6 must be uppercase
  $this->h2toc = array('H1'=>0, 'H2'=>1, 'H3'=>2);
 */

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
$this->indexUseSubentries = true;


// CSS & STYLES
$this->CSSselectMedia = 'print'; // screen, print, or any other CSS @media type (except "all")

// PAGE HEADERS & FOOTERS
$this->forcePortraitHeaders = false;

// Values used if simple FOOTER/HEADER given i.e. not array
$this->defaultheaderfontsize = 8; // pt
$this->defaultheaderfontstyle = 'BI'; // '', or 'B' or 'I' or 'BI'
$this->defaultheaderline = 1; // 1 or 0 - line under the header
$this->defaultfooterfontsize = 8; // pt
$this->defaultfooterfontstyle = 'BI'; // '', or 'B' or 'I' or 'BI'
$this->defaultfooterline = 1; // 1 or 0 - line over the footer
$this->header_line_spacing = 0.25; // spacing between bottom of header and line (if present) - function of fontsize
$this->footer_line_spacing = 0.25; // spacing between bottom of header and line (if present) - function of fontsize
// If 'pad' margin-top sets fixed distance in mm (padding) between bottom of header and top of text.
// If 'stretch' margin-top sets a minimum distance in mm between top of page and top of text, which expands if header is too large to fit.
$this->setAutoTopMargin = false;
$this->setAutoBottomMargin = false;
$this->autoMarginPadding = 2; // distance in mm used as padding if 'stretch' mode is used

// TABLES
$this->simpleTables = false; // Forces all cells to have same border, background etc. Improves performance
$this->packTableData = false; // Reduce memory usage processing tables (but with increased processing time)

$this->ignore_table_percents = false;
$this->ignore_table_widths = false;
$this->keep_table_proportions = true; // If table width set > page width, force resizing but keep relative sizes
// Also forces respect of cell widths set by %
$this->shrink_tables_to_fit = 1.4; // automatically reduce fontsize in table if words would have to split ( not in CJK)
// 0 or false to disable; value (if set) gives maximum factor to reduce fontsize

$this->tableMinSizePriority = false; // If page-break-inside:avoid but cannot fit on full page without
// exceeding autosize; setting this value to true will force respect for
// autosize, and disable the page-break-inside:avoid

$this->use_kwt = false;	// "Keep-with-table" Attempts to keep a <h1> to <h6> tagged heading together
// with a table which comes immediately after it.
$this->iterationCounter = false; // Set to TRUE to use table Head iteration counter
$this->splitTableBorderWidth = 0; // Use table border (using this width in mm) when table breaks across pages
// Recommended to use small value e.g. 0.01

// // Allowed characters for text alignment on decimal marks. Additional codes must start with D
// DM - middot U+00B7
// DA - arabic decimal mark U+066B
$this->decimal_align = array('DP' => '.', 'DC' => ',', 'DM' => "\xc2\xb7", 'DA' => "\xd9\xab", 'DD' => '-');


// IMAGES
$this->interpolateImages = false; // if image-rendering=='auto', this defines value for image-rendering
// if true, image interpolation shall be performed by a conforming reader
$this->img_dpi = 96; // Default dpi to output images if size not defined
// See also above "dpi"

// TEXT SPACING & JUSTIFICATION

$this->useKerning = false;	// Specify whether kerning should be used when CSS font-kerning="auto" used for HTML;
// Also whether kerning should be used in any direct writing e.g. $mpdf->Text();
$this->justifyB4br = false;	// In justified text, <BR> does not cause the preceding text to be justified in browsers
// Change to true to force justification (as in MS Word)

$this->tabSpaces = 8; // Number of spaces to replace for a TAB in <pre> sections
// Notepad uses 6, HTML specification recommends 8
$this->jSWord = 0.4; // Proportion (/1) of space (when justifying margins) to allocate to Word vs. Character
$this->jSmaxChar = 2; // Maximum spacing to allocate to character spacing. (0 = no maximum)

$this->jSmaxCharLast = 1; // Maximum character spacing allowed (carried over) when finishing a last line
$this->jSmaxWordLast = 2; // Maximum word spacing allowed (carried over) when finishing a last line

// LINE SPACING & TEXT BASELINE
$this->useFixedNormalLineHeight = false; // Use the fixed factor ($this->normalLineheight) when line-height:normal
// Compatible with mPDF versions < 6

$this->useFixedTextBaseline = false; // Use a fixed ratio ($this->baselineC) to set the text baseline
// Compatible with mPDF versions < 6

$this->normalLineheight = 1.33; // Default Value used for line-height when CSS specified as 'normal' (default)

$this->adjustFontDescLineheight = 1.14; // Correction factor applied to lineheight values derived from 'win', 'mac', 'winTypo'
// Small Caps
$this->smCapsScale = 0.75; // Factor of 1 to scale capital letters
$this->smCapsStretch = 110; // % to stretch small caps horizontally (i.e. 100 = no stretch)
// Line-breaking
// The alternative to these next 2 is the use of U+200B Zero-width space
// These are only effective if using OTL for the fonts
$this->useDictionaryLBR = true; // Use the dictionaries to determine line-breaking in Lao, Khmer and Thai
$this->useTibetanLBR = true; // Use the inbuilt algorithm to determine line-breaking in Tibetan

// CJK Line-breaking
$this->allowCJKorphans = true; // FALSE=always wrap to next line; TRUE=squeeze or overflow
$this->allowCJKoverflow = false; // FALSE=squeeze; TRUE=overflow (only some characters, and disabled in tables)
$this->CJKforceend = false; // Forces overflowng punctuation to hang outside right margin mPDF 5.6.40

// HYPHENATION (using word dictionaries)
$this->SHYlang = "en"; // Should be one of: 'en','de','es','fi','fr','it','nl','pl','ru','sv'
$this->SHYleftmin = 2;
$this->SHYrightmin = 2;
$this->SHYcharmin = 2;
$this->SHYcharmax = 10;

// COLUMNS
$this->keepColumns = false; // Set to go to the second column only when the first is full of text etc.
$this->max_colH_correction = 1.15; // Maximum ratio to adjust column height when justifying - too large a value can give ugly results
$this->ColGap = 5;


// LISTS
// mPDF 6
$this->list_auto_mode = 'browser';		// 'mpdf' or 'browser' - Specify whether to use mPDF custom method of automatic
// indentation of lists, or standard browser-compatible
// custom mPDF method is ignored if list-style-position: inside, or image used for marker (or custom U+)
$this->list_indent_default = '40px';	// List Indentation when set to 'auto' if using standard browser-compatible method
$this->list_indent_default_mpdf = '0em';	// List Indentation when set to 'auto' if using mPDF custom method
$this->list_indent_first_level = 0;		// 1/0 yes/no to indent first level of list, if using mPDF custom method

$this->list_number_suffix = '.';		// Content to follow a numbered list marker e.g. '.' gives 1. or IV.; ')' gives 1) or a)

// To specify a bullet size and offset proportional to the list item's font size:
//$this->list_marker_offset = '0.45em';	// Offset (CSS length) of list marker bullets (disc/circle/square)
//$this->list_symbol_size = '0.31em';		// Size (CSS) of list marker bullets (disc/circle/square)
// Browsers use a fixed bullet size and offset
$this->list_marker_offset = '5.5pt'; // Offset (CSS length) of list marker bullets (disc/circle/square)
$this->list_symbol_size = '3.6pt'; // Size (CSS) of list marker bullets (disc/circle/square)

// ACTIVE FORMS
$this->useActiveForms = false;

// WATERMARKS
$this->watermarkImgBehind = false;
$this->showWatermarkText = 0;
$this->showWatermarkImage = 0;
$this->watermarkText = '';
$this->watermarkImage = '';
$this->watermark_font = '';
$this->watermarkTextAlpha = 0.2;
$this->watermarkImageAlpha = 0.2;
$this->watermarkImgAlphaBlend = 'Normal';
// Accepts any PDF spec. value: Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
// HardLight, SoftLight, Difference, Exclusion
// "Multiply" works well for watermark image on top

// BORDERS
$this->autoPadding = false; // Automatically increases padding in block elements when border-radius set - if required


//////////////////////////////////////////////

// Default values if no style sheet offered	(cf. http://www.w3.org/TR/CSS21/sample.html)
$this->defaultCSS = array(
	'BODY' => array(
		'FONT-FAMILY' => 'serif',
		'FONT-SIZE' => '11pt',
		'TEXT-INDENT' => '0pt',
		'LINE-HEIGHT' => 'normal',
		'MARGIN-COLLAPSE' => 'collapse', /* Custom property to collapse top/bottom margins at top/bottom of page - ignored in tables/lists */
		'HYPHENS' => 'manual', /* mPDF 5.6.08 */
		'FONT-KERNING' => 'auto', /* mPDF 6 */
	),
	'P' => array(
		'MARGIN' => '1.12em 0',
	),
	'H1' => array(
		'FONT-SIZE' => '2em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '0.67em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H2' => array(
		'FONT-SIZE' => '1.5em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '0.75em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H3' => array(
		'FONT-SIZE' => '1.17em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '0.83em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H4' => array(
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.12em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H5' => array(
		'FONT-SIZE' => '0.83em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.5em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'H6' => array(
		'FONT-SIZE' => '0.75em',
		'FONT-WEIGHT' => 'bold',
		'MARGIN' => '1.67em 0',
		'PAGE-BREAK-AFTER' => 'avoid',
	),
	'HR' => array(
		'COLOR' => '#888888',
		'TEXT-ALIGN' => 'center',
		'WIDTH' => '100%',
		'HEIGHT' => '0.2mm',
		'MARGIN-TOP' => '0.83em',
		'MARGIN-BOTTOM' => '0.83em',
	),
	'PRE' => array(
		'MARGIN' => '0.83em 0',
		'FONT-FAMILY' => 'monospace',
	),
	'S' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'STRIKE' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'DEL' => array(
		'TEXT-DECORATION' => 'line-through',
	),
	'SUB' => array(
		'VERTICAL-ALIGN' => 'sub',
		'FONT-SIZE' => '55%', /* Recommended 0.83em */
	),
	'SUP' => array(
		'VERTICAL-ALIGN' => 'super',
		'FONT-SIZE' => '55%', /* Recommended 0.83em */
	),
	'U' => array(
		'TEXT-DECORATION' => 'underline',
	),
	'INS' => array(
		'TEXT-DECORATION' => 'underline',
	),
	'B' => array(
		'FONT-WEIGHT' => 'bold',
	),
	'STRONG' => array(
		'FONT-WEIGHT' => 'bold',
	),
	'I' => array(
		'FONT-STYLE' => 'italic',
	),
	'CITE' => array(
		'FONT-STYLE' => 'italic',
	),
	'Q' => array(
		'FONT-STYLE' => 'italic',
	),
	'EM' => array(
		'FONT-STYLE' => 'italic',
	),
	'VAR' => array(
		'FONT-STYLE' => 'italic',
	),
	'SAMP' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'CODE' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'KBD' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'TT' => array(
		'FONT-FAMILY' => 'monospace',
	),
	'SMALL' => array(
		'FONT-SIZE' => '83%',
	),
	'BIG' => array(
		'FONT-SIZE' => '117%',
	),
	'ACRONYM' => array(
		'FONT-SIZE' => '77%',
		'FONT-WEIGHT' => 'bold',
	),
	'ADDRESS' => array(
		'FONT-STYLE' => 'italic',
	),
	'BLOCKQUOTE' => array(
		'MARGIN-LEFT' => '40px',
		'MARGIN-RIGHT' => '40px',
		'MARGIN-TOP' => '1.12em',
		'MARGIN-BOTTOM' => '1.12em',
	),
	'A' => array(
		'COLOR' => '#0000FF',
		'TEXT-DECORATION' => 'underline',
	),
	'UL' => array(
		'PADDING' => '0 auto', /* mPDF 6 */
		'MARGIN-TOP' => '0.83em', /* mPDF 6 */
		'MARGIN-BOTTOM' => '0.83em', /* mPDF 6 */
	),
	'OL' => array(
		'PADDING' => '0 auto', /* mPDF 6 */
		'MARGIN-TOP' => '0.83em', /* mPDF 6 */
		'MARGIN-BOTTOM' => '0.83em', /* mPDF 6 */
	),
	'DL' => array(
		'MARGIN' => '1.67em 0',
	),
	'DT' => array(
	),
	'DD' => array(
		'PADDING-LEFT' => '40px',
	),
	'TABLE' => array(
		'MARGIN' => '0',
		'BORDER-COLLAPSE' => 'separate',
		'BORDER-SPACING' => '2px',
		'EMPTY-CELLS' => 'show',
		'LINE-HEIGHT' => '1.2',
		'VERTICAL-ALIGN' => 'middle',
		'HYPHENS' => 'manual', /* mPDF 6 */
		'FONT-KERNING' => 'auto', /* mPDF 6 */
	),
	'THEAD' => array(
	),
	'TFOOT' => array(
	),
	'TH' => array(
		'FONT-WEIGHT' => 'bold',
		'TEXT-ALIGN' => 'center',
		'PADDING-LEFT' => '0.1em',
		'PADDING-RIGHT' => '0.1em',
		'PADDING-TOP' => '0.1em',
		'PADDING-BOTTOM' => '0.1em',
	),
	'TD' => array(
		'PADDING-LEFT' => '0.1em',
		'PADDING-RIGHT' => '0.1em',
		'PADDING-TOP' => '0.1em',
		'PADDING-BOTTOM' => '0.1em',
	),
	'CAPTION' => array(
		'TEXT-ALIGN' => 'center',
	),
	'IMG' => array(
		'MARGIN' => '0',
		'VERTICAL-ALIGN' => 'baseline',
		'IMAGE-RENDERING' => 'auto',
	),
	'INPUT' => array(
		'FONT-FAMILY' => 'sans-serif',
		'VERTICAL-ALIGN' => 'middle',
		'FONT-SIZE' => '0.9em',
	),
	'SELECT' => array(
		'FONT-FAMILY' => 'sans-serif',
		'FONT-SIZE' => '0.9em',
		'VERTICAL-ALIGN' => 'middle',
	),
	'TEXTAREA' => array(
		'FONT-FAMILY' => 'monospace',
		'FONT-SIZE' => '0.9em',
		'VERTICAL-ALIGN' => 'text-bottom',
	),
	'MARK' => array(/* mPDF 5.5.09 */
		'BACKGROUND-COLOR' => 'yellow',
	),
);


//////////////////////////////////////////////////
// VALUES ONLY LIKELY TO BE CHANGED BY DEVELOPERS
//////////////////////////////////////////////////
$this->pdf_version = '1.4';

// Hyphenation
$this->SHYlanguages = array('en', 'de', 'es', 'fi', 'fr', 'it', 'nl', 'pl', 'ru', 'sv'); // existing defined patterns

$this->default_lineheight_correction = 1.2; // Value 1 sets lineheight=fontsize height;
// Value used if line-height not set by CSS (usually is)

$this->fontsizes = array('XX-SMALL' => 0.7, 'X-SMALL' => 0.77, 'SMALL' => 0.86, 'MEDIUM' => 1, 'LARGE' => 1.2, 'X-LARGE' => 1.5, 'XX-LARGE' => 2);

// CHARACTER PATTERN MATCHES TO DETECT LANGUAGES
// pattern used to detect RTL characters -> force RTL
$this->pregRTLchars = "\x{0590}-\x{06FF}\x{0700}-\x{085F}\x{FB00}-\x{FDFD}\x{FE70}-\x{FEFF}"; // 085F to include Mandaic
// Chars which distinguish CJK but not between different
$this->pregCJKchars = "\x{1100}-\x{11FF}\x{2E80}-\x{A4CF}\x{A800}-\x{D7AF}\x{F900}-\x{FAFF}\x{FE30}-\x{FE6F}\x{FF00}-\x{FFEF}\x{20000}-\x{2FA1F}";

// For CJK Line-breaking - References:
// http://en.wikipedia.org/wiki/Line_breaking_rules_in_East_Asian_languages
// http://msdn.microsoft.com/en-us/goglobal/bb688158.aspx - listed using charsets
// Word wrapping in other langauges - http://msdn.microsoft.com/en-us/goglobal/bb688158.aspx
// Word wrapping in Japanese/Korean - http://en.wikipedia.org/wiki/Kinsoku_shori
// Unicode character types: http://unicode.org/reports/tr14/
// http://xml.ascc.net/en/utf-8/faq/zhl10n-faq-xsl.html#qb1
// ECMA-376 4th edition Part 1
// http://www.ecma-international.org/publications/standards/Ecma-376.htm

//Leading characters - Not allowed at end of line
$this->CJKleading = "\$\(\*\[\{\x{00a3}\x{00a5}\x{00ab}\x{00b7}\x{2018}\x{201c}\x{2035}\x{3005}\x{3007}\x{3008}\x{300a}\x{300c}\x{300e}\x{3010}\x{3014}\x{3016}\x{3018}\x{301d}\x{fe34}\x{fe35}\x{fe37}\x{fe39}\x{fe3b}\x{fe3d}\x{fe3f}\x{fe41}\x{fe43}\x{fe57}\x{fe59}\x{fe5b}\x{fe5d}\x{ff04}\x{ff08}\x{ff0e}\x{ff3b}\x{ff5b}\x{ff5f}\x{ff62}\x{ffe1}\x{ffe5}\x{ffe6}";

// Following characters - Not allowed at start
$this->CJKfollowing = "!%\),\.:;>\?\]\}\x{00a2}\x{00a8}\x{00b0}\x{00b7}\x{00bb}\x{02c7}\x{02c9}\x{2010}\x{2013}-\x{2016}\x{2019}\x{201d}-\x{201f}\x{2020}-\x{2022}\x{2025}-\x{2027}\x{2030}\x{2032}\x{2033}\x{203a}\x{203c}\x{2047}-\x{2049}\x{2103}\x{2236}\x{2574}\x{3001}-\x{3003}\x{3005}\x{3006}\x{3009}\x{300b}\x{300d}\x{300f}\x{3011}\x{3015}\x{3017}\x{3019}\x{301c}\x{301e}\x{301f}\x{303b}\x{3041}\x{3043}\x{3045}\x{3047}\x{3049}\x{3063}\x{3083}\x{3085}\x{3087}\x{308e}\x{3095}\x{3096}\x{309b}-\x{309e}\x{30a0}\x{30a1}\x{30a3}\x{30a5}\x{30a7}\x{30a9}\x{30c3}\x{30e3}\x{30e5}\x{30e7}\x{30ee}\x{30f5}\x{30f6}\x{30fb}-\x{30fd}\x{30fe}\x{31f0}-\x{31ff}\x{fe30}\x{fe31}-\x{fe34}\x{fe36}\x{fe38}\x{fe3a}\x{fe3c}\x{fe3e}\x{fe40}\x{fe42}\x{fe44}\x{fe4f}\x{fe50}-\x{fe58}\x{fe5a}\x{fe5c}-\x{fe5e}\x{ff01}\x{ff02}\x{ff05}\x{ff07}\x{ff09}\x{ff0c}\x{ff0e}\x{ff1a}\x{ff1b}\x{ff1f}\x{ff3d}\x{ff40}\x{ff5c}-\x{ff5e}\x{ff60}\x{ff61}\x{ff63}-\x{ff65}\x{ff9e}\x{ff9f}\x{ffe0}";

// Characters which are allowed to overflow the right margin (from CSS3 http://www.w3.org/TR/2012/WD-css3-text-20120814/#hanging-punctuation)
$this->CJKoverflow = "\.,\x{ff61}\x{ff64}\x{3001}\x{3002}\x{fe50}-\x{fe52}\x{ff0c}\x{ff0e}";

// mPDF 6
// Used for preventing letter-spacing in cursive scripts
// NB The following scripts in Unicode 6 are considered to be cursive scripts,
// and do not allow expansion opportunities between their letters:
// Arabic, Syriac, Mandaic, Mongolian, N'Ko, Phags Pa
$this->pregCURSchars = "\x{0590}-\x{083E}\x{0900}-\x{0DFF}\x{FB00}-\x{FDFD}\x{FE70}-\x{FEFF}";


$this->allowedCSStags = 'DIV|P|H1|H2|H3|H4|H5|H6|FORM|IMG|A|BODY|TABLE|HR|THEAD|TFOOT|TBODY|TH|TR|TD|UL|OL|LI|PRE|BLOCKQUOTE|ADDRESS|DL|DT|DD';
$this->allowedCSStags .= '|ARTICLE|ASIDE|FIGURE|FIGCAPTION|FOOTER|HEADER|HGROUP|NAV|SECTION|MAIN|MARK|DETAILS|SUMMARY|METER|PROGRESS|TIME';
$this->allowedCSStags .= '|SPAN|TT|I|B|BIG|SMALL|EM|STRONG|DFN|CODE|SAMP|KBD|VAR|CITE|ABBR|ACRONYM|STRIKE|S|U|DEL|INS|Q|FONT';
$this->allowedCSStags .= '|SELECT|INPUT|TEXTAREA|CAPTION|FIELDSET|LEGEND';
$this->allowedCSStags .= '|TEXTCIRCLE|DOTTAB|BDO|BDI';

$this->outerblocktags = array('DIV', 'FORM', 'CENTER', 'DL', 'FIELDSET', 'ARTICLE', 'ASIDE', 'FIGURE', 'FIGCAPTION', 'FOOTER', 'HEADER', 'HGROUP', 'MAIN', 'NAV', 'SECTION', 'DETAILS', 'SUMMARY', 'UL', 'OL', 'LI');
$this->innerblocktags = array('P', 'BLOCKQUOTE', 'ADDRESS', 'PRE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'DT', 'DD', 'CAPTION');
