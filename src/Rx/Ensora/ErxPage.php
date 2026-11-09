<?php

/**
 * Builds, checks and records the request interface/eRx.php posts to launch Ensora eRx.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sam Likins <sam.likins@wsi-services.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2015 Sam Likins <sam.likins@wsi-services.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Encryption\Plaintext;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SensitiveParameter;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use UnexpectedValueException;

class ErxPage
{
    public const int DEBUG_XML = 1;
    public const int DEBUG_RESULT = 2;

    private int $authUserId = 0;
    private string $destination = '';
    private int $patientId = 0;
    /** @var list<int> */
    private array $prescriptionIds = [];
    private int $prescriptionCount = 0;

    public function __construct(
        private readonly ErxXmlBuilder $xmlBuilder,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getXMLBuilder(): ErxXmlBuilder
    {
        return $this->xmlBuilder;
    }

    public function setAuthUserId(int $userId): static
    {
        $this->authUserId = $userId;

        return $this;
    }

    public function getAuthUserId(): int
    {
        return $this->authUserId;
    }

    /**
     * Set the destination for the page request; empty picks one by user role
     */
    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * Set the Patient Id for the page request; 0 sends no patient
     */
    public function setPatientId(int $patientId): static
    {
        $this->patientId = $patientId;

        return $this;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    /**
     * @param string $prescriptionIds colon-separated prescription ids
     */
    public function setPrescriptionIds(string $prescriptionIds): static
    {
        $this->prescriptionIds = [];
        foreach (explode(':', $prescriptionIds) as $id) {
            if (ctype_digit($id) && (int) $id !== 0) {
                $this->prescriptionIds[] = (int) $id;
            }
        }

        return $this;
    }

    /**
     * @return list<int>
     */
    public function getPrescriptionIds(): array
    {
        return $this->prescriptionIds;
    }

    public function setPrescriptionCount(int $count): static
    {
        $this->prescriptionCount = $count;

        return $this;
    }

    public function getPrescriptionCount(): int
    {
        return $this->prescriptionCount;
    }

    /**
     * Check for required PHP extensions, return array of messages for missing extensions
     *
     * @return list<string>
     */
    public function checkForMissingExtensions(): array
    {
        $extensions = [
            'XML',
            'SOAP',
            'cURL',
            'OpenSSL',
        ];

        $messages = [];

        foreach ($extensions as $extension) {
            if (!extension_loaded(strtolower($extension))) {
                $messages[] = xl('Enable Extension') . ' ' . $extension;
            }
        }

        return $messages;
    }

    /**
     * Construct the XML document
     *
     * @return array{
     *   demographics: list<string>,
     *   empty: list<string>,
     *   warning: list<string>,
     * }
     *
     * @throws ErxLaunchBlockedException when the launch cannot go ahead
     */
    public function buildXML(): array
    {
        $XMLBuilder = $this->getXMLBuilder();
        $NCScript = $XMLBuilder->getNCScript();
        $authUserId = $this->getAuthUserId();
        $destination = $this->getDestination();

        $NCScript->appendChild($XMLBuilder->getCredentials());
        $NCScript->appendChild($XMLBuilder->getUserRole($authUserId));
        $NCScript->appendChild($XMLBuilder->getDestination($authUserId, $destination));
        $NCScript->appendChild($XMLBuilder->getAccount());
        $XMLBuilder->appendChildren($NCScript, $XMLBuilder->getStaffElements($authUserId, $destination));
        $XMLBuilder->appendChildren($NCScript, $XMLBuilder->getPatientElements($this->getPatientId(), $this->getPrescriptionCount(), $this->getPrescriptionIds()));

        return [
            'demographics' => $XMLBuilder->getDemographicsCheckMessages(),
            'empty' => $XMLBuilder->getFieldEmptyMessages(),
            'warning' => $XMLBuilder->getWarningMessages(),
        ];
    }

    /**
     * The XML as DOM serialized it. ErxXmlBuilder::createElementText() cleans
     * each value, so the markup itself is never edited.
     */
    public function getXML(): string
    {
        return $this->getXMLBuilder()->getXml();
    }

    /**
     * A failed write is logged and dropped, so it never hides the validation
     * errors checkError() returns.
     */
    protected function errorLog(#[SensitiveParameter] string $message): void
    {
        $message = $this->redactPassword($message);
        $siteDirectory = $this->getXMLBuilder()->getGlobals()->getOpenEMRSiteDirectory();
        if ($siteDirectory === '') {
            $this->logger->error('Could not write the Ensora eRx error log: no site directory');
            return;
        }

        $path = $siteDirectory . '/documents/erx_error';
        $filesystem = new Filesystem();
        try {
            $filesystem->mkdir($path);
            $filesystem->appendToFile(
                $path . '/erx_error-' . date('Y-m-d') . '.log',
                date('Y-m-d H:i:s') . ' ==========> ' . $message . PHP_EOL,
            );
        } catch (IOExceptionInterface $e) {
            $this->logger->error('Could not write the Ensora eRx error log', ['exception' => $e]);
        }
    }

    /**
     * Replace the password the request carried wherever a logged response
     * echoes it, raw or XML-escaped once or twice. Logged requests come from
     * ErxXmlBuilder::getRedactedXml(), which never holds it.
     */
    private function redactPassword(#[SensitiveParameter] string $message): string
    {
        $sent = $this->getXMLBuilder()->getSentPassword();
        if (!$sent instanceof Plaintext || $sent->bytes === '') {
            return $message;
        }

        $password = $sent->bytes;

        // Cover the password as sent and as a page may echo it, escaped once or twice, quotes included.
        $forms = [$password];
        foreach ([ENT_XML1 | ENT_NOQUOTES, ENT_XML1 | ENT_QUOTES, ENT_HTML401 | ENT_QUOTES] as $flags) {
            $once = htmlspecialchars($password, $flags);
            $short = str_replace('&#039;', '&#39;', $once);
            array_push($forms, $once, $short, htmlspecialchars($once, $flags), htmlspecialchars($short, $flags));
        }
        $forms = array_values(array_unique($forms));
        // Replace longer forms first so a shorter one can't split a longer one.
        usort($forms, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));

        return str_replace($forms, '[redacted]', $message);
    }

    /**
     * @return list<string>
     */
    public function checkError(string $xml): array
    {
        $XMLBuilder = $this->getXMLBuilder();

        try {
            $result = $XMLBuilder->checkError($xml);
        } catch (UnexpectedValueException $e) {
            $this->logger->error('Ensora eRx request not sent', ['exception' => $e]);

            return [xl('The Ensora eRx path is not configured, please contact your systems administrator.')];
        } catch (GuzzleException $e) {
            $this->logger->error('Ensora eRx request failed', ['exception' => $e]);

            return [xl('Ensora eRx could not be reached, please contact your systems administrator.')];
        }

        // An empty page has no errors to find, but it means the check failed,
        // so the launch must not go ahead. The old curl call stopped on '0' too.
        if (trim($result) === '' || $result === '0') {
            $this->errorLog($XMLBuilder->getRedactedXml());

            return [xl('Ensora eRx returned an empty response, please contact your systems administrator.')];
        }

        $errorText = self::errorText($result);
        $errorMessages = $errorText === null ? [] : array_slice(explode('Error', $errorText), 1);

        $debugSetting = $XMLBuilder->getGlobals()->getDebugSetting();

        if (str_contains($result, 'RxEntry.aspx')) {
            $this->errorLog($XMLBuilder->getRedactedXml());
            $this->errorLog($result);

            if ($errorMessages === []) {
                $errorMessages[] = xl('An undefined error occurred, please contact your systems administrator.');
            }
        } elseif ($debugSetting !== 0) {
            $debugString = '( ' . xl('DEBUG OUTPUT') . ' )' . PHP_EOL;

            if ($debugSetting & self::DEBUG_XML) {
                $this->errorLog($debugString . $XMLBuilder->getRedactedXml());
            }

            if ($debugSetting & self::DEBUG_RESULT) {
                $this->errorLog($debugString . $result);
            }
        }

        return $errorMessages;
    }

    public function updatePatientData(): void
    {
        $XMLBuilder = $this->getXMLBuilder();
        $Store = $XMLBuilder->getStore();
        $page = $this->getDestination();
        $patientId = $this->getPatientId();

        if ($page === 'compose') {
            $Store->updatePatientImportStatusByPatientId($patientId, 1);
        } elseif ($page === 'medentry') {
            $Store->updatePatientImportStatusByPatientId($patientId, 3);
        }

        foreach ($XMLBuilder->getSentAllergyIds() as $allergyId) {
            $Store->updateAllergyUploadedByPatientIdAllergyId(1, $patientId, $allergyId);
        }

        foreach ($XMLBuilder->getSentPrescriptionIds() as $prescriptionId) {
            $Store->updatePrescriptionsUploadActiveByPatientIdPrescriptionId(1, 0, $patientId, $prescriptionId);
        }

        foreach ($XMLBuilder->getSentMedicationIds() as $medicationId) {
            $Store->updateErxUploadedByListId($medicationId, 1);
        }
    }

    /**
     * The validation errors the response page lists above the original XML.
     */
    private static function errorText(string $page): ?string
    {
        $matched = preg_match('/<textarea.*>(.*)Original XML:/is', $page, $match);
        if ($matched === false) {
            throw new RuntimeException('Could not search the Ensora eRx response for errors');
        }

        return $matched === 1 ? $match[1] : null;
    }
}
