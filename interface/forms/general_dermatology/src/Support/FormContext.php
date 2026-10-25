<?php

declare(strict_types=1);

namespace GeneralDermatology\Support;

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Thin adapter for OpenEMR 8.5 session + globals access.
 */
final class FormContext
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly OEGlobalsBag $globals,
    ) {
    }

    public static function fromRuntime(): self
    {
        return new self(
            SessionWrapperFactory::getInstance()->getActiveSession(),
            OEGlobalsBag::getInstance(),
        );
    }

    public function session(): SessionInterface
    {
        return $this->session;
    }

    public function globals(): OEGlobalsBag
    {
        return $this->globals;
    }

    public function pid(): int
    {
        return (int) $this->session->get('pid', 0);
    }

    public function encounter(): int|string
    {
        $encounter = $this->session->get('encounter');
        if ($encounter === null || $encounter === '') {
            return 0;
        }

        return is_numeric($encounter) ? (int) $encounter : (string) $encounter;
    }

    public function authUser(): string
    {
        return (string) $this->session->get('authUser', '');
    }

    public function webroot(): string
    {
        return rtrim($this->globals->getString('webroot'), '/');
    }

    public function isDevelopment(): bool
    {
        return (bool) $this->globals->get('development_flag');
    }
}
