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

use OpenEMR\Services\EncounterService;
use OpenEMR\RestControllers\RestControllerHelper;

class RestControllerHelper
{
    public static function responseHandler($serviceResult, $customRespPayload, $idealStatusCode)
    {
        if ($serviceResult) {
            http_response_code($idealStatusCode);

            if ($customRespPayload) {
                return $customRespPayload;
            }

            return $serviceResult;
        }

        http_response_code(400);
    }

    public static function validationHandler($validationResult)
    {
        if (!$validationResult->isValid()) {
            http_response_code(400);
            return $validationResult->getMessages();
        }
    }
}
