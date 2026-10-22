<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx;

use OpenEMR\Rx\CredentialValidator;
use PHPUnit\Framework\TestCase;

final class CredentialValidatorTest extends TestCase
{
    public function testHasRequiredCredentialsReturnsTrueWhenAllPresent(): void
    {
        $globals = [
            'erx_account_partner_name' => 'partner',
            'erx_account_name' => 'account',
            'erx_account_password' => 'secret',
        ];

        $this->assertTrue(CredentialValidator::hasRequiredCredentials($globals));
    }

    public function testHasRequiredCredentialsReturnsFalseWhenAnyMissing(): void
    {
        $globals = [
            'erx_account_partner_name' => 'partner',
            'erx_account_name' => '',
            'erx_account_password' => 'secret',
        ];

        $this->assertFalse(CredentialValidator::hasRequiredCredentials($globals));
    }

    public function testGetMissingCredentialsReturnsOnlyEmptyFields(): void
    {
        $globals = [
            'erx_account_partner_name' => 'partner',
            'erx_account_name' => '',
        ];

        $missing = CredentialValidator::getMissingCredentials($globals);

        $this->assertSame(
            [
                'erx_account_name' => 'Account Name',
                'erx_account_password' => 'Account Password',
            ],
            $missing
        );
    }

    public function testIsAuthenticationErrorMatchesErrorElementText(): void
    {
        $xml = '<?xml version="1.0"?><Response><Error>Invalid credentials supplied</Error></Response>';

        $this->assertTrue(CredentialValidator::isAuthenticationError($xml));
    }

    public function testIsAuthenticationErrorIgnoresNonErrorText(): void
    {
        $xml = '<?xml version="1.0"?><Response><Status>OK</Status><Note>credentials on file</Note></Response>';

        $this->assertFalse(CredentialValidator::isAuthenticationError($xml));
    }

    public function testIsAuthenticationErrorReturnsFalseForEmptyOrInvalidXml(): void
    {
        $this->assertFalse(CredentialValidator::isAuthenticationError(''));
        $this->assertFalse(CredentialValidator::isAuthenticationError('not-xml'));
    }
}
