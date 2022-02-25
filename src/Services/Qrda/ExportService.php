<?php
namespace OpenEMR\Services\Qrda;

use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qdm\QdmBuilder;

class ExportService
{
    protected $builder;
    protected $request;

    public function __construct(QdmBuilder $builder, QdmRequestInterface $request)
    {
        $this->builder = $builder;
        $this->request = $request;
    }

    public function export()
    {
        $patientModels = $this->builder->build($this->request);

        foreach ($patientModels as $patient) {
            $cat1 = new Cat1($patient);
            echo $cat1->render();
        }

    }
}
