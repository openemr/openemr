<?php

namespace PubNub\Crypto;

class Header
{
    public const HEADER_VERSION = 1;
    private string $sentinel;
    private string $cryptorId;
    private string $cryptorData;
    private int $length;

    public function __construct(
        string $sentinel,
        string $cryptorId,
        string $cryptorData,
        int $length
    ) {
        $this->sentinel = $sentinel;
        $this->cryptorId = $cryptorId;
        $this->cryptorData = $cryptorData;
        $this->length = $length;
    }

    public function getSentinel(): string
    {
        return $this->sentinel;
    }

    public function getCryptorId(): string
    {
        return $this->cryptorId;
    }

    public function getCryptorData(): string
    {
        return $this->cryptorData;
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
