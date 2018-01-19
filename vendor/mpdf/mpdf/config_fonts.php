<?php

// Optionally define a folder which contains TTF fonts
// mPDF will look here before looking in the usual _MPDF_TTFONTPATH
// Useful if you already have a folder for your fonts
// e.g. on Windows: define("_MPDF_SYSTEM_TTFONTS", 'C:/Windows/Fonts/');

//if (!defined("_MPDF_SYSTEM_TTFONTS")) { define("_MPDF_SYSTEM_TTFONTS", 'C:/xampp/htdocs/common/ttffonts/'); }

// Optionally set font(s) (names as defined below in $this->fontdata) to use for missing characters
// when using useSubstitutions. Use a font with wide coverage - dejavusanscondensed is a good start
// only works using subsets (otherwise would add very large file)
// More than 1 font can be specified but each will add to the processing time of the script

// $this->backupSubsFont = array('dejavusanscondensed','arialunicodems','sun-exta');	// this will recognise most scripts
$this->backupSubsFont = array('dejavusanscondensed', 'freeserif');

// Optionally set a font (name as defined below in $this->fontdata) to use for CJK characters
// in Plane 2 Unicode (> U+20000) when using useSubstitutions.
// Use a font like hannomb or sun-extb if available
// only works using subsets (otherwise would add very large file)

$this->backupSIPFont = 'sun-extb';

/*
  This array defines translations from font-family in CSS or HTML
  to the internal font-family name used in mPDF.
  Can include as many as want, regardless of which fonts are installed.
  By default mPDF will take a CSS/HTML font-family and remove spaces
  and change to lowercase e.g. "Arial Unicode MS" will be recognised as
  "arialunicodems"
  You only need to define additional translations.
  You can also use it to define specific substitutions e.g.
  'helvetica' => 'arial'
  Generic substitutions (i.e. to a sans-serif or serif font) are set
  by including the font-family in e.g. $this->sans_fonts below
 */
$this->fonttrans = array(
	'times' => 'timesnewroman',
	'courier' => 'couriernew',
	'trebuchet' => 'trebuchetms',
	'comic' => 'comicsansms',
	'franklin' => 'franklingothicbook',
	'ocr-b' => 'ocrb',
	'ocr-b10bt' => 'ocrb',
	'damase' => 'mph2bdamase',
);

/*
  This array lists the file names of the TrueType .ttf or .otf font files
  for each variant of the (internal mPDF) font-family name.
  ['R'] = Regular (Normal), others are Bold, Italic, and Bold-Italic
  Each entry must contain an ['R'] entry, but others are optional.
  Only the font (files) entered here will be available to use in mPDF.
  Put preferred default first in order
  This will be used if a named font cannot be found in any of
  $this->sans_fonts, $this->serif_fonts or $this->mono_fonts

  ['sip-ext'] = 'sun-extb'; name a related font file containing SIP characters
  ['useOTL'] => 0xFF,	Enable use of OTL features.
  ['useKashida'] => 75,	Enable use of kashida for text justification in Arabic text

  If a .ttc TrueType collection file is referenced, the number of the font
  within the collection is required. Fonts in the collection are numbered
  starting at 1, as they appear in the .ttc file e.g.
  "cambria" => array(
  'R' => "cambria.ttc",
  'B' => "cambriab.ttf",
  'I' => "cambriai.ttf",
  'BI' => "cambriaz.ttf",
  'TTCfontID' => array(
  'R' => 1,
  ),
  ),
  "cambriamath" => array(
  'R' => "cambria.ttc",
  'TTCfontID' => array(
  'R' => 2,
  ),
  ),
 */

$this->fontdata = array(
	"dejavusanscondensed" => array(
		'R' => "DejaVuSansCondensed.ttf",
		'B' => "DejaVuSansCondensed-Bold.ttf",
		'I' => "DejaVuSansCondensed-Oblique.ttf",
		'BI' => "DejaVuSansCondensed-BoldOblique.ttf",
		'useOTL' => 0xFF,
		'useKashida' => 75,
	),
	"dejavusans" => array(
		'R' => "DejaVuSans.ttf",
		'B' => "DejaVuSans-Bold.ttf",
		'I' => "DejaVuSans-Oblique.ttf",
		'BI' => "DejaVuSans-BoldOblique.ttf",
		'useOTL' => 0xFF,
		'useKashida' => 75,
	),
	"dejavuserif" => array(
		'R' => "DejaVuSerif.ttf",
		'B' => "DejaVuSerif-Bold.ttf",
		'I' => "DejaVuSerif-Italic.ttf",
		'BI' => "DejaVuSerif-BoldItalic.ttf",
	),
	"dejavuserifcondensed" => array(
		'R' => "DejaVuSerifCondensed.ttf",
		'B' => "DejaVuSerifCondensed-Bold.ttf",
		'I' => "DejaVuSerifCondensed-Italic.ttf",
		'BI' => "DejaVuSerifCondensed-BoldItalic.ttf",
	),
	"dejavusansmono" => array(
		'R' => "DejaVuSansMono.ttf",
		'B' => "DejaVuSansMono-Bold.ttf",
		'I' => "DejaVuSansMono-Oblique.ttf",
		'BI' => "DejaVuSansMono-BoldOblique.ttf",
		'useOTL' => 0xFF,
		'useKashida' => 75,
	),
	"freesans" => array(
		'R' => "FreeSans.ttf",
		'B' => "FreeSansBold.ttf",
		'I' => "FreeSansOblique.ttf",
		'BI' => "FreeSansBoldOblique.ttf",
		'useOTL' => 0xFF,
	),
	"freeserif" => array(
		'R' => "FreeSerif.ttf",
		'B' => "FreeSerifBold.ttf",
		'I' => "FreeSerifItalic.ttf",
		'BI' => "FreeSerifBoldItalic.ttf",
		'useOTL' => 0xFF,
		'useKashida' => 75,
	),
	"freemono" => array(
		'R' => "FreeMono.ttf",
		'B' => "FreeMonoBold.ttf",
		'I' => "FreeMonoOblique.ttf",
		'BI' => "FreeMonoBoldOblique.ttf",
	),
	/* OCR-B font for Barcodes */
	"ocrb" => array(
		'R' => "ocrb10.ttf",
	),
	/* Miscellaneous language font(s) */
	"estrangeloedessa" => array(/* Syriac */
		'R' => "SyrCOMEdessa.otf",
		'useOTL' => 0xFF,
	),
	"kaputaunicode" => array(/* Sinhala  */
		'R' => "kaputaunicode.ttf",
		'useOTL' => 0xFF,
	),
	"abyssinicasil" => array(/* Ethiopic */
		'R' => "Abyssinica_SIL.ttf",
		'useOTL' => 0xFF,
	),
	"aboriginalsans" => array(/* Cherokee and Canadian */
		'R' => "AboriginalSansREGULAR.ttf",
	),
	"jomolhari" => array(/* Tibetan */
		'R' => "Jomolhari.ttf",
		'useOTL' => 0xFF,
	),
	"sundaneseunicode" => array(/* Sundanese */
		'R' => "SundaneseUnicode-1.0.5.ttf",
		'useOTL' => 0xFF,
	),
	"taiheritagepro" => array(/* Tai Viet */
		'R' => "TaiHeritagePro.ttf",
	),
	"aegean" => array(
		'R' => "Aegean.otf",
		'useOTL' => 0xFF,
	),
	"aegyptus" => array(
		'R' => "Aegyptus.otf",
		'useOTL' => 0xFF,
	),
	"akkadian" => array(/* Cuneiform */
		'R' => "Akkadian.otf",
		'useOTL' => 0xFF,
	),
	"quivira" => array(
		'R' => "Quivira.otf",
		'useOTL' => 0xFF,
	),
	"eeyekunicode" => array(/* Meetei Mayek */
		'R' => "Eeyek.ttf",
	),
	"lannaalif" => array(/* Tai Tham */
		'R' => "lannaalif-v1-03.ttf",
		'useOTL' => 0xFF,
	),
	"daibannasilbook" => array(/* New Tai Lue */
		'R' => "DBSILBR.ttf",
	),
	"garuda" => array(/* Thai */
		'R' => "Garuda.ttf",
		'B' => "Garuda-Bold.ttf",
		'I' => "Garuda-Oblique.ttf",
		'BI' => "Garuda-BoldOblique.ttf",
		'useOTL' => 0xFF,
	),
	"khmeros" => array(/* Khmer */
		'R' => "KhmerOS.ttf",
		'useOTL' => 0xFF,
	),
	"dhyana" => array(/* Lao fonts */
		'R' => "Dhyana-Regular.ttf",
		'B' => "Dhyana-Bold.ttf",
		'useOTL' => 0xFF,
	),
	"tharlon" => array(/* Myanmar / Burmese */
		'R' => "Tharlon-Regular.ttf",
		'useOTL' => 0xFF,
	),
	"padaukbook" => array(/* Myanmar / Burmese */
		'R' => "Padauk-book.ttf",
		'useOTL' => 0xFF,
	),
	"zawgyi-one" => array(/* Myanmar / Burmese */
		'R' => "ZawgyiOne.ttf",
		'useOTL' => 0xFF,
	),
	"ayar" => array(/* Myanmar / Burmese */
		'R' => "ayar.ttf",
		'useOTL' => 0xFF,
	),
	"taameydavidclm" => array(/* Hebrew with full Niqud and Cantillation */
		'R' => "TaameyDavidCLM-Medium.ttf",
		'useOTL' => 0xFF,
	),
	/* SMP */
	"mph2bdamase" => array(
		'R' => "damase_v.2.ttf",
	),
	/* Indic */
	"lohitkannada" => array(
		'R' => "Lohit-Kannada.ttf",
		'useOTL' => 0xFF,
	),
	"pothana2000" => array(
		'R' => "Pothana2000.ttf",
		'useOTL' => 0xFF,
	),
	/* Arabic fonts */
	"xbriyaz" => array(
		'R' => "XB Riyaz.ttf",
		'B' => "XB RiyazBd.ttf",
		'I' => "XB RiyazIt.ttf",
		'BI' => "XB RiyazBdIt.ttf",
		'useOTL' => 0xFF,
		'useKashida' => 75,
	),
	"lateef" => array(/* Sindhi, Pashto and Urdu */
		'R' => "LateefRegOT.ttf",
		'useOTL' => 0xFF,
		'useKashida' => 75,
	),
	"kfgqpcuthmantahanaskh" => array(/* KFGQPC Uthman Taha Naskh - Koranic */
		'R' => "Uthman.otf",
		'useOTL' => 0xFF,
		'useKashida' => 75,
	),
	/* CJK fonts */
	"sun-exta" => array(
		'R' => "Sun-ExtA.ttf",
		'sip-ext' => 'sun-extb', /* SIP=Plane2 Unicode (extension B) */
	),
	"sun-extb" => array(
		'R' => "Sun-ExtB.ttf",
	),
	"unbatang" => array(/* Korean */
		'R' => "UnBatang_0613.ttf",
	),
);


// Add fonts to this array if they contain characters in the SIP or SMP Unicode planes
// but you do not require them. This allows a more efficient form of subsetting to be used.
$this->BMPonly = array(
	"dejavusanscondensed",
	"dejavusans",
	"dejavuserifcondensed",
	"dejavuserif",
	"dejavusansmono",
);

// These next 3 arrays do two things:
// 1. If a font referred to in HTML/CSS is not available to mPDF, these arrays will determine whether
//    a serif/sans-serif or monospace font is substituted
// 2. The first font in each array will be the font which is substituted in circumstances as above
//     (Otherwise the order is irrelevant)
// Use the mPDF font-family names i.e. lowercase and no spaces (after any translations in $fonttrans)
// Always include "sans-serif", "serif" and "monospace" etc.
$this->sans_fonts = array('dejavusanscondensed', 'sans', 'sans-serif', 'cursive', 'fantasy', 'dejavusans', 'freesans', 'liberationsans',
	'arial', 'helvetica', 'verdana', 'geneva', 'lucida', 'arialnarrow', 'arialblack', 'arialunicodems',
	'franklin', 'franklingothicbook', 'tahoma', 'garuda', 'calibri', 'trebuchet', 'lucidagrande', 'microsoftsansserif',
	'trebuchetms', 'lucidasansunicode', 'franklingothicmedium', 'albertusmedium', 'xbriyaz', 'albasuper', 'quillscript',
	'humanist777', 'humanist777black', 'humanist777light', 'futura', 'hobo', 'segoeprint'
);

$this->serif_fonts = array('dejavuserifcondensed', 'serif', 'dejavuserif', 'freeserif', 'liberationserif',
	'timesnewroman', 'times', 'centuryschoolbookl', 'palatinolinotype', 'centurygothic',
	'bookmanoldstyle', 'bookantiqua', 'cyberbit', 'cambria',
	'norasi', 'charis', 'palatino', 'constantia', 'georgia', 'albertus', 'xbzar', 'algerian', 'garamond',
);

$this->mono_fonts = array('dejavusansmono', 'mono', 'monospace', 'freemono', 'liberationmono', 'courier', 'ocrb', 'ocr-b', 'lucidaconsole',
	'couriernew', 'monotypecorsiva'
);
