<?php

/**
 * A page renderer built the first time a page is rendered, so a request that renders no page
 * (a JSON API call) never builds it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

final class LazyPageRenderer implements PortalPageRenderer
{
    private ?PortalPageRenderer $renderer = null;

    /**
     * @param \Closure(): PortalPageRenderer $factory
     */
    public function __construct(private readonly \Closure $factory)
    {
    }

    public function render(string $page, array $vars = []): string
    {
        $this->renderer ??= ($this->factory)();

        return $this->renderer->render($page, $vars);
    }
}
