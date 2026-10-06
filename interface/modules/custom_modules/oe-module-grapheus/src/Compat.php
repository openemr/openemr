<?php

/**
 * The few OpenEMR services the module needs, in one place: the session, CSRF
 * tokens, the request, paths and attaching a form to an encounter.
 * (OpenEMR 8.x. The 7.0.x build of this file uses the legacy equivalents.)
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Exetazo\Grapheus;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FormService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class Compat
{
    public const CSRF_SUBJECT = 'grapheus';

    private static ?Request $request = null;

    public static function request(): Request
    {
        return self::$request ??= Request::createFromGlobals();
    }

    public static function session(): SessionInterface
    {
        return SessionWrapperFactory::getInstance()->getActiveSession();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::session()->get($key, $default);
    }

    public static function csrfToken(): string
    {
        return CsrfUtils::collectCsrfToken(self::session(), self::CSRF_SUBJECT);
    }

    public static function csrfValid(string $token): bool
    {
        return CsrfUtils::verifyCsrfToken($token, self::session(), self::CSRF_SUBJECT);
    }

    /** Let other OpenEMR requests run while a long upload is in progress. */
    public static function releaseSession(): void
    {
        self::session()->save();
    }

    public static function webroot(): string
    {
        return OEGlobalsBag::getInstance()->getWebRoot();
    }

    public static function fileroot(): string
    {
        return OEGlobalsBag::getInstance()->getProjectDir();
    }

    public static function moduleUrl(string $path = ''): string
    {
        return self::webroot() . '/interface/modules/custom_modules/oe-module-grapheus/public' . ($path === '' ? '' : '/' . ltrim($path, '/'));
    }

    public static function addForm(int $encounter, string $name, int $formId, string $dir, int $pid, int $authorized): void
    {
        (new FormService())->addForm($encounter, $name, $formId, $dir, $pid, (string) $authorized);
    }
}
