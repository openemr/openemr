<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Services;

use Symfony\Contracts\EventDispatcher\Event;

class DornLabEvent extends Event
{
    public const GEN_HL7_ORDER = 'dorn.gen_hl7_order';
    public const GEN_BARCODE = 'dorn.gen_barcode';
    public const SEND_ORDER = 'dorn.send_order';
    private readonly ?string $hl7;
    private readonly ?string $reqStr;
    private array $messages = [];
    private $sendOrderResponse;

    public function __construct(private readonly int $formid, private readonly int $ppid, ?string &$hl7 = null, ?string &$reqStr = null)
    {
        $this->hl7 = &$hl7;
        $this->reqStr = &$reqStr;
    }

    public function setSendOrderResponse($response): void
    {
        $this->sendOrderResponse = $response;
    }

    public function getSendOrderResponse()
    {
        return $this->sendOrderResponse;
    }

    public function getPpid(): int
    {
        return $this->ppid;
    }

    public function getFormid(): int
    {
        return $this->formid;
    }

    public function &getHl7(): ?string
    {
        return $this->hl7;
    }

    public function &getReqStr(): ?string
    {
        return $this->reqStr;
    }

    public function addMessage(string $message): void
    {
        if (empty($message)) {
            return;
        }
        $this->messages[] = $message;
    }

    /**
     * @param string $prefix String to prepend if there are any messages
     * @param bool   $clear  If true, clears the messages array after building the string
     * @return string
     */
    public function getMessagesAsString(string $prefix = '', bool $clear = false): string
    {
        $rtn = '';
        if (!empty($this->messages)) {
            // implode and prepend only when there are messages
            $rtn = "\n" . $prefix . implode("\n", $this->messages);
        }
        if ($clear) {
            $this->messages = [];
        }
        return $rtn;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
