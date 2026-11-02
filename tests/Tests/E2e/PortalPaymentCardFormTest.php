<?php

/**
 * E2e test: the portal card payment forms post the patient's form_pid.
 *
 * portal/lib/paylib.php checks the posted form_pid against the portal
 * session patient on every payment branch (#13472). The Stripe and
 * Authorize.Net card forms are posted as they are by
 * portal_payment.stripe.js and portal_payment.authorizenet.js, so without a
 * form_pid field every card payment was refused with a 403
 * ("Error: Forbidden"). This logs in through the portal as a fixture patient
 * and checks the card form for each gateway carries that patient's pid. The
 * gateway is never called.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class PortalPaymentCardFormTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const PORTAL_LOGIN = 'e2e-portal-payment';
    private const PORTAL_PASSWORD = 'E2e-Portal-Payment-1';

    /** @var array<string, string|null> */
    private array $savedGlobals = [];
    private ?PortalPatientFixtureManager $portalFixtures = null;

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function gatewayProvider(): array
    {
        return [
            'Stripe' => ['Stripe', 'payment-form'],
            'Authorize.Net' => ['AuthorizeNet', 'paymentForm'],
        ];
    }

    #[Test]
    #[DataProvider('gatewayProvider')]
    #[Depends('testLoginAuthorized')]
    public function testCardFormPostsThePatientsPid(string $gateway, string $formName): void
    {
        try {
            $this->setGlobals([
                'payment_gateway' => $gateway,
                'portal_onsite_two_enable' => '1',
                'enforce_signin_email' => '0',
            ]);
            $this->portalFixtures = new PortalPatientFixtureManager();
            $this->portalFixtures->removePortalPatientFixtures();
            $patient = $this->portalFixtures->installPortalPatient(self::PORTAL_LOGIN, self::PORTAL_PASSWORD);

            $this->base();
            $this->portalLogin();
            $this->client->request('GET', '/portal/portal_payment.php');
            $this->client->waitFor('#invoiceForm');

            $formPids = $this->client->executeScript(
                'var form = document.forms[arguments[0]];'
                . ' return form ? new FormData(form).getAll("form_pid") : null;',
                [$formName]
            );
            $this->assertSame(
                [(string) $patient['pid']],
                $formPids,
                "The {$gateway} card form must post the portal patient's form_pid once"
            );
        } finally {
            try {
                if (isset($this->client)) {
                    $this->client->quit();
                }
            } finally {
                $this->portalFixtures?->removePortalPatientFixtures();
                $this->restoreGlobals();
            }
        }
    }

    private function portalLogin(): void
    {
        $this->client->request('GET', '/portal/index.php');
        $this->client->waitFor('#pass');
        $this->client->executeScript(
            'var pass = document.getElementById("pass");'
            . ' var form = pass.form;'
            . ' form.querySelector("#uname").value = arguments[0];'
            . ' pass.value = arguments[1];'
            . ' form.submit();',
            [self::PORTAL_LOGIN, self::PORTAL_PASSWORD]
        );
        $this->client->wait(30)->until(
            fn(): bool => str_contains($this->client->getCurrentURL(), '/portal/home.php'),
            'The portal login did not reach portal/home.php'
        );
    }

    /**
     * @param array<string, string> $values
     */
    private function setGlobals(array $values): void
    {
        foreach ($values as $name => $value) {
            $saved = QueryUtils::fetchSingleValue('SELECT gl_value FROM globals WHERE gl_name = ? AND gl_index = 0', 'gl_value', [$name]);
            $this->savedGlobals[$name] = is_string($saved) ? $saved : null;
            QueryUtils::sqlStatementThrowException('REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)', [$name, $value]);
        }
    }

    private function restoreGlobals(): void
    {
        foreach ($this->savedGlobals as $name => $value) {
            if ($value === null) {
                QueryUtils::sqlStatementThrowException('DELETE FROM globals WHERE gl_name = ? AND gl_index = 0', [$name]);
            } else {
                QueryUtils::sqlStatementThrowException('REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)', [$name, $value]);
            }
        }
        $this->savedGlobals = [];
    }
}
