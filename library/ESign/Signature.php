<?php

/**
 * Signature class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/SignatureIF.php';
require_once $GLOBALS['srcdir'] . '/ESign/Utils/Verification.php';

class Signature implements SignatureIF
{
    private $_verification = null;

    /**
     * @param mixed $id id of the signature
     * @param mixed $tid
     * @param mixed $table
     * @param mixed $isLock flag signifying whether the signable object is locked
     * @param mixed $uid user id of the signer
     * @param mixed $firstName first name of signer
     * @param mixed $lastName last name of signer
     * @param mixed $suffix suffix of signer
     * @param mixed $valedictory aka credential of signer
     * @param mixed $datetime date and time of the signature
     * @param mixed $hash hash of the thing being signed on (SignableIF)
     * @param mixed $amendment note about the signature, if any
     * @param mixed $signatureHash hash of data in this signature
     */
    public function __construct(
        private $id,
        private $tid,
        private $table,
        private $isLock,
        private $uid,
        private $firstName,
        private $lastName,
        private $suffix,
        private $valedictory,
        private $datetime,
        private $hash,
        private $amendment = null,
        private $signatureHash = null
    ) {
        $this->_verification = new Utils_Verification();
    }

    public function getClass()
    {
        $class = "";
        if ($this->isLock() === true) {
            $class .= " locked";
        }

        return $class;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getValedictory()
    {
        return $this->valedictory;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function isLock()
    {
        if ($this->isLock > 0) {
            return true;
        }

        return false;
    }

    public function getAmendment()
    {
        return $this->amendment;
    }

    public function getData()
    {
        $data = [ $this->tid, $this->table, $this->uid, $this->isLock, $this->hash, $this->amendment ];
        return $data;
    }

    public function verify()
    {
        return $this->_verification->verify($this->getData(), $this->signatureHash);
    }
}
