<?php
/**
 * ProductRegistrationService
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

require_once($GLOBALS['fileroot'] . "/interface/main/exceptions/invalid_email_exception.php");
require_once($GLOBALS['fileroot'] . "/interface/product_registration/exceptions/generic_product_registration_exception.php");
require_once($GLOBALS['fileroot'] . "/interface/product_registration/exceptions/duplicate_registration_exception.php");

class ProductRegistrationService {
    public function __construct() {}

    public function getProductStatus() {
        $row = sqlQuery('SELECT * FROM product_registration LIMIT 1');

        $payload = new stdClass();

        if (empty($row)) {
            $payload->status = 'UNREGISTERED';
        } else if (!(empty($row['registration_id']))) {
            $payload->status = 'REGISTERED';
            $payload->email = $row['email'];
            $payload->registration_id = $row['registration_id'];
        } else if (!(empty($row['opt_out'])) && $row['opt_out'] == true) {
            $payload->status = 'OPT_OUT';
        }

        return $payload;
    }

    public function registerProduct($email) {
        if (!$email || $email == 'false') {
            $this->optOutStrategy();
            return null;
        } else {
            return $this->optInStrategy($email);
        }
    }

    private function optInStrategy($email) {
        $curl = curl_init('https://reg.open-emr.org/api/registration');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array('email' => $email)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $responseBodyRaw = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        switch ($responseCode) {
            case 201:
                $responseBodyParsed = json_decode($responseBodyRaw);
                sqlStatement('INSERT INTO product_registration (registration_id, email, opt_out) VALUES (?, ?, ?)', array(
                    $responseBodyParsed->productId, $email, false)
                );

                return $responseBodyParsed->productId;
                break;
            case 400:
                throw new InvalidEmailException($email . ' ' . xl("is not a valid email address"));
                break;
            case 409:
                throw new DuplicateRegistrationException(xl("Already registered"));
                break;
            default:
                throw new GenericProductRegistrationException(xl("Server error: try again later"));
        }
    }

    // void... don't bother checking for success/failure.
    private function optOutStrategy() {
        sqlStatement('INSERT INTO product_registration (registration_id, email, opt_out) VALUES (?, ?, ?)',
                     array('', null, true));
    }
}
