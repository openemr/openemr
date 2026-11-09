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
use Symfony\Component\Filesystem\Filesystem;
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
     * @return array<string, array{int, ?int, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function linkProvider(): array
    {
        return [
            'current patient, no token' => [self::SESSION_PID, null, 'none', true],
            'current patient and encounter, no token' => [self::SESSION_PID, self::SESSION_ENCOUNTER, 'none', true],
            'other patient, no token' => [22, null, 'none', false],
            'other patient, wrong token' => [22, null, 'wrong', false],
            'other patient, non-string token' => [22, null, 'array', false],
            'other patient, valid token' => [22, null, 'valid', true],
            'other encounter, no token' => [self::SESSION_PID, 501, 'none', false],
            'other encounter, valid token' => [self::SESSION_PID, 501, 'valid', true],
            'zero encounter, no token' => [self::SESSION_PID, 0, 'none', false],
            'zero encounter, valid token' => [self::SESSION_PID, 0, 'valid', true],
            'negative encounter, no token' => [self::SESSION_PID, -1, 'none', false],
            'other patient and encounter, valid token' => [22, 900, 'valid', true],
        ];
    }

    #[DataProvider('linkProvider')]
    public function testMayLinkSwitchContext(int $pid, ?int $encounter, string $token, bool $allowed): void
    {
        $this->assertSame($allowed, PatientSessionUtil::mayLinkSwitchContext($pid, $encounter, $this->token($token)));
    }

    public function testNoPatientOpenStillNeedsATokenToOpenOne(): void
    {
        $this->session->remove('pid');

        $this->assertFalse(PatientSessionUtil::mayLinkSwitchContext(22, null, null));
        $this->assertTrue(PatientSessionUtil::mayLinkSwitchContext(22, null, $this->token('valid')));
    }

    public function testRequireCsrfLetsTheCurrentChartReopen(): void
    {
        $request = Request::create('/demographics.php', 'GET', ['set_pid' => self::SESSION_PID]);

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

    public function testRequireCsrfAcceptsAnArrayTokenWhenReopeningTheCurrentChart(): void
    {
        $request = Request::create('/demographics.php', 'GET', [
            'set_pid' => self::SESSION_PID,
            'csrf_token_form' => ['not', 'a', 'string'],
        ]);

        PatientSessionUtil::requireCsrfForLinkSwitch($request, self::SESSION_PID);
        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{list<string>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function childProcessProvider(): array
    {
        return [
            'forged switch is refused and keeps the open chart' => [['22'], 'Authentication Error pid=11'],
            'array token is refused like a missing one' => [['22', 'array-token'], 'Authentication Error pid=11'],
            'token-bearing switch continues' => [['22', 'with-token'], 'allowed pid=22'],
        ];
    }

    /**
     * @param list<string> $arguments
     */
    #[DataProvider('childProcessProvider')]
    public function testSwitchInAChildProcess(array $arguments, string $output): void
    {
        $process = new Process([PHP_BINARY, self::FIXTURE, ...$arguments]);
        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertSame($output, $process->getOutput());
    }

    /**
     * demographics.php needs the full bootstrap, so check its source: the
     * guard must run before the setpid() that writes the session.
     */
    public function testDemographicsChecksTheTokenBeforeSwitching(): void
    {
        $source = (new Filesystem())->readFile(dirname(__DIR__, 5) . '/interface/patient_file/summary/demographics.php');
        $parts = explode('PatientSessionUtil::requireCsrfForLinkSwitch($request, $setPid, $setEncounterId > 0 ? $setEncounterId : null);', $source);
        $this->assertCount(2, $parts, 'demographics.php should call the guard exactly once');
        [$beforeGuard, $afterGuard] = $parts;
        $this->assertStringNotContainsString('setpid($setPid);', $beforeGuard);
        $this->assertStringContainsString('setpid($setPid);', $afterGuard);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function guardedPageProvider(): array
    {
        return [
            'encounter_top' => ['interface/patient_file/encounter/encounter_top.php'],
            'pnotes_full' => ['interface/patient_file/summary/pnotes_full.php'],
            'pnotes_full_add' => ['interface/patient_file/summary/pnotes_full_add.php'],
            'orders_results' => ['interface/orders/orders_results.php'],
            'demographics_full' => ['interface/patient_file/summary/demographics_full.php'],
            'insurance_edit' => ['interface/patient_file/summary/insurance_edit.php'],
            'encounters' => ['interface/patient_file/history/encounters.php'],
            'history_sdoh' => ['interface/patient_file/history/history_sdoh.php'],
            'history_sdoh_list' => ['interface/patient_file/history/history_sdoh_list.php'],
            'history_sdoh_health_concerns' => ['interface/patient_file/history/history_sdoh_health_concerns.php'],
            'eye_mag a_issue' => ['interface/forms/eye_mag/a_issue.php'],
            'eye_mag new' => ['interface/forms/eye_mag/new.php'],
            'misc_billing_options new' => ['interface/forms/misc_billing_options/new.php'],
            'ub04_form' => ['interface/billing/ub04_form.php'],
        ];
    }

    /**
     * These pages need the full bootstrap, so check their source: the guard
     * must run before the first call that writes the session.
     */
    #[DataProvider('guardedPageProvider')]
    public function testPageChecksTheTokenBeforeSwitching(string $path): void
    {
        $source = (new Filesystem())->readFile(dirname(__DIR__, 5) . '/' . $path);
        $guard = null;
        $firstWrite = null;
        foreach (self::functionCalls($source) as $position => $name) {
            if ($name === 'requireCsrfForLinkSwitch') {
                $guard ??= $position;
            } elseif (in_array(strtolower($name), ['setpid', 'setencounter', 'setsession'], true)) {
                $firstWrite ??= $position;
            }
        }

        $this->assertIsInt($guard, "{$path} should call the guard");
        $this->assertIsInt($firstWrite, "{$path} should switch the session patient or encounter");
        $this->assertLessThan($firstWrite, $guard, "{$path} should call the guard before it switches");
    }

    /**
     * Names of the functions and methods called in $source, in order.
     *
     * @return list<string>
     */
    private static function functionCalls(string $source): array
    {
        $tokens = array_values(array_filter(
            \PhpToken::tokenize($source),
            static fn (\PhpToken $token): bool => !$token->isIgnorable(),
        ));
        $calls = [];
        foreach ($tokens as $index => $token) {
            if ($token->is(T_STRING) && ($tokens[$index + 1] ?? null)?->text === '(') {
                $calls[] = $token->text;
            }
        }
        return $calls;
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
