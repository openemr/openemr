<?php

namespace PubNub\Crypto;

class Payload
{
    private string $data;
    private ?string $cryptorData;
    private ?string $cryptorId;

    public function __construct(string $data, ?string $cryptorData = null, ?string $cryptorId = null)
    {
        $this->data = $data;
        $this->cryptorData = $cryptorData;
        $this->cryptorId = $cryptorId;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getCryptorData(): ?string
    {
        return $this->cryptorData;
    }

    public function getCryptorId(): ?string
    {
        return $this->cryptorId;
    }
}
