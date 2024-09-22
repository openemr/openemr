<?php

namespace PubNub\Managers;

use PubNub\PNConfiguration;

class BasePathManager
{
    /** default subdomain used if cache busting is disabled. */
    protected const DEFAULT_SUBDOMAIN = "ps";

    /** Default base path if a custom one is not provided.*/
    protected const DEFAULT_BASE_PATH = "pndsn.com";

    /** @var  PNConfiguration */
    private $config;

    /**
     * BasePathManager constructor.
     *
     * @param $initialConfig PNConfiguration
     */
    public function __construct($initialConfig)
    {
        $this->config = $initialConfig;
    }

    /**
     * Prepares a next usable base url.
     *
     * @return string
     */
    public function getBasePath($customHost = null)
    {
        $constructedUrl = "http";

        if ($this->config->isSecure()) {
            $constructedUrl .= "s";
        }

        $constructedUrl .= "://";

        if ($customHost != null) {
            $constructedUrl .= $customHost;
        } elseif ($this->config->getOrigin() != null) {
            $constructedUrl .= $this->config->getOrigin();
        } else {
            $constructedUrl .= static::DEFAULT_SUBDOMAIN . "." . static::DEFAULT_BASE_PATH;
        }

        return $constructedUrl;
    }
}
