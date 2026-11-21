<?php

/**
 * Whether an unbilled claim already names a file, and whether that file is there.
 *
 * The hold stores the file name before the file is written. The next run
 * uses this to tell a written file from a billed row whose file never landed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

enum UnbilledFileDecision
{
    /**
     * Screen text when the file from the previous run is present.
     */
    public const ALREADY_WRITTEN = 'This claim was already written to a file and is now marked billed.';

    /**
     * Screen text when the previous run named a file that is not there.
     */
    public const FILE_WAS_MISSING = 'The previous claim file was not written. This claim was generated again.';

    /**
     * Screen text when another run still owns the file name.
     */
    public const STILL_BEING_WRITTEN = 'This claim is already being written to a file.';

    /**
     * Screen text when the file was written and the billed update missed.
     */
    public const WRITTEN_NOT_BILLED = 'The claim file was written, but this claim was not marked billed.';

    case None;
    case Missing;
    case Present;

    /**
     * Another run still owns the file name. This run must not clear it.
     */
    case Busy;

    /**
     * An empty name has not been assigned. A name whose file is present was written.
     */
    public static function fromStoredFile(string $storedFile, bool $fileExists): self
    {
        if ($storedFile === '') {
            return self::None;
        }

        return $fileExists ? self::Present : self::Missing;
    }
}
