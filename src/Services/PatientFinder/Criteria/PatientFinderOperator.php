<?php

/**
 * An operator of a Patient Finder criterion and the kind of value it takes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

final readonly class PatientFinderOperator
{
    /**
     * @param string $label the translated text of the operator, e.g. "between"
     * @param PatientFinderInput $input the kind of value the operator takes
     * @param string $unit the translated unit shown after a number, e.g. "days"; empty for none
     */
    public function __construct(
        public string $label,
        public PatientFinderInput $input = PatientFinderInput::None,
        public int $min = 0,
        public int $max = 0,
        public string $unit = ''
    ) {
    }

    /**
     * @return array{label: string, input: string, min: int, max: int, unit: string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'input' => $this->input->value,
            'min' => $this->min,
            'max' => $this->max,
            'unit' => $this->unit,
        ];
    }

    /**
     * A whole number written as digits only and within this operator's range, else null.
     */
    public function wholeNumber(mixed $value): ?int
    {
        $text = is_string($value) ? trim($value) : (is_int($value) ? (string) $value : '');
        if ($text === '' || !ctype_digit($text) || strlen($text) > 6) {
            return null;
        }
        $number = (int) $text;
        return $number >= $this->min && $number <= $this->max ? $number : null;
    }
}
