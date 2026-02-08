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

class CustomerPrimaryInfoView
{
    public $accountNumber = "";
    public $npi = "";
    public $primaryId;
    public $primaryName = "";
    public $primaryPhone = "";
    public $primaryEmail = "";
    public $primaryAddress1 = "";
    public $primaryAddress2 = "";
    public $primaryCity = "";
    public $primaryState = "";
    public $primaryZipCode = "";

    public function __construct()
    {
    }

    public static function loadByPost($postData)
    {
        $model = new CustomerPrimaryInfoView();
        $model->primaryId = $postData["form_primaryId"];
        $model->accountNumber = $postData["form_account_number"];
        $model->npi = $postData["form_npi"];
        $model->primaryName = $postData["form_name"];
        $model->primaryPhone = $postData["form_phone"];
        $model->primaryEmail = $postData["form_email"];
        $model->primaryAddress1 = $postData["form_address1"];
        $model->primaryAddress2 = $postData["form_address2"];
        $model->primaryCity = $postData["form_city"];
        $model->primaryState = $postData["form_state"];
        $model->primaryZipCode = $postData["form_zip"];
        if ($model->primaryId == "") {
            $model->primaryId = null;
        }


        return $model;
    }
}
