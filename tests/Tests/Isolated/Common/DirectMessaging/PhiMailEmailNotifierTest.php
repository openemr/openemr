<?php

/**
 * Isolated tests for emailing phiMail alerts to the practice.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\PhiMailEmailNotifier;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

final class PhiMailEmailNotifierTest extends TestCase
{
    public function testSendsFromAndToThePracticeAddress(): void
    {
        $mailer = $this->createMock(PHPMailer::class);
        $mailer->expects(self::once())->method('addAddress')->with('practice@example.org');
        $mailer->expects(self::once())->method('send')->willReturn(true);
        $notifier = new PhiMailEmailNotifier('practice@example.org', static fn(): PHPMailer => $mailer);

        $notifier->notify('Direct Messaging Send Failure.', 'Sent to: someone');

        self::assertSame('practice@example.org', $mailer->From);
        self::assertSame('phiMail Gateway', $mailer->FromName);
        self::assertSame('Direct Messaging Send Failure.', $mailer->Subject);
        self::assertSame('Sent to: someone', $mailer->Body);
    }

    public function testSendsNothingWithoutAnAddress(): void
    {
        $notifier = new PhiMailEmailNotifier('', static fn(): never => throw new \LogicException('built a mailer with no address'));

        $notifier->notify('subject', 'body');

        $this->addToAssertionCount(1);
    }
}
