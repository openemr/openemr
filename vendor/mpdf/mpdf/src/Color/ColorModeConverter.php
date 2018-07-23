<?php

namespace Mpdf\Color;

class ColorModeConverter
{

	/**
	 * @param float[] $c
	 *
	 * @return float[]
	 */
	public function rgb2gray($c)
	{
		if (isset($c[4])) {
			return [1, ($c[1] * .21) + ($c[2] * .71) + ($c[3] * .07), ord(1), $c[4]];
		}

		return [1, ($c[1] * .21) + ($c[2] * .71) + ($c[3] * .07)];
	}

	/**
	 * @param float[] $c
	 *
	 * @return float[]
	 */
	public function cmyk2gray($c)
	{
		$rgb = $this->cmyk2rgb($c);
		return $this->rgb2gray($rgb);
	}

	/**
	 * @param float[] $c
	 *
	 * @return float[]
	 */
	public function rgb2cmyk($c)
	{
		$cyan = 1 - ($c[1] / 255);
		$magenta = 1 - ($c[2] / 255);
		$yellow = 1 - ($c[3] / 255);
		$min = min($cyan, $magenta, $yellow);

		if ($min == 1) {
			if ($c[0] == 5) {
				return [6, 100, 100, 100, 100, $c[4]];
			}

			return [4, 100, 100, 100, 100];
			// For K-Black
			//if ($c[0]==5) { return array (6,0,0,0,100, $c[4]); }
			//else { return array (4,0,0,0,100); }
		}
		$K = $min;
		$black = 1 - $K;
		if ($c[0] == 5) {
			return [6, ($cyan - $K) * 100 / $black, ($magenta - $K) * 100 / $black, ($yellow - $K) * 100 / $black, $K * 100, $c[4]];
		}

		return [4, ($cyan - $K) * 100 / $black, ($magenta - $K) * 100 / $black, ($yellow - $K) * 100 / $black, $K * 100];
	}

	/**
	 * @param float[] $c
	 *
	 * @return float[]
	 */
	public function cmyk2rgb($c)
	{
		$rgb = [];
		$colors = 255 - ($c[4] * 2.55);
		$rgb[0] = (int) ($colors * (255 - ($c[1] * 2.55)) / 255);
		$rgb[1] = (int) ($colors * (255 - ($c[2] * 2.55)) / 255);
		$rgb[2] = (int) ($colors * (255 - ($c[3] * 2.55)) / 255);
		if ($c[0] == 6) {
			return [5, $rgb[0], $rgb[1], $rgb[2], $c[5]];
		}

		return [3, $rgb[0], $rgb[1], $rgb[2]];
	}

	/**
	 * @param float $r
	 * @param float $g
	 * @param float $b
	 *
	 * @return float[]
	 */
	public function rgb2hsl($r, $g, $b)
	{
		$h = 0;

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);

		$diff = $max - $min;
		$l = ($max + $min) / 2;

		if ($diff == 0) {
			$h = 0;
			$s = 0;
		} else {

			if ($l < 0.5) {
				$s = $diff / ($max + $min);
			} else {
				$s = $diff / (2 - $max - $min);
			}

			$rDiff = ((($max - $r) / 6) + ($diff / 2)) / $diff;
			$gDiff = ((($max - $g) / 6) + ($diff / 2)) / $diff;
			$bDiff = ((($max - $b) / 6) + ($diff / 2)) / $diff;

			if ($r == $max) {
				$h = $bDiff - $gDiff;
			} elseif ($g == $max) {
				$h = (1 / 3) + $rDiff - $bDiff;
			} elseif ($b == $max) {
				$h = (2 / 3) + $gDiff - $rDiff;
			}

			if ($h < 0) {
				++$h;
			}

			if ($h > 1) {
				--$h;
			}
		}

		return [$h, $s, $l];
	}

	/**
	 * Input is HSL value of complementary colour, held in $h2, $s, $l as fractions of 1
	 * Output is RGB in normal 255 255 255 format, held in $r, $g, $b
	 *
	 * @param float $h
	 * @param float $s
	 * @param float $l
	 *
	 * @return float[]
	 */
	public function hsl2rgb($h, $s, $l)
	{
		if ($s == 0) {
			$r = $l * 255;
			$g = $l * 255;
			$b = $l * 255;
		} else {
			if ($l < 0.5) {
				$tmp = $l * (1 + $s);
			} else {
				$tmp = ($l + $s) - ($s * $l);
			}
			$tmp2 = 2 * $l - $tmp;
			$r = round(255 * $this->hue2rgb($tmp2, $tmp, $h + (1 / 3)));
			$g = round(255 * $this->hue2rgb($tmp2, $tmp, $h));
			$b = round(255 * $this->hue2rgb($tmp2, $tmp, $h - (1 / 3)));
		}

		return [$r, $g, $b];
	}

	/**
	 * @param float $v1
	 * @param float $v2
	 * @param float $vh
	 *
	 * @return float
	 */
	public function hue2rgb($v1, $v2, $vh)
	{
		if ($vh < 0) {
			++$vh;
		}

		if ($vh > 1) {
			--$vh;
		}

		if ((6 * $vh) < 1) {
			return ($v1 + ($v2 - $v1) * 6 * $vh);
		}

		if ((2 * $vh) < 1) {
			return $v2;
		}

		if ((3 * $vh) < 2) {
			return ($v1 + ($v2 - $v1) * ((2 / 3 - $vh) * 6));
		}

		return $v1;
	}

}
