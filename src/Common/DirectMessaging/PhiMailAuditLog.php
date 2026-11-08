<?php

/**
 * Writes the direct-message-check rows of the audit log, which operators search by their text.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\Common\Logging\EventAuditLogger;
use Psr\Log\LoggerInterface;

final readonly class PhiMailAuditLog
{
    private const EVENT = 'direct-message-check';
    private const USER = 'phimail-service';

    public function __construct(
        private EventAuditLogger $auditLogger,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Write an audit row.
     *
     * @param bool $failing true when the caller is about to report a check
     *     failure. A failed audit write is then logged instead of thrown, so it
     *     cannot replace that failure. On every other path it propagates, so a
     *     broken audit log fails the run.
     * @throws DbalException when the row cannot be written and $failing is false
     */
    public function record(bool $success, string $text, ?int $patientId = 0, bool $failing = false): void
    {
        if (!$success) {
            $this->logger->error('phiMail {event}: {text}', ['event' => self::EVENT, 'text' => $text, 'pid' => $patientId]);
        }
        try {
            $this->auditLogger->newEvent(self::EVENT, self::USER, 0, $success ? 1 : 0, $text, $patientId);
        } catch (DbalException $e) {
            if (!$failing) {
                throw $e;
            }
            $this->logger->error('phiMail could not write the audit row', ['event' => self::EVENT, 'exception' => $e]);
        }
    }
}
