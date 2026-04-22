<?php

/**
 * PasswordVerifyTest.
 *
 * Test to ensure works with following hashes
 *   - pre OpenEMR 5.0.0 $2a$05 hashes (removed these tests on 3/9/23 since no longer work in modern operating systems)
 *   - at and post OpenEMR 5.0.0 $2a$05 hashes
 *   - standard OpenEMR PASSWORD_BCRYPT hashes (at and post OpenEMR 6.0.0)
 *   - standard OpenEMR PASSWORD_ARGON2I hashes (at and post OpenEMR 6.0.0)
 *   - standard OpenEMR PASSWORD_ARGON2ID hashes (at and post OpenEMR 6.0.0)
 *   - standard OpenEMR CRYPT_SHA512 hashes (at and post OpenEMR 6.0.0)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit;

use OpenEMR\Common\Auth\AuthHash;
use PHPUnit\Framework\TestCase;

class PasswordVerifyTest extends TestCase
{
    public function testBlankPassword(): void
    {
        $pass = '';
        $hash = '$2y$10$Fikd4N/8KfwNjU0xruzckO5R068vRCg/OR47kIlg5B9FAHpAk9s2e';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testBlankHash(): void
    {
        $pass = 'pass';
        $hash = '';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testBlankPasswordAndHash(): void
    {
        $pass = '';
        $hash = '';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testMalformedHash(): void
    {
        $pass = 'pass';
        $hash = 'sgdf55';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    // Legacy are hashes created at and after OpenEMR 5.0.0 (and prior to 6.0.0)
    //  (note that 'Old Legacy' hashes prior to OpenEMR 5.0.0 do not work in modern
    //   OpenEMR versions)
    public function testLegacyBcryptCorrectPasswordOne(): void
    {
        $pass = 'pass';
        $hash = '$2a$05$.hH4Godes3dORmHjOjtXXekQPf2n5tQsw2H/ahwsBECLA/QCgWRS.';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testLegacyBcryptIncorrectPasswordOne(): void
    {
        $pass = 'wrongpass';
        $hash = '$2a$05$.hH4Godes3dORmHjOjtXXekQPf2n5tQsw2H/ahwsBECLA/QCgWRS.';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testLegacyBcryptCorrectPasswordTwo(): void
    {
        $pass = 'pass';
        $hash = '$2a$05$MKtnxYsfFPlb2mOW7Qzq2Oz61S26s5E80Yd60lKdX4Wy3PBdEufNu';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testLegacyBcryptIncorrectPasswordTwo(): void
    {
        $pass = 'wrongpass';
        $hash = '$2a$05$MKtnxYsfFPlb2mOW7Qzq2Oz61S26s5E80Yd60lKdX4Wy3PBdEufNu';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testLegacyBcryptCorrectPasswordThree(): void
    {
        $pass = 'receptionist';
        $hash = '$2a$05$bHD9eIJ0dc6fISnNdqJtbe2/LVUPWhWGSuJOxRGab/NaUZYV3vqBO';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testLegacyBcryptIncorrectPasswordThree(): void
    {
        $pass = 'wrongpass';
        $hash = '$2a$05$bHD9eIJ0dc6fISnNdqJtbe2/LVUPWhWGSuJOxRGab/NaUZYV3vqBO';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    // Standard are hashes created at and after OpenEMR 6.0.0
    public function testStandardBcryptCorrectPasswordOne(): void
    {
        $pass = 'pass';
        $hash = '$2y$10$Fikd4N/8KfwNjU0xruzckO5R068vRCg/OR47kIlg5B9FAHpAk9s2e';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardBcryptIncorrectPasswordOne(): void
    {
        $pass = 'wrongpass';
        $hash = '$2y$10$Fikd4N/8KfwNjU0xruzckO5R068vRCg/OR47kIlg5B9FAHpAk9s2e';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardBcryptCorrectPasswordTwo(): void
    {
        $pass = 'pass';
        $hash = '$2y$10$jEdmgbdVbEn4XuSKRfS8yOBD13EVqRu/on2UFhwfLgcGo6OZgmkYG';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardBcryptIncorrectPasswordTwo(): void
    {
        $pass = 'wrongpass';
        $hash = '$2y$10$jEdmgbdVbEn4XuSKRfS8yOBD13EVqRu/on2UFhwfLgcGo6OZgmkYG';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardBcryptCorrectPasswordThree(): void
    {
        $pass = 'pass';
        $hash = '$2y$10$1AdOj/O7G6kEZrFgXt5BvOukG.gCkpTung.676ajdYzDmjU3HPILu';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardBcryptIncorrectPasswordThree(): void
    {
        $pass = 'wrongpass';
        $hash = '$2y$10$1AdOj/O7G6kEZrFgXt5BvOukG.gCkpTung.676ajdYzDmjU3HPILu';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2iCorrectPasswordOne(): void
    {
        $pass = 'pass';
        $hash = '$argon2i$v=19$m=65536,t=4,p=1$djZ4NjhPMFJWd1Z0bjNaUg$2YH7/1DA+pCsFduPZZ2V0xUUorir3b6UbEuHcufD0EY';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2iIncorrectPasswordOne(): void
    {
        $pass = 'wrongpass';
        $hash = '$argon2i$v=19$m=65536,t=4,p=1$djZ4NjhPMFJWd1Z0bjNaUg$2YH7/1DA+pCsFduPZZ2V0xUUorir3b6UbEuHcufD0EY';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2iCorrectPasswordTwo(): void
    {
        $pass = 'pass';
        $hash = '$argon2i$v=19$m=65536,t=4,p=1$NXpYa2ZyVWZrbHdJbWpqbg$oL/S2VMqT9ovD4hc3sTWaXw1uZ2BS4FI57AlNtFcPKI';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2iIncorrectPasswordTwo(): void
    {
        $pass = 'wrongpass';
        $hash = '$argon2i$v=19$m=65536,t=4,p=1$NXpYa2ZyVWZrbHdJbWpqbg$oL/S2VMqT9ovD4hc3sTWaXw1uZ2BS4FI57AlNtFcPKI';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2iCorrectPasswordThree(): void
    {
        $pass = 'pass';
        $hash = '$argon2i$v=19$m=65536,t=4,p=1$dnhsZ0h3UFpGQ3NydHo3Tw$bp4YjHK97kHcMgEq6K41HAODUJ4ypvEM4xIrvCnodlc';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2iIncorrectPasswordThree(): void
    {
        $pass = 'wrongpass';
        $hash = '$argon2i$v=19$m=65536,t=4,p=1$dnhsZ0h3UFpGQ3NydHo3Tw$bp4YjHK97kHcMgEq6K41HAODUJ4ypvEM4xIrvCnodlc';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2idCorrectPasswordOne(): void
    {
        $pass = 'pass';
        $hash = '$argon2id$v=19$m=65536,t=4,p=1$V1lLdWVRMXpoaEVpTkFjVA$qJWxvShuN64245CAvn5AGaNThmKJkcJx8lZk42YXc0o';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2idIncorrectPasswordOne(): void
    {
        $pass = 'wrongpass';
        $hash = '$argon2id$v=19$m=65536,t=4,p=1$V1lLdWVRMXpoaEVpTkFjVA$qJWxvShuN64245CAvn5AGaNThmKJkcJx8lZk42YXc0o';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2idCorrectPasswordTwo(): void
    {
        $pass = 'pass';
        $hash = '$argon2id$v=19$m=65536,t=4,p=1$R1ltOUpkS2Z6YlBTcExQag$RlO+SqO8FRaZu1Luhv8uUOBzRXFLrCaqwDC3TLhW6nk';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2idIncorrectPasswordTwo(): void
    {
        $pass = 'wrongpass';
        $hash = '$argon2id$v=19$m=65536,t=4,p=1$R1ltOUpkS2Z6YlBTcExQag$RlO+SqO8FRaZu1Luhv8uUOBzRXFLrCaqwDC3TLhW6nk';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2idCorrectPasswordThree(): void
    {
        $pass = 'pass';
        $hash = '$argon2id$v=19$m=65536,t=4,p=1$UlhNaFczNE1XZXp0RmgvZw$d7AA4Kd2ac8gSnHi0ra3ODWcMA70fWyWjVcrOHPorKE';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardArgon2idIncorrectPasswordThree(): void
    {
        $pass = 'wrongpass';
        $hash = '$argon2id$v=19$m=65536,t=4,p=1$UlhNaFczNE1XZXp0RmgvZw$d7AA4Kd2ac8gSnHi0ra3ODWcMA70fWyWjVcrOHPorKE';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardSha512CorrectPasswordOne(): void
    {
        $pass = 'pass';
        $hash = '$6$rounds=100000$qbXwbkFptuwCMIi5$c86qZe.lyrjQiMCo6T/vSD1w3AUdrjBUMUyU0Tfyo6xKuC7Fz1pBHht08OE5aFyf3/4Hi2kX6Y1rpdWB1OZuf/';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardSha512IncorrectPasswordOne(): void
    {
        $pass = 'wrongpass';
        $hash = '$6$rounds=100000$qbXwbkFptuwCMIi5$c86qZe.lyrjQiMCo6T/vSD1w3AUdrjBUMUyU0Tfyo6xKuC7Fz1pBHht08OE5aFyf3/4Hi2kX6Y1rpdWB1OZuf/';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardSha512CorrectPasswordTwo(): void
    {
        $pass = 'pass';
        $hash = '$6$rounds=100000$WL7zSdykUtnTz6Jd$mOZ7rI7JRnl/r8rnoWNbVuRDbIcw8ROAryA.yeOPE2EE6qxocg.8MFM1VQp53Q1qS2r9VcyOLRUbLAFIZW3yZ1';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardSha512IncorrectPasswordTwo(): void
    {
        $pass = 'wrongpass';
        $hash = '$6$rounds=100000$WL7zSdykUtnTz6Jd$mOZ7rI7JRnl/r8rnoWNbVuRDbIcw8ROAryA.yeOPE2EE6qxocg.8MFM1VQp53Q1qS2r9VcyOLRUbLAFIZW3yZ1';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }

    public function testStandardSha512CorrectPasswordThree(): void
    {
        $pass = 'pass';
        $hash = '$6$rounds=100000$9EhQm5xpBIUgJIFx$lB913XUfwK4/1K1sSGKtaF9JXvLbRvtFvhp5UAp4z7S2OEK4HwhFqQvP/mzVO55NXtxCx5mi08SPU6VQgWfXI/';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = true;
        $this->assertTrue($result === $expected);
    }

    public function testStandardSha512IncorrectPasswordThree(): void
    {
        $pass = 'wrongpass';
        $hash = '$6$rounds=100000$9EhQm5xpBIUgJIFx$lB913XUfwK4/1K1sSGKtaF9JXvLbRvtFvhp5UAp4z7S2OEK4HwhFqQvP/mzVO55NXtxCx5mi08SPU6VQgWfXI/';
        $result = AuthHash::passwordVerify($pass, $hash);
        $expected = false;
        $this->assertTrue($result === $expected);
    }
}
