<?php

namespace  PubNub\Models\Consumer\AccessManager;


class PNAccessManagerAuditResult extends PNAccessManagerAbstractResult
{
    public function __toString()
    {
        return sprintf("Current permissions are valid for %d minutes: read %s, write %s, manage: %s",
            (int)$this->ttl, $this->readEnabled, $this->writeEnabled, $this->manageEnabled);
    }
}