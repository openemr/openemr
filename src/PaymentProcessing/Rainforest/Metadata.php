<?php

/**
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright (c) 2026 OpenCoreEMR, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      https://www.open-emr.org
 * @package   OpenEMR
 */

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest;

use JsonSerializable;
use UnexpectedValueException;

/**
 * Data exchange class for dealing with Rainforest metadata payloads and
 * translating back into OpenEMR payment info
 */
readonly class Metadata implements JsonSerializable
{
    /**
     * @param EncounterData[] $encounters
     */
    public function __construct(
        public string $patientId,
        public array $encounters,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'patientId' => $this->patientId,
            'encounters' => $this->encounters,
            'formatVersion' => 1,
        ];
    }

    public static function fromParsedJson(array $data): Metadata
    {
        if ($data['formatVersion'] !== 1) {
            throw new UnexpectedValueException('Unknown format version');
        }
        return new Metadata(
            patientId: $data['patientId'],
            encounters: array_map(EncounterData::fromParsedJson(...), $data['encounters']),
        );
    }
}
