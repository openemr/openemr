<?php

namespace Mpdf\Image;

use Mpdf\Cache;
use Mpdf\Color\ColorConverter;
use Mpdf\Color\ColorModeConverter;
use Mpdf\CssManager;
use Mpdf\Gif\Gif;
use Mpdf\Language\LanguageToFontInterface;
use Mpdf\Language\ScriptToLanguageInterface;
use Mpdf\Log\Context as LogContext;
use Mpdf\Mpdf;
use Mpdf\Otl;
use Mpdf\SizeConverter;

use Psr\Log\LoggerInterface;

class ImageProcessor implements \Psr\Log\LoggerAwareInterface
{

	/**
	 * @var \Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \Mpdf\Otl
	 */
	private $otl;

	/**
	 * @var \Mpdf\CssManager
	 */
	private $cssManager;

	/**
	 * @var \Mpdf\SizeConverter
	 */
	private $sizeConverter;

	/**
	 * @var \Mpdf\Color\ColorConverter
	 */
	private $colorConverter;

	/**
	 * @var \Mpdf\Color\ColorModeConverter
	 */
	private $colorModeConverter;

	/**
	 * @var \Mpdf\Cache
	 */
	private $cache;

	/**
	 * @var \Mpdf\Image\ImageTypeGuesser
	 */
	private $guesser;

	/**
	 * @var string[]
	 */
	private $failedImages;

	/**
	 * @var \Mpdf\Image\Bmp
	 */
	private $bmp;

	/**
	 * @var \Mpdf\Image\Wmf
	 */
	private $wmf;

	/**
	 * @var \Mpdf\Language\LanguageToFontInterface
	 */
	private $languageToFont;

	/**
	 * @var \Mpdf\Language\ScriptToLanguageInterface
	 */
	public $scriptToLanguage;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	public $logger;

	public function __construct(
		Mpdf $mpdf,
		Otl $otl,
		CssManager $cssManager,
		SizeConverter $sizeConverter,
		ColorConverter $colorConverter,
		ColorModeConverter $colorModeConverter,
		Cache $cache,
		LanguageToFontInterface $languageToFont,
		ScriptToLanguageInterface $scriptToLanguage,
		LoggerInterface $logger
	) {

		$this->mpdf = $mpdf;
		$this->otl = $otl;
		$this->cssManager = $cssManager;
		$this->sizeConverter = $sizeConverter;
		$this->colorConverter = $colorConverter;
		$this->colorModeConverter = $colorModeConverter;
		$this->cache = $cache;
		$this->languageToFont = $languageToFont;
		$this->scriptToLanguage = $scriptToLanguage;

		$this->logger = $logger;

		$this->guesser = new ImageTypeGuesser();

		$this->failedImages = [];
	}

	/**
	 * @param \Psr\Log\LoggerInterface
	 *
	 * @return \Mpdf\Mpdf
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;

		return $this;
	}

	public function getImage(&$file, $firsttime = true, $allowvector = true, $orig_srcpath = false, $interpolation = false)
	{
		// mPDF 6
		// firsttime i.e. whether to add to this->images - use false when calling iteratively
		// Image Data passed directly as var:varname
		if (preg_match('/var:\s*(.*)/', $file, $v)) {
			if (!isset($this->mpdf->imageVars[$v[1]])) {
				return $this->imageError($file, $firsttime, 'Unknown image variable');
			}
			$data = $this->mpdf->imageVars[$v[1]];
			$file = md5($data);
		}

		if (preg_match('/data:image\/(gif|jpeg|png);base64,(.*)/', $file, $v)) {
			$type = $v[1];
			$data = base64_decode($v[2]);
			$file = md5($data);
		}

		// mPDF 5.7.4 URLs
		if ($firsttime && $file && substr($file, 0, 5) != 'data:') {
			$file = str_replace(" ", "%20", $file);
		}
		if ($firsttime && $orig_srcpath) {
			// If orig_srcpath is a relative file path (and not a URL), then it needs to be URL decoded
			if (substr($orig_srcpath, 0, 5) != 'data:') {
				$orig_srcpath = str_replace(" ", "%20", $orig_srcpath);
			}
			if (!preg_match('/^(http|ftp)/', $orig_srcpath)) {
				$orig_srcpath = $this->urldecodeParts($orig_srcpath);
			}
		}

		$ppUx = 0;
		if ($orig_srcpath && isset($this->mpdf->images[$orig_srcpath])) {
			$file = $orig_srcpath;
			return $this->mpdf->images[$orig_srcpath];
		}

		if (isset($this->mpdf->images[$file])) {
			return $this->mpdf->images[$file];
		} elseif ($orig_srcpath && isset($this->mpdf->formobjects[$orig_srcpath])) {
			$file = $orig_srcpath;
			return $this->mpdf->formobjects[$file];
		} elseif (isset($this->mpdf->formobjects[$file])) {
			return $this->mpdf->formobjects[$file];
		} elseif ($firsttime && isset($this->failedImages[$file])) { // Save re-trying image URL's which have already failed
			return $this->imageError($file, $firsttime, '');
		}

		if (empty($data)) {

			$type = '';
			$data = '';

			if ($orig_srcpath && $this->mpdf->basepathIsLocal && $check = @fopen($orig_srcpath, "rb")) {
				fclose($check);
				$file = $orig_srcpath;
				$this->logger->debug(sprintf('Fetching (file_get_contents) content of file "%s" with local basepath', $file), ['context' => LogContext::REMOTE_CONTENT]);
				$data = file_get_contents($file);
				$type = $this->guesser->guess($data);
			}

			if (!$data && $check = @fopen($file, "rb")) {
				fclose($check);
				$this->logger->debug(sprintf('Fetching (file_get_contents) content of file "%s" with non-local basepath', $file), ['context' => LogContext::REMOTE_CONTENT]);
				$data = file_get_contents($file);
				$type = $this->guesser->guess($data);
			}

			if ((!$data || !$type) && function_exists('curl_init')) { // mPDF 5.7.4
				$this->mpdf->getFileContentsByCurl($file, $data);  // needs full url?? even on local (never needed for local)
				if ($data) {
					$type = $this->guesser->guess($data);
				}
			}

			if ((!$data || !$type) && !ini_get('allow_url_fopen')) { // only worth trying if remote file and !ini_get('allow_url_fopen')
				$this->mpdf->getFileContentsBySocket($file, $data); // needs full url?? even on local (never needed for local)
				if ($data) {
					$type = $this->guesser->guess($data);
				}
			}
		}

		if (!$data) {
			return $this->imageError($file, $firsttime, 'Could not find image file');
		}

		if (empty($type)) {
			$type = $this->guesser->guess($data);
		}

		if (($type == 'wmf' || $type == 'svg') && !$allowvector) {
			return $this->imageError($file, $firsttime, 'WMF or SVG image file not supported in this context');
		}

		// SVG
		if ($type == 'svg') {
			$svg = new Svg($this->mpdf, $this->otl, $this->cssManager, $this, $this->sizeConverter, $this->colorConverter, $this->languageToFont, $this->scriptToLanguage);
			$family = $this->mpdf->FontFamily;
			$style = $this->mpdf->FontStyle;
			$size = $this->mpdf->FontSizePt;
			$info = $svg->ImageSVG($data);
			// Restore font
			if ($family) {
				$this->mpdf->SetFont($family, $style, $size, false);
			}
			if (!$info) {
				return $this->imageError($file, $firsttime, 'Error parsing SVG file');
			}
			$info['type'] = 'svg';
			$info['i'] = count($this->mpdf->formobjects) + 1;
			$this->mpdf->formobjects[$file] = $info;

			return $info;
		}

		// JPEG
		if ($type == 'jpeg' || $type == 'jpg') {
			$hdr = $this->jpgHeaderFromString($data);
			if (!$hdr) {
				return $this->imageError($file, $firsttime, 'Error parsing JPG header');
			}
			$a = $this->jpgDataFromHeader($hdr);
			$channels = intval($a[4]);
			$j = strpos($data, 'JFIF');
			if ($j) {
				//Read resolution
				$unitSp = ord(substr($data, ($j + 7), 1));
				if ($unitSp > 0) {
					$ppUx = $this->twoBytesToInt(substr($data, ($j + 8), 2)); // horizontal pixels per meter, usually set to zero
					if ($unitSp == 2) { // = dots per cm (if == 1 set as dpi)
						$ppUx = round($ppUx / 10 * 25.4);
					}
				}
			}
			if ($a[2] == 'DeviceCMYK' && (($this->mpdf->PDFA && $this->mpdf->restrictColorSpace != 3) || $this->mpdf->restrictColorSpace == 2)) {

				// convert to RGB image
				if (!function_exists("gd_info")) {
					throw new \Mpdf\MpdfException("JPG image may not use CMYK color space (" . $file . ").");
				}

				if ($this->mpdf->PDFA && !$this->mpdf->PDFAauto) {
					$this->mpdf->PDFAXwarnings[] = "JPG image may not use CMYK color space - " . $file . " - (Image converted to RGB. NB This will alter the colour profile of the image.)";
				}

				$im = @imagecreatefromstring($data);
				if ($im) {
					$tempfile = $this->cache->tempFilename('_tempImgPNG' . md5($file) . random_int(1, 10000) . '.png');
					imageinterlace($im, false);
					$check = @imagepng($im, $tempfile);
					if (!$check) {
						return $this->imageError($file, $firsttime, 'Error creating temporary file (' . $tempfile . ') whilst using GD library to parse JPG(CMYK) image');
					}
					$info = $this->getImage($tempfile, false);
					if (!$info) {
						return $this->imageError($file, $firsttime, 'Error parsing temporary file (' . $tempfile . ') created with GD library to parse JPG(CMYK) image');
					}
					imagedestroy($im);
					unlink($tempfile);
					$info['type'] = 'jpg';
					if ($firsttime) {
						$info['i'] = count($this->mpdf->images) + 1;
						$info['interpolation'] = $interpolation; // mPDF 6
						$this->mpdf->images[$file] = $info;
					}
					return $info;
				} else {
					return $this->imageError($file, $firsttime, 'Error creating GD image file from JPG(CMYK) image');
				}

			} elseif ($a[2] == 'DeviceRGB' && ($this->mpdf->PDFX || $this->mpdf->restrictColorSpace == 3)) {
				// Convert to CMYK image stream - nominally returned as type='png'
				$info = $this->convImage($data, $a[2], 'DeviceCMYK', $a[0], $a[1], $ppUx, false);
				if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) {
					$this->mpdf->PDFAXwarnings[] = "JPG image may not use RGB color space - " . $file . " - (Image converted to CMYK. NB This will alter the colour profile of the image.)";
				}

			} elseif (($a[2] == 'DeviceRGB' || $a[2] == 'DeviceCMYK') && $this->mpdf->restrictColorSpace == 1) {
				// Convert to Grayscale image stream - nominally returned as type='png'
				$info = $this->convImage($data, $a[2], 'DeviceGray', $a[0], $a[1], $ppUx, false);

			} else {
				// mPDF 6 Detect Adobe APP14 Tag
				//$pos = strpos($data, "\xFF\xEE\x00\x0EAdobe\0");
				//if ($pos !== false) {
				//}
				// mPDF 6 ICC profile
				$offset = 0;
				$icc = [];
				while (($pos = strpos($data, "ICC_PROFILE\0", $offset)) !== false) {
					// get ICC sequence length
					$length = $this->twoBytesToInt(substr($data, ($pos - 2), 2)) - 16;
					$sn = max(1, ord($data[($pos + 12)]));
					$nom = max(1, ord($data[($pos + 13)]));
					$icc[($sn - 1)] = substr($data, ($pos + 14), $length);
					$offset = ($pos + 14 + $length);
				}
				// order and compact ICC segments
				if (count($icc) > 0) {
					ksort($icc);
					$icc = implode('', $icc);
					if (substr($icc, 36, 4) != 'acsp') {
						// invalid ICC profile
						$icc = false;
					}
					$input = substr($icc, 16, 4);
					$output = substr($icc, 20, 4);
					// Ignore Color profiles for conversion to other colorspaces e.g. CMYK/Lab
					if ($input != 'RGB ' || $output != 'XYZ ') {
						$icc = false;
					}
				} else {
					$icc = false;
				}

				$info = ['w' => $a[0], 'h' => $a[1], 'cs' => $a[2], 'bpc' => $a[3], 'f' => 'DCTDecode', 'data' => $data, 'type' => 'jpg', 'ch' => $channels, 'icc' => $icc];
				if ($ppUx) {
					$info['set-dpi'] = $ppUx;
				}
			}

			if (!$info) {
				return $this->imageError($file, $firsttime, 'Error parsing or converting JPG image');
			}

			if ($firsttime) {
				$info['i'] = count($this->mpdf->images) + 1;
				$info['interpolation'] = $interpolation; // mPDF 6
				$this->mpdf->images[$file] = $info;
			}

			return $info;
		} elseif ($type == 'png') {

			// Check signature
			if (substr($data, 0, 8) != chr(137) . 'PNG' . chr(13) . chr(10) . chr(26) . chr(10)) {
				return $this->imageError($file, $firsttime, 'Error parsing PNG identifier');
			}

			// Read header chunk
			if (substr($data, 12, 4) != 'IHDR') {
				return $this->imageError($file, $firsttime, 'Incorrect PNG file (no IHDR block found)');
			}

			$w = $this->fourBytesToInt(substr($data, 16, 4));
			$h = $this->fourBytesToInt(substr($data, 20, 4));
			$bpc = ord(substr($data, 24, 1));
			$errpng = false;
			$pngalpha = false;
			$channels = 0;

			//	if($bpc>8) { $errpng = 'not 8-bit depth'; }	// mPDF 6 Allow through to be handled as native PNG

			$ct = ord(substr($data, 25, 1));
			if ($ct == 0) {
				$colspace = 'DeviceGray';
				$channels = 1;
			} elseif ($ct == 2) {
				$colspace = 'DeviceRGB';
				$channels = 3;
			} elseif ($ct == 3) {
				$colspace = 'Indexed';
				$channels = 1;
			} elseif ($ct == 4) {
				$colspace = 'DeviceGray';
				$channels = 1;
				$errpng = 'alpha channel';
				$pngalpha = true;
			} else {
				$colspace = 'DeviceRGB';
				$channels = 3;
				$errpng = 'alpha channel';
				$pngalpha = true;
			}

			if ($ct < 4 && strpos($data, 'tRNS') !== false) {
				$errpng = 'transparency';
				$pngalpha = true;
			} // mPDF 6

			if ($ct == 3 && strpos($data, 'iCCP') !== false) {
				$errpng = 'indexed plus ICC';
			} // mPDF 6
			// $pngalpha is used as a FLAG of any kind of transparency which COULD be tranferred to an alpha channel
			// incl. single-color tarnsparency, depending which type of handling occurs later

			if (ord(substr($data, 26, 1)) != 0) {
				$errpng = 'compression method';
			} // only 0 should be specified
			if (ord(substr($data, 27, 1)) != 0) {
				$errpng = 'filter method';
			}  // only 0 should be specified
			if (ord(substr($data, 28, 1)) != 0) {
				$errpng = 'interlaced file';
			}

			$j = strpos($data, 'pHYs');
			if ($j) {
				//Read resolution
				$unitSp = ord(substr($data, ($j + 12), 1));
				if ($unitSp == 1) {
					$ppUx = $this->fourBytesToInt(substr($data, ($j + 4), 4)); // horizontal pixels per meter, usually set to zero
					$ppUx = round($ppUx / 1000 * 25.4);
				}
			}

			// mPDF 6 Gamma correction
			$gamma = 0;
			$gAMA = 0;
			$j = strpos($data, 'gAMA');
			if ($j && strpos($data, 'sRGB') === false) { // sRGB colorspace - overrides gAMA
				$gAMA = $this->fourBytesToInt(substr($data, ($j + 4), 4)); // Gamma value times 100000
				$gAMA /= 100000;

				// http://www.libpng.org/pub/png/spec/1.2/PNG-Encoders.html
				// "If the source file's gamma value is greater than 1.0, it is probably a display system exponent,..."
				// ("..and you should use its reciprocal for the PNG gamma.")
				//if ($gAMA > 1) { $gAMA = 1/$gAMA; }
				// (Some) Applications seem to ignore it... appearing how it was probably intended
				// Test Case - image(s) on http://www.w3.org/TR/CSS21/intro.html  - PNG has gAMA set as 1.45454
				// Probably unintentional as mentioned above and should be 0.45454 which is 1 / 2.2
				// Tested on Windows PC
				// Firefox and Opera display gray as 234 (correct, but looks wrong)
				// IE9 and Safari display gray as 193 (incorrect but looks right)
				// See test different gamma chunks at http://www.libpng.org/pub/png/pngsuite-all-good.html
			}

			if ($gAMA) {
				$gamma = 1 / $gAMA;
			}

			// Don't need to apply gamma correction if == default i.e. 2.2
			if ($gamma > 2.15 && $gamma < 2.25) {
				$gamma = 0;
			}

			// NOT supported at present
			//$j = strpos($data,'sRGB');	// sRGB colorspace - overrides gAMA
			//$j = strpos($data,'cHRM');	// Chromaticity and Whitepoint
			// $firsttime added mPDF 6 so when PNG Grayscale with alpha using resrtictcolorspace to CMYK
			// the alpha channel is sent through as secondtime as Indexed and should not be converted to CMYK
			if ($firsttime && ($colspace == 'DeviceRGB' || $colspace == 'Indexed') && ($this->mpdf->PDFX || $this->mpdf->restrictColorSpace == 3)) {
				// Convert to CMYK image stream - nominally returned as type='png'
				$info = $this->convImage($data, $colspace, 'DeviceCMYK', $w, $h, $ppUx, $pngalpha, $gamma, $ct); // mPDF 5.7.2 Gamma correction
				if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) {
					$this->mpdf->PDFAXwarnings[] = "PNG image may not use RGB color space - " . $file . " - (Image converted to CMYK. NB This will alter the colour profile of the image.)";
				}
			} // $firsttime added mPDF 6 so when PNG Grayscale with alpha using resrtictcolorspace to CMYK
			// the alpha channel is sent through as secondtime as Indexed and should not be converted to CMYK
			elseif ($firsttime && ($colspace == 'DeviceRGB' || $colspace == 'Indexed') && $this->mpdf->restrictColorSpace == 1) {
				// Convert to Grayscale image stream - nominally returned as type='png'
				$info = $this->convImage($data, $colspace, 'DeviceGray', $w, $h, $ppUx, $pngalpha, $gamma, $ct); // mPDF 5.7.2 Gamma correction
			} elseif (($this->mpdf->PDFA || $this->mpdf->PDFX) && $pngalpha) {
				// Remove alpha channel
				if ($this->mpdf->restrictColorSpace == 1) { // Grayscale
					$info = $this->convImage($data, $colspace, 'DeviceGray', $w, $h, $ppUx, $pngalpha, $gamma, $ct); // mPDF 5.7.2 Gamma correction
				} elseif ($this->mpdf->restrictColorSpace == 3) { // CMYK
					$info = $this->convImage($data, $colspace, 'DeviceCMYK', $w, $h, $ppUx, $pngalpha, $gamma, $ct); // mPDF 5.7.2 Gamma correction
				} elseif ($this->mpdf->PDFA) { // RGB
					$info = $this->convImage($data, $colspace, 'DeviceRGB', $w, $h, $ppUx, $pngalpha, $gamma, $ct); // mPDF 5.7.2 Gamma correction
				}
				if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) {
					$this->mpdf->PDFAXwarnings[] = "Transparency (alpha channel) not permitted in PDFA or PDFX files - " . $file . " - (Image converted to one without transparency.)";
				}
			} elseif ($firsttime && ($errpng || $pngalpha || $gamma)) { // mPDF 5.7.2 Gamma correction
				if (function_exists('gd_info')) {
					$gd = gd_info();
				} else {
					$gd = [];
				}
				if (!isset($gd['PNG Support'])) {
					return $this->imageError($file, $firsttime, 'GD library required for PNG image (' . $errpng . ')');
				}
				$im = imagecreatefromstring($data);

				if (!$im) {
					return $this->imageError($file, $firsttime, 'Error creating GD image from PNG file (' . $errpng . ')');
				}
				$w = imagesx($im);
				$h = imagesy($im);
				if ($im) {
					$tempfile =  $this->cache->tempFilename('_tempImgPNG' . md5($file) . random_int(1, 10000) . '.png');

					// Alpha channel set (including using tRNS for Paletted images)
					if ($pngalpha) {
						if ($this->mpdf->PDFA) {
							throw new \Mpdf\MpdfException("PDFA1-b does not permit images with alpha channel transparency (" . $file . ").");
						}

						$imgalpha = imagecreate($w, $h);
						// generate gray scale pallete
						for ($c = 0; $c < 256; ++$c) {
							imagecolorallocate($imgalpha, $c, $c, $c);
						}

						// mPDF 6
						if ($colspace == 'Indexed') { // generate Alpha channel values from tRNS
							// Read transparency info
							$p = strpos($data, 'tRNS');
							if ($p) {
								$n = $this->fourBytesToInt(substr($data, ($p - 4), 4));
								$transparency = substr($data, ($p + 4), $n);
								// ord($transparency{$index}) = the alpha value for that index
								// generate alpha channel
								for ($ypx = 0; $ypx < $h; ++$ypx) {
									for ($xpx = 0; $xpx < $w; ++$xpx) {
										$colorindex = imagecolorat($im, $xpx, $ypx);
										if ($colorindex >= $n) {
											$alpha = 255;
										} else {
											$alpha = ord($transparency{$colorindex});
										} // 0-255
										if ($alpha > 0) {
											imagesetpixel($imgalpha, $xpx, $ypx, $alpha);
										}
									}
								}
							}
						} elseif ($ct === 0 || $ct == 2) { // generate Alpha channel values from tRNS
							// Get transparency as array of RGB
							$p = strpos($data, 'tRNS');
							if ($p) {
								$trns = '';
								$n = $this->fourBytesToInt(substr($data, ($p - 4), 4));
								$t = substr($data, ($p + 4), $n);
								if ($colspace == 'DeviceGray') {  // ct===0
									$trns = [$this->translateValue(substr($t, 0, 2), $bpc)];
								} else /* $colspace=='DeviceRGB' */ {  // ct==2
									$trns = [];
									$trns[0] = $this->translateValue(substr($t, 0, 2), $bpc);
									$trns[1] = $this->translateValue(substr($t, 2, 2), $bpc);
									$trns[2] = $this->translateValue(substr($t, 4, 2), $bpc);
								}

								// generate alpha channel
								for ($ypx = 0; $ypx < $h; ++$ypx) {
									for ($xpx = 0; $xpx < $w; ++$xpx) {
										$rgb = imagecolorat($im, $xpx, $ypx);
										$r = ($rgb >> 16) & 0xFF;
										$g = ($rgb >> 8) & 0xFF;
										$b = $rgb & 0xFF;
										if ($colspace == 'DeviceGray' && $b == $trns[0]) {
											$alpha = 0;
										} elseif ($r == $trns[0] && $g == $trns[1] && $b == $trns[2]) {
											$alpha = 0;
										} // ct==2
										else {
											$alpha = 255;
										}
										if ($alpha > 0) {
											imagesetpixel($imgalpha, $xpx, $ypx, $alpha);
										}
									}
								}
							}
						} else {
							// extract alpha channel
							for ($ypx = 0; $ypx < $h; ++$ypx) {
								for ($xpx = 0; $xpx < $w; ++$xpx) {
									$alpha = (imagecolorat($im, $xpx, $ypx) & 0x7F000000) >> 24;
									if ($alpha < 127) {
										imagesetpixel($imgalpha, $xpx, $ypx, (255 - ($alpha * 2)));
									}
								}
							}
						}

						// NB This must happen after the Alpha channel is extracted
						// imagegammacorrect() removes the alpha channel data in $im - (I think this is a bug in PHP)
						if ($gamma) {
							imagegammacorrect($im, $gamma, 2.2);
						}

						$tempfile_alpha =  $this->cache->tempFilename('_tempMskPNG' . md5($file) . random_int(1, 10000) . '.png');

						$check = @imagepng($imgalpha, $tempfile_alpha);

						if (!$check) {
							return $this->imageError($file, $firsttime, 'Failed to create temporary image file (' . $tempfile_alpha . ') parsing PNG image with alpha channel (' . $errpng . ')');
						}

						imagedestroy($imgalpha);
						// extract image without alpha channel
						$imgplain = imagecreatetruecolor($w, $h);
						imagealphablending($imgplain, false); // mPDF 5.7.2
						imagecopy($imgplain, $im, 0, 0, 0, 0, $w, $h);

						// create temp image file
						$check = @imagepng($imgplain, $tempfile);
						if (!$check) {
							return $this->imageError($file, $firsttime, 'Failed to create temporary image file (' . $tempfile . ') parsing PNG image with alpha channel (' . $errpng . ')');
						}
						imagedestroy($imgplain);
						// embed mask image
						$minfo = $this->getImage($tempfile_alpha, false);
						unlink($tempfile_alpha);

						if (!$minfo) {
							return $this->imageError($file, $firsttime, 'Error parsing temporary file (' . $tempfile_alpha . ') created with GD library to parse PNG image');
						}

						$imgmask = count($this->mpdf->images) + 1;
						$minfo['cs'] = 'DeviceGray';
						$minfo['i'] = $imgmask;
						$this->mpdf->images[$tempfile_alpha] = $minfo;
						// embed image, masked with previously embedded mask
						$info = $this->getImage($tempfile, false);
						unlink($tempfile);

						if (!$info) {
							return $this->imageError($file, $firsttime, 'Error parsing temporary file (' . $tempfile . ') created with GD library to parse PNG image');
						}

						$info['masked'] = $imgmask;
						if ($ppUx) {
							$info['set-dpi'] = $ppUx;
						}
						$info['type'] = 'png';
						if ($firsttime) {
							$info['i'] = count($this->mpdf->images) + 1;
							$info['interpolation'] = $interpolation; // mPDF 6
							$this->mpdf->images[$file] = $info;
						}

						return $info;
					} else {  // No alpha/transparency set (but cannot read directly because e.g. bit-depth != 8, interlaced etc)
						// ICC profile
						$icc = false;
						$p = strpos($data, 'iCCP');
						if ($p && $colspace == "Indexed") { // Cannot have ICC profile and Indexed together
							$p += 4;
							$n = $this->fourBytesToInt(substr($data, ($p - 8), 4));
							$nullsep = strpos(substr($data, $p, 80), chr(0));
							$icc = substr($data, ($p + $nullsep + 2), ($n - ($nullsep + 2)));
							$icc = @gzuncompress($icc); // Ignored if fails
							if ($icc) {
								if (substr($icc, 36, 4) != 'acsp') {
									$icc = false;
								} // invalid ICC profile
								else {
									$input = substr($icc, 16, 4);
									$output = substr($icc, 20, 4);
									// Ignore Color profiles for conversion to other colorspaces e.g. CMYK/Lab
									if ($input != 'RGB ' || $output != 'XYZ ') {
										$icc = false;
									}
								}
							}
							// Convert to RGB colorspace so can use ICC Profile
							if ($icc) {
								imagepalettetotruecolor($im);
								$colspace = 'DeviceRGB';
								$channels = 3;
							}
						}

						if ($gamma) {
							imagegammacorrect($im, $gamma, 2.2);
						}

						imagealphablending($im, false);
						imagesavealpha($im, false);
						imageinterlace($im, false);

						$check = @imagepng($im, $tempfile);
						if (!$check) {
							return $this->imageError($file, $firsttime, 'Failed to create temporary image file (' . $tempfile . ') parsing PNG image (' . $errpng . ')');
						}
						imagedestroy($im);
						$info = $this->getImage($tempfile, false);
						unlink($tempfile);
						if (!$info) {
							return $this->imageError($file, $firsttime, 'Error parsing temporary file (' . $tempfile . ') created with GD library to parse PNG image');
						}

						if ($ppUx) {
							$info['set-dpi'] = $ppUx;
						}
						$info['type'] = 'png';
						if ($firsttime) {
							$info['i'] = count($this->mpdf->images) + 1;
							$info['interpolation'] = $interpolation; // mPDF 6
							if ($icc) {
								$info['ch'] = $channels;
								$info['icc'] = $icc;
							}
							$this->mpdf->images[$file] = $info;
						}
						return $info;
					}
				}
			} else { // PNG image with no need to convert alph channels, bpc <> 8 etc.
				$parms = '/DecodeParms <</Predictor 15 /Colors ' . $channels . ' /BitsPerComponent ' . $bpc . ' /Columns ' . $w . '>>';
				//Scan chunks looking for palette, transparency and image data
				$pal = '';
				$trns = '';
				$pngdata = '';
				$icc = false;
				$p = 33;
				do {
					$n = $this->fourBytesToInt(substr($data, $p, 4));
					$p += 4;
					$type = substr($data, $p, 4);
					$p += 4;
					if ($type == 'PLTE') {
						//Read palette
						$pal = substr($data, $p, $n);
						$p += $n;
						$p += 4;
					} elseif ($type == 'tRNS') {
						//Read transparency info
						$t = substr($data, $p, $n);
						$p += $n;
						if ($ct == 0) {
							$trns = [ord(substr($t, 1, 1))];
						} elseif ($ct == 2) {
							$trns = [ord(substr($t, 1, 1)), ord(substr($t, 3, 1)), ord(substr($t, 5, 1))];
						} else {
							$pos = strpos($t, chr(0));
							if (is_int($pos)) {
								$trns = [$pos];
							}
						}
						$p += 4;
					} elseif ($type == 'IDAT') {
						$pngdata.=substr($data, $p, $n);
						$p += $n;
						$p += 4;
					} elseif ($type == 'iCCP') {
						$nullsep = strpos(substr($data, $p, 80), chr(0));
						$icc = substr($data, ($p + $nullsep + 2), ($n - ($nullsep + 2)));
						$icc = @gzuncompress($icc); // Ignored if fails
						if ($icc) {
							if (substr($icc, 36, 4) != 'acsp') {
								$icc = false;
							} // invalid ICC profile
							else {
								$input = substr($icc, 16, 4);
								$output = substr($icc, 20, 4);
								// Ignore Color profiles for conversion to other colorspaces e.g. CMYK/Lab
								if ($input != 'RGB ' || $output != 'XYZ ') {
									$icc = false;
								}
							}
						}
						$p += $n;
						$p += 4;
					} elseif ($type == 'IEND') {
						break;
					} elseif (preg_match('/[a-zA-Z]{4}/', $type)) {
						$p += $n + 4;
					} else {
						return $this->imageError($file, $firsttime, 'Error parsing PNG image data');
					}
				} while ($n);
				if (!$pngdata) {
					return $this->imageError($file, $firsttime, 'Error parsing PNG image data - no IDAT data found');
				}
				if ($colspace == 'Indexed' && empty($pal)) {
					return $this->imageError($file, $firsttime, 'Error parsing PNG image data - missing colour palette');
				}

				if ($colspace == 'Indexed' && $icc) {
					$icc = false;
				} // mPDF 6 cannot have ICC profile and Indexed in a PDF document as both use the colorspace tag.

				$info = ['w' => $w, 'h' => $h, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'FlateDecode', 'parms' => $parms, 'pal' => $pal, 'trns' => $trns, 'data' => $pngdata, 'ch' => $channels, 'icc' => $icc];
				$info['type'] = 'png';
				if ($ppUx) {
					$info['set-dpi'] = $ppUx;
				}
			}

			if (!$info) {
				return $this->imageError($file, $firsttime, 'Error parsing or converting PNG image');
			}

			if ($firsttime) {
				$info['i'] = count($this->mpdf->images) + 1;
				$info['interpolation'] = $interpolation; // mPDF 6
				$this->mpdf->images[$file] = $info;
			}

			return $info;

		} elseif ($type == 'gif') { // GIF

			if (function_exists('gd_info')) {
				$gd = gd_info();
			} else {
				$gd = [];
			}

			if (isset($gd['GIF Read Support']) && $gd['GIF Read Support']) {

				$im = @imagecreatefromstring($data);
				if ($im) {
					$tempfile = $this->cache->tempFilename('_tempImgPNG' . md5($file) . random_int(1, 10000) . '.png');
					imagealphablending($im, false);
					imagesavealpha($im, false);
					imageinterlace($im, false);
					if (!is_writable($tempfile)) {
						ob_start();
						$check = @imagepng($im);
						if (!$check) {
							return $this->imageError($file, $firsttime, 'Error creating temporary image object whilst using GD library to parse GIF image');
						}
						$this->mpdf->imageVars['tempImage'] = ob_get_contents();
						$tempimglnk = 'var:tempImage';
						ob_end_clean();
						$info = $this->getImage($tempimglnk, false);
						if (!$info) {
							return $this->imageError($file, $firsttime, 'Error parsing temporary file image object created with GD library to parse GIF image');
						}
						imagedestroy($im);
					} else {
						$check = @imagepng($im, $tempfile);
						if (!$check) {
							return $this->imageError($file, $firsttime, 'Error creating temporary file (' . $tempfile . ') whilst using GD library to parse GIF image');
						}
						$info = $this->getImage($tempfile, false);
						if (!$info) {
							return $this->imageError($file, $firsttime, 'Error parsing temporary file (' . $tempfile . ') created with GD library to parse GIF image');
						}
						imagedestroy($im);
						unlink($tempfile);
					}
					$info['type'] = 'gif';
					if ($firsttime) {
						$info['i'] = count($this->mpdf->images) + 1;
						$info['interpolation'] = $interpolation; // mPDF 6
						$this->mpdf->images[$file] = $info;
					}
					return $info;
				} else {
					return $this->imageError($file, $firsttime, 'Error creating GD image file from GIF image');
				}
			}

			$gif = new Gif();

			$h = 0;
			$w = 0;
			$gif->loadFile($data, 0);

			if (isset($gif->m_img->m_gih->m_bLocalClr) && $gif->m_img->m_gih->m_bLocalClr) {
				$nColors = $gif->m_img->m_gih->m_nTableSize;
				$pal = $gif->m_img->m_gih->m_colorTable->toString();
				if ((isset($bgColor)) and $bgColor != -1) { // mPDF 5.7.3
					$bgColor = $gif->m_img->m_gih->m_colorTable->colorIndex($bgColor);
				}
				$colspace = 'Indexed';
			} elseif (isset($gif->m_gfh->m_bGlobalClr) && $gif->m_gfh->m_bGlobalClr) {
				$nColors = $gif->m_gfh->m_nTableSize;
				$pal = $gif->m_gfh->m_colorTable->toString();
				if ((isset($bgColor)) and $bgColor != -1) {
					$bgColor = $gif->m_gfh->m_colorTable->colorIndex($bgColor);
				}
				$colspace = 'Indexed';
			} else {
				$nColors = 0;
				$bgColor = -1;
				$colspace = 'DeviceGray';
				$pal = '';
			}

			$trns = '';
			if (isset($gif->m_img->m_bTrans) && $gif->m_img->m_bTrans && ($nColors > 0)) {
				$trns = [$gif->m_img->m_nTrans];
			}
			$gifdata = $gif->m_img->m_data;
			$w = $gif->m_gfh->m_nWidth;
			$h = $gif->m_gfh->m_nHeight;
			$gif->ClearData();

			if ($colspace == 'Indexed' and empty($pal)) {
				return $this->imageError($file, $firsttime, 'Error parsing GIF image - missing colour palette');
			}

			if ($this->mpdf->compress) {
				$gifdata = $this->gzCompress($gifdata);
				$info = ['w' => $w, 'h' => $h, 'cs' => $colspace, 'bpc' => 8, 'f' => 'FlateDecode', 'pal' => $pal, 'trns' => $trns, 'data' => $gifdata];
			} else {
				$info = ['w' => $w, 'h' => $h, 'cs' => $colspace, 'bpc' => 8, 'pal' => $pal, 'trns' => $trns, 'data' => $gifdata];
			}

			$info['type'] = 'gif';
			if ($firsttime) {
				$info['i'] = count($this->mpdf->images) + 1;
				$info['interpolation'] = $interpolation; // mPDF 6
				$this->mpdf->images[$file] = $info;
			}

			return $info;

		} elseif ($type == 'bmp') {

			if (empty($this->bmp)) {
				$this->bmp = new Bmp($this->mpdf);
			}
			$info = $this->bmp->_getBMPimage($data, $file);
			if (isset($info['error'])) {
				return $this->imageError($file, $firsttime, $info['error']);
			}
			if ($firsttime) {
				$info['i'] = count($this->mpdf->images) + 1;
				$info['interpolation'] = $interpolation; // mPDF 6
				$this->mpdf->images[$file] = $info;
			}
			return $info;

		} elseif ($type == 'wmf') {

			if (empty($this->wmf)) {
				$this->wmf = new Wmf($this->mpdf, $this->colorConverter);
			}

			$wmfres = $this->wmf->_getWMFimage($data);

			if ($wmfres[0] == 0) {
				if ($wmfres[1]) {
					return $this->imageError($file, $firsttime, $wmfres[1]);
				}
				return $this->imageError($file, $firsttime, 'Error parsing WMF image');
			}

			$info = ['x' => $wmfres[2][0], 'y' => $wmfres[2][1], 'w' => $wmfres[3][0], 'h' => $wmfres[3][1], 'data' => $wmfres[1]];
			$info['i'] = count($this->mpdf->formobjects) + 1;
			$info['type'] = 'wmf';
			$this->mpdf->formobjects[$file] = $info;

			return $info;

		} else { // UNKNOWN TYPE - try GD imagecreatefromstring

			if (function_exists('gd_info')) {
				$gd = gd_info();
			} else {
				$gd = [];
			}

			if (isset($gd['PNG Support']) && $gd['PNG Support']) {

				$im = @imagecreatefromstring($data);

				if (!$im) {
					return $this->imageError($file, $firsttime, 'Error parsing image file - image type not recognised, and not supported by GD imagecreate');
				}

				$tempfile = $this->cache->tempFilename('_tempImgPNG' . md5($file) . random_int(1, 10000) . '.png');

				imagealphablending($im, false);
				imagesavealpha($im, false);
				imageinterlace($im, false);

				$check = @imagepng($im, $tempfile);

				if (!$check) {
					return $this->imageError($file, $firsttime, 'Error creating temporary file (' . $tempfile . ') whilst using GD library to parse unknown image type');
				}

				$info = $this->getImage($tempfile, false);

				imagedestroy($im);
				unlink($tempfile);

				if (!$info) {
					return $this->imageError($file, $firsttime, 'Error parsing temporary file (' . $tempfile . ') created with GD library to parse unknown image type');
				}

				$info['type'] = 'png';
				if ($firsttime) {
					$info['i'] = count($this->mpdf->images) + 1;
					$info['interpolation'] = $interpolation; // mPDF 6
					$this->mpdf->images[$file] = $info;
				}

				return $info;
			}
		}

		return $this->imageError($file, $firsttime, 'Error parsing image file - image type not recognised');
	}

	private function convImage(&$data, $colspace, $targetcs, $w, $h, $dpi, $mask, $gamma_correction = false, $pngcolortype = false)
	{
		if ($this->mpdf->PDFA || $this->mpdf->PDFX) {
			$mask = false;
		}

		$im = @imagecreatefromstring($data);
		$info = [];
		$bpc = ord(substr($data, 24, 1));

		if ($im) {
			$imgdata = '';
			$mimgdata = '';
			$minfo = [];

			// mPDF 6 Gamma correction
			// Need to extract alpha channel info before imagegammacorrect (which loses the data)
			if ($mask) { // i.e. $pngalpha for PNG
				// mPDF 6
				if ($colspace == 'Indexed') { // generate Alpha channel values from tRNS - only from PNG
					//Read transparency info
					$transparency = '';
					$p = strpos($data, 'tRNS');
					if ($p) {
						$n = $this->fourBytesToInt(substr($data, ($p - 4), 4));
						$transparency = substr($data, ($p + 4), $n);
						// ord($transparency{$index}) = the alpha value for that index
						// generate alpha channel
						for ($ypx = 0; $ypx < $h; ++$ypx) {
							for ($xpx = 0; $xpx < $w; ++$xpx) {
								$colorindex = imagecolorat($im, $xpx, $ypx);
								if ($colorindex >= $n) {
									$alpha = 255;
								} else {
									$alpha = ord($transparency{$colorindex});
								} // 0-255
								$mimgdata .= chr($alpha);
							}
						}
					}
				} elseif ($pngcolortype === 0 || $pngcolortype == 2) { // generate Alpha channel values from tRNS
					// Get transparency as array of RGB
					$p = strpos($data, 'tRNS');
					if ($p) {
						$trns = '';
						$n = $this->fourBytesToInt(substr($data, ($p - 4), 4));
						$t = substr($data, ($p + 4), $n);
						if ($colspace == 'DeviceGray') {  // ct===0
							$trns = [$this->translateValue(substr($t, 0, 2), $bpc)];
						} else /* $colspace=='DeviceRGB' */ {  // ct==2
							$trns = [];
							$trns[0] = $this->translateValue(substr($t, 0, 2), $bpc);
							$trns[1] = $this->translateValue(substr($t, 2, 2), $bpc);
							$trns[2] = $this->translateValue(substr($t, 4, 2), $bpc);
						}

						// generate alpha channel
						for ($ypx = 0; $ypx < $h; ++$ypx) {
							for ($xpx = 0; $xpx < $w; ++$xpx) {
								$rgb = imagecolorat($im, $xpx, $ypx);
								$r = ($rgb >> 16) & 0xFF;
								$g = ($rgb >> 8) & 0xFF;
								$b = $rgb & 0xFF;
								if ($colspace == 'DeviceGray' && $b == $trns[0]) {
									$alpha = 0;
								} elseif ($r == $trns[0] && $g == $trns[1] && $b == $trns[2]) {
									$alpha = 0;
								} // ct==2
								else {
									$alpha = 255;
								}
								$mimgdata .= chr($alpha);
							}
						}
					}
				} else {
					for ($i = 0; $i < $h; $i++) {
						for ($j = 0; $j < $w; $j++) {
							$rgb = imagecolorat($im, $j, $i);
							$alpha = ($rgb & 0x7F000000) >> 24;
							if ($alpha < 127) {
								$mimgdata .= chr(255 - ($alpha * 2));
							} else {
								$mimgdata .= chr(0);
							}
						}
					}
				}
			}

			// mPDF 6 Gamma correction
			if ($gamma_correction) {
				imagegammacorrect($im, $gamma_correction, 2.2);
			}

			//Read transparency info
			$trns = [];
			$trnsrgb = false;
			if (!$this->mpdf->PDFA && !$this->mpdf->PDFX && !$mask) {  // mPDF 6 added NOT mask
				$p = strpos($data, 'tRNS');
				if ($p) {
					$n = $this->fourBytesToInt(substr($data, ($p - 4), 4));
					$t = substr($data, ($p + 4), $n);
					if ($colspace == 'DeviceGray') {  // ct===0
						$trns = [$this->translateValue(substr($t, 0, 2), $bpc)];
					} elseif ($colspace == 'DeviceRGB') {  // ct==2
						$trns[0] = $this->translateValue(substr($t, 0, 2), $bpc);
						$trns[1] = $this->translateValue(substr($t, 2, 2), $bpc);
						$trns[2] = $this->translateValue(substr($t, 4, 2), $bpc);
						$trnsrgb = $trns;
						if ($targetcs == 'DeviceCMYK') {
							$col = $this->colorModeConverter->rgb2cmyk([3, $trns[0], $trns[1], $trns[2]]);
							$c1 = intval($col[1] * 2.55);
							$c2 = intval($col[2] * 2.55);
							$c3 = intval($col[3] * 2.55);
							$c4 = intval($col[4] * 2.55);
							$trns = [$c1, $c2, $c3, $c4];
						} elseif ($targetcs == 'DeviceGray') {
							$c = intval(($trns[0] * .21) + ($trns[1] * .71) + ($trns[2] * .07));
							$trns = [$c];
						}
					} else { // Indexed
						$pos = strpos($t, chr(0));
						if (is_int($pos)) {
							$pal = imagecolorsforindex($im, $pos);
							$r = $pal['red'];
							$g = $pal['green'];
							$b = $pal['blue'];
							$trns = [$r, $g, $b]; // ****
							$trnsrgb = $trns;
							if ($targetcs == 'DeviceCMYK') {
								$col = $this->colorModeConverter->rgb2cmyk([3, $r, $g, $b]);
								$c1 = intval($col[1] * 2.55);
								$c2 = intval($col[2] * 2.55);
								$c3 = intval($col[3] * 2.55);
								$c4 = intval($col[4] * 2.55);
								$trns = [$c1, $c2, $c3, $c4];
							} elseif ($targetcs == 'DeviceGray') {
								$c = intval(($r * .21) + ($g * .71) + ($b * .07));
								$trns = [$c];
							}
						}
					}
				}
			}

			for ($i = 0; $i < $h; $i++) {
				for ($j = 0; $j < $w; $j++) {
					$rgb = imagecolorat($im, $j, $i);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					if ($colspace == 'Indexed') {
						$pal = imagecolorsforindex($im, $rgb);
						$r = $pal['red'];
						$g = $pal['green'];
						$b = $pal['blue'];
					}

					if ($targetcs == 'DeviceCMYK') {
						$col = $this->colorModeConverter->rgb2cmyk([3, $r, $g, $b]);
						$c1 = intval($col[1] * 2.55);
						$c2 = intval($col[2] * 2.55);
						$c3 = intval($col[3] * 2.55);
						$c4 = intval($col[4] * 2.55);
						if ($trnsrgb) {
							// original pixel was not set as transparent but processed color does match
							if ($trnsrgb != [$r, $g, $b] && $trns == [$c1, $c2, $c3, $c4]) {
								if ($c4 == 0) {
									$c4 = 1;
								} else {
									$c4--;
								}
							}
						}
						$imgdata .= chr($c1) . chr($c2) . chr($c3) . chr($c4);
					} elseif ($targetcs == 'DeviceGray') {
						$c = intval(($r * .21) + ($g * .71) + ($b * .07));
						if ($trnsrgb) {
							// original pixel was not set as transparent but processed color does match
							if ($trnsrgb != [$r, $g, $b] && $trns == [$c]) {
								if ($c == 0) {
									$c = 1;
								} else {
									$c--;
								}
							}
						}
						$imgdata .= chr($c);
					} elseif ($targetcs == 'DeviceRGB') {
						$imgdata .= chr($r) . chr($g) . chr($b);
					}
				}
			}

			if ($targetcs == 'DeviceGray') {
				$ncols = 1;
			} elseif ($targetcs == 'DeviceRGB') {
				$ncols = 3;
			} elseif ($targetcs == 'DeviceCMYK') {
				$ncols = 4;
			}

			$imgdata = $this->gzCompress($imgdata);
			$info = ['w' => $w, 'h' => $h, 'cs' => $targetcs, 'bpc' => 8, 'f' => 'FlateDecode', 'data' => $imgdata, 'type' => 'png',
				'parms' => '/DecodeParms <</Colors ' . $ncols . ' /BitsPerComponent 8 /Columns ' . $w . '>>'];
			if ($dpi) {
				$info['set-dpi'] = $dpi;
			}
			if ($mask) {
				$mimgdata = $this->gzCompress($mimgdata);
				$minfo = ['w' => $w, 'h' => $h, 'cs' => 'DeviceGray', 'bpc' => 8, 'f' => 'FlateDecode', 'data' => $mimgdata, 'type' => 'png',
					'parms' => '/DecodeParms <</Colors ' . $ncols . ' /BitsPerComponent 8 /Columns ' . $w . '>>'];
				if ($dpi) {
					$minfo['set-dpi'] = $dpi;
				}
				$tempfile = '_tempImgPNG' . md5($data) . random_int(1, 10000) . '.png';
				$imgmask = count($this->mpdf->images) + 1;
				$minfo['i'] = $imgmask;
				$this->mpdf->images[$tempfile] = $minfo;
				$info['masked'] = $imgmask;
			} elseif ($trns) {
				$info['trns'] = $trns;
			}
			imagedestroy($im);
		}
		return $info;
	}

	private function jpgHeaderFromString(&$data)
	{
		$p = 4;
		$p += $this->twoBytesToInt(substr($data, $p, 2)); // Length of initial marker block
		$marker = substr($data, $p, 2);

		while ($marker != chr(255) . chr(192) && $marker != chr(255) . chr(194) && $p < strlen($data)) {
			// Start of frame marker (FFC0) or (FFC2) mPDF 4.4.004
			$p += ($this->twoBytesToInt(substr($data, $p + 2, 2))) + 2; // Length of marker block
			$marker = substr($data, $p, 2);
		}

		if ($marker != chr(255) . chr(192) && $marker != chr(255) . chr(194)) {
			return false;
		}
		return substr($data, $p + 2, 10);
	}

	private function jpgDataFromHeader($hdr)
	{
		$bpc = ord(substr($hdr, 2, 1));

		if (!$bpc) {
			$bpc = 8;
		}

		$h = $this->twoBytesToInt(substr($hdr, 3, 2));
		$w = $this->twoBytesToInt(substr($hdr, 5, 2));

		$channels = ord(substr($hdr, 7, 1));

		if ($channels == 3) {
			$colspace = 'DeviceRGB';
		} elseif ($channels == 4) {
			$colspace = 'DeviceCMYK';
		} else {
			$colspace = 'DeviceGray';
		}

		return [$w, $h, $colspace, $bpc, $channels];
	}

	/**
	 * Corrects 2-byte integer to 8-bit depth value
	 * If original image is bpc != 8, tRNS will be in this bpc
	 * $im from imagecreatefromstring will always be in bpc=8
	 * So why do we only need to correct 16-bit tRNS and NOT 2 or 4-bit???
	 */
	private function translateValue($s, $bpc)
	{
		$n = $this->twoBytesToInt($s);

		if ($bpc == 16) {
			$n = ($n >> 8);
		}

		//elseif ($bpc==4) { $n = ($n << 2); }
		//elseif ($bpc==2) { $n = ($n << 4); }

		return $n;
	}

	/**
	 * Read a 4-byte integer from string
	 */
	private function fourBytesToInt($s)
	{
		return (ord($s[0]) << 24) + (ord($s[1]) << 16) + (ord($s[2]) << 8) + ord($s[3]);
	}

	/**
	 * Equivalent to _get_ushort
	 * Read a 2-byte integer from string
	 */
	private function twoBytesToInt($s)
	{
		return (ord(substr($s, 0, 1)) << 8) + ord(substr($s, 1, 1));
	}

	private function gzCompress($data)
	{
		if (!function_exists('gzcompress')) {
			throw new \Mpdf\MpdfException('gzcompress is not available. install ext-zlib extension.');
		}

		return gzcompress($data);
	}

	/**
	 * Throw an exception and save re-trying image URL's which have already failed
	 */
	private function imageError($file, $firsttime, $msg)
	{
		$this->failedImages[$file] = true;

		if ($firsttime && ($this->mpdf->showImageErrors || $this->mpdf->debug)) {
			throw new \Mpdf\MpdfImageException(sprintf('%s (%s)', $msg, $file));
		}

		$this->logger->warning(sprintf('%s (%s)', $msg, $file), ['context' => LogContext::IMAGES]);
	}

	/**
	 * @since mPDF 5.7.4
	 * @param string $url
	 * @return string
	 */
	private function urldecodeParts($url)
	{
		$file = $url;
		$query = '';
		if (preg_match('/[?]/', $url)) {
			$bits = preg_split('/[?]/', $url, 2);
			$file = $bits[0];
			$query = '?' . $bits[1];
		}
		$file = rawurldecode($file);
		$query = urldecode($query);

		return $file . $query;
	}


}
