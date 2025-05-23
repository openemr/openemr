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

class UploadEdiFileContentModel
{
    public $AccountNumber = "";
    public $EdiFileContent = "";
    public $FileName = "";


    public function __construct($acct, $ediFileContent, $fileName)
    {
        $this->AccountNumber = $acct;
        $this->EdiFileContent = $ediFileContent;
        $this->FileName = $fileName;
    }
}
