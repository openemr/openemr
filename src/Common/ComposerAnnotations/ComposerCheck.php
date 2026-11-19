<?php

/**
 * The Composer checks whose failures openemr:composer-annotations reports
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\ComposerAnnotations;

/**
 * Backed by the name the workflows pass on the command line.
 */
enum ComposerCheck: string
{
    /** `composer require-checker -- --output=json` */
    case RequireChecker = 'require-checker';
    /** `composer validate --strict`, stdout and stderr */
    case Validate = 'validate';
    /** `composer normalize --dry-run` */
    case Normalize = 'normalize';
}
