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

use LogicException;
use OpenEMR\BC\ServiceContainer;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

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

    public function __construct(private ?ErxXmlBuilder $xmlBuilder = null)
    {
    }

    /**
     * Set XMLBuilder to handle eRx XML
     */
    public function setXMLBuilder(ErxXmlBuilder $xmlBuilder): static
    {
        $this->xmlBuilder = $xmlBuilder;

        return $this;
    }

    /**
     * Get XMLBuilder for handling eRx XML
     */
    public function getXMLBuilder(): ErxXmlBuilder
    {
        if (!$this->xmlBuilder instanceof ErxXmlBuilder) {
            throw new LogicException('ErxPage has no ErxXmlBuilder');
        }

        return $this->xmlBuilder;
    }

    /**
     * Set the Id of the authenticated user
     */
    public function setAuthUserId(int $userId): static
    {
        $this->authUserId = $userId;

        return $this;
    }

    /**
     * Get the Id of the authenticated user
     */
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

    /**
     * Get the destination for the page request
     */
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

    /**
     * Get the Patient Id for the page request
     */
    public function getPatientId(): int
    {
        return $this->patientId;
    }

    /**
     * Set the Prescription Ids to send with page request
     *
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
     * Get the Prescription Ids for the page request
     *
     * @return list<int>
     */
    public function getPrescriptionIds(): array
    {
        return $this->prescriptionIds;
    }

    /**
     * Set the prescription count for the page request
     */
    public function setPrescriptionCount(int $count): static
    {
        $this->prescriptionCount = $count;

        return $this;
    }

    /**
     * Get the prescription count for the page request
     */
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
        $xml = $this->getXMLBuilder()->getDocument()->saveXML();
        if ($xml === false) {
            throw new RuntimeException('Could not serialize the Ensora eRx request');
        }

        return $xml;
    }

    /**
     * A failed write is logged and dropped, so it never hides the validation
     * errors checkError() returns.
     */
    protected function errorLog(string $message): void
    {
        $message = $this->redactPassword($message);
        $siteDirectory = $this->getXMLBuilder()->getGlobals()->getOpenEMRSiteDirectory();
        if ($siteDirectory === '') {
            ServiceContainer::getLogger()->error('Could not write the Ensora eRx error log: no site directory');
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
            ServiceContainer::getLogger()->error('Could not write the Ensora eRx error log', ['exception' => $e]);
        }
    }

    /**
     * Replace the account password wherever a logged request or response
     * carries it, raw or XML-escaped once or twice.
     */
    private function redactPassword(string $message): string
    {
        $password = $this->getXMLBuilder()->getGlobals()->getCredentials()[2] ?? null;
        if (!is_string($password) || $password === '') {
            return $message;
        }

        $escaped = htmlspecialchars($password, ENT_XML1 | ENT_NOQUOTES);
        $forms = array_unique([htmlspecialchars($escaped, ENT_XML1 | ENT_NOQUOTES), $escaped, $password]);

        return str_replace($forms, '[redacted]', $message);
    }

    /**
     * @return list<string>
     */
    public function checkError(string $xml): array
    {
        $XMLBuilder = $this->getXMLBuilder();

        $result = $XMLBuilder->checkError($xml);

        $errorText = self::errorText($result);
        $errorMessages = $errorText === null ? [] : array_slice(explode('Error', $errorText), 1);

        $debugSetting = $XMLBuilder->getGlobals()->getDebugSetting();

        if (str_contains($result, 'RxEntry.aspx')) {
            $this->errorLog($xml);
            $this->errorLog($result);

            if ($errorMessages === []) {
                $errorMessages[] = xl('An undefined error occurred, please contact your systems administrator.');
            }
        } elseif ($debugSetting !== 0) {
            $debugString = '( ' . xl('DEBUG OUTPUT') . ' )' . PHP_EOL;

            if ($debugSetting & self::DEBUG_XML) {
                $this->errorLog($debugString . $xml);
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
