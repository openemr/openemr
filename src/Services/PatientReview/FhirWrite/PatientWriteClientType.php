<?php

/**
 * Whether a patient app authenticates itself to the server.
 *
 * A confidential client proves its identity with a secret or JWKS. A public client can't keep
 * a secret, so PKCE protects its code exchange but nothing proves which app is calling.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

enum PatientWriteClientType
{
    case Confidential;
    case Public;
}
