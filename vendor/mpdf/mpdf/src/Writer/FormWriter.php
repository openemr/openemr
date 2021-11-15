<?php

namespace Mpdf\Writer;

use Mpdf\Strict;

use Mpdf\Mpdf;

final class FormWriter
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

	public function writeFormObjects() // _putformobjects
	{
		foreach ($this->mpdf->formobjects as $file => $info) {

			$this->writer->object();

			$this->mpdf->formobjects[$file]['n'] = $this->mpdf->n;

			$this->writer->write('<</Type /XObject');
			$this->writer->write('/Subtype /Form');
			$this->writer->write('/Group ' . ($this->mpdf->n + 1) . ' 0 R');
			$this->writer->write('/BBox [' . $info['x'] . ' ' . $info['y'] . ' ' . ($info['w'] + $info['x']) . ' ' . ($info['h'] + $info['y']) . ']');

			if ($this->mpdf->compress) {
				$this->writer->write('/Filter /FlateDecode');
			}

			$data = $this->mpdf->compress ? gzcompress($info['data']) : $info['data'];
			$this->writer->write('/Length ' . strlen($data) . '>>');
			$this->writer->stream($data);

			unset($this->mpdf->formobjects[$file]['data']);

			$this->writer->write('endobj');

			// Required for SVG transparency (opacity) to work
			$this->writer->object();
			$this->writer->write('<</Type /Group');
			$this->writer->write('/S /Transparency');
			$this->writer->write('>>');
			$this->writer->write('endobj');
		}
	}
}
