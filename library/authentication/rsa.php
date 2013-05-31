<?php
/**
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
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
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */



/**
 * This class is used to create and store RSA key pair.
 */
class rsa_key_manager
{
    static $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA
    );

    protected $pubKey;
    protected $privKey;


    /**
     * Generate a NEW key pair and store the details in the database
     * 
     * The rsa_pairs table is used to store a public and private key as well as a created timestamp
     * This timestamp is used to track the "age" of a pair, and remove old/unused pairs
     */
    public function initialize()
    {
        $pair=openssl_pkey_new();
        $keyDetails=openssl_pkey_get_details($pair);
        $this->pubKey=$keyDetails['key'];
        openssl_pkey_export($pair, $this->privKey);
        sqlQuery("INSERT into rsa_pairs (public,private,created) values (?,?,NOW())",array($this->get_pubKeyJS(),$this->privKey));
    }
    
    
    /**
     * Retrieve and existing private key based on the public key value and delete it.
     * This function also garbage collects any "stale/unused" pairs that are older than 5 minutes (300 seconds)
     * 
     * @param type $pub
     */
    public function load_from_db($pub)
    {
        $res=sqlQuery("SELECT private FROM rsa_pairs where public=?",array($pub));
        $this->privKey=$res['private'];
        // Delete this pair, and garbage collect any pairs older than 5 minutes.
        sqlQuery("DELETE FROM rsa_pairs where public=? OR timestampdiff(second,created,now()) > ?",array($pub,300));
        
    }
    
    /**
     * 
     * @return string       The public key
     */
    public function get_pubKey()
    {
        return $this->pubKey;
    }
    
    /**
     *
     * @param type $msg     The ciphertext to be decrypted
     * @return string       The cleartext of the message
     */
    public function decrypt($msg)
    {
        $decrypted='';
        $status=openssl_private_decrypt(base64_decode($msg),$decrypted,$this->privKey);
        return $decrypted;
    }
    
    /**
     * 
     * Return the public key in a format that can be used by javascript.  (Strip out the new lines)
     * 
     * @return string
     */
    public function get_pubKeyJS()
    {
        return preg_replace("/\\n/","",$this->get_pubKey());
    }
}
?>