<?php

namespace Mpdf\Tag;

class Progress extends Meter
{
	protected function makeSVG($type, $value, $max, $min, $optimum, $low, $high)
	{
		$svg = '';

		if ($type == '2') {
			/////////////////////////////////////////////////////////////////////////////////////
			///////// CUSTOM <progress type="2">
			/////////////////////////////////////////////////////////////////////////////////////
		} else {
			/////////////////////////////////////////////////////////////////////////////////////
			///////// DEFAULT <progress>
			/////////////////////////////////////////////////////////////////////////////////////
			$h = 10;
			$w = 100;
			$border_radius = 0.143;  // Factor of Height

			if ($value or $value === '0') {
				$fill = 'url(#GrGRAY)';
			} else {
				$fill = '#f8f8f8';
			}

			$svg = '<svg width="' . $w . 'px" height="' . $h . 'px" viewBox="0 0 ' . $w . ' ' . $h . '"><g>

<defs>
<linearGradient id="GrGRAY" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(222, 222, 222)" />
<stop offset="20%" stop-color="rgb(232, 232, 232)" />
<stop offset="25%" stop-color="rgb(232, 232, 232)" />
<stop offset="100%" stop-color="rgb(182, 182, 182)" />
</linearGradient>

<linearGradient id="GrGREEN" x1="0" y1="0" x2="0" y2="1" gradientUnits="boundingBox">
<stop offset="0%" stop-color="rgb(102, 230, 102)" />
<stop offset="20%" stop-color="rgb(218, 255, 218)" />
<stop offset="25%" stop-color="rgb(218, 255, 218)" />
<stop offset="100%" stop-color="rgb(0, 148, 0)" />
</linearGradient>

</defs>

<rect x="0" y="0" rx="' . ($h * $border_radius) . 'px" ry="' . ($h * $border_radius) . 'px" width="' . $w . '" height="' . $h . '" fill="' . $fill . '" stroke="none" />
';

			if ($value) {
				$barw = (($value - $min) / ($max - $min) ) * $w;
				$barcol = 'url(#GrGREEN)';
				$svg .= '<rect x="0" y="0" rx="' . ($h * $border_radius) . 'px" ry="' . ($h * $border_radius) . 'px" width="' . $barw . '" height="' . $h . '" fill="' . $barcol . '" stroke="none" />';
			}


			// Borders
			$svg .= '<rect x="0" y="0" rx="' . ($h * $border_radius) . 'px" ry="' . ($h * $border_radius) . 'px" width="' . $w . '" height="' . $h . '" fill="none" stroke="#888888" stroke-width="0.5px" />';
			if ($value) {
				//  $svg .= '<rect x="0" y="0" rx="'.($h*$border_radius).'px" ry="'.($h*$border_radius).'px" width="'.$barw.'" height="'.$h.'" fill="none" stroke="#888888" stroke-width="0.5px" />';
			}


			$svg .= '</g></svg>';
		}


		return $svg;
	}

}
