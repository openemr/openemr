<?php

/**
 * One GitHub Actions error annotation
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\ComposerAnnotations;

final readonly class Annotation
{
    public function __construct(
        public string $file,
        public string $title,
        public string $message,
    ) {
    }

    /**
     * The `::error` workflow command, with the message escaped as the Actions toolkit does.
     */
    public function toWorkflowCommand(): string
    {
        $message = strtr($this->message, [
            '%' => '%25',
            chr(13) => '%0D',
            chr(10) => '%0A',
        ]);
        return sprintf('::error file=%s,title=%s::%s', $this->file, $this->title, $message);
    }
}
