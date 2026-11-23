<?php

/**
 * The response to the tab bar's request for the line under the patient's name:
 * {"parts": ["PID 43", "Male", …]}, or a 404 with no parts when there is no such patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tabs;

use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class PatientBarInfoEndpoint
{
    public function __construct(
        private PatientBarInfoService $service,
    ) {
    }

    /**
     * @param string $rawPid the pid as posted; anything but digits finds no patient
     */
    public function respond(string $rawPid): JsonResponse
    {
        $info = ctype_digit($rawPid) ? $this->service->forPid((int) $rawPid) : null;
        // Symfony marks it private (no shared caches), which patient data needs.
        return new JsonResponse(
            ['parts' => $info?->parts() ?? []],
            $info === null ? JsonResponse::HTTP_NOT_FOUND : JsonResponse::HTTP_OK,
        );
    }
}
