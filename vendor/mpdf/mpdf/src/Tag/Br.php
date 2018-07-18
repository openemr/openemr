<?php

namespace Mpdf\Tag;

class Br extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		// Added mPDF 3.0 Float DIV - CLEAR
		if (isset($attr['STYLE'])) {
			$properties = $this->cssManager->readInlineCSS($attr['STYLE']);
			if (isset($properties['CLEAR'])) {
				$this->mpdf->ClearFloats(strtoupper($properties['CLEAR']), $this->mpdf->blklvl);
			} // *CSS-FLOAT*
		}

		// mPDF 6 bidi
		// Inline
		// If unicode-bidi set, any embedding levels, isolates, or overrides started by
		// the inline box are closed at the br and reopened on the other side
		$blockpre = '';
		$blockpost = '';
		if (isset($this->mpdf->blk[$this->mpdf->blklvl]['bidicode'])) {
			$blockpre = $this->mpdf->_setBidiCodes('end', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
			$blockpost = $this->mpdf->_setBidiCodes('start', $this->mpdf->blk[$this->mpdf->blklvl]['bidicode']);
		}

		// Inline
		// If unicode-bidi set, any embedding levels, isolates, or overrides started by
		// the inline box are closed at the br and reopened on the other side
		$inlinepre = '';
		$inlinepost = '';
		$iBDF = [];
		if (count($this->mpdf->InlineBDF)) {
			foreach ($this->mpdf->InlineBDF as $k => $ib) {
				foreach ($ib as $ib2) {
					$iBDF[$ib2[1]] = $ib2[0];
				}
			}
			if (count($iBDF)) {
				ksort($iBDF);
				for ($i = count($iBDF) - 1; $i >= 0; $i--) {
					$inlinepre .= $this->mpdf->_setBidiCodes('end', $iBDF[$i]);
				}
				for ($i = 0; $i < count($iBDF); $i++) {
					$inlinepost .= $this->mpdf->_setBidiCodes('start', $iBDF[$i]);
				}
			}
		}

		/* -- TABLES -- */
		if ($this->mpdf->tableLevel) {
			if ($this->mpdf->blockjustfinished) {
				$this->mpdf->_saveCellTextBuffer($blockpre . $inlinepre . "\n" . $inlinepost . $blockpost);
			}

			$this->mpdf->_saveCellTextBuffer($blockpre . $inlinepre . "\n" . $inlinepost . $blockpost);
			if (!isset($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'])) {
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
			} elseif ($this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] < $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s']) {
				$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['maxs'] = $this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'];
			}
			$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col]['s'] = 0; // reset
		} else {
			/* -- END TABLES -- */
			if (count($this->mpdf->textbuffer)) {
				$this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0] = preg_replace(
					'/ $/',
					'',
					$this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][0]
				);
				if (!empty($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][18])) {
					$this->otl->trimOTLdata($this->mpdf->textbuffer[count($this->mpdf->textbuffer) - 1][18], false);
				} // *OTL*
			}
			$this->mpdf->_saveTextBuffer($blockpre . $inlinepre . "\n" . $inlinepost . $blockpost);
		} // *TABLES*
		$this->mpdf->ignorefollowingspaces = true;
		$this->mpdf->blockjustfinished = false;

		$this->mpdf->linebreakjustfinished = true;
	}

	public function close(&$ahtml, &$ihtml)
	{
	}
}
