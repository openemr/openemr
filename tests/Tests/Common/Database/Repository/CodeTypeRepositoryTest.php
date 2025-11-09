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
use OpenEMR\Common\Database\Repository\Settings\CodeTypeRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('db')]
#[Group('repository')]
#[CoversClass(CodeTypeRepository::class)]
#[CoversMethod(CodeTypeRepository::class, 'findOneBy')]
#[CoversMethod(CodeTypeRepository::class, 'findAll')]
#[CoversMethod(CodeTypeRepository::class, 'findActive')]
final class CodeTypeRepositoryTest extends TestCase
{
    #[Test]
    public function findOneByTest(): void
    {
        $repository = RepositoryFactory::createRepository(CodeTypeRepository::class);
        $codeType = $repository->findOneBy(['ct_key' => 'CPT4']);

        $this->assertNotNull($codeType);
        $this->assertArrayHasAllCodeTypeKeys($codeType);
    }

    #[Test]
    public function findAllTest(): void
    {
        $repository = RepositoryFactory::createRepository(CodeTypeRepository::class);
        $codeTypes = $repository->findAll();

        $this->assertIsArray($codeTypes);
        foreach ($codeTypes as $codeType) {
            $this->assertArrayHasAllCodeTypeKeys($codeType);
        }
    }

    #[Test]
    public function findActiveTest(): void
    {
        $repository = RepositoryFactory::createRepository(CodeTypeRepository::class);
        $codeTypes = $repository->findActive();

        $this->assertIsArray($codeTypes);
        foreach ($codeTypes as $codeType) {
            $this->assertArrayHasAllCodeTypeKeys($codeType);
        }
    }

    private function assertArrayHasAllCodeTypeKeys(array $data): void
    {
        $this->assertArrayHasKey('ct_key', $data);
        $this->assertIsString($data['ct_key']);

        $this->assertArrayHasKey('ct_id', $data);
        $this->assertIsInt($data['ct_id']);

        $this->assertArrayHasKey('ct_seq', $data);
        $this->assertIsInt($data['ct_seq']);

        $this->assertArrayHasKey('ct_mod', $data);
        $this->assertIsInt($data['ct_mod']);

        $this->assertArrayHasKey('ct_just', $data);
        $this->assertIsString($data['ct_just']);

        $this->assertArrayHasKey('ct_mask', $data);
        $this->assertIsString($data['ct_mask']);

        $this->assertArrayHasKey('ct_fee', $data);
        $this->assertIsInt($data['ct_fee']);

        $this->assertArrayHasKey('ct_rel', $data);
        $this->assertIsInt($data['ct_rel']);

        $this->assertArrayHasKey('ct_nofs', $data);
        $this->assertIsInt($data['ct_nofs']);

        $this->assertArrayHasKey('ct_diag', $data);
        $this->assertIsInt($data['ct_diag']);

        $this->assertArrayHasKey('ct_active', $data);
        $this->assertIsInt($data['ct_active']);

        $this->assertArrayHasKey('ct_label', $data);
        $this->assertIsString($data['ct_label']);

        $this->assertArrayHasKey('ct_external', $data);
        $this->assertIsInt($data['ct_external']);

        $this->assertArrayHasKey('ct_claim', $data);
        $this->assertIsInt($data['ct_claim']);

        $this->assertArrayHasKey('ct_proc', $data);
        $this->assertIsInt($data['ct_proc']);

        $this->assertArrayHasKey('ct_term', $data);
        $this->assertIsInt($data['ct_term']);

        $this->assertArrayHasKey('ct_problem', $data);
        $this->assertIsInt($data['ct_problem']);

        $this->assertArrayHasKey('ct_drug', $data);
        $this->assertIsInt($data['ct_drug']);
    }
}
