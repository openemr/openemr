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
    /**
     * @param ?positive-int $line the line in the file, or null to annotate the file as a whole
     */
    public function __construct(
        public string $file,
        public string $title,
        public string $message,
        public ?int $line = null,
    ) {
    }

    /**
     * The `::error` workflow command, with each value escaped as the Actions toolkit does.
     */
    public function toWorkflowCommand(): string
    {
        return sprintf(
            '::error file=%s%s,title=%s::%s',
            self::escapeProperty($this->file),
            $this->line === null ? '' : ',line=' . $this->line,
            self::escapeProperty($this->title),
            self::escapeData($this->message),
        );
    }

    private static function escapeData(string $value): string
    {
        return strtr($value, ['%' => '%25', chr(13) => '%0D', chr(10) => '%0A']);
    }

    /**
     * Properties also escape the characters that separate them.
     */
    private static function escapeProperty(string $value): string
    {
        return strtr(self::escapeData($value), [':' => '%3A', ',' => '%2C']);
    }
}
