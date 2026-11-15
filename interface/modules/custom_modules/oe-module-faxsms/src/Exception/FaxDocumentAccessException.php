<?php

/**
 * A patient document can't be read for a fax or email send: the id is invalid,
 * the user may not read it, or its content can't be loaded.
 *
 * The message is a translated sentence meant for the user, so callers may show
 * it as is. Anything internal (a file path, a decrypt error) stays on the
 * previous exception.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Exception;

final class FaxDocumentAccessException extends FaxException
{
}
