<?php

/**
 * Rel-side only: replace `contrib/util/language_translations/currentLanguage_utf8.sql`
 * with the blob from the prior rel branch (e.g., when cutting rel-820,
 * copy the file's contents from rel-810). The file is ~250k lines /
 * ~23 MB; the bot fetches the prior rel's blob via git rather than
 * synthesising the content.
 *
 * Idempotent: if the local file already matches the prior rel's blob,
 * no-op.
 *
 * Implementation: shells out to git via the Symfony Process component.
 * `git fetch <openemr-remote> <prevRelBranch>` brings the ref in as
 * FETCH_HEAD; `git show FETCH_HEAD:<path>` emits the file contents.
 * The Process factory is injectable so tests can stub the git calls
 * without touching the network or a real repo.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use Symfony\Component\Process\Process;

final readonly class TranslationFileCopyFromPriorRelMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'contrib/util/language_translations/currentLanguage_utf8.sql';
    private const DEFAULT_REMOTE_URL = 'https://github.com/openemr/openemr.git';

    /**
     * @param (\Closure(list<string>, string): Process)|null $processFactory
     */
    public function __construct(
        private ?\Closure $processFactory = null,
        private string $remoteUrl = self::DEFAULT_REMOTE_URL,
    ) {
    }

    public function name(): string
    {
        return 'currentLanguage_utf8.sql (copy from prior rel branch)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $prev = $context->prevRelBranch;
        if ($prev === null) {
            throw new \RuntimeException(
                self::class . ' requires --prev-rel-branch to be supplied via MutatorContext',
            );
        }

        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $localCurrent = file_get_contents($path);
        if ($localCurrent === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        // (1) Fetch the prior rel branch into FETCH_HEAD.
        $fetch = $this->makeProcess(
            ['git', 'fetch', '--no-tags', '--depth=1', $this->remoteUrl, $prev],
            $context->projectDir,
        );
        // The translation blob is ~23 MB; pulling it over a slow network
        // can exceed Symfony Process's 60s default. Match the show step.
        $fetch->setTimeout(300);
        $fetch->run();
        if (!$fetch->isSuccessful()) {
            throw new \RuntimeException(
                'git fetch of prior rel branch failed: '
                . trim($fetch->getErrorOutput() . "\n" . $fetch->getOutput()),
            );
        }

        // (2) Read the blob at FETCH_HEAD:<path>.
        $show = $this->makeProcess(
            ['git', 'show', 'FETCH_HEAD:' . self::RELATIVE_PATH],
            $context->projectDir,
        );
        // git show streams the full ~23 MB blob; raise the execution
        // timeout above Symfony Process's 60s default.
        $show->setTimeout(300);
        $show->run();
        if (!$show->isSuccessful()) {
            throw new \RuntimeException(
                'git show of prior rel translation file failed: '
                . trim($show->getErrorOutput()),
            );
        }
        $priorContent = $show->getOutput();

        if ($priorContent === $localCurrent) {
            return MutatorResult::noop();
        }

        if (file_put_contents($path, $priorContent) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }

    /**
     * @param list<string> $command
     */
    private function makeProcess(array $command, string $cwd): Process
    {
        if ($this->processFactory !== null) {
            return ($this->processFactory)($command, $cwd);
        }
        return new Process($command, $cwd);
    }
}
