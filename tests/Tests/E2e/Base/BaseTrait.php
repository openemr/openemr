<?php

/**
 * BaseTrait trait
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Base;

use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;

trait BaseTrait
{
    protected function base(): void
    {
        $e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
        $this->client = static::createPantherClient(['external_base_uri' => $e2eBaseUrl]);
        $this->client->manage()->window()->maximize();
    }

    protected function switchToIFrame($selector): void
    {
        $iframe = $this->client->findElement($selector);
        $this->client->switchTo()->frame($iframe);
        $this->crawler = $this->client->refreshCrawler();
    }

    protected function assertActiveTab($text): void
    {
        $startTime = (int) (microtime(true) * 1000);
        while (strpos($this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), "Loading") === 0) {
            if (($startTime + 10000) < ((int) (microtime(true) * 1000))) {
                $this->fail("Timeout waiting for tab [$text]");
            }
            usleep(100);
        }
        $this->assertSame($text, $this->crawler->filterXPath(XpathsConstants::ACTIVE_TAB)->text(), "[$text] tab load FAILED");
    }
}
