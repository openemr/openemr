<?php

namespace  PubNub\Models\Consumer\AccessManager;


class PNAccessManagerGrantResult extends PNAccessManagerAbstractResult
{
    public function __toString()
    {
        return sprintf("Current permissions are valid for %d minutes: read %s, write %s, manage: %s, delete: %s, get: %s, update: %s, join: %s",
            (int) $this->ttl, $this->readEnabled, $this->writeEnabled, $this->manageEnabled, $this->deleteEnabled, $this->getEnabled, $this->updateEnabled, $this->joinEnabled);
    }
}