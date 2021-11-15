mPDF 8.0.x
===========================

* Ability to customize User-Agent header in the HTTP requests sent by cURL (@samuelecat, #1229)
* Add Page Number Myanmar Language Support (@MinKyawNyunt, #1201)
* new `Mpdf\Exception\FontException` extending base `MpdfException` was introduced and is thrown on Font manipulation
* A bit cleaner exception messages for font-related errors
* Use atomic cache writing. (@PATROMO, #1186)
* Fix: "Undefined index: group" when calling MultiCell when using font without OTL data (@Kekos, #1213, #941)
* Add C128RAW barcode type to create any barcode (ex: subtype change in middle of barcode) (#1124)
* Add proxy support to curl
* Fixed date and time format in the informations dictionary (#1083, @peterdevpl)
* Checking allowed stream wrappers in CssManager
* PHP 7.4 support (until final 7.4 release with composer --ignore-platform-reqs)
* Improve debugging of remote content issues (@ribeirobreno)
* Added `exposeVersion` configuration variable allowing to hide mPDF version from Producer tag and HTTP headers
* Added the check for JPEG SOF header 0xFF 0xC1 (extended) (@jamiejones85)
* Allows setting `none` as zoom mode in `SetDisplayMode` method, so that OpenAction is not written (#602)
* Allowed image stream whitelist to be customised (#1005, thanks @jakejackson)
* Fixed parsing of top-left-bottom-right CSS rules with !important (#1009)
* Fixed skipping ordered list numbering with page-break-inside: avoid (#339)
* Compound classes selector support, like `.one.two` or `div.message.special` (#538, @peterdevpl)
* Fixed CMYK colors in text-shadow (#1115, @lexilya)
* Skip non supported wrappers when resolving paths (#1204, @MarkVaughn)
* Fixed SVGs using a style tag, has styles ignored ( Requires ext-dom ) (#450, @antman3351)
* Allows `{nb}`, `{nbpg}`, `{PAGENO}` and `{DATE ...}` substitution in body (#172 and #267, @Dasc3er)
* Cache now creates a dedicated subdirectory `/mpdf`.
* It is possible to disable automatic cache cleanup with `cacheCleanupInterval` config variable
* PHP 8.0 is supported since 8.0.10 (#1263)

mPDF 8.0.0
===========================

### 15/03/2019

* Updated FPDI dependency to version 2 (thanks a lot, @JanSlabon)
    - removed `SetImportUse` method
    - case of `ImportPage` method changed to `importPage`
    - similarly, case of `setSourceFile` and `useTemplate` was changed to a lowercase first letter.
    - signature of `importPage` changed
    - returned value of `useTemplate` changed
* Moved QRCode generating code portions to external package _mpdf/qrcode_
    - This reduced package size considerably (ca 6MB)
* Fraction sizes without leading zeros allowed for font sizes (#973, thanks @peterdevpl)
* WriteHTML is now strict about used `$mode` parameter (#915, thanks, @tomtomau)
* Fixed regression in nested tables (#860, thanks, @machour)
* Scientific notation handling in CSS font sizes (#753, thanks, @peterdevpl)


mPDF 7.1.x
===========================

* PHAR security issue fixed (thanks, @jakejackson)
* Font temporary data saved as JSON instead of generating PHP files (thanks, @jakejackson)
* cURL handling enhancements (thanks, @jakejackson)
* SVG parsing fixes (thanks, @achretien)
* Write PDF content with *Writer service classes
* PHP 7.3 is supported
* Added myclabs/deepcopy dependency, fixed TOC page numbering (thanks, @jakejackson)
* Custom color for QR codes
* Added support for orientation config key
* Code and tests cleanups and enhancements
    - PHPUnit dedicated assertions (thanks, @carusogabriel)
    - WriteHTML part constants (thanks, @tomtomau)
    - Various notice fixes (kudos to all respective authors)

mPDF 7.0.x
===========================

* Allow passing file content or file path to `SetAssociatedFiles` (#558)
* Allowed ^1.4 and ^2.0 of paragon/random_compat to allow wider usage
* Fix of undefined _getImage function (#539)
* Code cleanup
* Better writable rights for temp dir validation (#534)
* Fix displaying dollar character in footer with core fonts (#520)
* Fixed missed code2utf call (#531)
* Refactored and cleaned-up classes and subnamespaces


mPDF 7.0.0
===========================

### 19/10/2017

Backward incompatible changes
-----------------------------

- PHP `^5.6 || ~7.0.0 || ~7.1.0 || ~7.2.0` is required.
- Entire project moved under `Mpdf` namespace
    - Practically all classes renamed to use `PascalCase` and named to be more verbose
    - Changed directory structure to comply to `PSR-4`
- Removed explicit require calls, replaced with Composer autoloading
- Removed configuration files
    - All configuration now done via `__construct` parameter (see below)
- Changed `\Mpdf\Mpdf` constructor signature
    - Class now accepts only single array `$config` parameter
    - Array keys are former `config.php` and `config_fonts.php` properties
    - Additionally, former constructor parameters can be used as keys
- `tempDir` directory now must be writable, otherwise an exception is thrown
- ICC profile is loaded as entire path to file (to prevent a need to write inside vendor directory)
- Moved examples to separate repository
- Moved `TextVars` constants to separate class
- Moved border constants to separate class
- `scriptToLang` and `langToFont` in separate interfaced class methods
- Will now throw an exception when `mbstring.func_overload` is set
- Moved Glyph operator `GF_` constants in separate `\Mpdf\Fonts\GlyphOperator` class
- All methods in Barcode class renamed to camelCase including public `dec_to_hex` and `hex_to_dec`
- Decimal conversion methods (to roman, cjk, etc.) were moved to classes in `\Mpdf\Conversion` namespace
- Images in PHP variables (`<img src="var:smileyface">`) were moved from direct Mpdf properties to `Mpdf::$imageVars` public property array
- Removed global `_SVG_AUTOFONT` and `_SVG_CLASSES` constants in favor of `svgAutoFont` and `svgClasses` configuration keys
- Moved global `_testIntersect`, `_testIntersectCircle` and `calc_bezier_bbox` fucntions inside `Svg` class as private methods.
    - Changed names to camelCase without underscores and to `computeBezierBoundingBox`
- Security: Embedded files via `<annotation>` custom tag must be explicitly allowed via `allowAnnotationFiles` configuration key
- `fontDir` property of Mpdf class is private and must be accessed via configuration variable with array of paths or `AddFontDirectory` method
- QR code `<barcode>` element now treats `\r\n` and `\n` as actual line breaks
- cURL is prefered over socket when downloading images.
- Removed globally defined functions from `functions.php` in favor of `\Mpdf\Utils` classes `PdfDate` and `UtfString`.
    - Unused global functions were removed entirely.


Removed features
----------------

- Progressbar support
- JpGraph support
- `error_reporting` changes
- Timezone changes
- `compress.php` utility
- `_MPDF_PATH` and `_MPDF_URI` constants
- `_MPDF_TEMP_PATH` constant in favor of `tempDir` configuration variable
- `_MPDF_TTFONTDATAPATH` in  favor of `tempDir` configuration variable
- `_MPDFK` constant in favor of `\Mpdf\Mpdf::SCALE` class constant
- `FONT_DESCRIPTOR` constant in favor of `fontDescriptor` configuration variable
- `_MPDF_SYSTEM_TTFONTS` constant in favor of `fontDir` configuration variable with array of paths or `AddFontDirectory` method
- HTML output of error messages and debugs
- Formerly deprecated methods


Fixes and code enhancements
----------------------------

- Fixed joining arab letters
- Fixed redeclared `unicode_hex` function
- Converted arrays to short syntax
- Refactored and tested color handling with potential conversion fixes in `hsl*()` color definitions
- Refactored `Barcode` class with separate class in `Mpdf\Barcode` namespace for each barcode type
- Fixed colsum calculation for different locales (by @flow-control in #491)
- Image type guessing from content separated to its own class


New features
------------

- Refactored caching (custom `Cache` and `FontCache` classes)
- Implemented `Psr\Log\LoggerAware` interface
    - All debug and additional messages are now sent to the logger
    - Messages can be filtered based on `\Mpdf\Log\Context` class constants
- `FontFileFinder` class allowing to specify multiple paths to search for fonts
- `MpdfException` now extends `ErrorException` to allow specifying place in code where error occured
- Generating font metrics moved to separate class
- Added `\Mpdf\Output\Destination` class with verbose output destination constants
- Availability to set custom default CSS file
- Availability to set custom hyphenation dictionary file
- Refactored code portions to new "separate" classes:
    - `Mpdf\Color\*` classes
        - `ColorConvertor`
        - `ColorModeConvertor`
        - `ColorSpaceRestrictor`
    - `Mpdf\SizeConvertor`
    - `Mpdf\Hyphenator`
    - `Mpdf\Image\ImageProcessor`
    - `Mpdf\Image\ImageTypeGuesser`
    - `Mpdf\Conversion\*` classes
- Custom watermark angle with `watermarkAngle` configuration variable
- Custom document properties (idea by @zarubik in #142)
- PDF/A-3 associated files + additional xmp rdf (by @chab in #130)
- Additional font directories can be added via `addFontDir` method
- Introduced `cleanup` method which restores original `mb_` encoding settings (see #421)
- QR code `<barcode>` element now treats `\r\n` and `\n` as actual line breaks
- Customizable following of 3xx HTTP redirects, validation of SSL certificates, cURL timeout.
    - `curlFollowLocation`
    - `curlAllowUnsafeSslRequests`
    - `curlTimeout`
- QR codes can be generated without a border using `disableborder="1"` HTML attribute in `<barcode>` tag


Git repository enhancements
---------------------------

- Added contributing guidelines
- Added Issue template


mPDF 6.1.0
===========================

### 26/04/2016

- Composer updates
    - First release officially supporting Composer
    - Updated license in composer.json
    - Chmod 777 on dirs `ttfontdata`, `tmp`, `graph_cache` after composer install
- Requiring PHP 5.4.0+ with Composer
- Code style
    - Reformated (almost) all PHP files to keep basic code style
    - Removed trailing whitespaces
    - Converted all txt, php, css, and htm files to utf8
    - Removed closing PHP tags
    - Change all else if calls to elseif
- Added base PHPUnit tests
- Added Travis CI integration with unit tests
- Changed all `mPDF::Error` and `die()` calls to throwing `MpdfException`
- PDF Import changes
    - FPDI updated to 1.6.0 to fix incompatible licenses
    - FPDI loaded from Composer or manually only
- Removed iccprofiles/CMYK directory
- Renamed example files: change spaces to underscores to make scripting easier
- Fixed `LEDGER` and `TABLOID` paper sizes
- Implemented static cache for mpdf function `ConvertColor`.
- Removed PHP4 style constructors
- Work with HTML tags separated to `Tag` class
- Fixed most Strict standards PHP errors
- Add config constant so we can define custom font data
- HTML
    - fax & tel support in href attribute
    - Check $html in `$mpdf->WriteHTML()` to see if it is an integer, float, string, boolean or
      a class with `__toString()` and cast to a string, otherwise throw exception.
- PHP 7
    - Fix getting image from internal variable in PHP7 (4dcc2b4)
    - Fix PHP7 Fatal error: `'break' not in the 'loop' or 'switch' context` (002bb8a)
- Fixed output file name for `D` and `I` output modes (issue #105, f297546)

mPDF 6.0
===========================

### 20/12/2014

New features / Improvements
---------------------------
- Support for OpenTypeLayout tables / features for complex scripts and Advances Typography.
- Improved bidirectional text handling.
- Improved line-breaking, including for complex scripts e.g. Lao, Thai and Khmer.
- Updated page-breaking options.
- Automatic language mark-up and font selection using autoScriptToLang and autoLangToFont.
- Kashida for text-justification in arabic scripts.
- Index collation for non-ASCII characters.
- Index mark-up allowing control over layout using CSS.
- `{PAGENO}` and `{nbpg}` can use any of the number types as in list-style e.g. set in `<pagebreak>` using pagenumstyle.
- CSS support for lists.
- Default stylesheet - `mpdf.css` - updated.

Added CSS support
-----------------
- lang attribute selector e.g. :lang(fr), [lang="fr"]
- font-variant-position
- font-variant-caps
- font-variant-ligatures
- font-variant-numeric
- font-variant-alternates - Only [normal | historical-forms] supported (i.e. most are NOT supported)
- font-variant - as above, and except for: east-asian-variant-values, east-asian-width-values, ruby
- font-language-override
- font-feature-settings
- text-outline is now supported on TD/TH tags
- hebrew, khmer, cambodian, lao, and cjk-decimal recognised as values for "list-style-type" in numbered lists and page numbering.
- list-style-image and list-style-position
- transform (on `<img>` only)
- text-decoration:overline
- image-rendering
- unicode-bidi (also `<bdi>` tag)
- vertical-align can use lengths e.g. 0.5em
- line-stacking-strategy
- line-stacking-shift

mPDF 5.7.4
================

### 15/12/2014

Bug Fixes & Minor Additions
---------------------------
- SVG images now support embedded images e.g. `<image xlink:href="image.png" width="100px" height="100px" />`
- SVG images now supports `<tspan>` element e.g. `<tspan x,y,dx,dy,text-anchor >`, and also `<tref>`
- SVG images now can use Autofont (see top of `classes/svg.php` file)
- SVG images now has limited support for CSS classes (see top of `classes/svg.php` file)
- SVG images - style inheritance improved
- SVG images - improved handling of comments and other extraneous code
- SVG images - fix to ensure opacity is reset before another element
- SVG images - font-size not resetting after a `<text>` element
- SVG radial gradients bug (if the focus [fx,fy] lies outside circle defined by [cx,cy] and r) cf. pservers-grad-15-b.svg
- SVG allows spaces in attribute definitions in `<use>` or `<defs>` e.g. `<use x = "0" y = "0" xlink:href = "#s3" />`
- SVG text which contains a `<` sign, it will break the text - now processed as `&lt;` (despite the fact that this does not conform to XML spec)
- SVG images - support automatic font selection and (minimal) use of CSS classes - cf. the defined constants at top of svg.php file
- SVG images - text-anchor now supported as a CSS style, as well as an HTML attribute
- CSS support for :nth-child() selector improved to fully support the draft CSS3 spec - http://www.w3.org/TR/selectors/#nth-child-pseudo
    [NB only works on table columns or rows]
- text-indent when set as "em" - incorrectly calculated if last text in line in different font size than for block
- CSS not applying cascaded styles on `<A>` elements - [changed MergeCSS() type to INLINE for 'A', LEGEND, METER and PROGRESS]
- fix for underline/strikethrough/overline so that line position(s) are based correctly on font-size/font in nested situations
- Error: Strict warning: Only variables should be passed by reference - in PHP5.5.9
- bug accessing images from some servers (HTTP 403 Forbidden whn accessed using fopen etc.)
- Setting page format incorrectly set default twice and missed some options
- bug fixed in Overwrite() when specifying replacement as a string
- barcode C93 - updated C93 code from TCPDF because of bug - incorrect checksum character for "153-2-4"
- Tables - bug when using colspan across columns which may have a cell width specified
    cf. http://www.mpdf1.com/forum/discussion/2221/colspan-bug
- Tables - cell height (when specified) is not resized when table is shrunk
- Tables - if table width specified, but narrower than minimum cell wdith, and less than page width - table will expand to
    minimum cell width(s) as long as $keep_table_proportions = true
- Tables - if using packTableData, and borders-collapse, wider border is overwriting content of adjacent cell
    Test case:
    ```
    <table style="border-collapse: collapse;">
    <tr><td style="border-bottom: 42px solid #0FF; "> Hallo world </td></tr>
    <tr><td style="border-top: 14px solid #0F0; "> Hallo world </td></tr>
    </table>
    ```
- Images - image height is reset proportional to original if width is set to maximum e.g. `<img width="100%" height="20mm">`
- URL handling changed to work with special characters in path fragments; affects `<a>` links, `<img>` images and
    CSS url() e.g background-image
    - also to ignore `../` included as a query value
- Barcodes with bottom numerals e.g. EAN-13 - incorrect numeral size when using core fonts

--------------------------------

NB Spec. for embedded SVG images:
as per http://www.w3.org/TR/2003/REC-SVG11-20030114/struct.html#ImageElement
Attributes supported:
- x
- y
- xlink:href (required) - can be jpeg, png or gif image - not vector (SVG or WMF) image
- width (required)
- height (required)
- preserveAspectRatio

Note: all attribute names and values are case-sensitive
width and height cannot be assigned by CSS - must be attributes

mPDF 5.7.3
================

### 24/8/2014

Bug Fixes & Minor Additions
---------------------------

- Tables - cellSpacing and cellPadding taking preference over CSS stylesheet
- Tables - background images in table inside HTML Footer incorrectly positioned
- Tables - cell in a nested table with a specified width, should determine width of parent table cell
    (cf. http://www.mpdf1.com/forum/discussion/1648/nested-table-bug-)
- Tables - colspan (on a row after first row) exceeds number of columns in table
- Gradients in Imported documents (mPDFI) causing error in some browsers
- Fatal error after page-break-after:always on root level block element
- Support for 'https/SSL' if file_get_contents_by_socket required (e.g. getting images with allow_url_fopen turned off)
- Improved support for specified ports when getting external CSS stylesheets e.g. www.domain.com:80
- error accessing local .css files with dummy queries (cache-busting) e.g. mpdfstyleA4.css?v=2.0.18.9
- start of end tag in PRE incorrectly changed to &lt;
- error thrown when open.basedir restriction in effect (deleting temporary files)
- image which forces pagebreak incorrectly positioned at top of page
- [changes to avoid warning notices by checking if (isset(x)) before referencing it]
- text with letter-spacing set inside table which needs to be resixed (shrunk) - letter-spacing was not adjusted
- nested table incorrectly calculating width and unnecessarily wrapping text
- vertical-align:super|sub can be nested using `<span>` elements
- inline elements can be nested e.g. text `<sup>text<sup>13</sup>text</sup>` text
- CSS vertical-align:0.5em (or %) now supported
- underline and strikethrough now use the parent inline block baseline/fontsize/color for child inline elements *** change in behaviour
    (Adjusts line height to take account of superscript and subscript except in tables)
- nested table incorrectly calculating width and unnecessarily wrapping text
- tables - font size carrying over from one nested table to the next nested table
- tables - border set as attribute on `<TABLE>` overrides border set as CSS on `<TD>`
- tables - if table width set to 100% and one cell/column is empty with no padding/border, sizing incorrectly
    (http://www.mpdf1.com/forum/discussion/1886/td-fontsize-in-nested-table-bug-#Item_5)
- `<main>` added as recognised tag
- CSS style transform supported on `<img>` element (only)
    All transform functions are supported except matrix() i.e. translate(), translateX(), translateY(), skew(), skewX(), skewY(),
    scale(), scaleX(), scaleY(), rotate()
    NB When using Columns or Keep-with-table (use_kwt), cannot use transform
- CSS background-color now supported on `<img>` element
- @page :first not recognised unless @page {} has styles set
- left/right margins not allowed on @page :first

mPDF 5.7.2
================

### 28/12/2013

Bug Fixes
---------

- `<tfoot>` not printing at all (since v5.7)
- list-style incorrectly overriding list-style-type in cascading CSS
- page-break-after:avoid not taking into account bottom padding and margin when estimating if next line can fit on page
- images not displayed when using "https://" if images are referenced by src="//domain.com/image"
- +aCJK incorrectly parsed when instantiating class e.g. new mpDF('ja+aCJK')
- line-breaking - zero-width object at end of line (e.g. index entry) causing a space left untrimmed at end of line
- ToC since v5.7 incorrectly handling non-ascii characters, entities or tags
- cell height miscalculated when using hard-hyphenate
- border colors set with transparency not working
- transparency settings for stroke and fill interfering with one another
- 'float' inside a HTML header/footer - not clearing the float before first line of text
- error if script run across date change at midnight
- temporary file name collisions (e.g. when processing images) if numerous users
- `<watermarkimage>` position attribute not working
- `<` (less-than sign) inside a PRE element, and NOT start of a valid tag, was incorrectly removed
- file attachments not opening in Reader XI
- JPG images not recognised if not containing JFIF or Exif markers
- instance of preg_replace with /e modifier causing error in PHP 5.5
- correctly handle CSS URLs with no scheme
- Index entries causing errors when repeat entries are used within page-break-inside:avoid, rotated tables etc.
- table with fixed width column and long word in cell set to colspan across this column (adding spare width to all columns)
- incorrect hyphenation if multiple soft-hyphens on line before break
- SVG images - objects contained in `<defs>` being displayed
- SVG images - multiple, or quoted fonts e.g. style="font-family:'lucida grande', verdana" not recognised
- SVG images - line with opacity=0 still visible (only in some PDF viewers/browsers)
- text in an SVG image displaying with incorrect font in some PDF viewers/browsers
- SVG images - fill:RGB(0,0,0) not recognised when uppercase
- background images using data:image\/(jpeg|gif|png);base64 format - error when reading in stylesheet

New CSS support
---------------

- added support for style="opacity:0.6;" in SVG images - previously only supported style="fill-opacity:0.6; stroke-opacity: 0.6;"
- improved PNG image handling for some cases of alpha channel transparency
- khmer, cambodian and lao recognised as list-style-type for numbered lists

SVG Images
----------

- Limited support for `<use>` and `<defs>`

mPDF 5.7.1
================
## 01/09/2013

1) FILES: mpdf.php

Bug fix; Dollar sign enclosed by `<pre>` tag causing error.
Test e.g.: `<pre>Test $1.00 Test</pre> <pre>Test $2.00 Test</pre> <pre>Test $3.00 Test</pre> <pre>Test $4.00 Test</pre>`

-----------------------------

2) FILES: includes/functions.php AND mpdf.php

Changes to `preg_replace` with `/e` modifier to use `preg_replace_callback`
(/e depracated from PHP 5.5)

-----------------------------

3) FILES: classes/barcode.php

Small change to function `barcode_c128()` which allows ASCII 0 - 31 to be used in C128A e.g. chr(13) in:
`<barcode code="5432&#013;1068" type="C128A" />`

-----------------------------

4) FILES: mpdf.php

Using $use_kwt ("keep-[heading]-with-table") if `<h4></h4>` before table is on 2 lines and pagebreak occurs after first line
the first line is displayed at the bottom of the 2nd page.
Edited so that $use_kwt only works if the HEADING is only one line. Else ignores (but prints correctly)

-----------------------------

5) FILES: mpdf.php

Clearing old temporary files from `_MPDF_TEMP_PATH` will now ignore "hidden" files e.g. starting with a "`.`" `.htaccess`, `.gitignore` etc.
and also leave `dummy.txt` alone


mPDF 5.7
===========================

### 14/07/2013

Files changed
-------------
- config.php
- mpdf.php
- classes/tocontents.php
- classes/cssmgr.php
- classes/svg.php
- includes/functions.php
- includes/out.php
- examples/formsubmit.php [Important - Security update]

Updated Example Files in /examples/
-----------------------------------

- All example files
- mpdfstyleA4.css

config.php
----------

Removed:
- $this->hyphenateTables
- $this->hyphenate
- $this->orphansAllowed
Edited:
- "hyphens: manual" - Added to $this->defaultCSS
- $this->allowedCSStags now includes '|TEXTCIRCLE|DOTTAB'
New:
- $this->decimal_align = array('DP'=>'.', 'DC'=>',', 'DM'=>"\xc2\xb7", 'DA'=>"\xd9\xab", 'DD'=>'-');
- $this->h2toc = array('H1'=>0, 'H2'=>1, 'H3'=>2);
- $this->h2bookmarks = array('H1'=>0, 'H2'=>1, 'H3'=>2);
- $this->CJKforceend = false; // Forces overflowng punctuation to hang outside right margin (used with CJK script)


Backwards compatability
-----------------------

Changes in mPDF 5.7 may cause some changes to the way your documents appear. There are two main differences:
1) Hyphenation. To retain appearance compatible with earlier versions, set the CSS property "hyphens: auto" whenever
    you previously used $mpdf->hyphenate=true;
2) Table of Contents - appearance can now be controlled with CSS styles. By default, in mPDF 5.7, no styling is applied so you will get:
    - No indent (previous default of 5mm) - ($tocindent is ignored)
    - Any font, font-size set ($tocfont or $tocfontsize) will not work
    - HyperLinks will appear with your default appearance - usually blue and underlined
    - line spacing will be narrower (can use line-height or margin-top in CSS)

New features / Improvements
---------------------------
- Layout of Table of Content ToC now controlled using CSS styles
- Text alignment on decimal mark inside tables
- Automatically generated bookmarks and/or ToC entries from H1 - H6 tags
- Support for unit of "rem" as size e.g. font-size: 1rem;
- Origin and clipping for background images and gradients controlled by CSS i.e. background-origin, background-size, background-clip
- Text-outline controlled by CSS (compatible with CSS3 spec.)
- Use of `<dottab>` enhanced by custom CSS "outdent" property
- Image HTML attributes `<img>` added: max-height, max-width, min-height and min-width
- Spotcolor can now be defined as it is used e.g. color: spot(PANTONE 534 EC, 100%, 85, 65, 47, 9);
- Lists - added support for "start" attribute in `<ol>` e.g. `<ol start="5">`
- Hyphenation controlled using CSS, consistent with CSS3 spec.
- Line breaking improved to avoid breaks within words where HTML tags are used e.g. H<sub>2<sub>0
- Line breaking in CJK scripts improved (and ability to force hanging punctuation)
- Numerals in a CJK script are kept together
- RTL improved support for phrases containing numerals and \ and /
- Bidi override codes supported - Right-to-Left Embedding [RLE] U+202B, Left-to-Right Embedding [LRE] U+202A,
    U+202C POP DIRECTIONAL FORMATTING (PDF)
- Support for `<base href="">` in HTML - uses it to SetBasePath for relative URLs.
- HTML tag - added support for `<wbr>` or `<wbr />` - converted to a soft-hyphen
- CSS now takes precedence over HTML attribute e.g. `<table bgcolor="black" style="background-color:yellow">`

Added CSS support
-----------------
- max-height, max-width, min-height and min-width for images `<img>`
- "hyphens: none|manual|auto" as per CSS3 spec.
- Decimal mark alignment e.g. text-align: "." center;
- "rem" accepted as a valid (font)size in CSS e.g. font-size: 1.5rem
- text-outline, text-outline-width and text-outline-color supported everywhere except in tables (blur not supported)
- background-origin, background-size, background-clip are now supported everywhere except in tables
- "visibility: hidden|visible|printonly|screenonly" for inline elements e.g. `<span>`
- Colors: device-cmyk(c,m,y,k) as per CSS3 spec. For consistency, device-cmyka also supported (not CSS3 spec)
- "z-index" can be used to utilise layers in the PDF document
- Custom CSS property added: "outdent" - opposite of indent

The HTML elements `<dottab>` and `<textcircle>` can now have CSS properties applied to them.

Bug fixes
---------
- SVG images - path including e.g. 1.234E-15 incorrectly parsed (not recognising capital E)
- Tables - if a table starts when the Y position on page is below bottom margin caused endless loop
- Float-ing DIVs - starting a float at bottom of page and it causes page break before anything output, second new page is forced
- Tables - Warning notice now given in Table footer or header if `<tfoot>` placed after `<tbody>` and table spans page
- Columns - block with border-width wider than the length of the border line, line overflows
- Columns - block with no padding containing a block with borders but no backgound colour, borders not printed
- Table in Columns - when background color set by surrounding block element - colour missing for height of half bottom border.
- TOCpagebreakByArray() when called by function was not adding the pagebreak
- Border around block element - dashed not showing correctly (not resetting linewidth between different edges)
- Double border in table - when background colour set in surrounding block element - shows as black line between the 2 bits of double
- Borders around DIVs - "double" border problem if not all 4 sides equally - fixed
- Borders around DIVs - solid (and double) borders overlap as in tables - now fixed so mitred joins as in browser
    [Inadvertently improves borders in Columns because of change in LineCap]
- Page numbering - $mpdf->pagenumSuffix etc not suppressed in HTML headers/footers if number suppressed
- Page numbering - Page number total {nbpg} incorrect  - e.g. showing decreasing numbers through document, when ToC present
- RTL numerals - incorrectly reversing a number followed by a comma
- Transform to uppercase/lowercase not working for chars > ASCII 128 when using core fonts
- TOCpagebreak - Not setting TOC-FOOTER
- TOCpagebreak - toc-even-header-name etc. not working
- Parsing some relative URLs incorrectly
- Textcircle - when moved to next page by "page-break-inside: avoid"
- Bookmarks will now work if jump more than one level e.g. 0,2,1  Inserts a new blank entry at level 1
- Paths to img or stylesheets - incorrectly reading "//www.domain.com" i.e. when starting with two /
- data:image as background url() - incorrectly adjusting path on server if MPDF_PATH not specified (included in release mPDF 5.6.1)
- Image problem if spaces or commas in path using http:// URL (included in release mPDF 5.6.1)
- Image URL parsing rewritten to handle both urlencoded URLs and not urlencoded (included in release mPDF 5.6.1)
- `<dottab>` fixed to allow color, font-size and font-family to be correctly used, avoid dots being moved to new page, and to work in RTL
- Table {colsum} summed figures in table header
- list-style-type (custom) colour not working
- `<tocpagebreak>` toc-preHTML and toc-postHTML can now contain quotes

mPDF 5.6
===========================

### 20/01/2013

Files changed
-------------
- mpdf.php
- config.php
- includes/functions.php
- classes/meter.php
- classes/directw.php


config.php changes
------------------

- $this->allowedCSStags - added HTML5 tags + textcircle AND
- $this->outerblocktags - added HTML5 tags
- $this->defaultCSS  - added default CSS properties

New features / Improvements
---------------------------
CSS support added for for min-height, min-width, max-height and max-width in `<img>`

Images embedded in CSS
- `<img src="data:image/gif;base64,....">` improved to make it more robust, and background: `url(data:image...` now added to work

HTML5 tags supported
- as generic block elements: `<article><aside><details><figure><figcaption><footer><header><hgroup><nav><section><summary>`
- as in-line elements: `<mark><time><meter><progress>`
- `<mark>` has a default CSS set in config.php to yellow highlight
- `<meter>` and `<progress>` support attributes as for HTML5
- custom appearances for `<meter>` and `<progress>` can be made by editing `classes/meter.php` file
- `<meter>` and `<progress>` suppress text inside the tags

Textcircle/Circular
- font: "auto" added: automatically sizes text to fill semicircle (if both set) or full circle (if only one set)
    NB for this AND ALL CSS on `<textcircle>`: does not inherit CSS styles
- attribute: divider="[characters including HTML entities]" added
- `<textcircle r="30mm" top-text="Text Circular Text Circular" bottom-text="Text Circular Text Circular"
    divider="&nbsp;&bull;&nbsp;" style="font-size: auto" />`

&raquo; &rsquo; &sbquo; &bdquo; are now included in "orphan"-management at the end of lines

Improved CJK line wrapping (if CJK character at end of line, breaks there rather than previous wordspace)

NB mPDF 5.5 added support for `<fieldset>` and `<legend>` (omitted from ChangeLog)

Bug fixes
---------

- embedded fonts: Panose string incorrectly output as decimals - changed to hexadecimal
    Only a problem in limited circumstances.
    *****Need to delete all ttfontdata/ files in order for fix to have effect.
- `<textCircle>` background white even when set to none/transparent
- border="0" causing mPDF to add border to table CELLS as well as table
- iteration counter in THEAD crashed in some circumstances
- CSS color now supports spaces in the rgb() format e.g. border: 1px solid rgb(170, 170, 170);
- CJK not working in table following changes made in v5.4
- images fixed to work with Google Chart API (now mPDF does not urldecode the query part of the src)
- CSS `<style>` within HTML page crashed if CSS is too large  (? > 32Kb)
- SVG image nested int eht HTML failed to show if code too large (? > 32Kb)
- cyrillic character p &#1088; at end of table cell caused cell height to be incorrectly calculated

mPDF 5.5
===========================

### 02/03/2012

Files changed
-------------

- mpdf.php
- classes/ttfontsuni.php
- classes/svg.php
- classes/tocontents.php
- config.php
- config_fonts.php
- utils/font_collections.php
- utils/font_coverage.php
- utils/font_dump.php

Files added
-----------

classes/ttfontsuni_analysis.php

config.php changes
------------------

To avoid just the border/background-color of the (empty) end of a block being moved on to next page (`</div></div>`)

`$this->margBuffer = 0; // Allow an (empty) end of block to extend beyond the bottom margin by this amount (mm)`

config_fonts.php changes
------------------------

Added to (arabic) fonts to allow "use non-mapped Arabic Glyphs" e.g. for Pashto
    'unAGlyphs' => true,

Arabic text
-----------

Arabic text (RTL) rewritten with improved support for Pashto/Sindhi/Urdu/Kurdish
    Presentation forms added:
    U+0649, U+0681, U+0682, U+0685, U+069A-U+069E, U+06A0, U+06A2, U+06A3, U+06A5, U+06AB-U+06AE,
    U+06B0-U+06B4, U+06B5-U+06B9, U+06BB, U+06BC, U+06BE, U+06BF, U+06C0, U+06CD, U+06CE, U+06D1, U+06D3, U+0678
    Joining improved:
    U+0672, U+0675, U+0676, U+0677, U+0679-U+067D, U+067F, U+0680, U+0683, U+0684, U+0687, U+0687, U+0688-U+0692,
    U+0694, U+0695, U+0697, U+0699, U+068F, U+06A1, U+06A4, U+06A6, U+06A7, U+06A8, U+06AA, U+06BA, U+06C2-U+06CB, U+06CF

Note - Some characters in Pashto/Sindhi/Urdu/Kurdish do not have Unicode values for the final/initial/medial forms of the characters.
However, some fonts include these characters "un-mapped" to Unicode (including XB Zar and XB Riyaz, which are bundled with mPDF).
    `'unAGlyphs' => true`, added to the config_fonts.php file for appropriate fonts will

This requires the font file to include a Format 2.0 POST table which references the glyphs as e.g. uni067C.med or uni067C.medi:
    e.g. XB Riyaz, XB Zar, Arabic Typesetting (MS), Arial (MS)

NB If you want to know if a font file is suitable, you can open a .ttf file in a text editor and search for "uni067C.med" - if it exists, it may work!
Using "unAGlyphs" forces subsetting of fonts, and will not work with SIP/SMP fonts (using characters beyond the Unicode BMP Plane).

mPDF maps these characters to part of the Private Use Area allocated by Unicode U+F500-F7FF. This could interfere with correct use
if the font already utilises these codes (unlikely).

mPDF now deletes U+200C,U+200D,U+200E,U+200F zero-widthjoiner/non-joiner, LTR and RTL marks so they will not appear
even though some fonts contain glyphs for these characters.


Other New features / Improvements
---------------------------------
Avoid just the border/background-color of the (empty) end of a block being moved on to next page (`</div></div>`)
using configurable variable: `$this->margBuffer`;


The TTFontsUni class contained a long function (extractcoreinfo) which is not used routinely in mPDF

This has been moved to a new file: classes/ttfontsuni_analysis.php.

The 3 utility scripts have been updated to use the new extended class:

- utils/font_collections.php
- utils/font_coverage.php
- utils/font_dump.php


Bug fixes
---------
- Border & background when closing 2 blocks (e.g. `</div></div>`) incorrectly being moved to next page because incorrectly
    calculating how much space required
- Fixed/Absolute-positioned elements not inheriting letter-spacing style
- Rotated cell - error if text-rotate set on a table cell, but no text content in cell
- SVG images, text-anchor not working
- Nested table - not resetting cell style (font, color etc) after nested table, if text follows immediately
- Nested table - font-size 70% set in extenal style sheet; if repeated nested tables, sets 70% of 70% etc etc
- SVG setting font-size as percent on successive `<text>` elements gives progressively smaller text
- mPDF will check if magic_quotes_runtime set ON even >= PHP 5.3 (will now cause an error message)
- not resetting after 2 nested tags of same type e.g. `<b><b>bold</b></b>` still bold
- When using charset_in other than utf-8, HTML Footers using tags e.g. `<htmlpageheader>` do not decode correctly
- ToC if nested > 3 levels, line spacing reduces and starts to overlap

Older changes can be seen [on the documentation site](https://mpdf.github.io/about-mpdf/changelog.html).
