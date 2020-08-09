<?php

/**
 * library/create_ssl_certificate.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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

    /* Create the public/private key pair */
    $privkey = openssl_pkey_new();
    if ($privkey === false) {
        return false;
    }

    $csr = openssl_csr_new($dn, $privkey);
    if ($csr === false) {
        return false;
    }

    return array($csr, $privkey);
}


/**
 * Create a certificate, signed by the given Certificate Authority.
 * @param $csr     - The certificate signing request
 * @param $cacert  - The Certificate Authority to sign with, or NULL if not used.
 * @param $cakey   - The Certificate Authority private key data to sign with.
 * @return data    - A signed certificate, or false on error.
 */
function create_crt($csr, $cacert, $cakey)
{
    $cert = openssl_csr_sign($csr, $cacert, $cakey, 3650, ['digest_alg' => 'sha256'], rand(1000, 9999));
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
    /* Generate a certificate signing request */
    $arr = create_csr($commonName, $emailAddress, "", "", "", "", "");
    if ($arr === false) {
        return false;
    }

    $csr = $arr[0];
    $privkey = $arr[1];

    /* user id is used as serial number to sign a certificate */
    $serial = (is_int($serial)) ? $serial : 0;
    $res = sqlStatement("SELECT id FROM users WHERE username = ?", array($commonName));
    if ($row = sqlFetchArray($res)) {
        $serial = $row['id'];
    }

    $cert = openssl_csr_sign(
        $csr,
        file_get_contents($cacert),
        file_get_contents($cakey),
        $valid_days,
        ['digest_alg' => 'sha256'],
        $serial
    );

    if ($cert === false) {
        return false;
    }

    /* Convert the user certificate to .p12 (PKCS 12) format, which is the
     * standard format used by browsers.
     */
    $clientPassPhrase = trim($_POST['clientPassPhrase']);
    if (openssl_pkcs12_export($cert, $p12Out, $privkey, $clientPassPhrase) === false) {
        return false;
    }

    return $p12Out;
}
