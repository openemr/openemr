<?php

/**
 * One module for OpenEMR 7.0.x and 8.x. OpenEMR 8 moved session data behind
 * SessionWrapperFactory, CSRF tokens now take the session, and globals live in
 * OEGlobalsBag; 7.0.x uses $_SESSION and $GLOBALS. Everything version-specific
 * goes through here.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Exetazo\Grapheus;

use OpenEMR\Common\Csrf\CsrfUtils;

class Compat
{
    public const CSRF_SUBJECT = 'grapheus';

    /** The OpenEMR 8 session object, or null on 7.0.x. */
    public static function session(): ?object
    {
        $f = 'OpenEMR\\Common\\Session\\SessionWrapperFactory';
        return class_exists($f) ? $f::getInstance()->getActiveSession() : null;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $s = self::session();
        if ($s) {
            return $s->get($key, $default);
        }
        return $_SESSION[$key] ?? $default;
    }

    public static function csrfToken(): string
    {
        $s = self::session();
        return $s ? CsrfUtils::collectCsrfToken($s, self::CSRF_SUBJECT) : CsrfUtils::collectCsrfToken(self::CSRF_SUBJECT);
    }

    public static function csrfValid(string $token): bool
    {
        $s = self::session();
        return $s ? CsrfUtils::verifyCsrfToken($token, $s, self::CSRF_SUBJECT) : CsrfUtils::verifyCsrfToken($token, self::CSRF_SUBJECT);
    }

    /** Let other OpenEMR requests run while a long upload is in progress. */
    public static function releaseSession(): void
    {
        $s = self::session();
        if ($s && method_exists($s, 'save')) {
            $s->save();
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    private static function bag(): ?object
    {
        $b = 'OpenEMR\\Core\\OEGlobalsBag';
        return class_exists($b) ? $b::getInstance() : null;
    }

    public static function webroot(): string
    {
        $b = self::bag();
        return $b ? (string) $b->getWebRoot() : (string) ($GLOBALS['webroot'] ?? '');
    }

    public static function fileroot(): string
    {
        $b = self::bag();
        if ($b) {
            return (string) ($b->get('fileroot') ?? $b->getProjectDir());
        }
        return (string) ($GLOBALS['fileroot'] ?? dirname(__DIR__, 5));
    }

    public static function moduleUrl(string $path = ''): string
    {
        return self::webroot() . '/interface/modules/custom_modules/oe-module-grapheus/public' . ($path === '' ? '' : '/' . ltrim($path, '/'));
    }
}
