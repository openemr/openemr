<?php

/**
 * PatientDocumentEvents
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientDocuments;

use Symfony\Component\EventDispatcher\Event;

class PatientDocumentEvent extends Event
{
    const ACTIONS_RENDER_FAX_ANCHOR = 'documents.actions.render.fax.anchor';
    const JAVASCRIPT_READY_FAX_DIALOG = 'documents.javascript.fax.dialog';
}
