<?php

/**
 * Interface for audit message writers
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

interface WriterInterface
{
    /**
     * Returns true if the message was sent, false if not.
     */
    public function writeMessage(string $message): bool;
}
