<?php

namespace Mpdf\Writer;

use Mpdf\Strict;
use Mpdf\Mpdf;

final class BackgroundWriter
{

	use Strict;

	/**
	 * @var \Mpdf\Mpdf
	 */
	private $mpdf;

	/**
	 * @var \Mpdf\Writer\BaseWriter
	 */
	private $writer;

	public function __construct(Mpdf $mpdf, BaseWriter $writer)
	{
		$this->mpdf = $mpdf;
		$this->writer = $writer;
	}

	public function writePatterns() // _putpatterns
	{
		$patternCount = count($this->mpdf->patterns);

		for ($i = 1; $i <= $patternCount; $i++) {

			$x = $this->mpdf->patterns[$i]['x'];
			$y = $this->mpdf->patterns[$i]['y'];
			$w = $this->mpdf->patterns[$i]['w'];
			$h = $this->mpdf->patterns[$i]['h'];
			$pgh = $this->mpdf->patterns[$i]['pgh'];
			$orig_w = $this->mpdf->patterns[$i]['orig_w'];
			$orig_h = $this->mpdf->patterns[$i]['orig_h'];
			$image_id = $this->mpdf->patterns[$i]['image_id'];
			$itype = $this->mpdf->patterns[$i]['itype'];

			if (isset($this->mpdf->patterns[$i]['bpa'])) {
				$bpa = $this->mpdf->patterns[$i]['bpa'];
			} else {
				$bpa = []; // background positioning area
			}

			if ($this->mpdf->patterns[$i]['x_repeat']) {
				$x_repeat = true;
			} else {
				$x_repeat = false;
			}

			if ($this->mpdf->patterns[$i]['y_repeat']) {
				$y_repeat = true;
			} else {
				$y_repeat = false;
			}

			$x_pos = $this->mpdf->patterns[$i]['x_pos'];

			if (false !== strpos($x_pos, '%')) {
				$x_pos = (float) $x_pos;
				$x_pos /= 100;

				if (isset($bpa['w']) && $bpa['w']) {
					$x_pos = ($bpa['w'] * $x_pos) - ($orig_w / Mpdf::SCALE * $x_pos);
				} else {
					$x_pos = ($w * $x_pos) - ($orig_w / Mpdf::SCALE * $x_pos);
				}
			}

			$y_pos = $this->mpdf->patterns[$i]['y_pos'];

			if (false !== strpos($y_pos, '%')) {
				$y_pos = (float) $y_pos;
				$y_pos /= 100;

				if (isset($bpa['h']) && $bpa['h']) {
					$y_pos = ($bpa['h'] * $y_pos) - ($orig_h / Mpdf::SCALE * $y_pos);
				} else {
					$y_pos = ($h * $y_pos) - ($orig_h / Mpdf::SCALE * $y_pos);
				}
			}

			if (isset($bpa['x']) && $bpa['x']) {
				$adj_x = ($x_pos + $bpa['x']) * Mpdf::SCALE;
			} else {
				$adj_x = ($x_pos + $x) * Mpdf::SCALE;
			}

			if (isset($bpa['y']) && $bpa['y']) {
				$adj_y = (($pgh - $y_pos - $bpa['y']) * Mpdf::SCALE) - $orig_h;
			} else {
				$adj_y = (($pgh - $y_pos - $y) * Mpdf::SCALE) - $orig_h;
			}

			$img_obj = false;

			if ($itype === 'svg' || $itype === 'wmf') {
				foreach ($this->mpdf->formobjects as $fo) {
					if ($fo['i'] == $image_id) {
						$img_obj = $fo['n'];
						$fo_w = $fo['w'];
						$fo_h = -$fo['h'];
						$wmf_x = $fo['x'];
						$wmf_y = $fo['y'];
						break;
					}
				}
			} else {
				foreach ($this->mpdf->images as $img) {
					if ($img['i'] == $image_id) {
						$img_obj = $img['n'];
						break;
					}
				}
			}

			if (!$img_obj) {
				throw new \Mpdf\MpdfException('Problem: Image object not found for background pattern ' . $img['i']);
			}

			$this->writer->object();
			$this->writer->write('<</ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');

			if ($itype === 'svg' || $itype === 'wmf') {
				$this->writer->write('/XObject <</FO' . $image_id . ' ' . $img_obj . ' 0 R >>');

				// ******* ADD ANY ExtGStates, Shading AND Fonts needed for the FormObject
				// Set in classes/svg array['fo'] = true
				// Required that _putshaders comes before _putpatterns in _putresources
				// This adds any resources associated with any FormObject to every Formobject - overkill but works!
				if (count($this->mpdf->extgstates)) {
					$this->writer->write('/ExtGState <<');
					foreach ($this->mpdf->extgstates as $k => $extgstate) {
						if (isset($extgstate['fo']) && $extgstate['fo']) {
							if (isset($extgstate['trans'])) {
								$this->writer->write('/' . $extgstate['trans'] . ' ' . $extgstate['n'] . ' 0 R');
							} else {
								$this->writer->write('/GS' . $k . ' ' . $extgstate['n'] . ' 0 R');
							}
						}
					}
					$this->writer->write('>>');
				}

				/* -- BACKGROUNDS -- */
				if (isset($this->mpdf->gradients) && ( count($this->mpdf->gradients) > 0)) {
					$this->writer->write('/Shading <<');
					foreach ($this->mpdf->gradients as $id => $grad) {
						if (isset($grad['fo']) && $grad['fo']) {
							$this->writer->write('/Sh' . $id . ' ' . $grad['id'] . ' 0 R');
						}
					}
					$this->writer->write('>>');
				}

				/* -- END BACKGROUNDS -- */
				$this->writer->write('/Font <<');

				foreach ($this->mpdf->fonts as $font) {
					if (!$font['used'] && $font['type'] === 'TTF') {
						continue;
					}
					if (isset($font['fo']) && $font['fo']) {
						if ($font['type'] === 'TTF' && ($font['sip'] || $font['smp'])) {
							foreach ($font['n'] as $k => $fid) {
								$this->writer->write('/F' . $font['subsetfontids'][$k] . ' ' . $font['n'][$k] . ' 0 R');
							}
						} else {
							$this->writer->write('/F' . $font['i'] . ' ' . $font['n'] . ' 0 R');
						}
					}
				}
				$this->writer->write('>>');
			} else {
				$this->writer->write('/XObject <</I' . $image_id . ' ' . $img_obj . ' 0 R >>');
			}

			$this->writer->write('>>');
			$this->writer->write('endobj');

			$this->writer->object();
			$this->mpdf->patterns[$i]['n'] = $this->mpdf->n;
			$this->writer->write('<< /Type /Pattern /PatternType 1 /PaintType 1 /TilingType 2');
			$this->writer->write('/Resources ' . ($this->mpdf->n - 1) . ' 0 R');

			$this->writer->write(sprintf('/BBox [0 0 %.3F %.3F]', $orig_w, $orig_h));

			if ($x_repeat) {
				$this->writer->write(sprintf('/XStep %.3F', $orig_w));
			} else {
				$this->writer->write(sprintf('/XStep %d', 99999));
			}

			if ($y_repeat) {
				$this->writer->write(sprintf('/YStep %.3F', $orig_h));
			} else {
				$this->writer->write(sprintf('/YStep %d', 99999));
			}

			if ($itype === 'svg' || $itype === 'wmf') {
				$this->writer->write(sprintf('/Matrix [1 0 0 -1 %.3F %.3F]', $adj_x, $adj_y + $orig_h));
				$s = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $orig_w / $fo_w, -$orig_h / $fo_h, -($orig_w / $fo_w) * $wmf_x, ($orig_w / $fo_w) * $wmf_y, $image_id);
			} else {
				$this->writer->write(sprintf('/Matrix [1 0 0 1 %.3F %.3F]', $adj_x, $adj_y));
				$s = sprintf('q %.3F 0 0 %.3F 0 0 cm /I%d Do Q', $orig_w, $orig_h, $image_id);
			}

			if ($this->mpdf->compress) {
				$this->writer->write('/Filter /FlateDecode');
				$s = gzcompress($s);
			}
			$this->writer->write('/Length ' . strlen($s) . '>>');
			$this->writer->stream($s);
			$this->writer->write('endobj');
		}
	}

	public function writeShaders() // _putshaders
	{
		$maxid = count($this->mpdf->gradients); // index for transparency gradients

		foreach ($this->mpdf->gradients as $id => $grad) {

			if (empty($grad['is_mask']) && ($grad['type'] == 2 || $grad['type'] == 3)) {

				$this->writer->object();
				$this->writer->write('<<');
				$this->writer->write('/FunctionType 3');
				$this->writer->write('/Domain [0 1]');

				$fn = [];
				$bd = [];
				$en = [];

				for ($i = 0; $i < (count($grad['stops']) - 1); $i++) {
					$fn[] = ($this->mpdf->n + 1 + $i) . ' 0 R';
					$en[] = '0 1';
					if ($i > 0) {
						$bd[] = sprintf('%.3F', $grad['stops'][$i]['offset']);
					}
				}

				$this->writer->write('/Functions [' . implode(' ', $fn) . ']');
				$this->writer->write('/Bounds [' . implode(' ', $bd) . ']');
				$this->writer->write('/Encode [' . implode(' ', $en) . ']');
				$this->writer->write('>>');
				$this->writer->write('endobj');

				$f1 = $this->mpdf->n;

				for ($i = 0; $i < (count($grad['stops']) - 1); $i++) {
					$this->writer->object();
					$this->writer->write('<<');
					$this->writer->write('/FunctionType 2');
					$this->writer->write('/Domain [0 1]');
					$this->writer->write('/C0 [' . $grad['stops'][$i]['col'] . ']');
					$this->writer->write('/C1 [' . $grad['stops'][$i + 1]['col'] . ']');
					$this->writer->write('/N 1');
					$this->writer->write('>>');
					$this->writer->write('endobj');
				}
			}

			if ($grad['type'] == 2 || $grad['type'] == 3) {

				if (isset($grad['trans']) && $grad['trans']) {

					$this->writer->object();
					$this->writer->write('<<');
					$this->writer->write('/FunctionType 3');
					$this->writer->write('/Domain [0 1]');

					$fn = [];
					$bd = [];
					$en = [];

					for ($i = 0; $i < (count($grad['stops']) - 1); $i++) {
						$fn[] = ($this->mpdf->n + 1 + $i) . ' 0 R';
						$en[] = '0 1';
						if ($i > 0) {
							$bd[] = sprintf('%.3F', $grad['stops'][$i]['offset']);
						}
					}

					$this->writer->write('/Functions [' . implode(' ', $fn) . ']');
					$this->writer->write('/Bounds [' . implode(' ', $bd) . ']');
					$this->writer->write('/Encode [' . implode(' ', $en) . ']');
					$this->writer->write('>>');
					$this->writer->write('endobj');

					$f2 = $this->mpdf->n;

					for ($i = 0; $i < (count($grad['stops']) - 1); $i++) {
						$this->writer->object();
						$this->writer->write('<<');
						$this->writer->write('/FunctionType 2');
						$this->writer->write('/Domain [0 1]');
						$this->writer->write(sprintf('/C0 [%.3F]', $grad['stops'][$i]['opacity']));
						$this->writer->write(sprintf('/C1 [%.3F]', $grad['stops'][$i + 1]['opacity']));
						$this->writer->write('/N 1');
						$this->writer->write('>>');
						$this->writer->write('endobj');
					}
				}
			}

			if (empty($grad['is_mask'])) {

				$this->writer->object();
				$this->writer->write('<<');
				$this->writer->write('/ShadingType ' . $grad['type']);

				if (isset($grad['colorspace'])) {
					$this->writer->write('/ColorSpace /Device' . $grad['colorspace']);  // Can use CMYK if all C0 and C1 above have 4 values
				} else {
					$this->writer->write('/ColorSpace /DeviceRGB');
				}

				if ($grad['type'] == 2) {
					$this->writer->write(sprintf('/Coords [%.3F %.3F %.3F %.3F]', $grad['coords'][0], $grad['coords'][1], $grad['coords'][2], $grad['coords'][3]));
					$this->writer->write('/Function ' . $f1 . ' 0 R');
					$this->writer->write('/Extend [' . $grad['extend'][0] . ' ' . $grad['extend'][1] . '] ');
					$this->writer->write('>>');
				} elseif ($grad['type'] == 3) {
					// x0, y0, r0, x1, y1, r1
					// at this this time radius of inner circle is 0
					$ir = 0;
					if (isset($grad['coords'][5]) && $grad['coords'][5]) {
						$ir = $grad['coords'][5];
					}
					$this->writer->write(sprintf('/Coords [%.3F %.3F %.3F %.3F %.3F %.3F]', $grad['coords'][0], $grad['coords'][1], $ir, $grad['coords'][2], $grad['coords'][3], $grad['coords'][4]));
					$this->writer->write('/Function ' . $f1 . ' 0 R');
					$this->writer->write('/Extend [' . $grad['extend'][0] . ' ' . $grad['extend'][1] . '] ');
					$this->writer->write('>>');
				} elseif ($grad['type'] == 6) {
					$this->writer->write('/BitsPerCoordinate 16');
					$this->writer->write('/BitsPerComponent 8');
					if ($grad['colorspace'] === 'CMYK') {
						$this->writer->write('/Decode[0 1 0 1 0 1 0 1 0 1 0 1]');
					} elseif ($grad['colorspace'] === 'Gray') {
						$this->writer->write('/Decode[0 1 0 1 0 1]');
					} else {
						$this->writer->write('/Decode[0 1 0 1 0 1 0 1 0 1]');
					}
					$this->writer->write('/BitsPerFlag 8');
					$this->writer->write('/Length ' . strlen($grad['stream']));
					$this->writer->write('>>');
					$this->writer->stream($grad['stream']);
				}

				$this->writer->write('endobj');
			}

			$this->mpdf->gradients[$id]['id'] = $this->mpdf->n;

			// set pattern object
			$this->writer->object();
			$out = '<< /Type /Pattern /PatternType 2';
			$out .= ' /Shading ' . $this->mpdf->gradients[$id]['id'] . ' 0 R';
			$out .= ' >>';
			$out .= "\n" . 'endobj';
			$this->writer->write($out);


			$this->mpdf->gradients[$id]['pattern'] = $this->mpdf->n;

			if (isset($grad['trans']) && $grad['trans']) {

				// luminosity pattern
				$transid = $id + $maxid;

				$this->writer->object();
				$this->writer->write('<<');
				$this->writer->write('/ShadingType ' . $grad['type']);
				$this->writer->write('/ColorSpace /DeviceGray');

				if ($grad['type'] == 2) {
					$this->writer->write(sprintf('/Coords [%.3F %.3F %.3F %.3F]', $grad['coords'][0], $grad['coords'][1], $grad['coords'][2], $grad['coords'][3]));
					$this->writer->write('/Function ' . $f2 . ' 0 R');
					$this->writer->write('/Extend [' . $grad['extend'][0] . ' ' . $grad['extend'][1] . '] ');
					$this->writer->write('>>');
				} elseif ($grad['type'] == 3) {
					// x0, y0, r0, x1, y1, r1
					// at this this time radius of inner circle is 0
					$ir = 0;
					if (isset($grad['coords'][5]) && $grad['coords'][5]) {
						$ir = $grad['coords'][5];
					}
					$this->writer->write(sprintf('/Coords [%.3F %.3F %.3F %.3F %.3F %.3F]', $grad['coords'][0], $grad['coords'][1], $ir, $grad['coords'][2], $grad['coords'][3], $grad['coords'][4]));
					$this->writer->write('/Function ' . $f2 . ' 0 R');
					$this->writer->write('/Extend [' . $grad['extend'][0] . ' ' . $grad['extend'][1] . '] ');
					$this->writer->write('>>');
				} elseif ($grad['type'] == 6) {
					$this->writer->write('/BitsPerCoordinate 16');
					$this->writer->write('/BitsPerComponent 8');
					$this->writer->write('/Decode[0 1 0 1 0 1]');
					$this->writer->write('/BitsPerFlag 8');
					$this->writer->write('/Length ' . strlen($grad['stream_trans']));
					$this->writer->write('>>');
					$this->writer->stream($grad['stream_trans']);
				}
				$this->writer->write('endobj');

				$this->mpdf->gradients[$transid]['id'] = $this->mpdf->n;

				$this->writer->object();
				$this->writer->write('<< /Type /Pattern /PatternType 2');
				$this->writer->write('/Shading ' . $this->mpdf->gradients[$transid]['id'] . ' 0 R');
				$this->writer->write('>>');
				$this->writer->write('endobj');

				$this->mpdf->gradients[$transid]['pattern'] = $this->mpdf->n;
				$this->writer->object();

				// Need to extend size of viewing box in case of transformations
				$str = 'q /a0 gs /Pattern cs /p' . $transid . ' scn -' . ($this->mpdf->wPt / 2) . ' -' . ($this->mpdf->hPt / 2) . ' ' . (2 * $this->mpdf->wPt) . ' ' . (2 * $this->mpdf->hPt) . ' re f Q';
				$filter = ($this->mpdf->compress) ? '/Filter /FlateDecode ' : '';
				$p = ($this->mpdf->compress) ? gzcompress($str) : $str;

				$this->writer->write('<< /Type /XObject /Subtype /Form /FormType 1 ' . $filter);
				$this->writer->write('/Length ' . strlen($p));
				$this->writer->write('/BBox [-' . ($this->mpdf->wPt / 2) . ' -' . ($this->mpdf->hPt / 2) . ' ' . (2 * $this->mpdf->wPt) . ' ' . (2 * $this->mpdf->hPt) . ']');
				$this->writer->write('/Group << /Type /Group /S /Transparency /CS /DeviceGray >>');
				$this->writer->write('/Resources <<');
				$this->writer->write('/ExtGState << /a0 << /ca 1 /CA 1 >> >>');
				$this->writer->write('/Pattern << /p' . $transid . ' ' . $this->mpdf->gradients[$transid]['pattern'] . ' 0 R >>');
				$this->writer->write('>>');
				$this->writer->write('>>');
				$this->writer->stream($p);
				$this->writer->write('endobj');
				$this->writer->object();
				$this->writer->write('<< /Type /Mask /S /Luminosity /G ' . ($this->mpdf->n - 1) . ' 0 R >>' . "\n" . 'endobj');
				$this->writer->object();
				$this->writer->write('<< /Type /ExtGState /SMask ' . ($this->mpdf->n - 1) . ' 0 R /AIS false >>' . "\n" . 'endobj');

				if (isset($grad['fo']) && $grad['fo']) {
					$this->mpdf->extgstates[] = ['n' => $this->mpdf->n, 'trans' => 'TGS' . $id, 'fo' => true];
				} else {
					$this->mpdf->extgstates[] = ['n' => $this->mpdf->n, 'trans' => 'TGS' . $id];
				}
			}
		}
	}

}
