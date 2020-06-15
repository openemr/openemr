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

use OpenEMR\Common\Database\Connector;
use OpenEMR\Common\Logging\Logger;
use OpenEMR\Entities\ProductRegistration;

require_once($GLOBALS['fileroot'] . "/interface/product_registration/exceptions/generic_product_registration_exception.php");

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
        $optOut = '';

        if ($row !== null) {
            $email = $row->getEmail();
            $optOut = $row->getOptOut();
        }

        if (empty($row)) {
            $row = new ProductRegistration();
            $row->setStatusAsString('UNREGISTERED');
        } elseif (!empty($email)) {
            $row->setStatusAsString('REGISTERED');
        } elseif (!empty($optOut) && $optOut == true) {
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
        // build the information array
        $info = ['email' => $email, 'date' => date('Y-m-d H:i:s'), 'version' => $GLOBALS['openemr_version']];
        if (!empty(getenv('OPENEMR_DOCKER_ENV_TAG', true))) {
            // this will add standard package information if it exists
            $info['distribution'] = getenv('OPENEMR_DOCKER_ENV_TAG', true);
        }

        $this->logger->debug('Attempting to register product with email ' . $email);
        $curl = curl_init('https://reg.open-emr.org/api/registration');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($info));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $responseBodyRaw = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $this->logger->debug('Raw response from remote server: ' . $responseBodyRaw);
        switch ($responseCode) {
            case 201:
                $entry = new ProductRegistration();
                $entry->setEmail($email);
                $entry->setOptOut(false);

                $newEmail = $this->repository->save($entry);
                $this->logger->debug('Successfully registered product ' . $newEmail);

                return $newEmail;
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
        $entry->setEmail(null);
        $entry->setOptOut(true);

        $this->repository->save($entry);
    }
}
