<?php

/**
 * Tests for the CSRF gates on the therapy groups module.
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
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Session\TherapyGroupSessionUtil;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Process\Process;

final class TherapyGroupSessionUtilTest extends TestCase
{
    private const FIXTURE = __DIR__ . '/fixtures/therapy-group-guard-denial.php';
    private const CONTROLLERS = '/interface/therapy_groups/therapy_groups_controllers/';
    private const SESSION_GROUP = 7;

    private SessionWrapperFactory $factory;
    private ?SessionInterface $savedSession = null;
    private Session $session;

    protected function setUp(): void
    {
        $this->factory = SessionWrapperFactory::getInstance();
        $activeSession = (new \ReflectionClass($this->factory))->getProperty('activeSession')->getValue($this->factory);
        $this->savedSession = $activeSession instanceof SessionInterface ? $activeSession : null;

        $this->session = new Session(new MockArraySessionStorage());
        $this->session->set('pid', 0);
        $this->session->set('therapy_group', self::SESSION_GROUP);
        CsrfUtils::setupCsrfKey($this->session);
        $this->factory->setActiveSession($this->session);
    }

    protected function tearDown(): void
    {
        (new \ReflectionClass($this->factory))->getProperty('activeSession')->setValue($this->factory, $this->savedSession);
    }

    /**
     * @return array<string, array{mixed, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function storedGroupProvider(): array
    {
        return [
            'int' => [7, 7],
            'digit string from an older session' => ['7', 7],
            'missing' => [null, null],
            'not a number' => ['from_session', null],
            'not a whole number' => ['7.5', null],
            'exponent' => ['1e1', null],
        ];
    }

    #[DataProvider('storedGroupProvider')]
    public function testGetGroupId(mixed $stored, ?int $expected): void
    {
        $this->session->set('therapy_group', $stored);

        $this->assertSame($expected, TherapyGroupSessionUtil::getGroupId());
    }

    /**
     * @return array<string, array{array<string, mixed>, array<string, mixed>, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function tokenSourceProvider(): array
    {
        return [
            'query' => [['csrf_token_form' => 'from-query'], [], 'from-query'],
            'posted form' => [[], ['csrf_token_form' => 'from-form'], 'from-form'],
            'query ahead of posted form' => [['csrf_token_form' => 'from-query'], ['csrf_token_form' => 'from-form'], 'from-query'],
            'array' => [['csrf_token_form' => ['not', 'a', 'string']], [], null],
            'none' => [[], [], null],
        ];
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $form
     */
    #[DataProvider('tokenSourceProvider')]
    public function testCsrfTokenFrom(array $query, array $form, ?string $expected): void
    {
        $request = new Request($query, $form);

        $this->assertSame($expected, TherapyGroupSessionUtil::csrfTokenFrom($request));
    }

    /**
     * @return array<string, array{int, int, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function linkProvider(): array
    {
        return [
            'current group, no token' => [self::SESSION_GROUP, 0, 'none', true],
            'other group, no token' => [8, 0, 'none', false],
            'other group, wrong token' => [8, 0, 'wrong', false],
            'other group, valid token' => [8, 0, 'valid', true],
            'current group over an open patient, no token' => [self::SESSION_GROUP, 11, 'none', false],
            'current group over an open patient, valid token' => [self::SESSION_GROUP, 11, 'valid', true],
        ];
    }

    #[DataProvider('linkProvider')]
    public function testMayLinkSwitchGroup(int $groupId, int $sessionPid, string $token, bool $allowed): void
    {
        $this->session->set('pid', $sessionPid);

        $this->assertSame($allowed, TherapyGroupSessionUtil::mayLinkSwitchGroup($groupId, $this->token($token)));
    }

    public function testNoGroupOpenStillNeedsATokenToOpenOne(): void
    {
        $this->session->remove('therapy_group');

        $this->assertFalse(TherapyGroupSessionUtil::mayLinkSwitchGroup(8, null));
        $this->assertTrue(TherapyGroupSessionUtil::mayLinkSwitchGroup(8, $this->token('valid')));
    }

    public function testRequireCsrfLetsTheCurrentGroupReopen(): void
    {
        $request = Request::create('/therapy_groups/index.php', 'GET', ['group_id' => self::SESSION_GROUP]);

        TherapyGroupSessionUtil::requireCsrfForGroupSwitch($request, self::SESSION_GROUP);
        $this->addToAssertionCount(1);
    }

    public function testRequireCsrfAcceptsAnArrayTokenWhenReopeningTheCurrentGroup(): void
    {
        $request = Request::create('/therapy_groups/index.php', 'GET', [
            'group_id' => self::SESSION_GROUP,
            'csrf_token_form' => ['not', 'a', 'string'],
        ]);

        TherapyGroupSessionUtil::requireCsrfForGroupSwitch($request, self::SESSION_GROUP);
        $this->addToAssertionCount(1);
    }

    public function testRequireCsrfForGroupSwitchAcceptsAPostedToken(): void
    {
        $request = Request::create('/therapy_groups/index.php?method=addGroup', 'POST', [
            'save' => 'save',
            'csrf_token_form' => CsrfUtils::collectCsrfToken($this->session),
        ]);

        TherapyGroupSessionUtil::requireCsrfForGroupSwitch($request, 8);
        $this->addToAssertionCount(1);
    }

    public function testRequireCsrfTokenAcceptsAValidToken(): void
    {
        $request = Request::create('/therapy_groups/index.php', 'GET', [
            'method' => 'groupParticipants',
            'deleteParticipant' => '1',
            'csrf_token_form' => CsrfUtils::collectCsrfToken($this->session),
        ]);

        TherapyGroupSessionUtil::requireCsrfToken($request);
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
            'forged switch is refused and keeps the open chart' => [['switch', '8'], 'Authentication Error pid=11 group=none'],
            'array token is refused like a missing one' => [['switch', '8', 'array-token'], 'Authentication Error pid=11 group=none'],
            'token-bearing switch continues' => [['switch', '8', 'with-token'], 'allowed pid=0 group=8'],
            'forged write is refused' => [['write', '8'], 'Authentication Error pid=11 group=none'],
            'write with an array token is refused' => [['write', '8', 'array-token'], 'Authentication Error pid=11 group=none'],
            'token-bearing write continues' => [['write', '8', 'with-token'], 'allowed pid=11 group=none'],
        ];
    }

    /**
     * @param list<string> $arguments
     */
    #[DataProvider('childProcessProvider')]
    public function testGuardInAChildProcess(array $arguments, string $output): void
    {
        $process = new Process([PHP_BINARY, self::FIXTURE, ...$arguments]);
        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
        $this->assertSame($output, $process->getOutput());
    }

    /**
     * @return array<string, array{non-empty-string, non-empty-string, non-empty-string, non-empty-string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function guardedWriteProvider(): array
    {
        $guard = 'TherapyGroupSessionUtil::requireCsrfToken($this->request);';
        return [
            'group switch' => [
                'therapy_groups_controller.php',
                <<<'PHP'
                public static function setSession(int $groupId, Request $request): void
                    {
                        // Opening a group closes the open patient, so a link from another
                        // site must not be able to do it.
                PHP,
                'TherapyGroupSessionUtil::requireCsrfForGroupSwitch($request, $groupId);',
                'setpid(0);',
            ],
            'group save' => [
                'therapy_groups_controller.php',
                <<<'PHP'
                if (isset($_POST['save'])) {
                PHP,
                $guard,
                '$this->saveNewGroup(',
            ],
            'group delete' => [
                'therapy_groups_controller.php',
                <<<'PHP'
                if ($_GET['deleteGroup'] == 1) {
                PHP,
                $guard,
                '$this->deleteGroup(',
            ],
            'participant save' => [
                'participants_controller.php',
                <<<'PHP'
                if (isset($_POST['save'])) {
                PHP,
                $guard,
                '->updateParticipant(',
            ],
            'participant delete' => [
                'participants_controller.php',
                <<<'PHP'
                if (isset($_GET['deleteParticipant'])) {
                PHP,
                $guard,
                '->removeParticipant(',
            ],
            'participant add' => [
                'participants_controller.php',
                <<<'PHP'
                if (isset($_POST['save_new'])) {
                PHP,
                $guard,
                '->saveParticipant(',
            ],
        ];
    }

    /**
     * The controllers need the full bootstrap, so check their source: each
     * write's guard must be the first statement under the condition that
     * leads to it, ahead of the write itself.
     *
     * @param non-empty-string $condition
     * @param non-empty-string $guard
     */
    #[DataProvider('guardedWriteProvider')]
    public function testControllerChecksTheTokenBeforeWriting(string $file, string $condition, string $guard, string $write): void
    {
        $source = (new Filesystem())->readFile(dirname(__DIR__, 5) . self::CONTROLLERS . $file);
        $parts = explode($condition, $source);
        $this->assertCount(2, $parts, "{$file} should contain the condition exactly once");
        $afterCondition = ltrim($parts[1]);
        $this->assertStringStartsWith($guard, $afterCondition);
        $this->assertStringContainsString($write, substr($afterCondition, strlen($guard)));
    }

    private function token(string $kind): ?string
    {
        return match ($kind) {
            'none' => null,
            'wrong' => 'not-the-session-token',
            'valid' => CsrfUtils::collectCsrfToken($this->session),
            default => throw new \InvalidArgumentException('Unknown token kind'),
        };
    }
}
