<?php

/**
 * Saves and retrieves from the database TeleHealth user objects.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Repository;

use Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\BaseService;
use Ramsey\Uuid\UuidFactory;

class TeleHealthUserRepository extends BaseService
{
    const TABLE_NAME = "comlink_telehealth_auth";

    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        $this->logger = new SystemLogger();
    }

    public function saveUser(TeleHealthUser $user)
    {
        $active = $user->getIsActive() ? 1 : 0;
        $binds = [$user->getUsername(), $user->getAuthToken(), $active, $user->getRegistrationCode()];

        if (empty($user->getId())) {
            $sql = "INSERT INTO " . self::TABLE_NAME . "(`date_updated`, `username`,`auth_token`"
                . ",`active`, `app_registration_code`, `user_id`,`patient_id`, `date_registered`) VALUES (NOW(),?,?,?,?,?,?,?)";
        } else {
            $sql = "UPDATE " . self::TABLE_NAME . " SET `date_updated`=NOW(),`username`=?,`auth_token`=?,`active`=?"
            . ",`app_registration_code`=?,`user_id`=?,`patient_id`=?"
            . " WHERE `id`=?";
        }

        // grab the user first
        if (empty($user->getUsername())) {
            throw new \InvalidArgumentException("username cannot be empty");
        }
        if (empty($user->getAuthToken())) {
            throw new \InvalidArgumentException("authToken cannot be empty");
        }

        if ($user->getIsPatient()) {
            $binds[] = null; // no user id
            $binds[] = $user->getDbRecordId(); // set patient id
        } else {
            $binds[] = $user->getDbRecordId();
            $binds[] = null;
        }

        if (!empty($user->getId())) {
            $binds[] = $user->getId();
        } else {
            if (!empty($user->getDateRegistered())) {
                $binds[] = $user->getDateRegistered()->format(DATE_ISO8601);
            } else {
                $binds[] = (new \DateTime())->format(DATE_ISO8601);
            }
        }

        return QueryUtils::sqlInsert($sql, $binds);
    }

    public function getUser($username): ?TeleHealthUser
    {
        $result = $this->search(['username' => $username]);
        if ($result->hasData()) {
            return $result->getData()[0];
        }
        return null;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        $dateFormat = "Y-m-d H:i:s";
        $user = new TeleHealthUser();
        $user->setId($row['id'])
            ->setUsername($row['username'])
            ->setAuthToken($row['auth_token'])
            ->setDbRecordId($row['patient_id'] ?? $row['user_id'])
            ->setIsPatient(isset($row['patient_id']))
            ->setIsActive($row['active'] == 1)
            ->setRegistrationCode($row['app_registration_code']);

        if (isset($row['date_registered'])) {
            $date = \DateTime::createFromFormat($dateFormat, $row['date_registered']);
            if ($date !== false) {
                $user->setDateRegistered($date);
            } else {
                $this->logger->errorLogCaller('failed to create date_registered', ['value' => $row['date_registered']]);
            }
        }
        if (isset($row['date_created'])) {
            $date = \DateTime::createFromFormat($dateFormat, $row['date_created']);
            if ($date !== false) {
                $user->setDateCreated($date);
            } else {
                $this->logger->errorLogCaller('failed to create date_created', ['value' => $row['date_created']]);
            }
        }
        if (isset($row['date_updated'])) {
            $date = \DateTime::createFromFormat($dateFormat, $row['date_updated']);
            if ($date !== false) {
                $user->setDateUpdated($date);
            } else {
                $this->logger->errorLogCaller('failed to create date_updated', ['value' => $row['date_updated']]);
            }
        }
        return $user;
    }


    /**
     * Since users can't see this password and they can't change this password w/o system user intervention we are just
     * going to generate a random uuid for the password for the user.  The algorithm can be changed in the future in
     * the event there is a compromise and we can use the registration API to change it.  Originally we wanted to use
     * the user's password, however, we have no easy way that OpenEMR gives us access to the password and talking with
     * the OpenEMR Administrators the hashing algorithm can be changed on the fly w/ OpenEMR so using the hash is not a
     * good idea either.  Since the business requirement is that users can't see their password we will generate the
     * password and use our standard encrypt method just in case the DB is taken.
     */
    public function createUniquePassword()
    {
        $factory = new UuidFactory();
        $uuidString = $factory->uuid4()->toString();
        $cryptoGen = new CryptoGen();
        // we could make this even stronger by using the API password for the encryption password...
        // but this is probably good enough
        return $cryptoGen->encryptStandard($uuidString);
    }

    public function decryptPassword($password)
    {
        $cryptoGen = new CryptoGen();
        return $cryptoGen->decryptStandard($password);
    }
}
