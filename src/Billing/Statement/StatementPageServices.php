<?php

/**
 * What a page that lists and runs patient statements needs, built from the globals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Core\OEGlobalsBag;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class StatementPageServices
{
    private function __construct(
        public StatementPortal $portal,
        public StatementEmail $email,
        public StatementRunHandler $runHandler,
    ) {
    }

    /**
     * @param \Closure(array<string, mixed>): string $buildStatement make_statement() from the site's statement.inc.php
     * @param string $batchFile $STMT_TEMP_FILE from the site's statement.inc.php
     * @param string $pdfFile $STMT_TEMP_FILE_PDF from the site's statement.inc.php
     * @param string $printCommand OPENEMR_PRINT_COMMAND from the site's config.php
     * @param string $today Y-m-d
     * @param ?\Closure(mixed, mixed, mixed, mixed, mixed, mixed, mixed, mixed, mixed, mixed=, mixed=): (int|string) $sendMail sendMail() from portal/lib/portal_mail.inc.php, when the portal is on
     */
    public static function create(
        OEGlobalsBag $globals,
        SessionInterface $session,
        LoggerInterface $logger,
        \Closure $buildStatement,
        string $batchFile,
        string $pdfFile,
        string $printCommand,
        string $today,
        ?\Closure $sendMail,
    ): self {
        $portal = new StatementPortal(
            $globals->getBoolean('portal_onsite_two_enable'),
            $globals->getString('OE_SITE_DIR') . '/documents/onsite_portal_documents/templates',
            $session,
            $sendMail
        );
        $email = new StatementEmail($logger);
        $htmlStatements = $globals->getString('statement_appearance') === '1';
        $rightToLeft = $session->get('language_direction') === 'rtl';
        $steps = new PageStatementRunSteps($buildStatement, $email, $portal, $htmlStatements, $rightToLeft, $today);
        $output = new StatementFileOutput($pdfFile, $globals->getString('temporary_files_dir'), $htmlStatements, $rightToLeft, $printCommand);
        return new self($portal, $email, new StatementRunHandler(new StatementRun($steps, $logger), $output, $batchFile));
    }
}
