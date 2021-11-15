<?php

namespace Mpdf\Tag;

use Mpdf\Mpdf;

class Img extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$this->mpdf->ignorefollowingspaces = false;
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
		if (isset($attr['SRC'])) {
			$srcpath = $attr['SRC'];
			$orig_srcpath = (isset($attr['ORIG_SRC']) ? $attr['ORIG_SRC'] : '');
			$properties = $this->cssManager->MergeCSS('', 'IMG', $attr);
			if (isset($properties ['DISPLAY']) && strtolower($properties ['DISPLAY']) === 'none') {
				return;
			}
			if (isset($properties['Z-INDEX']) && $this->mpdf->current_layer == 0) {
				$v = (int) $properties['Z-INDEX'];
				if ($v > 0) {
					$objattr['z-index'] = $v;
				}
			}

			$objattr['visibility'] = 'visible';
			if (isset($properties['VISIBILITY'])) {
				$v = strtolower($properties['VISIBILITY']);
				if (($v === 'hidden' || $v === 'printonly' || $v === 'screenonly') && $this->mpdf->visibility === 'visible') {
					$objattr['visibility'] = $v;
				}
			}

			// VSPACE and HSPACE converted to margins in MergeCSS
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
				$w = $this->sizeConverter->convert(
					$attr['WIDTH'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			if (isset($properties['HEIGHT'])) {
				$h = $this->sizeConverter->convert(
					$properties['HEIGHT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			} elseif (isset($attr['HEIGHT'])) {
				$h = $this->sizeConverter->convert(
					$attr['HEIGHT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			$maxw = $maxh = $minw = $minh = false;
			if (isset($properties['MAX-WIDTH'])) {
				$maxw = $this->sizeConverter->convert(
					$properties['MAX-WIDTH'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			} elseif (isset($attr['MAX-WIDTH'])) {
				$maxw = $this->sizeConverter->convert(
					$attr['MAX-WIDTH'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			if (isset($properties['MAX-HEIGHT'])) {
				$maxh = $this->sizeConverter->convert(
					$properties['MAX-HEIGHT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			} elseif (isset($attr['MAX-HEIGHT'])) {
				$maxh = $this->sizeConverter->convert(
					$attr['MAX-HEIGHT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			if (isset($properties['MIN-WIDTH'])) {
				$minw = $this->sizeConverter->convert(
					$properties['MIN-WIDTH'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			} elseif (isset($attr['MIN-WIDTH'])) {
				$minw = $this->sizeConverter->convert(
					$attr['MIN-WIDTH'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			}
			if (isset($properties['MIN-HEIGHT'])) {
				$minh = $this->sizeConverter->convert(
					$properties['MIN-HEIGHT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
			} elseif (isset($attr['MIN-HEIGHT'])) {
				$minh = $this->sizeConverter->convert(
					$attr['MIN-HEIGHT'],
					$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
					$this->mpdf->FontSize,
					false
				);
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

			// mPDF 5.7.3 TRANSFORMS
			if (isset($properties['BACKGROUND-COLOR']) && $properties['BACKGROUND-COLOR'] != '') {
				$objattr['bgcolor'] = $this->colorConverter->convert($properties['BACKGROUND-COLOR'], $this->mpdf->PDFAXwarnings);
			}

			/* -- BACKGROUNDS -- */
			if (isset($properties['GRADIENT-MASK']) && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $properties['GRADIENT-MASK'])) {
				$objattr['GRADIENT-MASK'] = $properties['GRADIENT-MASK'];
			}
			/* -- END BACKGROUNDS -- */

			// mPDF 6
			$interpolation = false;
			if (!empty($properties['IMAGE-RENDERING'])) {
				$interpolation = false;
				if (strtolower($properties['IMAGE-RENDERING']) === 'crisp-edges') {
					$interpolation = false;
				} elseif (strtolower($properties['IMAGE-RENDERING']) === 'optimizequality') {
					$interpolation = true;
				} elseif (strtolower($properties['IMAGE-RENDERING']) === 'smooth') {
					$interpolation = true;
				} elseif (strtolower($properties['IMAGE-RENDERING']) === 'auto') {
					$interpolation = $this->mpdf->interpolateImages;
				}
				$info['interpolation'] = $interpolation;
			}

			// Image file
			$info = $this->imageProcessor->getImage($srcpath, true, true, $orig_srcpath, $interpolation); // mPDF 6
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

			$image_orientation = 0;
			if (isset($attr['ROTATE'])) {
				$image_orientation = $attr['ROTATE'];
			} elseif (isset($properties['IMAGE-ORIENTATION'])) {
				$image_orientation = $properties['IMAGE-ORIENTATION'];
			}
			if ($image_orientation) {
				if ($image_orientation == 90 || $image_orientation == -90 || $image_orientation == 270) {
					$tmpw = $info['w'];
					$info['w'] = $info['h'];
					$info['h'] = $tmpw;
				}
				$objattr['ROTATE'] = $image_orientation;
			}

			$objattr['file'] = $srcpath;
			//Default width and height calculation if needed
			if ($w == 0 && $h == 0) {
				/* -- IMAGES-WMF -- */
				if ($info['type'] === 'wmf') {
					// WMF units are twips (1/20pt)
					// divide by 20 to get points
					// divide by k to get user units
					$w = abs($info['w']) / (20 * Mpdf::SCALE);
					$h = abs($info['h']) / (20 * Mpdf::SCALE);
				} else { 							/* -- END IMAGES-WMF -- */
					if ($info['type'] === 'svg') {
						// SVG units are pixels
						$w = abs($info['w']) / Mpdf::SCALE;
						$h = abs($info['h']) / Mpdf::SCALE;
					} else {
						//Put image at default image dpi
						$w = ($info['w'] / Mpdf::SCALE) * (72 / $this->mpdf->img_dpi);
						$h = ($info['h'] / Mpdf::SCALE) * (72 / $this->mpdf->img_dpi);
					}
				}
				if (isset($properties['IMAGE-RESOLUTION'])) {
					if (preg_match('/from-image/i', $properties['IMAGE-RESOLUTION']) && isset($info['set-dpi']) && $info['set-dpi'] > 0) {
						$w *= $this->mpdf->img_dpi / $info['set-dpi'];
						$h *= $this->mpdf->img_dpi / $info['set-dpi'];
					} elseif (preg_match('/(\d+)dpi/i', $properties['IMAGE-RESOLUTION'], $m)) {
						$dpi = $m[1];
						if ($dpi > 0) {
							$w *= $this->mpdf->img_dpi / $dpi;
							$h *= $this->mpdf->img_dpi / $dpi;
						}
					}
				}
			}
			// IF WIDTH OR HEIGHT SPECIFIED
			if ($w == 0) {
				$w = $info['h'] ? abs($h * $info['w'] / $info['h']) : INF;
			}

			if ($h == 0) {
				$h = $info['w'] ? abs($w * $info['h'] / $info['w']) : INF;
			}

			if ($minw && $w < $minw) {
				$w = $minw;
				$h = $info['w'] ? abs($w * $info['h'] / $info['w']) : INF;
			}
			if ($maxw && $w > $maxw) {
				$w = $maxw;
				$h = $info['w'] ? abs($w * $info['h'] / $info['w']) : INF;
			}
			if ($minh && $h < $minh) {
				$h = $minh;
				$w = $info['h'] ? abs($h * $info['w'] / $info['h']) : INF;
			}
			if ($maxh && $h > $maxh) {
				$h = $maxh;
				$w = $info['h'] ? abs($h * $info['w'] / $info['h']) : INF;
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
			/* -- IMAGES-WMF -- */
			if ($info['type'] === 'wmf') {
				$objattr['wmf_x'] = $info['x'];
				$objattr['wmf_y'] = $info['y'];
			} else { 						/* -- END IMAGES-WMF -- */
				if ($info['type'] === 'svg') {
					$objattr['wmf_x'] = $info['x'];
					$objattr['wmf_y'] = $info['y'];
				}
			}
			$objattr['height'] = $h + $extraheight;
			$objattr['width'] = $w + $extrawidth;
			$objattr['image_height'] = $h;
			$objattr['image_width'] = $w;
			/* -- CSS-IMAGE-FLOAT -- */
			if (!$this->mpdf->ColActive && !$this->mpdf->tableLevel && !$this->mpdf->listlvl && !$this->mpdf->kwt) {
				if (isset($properties['FLOAT']) && (strtoupper($properties['FLOAT']) === 'RIGHT' || strtoupper($properties['FLOAT']) === 'LEFT')) {
					$objattr['float'] = strtoupper(substr($properties['FLOAT'], 0, 1));
				}
			}
			/* -- END CSS-IMAGE-FLOAT -- */
			// mPDF 5.7.3 TRANSFORMS
			if (isset($properties['TRANSFORM']) && !$this->mpdf->ColActive && !$this->mpdf->kwt) {
				$objattr['transform'] = $properties['TRANSFORM'];
			}

			$e = "\xbb\xa4\xactype=image,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

			/* -- TABLES -- */
			// Output it to buffers
			if ($this->mpdf->tableLevel) {
				$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
			} else {
				/* -- END TABLES -- */
				$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
			} // *TABLES*
			/* -- ANNOTATIONS -- */
			if ($this->mpdf->title2annots && isset($attr['TITLE'])) {
				$objattr = [];
				$objattr['margin_top'] = 0;
				$objattr['margin_bottom'] = 0;
				$objattr['margin_left'] = 0;
				$objattr['margin_right'] = 0;
				$objattr['width'] = 0;
				$objattr['height'] = 0;
				$objattr['border_top']['w'] = 0;
				$objattr['border_bottom']['w'] = 0;
				$objattr['border_left']['w'] = 0;
				$objattr['border_right']['w'] = 0;
				$objattr['CONTENT'] = $attr['TITLE'];
				$objattr['type'] = 'annot';
				$objattr['POS-X'] = 0;
				$objattr['POS-Y'] = 0;
				$objattr['ICON'] = 'Comment';
				$objattr['AUTHOR'] = '';
				$objattr['SUBJECT'] = '';
				$objattr['OPACITY'] = $this->mpdf->annotOpacity;
				$objattr['COLOR'] = $this->colorConverter->convert('yellow', $this->mpdf->PDFAXwarnings);
				$e = "\xbb\xa4\xactype=annot,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
				if ($this->mpdf->tableLevel) { // *TABLES*
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][] = [$e]; // *TABLES*
				} // *TABLES*
				else { // *TABLES*
					$this->mpdf->textbuffer[] = [$e];
				} // *TABLES*
			}
			/* -- END ANNOTATIONS -- */
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
