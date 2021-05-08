<?php

/**
 * FhirURLResolver is a simple class that takes in a FHIR base server url and can be used to extract pieces of the URL
 * that are needed within the FHIR system such as a resource's relative URI
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

class FhirUrlResolver
{
    private $fhirBaseURL;

    private $baseUrlLength;

    public function __construct($baseURL)
    {
        $this->fhirBaseURL = $baseURL;
        $this->baseUrlLength = strlen($baseURL);
    }

    public function getRelativeUrl($url): ?string
    {
        // extracts everything but the resource/:id portion of a URL from the base url.
        // if the URI passed in does not match the base fhir URI we do nothing with it
        if (strstr($url, $this->fhirBaseURL) === false) {
            return null;
        } else {
            // grab everything from our string onwards...
            $relativeUrl = substr($url, $this->baseUrlLength - 1);
            return $relativeUrl !== false ? $relativeUrl : null;
        }
    }
}
