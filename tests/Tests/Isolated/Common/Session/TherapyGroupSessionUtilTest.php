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
    private const FIXTURE = __DIR__ . '/fixtures/therapy-group-switch-denial.php';
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
            'numeric string from a GET link' => ['7', 7],
            'missing' => [null, null],
            'not numeric' => ['from_session', null],
        ];
    }

    #[DataProvider('storedGroupProvider')]
    public function testGetGroupId(mixed $stored, ?int $expected): void
    {
        $this->session->set('therapy_group', $stored);

        $this->assertSame($expected, TherapyGroupSessionUtil::getGroupId());
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
            'other group, non-string token' => [8, 0, 'array', false],
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

    public function testRequireCsrfReadsTheTokenFromTheQuery(): void
    {
        $request = Request::create('/therapy_groups/index.php', 'GET', [
            'group_id' => '8',
            'csrf_token_form' => CsrfUtils::collectCsrfToken($this->session),
        ]);

        TherapyGroupSessionUtil::requireCsrfForGroupSwitch($request, 8);
        $this->addToAssertionCount(1);
    }

    public function testRequireCsrfReadsTheTokenFromThePostedForm(): void
    {
        $request = Request::create('/therapy_groups/index.php?method=addGroup', 'POST', [
            'save' => 'save',
            'csrf_token_form' => CsrfUtils::collectCsrfToken($this->session),
        ]);

        TherapyGroupSessionUtil::requireCsrfForGroupSwitch($request, 8);
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
            'forged switch is refused and keeps the open chart' => [['8'], 'Authentication Error pid=11 group=none'],
            'array token is refused like a missing one' => [['8', 'array-token'], 'Authentication Error pid=11 group=none'],
            'token-bearing switch continues' => [['8', 'with-token'], 'allowed pid=0 group=8'],
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
     * @return array<string, array{non-empty-string, non-empty-string, non-empty-string, non-empty-string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function guardedWriteProvider(): array
    {
        $post = 'CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);';
        $get = 'CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);';
        return [
            'group switch' => [
                'therapy_groups_controller.php',
                "public static function setSession(int \$groupId): void\n    {",
                'TherapyGroupSessionUtil::requireCsrfForGroupSwitch(CurrentRequest::get(), $groupId);',
                'setpid(0);',
            ],
            'group save' => ['therapy_groups_controller.php', "if (isset(\$_POST['save'])) {", $post, '$this->saveNewGroup('],
            'group delete' => ['therapy_groups_controller.php', "if (\$_GET['deleteGroup'] == 1) {", $get, '$this->deleteGroup('],
            'participant save' => ['participants_controller.php', "if (isset(\$_POST['save'])) {", $post, '->updateParticipant('],
            'participant delete' => ['participants_controller.php', "if (isset(\$_GET['deleteParticipant'])) {", $get, '->removeParticipant('],
            'participant add' => ['participants_controller.php', "if (isset(\$_POST['save_new'])) {", $post, '->saveParticipant('],
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
        // Skip line comments between the condition and the guard.
        while (str_starts_with($afterCondition, '//')) {
            $afterCondition = ltrim(explode("\n", $afterCondition, 2)[1] ?? '');
        }
        $this->assertStringStartsWith($guard, $afterCondition);
        $this->assertStringContainsString($write, substr($afterCondition, strlen($guard)));
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
