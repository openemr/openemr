<?php

/**
 * The portal/patient page renderer is built on the first page render, once, and not at all
 * for a request that renders no page.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Controllers\Portal\Patient\LazyPageRenderer;
use OpenEMR\Controllers\Portal\Patient\PortalPageRenderer;
use PHPUnit\Framework\TestCase;

final class LazyPageRendererTest extends TestCase
{
    public function testTheRendererIsBuiltOnTheFirstRenderOnly(): void
    {
        $built = new class {
            public int $count = 0;
        };
        $pages = new LazyPageRenderer(static function () use ($built): PortalPageRenderer {
            $built->count++;

            return new class implements PortalPageRenderer {
                public function render(string $page, array $vars = []): string
                {
                    return $page . ':' . implode(',', array_keys($vars));
                }
            };
        });

        $this->assertSame(0, $built->count, 'nothing is built until a page is rendered');
        $this->assertSame('ProviderHome:a', $pages->render('ProviderHome', ['a' => 1]));
        $this->assertSame('DefaultError404:', $pages->render('DefaultError404'));
        $this->assertSame(1, $built->count);
    }
}
