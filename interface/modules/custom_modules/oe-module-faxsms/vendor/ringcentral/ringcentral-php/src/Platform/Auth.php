<?php

namespace RingCentral\SDK\Platform;

class Auth
{
    /** @var string */
    protected $token_type;

    /** @var string */
    protected $access_token;

    /** @var int */
    protected $expires_in;

    /** @var int */
    protected $expire_time;

    /** @var string */
    protected $refresh_token;

    /** @var int */
    protected $refresh_token_expires_in;

    /** @var int */
    protected $refresh_token_expire_time;

    /** @var string */
    protected $scope;

    /** @var string */
    protected $owner_id;

    /**
     * Auth constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Set the auth data using the provided data array.
     * Only updates provided fields i.e. any field not provided does NOT get reset.
     * Does nothing if an empty array is provided. Use reset() if you with to clear existing data.
     *
     * @param array $data Any auth data to set.
     *
     * @return $this
     */
    public function setData(array $data = [])
    {

        if (empty($data)) {
            return $this;
        }

        // Misc

        if (!empty($data['token_type'])) {
            $this->token_type = $data['token_type'];
        }

        if (!empty($data['owner_id'])) {
            $this->owner_id = $data['owner_id'];
        }

        if (!empty($data['scope'])) {
            $this->scope = $data['scope'];
        }

        // Access token

        if (!empty($data['access_token'])) {
            $this->access_token = $data['access_token'];
        }

        if (!empty($data['expires_in'])) {
            $this->expires_in = $data['expires_in'];
        }

        if (empty($data['expire_time']) && !empty($data['expires_in'])) {
            $this->expire_time = time() + $data['expires_in'];
        } elseif (!empty($data['expire_time'])) {
            $this->expire_time = $data['expire_time'];
        }

        // Refresh token

        if (!empty($data['refresh_token'])) {
            $this->refresh_token = $data['refresh_token'];
        }

        if (!empty($data['refresh_token_expires_in'])) {
            $this->refresh_token_expires_in = $data['refresh_token_expires_in'];
        }

        if (empty($data['refresh_token_expire_time']) && !empty($data['refresh_token_expires_in'])) {
            $this->refresh_token_expire_time = time() + $data['refresh_token_expires_in'];
        } elseif (!empty($data['refresh_token_expire_time'])) {
            $this->refresh_token_expire_time = $data['refresh_token_expire_time'];
        }

        return $this;

    }

    /**
     * Reset all auth fields.
     *
     * @return $this
     */
    public function reset()
    {

        $this->token_type = '';

        $this->access_token = '';
        $this->expires_in = 0;
        $this->expire_time = 0;

        $this->refresh_token = '';
        $this->refresh_token_expires_in = 0;
        $this->refresh_token_expire_time = 0;

        $this->scope = '';
        $this->owner_id = '';

        return $this;

    }

    /**
     * Get all auth data.
     *
     * @return array An array containing all of the currently set auth data.
     */
    public function data()
    {

        return [
            'token_type'                => $this->token_type,
            'access_token'              => $this->access_token,
            'expires_in'                => $this->expires_in,
            'expire_time'               => $this->expire_time,
            'refresh_token'             => $this->refresh_token,
            'refresh_token_expires_in'  => $this->refresh_token_expires_in,
            'refresh_token_expire_time' => $this->refresh_token_expire_time,
            'scope'                     => $this->scope,
            'owner_id'                  => $this->owner_id,
        ];

    }

    /**
     * Get the access token.
     *
     * @return string
     */
    public function accessToken()
    {
        return $this->access_token;
    }

    /**
     * Get the refresh token.
     *
     * @return string
     */
    public function refreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * Get the token type.
     *
     * @return string
     */
    public function tokenType()
    {
        return $this->token_type;
    }

    /**
     * Return whether or not the access token is valid (i.e. not expired).
     *
     * @return bool True if the access token is valid, otherwise false.
     */
    public function accessTokenValid()
    {
        return $this->expire_time > time();
    }

    /**
     * Return whether or not the refresh token is valid (i.e. not expired).
     *
     * @return bool True if the access token is valid, otherwise false.
     */
    public function refreshTokenValid()
    {
        return $this->refresh_token_expire_time > time();
    }
}