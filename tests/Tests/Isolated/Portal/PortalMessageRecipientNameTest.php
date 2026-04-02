<?php

/**
 * PortalMessageRecipientNameTest
 *
 * Verifies that handle_note.php resolves the recipient display name server-side
 * rather than accepting whatever the client submits, fixing issue #11202 where
 * portal_username was stored as recipient_name instead of the patient's real name.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    craigrallen
 * @copyright Copyright (c) 2026 OpenEMR Community
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Portal;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the fix to issue #11202: chart notes displaying portal_username
 * instead of the patient's real name in the message recipient field.
 */
class PortalMessageRecipientNameTest extends TestCase
{
    /**
     * Verify that handle_note.php contains server-side recipient name resolution.
     *
     * The fix must look up the patient's real name via patient_data JOIN
     * patient_access_onsite using the recipient_id (portal_username).
     */
    #[Test]
    public function handleNoteResolvesRecipientNameServerSide(): void
    {
        $handleNoteFile = realpath(__DIR__ . '/../../../../portal/messaging/handle_note.php');
        $this->assertNotFalse($handleNoteFile, 'portal/messaging/handle_note.php not found');
        $content = file_get_contents($handleNoteFile);
        $this->assertIsString($content);

        // Must join patient_data with patient_access_onsite to look up real name
        $this->assertStringContainsString(
            'patient_access_onsite',
            $content,
            'handle_note.php must look up recipient via patient_access_onsite'
        );
        $this->assertStringContainsString(
            'portal_username',
            $content,
            'handle_note.php must match recipient_id against portal_username'
        );

        // Must use real patient name fields, not just accept POST data
        $this->assertStringContainsString(
            "CONCAT(pd.fname, ' ', pd.lname)",
            $content,
            'handle_note.php must resolve recipient full name from patient_data'
        );
    }

    /**
     * Verify that recipient_name is resolved before it could be set from raw POST.
     *
     * The resolution block must appear before the sendMail call that uses $rn,
     * ensuring the stored name is always the patient's real name.
     */
    #[Test]
    public function handleNoteResolutionAppearsBeforeSendMail(): void
    {
        $handleNoteFile = realpath(__DIR__ . '/../../../../portal/messaging/handle_note.php');
        $this->assertNotFalse($handleNoteFile, 'portal/messaging/handle_note.php not found');
        $content = file_get_contents($handleNoteFile);
        $this->assertIsString($content);

        $resolutionPos = strpos($content, 'patient_access_onsite');
        $sendMailPos   = strpos($content, 'sendMail(');

        $this->assertNotFalse($resolutionPos, 'patient_access_onsite lookup not found');
        $this->assertNotFalse($sendMailPos, 'sendMail() call not found');
        $this->assertLessThan(
            $sendMailPos,
            $resolutionPos,
            'Recipient name resolution must occur before sendMail() is called'
        );
    }

    /**
     * Verify the fix falls back to EMR user lookup for staff-to-staff messages.
     *
     * If the recipient_id does not match a portal patient, handle_note.php must
     * attempt a users table lookup so staff replies continue to work.
     */
    #[Test]
    public function handleNoteFallsBackToUsersTableForStaffRecipients(): void
    {
        $handleNoteFile = realpath(__DIR__ . '/../../../../portal/messaging/handle_note.php');
        $this->assertNotFalse($handleNoteFile, 'portal/messaging/handle_note.php not found');
        $content = file_get_contents($handleNoteFile);
        $this->assertIsString($content);

        $this->assertStringContainsString(
            'FROM users WHERE username',
            $content,
            'handle_note.php must fall back to users table for non-portal recipients'
        );
    }
}
