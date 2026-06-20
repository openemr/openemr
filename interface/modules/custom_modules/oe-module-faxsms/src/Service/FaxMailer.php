<?php

/**
 * Send fax documents by email.
 *
 * Each fax provider client used to inline its own static emailDocument
 * helper and (in two of them) its own copy of the tempnam-for-content
 * dance for the email-attachment branch. This service collects both
 * behaviors in one place.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Service;

use MyMailer;
use OpenEMR\Core\OEGlobalsBag;

final readonly class FaxMailer
{
    /**
     * Send a fax document by email with the file as an attachment.
     */
    public static function send(
        string $email,
        string $body,
        string $file,
        array $user = []
    ): string {
        $fromName = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        $desc = xlt("Comment") . ":\n" . text($body) . "\n" . xlt("This email has an attached fax document.");

        $mail = new MyMailer();
        $fromName = text($fromName);
        $from = OEGlobalsBag::getInstance()->getString("practice_return_email_path");

        $mail->AddReplyTo($from, $fromName);
        $mail->SetFrom($from, $from);
        $mail->AddAddress($email, $email);
        $mail->Subject = xlt("Forwarded Fax Document");
        $mail->Body = $desc;
        $mail->AddAttachment($file, self::attachmentName($file));

        return $mail->Send()
            ? xlt("Email successfully sent.")
            : xlt("Error: Email failed") . text($mail->ErrorInfo);
    }

    /**
     * Mail a fax payload that may be either an on-disk plaintext path or
     * raw content bytes. When $payloadIsContent is true, write a per-
     * request plaintext scratch file under sys_get_temp_dir() so the
     * underlying AddAttachment call sees a real file. Returns the scratch
     * path so the caller can add it to its cleanup list, or null if no
     * scratch file was created.
     *
     * The $email parameter is mixed because controllers pull it straight
     * from the request; this helper narrows it once and silently no-ops
     * for non-string values so call sites don't have to repeat the guard.
     */
    public static function mailUploadedDocument(
        mixed $email,
        string $body,
        mixed $payload,
        array $user,
        bool $payloadIsContent
    ): ?string {
        if (!is_string($email)) {
            return null;
        }
        if (!$payloadIsContent) {
            self::send($email, $body, (string)$payload, $user);
            return null;
        }
        $tmp = tempnam(sys_get_temp_dir(), 'fax_');
        if ($tmp === false) {
            return null;
        }
        if (file_put_contents($tmp, (string)$payload) === false) {
            // Scratch file was created by tempnam() but the payload didn't
            // make it onto disk; bail rather than mail an empty attachment.
            // The half-written tempnam gets unlinked best-effort here so it
            // doesn't linger.
            unlink($tmp);
            return null;
        }
        self::send($email, $body, $tmp, $user);
        return $tmp;
    }

    /**
     * Derive a recipient-facing attachment filename whose extension matches
     * the file's actual content. The plaintext payload is delivered through a
     * tempnam scratch file whose "....tmp" suffix would otherwise reach the
     * recipient and trip mail clients (and is what RingCentral rejected on
     * the send side), so sniff the bytes and map to a sensible name.
     */
    private static function attachmentName(string $file): string
    {
        $mime = '';
        if (is_file($file) && function_exists('mime_content_type')) {
            $detected = mime_content_type($file);
            if (is_string($detected)) {
                $mime = strtolower($detected);
            }
        }
        $ext = match ($mime) {
            'image/tiff', 'image/tif' => 'tiff',
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'text/plain' => 'txt',
            default => 'pdf',
        };
        return 'fax_document.' . $ext;
    }
}
