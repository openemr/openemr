<?php

/**
 * The line under the patient's name in the patient bar, as its parts:
 * "PID 43", "Male", "DOB 06/09/1993", "33 years". The theme draws the bullets between them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tabs;

final readonly class PatientBarInfo
{
    /**
     * @param int    $pid      the patient's internal ID, which every patient has
     * @param string $sex      the Sex list title, already translated
     * @param string $dob      the date of birth in the site's short date format
     * @param string $age      OpenEMR's age display: "33", "5 month", or "33y 2m 5d"
     * @param bool   $deceased true when $age is the age at death
     */
    public function __construct(
        public int $pid,
        public string $sex,
        public string $dob,
        public string $age,
        public bool $deceased = false,
    ) {
    }

    /**
     * The parts that have a value, in order.
     *
     * @return list<string>
     */
    public function parts(): array
    {
        $parts = [xl('PID') . ' ' . $this->pid];
        if ($this->sex !== '') {
            $parts[] = $this->sex;
        }
        if ($this->dob !== '') {
            $parts[] = xl('DOB') . ' ' . $this->dob;
        }
        $age = $this->ageText();
        if ($age !== '') {
            $parts[] = $this->deceased ? xl('Age at death') . ' ' . $age : $age;
        }
        return $parts;
    }

    /**
     * A bare number of years gets its unit; ages in months, or in years, months and days,
     * already carry theirs.
     */
    private function ageText(): string
    {
        if (ctype_digit($this->age)) {
            return $this->age . ' ' . xl('years');
        }
        return $this->age;
    }
}
