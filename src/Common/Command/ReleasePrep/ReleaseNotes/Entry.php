<?php

/**
 * One CHANGELOG bullet: a PR (or hand-authored entry) destined for a
 * specific Section in the rendered release notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\ReleaseNotes;

final readonly class Entry
{
    public function __construct(
        public string $title,
        public ?int $prNumber = null,
        public ?string $prUrl = null,
    ) {
        if ($title === '') {
            throw new \InvalidArgumentException('Entry title cannot be empty');
        }
        if ($prNumber !== null && $prNumber <= 0) {
            throw new \InvalidArgumentException('Entry prNumber must be positive');
        }
        if (($prNumber === null) !== ($prUrl === null)) {
            throw new \InvalidArgumentException('Entry prNumber and prUrl must both be set or both be null');
        }
    }
}
