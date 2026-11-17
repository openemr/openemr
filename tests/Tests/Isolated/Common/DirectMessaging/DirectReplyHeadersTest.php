<?php

/**
 * Isolated tests for reading which sent messages a received Direct message answers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\DirectReplyHeaders;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DirectReplyHeadersTest extends TestCase
{
    /**
     * @return array<string, array{string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function headersProvider(): array
    {
        return [
            'none' => ["Subject: hello\nFrom: a@direct.example\n", []],
            'in-reply-to' => ["Subject: Re: hello\nIn-Reply-To: <one@direct.example>\n", ['one@direct.example']],
            'in-reply-to first, then references newest first' => [
                "In-Reply-To: <three@x>\nReferences: <one@x> <two@x> <three@x>\n",
                ['three@x', 'two@x', 'one@x'],
            ],
            'folded references' => ["References: <one@x>\n\t<two@x>\n <three@x>\nSubject: x\n", ['three@x', 'two@x', 'one@x']],
            'header names in any case, CRLF line ends' => ["in-reply-to: <one@x>\r\nREFERENCES: <zero@x>\r\n", ['one@x', 'zero@x']],
            'text outside brackets ignored' => ["In-Reply-To: Your message of Monday <one@x> (sent)\n", ['one@x']],
            'other headers that mention ids ignored' => ["Message-ID: <mine@x>\nX-Original: <other@x>\n", []],
            'a folded line before any header ignored' => [" <stray@x>\nIn-Reply-To: <one@x>\n", ['one@x']],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('headersProvider')]
    public function testReadsTheIdsAMessageAnswers(string $headers, array $expected): void
    {
        self::assertSame($expected, DirectReplyHeaders::referencedMessageIds($headers));
    }

    /**
     * A message answers at most so many ids.
     */
    public function testAMessageAnswersAtMostSoManyIds(): void
    {
        $ids = array_map(static fn(int $n): string => '<id' . $n . '@x>', range(1, 30));

        $found = DirectReplyHeaders::referencedMessageIds('References: ' . implode(' ', $ids) . "\n");

        self::assertCount(DirectReplyHeaders::MAX_IDS, $found);
        self::assertSame('id30@x', $found[0]);
    }
}
