<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
<<<<<<< HEAD
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
=======
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
>>>>>>> d11e3347b (modules setup and UI changes)
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 namespace OpenEMR\Modules\Dorn\models;

class CreateRouteFromPrimaryViewModel
{
<<<<<<< HEAD
    public $customerAccountNumber;
    public $npi = "";
    public $labGuid;
    public $labAccountNumber;
    public mixed $clientSiteId;
    public mixed $eulaVersion;
    public bool $eulaAcceptance;
    public mixed $eulaAccepterFullName;
    public mixed $eulaAcceptanceDateTimeUtc;

=======
    public $customerAccountNumber = "";
    public $npi = "";
    public $labGuid;
    public $labAccountNumber = "";
>>>>>>> d11e3347b (modules setup and UI changes)
    public function __construct()
    {
    }

    public static function loadByPost($postData)
    {
        $model = new CreateRouteFromPrimaryViewModel();
<<<<<<< HEAD
        $model->customerAccountNumber = $postData["form_customerAcctNumber"] ?? "";
        $model->npi = $postData["form_primaries"] ?? "";
        $model->labGuid = $postData["form_labGuid"] ?? "";
        $model->labAccountNumber = $postData["form_labAcctNumber"] ?? "";
        $model->clientSiteId = $postData["form_clientSiteId"] ?? "";
        $model->eulaVersion = $postData["form_eulaVersion"] ?? "";
        $model->eulaAccepterFullName = $postData["form_eulaAccepterFullName"] ?? "";
        $model->eulaAcceptanceDateTimeUtc = $postData["form_eulaAcceptanceDateTimeUtc"] ?? "";
        $model->eulaAcceptance = isset($postData["form_eulaAcceptance"]) && (bool)$postData["form_eulaAcceptance"];

=======
        $model->npi = $postData["form_primaries"];
        $model->labGuid = $postData["form_labGuid"];
        $model->labAccountNumber = $postData["form_labAcctNumber"];
>>>>>>> d11e3347b (modules setup and UI changes)
        return $model;
    }
}
