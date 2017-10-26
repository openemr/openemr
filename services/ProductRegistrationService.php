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
 * @author  Victor Kofia <victor.kofia@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\Connector;
use OpenEMR\Common\Logging\Logger;
use OpenEMR\Entities\ProductRegistration;

require_once($GLOBALS['fileroot'] . "/interface/main/exceptions/invalid_email_exception.php");
require_once($GLOBALS['fileroot'] . "/interface/product_registration/exceptions/generic_product_registration_exception.php");
require_once($GLOBALS['fileroot'] . "/interface/product_registration/exceptions/duplicate_registration_exception.php");

class ProductRegistrationService
{
    /**
     * Logger used primarily for logging events that are of interest to
     * developers.
     */
    private $logger;

    /**
     * The product registration repository to be used for db CRUD operations.
     */
    private $repository;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger("\OpenEMR\Services\ProductRegistrationService");
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository('\OpenEMR\Entities\ProductRegistration');
    }

    public function getProductStatus()
    {
        $this->logger->debug('Getting current product registration status');
        $row = $this->repository->findFirst();

        // Unboxing these here to avoid PHP 5.4 "Can't use method
        // return value in write context" error.
        $id = '';
        $optOut = '';

        if ($row !== null) {
            $id = $row->getRegistrationId();
            $optOut = $row->getOptOut();
        }

        if (empty($row)) {
            $row = new ProductRegistration();
            $row->setStatusAsString('UNREGISTERED');
        } else if ($id !== 'null') {
            $row->setStatusAsString('REGISTERED');
        } else if (!empty($optOut) && $optOut == true) {
            $row->setStatusAsString('OPT_OUT');
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
        $this->logger->debug('Attempting to register product with email ' . $email);
        $curl = curl_init('https://reg.open-emr.org/api/registration');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array('email' => $email)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $responseBodyRaw = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $this->logger->debug('Raw response from remote server: ' . $responseBodyRaw);
        switch ($responseCode) {
            case 201:
                $responseBodyParsed = json_decode($responseBodyRaw);

                $entry = new ProductRegistration();
                $entry->setRegistrationId($responseBodyParsed->productId);
                $entry->setEmail($email);
                $entry->setOptOut(false);

                $newId = $this->repository->save($entry);
                $this->logger->debug('Successfully registered product ' . $newId);

                return $newId;
                break;
            case 400:
                throw new \InvalidEmailException($email . ' ' . xl("is not a valid email address"));
                break;
            case 409:
                throw new \DuplicateRegistrationException(xl("Already registered"));
                break;
            default:
                throw new \GenericProductRegistrationException(xl("Server error: try again later"));
        }
    }

    // void... don't bother checking for success/failure.
    private function optOutStrategy()
    {
        $this->logger->debug('Attempting to opt out of product registration');
        $entry = new ProductRegistration();
        $entry->setRegistrationId('null');
        $entry->setEmail(null);
        $entry->setOptOut(true);

        $this->repository->save($entry);
    }
}
