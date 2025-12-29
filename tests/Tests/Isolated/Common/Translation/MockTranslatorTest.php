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

use OpenEMR\Common\Translation\MockTranslator;
use OpenEMR\Common\Translation\TranslatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('translation')]
#[CoversClass(MockTranslator::class)]
class MockTranslatorTest extends TestCase
{
    #[Test]
    public function implementsTranslatorInterface(): void
    {
        $mock = new MockTranslator();

        $this->assertInstanceOf(TranslatorInterface::class, $mock);
    }

    #[Test]
    public function translateReturnsConfiguredTranslation(): void
    {
        $mock = new MockTranslator(['Hello' => 'Hola']);

        $this->assertEquals('Hola', $mock->translate('Hello'));
    }

    #[Test]
    public function translateReturnsOriginalWhenNotConfigured(): void
    {
        $mock = new MockTranslator(['Hello' => 'Hola']);

        $this->assertEquals('Goodbye', $mock->translate('Goodbye'));
    }

    #[Test]
    public function translateReturnsOriginalWhenDisabled(): void
    {
        $mock = new MockTranslator(
            ['Hello' => 'Hola'],
            ['disabled' => true]
        );

        $this->assertEquals('Hello', $mock->translate('Hello'));
    }

    #[Test]
    public function translateTextEscapesHtml(): void
    {
        $mock = new MockTranslator(['Test' => '<script>alert("xss")</script>']);

        $result = $mock->translateText('Test');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    #[Test]
    public function translateAttributeEscapesQuotes(): void
    {
        $mock = new MockTranslator(['Test' => 'Hello "World"']);

        $result = $mock->translateAttribute('Test');

        $this->assertStringContainsString('&quot;', $result);
    }

    #[Test]
    public function translateJavaScriptReturnsJsonEncoded(): void
    {
        $mock = new MockTranslator(['Test' => 'Hello World']);

        $result = $mock->translateJavaScript('Test');

        $this->assertEquals('"Hello World"', $result);
    }

    #[Test]
    public function translateListLabelRespectsConfig(): void
    {
        $mock = new MockTranslator(
            ['Test' => 'Prueba'],
            ['translate_lists' => false]
        );

        $this->assertEquals('Test', $mock->translateListLabel('Test'));
    }

    #[Test]
    public function translateLayoutLabelRespectsConfig(): void
    {
        $mock = new MockTranslator(
            ['Test' => 'Prueba'],
            ['translate_layout' => false]
        );

        $this->assertEquals('Test', $mock->translateLayoutLabel('Test'));
    }

    #[Test]
    public function translateGaclGroupRespectsConfig(): void
    {
        $mock = new MockTranslator(
            ['Test' => 'Prueba'],
            ['translate_gacl_groups' => false]
        );

        $this->assertEquals('Test', $mock->translateGaclGroup('Test'));
    }

    #[Test]
    public function translateFormTitleRespectsConfig(): void
    {
        $mock = new MockTranslator(
            ['Test' => 'Prueba'],
            ['translate_form_titles' => false]
        );

        $this->assertEquals('Test', $mock->translateFormTitle('Test'));
    }

    #[Test]
    public function translateDocumentCategoryRespectsConfig(): void
    {
        $mock = new MockTranslator(
            ['Test' => 'Prueba'],
            ['translate_document_categories' => false]
        );

        $this->assertEquals('Test', $mock->translateDocumentCategory('Test'));
    }

    #[Test]
    public function translateApptCategoryRespectsConfig(): void
    {
        $mock = new MockTranslator(
            ['Test' => 'Prueba'],
            ['translate_appt_categories' => false]
        );

        $this->assertEquals('Test', $mock->translateApptCategory('Test'));
    }

    #[Test]
    public function warmCacheDoesNothing(): void
    {
        $mock = new MockTranslator();

        // Should not throw
        $mock->warmCache();

        $this->assertTrue(true);
    }

    #[Test]
    public function isDisabledReturnsConfiguredValue(): void
    {
        $mockEnabled = new MockTranslator([], ['disabled' => false]);
        $mockDisabled = new MockTranslator([], ['disabled' => true]);

        $this->assertFalse($mockEnabled->isDisabled());
        $this->assertTrue($mockDisabled->isDisabled());
    }

    #[Test]
    public function getLanguageIdReturnsConfiguredValue(): void
    {
        $mock = new MockTranslator([], ['language_id' => 42]);

        $this->assertEquals(42, $mock->getLanguageId());
    }

    #[Test]
    public function setTranslationsReplacesTranslations(): void
    {
        $mock = new MockTranslator(['Hello' => 'Hola']);

        $mock->setTranslations(['Goodbye' => 'Adios']);

        $this->assertEquals('Hello', $mock->translate('Hello'));
        $this->assertEquals('Adios', $mock->translate('Goodbye'));
    }

    #[Test]
    public function addTranslationAddsToExisting(): void
    {
        $mock = new MockTranslator(['Hello' => 'Hola']);

        $mock->addTranslation('Goodbye', 'Adios');

        $this->assertEquals('Hola', $mock->translate('Hello'));
        $this->assertEquals('Adios', $mock->translate('Goodbye'));
    }

    #[Test]
    public function setConfigMergesWithExisting(): void
    {
        $mock = new MockTranslator([], [
            'disabled' => false,
            'translate_lists' => true,
        ]);

        $mock->setConfig(['disabled' => true]);

        $this->assertTrue($mock->isDisabled());
        // translate_lists should still be true
        $mock->setTranslations(['Test' => 'Prueba']);
        $this->assertEquals('Prueba', $mock->translateListLabel('Test'));
    }
}
