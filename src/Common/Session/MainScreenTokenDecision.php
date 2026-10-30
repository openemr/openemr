<?php

/**
 * Result of checking the main-screen URL token.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

enum MainScreenTokenDecision
{
    /**
     * The token matches and no session write is required.
     */
    case Accept;

    /**
     * The token matches for the first time. Record the time so a later retry can be judged.
     */
    case AcceptAndStamp;

    /**
     * The token is missing, different, or older than the retry window. End the session.
     */
    case Reject;
}
