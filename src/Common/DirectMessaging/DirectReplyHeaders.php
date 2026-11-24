<?php

/**
 * The message ids a received Direct message says it answers (#15137): its In-Reply-To, then its
 * References from the newest back, as RFC 5322 section 3.6.4 has a reply carry them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final class DirectReplyHeaders
{
    /** At most this many ids are looked up, so a long References chain can't make a long query. */
    public const MAX_IDS = 20;

    /**
     * @param string $headers the message's header block, a header per line, as the server sends it
     * @return list<string> the ids without their angle brackets, nearest first, each once
     */
    public static function referencedMessageIds(string $headers): array
    {
        $inReplyTo = [];
        $references = [];
        foreach (self::unfold($headers) as $line) {
            $colon = strpos($line, ':');
            if ($colon === false) {
                continue;
            }
            $name = strtolower(trim(substr($line, 0, $colon)));
            $value = substr($line, $colon + 1);
            if ($name === 'in-reply-to') {
                $inReplyTo = array_merge($inReplyTo, self::ids($value));
            } elseif ($name === 'references') {
                $references = array_merge($references, self::ids($value));
            }
        }
        $ids = array_values(array_unique(array_merge($inReplyTo, array_reverse($references))));
        return array_slice($ids, 0, self::MAX_IDS);
    }

    /**
     * The header lines with folded continuations joined back on (RFC 5322 section 2.2.3).
     *
     * @return list<string>
     */
    private static function unfold(string $headers): array
    {
        $lines = [];
        foreach (preg_split('/\r?\n/', $headers) ?: [] as $line) {
            if ($line !== '' && ($line[0] === ' ' || $line[0] === "\t") && $lines !== []) {
                $lines[count($lines) - 1] .= ' ' . trim($line);
                continue;
            }
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * The <id> tokens in a header value, without their brackets.
     *
     * @return list<string>
     */
    private static function ids(string $value): array
    {
        if (preg_match_all('/<([^<>\s]+)>/', $value, $matches) === false) {
            return [];
        }
        return $matches[1];
    }
}
