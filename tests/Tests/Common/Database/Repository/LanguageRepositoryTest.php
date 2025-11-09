<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Common\Database\Repository;

use OpenEMR\Common\Database\Repository\RepositoryFactory;
use OpenEMR\Common\Database\Repository\Settings\LanguageRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('db')]
#[Group('repository')]
#[CoversClass(LanguageRepository::class)]
#[CoversMethod(LanguageRepository::class, 'findOneBy')]
#[CoversMethod(LanguageRepository::class, 'findAll')]
final class LanguageRepositoryTest extends TestCase
{
    #[Test]
    public function findOneByTest(): void
    {
        $repository = RepositoryFactory::createRepository(LanguageRepository::class);
        $this->assertEquals([
            'lang_id' => 1,
            'lang_code' => 'en',
            'lang_description' => 'English (Standard)',
            'lang_is_rtl' => 0,
        ], $repository->findOneBy(['lang_code' => 'en']));
    }

    #[Test]
    public function findAllTest(): void
    {
        $repository = RepositoryFactory::createRepository(LanguageRepository::class);
        $codeTypes = $repository->findAll();

        $this->assertIsArray($codeTypes);
        foreach ($codeTypes as $codeType) {
            $this->assertArrayHasAllCodeTypeKeys($codeType);
        }
    }

    private function assertArrayHasAllCodeTypeKeys(array $data): void
    {
        $this->assertArrayHasKey('lang_id', $data);
        $this->assertIsInt($data['lang_id']);

        $this->assertArrayHasKey('lang_code', $data);
        $this->assertIsString($data['lang_code']);

        $this->assertArrayHasKey('lang_description', $data);
        $this->assertIsString($data['lang_description']);

        $this->assertArrayHasKey('lang_is_rtl', $data);
        $this->assertIsInt($data['lang_is_rtl']);
    }
}
