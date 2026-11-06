<?php

/**
 * JSON responses for the portal/patient API, in the shapes its page scripts expect.
 *
 * Errors keep the old Phreeze shape and status: 401 with `{"success": false, "message": …,
 * "errors": {…}}`. An unexpected failure is logged and reported with a generic message.
 *
 * Invalid UTF-8 in a value (say, a stored document) is sent as U+FFFD rather than failing the
 * whole response.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use OpenEMR\BC\ServiceContainer;
use Symfony\Component\HttpFoundation\Response;

final class PortalJson
{
    public static function ok(mixed $data): Response
    {
        return new Response(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    /**
     * @param array<string, string> $errors camelCase field => message
     * @param int $status the HTTP status; 401 is what the portal pages have always been sent
     */
    public static function error(string $message, array $errors = [], int $status = Response::HTTP_UNAUTHORIZED): Response
    {
        return new Response(
            json_encode(['success' => false, 'message' => $message, 'errors' => $errors], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE),
            $status,
            ['Content-Type' => 'application/json'],
        );
    }

    public static function exception(\Throwable $exception): Response
    {
        ServiceContainer::getLogger()->error('Portal request failed', ['exception' => $exception]);

        return self::error(xl('An unexpected error occurred'));
    }

    /**
     * Decode a request body that must be a JSON object.
     *
     * @throws \InvalidArgumentException when it is not
     */
    public static function body(string $content): \stdClass
    {
        $json = json_decode($content);
        if (!$json instanceof \stdClass) {
            throw new \InvalidArgumentException('The request body does not contain valid JSON');
        }

        return $json;
    }

    /**
     * A body field, or the default when the body leaves it out.
     */
    public static function field(\stdClass $json, string $name, mixed $default = ''): mixed
    {
        return property_exists($json, $name) ? $json->$name : $default;
    }
}
