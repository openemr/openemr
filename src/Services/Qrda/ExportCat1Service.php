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

    public function export($measures = [])
    {
        $measure_arr = MeasureService::fetchAllMeasuresArray($measures);
        $patientModels = $this->builder->build($this->request);
        $string = "";
        foreach ($patientModels as $patient) {
            $cat1 = new Cat1($patient, $measure_arr);
            $string .= $cat1->renderCat1Xml();
        }

        return $string;
    }
}
