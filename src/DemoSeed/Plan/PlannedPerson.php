<?php

/**
 * A related person the loader writes to the person table. When linkedPid
 * is set the person stands for that patient and is linked to it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

final readonly class PlannedPerson
{
    /**
     * @param string $ref unique within the plan
     * @param ?string $sex option id of list `sex` (Male, Female)
     */
    public function __construct(
        public string $ref,
        public string $firstName,
        public string $lastName,
        public ?string $sex,
        public ?\DateTimeImmutable $birthDate,
        public ?int $linkedPid,
        public ?string $note = null,
    ) {
    }
}
