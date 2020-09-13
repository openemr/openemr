<?php

/**
 * ProductRegistrationService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia <victor.kofia@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

require_once($GLOBALS['fileroot'] . "/interface/product_registration/exceptions/generic_product_registration_exception.php");

class ProductRegistrationService
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
    }

    public function getProductStatus()
    {
        $row = sqlQuery("SELECT * FROM `product_registration`");

        if (!empty($row)) {
            $email = $row['email'];
            $optOut = $row['opt_out'];
        }

        if (empty($row)) {
            $row['statusAsString'] = 'UNREGISTERED';
        } elseif (!empty($email)) {
            $row['statusAsString'] = 'REGISTERED';
        } elseif (!empty($optOut) && $optOut == 1) {
            $row['statusAsString'] = 'OPT_OUT';
        }

        return $row;
    }

    public function registerProduct($email)
    {
        if (!$email || $email == 'false') {
            $this->optOutStrategy();
            return null;
        } else {
            return $this->optInStrategy($email);
        }
    }

    private function optInStrategy($email)
    {
        // build the information array
        $info = ['email' => $email, 'version' => $GLOBALS['openemr_version']];
        if (!empty(getenv('OPENEMR_DOCKER_ENV_TAG', true))) {
            // this will add standard package information if it exists
            $info['distribution'] = getenv('OPENEMR_DOCKER_ENV_TAG', true);
        }

        $curl = curl_init('https://reg.open-emr.org/api/registration');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($info));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $responseBodyRaw = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        switch ($responseCode) {
            case 201:
                sqlStatement("INSERT INTO `product_registration` (`email`, `opt_out`) VALUES (?, 0)", [$email]);
                return $email;
                break;
            default:
                throw new \GenericProductRegistrationException(xl("Server error: try again later"));
        }
    }

    // void... don't bother checking for success/failure.
    private function optOutStrategy()
    {
        sqlStatement("INSERT INTO `product_registration` (`email`, `opt_out`) VALUES (null, 1)");
    }
}
