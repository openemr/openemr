<?php

/**
 * Integration test for FhirOperationExportRestController::processResourceExportForJob
 * on the zero-success path.
 *
 * Locks the behavior PR #14617 fixed: when an export produces no
 * output (empty resources list, or every resource produced null),
 * ExportJob.output must serialize as the JSON string "[]", not
 * "null". The pre-fix typo (`$ouputResult = []`) left `$outputResult`
 * undefined on the empty-foreach path, and `json_encode` on an
 * undefined variable yields `"null"` with an E_WARNING instead of
 * the intended empty array.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR\Operations;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\RestControllers\FHIR\Operations\FhirOperationExportRestController;
use OpenEMR\Services\FHIR\FhirExportJobService;
use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;
use OpenEMR\Tests\Support\Fixtures\UuidRegistryCleanup;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FhirOperationExportZeroSuccessIntegrationTest extends TestCase
{
    private const CLIENT_TAG = 'test-fixture-export-zero-success-client';

    private FhirExportJobService $jobService;

    /**
     * ExportJob row ids created by the test for tearDown sweep.
     *
     * @var list<int>
     */
    private array $createdJobIds = [];

    protected function setUp(): void
    {
        $this->jobService = new FhirExportJobService();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdJobIds as $id) {
            UuidRegistryCleanup::sweepRowsAndUuidEntries(
                'export_job',
                'id = ?',
                [$id]
            );
        }
        $this->createdJobIds = [];
    }

    /**
     * An ExportJob with no resources exercises the branch where the
     * resource foreach never runs. `$outputResult` is used after the
     * loop at `json_encode($outputResult)` to populate
     * `export_job.output`. PR #14617 initializes it to `[]` so the
     * serialized value is `"[]"`; the pre-fix typo left it undefined
     * and the serialization was `"null"`. This test pins the fixed
     * shape at the service tier, without needing the Bulk Data IG
     * kick-off path (which rejects empty resource lists before this
     * method runs — meaning the api tier cannot reach this branch
     * on its own).
     */
    #[Test]
    public function testEmptyResourcesListProducesEmptyArrayOutputNotNull(): void
    {
        $job = $this->buildZeroResourceExportJob();
        $createdJob = $this->jobService->createJobRequest($job);
        $this->createdJobIds[] = $createdJob->getId();

        $controller = $this->buildControllerForZeroSuccessPath();
        $processedJob = $controller->processResourceExportForJob($createdJob);

        $this->assertSame(
            '[]',
            $processedJob->getOutput(),
            'Zero-success export must emit JSON "[]" (empty array), not "null". '
                . 'See PR #14617 — the pre-fix typo left $outputResult undefined on '
                . 'this branch and json_encode(undefined) returned "null".'
        );
        $this->assertSame(
            '[]',
            $processedJob->getErrors(),
            'The zero-success path also emits "[]" for errors; pin the sibling so '
                . 'a future refactor swapping error initialization surfaces here.'
        );
        $this->assertSame(
            ExportJob::STATUS_COMPLETED,
            $processedJob->getStatus(),
            'processResourceExportForJob marks the job COMPLETED even when no '
                . 'resource produced output — the empty-array shape IS success, '
                . 'not an in-progress state.'
        );
    }

    private function buildZeroResourceExportJob(): ExportJob
    {
        $job = new ExportJob();
        $job->setStartTime(new \DateTime());
        $job->setResourceIncludeTime(new \DateTime('@0'));
        $job->setOutputFormat(ExportJob::OUTPUT_FORMAT_FHIR_NDJSON);
        $job->setResources([]);
        $job->setClientId(self::CLIENT_TAG);
        $job->setUserId('test-fixture-export-zero-success-user');
        $job->setAccessTokenId('test-fixture-export-zero-success-token');
        $job->setExportType(ExportJob::EXPORT_OPERATION_SYSTEM);
        $job->setRequestURI('/fhir/$export');
        $job->setApiBaseUrl('https://localhost/apis/default');
        return $job;
    }

    /**
     * The controller constructor requires a HttpRestRequest with a
     * FhirServiceLocator attribute + an OEGlobalsBag. The zero-success
     * path does not resolve any resource services, so the locator can
     * be empty.
     */
    private function buildControllerForZeroSuccessPath(): FhirOperationExportRestController
    {
        $request = HttpRestRequest::create('/apis/default/fhir/$export', 'GET');
        $request->attributes->set('_serviceLocator', new FhirServiceLocator([]));
        return new FhirOperationExportRestController($request, new OEGlobalsBag());
    }
}
