<?php

/**
 * FHIRResources service class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;

require_once(__DIR__ . '/../../../_rest_config.php');

class FhirResourcesService
{
    public function createBundle($resource = '', $resource_array = [], $encode = true)
    {
        $bundleUrl = $GLOBALS['site_addr_oath'] . ($_SERVER['REDIRECT_URL'] ?? '');
        $nowDate = date("Y-m-d\TH:i:s");
        $meta = array('lastUpdated' => $nowDate);
        $bundleLink = new FHIRBundleLink(array('relation' => 'self', 'url' => $bundleUrl));
        // set bundle type default to collection so may include different
        // resource types. at least I hope thats how it works....
        // For now commenting Identifier assignment to pass the resource validation
        // In future once the identifier system is in place. This needs to be fixed
        // https://www.hl7.org/fhir/bundle-definitions.html#Bundle.identifier -Vishnu
        $bundleInit = array(
            // 'identifier' => $resource . "bundle",
            'type' => 'collection',
            'total' => count($resource_array),
            'meta' => $meta);
        $bundle = new FHIRBundle($bundleInit);
        $bundle->addLink($bundleLink);
        foreach ($resource_array as $addResource) {
            $bundle->addEntry($addResource);
        }

        if ($encode) {
            return json_encode($bundle);
        }

        return $bundle;
    }

    public function parseResource($rjson = '', $scheme = 'json')
    {
        $parser = new PHPFHIRResponseParser(false);
        if ($scheme == 'json') {
            $class_object = $parser->parse($rjson);
        } else {
            // @todo xml- not sure yet.
        }
        return $class_object; // feed to resource class or use as is object
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
    }
}
