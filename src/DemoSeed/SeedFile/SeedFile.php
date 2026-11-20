<?php

/**
 * A parsed seed file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class SeedFile
{
    /**
     * @param list<FacilitySpec> $facilities
     * @param list<ProviderSpec> $providers
     * @param list<CaseSpec> $cases
     * @param list<ListReference> $listReferences every list value the file uses
     * @param list<string> $sectionsNotLoaded parts of the file this version of
     *        the loader recognizes but doesn't load yet
     */
    public function __construct(
        public int $seed,
        public SyntheaOptions $synthea,
        public ?ContactDataSettings $contactData,
        public ?NamePools $names,
        public array $facilities,
        public array $providers,
        public Rules $rules,
        public array $cases,
        public array $listReferences,
        public array $sectionsNotLoaded,
    ) {
    }

    public function withSynthea(SyntheaOptions $synthea): self
    {
        return new self(
            $this->seed,
            $synthea,
            $this->contactData,
            $this->names,
            $this->facilities,
            $this->providers,
            $this->rules,
            $this->cases,
            $this->listReferences,
            $this->sectionsNotLoaded,
        );
    }

    public function provider(string $key): ProviderSpec
    {
        foreach ($this->providers as $provider) {
            if ($provider->key === $key) {
                return $provider;
            }
        }
        throw new \OutOfBoundsException('Unknown provider key ' . $key);
    }

    public function facility(string $key): FacilitySpec
    {
        foreach ($this->facilities as $facility) {
            if ($facility->key === $key) {
                return $facility;
            }
        }
        throw new \OutOfBoundsException('Unknown facility key ' . $key);
    }
}
