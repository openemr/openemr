<?php

namespace ESign;

/**
 * Signature class 
 * 
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

require_once $GLOBALS['srcdir'].'/ESign/SignatureIF.php';
require_once $GLOBALS['srcdir'].'/ESign/Utils/Verification.php';

class Signature implements SignatureIF
{    
    private $id; // id of the signature
    private $tid;
    private $table;
    private $isLock = null; // flag signifying whether the signable object is locked
    private $uid; // user id of the signer
    private $firstName; // first name of signer
    private $lastName; // last name of signer
    private $datetime; // date and time of the signature
    private $hash; // hash of the thing being signed on (SignableIF)
    private $signatureHash = null; // hash of data in this signature
    private $amendment = null; // note about the signature, if any

    private $_verification = null;
    
    public function __construct( $id, $tid, $table, $isLock, $uid, $firstName, $lastName, $datetime, $hash, $amendment = null, $signatureHash = null )
    {
        $this->id = $id;
        $this->tid = $tid;
        $this->table = $table;
        $this->isLock = $isLock;
        $this->uid = $uid;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->datetime = $datetime;
        $this->hash = $hash;
        $this->amendment = $amendment;
        $this->signatureHash = $signatureHash;
        
        $this->_verification = new Utils_Verification();
    }
    
    public function getClass()
    {
       $class = "";
       if ( $this->isLock() === true ) {
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
    
    public function getDatetime()
    {
        return $this->datetime;
    }
    
    public function isLock()
    {
        if ( $this->isLock > 0 ) {
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
        $data = array( $this->tid, $this->table, $this->uid, $this->isLock, $this->hash, $this->amendment );
        return $data;
    }
    
    public function verify()
    {
        return $this->_verification->verify( $this->getData(), $this->signatureHash );
    }
}
