<?php

/**
 * A patient's residence for the UDS Patients by ZIP Code Table: either a
 * normalized 5-digit ZIP code or the "Unknown Residence" bucket.
 *
 * The manual reports patients by their most recent ZIP on file, with a distinct
 * row for patients whose residence is not known and no proxy is available
 * (manual lines 1216–1219). ZIP+4 and other trailing detail are dropped to the
 * 5-digit prefix. Distinguishing in-service-area ZIPs from the "Other ZIP Codes"
 * grouping needs the center's scope config and is handled at the boundary, not
 * here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

use DomainException;

final readonly class ZipResidence
{
    private const UNKNOWN_KEY = '__unknown__';

    private function __construct(public ?string $zip)
    {
    }

    public static function ofZip(string $zip): self
    {
        if (preg_match('/^\d{5}$/', $zip) !== 1) {
            throw new DomainException('ZIP code must be exactly 5 digits');
        }

        return new self($zip);
    }

    public static function unknown(): self
    {
        return new self(null);
    }

    /**
     * Best-effort normalization of a raw stored value: the leading 5 digits
     * become a ZIP, anything else (null, blank, too short) is Unknown Residence.
     */
    public static function fromRawZip(?string $raw): self
    {
        if ($raw === null) {
            return self::unknown();
        }

        if (preg_match('/(\d{5})/', $raw, $matches) === 1) {
            return new self($matches[1]);
        }

        return self::unknown();
    }

    public function isUnknown(): bool
    {
        return $this->zip === null;
    }

    /**
     * Stable grouping key — the ZIP itself, or a reserved sentinel for the
     * Unknown Residence bucket (which can never collide with a 5-digit ZIP).
     */
    public function key(): string
    {
        return $this->zip ?? self::UNKNOWN_KEY;
    }

    public function label(): string
    {
        return $this->zip ?? 'Unknown Residence';
    }
}
