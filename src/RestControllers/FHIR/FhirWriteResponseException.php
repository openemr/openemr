<?php

/**
 * Carries an unsuccessful FHIR write response out of a transaction callback.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers\FHIR;

use Symfony\Component\HttpFoundation\Response;

final class FhirWriteResponseException extends \RuntimeException
{
    public function __construct(private readonly Response $response)
    {
        parent::__construct('FHIR write returned an unsuccessful response');
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
