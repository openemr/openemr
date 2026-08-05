<?php

/**
 * Isolated tests for AuthEvent value object.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aanand Sreekumaran Nair Jayakumari
 * @copyright Copyright (c) 2026 Aanand Sreekumaran Nair Jayakumari
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth;

use OpenEMR\Common\Auth\AuthEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AuthEventTest extends TestCase
{
    public function testConstructorStoresValue(): void
    {
        $event = new AuthEvent('login');
        $this->assertSame('login', $event->value);
    }

    public function testConstructorThrowsOnEmptyString(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('AuthEvent value cannot be empty');
        new AuthEvent('');
    }

    public function testLoginFactory(): void
    {
        $event = AuthEvent::login();
        $this->assertSame('login', $event->value);
    }

    public function testMfaFactory(): void
    {
        $event = AuthEvent::mfa();
        $this->assertSame('mfa', $event->value);
    }

    public function testPasswordFactory(): void
    {
        $event = AuthEvent::password();
        $this->assertSame('password', $event->value);
    }

    public function testLogoutFactory(): void
    {
        $event = AuthEvent::logout();
        $this->assertSame('logout', $event->value);
    }

    public function testAuthFactory(): void
    {
        $event = AuthEvent::auth();
        $this->assertSame('auth', $event->value);
    }

    public function testEachFactoryReturnsDistinctValue(): void
    {
        $values = [
            AuthEvent::login()->value,
            AuthEvent::mfa()->value,
            AuthEvent::password()->value,
            AuthEvent::logout()->value,
            AuthEvent::auth()->value,
        ];
        $this->assertCount(count($values), array_unique($values), 'Each factory must return a distinct value');
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonEmptyValueProvider(): array
    {
        return [
            'arbitrary non-empty string' => ['some-event'],
            'single character'           => ['x'],
            'whitespace only'            => [' '],
        ];
    }

    #[DataProvider('nonEmptyValueProvider')]
    public function testConstructorAcceptsAnyNonEmptyString(string $value): void
    {
        $event = new AuthEvent($value);
        $this->assertSame($value, $event->value);
    }
}
