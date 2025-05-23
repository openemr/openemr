<?php

namespace OpenEMR\Services\Qdm;

class PopulationSet
{
    public function __construct($jsonPopulationSet)
    {
        // sub class elements we will leave as arrays
        // TODO: do we need to populate these QDM models?
        $this->populations = $jsonPopulationSet['populations'] ?? [];
        $this->stratifications = $jsonPopulationSet['stratifications'] ?? [];
        $this->supplemental_data_elements = $jsonPopulationSet['supplemental_data_elements'] ?? [];
        $this->observations = $jsonPopulationSet['observations'] ?? [];


        $this->population_set_id = $jsonPopulationSet['population_set_id'] ?? '';
        $this->title = $jsonPopulationSet['title'] ?? '';
    }
    // for now we treat this as a deserialized JSON array
    // @see projecttacoma/cqm-models app/models/cqm/population_set.rb
    //
    public $populations;

    /**
     * @var array
     */
    public $stratifications;

    /**
     * @var array
     */
    public $supplemental_data_elements;

    /**
     * @var array
     */
    public $observations;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $population_set_id;
}
