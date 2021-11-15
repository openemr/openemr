<?php

namespace Mpdf\Writer;

use Mpdf\Strict;
use Mpdf\Mpdf;

final class BookmarkWriter
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

	public function writeBookmarks() // _putbookmarks
	{
		$nb = count($this->mpdf->BMoutlines);
		if ($nb === 0) {
			return;
		}

		$bmo = $this->mpdf->BMoutlines;
		$this->mpdf->BMoutlines = [];
		$lastlevel = -1;
		for ($i = 0; $i < count($bmo); $i++) {
			if ($bmo[$i]['l'] > 0) {
				while ($bmo[$i]['l'] - $lastlevel > 1) { // If jump down more than one level, insert a new entry
					$new = $bmo[$i];
					$new['t'] = "[" . $new['t'] . "]"; // Put [] around text/title to highlight
					$new['l'] = $lastlevel + 1;
					$lastlevel++;
					$this->mpdf->BMoutlines[] = $new;
				}
			}
			$this->mpdf->BMoutlines[] = $bmo[$i];
			$lastlevel = $bmo[$i]['l'];
		}
		$nb = count($this->mpdf->BMoutlines);

		$lru = [];
		$level = 0;
		foreach ($this->mpdf->BMoutlines as $i => $o) {
			if ($o['l'] > 0) {
				$parent = $lru[$o['l'] - 1];
				// Set parent and last pointers
				$this->mpdf->BMoutlines[$i]['parent'] = $parent;
				$this->mpdf->BMoutlines[$parent]['last'] = $i;
				if ($o['l'] > $level) {
					// Level increasing: set first pointer
					$this->mpdf->BMoutlines[$parent]['first'] = $i;
				}
			} else {
				$this->mpdf->BMoutlines[$i]['parent'] = $nb;
			}
			if ($o['l'] <= $level and $i > 0) {
				// Set prev and next pointers
				$prev = $lru[$o['l']];
				$this->mpdf->BMoutlines[$prev]['next'] = $i;
				$this->mpdf->BMoutlines[$i]['prev'] = $prev;
			}
			$lru[$o['l']] = $i;
			$level = $o['l'];
		}


		// Outline items
		$n = $this->mpdf->n + 1;
		foreach ($this->mpdf->BMoutlines as $i => $o) {
			$this->writer->object();
			$this->writer->write('<</Title ' . $this->writer->utf16BigEndianTextString($o['t']));
			$this->writer->write('/Parent ' . ($n + $o['parent']) . ' 0 R');
			if (isset($o['prev'])) {
				$this->writer->write('/Prev ' . ($n + $o['prev']) . ' 0 R');
			}
			if (isset($o['next'])) {
				$this->writer->write('/Next ' . ($n + $o['next']) . ' 0 R');
			}
			if (isset($o['first'])) {
				$this->writer->write('/First ' . ($n + $o['first']) . ' 0 R');
			}
			if (isset($o['last'])) {
				$this->writer->write('/Last ' . ($n + $o['last']) . ' 0 R');
			}


			if (isset($this->mpdf->pageDim[$o['p']]['h'])) {
				$h = $this->mpdf->pageDim[$o['p']]['h'];
			} else {
				$h = 0;
			}

			$this->writer->write(sprintf('/Dest [%d 0 R /XYZ 0 %.3F null]', 1 + 2 * ($o['p']), ($h - $o['y']) * Mpdf::SCALE));
			if (isset($this->mpdf->bookmarkStyles) && isset($this->mpdf->bookmarkStyles[$o['l']])) {
				// font style
				$bms = $this->mpdf->bookmarkStyles[$o['l']]['style'];
				$style = 0;
				if (strpos($bms, 'B') !== false) {
					$style += 2;
				}
				if (strpos($bms, 'I') !== false) {
					$style += 1;
				}
				$this->writer->write(sprintf('/F %d', $style));
				// Colour
				$col = $this->mpdf->bookmarkStyles[$o['l']]['color'];
				if (isset($col) && is_array($col) && count($col) == 3) {
					$this->writer->write(sprintf('/C [%.3F %.3F %.3F]', ($col[0] / 255), ($col[1] / 255), ($col[2] / 255)));
				}
			}

			$this->writer->write('/Count 0>>');
			$this->writer->write('endobj');
		}
		// Outline root
		$this->writer->object();

		$this->mpdf->OutlineRoot = $this->mpdf->n;

		$this->writer->write('<</Type /BMoutlines /First ' . $n . ' 0 R');
		$this->writer->write('/Last ' . ($n + $lru[0]) . ' 0 R>>');
		$this->writer->write('endobj');
	}

}
