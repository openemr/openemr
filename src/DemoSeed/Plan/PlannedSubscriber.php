<?php

/**
 * The subscriber columns of a policy: who holds it and how they relate
 * to the patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

use OpenEMR\DemoSeed\PostalAddress;

final readonly class PlannedSubscriber
{
    /**
     * @param string $relationship list `sub_relation`: self, spouse, child, other
     * @param string $sex list `sex` option id
     */
    public function __construct(
        public string $relationship,
        public string $firstName,
        public ?string $middleName,
        public string $lastName,
        public \DateTimeImmutable $birthDate,
        public string $sex,
        public PostalAddress $address,
        public ?string $phone,
    ) {
    }
}
