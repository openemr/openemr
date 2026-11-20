<?php

/**
 * The yes/no parts of a relationship (contact_relation).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class RelationFlags
{
    public const KEYS = ['primary_contact', 'emergency_contact', 'medical_decisions', 'receive_medical_info'];

    public function __construct(
        public bool $primaryContact = false,
        public bool $emergencyContact = false,
        public bool $medicalDecisions = false,
        public bool $receiveMedicalInfo = false,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        return new self(
            $map->optionalBool('primary_contact'),
            $map->optionalBool('emergency_contact'),
            $map->optionalBool('medical_decisions'),
            $map->optionalBool('receive_medical_info'),
        );
    }
}
