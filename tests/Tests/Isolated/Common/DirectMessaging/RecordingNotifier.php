<?php

/**
 * Test double: a notifier that records the alerts it was asked to send.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\PhiMailNotifier;

final class RecordingNotifier implements PhiMailNotifier
{
    /** @var list<array{string, string}> subject and body of each alert */
    public array $alerts = [];

    public function notify(string $subject, string $body): void
    {
        $this->alerts[] = [$subject, $body];
    }
}
