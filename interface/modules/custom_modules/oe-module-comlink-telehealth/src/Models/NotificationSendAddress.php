<?php

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Models;

class NotificationSendAddress
{
    const TYPE_EMAIL = "Email";
    const TYPE_SMS = "SMS";
    const TYPE_OTHER = "Other";

    /**
     * @param string $destination
     * @param string $name
     * @param string $type
     */
    public function __construct(private $destination, private $name = "", private $type = "Email")
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
