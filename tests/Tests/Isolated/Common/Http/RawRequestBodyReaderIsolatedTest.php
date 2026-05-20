<?php

/**
 * Isolated RawRequestBodyReader tests
 *
 * Verifies the reader returns the stream contents as a typed string, caches
 * subsequent reads, and throws a typed exception on stream-open failure.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Alanis <progradedteam@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\RawPostParserException;
use OpenEMR\Common\Http\RawRequestBodyReader;
use PHPUnit\Framework\TestCase;

class RawRequestBodyReaderIsolatedTest extends TestCase
{
    public function testReadsStreamContents(): void
    {
        $reader = new RawRequestBodyReader(
            'data://text/plain;base64,' . base64_encode('foo=bar&baz=qux'),
        );

        $this->assertSame('foo=bar&baz=qux', $reader->read());
    }

    public function testCachesAcrossReads(): void
    {
        // Use a single tempfile, read once, then truncate underneath: the
        // cached value should not change.
        $path = tempnam(sys_get_temp_dir(), 'oemr-raw-reader-');
        $this->assertNotFalse($path);
        try {
            file_put_contents($path, 'first');
            $reader = new RawRequestBodyReader($path);
            $this->assertSame('first', $reader->read());

            file_put_contents($path, 'second');
            $this->assertSame('first', $reader->read());
        } finally {
            @unlink($path);
        }
    }

    public function testThrowsOnUnreadableStream(): void
    {
        $reader = new RawRequestBodyReader('/this/path/does/not/exist/openemr');

        $this->expectException(RawPostParserException::class);
        $this->expectExceptionMessage('Unable to read raw request body');
        $reader->read();
    }
}
