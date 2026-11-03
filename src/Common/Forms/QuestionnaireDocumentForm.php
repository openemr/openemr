<?php

/**
 * A questionnaire form that a portal document holds, with the patient and encounter it was saved under.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms;

final readonly class QuestionnaireDocumentForm
{
    /**
     * @param int  $pid       The document's patient, which is also the form's.
     * @param int  $encounter The form's encounter; 0 when the document has none.
     * @param bool $locked    A document holding the form is locked (charted history): view, don't change.
     */
    public function __construct(public int $pid, public int $encounter, public bool $locked)
    {
        if ($pid <= 0) {
            throw new \DomainException('A portal document form must belong to a patient');
        }
        if ($encounter < 0) {
            throw new \DomainException('A portal document form encounter cannot be negative');
        }
    }
}
