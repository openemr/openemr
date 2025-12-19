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
 *   $languageRepository = LanguageRepository::getInstance();
 *   $language = $userRepository->findOneBy(['lang_code' => 'en']);
 *
 * @phpstan-type TLanguage = array{
 *     lang_id: int,
 *     lang_code: string,
 *     lang_description: string,
 *     lang_is_rtl: int,
 * }
 *
 * @template-extends AbstractRepository<TLanguage>
 */
class LanguageRepository extends AbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            'lang_languages',
            [
                'lang_description' => 'ASC',
            ],
        );
    }

    public function normalize(array $data): array
    {
        // For some reason these values returned as string, so fixing that here
        $data['lang_id'] = (int) $data['lang_id'];
        $data['lang_is_rtl'] = (int) $data['lang_is_rtl'];

        return $data;
    }
}
