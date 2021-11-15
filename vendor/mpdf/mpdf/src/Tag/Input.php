<?php

namespace Mpdf\Tag;

use Mpdf\Mpdf;
use Mpdf\Utils\UtfString;

class Input extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();
		$this->mpdf->ignorefollowingspaces = false;
		if (!isset($attr['TYPE'])) {
			$attr['TYPE'] = 'TEXT';
		}
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
		$objattr['type'] = 'input';
		if (isset($attr['DISABLED'])) {
			$objattr['disabled'] = true;
		}
		if (isset($attr['READONLY'])) {
			$objattr['readonly'] = true;
		}
		if (isset($attr['REQUIRED'])) {
			$objattr['required'] = true;
		}
		if (isset($attr['SPELLCHECK']) && strtolower($attr['SPELLCHECK']) === 'true') {
			$objattr['spellcheck'] = true;
		}
		if (isset($attr['TITLE'])) {
			$objattr['title'] = $attr['TITLE'];
		} elseif (isset($attr['ALT'])) {
			$objattr['title'] = $attr['ALT'];
		} else {
			$objattr['title'] = '';
		}
		$objattr['title'] = UtfString::strcode2utf($objattr['title']);
		$objattr['title'] = $this->mpdf->lesser_entity_decode($objattr['title']);
		if ($this->mpdf->onlyCoreFonts) {
			$objattr['title'] = mb_convert_encoding($objattr['title'], $this->mpdf->mb_enc, 'UTF-8');
		}
		if ($this->mpdf->useActiveForms && isset($attr['NAME'])) {
			$objattr['fieldname'] = $attr['NAME'];
		}
		if (isset($attr['VALUE'])) {
			$attr['VALUE'] = UtfString::strcode2utf($attr['VALUE']);
			$attr['VALUE'] = $this->mpdf->lesser_entity_decode($attr['VALUE']);
			if ($this->mpdf->onlyCoreFonts) {
				$attr['VALUE'] = mb_convert_encoding($attr['VALUE'], $this->mpdf->mb_enc, 'UTF-8');
			}
			$objattr['value'] = $attr['VALUE'];
		}

		$this->mpdf->InlineProperties['INPUT'] = $this->mpdf->saveInlineProperties();
		$properties = $this->cssManager->MergeCSS('', 'INPUT', $attr);
		$objattr['vertical-align'] = '';

		if (isset($properties['FONT-FAMILY'])) {
			$this->mpdf->SetFont($properties['FONT-FAMILY'], $this->mpdf->FontStyle, 0, false);
		}
		if (isset($properties['FONT-SIZE'])) {
			$mmsize = $this->sizeConverter->convert($properties['FONT-SIZE'], $this->mpdf->default_font_size / Mpdf::SCALE);
			$this->mpdf->SetFontSize($mmsize * Mpdf::SCALE, false);
		}
		if (isset($properties['COLOR'])) {
			$objattr['color'] = $this->colorConverter->convert($properties['COLOR'], $this->mpdf->PDFAXwarnings);
		}
		$objattr['fontfamily'] = $this->mpdf->FontFamily;
		$objattr['fontsize'] = $this->mpdf->FontSizePt;
		if ($this->mpdf->useActiveForms) {
			if (isset($attr['ALIGN'])) {
				$objattr['text_align'] = $this->getAlign($attr['ALIGN']);
			} elseif (isset($properties['TEXT-ALIGN'])) {
				$objattr['text_align'] = $this->getAlign($properties['TEXT-ALIGN']);
			}
			if (isset($properties['BORDER-TOP-COLOR'])) {
				$objattr['border-col'] = $this->colorConverter->convert($properties['BORDER-TOP-COLOR'], $this->mpdf->PDFAXwarnings);
			}
			if (isset($properties['BACKGROUND-COLOR'])) {
				$objattr['background-col'] = $this->colorConverter->convert($properties['BACKGROUND-COLOR'], $this->mpdf->PDFAXwarnings);
			}
		}

		$type = '';
		$texto = '';
		$height = $this->mpdf->FontSize;
		$width = 0;
		$spacesize = $this->mpdf->GetCharWidth(' ', false);

		$w = 0;
		if (isset($properties['WIDTH'])) {
			$w = $this->sizeConverter->convert($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
		}

		if ($properties['VERTICAL-ALIGN']) {
			$objattr['vertical-align'] = $this->getAlign($properties['VERTICAL-ALIGN']);
		}

		switch (strtoupper($attr['TYPE'])) {
			case 'HIDDEN':
				$this->mpdf->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
				if ($this->mpdf->useActiveForms) {
					$this->form->SetFormText(0, 0, $objattr['fieldname'], $objattr['value'], $objattr['value'], '', 0, '', true);
				}
				if ($this->mpdf->InlineProperties[$tag]) {
					$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
				}
				unset($this->mpdf->InlineProperties[$tag]);
				return;

			case 'CHECKBOX': //Draw Checkbox
				$type = 'CHECKBOX';
				if (isset($attr['CHECKED'])) {
					$objattr['checked'] = true;
				} else {
					$objattr['checked'] = false;
				}
				$width = $this->mpdf->FontSize;
				$height = $this->mpdf->FontSize;
				break;

			case 'RADIO': //Draw Radio button
				$type = 'RADIO';
				if (isset($attr['CHECKED'])) {
					$objattr['checked'] = true;
				}
				$width = $this->mpdf->FontSize;
				$height = $this->mpdf->FontSize;
				break;

			/* -- IMAGES-CORE -- */
			case 'IMAGE': // Draw an Image button
				if (isset($attr['SRC'])) {
					$type = 'IMAGE';
					$srcpath = $attr['SRC'];
					$orig_srcpath = $attr['ORIG_SRC'];
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

					$objattr['padding_top'] = 0;
					$objattr['padding_bottom'] = 0;
					$objattr['padding_left'] = 0;
					$objattr['padding_right'] = 0;

					if (isset($properties['VERTICAL-ALIGN'])) {
						$objattr['vertical-align'] = $this->getAlign($properties['VERTICAL-ALIGN']);
					}

					$w = 0;
					$h = 0;
					if (isset($properties['WIDTH'])) {
						$w = $this->sizeConverter->convert($properties['WIDTH'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
					}
					if (isset($properties['HEIGHT'])) {
						$h = $this->sizeConverter->convert($properties['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
					}

					$extraheight = $objattr['margin_top'] + $objattr['margin_bottom'] + $objattr['border_top']['w'] + $objattr['border_bottom']['w'];
					$extrawidth = $objattr['margin_left'] + $objattr['margin_right'] + $objattr['border_left']['w'] + $objattr['border_right']['w'];

					// Image file
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
						break;
					}
					if ($info['cs'] === 'Indexed') {
						$objattr['Indexed'] = true;
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
						} else { 									/* -- END IMAGES-WMF -- */
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
						$w = $h * $info['w'] / $info['h'];
					}
					if ($h == 0) {
						$h = $w * $info['h'] / $info['w'];
					}
					// Resize to maximum dimensions of page
					$maxWidth = $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'];
					$maxHeight = $this->mpdf->h - ($this->mpdf->tMargin + $this->mpdf->bMargin + 10);
					if ($this->mpdf->fullImageHeight) {
						$maxHeight = $this->mpdf->fullImageHeight;
					}
					if (($w + $extrawidth) > ($maxWidth + 0.0001)) { // mPDF 5.7.4  0.0001 to allow for rounding errors when w==maxWidth
						$w = $maxWidth - $extrawidth;
						$h = $w * $info['h'] / $info['w'];
					}
					if ($h + $extraheight > $maxHeight) {
						$h = $maxHeight - $extraheight;
						$w = $h * $info['w'] / $info['h'];
					}
					$height = $h + $extraheight;
					$width = $w + $extrawidth;
					$objattr['type'] = 'image';
					$objattr['itype'] = $info['type'];
					$objattr['orig_h'] = $info['h'];
					$objattr['orig_w'] = $info['w'];
					/* -- IMAGES-WMF -- */
					if ($info['type'] === 'wmf') {
						$objattr['wmf_x'] = $info['x'];
						$objattr['wmf_y'] = $info['y'];
						/* -- END IMAGES-WMF -- */
					} else {
						if ($info['type'] === 'svg') {
							$objattr['wmf_x'] = $info['x'];
							$objattr['wmf_y'] = $info['y'];
						}
					}
					$objattr['height'] = $h + $extraheight;
					$objattr['width'] = $w + $extrawidth;

					$objattr['image_height'] = $h;
					$objattr['image_width'] = $w;
					$objattr['ID'] = $info['i'];
					$texto = 'X';
					if ($this->mpdf->useActiveForms) {
						if (isset($attr['ONCLICK'])) {
							$objattr['onClick'] = $attr['ONCLICK'];
						}
						$objattr['type'] = 'input';
						$type = 'IMAGE';
					}
					break;
				}
			/* -- END IMAGES-CORE -- */

			case 'BUTTON': // Draw a button
			case 'SUBMIT':
			case 'RESET':
				$type = strtoupper($attr['TYPE']);
				if ($type === 'IMAGE') {
					$type = 'BUTTON';
				} // src path not found
				if (isset($attr['NOPRINT'])) {
					$objattr['noprint'] = true;
				}
				if (!isset($attr['VALUE'])) {
					$objattr['value'] = ucfirst(strtolower($type));
				}

				$texto = ' ' . $objattr['value'] . ' ';

				$width = $this->mpdf->GetStringWidth($texto) + ($this->form->form_element_spacing['button']['outer']['h'] * 2)
					+ ($this->form->form_element_spacing['button']['inner']['h'] * 2);

				$height = $this->mpdf->FontSize + ($this->form->form_element_spacing['button']['outer']['v'] * 2)
					+ ($this->form->form_element_spacing['button']['inner']['v'] * 2);

				if ($this->mpdf->useActiveForms && isset($attr['ONCLICK'])) {
					$objattr['onClick'] = $attr['ONCLICK'];
				}
				break;

			case 'PASSWORD':
			case 'TEXT':
			default:
				if ($type == '') {
					$type = 'TEXT';
				}
				if (strtoupper($attr['TYPE']) === 'PASSWORD') {
					$type = 'PASSWORD';
				}
				if (isset($attr['VALUE'])) {
					if ($type === 'PASSWORD') {
						$num_stars = mb_strlen($attr['VALUE'], $this->mpdf->mb_enc);
						$texto = str_repeat('*', $num_stars);
					} else {
						$texto = $attr['VALUE'];
					}
				}
				$xw = ($this->form->form_element_spacing['input']['outer']['h'] * 2) + ($this->form->form_element_spacing['input']['inner']['h'] * 2);
				$xh = ($this->form->form_element_spacing['input']['outer']['v'] * 2) + ($this->form->form_element_spacing['input']['inner']['v'] * 2);
				if ($w) {
					$width = $w + $xw;
				} else {
					$width = (20 * $spacesize) + $xw;
				} // Default width in chars
				if (isset($attr['SIZE']) && ctype_digit($attr['SIZE'])) {
					$width = ($attr['SIZE'] * $spacesize) + $xw;
				}
				$height = $this->mpdf->FontSize + $xh;
				if (isset($attr['MAXLENGTH']) && ctype_digit($attr['MAXLENGTH'])) {
					$objattr['maxlength'] = $attr['MAXLENGTH'];
				}
				if ($this->mpdf->useActiveForms) {
					if (isset($attr['ONCALCULATE'])) {
						$objattr['onCalculate'] = $attr['ONCALCULATE'];
					} elseif (isset($attr['ONCHANGE'])) {
						$objattr['onCalculate'] = $attr['ONCHANGE'];
					}
					if (isset($attr['ONVALIDATE'])) {
						$objattr['onValidate'] = $attr['ONVALIDATE'];
					}
					if (isset($attr['ONKEYSTROKE'])) {
						$objattr['onKeystroke'] = $attr['ONKEYSTROKE'];
					}
					if (isset($attr['ONFORMAT'])) {
						$objattr['onFormat'] = $attr['ONFORMAT'];
					}
				}
				break;
		}

		$objattr['subtype'] = $type;
		$objattr['text'] = $texto;
		$objattr['width'] = $width;
		$objattr['height'] = $height;
		$e = "\xbb\xa4\xactype=input,objattr=" . serialize($objattr) . "\xbb\xa4\xac";

		/* -- TABLES -- */
		// Output it to buffers
		if ($this->mpdf->tableLevel) {
			$this->mpdf->_saveCellTextBuffer($e, $this->mpdf->HREF);
			$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $objattr['width'];
		} else {
			/* -- END TABLES -- */
			$this->mpdf->_saveTextBuffer($e, $this->mpdf->HREF);
		} // *TABLES*

		if ($this->mpdf->InlineProperties[$tag]) {
			$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties[$tag]);
		}
		unset($this->mpdf->InlineProperties[$tag]);
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
