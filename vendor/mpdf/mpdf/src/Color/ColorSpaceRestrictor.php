<?php

namespace Mpdf\Color;

use Mpdf\Mpdf;

class ColorSpaceRestrictor
{

	const RESTRICT_TO_GRAYSCALE = 1;

	const RESTRICT_TO_RGB_SPOT_GRAYSCALE = 2;

	const RESTRICT_TO_CMYK_SPOT_GRAYSCALE = 3;

	/**
	 * @var \Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \Mpdf\Color\ColorModeConverter
	 */
	private $colorModeConverter;

	/**
	 * @var int
	 */
	private $mode;

	/**
	 * Process $mode settings
	 *     1 - allow GRAYSCALE only [convert CMYK/RGB->gray]
	 *     2 - allow RGB / SPOT COLOR / Grayscale [convert CMYK->RGB]
	 *     3 - allow CMYK / SPOT COLOR / Grayscale [convert RGB->CMYK]
	 *
	 * @param \Mpdf\Mpdf $mpdf
	 * @param \Mpdf\Color\ColorModeConverter $colorModeConverter
	 * @param int $mode
	 */
	public function __construct(Mpdf $mpdf, ColorModeConverter $colorModeConverter, $mode)
	{
		$this->mpdf = $mpdf;
		$this->colorModeConverter = $colorModeConverter;
		$this->mode = $mode;
	}

	/**
	 * @param mixed $c
	 * @param string $color
	 * @param string[] $PDFAXwarnings
	 *
	 * @return float[]
	 */
	public function restrictColorSpace($c, $color, &$PDFAXwarnings = [])
	{
		$mode = (int) $c[0];
		switch ($mode) {
			case 1:
				return $c;
			case 2:
				return $this->restrictSpotColorSpace($c, $PDFAXwarnings);
			case 3:
				return $this->restrictRgbColorSpace($c, $color, $PDFAXwarnings);
			case 4:
				return $this->restrictCmykColorSpace($c, $color, $PDFAXwarnings);
			case 5:
				return $this->restrictRgbaColorSpace($c, $color, $PDFAXwarnings);
			case 6:
				return $this->restrictCmykaColorSpace($c, $color, $PDFAXwarnings);
		}

		return $c;
	}

	/**
	 * @param string $c
	 * @param string[] $PDFAXwarnings
	 *
	 * @return float[]
	 */
	private function restrictSpotColorSpace($c, &$PDFAXwarnings = [])
	{
		if (!isset($this->mpdf->spotColorIDs[$c[1]])) {
				throw new \Mpdf\MpdfException('Error: Spot colour has not been defined - ' . $this->mpdf->spotColorIDs[$c[1]]);
		}

		if ($this->mpdf->PDFA) {
			if ($this->mpdf->PDFA && !$this->mpdf->PDFAauto) {
				$PDFAXwarnings[] = "Spot color specified '" . $this->mpdf->spotColorIDs[$c[1]] . "' (converted to process color)";
			}
			if ($this->mode != 3) {
				$sp = $this->mpdf->spotColors[$this->mpdf->spotColorIDs[$c[1]]];
				$c = $this->colorModeConverter->cmyk2rgb([4, $sp['c'], $sp['m'], $sp['y'], $sp['k']]);
			}
		} elseif ($this->mode == 1) {
			$sp = $this->mpdf->spotColors[$this->mpdf->spotColorIDs[$c[1]]];
			$c = $this->colorModeConverter->cmyk2gray([4, $sp['c'], $sp['m'], $sp['y'], $sp['k']]);
		}

		return $c;
	}

	/**
	 * @param mixed $c
	 * @param string $color
	 * @param string[] $PDFAXwarnings
	 *
	 * @return float[]
	 */
	private function restrictRgbColorSpace($c, $color, &$PDFAXwarnings = [])
	{
		if ($this->mpdf->PDFX || ($this->mpdf->PDFA && $this->mode == 3)) {
			if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) {
				$PDFAXwarnings[] = "RGB color specified '" . $color . "' (converted to CMYK)";
			}
			$c = $this->colorModeConverter->rgb2cmyk($c);
		} elseif ($this->mode == 1) {
			$c = $this->colorModeConverter->rgb2gray($c);
		} elseif ($this->mode == 3) {
			$c = $this->colorModeConverter->rgb2cmyk($c);
		}

		return $c;
	}

	/**
	 * @param mixed $c
	 * @param string $color
	 * @param string[] $PDFAXwarnings
	 *
	 * @return float[]
	 */
	private function restrictCmykColorSpace($c, $color, &$PDFAXwarnings = [])
	{
		if ($this->mpdf->PDFA && $this->mode != 3) {
			if ($this->mpdf->PDFA && !$this->mpdf->PDFAauto) {
				$PDFAXwarnings[] = "CMYK color specified '" . $color . "' (converted to RGB)";
			}
			$c = $this->colorModeConverter->cmyk2rgb($c);
		} elseif ($this->mode == 1) {
			$c = $this->colorModeConverter->cmyk2gray($c);
		} elseif ($this->mode == 2) {
			$c = $this->colorModeConverter->cmyk2rgb($c);
		}

		return $c;
	}

	/**
	 * @param mixed $c
	 * @param string $color
	 * @param string[] $PDFAXwarnings
	 *
	 * @return float[]
	 */
	private function restrictRgbaColorSpace($c, $color, &$PDFAXwarnings = [])
	{
		if ($this->mpdf->PDFX || ($this->mpdf->PDFA && $this->mode == 3)) {
			if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) {
				$PDFAXwarnings[] = "RGB color with transparency specified '" . $color . "' (converted to CMYK without transparency)";
			}
			$c = $this->colorModeConverter->rgb2cmyk($c);
			$c = [4, $c[1], $c[2], $c[3], $c[4]];
		} elseif ($this->mpdf->PDFA && $this->mode != 3) {
			if (!$this->mpdf->PDFAauto) {
				$PDFAXwarnings[] = "RGB color with transparency specified '" . $color . "' (converted to RGB without transparency)";
			}
			$c = $this->colorModeConverter->rgb2cmyk($c);
			$c = [4, $c[1], $c[2], $c[3], $c[4]];
		} elseif ($this->mode == 1) {
			$c = $this->colorModeConverter->rgb2gray($c);
		} elseif ($this->mode == 3) {
			$c = $this->colorModeConverter->rgb2cmyk($c);
		}

		return $c;
	}

	/**
	 * @param mixed $c
	 * @param string $color
	 * @param string[] $PDFAXwarnings
	 *
	 * @return float[]
	 */
	private function restrictCmykaColorSpace($c, $color, &$PDFAXwarnings = [])
	{
		if ($this->mpdf->PDFA && $this->mode != 3) {
			if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) {
				$PDFAXwarnings[] = "CMYK color with transparency specified '" . $color . "' (converted to RGB without transparency)";
			}
			$c = $this->colorModeConverter->cmyk2rgb($c);
			$c = [3, $c[1], $c[2], $c[3]];
		} elseif ($this->mpdf->PDFX || ($this->mpdf->PDFA && $this->mode == 3)) {
			if (($this->mpdf->PDFA && !$this->mpdf->PDFAauto) || ($this->mpdf->PDFX && !$this->mpdf->PDFXauto)) {
				$PDFAXwarnings[] = "CMYK color with transparency specified '" . $color . "' (converted to CMYK without transparency)";
			}
			$c = $this->colorModeConverter->cmyk2rgb($c);
			$c = [3, $c[1], $c[2], $c[3]];
		} elseif ($this->mode == 1) {
			$c = $this->colorModeConverter->cmyk2gray($c);
		} elseif ($this->mode == 2) {
			$c = $this->colorModeConverter->cmyk2rgb($c);
		}

		return $c;
	}

}
