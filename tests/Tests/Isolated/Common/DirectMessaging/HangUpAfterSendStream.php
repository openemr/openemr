<?php

/**
 * A stream wrapper that plays scripted phiMail replies and fails to flush the
 * OK the client sends after SEND, as when the server hangs up after replying.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

final class HangUpAfterSendStream
{
    public const PROTOCOL = 'phimail-hang-up';

    /** What the server replies, in order */
    public static string $replies = '';

    /** Everything the client wrote */
    public static string $sent = '';

    /** @var resource|null set by PHP for every stream wrapper */
    public $context;

    private int $position = 0;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        return true;
    }

    /**
     * Hand out one line at a time, since PHP drops buffered reads when the client writes.
     */
    public function stream_read(int $count): string
    {
        $end = strpos(self::$replies, "\n", $this->position);
        $length = $end === false ? $count : min($count, $end + 1 - $this->position);
        $chunk = substr(self::$replies, $this->position, $length);
        $this->position += strlen($chunk);
        return $chunk;
    }

    public function stream_write(string $data): int
    {
        self::$sent .= $data;
        return strlen($data);
    }

    public function stream_flush(): bool
    {
        return !str_ends_with(self::$sent, "SEND\nOK\n");
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen(self::$replies);
    }

    public function stream_close(): void
    {
    }
}
