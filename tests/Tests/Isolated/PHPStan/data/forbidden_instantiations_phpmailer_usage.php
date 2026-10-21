<?php

/**
 * Fixture for ForbiddenInstantiationsRuleTest (the PHPMailer entry).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\data;

use PHPMailer\PHPMailer\PHPMailer;

function buildPlainObject(): \stdClass
{
    // Not PHPMailer — the rule must leave other instantiations alone.
    return new \stdClass();
}

function buildMailer(): PHPMailer
{
    return new PHPMailer();
}
