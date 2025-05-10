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

    private int $ppid;
    private int $formid;
    private ?string $hl7;
    private ?string $reqStr;
    private array $messages = [];

    public function __construct($formid, $ppid, ?string &$hl7 = null, ?string &$reqStr = null)
    {
        $this->ppid = $ppid;
        $this->formid = $formid;
        $this->hl7 = &$hl7;
        $this->reqStr = &$reqStr;
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
        $this->messages[] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
