<?php

namespace Mpdf;

use Mpdf\Color\ColorConverter;
use Mpdf\Css\TextVars;
use Mpdf\File\StreamWrapperChecker;
use Mpdf\Utils\Arrays;
use Mpdf\Utils\UtfString;

class CssManager
{

	/**
	 * @var \Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \Mpdf\Cache
	 */
	private $cache;

	/**
	 * @var \Mpdf\SizeConverter
	 */
	private $sizeConverter;

	/**
	 * @var \Mpdf\Color\ColorConverter
	 */
	private $colorConverter;

	var $tablecascadeCSS;

	var $cascadeCSS;

	var $CSS;

	var $tbCSSlvl;

	var $cell_border_dominance_B;

	var $cell_border_dominance_L;

	var $cell_border_dominance_R;

	var $cell_border_dominance_T;

	public function __construct(Mpdf $mpdf, Cache $cache, SizeConverter $sizeConverter, ColorConverter $colorConverter)
	{
		$this->mpdf = $mpdf;
		$this->cache = $cache;
		$this->sizeConverter = $sizeConverter;

		$this->tablecascadeCSS = [];
		$this->CSS = [];
		$this->cascadeCSS = [];
		$this->tbCSSlvl = 0;
		$this->colorConverter = $colorConverter;
	}

	function ReadCSS($html)
	{
		preg_match_all('/<style[^>]*media=["\']([^"\'>]*)["\'].*?<\/style>/is', $html, $m);
		$count_m = count($m[0]);
		for ($i = 0; $i < $count_m; $i++) {
			if ($this->mpdf->CSSselectMedia && !preg_match('/(' . trim($this->mpdf->CSSselectMedia) . '|all)/i', $m[1][$i])) {
				$html = str_replace($m[0][$i], '', $html);
			}
		}

		preg_match_all('/<link[^>]*media=["\']([^"\'>]*)["\'].*?>/is', $html, $m);
		$count_m = count($m[0]);
		for ($i = 0; $i < $count_m; $i++) {
			if ($this->mpdf->CSSselectMedia && !preg_match('/(' . trim($this->mpdf->CSSselectMedia) . '|all)/i', $m[1][$i])) {
				$html = str_replace($m[0][$i], '', $html);
			}
		}

		// mPDF 5.5.02
		// Remove Comment tags <!-- ... --> inside CSS as <style> in HTML document
		// Remove Comment tags /* ...  */ inside CSS as <style> in HTML document
		// But first, we replace upper and mixed case closing style tag with lower
		// case so we can use str_replace later.
		preg_match_all('/<style.*?>(.*?)<\/style>/si', $html, $m);
		$count_m = count($m[1]);
		if ($count_m) {
			for ($i = 0; $i < $count_m; $i++) {
				// Remove comment tags
				$sub = preg_replace('/(<\!\-\-|\-\->)/s', ' ', $m[1][$i]);
				$sub = '>'.preg_replace('|/\*.*?\*/|s', ' ', $sub).'</style>';
				$html = str_replace('>'.$m[1][$i].'</style>', $sub, $html);
			}
		}

		$html = preg_replace('/<!--mpdf/i', '', $html);
		$html = preg_replace('/mpdf-->/i', '', $html);
		$html = preg_replace('/<\!\-\-.*?\-\->/s', ' ', $html);

		$match = 0; // no match for instance
		$CSSext = [];

		// CSS inside external files
		$regexp = '/<link[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^>"\']*)["\'].*?>/si';
		$x = preg_match_all($regexp, $html, $cxt);
		if ($x) {
			$match += $x;
			$CSSext = $cxt[1];
		}
		$regexp = '/<link[^>]*href=["\']([^>"\']*)["\'][^>]*?rel=["\']stylesheet["\'].*?>/si';
		$x = preg_match_all($regexp, $html, $cxt);
		if ($x) {
			$match += $x;
			$CSSext = array_merge($CSSext, $cxt[1]);
		}

		// look for @import stylesheets
		// $regexp = '/@import url\([\'\"]{0,1}([^\)]*?\.css)[\'\"]{0,1}\)/si';
		// $regexp = '/@import url\([\'\"]{0,1}([^\)]*?\.css(\?\S+)?)[\'\"]{0,1}\)/si';
		$regexp = '/@import url\([\'\"]{0,1}(\S*?\.css(\?[^\s\'\"]+)?)[\'\"]{0,1}\)\;?/si';
		$x = preg_match_all($regexp, $html, $cxt);
		if ($x) {
			$match += $x;
			$CSSext = array_merge($CSSext, $cxt[1]);
		}

		// look for @import without the url()
		// $regexp = '/@import [\'\"]{0,1}([^;]*?\.css)[\'\"]{0,1}/si';
		// $regexp = '/@import [\'\"]{0,1}([^;]*?\.css(\?\S+)?)[\'\"]{0,1}/si';
		$regexp = '/@import (?!url)[\'\"]{0,1}(\S*?\.css(\?[^\s\'\"]+)?)[\'\"]{0,1}\;?/si';
		$x = preg_match_all($regexp, $html, $cxt);
		if ($x) {
			$match += $x;
			$CSSext = array_merge($CSSext, $cxt[1]);
		}

		$ind = 0;
		$CSSstr = '';

		if (!is_array($this->cascadeCSS)) {
			$this->cascadeCSS = [];
		}

		while ($match) {

			$path = $CSSext[$ind];

			$path = htmlspecialchars_decode($path); // mPDF 6

			$this->mpdf->GetFullPath($path);

			$CSSextblock = $this->getFileContents($path);
			if ($CSSextblock) {
				// look for embedded @import stylesheets in other stylesheets
				// and fix url paths (including background-images) relative to stylesheet
				// $regexpem = '/@import url\([\'\"]{0,1}(.*?\.css)[\'\"]{0,1}\)/si';
				$regexpem = '/@import url\([\'\"]{0,1}(.*?\.css(\?\S+)?)[\'\"]{0,1}\)/si';
				$xem = preg_match_all($regexpem, $CSSextblock, $cxtem);
				$cssBasePath = preg_replace('/\/[^\/]*$/', '', $path) . '/';
				if ($xem) {
					foreach ($cxtem[1] as $cxtembedded) {
						// path is relative to original stylesheet!!
						$this->mpdf->GetFullPath($cxtembedded, $cssBasePath);
						$match++;
						$CSSext[] = $cxtembedded;
					}
				}
				$regexpem = '/(background[^;]*url\s*\(\s*[\'\"]{0,1})([^\)\'\"]*)([\'\"]{0,1}\s*\))/si';
				$xem = preg_match_all($regexpem, $CSSextblock, $cxtem);
				if ($xem) {
					$count_cxtem = count($cxtem[0]);
					for ($i = 0; $i < $count_cxtem; $i++) {
						// path is relative to original stylesheet!!
						$embedded = $cxtem[2][$i];
						if (!preg_match('/^data:image/i', $embedded)) { // mPDF 5.5.13
							$this->mpdf->GetFullPath($embedded, $cssBasePath);
							$CSSextblock = str_replace($cxtem[0][$i], ($cxtem[1][$i] . $embedded . $cxtem[3][$i]), $CSSextblock);
						}
					}
				}
				$CSSstr .= ' ' . $CSSextblock;
			}
			$match--;
			$ind++;
		}

		// CSS as <style> in HTML document
		$regexp = '/<style.*?>(.*?)<\/style>/si';
		$match = preg_match_all($regexp, $html, $CSSblock);
		if ($match) {
			$tmpCSSstr = implode(' ', $CSSblock[1]);
			$regexpem = '/(background[^;]*url\s*\(\s*[\'\"]{0,1})([^\)\'\"]*)([\'\"]{0,1}\s*\))/si';
			$xem = preg_match_all($regexpem, $tmpCSSstr, $cxtem);
			if ($xem) {
				$count_cxtem = count($cxtem[0]);
				for ($i = 0; $i < $count_cxtem; $i++) {
					$embedded = $cxtem[2][$i];
					if (!preg_match('/^data:image/i', $embedded)) { // mPDF 5.5.13
						$this->mpdf->GetFullPath($embedded);
						$tmpCSSstr = str_replace($cxtem[0][$i], ($cxtem[1][$i] . $embedded . $cxtem[3][$i]), $tmpCSSstr);
					}
				}
			}
			$CSSstr .= ' ' . $tmpCSSstr;
		}

		// Remove comments
		$CSSstr = preg_replace('|/\*.*?\*/|s', ' ', $CSSstr);
		$CSSstr = preg_replace('/[\s\n\r\t\f]/s', ' ', $CSSstr);

		if (preg_match('/@media/', $CSSstr)) {
			preg_match_all('/@media(.*?)\{(([^\{\}]*\{[^\{\}]*\})+)\s*\}/is', $CSSstr, $m);
			$count_m = count($m[0]);
			for ($i = 0; $i < $count_m; $i++) {
				if ($this->mpdf->CSSselectMedia && !preg_match('/(' . trim($this->mpdf->CSSselectMedia) . '|all)/i', $m[1][$i])) {
					$CSSstr = str_replace($m[0][$i], '', $CSSstr);
				} else {
					$CSSstr = str_replace($m[0][$i], ' ' . $m[2][$i] . ' ', $CSSstr);
				}
			}
		}

		// Replace any background: url(data:image... with temporary image file reference
		preg_match_all("/(url\(data:image\/(jpeg|gif|png);base64,(.*?)\))/si", $CSSstr, $idata); // mPDF 5.7.2
		$count_idata = count($idata[0]);
		if ($count_idata) {
			for ($i = 0; $i < $count_idata; $i++) {
				$file = $this->cache->write('_tempCSSidata' . random_int(1, 10000) . '_' . $i . '.' . $idata[2][$i], base64_decode($idata[3][$i]));
				$CSSstr = str_replace($idata[0][$i], 'url("' . $file . '")', $CSSstr);  // mPDF 5.5.17
			}
		}

		$CSSstr = preg_replace('/(<\!\-\-|\-\->)/s', ' ', $CSSstr);

		// mPDF 5.7.4 URLs
		// Characters "(" ")" and ";" in url() e.g. background-image, cause problems parsing the CSS string
		// URLencode ( and ), but change ";" to a code which can be converted back after parsing (so as not to confuse ;
		// with a segment delimiter in the URI)
		$tempmarker = '%ZZ';
		if (strpos($CSSstr, 'url(') !== false) {
			preg_match_all('/url\(\"(.*?)\"\)/', $CSSstr, $m);
			$count_m = count($m[1]);
			for ($i = 0; $i < $count_m; $i++) {
				$tmp = str_replace(['(', ')', ';'], ['%28', '%29', $tempmarker], $m[1][$i]);
				$CSSstr = str_replace($m[0][$i], 'url(\'' . $tmp . '\')', $CSSstr);
			}
			preg_match_all('/url\(\'(.*?)\'\)/', $CSSstr, $m);
			$count_m = count($m[1]);
			for ($i = 0; $i < $count_m; $i++) {
				$tmp = str_replace(['(', ')', ';'], ['%28', '%29', $tempmarker], $m[1][$i]);
				$CSSstr = str_replace($m[0][$i], 'url(\'' . $tmp . '\')', $CSSstr);
			}
			preg_match_all('/url\(([^\'\"].*?[^\'\"])\)/', $CSSstr, $m);
			$count_m = count($m[1]);
			for ($i = 0; $i < $count_m; $i++) {
				$tmp = str_replace(['(', ')', ';'], ['%28', '%29', $tempmarker], $m[1][$i]);
				$CSSstr = str_replace($m[0][$i], 'url(\'' . $tmp . '\')', $CSSstr);
			}
		}

		if ($CSSstr) {

			$classproperties = []; // mPDF 6
			preg_match_all('/(.*?)\{(.*?)\}/', $CSSstr, $styles);
			$styles_count = count($styles[1]);
			for ($i = 0; $i < $styles_count; $i++) {

				// SET array e.g. $classproperties['COLOR'] = '#ffffff';
				$stylestr = trim($styles[2][$i]);
				$stylearr = explode(';', $stylestr);

				foreach ($stylearr as $sta) {
					if (trim($sta)) {
						// Changed to allow style="background: url('http://www.bpm1.com/bg.jpg')"
						$tmp = explode(':', $sta, 2);
						$property = $tmp[0];
						if (isset($tmp[1])) {
							$value = $tmp[1];
						} else {
							$value = '';
						}
						$value = str_replace($tempmarker, ';', $value); // mPDF 5.7.4 URLs
						$property = trim($property);
						$value = preg_replace('/\s*!important/i', '', $value);
						$value = trim($value);
						if ($property && ($value || $value === '0')) {
							// Ignores -webkit-gradient so doesn't override -moz-
							if ((strtoupper($property) === 'BACKGROUND-IMAGE' || strtoupper($property) === 'BACKGROUND') && false !== stripos($value, '-webkit-gradient')) {
								continue;
							}
							$classproperties[strtoupper($property)] = $value;
						}
					}
				}

				$classproperties = $this->fixCSS($classproperties);
				$tagstr = strtoupper(trim($styles[1][$i]));
				$tagarr = explode(',', $tagstr);
				$pageselectors = false; // used to turn on $this->mpdf->mirrorMargins

				foreach ($tagarr as $tg) {

					if (preg_match('/NTH-CHILD\((\s*(([\-+]?\d*)N(\s*[\-+]\s*\d+)?|[\-+]?\d+|ODD|EVEN)\s*)\)/', $tg, $m)) {
						$tg = preg_replace('/NTH-CHILD\(.*\)/', 'NTH-CHILD(' . str_replace(' ', '', $m[1]) . ')', $tg);
					}

					$tags = preg_split('/\s+/', trim($tg));
					$level = count($tags);
					$t = '';
					$t2 = '';
					$t3 = '';

					if (trim($tags[0]) === '@PAGE') {

						if (isset($tags[0])) {
							$t = trim($tags[0]);
						}

						if (isset($tags[1])) {
							$t2 = trim($tags[1]);
						}

						if (isset($tags[2])) {
							$t3 = trim($tags[2]);
						}

						$tag = '';
						if ($level === 1) {
							$tag = $t;
						} elseif ($level === 2 && preg_match('/^[:](.*)$/', $t2, $m)) {
							$tag = $t . '>>PSEUDO>>' . $m[1];
							if ($m[1] === 'LEFT' || $m[1] === 'RIGHT') {
								$pageselectors = true;
							} // used to turn on $this->mpdf->mirrorMargins
						} elseif ($level === 2) {
							$tag = $t . '>>NAMED>>' . $t2;
						} elseif ($level === 3 && preg_match('/^[:](.*)$/', $t3, $m)) {
							$tag = $t . '>>NAMED>>' . $t2 . '>>PSEUDO>>' . $m[1];
							if ($m[1] === 'LEFT' || $m[1] === 'RIGHT') {
								$pageselectors = true;
							} // used to turn on $this->mpdf->mirrorMargins
						}

						if (isset($this->CSS[$tag]) && $tag) {
							$this->CSS[$tag] = $this->array_merge_recursive_unique($this->CSS[$tag], $classproperties);
						} elseif ($tag) {
							$this->CSS[$tag] = $classproperties;
						}

					} elseif ($level === 1) {  // e.g. p or .class or #id or p.class or p#id

						if (isset($tags[0])) {
							$t = trim($tags[0]);
						}

						if ($t) {

							$tag = '';

							if (preg_match('/^[.](.*)$/', $t, $m)) {
								$classes = explode('.', $m[1]);
								sort($classes);
								$tag = 'CLASS>>' . join('.', $classes);
							} elseif (preg_match('/^[#](.*)$/', $t, $m)) {
								$tag = 'ID>>' . $m[1];
							} elseif (preg_match('/^\[LANG=[\'\"]{0,1}([A-Z\-]{2,11})[\'\"]{0,1}\]$/', $t, $m)) {
								$tag = 'LANG>>' . strtolower($m[1]);
							} elseif (preg_match('/^:LANG\([\'\"]{0,1}([A-Z\-]{2,11})[\'\"]{0,1}\)$/', $t, $m)) { // mPDF 6  Special case for lang as attribute selector
								$tag = 'LANG>>' . strtolower($m[1]);
							} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')[.](.*)$/', $t, $m)) { // mPDF 6  Special case for lang as attribute selector
								$classes = explode('.', $m[2]);
								sort($classes);
								$tag = $m[1] . '>>CLASS>>' . join('.', $classes);
							} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')\s*:NTH-CHILD\((.*)\)$/', $t, $m)) {
								$tag = $m[1] . '>>SELECTORNTHCHILD>>' . $m[2];
							} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')[#](.*)$/', $t, $m)) {
								$tag = $m[1] . '>>ID>>' . $m[2];
							} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')\[LANG=[\'\"]{0,1}([A-Z\-]{2,11})[\'\"]{0,1}\]$/', $t, $m)) {
								$tag = $m[1] . '>>LANG>>' . strtolower($m[2]);
							} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . '):LANG\([\'\"]{0,1}([A-Z\-]{2,11})[\'\"]{0,1}\)$/', $t, $m)) {  // mPDF 6  Special case for lang as attribute selector
								$tag = $m[1] . '>>LANG>>' . strtolower($m[2]);
							} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')$/', $t)) { // mPDF 6  Special case for lang as attribute selector
								$tag = $t;
							}

							if (isset($this->CSS[$tag]) && $tag) {
								$this->CSS[$tag] = $this->array_merge_recursive_unique($this->CSS[$tag], $classproperties);
							} elseif ($tag) {
								$this->CSS[$tag] = $classproperties;
							}
						}

					} else {

						$tmp = [];

						for ($n = 0; $n < $level; $n++) {

							$tag = '';

							if (isset($tags[$n])) {
								$t = trim($tags[$n]);
							} else {
								$t = '';
							}

							if ($t) {

								if (preg_match('/^[.](.*)$/', $t, $m)) {
									$classes = explode('.', $m[1]);
									sort($classes);
									$tag = 'CLASS>>' . join('.', $classes);
								} elseif (preg_match('/^[#](.*)$/', $t, $m)) {
									$tag = 'ID>>' . $m[1];
								} elseif (preg_match('/^\[LANG=[\'\"]{0,1}([A-Z\-]{2,11})[\'\"]{0,1}\]$/', $t, $m)) {
									$tag = 'LANG>>' . strtolower($m[1]);
								} elseif (preg_match('/^:LANG\([\'\"]{0,1}([A-Z\-]{2,11})[\'\"]{0,1}\)$/', $t, $m)) { // mPDF 6  Special case for lang as attribute selector
									$tag = 'LANG>>' . strtolower($m[1]);
								} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')[.](.*)$/', $t, $m)) { // mPDF 6  Special case for lang as attribute selector
									$classes = explode('.', $m[2]);
									sort($classes);
									$tag = $m[1] . '>>CLASS>>' . join('.', $classes);
								} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')\s*:NTH-CHILD\((.*)\)$/', $t, $m)) {
									$tag = $m[1] . '>>SELECTORNTHCHILD>>' . $m[2];
								} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')[#](.*)$/', $t, $m)) {
									$tag = $m[1] . '>>ID>>' . $m[2];
								} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')\[LANG=[\'\"]{0,1}([A-Z\-]{2,11})[\'\"]{0,1}\]$/', $t, $m)) {
									$tag = $m[1] . '>>LANG>>' . strtolower($m[2]);
								} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . '):LANG\([\'\"]{0,1}([A-Z\-]{2,11})[\'\"]{0,1}\)$/', $t, $m)) { // mPDF 6  Special case for lang as attribute selector
									$tag = $m[1] . '>>LANG>>' . strtolower($m[2]);
								} elseif (preg_match('/^(' . $this->mpdf->allowedCSStags . ')$/', $t)) { // mPDF 6  Special case for lang as attribute selector
									$tag = $t;
								}

								if ($tag) {
									$tmp[] = $tag;
								} else {
									break;
								}
							}
						}

						if ($tag) {
							$x = &$this->cascadeCSS;
							foreach ($tmp as $tp) {
								$x = &$x[$tp];
							}
							$x = $this->array_merge_recursive_unique($x, $classproperties);
							$x['depth'] = $level;
						}
					}
				}
				if ($pageselectors) {
					$this->mpdf->mirrorMargins = true;
				}
				$classproperties = [];
			}
		}

		// Remove CSS (tags and content), if any
		$regexp = '/<style.*?>(.*?)<\/style>/si'; // it can be <style> or <style type="txt/css">
		$html = preg_replace($regexp, '', $html);

		return $html;
	}

	function readInlineCSS($html)
	{
		$html = htmlspecialchars_decode($html); // mPDF 5.7.4 URLs
		// mPDF 5.7.4 URLs
		// Characters "(" ")" and ";" in url() e.g. background-image, cause problems parsing the CSS string
		// URLencode ( and ), but change ";" to a code which can be converted back after parsing (so as not to confuse ;
		// with a segment delimiter in the URI)
		$tempmarker = '%ZZ';

		if (strpos($html, 'url(') !== false) {
			preg_match_all('/url\(\"(.*?)\"\)/', $html, $m);
			$m_count = count($m[1]);
			for ($i = 0; $i < $m_count; $i++) {
				$tmp = str_replace(['(', ')', ';'], ['%28', '%29', $tempmarker], $m[1][$i]);
				$html = str_replace($m[0][$i], 'url(\'' . $tmp . '\')', $html);
			}
			preg_match_all('/url\(\'(.*?)\'\)/', $html, $m);
			$m_count = count($m[1]);
			for ($i = 0; $i < $m_count; $i++) {
				$tmp = str_replace(['(', ')', ';'], ['%28', '%29', $tempmarker], $m[1][$i]);
				$html = str_replace($m[0][$i], 'url(\'' . $tmp . '\')', $html);
			}
			preg_match_all('/url\(([^\'\"].*?[^\'\"])\)/', $html, $m);
			$m_count = count($m[1]);
			for ($i = 0; $i < $m_count; $i++) {
				$tmp = str_replace(['(', ')', ';'], ['%28', '%29', $tempmarker], $m[1][$i]);
				$html = str_replace($m[0][$i], 'url(\'' . $tmp . '\')', $html);
			}
		}

		// Fix incomplete CSS code
		$size = strlen($html) - 1;
		if (substr($html, $size, 1) !== ';') {
			$html .= ';';
		}

		// Make CSS[Name-of-the-class] = array(key => value)
		$regexp = '|\\s*?(\\S+?):(.+?);|i';
		preg_match_all($regexp, $html, $styleinfo);
		$properties = $styleinfo[1];
		$values = $styleinfo[2];

		// Array-properties and Array-values must have the SAME SIZE!
		$classproperties = [];
		$properties_count = count($properties);
		for ($i = 0; $i < $properties_count; $i++) {

			// Ignores -webkit-gradient so doesn't override -moz-
			if ((strtoupper($properties[$i]) === 'BACKGROUND-IMAGE' || strtoupper($properties[$i]) === 'BACKGROUND') && false !== stripos($values[$i], '-webkit-gradient')) {
				continue;
			}

			$values[$i] = str_replace($tempmarker, ';', $values[$i]); // mPDF 5.7.4 URLs
			$classproperties[strtoupper($properties[$i])] = trim($values[$i]);
		}

		return $this->fixCSS($classproperties);
	}

	function _fix_borderStr($bd)
	{
		preg_match_all("/\((.*?)\)/", $bd, $m);
		if (count($m[1])) {
			$m_count = count($m[1]);
			for ($i = 0; $i < $m_count; $i++) {
				$sub = str_replace(' ', '', $m[1][$i]);
				$bd = str_replace($m[1][$i], $sub, $bd);
			}
		}

		$prop = preg_split('/\s+/', trim($bd));
		$w = 'medium';
		$c = '#000000';
		$s = 'none';

		$prop_count = count($prop);
		if ($prop_count === 1) {

			// solid
			if (in_array($prop[0], $this->mpdf->borderstyles) || $prop[0] === 'none' || $prop[0] === 'hidden') {
				$s = $prop[0];
			} // #000000
			elseif (is_array($this->colorConverter->convert($prop[0], $this->mpdf->PDFAXwarnings))) {
				$c = $prop[0];
			} // 1px
			else {
				$w = $prop[0];
			}

		} elseif ($prop_count === 2) {
			// 1px solid
			if (in_array($prop[1], $this->mpdf->borderstyles) || $prop[1] === 'none' || $prop[1] === 'hidden') {
				$w = $prop[0];
				$s = $prop[1];
			} // solid #000000
			elseif (in_array($prop[0], $this->mpdf->borderstyles) || $prop[0] === 'none' || $prop[0] === 'hidden') {
				$s = $prop[0];
				$c = $prop[1];
			} // 1px #000000
			else {
				$w = $prop[0];
				$c = $prop[1];
			}

		} elseif ($prop_count === 3) {
			// Change #000000 1px solid to 1px solid #000000 (proper)
			if (0 === strpos($prop[0], '#')) {
				$c = $prop[0];
				$w = $prop[1];
				$s = $prop[2];
			} // Change solid #000000 1px to 1px solid #000000 (proper)
			elseif (substr($prop[0], 1, 1) === '#') {
				$s = $prop[0];
				$c = $prop[1];
				$w = $prop[2];
			} // Change solid 1px #000000 to 1px solid #000000 (proper)
			elseif (in_array($prop[0], $this->mpdf->borderstyles) || $prop[0] === 'none' || $prop[0] === 'hidden') {
				$s = $prop[0];
				$w = $prop[1];
				$c = $prop[2];
			} else {
				$w = $prop[0];
				$s = $prop[1];
				$c = $prop[2];
			}

		} else {
			return '';
		}

		$s = strtolower($s);

		return $w . ' ' . $s . ' ' . $c;
	}

	function fixCSS($prop)
	{
		if (!is_array($prop) || (count($prop) == 0)) {
			return [];
		}

		$newprop = [];

		foreach ($prop as $k => $v) {

			if ($k !== 'BACKGROUND-IMAGE' && $k !== 'BACKGROUND' && $k !== 'ODD-HEADER-NAME' && $k !== 'EVEN-HEADER-NAME' && $k !== 'ODD-FOOTER-NAME' && $k !== 'EVEN-FOOTER-NAME' && $k !== 'HEADER' && $k !== 'FOOTER') {
				$v = strtolower($v);
			}

			if ($k === 'FONT') {

				$s = trim($v);

				preg_match_all('/\"(.*?)\"/', $s, $ff);
				if (count($ff[1])) {
					foreach ($ff[1] as $ffp) {
						$w = preg_split('/\s+/', $ffp);
						$s = preg_replace('/\"' . $ffp . '\"/', $w[0], $s);
					}
				}

				preg_match_all('/\'(.*?)\'/', $s, $ff);
				if (count($ff[1])) {
					foreach ($ff[1] as $ffp) {
						$w = preg_split('/\s+/', $ffp);
						$s = preg_replace('/\'' . $ffp . '\'/', $w[0], $s);
					}
				}

				$s = preg_replace('/\s*,\s*/', ',', $s);
				$bits = preg_split('/\s+/', $s);
				if (count($bits) > 1) {
					$k = 'FONT-FAMILY';
					$v = $bits[(count($bits) - 1)];
					$fs = $bits[(count($bits) - 2)];

					if (preg_match('/(.*?)\/(.*)/', $fs, $fsp)) {
						$newprop['FONT-SIZE'] = $fsp[1];
						$newprop['LINE-HEIGHT'] = $fsp[2];
					} else {
						$newprop['FONT-SIZE'] = $fs;
					}

					if (preg_match('/(italic|oblique)/i', $s)) {
						$newprop['FONT-STYLE'] = 'italic';
					} else {
						$newprop['FONT-STYLE'] = 'normal';
					}

					if (false !== stripos($s, 'bold')) {
						$newprop['FONT-WEIGHT'] = 'bold';
					} else {
						$newprop['FONT-WEIGHT'] = 'normal';
					}

					if (false !== stripos($s, 'small-caps')) {
						$newprop['TEXT-TRANSFORM'] = 'uppercase';
					}
				}

			} elseif ($k === 'FONT-FAMILY') {

				$aux_fontlist = explode(',', $v);
				$found = 0;

				foreach ($aux_fontlist as $f) {

					$fonttype = trim($f);
					$fonttype = preg_replace('/["\']*(.*?)["\']*/', '\\1', $fonttype);
					$fonttype = preg_replace('/ /', '', $fonttype);
					$v = strtolower(trim($fonttype));

					if (isset($this->mpdf->fonttrans[$v]) && $this->mpdf->fonttrans[$v]) {
						$v = $this->mpdf->fonttrans[$v];
					}

					if ((!$this->mpdf->onlyCoreFonts && in_array($v, $this->mpdf->available_unifonts)) ||
						in_array($v, ['ccourier', 'ctimes', 'chelvetica']) ||
						($this->mpdf->onlyCoreFonts && in_array($v, ['courier', 'times', 'helvetica', 'arial'])) ||
						in_array($v, ['sjis', 'uhc', 'big5', 'gb'])) {
						$newprop[$k] = $v;
						$found = 1;
						break;
					}
				}

				if (!$found) {
					foreach ($aux_fontlist as $f) {

						$fonttype = trim($f);
						$fonttype = preg_replace('/["\']*(.*?)["\']*/', '\\1', $fonttype);
						$fonttype = preg_replace('/ /', '', $fonttype);
						$v = strtolower(trim($fonttype));

						if (isset($this->mpdf->fonttrans[$v]) && $this->mpdf->fonttrans[$v]) {
							$v = $this->mpdf->fonttrans[$v];
						}

						if (in_array($v, $this->mpdf->sans_fonts) || in_array($v, $this->mpdf->serif_fonts) || in_array($v, $this->mpdf->mono_fonts)) {
							$newprop[$k] = $v;
							break;
						}
					}
				}

			} elseif ($k === 'FONT-VARIANT') {

				if (preg_match('/(normal|none)/', $v, $m)) {
					$newprop['FONT-VARIANT-LIGATURES'] = $m[1];
					$newprop['FONT-VARIANT-CAPS'] = $m[1];
					$newprop['FONT-VARIANT-NUMERIC'] = $m[1];
					$newprop['FONT-VARIANT-ALTERNATES'] = $m[1];
				} else {
					if (preg_match_all('/(no-common-ligatures|\bcommon-ligatures|no-discretionary-ligatures|\bdiscretionary-ligatures|no-historical-ligatures|\bhistorical-ligatures|no-contextual|\bcontextual)/i', $v, $m)) {
						$newprop['FONT-VARIANT-LIGATURES'] = implode(' ', $m[1]);
					}
					if (preg_match('/(all-small-caps|\bsmall-caps|all-petite-caps|\bpetite-caps|unicase|titling-caps)/i', $v, $m)) {
						$newprop['FONT-VARIANT-CAPS'] = $m[1];
					}
					if (preg_match_all('/(lining-nums|oldstyle-nums|proportional-nums|tabular-nums|diagonal-fractions|stacked-fractions)/i', $v, $m)) {
						$newprop['FONT-VARIANT-NUMERIC'] = implode(' ', $m[1]);
					}
					if (preg_match('/(historical-forms)/i', $v, $m)) {
						$newprop['FONT-VARIANT-ALTERNATES'] = $m[1];
					}
				}

			} elseif ($k === 'MARGIN') {

				$tmp = $this->expand24($v);

				$newprop['MARGIN-TOP'] = $tmp['T'];
				$newprop['MARGIN-RIGHT'] = $tmp['R'];
				$newprop['MARGIN-BOTTOM'] = $tmp['B'];
				$newprop['MARGIN-LEFT'] = $tmp['L'];

			} elseif ($k === 'BORDER-RADIUS' || $k === 'BORDER-TOP-LEFT-RADIUS' || $k === 'BORDER-TOP-RIGHT-RADIUS' || $k === 'BORDER-BOTTOM-LEFT-RADIUS' || $k === 'BORDER-BOTTOM-RIGHT-RADIUS') {

				$tmp = $this->border_radius_expand($v, $k);

				if (isset($tmp['TL-H'])) {
					$newprop['BORDER-TOP-LEFT-RADIUS-H'] = $tmp['TL-H'];
				}
				if (isset($tmp['TL-V'])) {
					$newprop['BORDER-TOP-LEFT-RADIUS-V'] = $tmp['TL-V'];
				}
				if (isset($tmp['TR-H'])) {
					$newprop['BORDER-TOP-RIGHT-RADIUS-H'] = $tmp['TR-H'];
				}
				if (isset($tmp['TR-V'])) {
					$newprop['BORDER-TOP-RIGHT-RADIUS-V'] = $tmp['TR-V'];
				}
				if (isset($tmp['BL-H'])) {
					$newprop['BORDER-BOTTOM-LEFT-RADIUS-H'] = $tmp['BL-H'];
				}
				if (isset($tmp['BL-V'])) {
					$newprop['BORDER-BOTTOM-LEFT-RADIUS-V'] = $tmp['BL-V'];
				}
				if (isset($tmp['BR-H'])) {
					$newprop['BORDER-BOTTOM-RIGHT-RADIUS-H'] = $tmp['BR-H'];
				}
				if (isset($tmp['BR-V'])) {
					$newprop['BORDER-BOTTOM-RIGHT-RADIUS-V'] = $tmp['BR-V'];
				}

			} elseif ($k === 'PADDING') {

				$tmp = $this->expand24($v);

				$newprop['PADDING-TOP'] = $tmp['T'];
				$newprop['PADDING-RIGHT'] = $tmp['R'];
				$newprop['PADDING-BOTTOM'] = $tmp['B'];
				$newprop['PADDING-LEFT'] = $tmp['L'];

			} elseif ($k === 'BORDER') {

				if ($v == '1') {
					$v = '1px solid #000000';
				} else {
					$v = $this->_fix_borderStr($v);
				}

				$newprop['BORDER-TOP'] = $v;
				$newprop['BORDER-RIGHT'] = $v;
				$newprop['BORDER-BOTTOM'] = $v;
				$newprop['BORDER-LEFT'] = $v;

			} elseif ($k === 'BORDER-TOP') {

				$newprop['BORDER-TOP'] = $this->_fix_borderStr($v);

			} elseif ($k === 'BORDER-RIGHT') {

				$newprop['BORDER-RIGHT'] = $this->_fix_borderStr($v);

			} elseif ($k === 'BORDER-BOTTOM') {

				$newprop['BORDER-BOTTOM'] = $this->_fix_borderStr($v);

			} elseif ($k === 'BORDER-LEFT') {

				$newprop['BORDER-LEFT'] = $this->_fix_borderStr($v);

			} elseif ($k === 'BORDER-STYLE') {

				$e = $this->expand24($v);

				if (!empty($e)) {
					$newprop['BORDER-TOP-STYLE'] = $e['T'];
					$newprop['BORDER-RIGHT-STYLE'] = $e['R'];
					$newprop['BORDER-BOTTOM-STYLE'] = $e['B'];
					$newprop['BORDER-LEFT-STYLE'] = $e['L'];
				}

			} elseif ($k === 'BORDER-WIDTH') {

				$e = $this->expand24($v);
				if (!empty($e)) {
					$newprop['BORDER-TOP-WIDTH'] = $e['T'];
					$newprop['BORDER-RIGHT-WIDTH'] = $e['R'];
					$newprop['BORDER-BOTTOM-WIDTH'] = $e['B'];
					$newprop['BORDER-LEFT-WIDTH'] = $e['L'];
				}

			} elseif ($k === 'BORDER-COLOR') {

				$e = $this->expand24($v);
				if (!empty($e)) {
					$newprop['BORDER-TOP-COLOR'] = $e['T'];
					$newprop['BORDER-RIGHT-COLOR'] = $e['R'];
					$newprop['BORDER-BOTTOM-COLOR'] = $e['B'];
					$newprop['BORDER-LEFT-COLOR'] = $e['L'];
				}

			} elseif ($k === 'BORDER-SPACING') {

				$prop = preg_split('/\s+/', trim($v));
				if (count($prop) == 1) {
					$newprop['BORDER-SPACING-H'] = $prop[0];
					$newprop['BORDER-SPACING-V'] = $prop[0];
				} elseif (count($prop) == 2) {
					$newprop['BORDER-SPACING-H'] = $prop[0];
					$newprop['BORDER-SPACING-V'] = $prop[1];
				}

			} elseif ($k === 'TEXT-OUTLINE') {

				$prop = preg_split('/\s+/', trim($v));

				if (strtolower(trim($v)) === 'none') {
					$newprop['TEXT-OUTLINE'] = 'none';
				} elseif (count($prop) == 2) {
					$newprop['TEXT-OUTLINE-WIDTH'] = $prop[0];
					$newprop['TEXT-OUTLINE-COLOR'] = $prop[1];
				} elseif (count($prop) == 3) {
					$newprop['TEXT-OUTLINE-WIDTH'] = $prop[0];
					$newprop['TEXT-OUTLINE-COLOR'] = $prop[2];
				}

			} elseif ($k === 'SIZE') {

				$prop = preg_split('/\s+/', trim($v));

				if (preg_match('/(auto|portrait|landscape)/', $prop[0])) {
					$newprop['SIZE'] = strtoupper($prop[0]);
				} elseif (count($prop) == 1) {
					$newprop['SIZE']['W'] = $this->sizeConverter->convert($prop[0]);
					$newprop['SIZE']['H'] = $this->sizeConverter->convert($prop[0]);
				} elseif (count($prop) == 2) {
					$newprop['SIZE']['W'] = $this->sizeConverter->convert($prop[0]);
					$newprop['SIZE']['H'] = $this->sizeConverter->convert($prop[1]);
				}

			} elseif ($k === 'SHEET-SIZE') {

				$prop = preg_split('/\s+/', trim($v));

				if (count($prop) == 2) {

					$newprop['SHEET-SIZE'] = [$this->sizeConverter->convert($prop[0]), $this->sizeConverter->convert($prop[1])];

				} else {

					if (preg_match('/([0-9a-zA-Z]*)-L/i', $v, $m)) { // e.g. A4-L = A$ landscape
						$ft = PageFormat::getSizeFromName($m[1]);
						$format = [$ft[1], $ft[0]];
					} else {
						$format = PageFormat::getSizeFromName($v);
					}
					if ($format) {
						$newprop['SHEET-SIZE'] = [$format[0] / Mpdf::SCALE, $format[1] / Mpdf::SCALE];
					}

				}

			} elseif ($k === 'BACKGROUND') {

				$bg = $this->parseCSSbackground($v);

				if ($bg['c']) {
					$newprop['BACKGROUND-COLOR'] = $bg['c'];
				} else {
					$newprop['BACKGROUND-COLOR'] = 'transparent';
				}

				if ($bg['i']) {
					$newprop['BACKGROUND-IMAGE'] = $bg['i'];
					if ($bg['r']) {
						$newprop['BACKGROUND-REPEAT'] = $bg['r'];
					}
					if ($bg['p']) {
						$newprop['BACKGROUND-POSITION'] = $bg['p'];
					}
				} else {
					$newprop['BACKGROUND-IMAGE'] = '';
				}

			} elseif ($k === 'BACKGROUND-IMAGE') {

				if (preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient\(.*\)/i', $v, $m)) {
					$newprop['BACKGROUND-IMAGE'] = $m[0];
					continue;
				}

				if (preg_match('/url\([\'\"]{0,1}(.*?)[\'\"]{0,1}\)/i', $v, $m)) {
					$newprop['BACKGROUND-IMAGE'] = $m[1];
				} elseif (strtolower($v) === 'none') {
					$newprop['BACKGROUND-IMAGE'] = '';
				}

			} elseif ($k === 'BACKGROUND-REPEAT') {

				if (preg_match('/(repeat-x|repeat-y|no-repeat|repeat)/i', $v, $m)) {
					$newprop['BACKGROUND-REPEAT'] = strtolower($m[1]);
				}

			} elseif ($k === 'BACKGROUND-POSITION') {

				$s = $v;
				$bits = preg_split('/\s+/', trim($s));

				// These should be Position x1 or x2
				if (count($bits) === 1) {
					if (false !== strpos($bits[0], 'bottom')) {
						$bg['p'] = '50% 100%';
					} elseif (false !== strpos($bits[0], 'top')) {
						$bg['p'] = '50% 0%';
					} else {
						$bg['p'] = $bits[0] . ' 50%';
					}

				} elseif (count($bits) === 2) {
					// Can be either right center or center right
					if (preg_match('/(top|bottom)/', $bits[0]) || preg_match('/(left|right)/', $bits[1])) {
						$bg['p'] = $bits[1] . ' ' . $bits[0];
					} else {
						$bg['p'] = $bits[0] . ' ' . $bits[1];
					}
				}

				if (isset($bg['p'])) {

					$bg['p'] = preg_replace('/(left|top)/', '0%', $bg['p']);
					$bg['p'] = preg_replace('/(right|bottom)/', '100%', $bg['p']);
					$bg['p'] = preg_replace('/(center)/', '50%', $bg['p']);

					if (!preg_match('/[\-]{0,1}\d+(in|cm|mm|pt|pc|em|ex|px|%)* [\-]{0,1}\d+(in|cm|mm|pt|pc|em|ex|px|%)*/', $bg['p'])) {
						$bg['p'] = false;
					}

					$newprop['BACKGROUND-POSITION'] = $bg['p'];
				}

			} elseif ($k === 'IMAGE-ORIENTATION') {

				if (preg_match('/([\-]*[0-9\.]+)(deg|grad|rad)/i', $v, $m)) {

					$angle = $m[1] + 0;

					if (strtolower($m[2]) === 'grad') {
						$angle *= (360 / 400);
					} elseif (strtolower($m[2]) === 'rad') {
						$angle = rad2deg($angle);
					}

					while ($angle < 0) {
						$angle += 360;
					}

					$angle %= 360;
					$angle /= 90;
					$angle = round($angle) * 90;

					$newprop['IMAGE-ORIENTATION'] = $angle;
				}

			} elseif ($k === 'TEXT-ALIGN') {

				if (preg_match('/["\'](.){1}["\']/i', $v, $m)) {

					$d = array_search($m[1], $this->mpdf->decimal_align);

					if ($d !== false) {
						$newprop['TEXT-ALIGN'] = $d;
					}
					if (preg_match('/(center|left|right)/i', $v, $m)) {
						$newprop['TEXT-ALIGN'] .= strtoupper(substr($m[1], 0, 1));
					} else {
						$newprop['TEXT-ALIGN'] .= 'R';
					} // default = R

				} elseif (preg_match('/["\'](\\\[a-fA-F0-9]{1,6})["\']/i', $v, $m)) {

					$utf8 = UtfString::codeHex2utf(substr($m[1], 1, 6));
					$d = array_search($utf8, $this->mpdf->decimal_align);

					if ($d !== false) {
						$newprop['TEXT-ALIGN'] = $d;
					}

					if (preg_match('/(center|left|right)/i', $v, $m)) {
						$newprop['TEXT-ALIGN'] .= strtoupper(substr($m[1], 0, 1));
					} else {
						$newprop['TEXT-ALIGN'] .= 'R';
					} // default = R

				} else {
					$newprop[$k] = $v;
				}

			} elseif ($k === 'LIST-STYLE') {

				if (preg_match('/none/i', $v, $m)) {
					$newprop['LIST-STYLE-TYPE'] = 'none';
					$newprop['LIST-STYLE-IMAGE'] = 'none';
				}

				if (preg_match('/(lower-roman|upper-roman|lower-latin|lower-alpha|upper-latin|upper-alpha|decimal|disc|circle|square|arabic-indic|bengali|devanagari|gujarati|gurmukhi|kannada|malayalam|oriya|persian|tamil|telugu|thai|urdu|cambodian|khmer|lao|cjk-decimal|hebrew)/i', $v, $m)) {
					$newprop['LIST-STYLE-TYPE'] = strtolower(trim($m[1]));
				} elseif (preg_match('/U\+([a-fA-F0-9]+)/i', $v, $m)) {
					$newprop['LIST-STYLE-TYPE'] = strtolower(trim($m[1]));
				}

				if (preg_match('/url\([\'\"]{0,1}(.*?)[\'\"]{0,1}\)/i', $v, $m)) {
					$newprop['LIST-STYLE-IMAGE'] = strtolower(trim($m[1]));
				}

				if (preg_match('/(inside|outside)/i', $v, $m)) {
					$newprop['LIST-STYLE-POSITION'] = strtolower(trim($m[1]));
				}

			} else {
				$newprop[$k] = $v;
			}
		}

		return $newprop;
	}

	function setCSSboxshadow($v)
	{
		$sh = [];
		$c = preg_match_all('/(rgba|rgb|device-cmyka|cmyka|device-cmyk|cmyk|hsla|hsl)\(.*?\)/', $v, $x); // mPDF 5.6.05
		for ($i = 0; $i < $c; $i++) {
			$col = preg_replace('/,/', '*', $x[0][$i]);
			$v = str_replace($x[0][$i], $col, $v);
		}
		$ss = explode(',', $v);
		foreach ($ss as $s) {
			$new = ['inset' => false, 'blur' => 0, 'spread' => 0];
			if (false !== stripos($s, 'inset')) {
				$new['inset'] = true;
				$s = preg_replace('/\s*inset\s*/', '', $s);
			}
			$p = explode(' ', trim($s));
			if (isset($p[0])) {
				$new['x'] = $this->sizeConverter->convert(trim($p[0]), $this->mpdf->blk[$this->mpdf->blklvl - 1]['inner_width'], $this->mpdf->FontSize, false);
			}
			if (isset($p[1])) {
				$new['y'] = $this->sizeConverter->convert(trim($p[1]), $this->mpdf->blk[$this->mpdf->blklvl - 1]['inner_width'], $this->mpdf->FontSize, false);
			}
			if (isset($p[2])) {
				if (preg_match('/^\s*[\.\-0-9]/', $p[2])) {
					$new['blur'] = $this->sizeConverter->convert(trim($p[2]), $this->mpdf->blk[$this->mpdf->blklvl - 1]['inner_width'], $this->mpdf->FontSize, false);
				} else {
					$new['col'] = $this->colorConverter->convert(preg_replace('/\*/', ',', $p[2]), $this->mpdf->PDFAXwarnings);
				}
				if (isset($p[3])) {
					if (preg_match('/^\s*[\.\-0-9]/', $p[3])) {
						$new['spread'] = $this->sizeConverter->convert(trim($p[3]), $this->mpdf->blk[$this->mpdf->blklvl - 1]['inner_width'], $this->mpdf->FontSize, false);
					} else {
						$new['col'] = $this->colorConverter->convert(preg_replace('/\*/', ',', $p[3]), $this->mpdf->PDFAXwarnings);
					}
					if (isset($p[4])) {
						$new['col'] = $this->colorConverter->convert(preg_replace('/\*/', ',', $p[4]), $this->mpdf->PDFAXwarnings);
					}
				}
			}
			if (empty($new['col'])) {
				$new['col'] = $this->colorConverter->convert('#888888', $this->mpdf->PDFAXwarnings);
			}
			if (isset($new['y'])) {
				array_unshift($sh, $new);
			}
		}
		return $sh;
	}

	function setCSStextshadow($v)
	{
		$sh = [];
		$c = preg_match_all('/(rgba|rgb|device-cmyka|cmyka|device-cmyk|cmyk|hsla|hsl)\(.*?\)/', $v, $x); // mPDF 5.6.05

		for ($i = 0; $i < $c; $i++) {
			$col = preg_replace('/,\s/', '*', $x[0][$i]);
			$v = str_replace($x[0][$i], $col, $v);
		}

		$ss = explode(',', $v);

		foreach ($ss as $s) {

			$new = ['blur' => 0];
			$p = explode(' ', trim($s));

			if (isset($p[0])) {
				$new['x'] = $this->sizeConverter->convert(trim($p[0]), $this->mpdf->FontSize, $this->mpdf->FontSize, false);
			}

			if (isset($p[1])) {
				$new['y'] = $this->sizeConverter->convert(trim($p[1]), $this->mpdf->FontSize, $this->mpdf->FontSize, false);
			}

			if (isset($p[2])) {

				if (preg_match('/^\s*[\.\-0-9]/', $p[2])) {

					$new['blur'] = $this->sizeConverter->convert(
						trim($p[2]),
						isset($this->mpdf->blk[$this->mpdf->blklvl]['inner_width']) ? $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'] : 0,
						$this->mpdf->FontSize,
						false
					);

				} else {
					$new['col'] = $this->colorConverter->convert(preg_replace('/\*/', ',', $p[2]), $this->mpdf->PDFAXwarnings);
				}

				if (isset($p[3])) {
					$new['col'] = $this->colorConverter->convert(preg_replace('/\*/', ',', $p[3]), $this->mpdf->PDFAXwarnings);
				}
			}

			if (!isset($new['col']) || !$new['col']) {
				$new['col'] = $this->colorConverter->convert('#888888', $this->mpdf->PDFAXwarnings);
			}

			if (isset($new['y'])) {
				array_unshift($sh, $new);
			}

		}

		return $sh;
	}

	function parseCSSbackground($s)
	{
		$bg = ['c' => false, 'i' => false, 'r' => false, 'p' => false,];
		/* -- BACKGROUNDS -- */
		if (preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient\(.*\)/i', $s, $m)) {
			$bg['i'] = $m[0];
		} else {
			if (preg_match('/url\(/i', $s)) { /* -- END BACKGROUNDS -- */
				// If color, set and strip it off
				// mPDF 5.6.05
				if (preg_match('/^\s*(#[0-9a-fA-F]{3,6}|(rgba|rgb|device-cmyka|cmyka|device-cmyk|cmyk|hsla|hsl|spot)\(.*?\)|[a-zA-Z]{3,})\s+(url\(.*)/i', $s, $m)) {
					$bg['c'] = strtolower($m[1]);
					$s = $m[3];
				}
				/* -- BACKGROUNDS -- */
				if (preg_match('/url\([\'\"]{0,1}(.*?)[\'\"]{0,1}\)\s*(.*)/i', $s, $m)) {
					$bg['i'] = $m[1];
					$s = strtolower($m[2]);
					if (preg_match('/(repeat-x|repeat-y|no-repeat|repeat)/', $s, $m)) {
						$bg['r'] = $m[1];
					}
					// Remove repeat, attachment (discarded) and also any inherit
					$s = preg_replace('/(repeat-x|repeat-y|no-repeat|repeat|scroll|fixed|inherit)/', '', $s);
					$bits = preg_split('/\s+/', trim($s));
					// These should be Position x1 or x2
					if (count($bits) == 1) {
						if (false !== strpos($bits[0], 'bottom')) {
							$bg['p'] = '50% 100%';
						} elseif (false !== strpos($bits[0], 'top')) {
							$bg['p'] = '50% 0%';
						} else {
							$bg['p'] = $bits[0] . ' 50%';
						}
					} elseif (count($bits) == 2) {
						// Can be either right center or center right
						if (preg_match('/(top|bottom)/', $bits[0]) || preg_match('/(left|right)/', $bits[1])) {
							$bg['p'] = $bits[1] . ' ' . $bits[0];
						} else {
							$bg['p'] = $bits[0] . ' ' . $bits[1];
						}
					}
					if ($bg['p']) {
						$bg['p'] = preg_replace('/(left|top)/', '0%', $bg['p']);
						$bg['p'] = preg_replace('/(right|bottom)/', '100%', $bg['p']);
						$bg['p'] = preg_replace('/(center)/', '50%', $bg['p']);
						if (!preg_match('/[\-]{0,1}\d+(in|cm|mm|pt|pc|em|ex|px|%)* [\-]{0,1}\d+(in|cm|mm|pt|pc|em|ex|px|%)*/', $bg['p'])) {
							$bg['p'] = false;
						}
					}
				}
				/* -- END BACKGROUNDS -- */
			} elseif (preg_match('/^\s*(#[0-9a-fA-F]{3,6}|(rgba|rgb|device-cmyka|cmyka|device-cmyk|cmyk|hsla|hsl|spot)\(.*?\)|[a-zA-Z]{3,})/i', $s, $m)) {
				$bg['c'] = strtolower($m[1]);
			}
		} // mPDF 5.6.05
		return ($bg);
	}

	function expand24($mp)
	{
		$prop = preg_split('/\s+/', trim($mp));
		$prop_count = count($prop);

		if ($prop_count === 1) {
			return ['T' => $prop[0], 'R' => $prop[0], 'B' => $prop[0], 'L' => $prop[0]];
		}

		if ($prop_count === 2) {
			return ['T' => $prop[0], 'R' => $prop[1], 'B' => $prop[0], 'L' => $prop[1]];
		}

		if ($prop_count === 3) {
			return ['T' => $prop[0], 'R' => $prop[1], 'B' => $prop[2], 'L' => $prop[1]];
		}

		// Ignore rule parts after first 4 values (most likely !important)
		if ($prop_count >= 4) {
			return ['T' => $prop[0], 'R' => $prop[1], 'B' => $prop[2], 'L' => $prop[3]];
		}

		return [];
	}

	/* -- BORDER-RADIUS -- */

	function border_radius_expand($val, $k)
	{
		$b = [];

		if ($k === 'BORDER-RADIUS') {

			$hv = explode('/', trim($val));
			$prop = preg_split('/\s+/', trim($hv[0]));

			if (count($prop) == 1) {
				$b['TL-H'] = $b['TR-H'] = $b['BR-H'] = $b['BL-H'] = $prop[0];
			} elseif (count($prop) == 2) {
				$b['TL-H'] = $b['BR-H'] = $prop[0];
				$b['TR-H'] = $b['BL-H'] = $prop[1];
			} elseif (count($prop) == 3) {
				$b['TL-H'] = $prop[0];
				$b['TR-H'] = $b['BL-H'] = $prop[1];
				$b['BR-H'] = $prop[2];
			} elseif (count($prop) == 4) {
				$b['TL-H'] = $prop[0];
				$b['TR-H'] = $prop[1];
				$b['BR-H'] = $prop[2];
				$b['BL-H'] = $prop[3];
			}

			if (count($hv) == 2) {
				$prop = preg_split('/\s+/', trim($hv[1]));
				if (count($prop) == 1) {
					$b['TL-V'] = $b['TR-V'] = $b['BR-V'] = $b['BL-V'] = $prop[0];
				} elseif (count($prop) == 2) {
					$b['TL-V'] = $b['BR-V'] = $prop[0];
					$b['TR-V'] = $b['BL-V'] = $prop[1];
				} elseif (count($prop) == 3) {
					$b['TL-V'] = $prop[0];
					$b['TR-V'] = $b['BL-V'] = $prop[1];
					$b['BR-V'] = $prop[2];
				} elseif (count($prop) == 4) {
					$b['TL-V'] = $prop[0];
					$b['TR-V'] = $prop[1];
					$b['BR-V'] = $prop[2];
					$b['BL-V'] = $prop[3];
				}
			} else {
				$b['TL-V'] = Arrays::get($b, 'TL-H', 0);
				$b['TR-V'] = Arrays::get($b, 'TR-H', 0);
				$b['BL-V'] = Arrays::get($b, 'BL-H', 0);
				$b['BR-V'] = Arrays::get($b, 'BR-H', 0);
			}

			return $b;
		}

		// Parse 2
		$prop = preg_split('/\s+/', trim($val));

		if (count($prop) == 1) {
			$h = $v = $val;
		} else {
			$h = $prop[0];
			$v = $prop[1];
		}

		if ($h == 0 || $v == 0) {
			$h = $v = 0;
		}

		if ($k === 'BORDER-TOP-LEFT-RADIUS') {
			$b['TL-H'] = $h;
			$b['TL-V'] = $v;
		} elseif ($k === 'BORDER-TOP-RIGHT-RADIUS') {
			$b['TR-H'] = $h;
			$b['TR-V'] = $v;
		} elseif ($k === 'BORDER-BOTTOM-LEFT-RADIUS') {
			$b['BL-H'] = $h;
			$b['BL-V'] = $v;
		} elseif ($k === 'BORDER-BOTTOM-RIGHT-RADIUS') {
			$b['BR-H'] = $h;
			$b['BR-V'] = $v;
		}

		return $b;
	}
	/* -- END BORDER-RADIUS -- */

	function _mergeCSS($p, &$t)
	{
		// Save Cascading CSS e.g. "div.topic p" at this block level
		if (isset($p) && $p) {
			if ($t) {
				$t = $this->array_merge_recursive_unique($t, $p);
			} else {
				$t = $p;
			}
		}
	}

	// for CSS handling
	function array_merge_recursive_unique($array1, $array2)
	{
		$arrays = func_get_args();
		$narrays = count($arrays);
		$ret = $arrays[0];
		for ($i = 1; $i < $narrays; $i ++) {
			foreach ($arrays[$i] as $key => $value) {
				if (((string) $key) === ((string) ((int) $key))) { // integer or string as integer key - append
					$ret[] = $value;
				} else { // string key - merge
					if (is_array($value) && isset($ret[$key])) {
						$ret[$key] = $this->array_merge_recursive_unique($ret[$key], $value);
					} else {
						$ret[$key] = $value;
					}
				}
			}
		}
		return $ret;
	}

	function _mergeFullCSS($p, &$t, $tag, $classes, $id, $lang)
	{
	// mPDF 6
		if (isset($p[$tag])) {
			$this->_mergeCSS($p[$tag], $t);
		}
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach ($classes as $class) {
			if (isset($p['CLASS>>' . $class])) {
				$this->_mergeCSS($p['CLASS>>' . $class], $t);
			}
		}
		// STYLESHEET nth-child SELECTOR e.g. tr:nth-child(odd)  td:nth-child(2n+1)
		if ($tag === 'TR' && isset($p) && $p) {
			foreach ($p as $k => $val) {
				if (preg_match('/' . $tag . '>>SELECTORNTHCHILD>>(.*)/', $k, $m)) {
					$select = false;
					if ($tag === 'TR') {
						$row = $this->mpdf->row;
						$thnr = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead']) ? count($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead']) : 0);
						$tfnr = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot']) ? count($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot']) : 0);
						if ($this->mpdf->tabletfoot) {
							$row -= $thnr;
						} elseif (!$this->mpdf->tablethead) {
							$row -= ($thnr + $tfnr);
						}
						if (preg_match('/(([\-+]?\d*)?N([\-+]\d+)?|[\-+]?\d+|ODD|EVEN)/', $m[1], $a)) { // mPDF 5.7.4
							$select = $this->_nthchild($a, $row);
						}
					} elseif ($tag === 'TD' || $tag === 'TH') {
						if (preg_match('/(([\-+]?\d*)?N([\-+]\d+)?|[\-+]?\d+|ODD|EVEN)/', $m[1], $a)) { // mPDF 5.7.4
							$select = $this->_nthchild($a, $this->mpdf->col);
						}
					}
					if ($select) {
						$this->_mergeCSS($p[$tag . '>>SELECTORNTHCHILD>>' . $m[1]], $t);
					}
				}
			}
		}
		// STYLESHEET CLASS e.g. [lang=fr]{} or :lang(fr)
		if (isset($lang) && isset($p['LANG>>' . $lang])) {
			$this->_mergeCSS($p['LANG>>' . $lang], $t);
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($id) && isset($p['ID>>' . $id])) {
			$this->_mergeCSS($p['ID>>' . $id], $t);
		}

		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach ($classes as $class) {
			if (isset($p[$tag . '>>CLASS>>' . $class])) {
				$this->_mergeCSS($p[$tag . '>>CLASS>>' . $class], $t);
			}
		}
		// STYLESHEET CLASS e.g. [lang=fr]{} or :lang(fr)
		if (isset($lang) && isset($p[$tag . '>>LANG>>' . $lang])) {
			$this->_mergeCSS($p[$tag . '>>LANG>>' . $lang], $t);
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($id) && isset($p[$tag . '>>ID>>' . $id])) {
			$this->_mergeCSS($p[$tag . '>>ID>>' . $id], $t);
		}
	}

	function setBorderDominance($prop, $val)
	{
		if (!empty($prop['BORDER-LEFT'])) {
			$this->cell_border_dominance_L = $val;
		}
		if (!empty($prop['BORDER-RIGHT'])) {
			$this->cell_border_dominance_R = $val;
		}
		if (!empty($prop['BORDER-TOP'])) {
			$this->cell_border_dominance_T = $val;
		}
		if (!empty($prop['BORDER-BOTTOM'])) {
			$this->cell_border_dominance_B = $val;
		}
	}

	function _set_mergedCSS(&$m, &$p, $d = true, $bd = false)
	{
		if (isset($m)) {
			if ((isset($m['depth']) && $m['depth'] > 1) || $d == false) {  // include check for 'depth'
				if ($bd) {
					$this->setBorderDominance($m, $bd);
				} // *TABLES*
				if (is_array($m)) {
					$p = array_merge($p, $m);
					$this->_mergeBorders($p, $m);
				}
			}
		}
	}

	function _mergeBorders(&$b, &$a)
	{
	// Merges $a['BORDER-TOP-STYLE'] to $b['BORDER-TOP'] etc.
		foreach (['TOP', 'RIGHT', 'BOTTOM', 'LEFT'] as $side) {
			foreach (['STYLE', 'WIDTH', 'COLOR'] as $el) {
				if (isset($a['BORDER-' . $side . '-' . $el])) { // e.g. $b['BORDER-TOP-STYLE']
					$s = trim($a['BORDER-' . $side . '-' . $el]);
					if (isset($b['BORDER-' . $side])) { // e.g. $b['BORDER-TOP']
						$p = trim($b['BORDER-' . $side]);
					} else {
						$p = '';
					}
					if ($el === 'STYLE') {
						if ($p) {
							$b['BORDER-' . $side] = preg_replace('/(\S+)\s+(\S+)\s+(\S+)/', '\\1 ' . $s . ' \\3', $p);
						} else {
							$b['BORDER-' . $side] = '0px ' . $s . ' #000000';
						}
					} elseif ($el === 'WIDTH') {
						if ($p) {
							$b['BORDER-' . $side] = preg_replace('/(\S+)\s+(\S+)\s+(\S+)/', $s . ' \\2 \\3', $p);
						} else {
							$b['BORDER-' . $side] = $s . ' none #000000';
						}
					} elseif ($el === 'COLOR') {
						if ($p) {
							$b['BORDER-' . $side] = preg_replace('/(\S+)\s+(\S+)\s+(\S+)/', '\\1 \\2 ' . $s, $p);
						} else {
							$b['BORDER-' . $side] = '0px none ' . $s;
						}
					}
				}
			}
		}
	}

	function MergeCSS($inherit, $tag, $attr)
	{
		$p = [];

		$attr = is_array($attr) ? $attr : [];

		$classes = [];
		if (isset($attr['CLASS'])) {
			$classes = array_map(function ($combination) {
				return join('.', $combination);
			}, Arrays::allUniqueSortedCombinations(preg_split('/\s+/', $attr['CLASS'])));
		}
		if (!isset($attr['ID'])) {
			$attr['ID'] = '';
		}
		// mPDF 6
		$shortlang = '';
		if (!isset($attr['LANG'])) {
			$attr['LANG'] = '';
		} else {
			$attr['LANG'] = strtolower($attr['LANG']);
			if (strlen($attr['LANG']) == 5) {
				$shortlang = substr($attr['LANG'], 0, 2);
			}
		}

		/* -- TABLES -- */

		// Set Inherited properties
		if ($inherit === 'TOPTABLE') { // $tag = TABLE

			// Save Cascading CSS e.g. "div.topic p" at this block level
			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'])) {
				$this->tablecascadeCSS[0] = $this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'];
			} else {
				$this->tablecascadeCSS[0] = $this->cascadeCSS;
			}
		}

		// Set Inherited properties
		if ($inherit === 'TOPTABLE' || $inherit === 'TABLE') {

			// Cascade everything from last level that is not an actual property, or defined by current tag/attributes
			if (isset($this->tablecascadeCSS[$this->tbCSSlvl - 1]) && is_array($this->tablecascadeCSS[$this->tbCSSlvl - 1])) {
				foreach ($this->tablecascadeCSS[$this->tbCSSlvl - 1] as $k => $v) {
					$this->tablecascadeCSS[$this->tbCSSlvl][$k] = $v;
				}
			}

			$this->_mergeFullCSS(
				$this->cascadeCSS,
				$this->tablecascadeCSS[$this->tbCSSlvl],
				$tag,
				$classes,
				$attr['ID'],
				$attr['LANG']
			);

			// Cascading forward CSS e.g. "table.topic td" for this table in $this->tablecascadeCSS
			// STYLESHEET TAG e.g. table
			if (isset($this->tablecascadeCSS[$this->tbCSSlvl - 1])) {
				$this->_mergeFullCSS(
					$this->tablecascadeCSS[$this->tbCSSlvl - 1],
					$this->tablecascadeCSS[$this->tbCSSlvl],
					$tag,
					$classes,
					$attr['ID'],
					$attr['LANG']
				);
			}
		}

		/* -- END TABLES -- */

		//===============================================
		// Set Inherited properties
		if ($inherit === 'BLOCK') {
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS']) && is_array($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS'])) {
				foreach ($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS'] as $k => $v) {
					$this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'][$k] = $v;
				}
			}

			//===============================================
			// Save Cascading CSS e.g. "div.topic p" at this block level
			$this->_mergeFullCSS($this->cascadeCSS, $this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'], $tag, $classes, $attr['ID'], $attr['LANG']);
			//===============================================
			// Cascading forward CSS
			//===============================================
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1])) {
				$this->_mergeFullCSS($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS'], $this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'], $tag, $classes, $attr['ID'], $attr['LANG']);
			}
			//===============================================
			// Block properties which are inherited
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['margin_collapse']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['margin_collapse']) {
				$p['MARGIN-COLLAPSE'] = 'COLLAPSE';
			} // custom tag, but follows CSS principle that border-collapse is inherited
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['line_height']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['line_height']) {
				$p['LINE-HEIGHT'] = $this->mpdf->blk[$this->mpdf->blklvl - 1]['line_height'];
			}
			// mPDF 6
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['line_stacking_strategy']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['line_stacking_strategy']) {
				$p['LINE-STACKING-STRATEGY'] = $this->mpdf->blk[$this->mpdf->blklvl - 1]['line_stacking_strategy'];
			}
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['line_stacking_shift']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['line_stacking_shift']) {
				$p['LINE-STACKING-SHIFT'] = $this->mpdf->blk[$this->mpdf->blklvl - 1]['line_stacking_shift'];
			}

			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['direction']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['direction']) {
				$p['DIRECTION'] = $this->mpdf->blk[$this->mpdf->blklvl - 1]['direction'];
			}
			// mPDF 6  Lists
			if ($tag === 'LI') {
				if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_type']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_type']) {
					$p['LIST-STYLE-TYPE'] = $this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_type'];
				}
			}
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_image']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_image']) {
				$p['LIST-STYLE-IMAGE'] = $this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_image'];
			}
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_position']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_position']) {
				$p['LIST-STYLE-POSITION'] = $this->mpdf->blk[$this->mpdf->blklvl - 1]['list_style_position'];
			}

			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['align']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['align']) {
				if ($this->mpdf->blk[$this->mpdf->blklvl - 1]['align'] === 'L') {
					$p['TEXT-ALIGN'] = 'left';
				} elseif ($this->mpdf->blk[$this->mpdf->blklvl - 1]['align'] === 'J') {
					$p['TEXT-ALIGN'] = 'justify';
				} elseif ($this->mpdf->blk[$this->mpdf->blklvl - 1]['align'] === 'R') {
					$p['TEXT-ALIGN'] = 'right';
				} elseif ($this->mpdf->blk[$this->mpdf->blklvl - 1]['align'] === 'C') {
					$p['TEXT-ALIGN'] = 'center';
				}
			}
			if ($this->mpdf->ColActive || $this->mpdf->keep_block_together) {
				if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['bgcolor']) && $this->mpdf->blk[$this->mpdf->blklvl - 1]['bgcolor']) { // Doesn't officially inherit, but default value is transparent (?=inherited)
					$cor = $this->mpdf->blk[$this->mpdf->blklvl - 1]['bgcolorarray'];
					$p['BACKGROUND-COLOR'] = $this->colorConverter->colAtoString($cor);
				}
			}

			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['text_indent']) && ($this->mpdf->blk[$this->mpdf->blklvl - 1]['text_indent'] || $this->mpdf->blk[$this->mpdf->blklvl - 1]['text_indent'] === 0)) {
				$p['TEXT-INDENT'] = $this->mpdf->blk[$this->mpdf->blklvl - 1]['text_indent'];
			}
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1]['InlineProperties'])) {
				$biilp = $this->mpdf->blk[$this->mpdf->blklvl - 1]['InlineProperties'];
				$this->inlinePropsToCSS($biilp, $p); // mPDF 5.7.1
			} else {
				$biilp = null;
			}
		}
		//===============================================
		//===============================================
		// INLINE HTML ATTRIBUTES e.g. .. ALIGN="CENTER">
		// mPDF 6 (added)
		if (isset($attr['DIR']) && $attr['DIR'] != '') {
			$p['DIRECTION'] = $attr['DIR'];
		}
		// mPDF 6 (moved)
		if (isset($attr['LANG']) && $attr['LANG'] != '') {
			$p['LANG'] = $attr['LANG'];
		}
		if (isset($attr['COLOR']) && $attr['COLOR'] != '') {
			$p['COLOR'] = $attr['COLOR'];
		}

		if ($tag !== 'INPUT') {
			if (isset($attr['WIDTH']) && $attr['WIDTH'] != '') {
				$p['WIDTH'] = $attr['WIDTH'];
			}
			if (isset($attr['HEIGHT']) && $attr['HEIGHT'] != '') {
				$p['HEIGHT'] = $attr['HEIGHT'];
			}
		}
		if ($tag === 'FONT') {
			if (isset($attr['FACE'])) {
				$p['FONT-FAMILY'] = $attr['FACE'];
			}
			if (isset($attr['SIZE']) && $attr['SIZE'] != '') {
				$s = '';
				if ($attr['SIZE'] === '+1') {
					$s = '120%';
				} elseif ($attr['SIZE'] === '-1') {
					$s = '86%';
				} elseif ($attr['SIZE'] === '1') {
					$s = 'XX-SMALL';
				} elseif ($attr['SIZE'] == '2') {
					$s = 'X-SMALL';
				} elseif ($attr['SIZE'] == '3') {
					$s = 'SMALL';
				} elseif ($attr['SIZE'] == '4') {
					$s = 'MEDIUM';
				} elseif ($attr['SIZE'] == '5') {
					$s = 'LARGE';
				} elseif ($attr['SIZE'] == '6') {
					$s = 'X-LARGE';
				} elseif ($attr['SIZE'] == '7') {
					$s = 'XX-LARGE';
				}
				if ($s) {
					$p['FONT-SIZE'] = $s;
				}
			}
		}
		if (isset($attr['VALIGN']) && $attr['VALIGN'] != '') {
			$p['VERTICAL-ALIGN'] = $attr['VALIGN'];
		}
		if (isset($attr['VSPACE']) && $attr['VSPACE'] != '') {
			$p['MARGIN-TOP'] = $attr['VSPACE'];
			$p['MARGIN-BOTTOM'] = $attr['VSPACE'];
		}
		if (isset($attr['HSPACE']) && $attr['HSPACE'] != '') {
			$p['MARGIN-LEFT'] = $attr['HSPACE'];
			$p['MARGIN-RIGHT'] = $attr['HSPACE'];
		}
		//===============================================
		//===============================================
		// DEFAULT for this TAG set in DefaultCSS
		if (isset($this->mpdf->defaultCSS[$tag])) {
			$zp = $this->fixCSS($this->mpdf->defaultCSS[$tag]);
			if (is_array($zp)) {  // Default overwrites Inherited
				$p = array_merge($p, $zp);  // !! Note other way round !!
				$this->_mergeBorders($p, $zp);
			}
		}
		//===============================================
		/* -- TABLES -- */
		// mPDF 5.7.3
		// cellSpacing overwrites TABLE default but not specific CSS set on table
		if ($tag === 'TABLE' && isset($attr['CELLSPACING'])) {
			$p['BORDER-SPACING-H'] = $p['BORDER-SPACING-V'] = $attr['CELLSPACING'];
		}
		// cellPadding overwrites TD/TH default but not specific CSS set on cell
		if (($tag === 'TD' || $tag === 'TH') && isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cell_padding']) && ($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cell_padding'] || $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cell_padding'] === '0')) {  // mPDF 5.7.3
			$p['PADDING-LEFT'] = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cell_padding'];
			$p['PADDING-RIGHT'] = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cell_padding'];
			$p['PADDING-TOP'] = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cell_padding'];
			$p['PADDING-BOTTOM'] = $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['cell_padding'];
		}
		/* -- END TABLES -- */
		//===============================================
		// STYLESHEET TAG e.g. h1  p  div  table
		if (isset($this->CSS[$tag]) && $this->CSS[$tag]) {
			$zp = $this->CSS[$tag];
			if ($tag === 'TD' || $tag === 'TH') {
				$this->setBorderDominance($zp, 9);
			} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
				$this->_mergeBorders($p, $zp);
			}
		}
		//===============================================
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach ($classes as $class) {
			$zp = [];
			if (isset($this->CSS['CLASS>>' . $class]) && $this->CSS['CLASS>>' . $class]) {
				$zp = $this->CSS['CLASS>>' . $class];
			}
			if ($tag === 'TD' || $tag === 'TH') {
				$this->setBorderDominance($zp, 9);
			} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
				$this->_mergeBorders($p, $zp);
			}
		}
		//===============================================
		/* -- TABLES -- */
		// STYLESHEET nth-child SELECTOR e.g. tr:nth-child(odd)  td:nth-child(2n+1)
		if ($tag === 'TR' || $tag === 'TD' || $tag === 'TH') {
			foreach ($this->CSS as $k => $val) {
				if (preg_match('/' . $tag . '>>SELECTORNTHCHILD>>(.*)/', $k, $m)) {
					$select = false;
					if ($tag === 'TR') {
						$row = $this->mpdf->row;
						$thnr = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead']) ? count($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead']) : 0);
						$tfnr = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot']) ? count($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot']) : 0);
						if ($this->mpdf->tabletfoot) {
							$row -= $thnr;
						} elseif (!$this->mpdf->tablethead) {
							$row -= ($thnr + $tfnr);
						}
						if (preg_match('/(([\-+]?\d*)?N([\-+]\d+)?|[\-+]?\d+|ODD|EVEN)/', $m[1], $a)) { // mPDF 5.7.4
							$select = $this->_nthchild($a, $row);
						}
					} elseif ($tag === 'TD' || $tag === 'TH') {
						if (preg_match('/(([\-+]?\d*)?N([\-+]\d+)?|[\-+]?\d+|ODD|EVEN)/', $m[1], $a)) { // mPDF 5.7.4
							$select = $this->_nthchild($a, $this->mpdf->col);
						}
					}
					if ($select) {
						$zp = $this->CSS[$tag . '>>SELECTORNTHCHILD>>' . $m[1]];
						if ($tag === 'TD' || $tag === 'TH') {
							$this->setBorderDominance($zp, 9);
						}
						if (is_array($zp)) {
							$p = array_merge($p, $zp);
							$this->_mergeBorders($p, $zp);
						}
					}
				}
			}
		}
		/* -- END TABLES -- */
		//===============================================
		// STYLESHEET LANG e.g. [lang=fr]{} or :lang(fr)
		if (isset($attr['LANG'])) {
			if (isset($this->CSS['LANG>>' . $attr['LANG']]) && $this->CSS['LANG>>' . $attr['LANG']]) {
				$zp = $this->CSS['LANG>>' . $attr['LANG']];
				if ($tag === 'TD' || $tag === 'TH') {
					$this->setBorderDominance($zp, 9);
				} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
				if (is_array($zp)) {
					$p = array_merge($p, $zp);
					$this->_mergeBorders($p, $zp);
				}
			} elseif (isset($this->CSS['LANG>>' . $shortlang]) && $this->CSS['LANG>>' . $shortlang]) {
				$zp = $this->CSS['LANG>>' . $shortlang];
				if ($tag === 'TD' || $tag === 'TH') {
					$this->setBorderDominance($zp, 9);
				} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
				if (is_array($zp)) {
					$p = array_merge($p, $zp);
					$this->_mergeBorders($p, $zp);
				}
			}
		}
		//===============================================
		// STYLESHEET ID e.g. #smallone{}  #redletter{}
		if (isset($attr['ID']) && isset($this->CSS['ID>>' . $attr['ID']]) && $this->CSS['ID>>' . $attr['ID']]) {
			$zp = $this->CSS['ID>>' . $attr['ID']];
			if ($tag === 'TD' || $tag === 'TH') {
				$this->setBorderDominance($zp, 9);
			} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
				$this->_mergeBorders($p, $zp);
			}
		}

		//===============================================
		// STYLESHEET CLASS e.g. p.smallone{}  div.redletter{}
		foreach ($classes as $class) {
			$zp = [];
			if (isset($this->CSS[$tag . '>>CLASS>>' . $class]) && $this->CSS[$tag . '>>CLASS>>' . $class]) {
				$zp = $this->CSS[$tag . '>>CLASS>>' . $class];
			}
			if ($tag === 'TD' || $tag === 'TH') {
				$this->setBorderDominance($zp, 9);
			} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
				$this->_mergeBorders($p, $zp);
			}
		}
		//===============================================
		// STYLESHEET LANG e.g. [lang=fr]{} or :lang(fr)
		if (isset($attr['LANG'])) {
			if (isset($this->CSS[$tag . '>>LANG>>' . $attr['LANG']]) && $this->CSS[$tag . '>>LANG>>' . $attr['LANG']]) {
				$zp = $this->CSS[$tag . '>>LANG>>' . $attr['LANG']];
				if ($tag === 'TD' || $tag === 'TH') {
					$this->setBorderDominance($zp, 9);
				} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
				if (is_array($zp)) {
					$p = array_merge($p, $zp);
					$this->_mergeBorders($p, $zp);
				}
			} elseif (isset($this->CSS[$tag . '>>LANG>>' . $shortlang]) && $this->CSS[$tag . '>>LANG>>' . $shortlang]) {
				$zp = $this->CSS[$tag . '>>LANG>>' . $shortlang];
				if ($tag === 'TD' || $tag === 'TH') {
					$this->setBorderDominance($zp, 9);
				} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
				if (is_array($zp)) {
					$p = array_merge($p, $zp);
					$this->_mergeBorders($p, $zp);
				}
			}
		}
		//===============================================
		// STYLESHEET CLASS e.g. p#smallone{}  div#redletter{}
		if (isset($attr['ID']) && isset($this->CSS[$tag . '>>ID>>' . $attr['ID']]) && $this->CSS[$tag . '>>ID>>' . $attr['ID']]) {
			$zp = $this->CSS[$tag . '>>ID>>' . $attr['ID']];
			if ($tag === 'TD' || $tag === 'TH') {
				$this->setBorderDominance($zp, 9);
			} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
				$this->_mergeBorders($p, $zp);
			}
		}
		//===============================================
		// Cascaded e.g. div.class p only works for block level
		if ($inherit === 'BLOCK') {
			if (isset($this->mpdf->blk[$this->mpdf->blklvl - 1])) { // mPDF 6
				$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS'][$tag], $p);
				foreach ($classes as $class) {
					$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS']['CLASS>>' . $class], $p);
				}
				$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS']['ID>>' . $attr['ID']], $p);
				foreach ($classes as $class) {
					$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS'][$tag . '>>CLASS>>' . $class], $p);
				}
				$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl - 1]['cascadeCSS'][$tag . '>>ID>>' . $attr['ID']], $p);
			}
		} elseif ($inherit === 'INLINE') {
			$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'][$tag], $p);
			foreach ($classes as $class) {
				$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS']['CLASS>>' . $class], $p);
			}
			$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS']['ID>>' . $attr['ID']], $p);
			foreach ($classes as $class) {
				$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'][$tag . '>>CLASS>>' . $class], $p);
			}
			$this->_set_mergedCSS($this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'][$tag . '>>ID>>' . $attr['ID']], $p);
		} elseif ($inherit === 'TOPTABLE' || $inherit === 'TABLE') { // NB looks at $this->tablecascadeCSS-1 for cascading CSS
			if (isset($this->tablecascadeCSS[$this->tbCSSlvl - 1])) { // mPDF 6
				// false, 9 = don't check for 'depth' and do set border dominance
				$this->_set_mergedCSS($this->tablecascadeCSS[$this->tbCSSlvl - 1][$tag], $p, false, 9);
				foreach ($classes as $class) {
					$this->_set_mergedCSS($this->tablecascadeCSS[$this->tbCSSlvl - 1]['CLASS>>' . $class], $p, false, 9);
				}
				// STYLESHEET nth-child SELECTOR e.g. tr:nth-child(odd)  td:nth-child(2n+1)
				if ($tag === 'TR' || $tag === 'TD' || $tag === 'TH') {
					foreach ($this->tablecascadeCSS[$this->tbCSSlvl - 1] as $k => $val) {
						if (preg_match('/' . $tag . '>>SELECTORNTHCHILD>>(.*)/', $k, $m)) {
							$select = false;
							if ($tag === 'TR') {
								$row = $this->mpdf->row;
								$thnr = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead']) ? count($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead']) : 0);
								$tfnr = (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot']) ? count($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot']) : 0);
								if ($this->mpdf->tabletfoot) {
									$row -= $thnr;
								} elseif (!$this->mpdf->tablethead) {
									$row -= ($thnr + $tfnr);
								}
								if (preg_match('/(([\-+]?\d*)?N([\-+]\d+)?|[\-+]?\d+|ODD|EVEN)/', $m[1], $a)) { // mPDF 5.7.4
									$select = $this->_nthchild($a, $row);
								}
							} elseif ($tag === 'TD' || $tag === 'TH') {
								if (preg_match('/(([\-+]?\d*)?N([\-+]\d+)?|[\-+]?\d+|ODD|EVEN)/', $m[1], $a)) { // mPDF 5.7.4
									$select = $this->_nthchild($a, $this->mpdf->col);
								}
							}
							if ($select) {
								$this->_set_mergedCSS($this->tablecascadeCSS[$this->tbCSSlvl - 1][$tag . '>>SELECTORNTHCHILD>>' . $m[1]], $p, false, 9);
							}
						}
					}
				}
			}
			$this->_set_mergedCSS($this->tablecascadeCSS[$this->tbCSSlvl - 1]['ID>>' . $attr['ID']], $p, false, 9);
			foreach ($classes as $class) {
				$this->_set_mergedCSS($this->tablecascadeCSS[$this->tbCSSlvl - 1][$tag . '>>CLASS>>' . $class], $p, false, 9);
			}
			$this->_set_mergedCSS($this->tablecascadeCSS[$this->tbCSSlvl - 1][$tag . '>>ID>>' . $attr['ID']], $p, false, 9);
		}

		// INLINE STYLE e.g. style="CSS:property"
		if (isset($attr['STYLE'])) {
			$zp = $this->readInlineCSS($attr['STYLE']);
			if ($tag === 'TD' || $tag === 'TH') {
				$this->setBorderDominance($zp, 9);
			} // *TABLES*	// *TABLES-ADVANCED-BORDERS*
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
				$this->_mergeBorders($p, $zp);
			}
		}

		return $p;
	}

	// Convert inline Properties back to CSS
	function inlinePropsToCSS($bilp, &$p)
	{
		if (isset($bilp['family']) && $bilp['family']) {
			$p['FONT-FAMILY'] = $bilp['family'];
		}
		if (isset($bilp['I']) && $bilp['I']) {
			$p['FONT-STYLE'] = 'italic';
		}
		if (isset($bilp['sizePt']) && $bilp['sizePt']) {
			$p['FONT-SIZE'] = $bilp['sizePt'] . 'pt';
		}
		if (isset($bilp['B']) && $bilp['B']) {
			$p['FONT-WEIGHT'] = 'bold';
		}
		if (isset($bilp['colorarray']) && $bilp['colorarray']) {
			$cor = $bilp['colorarray'];
			$p['COLOR'] = $this->colorConverter->colAtoString($cor);
		}
		if (isset($bilp['lSpacingCSS']) && $bilp['lSpacingCSS']) {
			$p['LETTER-SPACING'] = $bilp['lSpacingCSS'];
		}
		if (isset($bilp['wSpacingCSS']) && $bilp['wSpacingCSS']) {
			$p['WORD-SPACING'] = $bilp['wSpacingCSS'];
		}

		if (isset($bilp['textparam']) && $bilp['textparam']) {
			if (isset($bilp['textparam']['hyphens'])) {
				if ($bilp['textparam']['hyphens'] == 2) {
					$p['HYPHENS'] = 'none';
				}
				if ($bilp['textparam']['hyphens'] == 1) {
					$p['HYPHENS'] = 'auto';
				}
				if ($bilp['textparam']['hyphens'] == 0) {
					$p['HYPHENS'] = 'manual';
				}
			}
			if (isset($bilp['textparam']['outline-s']) && !$bilp['textparam']['outline-s']) {
				$p['TEXT-OUTLINE'] = 'none';
			}
			if (isset($bilp['textparam']['outline-COLOR']) && $bilp['textparam']['outline-COLOR']) {
				$p['TEXT-OUTLINE-COLOR'] = $this->colorConverter->colAtoString($bilp['textparam']['outline-COLOR']);
			}
			if (isset($bilp['textparam']['outline-WIDTH']) && $bilp['textparam']['outline-WIDTH']) {
				$p['TEXT-OUTLINE-WIDTH'] = $bilp['textparam']['outline-WIDTH'] . 'mm';
			}
		}

		if (isset($bilp['textvar']) && $bilp['textvar']) {
			// CSS says text-decoration is not inherited, but IE7 does??
			if ($bilp['textvar'] & TextVars::FD_LINETHROUGH) {
				if ($bilp['textvar'] & TextVars::FD_UNDERLINE) {
					$p['TEXT-DECORATION'] = 'underline line-through';
				} else {
					$p['TEXT-DECORATION'] = 'line-through';
				}
			} elseif ($bilp['textvar'] & TextVars::FD_UNDERLINE) {
				$p['TEXT-DECORATION'] = 'underline';
			} else {
				$p['TEXT-DECORATION'] = 'none';
			}

			if ($bilp['textvar'] & TextVars::FA_SUPERSCRIPT) {
				$p['VERTICAL-ALIGN'] = 'super';
			} elseif ($bilp['textvar'] & TextVars::FA_SUBSCRIPT) {
				$p['VERTICAL-ALIGN'] = 'sub';
			} else {
				$p['VERTICAL-ALIGN'] = 'baseline';
			}

			if ($bilp['textvar'] & TextVars::FT_CAPITALIZE) {
				$p['TEXT-TRANSFORM'] = 'capitalize';
			} elseif ($bilp['textvar'] & TextVars::FT_UPPERCASE) {
				$p['TEXT-TRANSFORM'] = 'uppercase';
			} elseif ($bilp['textvar'] & TextVars::FT_LOWERCASE) {
				$p['TEXT-TRANSFORM'] = 'lowercase';
			} else {
				$p['TEXT-TRANSFORM'] = 'none';
			}

			if ($bilp['textvar'] & TextVars::FC_KERNING) {
				$p['FONT-KERNING'] = 'normal';
			} // ignore 'auto' as default already applied
			//if (isset($bilp[ 'OTLtags' ]) && $bilp[ 'OTLtags' ]['Plus'] contains 'kern'
			else {
				$p['FONT-KERNING'] = 'none';
			}

			if ($bilp['textvar'] & TextVars::FA_SUPERSCRIPT) {
				$p['FONT-VARIANT-POSITION'] = 'super';
			} //if (isset($bilp[ 'OTLtags' ]) && $bilp[ 'OTLtags' ]['Plus'] contains 'sups' / 'subs'
			elseif ($bilp['textvar'] & TextVars::FA_SUBSCRIPT) {
				$p['FONT-VARIANT-POSITION'] = 'sub';
			} else {
				$p['FONT-VARIANT-POSITION'] = 'normal';
			}

			if ($bilp['textvar'] & TextVars::FC_SMALLCAPS) {
				$p['FONT-VARIANT-CAPS'] = 'small-caps';
			}
		}
		if (isset($bilp['fontLanguageOverride'])) {
			if ($bilp['fontLanguageOverride']) {
				$p['FONT-LANGUAGE-OVERRIDE'] = $bilp['fontLanguageOverride'];
			} else {
				$p['FONT-LANGUAGE-OVERRIDE'] = 'normal';
			}
		}
		// All the variations of font-variant-* we are going to set as font-feature-settings...
		if (isset($bilp['OTLtags']) && $bilp['OTLtags']) {
			$ffs = [];
			if (isset($bilp['OTLtags']['Minus']) && $bilp['OTLtags']['Minus']) {
				$f = preg_split('/\s+/', trim($bilp['OTLtags']['Minus']));
				foreach ($f as $ff) {
					$ffs[] = "'" . $ff . "' 0";
				}
			}
			if (isset($bilp['OTLtags']['FFMinus']) && $bilp['OTLtags']['FFMinus']) {
				$f = preg_split('/\s+/', trim($bilp['OTLtags']['FFMinus']));
				foreach ($f as $ff) {
					$ffs[] = "'" . $ff . "' 0";
				}
			}
			if (isset($bilp['OTLtags']['Plus']) && $bilp['OTLtags']['Plus']) {
				$f = preg_split('/\s+/', trim($bilp['OTLtags']['Plus']));
				foreach ($f as $ff) {
					$ffs[] = "'" . $ff . "' 1";
				}
			}
			if (isset($bilp['OTLtags']['FFPlus']) && $bilp['OTLtags']['FFPlus']) { // May contain numeric value e.g. salt4
				$f = preg_split('/\s+/', trim($bilp['OTLtags']['FFPlus']));
				foreach ($f as $ff) {
					if (strlen($ff) > 4) {
						$ffs[] = "'" . substr($ff, 0, 4) . "' " . substr($ff, 4);
					} else {
						$ffs[] = "'" . $ff . "' 1";
					}
				}
			}
			$p['FONT-FEATURE-SETTINGS'] = implode(', ', $ffs);
		}
	}

	function PreviewBlockCSS($tag, $attr)
	{
		// Looks ahead from current block level to a new level
		$p = [];

		$oldcascadeCSS = $this->mpdf->blk[$this->mpdf->blklvl]['cascadeCSS'];
		$classes = [];
		if (isset($attr['CLASS'])) {
			$classes = array_map(function ($combination) {
				return join('.', $combination);
			}, Arrays::allUniqueSortedCombinations(preg_split('/\s+/', $attr['CLASS'])));
		}
		//===============================================
		// DEFAULT for this TAG set in DefaultCSS
		if (isset($this->mpdf->defaultCSS[$tag])) {
			$zp = $this->fixCSS($this->mpdf->defaultCSS[$tag]);
			if (is_array($zp)) {
				$p = array_merge($zp, $p);
			} // Inherited overwrites default
		}
		// STYLESHEET TAG e.g. h1  p  div  table
		if (isset($this->CSS[$tag])) {
			$zp = $this->CSS[$tag];
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
			}
		}
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach ($classes as $class) {
			$zp = [];
			if (isset($this->CSS['CLASS>>' . $class])) {
				$zp = $this->CSS['CLASS>>' . $class];
			}
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
			}
		}
		// STYLESHEET ID e.g. #smallone{}  #redletter{}
		if (isset($attr['ID']) && isset($this->CSS['ID>>' . $attr['ID']])) {
			$zp = $this->CSS['ID>>' . $attr['ID']];
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
			}
		}
		// STYLESHEET CLASS e.g. p.smallone{}  div.redletter{}
		foreach ($classes as $class) {
			$zp = [];
			if (isset($this->CSS[$tag . '>>CLASS>>' . $class])) {
				$zp = $this->CSS[$tag . '>>CLASS>>' . $class];
			}
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
			}
		}
		// STYLESHEET CLASS e.g. p#smallone{}  div#redletter{}
		if (isset($attr['ID']) && isset($this->CSS[$tag . '>>ID>>' . $attr['ID']])) {
			$zp = $this->CSS[$tag . '>>ID>>' . $attr['ID']];
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
			}
		}
		//===============================================
		// STYLESHEET TAG e.g. div h1    div p

		$this->_set_mergedCSS($oldcascadeCSS[$tag], $p);
		// STYLESHEET CLASS e.g. .smallone{}  .redletter{}
		foreach ($classes as $class) {
			$this->_set_mergedCSS($oldcascadeCSS['CLASS>>' . $class], $p);
		}
		// STYLESHEET CLASS e.g. #smallone{}  #redletter{}
		if (isset($attr['ID'])) {
			$this->_set_mergedCSS($oldcascadeCSS['ID>>' . $attr['ID']], $p);
		}
		// STYLESHEET CLASS e.g. div.smallone{}  p.redletter{}
		foreach ($classes as $class) {
			$this->_set_mergedCSS($oldcascadeCSS[$tag . '>>CLASS>>' . $class], $p);
		}
		// STYLESHEET CLASS e.g. div#smallone{}  p#redletter{}
		if (isset($attr['ID'])) {
			$this->_set_mergedCSS($oldcascadeCSS[$tag . '>>ID>>' . $attr['ID']], $p);
		}
		//===============================================
		// INLINE STYLE e.g. style="CSS:property"
		if (isset($attr['STYLE'])) {
			$zp = $this->readInlineCSS($attr['STYLE']);
			if (is_array($zp)) {
				$p = array_merge($p, $zp);
			}
		}
		//===============================================
		return $p;
	}

	// mPDF 5.7.4   nth-child
	function _nthchild($f, $c)
	{
		// $f is formula e.g. 2N+1 split into a preg_match array
		// $c is the comparator value e.g row or column number
		$c += 1;
		$select = false;

		$f_count = count($f);
		if ($f[0] === 'ODD') {
			$a = 2;
			$b = 1;
		} elseif ($f[0] === 'EVEN') {
			$a = 2;
			$b = 0;
		} elseif ($f_count === 2) {
			$a = 0;
			$b = $f[1] + 0;
		} // e.g. (+6)
		elseif ($f_count === 3) {  // e.g. (2N)
			if ($f[2] == '') {
				$a = 1;
			} elseif ($f[2] == '-') {
				$a = -1;
			} else {
				$a = $f[2] + 0;
			}
			$b = 0;
		} elseif ($f_count === 4) {  // e.g. (2N+6)
			if ($f[2] == '') {
				$a = 1;
			} elseif ($f[2] == '-') {
				$a = -1;
			} else {
				$a = $f[2] + 0;
			}
			$b = $f[3] + 0;
		} else {
			return false;
		}
		if ($a > 0) {
			if (((($c % $a) - $b) % $a) === 0 && $c >= $b) {
				$select = true;
			}
		} elseif ($a == 0) {
			if ($c == $b) {
				$select = true;
			}
		} else {  // if ($a<0)
			if (((($c % $a) - $b) % $a) === 0 && $c <= $b) {
				$select = true;
			}
		}
		return $select;
	}

	private function getFileContents($path)
	{
		// If local file try using local path (? quicker, but also allowed even if allow_url_fopen false)
		$wrapperChecker = new StreamWrapperChecker($this->mpdf);
		if ($wrapperChecker->hasBlacklistedStreamWrapper($path)) {
			throw new \Mpdf\MpdfException('File contains an invalid stream. Only ' . implode(', ', $wrapperChecker->getWhitelistedStreamWrappers()) . ' streams are allowed.');
		}

		// mPDF 5.7.3
		if (strpos($path, '//') === false) {
			$path = preg_replace('/\.css\?.*$/', '.css', $path);
		}

		$contents = @file_get_contents($path);

		if ($contents) {
			return $contents;
		}

		if ($this->mpdf->basepathIsLocal) {

			$tr = parse_url($path);
			$lp = __FILE__;
			$ap = realpath($lp);
			$ap = str_replace("\\", '/', $ap);
			$docroot = substr($ap, 0, strpos($ap, $lp));

			// WriteHTML parses all paths to full URLs; may be local file name
			// DOCUMENT_ROOT is not returned on IIS
			if (!empty($tr['scheme']) && $tr['host'] && !empty($_SERVER['DOCUMENT_ROOT'])) {
				$localpath = $_SERVER['DOCUMENT_ROOT'] . $tr['path'];
			} elseif ($docroot) {
				$localpath = $docroot . $tr['path'];
			} else {
				$localpath = $path;
			}

			$contents = @file_get_contents($localpath);

		} elseif (!$contents && !ini_get('allow_url_fopen') && function_exists('curl_init')) { // if not use full URL

			$ch = curl_init($path);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$contents = curl_exec($ch);
			curl_close($ch);

		}

		return $contents;
	}

}
