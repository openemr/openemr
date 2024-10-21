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

 namespace OpenEMR\Modules\Dorn\models;

class CreateRouteFromPrimaryViewModel
{
    public $customerAccountNumber = "";
    public $npi = "";
    public $labGuid;
    public $labAccountNumber = "";
    public function __construct()
    {
    }

    public static function loadByPost($postData)
    {
        $model = new CreateRouteFromPrimaryViewModel();
        $model->npi = $postData["form_primaries"];
        $model->labGuid = $postData["form_labGuid"];
        $model->labAccountNumber = $postData["form_labAcctNumber"];
        return $model;
    }
}
