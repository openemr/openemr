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
        $results = $this->calculator->calculateMeasure($this->request, $measure, $effectiveDate, $effectiveDateEnd);
        $options = [
            'start_time' => $effectiveDate,
            'end_time' => $effectiveDateEnd
            /*
             * These are options: TODO what is required?
            $options['provider'];
            $options['start_time'];
            $options['end_time'];
            $options['submission_program'];
            $options['ry2022_submission'];
            */
        ];
        $cat3 = new Cat3($results, $measure, $options);
        $string = $cat3->renderXml();

        return $string;
    }

}
