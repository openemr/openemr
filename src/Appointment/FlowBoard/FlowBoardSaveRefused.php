<?php

/**
 * FlowBoardSaveRefused
 *
 * Thrown inside FlowBoardChange's transaction when the tracker refuses the save, so the
 * arrival encounter created in the same transaction is rolled back.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\FlowBoard;

/**
 * @internal
 */
final class FlowBoardSaveRefused extends \RuntimeException
{
}
