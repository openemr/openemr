<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest;

use JsonSerializable;

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
            encounters: EncounterData::fromParsedJson($data['encounters']),
        );
    }
}
