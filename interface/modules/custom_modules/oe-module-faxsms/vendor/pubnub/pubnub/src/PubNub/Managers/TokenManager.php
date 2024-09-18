<?php

namespace PubNub\Managers;

class TokenManager
{
    private $token = null;

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }
}
