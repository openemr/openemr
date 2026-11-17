<?php

/**
 * E2e test: text typed into the Text Templates editor reaches it.
 *
 * Double-clicking a textarea on an encounter form opens the Text Templates
 * dialog (library/custom_template/custom_template.php), core's CKEditor 5
 * editor. An editor can render and still refuse input: CKEditor's LTS
 * releases lock an editor configured with licenseKey 'GPL' read-only, and
 * say so only in the browser console. Checking that the editor appears
 * misses that, so this test types into it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class TextTemplateEditorTypingTest extends PantherTestCase
{
    use LoginTrait;

    private const TYPED_TEXT = 'typed by e2e';
    private const EDITOR_IFRAME = '//iframe[contains(@src, "custom_template.php")]';
    private const EDITABLE = '.ck-editor__editable';

    /**
     * Fails when the editor renders but ignores keystrokes, as a locked one does.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testTypedTextReachesTheTemplateEditor(): void
    {
        try {
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->openTemplateEditor();

            // Type with real keystrokes: editor.model.change() writes past a
            // read-only lock, so inserting through the API proves nothing.
            $this->client->findElement(WebDriverBy::cssSelector(self::EDITABLE))->click();
            $this->client->getKeyboard()->sendKeys(self::TYPED_TEXT);
            try {
                $this->client->wait(10)->until(
                    fn(): bool => str_contains($this->editorState('getData'), self::TYPED_TEXT)
                );
                // @codeCoverageIgnoreStart
                // The failure branch runs only when the editor refuses typed
                // text, which a working editor in normal CI never does.
            } catch (TimeoutException) {
                $this->fail(sprintf(
                    'Text typed into the Text Templates editor never reached it (isReadOnly: %s, data: %s). A read-only editor with no other error is how CKEditor 5 refuses licenseKey "GPL" on an LTS release; check the browser console for license-key-lts-not-allowed.',
                    $this->editorState('isReadOnly'),
                    $this->editorState('getData'),
                ));
            }
            // @codeCoverageIgnoreEnd
        } finally {
            if (isset($this->client)) {
                $this->client->quit();
            }
        }
    }

    /**
     * Open a SOAP form in the encounter tab, double-click its first textarea,
     * and wait in the dialog until its editor exists.
     */
    private function openTemplateEditor(): void
    {
        $this->client->executeScript(
            'navigateTab(webroot_url + arguments[0], "enc"); activateTabByName("enc", true);',
            ['/interface/patient_file/encounter/load_form.php?formname=soap']
        );
        $this->client->waitFor(XpathsConstants::ENCOUNTER_IFRAME);
        $this->client->switchTo()->frame($this->client->findElement(WebDriverBy::xpath(XpathsConstants::ENCOUNTER_IFRAME)));
        $this->client->waitFor('textarea[name="subjective"]');
        // CustomTemplateLoader.js loads CustomTemplateApi.js on window load,
        // and the double-click handler exists only after that.
        $this->client->wait(30)->until(
            static fn(JavaScriptExecutor $driver): bool => $driver->executeScript('return typeof bindTextArea === "function";') === true,
            'The SOAP form never loaded CustomTemplateApi.js; is the text_templates_enabled global on?'
        );
        // Dispatch the dblclick event bindTextArea() listens for. The typing
        // below is what this test checks, so it uses real keystrokes.
        $this->client->executeScript(
            'arguments[0].dispatchEvent(new MouseEvent("dblclick", {bubbles: true}));',
            [$this->client->findElement(WebDriverBy::name('subjective'))]
        );

        // dlgopen() puts the dialog's iframe in the top window.
        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(self::EDITOR_IFRAME);
        $this->client->switchTo()->frame($this->client->findElement(WebDriverBy::xpath(self::EDITOR_IFRAME)));
        $this->client->wait(30)->until(
            static fn(JavaScriptExecutor $driver): bool => $driver->executeScript(
                'return document.querySelector(arguments[0])?.ckeditorInstance !== undefined;',
                [self::EDITABLE]
            ) === true,
            'The Text Templates dialog never created its CKEditor 5 editor'
        );

        // A text-only context covers the editor with a warning for 10
        // seconds. Dismiss it for this visit only; "Dismiss Forever" would
        // save a user setting.
        $dismissButtons = $this->client->findElements(WebDriverBy::id('alertDismissButton'));
        if ($dismissButtons !== []) {
            $dismissButtons[0]->click();
            $this->client->wait(10)->until(
                WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::id('alert_box')),
                'The text-only template warning did not go away when dismissed'
            );
        }
    }

    /**
     * Read one property of the editor, or call one of its methods, as text.
     */
    private function editorState(string $member): string
    {
        $state = $this->client->executeScript(
            <<<'JS'
                const editor = document.querySelector(arguments[0]).ckeditorInstance;
                const value = typeof editor[arguments[1]] === 'function' ? editor[arguments[1]]() : editor[arguments[1]];
                return String(value);
                JS,
            [self::EDITABLE, $member]
        );
        $this->assertIsString($state);
        return $state;
    }
}
