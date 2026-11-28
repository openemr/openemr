<?php

/**
 * FlowBoardChangeEndpoint
 *
 * The response to the Flow Board's status or room dropdown: a POST of `eid` with `status` or
 * `room` (or both). {"saved": true} on success; otherwise an error status with
 * {"saved": false, "reason": ...} that the board turns into a generic message.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\FlowBoard;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class FlowBoardChangeEndpoint
{
    public function __construct(
        private FlowBoardChange $change,
    ) {
    }

    public function respond(Request $request, string $user): JsonResponse
    {
        if (!$request->isMethod('POST')) {
            return self::failure('method', JsonResponse::HTTP_METHOD_NOT_ALLOWED);
        }

        // Read the fields raw: InputBag's getters throw on an array value (eid[]=1).
        $body = $request->request->all();
        $eventId = $body['eid'] ?? null;
        $status = $body['status'] ?? null;
        $room = $body['room'] ?? null;
        if (
            !is_string($eventId)
            || !ctype_digit($eventId)
            || !(is_string($status) || $status === null)
            || !(is_string($room) || $room === null)
            || ($status === null && $room === null)
            || $status === ''
        ) {
            return self::failure('request', JsonResponse::HTTP_BAD_REQUEST);
        }

        return match ($this->change->apply((int) $eventId, $status, $room, $user)) {
            FlowBoardChangeResult::Saved => new JsonResponse(['saved' => true]),
            FlowBoardChangeResult::NotFound => self::failure('not_found', JsonResponse::HTTP_NOT_FOUND),
            FlowBoardChangeResult::Repeating => self::failure('repeating', JsonResponse::HTTP_CONFLICT),
            FlowBoardChangeResult::UnknownStatus => self::failure('status', JsonResponse::HTTP_BAD_REQUEST),
            FlowBoardChangeResult::UnknownRoom => self::failure('room', JsonResponse::HTTP_BAD_REQUEST),
        };
    }

    private static function failure(string $reason, int $status): JsonResponse
    {
        return new JsonResponse(['saved' => false, 'reason' => $reason], $status);
    }
}
