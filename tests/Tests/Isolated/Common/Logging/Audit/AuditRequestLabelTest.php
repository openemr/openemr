<?php

/**
 * Tests for the user-action label on a SELECT summary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use OpenEMR\Common\Logging\Audit\AuditRequestLabel;
use PHPUnit\Framework\TestCase;

class AuditRequestLabelTest extends TestCase
{
    public function testQuerySecretsAreLeftOut(): void
    {
        $label = AuditRequestLabel::fromParts(
            'get',
            '/interface/main/finder/dynamic_finder.php?csrf_token_form=abc&searchparm=Smith&password=secret&access_token=abc&jwt=zzz&api_key=k&set_pid=1',
            null,
        );

        self::assertSame(
            'GET /interface/main/finder/dynamic_finder.php?searchparm=Smith&set_pid=1',
            $label->label,
        );
    }

    public function testCommandLabelUsesTheScriptName(): void
    {
        $label = AuditRequestLabel::fromParts(null, null, '/var/www/openemr/interface/cli/something.php');

        self::assertSame('cli something.php', $label->label);
    }

    public function testLongLabelsAreCapped(): void
    {
        $label = AuditRequestLabel::fromParts('GET', '/' . str_repeat('a', 600), null);

        self::assertSame(500, strlen($label->label));
        self::assertStringEndsWith('...', $label->label);
    }
}
