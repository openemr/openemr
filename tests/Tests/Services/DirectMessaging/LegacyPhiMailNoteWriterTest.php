<?php

/**
 * Database tests for writing phiMail notes and linking documents to them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\DirectMessaging;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\DirectMessaging\LegacyPhiMailNoteWriter;
use OpenEMR\Services\GpRelationService;
use PHPUnit\Framework\TestCase;

final class LegacyPhiMailNoteWriterTest extends TestCase
{
    // A document ID no fixture uses.
    private const DOCUMENT_ID = 987654301;

    private ?int $noteId = null;

    protected function tearDown(): void
    {
        if ($this->noteId === null) {
            return;
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM pnotes WHERE id = ?', [$this->noteId]);
        QueryUtils::sqlStatementThrowException('DELETE FROM gprelations WHERE id1 = ? AND id2 = ?', [self::DOCUMENT_ID, $this->noteId]);
    }

    public function testAddsANoteFromTheServiceAndLinksADocument(): void
    {
        $writer = new LegacyPhiMailNoteWriter();

        $this->noteId = $writer->addNote(0, 'Direct Message Received.', 'admin');
        $writer->linkDocument(self::DOCUMENT_ID, $this->noteId);

        $note = QueryUtils::querySingleRow(
            'SELECT pid, body, user, title, assigned_to, message_status, activity, authorized FROM pnotes WHERE id = ?',
            [$this->noteId],
        );
        self::assertIsArray($note);
        // The database returns numbers as strings, so compare loosely.
        self::assertEquals(
            [
                'pid' => 0,
                'user' => 'phimail-service',
                'title' => 'Unassigned',
                'assigned_to' => 'admin',
                'message_status' => 'New',
                'activity' => 1,
                'authorized' => 0,
            ],
            array_diff_key($note, ['body' => true]),
        );
        self::assertIsString($note['body']);
        self::assertStringEndsWith('(phimail-service to admin) Direct Message Received.', $note['body']);
        self::assertTrue(GpRelationService::isGpRelation(1, self::DOCUMENT_ID, 6, $this->noteId));
    }
}
