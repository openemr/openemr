<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;


use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qdm\QdmBuilder;

class ExportCat3Service
{
    protected $calculator;
    protected $builder;
    protected $request;

    /**
     * ExportCat3Service constructor.
     * @param CqmCalculator $calculator
     * @param QdmBuilder $builder
     * @param QdmRequestInterface $request
     */
    public function __construct(CqmCalculator $calculator, QdmBuilder $builder, QdmRequestInterface $request)
    {
        $this->calculator = $calculator;
        $this->builder = $builder;
        $this->request = $request;
    }

    public function export($measure, $effectiveDate, $effectiveDateEnd)
    {
        $patientModels = $this->builder->build($this->request);
        $string = "";
        $results = $this->calculator->calculateMeasure($this->request, $measure, $effectiveDate, $effectiveDateEnd);
        $cat3 = new Cat3();
        $string = $cat3->renderXml();

        return $string;
    }

}
