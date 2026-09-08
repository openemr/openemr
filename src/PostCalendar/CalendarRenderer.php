<?php

/**
 * PostCalendar Twig renderer.
 *
 * Owns a Twig environment configured for the PostCalendar templates under
 * `templates/calendar/`, registers PostCalendarTwigExtension, and provides
 * a small render API for the calendar's consumer files (pnuserapi.php,
 * pnuser.php, pnadmin.php).
 *
 * This replaces the legacy `pcSmarty` class. The `pcSmarty extends
 * Smarty_Legacy` pattern (and the entire `library/smarty_legacy/`
 * directory) is removed in the same change that switches every consumer
 * to use this renderer.
 *
 * Public API mirrors the Smarty subset the consumers actually use:
 *
 *   - assign(string|array, mixed = null): assign template variables
 *   - render(string $template): return rendered HTML
 *   - getVar(string): retrieve a previously-assigned variable. Used by
 *     pnuser.php's search-results path to read back the A_EVENTS array
 *     after assignment for a follow-up provider-name resolution pass —
 *     could be refactored to compute that pass before the assign, but
 *     until then the round-trip needs read access.
 *
 * The renderer holds no global state and can be instantiated multiple
 * times per request. There's no clear() — assigned variables
 * accumulate across calls. Each legacy entry point instantiates a
 * fresh renderer per request, so this is fine; if a caller ever
 * reuses an instance across renders it needs to manage that itself.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar;

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use Twig\Environment;

final class CalendarRenderer
{
    /** @var array<string, mixed> */
    private array $variables = [];

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * Build a renderer wired to the OpenEMR-configured Twig environment.
     *
     * Used by the legacy entry points (pnuserapi.php, pnuser.php,
     * pnadmin.php) where constructor injection isn't practical because
     * those files run at include-time, not through a DI container.
     * Tests should call the constructor directly with a fixture
     * Environment so they don't need a booted Kernel.
     */
    public static function create(): self
    {
        $container = new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel());
        $twig = $container->getTwig();
        $twig->addExtension(new PostCalendarTwigExtension());
        return new self($twig);
    }

    /**
     * Assign one or many template variables.
     *
     * Accepts either:
     *   - assign('name', $value)
     *   - assign(['name1' => $v1, 'name2' => $v2])
     *
     * @param string|array<string, mixed> $name
     */
    public function assign(string|array $name, mixed $value = null): void
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                if ($key !== '') {
                    $this->variables[$key] = $val;
                }
            }
            return;
        }
        if ($name !== '') {
            $this->variables[$name] = $value;
        }
    }

    /**
     * Legacy Smarty assign-by-reference. Twig copies values when rendering,
     * so by-reference semantics don't matter — alias to assign() to keep
     * the legacy consumer call sites (pnuserapi/pnadmin/pnuser) working
     * unchanged during the migration window.
     */
    public function assign_by_ref(string $name, mixed &$value): void
    {
        $this->assign($name, $value);
    }

    public function getVar(string $name): mixed
    {
        return $this->variables[$name] ?? null;
    }

    /**
     * Render the named Twig template under templates/calendar/ with all
     * variables assigned so far. Returns the rendered string; does not
     * echo. Throws if the template doesn't exist (no Smarty fallback —
     * the PostCalendar template set is fully migrated by this PR).
     */
    public function render(string $template): string
    {
        return $this->twig->render($template, $this->variables);
    }
}
