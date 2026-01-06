<?php

namespace OpenEMR\Services\Qdm;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7;

class MeasureService
{
    public function __construct()
    {
    }

    /**
     * Get measure sources with dynamic path building
     * The 'openemr/oe-cqm-parsers' path is built at runtime using the global cqm_performance_period
     */
    public static function fetchMeasureSourceOptions()
    {
        $reporting_year = $GLOBALS['cqm_performance_period'] ?? '2023';
        $reporting_year .= '_reporting_period';

        return [
            'openemr/oe-cqm-parsers' => "/ccdaservice/node_modules/oe-cqm-parsers/$reporting_year/json_measures",
            'contrib' => '/contrib/ecqm/EP-EC-eCQM-2020-05'
        ];
    }

    public static function fetchMeasureOptions()
    {
        $measureSources = self::fetchMeasureSourceOptions();
        $measureSourcePath = $measureSources['openemr/oe-cqm-parsers'];
        $measurePath = $GLOBALS['fileroot'] . $measureSourcePath;
        $options = [];

        foreach (glob("$measurePath/*", GLOB_ONLYDIR) as $measureDirectory) {
            $options[basename($measureDirectory)] = $measureDirectory;
        }

        return $options;
    }

    public static function fetchMeasuresPath()
    {
        $measureSources = self::fetchMeasureSourceOptions();
        $measureSourcePath = $measureSources['openemr/oe-cqm-parsers'];
        return $GLOBALS['fileroot'] . $measureSourcePath;
    }

    /**
     * Given a full path to the measure directory, get the paths to the
     * measure file, and the value sets file.
     *
     * @param  $measurePath
     * @return string[]
     */
    public static function fetchMeasureFiles($measurePath)
    {
        return [
            'measure' => $measurePath . '/' . basename((string) $measurePath) . '.json',
            'valueSets' => $measurePath . '/value_sets.json'
        ];
    }

    public static function fetchAllMeasuresArray($measures = [])
    {
        $measureObjects = [];
        foreach ($measures as $measure) {
            $measureObjects[] = self::fetchMeasureJson($measure);
        }
        return $measureObjects;
    }

    public static function fetchMeasureJson($measure, $assoc = true)
    {
        $measureFiles = MeasureService::fetchMeasureFiles($measure);
        $json = file_get_contents($measureFiles['measure']);
        if ($assoc) {
            return json_decode($json, true);
        }
        return $json;
    }

    /**
     * Get the current reporting year from global configuration
     *
     * @return string
     */
    public static function getCurrentReportingYear()
    {
        return $GLOBALS['cqm_performance_period'] ?? '2023';
    }

    /**
     * Validate that the reporting year has measure files available
     *
     * @param string $year
     * @return bool
     */
    public static function validateReportingYear($year = null)
    {
        if ($year === null) {
            $year = self::getCurrentReportingYear();
        }

        $tempGlobal = $GLOBALS['cqm_performance_period'];
        $GLOBALS['cqm_performance_period'] = $year;

        $measureSources = self::fetchMeasureSourceOptions();
        $measurePath = $GLOBALS['fileroot'] . $measureSources['openemr/oe-cqm-parsers'];

        // Restore original global
        $GLOBALS['cqm_performance_period'] = $tempGlobal;

        return is_dir($measurePath) && count(glob("$measurePath/*", GLOB_ONLYDIR)) > 0;
    }
}
