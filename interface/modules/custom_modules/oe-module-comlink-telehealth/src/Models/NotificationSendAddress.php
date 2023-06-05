<?php

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Models;

class NotificationSendAddress
{
    const TYPE_EMAIL = "Email";
    const TYPE_SMS = "SMS";
    const TYPE_OTHER = "Other";
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var "Email"|"Other"
     */
    private $type;

    public function __construct($destination, $name = "", $type = "Email")
    {
        $this->destination = $destination;
        $this->name = $name;
        $this->type = $type;
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
