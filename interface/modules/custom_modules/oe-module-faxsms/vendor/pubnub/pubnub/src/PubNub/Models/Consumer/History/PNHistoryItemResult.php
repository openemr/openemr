<?php

namespace PubNub\Models\Consumer\History;

use PubNub\PubNubCryptoCore;

class PNHistoryItemResult
{
    /** @var  any */
    private $entry;

    /** @var  PubNubCryptoCore */
    private $crypto;

    /** @var  int */
    private $timetoken;

    private $error = null;

    /**
     * PNHistoryItemResult constructor.
     * @param string $entry
     * @param PubNubCryptoCore $crypto
     * @param int $timetoken
     */
    public function __construct($entry, $crypto, $timetoken = null)
    {
        $this->entry = $entry;
        $this->crypto = $crypto;
        $this->timetoken = $timetoken;
    }

    public function __toString()
    {
        return sprintf("History item with tt: %s and content: %s", $this->getTimetoken(), $this->getEntry());
    }

    public function decrypt()
    {
        try {
            if (is_string($this->entry)) {
                $this->entry = $this->crypto->decrypt($this->entry);
            } elseif (is_array($this->entry) and key_exists('pn_other', $this->entry)) {
                $this->entry['pn_other'] = $this->crypto->decrypt($this->entry['pn_other']);
            }
        } catch (\Exception $error) {
            $this->error = $error;
        }
    }

    /**
     * @return any
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @return int
     */
    public function getTimetoken()
    {
        return $this->timetoken;
    }
}
