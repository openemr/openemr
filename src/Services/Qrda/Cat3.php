<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda;

use OpenEMR\Services\Qdm\IndividualResult;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qrda\Helpers\Cat3View;
use OpenEMR\Services\Qrda\Helpers\Date;
use OpenEMR\Services\Qrda\Helpers\PatientView;
use OpenEMR\Services\Qrda\Helpers\View;
use Ramsey\Uuid\Rfc4122\UuidV4;

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

    // master branch qrda-reports uses this updated mustache template
    //    protected $template = 'qrda3.mustache';

    // version 3.1.8 of qrda-reports uses this template which is what the latest Cypress version uses.
    protected $template = 'qrda3_r21.mustache';
    protected $measures = [];
    protected $aggregate_results = [];
    protected $measure_result_hash = [];
    protected $provider;
    protected $submission_program;
    protected $ry2022_submission;
    protected $_qrda_guid; // for extension in root template

    public function __construct($aggregate_results = array(), $measures = array(), $options = array())
    {
        parent::__construct(
            array(
                'entity_flags' => ENT_QUOTES,
                'loader' => new \Mustache_Loader_FilesystemLoader($this->templatePath),
            )
        );

        $this->_qrda_guid = UuidV4::uuid4();

        $this->aggregate_results = $aggregate_results;
        $this->measures = $measures;

        // Initialize our measure results data structure
        foreach ($this->measures as $measure) {
            if (!$measure instanceof Measure) {
                throw new \InvalidArgumentException("Passed in measure must be of type " . Measure::class);
            }
            $this->measure_result_hash[$measure->hqmf_id] = [
                'population_sets' => $measure->population_sets,
                'hqmf_id' => $measure->hqmf_id,
                'hqmf_set_id' => $measure->hqmf_set_id,
                'description' => $measure->description,
                'measure_data' => [],
                'aggregate_count' => []
            ];
        }

        foreach ($this->aggregate_results as $hqmf_id => $measure_aggregate_result) {
            foreach ($measure_aggregate_result as $aggregate_result) {
                $this->measure_result_hash[$hqmf_id]['measure_data'][] = $aggregate_result;
            }
        }

        foreach ($this->measure_result_hash as $key => $hash) {
            // TODO $measure_result_hash measure_data entries don't have required indexes for agg_results()
            // There should be an index 'pop_set_hash' but I'm not sure what it needs
            $this->measure_result_hash[$key]['aggregate_count'] = $this->agg_results($key, $hash['measure_data'], $hash['population_sets']);
        }

        $this->provider = $options['provider'];
        // Start and end time properties are in Date helper
        $this->_performance_period_start = $options['start_time'];
        $this->_performance_period_end = $options['end_time'];
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
        $measure_results = array_values($this->measure_result_hash);
        // we convert all QDM and other objects to serializable json and then decode them
        return json_decode(json_encode($measure_results));
    }

    public function cpcplus()
    {
        return $this->submission_program == 'CPCPLUS';
        /**
        def cpcplus?
         *
        @submission_program == 'CPCPLUS'
        end
         */
    }

    public function is_ry2022_submission()
    {
        return $this->ry2022_submission;
        /**
        def ry2022_submission?
         *
        @ry2022_submission
        end
         */
    }
}
