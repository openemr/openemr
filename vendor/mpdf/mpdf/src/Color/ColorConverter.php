<?php

namespace Mpdf\Color;

use Mpdf\Mpdf;

class ColorConverter
{

	const MODE_GRAYSCALE = 1;

	const MODE_SPOT = 2;

	const MODE_RGB = 3;

	const MODE_CMYK = 4;

	const MODE_RGBA = 5;

	const MODE_CMYKA = 6;

	private $mpdf;

	private $colorModeConverter;

	private $colorSpaceRestrictor;

	private $cache;

	public function __construct(Mpdf $mpdf, ColorModeConverter $colorModeConverter, ColorSpaceRestrictor $colorSpaceRestrictor)
	{
		$this->mpdf = $mpdf;
		$this->colorModeConverter = $colorModeConverter;
		$this->colorSpaceRestrictor = $colorSpaceRestrictor;

		$this->cache = [];
	}

	public function convert($color, array &$PDFAXwarnings = [])
	{
		$color = strtolower(trim($color));

		if ($color === 'transparent' || $color === 'inherit') {
			return false;
		}

		if (isset(NamedColors::$colors[$color])) {
			$color = NamedColors::$colors[$color];
		}

		if (!isset($this->cache[$color])) {
			$c = $this->convertPlain($color, $PDFAXwarnings);
			$cstr = '';
			if (is_array($c)) {
				$c = array_pad($c, 6, 0);
				$cstr = pack('a1ccccc', $c[0], $c[1] & 0xFF, $c[2] & 0xFF, $c[3] & 0xFF, $c[4] & 0xFF, $c[5] & 0xFF);
			}

			$this->cache[$color] = $cstr;
		}

		return $this->cache[$color];
	}

	public function lighten($c)
	{
		$this->ensureBinaryColorFormat($c);

		if ($c[0] == static::MODE_RGB || $c[0] == static::MODE_RGBA) {
			list($h, $s, $l) = $this->colorModeConverter->rgb2hsl(ord($c[1]) / 255, ord($c[2]) / 255, ord($c[3]) / 255);
			$l += ((1 - $l) * 0.8);
			list($r, $g, $b) = $this->colorModeConverter->hsl2rgb($h, $s, $l);
			$ret = [3, $r, $g, $b];
		} elseif ($c[0] == static::MODE_CMYK || $c[0] == static::MODE_CMYKA) {
			$ret = [4, max(0, ord($c[1]) - 20), max(0, ord($c[2]) - 20), max(0, ord($c[3]) - 20), max(0, ord($c[4]) - 20)];
		} elseif ($c[0] == static::MODE_GRAYSCALE) {
			$ret = [1, min(255, ord($c[1]) + 32)];
		}

		$c = array_pad($ret, 6, 0);
		$cstr = pack('a1ccccc', $c[0], $c[1] & 0xFF, $c[2] & 0xFF, $c[3] & 0xFF, $c[4] & 0xFF, $c[5] & 0xFF);

		return $cstr;
	}

	public function darken($c)
	{
		$this->ensureBinaryColorFormat($c);

		if ($c[0] == static::MODE_RGB || $c[0] == static::MODE_RGBA) {
			list($h, $s, $l) = $this->colorModeConverter->rgb2hsl(ord($c[1]) / 255, ord($c[2]) / 255, ord($c[3]) / 255);
			$s *= 0.25;
			$l *= 0.75;
			list($r, $g, $b) = $this->colorModeConverter->hsl2rgb($h, $s, $l);
			$ret = [3, $r, $g, $b];
		} elseif ($c[0] == static::MODE_CMYK || $c[0] == static::MODE_CMYKA) {
			$ret = [4, min(100, ord($c[1]) + 20), min(100, ord($c[2]) + 20), min(100, ord($c[3]) + 20), min(100, ord($c[4]) + 20)];
		} elseif ($c[0] == static::MODE_GRAYSCALE) {
			$ret = [1, max(0, ord($c[1]) - 32)];
		}
		$c = array_pad($ret, 6, 0);
		$cstr = pack('a1ccccc', $c[0], $c[1] & 0xFF, $c[2] & 0xFF, $c[3] & 0xFF, $c[4] & 0xFF, $c[5] & 0xFF);

		return $cstr;
	}

	/**
	 * @param string $c
	 * @return float[]
	 */
	public function invert($c)
	{
		$this->ensureBinaryColorFormat($c);

		if ($c[0] == static::MODE_RGB || $c[0] == static::MODE_RGBA) {
			return [3, 255 - ord($c[1]), 255 - ord($c[2]), 255 - ord($c[3])];
		}

		if ($c[0] == static::MODE_CMYK || $c[0] == static::MODE_CMYKA) {
			return [4, 100 - ord($c[1]), 100 - ord($c[2]), 100 - ord($c[3]), 100 - ord($c[4])];
		}

		if ($c[0] == static::MODE_GRAYSCALE) {
			return [1, 255 - ord($c[1])];
		}

		// Cannot cope with non-RGB colors at present
		throw new \Mpdf\MpdfException('Trying to invert non-RGB color');
	}

	/**
	 * @param string $c Binary color string
	 *
	 * @return string
	 */
	public function colAtoString($c)
	{
		if ($c[0] == static::MODE_GRAYSCALE) {
			return 'rgb(' . ord($c[1]) . ', ' . ord($c[1]) . ', ' . ord($c[1]) . ')';
		}

		if ($c[0] == static::MODE_SPOT) {
			return 'spot(' . ord($c[1]) . ', ' . ord($c[2]) . ')';
		}

		if ($c[0] == static::MODE_RGB) {
			return 'rgb(' . ord($c[1]) . ', ' . ord($c[2]) . ', ' . ord($c[3]) . ')';
		}

		if ($c[0] == static::MODE_CMYK) {
			return 'cmyk(' . ord($c[1]) . ', ' . ord($c[2]) . ', ' . ord($c[3]) . ', ' . ord($c[4]) . ')';
		}

		if ($c[0] == static::MODE_RGBA) {
			return 'rgba(' . ord($c[1]) . ', ' . ord($c[2]) . ', ' . ord($c[3]) . ', ' . sprintf('%0.2F', ord($c[4]) / 100) . ')';
		}

		if ($c[0] == static::MODE_CMYKA) {
			return 'cmyka(' . ord($c[1]) . ', ' . ord($c[2]) . ', ' . ord($c[3]) . ', ' . ord($c[4]) . ', ' . sprintf('%0.2F', ord($c[5]) / 100) . ')';
		}

		return '';
	}

	/**
	 * @param string $color
	 * @param string[] $PDFAXwarnings
	 *
	 * @return bool|float[]
	 */
	private function convertPlain($color, array &$PDFAXwarnings = [])
	{
		$c = false;

		if (preg_match('/^[\d]+$/', $color)) {
			$c = [static::MODE_GRAYSCALE, $color]; // i.e. integer only
		} elseif (strpos($color, '#') === 0) { // case of #nnnnnn or #nnn
			$c = $this->processHashColor($color);
		} elseif (preg_match('/(rgba|rgb|device-cmyka|cmyka|device-cmyk|cmyk|hsla|hsl|spot)\((.*?)\)/', $color, $m)) {
			$c = $this->processModeColor($m[1], explode(',', $m[2]));
		}

		if ($this->mpdf->PDFA || $this->mpdf->PDFX || $this->mpdf->restrictColorSpace) {
			$c = $this->restrictColorSpace($c, $color, $PDFAXwarnings);
		}

		return $c;
	}

	/**
	 * @param string $color
	 *
	 * @return float[]
	 */
	private function processHashColor($color)
	{
		// in case of Background: #CCC url() x-repeat etc.
		$cor = preg_replace('/\s+.*/', '', $color);

		// Turn #RGB into #RRGGBB
		if (strlen($cor) === 4) {
			$cor = '#' . $cor[1] . $cor[1] . $cor[2] . $cor[2] . $cor[3] . $cor[3];
		}

		$r = hexdec(substr($cor, 1, 2));
		$g = hexdec(substr($cor, 3, 2));
		$b = hexdec(substr($cor, 5, 2));

		return [3, $r, $g, $b];
	}

	/**
	 * @param $mode
	 * @param mixed[] $cores
	 * @return bool|float[]
	 */
	private function processModeColor($mode, array $cores)
	{
		$c = false;

		$cores = $this->convertPercentCoreValues($mode, $cores);

		switch ($mode) {
			case 'rgb':
				return [static::MODE_RGB, $cores[0], $cores[1], $cores[2]];

			case 'rgba':
				return [static::MODE_RGBA, $cores[0], $cores[1], $cores[2], $cores[3] * 100];

			case 'cmyk':
			case 'device-cmyk':
				return [static::MODE_CMYK, $cores[0], $cores[1], $cores[2], $cores[3]];

			case 'cmyka':
			case 'device-cmyka':
				return [static::MODE_CMYKA, $cores[0], $cores[1], $cores[2], $cores[3], $cores[4] * 100];

			case 'hsl':
				$conv = $this->colorModeConverter->hsl2rgb($cores[0] / 360, $cores[1], $cores[2]);
				return [static::MODE_RGB, $conv[0], $conv[1], $conv[2]];

			case 'hsla':
				$conv = $this->colorModeConverter->hsl2rgb($cores[0] / 360, $cores[1], $cores[2]);
				return [static::MODE_RGBA, $conv[0], $conv[1], $conv[2], $cores[3] * 100];

			case 'spot':
				$name = strtoupper(trim($cores[0]));

				if (!isset($this->mpdf->spotColors[$name])) {
					if (isset($cores[5])) {
						$this->mpdf->AddSpotColor($cores[0], $cores[2], $cores[3], $cores[4], $cores[5]);
					} else {
						throw new \Mpdf\MpdfException(sprintf('Undefined spot color "%s"', $name));
					}
				}

				return [static::MODE_SPOT, $this->mpdf->spotColors[$name]['i'], $cores[1]];
		}

		return $c;
	}

	/**
	 * @param string $mode
	 * @param mixed[] $cores
	 *
	 * @return float[]
	 */
	private function convertPercentCoreValues($mode, array $cores)
	{
		$ncores = count($cores);

		if (strpos($cores[0], '%') !== false) {
			$cores[0] = (float) $cores[0];
			if ($mode === 'rgb' || $mode === 'rgba') {
				$cores[0] = (int) ($cores[0] * 255 / 100);
			}
		}

		if ($ncores > 1 && strpos($cores[1], '%') !== false) {
			$cores[1] = (float) $cores[1];
			if ($mode === 'rgb' || $mode === 'rgba') {
				$cores[1] = (int) ($cores[1] * 255 / 100);
			}
			if ($mode === 'hsl' || $mode === 'hsla') {
				$cores[1] /= 100;
			}
		}

		if ($ncores > 2 && strpos($cores[2], '%') !== false) {
			$cores[2] = (float) $cores[2];
			if ($mode === 'rgb' || $mode === 'rgba') {
				$cores[2] = (int) ($cores[2] * 255 / 100);
			}
			if ($mode === 'hsl' || $mode === 'hsla') {
				$cores[2] /= 100;
			}
		}

		if ($ncores > 3 && strpos($cores[3], '%') !== false) {
			$cores[3] = (float) $cores[3];
		}

		return $cores;
	}

	/**
	 * @param mixed $c
	 * @param string $color
	 * @param string[] $PDFAXwarnings
	 *
	 * @return float[]
	 */
	private function restrictColorSpace($c, $color, &$PDFAXwarnings = [])
	{
		return $this->colorSpaceRestrictor->restrictColorSpace($c, $color, $PDFAXwarnings);
	}

	/**
	 * @param string $color Binary color string
	 */
	private function ensureBinaryColorFormat($color)
	{
		if (!is_string($color)) {
			throw new \Mpdf\MpdfException('Invalid color input, binary color string expected');
		}

		if (strlen($color) !== 6) {
			throw new \Mpdf\MpdfException('Invalid color input, binary color string expected');
		}

		if (!in_array($color[0], [static::MODE_GRAYSCALE, static::MODE_SPOT, static::MODE_RGB, static::MODE_CMYK, static::MODE_RGBA, static::MODE_CMYKA])) {
			throw new \Mpdf\MpdfException('Invalid color input, invalid color mode in binary color string');
		}
	}

}
