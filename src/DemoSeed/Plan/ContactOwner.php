<?php

/**
 * Who owns a planned phone or address: a patient (by pid) or a person
 * the plan makes up (by its reference).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

final readonly class ContactOwner
{
    private function __construct(public ?int $pid, public ?string $personRef)
    {
    }

    public static function patient(int $pid): self
    {
        return new self($pid, null);
    }

    public static function person(string $personRef): self
    {
        return new self(null, $personRef);
    }

    public function key(): string
    {
        return $this->pid !== null ? 'patient:' . $this->pid : 'person:' . $this->personRef;
    }
}
