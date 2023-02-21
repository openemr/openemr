<?php

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Events;

use Comlink\OpenEMR\Modules\TeleHealthModule\Models\NotificationSendAddress;

class TelehealthNotificationSendEvent
{
    const EVENT_HANDLE = "comlink.telehealth.notification.send";

    /**
     * @var string
     */
    private $messageId;

    /**
     * Note as this table changes this data record could change.  If you need type safety its recommended to use the pid.
     * @var array The patient record array from the patient_data table.
     */
    private $patient;

    /**
     * @var The unique pid id of the patient
     */
    private $pid;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $joinLink;

    /**
     * @var NotificationSendAddress
     */
    private $from;

    /**
     * @var NotificationSendAddress[]
     */
    private $sendToDestinations;

    /**
     * @var NotificationSendAddress[]
     */
    private $replyToDestinations;

    /**
     * @var string
     */
    private $textBody;

    /**
     * @var string
     */
    private $htmlBody;

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @param string $messageId
     * @return TelehealthNotificationSendEvent
     */
    public function setMessageId(string $messageId): TelehealthNotificationSendEvent
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @return array
     */
    public function getPatient(): array
    {
        return $this->patient;
    }

    /**
     * @param array $patient
     * @return TelehealthNotificationSendEvent
     */
    public function setPatient(array $patient): TelehealthNotificationSendEvent
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return The
     */
    public function getPid(): The
    {
        return $this->pid;
    }

    /**
     * @param The $pid
     * @return TelehealthNotificationSendEvent
     */
    public function setPid(The $pid): TelehealthNotificationSendEvent
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return TelehealthNotificationSendEvent
     */
    public function setSubject(string $subject): TelehealthNotificationSendEvent
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getJoinLink(): string
    {
        return $this->joinLink;
    }

    /**
     * @param string $joinLink
     * @return TelehealthNotificationSendEvent
     */
    public function setJoinLink(string $joinLink): TelehealthNotificationSendEvent
    {
        $this->joinLink = $joinLink;
        return $this;
    }

    /**
     * @return NotificationSendAddress
     */
    public function getFrom(): NotificationSendAddress
    {
        return $this->from;
    }

    /**
     * @param NotificationSendAddress $from
     * @return TelehealthNotificationSendEvent
     */
    public function setFrom($destination, $name, $type = NotificationSendAddress::TYPE_EMAIL): TelehealthNotificationSendEvent
    {
        $this->from = new NotificationSendAddress($destination, $name, $type);
        return $this;
    }

    /**
     * @return NotificationSendAddress[]
     */
    public function getSendToDestinations(): array
    {
        return $this->sendToDestinations;
    }

    /**
     * @param NotificationSendAddress[] $sendToDestinations
     * @return TelehealthNotificationSendEvent
     */
    public function setSendToDestinations(array $sendToDestinations): TelehealthNotificationSendEvent
    {
        $this->sendToDestinations = $sendToDestinations;
        return $this;
    }

    /**
     * @param $destination
     * @param $name
     * @param string $type
     * @return TelehealthNotificationSendEvent
     */
    public function addSendToDestination($destination, $name, $type = NotificationSendAddress::TYPE_EMAIL): TelehealthNotificationSendEvent
    {
        $this->sendToDestinations[] = new NotificationSendAddress($destination, $name, $type);
        return $this;
    }

    /**
     * @return NotificationSendAddress[]
     */
    public function getReplyToDestinations(): array
    {
        return $this->replyToDestinations;
    }

    /**
     * @param NotificationSendAddress[] $replyToDestinations
     * @return TelehealthNotificationSendEvent
     */
    public function setReplyToDestinations(array $replyToDestinations): TelehealthNotificationSendEvent
    {
        $this->replyToDestinations = $replyToDestinations;
        return $this;
    }

    /**
     * @param $destination
     * @param $name
     * @param string $type
     * @return TelehealthNotificationSendEvent
     */
    public function addReplyToDestination($destination, $name, $type = NotificationSendAddress::TYPE_EMAIL): TelehealthNotificationSendEvent
    {
        $this->replyToDestinations[] = new NotificationSendAddress($destination, $name, $type);
        return $this;
    }

    /**
     * @return string
     */
    public function getTextBody(): string
    {
        return $this->textBody;
    }

    /**
     * @param string $textBody
     * @return TelehealthNotificationSendEvent
     */
    public function setTextBody(string $textBody): TelehealthNotificationSendEvent
    {
        $this->textBody = $textBody;
        return $this;
    }

    /**
     * @return string
     */
    public function getHtmlBody(): string
    {
        return $this->htmlBody;
    }

    /**
     * @param string $htmlBody
     * @return TelehealthNotificationSendEvent
     */
    public function setHtmlBody(string $htmlBody): TelehealthNotificationSendEvent
    {
        $this->htmlBody = $htmlBody;
        return $this;
    }
}
