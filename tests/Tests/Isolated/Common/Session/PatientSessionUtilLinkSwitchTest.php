<?php

/**
 * Tests for the CSRF gate on patient/encounter switches made by GET links.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Process\Process;

final class PatientSessionUtilLinkSwitchTest extends TestCase
{
    private const FIXTURE = __DIR__ . '/fixtures/patient-link-switch-denial.php';
    private const SESSION_PID = 11;
    private const SESSION_ENCOUNTER = 500;

    private SessionWrapperFactory $factory;
    private ?SessionInterface $savedSession = null;
    private Session $session;

    protected function setUp(): void
    {
        $this->factory = SessionWrapperFactory::getInstance();
        $activeSession = (new \ReflectionClass($this->factory))->getProperty('activeSession')->getValue($this->factory);
        $this->savedSession = $activeSession instanceof SessionInterface ? $activeSession : null;

        $this->session = new Session(new MockArraySessionStorage());
        $this->session->set('pid', self::SESSION_PID);
        $this->session->set('encounter', self::SESSION_ENCOUNTER);
        CsrfUtils::setupCsrfKey($this->session);
        $this->factory->setActiveSession($this->session);
    }

    protected function tearDown(): void
    {
        (new \ReflectionClass($this->factory))->getProperty('activeSession')->setValue($this->factory, $this->savedSession);
    }

    /**
     * @return array<string, array{int, int, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function linkProvider(): array
    {
        return [
            'current patient, no token' => [self::SESSION_PID, 0, 'none', true],
            'current patient and encounter, no token' => [self::SESSION_PID, self::SESSION_ENCOUNTER, 'none', true],
            'other patient, no token' => [22, 0, 'none', false],
            'other patient, wrong token' => [22, 0, 'wrong', false],
            'other patient, non-string token' => [22, 0, 'array', false],
            'other patient, valid token' => [22, 0, 'valid', true],
            'other encounter, no token' => [self::SESSION_PID, 501, 'none', false],
            'other encounter, valid token' => [self::SESSION_PID, 501, 'valid', true],
            'other patient and encounter, valid token' => [22, 900, 'valid', true],
        ];
    }

    #[DataProvider('linkProvider')]
    public function testMayLinkSwitchContext(int $pid, int $encounter, string $token, bool $allowed): void
    {
        $this->assertSame($allowed, PatientSessionUtil::mayLinkSwitchContext($pid, $encounter, $this->token($token)));
    }

    public function testNoPatientOpenStillNeedsATokenToOpenOne(): void
    {
        $this->session->remove('pid');

        $this->assertFalse(PatientSessionUtil::mayLinkSwitchContext(22, 0, null));
        $this->assertTrue(PatientSessionUtil::mayLinkSwitchContext(22, 0, $this->token('valid')));
    }

    public function testRequireCsrfLetsTheCurrentChartReopen(): void
    {
        $request = Request::create('/demographics.php', 'GET', ['set_pid' => (string) self::SESSION_PID]);

        PatientSessionUtil::requireCsrfForLinkSwitch($request, self::SESSION_PID);
        $this->addToAssertionCount(1);
    }

    public function testRequireCsrfReadsTheTokenFromTheQuery(): void
    {
        $request = Request::create('/demographics.php', 'GET', [
            'set_pid' => '22',
            'csrf_token_form' => CsrfUtils::collectCsrfToken($this->session),
        ]);

        PatientSessionUtil::requireCsrfForLinkSwitch($request, 22);
        $this->addToAssertionCount(1);
    }

    public function testForgedSwitchEndsTheRequest(): void
    {
        $process = new Process([PHP_BINARY, self::FIXTURE, '22']);
        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertSame('Authentication Error', $process->getOutput());
    }

    public function testTokenBearingSwitchContinues(): void
    {
        $process = new Process([PHP_BINARY, self::FIXTURE, '22', 'with-token']);
        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertSame('allowed', $process->getOutput());
    }

    private function token(string $kind): mixed
    {
        return match ($kind) {
            'none' => null,
            'wrong' => 'not-the-session-token',
            'array' => ['not', 'a', 'string'],
            'valid' => CsrfUtils::collectCsrfToken($this->session),
            default => throw new \InvalidArgumentException('Unknown token kind'),
        };
    }
}
