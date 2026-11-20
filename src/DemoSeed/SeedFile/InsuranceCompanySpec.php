<?php

/**
 * One `insurance_companies[]` entry.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class InsuranceCompanySpec
{
    /**
     * @param int $typeCode insurance_type_codes.id
     */
    public function __construct(
        public string $key,
        public string $name,
        public int $typeCode,
        public ?string $cmsId,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['key', 'name', 'type', 'cms_id']);
        $type = $map->int('type');
        if ($type < 1) {
            throw SeedFileException::at($map->pathOf('type'), 'must be an insurance_type_codes id');
        }
        $cmsId = $map->optionalString('cms_id');
        if ($cmsId !== null && strlen($cmsId) > 15) {
            throw SeedFileException::at($map->pathOf('cms_id'), 'must be at most 15 characters');
        }
        return new self($map->string('key'), $map->string('name'), $type, $cmsId);
    }

    /**
     * What `{CMS}` stands for in a number format.
     */
    public function numberPrefix(): string
    {
        return $this->cmsId ?? strtoupper($this->key);
    }
}
