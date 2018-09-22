<?php
/**
 * NlpService
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
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

namespace OpenEMR\Services;

class NlpService
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->user = $GLOBALS['ctakes_user'];
        $this->pass = $GLOBALS['ctakes_pass'];
        $this->host = $GLOBALS['ctakes_host'];
        $this->port = $GLOBALS['ctakes_port'];
    }
    
    public function is_ctakes_setup()
    {
        return isset($this->user) && isset($this->pass) && isset($this->host) && isset($this->port);
    }
    
    public function get($text)
    {
        $curl = curl_init($this->host . ':' . $this->port . '/ctakes-web-rest/service/analyze');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($text));
        curl_setopt($curl, CURLOPT_USERPWD, $this->user . ":" . $this->pass);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $responseBody = curl_exec($curl);
        
        $responseBodyJson = json_decode($responseBody, true);
        
        $result = array();

        foreach ($responseBodyJson as $findings) {
            foreach ($findings as $codeKey => $codeValues) {
                foreach ($codeValues as $codeValue) {
                    $term = "codingScheme";
                    if (substr($codeValue, 1, 12) === $term) {
                        $parts = explode(" ", $codeValue);
                        $system = str_replace(",", "", $parts[1]);
                        $code = str_replace(",", "", $parts[3]);
                        
                        array_push($result, "\"" . $codeKey . "\": " . $system . " " . $code);
                    }
                }
            }
        }
        
        curl_close($curl);
        
        return $result;
    }
}
