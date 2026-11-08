<?php

/**
 * Exercise fee-sheet AJAX saves through the browser and persistent HTTP state.
 *
 * @package OpenEMR
 * @author Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance;

use Facebook\WebDriver\JavaScriptExecutor;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;
use OpenEMR\Tests\Acceptance\Support\UiSeedingTrait;

final class FeeSheetChecksumAcceptanceTest extends PantherAcceptanceTestCase
{
    use UiSeedingTrait;

    public function testRapidSelectionsAndJustificationSavePreserveCurrentChecksum(): void
    {
        $client = $this->client = BrowserSession::create();
        $this->performLoginAsAdmin();
        $this->addPatientViaUi();
        $this->addEncounterViaUi();
        $client->switchTo()->defaultContent();
        $client->request('GET', '/interface/forms/fee_sheet/new.php');
        $driver = $client->getWebDriver();
        self::assertInstanceOf(JavaScriptExecutor::class, $driver);
        // This page normally lives inside the main shell. Keep the production
        // AJAX functions intact while supplying its parent session-refresh hook.
        $driver->executeScript("window.restoreSession = function () {}; window.reviewAlerts = []; window.alert = m => reviewAlerts.push(m); document.forms[0].ProviderID.value = '1';");
        $result = $driver->executeAsyncScript(<<<'JS'
const done = arguments[0];
for (const code of ['ICD10|R51.9|', 'ICD10|M54.5|', 'CPT4|99213|']) {
    const select = document.createElement('select');
    select.add(new Option('Select', ''));
    select.add(new Option(code, code));
    select.selectedIndex = 1;
    codeselect(select);
}
codeselect_and_save_queue.then(() => done(reviewAlerts));
JS);
        self::assertSame([], $result);
        $driver->navigate()->refresh();
        $driver->executeScript("window.restoreSession = function () {}; window.reviewAlerts = []; window.alert = m => reviewAlerts.push(m);");
        $codes = $driver->executeScript(<<<'JS'
return Array.from(document.querySelectorAll('input[name$="[code]"]')).map(input => input.value).sort();
JS);
        self::assertSame(['99213', 'M54.5', 'R51.9'], $codes);
        $result = $driver->executeAsyncScript(<<<'JS'
const done = arguments[0], form = document.forms[0], old = form.form_checksum.value;
form.querySelector('input[name$="[price]"]').value = '12.00';
const data = new FormData(form);
data.append('running_as_ajax', '1'); data.append('dx_update', '1');
$.ajax({url: fee_sheet_new, type: 'POST', data, processData: false, contentType: false}).done(html => {
    const parsed = new DOMParser().parseFromString(html, 'text/html');
    done({old, checksum: parsed.querySelector('[name=form_checksum]').value, alert: parsed.querySelector('[name=form_alertmsg]').value});
}).fail(xhr => done({error: xhr.status}));
JS);
        self::assertIsArray($result);
        self::assertArrayNotHasKey('error', $result);
        self::assertSame('', $result['alert']);
        self::assertNotSame($result['old'], $result['checksum']);
        // The browser deliberately still has the old checksum. Reject its
        // second save before any stale line-item data can overwrite the server.
        $conflict = $driver->executeAsyncScript(<<<'JS'
const done = arguments[0], form = document.forms[0];
form.querySelector('input[name$="[price]"]').value = '99.00';
const data = new FormData(form);
data.append('running_as_ajax', '1'); data.append('dx_update', '1');
$.ajax({url: fee_sheet_new, type: 'POST', data, processData: false, contentType: false}).done(html => {
    done(new DOMParser().parseFromString(html, 'text/html').querySelector('[name=form_alertmsg]').value);
}).fail(xhr => done('HTTP ' + xhr.status));
JS);
        self::assertIsString($conflict);
        self::assertStringContainsString('Someone else has just changed this visit', $conflict);
        $driver->navigate()->refresh();
        $price = $driver->executeScript("return document.querySelector('input[name$=\"[price]\"]').value;");
        self::assertIsString($price);
        self::assertSame(12.0, (float) $price);
    }
}
