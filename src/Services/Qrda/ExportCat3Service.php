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
use OpenEMR\Services\Qdm\MeasureService;

class ExportCat3Service
{
    protected $calculator;
    protected $request;
    protected $measures;

    /**
     * ExportCat3Service constructor.
     * @param CqmCalculator $calculator
     * @param QdmRequestInterface $request
     */
    public function __construct(CqmCalculator $calculator, QdmRequestInterface $request)
    {
        $this->calculator = $calculator;
        $this->request = $request;
    }

    public function export($measures, $effectiveDate, $effectiveDateEnd)
    {
        $this->measures = MeasureService::fetchAllMeasuresArray($measures);
        $results = [];
        foreach ($measures as $measure) {
            $measure_arr = MeasureService::fetchMeasureJson($measure);
            $result = $this->calculator->calculateMeasure($this->request, $measure, $effectiveDate, $effectiveDateEnd);
            $results[$measure_arr['hqmf_id']] = $result;
        }

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
        $cat3 = new Cat3($results, $this->measures, $options);
        $string = $cat3->renderXml();

        return $string;
    }

}
