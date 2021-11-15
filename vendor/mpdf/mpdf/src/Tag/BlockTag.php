<?php

namespace Mpdf\Tag;

use Mpdf\Conversion\DecToAlpha;
use Mpdf\Conversion\DecToRoman;

use Mpdf\Utils\Arrays;
use Mpdf\Utils\UtfString;

abstract class BlockTag extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();

		// mPDF 6  Lists
		$this->mpdf->lastoptionaltag = '';

		// mPDF 6 bidi
		// Block
		// If unicode-bidi set on current clock, any embedding levels, isolates, or overrides are closed (not inherited)
		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bidicode'])) {
			$blockpost = $this->mpdf->_setBidiCodes('end', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
			if ($blockpost) {
				$this->mpdf->OTLdata = [];
				if ($this->mpdf->tableLevel) {
					$this->mpdf->_saveCellTextBuffer($blockpost);
				} else {
					$this->mpdf->_saveTextBuffer($blockpost);
				}
			}
		}


		$p = $this->cssManager->PreviewBlockCSS($tag, $attr);
		if (isset($p['DISPLAY']) && strtolower($p['DISPLAY']) === 'none') {
			$this->mpdf->blklvl++;
			$this->mpdf->blk[$this->mpdf->blklvl]['hide'] = true;
			$this->mpdf->blk[$this->mpdf->blklvl]['tag'] = $tag;  // mPDF 6
			return;
		}
		if ($tag === 'CAPTION') {
			// position is written in AdjstHTML
			$divpos = 'T';
			if (isset($attr['POSITION']) && strtolower($attr['POSITION']) === 'bottom') {
				$divpos = 'B';
			}

			$cappos = 'T';
			if (isset($attr['ALIGN']) && strtolower($attr['ALIGN']) === 'bottom') {
				$cappos = 'B';
			} elseif (isset($p['CAPTION-SIDE']) && strtolower($p['CAPTION-SIDE']) === 'bottom') {
				$cappos = 'B';
			}
			if (isset($attr['ALIGN'])) {
				unset($attr['ALIGN']);
			}
			if ($cappos != $divpos) {
				$this->mpdf->blklvl++;
				$this->mpdf->blk[$this->mpdf->blklvl]['hide'] = true;
				$this->mpdf->blk[$this->mpdf->blklvl]['tag'] = $tag;  // mPDF 6
				return;
			}
		}

		/* -- FORMS -- */
		if ($tag === 'FORM') {
			$this->form->formMethod = 'POST';
			if (isset($attr['METHOD']) && strtolower($attr['METHOD']) === 'get') {
				$this->form->formMethod = 'GET';
			}

			$this->form->formAction = '';
			if (isset($attr['ACTION'])) {
				$this->form->formAction = $attr['ACTION'];
			}
		}
		/* -- END FORMS -- */


		/* -- CSS-POSITION -- */
		if ((isset($p['POSITION'])
				&& (strtolower($p['POSITION']) === 'fixed'
					|| strtolower($p['POSITION']) === 'absolute'))
			&& $this->mpdf->blklvl == 0) {
			if ($this->mpdf->inFixedPosBlock) {
				throw new \Mpdf\MpdfException('Cannot nest block with position:fixed or position:absolute');
			}
			$this->mpdf->inFixedPosBlock = true;
			return;
		}
		/* -- END CSS-POSITION -- */
		// Start Block
		$this->mpdf->ignorefollowingspaces = true;

		$lastbottommargin = 0;
		if ($this->mpdf->blockjustfinished && !count($this->mpdf->textbuffer)
			&& $this->mpdf->y != $this->mpdf->tMargin
			&& $this->mpdf->collapseBlockMargins) {
			$lastbottommargin = $this->mpdf->lastblockbottommargin;
		}
		$this->mpdf->lastblockbottommargin = 0;
		$this->mpdf->blockjustfinished = false;


		$this->mpdf->InlineBDF = []; // mPDF 6
		$this->mpdf->InlineBDFctr = 0; // mPDF 6
		$this->mpdf->InlineProperties = [];
		$this->mpdf->divbegin = true;

		$this->mpdf->linebreakjustfinished = false;

		/* -- TABLES -- */
		if ($this->mpdf->tableLevel) {
			// If already something on the line
			if ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] > 0 && !$this->mpdf->nestedtablejustfinished) {
				$this->mpdf->_saveCellTextBuffer("\n");
				if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
				} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
					$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
				}
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0; // reset
			}
			// Cannot set block properties inside table - use Bold to indicate h1-h6
			if ($tag === 'CENTER' && $this->mpdf->tdbegin) {
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['a'] = $this->getAlign('center');
			}

			$this->mpdf->InlineProperties['BLOCKINTABLE'] = $this->mpdf->saveInlineProperties();
			$properties = $this->cssManager->MergeCSS('', $tag, $attr);
			if (!empty($properties)) {
				$this->mpdf->setCSS($properties, 'INLINE');
			}

			// mPDF 6  Lists
			if ($tag === 'UL' || $tag === 'OL') {
				$this->mpdf->listlvl++;
				if (isset($attr['START'])) {
					$this->mpdf->listcounter[$this->mpdf->listlvl] = (int) $attr['START'] - 1;
				} else {
					$this->mpdf->listcounter[$this->mpdf->listlvl] = 0;
				}
				$this->mpdf->listitem = [];
				if ($tag === 'OL') {
					$this->mpdf->listtype[$this->mpdf->listlvl] = 'decimal';
				} elseif ($tag === 'UL') {
					if ($this->mpdf->listlvl % 3 == 1) {
						$this->mpdf->listtype[$this->mpdf->listlvl] = 'disc';
					} elseif ($this->mpdf->listlvl % 3 == 2) {
						$this->mpdf->listtype[$this->mpdf->listlvl] = 'circle';
					} else {
						$this->mpdf->listtype[$this->mpdf->listlvl] = 'square';
					}
				}
			}

			// mPDF 6  Lists - in Tables
			if ($tag === 'LI') {

				if ($this->mpdf->listlvl == 0) { //in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
					$this->mpdf->listlvl++; // first depth level
					$this->mpdf->listcounter[$this->mpdf->listlvl] = 0;
				}

				$this->mpdf->listcounter[$this->mpdf->listlvl]++;
				$this->mpdf->listitem = [];
				//if in table - output here as a tabletextbuffer
				//position:inside OR position:outside (always output in table as position:inside)

				$decToAlpha = new DecToAlpha();
				$decToRoman = new DecToRoman();

				switch ($this->mpdf->listtype[$this->mpdf->listlvl]) {
					case 'upper-alpha':
					case 'upper-latin':
					case 'A':
						$blt = $decToAlpha->convert($this->mpdf->listcounter[$this->mpdf->listlvl]) . $this->mpdf->list_number_suffix;
						break;
					case 'lower-alpha':
					case 'lower-latin':
					case 'a':
						$blt = $decToAlpha->convert($this->mpdf->listcounter[$this->mpdf->listlvl], false) . $this->mpdf->list_number_suffix;
						break;
					case 'upper-roman':
					case 'I':
						$blt = $decToRoman->convert($this->mpdf->listcounter[$this->mpdf->listlvl]) . $this->mpdf->list_number_suffix;
						break;
					case 'lower-roman':
					case 'i':
						$blt = $decToRoman->convert($this->mpdf->listcounter[$this->mpdf->listlvl]) . $this->mpdf->list_number_suffix;
						break;
					case 'decimal':
					case '1':
						$blt = $this->mpdf->listcounter[$this->mpdf->listlvl] . $this->mpdf->list_number_suffix;
						break;
					default:
						$blt = '-';
						if ($this->mpdf->listlvl % 3 == 1 && $this->mpdf->_charDefined($this->mpdf->CurrentFont['cw'], 8226)) {
							$blt = "\xe2\x80\xa2";
						} // &#8226;
						elseif ($this->mpdf->listlvl % 3 == 2 && $this->mpdf->_charDefined($this->mpdf->CurrentFont['cw'], 9900)) {
							$blt = "\xe2\x9a\xac";
						} // &#9900;
						elseif ($this->mpdf->listlvl % 3 == 0 && $this->mpdf->_charDefined($this->mpdf->CurrentFont['cw'], 9642)) {
							$blt = "\xe2\x96\xaa";
						} // &#9642;
						break;
				}

				// change to &nbsp; spaces
				if ($this->mpdf->usingCoreFont) {
					$ls = str_repeat(chr(160) . chr(160), ($this->mpdf->listlvl - 1) * 2) . $blt . ' ';
				} else {
					$ls = str_repeat("\xc2\xa0\xc2\xa0", ($this->mpdf->listlvl - 1) * 2) . $blt . ' ';
				}
				$this->mpdf->_saveCellTextBuffer($ls);
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] += $this->mpdf->GetStringWidth($ls);
			}

			return;
		}
		/* -- END TABLES -- */

		if ($this->mpdf->lastblocklevelchange == 1) {
			$blockstate = 1;
		} // Top margins/padding only
		elseif ($this->mpdf->lastblocklevelchange < 1) {
			$blockstate = 0;
		} // NO margins/padding

		$this->mpdf->printbuffer($this->mpdf->textbuffer, $blockstate);
		$this->mpdf->textbuffer = [];

		$save_blklvl = $this->mpdf->blklvl;
		$save_blk = $this->mpdf->blk;

		$this->mpdf->Reset();

		$pagesel = '';
		/* -- CSS-PAGE -- */
		if (isset($p['PAGE'])) {
			$pagesel = $p['PAGE'];
		}  // mPDF 6 (uses $p - preview of properties so blklvl can be incremented after page-break)
		/* -- END CSS-PAGE -- */

		// If page-box has changed AND/OR PAGE-BREAK-BEFORE
		// mPDF 6 (uses $p - preview of properties so blklvl can be imcremented after page-break)
		if (!$this->mpdf->tableLevel && (($pagesel && (!isset($this->mpdf->page_box['current'])
						|| $pagesel != $this->mpdf->page_box['current']))
				|| (isset($p['PAGE-BREAK-BEFORE'])
					&& $p['PAGE-BREAK-BEFORE']))) {
			// mPDF 6 pagebreaktype
			$startpage = $this->mpdf->page;
			$pagebreaktype = $this->mpdf->defaultPagebreakType;
			$this->mpdf->lastblocklevelchange = -1;
			if ($this->mpdf->ColActive) {
				$pagebreaktype = 'cloneall';
			}
			if ($pagesel && (!isset($this->mpdf->page_box['current']) || $pagesel != $this->mpdf->page_box['current'])) {
				$pagebreaktype = 'cloneall';
			}
			$this->mpdf->_preForcedPagebreak($pagebreaktype);

			if (isset($p['PAGE-BREAK-BEFORE'])) {
				if (strtoupper($p['PAGE-BREAK-BEFORE']) === 'RIGHT') {
					$this->mpdf->AddPage(
						$this->mpdf->CurOrientation,
						'NEXT-ODD',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						0,
						0,
						0,
						0,
						$pagesel
					);
				} elseif (strtoupper($p['PAGE-BREAK-BEFORE']) === 'LEFT') {
					$this->mpdf->AddPage(
						$this->mpdf->CurOrientation,
						'NEXT-EVEN',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						0,
						0,
						0,
						0,
						$pagesel
					);
				} elseif (strtoupper($p['PAGE-BREAK-BEFORE']) === 'ALWAYS') {
					$this->mpdf->AddPage($this->mpdf->CurOrientation, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0, $pagesel);
				} elseif ($this->mpdf->page_box['current'] != $pagesel) {
					$this->mpdf->AddPage($this->mpdf->CurOrientation, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0, $pagesel);
				} // *CSS-PAGE*
			} /* -- CSS-PAGE -- */
			// Must Add new page if changed page properties
			elseif (!isset($this->mpdf->page_box['current']) || $pagesel != $this->mpdf->page_box['current']) {
				$this->mpdf->AddPage($this->mpdf->CurOrientation, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 0, 0, 0, $pagesel);
			}
			/* -- END CSS-PAGE -- */

			// mPDF 6 pagebreaktype
			$this->mpdf->_postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl);
		}

		// mPDF 6 pagebreaktype - moved after pagebreak
		$this->mpdf->blklvl++;
		$currblk = & $this->mpdf->blk[$this->mpdf->blklvl];
		$this->mpdf->initialiseBlock($currblk);
		$prevblk = & $this->mpdf->blk[$this->mpdf->blklvl - 1];
		$currblk['tag'] = $tag;
		$currblk['attr'] = $attr;

		$properties = $this->cssManager->MergeCSS('BLOCK', $tag, $attr); // mPDF 6 - moved to after page-break-before
		// mPDF 6 page-break-inside:avoid
		if (isset($properties['PAGE-BREAK-INSIDE']) && strtoupper($properties['PAGE-BREAK-INSIDE']) === 'AVOID'
			&& !$this->mpdf->ColActive && !$this->mpdf->keep_block_together && !isset($attr['PAGEBREAKAVOIDCHECKED'])) {
			// avoid re-iterating using PAGEBREAKAVOIDCHECKED; set in CloseTag
			$currblk['keep_block_together'] = 1;
			$currblk['array_i'] = $ihtml; // mPDF 6
			$this->mpdf->kt_y00 = $this->mpdf->y;
			$this->mpdf->kt_p00 = $this->mpdf->page;
			$this->mpdf->keep_block_together = 1;
		}
		if ($lastbottommargin && !empty($properties['MARGIN-TOP']) && empty($properties['FLOAT'])) {
			$currblk['lastbottommargin'] = $lastbottommargin;
		}

		if (isset($properties['Z-INDEX']) && $this->mpdf->current_layer == 0) {
			$v = (int) $properties['Z-INDEX'];
			if ($v > 0) {
				$currblk['z-index'] = $v;
				$this->mpdf->BeginLayer($v);
			}
		}


		// mPDF 6  Lists
		// List-type set by attribute
		if ($tag === 'OL' || $tag === 'UL' || $tag === 'LI') {
			if (!empty($attr['TYPE'])) {
				$listtype = $attr['TYPE'];
				switch ($listtype) {
					case 'A':
						$listtype = 'upper-latin';
						break;
					case 'a':
						$listtype = 'lower-latin';
						break;
					case 'I':
						$listtype = 'upper-roman';
						break;
					case 'i':
						$listtype = 'lower-roman';
						break;
					case '1':
						$listtype = 'decimal';
						break;
				}
				$currblk['list_style_type'] = $listtype;
			}
		}

		$this->mpdf->setCSS($properties, 'BLOCK', $tag); //name(id/class/style) found in the CSS array!
		$currblk['InlineProperties'] = $this->mpdf->saveInlineProperties();

		if (isset($properties['VISIBILITY'])) {
			$v = strtolower($properties['VISIBILITY']);
			if (($v === 'hidden' || $v === 'printonly' || $v === 'screenonly') && $this->mpdf->visibility === 'visible' && !$this->mpdf->tableLevel) {
				$currblk['visibility'] = $v;
				$this->mpdf->SetVisibility($v);
			}
		}

		// mPDF 6
		if (!empty($attr['ALIGN'])) {
			$currblk['block-align'] = $this->getAlign($attr['ALIGN']);
		}


		if (isset($properties['HEIGHT'])) {
			$currblk['css_set_height'] = $this->sizeConverter->convert(
				$properties['HEIGHT'],
				$this->mpdf->h - $this->mpdf->tMargin - $this->mpdf->bMargin,
				$this->mpdf->FontSize,
				false
			);
			if (($currblk['css_set_height'] + $this->mpdf->y) > $this->mpdf->PageBreakTrigger
				&& $this->mpdf->y > $this->mpdf->tMargin + 5
				&& $currblk['css_set_height'] < ($this->mpdf->h - ($this->mpdf->tMargin + $this->mpdf->bMargin))) {
				$this->mpdf->AddPage($this->mpdf->CurOrientation);
			}
		} else {
			$currblk['css_set_height'] = false;
		}


		// Added mPDF 3.0 Float DIV
		if (isset($prevblk['blockContext'])) {
			$currblk['blockContext'] = $prevblk['blockContext'];
		} // *CSS-FLOAT*

		if (isset($properties['CLEAR'])) {
			$this->mpdf->ClearFloats(strtoupper($properties['CLEAR']), $this->mpdf->blklvl - 1);
		} // *CSS-FLOAT*

		$currblk['padding_left'] = is_numeric($currblk['padding_left']) ? $currblk['padding_left'] : 0;
		$currblk['padding_right'] = is_numeric($currblk['padding_right']) ? $currblk['padding_right'] : 0;

		$container_w = $prevblk['inner_width'];
		$bdr = $currblk['border_right']['w'];
		$bdl = $currblk['border_left']['w'];
		$pdr = $currblk['padding_right'];
		$pdl = $currblk['padding_left'];

		$setwidth = 0;
		if (isset($currblk['css_set_width'])) {
			$setwidth = $currblk['css_set_width'];
		}

		/* -- CSS-FLOAT -- */
		if (isset($properties['FLOAT']) && strtoupper($properties['FLOAT']) === 'RIGHT' && !$this->mpdf->ColActive) {

			// Cancel Keep-Block-together
			$currblk['keep_block_together'] = false;
			$this->mpdf->kt_y00 = 0;
			$this->mpdf->keep_block_together = 0;

			$this->mpdf->blockContext++;
			$currblk['blockContext'] = $this->mpdf->blockContext;

			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);

			// DIV is too narrow for text to fit!
			$maxw = $container_w - $l_width - $r_width;
			$doubleCharWidth = (2 * $this->mpdf->GetCharWidth('W', false));
			if (($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) > $maxw
				|| ($maxw - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) < (2 * $this->mpdf->GetCharWidth('W', false))) {
				// Too narrow to fit - try to move down past L or R float
				if ($l_max < $r_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width)
					&& (($container_w - $r_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > $doubleCharWidth) {
					$this->mpdf->ClearFloats('LEFT', $this->mpdf->blklvl - 1);
				} elseif ($r_max < $l_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width)
					&& (($container_w - $l_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > $doubleCharWidth) {
					$this->mpdf->ClearFloats('RIGHT', $this->mpdf->blklvl - 1);
				} else {
					$this->mpdf->ClearFloats('BOTH', $this->mpdf->blklvl - 1);
				}
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);
			}

			if ($r_exists) {
				$currblk['margin_right'] += $r_width;
			}

			$currblk['float'] = 'R';
			$currblk['float_start_y'] = $this->mpdf->y;

			if (isset($currblk['css_set_width'])) {
				$currblk['margin_left'] = $container_w - ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
				$currblk['float_width'] = ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
			} else {
				// *** If no width set - would need to buffer and keep track of max width, then Right-align if not full width
				// and do borders and backgrounds - For now - just set to maximum width left

				if ($l_exists) {
					$currblk['margin_left'] += $l_width;
				}
				$currblk['css_set_width'] = $container_w - ($currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr);

				$currblk['float_width'] = ($currblk['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $currblk['margin_right']);
			}

		} elseif (isset($properties['FLOAT']) && strtoupper($properties['FLOAT']) === 'LEFT' && !$this->mpdf->ColActive) {
			// Cancel Keep-Block-together
			$currblk['keep_block_together'] = false;
			$this->mpdf->kt_y00 = 0;
			$this->mpdf->keep_block_together = 0;

			$this->mpdf->blockContext++;
			$currblk['blockContext'] = $this->mpdf->blockContext;

			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);

			// DIV is too narrow for text to fit!
			$maxw = $container_w - $l_width - $r_width;
			$doubleCharWidth = (2 * $this->mpdf->GetCharWidth('W', false));
			if (($setwidth + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr) > $maxw
				|| ($maxw - ($currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) < (2 * $this->mpdf->GetCharWidth('W', false))) {
				// Too narrow to fit - try to move down past L or R float
				if ($l_max < $r_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width)
					&& (($container_w - $r_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > $doubleCharWidth) {
					$this->mpdf->ClearFloats('LEFT', $this->mpdf->blklvl - 1);
				} elseif ($r_max < $l_max && ($setwidth + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width)
					&& (($container_w - $l_width) - ($currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr)) > $doubleCharWidth) {
					$this->mpdf->ClearFloats('RIGHT', $this->mpdf->blklvl - 1);
				} else {
					$this->mpdf->ClearFloats('BOTH', $this->mpdf->blklvl - 1);
				}
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);
			}

			if ($l_exists) {
				$currblk['margin_left'] += $l_width;
			}

			$currblk['float'] = 'L';
			$currblk['float_start_y'] = $this->mpdf->y;
			if ($setwidth) {
				$currblk['margin_right'] = $container_w - ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
				$currblk['float_width'] = ($setwidth + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
			} else {
				// *** If no width set - would need to buffer and keep track of max width, then Right-align if not full width
				// and do borders and backgrounds - For now - just set to maximum width left

				if ($r_exists) {
					$currblk['margin_right'] += $r_width;
				}
				$currblk['css_set_width'] = $container_w - ($currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr);

				$currblk['float_width'] = ($currblk['css_set_width'] + $bdl + $pdl + $bdr + $pdr + $currblk['margin_left']);
			}
		} else {
			// Don't allow overlap - if floats present - adjust padding to avoid overlap with Floats
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);
			$maxw = $container_w - $l_width - $r_width;

			$pdl = is_numeric($pdl) ? $pdl : 0;
			$pdr = is_numeric($pdr) ? $pdr : 0;

			$doubleCharWidth = (2 * $this->mpdf->GetCharWidth('W', false));
			if (($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) > $maxw
				|| ($maxw - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) < $doubleCharWidth) {
				// Too narrow to fit - try to move down past L or R float
				if ($l_max < $r_max && ($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $r_width)
					&& (($container_w - $r_width) - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) > $doubleCharWidth) {
					$this->mpdf->ClearFloats('LEFT', $this->mpdf->blklvl - 1);
				} elseif ($r_max < $l_max && ($setwidth + $currblk['margin_left'] + $currblk['margin_right'] + $bdl + $pdl + $bdr + $pdr) <= ($container_w - $l_width)
					&& (($container_w - $l_width) - ($currblk['margin_right'] + $currblk['margin_left'] + $bdl + $pdl + $bdr + $pdr)) > $doubleCharWidth) {
					$this->mpdf->ClearFloats('RIGHT', $this->mpdf->blklvl - 1);
				} else {
					$this->mpdf->ClearFloats('BOTH', $this->mpdf->blklvl - 1);
				}
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->mpdf->GetFloatDivInfo($this->mpdf->blklvl - 1);
			}
			if ($r_exists) {
				$currblk['padding_right'] = max($r_width - $currblk['margin_right'] - $bdr, $pdr);
			}
			if ($l_exists) {
				$currblk['padding_left'] = max($l_width - $currblk['margin_left'] - $bdl, $pdl);
			}
		}
		/* -- END CSS-FLOAT -- */


		/* -- BORDER-RADIUS -- */
		// Automatically increase padding if required for border-radius
		if ($this->mpdf->autoPadding && !$this->mpdf->ColActive) {
			$currblk['border_radius_TL_H'] = Arrays::get($currblk, 'border_radius_TL_H', 0);
			$currblk['border_radius_TL_V'] = Arrays::get($currblk, 'border_radius_TL_V', 0);
			$currblk['border_radius_TR_H'] = Arrays::get($currblk, 'border_radius_TR_H', 0);
			$currblk['border_radius_TR_V'] = Arrays::get($currblk, 'border_radius_TR_V', 0);
			$currblk['border_radius_BL_H'] = Arrays::get($currblk, 'border_radius_BL_H', 0);
			$currblk['border_radius_BL_V'] = Arrays::get($currblk, 'border_radius_BL_V', 0);
			$currblk['border_radius_BR_H'] = Arrays::get($currblk, 'border_radius_BR_H', 0);
			$currblk['border_radius_BR_V'] = Arrays::get($currblk, 'border_radius_BR_V', 0);

			if ($currblk['border_radius_TL_H'] > $currblk['padding_left'] && $currblk['border_radius_TL_V'] > $currblk['padding_top']) {
				if ($currblk['border_radius_TL_H'] > $currblk['border_radius_TL_V']) {
					$this->mpdf->_borderPadding(
						$currblk['border_radius_TL_H'],
						$currblk['border_radius_TL_V'],
						$currblk['padding_left'],
						$currblk['padding_top']
					);
				} else {
					$this->mpdf->_borderPadding(
						$currblk['border_radius_TL_V'],
						$currblk['border_radius_TL_H'],
						$currblk['padding_top'],
						$currblk['padding_left']
					);
				}
			}
			if ($currblk['border_radius_TR_H'] > $currblk['padding_right'] && $currblk['border_radius_TR_V'] > $currblk['padding_top']) {
				if ($currblk['border_radius_TR_H'] > $currblk['border_radius_TR_V']) {
					$this->mpdf->_borderPadding(
						$currblk['border_radius_TR_H'],
						$currblk['border_radius_TR_V'],
						$currblk['padding_right'],
						$currblk['padding_top']
					);
				} else {
					$this->mpdf->_borderPadding(
						$currblk['border_radius_TR_V'],
						$currblk['border_radius_TR_H'],
						$currblk['padding_top'],
						$currblk['padding_right']
					);
				}
			}
			if ($currblk['border_radius_BL_H'] > $currblk['padding_left'] && $currblk['border_radius_BL_V'] > $currblk['padding_bottom']) {
				if ($currblk['border_radius_BL_H'] > $currblk['border_radius_BL_V']) {
					$this->mpdf->_borderPadding(
						$currblk['border_radius_BL_H'],
						$currblk['border_radius_BL_V'],
						$currblk['padding_left'],
						$currblk['padding_bottom']
					);
				} else {
					$this->mpdf->_borderPadding(
						$currblk['border_radius_BL_V'],
						$currblk['border_radius_BL_H'],
						$currblk['padding_bottom'],
						$currblk['padding_left']
					);
				}
			}
			if ($currblk['border_radius_BR_H'] > $currblk['padding_right'] && $currblk['border_radius_BR_V'] > $currblk['padding_bottom']) {
				if ($currblk['border_radius_BR_H'] > $currblk['border_radius_BR_V']) {
					$this->mpdf->_borderPadding(
						$currblk['border_radius_BR_H'],
						$currblk['border_radius_BR_V'],
						$currblk['padding_right'],
						$currblk['padding_bottom']
					);
				} else {
					$this->mpdf->_borderPadding(
						$currblk['border_radius_BR_V'],
						$currblk['border_radius_BR_H'],
						$currblk['padding_bottom'],
						$currblk['padding_right']
					);
				}
			}
		}
		/* -- END BORDER-RADIUS -- */

		// Hanging indent - if negative indent: ensure padding is >= indent
		if (!isset($currblk['text_indent'])) {
			$currblk['text_indent'] = null;
		}
		if (!isset($currblk['inner_width'])) {
			$currblk['inner_width'] = null;
		}
		$cbti = $this->sizeConverter->convert(
			$currblk['text_indent'],
			$this->mpdf->blk[$this->mpdf->blklvl]['inner_width'],
			$this->mpdf->FontSize,
			false
		);
		if ($cbti < 0) {
			$hangind = -$cbti;
			if (isset($currblk['direction']) && $currblk['direction'] === 'rtl') { // *OTL*
				$currblk['padding_right'] = max($currblk['padding_right'], $hangind); // *OTL*
			} // *OTL*
			else { // *OTL*
				$currblk['padding_left'] = max($currblk['padding_left'], $hangind);
			} // *OTL*
		}

		if (isset($currblk['css_set_width'])) {
			if (isset($properties['MARGIN-LEFT'], $properties['MARGIN-RIGHT'])
				&& strtolower($properties['MARGIN-LEFT']) === 'auto' && strtolower($properties['MARGIN-RIGHT']) === 'auto') {
				// Try to reduce margins to accomodate - if still too wide, set margin-right/left=0 (reduces width)
				$anyextra = $prevblk['inner_width'] - ($currblk['css_set_width'] + $currblk['border_left']['w']
						+ $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right']);
				if ($anyextra > 0) {
					$currblk['margin_left'] = $currblk['margin_right'] = $anyextra / 2;
				} else {
					$currblk['margin_left'] = $currblk['margin_right'] = 0;
				}
			} elseif (isset($properties['MARGIN-LEFT']) && strtolower($properties['MARGIN-LEFT']) === 'auto') {
				// Try to reduce margin-left to accomodate - if still too wide, set margin-left=0 (reduces width)
				$currblk['margin_left'] = $prevblk['inner_width'] - ($currblk['css_set_width']
						+ $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w']
						+ $currblk['padding_right'] + $currblk['margin_right']);
				if ($currblk['margin_left'] < 0) {
					$currblk['margin_left'] = 0;
				}
			} elseif (isset($properties['MARGIN-RIGHT']) && strtolower($properties['MARGIN-RIGHT']) === 'auto') {
				// Try to reduce margin-right to accomodate - if still too wide, set margin-right=0 (reduces width)
				$currblk['margin_right'] = $prevblk['inner_width'] - ($currblk['css_set_width']
						+ $currblk['border_left']['w'] + $currblk['padding_left']
						+ $currblk['border_right']['w'] + $currblk['padding_right'] + $currblk['margin_left']);
				if ($currblk['margin_right'] < 0) {
					$currblk['margin_right'] = 0;
				}
			} else {
				if ($currblk['direction'] === 'rtl') { // *OTL*
					// Try to reduce margin-left to accomodate - if still too wide, set margin-left=0 (reduces width)
					$currblk['margin_left'] = $prevblk['inner_width'] - ($currblk['css_set_width']
							+ $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w']
							+ $currblk['padding_right'] + $currblk['margin_right']); // *OTL*
					if ($currblk['margin_left'] < 0) { // *OTL*
						$currblk['margin_left'] = 0; // *OTL*
					} // *OTL*
				} // *OTL*
				else { // *OTL*
					// Try to reduce margin-right to accomodate - if still too wide, set margin-right=0 (reduces width)
					$currblk['margin_right'] = $prevblk['inner_width'] - ($currblk['css_set_width']
							+ $currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w']
							+ $currblk['padding_right'] + $currblk['margin_left']);
					if ($currblk['margin_right'] < 0) {
						$currblk['margin_right'] = 0;
					}
				} // *OTL*
			}
		}

		$currblk['outer_left_margin'] = $prevblk['outer_left_margin'] + $currblk['margin_left']
			+ $prevblk['border_left']['w'] + $prevblk['padding_left'];

		$currblk['outer_right_margin'] = $prevblk['outer_right_margin'] + $currblk['margin_right']
			+ $prevblk['border_right']['w'] + $prevblk['padding_right'];

		$currblk['width'] = $this->mpdf->pgwidth - ($currblk['outer_right_margin'] + $currblk['outer_left_margin']);

		$currblk['inner_width'] = $currblk['width']
			- ($currblk['border_left']['w'] + $currblk['padding_left'] + $currblk['border_right']['w'] + $currblk['padding_right']);

		// Check DIV is not now too narrow to fit text
		$mw = 2 * $this->mpdf->GetCharWidth('W', false);
		if ($currblk['inner_width'] < $mw) {
			$currblk['padding_left'] = 0;
			$currblk['padding_right'] = 0;
			$currblk['border_left']['w'] = 0.2;
			$currblk['border_right']['w'] = 0.2;
			$currblk['margin_left'] = 0;
			$currblk['margin_right'] = 0;
			$currblk['outer_left_margin'] = $prevblk['outer_left_margin'] + $currblk['margin_left']
				+ $prevblk['border_left']['w'] + $prevblk['padding_left'];
			$currblk['outer_right_margin'] = $prevblk['outer_right_margin'] + $currblk['margin_right']
				+ $prevblk['border_right']['w'] + $prevblk['padding_right'];
			$currblk['width'] = $this->mpdf->pgwidth - ($currblk['outer_right_margin'] + $currblk['outer_left_margin']);
			$currblk['inner_width'] = $this->mpdf->pgwidth - ($currblk['outer_right_margin']
					+ $currblk['outer_left_margin'] + $currblk['border_left']['w'] + $currblk['padding_left']
					+ $currblk['border_right']['w'] + $currblk['padding_right']);
			// if ($currblk['inner_width'] < $mw) { throw new \Mpdf\MpdfException("DIV is too narrow for text to fit!"); }
		}

		$this->mpdf->x = $this->mpdf->lMargin + $currblk['outer_left_margin'];

		/* -- BACKGROUNDS -- */
		if (!empty($properties['BACKGROUND-IMAGE']) && !$this->mpdf->kwt && !$this->mpdf->ColActive && !$this->mpdf->keep_block_together) {
			$ret = $this->mpdf->SetBackground($properties, $currblk['inner_width']);
			if ($ret) {
				$currblk['background-image'] = $ret;
			}
		}
		/* -- END BACKGROUNDS -- */

		/* -- TABLES -- */
		if ($this->mpdf->use_kwt && isset($attr['KEEP-WITH-TABLE']) && !$this->mpdf->ColActive && !$this->mpdf->keep_block_together) {
			$this->mpdf->kwt = true;
			$this->mpdf->kwt_y0 = $this->mpdf->y;
			//$this->mpdf->kwt_x0 = $this->mpdf->x;
			$this->mpdf->kwt_x0 = $this->mpdf->lMargin; // mPDF 6
			$this->mpdf->kwt_height = 0;
			$this->mpdf->kwt_buffer = [];
			$this->mpdf->kwt_Links = [];
			$this->mpdf->kwt_Annots = [];
			$this->mpdf->kwt_moved = false;
			$this->mpdf->kwt_saved = false;
			$this->mpdf->kwt_Reference = [];
			$this->mpdf->kwt_BMoutlines = [];
			$this->mpdf->kwt_toc = [];
		} else {
			/* -- END TABLES -- */
			$this->mpdf->kwt = false;
		} // *TABLES*

		// Save x,y coords in case we need to print borders...
		$currblk['y0'] = $this->mpdf->y;
		$currblk['initial_y0'] = $this->mpdf->y; // mPDF 6
		$currblk['x0'] = $this->mpdf->x;
		$currblk['initial_x0'] = $this->mpdf->x; // mPDF 6
		$currblk['initial_startpage'] = $this->mpdf->page;
		$currblk['startpage'] = $this->mpdf->page; // mPDF 6
		$this->mpdf->oldy = $this->mpdf->y;

		$this->mpdf->lastblocklevelchange = 1;

		// mPDF 6  Lists
		if ($tag === 'OL' || $tag === 'UL') {
			$this->mpdf->listlvl++;
			if (!empty($attr['START'])) {
				$this->mpdf->listcounter[$this->mpdf->listlvl] = (int) $attr['START'] - 1;
			} else {
				$this->mpdf->listcounter[$this->mpdf->listlvl] = 0;
			}
			$this->mpdf->listitem = [];

			// List-type
			if (empty($currblk['list_style_type'])) {
				if ($tag === 'OL') {
					$currblk['list_style_type'] = 'decimal';
				} elseif ($tag === 'UL') {
					if ($this->mpdf->listlvl % 3 == 1) {
						$currblk['list_style_type'] = 'disc';
					} elseif ($this->mpdf->listlvl % 3 == 2) {
						$currblk['list_style_type'] = 'circle';
					} else {
						$currblk['list_style_type'] = 'square';
					}
				}
			}

			// List-image
			if (empty($currblk['list_style_image'])) {
				$currblk['list_style_image'] = 'none';
			}

			// List-position
			if (empty($currblk['list_style_position'])) {
				$currblk['list_style_position'] = 'outside';
			}

			// Default indentation using padding
			if (strtolower($this->mpdf->list_auto_mode) === 'mpdf' && isset($currblk['list_style_position'])
				&& $currblk['list_style_position'] === 'outside' && isset($currblk['list_style_image'])
				&& $currblk['list_style_image'] === 'none' && (!isset($currblk['list_style_type'])
					|| !preg_match('/U\+([a-fA-F0-9]+)/i', $currblk['list_style_type']))) {
				$autopadding = $this->mpdf->_getListMarkerWidth($currblk, $ahtml, $ihtml);
				if ($this->mpdf->listlvl > 1 || $this->mpdf->list_indent_first_level) {
					$autopadding += $this->sizeConverter->convert(
						$this->mpdf->list_indent_default,
						$currblk['inner_width'],
						$this->mpdf->FontSize,
						false
					);
				}
				// autopadding value is applied to left or right according
				// to dir of block. Once a CSS value is set for padding it overrides this default value.
				if (isset($properties['PADDING-RIGHT']) && $properties['PADDING-RIGHT'] === 'auto'
					&& isset($currblk['direction']) && $currblk['direction'] === 'rtl') {
					$currblk['padding_right'] = $autopadding;
				} elseif (isset($properties['PADDING-LEFT']) && $properties['PADDING-LEFT'] === 'auto') {
					$currblk['padding_left'] = $autopadding;
				}
			} else {
				// Initial default value is set by $this->mpdf->list_indent_default in config.php; this value is applied to left or right according
				// to dir of block. Once a CSS value is set for padding it overrides this default value.
				if (isset($properties['PADDING-RIGHT']) && $properties['PADDING-RIGHT'] === 'auto'
					&& isset($currblk['direction']) && $currblk['direction'] === 'rtl') {
					$currblk['padding_right'] = $this->sizeConverter->convert(
						$this->mpdf->list_indent_default,
						$currblk['inner_width'],
						$this->mpdf->FontSize,
						false
					);
				} elseif (isset($properties['PADDING-LEFT']) && $properties['PADDING-LEFT'] === 'auto') {
					$currblk['padding_left'] = $this->sizeConverter->convert(
						$this->mpdf->list_indent_default,
						$currblk['inner_width'],
						$this->mpdf->FontSize,
						false
					);
				}
			}
		}

		// mPDF 6  Lists
		if ($tag === 'LI') {
			if ($this->mpdf->listlvl == 0) { // in case of malformed HTML code. Example:(...)</p><li>Content</li><p>Paragraph1</p>(...)
				$this->mpdf->listlvl++; // first depth level
				$this->mpdf->listcounter[$this->mpdf->listlvl] = 0;
			}

			if (!isset($attr['PAGEBREAKAVOIDCHECKED']) || !$attr['PAGEBREAKAVOIDCHECKED']) {
				$this->mpdf->listcounter[$this->mpdf->listlvl]++;
			}

			$this->mpdf->listitem = [];

			// Listitem-type
			$this->mpdf->_setListMarker($currblk['list_style_type'], $currblk['list_style_image'], $currblk['list_style_position']);
		}

		// mPDF 6 Bidirectional formatting for block elements
		$bdf = false;
		$bdf2 = '';
		$popd = '';

		// Get current direction
		$currdir = 'ltr';
		if (isset($currblk['direction'])) {
			$currdir = $currblk['direction'];
		}
		if (isset($attr['DIR']) && $attr['DIR'] != '') {
			$currdir = strtolower($attr['DIR']);
		}
		if (isset($properties['DIRECTION'])) {
			$currdir = strtolower($properties['DIRECTION']);
		}

		// mPDF 6 bidi
		// cf. http://www.w3.org/TR/css3-writing-modes/#unicode-bidi
		if (isset($properties ['UNICODE-BIDI'])
			&& (strtolower($properties ['UNICODE-BIDI']) === 'bidi-override' || strtolower($properties ['UNICODE-BIDI']) === 'isolate-override')) {
			if ($currdir === 'rtl') {
				$bdf = 0x202E;
				$popd = 'RLOPDF';
			} // U+202E RLO
			else {
				$bdf = 0x202D;
				$popd = 'LROPDF';
			} // U+202D LRO
		} elseif (isset($properties ['UNICODE-BIDI']) && strtolower($properties ['UNICODE-BIDI']) === 'plaintext') {
			$bdf = 0x2068;
			$popd = 'FSIPDI'; // U+2068 FSI
		}
		if ($bdf) {
			if ($bdf2) {
				$bdf2 = UtfString::code2utf($bdf);
			}
			$this->mpdf->OTLdata = [];
			if ($this->mpdf->tableLevel) {
				$this->mpdf->_saveCellTextBuffer(UtfString::code2utf($bdf) . $bdf2);
			} else {
				$this->mpdf->_saveTextBuffer(UtfString::code2utf($bdf) . $bdf2);
			}
			$this->mpdf->biDirectional = true;
			$currblk['bidicode'] = $popd;
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();

		// mPDF 6 bidi
		// Block
		// If unicode-bidi set, any embedding levels, isolates, or overrides started by this box are closed
		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bidicode'])) {
			$blockpost = $this->mpdf->_setBidiCodes('end', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
			if ($blockpost) {
				$this->mpdf->OTLdata = [];
				if ($this->mpdf->tableLevel) {
					$this->mpdf->_saveCellTextBuffer($blockpost);
				} else {
					$this->mpdf->_saveTextBuffer($blockpost);
				}
			}
		}

		$this->mpdf->ignorefollowingspaces = true; //Eliminate exceeding left-side spaces
		$this->mpdf->blockjustfinished = true;

		$this->mpdf->lastblockbottommargin = $this->mpdf->blk[$this->mpdf->blklvl]['margin_bottom'];
		// mPDF 6  Lists
		if ($tag === 'UL' || $tag === 'OL') {
			if ($this->mpdf->listlvl > 0 && $this->mpdf->tableLevel) {
				if (isset($this->mpdf->listtype[$this->mpdf->listlvl])) {
					unset($this->mpdf->listtype[$this->mpdf->listlvl]);
				}
			}
			$this->mpdf->listlvl--;
			$this->mpdf->listitem = [];
		}
		if ($tag === 'LI') {
			$this->mpdf->listitem = [];
		}

		if (preg_match('/^H\d/', $tag) && !$this->mpdf->tableLevel && !$this->mpdf->writingToC) {
			if (isset($this->mpdf->h2toc[$tag]) || isset($this->mpdf->h2bookmarks[$tag])) {
				$content = '';
				if (count($this->mpdf->textbuffer) == 1) {
					$content = $this->mpdf->textbuffer[0][0];
				} else {
					for ($i = 0; $i < count($this->mpdf->textbuffer); $i++) {
						if (0 !== strpos($this->mpdf->textbuffer[$i][0], "\xbb\xa4\xac")) { //inline object
							$content .= $this->mpdf->textbuffer[$i][0];
						}
					}
				}
				/* -- TOC -- */
				if (isset($this->mpdf->h2toc[$tag])) {
					$objattr = [];
					$objattr['type'] = 'toc';
					$objattr['toclevel'] = $this->mpdf->h2toc[$tag];
					$objattr['CONTENT'] = htmlspecialchars($content);
					$e = "\xbb\xa4\xactype=toc,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
					array_unshift($this->mpdf->textbuffer, [$e]);
				}
				/* -- END TOC -- */
				/* -- BOOKMARKS -- */
				if (isset($this->mpdf->h2bookmarks[$tag])) {
					$objattr = [];
					$objattr['type'] = 'bookmark';
					$objattr['bklevel'] = $this->mpdf->h2bookmarks[$tag];
					$objattr['CONTENT'] = $content;
					$e = "\xbb\xa4\xactype=toc,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
					array_unshift($this->mpdf->textbuffer, [$e]);
				}
				/* -- END BOOKMARKS -- */
			}
		}

		/* -- TABLES -- */
		if ($this->mpdf->tableLevel) {
			if ($this->mpdf->linebreakjustfinished) {
				$this->mpdf->blockjustfinished = false;
			}
			if (isset($this->mpdf->InlineProperties['BLOCKINTABLE'])) {
				if ($this->mpdf->InlineProperties['BLOCKINTABLE']) {
					$this->mpdf->restoreInlineProperties($this->mpdf->InlineProperties['BLOCKINTABLE']);
				}
				unset($this->mpdf->InlineProperties['BLOCKINTABLE']);
			}
			if ($tag === 'PRE') {
				$this->mpdf->ispre = false;
			}
			return;
		}
		/* -- END TABLES -- */
		$this->mpdf->lastoptionaltag = '';
		$this->mpdf->divbegin = false;

		$this->mpdf->linebreakjustfinished = false;

		$this->mpdf->x = $this->mpdf->lMargin + $this->mpdf->blk[$this->mpdf->blklvl]['outer_left_margin'];

		/* -- CSS-FLOAT -- */
		// If float contained in a float, need to extend bottom to allow for it
		$currpos = $this->mpdf->page * 1000 + $this->mpdf->y;
		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['float_endpos']) && $this->mpdf->blk[$this->mpdf->blklvl]['float_endpos'] > $currpos) {
			$old_page = $this->mpdf->page;
			$new_page = (int) ($this->mpdf->blk[$this->mpdf->blklvl]['float_endpos'] / 1000);
			if ($old_page != $new_page) {
				$s = $this->mpdf->PrintPageBackgrounds();
				// Writes after the marker so not overwritten later by page background etc.
				$this->mpdf->pages[$this->mpdf->page] = preg_replace(
					'/(___BACKGROUND___PATTERNS' . $this->mpdf->uniqstr . ')/',
					'\\1' . "\n" . $s . "\n",
					$this->mpdf->pages[$this->mpdf->page]
				);
				$this->mpdf->pageBackgrounds = [];
				$this->mpdf->page = $new_page;
				$this->mpdf->ResetMargins();
				$this->mpdf->Reset();
				$this->mpdf->pageoutput[$this->mpdf->page] = [];
			}
			// mod changes operands to integers before processing
			$this->mpdf->y = (($this->mpdf->blk[$this->mpdf->blklvl]['float_endpos'] * 1000) % 1000000) / 1000;
		}
		/* -- END CSS-FLOAT -- */


		//Print content
		$blockstate = 0;
		if ($this->mpdf->lastblocklevelchange == 1) {
			$blockstate = 3;
		} // Top & bottom margins/padding
		elseif ($this->mpdf->lastblocklevelchange == -1) {
			$blockstate = 2;
		} // Bottom margins/padding only

		// called from after e.g. </table> </div> </div> ...    Outputs block margin/border and padding
		if (count($this->mpdf->textbuffer) && $this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1]) {
			if (0 !== strpos($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0], "\xbb\xa4\xac")) { // not special content
				// Right trim last content and adjust OTLdata
				if (preg_match('/[ ]+$/', $this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0], $m)) {
					$strip = strlen($m[0]);
					$this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0] = substr(
						$this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0],
						0,
						strlen($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0]) - $strip
					);
					/* -- OTL -- */
					if (!empty($this->mpdf->CurrentFont['useOTL'])) {
						$this->otl->trimOTLdata($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][18], false); // mPDF 6  ZZZ99K
					}
					/* -- END OTL -- */
				}
			}
		}

		if (count($this->mpdf->textbuffer) == 0 && $this->mpdf->lastblocklevelchange != 0) {
			/*$this->mpdf->newFlowingBlock(
				$this->mpdf->blk[$this->mpdf->blklvl]['width'],
				$this->mpdf->lineheight,
				'',
				false,
				2,
				true,
				(isset($this->mpdf->blk[$this->mpdf->blklvl]['direction']) ? $this->mpdf->blk[$this->mpdf->blklvl]['direction'] : 'ltr')
			);*/

			$this->mpdf->newFlowingBlock(
				$this->mpdf->blk[$this->mpdf->blklvl]['width'],
				$this->mpdf->lineheight,
				'',
				false,
				$blockstate,
				true,
				(isset($this->mpdf->blk[$this->mpdf->blklvl]['direction']) ? $this->mpdf->blk[$this->mpdf->blklvl]['direction'] : 'ltr')
			);

			$this->mpdf->finishFlowingBlock(true); // true = END of flowing block
			$this->mpdf->PaintDivBB('', $blockstate);
		} else {
			$this->mpdf->printbuffer($this->mpdf->textbuffer, $blockstate);
		}


		$this->mpdf->textbuffer = [];

		if ($this->mpdf->kwt) {
			$this->mpdf->kwt_height = $this->mpdf->y - $this->mpdf->kwt_y0;
		}

		/* -- CSS-IMAGE-FLOAT -- */
		$this->mpdf->printfloatbuffer();
		/* -- END CSS-IMAGE-FLOAT -- */

		if ($tag === 'PRE') {
			$this->mpdf->ispre = false;
		}

		/* -- CSS-FLOAT -- */
		if ($this->mpdf->blk[$this->mpdf->blklvl]['float'] === 'R') {
			// If width not set, here would need to adjust and output buffer
			$s = $this->mpdf->PrintPageBackgrounds();
			// Writes after the marker so not overwritten later by page background etc.
			$this->mpdf->pages[$this->mpdf->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->mpdf->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->mpdf->pages[$this->mpdf->page]);
			$this->mpdf->pageBackgrounds = [];
			$this->mpdf->Reset();
			$this->mpdf->pageoutput[$this->mpdf->page] = [];

			for ($i = ($this->mpdf->blklvl - 1); $i >= 0; $i--) {
				if (isset($this->mpdf->blk[$i]['float_endpos'])) {
					$this->mpdf->blk[$i]['float_endpos'] = max($this->mpdf->blk[$i]['float_endpos'], $this->mpdf->page * 1000 + $this->mpdf->y);
				} else {
					$this->mpdf->blk[$i]['float_endpos'] = $this->mpdf->page * 1000 + $this->mpdf->y;
				}
			}

			$this->mpdf->floatDivs[] = [
				'side' => 'R',
				'startpage' => $this->mpdf->blk[$this->mpdf->blklvl]['startpage'],
				'y0' => $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'],
				'startpos' => $this->mpdf->blk[$this->mpdf->blklvl]['startpage'] * 1000 + $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'],
				'endpage' => $this->mpdf->page,
				'y1' => $this->mpdf->y,
				'endpos' => $this->mpdf->page * 1000 + $this->mpdf->y,
				'w' => $this->mpdf->blk[$this->mpdf->blklvl]['float_width'],
				'blklvl' => $this->mpdf->blklvl,
				'blockContext' => $this->mpdf->blk[$this->mpdf->blklvl - 1]['blockContext']
			];

			$this->mpdf->y = $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'];
			$this->mpdf->page = $this->mpdf->blk[$this->mpdf->blklvl]['startpage'];
			$this->mpdf->ResetMargins();
			$this->mpdf->pageoutput[$this->mpdf->page] = [];
		}
		if ($this->mpdf->blk[$this->mpdf->blklvl]['float'] === 'L') {
			// If width not set, here would need to adjust and output buffer
			$s = $this->mpdf->PrintPageBackgrounds();
			// Writes after the marker so not overwritten later by page background etc.
			$this->mpdf->pages[$this->mpdf->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->mpdf->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->mpdf->pages[$this->mpdf->page]);
			$this->mpdf->pageBackgrounds = [];
			$this->mpdf->Reset();
			$this->mpdf->pageoutput[$this->mpdf->page] = [];

			for ($i = ($this->mpdf->blklvl - 1); $i >= 0; $i--) {
				if (isset($this->mpdf->blk[$i]['float_endpos'])) {
					$this->mpdf->blk[$i]['float_endpos'] = max($this->mpdf->blk[$i]['float_endpos'], $this->mpdf->page * 1000 + $this->mpdf->y);
				} else {
					$this->mpdf->blk[$i]['float_endpos'] = $this->mpdf->page * 1000 + $this->mpdf->y;
				}
			}

			$this->mpdf->floatDivs[] = [
				'side' => 'L',
				'startpage' => $this->mpdf->blk[$this->mpdf->blklvl]['startpage'],
				'y0' => $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'],
				'startpos' => $this->mpdf->blk[$this->mpdf->blklvl]['startpage'] * 1000 + $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'],
				'endpage' => $this->mpdf->page,
				'y1' => $this->mpdf->y,
				'endpos' => $this->mpdf->page * 1000 + $this->mpdf->y,
				'w' => $this->mpdf->blk[$this->mpdf->blklvl]['float_width'],
				'blklvl' => $this->mpdf->blklvl,
				'blockContext' => $this->mpdf->blk[$this->mpdf->blklvl - 1]['blockContext']
			];

			$this->mpdf->y = $this->mpdf->blk[$this->mpdf->blklvl]['float_start_y'];
			$this->mpdf->page = $this->mpdf->blk[$this->mpdf->blklvl]['startpage'];
			$this->mpdf->ResetMargins();
			$this->mpdf->pageoutput[$this->mpdf->page] = [];
		}
		/* -- END CSS-FLOAT -- */

		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['visibility']) && $this->mpdf->blk[$this->mpdf->blklvl]['visibility'] !== 'visible') {
			$this->mpdf->SetVisibility('visible');
		}

		$page_break_after = '';
		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['page_break_after'])) {
			$page_break_after = $this->mpdf->blk[$this->mpdf->blklvl]['page_break_after'];
		}

		// Reset values
		$this->mpdf->Reset();

		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['z-index']) && $this->mpdf->blk[$this->mpdf->blklvl]['z-index'] > 0) {
			$this->mpdf->EndLayer();
		}

		// mPDF 6 page-break-inside:avoid
		if ($this->mpdf->blk[$this->mpdf->blklvl]['keep_block_together']) {
			$movepage = false;
			// If page-break-inside:avoid section has broken to new page but fits on one side - then move:
			if (($this->mpdf->page - $this->mpdf->kt_p00) == 1 && $this->mpdf->y < $this->mpdf->kt_y00) {
				$movepage = true;
			}
			if (($this->mpdf->page - $this->mpdf->kt_p00) > 0) {
				for ($i = $this->mpdf->page; $i > $this->mpdf->kt_p00; $i--) {
					unset($this->mpdf->pages[$i]);
					if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bb_painted'][$i])) {
						unset($this->mpdf->blk[$this->mpdf->blklvl]['bb_painted'][$i]);
					}
					if (isset($this->mpdf->blk[$this->mpdf->blklvl]['marginCorrected'][$i])) {
						unset($this->mpdf->blk[$this->mpdf->blklvl]['marginCorrected'][$i]);
					}
					if (isset($this->mpdf->pageoutput[$i])) {
						unset($this->mpdf->pageoutput[$i]);
					}
				}
				$this->mpdf->page = $this->mpdf->kt_p00;
			}
			$this->mpdf->keep_block_together = 0;
			$this->mpdf->pageoutput[$this->mpdf->page] = [];

			$this->mpdf->y = $this->mpdf->kt_y00;

			$ihtml = $this->mpdf->blk[$this->mpdf->blklvl]['array_i'] - 1;

			$ahtml[$ihtml + 1] .= ' pagebreakavoidchecked="true";'; // avoid re-iterating; read in OpenTag()

			unset($this->mpdf->blk[$this->mpdf->blklvl]);
			$this->mpdf->blklvl--;

			for ($blklvl = 1; $blklvl <= $this->mpdf->blklvl; $blklvl++) {
				$this->mpdf->blk[$blklvl]['y0'] = $this->mpdf->blk[$blklvl]['initial_y0'];
				$this->mpdf->blk[$blklvl]['x0'] = $this->mpdf->blk[$blklvl]['initial_x0'];
				$this->mpdf->blk[$blklvl]['startpage'] = $this->mpdf->blk[$blklvl]['initial_startpage'];
			}

			if (isset($this->mpdf->blk[$this->mpdf->blklvl]['x0'])) {
				$this->mpdf->x = $this->mpdf->blk[$this->mpdf->blklvl]['x0'];
			} else {
				$this->mpdf->x = $this->mpdf->lMargin;
			}

			$this->mpdf->lastblocklevelchange = 0;
			$this->mpdf->ResetMargins();
			if ($movepage) {
				$this->mpdf->AddPage();
			}
			return;
		}

		if ($this->mpdf->blklvl > 0) { // ==0 SHOULDN'T HAPPEN - NOT XHTML
			if ($this->mpdf->blk[$this->mpdf->blklvl]['tag'] == $tag) {
				unset($this->mpdf->blk[$this->mpdf->blklvl]);
				$this->mpdf->blklvl--;
			}
			//else { echo $tag; exit; }	// debug - forces error if incorrectly nested html tags
		}

		$this->mpdf->lastblocklevelchange = -1;
		// Reset Inline-type properties
		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['InlineProperties'])) {
			$this->mpdf->restoreInlineProperties($this->mpdf->blk[$this->mpdf->blklvl]['InlineProperties']);
		}

		$this->mpdf->x = $this->mpdf->lMargin + $this->mpdf->blk[$this->mpdf->blklvl]['outer_left_margin'];

		if (!$this->mpdf->tableLevel && $page_break_after) {
			$save_blklvl = $this->mpdf->blklvl;
			$save_blk = $this->mpdf->blk;
			$save_silp = $this->mpdf->saveInlineProperties();
			$save_ilp = $this->mpdf->InlineProperties;
			$save_bflp = $this->mpdf->InlineBDF;
			$save_bflpc = $this->mpdf->InlineBDFctr; // mPDF 6
			// mPDF 6 pagebreaktype
			$startpage = $this->mpdf->page;
			$pagebreaktype = $this->mpdf->defaultPagebreakType;
			if ($this->mpdf->ColActive) {
				$pagebreaktype = 'cloneall';
			}

			// mPDF 6 pagebreaktype
			$this->mpdf->_preForcedPagebreak($pagebreaktype);

			if ($page_break_after === 'RIGHT') {
				$this->mpdf->AddPage($this->mpdf->CurOrientation, 'NEXT-ODD');
			} elseif ($page_break_after === 'LEFT') {
				$this->mpdf->AddPage($this->mpdf->CurOrientation, 'NEXT-EVEN');
			} else {
				$this->mpdf->AddPage($this->mpdf->CurOrientation);
			}

			// mPDF 6 pagebreaktype
			$this->mpdf->_postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl);

			$this->mpdf->InlineProperties = $save_ilp;
			$this->mpdf->InlineBDF = $save_bflp;
			$this->mpdf->InlineBDFctr = $save_bflpc; // mPDF 6
			$this->mpdf->restoreInlineProperties($save_silp);
		}
		// mPDF 6 bidi
		// Block
		// If unicode-bidi set, any embedding levels, isolates, or overrides reopened in the continuing block
		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bidicode'])) {
			$blockpre = $this->mpdf->_setBidiCodes('start', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
			if ($blockpre) {
				$this->mpdf->OTLdata = [];
				if ($this->mpdf->tableLevel) {
					$this->mpdf->_saveCellTextBuffer($blockpre);
				} else {
					$this->mpdf->_saveTextBuffer($blockpre);
				}
			}
		}
	}

}
