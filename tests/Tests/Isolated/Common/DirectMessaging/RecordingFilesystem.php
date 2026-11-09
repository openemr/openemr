<?php

/**
 * Test double: a Filesystem that records the temp files it creates, so a test
 * can check that PhiMailConnection::readBlob() removed the one it got back.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use Symfony\Component\Filesystem\Filesystem;

final class RecordingFilesystem extends Filesystem
{
    /** @var list<string> paths returned by tempnam() */
    public array $tempFiles = [];

    public function tempnam(string $dir, string $prefix, string $suffix = ''): string
    {
        $path = parent::tempnam($dir, $prefix, $suffix);
        $this->tempFiles[] = $path;
        return $path;
    }
}
