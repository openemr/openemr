<?php
declare(strict_types=1);

use \ParagonIE\ConstantTime\Hex;
use \ParagonIE\MultiFactor\OTP\TOTP;

/**
 * Class TOTPTest
 */
class TOPTTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test vectors from RFC 6238
     */
    public function testTOTP()
    {
        $seed = Hex::decode(
            "3132333435363738393031323334353637383930"
        );
        $seed32 = Hex::decode(
            "3132333435363738393031323334353637383930" .
            "313233343536373839303132"
        );
        // Seed for HMAC-SHA512 - 64 bytes
        $seed64 = Hex::decode(
            "3132333435363738393031323334353637383930" .
            "3132333435363738393031323334353637383930" .
            "3132333435363738393031323334353637383930" .
            "31323334"
        );

        $testVectors = [
            [
                'time' =>
                    59,
                'outputs' => [
                    'sha1' =>
                        '94287082',
                    'sha256' =>
                        '46119246',
                    'sha512' =>
                        '90693936'
                ]
            ], [
                'time' =>
                    1111111109,
                'outputs' => [
                    'sha1' =>
                        '07081804',
                    'sha256' =>
                        '68084774',
                    'sha512' =>
                        '25091201'
                ]
            ], [
                'time' =>
                    1111111111,
                'outputs' => [
                    'sha1' =>
                        '14050471',
                    'sha256' =>
                        '67062674',
                    'sha512' =>
                        '99943326'
                ]
            ], [
                'time' =>
                    1234567890,
                'outputs' => [
                    'sha1' =>
                        '89005924',
                    'sha256' =>
                        '91819424',
                    'sha512' =>
                        '93441116'
                ]
            ], [
                'time' =>
                    2000000000,
                'outputs' => [
                    'sha1' =>
                        '69279037',
                    'sha256' =>
                        '90698825',
                    'sha512' =>
                        '38618901'
                ]
            ]
        ];
        if (PHP_INT_SIZE > 4) {
            // 64-bit systems only:
            $testVectors[] = [
                'time' =>
                    20000000000,
                'outputs' => [
                    'sha1' =>
                        '65353130',
                    'sha256' =>
                        '77737706',
                    'sha512' =>
                        '47863826'
                ]
            ];
        }

        $sha1 = new TOTP(0, 30, 8, 'sha1');
        $sha256 = new TOTP(0, 30, 8, 'sha256');
        $sha512 = new TOTP(0, 30, 8, 'sha512');
        
        foreach ($testVectors as $test) {
            $this->assertSame(
                $test['outputs']['sha1'],
                $sha1->getCode($seed, $test['time']),
                $test['time']
            );

            $this->assertSame(
                $test['outputs']['sha256'],
                $sha256->getCode($seed32, $test['time']),
                $test['time']
            );

            $this->assertSame(
                $test['outputs']['sha512'],
                $sha512->getCode($seed64, $test['time']),
                $test['time']
            );
        }
    }
}
