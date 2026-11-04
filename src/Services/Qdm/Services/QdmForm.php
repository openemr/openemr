<?php

/**
 * The encounter forms whose rows the QDM services read, by the directory forms.formdir records.
 * AbstractQdmService::countedFormRow() takes one to leave out the rows of a form deleted from
 * the visit.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Qdm\Services;

/**
 * Backed because the value is what forms.formdir stores.
 */
enum QdmForm: string
{
    case CarePlan = 'care_plan';
    case Observation = 'observation';
    case ProcedureOrder = 'procedure_order';
    case Vitals = 'vitals';

    /**
     * Whether every writer of this form's rows files a forms row for each, so that a row with none
     * is a leftover: the patient delete removes a patient's forms rows and leaves the form's own.
     * The Vitals form, the vitals API and the C-CDA import all file one. Orders made through the
     * API, and care plan items and observations from the C-CDA and QRDA import, can have none.
     */
    public function alwaysFiled(): bool
    {
        return match ($this) {
            self::Vitals => true,
            self::CarePlan, self::Observation, self::ProcedureOrder => false,
        };
    }
}
