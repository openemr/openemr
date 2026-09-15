<?php

/**
 * LoadCodesController backs the Administration -> Coding -> Native Data Loads page, which uploads
 * and installs a code set into the codes table.
 *
 * The page is extensible: it asks listeners which code types they can import
 * ({@see CodeImportSupportedTypeFilterEvent}) and then hands the uploaded file to whichever
 * listener claims it ({@see CodeImportEvent}). Core's own importers are registered last, at
 * {@see CodeImportEvent::PRIORITY_CORE_FALLBACK}, so a module can preempt them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2026 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Events\Codes\CodeImportEvent;
use OpenEMR\Events\Codes\CodeImportSupportedTypeFilterEvent;
use OpenEMR\Services\CodeTypes\CodeImportException;
use OpenEMR\Services\CodeTypes\Importer\BaseCodeTypeImporter;
use OpenEMR\Services\CodeTypes\Importer\RXCUIImportService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class LoadCodesController
{
    public const TEMPLATE = 'super/load_codes.html.twig';

    /** Mirrors the MAX_FILE_SIZE hidden input; the RxNorm monthly release is around 300MB. */
    private const MAX_FILE_SIZE = 350000000;

    /**
     * The importers OpenEMR itself ships, keyed by the code type each one handles.
     *
     * @var array<string, BaseCodeTypeImporter>
     */
    private readonly array $coreImporters;

    /**
     * @param list<BaseCodeTypeImporter> $coreImporters
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
        private readonly LoggerInterface $logger,
        array $coreImporters
    ) {
        $keyed = [];
        foreach ($coreImporters as $importer) {
            $keyed[$importer->getCodeType()] = $importer;
        }
        $this->coreImporters = $keyed;
    }

    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('admin', 'super')) {
            AccessDeniedHelper::denyWithTemplate(
                "ACL check failed for admin/super: Install Code Set",
                xl("Install Code Set")
            );
        }

        // Registered below every module listener so that a module can take over a code type that
        // core also supports simply by claiming the event at the default priority.
        $this->eventDispatcher->addListener(
            CodeImportEvent::EVENT_NAME,
            $this->handleCoreImport(...),
            CodeImportEvent::PRIORITY_CORE_FALLBACK
        );

        $filterEvent = new CodeImportSupportedTypeFilterEvent(array_keys($this->coreImporters));
        $this->eventDispatcher->dispatch($filterEvent, CodeImportSupportedTypeFilterEvent::EVENT_NAME);
        $supportedCodeTypes = $filterEvent->getSupportedCodeTypes();

        $isUpload = $request->request->get('bn_upload') !== null;
        $codeType = $request->request->getString('form_code_type');
        // The replace checkbox defaults to on, which is how this page has always behaved. Once the
        // form has been submitted the posted value is what gets reflected back.
        $isReplace = $isUpload ? $request->request->getBoolean('form_replace') : true;

        $messages = [];
        if ($isUpload) {
            CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
            $messages = $this->handleUpload($request, $codeType, $isReplace, $supportedCodeTypes);
        }

        return $this->render([
            'csrfToken' => CsrfUtils::collectCsrfToken(session: $this->session),
            'messages' => $messages,
            'supportedCodeTypes' => $supportedCodeTypes,
            'selectedCodeType' => $codeType,
            'formReplace' => $isReplace,
            'maxFileSize' => self::MAX_FILE_SIZE,
            'showRxcuiHelp' => in_array(RXCUIImportService::CODE_TYPE_NAME, $supportedCodeTypes, true),
        ]);
    }

    /**
     * @param list<string> $supportedCodeTypes
     *
     * @return array<string, list<string>>
     */
    private function handleUpload(
        Request $request,
        string $codeType,
        bool $isReplace,
        array $supportedCodeTypes
    ): array {
        if (!in_array($codeType, $supportedCodeTypes, true)) {
            return [CodeImportEvent::MESSAGE_TYPE_ERROR => [
                xl('No handler is available for this code type') . ': ' . $codeType,
            ]];
        }

        $upload = $request->files->get('form_file');
        if (!$upload instanceof UploadedFile || !$upload->isValid()) {
            return [CodeImportEvent::MESSAGE_TYPE_ERROR => [
                $this->describeUploadFailure($upload instanceof UploadedFile ? $upload : null),
            ]];
        }

        $importEvent = new CodeImportEvent($codeType, $upload->getPathname(), $isReplace);
        $this->eventDispatcher->dispatch($importEvent, CodeImportEvent::EVENT_NAME);

        if (!$importEvent->isHandled()) {
            return [CodeImportEvent::MESSAGE_TYPE_ERROR => [
                xl('No handler is available for this code type') . ': ' . $codeType,
            ]];
        }

        return $importEvent->getMessages();
    }

    /**
     * Imports the code types core ships with. Runs only when no other listener claimed the event.
     */
    private function handleCoreImport(CodeImportEvent $event): void
    {
        if ($event->isHandled()) {
            return;
        }

        $importer = $this->coreImporters[$event->getCodeType()] ?? null;
        if ($importer === null) {
            return;
        }

        try {
            $result = $importer->import($event->getFilePath(), $event->isReplace());
            $event->addMessage(CodeImportEvent::MESSAGE_TYPE_SUCCESS, xl('Code set load successful.'));
            $event->addMessage(CodeImportEvent::MESSAGE_TYPE_SUCCESS, sprintf(
                xl('Codes inserted: %1$d, codes updated: %2$d'),
                $result['inserted'],
                $result['updated']
            ));
        } catch (CodeImportException $exception) {
            $this->logger->error('Code set import failed', [
                'codeType' => $event->getCodeType(),
                'exception' => $exception,
            ]);
            $event->addMessage(
                CodeImportEvent::MESSAGE_TYPE_ERROR,
                xl('The code set could not be imported. Check the system log for details.')
            );
        }
        $event->setHandled(true);
    }

    /**
     * PHP discards oversized uploads before the script runs, so an invalid upload here is almost
     * always a post_max_size / upload_max_filesize problem.
     */
    private function describeUploadFailure(?UploadedFile $upload): string
    {
        if ($upload === null) {
            return xl('No file was uploaded.');
        }
        return $upload->getErrorMessage() . ' ' . sprintf(
            xl('Check the post_max_size and upload_max_filesize settings in %s.'),
            php_ini_loaded_file() ?: xl('your php.ini')
        );
    }

    /**
     * @param array<string, mixed> $templateVariables
     */
    private function render(array $templateVariables): Response
    {
        return new Response(
            $this->twig->render(self::TEMPLATE, $templateVariables),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}
