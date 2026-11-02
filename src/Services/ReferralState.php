<?php

/**
 * Where a referral stands (#14710): open, completed (answered), or closed without an answer
 * (its Task cancelled or entered in error). ReferralStatus decides which.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

enum ReferralState
{
    case Open;
    case Completed;
    case Closed;
}
