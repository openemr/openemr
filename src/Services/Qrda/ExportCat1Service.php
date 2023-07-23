<?php

namespace OpenEMR\Services\Qrda;

use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;

class ExportCat1Service
{
    protected $builder;
    protected $request;

    public function __construct(QdmBuilder $builder, QdmRequestInterface $request)
    {
        $this->builder = $builder;
        $this->request = $request;
    }

    /**
     * @param array $measures
     * @param array $options
     * @return string
     * @throws \Exception
     *
     * Takes an array of measures (paths to measures) and an array of options
     * to produce a string representing a QRDA Cat I XML document
     *
     * options
     *  - performance_period_start
     *  - performance_period_end
     *  - provider
     *  - submission_program
     */
    public function export($measures = [], $options = [])
    {
        $measure_arr = MeasureService::fetchAllMeasuresArray($measures);
        $patientModels = $this->builder->build($this->request);
        $string = "";
        foreach ($patientModels as $patient) {
            $cat1 = new Cat1($patient, $measure_arr, $options);
            $string .= $cat1->renderCat1Xml();
        }

        return $string;
    }
}
