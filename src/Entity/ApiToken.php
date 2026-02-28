<?php

declare(strict_types=1);

namespace OpenEMR\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping;

#[Mapping\Entity]
class ApiToken
{
    #[Mapping\Column(type: Types::BIGINT)]
    #[Mapping\Id]
    #[Mapping\GeneratedValue]
    public readonly int $id;

    #[Mapping\Column(length: 40, nullable: true)]
    public ?string $userId;

    #[Mapping\Column(length: 80, nullable: true)]
    public ?string $clientId;

    #[Mapping\Column(length: 128, nullable: true)]
    public ?string $token;

    #[Mapping\Column(nullable: true)]
    public ?DateTimeImmutable $expiry;

    #[Mapping\Column]
    public bool $revoked = false;

    // #[Mapping\Column(length: 65535, nullable: true, type: Types::TEXT)]
    // public ?string $context;
    #[Mapping\Column(nullable: true)]
    public ?array $context;

    // #[Mapping\Column(
    //     length: 65535,
    //     nullable: true,
    //     options: ['comment' => 'json encoded'],
    //     type: Types::TEXT),
    // ]
    // public string $scope;
    #[Mapping\Column(nullable: true)]
    public ?array $scope;
}
