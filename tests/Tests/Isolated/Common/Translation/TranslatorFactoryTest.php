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
use OpenEMR\Common\Translation\Translator;
use OpenEMR\Common\Translation\TranslatorFactory;
use OpenEMR\Common\Translation\TranslatorInterface;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('translation')]
#[CoversClass(TranslatorFactory::class)]
class TranslatorFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        TranslatorFactory::reset();
        OEGlobalsBag::resetInstance();
    }

    protected function tearDown(): void
    {
        TranslatorFactory::reset();
        OEGlobalsBag::resetInstance();
    }

    #[Test]
    public function getInstanceReturnsSameInstance(): void
    {
        $instance1 = TranslatorFactory::getInstance();
        $instance2 = TranslatorFactory::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    #[Test]
    public function getInstanceReturnsTranslatorInterface(): void
    {
        $instance = TranslatorFactory::getInstance();

        $this->assertInstanceOf(TranslatorInterface::class, $instance);
    }

    #[Test]
    public function getInstanceReturnsTranslatorByDefault(): void
    {
        $instance = TranslatorFactory::getInstance();

        $this->assertInstanceOf(Translator::class, $instance);
    }

    #[Test]
    public function createReturnsNewInstance(): void
    {
        $instance1 = TranslatorFactory::create();
        $instance2 = TranslatorFactory::create();

        $this->assertNotSame($instance1, $instance2);
    }

    #[Test]
    public function createReturnsTranslatorInterface(): void
    {
        $instance = TranslatorFactory::create();

        $this->assertInstanceOf(TranslatorInterface::class, $instance);
    }

    #[Test]
    public function setInstanceAllowsMockInjection(): void
    {
        $mock = new MockTranslator(['Hello' => 'Hola']);

        TranslatorFactory::setInstance($mock);
        $instance = TranslatorFactory::getInstance();

        $this->assertSame($mock, $instance);
        $this->assertEquals('Hola', $instance->translate('Hello'));
    }

    #[Test]
    public function setInstanceWithNullClearsInstance(): void
    {
        $mock = new MockTranslator();
        TranslatorFactory::setInstance($mock);

        TranslatorFactory::setInstance(null);
        $instance = TranslatorFactory::getInstance();

        $this->assertNotSame($mock, $instance);
        $this->assertInstanceOf(Translator::class, $instance);
    }

    #[Test]
    public function resetClearsInstanceAndResetsConfig(): void
    {
        $mock = new MockTranslator();
        TranslatorFactory::setInstance($mock);
        TranslatorFactory::setCompatibilityMode(false);

        TranslatorFactory::reset();

        $this->assertNotSame($mock, TranslatorFactory::getInstance());
        $this->assertTrue(TranslatorFactory::isCompatibilityMode());
    }

    #[Test]
    public function compatibilityModeIsEnabledByDefault(): void
    {
        $this->assertTrue(TranslatorFactory::isCompatibilityMode());
    }

    #[Test]
    public function setCompatibilityModeChangesMode(): void
    {
        TranslatorFactory::setCompatibilityMode(false);

        $this->assertFalse(TranslatorFactory::isCompatibilityMode());
    }

    #[Test]
    public function createWithCustomGlobalsBag(): void
    {
        $globalsBag = new OEGlobalsBag(['disable_translation' => true]);
        $translator = TranslatorFactory::create($globalsBag);

        $this->assertTrue($translator->isDisabled());
    }

    #[Test]
    public function createWithCustomSessionAccessor(): void
    {
        $globalsBag = new OEGlobalsBag([]);
        $sessionAccessor = fn($key) => $key === 'language_choice' ? 42 : null;

        $translator = TranslatorFactory::create($globalsBag, $sessionAccessor);

        $this->assertEquals(42, $translator->getLanguageId());
    }
}
