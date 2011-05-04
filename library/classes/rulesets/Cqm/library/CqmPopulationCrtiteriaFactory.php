<?php
interface CqmPopulationCrtiteriaFactory extends RsFilterIF
{
    public function createInitialPatientPopulation();
    public function createDenominator();
    public function createNumerators();
    public function createExclusion();
}
