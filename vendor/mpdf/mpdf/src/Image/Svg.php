<?php

namespace Mpdf\Image;

use Mpdf\Color\ColorConverter;
use Mpdf\Css\TextVars;
use Mpdf\CssManager;
use Mpdf\Language\LanguageToFontInterface;
use Mpdf\Language\ScriptToLanguageInterface;
use Mpdf\Mpdf;
use Mpdf\Otl;
use Mpdf\SizeConverter;
use Mpdf\Ucdn;
use Mpdf\Utils\Arrays;
use Mpdf\Utils\UtfString;

/**
 * SVG class modified for mPDF version >= 6.0
 *
 * Works in pixels as main units - converting to PDF units when outputing to PDF string and on returning size
 *
 * @author Ian Back
 * @author sylvain briand (syb@godisaduck.com), modified by rick trevino (rtrevino1@yahoo.com)
 *
 * @link http://www.godisaduck.com/svg2pdf_with_fpdf
 * @link http://rhodopsin.blogspot.com
 */
class Svg
{

	/**
	 * ATM marked as public in spite of xml handling callbacks
	 *
	 * @var \Mpdf\Mpdf
	 */
	public $mpdf;

	/**
	 * @var \Mpdf\Otl
	 */
	public $otl;

	/**
	 * @var \Mpdf\CssManager
	 */
	public $cssManager;

	/**
	 * @var \Mpdf\SizeConverter
	 */
	public $sizeConverter;

	/**
	 * @var \Mpdf\Color\ColorConverter
	 */
	public $colorConverter;

	/**
	 * @var \Mpdf\Language\LanguageToFontInterface
	 */
	public $languageToFont;

	/**
	 * @var \Mpdf\Language\ScriptToLanguageInterface
	 */
	public $scriptToLanguage;

	/**
	 * @var \Mpdf\Image\ImageProcessor
	 */
	private $imageProcessor;

	/**
	 * Holds content of SVG fonts defined in image
	 *
	 * @var array
	 */
	var $svg_font;

	/**
	 * contient les infos sur les gradient fill du svg classé par id du svg
	 *
	 * @var array
	 */
	var $svg_gradient;

	/**
	 * contient les ids des objet shading
	 *
	 * @var array
	 */
	var $svg_shadinglist;

	/**
	 * contenant les infos du svg voulue par l'utilisateur
	 *
	 * @var array
	 */
	var $svg_info;

	/**
	 * holds all attributes of root <svg> tag
	 *
	 * @var array
	 */
	var $svg_attribs;

	/**
	 * contenant les style de groupes du svg
	 *
	 * @var array
	 */
	var $svg_style;

	/**
	 * contenant le tracage du svg en lui même.
	 *
	 * @var string
	 */
	var $svg_string;

	/**
	 * holds string info to write txt to image
	 *
	 * @var string
	 */
	var $txt_data;

	/**
	 * @var array
	 */
	var $txt_style;

	var $xbase;

	var $ybase;

	var $svg_error;

	var $subPathInit;

	var $spxstart;

	var $spystart;

	var $kp; // convert pixels to PDF units

	var $pathBBox;

	var $textlength; // mPDF 5.7.4

	var $texttotallength; // mPDF 5.7.4

	var $textoutput; // mPDF 5.7.4

	var $textanchor; // mPDF 5.7.4

	var $textXorigin; // mPDF 5.7.4

	var $textYorigin; // mPDF 5.7.4

	var $textjuststarted; // mPDF 5.7.4

	var $intext;  // mPDF 5.7.4

	private $dashesUsed;

	private $kf;

	private $lastcommand;

	private $lastcontrolpoints;

	private $inDefs;

	public function __construct(
		Mpdf $mpdf,
		Otl $otl,
		CssManager $cssManager,
		ImageProcessor $imageProcessor,
		SizeConverter $sizeConverter,
		ColorConverter $colorConverter,
		LanguageToFontInterface $languageToFont,
		ScriptToLanguageInterface $scriptToLanguage
	) {

		$this->mpdf = $mpdf;
		$this->otl = $otl;
		$this->cssManager = $cssManager;
		$this->imageProcessor = $imageProcessor;
		$this->sizeConverter = $sizeConverter;
		$this->colorConverter = $colorConverter;
		$this->languageToFont = $languageToFont;
		$this->scriptToLanguage = $scriptToLanguage;

		$this->svg_font = []; // mPDF 6
		$this->svg_gradient = [];
		$this->svg_shadinglist = [];
		$this->txt_data = [];
		$this->svg_string = '';
		$this->svg_info = [];
		$this->svg_attribs = [];
		$this->xbase = 0;
		$this->ybase = 0;
		$this->svg_error = false;
		$this->subPathInit = false;
		$this->dashesUsed = false;

		$this->textlength = 0; // mPDF 5.7.4
		$this->texttotallength = 0; // mPDF 5.7.4
		$this->textoutput = ''; // mPDF 5.7.4
		$this->textanchor = 'start'; // mPDF 5.7.4
		$this->textXorigin = 0; // mPDF 5.7.4
		$this->textYorigin = 0; // mPDF 5.7.4
		$this->textjuststarted = false; // mPDF 5.7.4
		$this->intext = false; // mPDF 5.7.4

		$this->kp = 72 / $mpdf->img_dpi; // constant To convert pixels to pts/PDF units
		$this->kf = 1; // constant To convert font size if re-mapped
		$this->pathBBox = [];

		$this->svg_style = [
			[
				'fill' => 'black',
				'fill-opacity' => 1, //	remplissage opaque par defaut
				'fill-rule' => 'nonzero', //	mode de remplissage par defaut
				'stroke' => 'none', //	pas de trait par defaut
				'stroke-linecap' => 'butt', //	style de langle par defaut
				'stroke-linejoin' => 'miter',
				'stroke-miterlimit' => 4, //	limite de langle par defaut
				'stroke-opacity' => 1, //	trait opaque par defaut
				'stroke-width' => 1,
				'stroke-dasharray' => 0,
				'stroke-dashoffset' => 0,
				'color' => ''
			]
		];

		$this->txt_style = [
			[
				'fill' => 'black', //	pas de remplissage par defaut
				'font-family' => $mpdf->default_font,
				'font-size' => $mpdf->default_font_size, // 	****** this is pts
				'font-weight' => 'normal', //	normal | bold
				'font-style' => 'normal', //	italic | normal
				'text-anchor' => 'start', // alignment: start, middle, end
				'fill-opacity' => 1, //	remplissage opaque par defaut
				'fill-rule' => 'nonzero', //	mode de remplissage par defaut
				'stroke' => 'none', //	pas de trait par defaut
				'stroke-opacity' => 1, //	trait opaque par defaut
				'stroke-width' => 1,
				'color' => ''
			]
		];
	}

	// mPDF 5.7.4 Embedded image
	function svgImage($attribs)
	{
		// x and y are coordinates
		$x = (isset($attribs['x']) ? $attribs['x'] : 0);
		$y = (isset($attribs['y']) ? $attribs['y'] : 0);
		// preserveAspectRatio
		$par = (isset($attribs['preserveAspectRatio']) ? $attribs['preserveAspectRatio'] : 'xMidYMid meet');
		// width and height are <lengths> - Required attributes
		$wset = (isset($attribs['width']) ? $attribs['width'] : 0);
		$hset = (isset($attribs['height']) ? $attribs['height'] : 0);
		$w = $this->sizeConverter->convert($wset, $this->svg_info['w'] * (25.4 / $this->mpdf->dpi), $this->mpdf->FontSize, false);
		$h = $this->sizeConverter->convert($hset, $this->svg_info['h'] * (25.4 / $this->mpdf->dpi), $this->mpdf->FontSize, false);
		if ($w == 0 || $h == 0) {
			return;
		}
		// Convert to pixels = SVG units
		$w *= 1 / (25.4 / $this->mpdf->dpi);
		$h *= 1 / (25.4 / $this->mpdf->dpi);

		$srcpath = $attribs['xlink:href'];
		$orig_srcpath = '';
		if (trim($srcpath) != '' && substr($srcpath, 0, 4) == 'var:') {
			$orig_srcpath = $srcpath;
			$srcpath = $this->mpdf->GetFullPath($srcpath);
		}

		// Image file (does not allow vector images i.e. WMF/SVG)
		// mPDF 6 Added $this->mpdf->interpolateImages
		$info = $this->imageProcessor->getImage($srcpath, true, false, $orig_srcpath, $this->mpdf->interpolateImages);
		if (!$info) {
			return;
		}

		// x,y,w,h define the reference rectangle
		$img_h = $h;
		$img_w = $w;
		$img_x = $x;
		$img_y = $y;
		$meetOrSlice = 'meet';

		// preserveAspectRatio
		$ar = preg_split('/\s+/', strtolower($par));
		if ($ar[0] != 'none') { // If "none" need to do nothing
			//  Force uniform scaling
			if (isset($ar[1]) && $ar[1] == 'slice') {
				$meetOrSlice = 'slice';
			} else {
				$meetOrSlice = 'meet';
			}
			if ($info['h'] / $info['w'] > $h / $w) {
				if ($meetOrSlice == 'meet') { // the entire viewBox is visible within the viewport
					$img_w = $img_h * $info['w'] / $info['h'];
				} else { // the entire viewport is covered by the viewBox
					$img_h = $img_w * $info['h'] / $info['w'];
				}
			} elseif ($info['h'] / $info['w'] < $h / $w) {
				if ($meetOrSlice == 'meet') { // the entire viewBox is visible within the viewport
					$img_h = $img_w * $info['h'] / $info['w'];
				} else { // the entire viewport is covered by the viewBox
					$img_w = $img_h * $info['w'] / $info['h'];
				}
			}
			if ($ar[0] == 'xminymin') {
				// do nothing to x
				// do nothing to y
			} elseif ($ar[0] == 'xmidymin') {
				$img_x += $w / 2 - $img_w / 2; // xMid
				// do nothing to y
			} elseif ($ar[0] == 'xmaxymin') {
				$img_x += $w - $img_w; // xMax
				// do nothing to y
			} elseif ($ar[0] == 'xminymid') {
				// do nothing to x
				$img_y += $h / 2 - $img_h / 2; // yMid
			} elseif ($ar[0] == 'xmaxymid') {
				$img_x += $w - $img_w; // xMax
				$img_y += $h / 2 - $img_h / 2; // yMid
			} elseif ($ar[0] == 'xminymax') {
				// do nothing to x
				$img_y += $h - $img_h; // yMax
			} elseif ($ar[0] == 'xmidymax') {
				$img_x += $w / 2 - $img_w / 2; // xMid
				$img_y += $h - $img_h; // yMax
			} elseif ($ar[0] == 'xmaxymax') {
				$img_x += $w - $img_w; // xMax
				$img_y += $h - $img_h; // yMax
			} else { // xMidYMid (the default)
				$img_x += $w / 2 - $img_w / 2; // xMid
				$img_y += $h / 2 - $img_h / 2; // yMid
			}
		}

		// Output
		if ($meetOrSlice == 'slice') { // need to add a clipping path to reference rectangle
			$s = ' q 0 w '; // Line width=0
			$s .= sprintf('%.3F %.3F m ', ($x) * $this->kp, (-($y + $h)) * $this->kp); // start point TL before the arc
			$s .= sprintf('%.3F %.3F l ', ($x) * $this->kp, (-($y)) * $this->kp); // line to BL
			$s .= sprintf('%.3F %.3F l ', ($x + $w) * $this->kp, (-($y)) * $this->kp); // line to BR
			$s .= sprintf('%.3F %.3F l ', ($x + $w) * $this->kp, (-($y + $h)) * $this->kp); // line to TR
			$s .= sprintf('%.3F %.3F l ', ($x) * $this->kp, (-($y + $h)) * $this->kp); // line to TL
			$s .= ' W n '; // Ends path no-op & Sets the clipping path
			$this->svgWriteString($s);
		}

		$outstring = sprintf(" q %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q ", $img_w * $this->kp, $img_h * $this->kp, $img_x * $this->kp, -($img_y + $img_h) * $this->kp, $info['i']);
		$this->svgWriteString($outstring);

		if ($meetOrSlice == 'slice') { // need to end clipping path
			$this->svgWriteString(' Q ');
		}
	}

	function svgGradient($gradient_info, $attribs, $element)
	{
		$n = count($this->mpdf->gradients) + 1;

		// Get bounding dimensions of element
		$w = 100;
		$h = 100;
		$x_offset = 0;
		$y_offset = 0;
		if ($element == 'rect') {
			$w = $attribs['width'];
			$h = $attribs['height'];
			$x_offset = $attribs['x'];
			$y_offset = $attribs['y'];
		} elseif ($element == 'ellipse') {
			$w = $attribs['rx'] * 2;
			$h = $attribs['ry'] * 2;
			$x_offset = $attribs['cx'] - $attribs['rx'];
			$y_offset = $attribs['cy'] - $attribs['ry'];
		} elseif ($element == 'circle') {
			$w = $attribs['r'] * 2;
			$h = $attribs['r'] * 2;
			$x_offset = $attribs['cx'] - $attribs['r'];
			$y_offset = $attribs['cy'] - $attribs['r'];
		} elseif ($element == 'polygon') {
			$pts = preg_split('/[ ,]+/', trim($attribs['points']));
			$maxr = $maxb = 0;
			$minl = $mint = 999999;
			for ($i = 0; $i < count($pts); $i++) {
				if ($i % 2 == 0) { // x values
					$minl = min($minl, $pts[$i]);
					$maxr = max($maxr, $pts[$i]);
				} else { // y values
					$mint = min($mint, $pts[$i]);
					$maxb = max($maxb, $pts[$i]);
				}
			}
			$w = $maxr - $minl;
			$h = $maxb - $mint;
			$x_offset = $minl;
			$y_offset = $mint;
		} elseif ($element == 'path') {
			if (is_array($this->pathBBox) && $this->pathBBox[2] > 0) {
				$w = $this->pathBBox[2];
				$h = $this->pathBBox[3];
				$x_offset = $this->pathBBox[0];
				$y_offset = $this->pathBBox[1];
			} else {
				preg_match_all('/([a-z]|[A-Z])([ ,\-.\d]+)*/', $attribs['d'], $commands, PREG_SET_ORDER);
				$maxr = $maxb = 0;
				$minl = $mint = 999999;
				foreach ($commands as $c) {
					if (count($c) == 3) {
						list($tmp, $cmd, $arg) = $c;
						if ($cmd == 'M' || $cmd == 'L' || $cmd == 'C' || $cmd == 'S' || $cmd == 'Q' || $cmd == 'T') {
							$pts = preg_split('/[ ,]+/', trim($arg));
							for ($i = 0; $i < count($pts); $i++) {
								if ($i % 2 == 0) { // x values
									$minl = min($minl, $pts[$i]);
									$maxr = max($maxr, $pts[$i]);
								} else { // y values
									$mint = min($mint, $pts[$i]);
									$maxb = max($maxb, $pts[$i]);
								}
							}
						}
						if ($cmd == 'H') { // sets new x
							$minl = min($minl, $arg);
							$maxr = max($maxr, $arg);
						}
						if ($cmd == 'V') { // sets new y
							$mint = min($mint, $arg);
							$maxb = max($maxb, $arg);
						}
					}
				}
				$w = $maxr - $minl;
				$h = $maxb - $mint;
				$x_offset = $minl;
				$y_offset = $mint;
			}
		}
		if (!$w || $w == -999999) {
			$w = 100;
		}
		if (!$h || $h == -999999) {
			$h = 100;
		}
		if ($x_offset == 999999) {
			$x_offset = 0;
		}
		if ($y_offset == 999999) {
			$y_offset = 0;
		}

		// TRANSFORMATIONS
		$transformations = '';
		if (isset($gradient_info['transform'])) {
			preg_match_all('/(matrix|translate|scale|rotate|skewX|skewY)\((.*?)\)/is', $gradient_info['transform'], $m);
			if (count($m[0])) {
				for ($i = 0; $i < count($m[0]); $i++) {
					$c = strtolower($m[1][$i]);
					$v = trim($m[2][$i]);
					$vv = preg_split('/[ ,]+/', $v);
					if ($c == 'matrix' && count($vv) == 6) {
						// Note angle of rotation is reversed (from SVG to PDF), so vv[1] and vv[2] are negated
						// cf svgDefineStyle()
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F %.3F %.3F cm ', $vv[0], -$vv[1], -$vv[2], $vv[3], $vv[4] * $this->kp, -$vv[5] * $this->kp);
					} elseif ($c == 'translate' && count($vv)) {
						$tm[4] = $vv[0];
						if (count($vv) == 2) {
							$t_y = -$vv[1];
						} else {
							$t_y = 0;
						}
						$tm[5] = $t_y;
						$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $tm[4] * $this->kp, $tm[5] * $this->kp);
					} elseif ($c == 'scale' && count($vv)) {
						if (count($vv) == 2) {
							$s_y = $vv[1];
						} else {
							$s_y = $vv[0];
						}
						$tm[0] = $vv[0];
						$tm[3] = $s_y;
						$transformations .= sprintf(' %.3F 0 0 %.3F 0 0 cm ', $tm[0], $tm[3]);
					} elseif ($c == 'rotate' && count($vv)) {
						$tm[0] = cos(deg2rad(-$vv[0]));
						$tm[1] = sin(deg2rad(-$vv[0]));
						$tm[2] = -$tm[1];
						$tm[3] = $tm[0];
						if (count($vv) == 3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $vv[1] * $this->kp, -$vv[2] * $this->kp);
						}
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F 0 0 cm ', $tm[0], $tm[1], $tm[2], $tm[3]);
						if (count($vv) == 3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', -$vv[1] * $this->kp, $vv[2] * $this->kp);
						}
					} elseif ($c == 'skewx' && count($vv)) {
						$tm[2] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 0 %.3F 1 0 0 cm ', $tm[2]);
					} elseif ($c == 'skewy' && count($vv)) {
						$tm[1] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 %.3F 0 1 0 0 cm ', $tm[1]);
					}
				}
			}
		}


		$return = "";

		if (isset($gradient_info['units']) && strtolower($gradient_info['units']) == 'userspaceonuse') {
			if ($transformations) {
				$return .= $transformations;
			}
		}
		$spread = 'P';  // pad
		if (isset($gradient_info['spread'])) {
			if (strtolower($gradient_info['spread']) == 'reflect') {
				$spread = 'F';
			} // reflect
			elseif (strtolower($gradient_info['spread']) == 'repeat') {
				$spread = 'R';
			} // repeat
		}


		for ($i = 0; $i < (count($gradient_info['color'])); $i++) {
			if (stristr($gradient_info['color'][$i]['offset'], '%') !== false) {
				$gradient_info['color'][$i]['offset'] = ((float) $gradient_info['color'][$i]['offset']) / 100;
			}
			if (isset($gradient_info['color'][($i + 1)]['offset']) && stristr($gradient_info['color'][($i + 1)]['offset'], '%') !== false) {
				$gradient_info['color'][($i + 1)]['offset'] = ((float) $gradient_info['color'][($i + 1)]['offset']) / 100;
			}
			if ($gradient_info['color'][$i]['offset'] < 0) {
				$gradient_info['color'][$i]['offset'] = 0;
			}
			if ($gradient_info['color'][$i]['offset'] > 1) {
				$gradient_info['color'][$i]['offset'] = 1;
			}
			if ($i > 0) {
				if ($gradient_info['color'][$i]['offset'] < $gradient_info['color'][($i - 1)]['offset']) {
					$gradient_info['color'][$i]['offset'] = $gradient_info['color'][($i - 1)]['offset'];
				}
			}
		}

		if (isset($gradient_info['color'][0]['offset']) && $gradient_info['color'][0]['offset'] > 0) {
			array_unshift($gradient_info['color'], $gradient_info['color'][0]);
			$gradient_info['color'][0]['offset'] = 0;
		}
		$ns = count($gradient_info['color']);
		if (isset($gradient_info['color'][($ns - 1)]['offset']) && $gradient_info['color'][($ns - 1)]['offset'] < 1) {
			$gradient_info['color'][] = $gradient_info['color'][($ns - 1)];
			$gradient_info['color'][($ns)]['offset'] = 1;
		}
		$ns = count($gradient_info['color']);




		if ($gradient_info['type'] == 'linear') {
			// mPDF 4.4.003
			if (isset($gradient_info['units']) && strtolower($gradient_info['units']) == 'userspaceonuse') {
				if (isset($gradient_info['info']['x1'])) {
					$gradient_info['info']['x1'] = ($gradient_info['info']['x1'] - $x_offset) / $w;
				}
				if (isset($gradient_info['info']['y1'])) {
					$gradient_info['info']['y1'] = ($gradient_info['info']['y1'] - $y_offset) / $h;
				}
				if (isset($gradient_info['info']['x2'])) {
					$gradient_info['info']['x2'] = ($gradient_info['info']['x2'] - $x_offset) / $w;
				}
				if (isset($gradient_info['info']['y2'])) {
					$gradient_info['info']['y2'] = ($gradient_info['info']['y2'] - $y_offset) / $h;
				}
			}
			if (isset($gradient_info['info']['x1'])) {
				$x1 = $gradient_info['info']['x1'];
			} else {
				$x1 = 0;
			}
			if (isset($gradient_info['info']['y1'])) {
				$y1 = $gradient_info['info']['y1'];
			} else {
				$y1 = 0;
			}
			if (isset($gradient_info['info']['x2'])) {
				$x2 = $gradient_info['info']['x2'];
			} else {
				$x2 = 1;
			}
			if (isset($gradient_info['info']['y2'])) {
				$y2 = $gradient_info['info']['y2'];
			} else {
				$y2 = 0;
			} // mPDF 6

			if (stristr($x1, '%') !== false) {
				$x1 = ($x1 + 0) / 100;
			}
			if (stristr($x2, '%') !== false) {
				$x2 = ($x2 + 0) / 100;
			}
			if (stristr($y1, '%') !== false) {
				$y1 = ($y1 + 0) / 100;
			}
			if (stristr($y2, '%') !== false) {
				$y2 = ($y2 + 0) / 100;
			}

			// mPDF 5.0.042
			$bboxw = $w;
			$bboxh = $h;
			$usex = $x_offset;
			$usey = $y_offset;
			$usew = $bboxw;
			$useh = $bboxh;
			if (isset($gradient_info['units']) && strtolower($gradient_info['units']) == 'userspaceonuse') {
				$angle = rad2deg(atan2(($gradient_info['info']['y2'] - $gradient_info['info']['y1']), ($gradient_info['info']['x2'] - $gradient_info['info']['x1'])));
				if ($angle < 0) {
					$angle += 360;
				} elseif ($angle > 360) {
					$angle -= 360;
				}
				if ($angle != 0 && $angle != 360 && $angle != 90 && $angle != 180 && $angle != 270) {
					if ($w >= $h) {
						$y1 *= $h / $w;
						$y2 *= $h / $w;
						$usew = $useh = $bboxw;
					} else {
						$x1 *= $w / $h;
						$x2 *= $w / $h;
						$usew = $useh = $bboxh;
					}
				}
			}
			$a = $usew;  // width
			$d = -$useh; // height
			$e = $usex;  // x- offset
			$f = -$usey; // -y-offset

			$return .= sprintf('%.3F 0 0 %.3F %.3F %.3F cm ', $a * $this->kp, $d * $this->kp, $e * $this->kp, $f * $this->kp);

			if (isset($gradient_info['units']) && strtolower($gradient_info['units']) == 'objectboundingbox') {
				if ($transformations) {
					$return .= $transformations;
				}
			}

			$trans = false;

			if ($spread == 'R' || $spread == 'F') { // Repeat  /  Reflect

				$offs = [];
				for ($i = 0; $i < $ns; $i++) {
					$offs[$i] = $gradient_info['color'][$i]['offset'];
				}

				$gp = 0;
				$inside = true;

				while ($inside) {
					$gp++;
					for ($i = 0; $i < $ns; $i++) {
						if ($spread == 'F' && ($gp % 2) == 1) { // Reflect
							$gradient_info['color'][(($ns * $gp) + $i)] = $gradient_info['color'][(($ns * ($gp - 1)) + ($ns - $i - 1))];
							$tmp = $gp + (1 - $offs[($ns - $i - 1)]);
							$gradient_info['color'][(($ns * $gp) + $i)]['offset'] = $tmp;
						} else { // Reflect
							$gradient_info['color'][(($ns * $gp) + $i)] = $gradient_info['color'][$i];
							$tmp = $gp + $offs[$i];
							$gradient_info['color'][(($ns * $gp) + $i)]['offset'] = $tmp;
						}
						// IF STILL INSIDE BOX OR STILL VALID
						// Point on axis to test
						$px1 = $x1 + ($x2 - $x1) * $tmp;
						$py1 = $y1 + ($y2 - $y1) * $tmp;
						// Get perpendicular axis
						$alpha = atan2($y2 - $y1, $x2 - $x1);
						$alpha += M_PI / 2; // rotate 90 degrees
						// Get arbitrary point to define line perpendicular to axis
						$px2 = $px1 + cos($alpha);
						$py2 = $py1 + sin($alpha);

						$res1 = $this->testIntersect($px1, $py1, $px2, $py2, 0, 0, 0, 1); // $x=0 vert axis
						$res2 = $this->testIntersect($px1, $py1, $px2, $py2, 1, 0, 1, 1); // $x=1 vert axis
						$res3 = $this->testIntersect($px1, $py1, $px2, $py2, 0, 0, 1, 0); // $y=0 horiz axis
						$res4 = $this->testIntersect($px1, $py1, $px2, $py2, 0, 1, 1, 1); // $y=1 horiz axis

						if (!$res1 && !$res2 && !$res3 && !$res4) {
							$inside = false;
						}
					}
				}

				$inside = true;
				$gp = 0;
				while ($inside) {
					$gp++;
					$newarr = [];
					for ($i = 0; $i < $ns; $i++) {
						if ($spread == 'F') { // Reflect
							$newarr[$i] = $gradient_info['color'][($ns - $i - 1)];
							if (($gp % 2) == 1) {
								$tmp = -$gp + (1 - $offs[($ns - $i - 1)]);
								$newarr[$i]['offset'] = $tmp;
							} else {
								$tmp = -$gp + $offs[$i];
								$newarr[$i]['offset'] = $tmp;
							}
						} else { // Reflect
							$newarr[$i] = $gradient_info['color'][$i];
							$tmp = -$gp + $offs[$i];
							$newarr[$i]['offset'] = $tmp;
						}

						// IF STILL INSIDE BOX OR STILL VALID
						// Point on axis to test
						$px1 = $x1 + ($x2 - $x1) * $tmp;
						$py1 = $y1 + ($y2 - $y1) * $tmp;
						// Get perpendicular axis
						$alpha = atan2($y2 - $y1, $x2 - $x1);
						$alpha += M_PI / 2; // rotate 90 degrees
						// Get arbitrary point to define line perpendicular to axis
						$px2 = $px1 + cos($alpha);
						$py2 = $py1 + sin($alpha);

						$res1 = $this->testIntersect($px1, $py1, $px2, $py2, 0, 0, 0, 1); // $x=0 vert axis
						$res2 = $this->testIntersect($px1, $py1, $px2, $py2, 1, 0, 1, 1); // $x=1 vert axis
						$res3 = $this->testIntersect($px1, $py1, $px2, $py2, 0, 0, 1, 0); // $y=0 horiz axis
						$res4 = $this->testIntersect($px1, $py1, $px2, $py2, 0, 1, 1, 1); // $y=1 horiz axis
						if (!$res1 && !$res2 && !$res3 && !$res4) {
							$inside = false;
						}
					}
					for ($i = ($ns - 1); $i >= 0; $i--) {
						if (isset($newarr[$i]['offset'])) {
							array_unshift($gradient_info['color'], $newarr[$i]);
						}
					}
				}
			}

			// Gradient STOPs
			$stops = count($gradient_info['color']);
			if ($stops < 2) {
				return '';
			}

			$range = $gradient_info['color'][count($gradient_info['color']) - 1]['offset'] - $gradient_info['color'][0]['offset'];
			$min = $gradient_info['color'][0]['offset'];

			for ($i = 0; $i < ($stops); $i++) {
				if (!$gradient_info['color'][$i]['color']) {
					if ($gradient_info['colorspace'] == 'RGB') {
						$gradient_info['color'][$i]['color'] = '0 0 0';
					} elseif ($gradient_info['colorspace'] == 'Gray') {
						$gradient_info['color'][$i]['color'] = '0';
					} elseif ($gradient_info['colorspace'] == 'CMYK') {
						$gradient_info['color'][$i]['color'] = '1 1 1 1';
					}
				}
				$offset = ($gradient_info['color'][$i]['offset'] - $min) / $range;
				$this->mpdf->gradients[$n]['stops'][] = [
					'col' => $gradient_info['color'][$i]['color'],
					'opacity' => $gradient_info['color'][$i]['opacity'],
					'offset' => $offset];
				if ($gradient_info['color'][$i]['opacity'] < 1) {
					$trans = true;
				}
			}
			$grx1 = $x1 + ($x2 - $x1) * $gradient_info['color'][0]['offset'];
			$gry1 = $y1 + ($y2 - $y1) * $gradient_info['color'][0]['offset'];
			$grx2 = $x1 + ($x2 - $x1) * $gradient_info['color'][count($gradient_info['color']) - 1]['offset'];
			$gry2 = $y1 + ($y2 - $y1) * $gradient_info['color'][count($gradient_info['color']) - 1]['offset'];

			$this->mpdf->gradients[$n]['coords'] = [$grx1, $gry1, $grx2, $gry2];

			$this->mpdf->gradients[$n]['colorspace'] = $gradient_info['colorspace'];

			$this->mpdf->gradients[$n]['type'] = 2;
			$this->mpdf->gradients[$n]['fo'] = true;

			$this->mpdf->gradients[$n]['extend'] = ['true', 'true'];
			if ($trans) {
				$this->mpdf->gradients[$n]['trans'] = true;
				$return .= ' /TGS' . ($n) . ' gs ';
			}
			$return .= ' /Sh' . ($n) . ' sh ';
			$return .= " Q\n";
		} elseif ($gradient_info['type'] == 'radial') {
			if (isset($gradient_info['units']) && strtolower($gradient_info['units']) == 'userspaceonuse') {
				if ($w > $h) {
					$h = $w;
				} else {
					$w = $h;
				}
				if (isset($gradient_info['info']['x0'])) {
					$gradient_info['info']['x0'] = ($gradient_info['info']['x0'] - $x_offset) / $w;
				}
				if (isset($gradient_info['info']['y0'])) {
					$gradient_info['info']['y0'] = ($gradient_info['info']['y0'] - $y_offset) / $h;
				}
				if (isset($gradient_info['info']['x1'])) {
					$gradient_info['info']['x1'] = ($gradient_info['info']['x1'] - $x_offset) / $w;
				}
				if (isset($gradient_info['info']['y1'])) {
					$gradient_info['info']['y1'] = ($gradient_info['info']['y1'] - $y_offset) / $h;
				}
				if (isset($gradient_info['info']['r'])) {
					$gradient_info['info']['rx'] = $gradient_info['info']['r'] / $w;
				}
				if (isset($gradient_info['info']['r'])) {
					$gradient_info['info']['ry'] = $gradient_info['info']['r'] / $h;
				}
			}

			if (isset($gradient_info['info']['x0'])) {
				$x0 = $gradient_info['info']['x0'];
			} else {
				$x0 = 0.5;
			}
			if (isset($gradient_info['info']['y0'])) {
				$y0 = $gradient_info['info']['y0'];
			} else {
				$y0 = 0.5;
			}
			if (isset($gradient_info['info']['rx'])) {
				$rx = $gradient_info['info']['rx'];
			} elseif (isset($gradient_info['info']['r'])) {
				$rx = $gradient_info['info']['r'];
			} else {
				$rx = 0.5;
			}
			if (isset($gradient_info['info']['ry'])) {
				$ry = $gradient_info['info']['ry'];
			} elseif (isset($gradient_info['info']['r'])) {
				$ry = $gradient_info['info']['r'];
			} else {
				$ry = 0.5;
			}
			if (isset($gradient_info['info']['x1'])) {
				$x1 = $gradient_info['info']['x1'];
			} else {
				$x1 = $x0;
			}
			if (isset($gradient_info['info']['y1'])) {
				$y1 = $gradient_info['info']['y1'];
			} else {
				$y1 = $y0;
			}

			if (stristr($x1, '%') !== false) {
				$x1 = ($x1 + 0) / 100;
			}
			if (stristr($x0, '%') !== false) {
				$x0 = ($x0 + 0) / 100;
			}
			if (stristr($y1, '%') !== false) {
				$y1 = ($y1 + 0) / 100;
			}
			if (stristr($y0, '%') !== false) {
				$y0 = ($y0 + 0) / 100;
			}
			if (stristr($rx, '%') !== false) {
				$rx = ($rx + 0) / 100;
			}
			if (stristr($ry, '%') !== false) {
				$ry = ($ry + 0) / 100;
			}

			$bboxw = $w;
			$bboxh = $h;
			$usex = $x_offset;
			$usey = $y_offset;
			$usew = $bboxw;
			$useh = $bboxh;

			if (isset($gradient_info['units']) && strtolower($gradient_info['units']) == 'userspaceonuse') {

				$ay1 = isset($gradient_info['info']['y1']) ? $gradient_info['info']['y1'] : 0;
				$ax1 = isset($gradient_info['info']['x1']) ? $gradient_info['info']['x1'] : 0;

				$angle = rad2deg(atan2(
					($gradient_info['info']['y0'] - $ay1),
					($gradient_info['info']['x0'] - $ax1)
				));

				if ($angle < 0) {
					$angle += 360;
				} elseif ($angle > 360) {
					$angle -= 360;
				}

				if ($angle != 0 && $angle != 360 && $angle != 90 && $angle != 180 && $angle != 270) {
					if ($w >= $h) {
						$y1 *= $h / $w;
						$y0 *= $h / $w;
						$rx *= $h / $w;
						$ry *= $h / $w;
						$usew = $useh = $bboxw;
					} else {
						$x1 *= $w / $h;
						$x0 *= $w / $h;
						$rx *= $w / $h;
						$ry *= $w / $h;
						$usew = $useh = $bboxh;
					}
				}
			}

			$a = $usew;  // width
			$d = -$useh; // height
			$e = $usex;  // x- offset
			$f = -$usey; // -y-offset

			$r = $rx;

			$return .= sprintf('%.3F 0 0 %.3F %.3F %.3F cm ', $a * $this->kp, $d * $this->kp, $e * $this->kp, $f * $this->kp);

			// mPDF 5.0.039
			if (isset($gradient_info['units']) && strtolower($gradient_info['units']) == 'objectboundingbox') {
				if ($transformations) {
					$return .= $transformations;
				}
			}

			// mPDF 5.7.4
			// x1 and y1 (fx, fy) should be inside the circle defined by x0 y0 (cx, cy)
			// "If the point defined by fx and fy lies outside the circle defined by cx, cy and r, then the user agent shall set
			// the focal point to the intersection of the line from (cx, cy) to (fx, fy) with the circle defined by cx, cy and r."
			while (pow(($x1 - $x0), 2) + pow(($y1 - $y0), 2) >= pow($r, 2)) {
				// Gradually move along fx,fy towards cx,cy in 100'ths until meets criteria
				$x1 -= ($x1 - $x0) / 100;
				$y1 -= ($y1 - $y0) / 100;
			}


			if ($spread == 'R' || $spread == 'F') { // Repeat  /  Reflect
				$offs = [];
				for ($i = 0; $i < $ns; $i++) {
					$offs[$i] = $gradient_info['color'][$i]['offset'];
				}
				$gp = 0;
				$inside = true;
				while ($inside) {
					$gp++;
					for ($i = 0; $i < $ns; $i++) {
						if ($spread == 'F' && ($gp % 2) == 1) { // Reflect
							$gradient_info['color'][(($ns * $gp) + $i)] = $gradient_info['color'][(($ns * ($gp - 1)) + ($ns - $i - 1))];
							$tmp = $gp + (1 - $offs[($ns - $i - 1)]);
							$gradient_info['color'][(($ns * $gp) + $i)]['offset'] = $tmp;
						} else { // Reflect
							$gradient_info['color'][(($ns * $gp) + $i)] = $gradient_info['color'][$i];
							$tmp = $gp + $offs[$i];
							$gradient_info['color'][(($ns * $gp) + $i)]['offset'] = $tmp;
						}
						// IF STILL INSIDE BOX OR STILL VALID
						// TEST IF circle (perimeter) intersects with
						// or is enclosed
						// Point on axis to test
						$px = $x1 + ($x0 - $x1) * $tmp;
						$py = $y1 + ($y0 - $y1) * $tmp;
						$pr = $r * $tmp;
						$res = $this->testIntersectCircle($px, $py, $pr);
						if (!$res) {
							$inside = false;
						}
					}
				}
			}

			// Gradient STOPs
			$stops = count($gradient_info['color']);
			if ($stops < 2) {
				return '';
			}

			$range = $gradient_info['color'][count($gradient_info['color']) - 1]['offset'] - $gradient_info['color'][0]['offset'];
			$min = $gradient_info['color'][0]['offset'];

			for ($i = 0; $i < ($stops); $i++) {
				if (!$gradient_info['color'][$i]['color']) {
					if ($gradient_info['colorspace'] == 'RGB') {
						$gradient_info['color'][$i]['color'] = '0 0 0';
					} elseif ($gradient_info['colorspace'] == 'Gray') {
						$gradient_info['color'][$i]['color'] = '0';
					} elseif ($gradient_info['colorspace'] == 'CMYK') {
						$gradient_info['color'][$i]['color'] = '1 1 1 1';
					}
				}
				$offset = ($gradient_info['color'][$i]['offset'] - $min) / $range;
				$this->mpdf->gradients[$n]['stops'][] = [
					'col' => $gradient_info['color'][$i]['color'],
					'opacity' => $gradient_info['color'][$i]['opacity'],
					'offset' => $offset];
				if ($gradient_info['color'][$i]['opacity'] < 1) {
					$trans = true;
				}
			}
			$grx1 = $x1 + ($x0 - $x1) * $gradient_info['color'][0]['offset'];
			$gry1 = $y1 + ($y0 - $y1) * $gradient_info['color'][0]['offset'];
			$grx2 = $x1 + ($x0 - $x1) * $gradient_info['color'][count($gradient_info['color']) - 1]['offset'];
			$gry2 = $y1 + ($y0 - $y1) * $gradient_info['color'][count($gradient_info['color']) - 1]['offset'];
			$grir = $r * $gradient_info['color'][0]['offset'];
			$grr = $r * $gradient_info['color'][count($gradient_info['color']) - 1]['offset'];

			$this->mpdf->gradients[$n]['coords'] = [$grx1, $gry1, $grx2, $gry2, abs($grr), abs($grir)];

			$this->mpdf->gradients[$n]['colorspace'] = $gradient_info['colorspace'];

			$this->mpdf->gradients[$n]['type'] = 3;
			$this->mpdf->gradients[$n]['fo'] = true;

			$this->mpdf->gradients[$n]['extend'] = ['true', 'true'];
			if (isset($trans) && $trans) {
				$this->mpdf->gradients[$n]['trans'] = true;
				$return .= ' /TGS' . ($n) . ' gs ';
			}
			$return .= ' /Sh' . ($n) . ' sh ';
			$return .= " Q\n";
		}

		return $return;
	}

	function svgOffset($attribs)
	{
		// save all <svg> tag attributes
		$this->svg_attribs = $attribs;
		if (isset($this->svg_attribs['viewBox'])) {
			$vb = preg_split('/\s+/is', trim($this->svg_attribs['viewBox']));
			if (count($vb) == 4) {
				$this->svg_info['x'] = $vb[0];
				$this->svg_info['y'] = $vb[1];
				$this->svg_info['w'] = $vb[2];
				$this->svg_info['h'] = $vb[3];
//				return;
			}
		}
		$svg_w = 0;
		$svg_h = 0;
		if (isset($attribs['width']) && $attribs['width']) {
			$svg_w = $this->sizeConverter->convert($attribs['width']); // mm (interprets numbers as pixels)
		}
		if (isset($attribs['height']) && $attribs['height']) {
			$svg_h = $this->sizeConverter->convert($attribs['height']); // mm
		}


///*
		// mPDF 5.0.005
		if (isset($this->svg_info['w']) && $this->svg_info['w']) { // if 'w' set by viewBox
			if ($svg_w) { // if width also set, use these values to determine to set size of "pixel"
				$this->kp *= ($svg_w / 0.2645) / $this->svg_info['w'];
				$this->kf = ($svg_w / 0.2645) / $this->svg_info['w'];
			} elseif ($svg_h) {
				$this->kp *= ($svg_h / 0.2645) / $this->svg_info['h'];
				$this->kf = ($svg_h / 0.2645) / $this->svg_info['h'];
			}
			return;
		}
//*/
		// Added to handle file without height or width specified
		if (!$svg_w && !$svg_h) {
			$svg_w = $svg_h = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];
		} // DEFAULT
		if (!$svg_w) {
			$svg_w = $svg_h;
		}
		if (!$svg_h) {
			$svg_h = $svg_w;
		}

		$this->svg_info['x'] = 0;
		$this->svg_info['y'] = 0;
		$this->svg_info['w'] = $svg_w / 0.2645; // mm->pixels
		$this->svg_info['h'] = $svg_h / 0.2645; // mm->pixels
	}

	//
	// check if points are within svg, if not, set to max
	function svg_overflow($x, $y)
	{
		$x2 = $x;
		$y2 = $y;
		if (isset($this->svg_attribs['overflow'])) {
			if ($this->svg_attribs['overflow'] == 'hidden') {
				// Not sure if this is supposed to strip off units, but since I dont use any I will omlt this step
				$svg_w = preg_replace("/([0-9\.]*)(.*)/i", "$1", $this->svg_attribs['width']);
				$svg_h = preg_replace("/([0-9\.]*)(.*)/i", "$1", $this->svg_attribs['height']);

				// $xmax = floor($this->svg_attribs['width']);
				$xmax = floor($svg_w);
				$xmin = 0;
				// $ymax = floor(($this->svg_attribs['height'] * -1));
				$ymax = floor(($svg_h * -1));
				$ymin = 0;

				if ($x > $xmax) {
					$x2 = $xmax; // right edge
				}
				if ($x < $xmin) {
					$x2 = $xmin; // left edge
				}
				if ($y < $ymax) {
					$y2 = $ymax; // bottom
				}
				if ($y > $ymin) {
					$y2 = $ymin; // top
				}
			}
		}


		return ['x' => $x2, 'y' => $y2];
	}

	function svgDefineStyle($critere_style)
	{

		$tmp = count($this->svg_style) - 1;
		$current_style = $this->svg_style[$tmp];

		unset($current_style['transformations']);

		// TRANSFORM SCALE
		$transformations = '';
		if (isset($critere_style['transform'])) {
			preg_match_all('/(matrix|translate|scale|rotate|skewX|skewY)\((.*?)\)/is', $critere_style['transform'], $m);
			if (count($m[0])) {
				for ($i = 0; $i < count($m[0]); $i++) {
					$c = strtolower($m[1][$i]);
					$v = trim($m[2][$i]);
					$vv = preg_split('/[ ,]+/', $v);
					if ($c == 'matrix' && count($vv) == 6) {
						// mPDF 5.0.039
						// Note angle of rotation is reversed (from SVG to PDF), so vv[1] and vv[2] are negated
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F %.3F %.3F cm ', $vv[0], -$vv[1], -$vv[2], $vv[3], $vv[4] * $this->kp, -$vv[5] * $this->kp);

						/*
						  // The long way of doing this??
						  // need to reverse angle of rotation from SVG to PDF
						  $sx=sqrt(pow($vv[0],2)+pow($vv[2],2));
						  if ($vv[0] < 0) { $sx *= -1; } // change sign
						  $sy=sqrt(pow($vv[1],2)+pow($vv[3],2));
						  if ($vv[3] < 0) { $sy *= -1; } // change sign

						  // rotation angle is
						  $t=atan2($vv[1],$vv[3]);
						  $t=atan2(-$vv[2],$vv[0]);	// Should be the same value or skew has been applied

						  // Reverse angle
						  $t *= -1;

						  // Rebuild matrix
						  $ma = $sx * cos($t);
						  $mb = $sy * sin($t);
						  $mc = -$sx * sin($t);
						  $md = $sy * cos($t);

						  // $transformations .= sprintf(' %.3F %.3F %.3F %.3F %.3F %.3F cm ', $ma, $mb, $mc, $md, $vv[4]*$this->kp, -$vv[5]*$this->kp);
						 */
					} elseif ($c == 'translate' && count($vv)) {
						$tm[4] = $vv[0];
						if (count($vv) == 2) {
							$t_y = -$vv[1];
						} else {
							$t_y = 0;
						}
						$tm[5] = $t_y;
						$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $tm[4] * $this->kp, $tm[5] * $this->kp);
					} elseif ($c == 'scale' && count($vv)) {
						if (count($vv) == 2) {
							$s_y = $vv[1];
						} else {
							$s_y = $vv[0];
						}
						$tm[0] = $vv[0];
						$tm[3] = $s_y;
						$transformations .= sprintf(' %.3F 0 0 %.3F 0 0 cm ', $tm[0], $tm[3]);
					} elseif ($c == 'rotate' && count($vv)) {
						$tm[0] = cos(deg2rad(-$vv[0]));
						$tm[1] = sin(deg2rad(-$vv[0]));
						$tm[2] = -$tm[1];
						$tm[3] = $tm[0];
						if (count($vv) == 3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', $vv[1] * $this->kp, -$vv[2] * $this->kp);
						}
						$transformations .= sprintf(' %.3F %.3F %.3F %.3F 0 0 cm ', $tm[0], $tm[1], $tm[2], $tm[3]);
						if (count($vv) == 3) {
							$transformations .= sprintf(' 1 0 0 1 %.3F %.3F cm ', -$vv[1] * $this->kp, $vv[2] * $this->kp);
						}
					} elseif ($c == 'skewx' && count($vv)) {
						$tm[2] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 0 %.3F 1 0 0 cm ', $tm[2]);
					} elseif ($c == 'skewy' && count($vv)) {
						$tm[1] = tan(deg2rad(-$vv[0]));
						$transformations .= sprintf(' 1 %.3F 0 1 0 0 cm ', $tm[1]);
					}
				}
			}
			$current_style['transformations'] = $transformations;
		}

		if (isset($critere_style['style'])) {
			if (preg_match('/fill:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/i', $critere_style['style'], $m)) { // mPDF 5.7.2
				$current_style['fill'] = '#' . str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			} else {
				$tmp = preg_replace("/(.*)fill:\s*([a-z0-9#_()]*|none)(.*)/i", "$2", $critere_style['style']);
				if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
					$current_style['fill'] = $tmp;
				}
			}

			// mPDF 5.7.2
			if ((preg_match("/[^-]opacity:\s*([a-z0-9.]*|none)/i", $critere_style['style'], $m) ||
				preg_match("/^opacity:\s*([a-z0-9.]*|none)/i", $critere_style['style'], $m)) && $m[1] != 'inherit') {
				$current_style['fill-opacity'] = $m[1];
				$current_style['stroke-opacity'] = $m[1];
			}

			$tmp = preg_replace("/(.*)fill-opacity:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['fill-opacity'] = $tmp;
			}

			$tmp = preg_replace("/(.*)fill-rule:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['fill-rule'] = $tmp;
			}

			if (preg_match('/stroke:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $critere_style['style'], $m)) {
				$current_style['stroke'] = '#' . str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			} else {
				$tmp = preg_replace("/(.*)stroke:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
				if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
					$current_style['stroke'] = $tmp;
				}
			}

			$tmp = preg_replace("/(.*)stroke-linecap:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-linecap'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-linejoin:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-linejoin'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-miterlimit:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-miterlimit'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-opacity:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-opacity'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-width:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-width'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-dasharray:\s*([a-z0-9., ]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-dasharray'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-dashoffset:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-dashoffset'] = $tmp;
			}
		}

		// mPDF 5.7.2
		if (isset($critere_style['opacity']) && $critere_style['opacity'] != 'inherit') {
			$current_style['fill-opacity'] = $critere_style['opacity'];
			$current_style['stroke-opacity'] = $critere_style['opacity'];
		}

		if (isset($critere_style['fill']) && $critere_style['fill'] != 'inherit') {
			$current_style['fill'] = $critere_style['fill'];
		}

		if (isset($critere_style['fill-opacity']) && $critere_style['fill-opacity'] != 'inherit') {
			$current_style['fill-opacity'] = $critere_style['fill-opacity'];
		}

		if (isset($critere_style['fill-rule']) && $critere_style['fill-rule'] != 'inherit') {
			$current_style['fill-rule'] = $critere_style['fill-rule'];
		}

		if (isset($critere_style['stroke']) && $critere_style['stroke'] != 'inherit') {
			$current_style['stroke'] = $critere_style['stroke'];
		}

		if (isset($critere_style['stroke-linecap']) && $critere_style['stroke-linecap'] != 'inherit') {
			$current_style['stroke-linecap'] = $critere_style['stroke-linecap'];
		}

		if (isset($critere_style['stroke-linejoin']) && $critere_style['stroke-linejoin'] != 'inherit') {
			$current_style['stroke-linejoin'] = $critere_style['stroke-linejoin'];
		}

		if (isset($critere_style['stroke-miterlimit']) && $critere_style['stroke-miterlimit'] != 'inherit') {
			$current_style['stroke-miterlimit'] = $critere_style['stroke-miterlimit'];
		}

		if (isset($critere_style['stroke-opacity']) && $critere_style['stroke-opacity'] != 'inherit') {
			$current_style['stroke-opacity'] = $critere_style['stroke-opacity'];
		}

		if (isset($critere_style['stroke-width']) && $critere_style['stroke-width'] != 'inherit') {
			$current_style['stroke-width'] = $critere_style['stroke-width'];
		}

		if (isset($critere_style['stroke-dasharray']) && $critere_style['stroke-dasharray'] != 'inherit') {
			$current_style['stroke-dasharray'] = $critere_style['stroke-dasharray'];
		}
		if (isset($critere_style['stroke-dashoffset']) && $critere_style['stroke-dashoffset'] != 'inherit') {
			$current_style['stroke-dashoffset'] = $critere_style['stroke-dashoffset'];
		}

		// Used as indirect setting for currentColor
		if (isset($critere_style['color']) && $critere_style['color'] != 'inherit') {
			$current_style['color'] = $critere_style['color'];
		}

		return $current_style;
	}

	//
	//	Cette fonction ecrit le style dans le stream svg.
	function svgStyle($critere_style, $attribs, $element)
	{
		$path_style = '';
		$fill_gradient = '';
		$w = '';
		$style = '';
		if (substr_count($critere_style['fill'], 'url') > 0 && $element != 'line') {
			//
			// couleur degradé
			$id_gradient = preg_replace("/url\(#([\w_]*)\)/i", "$1", $critere_style['fill']);
			if ($id_gradient != $critere_style['fill']) {
				if (isset($this->svg_gradient[$id_gradient])) {
					$fill_gradient = $this->svgGradient($this->svg_gradient[$id_gradient], $attribs, $element);
					if ($fill_gradient) {
						$path_style = "q ";
						$w = "W";
						$style .= 'N';
					}
				}
			}
		} // Used as indirect setting for currentColor
		elseif (strtolower($critere_style['fill']) == 'currentcolor' && $element != 'line') {
			$col = $this->colorConverter->convert($critere_style['color'], $this->mpdf->PDFAXwarnings);
			if ($col) {
				if ($col[0] == 5 && is_numeric($col[4])) {
					$critere_style['fill-opacity'] = ord($col[4] / 100);
				} // RGBa
				if ($col[0] == 6 && is_numeric($col[5])) {
					$critere_style['fill-opacity'] = ord($col[5] / 100);
				} // CMYKa
				$path_style .= $this->mpdf->SetFColor($col, true) . ' ';
				$style .= 'F';
			}
		} elseif ($critere_style['fill'] != 'none' && $element != 'line') {
			$col = $this->colorConverter->convert($critere_style['fill'], $this->mpdf->PDFAXwarnings);
			if ($col) {
				if ($col[0] == 5 && is_numeric($col[4])) {
					$critere_style['fill-opacity'] = ord($col[4] / 100);
				} // RGBa
				if ($col[0] == 6 && is_numeric($col[5])) {
					$critere_style['fill-opacity'] = ord($col[5] / 100);
				} // CMYKa
				$path_style .= $this->mpdf->SetFColor($col, true) . ' ';
				$style .= 'F';
			}
		}
		if (substr_count($critere_style['stroke'], 'url') > 0) {
			/*
			  // Cannot put a gradient on a "stroke" in PDF?
			  $id_gradient = preg_replace("/url\(#([\w_]*)\)/i","$1",$critere_style['stroke']);
			  if ($id_gradient != $critere_style['stroke']) {
			  if (isset($this->svg_gradient[$id_gradient])) {
			  $fill_gradient = $this->svgGradient($this->svg_gradient[$id_gradient], $attribs, $element);
			  if ($fill_gradient) {
			  $path_style = "q ";
			  $w = "W";
			  $style .= 'D';
			  }
			  }
			  }
			 */
		} // Used as indirect setting for currentColor
		elseif (strtolower($critere_style['stroke']) == 'currentcolor') {
			$col = $this->colorConverter->convert($critere_style['color'], $this->mpdf->PDFAXwarnings);
			if ($col) {
				if ($col[0] == 5 && is_numeric($col[4])) {
					$critere_style['stroke-opacity'] = ord($col[4] / 100);
				} // RGBa
				if ($col[0] == 6 && is_numeric($col[5])) {
					$critere_style['stroke-opacity'] = ord($col[5] / 100);
				} // CMYKa
				$path_style .= $this->mpdf->SetDColor($col, true) . ' ';
				$style .= 'D';
				$lw = $this->ConvertSVGSizePixels($critere_style['stroke-width']);
				$path_style .= sprintf('%.3F w ', $lw * $this->kp);
			}
		} elseif ($critere_style['stroke'] != 'none') {
			$col = $this->colorConverter->convert($critere_style['stroke'], $this->mpdf->PDFAXwarnings);
			if ($col) {
				// mPDF 5.0.051
				// mPDF 5.3.74
				if ($col[0] == 5 && is_numeric($col[4])) {
					$critere_style['stroke-opacity'] = ord($col[4] / 100);
				} // RGBa
				if ($col[0] == 6 && is_numeric($col[5])) {
					$critere_style['stroke-opacity'] = ord($col[5] / 100);
				} // CMYKa
				$path_style .= $this->mpdf->SetDColor($col, true) . ' ';
				$style .= 'D';
				$lw = $this->ConvertSVGSizePixels($critere_style['stroke-width']);
				$path_style .= sprintf('%.3F w ', $lw * $this->kp);
			}
		}


		if ($critere_style['stroke'] != 'none') {
			if ($critere_style['stroke-linejoin'] == 'miter') {
				$path_style .= ' 0 j ';
			} elseif ($critere_style['stroke-linejoin'] == 'round') {
				$path_style .= ' 1 j ';
			} elseif ($critere_style['stroke-linejoin'] == 'bevel') {
				$path_style .= ' 2 j ';
			}

			if ($critere_style['stroke-linecap'] == 'butt') {
				$path_style .= ' 0 J ';
			} elseif ($critere_style['stroke-linecap'] == 'round') {
				$path_style .= ' 1 J ';
			} elseif ($critere_style['stroke-linecap'] == 'square') {
				$path_style .= ' 2 J ';
			}

			if (isset($critere_style['stroke-miterlimit'])) {
				if ($critere_style['stroke-miterlimit'] == 'none') {
				} elseif (preg_match('/^[\d.]+$/', $critere_style['stroke-miterlimit'])) {
					$path_style .= sprintf('%.2F M ', $critere_style['stroke-miterlimit']);
				}
			}
			if (isset($critere_style['stroke-dasharray'])) {
				$off = 0;
				$d = preg_split('/(,\s?|\s)/', $critere_style['stroke-dasharray']);
				if (count($d) == 1 && $d[0] == 0) {
					$path_style .= '[] 0 d ';
				} else {
					if (count($d) % 2 == 1) {
						$d = array_merge($d, $d);
					} // 5, 3, 1 => 5,3,1,5,3,1  OR 3 => 3,3
					$arr = '';
					for ($i = 0; $i < count($d); $i+=2) {
						$arr .= sprintf('%.3F %.3F ', $d[$i] * $this->kp, $d[$i + 1] * $this->kp);
					}
					if (isset($critere_style['stroke-dashoffset'])) {
						$off = $critere_style['stroke-dashoffset'] + 0;
					}
					$path_style .= sprintf('[%s] %.3F d ', $arr, $off * $this->kp);
				}
			}
		}

		if ($critere_style['fill-rule'] == 'evenodd') {
			$fr = '*';
		} else {
			$fr = '';
		}

		if (isset($critere_style['fill-opacity'])) {
			$opacity = 1;
			if ($critere_style['fill-opacity'] == 0) {
				$opacity = 0;
			} elseif ($critere_style['fill-opacity'] > 1) {
				$opacity = 1;
			} elseif ($critere_style['fill-opacity'] > 0) {
				$opacity = $critere_style['fill-opacity'];
			} elseif ($critere_style['fill-opacity'] < 0) {
				$opacity = 0;
			}
			$gs = $this->mpdf->AddExtGState(['ca' => $opacity, 'BM' => '/Normal']);
			$this->mpdf->extgstates[$gs]['fo'] = true;
			$path_style .= sprintf(' /GS%d gs ', $gs);
		}

		if (isset($critere_style['stroke-opacity'])) {
			$opacity = 1;
			if ($critere_style['stroke-opacity'] == 0) {
				$opacity = 0;
			} elseif ($critere_style['stroke-opacity'] > 1) {
				$opacity = 1;
			} elseif ($critere_style['stroke-opacity'] > 0) {
				$opacity = $critere_style['stroke-opacity'];
			} elseif ($critere_style['stroke-opacity'] < 0) {
				$opacity = 0;
			}
			$gs = $this->mpdf->AddExtGState(['CA' => $opacity, 'BM' => '/Normal']);
			$this->mpdf->extgstates[$gs]['fo'] = true;
			$path_style .= sprintf(' /GS%d gs ', $gs);
		}

		switch ($style) {
			case 'F':
				$op = 'f';
				break;
			case 'FD':
				$op = 'B';
				break;
			case 'ND':
				$op = 'S';
				break;
			case 'D':
				$op = 'S';
				break;
			default:
				$op = 'n';
		}

		$prestyle = $path_style . ' ';
		$poststyle = $w . ' ' . $op . $fr . ' ' . $fill_gradient . "\n";
		return [$prestyle, $poststyle];
	}

	//	fonction retracant les <path />
	function svgPath($command, $arguments)
	{
		$path_cmd = '';
		$newsubpath = false;
		// mPDF 5.0.039
		$minl = $this->pathBBox[0];
		$mint = $this->pathBBox[1];
		$maxr = $this->pathBBox[2] + $this->pathBBox[0];
		$maxb = $this->pathBBox[3] + $this->pathBBox[1];

		$start = [$this->xbase, -$this->ybase];

		// taken from https://github.com/PhenX/php-svg-lib/blob/master/src/Svg/Tag/Path.php#L47
		// Handle args like: a5.38022,5.38022,0,0,1-2.4207.72246,4.50524,4.50524,0,0,1-3.12681-1.33942,9.67442,9.67442,0,0,1-2.38273-3.016,1.87506,1.87506,0,0,1,.34979-2.43562
		preg_match_all('/([-+]?((\d+\.\d+)|((\d+)|(\.\d+)))(?:e[-+]?\d+)?)/i', $arguments, $a, PREG_PATTERN_ORDER);
		$a = $a[0];

		//	if the command is a capital letter, the coords go absolute, otherwise relative
		if (strtolower($command) == $command) {
			$relative = true;
		} else {
			$relative = false;
		}


		$argumentCount = count($a);

		//	each command may have different needs for arguments [1 to 8]

		switch (strtolower($command)) {

			case 'm': // move

				for ($i = 0; $i < $argumentCount; $i += 2) {

					$x = $a[$i];
					$y = $a[$i + 1];

					if ($relative) {

						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;

					} else {

						$pdfx = $x;
						$pdfy = -$y;
						$this->xbase = $x;
						$this->ybase = -$y;
					}

					$pdf_pt = $this->svg_overflow($pdfx, $pdfy);

					$minl = min($minl, $pdf_pt['x']);
					$maxr = max($maxr, $pdf_pt['x']);
					$mint = min($mint, -$pdf_pt['y']);
					$maxb = max($maxb, -$pdf_pt['y']);

					if ($i == 0) {
						$path_cmd .= sprintf('%.3F %.3F m ', $pdf_pt['x'] * $this->kp, $pdf_pt['y'] * $this->kp);
					} else {
						$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x'] * $this->kp, $pdf_pt['y'] * $this->kp);
					}

					// mPDF 4.4.003  Save start points of subpath
					if ($this->subPathInit) {
						$this->spxstart = $this->xbase;
						$this->spystart = $this->ybase;
						$this->subPathInit = false;
					}
				}

				break;

			case 'l': // a simple line

				for ($i = 0; $i < $argumentCount; $i+=2) {

					$x = ($a[$i]);
					$y = ($a[$i + 1]);

					if ($relative) {

						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;

					} else {

						$pdfx = $x;
						$pdfy = -$y;
						$this->xbase = $x;
						$this->ybase = -$y;

					}

					$pdf_pt = $this->svg_overflow($pdfx, $pdfy);

					$minl = min($minl, $pdf_pt['x']);
					$maxr = max($maxr, $pdf_pt['x']);
					$mint = min($mint, -$pdf_pt['y']);
					$maxb = max($maxb, -$pdf_pt['y']);

					$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x'] * $this->kp, $pdf_pt['y'] * $this->kp);
				}

				break;

			case 'h': // a very simple horizontal line

				for ($i = 0; $i < $argumentCount; $i++) {

					$x = ($a[$i]);

					if ($relative) {

						$y = 0;
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;

					} else {

						$y = -$this->ybase;
						$pdfx = $x;
						$pdfy = -$y;
						$this->xbase = $x;
						$this->ybase = -$y;

					}

					$pdf_pt = $this->svg_overflow($pdfx, $pdfy);
					$minl = min($minl, $pdf_pt['x']);
					$maxr = max($maxr, $pdf_pt['x']);
					$mint = min($mint, -$pdf_pt['y']);
					$maxb = max($maxb, -$pdf_pt['y']);
					$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x'] * $this->kp, $pdf_pt['y'] * $this->kp);
				}

				break;

			case 'v': // the simplest line, vertical

				for ($i = 0; $i < $argumentCount; $i++) {

					$y = ($a[$i]);

					if ($relative) {

						$x = 0;
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;

					} else {

						$x = $this->xbase;
						$pdfx = $x;
						$pdfy = -$y;
						$this->xbase = $x;
						$this->ybase = -$y;

					}

					$pdf_pt = $this->svg_overflow($pdfx, $pdfy);
					$minl = min($minl, $pdf_pt['x']);
					$maxr = max($maxr, $pdf_pt['x']);
					$mint = min($mint, -$pdf_pt['y']);
					$maxb = max($maxb, -$pdf_pt['y']);
					$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x'] * $this->kp, $pdf_pt['y'] * $this->kp);

				}

				break;

			case 's': // bezier with first vertex equal first control

				// mPDF 4.4.003

				if (!($this->lastcommand == 'C' || $this->lastcommand == 'c' || $this->lastcommand == 'S' || $this->lastcommand == 's')) {
					$this->lastcontrolpoints = [0, 0];
				}

				for ($i = 0; $i < $argumentCount; $i += 4) {

					$x1 = $this->lastcontrolpoints[0];
					$y1 = $this->lastcontrolpoints[1];

					$x2 = ($a[$i]);
					$y2 = ($a[$i + 1]);

					$x = ($a[$i + 2]);
					$y = ($a[$i + 3]);

					if ($relative) {

						$pdfx1 = ($this->xbase + $x1);
						$pdfy1 = ($this->ybase - $y1);
						$pdfx2 = ($this->xbase + $x2);
						$pdfy2 = ($this->ybase - $y2);
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;

					} else {

						$pdfx1 = $this->xbase + $x1;
						$pdfy1 = $this->ybase - $y1;
						$pdfx2 = $x2;
						$pdfy2 = -$y2;
						$pdfx = $x;
						$pdfy = -$y;
						$this->xbase = $x;
						$this->ybase = -$y;

					}

					$this->lastcontrolpoints = [($pdfx - $pdfx2), -($pdfy - $pdfy2)]; // mPDF 4.4.003 always relative

					$pdf_pt = $this->svg_overflow($pdfx, $pdfy);

					$curves = [$pdfx1, -$pdfy1, $pdfx2, -$pdfy2, $pdfx, -$pdfy];
					$bx = $this->computeBezierBoundingBox($start, $curves);
					$minl = min($minl, $bx[0]);
					$maxr = max($maxr, $bx[2]);
					$mint = min($mint, $bx[1]);
					$maxb = max($maxb, $bx[3]);

					if (($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy)) {
						$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x'] * $this->kp, $pdf_pt['y'] * $this->kp);
					} else {
						$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $pdfx1 * $this->kp, $pdfy1 * $this->kp, $pdfx2 * $this->kp, $pdfy2 * $this->kp, $pdfx * $this->kp, $pdfy * $this->kp);
					}
				}

				break;

			case 'c': // bezier with second vertex equal second control

				for ($i = 0; $i < $argumentCount; $i += 6) {

					$x1 = ($a[$i]);
					$y1 = ($a[$i + 1]);
					$x2 = ($a[$i + 2]);
					$y2 = ($a[$i + 3]);
					$x = ($a[$i + 4]);
					$y = ($a[$i + 5]);

					if ($relative) {

						$pdfx1 = ($this->xbase + $x1);
						$pdfy1 = ($this->ybase - $y1);
						$pdfx2 = ($this->xbase + $x2);
						$pdfy2 = ($this->ybase - $y2);
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);
						$this->xbase += $x;
						$this->ybase += -$y;

					} else {

						$pdfx1 = $x1;
						$pdfy1 = -$y1;
						$pdfx2 = $x2;
						$pdfy2 = -$y2;
						$pdfx = $x;
						$pdfy = -$y;
						$this->xbase = $x;
						$this->ybase = -$y;

					}

					$this->lastcontrolpoints = [($pdfx - $pdfx2), -($pdfy - $pdfy2)]; // mPDF 4.4.003 always relative
					// $pdf_pt2 = $this->svg_overflow($pdfx2,$pdfy2);
					// $pdf_pt1 = $this->svg_overflow($pdfx1,$pdfy1);
					$pdf_pt = $this->svg_overflow($pdfx, $pdfy);

					$curves = [$pdfx1, -$pdfy1, $pdfx2, -$pdfy2, $pdfx, -$pdfy];
					$bx = $this->computeBezierBoundingBox($start, $curves);
					$minl = min($minl, $bx[0]);
					$maxr = max($maxr, $bx[2]);
					$mint = min($mint, $bx[1]);
					$maxb = max($maxb, $bx[3]);

					if (($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy)) {
						$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x'] * $this->kp, $pdf_pt['y'] * $this->kp);
					} else {
						$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $pdfx1 * $this->kp, $pdfy1 * $this->kp, $pdfx2 * $this->kp, $pdfy2 * $this->kp, $pdfx * $this->kp, $pdfy * $this->kp);
					}
				}

				break;

			case 'q': // bezier quadratic avec point de control

				for ($i = 0; $i < $argumentCount; $i += 4) {

					$x1 = ($a[$i]);
					$y1 = ($a[$i + 1]);
					$x = ($a[$i + 2]);
					$y = ($a[$i + 3]);

					if ($relative) {

						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);

						$pdfx1 = ($this->xbase + ($x1 * 2 / 3));
						$pdfy1 = ($this->ybase - ($y1 * 2 / 3));
						// mPDF 4.4.003
						$pdfx2 = $pdfx1 + 1 / 3 * ($x);
						$pdfy2 = $pdfy1 + 1 / 3 * (-$y);

						$this->xbase += $x;
						$this->ybase += -$y;

					} else {

						$pdfx = $x;
						$pdfy = -$y;

						$pdfx1 = ($this->xbase + (($x1 - $this->xbase) * 2 / 3));
						$pdfy1 = ($this->ybase - (($y1 + $this->ybase) * 2 / 3));

						$pdfx2 = ($x + (($x1 - $x) * 2 / 3));
						$pdfy2 = (-$y - (($y1 - $y) * 2 / 3));

						// mPDF 4.4.003
						$pdfx2 = $pdfx1 + 1 / 3 * ($x - $this->xbase);
						$pdfy2 = $pdfy1 + 1 / 3 * (-$y - $this->ybase);

						$this->xbase = $x;
						$this->ybase = -$y;

					}

					$this->lastcontrolpoints = [($pdfx - $pdfx2), -($pdfy - $pdfy2)]; // mPDF 4.4.003 always relative

					$pdf_pt = $this->svg_overflow($pdfx, $pdfy);

					$curves = [$pdfx1, -$pdfy1, $pdfx2, -$pdfy2, $pdfx, -$pdfy];
					$bx = $this->computeBezierBoundingBox($start, $curves);
					$minl = min($minl, $bx[0]);
					$maxr = max($maxr, $bx[2]);
					$mint = min($mint, $bx[1]);
					$maxb = max($maxb, $bx[3]);

					if (($pdf_pt['x'] != $pdfx) || ($pdf_pt['y'] != $pdfy)) {
						$path_cmd .= sprintf('%.3F %.3F l ', $pdf_pt['x'] * $this->kp, $pdf_pt['y'] * $this->kp);
					} else {
						$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $pdfx1 * $this->kp, $pdfy1 * $this->kp, $pdfx2 * $this->kp, $pdfy2 * $this->kp, $pdfx * $this->kp, $pdfy * $this->kp);
					}
				}

				break;

			case 't': // bezier quadratic avec point de control simetrique a lancien point de control

				if (!($this->lastcommand == 'Q' || $this->lastcommand == 'q' || $this->lastcommand == 'T' || $this->lastcommand == 't')) {
					$this->lastcontrolpoints = [0, 0];
				}

				for ($i = 0; $i < $argumentCount; $i += 2) {

					$x = ($a[$i]);
					$y = ($a[$i + 1]);

					$x1 = $this->lastcontrolpoints[0];
					$y1 = $this->lastcontrolpoints[1];

					if ($relative) {
						$pdfx = ($this->xbase + $x);
						$pdfy = ($this->ybase - $y);

						$pdfx1 = ($this->xbase + ($x1));
						$pdfy1 = ($this->ybase - ($y1));
						// mPDF 4.4.003
						$pdfx2 = $pdfx1 + 1 / 3 * ($x);
						$pdfy2 = $pdfy1 + 1 / 3 * (-$y);

						$this->xbase += $x;
						$this->ybase += -$y;

					} else {

						$pdfx = $x;
						$pdfy = -$y;

						$pdfx1 = ($this->xbase + ($x1));
						$pdfy1 = ($this->ybase - ($y1));

						// mPDF 4.4.003
						$pdfx2 = $pdfx1 + 1 / 3 * ($x - $this->xbase);
						$pdfy2 = $pdfy1 + 1 / 3 * (-$y - $this->ybase);

						$this->xbase = $x;
						$this->ybase = -$y;
					}

					$this->lastcontrolpoints = [($pdfx - $pdfx2), -($pdfy - $pdfy2)]; // mPDF 4.4.003 always relative

					$curves = [$pdfx1, -$pdfy1, $pdfx2, -$pdfy2, $pdfx, -$pdfy];
					$bx = $this->computeBezierBoundingBox($start, $curves);
					$minl = min($minl, $bx[0]);
					$maxr = max($maxr, $bx[2]);
					$mint = min($mint, $bx[1]);
					$maxb = max($maxb, $bx[3]);

					$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $pdfx1 * $this->kp, $pdfy1 * $this->kp, $pdfx2 * $this->kp, $pdfy2 * $this->kp, $pdfx * $this->kp, $pdfy * $this->kp);
				}

				break;

			case 'a': // Elliptical arc

				for ($i = 0; $i < $argumentCount; $i += 7) {

					$rx = isset($a[$i]) ? $a[$i] : 0;
					$ry = isset($a[$i + 1]) ? $a[$i + 1] : 0;

					// x-axis-rotation
					$angle = isset($a[$i + 2]) ? $a[$i + 2] : 0;

					$largeArcFlag = isset($a[$i + 3]) ? $a[$i + 3] : 0;
					$sweepFlag = isset($a[$i + 4]) ? $a[$i + 4] : 0;

					$x2 = isset($a[$i + 5]) ? $a[$i + 5] : 0;
					$y2 = isset($a[$i + 6]) ? $a[$i + 6] : 0;

					$x1 = $this->xbase;
					$y1 = -$this->ybase;

					if ($relative) {
						$x2 = $this->xbase + $x2;
						$y2 = -$this->ybase + $y2;
						$this->xbase += isset($a[$i + 5]) ? $a[$i + 5] : 0;
						$this->ybase += isset($a[$i + 6]) ? -$a[$i + 6] : 0;
					} else {
						$this->xbase = $x2;
						$this->ybase = -$y2;
					}

					list($pcmd, $bounds) = $this->Arcto($x1, $y1, $x2, $y2, $rx, $ry, $angle, $largeArcFlag, $sweepFlag);

					$minl = min($minl, $x2, min($bounds[0]));
					$maxr = max($maxr, $x2, max($bounds[0]));
					$mint = min($mint, $y2, min($bounds[1]));
					$maxb = max($maxb, $y2, max($bounds[1]));

					$path_cmd .= $pcmd;

				}

				break;

			case 'z':

				$path_cmd .= 'h ';

				$this->subPathInit = true;
				$newsubpath = true;
				$this->xbase = $this->spxstart;
				$this->ybase = $this->spystart;

				break;
		}

		if (!$newsubpath) {
			$this->subPathInit = false;
		}

		$this->lastcommand = $command;

		// mPDF 5.0.039
		$this->pathBBox[0] = $minl;
		$this->pathBBox[1] = $mint;
		$this->pathBBox[2] = $maxr - $this->pathBBox[0];
		$this->pathBBox[3] = $maxb - $this->pathBBox[1];

		return $path_cmd;
	}

	function Arcto($x1, $y1, $x2, $y2, $rx, $ry, $angle, $largeArcFlag, $sweepFlag)
	{

		$bounds = [0 => [$x1, $x2], 1 => [$y1, $y2]];

		// 1. Treat out-of-range parameters as described in
		// http://www.w3.org/TR/SVG/implnote.html#ArcImplementationNotes
		// If the endpoints (x1, y1) and (x2, y2) are identical, then this
		// is equivalent to omitting the elliptical arc segment entirely
		if ($x1 == $x2 && $y1 == $y2) {
			return ['', $bounds]; // mPD 5.0.040
		}

		// If rX = 0 or rY = 0 then this arc is treated as a straight line
		// segment (a "lineto") joining the endpoints.
		if ($rx == 0.0 || $ry == 0.0) {
			//   return array(Lineto(x2, y2), $bounds);
		}

		// If rX or rY have negative signs, these are dropped; the absolute
		// value is used instead.
		if ($rx < 0.0) {
			$rx = -$rx;
		}

		if ($ry < 0.0) {
			$ry = -$ry;
		}

		// 2. convert to center parameterization as shown in
		// http://www.w3.org/TR/SVG/implnote.html
		$sinPhi = sin(deg2rad($angle));
		$cosPhi = cos(deg2rad($angle));

		$x1dash = $cosPhi * ($x1 - $x2) / 2.0 + $sinPhi * ($y1 - $y2) / 2.0;
		$y1dash = -$sinPhi * ($x1 - $x2) / 2.0 + $cosPhi * ($y1 - $y2) / 2.0;

		$numerator = $rx * $rx * $ry * $ry - $rx * $rx * $y1dash * $y1dash - $ry * $ry * $x1dash * $x1dash;

		if ($numerator < 0.0) {

			//  If rX , rY and are such that there is no solution (basically,
			//  the ellipse is not big enough to reach from (x1, y1) to (x2,
			//  y2)) then the ellipse is scaled up uniformly until there is
			//  exactly one solution (until the ellipse is just big enough).
			// -> find factor s, such that numerator' with rx'=s*rx and
			//    ry'=s*ry becomes 0 :
			$s = sqrt(1.0 - $numerator / ($rx * $rx * $ry * $ry));

			$rx *= $s;
			$ry *= $s;
			$root = 0.0;

		} else {
			$root = ($largeArcFlag == $sweepFlag ? -1.0 : 1.0) * sqrt($numerator / ($rx * $rx * $y1dash * $y1dash + $ry * $ry * $x1dash * $x1dash));
		}

		$cxdash = $root * $rx * $y1dash / $ry;
		$cydash = -$root * $ry * $x1dash / $rx;

		$cx = $cosPhi * $cxdash - $sinPhi * $cydash + ($x1 + $x2) / 2.0;
		$cy = $sinPhi * $cxdash + $cosPhi * $cydash + ($y1 + $y2) / 2.0;


		$theta1 = $this->CalcVectorAngle(1.0, 0.0, ($x1dash - $cxdash) / $rx, ($y1dash - $cydash) / $ry);
		$dtheta = $this->CalcVectorAngle(($x1dash - $cxdash) / $rx, ($y1dash - $cydash) / $ry, (-$x1dash - $cxdash) / $rx, (-$y1dash - $cydash) / $ry);

		if (!$sweepFlag && $dtheta > 0) {
			$dtheta -= 2.0 * M_PI;
		} elseif ($sweepFlag && $dtheta < 0) {
			$dtheta += 2.0 * M_PI;
		}

		// 3. convert into cubic bezier segments <= 90deg
		$segments = ceil(abs($dtheta / (M_PI / 2.0)));
		$delta = $dtheta / $segments;
		$t = 8.0 / 3.0 * sin($delta / 4.0) * sin($delta / 4.0) / sin($delta / 2.0);
		$coords = [];

		for ($i = 0; $i < $segments; $i++) {

			$cosTheta1 = cos($theta1);
			$sinTheta1 = sin($theta1);

			$theta2 = $theta1 + $delta;

			$cosTheta2 = cos($theta2);
			$sinTheta2 = sin($theta2);

			// a) calculate endpoint of the segment:
			$xe = $cosPhi * $rx * $cosTheta2 - $sinPhi * $ry * $sinTheta2 + $cx;
			$ye = $sinPhi * $rx * $cosTheta2 + $cosPhi * $ry * $sinTheta2 + $cy;

			// b) calculate gradients at start/end points of segment:
			$dx1 = $t * ( - $cosPhi * $rx * $sinTheta1 - $sinPhi * $ry * $cosTheta1);
			$dy1 = $t * ( - $sinPhi * $rx * $sinTheta1 + $cosPhi * $ry * $cosTheta1);

			$dxe = $t * ( $cosPhi * $rx * $sinTheta2 + $sinPhi * $ry * $cosTheta2);
			$dye = $t * ( $sinPhi * $rx * $sinTheta2 - $cosPhi * $ry * $cosTheta2);

			// c) draw the cubic bezier:
			$coords[$i] = [($x1 + $dx1), ($y1 + $dy1), ($xe + $dxe), ($ye + $dye), $xe, $ye];

			// do next segment
			$theta1 = $theta2;
			$x1 = $xe;
			$y1 = $ye;
		}

		$path = ' ';

		foreach ($coords as $c) {

			$cpx1 = $c[0];
			$cpy1 = $c[1];
			$cpx2 = $c[2];
			$cpy2 = $c[3];
			$x2 = $c[4];
			$y2 = $c[5];
			$path .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $cpx1 * $this->kp, -$cpy1 * $this->kp, $cpx2 * $this->kp, -$cpy2 * $this->kp, $x2 * $this->kp, -$y2 * $this->kp) . "\n";

			// mPDF 5.0.040
			$bounds[0][] = $c[4];
			$bounds[1][] = $c[5];
		}

		return [$path, $bounds]; // mPDF 5.0.040
	}

	function CalcVectorAngle($ux, $uy, $vx, $vy)
	{
		$ta = atan2($uy, $ux);
		$tb = atan2($vy, $vx);

		if ($tb >= $ta) {
			return ($tb - $ta);
		}

		return (6.28318530718 - ($ta - $tb));
	}

	function ConvertSVGSizePixels($size = 5, $maxsize = 'x')
	{
		// maxsize in pixels (user units) or 'y' or 'x'
		// e.g. $w = $this->ConvertSVGSizePixels($arguments['w'],$this->svg_info['w']*(25.4/$this->mpdf->dpi));
		// usefontsize - setfalse for e.g. margins - will ignore fontsize for % values
		// Depends of maxsize value to make % work properly. Usually maxsize == pagewidth
		// For text $maxsize = Fontsize
		// Setting e.g. margin % will use maxsize (pagewidth) and em will use fontsize

		if ($maxsize == 'y') {
			$maxsize = $this->svg_info['h'];
		} elseif ($maxsize == 'x') {
			$maxsize = $this->svg_info['w'];
		}

		$maxsize *= (25.4 / $this->mpdf->dpi); // convert pixels to mm
		$fontsize = $this->mpdf->FontSize / $this->kf;

		// Return as pixels
		$size = $this->sizeConverter->convert($size, $maxsize, $fontsize, false) * 1 / (25.4 / $this->mpdf->dpi);

		return $size;
	}

	function ConvertSVGSizePts($size = 5)
	{
		// usefontsize - setfalse for e.g. margins - will ignore fontsize for % values
		// Depends of maxsize value to make % work properly. Usually maxsize == pagewidth
		// For text $maxsize = Fontsize
		// Setting e.g. margin % will use maxsize (pagewidth) and em will use fontsize
		$maxsize = $this->mpdf->FontSize;

		// Return as pts
		$size = $this->sizeConverter->convert($size, $maxsize, false, true) * 72 / 25.4;

		return $size;
	}

	function svgRect($arguments)
	{
		if ($arguments['h'] == 0 || $arguments['w'] == 0) {
			return '';
		}

		$x = $this->ConvertSVGSizePixels($arguments['x'], 'x'); // mPDF 4.4.003
		$y = $this->ConvertSVGSizePixels($arguments['y'], 'y'); // mPDF 4.4.003
		$h = $this->ConvertSVGSizePixels($arguments['h'], 'y'); // mPDF 4.4.003
		$w = $this->ConvertSVGSizePixels($arguments['w'], 'x'); // mPDF 4.4.003
		$rx = $this->ConvertSVGSizePixels($arguments['rx'], 'x'); // mPDF 4.4.003
		$ry = $this->ConvertSVGSizePixels($arguments['ry'], 'y'); // mPDF 4.4.003

		// mPDF 4.4.003
		if ($rx > $w / 2) {
			$rx = $w / 2;
		}

		// mPDF 4.4.003
		if ($ry > $h / 2) {
			$ry = $h / 2;
		}

		if ($rx > 0 and $ry == 0) {
			$ry = $rx;
		}

		if ($ry > 0 and $rx == 0) {
			$rx = $ry;
		}

		if ($rx == 0 and $ry == 0) {
			//	trace un rectangle sans angle arrondit
			$path_cmd = sprintf('%.3F %.3F m ', ($x * $this->kp), -($y * $this->kp));
			$path_cmd .= sprintf('%.3F %.3F l ', (($x + $w) * $this->kp), -($y * $this->kp));
			$path_cmd .= sprintf('%.3F %.3F l ', (($x + $w) * $this->kp), -(($y + $h) * $this->kp));
			$path_cmd .= sprintf('%.3F %.3F l ', ($x) * $this->kp, -(($y + $h) * $this->kp));
			$path_cmd .= sprintf('%.3F %.3F l h ', ($x * $this->kp), -($y * $this->kp));
		} else {
			//	trace un rectangle avec les arrondit
			//	les points de controle du bezier sont deduis grace a la constante kappa
			$kappa = 4 * (sqrt(2) - 1) / 3;

			$kx = $kappa * $rx;
			$ky = $kappa * $ry;

			$path_cmd = sprintf('%.3F %.3F m ', ($x + $rx) * $this->kp, -$y * $this->kp);
			$path_cmd .= sprintf('%.3F %.3F l ', ($x + ($w - $rx)) * $this->kp, -$y * $this->kp);
			$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x + ($w - $rx + $kx)) * $this->kp, -$y * $this->kp, ($x + $w) * $this->kp, (-$y + (-$ry + $ky)) * $this->kp, ($x + $w) * $this->kp, (-$y + (-$ry)) * $this->kp);
			$path_cmd .= sprintf('%.3F %.3F l ', ($x + $w) * $this->kp, (-$y + (-$h + $ry)) * $this->kp);
			$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x + $w) * $this->kp, (-$y + (-$h - $ky + $ry)) * $this->kp, ($x + ($w - $rx + $kx)) * $this->kp, (-$y + (-$h)) * $this->kp, ($x + ($w - $rx)) * $this->kp, (-$y + (-$h)) * $this->kp);

			$path_cmd .= sprintf('%.3F %.3F l ', ($x + $rx) * $this->kp, (-$y + (-$h)) * $this->kp);
			$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x + ($rx - $kx)) * $this->kp, (-$y + (-$h)) * $this->kp, $x * $this->kp, (-$y + (-$h - $ky + $ry)) * $this->kp, $x * $this->kp, (-$y + (-$h + $ry)) * $this->kp);
			$path_cmd .= sprintf('%.3F %.3F l ', $x * $this->kp, (-$y + (-$ry)) * $this->kp);
			$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c h ', $x * $this->kp, (-$y + (-$ry + $ky)) * $this->kp, ($x + ($rx - $kx)) * $this->kp, -$y * $this->kp, ($x + $rx) * $this->kp, -$y * $this->kp);
		}
		return $path_cmd;
	}

	/**
	 * fonction retracant les <ellipse /> et <circle />
	 * le cercle est tracé grave a 4 bezier cubic, les poitn de controles
	 * sont deduis grace a la constante kappa * rayon
	 */
	function svgEllipse($arguments)
	{
		if ($arguments['rx'] == 0 || $arguments['ry'] == 0) {
			return '';
		}

		$kappa = 4 * (sqrt(2) - 1) / 3;

		$cx = $this->ConvertSVGSizePixels($arguments['cx'], 'x');
		$cy = $this->ConvertSVGSizePixels($arguments['cy'], 'y');
		$rx = $this->ConvertSVGSizePixels($arguments['rx'], 'x');
		$ry = $this->ConvertSVGSizePixels($arguments['ry'], 'y');

		$x1 = $cx;
		$y1 = -$cy + $ry;

		$x2 = $cx + $rx;
		$y2 = -$cy;

		$x3 = $cx;
		$y3 = -$cy - $ry;

		$x4 = $cx - $rx;
		$y4 = -$cy;

		$path_cmd = sprintf('%.3F %.3F m ', $x1 * $this->kp, $y1 * $this->kp);
		$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x1 + ($rx * $kappa)) * $this->kp, $y1 * $this->kp, $x2 * $this->kp, ($y2 + ($ry * $kappa)) * $this->kp, $x2 * $this->kp, $y2 * $this->kp);
		$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $x2 * $this->kp, ($y2 - ($ry * $kappa)) * $this->kp, ($x3 + ($rx * $kappa)) * $this->kp, $y3 * $this->kp, $x3 * $this->kp, $y3 * $this->kp);
		$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($x3 - ($rx * $kappa)) * $this->kp, $y3 * $this->kp, $x4 * $this->kp, ($y4 - ($ry * $kappa)) * $this->kp, $x4 * $this->kp, $y4 * $this->kp);
		$path_cmd .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $x4 * $this->kp, ($y4 + ($ry * $kappa)) * $this->kp, ($x1 - ($rx * $kappa)) * $this->kp, $y1 * $this->kp, $x1 * $this->kp, $y1 * $this->kp);
		$path_cmd .= 'h ';

		return $path_cmd;
	}

	function svgPolyline($arguments, $ispolyline = true)
	{
		if ($ispolyline) {
			$xbase = $arguments[0];
			$ybase = - $arguments[1];
		} else {
			if ($arguments[0] == $arguments[2] && $arguments[1] == $arguments[3]) {
				return '';
			} // Zero length line
			$xbase = $this->ConvertSVGSizePixels($arguments[0], 'x');
			$ybase = - $this->ConvertSVGSizePixels($arguments[1], 'y');
		}

		$path_cmd = sprintf('%.3F %.3F m ', $xbase * $this->kp, $ybase * $this->kp);

		for ($i = 2; $i < count($arguments); $i += 2) {
			if ($ispolyline) {
				$tmp_x = $arguments[$i];
				$tmp_y = - $arguments[($i + 1)];
			} else {
				$tmp_x = $this->ConvertSVGSizePixels($arguments[$i], 'x');
				$tmp_y = - $this->ConvertSVGSizePixels($arguments[($i + 1)], 'y');
			}
			$path_cmd .= sprintf('%.3F %.3F l ', $tmp_x * $this->kp, $tmp_y * $this->kp);
		}

		//	$path_cmd .= 'h '; // ?? In error - don't close subpath here
		return $path_cmd;
	}

	function svgPolygon($arguments)
	{
		$xbase = $arguments[0];
		$ybase = - $arguments[1];
		$path_cmd = sprintf('%.3F %.3F m ', $xbase * $this->kp, $ybase * $this->kp);

		for ($i = 2; $i < count($arguments); $i += 2) {
			$tmp_x = $arguments[$i];
			$tmp_y = - $arguments[($i + 1)];

			$path_cmd .= sprintf('%.3F %.3F l ', $tmp_x * $this->kp, $tmp_y * $this->kp);
		}

		$path_cmd .= sprintf('%.3F %.3F l ', $xbase * $this->kp, $ybase * $this->kp);
		$path_cmd .= 'h ';

		return $path_cmd;
	}

	function svgText()
	{
		$current_style = $this->txt_style[count($this->txt_style) - 1]; // mPDF 5.7.4
		$style = '';
		$op = '';
		$render = -1;

		if (isset($this->txt_data[2])) {

			// mPDF 6
			// If using SVG Font
			if (isset($this->svg_font[$current_style['font-family']])) {

				// select font
				$style = 'R';
				$style .= (isset($current_style['font-weight']) && $current_style['font-weight'] == 'bold') ? 'B' : '';
				$style .= (isset($current_style['font-style']) && $current_style['font-style'] == 'italic') ? 'I' : '';
				$style .= (isset($current_style['font-variant']) && $current_style['font-variant'] == 'small-caps') ? 'S' : '';

				$fontsize = $current_style['font-size'] * $this->mpdf->dpi / 72;

				if (isset($this->svg_font[$current_style['font-family']][$style])) {
					$svg_font = $this->svg_font[$current_style['font-family']][$style];
				} elseif (isset($this->svg_font[$current_style['font-family']]['R'])) {
					$svg_font = $this->svg_font[$current_style['font-family']]['R'];
				}

				if (!isset($svg_font['units-per-em']) || $svg_font['units-per-em'] < 1) {
					$svg_font['units-per-em'] = 1000;
				}

				$units_per_em = $svg_font['units-per-em'];
				$scale = $fontsize / $units_per_em;
				$stroke_width = $current_style['stroke-width'];
				$stroke_width /= $scale;

				$opacitystr = '';
				$fopacity = 1;

				if (isset($current_style['fill-opacity'])) {
					if ($current_style['fill-opacity'] == 0) {
						$fopacity = 0;
					} elseif ($current_style['fill-opacity'] > 1) {
						$fopacity = 1;
					} elseif ($current_style['fill-opacity'] > 0) {
						$fopacity = $current_style['fill-opacity'];
					} elseif ($current_style['fill-opacity'] < 0) {
						$fopacity = 0;
					}
				}

				$sopacity = 1;
				if (isset($current_style['stroke-opacity'])) {
					if ($current_style['stroke-opacity'] == 0) {
						$sopacity = 0;
					} elseif ($current_style['stroke-opacity'] > 1) {
						$sopacity = 1;
					} elseif ($current_style['stroke-opacity'] > 0) {
						$sopacity = $current_style['stroke-opacity'];
					} elseif ($current_style['stroke-opacity'] < 0) {
						$sopacity = 0;
					}
				}

				$gs = $this->mpdf->AddExtGState(['ca' => $fopacity, 'CA' => $sopacity, 'BM' => '/Normal']);
				$this->mpdf->extgstates[$gs]['fo'] = true;
				$opacitystr = sprintf(' /GS%d gs ', $gs);

				$fillstr = '';
				if (isset($current_style['fill']) && $current_style['fill'] != 'none') {
					$col = $this->colorConverter->convert($current_style['fill'], $this->mpdf->PDFAXwarnings);
					$fillstr = $this->mpdf->SetFColor($col, true);
					$render = "0"; // Fill (only)
					$op = 'f';
				}

				$strokestr = '';
				if ($stroke_width > 0 && $current_style['stroke'] != 'none') {
					$scol = $this->colorConverter->convert($current_style['stroke'], $this->mpdf->PDFAXwarnings);
					if ($scol) {
						$strokestr .= $this->mpdf->SetDColor($scol, true) . ' ';
					}
					$linewidth = $this->ConvertSVGSizePixels($stroke_width);
					if ($linewidth > 0) {
						$strokestr .= sprintf('%.3F w 0 J 0 j ', $linewidth * $this->kp);
						if ($render == -1) {
							$render = "1";
						} // stroke only
						else {
							$render = "2";
						}  // fill and stroke
						$op .= 'S';
					}
				}

				if ($render == -1) {
					return '';
				}

				if ($op == 'fS') {
					$op = 'B';
				}

				$x = $this->txt_data[0]; // mPDF 5.7.4
				$y = $this->txt_data[1]; // mPDF 5.7.4
				$txt = $this->txt_data[2];

				$txt = preg_replace('/\f/', '', $txt);
				$txt = preg_replace('/\r/', '', $txt);
				$txt = preg_replace('/\n/', ' ', $txt);
				$txt = preg_replace('/\t/', ' ', $txt);
				$txt = preg_replace("/[ ]+/u", ' ', $txt);

				if ($this->textjuststarted) {
					$txt = ltrim($txt);
				}  // mPDF 5.7.4

				$this->textjuststarted = false;  // mPDF 5.7.4

				$txt = $this->mpdf->purify_utf8_text($txt);

				if ($this->mpdf->text_input_as_HTML) {
					$txt = $this->mpdf->all_entities_to_utf8($txt);
				}

				$nb = mb_strlen($txt, 'UTF-8');
				$i = 0;
				$sw = 0;
				$subpath_cmd = '';

				while ($i < $nb) {

					// Get next character
					$char = mb_substr($txt, $i, 1, 'UTF-8');


					if (isset($svg_font['glyphs'][$char])) {
						$d = $svg_font['glyphs'][$char]['d'];
						if (isset($svg_font['glyphs'][$char]['horiz-adv-x'])) {
							$horiz_adv_x = $svg_font['glyphs'][$char]['horiz-adv-x'];
						} else {
							$horiz_adv_x = $svg_font['horiz-adv-x'];
						} // missing glyph width
					} else {
						$d = $svg_font['d'];
						$horiz_adv_x = $svg_font['horiz-adv-x']; // missing glyph width
					}

					preg_match_all('/([MZLHVCSQTAmzlhvcsqta])([eE ,\-.\d]+)*/', $d, $commands, PREG_SET_ORDER);
					$subpath_cmd .= sprintf('q %.4F 0 0 %.4F mPDF-AXS(%.4F) %.4F cm ', $scale, -$scale, ($x + $sw * $scale) * $this->kp, -$y * $this->kp);

					$this->subPathInit = true;
					$this->pathBBox = [999999, 999999, -999999, -999999];

					foreach ($commands as $cmd) {

						if (count($cmd) == 3 || (isset($cmd[2]) && $cmd[2] == '')) {
							list($tmp, $command, $arguments) = $cmd;
						} else {
							list($tmp, $command) = $cmd;
							$arguments = '';
						}

						$subpath_cmd .= $this->svgPath($command, $arguments);
					}

					$subpath_cmd .= $op . ' Q ';

					if ($this->pathBBox[2] == -1999998) {
						$this->pathBBox[2] = 100;
					}

					if ($this->pathBBox[3] == -1999998) {
						$this->pathBBox[3] = 100;
					}

					if ($this->pathBBox[0] == 999999) {
						$this->pathBBox[0] = 0;
					}

					if ($this->pathBBox[1] == 999999) {
						$this->pathBBox[1] = 0;
					}

					$sw += $horiz_adv_x;
					$i++;
				}

				$sw *= $scale; // convert stringwidth to units
				// mPDF 5.7.4

				$this->textlength = $sw;
				$this->texttotallength += $this->textlength;

				$path_cmd = sprintf('q %s %s Tr %s %s ', $opacitystr, $render, $fillstr, $strokestr);
				$path_cmd .= $subpath_cmd;
				$path_cmd .= 'Q ';

				unset($this->txt_data[0], $this->txt_data[1], $this->txt_data[2]);
				return $path_cmd;
			}

			// select font
			$style .= ($current_style['font-weight'] == 'bold') ? 'B' : '';
			$style .= ($current_style['font-style'] == 'italic') ? 'I' : '';
			$size = $current_style['font-size'] * $this->kf;

			$current_style['font-family'] = $this->mpdf->SetFont($current_style['font-family'], $style, $size, false);
			$this->mpdf->CurrentFont['fo'] = true;

			$opacitystr = '';
			// mPDF 6
			$fopacity = 1;

			if (isset($current_style['fill-opacity'])) {
				if ($current_style['fill-opacity'] == 0) {
					$fopacity = 0;
				} elseif ($current_style['fill-opacity'] > 1) {
					$fopacity = 1;
				} elseif ($current_style['fill-opacity'] > 0) {
					$fopacity = $current_style['fill-opacity'];
				} elseif ($current_style['fill-opacity'] < 0) {
					$fopacity = 0;
				}
			}

			$sopacity = 1;

			if (isset($current_style['stroke-opacity'])) {
				if ($current_style['stroke-opacity'] == 0) {
					$sopacity = 0;
				} elseif ($current_style['stroke-opacity'] > 1) {
					$sopacity = 1;
				} elseif ($current_style['stroke-opacity'] > 0) {
					$sopacity = $current_style['stroke-opacity'];
				} elseif ($current_style['stroke-opacity'] < 0) {
					$sopacity = 0;
				}
			}

			$gs = $this->mpdf->AddExtGState(['ca' => $fopacity, 'CA' => $sopacity, 'BM' => '/Normal']);
			$this->mpdf->extgstates[$gs]['fo'] = true;
			$opacitystr = sprintf(' /GS%d gs ', $gs);

			$fillstr = '';

			if (isset($current_style['fill']) && $current_style['fill'] != 'none') {
				$col = $this->colorConverter->convert($current_style['fill'], $this->mpdf->PDFAXwarnings);
				$fillstr = $this->mpdf->SetFColor($col, true);
				$render = "0"; // Fill (only)
			}

			$strokestr = '';

			if (isset($current_style['stroke-width']) && $current_style['stroke-width'] > 0 && $current_style['stroke'] != 'none') {
				$scol = $this->colorConverter->convert($current_style['stroke'], $this->mpdf->PDFAXwarnings);

				if ($scol) {
					$strokestr .= $this->mpdf->SetDColor($scol, true) . ' ';
				}

				$linewidth = $this->ConvertSVGSizePixels($current_style['stroke-width']);

				if ($linewidth > 0) {
					$strokestr .= sprintf('%.3F w 1 J 1 j ', $linewidth * $this->kp);
					if ($render == -1) {
						$render = "1";
					} // stroke only
					else {
						$render = "2";
					}  // fill and stroke
				}
			}

			if ($render == -1) {
				return '';
			}

			$x = $this->txt_data[0]; // mPDF 5.7.4
			$y = $this->txt_data[1]; // mPDF 5.7.4
			$txt = $this->txt_data[2];

			$txt = preg_replace('/\f/', '', $txt);
			$txt = preg_replace('/\r/', '', $txt);
			$txt = preg_replace('/\n/', ' ', $txt);
			$txt = preg_replace('/\t/', ' ', $txt);
			$txt = preg_replace("/[ ]+/u", ' ', $txt);

			if ($this->textjuststarted) {
				$txt = ltrim($txt);
			}  // mPDF 5.7.4

			$this->textjuststarted = false;  // mPDF 5.7.4

			$txt = $this->mpdf->purify_utf8_text($txt);

			if ($this->mpdf->text_input_as_HTML) {
				$txt = $this->mpdf->all_entities_to_utf8($txt);
			}

			if ($this->mpdf->usingCoreFont) {
				$txt = mb_convert_encoding($txt, $this->mpdf->mb_enc, 'UTF-8');
			}

			if (preg_match("/([" . $this->mpdf->pregRTLchars . "])/u", $txt)) {
				$this->mpdf->biDirectional = true;
			}

			$textvar = 0;
			$save_OTLtags = $this->mpdf->OTLtags;
			$this->mpdf->OTLtags = [];

			if ($this->mpdf->useKerning) {

				if ($this->mpdf->CurrentFont['haskernGPOS']) {

					if (isset($this->mpdf->OTLtags['Plus'])) {
						$this->mpdf->OTLtags['Plus'] .= ' kern';
					} else {
						$this->mpdf->OTLtags['Plus'] = ' kern';
					}

				} else {
					$textvar = ($textvar | TextVars::FC_KERNING);
				}
			}

			// Use OTL OpenType Table Layout - GSUB & GPOS
			if (isset($this->mpdf->CurrentFont['useOTL']) && $this->mpdf->CurrentFont['useOTL']) {
				$txt = $this->otl->applyOTL($txt, $this->mpdf->CurrentFont['useOTL']);
				$OTLdata = $this->otl->OTLdata;
			}

			$this->mpdf->OTLtags = $save_OTLtags;

			$this->mpdf->magic_reverse_dir($txt, $this->mpdf->directionality, $OTLdata);

			$this->mpdf->CurrentFont['used'] = true;

			$sw = $this->mpdf->GetStringWidth($txt, true, $OTLdata, $textvar); // also adds characters to subset

			// mPDF 5.7.4
			$this->textlength = $sw * 1 / (25.4 / $this->mpdf->dpi);
			$this->texttotallength += $this->textlength;

			$pdfx = $x * $this->kp;
			$pdfy = -$y * $this->kp;

			$aixextra = sprintf(' /F%d %.3F Tf %s %s Tr %s %s ', $this->mpdf->CurrentFont['i'], $this->mpdf->FontSizePt, $opacitystr, $render, $fillstr, $strokestr);

			$path_cmd = 'q 1 0 0 1 mPDF-AXS(0.00) 0 cm '; // Align X-shift
			$path_cmd .= $this->mpdf->Text($pdfx, $pdfy, $txt, $OTLdata, $textvar, $aixextra, 'SVG', true);
			$path_cmd .= " Q\n";

			unset($this->txt_data[0], $this->txt_data[1], $this->txt_data[2]);

			if (isset($current_style['font-size-parent'])) {
				$this->mpdf->SetFontSize($current_style['font-size-parent']);
			}

		} else {
			return ' ';
		}

		// Reset font	// mPDF 5.7.4
		$prev_style = $this->txt_style[count($this->txt_style) - 1];
		$style = '';
		$style .= ($prev_style['font-weight'] == 'bold') ? 'B' : '';
		$style .= ($prev_style['font-style'] == 'italic') ? 'I' : '';
		$size = $prev_style['font-size'] * $this->kf;
		$this->mpdf->SetFont($prev_style['font-family'], $style, $size, false);

		return $path_cmd;
	}

	function svgDefineTxtStyle($critere_style)
	{
		// get copy of current/default txt style, and modify it with supplied attributes
		$tmp = count($this->txt_style) - 1;
		$current_style = $this->txt_style[$tmp];

		if (isset($critere_style['style'])) {

			if (preg_match('/fill:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $critere_style['style'], $m)) {
				$current_style['fill'] = '#' . str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			} else {
				$tmp = preg_replace("/(.*)fill:\s*([a-z0-9#_()]*|none)(.*)/i", "$2", $critere_style['style']);
				if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
					$current_style['fill'] = $tmp;
				}
			}

			// mPDF 6
			if (preg_match("/[^-]opacity:\s*([a-z0-9.]*|none)/i", $critere_style['style'], $m) ||
				preg_match("/^opacity:\s*([a-z0-9.]*|none)/i", $critere_style['style'], $m)) {
				$current_style['fill-opacity'] = $m[1];
				$current_style['stroke-opacity'] = $m[1];
			}

			$tmp = preg_replace("/(.*)fill-opacity:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['fill-opacity'] = $tmp;
			}

			$tmp = preg_replace("/(.*)fill-rule:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp != $critere_style['style'] && $tmp != $critere_style['style']) {
				$current_style['fill-rule'] = $tmp;
			}

			if (preg_match('/stroke:\s*rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $critere_style['style'], $m)) {
				$current_style['stroke'] = '#' . str_pad(dechex($m[1]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($m[2]), 2, "0", STR_PAD_LEFT) . str_pad(dechex($m[3]), 2, "0", STR_PAD_LEFT);
			} else {
				$tmp = preg_replace("/(.*)stroke:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
				if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
					$current_style['stroke'] = $tmp;
				}
			}

			$tmp = preg_replace("/(.*)stroke-linecap:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-linecap'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-linejoin:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-linejoin'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-miterlimit:\s*([a-z0-9#]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-miterlimit'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-opacity:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-opacity'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-width:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-width'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-dasharray:\s*([a-z0-9., ]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-dasharray'] = $tmp;
			}

			$tmp = preg_replace("/(.*)stroke-dashoffset:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$current_style['stroke-dashoffset'] = $tmp;
			}

			$tmp = preg_replace("/(.*)font-family:\s*([a-z0-9.\"' ,\-]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$critere_style['font-family'] = $tmp;
			}

			$tmp = preg_replace("/(.*)font-size:\s*([a-z0-9.]*|none)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$critere_style['font-size'] = $tmp;
			}

			$tmp = preg_replace("/(.*)font-weight:\s*([a-z0-9.]*|normal)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$critere_style['font-weight'] = $tmp;
			}

			$tmp = preg_replace("/(.*)font-style:\s*([a-z0-9.]*|normal)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$critere_style['font-style'] = $tmp;
			}

			$tmp = preg_replace("/(.*)font-variant:\s*([a-z0-9.]*|normal)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$critere_style['font-variant'] = $tmp;
			}

			$tmp = preg_replace("/(.*)text-anchor:\s*(start|middle|end)(.*)/i", "$2", $critere_style['style']);
			if ($tmp && $tmp != 'inherit' && $tmp != $critere_style['style']) {
				$critere_style['text-anchor'] = $tmp;
			}
		}

		if (isset($critere_style['font'])) {
			// [ [ <'font-style'> || <'font-variant'> || <'font-weight'> ]?<'font-size'> [ / <'line-height'> ]? <'font-family'> ]

			$tmp = preg_replace("/(.*)(italic|oblique)(.*)/i", "$2", $critere_style['font']);
			if ($tmp != $critere_style['font']) {
				if ($tmp == 'oblique') {
					$tmp = 'italic';
				}
				$current_style['font-style'] = $tmp;
			}

			$tmp = preg_replace("/(.*)(bold|bolder)(.*)/i", "$2", $critere_style['font']);
			if ($tmp != $critere_style['font']) {
				if ($tmp == 'bolder') {
					$tmp = 'bold';
				}
				$current_style['font-weight'] = $tmp;
			}

			$tmp = preg_replace("/(.*)(small\-caps)(.*)/i", "$2", $critere_style['font']);
			if ($tmp != $critere_style['font']) {
				$current_style['font-variant'] = $tmp;
			}

			// select digits not followed by percent sign nor preceeded by forward slash
			$tmp = preg_replace("/(.*)\b(\d+)[\b|\/](.*)/i", "$2", $critere_style['font']);
			if ($tmp != $critere_style['font']) {
				$current_style['font-size'] = $this->ConvertSVGSizePts($tmp);
				$this->mpdf->SetFont('', '', $current_style['font-size'], false);
			}
		}

		// mPDF 6
		if (isset($critere_style['opacity']) && $critere_style['opacity'] != 'inherit') {
			$current_style['fill-opacity'] = $critere_style['opacity'];
			$current_style['stroke-opacity'] = $critere_style['opacity'];
		}

		// mPDF 6
		if (isset($critere_style['stroke-opacity']) && $critere_style['stroke-opacity'] != 'inherit') {
			$current_style['stroke-opacity'] = $critere_style['stroke-opacity'];
		}

		// mPDF 6
		if (isset($critere_style['fill-opacity']) && $critere_style['fill-opacity'] != 'inherit') {
			$current_style['fill-opacity'] = $critere_style['fill-opacity'];
		}

		if (isset($critere_style['fill']) && $critere_style['fill'] != 'inherit') {
			$current_style['fill'] = $critere_style['fill'];
		}

		if (isset($critere_style['stroke']) && $critere_style['stroke'] != 'inherit') {
			$current_style['stroke'] = $critere_style['stroke'];
		}

		if (isset($critere_style['stroke-width']) && $critere_style['stroke-width'] != 'inherit') {
			$current_style['stroke-width'] = $critere_style['stroke-width'];
		}

		if (isset($critere_style['font-style']) && $critere_style['font-style'] != 'inherit') {
			if (strtolower($critere_style['font-style']) == 'oblique') {
				$critere_style['font-style'] = 'italic';
			}
			$current_style['font-style'] = $critere_style['font-style'];
		}

		if (isset($critere_style['font-weight']) && $critere_style['font-weight'] != 'inherit') {
			if (strtolower($critere_style['font-weight']) == 'bolder') {
				$critere_style['font-weight'] = 'bold';
			}
			$current_style['font-weight'] = $critere_style['font-weight'];
		}

		if (isset($critere_style['font-variant']) && $critere_style['font-variant'] != 'inherit') {
			$current_style['font-variant'] = $critere_style['font-variant'];
		}

		if (isset($critere_style['font-size']) && $critere_style['font-size'] != 'inherit') {
			if (strpos($critere_style['font-size'], '%') !== false) {
				$current_style['font-size-parent'] = $current_style['font-size'];
			}
			$current_style['font-size'] = $this->ConvertSVGSizePts($critere_style['font-size']);
			$this->mpdf->SetFont('', '', $current_style['font-size'], false);
		}

		if (isset($critere_style['font-family']) && $critere_style['font-family'] != 'inherit') {
			$v = $critere_style['font-family'];
			$aux_fontlist = explode(",", $v);
			$found = 0;

			$svgfontstyle = 'R';
			$svgfontstyle .= (isset($current_style['font-weight']) && $current_style['font-weight'] == 'bold') ? 'B' : '';
			$svgfontstyle .= (isset($current_style['font-style']) && $current_style['font-style'] == 'italic') ? 'I' : '';
			$svgfontstyle .= (isset($current_style['font-variant']) && $current_style['font-variant'] == 'small-caps') ? 'S' : '';

			foreach ($aux_fontlist as $f) {
				$fonttype = trim($f);
				$fonttype = preg_replace('/["\']*(.*?)["\']*/', '\\1', $fonttype);
				$fonttype = preg_replace('/ /', '', $fonttype);
				$v = strtolower(trim($fonttype));
				if (isset($this->mpdf->fonttrans[$v]) && $this->mpdf->fonttrans[$v]) {
					$v = $this->mpdf->fonttrans[$v];
				}
				if ((!$this->mpdf->usingCoreFont && in_array($v, $this->mpdf->available_unifonts)) ||
					($this->mpdf->usingCoreFont && in_array($v, ['courier', 'times', 'helvetica', 'arial'])) ||
					in_array($v, ['sjis', 'uhc', 'big5', 'gb']) || isset($this->svg_font[$v][$svgfontstyle])) { // mPDF 6
					$current_style['font-family'] = $v;
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
					if (in_array($v, $this->mpdf->sans_fonts) || in_array($v, $this->mpdf->serif_fonts) || in_array($v, $this->mpdf->mono_fonts) || isset($this->svg_font[$v][$svgfontstyle])) {  // mPDF 6
						$current_style['font-family'] = $v;
						break;
					}
				}
			}
		}

		if (isset($critere_style['text-anchor']) && $critere_style['text-anchor'] != 'inherit') {
			$current_style['text-anchor'] = $critere_style['text-anchor'];
		}

		// add current style to text style array (will remove it later after writing text to svg_string)
		array_push($this->txt_style, $current_style);
	}

	function svgAddGradient($id, $array_gradient)
	{

		$this->svg_gradient[$id] = $array_gradient;
	}

	function svgWriteString($content)
	{
		$this->svg_string .= $content;
	}

	/**
	 * SVGs made with Adobe Illustrator use a style tag and classes instead of inline styles
	 * See: https://github.com/mpdf/mpdf/issues/450
	 *
	 * This function brutally copies the styles inline
	 *  ( Currently only looks for classes as a selector )
	 *
	 * @param string $data svg contents
	 * @return string svg contents
	 * @author Antonio Norman - softcodex.ch
	 */
	function mergeStyles($data)
	{
		$xml = new \DOMDocument();
		if (!$xml->loadXML($data, LIBXML_NOERROR)) {
			return $data;
		}

		// Check it's an SVG
		$svgNode = $xml->getElementsByTagName('svg');
		if ($svgNode->length === 0) {
			return $data;
		}

		// Find the style node
		$styles = [];
		/** @var $styleNode \DOMNode */
		foreach ($svgNode->item(0)->getElementsByTagName('style') as $styleNode) {

			preg_match_all('/(\.[^{]+)\s*\{\s*([^}]+)\s*}/m', $styleNode->nodeValue, $matches, PREG_SET_ORDER);
			foreach ($matches as $cssBlock) {
				$css = preg_replace('/\s{2,}/', ' ', $cssBlock[2]); // Clean spaces or new lines
				$selector = trim($cssBlock[1]);

				$styles[$selector] = isset($styles[$cssBlock[1]]) ?
					$styles[$selector] . ' ' . $css : // Append if the selector is already defined
					$css;
			}
		}

		if (empty($styles)) {
			return $data;
		}

		// Recursively loop the nodes inserting the styles inline
		$setStylesInline = function (\DOMNode $xml) use ($styles, &$setStylesInline) {
			// Apply the styles to the elements
			foreach ($xml->childNodes as $node) {

				if ($node->hasChildNodes()) {
					$setStylesInline($node);
				}

				if (!$node instanceof \DOMElement) {
					continue;
				}

				// Check the node has the a class with a style
				if (!$node->hasAttribute('class')) {
					continue;
				}

				// Allow for class=" class1  class2 "
				$classes = explode(' ', $node->getAttribute('class'));

				foreach ($classes as $class) {

					$class = '.' . trim($class);
					if (!empty($class) && isset($styles[$class])) {

						$style = $node->hasAttribute('style') ?
							$styles[$class] . ' ' . $node->getAttribute('style') :
							$styles[$class];

						$node->setAttribute('style', $style);
					}
				}
			}
		};

		$setStylesInline($xml);

		return $xml->saveXML();
	}

	/**
	 * analise le svg et renvoie aux fonctions precedente our le traitement
	 */
	function ImageSVG($data)
	{
		$data = preg_replace('/^.*?<svg([> ])/is', '<svg\\1', $data); // mPDF 5.7.4
		$data = preg_replace('/<!--.*?-->/is', '', $data); // mPDF 5.7.4

		// Converts < to &lt; when not a tag
		$data = preg_replace('/<([^!?\/a-zA-Z_:])/i', '&lt;\\1', $data); // mPDF 5.7.4

		$data = $this->mergeStyles($data);

		if ($this->mpdf->svgAutoFont) {
			$data = $this->markScriptToLang($data);
		}

		$this->svg_info = [];
		$last_gradid = ''; // mPDF 6
		$last_svg_fontid = ''; // mPDF 6
		$last_svg_fontdefw = ''; // mPDF 6
		$last_svg_fontstyle = ''; // mPDF 6

		if (preg_match('/<!ENTITY/si', $data)) {
			// Get User-defined entities
			preg_match_all('/<!ENTITY\s+([a-z]+)\s+\"(.*?)\">/si', $data, $ent);
			// Replace entities
			for ($i = 0; $i < count($ent[0]); $i++) {
				$data = preg_replace('/&' . preg_quote($ent[1][$i], '/') . ';/is', $ent[2][$i], $data);
			}
		}

		if (preg_match('/xlink:href\s*=/si', $data)) {

			// GRADIENTS
			// Get links
			preg_match_all('/(<(linearGradient|radialgradient)[^>]*)xlink:href\s*=\s*["\']#(.*?)["\'](.*?)\/>/si', $data, $links);
			if (count($links[0])) {
				$links[5] = [];
			}

			// Delete links from data - keeping in $links
			for ($i = 0; $i < count($links[0]); $i++) {
				$links[5][$i] = 'tmpLink' . random_int(100000, 9999999);
				$data = preg_replace('/' . preg_quote($links[0][$i], '/') . '/is', '<MYLINKS' . $links[5][$i] . '>', $data);
			}

			// Get targets
			preg_match_all('/<(linearGradient|radialgradient)([^>]*)id\s*=\s*["\'](.*?)["\'](.*?)>(.*?)<\/(linearGradient|radialgradient)>/si', $data, $m);
			$targets = [];
			$stops = [];

			// keeping in $targets
			for ($i = 0; $i < count($m[0]); $i++) {
				$stops[$m[3][$i]] = $m[5][$i];
			}

			// Add back links this time as targets (gradients)
			for ($i = 0; $i < count($links[0]); $i++) {
				$def = $links[1][$i] . ' ' . $links[4][$i] . '>' . $stops[$links[3][$i]] . '</' . $links[2][$i] . '>';
				$data = preg_replace('/<MYLINKS' . $links[5][$i] . '>/is', $def, $data);
			}

			// mPDF 5.7.4
			// <TREF>
			preg_match_all('/<tref ([^>]*)xlink:href\s*=\s*["\']#([^>]*?)["\']([^>]*)\/>/si', $data, $links);
			for ($i = 0; $i < count($links[0]); $i++) {
				// Get the item to use from defs
				$insert = '';
				if (preg_match('/<text [^>]*id\s*=\s*["\']' . $links[2][$i] . '["\'][^>]*>(.*?)<\/text>/si', $data, $m)) {
					$insert = $m[1];
				}
				if ($insert) {
					$data = preg_replace('/' . preg_quote($links[0][$i], '/') . '/is', $insert, $data);
				}
			}

			// mPDF 5.7.2
			// <USE>
			preg_match_all('/<use( [^>]*)xlink:href\s*=\s*["\']#([^>]*?)["\']([^>]*)\/>/si', $data, $links);
			for ($i = 0; $i < count($links[0]); $i++) {
				// Get the item to use from defs
				$insert = '';
				if (preg_match('/<([a-zA-Z]*) [^>]*id\s*=\s*["\']' . $links[2][$i] . '["\'][^>]*\/>/si', $data, $m)) {
					$insert = $m[0];
				}
				if (!$insert && preg_match('/<([a-zA-Z]*) [^>]*id\s*=\s*["\']' . $links[2][$i] . '["\']/si', $data, $m)) {
					if (preg_match('/<' . $m[1] . '[^>]*id\s*=\s*["\']' . $links[2][$i] . '["\'][^>]*>.*?<\/' . $m[1] . '>/si', $data, $m)) {
						$insert = $m[0];
					}
				}

				if ($insert) {
					$inners = $links[1][$i] . ' ' . $links[3][$i];

					// Change x,y coords to translate()
					if (preg_match('/\sy\s*=\s*["\']([^>]*?)["\']/', $inners, $m)) {
						$y = $m[1];
					} else {
						$y = 0;
					}

					if (preg_match('/\sx\s*=\s*["\']([^>]*?)["\']/', $inners, $m)) {
						$x = $m[1];
					} else {
						$x = 0;
					}

					if ($x || $y) {

						$inners = preg_replace('/(y|x)\s*=\s*["\']([^>]*?)["\']/', '', $inners);
						if (preg_match('/transform\s*=\s*["\']([^>]*?)["\']/', $inners, $m)) {

							if (preg_match('/translate\(\s*([0-9\.]+)\s*,\s*([0-9\.]+)\s*\)/', $m[1], $mm)) {
								$transform = $m[1]; // transform="...."
								$x += $mm[1];
								$y += $mm[2];
								$transform = preg_replace('/' . preg_quote($mm[0], '/') . '/', '', $transform);
								$transform = 'transform="' . $transform . ' translate(' . $x . ', ' . $y . ')"';
								$inners = preg_replace('/' . preg_quote($m[0], '/') . '/is', $transform, $inners);
							} else {
								$inners = preg_replace('/' . preg_quote($m[0], '/') . '/is', 'transform="' . $m[1] . ' translate(' . $x . ', ' . $y . ')"', $inners);
							}

						} else {
							$inners .= ' transform="translate(' . $x . ', ' . $y . ')"';
						}
					}
				}

				$replacement = '<g ' . $inners . '>' . $insert . '</g>';
				$data = preg_replace('/' . preg_quote($links[0][$i], '/') . '/is', $replacement, $data);
			}

			preg_match_all('/<use( [^>]*)xlink:href\s*=\s*["\']#([^>]*?)["\']([^>]*)>\s*<\/use>/si', $data, $links);
			for ($i = 0; $i < count($links[0]); $i++) {

				// Get the item to use from defs
				$insert = '';
				if (preg_match('/<([a-zA-Z]*) [^>]*id\s*=\s*["\']' . $links[2][$i] . '["\'][^>]*\/>/si', $data, $m)) {
					$insert = $m[0];
				}

				if (!$insert && preg_match('/<([a-zA-Z]*) [^>]*id\s*=\s*["\']' . $links[2][$i] . '["\']/si', $data, $m)) {
					if (preg_match('/<' . $m[1] . '[^>]*id\s*=\s*["\']' . $links[2][$i] . '["\'][^>]*>.*?<\/' . $m[1] . '>/si', $data, $m)) {
						$insert = $m[0];
					}
				}

				if ($insert) {
					$inners = $links[1][$i] . ' ' . $links[3][$i];

					// Change x,y coords to translate()
					if (preg_match('/\sy\s*=\s*["\']([^>]*?)["\']/', $inners, $m)) {
						$y = $m[1];
					} else {
						$y = 0;
					}

					if (preg_match('/\sx\s*=\s*["\']([^>]*?)["\']/', $inners, $m)) {
						$x = $m[1];
					} else {
						$x = 0;
					}

					if ($x || $y) {
						$inners = preg_replace('/(y|x)\s*=\s*["\']([^>]*?)["\']/', '', $inners);
						if (preg_match('/transform\s*=\s*["\']([^>]*?)["\']/', $inners, $m)) {
							if (preg_match('/translate\(\s*([0-9\.]+)\s*,\s*([0-9\.]+)\s*\)/', $m[1], $mm)) {
								$transform = $m[1]; // transform="...."
								$x += $mm[1];
								$y += $mm[2];
								$transform = preg_replace('/' . preg_quote($mm[0], '/') . '/', '', $transform);
								$transform = 'transform="' . $transform . ' translate(' . $x . ', ' . $y . ')"';
								$inners = preg_replace('/' . preg_quote($m[0], '/') . '/is', $transform, $inners);
							} else {
								$inners = preg_replace('/' . preg_quote($m[0], '/') . '/is', 'transform="' . $m[1] . ' translate(' . $x . ', ' . $y . ')"', $inners);
							}
						} else {
							$inners .= ' transform="translate(' . $x . ', ' . $y . ')"';
						}
					}

					$replacement = '<g ' . $inners . '>' . $insert . '</g>';
					$data = preg_replace('/' . preg_quote($links[0][$i], '/') . '/is', $replacement, $data);
				}
			}
		}

		// Removes <pattern>
		$data = preg_replace('/<pattern.*?<\/pattern>/is', '', $data);

		// Removes <marker>
		$data = preg_replace('/<marker.*?<\/marker>/is', '', $data);

		$this->svg_info['data'] = $data;
		$this->svg_string = '';

		$svg2pdf_xml = '';

		// Don't output stuff inside <defs>
		$this->inDefs = false;

		$svg2pdf_xml_parser = xml_parser_create("utf-8");

		xml_parser_set_option($svg2pdf_xml_parser, XML_OPTION_CASE_FOLDING, false);

		xml_set_element_handler(
			$svg2pdf_xml_parser,
			[$this, 'xml_svg2pdf_start'],
			[$this, 'xml_svg2pdf_end']
		);

		xml_set_character_data_handler(
			$svg2pdf_xml_parser,
			[$this, 'characterData']
		);

		xml_parse($svg2pdf_xml_parser, $data);

		if ($this->svg_error) {
			return false;
		} else {
			return [
				'x' => $this->svg_info['x'] * $this->kp,
				'y' => -$this->svg_info['y'] * $this->kp,
				'w' => $this->svg_info['w'] * $this->kp,
				'h' => -$this->svg_info['h'] * $this->kp,
				'data' => $this->svg_string,
			];
		}
	}

	// AUTOFONT =========================
	/** @todo reuse as much code from Mpdf::markScriptToLang as possible */
	function markScriptToLang($html)
	{
		if ($this->mpdf->onlyCoreFonts) {
			return $html;
		}

		$n = '';
		$a = preg_split('/<(.*?)>/ms', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach ($a as $i => $e) {
			if ($i % 2 == 0) {
				$e = UtfString::strcode2utf($e);
				$e = $this->mpdf->lesser_entity_decode($e);

				$earr = $this->mpdf->UTF8StringToArray($e, false);

				$scriptblock = 0;
				$scriptblocks = [];
				$scriptblocks[0] = 0;
				$chardata = [];
				$subchunk = 0;
				$charctr = 0;
				foreach ($earr as $char) {
					$ucd_record = Ucdn::get_ucd_record($char);
					$sbl = $ucd_record[6];

					if ($sbl && $sbl != 40 && $sbl != 102) {
						if ($scriptblock == 0) {
							$scriptblock = $sbl;
							$scriptblocks[$subchunk] = $scriptblock;
						} elseif ($scriptblock > 0 && $scriptblock != $sbl) {
							// NEW (non-common) Script encountered in this chunk.
							// Start a new subchunk
							$subchunk++;
							$scriptblock = $sbl;
							$charctr = 0;
							$scriptblocks[$subchunk] = $scriptblock;
						}
					}

					$chardata[$subchunk][$charctr]['script'] = $sbl;
					$chardata[$subchunk][$charctr]['uni'] = $char;
					$charctr++;
				}

				// If scriptblock[x] = common & non-baseScript
				// and scriptblock[x+1] = baseScript
				// Move common script from end of x to start of x+1
				for ($sch = 0; $sch < $subchunk; $sch++) {
					if ($scriptblocks[$sch] > 0 && $scriptblocks[$sch] != $this->mpdf->baseScript && $scriptblocks[$sch + 1] == $this->mpdf->baseScript) {
						$end = count($chardata[$sch]) - 1;
						while ($chardata[$sch][$end]['script'] == 0 && $end > 1) { // common script
							$tmp = array_pop($chardata[$sch]);
							array_unshift($chardata[$sch + 1], $tmp);
							$end--;
						}
					}
				}

				$o = '';
				for ($sch = 0; $sch <= $subchunk; $sch++) {
					if (isset($chardata[$sch])) {
						$s = '';
						for ($j = 0; $j < count($chardata[$sch]); $j++) {
							$s .= UtfString::code2utf($chardata[$sch][$j]['uni']);
						}
						// ZZZ99 Undo lesser_entity_decode as above - but only for <>&
						$s = str_replace("&", "&amp;", $s);
						$s = str_replace("<", "&lt;", $s);
						$s = str_replace(">", "&gt;", $s);

						if (substr($a[$i - 1], 0, 5) != '<text' && substr($a[$i - 1], 0, 5) != '<tspa') {
							continue;
						} // <tspan> or <text> only

						$lang = '';
						// Check Vietnamese if Latin script - even if Basescript
						if ($scriptblocks[$sch] == Ucdn::SCRIPT_LATIN && $this->mpdf->autoVietnamese && preg_match("/([" . $this->scriptToLanguage->getLanguageDelimiters('viet') . "])/u", $s)) {
							$lang = "vi";
						} // Check Arabic for different languages if Arabic script - even if Basescript
						elseif ($scriptblocks[$sch] == Ucdn::SCRIPT_ARABIC && $this->mpdf->autoArabic) {
							if (preg_match("/[" . $this->scriptToLanguage->getLanguageDelimiters('sindhi') . "]/u", $s)) {
								$lang = "sd";
							} elseif (preg_match("/[" . $this->scriptToLanguage->getLanguageDelimiters('urdu') . "]/u", $s)) {
								$lang = "ur";
							} elseif (preg_match("/[" . $this->scriptToLanguage->getLanguageDelimiters('pashto') . "]/u", $s)) {
								$lang = "ps";
							} elseif (preg_match("/[" . $this->scriptToLanguage->getLanguageDelimiters('persian') . "]/u", $s)) {
								$lang = "fa";
							} elseif ($this->mpdf->baseScript != Ucdn::SCRIPT_ARABIC && $this->scriptToLanguage->getLanguageByScript($scriptblocks[$sch])) {
								$lang = "'." . $this->scriptToLanguage->getLanguageByScript($scriptblocks[$sch]) . "'";
							}
						} // Identify Script block if not Basescript, and mark up as language
						elseif ($scriptblocks[$sch] > 0 && $scriptblocks[$sch] != $this->mpdf->baseScript && $this->scriptToLanguage->getLanguageByScript($scriptblocks[$sch])) {
							$lang = $this->scriptToLanguage->getLanguageByScript($scriptblocks[$sch]);
						}
						if ($lang) {
							$o .= '<tspan lang="' . $lang . '">' . $s . '</tspan>';
						} else {
							$o .= $s;
						}
					}
				}

				$a[$i] = $o;
			} else {
				$a[$i] = '<' . $e . '>';
			}
		}
		$n = implode('', $a);
		return $n;
	}

	function xml_svg2pdf_start($parser, $name, $attribs)
	{
		global $last_gradid, $last_svg_fontid, $last_svg_fontdefw, $last_svg_fontstyle; // mPDF 6

		// mPDF 6
		if (strtolower($name) == 'font') {
			$last_svg_fontid = '';

			if (isset($attribs['horiz-adv-x']) && $attribs['horiz-adv-x']) {
				$last_svg_fontdefw = $attribs['horiz-adv-x'];
			}

			return;
		} elseif (strtolower($name) == 'font-face') { // mPDF 6
			$last_svg_fontstyle = 'R';
			$last_svg_fontstyle .= (isset($attribs['font-weight']) && $attribs['font-weight'] == 'bold') ? 'B' : '';
			$last_svg_fontstyle .= (isset($attribs['font-style']) && $attribs['font-style'] == 'italic') ? 'I' : '';
			$last_svg_fontstyle .= (isset($attribs['font-variant']) && $attribs['font-variant'] == 'small-caps') ? 'S' : '';

			if (isset($attribs['font-family']) && $attribs['font-family']) {

				$tmp_svg_font = [
					'units-per-em' => (isset($attribs['units-per-em']) ? $attribs['units-per-em'] : ''),
					'd' => '',
					'glyphs' => []
				];

				$last_svg_fontid = strtolower($attribs['font-family']);

				if ($last_svg_fontdefw) {
					$tmp_svg_font['horiz-adv-x'] = $last_svg_fontdefw;
				} else {
					$tmp_svg_font['horiz-adv-x'] = 500;
				}

				$this->svg_font[$last_svg_fontid][$last_svg_fontstyle] = $tmp_svg_font;
			}

			return;
		} elseif (strtolower($name) == 'missing-glyph') { // mPDF 6

			if ($last_svg_fontid && isset($attribs['horiz-adv-x'])) {
				$this->svg_font[$last_svg_fontid][$last_svg_fontstyle]['horiz-adv-x'] = (isset($attribs['horiz-adv-x']) ? $attribs['horiz-adv-x'] : '');
				$this->svg_font[$last_svg_fontid][$last_svg_fontstyle]['d'] = (isset($attribs['d']) ? $attribs['d'] : '');
			}

			return;

		} elseif (strtolower($name) == 'glyph') { // mPDF 6

			if ($last_svg_fontid && isset($attribs['unicode'])) {
				$this->svg_font[$last_svg_fontid][$last_svg_fontstyle]['glyphs'][$attribs['unicode']] = [
					'horiz-adv-x' => (isset($attribs['horiz-adv-x']) ? $attribs['horiz-adv-x'] : $last_svg_fontdefw),
					'd' => (isset($attribs['d']) ? $attribs['d'] : ''),
				];
			}
			return;

		} elseif (strtolower($name) == 'lineargradient') { // mPDF 5.7.2

			$tmp_gradient = [
				'type' => 'linear',
				'transform' => (isset($attribs['gradientTransform']) ? $attribs['gradientTransform'] : ''),
				'units' => (isset($attribs['gradientUnits']) ? $attribs['gradientUnits'] : ''),
				'spread' => (isset($attribs['spreadMethod']) ? $attribs['spreadMethod'] : ''),
				'color' => []
			];

			if (isset($attribs['x1'])) {
				$tmp_gradient['info']['x1'] = $attribs['x1'];
			}

			if (isset($attribs['y1'])) {
				$tmp_gradient['info']['y1'] = $attribs['y1'];
			}

			if (isset($attribs['x2'])) {
				$tmp_gradient['info']['x2'] = $attribs['x2'];
			}

			if (isset($attribs['y2'])) {
				$tmp_gradient['info']['y2'] = $attribs['y2'];
			}

			$last_gradid = $attribs['id'];
			$this->svgAddGradient($attribs['id'], $tmp_gradient);

			return;

		} elseif (strtolower($name) == 'radialgradient') {

			$tmp_gradient = [
				'type' => 'radial',
				'transform' => (isset($attribs['gradientTransform']) ? $attribs['gradientTransform'] : ''),
				'units' => (isset($attribs['gradientUnits']) ? $attribs['gradientUnits'] : ''),
				'spread' => (isset($attribs['spreadMethod']) ? $attribs['spreadMethod'] : ''),
				'color' => []
			];

			if (isset($attribs['cx'])) {
				$tmp_gradient['info']['x0'] = $attribs['cx'];
			}

			if (isset($attribs['cy'])) {
				$tmp_gradient['info']['y0'] = $attribs['cy'];
			}

			if (isset($attribs['fx'])) {
				$tmp_gradient['info']['x1'] = $attribs['fx'];
			}

			if (isset($attribs['fy'])) {
				$tmp_gradient['info']['y1'] = $attribs['fy'];
			}

			if (isset($attribs['r'])) {
				$tmp_gradient['info']['r'] = $attribs['r'];
			}

			$last_gradid = $attribs['id'];
			$this->svgAddGradient($attribs['id'], $tmp_gradient);

			return;

		} elseif (strtolower($name) == 'stop') {

			if (!$last_gradid) {
				return;
			}

			$color = '#000000';
			if (isset($attribs['style']) and preg_match('/stop-color:\s*([^;]*)/i', $attribs['style'], $m)) {
				$color = trim($m[1]);
			} elseif (isset($attribs['stop-color']) && $attribs['stop-color']) {
				$color = $attribs['stop-color'];
			}

			$col = $this->colorConverter->convert($color, $this->mpdf->PDFAXwarnings);
			if (!$col) {
				$col = $this->colorConverter->convert('#000000', $this->mpdf->PDFAXwarnings);
			} // In case "transparent" or "inherit" returned

			if ($col[0] == 3 || $col[0] == 5) { // RGB
				$color_final = sprintf('%.3F %.3F %.3F', ord($col[1]) / 255, ord($col[2]) / 255, ord($col[3]) / 255);
				$this->svg_gradient[$last_gradid]['colorspace'] = 'RGB';
			} elseif ($col[0] == 4 || $col[0] == 6) { // CMYK
				$color_final = sprintf('%.3F %.3F %.3F %.3F', ord($col[1]) / 100, ord($col[2]) / 100, ord($col[3]) / 100, ord($col[4]) / 100);
				$this->svg_gradient[$last_gradid]['colorspace'] = 'CMYK';
			} elseif ($col[0] == 1) { // Grayscale
				$color_final = sprintf('%.3F', ord($col[1]) / 255);
				$this->svg_gradient[$last_gradid]['colorspace'] = 'Gray';
			}

			$stop_opacity = 1;

			if (isset($attribs['style']) and preg_match('/stop-opacity:\s*([0-9.]*)/i', $attribs['style'], $m)) {
				$stop_opacity = $m[1];
			} elseif (isset($attribs['stop-opacity'])) {
				$stop_opacity = $attribs['stop-opacity'];
			} elseif ($col[0] == 5) { // RGBa
				$stop_opacity = ord($col[4] / 100);
			} elseif ($col[0] == 6) { // CMYKa
				$stop_opacity = ord($col[5] / 100);
			}

			$tmp_color = [
				'color' => $color_final,
				'offset' => (isset($attribs['offset']) ? $attribs['offset'] : ''),
				'opacity' => $stop_opacity
			];
			array_push($this->svg_gradient[$last_gradid]['color'], $tmp_color);

			return;
		}

		if ($this->inDefs) {
			return;
		}

		$this->xbase = 0;
		$this->ybase = 0;

		switch (strtolower($name)) {

			// Don't output stuff inside <defs>
			case 'defs':
				$this->inDefs = true;
				return;

			case 'svg':
				$this->svgOffset($attribs);
				break;

			case 'path':

				$path = Arrays::get($attribs, 'd', '');
				preg_match_all('/([MZLHVCSQTAmzlhvcsqta])([eE ,\-.\d]+)*/', $path, $commands, PREG_SET_ORDER);
				$path_cmd = '';
				$this->subPathInit = true;
				$this->pathBBox = [999999, 999999, -999999, -999999];

				foreach ($commands as $c) {
					if ((isset($c) && count($c) == 3) || (isset($c[2]) && $c[2] == '')) {
						list($tmp, $command, $arguments) = $c;
					} else {
						list($tmp, $command) = $c;
						$arguments = '';
					}

					$path_cmd .= $this->svgPath($command, $arguments);
				}

				if ($this->pathBBox[2] == -1999998) {
					$this->pathBBox[2] = 100;
				}

				if ($this->pathBBox[3] == -1999998) {
					$this->pathBBox[3] = 100;
				}

				if ($this->pathBBox[0] == 999999) {
					$this->pathBBox[0] = 0;
				}

				if ($this->pathBBox[1] == 999999) {
					$this->pathBBox[1] = 0;
				}

				$critere_style = $attribs;
				unset($critere_style['d']);
				$path_style = $this->svgDefineStyle($critere_style);

				break;

			case 'rect':
				if (!isset($attribs['x'])) {
					$attribs['x'] = 0;
				}

				if (!isset($attribs['y'])) {
					$attribs['y'] = 0;
				}

				if (!isset($attribs['rx'])) {
					$attribs['rx'] = 0;
				}

				if (!isset($attribs['ry'])) {
					$attribs['ry'] = 0;
				}

				$arguments = [];

				if (isset($attribs['x'])) {
					$arguments['x'] = $attribs['x'];
				}

				if (isset($attribs['y'])) {
					$arguments['y'] = $attribs['y'];
				}

				if (isset($attribs['width'])) {
					$arguments['w'] = $attribs['width'];
				}

				if (isset($attribs['height'])) {
					$arguments['h'] = $attribs['height'];
				}

				if (isset($attribs['rx'])) {
					$arguments['rx'] = $attribs['rx'];
				}

				if (isset($attribs['ry'])) {
					$arguments['ry'] = $attribs['ry'];
				}

				$path_cmd = $this->svgRect($arguments);
				$critere_style = $attribs;
				unset($critere_style['x'], $critere_style['y'], $critere_style['rx'], $critere_style['ry'], $critere_style['height'], $critere_style['width']);
				$path_style = $this->svgDefineStyle($critere_style);

				break;

			case 'circle':

				if (!isset($attribs['cx'])) {
					$attribs['cx'] = 0;
				}

				if (!isset($attribs['cy'])) {
					$attribs['cy'] = 0;
				}

				$arguments = [];

				if (isset($attribs['cx'])) {
					$arguments['cx'] = $attribs['cx'];
				}

				if (isset($attribs['cy'])) {
					$arguments['cy'] = $attribs['cy'];
				}

				if (isset($attribs['r'])) {
					$arguments['rx'] = $attribs['r'];
				}

				if (isset($attribs['r'])) {
					$arguments['ry'] = $attribs['r'];
				}

				$path_cmd = $this->svgEllipse($arguments);
				$critere_style = $attribs;
				unset($critere_style['cx'], $critere_style['cy'], $critere_style['r']);
				$path_style = $this->svgDefineStyle($critere_style);

				break;

			case 'ellipse':

				if (!isset($attribs['cx'])) {
					$attribs['cx'] = 0;
				}

				if (!isset($attribs['cy'])) {
					$attribs['cy'] = 0;
				}

				$arguments = [];

				if (isset($attribs['cx'])) {
					$arguments['cx'] = $attribs['cx'];
				}

				if (isset($attribs['cy'])) {
					$arguments['cy'] = $attribs['cy'];
				}

				if (isset($attribs['rx'])) {
					$arguments['rx'] = $attribs['rx'];
				}

				if (isset($attribs['ry'])) {
					$arguments['ry'] = $attribs['ry'];
				}

				$path_cmd = $this->svgEllipse($arguments);
				$critere_style = $attribs;
				unset($critere_style['cx'], $critere_style['cy'], $critere_style['rx'], $critere_style['ry']);
				$path_style = $this->svgDefineStyle($critere_style);

				break;

			case 'line':

				$arguments = [];
				$arguments[0] = (isset($attribs['x1']) ? $attribs['x1'] : '');
				$arguments[1] = (isset($attribs['y1']) ? $attribs['y1'] : '');
				$arguments[2] = (isset($attribs['x2']) ? $attribs['x2'] : '');
				$arguments[3] = (isset($attribs['y2']) ? $attribs['y2'] : '');
				$path_cmd = $this->svgPolyline($arguments, false);
				$critere_style = $attribs;
				unset($critere_style['x1'], $critere_style['y1'], $critere_style['x2'], $critere_style['y2']);
				$path_style = $this->svgDefineStyle($critere_style);

				break;

			case 'polyline':

				$path = $attribs['points'];
				preg_match_all('/[0-9\-\.]*/', $path, $tmp, PREG_SET_ORDER);
				$arguments = [];

				for ($i = 0; $i < count($tmp); $i++) {
					if ($tmp[$i][0] != '') {
						array_push($arguments, $tmp[$i][0]);
					}
				}

				$path_cmd = $this->svgPolyline($arguments);
				$critere_style = $attribs;
				unset($critere_style['points']);
				$path_style = $this->svgDefineStyle($critere_style);

				break;

			case 'polygon':

				$path = $attribs['points'];
				preg_match_all('/([\-]*[0-9\.]+)/', $path, $tmp);
				$arguments = [];

				for ($i = 0; $i < count($tmp[0]); $i++) {
					if ($tmp[0][$i] != '') {
						array_push($arguments, $tmp[0][$i]);
					}
				}

				$path_cmd = $this->svgPolygon($arguments);
				//	definition du style de la forme:
				$critere_style = $attribs;
				unset($critere_style['points']);
				$path_style = $this->svgDefineStyle($critere_style);

				break;

			// mPDF 5.7.4 Embedded image
			case 'image':

				if (isset($attribs['xlink:href']) && $attribs['xlink:href']) {
					$this->svgImage($attribs);
				}

				break;

			case 'a':

				if (isset($attribs['xlink:href'])) {
					unset($attribs['xlink:href']); // this should be a hyperlink
					// not handled like a xlink:href in other elements
				}

				// fallthtough - then continue like a <g>

			case 'g':

				$array_style = $this->svgDefineStyle($attribs);

				if (!empty($array_style['transformations'])) {
					// If in the middle of <text> element, add to textoutput, else WriteString
					if ($this->intext) {
						$this->textoutput .= ' q ' . $array_style['transformations'];
					} else { // mPDF 5.7.4
						$this->svgWriteString(' q ' . $array_style['transformations']);
					}
				}

				array_push($this->svg_style, $array_style);

				$this->svgDefineTxtStyle($attribs);

				break;

			case 'text':

				$this->textlength = 0;  // mPDF 5.7.4
				$this->texttotallength = 0; // mPDF 5.7.4
				$this->textoutput = '';  // mPDF 5.7.4
				$this->textanchor = 'start'; // mPDF 5.7.4
				$this->textXorigin = 0;  // mPDF 5.7.4
				$this->textYorigin = 0;  // mPDF 5.7.4

				$this->intext = true;   // mPDF 5.7.4

				$styl = '';

				if ($this->mpdf->svgClasses && isset($attribs['class']) && $attribs['class']) {
					$classes = preg_split('/\s+/', trim($attribs['class']));
					foreach ($classes as $class) {
						if (isset($this->cssManager->CSS['CLASS>>' . strtoupper($class)])) {
							$c = $this->cssManager->CSS['CLASS>>' . strtoupper($class)];
							foreach ($c as $prop => $val) {
								$styl .= strtolower($prop) . ':' . $val . ';';
							}
						}
					}
				}

				if ($this->mpdf->svgAutoFont && isset($attribs['lang']) && $attribs['lang']) {
					if (!$this->mpdf->usingCoreFont) {
						if ($attribs['lang'] != $this->mpdf->default_lang) {
							list ($coreSuitable, $mpdf_unifont) = $this->languageToFont->getLanguageOptions($attribs['lang'], $this->mpdf->useAdobeCJK);
							if ($mpdf_unifont) {
								$styl .= 'font-family:' . $mpdf_unifont . ';';
							}
						}
					}
				}

				if ($styl) {
					if (isset($attribs['style'])) {
						$attribs['style'] = $styl . $attribs['style'];
					} else {
						$attribs['style'] = $styl;
					}
				}

				$array_style = $this->svgDefineStyle($attribs);
				if (!empty($array_style['transformations'])) {
					$this->textoutput .= ' q ' . $array_style['transformations']; // mPDF 5.7.4
				}
				array_push($this->svg_style, $array_style);

				$this->txt_data = [];
				$x = isset($attribs['x']) ? $this->ConvertSVGSizePixels($attribs['x'], 'x') : 0;  // mPDF 5.7.4
				$y = isset($attribs['y']) ? $this->ConvertSVGSizePixels($attribs['y'], 'y') : 0;  // mPDF 5.7.4
				$x += isset($attribs['dx']) ? $this->ConvertSVGSizePixels($attribs['dx'], 'x') : 0;  // mPDF 5.7.4
				$y += isset($attribs['dy']) ? $this->ConvertSVGSizePixels($attribs['dy'], 'y') : 0;  // mPDF 5.7.4

				$this->txt_data[0] = $x; // mPDF 5.7.4
				$this->txt_data[1] = $y; // mPDF 5.7.4
				$critere_style = $attribs;
				unset($critere_style['x'], $critere_style['y']);
				$this->svgDefineTxtStyle($critere_style);

				$this->textanchor = $this->txt_style[count($this->txt_style) - 1]['text-anchor']; // mPDF 5.7.4
				$this->textXorigin = $this->txt_data[0];  // mPDF 5.7.4
				$this->textYorigin = $this->txt_data[1];  // mPDF 5.7.4
				$this->textjuststarted = true;  // mPDF 5.7.4

				break;

			// mPDF 5.7.4
			case 'tspan':

				// OUTPUT CHUNK(s) UP To NOW (svgText updates $this->textlength)
				$p_cmd = $this->svgText();
				$this->textoutput .= $p_cmd;
				$tmp = count($this->svg_style) - 1;
				$current_style = $this->svg_style[$tmp];

				$styl = '';
				if ($this->mpdf->svgClasses && isset($attribs['class']) && $attribs['class']) {
					$classes = preg_split('/\s+/', trim($attribs['class']));
					foreach ($classes as $class) {
						if (isset($this->cssManager->CSS['CLASS>>' . strtoupper($class)])) {
							$c = $this->cssManager->CSS['CLASS>>' . strtoupper($class)];
							foreach ($c as $prop => $val) {
								$styl .= strtolower($prop) . ':' . $val . ';';
							}
						}
					}
				}

				if ($this->mpdf->svgAutoFont && isset($attribs['lang']) && $attribs['lang']) {
					if (!$this->mpdf->usingCoreFont) {
						if ($attribs['lang'] != $this->mpdf->default_lang) {
							list ($coreSuitable, $mpdf_unifont) = $this->languageToFont->getLanguageOptions($attribs['lang'], $this->mpdf->useAdobeCJK);
							if ($mpdf_unifont) {
								$styl .= 'font-family:' . $mpdf_unifont . ';';
							}
						}
					}
				}

				if ($styl) {
					if (isset($attribs['style'])) {
						$attribs['style'] = $styl . $attribs['style'];
					} else {
						$attribs['style'] = $styl;
					}
				}

				$array_style = $this->svgDefineStyle($attribs);

				$this->txt_data = [];


				// If absolute position adjustment (x or y), creates new block of text for text-alignment
				if (isset($attribs['x']) || isset($attribs['y'])) {
					// If text-anchor middle|end, adjust
					if ($this->textanchor == 'end') {
						$tx = -$this->texttotallength;
					} elseif ($this->textanchor == 'middle') {
						$tx = -$this->texttotallength / 2;
					} else {
						$tx = 0;
					}
					while (preg_match('/mPDF-AXS\((.*?)\)/', $this->textoutput, $m)) {
						if ($tx) {
							$txk = $m[1] + ($tx * $this->kp);
							$this->textoutput = preg_replace('/mPDF-AXS\((.*?)\)/', sprintf('%.4F', $txk), $this->textoutput, 1);
						} else {
							$this->textoutput = preg_replace('/mPDF-AXS\((.*?)\)/', '\\1', $this->textoutput, 1);
						}
					}

					$this->svgWriteString($this->textoutput);

					$this->textXorigin += $this->textlength;
					$currentX = $this->textXorigin;
					$currentY = $this->textYorigin;
					$this->textlength = 0;
					$this->texttotallength = 0;
					$this->textoutput = '';

					$x = isset($attribs['x']) ? $this->ConvertSVGSizePixels($attribs['x'], 'x') : $currentX;
					$y = isset($attribs['y']) ? $this->ConvertSVGSizePixels($attribs['y'], 'y') : $currentY;

					$this->txt_data[0] = $x;
					$this->txt_data[1] = $y;
					$critere_style = $attribs;
					unset($critere_style['x'], $critere_style['y']);
					$this->svgDefineTxtStyle($critere_style);

					$this->textanchor = $this->txt_style[count($this->txt_style) - 1]['text-anchor'];
					$this->textXorigin = $x;
					$this->textYorigin = $y;
				} else {
					$this->textXorigin += $this->textlength;
					$currentX = $this->textXorigin;
					$currentY = $this->textYorigin;

					$currentX += isset($attribs['dx']) ? $this->ConvertSVGSizePixels($attribs['dx'], 'x') : 0;
					$currentY += isset($attribs['dy']) ? $this->ConvertSVGSizePixels($attribs['dy'], 'y') : 0;

					$this->txt_data[0] = $currentX;
					$this->txt_data[1] = $currentY;
					$critere_style = $attribs;
					unset($critere_style['x'], $critere_style['y']);
					$this->svgDefineTxtStyle($critere_style);
					$this->textXorigin = $currentX;
					$this->textYorigin = $currentY;
				}

				if (!empty($array_style['transformations'])) {
					$this->textoutput .= ' q ' . $array_style['transformations'];
				}
				array_push($this->svg_style, $array_style);

				break;
		}

		// insertion des path et du style dans le flux de donné general.
		if (isset($path_cmd) && $path_cmd) {
			// mPDF 5.0
			list($prestyle, $poststyle) = $this->svgStyle($path_style, $attribs, strtolower($name));
			if (isset($path_style['transformations']) && $path_style['transformations']) { // transformation on an element
				$this->svgWriteString(" q " . $path_style['transformations'] . $prestyle . $path_cmd . $poststyle . " Q\n");
			} else {
				$this->svgWriteString(" q " . $prestyle . $path_cmd . $poststyle . " Q\n"); // mPDF 5.7.4
			}
		}
	}

	function characterData($parser, $data)
	{
		if ($this->inDefs) {
			return;
		}  // mPDF 5.7.2

		if (isset($this->txt_data[2])) {
			$this->txt_data[2] .= $data;
		} else {
			$this->txt_data[2] = $data;
			$this->txt_data[0] = $this->textXorigin;
			$this->txt_data[1] = $this->textYorigin;
		}
	}

	function xml_svg2pdf_end($parser, $name)
	{
		// mPDF 5.7.2
		// Don't output stuff inside <defs>
		if ($name == 'defs') {
			$this->inDefs = false;
			return;
		}

		if ($this->inDefs) {
			return;
		}

		switch ($name) {
			case "g":
			case "a":
				if ($this->intext) {
					$p_cmd = $this->svgText();
					$this->textoutput .= $p_cmd;
				}

				$tmp = count($this->svg_style) - 1;
				$current_style = $this->svg_style[$tmp];
				if (!empty($current_style['transformations'])) {
					// If in the middle of <text> element, add to textoutput, else WriteString
					if ($this->intext) {
						$this->textoutput .= " Q\n";
					} // mPDF 5.7.4
					else {
						$this->svgWriteString(" Q\n");
					}
				}

				array_pop($this->svg_style);
				array_pop($this->txt_style);

				if ($this->intext) {
					$this->textXorigin += $this->textlength;
					$this->textlength = 0;
				}

				break;

			case 'font':
				$last_svg_fontdefw = '';
				break;

			case 'font-face':
				$last_svg_fontid = '';
				$last_svg_fontstyle = '';
				break;

			case 'radialgradient':
			case 'lineargradient':
				$last_gradid = '';
				break;

			case 'text':
				if (!empty($this->txt_data[2])) {
					$this->txt_data[2] = rtrim($this->txt_data[2]); // mPDF 5.7.4
				}

				$path_cmd = $this->svgText();
				$this->textoutput .= $path_cmd; // mPDF 5.7.4
				$tmp = count($this->svg_style) - 1;
				$current_style = $this->svg_style[$tmp];

				if (!empty($current_style['transformations'])) {
					$this->textoutput .= " Q\n"; // mPDF 5.7.4
				}
				array_pop($this->svg_style);
				array_pop($this->txt_style); // mPDF 5.7.4

				// mPDF 5.7.4
				// If text-anchor middle|end, adjust
				if ($this->textanchor == 'end') {
					$tx = -$this->texttotallength;
				} elseif ($this->textanchor == 'middle') {
					$tx = -$this->texttotallength / 2;
				} else {
					$tx = 0;
				}
				while (preg_match('/mPDF-AXS\((.*?)\)/', $this->textoutput, $m)) {
					if ($tx) {
						$txk = $m[1] + ($tx * $this->kp);
						$this->textoutput = preg_replace('/mPDF-AXS\((.*?)\)/', sprintf('%.4F', $txk), $this->textoutput, 1);
					} else {
						$this->textoutput = preg_replace('/mPDF-AXS\((.*?)\)/', '\\1', $this->textoutput, 1);
					}
				}

				$this->svgWriteString($this->textoutput);
				$this->textlength = 0;
				$this->texttotallength = 0;
				$this->textoutput = '';
				$this->intext = false;   // mPDF 5.7.4

				break;
			// mPDF 5.7.4
			case "tspan":
				$p_cmd = $this->svgText();
				$this->textoutput .= $p_cmd;
				$tmp = count($this->svg_style) - 1;
				$current_style = $this->svg_style[$tmp];
				if (!empty($current_style['transformations'])) {
					$this->textoutput .= " Q\n";
				}
				array_pop($this->svg_style);
				array_pop($this->txt_style);

				$this->textXorigin += $this->textlength;
				$this->textlength = 0;

				break;
		}
	}

	private function computeBezierBoundingBox($start, $c)
	{
		$P0 = [$start[0], $start[1]];
		$P1 = [$c[0], $c[1]];
		$P2 = [$c[2], $c[3]];
		$P3 = [$c[4], $c[5]];
		$bounds = [];
		$bounds[0][] = $P0[0];
		$bounds[1][] = $P0[1];
		$bounds[0][] = $P3[0];
		$bounds[1][] = $P3[1];

		for ($i = 0; $i <= 1; $i++) {
			$b = 6 * $P0[$i] - 12 * $P1[$i] + 6 * $P2[$i];
			$a = -3 * $P0[$i] + 9 * $P1[$i] - 9 * $P2[$i] + 3 * $P3[$i];
			$c = 3 * $P1[$i] - 3 * $P0[$i];

			if ($a == 0) {

				if ($b == 0) {
					continue;
				}

				$t = -$c / $b;

				if ($t > 0 && $t < 1) {
					$bounds[$i][] = (pow((1 - $t), 3) * $P0[$i] + 3 * pow((1 - $t), 2) * $t * $P1[$i] + 3 * (1 - $t) * pow($t, 2) * $P2[$i] + pow($t, 3) * $P3[$i]);
				}

				continue;
			}

			$b2ac = pow($b, 2) - 4 * $c * $a;

			if ($b2ac < 0) {
				continue;
			}

			$t1 = (-$b + sqrt($b2ac)) / (2 * $a);

			if ($t1 > 0 && $t1 < 1) {
				$bounds[$i][] = (pow((1 - $t1), 3) * $P0[$i] + 3 * pow((1 - $t1), 2) * $t1 * $P1[$i] + 3 * (1 - $t1) * pow($t1, 2) * $P2[$i] + pow($t1, 3) * $P3[$i]);
			}

			$t2 = (-$b - sqrt($b2ac)) / (2 * $a);

			if ($t2 > 0 && $t2 < 1) {
				$bounds[$i][] = (pow((1 - $t2), 3) * $P0[$i] + 3 * pow((1 - $t2), 2) * $t2 * $P1[$i] + 3 * (1 - $t2) * pow($t2, 2) * $P2[$i] + pow($t2, 3) * $P3[$i]);
			}
		}

		$x = min($bounds[0]);
		$x2 = max($bounds[0]);
		$y = min($bounds[1]);
		$y2 = max($bounds[1]);

		return [$x, $y, $x2, $y2];
	}

	private function testIntersectCircle($cx, $cy, $cr)
	{
		// Tests whether a circle fully encloses a rectangle 0,0,1,1
		// to see if any further radial gradients need adding (SVG)
		// If centre of circle is inside 0,0,1,1 square
		if ($cx >= 0 && $cx <= 1 && $cy >= 0 && $cy <= 1) {
			$maxd = 1.5;
		} else {  // distance to four corners
			$d1 = sqrt(pow(($cy - 0), 2) + pow(($cx - 0), 2));
			$d2 = sqrt(pow(($cy - 1), 2) + pow(($cx - 0), 2));
			$d3 = sqrt(pow(($cy - 0), 2) + pow(($cx - 1), 2));
			$d4 = sqrt(pow(($cy - 1), 2) + pow(($cx - 1), 2));
			$maxd = max($d1, $d2, $d3, $d4);
		}

		return $cr < $maxd;
	}

	private function testIntersect($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4)
	{
		// Tests whether line (x1, y1) and (x2, y2) [a gradient axis (perpendicular)]
		// intersects with a specific line segment (x3, y3) and (x4, y4)
		$a1 = $y2 - $y1;
		$b1 = $x1 - $x2;
		$c1 = $a1 * $x1 + $b1 * $y1;
		$a2 = $y4 - $y3;
		$b2 = $x3 - $x4;
		$c2 = $a2 * $x3 + $b2 * $y3;
		$det = $a1 * $b2 - $a2 * $b1;

		if ($det == 0) { //Lines are parallel
			return false;
		} else {
			$x = ($b2 * $c1 - $b1 * $c2) / $det;
			$y = ($a1 * $c2 - $a2 * $c1) / $det;
			if ($x >= $x3 && $x <= $x4 && $y >= $y3 && $y <= $y4) {
				return true;
			}
		}

		return false;
	}

}
