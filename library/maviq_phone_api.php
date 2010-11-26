<?php 
// Copyright (C) 2010 Maviq <info@maviq.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
    if(!extension_loaded("curl"))
        throw(new Exception(
            "Curl extension is required"));
            
    class MaviqClient {
    
        protected $Endpoint;
        protected $SiteId;
        protected $Token;
        
        public function __construct($siteId, $token, $endpoint) {
            $this->SiteId = $siteId;
            $this->Token = $token;
            $this->Endpoint = $endpoint;    
        
        }
        
        public function sendRequest($path, $method="POST", $vars=array()){
        
            echo "Path: {$path}\n";

            $encoded = "";
            foreach($vars AS $key=>$value)
                $encoded .= "$key=".urlencode($value)."&";
            $encoded = substr($encoded, 0, -1);
            $tmpfile = "";
            $fp = null;
            
            // construct full url
            $url = "{$this->Endpoint}/$path";
            
        echo "Url: {$url}\n";

            // if GET and vars, append them
            if($method == "GET") 
                $url .= (FALSE === strpos($path, '?')?"?":"&").$encoded;

            // initialize a new curl object            
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            switch(strtoupper($method)) {
                case "GET":
                    curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
                    break;
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, TRUE);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
                    break;
                case "PUT":
                    // curl_setopt($curl, CURLOPT_PUT, TRUE);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                    file_put_contents($tmpfile = tempnam("/tmp", "put_"),
                        $encoded);
                    curl_setopt($curl, CURLOPT_INFILE, $fp = fopen($tmpfile,
                        'r'));
                    curl_setopt($curl, CURLOPT_INFILESIZE, 
                        filesize($tmpfile));
                    break;
                case "DELETE":
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                    break;
                default:
                    throw(new Exception("Unknown method $method"));
                    break;
            }
            
            // send credentials
            curl_setopt($curl, CURLOPT_USERPWD,
                $pwd = "{$this->SiteId}:{$this->Token}");
            
            // do the request. If FALSE, then an exception occurred    
            if(FALSE === ($result = curl_exec($curl)))
                throw(new Exception(
                    "Curl failed with error " . curl_error($curl)));
            
            // get result code
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            // unlink tmpfiles
            if($fp)
                fclose($fp);
            if(strlen($tmpfile))
                unlink($tmpfile);
                
            return new RestResponse($url, $result, $responseCode);
        }
        
    }
    
    class RestResponse {
        
        public $ResponseText;
        public $ResponseXml;
        public $HttpStatus;
        public $Url;
        public $QueryString;
        public $IsError;
        public $ErrorMessage;
        
        public function __construct($url, $text, $status) {
            preg_match('/([^?]+)\??(.*)/', $url, $matches);
            $this->Url = $matches[1];
            $this->QueryString = $matches[2];
            $this->ResponseText = $text;
            $this->HttpStatus = $status;
            if($this->HttpStatus != 204)
                $this->ResponseXml = @simplexml_load_string($text);
            
            if($this->IsError = ($status >= 400))
                $this->ErrorMessage =
                    (string)$this->ResponseXml->RestException->Message;
            
        }
        
    }
    
?>
        
