<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\CodeTypes;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use OpenEMR\Entities\Code;
use OpenEMR\Entities\CodeType;
use OpenEMR\Entities\ListOption;
use OpenEMR\Services\CodeTypes\CodeTypeMappingUpdater;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;

#[Group('isolated')]
class CodeTypeMappingUpdaterTest extends TestCase
{
    private EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject $em;
    /** @var EntityRepository<CodeType>&\PHPUnit\Framework\MockObject\MockObject */
    private EntityRepository $codeTypeRepo;
    /** @var EntityRepository<Code>&\PHPUnit\Framework\MockObject\MockObject */
    private EntityRepository $codeRepo;
    /** @var EntityRepository<ListOption>&\PHPUnit\Framework\MockObject\MockObject */
    private EntityRepository $listOptionRepo;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->codeTypeRepo = $this->createMock(EntityRepository::class);
        $this->codeRepo = $this->createMock(EntityRepository::class);
        $this->listOptionRepo = $this->createMock(EntityRepository::class);

        $this->em->method('getRepository')
            ->willReturnCallback(fn (string $class) => match ($class) {
                CodeType::class => $this->codeTypeRepo,
                Code::class => $this->codeRepo,
                ListOption::class => $this->listOptionRepo,
                default => throw new \InvalidArgumentException("Unexpected repository: $class"),
            });
    }

    public function testShouldUpdateCPT4MappingsReturnsFalseWhenCPT4NotActive(): void
    {
        $this->codeTypeRepo->method('findOneBy')
            ->with(['key' => 'CPT4', 'active' => true])
            ->willReturn(null);

        $updater = new CodeTypeMappingUpdater($this->em, new NullLogger());

        self::assertFalse(
            $updater->shouldUpdateCPT4Mappings(),
            'Should return false when CPT4 code type is not active',
        );
    }

    public function testShouldUpdateCPT4MappingsReturnsFalseWhenAllMappingsCurrent(): void
    {
        $codeType = $this->makeCodeType('CPT4', 1);
        $this->codeTypeRepo->method('findOneBy')
            ->willReturn($codeType);
        $this->codeTypeRepo->method('find')
            ->with('CPT4')
            ->willReturn($codeType);

        $code = $this->makeCode('99201', 'New Patient (Brief)', 1);
        $this->codeRepo->method('findOneBy')
            ->willReturn($code);

        $listOption = $this->makeListOption('encounter-types', 'new-patient-10', 'CPT4:99201');
        $this->listOptionRepo->method('find')
            ->willReturn($listOption);

        $updater = new CodeTypeMappingUpdater($this->em, new NullLogger());

        self::assertFalse(
            $updater->shouldUpdateCPT4Mappings(),
            'Should return false when all CPT4 mappings are current',
        );
    }

    public function testShouldUpdateCPT4MappingsReturnsTrueWhenMappingOutdated(): void
    {
        $codeType = $this->makeCodeType('CPT4', 1);
        $this->codeTypeRepo->method('findOneBy')
            ->willReturn($codeType);
        $this->codeTypeRepo->method('find')
            ->with('CPT4')
            ->willReturn($codeType);

        $code = $this->makeCode('99201', 'New Patient (Brief)', 1);
        $this->codeRepo->method('findOneBy')
            ->willReturn($code);

        $listOption = $this->makeListOption('encounter-types', 'new-patient-10', 'OLD:value');
        $this->listOptionRepo->method('find')
            ->willReturn($listOption);

        $updater = new CodeTypeMappingUpdater($this->em, new NullLogger());

        self::assertTrue(
            $updater->shouldUpdateCPT4Mappings(),
            'Should return true when CPT4 mapping needs update',
        );
    }

    public function testShouldUpdateSNOMEDMappingsReturnsTrueWhenMappingOutdated(): void
    {
        $listOption = $this->makeListOption('encounter-types', 'visit-after-hours', 'OLD:value');
        $this->listOptionRepo->method('find')
            ->willReturn($listOption);

        $updater = new CodeTypeMappingUpdater($this->em, new NullLogger());

        self::assertTrue(
            $updater->shouldUpdateSNOMEDMappings(),
            'Should return true when SNOMED mapping needs update',
        );
    }

    public function testShouldUpdateSNOMEDMappingsReturnsTrueWhenListOptionMissing(): void
    {
        $this->listOptionRepo->method('find')
            ->willReturn(null);

        $updater = new CodeTypeMappingUpdater($this->em, new NullLogger());

        self::assertTrue(
            $updater->shouldUpdateSNOMEDMappings(),
            'Should return true when list option does not exist',
        );
    }

    public function testUpdateSNOMEDMappingsUpdatesListOptionsAndFlushes(): void
    {
        $listOption = $this->makeListOption('encounter-types', 'office-visit', '');
        $this->listOptionRepo->method('find')
            ->willReturn($listOption);

        $this->em->expects(self::exactly(2))
            ->method('flush');

        $updater = new CodeTypeMappingUpdater($this->em, new NullLogger());
        $updater->updateSNOMEDMappings();

        self::assertStringStartsWith(
            'SNOMED-CT:',
            $listOption->codes,
            'ListOption codes should be updated with SNOMED-CT prefix',
        );
    }

    public function testUpdateCPT4MappingsUpdatesListOptionsAndFlushes(): void
    {
        $codeType = $this->makeCodeType('CPT4', 1);
        $this->codeTypeRepo->method('find')
            ->with('CPT4')
            ->willReturn($codeType);

        $code = $this->makeCode('99201', 'New Patient (Brief)', 1);
        $this->codeRepo->method('findOneBy')
            ->willReturn($code);

        $listOption = $this->makeListOption('encounter-types', 'new-patient-10', '');
        $this->listOptionRepo->method('find')
            ->willReturn($listOption);

        $this->em->expects(self::once())
            ->method('flush');

        $updater = new CodeTypeMappingUpdater($this->em, new NullLogger());
        $updater->updateCPT4Mappings();

        self::assertSame(
            'CPT4:99201',
            $listOption->codes,
            'ListOption codes should be updated with CPT4 code',
        );
    }

    private function makeCodeType(string $key, int $id): CodeType
    {
        $entity = (new ReflectionClass(CodeType::class))->newInstanceWithoutConstructor();
        $this->setReadonlyProperty($entity, 'key', $key);
        $this->setReadonlyProperty($entity, 'id', $id);
        $this->setReadonlyProperty($entity, 'active', true);
        $this->setReadonlyProperty($entity, 'seq', 1);
        return $entity;
    }

    private function makeCode(string $code, string $codeText, int $codeType): Code
    {
        $entity = (new ReflectionClass(Code::class))->newInstanceWithoutConstructor();
        $this->setReadonlyProperty($entity, 'id', 1);
        $this->setReadonlyProperty($entity, 'code', $code);
        $this->setReadonlyProperty($entity, 'codeText', $codeText);
        $this->setReadonlyProperty($entity, 'codeType', $codeType);
        return $entity;
    }

    private function makeListOption(string $listId, string $optionId, string $codes): ListOption
    {
        $entity = (new ReflectionClass(ListOption::class))->newInstanceWithoutConstructor();
        $this->setReadonlyProperty($entity, 'listId', $listId);
        $this->setReadonlyProperty($entity, 'optionId', $optionId);
        $entity->codes = $codes;
        return $entity;
    }

    private function setReadonlyProperty(object $entity, string $property, mixed $value): void
    {
        $ref = new ReflectionClass($entity);
        $prop = $ref->getProperty($property);
        $prop->setValue($entity, $value);
    }
}
