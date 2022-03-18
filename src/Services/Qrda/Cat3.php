<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;

use OpenEMR\Services\Qrda\Helpers\Cat3View;
use OpenEMR\Services\Qrda\Helpers\Date;
use OpenEMR\Services\Qrda\Helpers\PatientView;
use OpenEMR\Services\Qrda\Helpers\View;

class Cat3 extends \Mustache_Engine
{
    use Date;
    use View;
    use PatientView;
    use Cat3View;

    protected $templatePath =
        __DIR__ . DIRECTORY_SEPARATOR .
        'qrda-export' . DIRECTORY_SEPARATOR .
        'catIII';

    protected $template = 'qrda1_r5.mustache';
    protected $measures = [];
    protected $aggregate_results = [];
    protected $measure_result_hash = [];
    protected $provider;
    protected $performance_period_start;
    protected $performance_period_end;
    protected $submission_program;
    protected $ry2022_submission;

    public function __construct($aggregate_results = array(), $measures = array(), $options = array())
    {
        $this->aggregate_results = $aggregate_results;
        $this->measures = $measures;

        foreach ($this->measures as $measure) {
            $this->measure_result_hash[$measure['hqmf_id']] = [
                'population_sets' => $measure['population_sets'],
                'hqmf_id' => $measure['hqmf_id'],
                'hqmf_set_id' => $measure['hqmf_set_id'],
                'description' => $measure['description'],
                'measure_data' => [],
                'aggregate_count' => []
            ];
        }

        $this->provider = $options['provider'];
        $this->performance_period_start = $options['start_time'];
        $this->performance_period_end = $options['end_time'];
        $this->submission_program = $options['submission_program'];
        $this->ry2022_submission = $options['ry2022_submission'];
    }

    public function renderXml()
    {
        $xml = $this->render($this->template, $this); // we pass in ourselves as the context so mustache can see all of our methods, and helper methods
        return $xml;
    }

    protected function agg_results($measure_id, $cache_entries, $population_sets)
    {
        $aggregate_count = new AggregateCount($measure_id);
        foreach ($cache_entries as $cache_entry) {
            $aggregate_count->add_entry($cache_entry, $population_sets);
        }
        return $aggregate_count;
    }

    public function measure_results()
    {
        return json_encode(array_values($this->measure_result_hash));
    }

    public function cpcplus()
    {
        return $this->submission_program == 'CPCPLUS';
    }

    public function ry2022_submission()
    {
        return $this->ry2022_submission;
    }
}
