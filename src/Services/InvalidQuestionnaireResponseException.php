<?php

/**
 * Thrown when a native QuestionnaireResponse save fails input validation.
 *
 * Carries a safe, translated, user-facing message; the endpoint maps it to an HTTP 400.
 * Distinct from a genuine fault so the endpoint can return a helpful message without
 * swallowing real errors.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use RuntimeException;

class InvalidQuestionnaireResponseException extends RuntimeException
{
}
