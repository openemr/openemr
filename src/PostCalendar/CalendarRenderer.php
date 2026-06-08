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
 *   - getVar(string): retrieve a previously-assigned variable (only used
 *     by pnadmin.php legacy diagnostics; can be dropped once those are
 *     cleaned up).
 *
 * The renderer holds no global state and can be instantiated multiple
 * times per request.
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
    private readonly Environment $twig;

    /** @var array<string, mixed> */
    private array $variables = [];

    public function __construct()
    {
        $container = new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel());
        $this->twig = $container->getTwig();
        $this->twig->addExtension(new PostCalendarTwigExtension());
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
