<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://opencoreemr.com
 * @author    Michael Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Translation;

use OpenEMR\Common\Translation\Translator;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('translation')]
#[CoversClass(Translator::class)]
class TranslatorTest extends TestCase
{
    private Translator $translator;
    private OEGlobalsBag $globalsBag;

    protected function setUp(): void
    {
        $this->globalsBag = new OEGlobalsBag([
            'disable_translation' => false,
            'temp_skip_translations' => false,
            'translate_lists' => true,
            'translate_layout' => true,
            'translate_gacl_groups' => true,
            'translate_form_titles' => true,
            'translate_document_categories' => true,
            'translate_appt_categories' => true,
            'translate_no_safe_apostrophe' => false,
        ]);

        // Create translator with session accessor that returns language ID 1
        $this->translator = new Translator(
            $this->globalsBag,
            fn($key) => $key === 'language_choice' ? 1 : null
        );
    }

    #[Test]
    public function translateReturnsOriginalWhenDisabled(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = new Translator($globalsBag);

        $result = $translator->translate('Hello World');

        $this->assertEquals('Hello World', $result);
    }

    #[Test]
    public function translateReturnsOriginalWhenTempSkipEnabled(): void
    {
        $globalsBag = new OEGlobalsBag(['temp_skip_translations' => true]);
        $translator = new Translator($globalsBag);

        $result = $translator->translate('Hello World');

        $this->assertEquals('Hello World', $result);
    }

    #[Test]
    public function isDisabledReturnsTrueWhenDisableTranslationSet(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = new Translator($globalsBag);

        $this->assertTrue($translator->isDisabled());
    }

    #[Test]
    public function isDisabledReturnsTrueWhenTempSkipSet(): void
    {
        $globalsBag = new OEGlobalsBag(['temp_skip_translations' => true]);
        $translator = new Translator($globalsBag);

        $this->assertTrue($translator->isDisabled());
    }

    #[Test]
    public function isDisabledReturnsFalseByDefault(): void
    {
        $globalsBag = new OEGlobalsBag([]);
        $translator = new Translator($globalsBag);

        $this->assertFalse($translator->isDisabled());
    }

    #[Test]
    public function getLanguageIdReturnsSessionValue(): void
    {
        $translator = new Translator(
            $this->globalsBag,
            fn($key) => $key === 'language_choice' ? 5 : null
        );

        $this->assertEquals(5, $translator->getLanguageId());
    }

    #[Test]
    public function getLanguageIdReturnsOneAsDefault(): void
    {
        $translator = new Translator(
            $this->globalsBag,
            fn($key) => null
        );

        $this->assertEquals(1, $translator->getLanguageId());
    }

    #[Test]
    public function setLanguageIdOverridesSessionValue(): void
    {
        $translator = new Translator(
            $this->globalsBag,
            fn($key) => $key === 'language_choice' ? 1 : null
        );

        $translator->setLanguageId(10);

        $this->assertEquals(10, $translator->getLanguageId());
    }

    #[Test]
    public function translateTextEscapesHtmlEntities(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = new Translator($globalsBag);

        $result = $translator->translateText('<script>alert("xss")</script>');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    #[Test]
    public function translateAttributeEscapesQuotes(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = new Translator($globalsBag);

        $result = $translator->translateAttribute('Hello "World"');

        $this->assertStringNotContainsString('"World"', $result);
        $this->assertStringContainsString('&quot;', $result);
    }

    #[Test]
    public function translateJavaScriptReturnsJsonEncoded(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = new Translator($globalsBag);

        $result = $translator->translateJavaScript('Hello World');

        $this->assertEquals('"Hello World"', $result);
    }

    #[Test]
    public function translateListLabelRespectsConfig(): void
    {
        $globalsBag = new OEGlobalsBag([
            'disable_translation' => true,
            'translate_lists' => false,
        ]);
        $translator = new Translator($globalsBag);

        $result = $translator->translateListLabel('Test');

        $this->assertEquals('Test', $result);
    }

    #[Test]
    public function translateLayoutLabelRespectsConfig(): void
    {
        $globalsBag = new OEGlobalsBag([
            'disable_translation' => true,
            'translate_layout' => false,
        ]);
        $translator = new Translator($globalsBag);

        $result = $translator->translateLayoutLabel('Test');

        $this->assertEquals('Test', $result);
    }

    #[Test]
    public function resetClearsInternalState(): void
    {
        $translator = new Translator(
            $this->globalsBag,
            fn($key) => $key === 'language_choice' ? 5 : null
        );

        // Get language ID to cache it
        $translator->getLanguageId();
        $translator->setLanguageId(10);

        $translator->reset();

        // After reset, should read from session again
        $this->assertEquals(5, $translator->getLanguageId());
    }

    #[Test]
    public function isWarmedReturnsFalseInitially(): void
    {
        $this->assertFalse($this->translator->isWarmed());
    }

    #[Test]
    public function translateNormalizesNewlines(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = new Translator($globalsBag);

        $result = $translator->translate("Hello\nWorld");

        $this->assertStringNotContainsString("\n", $result);
        $this->assertEquals('Hello World', $result);
    }

    #[Test]
    public function translateRemovesCarriageReturns(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = new Translator($globalsBag);

        $result = $translator->translate("Hello\r\nWorld");

        $this->assertStringNotContainsString("\r", $result);
    }

    #[Test]
    public function translateConvertsQuotesToBackticksWhenSafeApostropheDisabled(): void
    {
        $globalsBag = new OEGlobalsBag([
            'disable_translation' => true,
            'translate_no_safe_apostrophe' => false,
        ]);
        $translator = new Translator($globalsBag);

        $result = $translator->translate('Hello "World"');

        $this->assertStringContainsString('`', $result);
        $this->assertStringNotContainsString('"', $result);
    }

    #[Test]
    public function translatePreservesQuotesWhenSafeApostropheEnabled(): void
    {
        $globalsBag = new OEGlobalsBag([
            'disable_translation' => true,
            'translate_no_safe_apostrophe' => true,
        ]);
        $translator = new Translator($globalsBag);

        $result = $translator->translate('Hello "World"');

        $this->assertStringContainsString('"', $result);
    }

    #[Test]
    public function translateRemovesMustacheComments(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = new Translator($globalsBag);

        $result = $translator->translate('Hello {{comment}} World');

        $this->assertStringNotContainsString('{{', $result);
        $this->assertStringNotContainsString('}}', $result);
    }
}
