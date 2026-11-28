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

        $body = $request->request;
        $eventId = $body->getString('eid');
        $status = $body->has('status') ? $body->getString('status') : null;
        $room = $body->has('room') ? $body->getString('room') : null;
        if (!ctype_digit($eventId) || ($status === null && $room === null) || $status === '') {
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
