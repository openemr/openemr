<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Setting\Service;

use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use Webmozart\Assert\InvalidArgumentException;

abstract class AbstractSettingSectionService implements SettingSectionServiceInterface
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        // @phpstan-ignore-next-line new.static
        return new static(
            GlobalsServiceFactory::getInstance(),
        );
    }

    public function __construct(
        protected readonly GlobalsService $globalsService,
    ) {
    }

    public function getSectionSlugs(): array
    {
        $sectionSlugs = array_map(
            fn (string $sectionName): string => $this->slugify($sectionName),
            $this->getSectionNames(),
        );

        sort($sectionSlugs);

        return $sectionSlugs;
    }

    abstract public function getSectionNames(): array;

    /**
     * We want to slugify section names,
     * so they can be used at API endpoints' paths
     */
    public function slugify(string $sectionName): string
    {
        return preg_replace(
            '/[^A-Za-z0-9-]/',
            '-',
            mb_strtolower($sectionName)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deslugify(string $sectionSlug): ?string
    {
        foreach ($this->getSectionNames() as $sectionName) {
            if ($this->slugify($sectionName) === $sectionSlug) {
                return $sectionName;
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Section "%s" does not exist. Possible ones: %s.',
            $sectionSlug,
            implode(', ', array_map(
                fn (string $sectionSlug): string => sprintf('"%s"', $sectionSlug),
                $this->getSectionSlugs()
            ))
        ));
    }
}
