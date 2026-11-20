<?php

/**
 * Test double: an in-memory stream wrapper whose writes always fail, for
 * driving PhiMailConnection::readBlob() into its file-write failure paths.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

final class FailingFileStreamWrapper
{
    public const SCHEME = 'phimail-test';

    /** Refuse to open an existing file for writing. */
    public static bool $failOpenForWrite = false;

    /** @var array<string, true> paths of the files that exist */
    public static array $files = [];

    /** @var resource|null set by PHP */
    public $context;

    public static function register(): void
    {
        self::$failOpenForWrite = false;
        self::$files = [];
        stream_wrapper_register(self::SCHEME, self::class);
    }

    public static function unregister(): void
    {
        stream_wrapper_unregister(self::SCHEME);
    }

    // PHP's stream wrapper protocol fixes this signature, including the reference.
    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        if (str_starts_with($mode, 'x')) {
            if (isset(self::$files[$path])) {
                return false;
            }
            self::$files[$path] = true;
            return true;
        }
        return !self::$failOpenForWrite;
    }

    public function stream_write(string $data): false
    {
        return false;
    }

    public function stream_eof(): bool
    {
        return true;
    }

    public function stream_close(): void
    {
    }

    public function unlink(string $path): bool
    {
        unset(self::$files[$path]);
        return true;
    }

    /**
     * Report every path without a file as a directory, so any directory exists.
     *
     * @return array{mode: int}
     */
    public function url_stat(string $path, int $flags): array
    {
        return ['mode' => isset(self::$files[$path]) ? 0100600 : 040700];
    }
}
