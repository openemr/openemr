<?php

namespace OpenEMR\Services\Qdm;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7;

class MeasureService
{
    public static $measure_sources = [
        'openemr/oe-cqm-parsers' => '/ccdaservice/node_modules/oe-cqm-parsers/json_measures',
        'contrib' => '/contrib/ecqm/EP-EC-eCQM-2020-05'
    ];

    public static function fetchMeasureSourceOptions()
    {
        return self::$measure_sources;
    }

    public static function fetchMeasureOptions()
    {
        $s = 'openemr/oe-cqm-parsers';
        $measureSourcePath = self::$measure_sources[$s];
        $measurePath = $GLOBALS['fileroot'] . $measureSourcePath;
        $options = [];
        foreach (glob("$measurePath/*", GLOB_ONLYDIR) as $measureDirectory) {
            $options[basename($measureDirectory)] = $measureDirectory;
        }

        return $options;
    }

    public static function fetchMeasuresPath()
    {
        $measureSourcePath = self::$measure_sources['openemr/oe-cqm-parsers'];
        return $GLOBALS['fileroot'] . $measureSourcePath;
    }

    /**
     * Given full path to the measure directory, get the paths to the
     * measure file, and the value sets file.
     *
     * @param  $measurePath
     * @return string[]
     */
    public static function fetchMeasureFiles($measurePath)
    {
        return [
            'measure' => $measurePath . '/' . basename($measurePath) . '.json',
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
}
