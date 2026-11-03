<?php

/**
 * The encounter forms whose rows the eCQM data layer reads, by the directory forms.formdir
 * records for each. AbstractQdmService::notInDeletedForm() takes one to leave out the rows of
 * a form deleted from the visit.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Qdm;

/**
 * Backed because the value is what forms.formdir stores.
 */
enum FormDirectory: string
{
    case CarePlan = 'care_plan';
    case Observation = 'observation';
    case ProcedureOrder = 'procedure_order';
    case Vitals = 'vitals';
}
