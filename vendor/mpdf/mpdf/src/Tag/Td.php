<?php

namespace Mpdf\Tag;

use Mpdf\Css\Border;
use Mpdf\Css\TextVars;
use Mpdf\Utils\UtfString;

class Td extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$tag = $this->getTagName();

		$this->mpdf->ignorefollowingspaces = true;
		$this->mpdf->lastoptionaltag = $tag; // Save current HTML specified optional endtag

		$this->cssManager->tbCSSlvl++;

		$this->mpdf->InlineProperties = [];
		$this->mpdf->InlineBDF = []; // mPDF 6
		$this->mpdf->InlineBDFctr = 0; // mPDF 6
		$this->mpdf->tdbegin = true;
		$this->mpdf->col++;

		while (isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col])) {
			$this->mpdf->col++;
		}

		// Update number column
		if ($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'] < $this->mpdf->col + 1) {
			$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'] = $this->mpdf->col + 1;
		}

		$table = &$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]];

		$c = ['a' => false,
			'R' => false,
			'nowrap' => false,
			'bgcolor' => false,
			'padding' => ['L' => false,
				'R' => false,
				'T' => false,
				'B' => false
			]
		];

		if ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0) {
			$table['simple']['border'] = false;
			$table['simple']['border_details']['R']['w'] = 0;
			$table['simple']['border_details']['L']['w'] = 0;
			$table['simple']['border_details']['T']['w'] = 0;
			$table['simple']['border_details']['B']['w'] = 0;
			$table['simple']['border_details']['R']['style'] = '';
			$table['simple']['border_details']['L']['style'] = '';
			$table['simple']['border_details']['T']['style'] = '';
			$table['simple']['border_details']['B']['style'] = '';
		} elseif (!$this->mpdf->simpleTables) {
			$c['border'] = false;
			$c['border_details']['R']['w'] = 0;
			$c['border_details']['L']['w'] = 0;
			$c['border_details']['T']['w'] = 0;
			$c['border_details']['B']['w'] = 0;
			$c['border_details']['mbw']['BL'] = 0;
			$c['border_details']['mbw']['BR'] = 0;
			$c['border_details']['mbw']['RT'] = 0;
			$c['border_details']['mbw']['RB'] = 0;
			$c['border_details']['mbw']['TL'] = 0;
			$c['border_details']['mbw']['TR'] = 0;
			$c['border_details']['mbw']['LT'] = 0;
			$c['border_details']['mbw']['LB'] = 0;
			$c['border_details']['R']['style'] = '';
			$c['border_details']['L']['style'] = '';
			$c['border_details']['T']['style'] = '';
			$c['border_details']['B']['style'] = '';
			$c['border_details']['R']['s'] = 0;
			$c['border_details']['L']['s'] = 0;
			$c['border_details']['T']['s'] = 0;
			$c['border_details']['B']['s'] = 0;
			$c['border_details']['R']['c'] = $this->colorConverter->convert(0, $this->mpdf->PDFAXwarnings);
			$c['border_details']['L']['c'] = $this->colorConverter->convert(0, $this->mpdf->PDFAXwarnings);
			$c['border_details']['T']['c'] = $this->colorConverter->convert(0, $this->mpdf->PDFAXwarnings);
			$c['border_details']['B']['c'] = $this->colorConverter->convert(0, $this->mpdf->PDFAXwarnings);
			$c['border_details']['R']['dom'] = 0;
			$c['border_details']['L']['dom'] = 0;
			$c['border_details']['T']['dom'] = 0;
			$c['border_details']['B']['dom'] = 0;
			$c['border_details']['cellposdom'] = 0;
		}


		if ($table['va']) {
			$c['va'] = $table['va'];
		}
		if ($table['txta']) {
			$c['a'] = $table['txta'];
		}
		if ($this->mpdf->table_border_attr_set && $table['border_details']) {
			if (!$this->mpdf->simpleTables) {
				$c['border_details']['R'] = $table['border_details']['R'];
				$c['border_details']['L'] = $table['border_details']['L'];
				$c['border_details']['T'] = $table['border_details']['T'];
				$c['border_details']['B'] = $table['border_details']['B'];
				$c['border'] = $table['border'];
				$c['border_details']['L']['dom'] = 1;
				$c['border_details']['R']['dom'] = 1;
				$c['border_details']['T']['dom'] = 1;
				$c['border_details']['B']['dom'] = 1;
			} elseif ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0) {
				$table['simple']['border_details']['R'] = $table['border_details']['R'];
				$table['simple']['border_details']['L'] = $table['border_details']['L'];
				$table['simple']['border_details']['T'] = $table['border_details']['T'];
				$table['simple']['border_details']['B'] = $table['border_details']['B'];
				$table['simple']['border'] = $table['border'];
			}
		}
		// INHERITED THEAD CSS Properties
		if ($this->mpdf->tablethead) {
			if ($this->mpdf->thead_valign_default) {
				$c['va'] = $this->getAlign($this->mpdf->thead_valign_default);
			}
			if ($this->mpdf->thead_textalign_default) {
				$c['a'] = $this->getAlign($this->mpdf->thead_textalign_default);
			}
			if ($this->mpdf->thead_font_weight === 'B') {
				$this->mpdf->SetStyle('B', true);
			}
			if ($this->mpdf->thead_font_style === 'I') {
				$this->mpdf->SetStyle('I', true);
			}
			if ($this->mpdf->thead_font_smCaps === 'S') {
				$this->mpdf->textvar |= TextVars::FC_SMALLCAPS;
			} // mPDF 5.7.1
		}

		// INHERITED TFOOT CSS Properties
		if ($this->mpdf->tabletfoot) {
			if ($this->mpdf->tfoot_valign_default) {
				$c['va'] = $this->getAlign($this->mpdf->tfoot_valign_default);
			}
			if ($this->mpdf->tfoot_textalign_default) {
				$c['a'] = $this->getAlign($this->mpdf->tfoot_textalign_default);
			}
			if ($this->mpdf->tfoot_font_weight === 'B') {
				$this->mpdf->SetStyle('B', true);
			}
			if ($this->mpdf->tfoot_font_style === 'I') {
				$this->mpdf->SetStyle('I', true);
			}
			if ($this->mpdf->tfoot_font_style === 'S') {
				$this->mpdf->textvar |= TextVars::FC_SMALLCAPS;
			} // mPDF 5.7.1
		}


		if ($this->mpdf->trow_text_rotate) {
			$c['R'] = $this->mpdf->trow_text_rotate;
		}

		$this->mpdf->cell_border_dominance_L = 0;
		$this->mpdf->cell_border_dominance_R = 0;
		$this->mpdf->cell_border_dominance_T = 0;
		$this->mpdf->cell_border_dominance_B = 0;

		$properties = $this->cssManager->MergeCSS('TABLE', $tag, $attr);

		$properties = $this->cssManager->array_merge_recursive_unique($this->mpdf->base_table_properties, $properties);

		$this->mpdf->Reset(); // mPDF 6   ?????????????????????

		$this->mpdf->setCSS($properties, 'TABLECELL', $tag);

		$c['dfs'] = $this->mpdf->FontSize; // Default Font size


		if (isset($properties['BACKGROUND-COLOR'])) {
			$c['bgcolor'] = $properties['BACKGROUND-COLOR'];
		} elseif (isset($properties['BACKGROUND'])) {
			$c['bgcolor'] = $properties['BACKGROUND'];
		} elseif (isset($attr['BGCOLOR'])) {
			$c['bgcolor'] = $attr['BGCOLOR'];
		}



		/* -- BACKGROUNDS -- */
		if (isset($properties['BACKGROUND-GRADIENT'])) {
			$c['gradient'] = $properties['BACKGROUND-GRADIENT'];
		} else {
			$c['gradient'] = false;
		}

		if (!empty($properties['BACKGROUND-IMAGE']) && !$this->mpdf->keep_block_together) {
			$ret = $this->mpdf->SetBackground($properties, $this->mpdf->blk[$this->mpdf->blklvl]['inner_width']);
			if ($ret) {
				$c['background-image'] = $ret;
			}
		}
		/* -- END BACKGROUNDS -- */
		if (isset($properties['VERTICAL-ALIGN'])) {
			$c['va'] = $this->getAlign($properties['VERTICAL-ALIGN']);
		} elseif (isset($attr['VALIGN'])) {
			$c['va'] = $this->getAlign($attr['VALIGN']);
		}


		if (!empty($properties['TEXT-ALIGN'])) {
			if (0 === strpos($properties['TEXT-ALIGN'], 'D')) {
				$c['a'] = $properties['TEXT-ALIGN'];
			} else {
				$c['a'] = $this->getAlign($properties['TEXT-ALIGN']);
			}
		}
		if (!empty($attr['ALIGN'])) {
			if (strtolower($attr['ALIGN']) === 'char') {
				if (!empty($attr['CHAR'])) {
					$char = html_entity_decode($attr['CHAR']);
					$char = UtfString::strcode2utf($char);
					$d = array_search($char, $this->mpdf->decimal_align);
					if ($d !== false) {
						$c['a'] = $d . 'R';
					}
				} else {
					$c['a'] = 'DPR';
				}
			} else {
				$c['a'] = $this->getAlign($attr['ALIGN']);
			}
		}

		// mPDF 6
		$c['direction'] = $table['direction'];
		if (isset($attr['DIR']) && $attr['DIR'] != '') {
			$c['direction'] = strtolower($attr['DIR']);
		}
		if (isset($properties['DIRECTION'])) {
			$c['direction'] = strtolower($properties['DIRECTION']);
		}

		if (!$c['a']) {
			if (isset($c['direction']) && $c['direction'] === 'rtl') {
				$c['a'] = 'R';
			} else {
				$c['a'] = 'L';
			}
		}

		$c['cellLineHeight'] = $table['cellLineHeight'];
		if (isset($properties['LINE-HEIGHT'])) {
			$c['cellLineHeight'] = $this->mpdf->fixLineheight($properties['LINE-HEIGHT']);
		}

		$c['cellLineStackingStrategy'] = $table['cellLineStackingStrategy'];
		if (isset($properties['LINE-STACKING-STRATEGY'])) {
			$c['cellLineStackingStrategy'] = strtolower($properties['LINE-STACKING-STRATEGY']);
		}

		$c['cellLineStackingShift'] = $table['cellLineStackingShift'];
		if (isset($properties['LINE-STACKING-SHIFT'])) {
			$c['cellLineStackingShift'] = strtolower($properties['LINE-STACKING-SHIFT']);
		}

		if (isset($properties['TEXT-ROTATE']) && ($properties['TEXT-ROTATE'] || $properties['TEXT-ROTATE'] === '0')) {
			$c['R'] = $properties['TEXT-ROTATE'];
		}
		if (isset($properties['BORDER'])) {
			$bord = $this->mpdf->border_details($properties['BORDER']);
			if ($bord['s']) {
				if (!$this->mpdf->simpleTables) {
					$c['border'] = Border::ALL;
					$c['border_details']['R'] = $bord;
					$c['border_details']['L'] = $bord;
					$c['border_details']['T'] = $bord;
					$c['border_details']['B'] = $bord;
					$c['border_details']['L']['dom'] = $this->mpdf->cell_border_dominance_L;
					$c['border_details']['R']['dom'] = $this->mpdf->cell_border_dominance_R;
					$c['border_details']['T']['dom'] = $this->mpdf->cell_border_dominance_T;
					$c['border_details']['B']['dom'] = $this->mpdf->cell_border_dominance_B;
				} elseif ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0) {
					$table['simple']['border'] = Border::ALL;
					$table['simple']['border_details']['R'] = $bord;
					$table['simple']['border_details']['L'] = $bord;
					$table['simple']['border_details']['T'] = $bord;
					$table['simple']['border_details']['B'] = $bord;
				}
			}
		}
		if (!$this->mpdf->simpleTables) {
			if (!empty($properties['BORDER-RIGHT'])) {
				$c['border_details']['R'] = $this->mpdf->border_details($properties['BORDER-RIGHT']);
				$this->mpdf->setBorder($c['border'], Border::RIGHT, $c['border_details']['R']['s']);
				$c['border_details']['R']['dom'] = $this->mpdf->cell_border_dominance_R;
			}
			if (!empty($properties['BORDER-LEFT'])) {
				$c['border_details']['L'] = $this->mpdf->border_details($properties['BORDER-LEFT']);
				$this->mpdf->setBorder($c['border'], Border::LEFT, $c['border_details']['L']['s']);
				$c['border_details']['L']['dom'] = $this->mpdf->cell_border_dominance_L;
			}
			if (!empty($properties['BORDER-BOTTOM'])) {
				$c['border_details']['B'] = $this->mpdf->border_details($properties['BORDER-BOTTOM']);
				$this->mpdf->setBorder($c['border'], Border::BOTTOM, $c['border_details']['B']['s']);
				$c['border_details']['B']['dom'] = $this->mpdf->cell_border_dominance_B;
			}
			if (!empty($properties['BORDER-TOP'])) {
				$c['border_details']['T'] = $this->mpdf->border_details($properties['BORDER-TOP']);
				$this->mpdf->setBorder($c['border'], Border::TOP, $c['border_details']['T']['s']);
				$c['border_details']['T']['dom'] = $this->mpdf->cell_border_dominance_T;
			}
		} elseif ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0) {
			if (!empty($properties['BORDER-LEFT'])) {
				$bord = $this->mpdf->border_details($properties['BORDER-LEFT']);
				if ($bord['s']) {
					$table['simple']['border'] = Border::ALL;
				} else {
					$table['simple']['border'] = 0;
				}
				$table['simple']['border_details']['R'] = $bord;
				$table['simple']['border_details']['L'] = $bord;
				$table['simple']['border_details']['T'] = $bord;
				$table['simple']['border_details']['B'] = $bord;
			}
		}

		if ($this->mpdf->simpleTables && $this->mpdf->row == 0 && $this->mpdf->col == 0 && !$table['borders_separate'] && $table['simple']['border']) {
			$table['border_details'] = $table['simple']['border_details'];
			$table['border'] = $table['simple']['border'];
		}

		// Border set on TR (if collapsed only)
		if (!$table['borders_separate'] && !$this->mpdf->simpleTables && isset($table['trborder-left'][$this->mpdf->row])) {
			if ($this->mpdf->col == 0) {
				$left = $this->mpdf->border_details($table['trborder-left'][$this->mpdf->row]);
				$c['border_details']['L'] = $left;
				$this->mpdf->setBorder($c['border'], Border::LEFT, $c['border_details']['L']['s']);
			}
			$c['border_details']['B'] = $this->mpdf->border_details($table['trborder-bottom'][$this->mpdf->row]);
			$this->mpdf->setBorder($c['border'], Border::BOTTOM, $c['border_details']['B']['s']);
			$c['border_details']['T'] = $this->mpdf->border_details($table['trborder-top'][$this->mpdf->row]);
			$this->mpdf->setBorder($c['border'], Border::TOP, $c['border_details']['T']['s']);
		}

		if ($this->mpdf->packTableData && !$this->mpdf->simpleTables) {
			$c['borderbin'] = $this->mpdf->_packCellBorder($c);
			unset($c['border'], $c['border_details']);
		}

		if (isset($properties['PADDING-LEFT'])) {
			$c['padding']['L'] = $this->sizeConverter->convert($properties['PADDING-LEFT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
		}
		if (isset($properties['PADDING-RIGHT'])) {
			$c['padding']['R'] = $this->sizeConverter->convert($properties['PADDING-RIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
		}
		if (isset($properties['PADDING-BOTTOM'])) {
			$c['padding']['B'] = $this->sizeConverter->convert($properties['PADDING-BOTTOM'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
		}
		if (isset($properties['PADDING-TOP'])) {
			$c['padding']['T'] = $this->sizeConverter->convert($properties['PADDING-TOP'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
		}

		$w = '';
		if (isset($properties['WIDTH'])) {
			$w = $properties['WIDTH'];
		} elseif (isset($attr['WIDTH'])) {
			$w = $attr['WIDTH'];
		}
		if ($w) {
			if (strpos($w, '%') && !$this->mpdf->ignore_table_percents) {
				$c['wpercent'] = (float) $w;
			} // makes 80% -> 80
			elseif (!strpos($w, '%') && !$this->mpdf->ignore_table_widths) {
				$c['w'] = $this->sizeConverter->convert($w, $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
			}
		}

		if (isset($properties['HEIGHT']) && !strpos($properties['HEIGHT'], '%')) {
			$c['h'] = $this->sizeConverter->convert($properties['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
		} elseif (isset($attr['HEIGHT']) && !strpos($attr['HEIGHT'], '%')) {
			$c['h'] = $this->sizeConverter->convert($attr['HEIGHT'], $this->mpdf->blk[$this->mpdf->blklvl]['inner_width'], $this->mpdf->FontSize, false);
		}

		if (isset($properties['WHITE-SPACE'])) {
			if (strtoupper($properties['WHITE-SPACE']) === 'NOWRAP') {
				$c['nowrap'] = 1;
			}
		}

		if (isset($attr['TEXT-ROTATE'])) {
			$c['R'] = $attr['TEXT-ROTATE'];
		}
		if (!empty($attr['NOWRAP'])) {
			$c['nowrap'] = 1;
		}

		$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col] = $c;
		unset($c);
		$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0;

		$cs = $rs = 1;
		if (isset($attr['COLSPAN']) && $attr['COLSPAN'] > 1) {
			$cs = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['colspan'] = $attr['COLSPAN'];
		}
		if ($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'] < $this->mpdf->col + $cs) {
			$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['nc'] = $this->mpdf->col + $cs;
		} // following code moved outside if...
		for ($l = $this->mpdf->col; $l < $this->mpdf->col + $cs; $l++) {
			if ($l - $this->mpdf->col) {
				$this->mpdf->cell[$this->mpdf->row][$l] = 0;
			}
		}
		if (isset($attr['ROWSPAN']) && $attr['ROWSPAN'] > 1) {
			$rs = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['rowspan'] = $attr['ROWSPAN'];
		}
		for ($k = $this->mpdf->row; $k < $this->mpdf->row + $rs; $k++) {
			for ($l = $this->mpdf->col; $l < $this->mpdf->col + $cs; $l++) {
				if ($k - $this->mpdf->row || $l - $this->mpdf->col) {
					$this->mpdf->cell[$k][$l] = 0;
				}
			}
		}
		unset($table);
	}

	public function close(&$ahtml, &$ihtml)
	{
		if ($this->mpdf->tableLevel) {
			$this->mpdf->lastoptionaltag = 'TR';
			unset($this->cssManager->tablecascadeCSS[$this->cssManager->tbCSSlvl]);
			$this->cssManager->tbCSSlvl--;
			if (!$this->mpdf->tdbegin) {
				return;
			}
			$this->mpdf->tdbegin = false;
			// Added for correct calculation of cell column width - otherwise misses the last line if not end </p> etc.
			if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
				if (!is_array($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col])) {
					throw new \Mpdf\MpdfException('You may have an error in your HTML code e.g. &lt;/td&gt;&lt;/td&gt;');
				}
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
			} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
			}

			// Remove last <br> if at end of cell
			$ntb = 0;
			if (isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'])) {
				$ntb = count($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer']);
			}
			if ($ntb > 1 && $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][$ntb - 1][0] === "\n") {
				unset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['textbuffer'][$ntb - 1]);
			}

			if ($this->mpdf->tablethead) {
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_thead'][$this->mpdf->row] = true;
				if ($this->mpdf->tableLevel == 1) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['headernrows']
						= max($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['headernrows'], $this->mpdf->row + 1);
				}
			}
			if ($this->mpdf->tabletfoot) {
				$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['is_tfoot'][$this->mpdf->row] = true;
				if ($this->mpdf->tableLevel == 1) {
					$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['footernrows']
						= max(
							$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['footernrows'],
							$this->mpdf->row + 1 - $this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['headernrows']
						);
				}
			}
			$this->mpdf->Reset();
		}
	}
}
