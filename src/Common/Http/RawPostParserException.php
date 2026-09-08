<?php

/**
 * RawPostParserException — thrown by RawPostParser and RawRequestBodyReader
 * when the raw POST body cannot be read or is incompatible with raw parsing
 * (e.g. multipart/form-data bodies that PHP consumes before php://input is
 * populated). Callers should catch this type rather than \RuntimeException.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Alanis <progradedteam@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

final class RawPostParserException extends \RuntimeException
{
}
