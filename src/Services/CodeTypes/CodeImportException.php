<?php

/**
 * CodeImportException is thrown when a code set import cannot be completed.
 *
 * It deliberately extends \RuntimeException rather than a broader type so that consumers can
 * catch it without also suppressing \Error / \ErrorException, which the project forbids (see
 * tests/PHPStan/Rules/ForbiddenCatchTypeRule.php).
 *
 * Messages carried by this exception are intended for the system log only. Never render them to
 * the user -- they can contain file paths and other internal details.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2026 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\CodeTypes;

use RuntimeException;

class CodeImportException extends RuntimeException
{
}
