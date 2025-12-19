<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database\Repository\Settings;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Repository\AbstractRepository;

/**
 * Usage:
 *   $codeTypeRepository = CodeTypeRepository::getInstance();
 *   $codeType = $codeTypeRepository->findOneBy(['ct_key' => 'ICD10-PCS']);
 *   $codeTypes = $codeTypeRepository->findActive();
 *
 * @phpstan-type TCodeType = array{
 *     ct_key: string,
 *     ct_id: int,
 *     ct_seq: int,
 *     ct_mod: int,
 *     ct_just: string,
 *     ct_mask: string,
 *     ct_fee: int,
 *     ct_rel: int,
 *     ct_nofs: int,
 *     ct_diag: int,
 *     ct_active: int,
 *     ct_label: string,
 *     ct_external: int,
 *     ct_claim: int,
 *     ct_proc: int,
 *     ct_term: int,
 *     ct_problem: int,
 *     ct_drug: int,
 * }
 *
 * @template-extends AbstractRepository<TCodeType>
 */
class CodeTypeRepository extends AbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            'code_types',
            [
                'ct_seq' => 'ASC',
                'ct_key' => 'ASC', // @todo Order by ct_label rather than ct_key?
            ],
        );
    }

    public function normalize(array $data): array
    {
        $data['ct_label'] = $data['ct_label'] ?: $data['ct_key']; // Probably very old fallback, @todo Remove it?

        // For some reason these values returned as string, so fixing that here
        $data['ct_id'] = (int) $data['ct_id'];
        $data['ct_seq'] = (int) $data['ct_seq'];
        $data['ct_mod'] = (int) $data['ct_mod'];
        $data['ct_fee'] = (int) $data['ct_fee'];
        $data['ct_rel'] = (int) $data['ct_rel'];
        $data['ct_nofs'] = (int) $data['ct_nofs'];
        $data['ct_diag'] = (int) $data['ct_diag'];
        $data['ct_active'] = (int) $data['ct_active'];
        $data['ct_external'] = (int) $data['ct_external'];
        $data['ct_claim'] = (int) $data['ct_claim'];
        $data['ct_proc'] = (int) $data['ct_proc'];
        $data['ct_term'] = (int) $data['ct_term'];
        $data['ct_problem'] = (int) $data['ct_problem'];
        $data['ct_drug'] = (int) $data['ct_drug'];

        return $data;
    }

    /**
     * @phpstan-return array<TCodeType>
     */
    public function findActive(): array
    {
        return $this->findBy(['ct_active' => 1]);
    }
}
