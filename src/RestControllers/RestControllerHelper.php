<?php

/**
 * RestControllerHelper
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

class RestControllerHelper
{
    /**
     * Configures the HTTP status code and payload returned within a response.
     */
    public static function responseHandler($serviceResult, $customRespPayload, $idealStatusCode)
    {
        if ($serviceResult) {
            http_response_code($idealStatusCode);

            if ($customRespPayload) {
                return $customRespPayload;
            }

            return $serviceResult;
        }

        // if no result is present return a 404 with a null response
        http_response_code(404);
        return null;
    }

    public static function validationHandler($validationResult)
    {
        if (property_exists($validationResult, 'isValid') && !$validationResult->isValid()) {
            http_response_code(400);
            return $validationResult->getMessages();
        }
    }
}
