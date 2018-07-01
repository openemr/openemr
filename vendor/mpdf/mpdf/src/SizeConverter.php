<?php

namespace Mpdf;

use Psr\Log\LoggerInterface;
use Mpdf\Log\Context as LogContext;

class SizeConverter implements \Psr\Log\LoggerAwareInterface
{

	private $dpi;

	private $defaultFontSize;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	public function __construct($dpi, $defaultFontSize, LoggerInterface $logger)
	{
		$this->dpi = $dpi;
		$this->defaultFontSize = $defaultFontSize;
		$this->logger = $logger;
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Depends of maxsize value to make % work properly. Usually maxsize == pagewidth
	 * For text $maxsize = $fontsize
	 * Setting e.g. margin % will use maxsize (pagewidth) and em will use fontsize
	 *
	 * @param mixed $size
	 * @param mixed $maxsize
	 * @param mixed $fontsize
	 * @param mixed $usefontsize Set false for e.g. margins - will ignore fontsize for % values
	 *
	 * @return float Final size in mm
	 */
	public function convert($size = 5, $maxsize = 0, $fontsize = false, $usefontsize = true)
	{
		$size = trim(strtolower($size));
		$res = preg_match('/^(?P<size>[-0-9.,]+)?(?P<unit>[%a-z-]+)?$/', $size, $parts);
		if (!$res) {
			// ignore definition
			$this->logger->warning(sprintf('Invalid size representation "%s"', $size), ['context' => LogContext::CSS_SIZE_CONVERSION]);
		}

		$unit = !empty($parts['unit']) ? $parts['unit'] : null;
		$size = !empty($parts['size']) ? (float) $parts['size'] : 0.0;

		switch ($unit) {
			case 'mm':
				// do nothing
				break;

			case 'cm':
				$size *= 10;
				break;

			case 'pt':
				$size *= 1 / Mpdf::SCALE;
				break;

			case 'rem':
				$size *= ($this->defaultFontSize / (1 / Mpdf::SCALE));
				break;

			case '%':
				if ($fontsize && $usefontsize) {
					$size *= $fontsize / 100;
				} else {
					$size *= $maxsize / 100;
				}
				break;

			case 'in':
				// mm in an inch
				$size *= 25.4;
				break;

			case 'pc':
				// PostScript picas
				$size *= 38.1 / 9;
				break;

			case 'ex':
				// Approximates "ex" as half of font height
				$size *= $this->multiplyFontSize($fontsize, $maxsize, 0.5);
				break;

			case 'em':
				$size *= $this->multiplyFontSize($fontsize, $maxsize, 1);
				break;

			case 'thin':
				$size = 1 * (25.4 / $this->dpi);
				break;

			case 'medium':
				$size = 3 * (25.4 / $this->dpi);
				// Commented-out dead code from legacy method
				// $size *= $this->multiplyFontSize($fontsize, $maxsize, 1);
				break;

			case 'thick':
				$size = 5 * (25.4 / $this->dpi); // 5 pixel width for table borders
				break;

			case 'xx-small':
				$size *= $this->multiplyFontSize($fontsize, $maxsize, 0.7);
				break;

			case 'x-small':
				$size *= $this->multiplyFontSize($fontsize, $maxsize, 0.77);
				break;

			case 'small':
				$size *= $this->multiplyFontSize($fontsize, $maxsize, 0.86);
				break;

			case 'large':
				$size *= $this->multiplyFontSize($fontsize, $maxsize, 1.2);
				break;

			case 'x-large':
				$size *= $this->multiplyFontSize($fontsize, $maxsize, 1.5);
				break;

			case 'xx-large':
				$size *= $this->multiplyFontSize($fontsize, $maxsize, 2);
				break;

			case 'px':
			default:
				$size *= (25.4 / $this->dpi);
				break;
		}

		return $size;
	}

	private function multiplyFontSize($fontsize, $maxsize, $ratio)
	{
		if ($fontsize) {
			return $fontsize * $ratio;
		}

		return $maxsize * $ratio;
	}
}
