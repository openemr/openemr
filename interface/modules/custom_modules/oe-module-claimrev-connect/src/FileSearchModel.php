<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    namespace OpenEMR\Modules\ClaimRevConnector;

class FileSearchModel
{
    public $accountNumber = "";
    public int $fileStatus = 3;
    public ?string $ediType = "";
    public ?string $ediVersion = "";
    public ?string $payerNumber = "";
    public ?string $fileId = "";
    public $receivedDateStart;
    public $receivedDateEnd;

    public function __construct()
    {
    }
}
