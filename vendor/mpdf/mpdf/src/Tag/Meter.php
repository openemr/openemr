<?php

namespace Mpdf\Tag;

use Mpdf\Mpdf;

class Meter extends InlineTag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();
		$this->mpdf->inMeter = true;

		$max = 1;
		if (!empty($attr['MAX'])) {
			$max = $attr['MAX'];
		}

		$min = 0;
		if (!empty($attr['MIN']) && $tag === 'METER') {
			$min = $attr['MIN'];
		}

		if ($max < $min) {
			$max = $min;
		}

		$value = '';
		if (isset($attr['VALUE']) && ($attr['VALUE'] || $attr['VALUE'] === '0')) {
			$value = $attr['VALUE'];
			if ($value < $min) {
				$value = $min;
			} elseif ($value > $max) {
				$value = $max;
			}
		}

		$low = $min;
		if (!empty($attr['LOW'])) {
			$low = $attr['LOW'];
		}
		if ($low < $min) {
			$low = $min;
		} elseif ($low > $max) {
			$low = $max;
		}
		$high = $max;
		if (!empty($attr['HIGH'])) {
			$high = $attr['HIGH'];
		}
		if ($high < $low) {
			$high = $low;
		} elseif ($high > $max) {
			$high = $max;
		}
		if (!empty($attr['OPTIMUM'])) {
			$optimum = $attr['OPTIMUM'];
		} else {
			$optimum = $min + (($max - $min) / 2);
		}
		if ($optimum < $min) {
			$optimum = $min;
		} elseif ($optimum > $max) {
			$optimum = $max;
		}
		$type = '';
		if (!empty($attr['TYPE'])) {
			$type = $attr['TYPE'];
		}
		$objattr = [];
		$objattr['margin_top'] = 0;
		$objattr['margin_bottom'] = 0;
		$objattr['margin_left'] = 0;
		$objattr['margin_right'] = 0;
		$objattr['padding_top'] = 0;
		$objattr['padding_bottom'] = 0;
		$objattr['padding_left'] = 0;
		$objattr['padding_right'] = 0;
		$objattr['width'] = 0;
		$objattr['height'] = 0;
		$objattr['border_top']['w'] = 0;
		$objattr['border_bottom']['w'] = 0;
		$objattr['border_left']['w'] = 0;
		$objattr['border_right']['w'] = 0;

		$properties = $this->cssManager->MergeCSS('INLINE', $tag, $attr);
		if (isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY']) === 'none') {
			return;
		}
		$objattr['visibility'] = 'visible';
		if (isset($properties['VISIBILITY'])) {
			$v = strtolower($properties['VISIBILITY']);
			if (($v === 'hidden' || $v === 'printonly' || $v === 'screenonly') && $this->mpdf->visibility === 'visible') {
				$objattr['visibility'] = $v;
			}
		}

		if (isset($properties['MARGIN-TOP'])) {
			$objattr['margin_top'] = $this->sizeConverter->convert(
				$properties['MARGIN-TOP'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['MARGIN-BOTTOM'])) {
			$objattr['margin_bottom'] = $this->sizeConverter->convert(
				$properties['MARGIN-BOTTOM'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['MARGIN-LEFT'])) {
			$objattr['margin_left'] = $this->sizeConverter->convert(
				$properties['MARGIN-LEFT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['MARGIN-RIGHT'])) {
			$objattr['margin_right'] = $this->sizeConverter->convert(
				$properties['MARGIN-RIGHT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}

		if (isset($properties['PADDING-TOP'])) {
			$objattr['padding_top'] = $this->sizeConverter->convert(
				$properties['PADDING-TOP'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['PADDING-BOTTOM'])) {
			$objattr['padding_bottom'] = $this->sizeConverter->convert(
				$properties['PADDING-BOTTOM'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['PADDING-LEFT'])) {
			$objattr['padding_left'] = $this->sizeConverter->convert(
				$properties['PADDING-LEFT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}
		if (isset($properties['PADDING-RIGHT'])) {
			$objattr['padding_right'] = $this->sizeConverter->convert(
				$properties['PADDING-RIGHT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		}

		if (isset($properties['BORDER-TOP'])) {
			$objattr['border_top'] = $this->mpdf->border_details($properties['BORDER-TOP']);
		}
		if (isset($properties['BORDER-BOTTOM'])) {
			$objattr['border_bottom'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
		}
		if (isset($properties['BORDER-LEFT'])) {
			$objattr['border_left'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
		}
		if (isset($properties['BORDER-RIGHT'])) {
			$objattr['border_right'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
		}

		if (isset($properties['VERTICAL-ALIGN'])) {
			$objattr['vertical-align'] = $this->getAlign($properties['VERTICAL-ALIGN']);
		}
		$w = 0;
		$h = 0;
		if (isset($properties['WIDTH'])) {
			$w = $this->sizeConverter->convert(
				$properties['WIDTH'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		} elseif (isset($attr['WIDTH'])) {
			$w = $this->sizeConverter->convert($attr['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
		}

		if (isset($properties['HEIGHT'])) {
			$h = $this->sizeConverter->convert(
				$properties['HEIGHT'],
				$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
				$this->mpdf->FontSize,
				false
			);
		} elseif (isset($attr['HEIGHT'])) {
			$h = $this->sizeConverter->convert($attr['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
		}

		if (isset($properties['OPACITY']) && $properties['OPACITY'] > 0 && $properties['OPACITY'] <= 1) {
			$objattr['opacity'] = $properties['OPACITY'];
		}
		if ($this->mpdf->HREF) {
			if (strpos($this->mpdf->HREF, '.') === false && strpos($this->mpdf->HREF, '@') !== 0) {
				$href = $this->mpdf->HREF;
				while (array_key_exists($href, $this->mpdf->internallink)) {
					$href = '#' . $href;
				}
				$this->mpdf->internallink[$href] = $this->mpdf->AddLink();
				$objattr['link'] = $this->mpdf->internallink[$href];
			} else {
				$objattr['link'] = $this->mpdf->HREF;
			}
		}
		$extraheight = $objattr['padding_top'] + $objattr['padding_bottom'] + $objattr['margin_top']
			+ $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];

		$extrawidth = $objattr['padding_left'] + $objattr['padding_right'] + $objattr['margin_left']
			+ $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

		$svg = $this->makeSVG($type, $value, $max, $min, $optimum, $low, $high);
		//Save to local file
		$srcpath = $this->cache->write('/_tempSVG' . uniqid(random_int(1, 100000), true) . '_' . strtolower($tag) . '.svg', $svg);
		$orig_srcpath = $srcpath;
		$this->mpdf->GetFullPath($srcpath);

		$info = $this->imageProcessor->getImage($srcpath, true, true, $orig_srcpath);
		if (!$info) {
			$info = $this->imageProcessor->getImage($this->mpdf->noImageFile);
			if ($info) {
				$srcpath = $this->mpdf->noImageFile;
				$w = ($info['w'] * (25.4 / $this->mpdf->img_dpi));
				$h = ($info['h'] * (25.4 / $this->mpdf->img_dpi));
			}
		}
		if (!$info) {
			return;
		}

		$objattr['file'] = $srcpath;

		// Default width and height calculation if needed
		if ($w == 0 && $h == 0) {
			// SVG units are pixels
			$w = $this->mpdf->FontSize / (10 / Mpdf::SCALE) * abs($info['w']) / Mpdf::SCALE;
			$h = $this->mpdf->FontSize / (10 / Mpdf::SCALE) * abs($info['h']) / Mpdf::SCALE;
		}

		// IF WIDTH OR HEIGHT SPECIFIED
		if ($w == 0) {
			$w = $info['h'] ? abs($h * $info['w'] / $info['h']) : INF;
		}
		if ($h == 0) {
			$h = $info['w'] ? abs($w * $info['h'] / $info['w']) : INF;
		}

		// Resize to maximum dimensions of page
		$maxWidth = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];
		$maxHeight = $this->mpdf->h - ($this->mpdf->tMargin + $this->mpdf->bMargin + 1);
		if ($this->mpdf->fullImageHeight) {
			$maxHeight = $this->mpdf->fullImageHeight;
		}
		if (($w + $extrawidth) > ($maxWidth + 0.0001)) { // mPDF 5.7.4  0.0001 to allow for rounding errors when w==maxWidth
			$w = $maxWidth - $extrawidth;
			$h = abs($w * $info['h'] / $info['w']);
		}

		if ($h + $extraheight > $maxHeight) {
			$h = $maxHeight - $extraheight;
			$w = abs($h * $info['w'] / $info['h']);
		}
		$objattr['type'] = 'image';
		$objattr['itype'] = $info['type'];

		$objattr['orig_h'] = $info['h'];
		$objattr['orig_w'] = $info['w'];
		$objattr['wmf_x'] = $info['x'];
		$objattr['wmf_y'] = $info['y'];
		$objattr['height'] = $h + $extraheight;
		$objattr['width'] = $w + $extrawidth;
		$objattr['image_height'] = $h;
		$objattr['image_width'] = $w;
		$e = "\xbb\xa4\xactype=image,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
		if ($this->mpdf->tableLevel) {
			$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
			$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
		} else {
			$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
		parent::close($ahtml, $ihtml);
		$this->mpdf->ignorefollowingspaces = false;
		$this->mpdf->inMeter = false;
	}

	protected function makeSVG($type, $value, $max, $min, $optimum, $low, $high)
	{
		if ($type == '2') {
			/////////////////////////////////////////////////////////////////////////////////////
			///////// CUSTOM <meter type="2">
			/////////////////////////////////////////////////////////////////////////////////////
			$h = 10;
			$w = 160;
			$border_radius = 0.143;  // Factor of Height

			$svg = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="' . $w . 'px" height="' . $h . 'px" viewBox="0 0 ' . $w . ' ' . $h . '" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ><g>


<defs>
<linearGradient id="GrGRAY" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(222, 222, 222)" />
<stop offset="20%" stop-color="rgb(232, 232, 232)" />
<stop offset="25%" stop-color="rgb(232, 232, 232)" />
<stop offset="100%" stop-color="rgb(182, 182, 182)" />
</linearGradient>

</defs>
';
			$svg .= '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" fill="#f4f4f4" stroke="none" />';

			// LOW to HIGH region
			//if ($low && $high && ($low != $min || $high != $max)) {
			if ($low && $high) {
				$barx = (($low - $min) / ($max - $min) ) * $w;
				$barw = (($high - $low) / ($max - $min) ) * $w;
				$svg .= '<rect x="' . $barx . '" y="0" width="' . $barw . '" height="' . $h . '" fill="url(#GrGRAY)" stroke="#888888" stroke-width="0.5px" />';
			}

			// OPTIMUM Marker (? AVERAGE)
			if ($optimum) {
				$barx = (($optimum - $min) / ($max - $min) ) * $w;
				$barw = $h / 2;
				$barcol = '#888888';
				$svg .= '<rect x="' . $barx . '" y="0" rx="' . ($h * $border_radius) . 'px" ry="' . ($h * $border_radius) . 'px" width="' . $barw . '" height="' . $h . '" fill="' . $barcol . '" stroke="none" />';
			}

			// VALUE Marker
			if ($value) {
				if ($min != $low && $value < $low) {
					$col = 'orange';
				} elseif ($max != $high && $value > $high) {
					$col = 'orange';
				} else {
					$col = '#008800';
				}
				$cx = (($value - $min) / ($max - $min) ) * $w;
				$cy = $h / 2;
				$rx = $h / 3.5;
				$ry = $h / 2.2;
				$svg .= '<ellipse fill="' . $col . '" stroke="#000000" stroke-width="0.5px" cx="' . $cx . '" cy="' . $cy . '" rx="' . $rx . '" ry="' . $ry . '"/>';
			}

			// BoRDER
			$svg .= '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" fill="none" stroke="#888888" stroke-width="0.5px" />';

			$svg .= '</g></svg>';
		} elseif ($type == '3') {
			/////////////////////////////////////////////////////////////////////////////////////
			///////// CUSTOM <meter type="2">
			/////////////////////////////////////////////////////////////////////////////////////
			$h = 10;
			$w = 100;
			$border_radius = 0.143;  // Factor of Height

			$svg = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="' . $w . 'px" height="' . $h . 'px" viewBox="0 0 ' . $w . ' ' . $h . '" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ><g>


<defs>
<linearGradient id="GrGRAY" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(222, 222, 222)" />
<stop offset="20%" stop-color="rgb(232, 232, 232)" />
<stop offset="25%" stop-color="rgb(232, 232, 232)" />
<stop offset="100%" stop-color="rgb(182, 182, 182)" />
</linearGradient>

</defs>
';
			$svg .= '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" fill="#f4f4f4" stroke="none" />';

			// LOW to HIGH region
			if ($low && $high && ($low != $min || $high != $max)) {
				//if ($low && $high) {
				$barx = (($low - $min) / ($max - $min) ) * $w;
				$barw = (($high - $low) / ($max - $min) ) * $w;
				$svg .= '<rect x="' . $barx . '" y="0" width="' . $barw . '" height="' . $h . '" fill="url(#GrGRAY)" stroke="#888888" stroke-width="0.5px" />';
			}

			// OPTIMUM Marker (? AVERAGE)
			if ($optimum) {
				$barx = (($optimum - $min) / ($max - $min) ) * $w;
				$barw = $h / 2;
				$barcol = '#888888';
				$svg .= '<rect x="' . $barx . '" y="0" rx="' . ($h * $border_radius) . 'px" ry="' . ($h * $border_radius) . 'px" width="' . $barw . '" height="' . $h . '" fill="' . $barcol . '" stroke="none" />';
			}

			// VALUE Marker
			if ($value) {
				if ($min != $low && $value < $low) {
					$col = 'orange';
				} elseif ($max != $high && $value > $high) {
					$col = 'orange';
				} else {
					$col = 'orange';
				}
				$cx = (($value - $min) / ($max - $min) ) * $w;
				$cy = $h / 2;
				$rx = $h / 2.2;
				$ry = $h / 2.2;
				$svg .= '<ellipse fill="' . $col . '" stroke="#000000" stroke-width="0.5px" cx="' . $cx . '" cy="' . $cy . '" rx="' . $rx . '" ry="' . $ry . '"/>';
			}

			// BoRDER
			$svg .= '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" fill="none" stroke="#888888" stroke-width="0.5px" />';

			$svg .= '</g></svg>';
		} else {
			/////////////////////////////////////////////////////////////////////////////////////
			///////// DEFAULT <meter>
			/////////////////////////////////////////////////////////////////////////////////////
			$h = 10;
			$w = 50;
			$border_radius = 0.143;  // Factor of Height

			$svg = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="' . $w . 'px" height="' . $h . 'px" viewBox="0 0 ' . $w . ' ' . $h . '" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" ><g>

<defs>
<linearGradient id="GrGRAY" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(222, 222, 222)" />
<stop offset="20%" stop-color="rgb(232, 232, 232)" />
<stop offset="25%" stop-color="rgb(232, 232, 232)" />
<stop offset="100%" stop-color="rgb(182, 182, 182)" />
</linearGradient>

<linearGradient id="GrRED" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(255, 162, 162)" />
<stop offset="20%" stop-color="rgb(255, 218, 218)" />
<stop offset="25%" stop-color="rgb(255, 218, 218)" />
<stop offset="100%" stop-color="rgb(255, 0, 0)" />
</linearGradient>

<linearGradient id="GrGREEN" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(102, 230, 102)" />
<stop offset="20%" stop-color="rgb(218, 255, 218)" />
<stop offset="25%" stop-color="rgb(218, 255, 218)" />
<stop offset="100%" stop-color="rgb(0, 148, 0)" />
</linearGradient>

<linearGradient id="GrBLUE" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(102, 102, 230)" />
<stop offset="20%" stop-color="rgb(238, 238, 238)" />
<stop offset="25%" stop-color="rgb(238, 238, 238)" />
<stop offset="100%" stop-color="rgb(0, 0, 128)" />
</linearGradient>

<linearGradient id="GrORANGE" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(255, 186, 0)" />
<stop offset="20%" stop-color="rgb(255, 238, 168)" />
<stop offset="25%" stop-color="rgb(255, 238, 168)" />
<stop offset="100%" stop-color="rgb(255, 155, 0)" />
</linearGradient>
</defs>

<rect x="0" y="0" rx="' . ($h * $border_radius) . 'px" ry="' . ($h * $border_radius) . 'px" width="' . $w . '" height="' . $h . '" fill="url(#GrGRAY)" stroke="none" />
';

			if ($value) {
				$barw = (($value - $min) / ($max - $min) ) * $w;
				if ($optimum < $low) {
					if ($value < $low) {
						$barcol = 'url(#GrGREEN)';
					} elseif ($value > $high) {
						$barcol = 'url(#GrRED)';
					} else {
						$barcol = 'url(#GrORANGE)';
					}
				} elseif ($optimum > $high) {
					if ($value < $low) {
						$barcol = 'url(#GrRED)';
					} elseif ($value > $high) {
						$barcol = 'url(#GrGREEN)';
					} else {
						$barcol = 'url(#GrORANGE)';
					}
				} else {
					if ($value < $low) {
						$barcol = 'url(#GrORANGE)';
					} elseif ($value > $high) {
						$barcol = 'url(#GrORANGE)';
					} else {
						$barcol = 'url(#GrGREEN)';
					}
				}
				$svg .= '<rect x="0" y="0" rx="' . ($h * $border_radius) . 'px" ry="' . ($h * $border_radius) . 'px" width="' . $barw . '" height="' . $h . '" fill="' . $barcol . '" stroke="none" />';
			}

			$svg .= '</g></svg>';
		}


		return $svg;
	}

}
