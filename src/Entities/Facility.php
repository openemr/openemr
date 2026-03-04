<?php

declare(strict_types=1);

namespace OpenEMR\Entities;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping;
use Ramsey\Uuid\{
    Uuid,
    UuidInterface,
};

#[Mapping\Entity]
#[Mapping\Table(name: 'facility')]
class Facility
{
    #[Mapping\Column(type: Types::INTEGER)]
    #[Mapping\Id]
    #[Mapping\GeneratedValue]
    public readonly int $id;

    #[Mapping\Column(nullable: true)]
    public ?UuidInterface $uuid;

    #[Mapping\Column(nullable: true)]
    public ?string $name;

    #[Mapping\Column(length: 30, nullable: true)]
    public ?string $phone;

    #[Mapping\Column(length: 30, nullable: true)]
    public ?string $fax;

    #[Mapping\Column(nullable: true)]
    public ?string $street;

    #[Mapping\Column(nullable: true)]
    public ?string $city;

    #[Mapping\Column(length: 50, nullable: true)]
    public ?string $state;

    #[Mapping\Column(length: 11, nullable: true)]
    public ?string $postalCode;

    #[Mapping\Column(length: 30, options: ['default' => ''])]
    public string $countryCode = '';

    #[Mapping\Column(length: 15, nullable: true)]
    public ?string $federalEin;

    #[Mapping\Column(nullable: true)]
    public ?string $website;

    #[Mapping\Column(nullable: true)]
    public ?string $email;

    #[Mapping\Column]
    public bool $serviceLocation = true;

    #[Mapping\Column]
    public bool $billingLocation = true;

    #[Mapping\Column]
    public bool $acceptsAssignment = true;

    #[Mapping\Column(type: Types::SMALLINT, nullable: true)]
    public ?int $posCode;

    #[Mapping\Column(length: 25, nullable: true)]
    public ?string $x12SenderId;

    #[Mapping\Column(length: 65, nullable: true)]
    public ?string $attn;

    #[Mapping\Column(length: 60, nullable: true)]
    public ?string $domainIdentifier;

    #[Mapping\Column(length: 15, nullable: true)]
    public ?string $facilityNpi;

    #[Mapping\Column(length: 15, nullable: true)]
    public ?string $facilityTaxonomy;

    #[Mapping\Column(length: 31, options: ['default' => ''])]
    public string $taxIdType = '';

    #[Mapping\Column(length: 7, options: ['default' => ''])]
    public string $color = '';

    #[Mapping\Column]
    public bool $primaryBusinessEntity = true;

    #[Mapping\Column(length: 31, nullable: true)]
    public ?string $facilityCode;

    #[Mapping\Column]
    public bool $extraValidation = true;

    #[Mapping\Column(length: 30, nullable: true)]
    public ?string $mailStreet;

    #[Mapping\Column(length: 30, nullable: true)]
    public ?string $mailStreet2;

    #[Mapping\Column(length: 50, nullable: true)]
    public ?string $mailCity;

    #[Mapping\Column(length: 3, nullable: true)]
    public ?string $mailState;

    #[Mapping\Column(length: 10, nullable: true)]
    public ?string $mailZip;

    #[Mapping\Column(options: ['default' => '', 'comment' => 'HIEs CCDA and FHIR an OID is required/wanted'])]
    public string $oid = '';

    #[Mapping\Column(length: 50, nullable: true)]
    public ?string $iban;

    #[Mapping\Column(type: Types::TEXT, nullable: true)]
    public ?string $info;

    #[Mapping\Column(length: 10, nullable: true)]
    public ?string $wenoId;

    #[Mapping\Column]
    public bool $inactive = false;

    #[Mapping\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeImmutable $dateCreated;

    #[Mapping\Column]
    public DateTimeImmutable $lastUpdated;

    #[Mapping\Column(length: 50, options: ['default' => 'prov', 'comment' => 'Organization type as defined by HL7 Value Set: OrganizationType'])]
    public string $organizationType = 'prov';

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->dateCreated = new DateTimeImmutable();
        $this->lastUpdated = new DateTimeImmutable();
    }
}
