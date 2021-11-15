<?php

namespace Mpdf\Writer;

use Mpdf\Strict;
use Mpdf\Mpdf;

final class ImageWriter
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

	public function writeImages()
	{
		$filter = $this->mpdf->compress ? '/Filter /FlateDecode ' : '';

		foreach ($this->mpdf->images as $file => $info) {

			$this->writer->object();

			$this->mpdf->images[$file]['n'] = $this->mpdf->n;

			$this->writer->write('<</Type /XObject');
			$this->writer->write('/Subtype /Image');
			$this->writer->write('/Width ' . $info['w']);
			$this->writer->write('/Height ' . $info['h']);

			if (isset($info['interpolation']) && $info['interpolation']) {
				$this->writer->write('/Interpolate true'); // mPDF 6 - image interpolation shall be performed by a conforming reader
			}

			if (isset($info['masked'])) {
				$this->writer->write('/SMask ' . ($this->mpdf->n - 1) . ' 0 R');
			}

			// set color space
			$icc = false;
			if (isset($info['icc']) && ( $info['icc'] !== false)) {
				// ICC Colour Space
				$icc = true;
				$this->writer->write('/ColorSpace [/ICCBased ' . ($this->mpdf->n + 1) . ' 0 R]');
			} elseif ($info['cs'] === 'Indexed') {
				if ($this->mpdf->PDFX || ($this->mpdf->PDFA && $this->mpdf->restrictColorSpace === 3)) {
					throw new \Mpdf\MpdfException('PDFA1-b and PDFX/1-a files do not permit using mixed colour space (' . $file . ').');
				}
				$this->writer->write('/ColorSpace [/Indexed /DeviceRGB ' . (strlen($info['pal']) / 3 - 1) . ' ' . ($this->mpdf->n + 1) . ' 0 R]');
			} else {
				$this->writer->write('/ColorSpace /' . $info['cs']);
				if ($info['cs'] === 'DeviceCMYK') {
					if ($this->mpdf->PDFA && $this->mpdf->restrictColorSpace !== 3) {
						throw new \Mpdf\MpdfException('PDFA1-b does not permit Images using mixed colour space (' . $file . ').');
					}
					if ($info['type'] === 'jpg') {
						$this->writer->write('/Decode [1 0 1 0 1 0 1 0]');
					}
				} elseif (($this->mpdf->PDFX || ($this->mpdf->PDFA && $this->mpdf->restrictColorSpace === 3)) && $info['cs'] === 'DeviceRGB') {
					throw new \Mpdf\MpdfException('PDFA1-b and PDFX/1-a files do not permit using mixed colour space (' . $file . ').');
				}
			}

			$this->writer->write('/BitsPerComponent ' . $info['bpc']);

			if (isset($info['f']) && $info['f']) {
				$this->writer->write('/Filter /' . $info['f']);
			}

			if (isset($info['parms'])) {
				$this->writer->write($info['parms']);
			}

			if (isset($info['trns']) && is_array($info['trns'])) {
				$trns = '';
				$maskCount = count($info['trns']);
				for ($i = 0; $i < $maskCount; $i++) {
					$trns .= $info['trns'][$i] . ' ' . $info['trns'][$i] . ' ';
				}
				$this->writer->write('/Mask [' . $trns . ']');
			}

			$this->writer->write('/Length ' . strlen($info['data']) . '>>');
			$this->writer->stream($info['data']);

			unset($this->mpdf->images[$file]['data']);

			$this->writer->write('endobj');

			if ($icc) { // ICC colour profile
				$this->writer->object();
				$icc = $this->mpdf->compress ? gzcompress($info['icc']) : $info['icc'];
				$this->writer->write('<</N ' . $info['ch'] . ' ' . $filter . '/Length ' . strlen($icc) . '>>');
				$this->writer->stream($icc);
				$this->writer->write('endobj');
			} elseif ($info['cs'] === 'Indexed') { // Palette
				$this->writer->object();
				$pal = $this->mpdf->compress ? gzcompress($info['pal']) : $info['pal'];
				$this->writer->write('<<' . $filter . '/Length ' . strlen($pal) . '>>');
				$this->writer->stream($pal);
				$this->writer->write('endobj');
			}
		}
	}

}
