<?php

/**
 * Site template stand-in for one statement-run test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

final class StatementSiteTemplate
{
    public static function supportsEnvelopeWindows(): bool
    {
        return true;
    }
}
