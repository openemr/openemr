<?php

/**
 * Dispatched when an OIDC token is received, before validation.
 *
 * Module listeners can perform provider-specific pre-processing — e.g.,
 * selecting the correct claim mapper or adding validation parameters
 * based on the token's issuer. Listeners may also reject the token
 * early by calling reject().
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Event;

use OpenEMR\Common\Auth\Oidc\Identity\ClaimMapperInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class OidcTokenReceivedEvent extends Event
{
    public const EVENT_NAME = 'oidc.token.received';

    private bool $rejected = false;
    private string $rejectionReason = '';
    private ?ClaimMapperInterface $claimMapper = null;

    /**
     * @param string $rawToken The raw JWT string as received.
     * @param string $issuer   The expected issuer (from configuration).
     */
    public function __construct(
        private readonly string $rawToken,
        private readonly string $issuer,
    ) {
    }

    public function getRawToken(): string
    {
        return $this->rawToken;
    }

    public function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * Reject the token before validation (e.g., unknown issuer, blocked tenant).
     */
    public function reject(string $reason): void
    {
        $this->rejected = true;
        $this->rejectionReason = $reason;
    }

    public function isRejected(): bool
    {
        return $this->rejected;
    }

    public function getRejectionReason(): string
    {
        return $this->rejectionReason;
    }

    /**
     * Override the claim mapper for this token (e.g., GCIP-specific mapper).
     */
    public function setClaimMapper(ClaimMapperInterface $claimMapper): void
    {
        $this->claimMapper = $claimMapper;
    }

    public function getClaimMapper(): ?ClaimMapperInterface
    {
        return $this->claimMapper;
    }
}
