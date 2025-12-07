<?php

// Copyright (C) 2010 Maviq <info@maviq.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Code migrated from curl to Guzzle by GitHub Copilot AI

use OpenEMR\Common\Http\GuzzleHttpClient;

class MaviqClient
{
    private GuzzleHttpClient $httpClient;

    public function __construct(protected $SiteId, protected $Token, protected $Endpoint)
    {
        $httpVerifySsl = (bool) ($GLOBALS['http_verify_ssl'] ?? true);
        $this->httpClient = new GuzzleHttpClient([
            'verify' => $httpVerifySsl,
        ]);
    }

    public function sendRequest($path, $method = "POST", $vars = [])
    {
        echo "Path: {$path}\n";

        // construct full url
        $url = "{$this->Endpoint}/$path";
        echo "Url: {$url}\n";

        $options = [
            'auth' => [$this->SiteId, $this->Token],
        ];

        switch (strtoupper((string) $method)) {
            case "GET":
                if (!empty($vars)) {
                    $options['query'] = $vars;
                }
                $httpResponse = $this->httpClient->get($url, $options);
                break;
            case "POST":
                $options['form_params'] = $vars;
                $httpResponse = $this->httpClient->post($url, $options);
                break;
            case "PUT":
                $options['form_params'] = $vars;
                $httpResponse = $this->httpClient->put($url, $options);
                break;
            case "DELETE":
                $httpResponse = $this->httpClient->delete($url, $options);
                break;
            default:
                throw(new Exception("Unknown method $method"));
        }

        // Check for errors
        if ($httpResponse->hasError()) {
            throw(new Exception(
                "HTTP request failed with error: " . ($httpResponse->getError() ?? 'Unknown error')
            ));
        }

        return new RestResponse($url, $httpResponse->getBody(), $httpResponse->getStatusCode());
    }
}
// End of AI-generated code

class RestResponse
{
    public $ResponseXml;
    public $Url;
    public $QueryString;
    public $IsError;
    public $ErrorMessage;

    public function __construct($url, public $ResponseText, public $HttpStatus)
    {
        preg_match('/([^?]+)\??(.*)/', (string) $url, $matches);
        $this->Url = $matches[1];
        $this->QueryString = $matches[2];
        if ($this->HttpStatus != 204) {
            $this->ResponseXml = @simplexml_load_string((string) $this->ResponseText);
        }

        if ($this->IsError = ($this->HttpStatus >= 400)) {
            $this->ErrorMessage =
                (string)$this->ResponseXml->RestException->Message;
        }
    }
}
