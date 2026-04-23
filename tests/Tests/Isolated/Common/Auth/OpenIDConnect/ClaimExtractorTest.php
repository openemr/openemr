<?php

/**
 * Unit tests for OpenEMR\Common\Auth\OpenIDConnect\ClaimExtractor.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 Milan Zivkovic <zivkovic.milan@gmail.com>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect;

use InvalidArgumentException;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use OpenEMR\Common\Auth\OpenIDConnect\ClaimExtractor;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClaimSetEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClaimExtractorTest extends TestCase
{
    public function testDefaultClaimSetsAreRegistered(): void
    {
        $extractor = new ClaimExtractor();

        self::assertTrue($extractor->hasClaimSet('profile'));
        self::assertTrue($extractor->hasClaimSet('email'));
        self::assertTrue($extractor->hasClaimSet('address'));
        self::assertTrue($extractor->hasClaimSet('phone'));
    }

    public function testDefaultProfileClaimSetContainsExpectedClaims(): void
    {
        $extractor = new ClaimExtractor();
        $profile = $extractor->getClaimSet('profile');

        self::assertNotNull($profile);
        self::assertContains('name', $profile->getClaims());
        self::assertContains('family_name', $profile->getClaims());
        self::assertContains('given_name', $profile->getClaims());
        self::assertContains('preferred_username', $profile->getClaims());
        self::assertContains('locale', $profile->getClaims());
        self::assertContains('updated_at', $profile->getClaims());
    }

    public function testDefaultEmailClaimSetContainsEmailClaims(): void
    {
        $extractor = new ClaimExtractor();
        $email = $extractor->getClaimSet('email');

        self::assertNotNull($email);
        self::assertSame(['email', 'email_verified'], $email->getClaims());
    }

    public function testDefaultAddressClaimSetContainsAddressClaim(): void
    {
        $extractor = new ClaimExtractor();
        $address = $extractor->getClaimSet('address');

        self::assertNotNull($address);
        self::assertSame(['address'], $address->getClaims());
    }

    public function testDefaultPhoneClaimSetContainsPhoneClaims(): void
    {
        $extractor = new ClaimExtractor();
        $phone = $extractor->getClaimSet('phone');

        self::assertNotNull($phone);
        self::assertSame(['phone_number', 'phone_number_verified'], $phone->getClaims());
    }

    public function testHasClaimSetReturnsFalseForUnknownScope(): void
    {
        $extractor = new ClaimExtractor();

        self::assertFalse($extractor->hasClaimSet('openid'));
        self::assertFalse($extractor->hasClaimSet('nonce'));
        self::assertFalse($extractor->hasClaimSet(''));
    }

    public function testGetClaimSetReturnsNullForUnknownScope(): void
    {
        $extractor = new ClaimExtractor();

        self::assertNull($extractor->getClaimSet('openid'));
        self::assertNull($extractor->getClaimSet('nonce'));
    }

    public function testCustomClaimSetIsRegistered(): void
    {
        $customSet = new ClaimSetEntity('nonce', ['nonce']);
        $extractor = new ClaimExtractor([$customSet]);

        self::assertTrue($extractor->hasClaimSet('nonce'));
        self::assertSame($customSet, $extractor->getClaimSet('nonce'));
    }

    public function testMultipleCustomClaimSetsAreRegistered(): void
    {
        $nonce = new ClaimSetEntity('nonce', ['nonce']);
        $fhirUser = new ClaimSetEntity('fhirUser', ['fhirUser']);
        $extractor = new ClaimExtractor([$nonce, $fhirUser]);

        self::assertSame($nonce, $extractor->getClaimSet('nonce'));
        self::assertSame($fhirUser, $extractor->getClaimSet('fhirUser'));
    }

    public function testNonProtectedDuplicateScopeOverwritesSilently(): void
    {
        $first = new ClaimSetEntity('custom', ['a']);
        $second = new ClaimSetEntity('custom', ['b']);
        $extractor = new ClaimExtractor([$first, $second]);

        self::assertSame($second, $extractor->getClaimSet('custom'));
    }

    #[DataProvider('protectedScopeProvider')]
    public function testCustomClaimSetCannotRedefineProtectedScope(string $protectedScope): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            '%s is a protected scope and is pre-defined by the OpenID Connect specification.',
            $protectedScope,
        ));

        new ClaimExtractor([new ClaimSetEntity($protectedScope, ['override'])]);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function protectedScopeProvider(): array
    {
        return [
            'profile' => ['profile'],
            'email'   => ['email'],
            'address' => ['address'],
            'phone'   => ['phone'],
        ];
    }

    public function testExtractReturnsEmptyArrayWhenNoScopesMatch(): void
    {
        $extractor = new ClaimExtractor();

        $result = $extractor->extract(
            ['openid', 'nonce'],
            ['name' => 'Ada', 'email' => 'ada@example.com'],
        );

        self::assertSame([], $result);
    }

    public function testExtractReturnsEmptyArrayWhenClaimsEmpty(): void
    {
        $extractor = new ClaimExtractor();

        $result = $extractor->extract(['profile', 'email'], []);

        self::assertSame([], $result);
    }

    public function testExtractReturnsEmptyArrayWhenScopesEmpty(): void
    {
        $extractor = new ClaimExtractor();

        $result = $extractor->extract([], ['name' => 'Ada']);

        self::assertSame([], $result);
    }

    public function testExtractReturnsOnlyClaimsBelongingToRequestedScope(): void
    {
        $extractor = new ClaimExtractor();

        $result = $extractor->extract(
            ['email'],
            [
                'name'           => 'Ada',
                'email'          => 'ada@example.com',
                'email_verified' => true,
                'phone_number'   => '555-0100',
            ],
        );

        self::assertSame(
            ['email' => 'ada@example.com', 'email_verified' => true],
            $result,
        );
    }

    public function testExtractMergesClaimsAcrossMultipleScopes(): void
    {
        $extractor = new ClaimExtractor();

        $result = $extractor->extract(
            ['email', 'phone'],
            [
                'email'                 => 'ada@example.com',
                'email_verified'        => true,
                'phone_number'          => '555-0100',
                'phone_number_verified' => false,
                'unrelated'             => 'ignored',
            ],
        );

        self::assertSame(
            [
                'email'                 => 'ada@example.com',
                'email_verified'        => true,
                'phone_number'          => '555-0100',
                'phone_number_verified' => false,
            ],
            $result,
        );
    }

    public function testExtractAcceptsScopeEntityInstances(): void
    {
        $extractor = new ClaimExtractor();

        $emailScope = $this->createMock(ScopeEntityInterface::class);
        $emailScope->method('getIdentifier')->willReturn('email');

        $result = $extractor->extract(
            [$emailScope],
            ['email' => 'ada@example.com', 'email_verified' => true],
        );

        self::assertSame(
            ['email' => 'ada@example.com', 'email_verified' => true],
            $result,
        );
    }

    public function testExtractAcceptsMixedStringAndScopeEntityInputs(): void
    {
        $extractor = new ClaimExtractor();

        $phoneScope = $this->createMock(ScopeEntityInterface::class);
        $phoneScope->method('getIdentifier')->willReturn('phone');

        $result = $extractor->extract(
            ['email', $phoneScope],
            [
                'email'        => 'ada@example.com',
                'phone_number' => '555-0100',
            ],
        );

        self::assertSame(
            ['email' => 'ada@example.com', 'phone_number' => '555-0100'],
            $result,
        );
    }

    public function testExtractSkipsScopesWithoutMatchingClaims(): void
    {
        $extractor = new ClaimExtractor();

        $result = $extractor->extract(
            ['email', 'phone'],
            ['email' => 'ada@example.com'],
        );

        self::assertSame(['email' => 'ada@example.com'], $result);
    }

    public function testExtractHonoursCustomClaimSets(): void
    {
        $extractor = new ClaimExtractor([
            new ClaimSetEntity('fhirUser', ['fhirUser']),
        ]);

        $result = $extractor->extract(
            ['fhirUser'],
            [
                'fhirUser' => 'https://example.org/fhir/Practitioner/1',
                'name'     => 'Ada',
            ],
        );

        self::assertSame(
            ['fhirUser' => 'https://example.org/fhir/Practitioner/1'],
            $result,
        );
    }

    public function testExtractDeduplicatesClaimsAcrossDuplicateScopes(): void
    {
        $extractor = new ClaimExtractor();

        $result = $extractor->extract(
            ['email', 'email'],
            ['email' => 'ada@example.com', 'email_verified' => true],
        );

        self::assertSame(
            ['email' => 'ada@example.com', 'email_verified' => true],
            $result,
        );
    }
}
