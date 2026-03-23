<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators\Checker;

use OpenEMR\Validators\Checker\EmailChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('validator')]
#[Group('checker')]
#[CoversClass(EmailChecker::class)]
#[CoversMethod(EmailChecker::class, 'isValidEmail')]
class EmailCheckerIsolatedTest extends TestCase
{
    #[Test]
    #[DataProvider('isValidEmailDataProvider')]
    public function isValidEmailTest(
        string $email,
        bool $expectedIsValidEmail,
    ): void {
        $emailChecker = new EmailChecker();

        $this->assertEquals(
            $expectedIsValidEmail,
            $emailChecker->isValidEmail($email),
        );
    }

    public static function isValidEmailDataProvider(): iterable
    {
        yield 'Plain' => ['test@example.com', true];
        yield 'With dot' => ['user.name@domain.com', true];
        yield 'With plus sign' => ['user+tag@example.org', true];
        yield 'Subdomain' => ['valid.email@subdomain.example.com', true];

        yield 'Dot and plus sign' => ['example.t1+t1@gmail.com', true];
        yield 'Special symbols' => ['ñoñó1234@server.com', true];

        yield 'No at sign' => ['invalid-email', false];
        yield 'No username' => ['@domain.com', false];
        yield 'No domain' => ['user@', false];
        yield 'No tld' => ['user@localhost', false]; // this is a valid email per RFC, but we DO NOT allow it in OpenEMR
        yield 'Space at username' => ['spaces in@email.com', false];
        yield 'Special symbols at domain' => ['ñoñó1234@ñoñó1234example.com', false];
    }
}
