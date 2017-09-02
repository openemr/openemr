<?php
/********************************************************************************\
 * Copyright (C) visolve (vicareplus_engg@visolve.com)                          *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 ********************************************************************************/


/* This file contains routines for creating SSL certificates */


/**
 * Create a Certificate Signing Request (CSR) with the given values
 * @param $commonName - The username/hostname
 * @param $emailAddress - The email of the username
 * @param $countryName - Two letter country code, like "US"
 * @param $stateOrProvinceName - State name
 * @param $localityName - City name
 * @param $organizationName - Organization Name
 * @param $organizationalUnitName - Organization Unit Name
 * @return array [ CSR data, privatekey ], or 'false' on error.
 */
function create_csr(
    $commonName,
    $emailAddress,
    $countryName,
    $stateOrProvinceName,
    $localityName,
    $organizationName,
    $organizationalUnitName
) {

    if ($commonName == "") {
        return false;
    }

    /* Build the Distinguished Name (DN) for the certificate */
    $dn = array("commonName" => $commonName);

    if ($emailAddress) {
        $dn = array_merge($dn, array("emailAddress" => $emailAddress));
    }

    if ($countryName) {
        $dn = array_merge($dn, array("countryName" => $countryName));
    }

    if ($stateOrProvinceName) {
        $dn = array_merge($dn, array("stateOrProvinceName" => $stateOrProvinceName));
    }

    if ($localityName) {
        $dn = array_merge($dn, array("localityName" => $localityName));
    }

    if ($organizationName) {
        $dn = array_merge($dn, array("organizationName" => $organizationName));
    }

    if ($organizationalUnitName) {
        $dn = array_merge($dn, array("organizationalUnitName" => $organizationalUnitName));
    }

    /* OpenSSL functions need the path to the openssl.cnf file */
    $opensslConf = $GLOBALS['webserver_root'] . "/library/openssl.cnf";
    $config = array('config' => $opensslConf);

    /* Create the public/private key pair */
    $privkey = openssl_pkey_new($config);
    if ($privkey === false) {
        return false;
    }

    $csr = openssl_csr_new($dn, $privkey, $config);
    if ($csr === false) {
        return false;
    }

    return array($csr, $privkey);
}


/**
 * Create a certificate, signed by the given Certificate Authority.
 * @param $privkey - The certificate private key
 * @param $csr     - The certificate signing request
 * @param $cacert  - The Certificate Authority to sign with, or NULL if not used.
 * @param $cakey   - The Certificate Authority private key data to sign with.
 * @return data    - A signed certificate, or false on error.
 */
function create_crt($privkey, $csr, $cacert, $cakey)
{

    $opensslConf = $GLOBALS['webserver_root'] . "/library/openssl.cnf";
    $config = array('config' => $opensslConf);

    $cert = openssl_csr_sign($csr, $cacert, $cakey, 3650, $config, rand(1000, 9999));
    return $cert;
}


/**
 * Create a new client certificate for a username or client hostname.
 * @param $commonName   - The username or hostname
 * @param $emailAddress - The user's email address
 * @param $serial       - The serial number
 * @param $cacert       - Path to Certificate Authority cert file.
 * @param $cakey        - Path to Certificate Authority key file.
 * @param $valid_days   - validity in number of days for the user certificate
 * @return string       - The client certificate signed by the Certificate Authority, or false on error.
 */
function create_user_certificate($commonName, $emailAddress, $serial, $cacert, $cakey, $valid_days)
{

    $opensslConf = $GLOBALS['webserver_root'] . "/library/openssl.cnf";
    $config = array('config' => $opensslConf);

    /* Generate a certificate signing request */
    $arr = create_csr($commonName, $emailAddress, "", "", "", "", "");
    if ($arr === false) {
        return false;
    }

    $csr = $arr[0];
    $privkey = $arr[1];

    /* user id is used as serial number to sign a certificate */
    $serial = 0;
    $res = sqlStatement("select id from users where username='".$commonName."'");
    if ($row = sqlFetchArray($res)) {
        $serial = $row['id'];
    }

    $cert = openssl_csr_sign(
        $csr,
        file_get_contents($cacert),
        file_get_contents($cakey),
        $valid_days,
        $config,
        $serial
    );

    if ($cert === false) {
        return false;
    }

    /* Convert the user certificate to .p12 (PKCS 12) format, which is the
     * standard format used by browsers.
     */
    if (openssl_pkcs12_export($cert, $p12Out, $privkey, "") === false) {
        return false;
    }

    return $p12Out;
}
