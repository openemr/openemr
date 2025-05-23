<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 namespace OpenEMR\Modules\Dorn\models;

class ReceiveResultsResponseModel
{
    public $labGuid;
    public $resultsGuid;
    public $resultsCount;
    public $log;
    public $isSuccess;
    public $isUnsolicited;
    public $orderNumber;
    public $message;
    public $resultsParseMsg;
    public $parseMsgArray;

    public function __construct()
    {
    }
}
