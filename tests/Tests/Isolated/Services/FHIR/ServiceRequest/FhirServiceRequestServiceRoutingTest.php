<?php

/**
 * FhirServiceRequestService maps a search to its procedure-order and referral services by category,
 * as the other mapped FHIR services do.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR\ServiceRequest;

use OpenEMR\Services\FHIR\FhirSearchSyntaxError;
use OpenEMR\Services\FHIR\FhirServiceRequestService;
use OpenEMR\Services\FHIR\ServiceRequest\FhirServiceRequestReferralService;
use OpenEMR\Services\Search\SearchParameterSyntaxException;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FhirServiceRequestServiceRoutingTest extends TestCase
{
    private const LABORATORY = '108252007';
    private const REFERRAL = '3457005';

    private RoutingTestStore $orders;
    private RoutingTestStore $referrals;
    private FhirServiceRequestService $service;

    protected function setUp(): void
    {
        $this->orders = new RoutingTestOrders('orders', [self::LABORATORY]);
        $this->referrals = new RoutingTestReferrals('referrals', [self::REFERRAL]);
        $this->service = new FhirServiceRequestService();
        $this->service->setMappedServices([$this->orders, $this->referrals]);
    }

    /**
     * @return list<mixed>
     */
    private function data(ProcessingResult $result): array
    {
        $data = $result->getData();
        return is_array($data) ? array_values($data) : [];
    }

    #[Test]
    public function aSearchWithoutCategoryReachesBothServices(): void
    {
        $result = $this->service->getAll(['status' => 'active']);

        $this->assertSame(1, $this->orders->calls);
        $this->assertSame(1, $this->referrals->calls);
        $this->assertSame(['orders', 'referrals'], $this->data($result));
    }

    #[Test]
    public function anOrderCategoryReachesOnlyTheOrderService(): void
    {
        $result = $this->service->getAll(['category' => 'http://snomed.info/sct|' . self::LABORATORY]);

        $this->assertSame(1, $this->orders->calls);
        $this->assertSame(0, $this->referrals->calls);
        $this->assertSame(['orders'], $this->data($result));
    }

    #[Test]
    public function theReferralCategoryReachesOnlyTheReferralService(): void
    {
        $result = $this->service->getAll(['category' => 'http://snomed.info/sct|' . self::REFERRAL]);

        $this->assertSame(0, $this->orders->calls);
        $this->assertSame(1, $this->referrals->calls);
        $this->assertSame(['referrals'], $this->data($result));
    }

    #[Test]
    public function bothCategoriesReachBothServices(): void
    {
        $this->service->getAll(['category' => self::LABORATORY . ',' . self::REFERRAL]);

        $this->assertSame(1, $this->orders->calls);
        $this->assertSame(1, $this->referrals->calls);
    }

    #[Test]
    public function aCategoryNeitherSupportsReachesBothAndEachAnswersForItsOwnRecords(): void
    {
        $this->service->getAll(['category' => 'http://snomed.info/sct|999999']);

        $this->assertSame(1, $this->orders->calls);
        $this->assertSame(1, $this->referrals->calls);
    }

    #[Test]
    public function anEscapedCommaStaysInsideOneCategoryValue(): void
    {
        // "3457005\,x" is one code, which no service supports, not the referral code and "x"
        $this->service->getAll(['category' => 'http://snomed.info/sct|' . self::REFERRAL . '\\,x']);

        $this->assertSame(1, $this->orders->calls);
        $this->assertSame(1, $this->referrals->calls);
        $this->assertSame([], $this->referrals->categoriesRouted);
    }

    #[Test]
    public function aRepeatedCategoryParameterIsASearchError(): void
    {
        $result = $this->service->getAll(['category' => [self::LABORATORY, self::REFERRAL]]);

        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('category', $messages);
        $this->assertSame(0, $this->orders->calls);
        $this->assertSame(0, $this->referrals->calls);
    }

    #[Test]
    public function anErrorFromOneServiceReturnsTheErrorAndNoData(): void
    {
        $this->orders->failWith = 'code';

        $result = $this->service->getAll([]);

        $this->assertTrue($result->hasErrors());
        $this->assertSame([], $this->data($result));
    }

    #[Test]
    public function aMalformedValueOneServiceRejectsKeepsItsSyntaxErrorMark(): void
    {
        // The mark is what makes the controller answer 400 with an OperationOutcome, not 200 and an empty Bundle.
        $this->referrals->syntaxErrorOn = 'authored';

        $result = $this->service->getAll(['category' => 'http://snomed.info/sct|' . self::REFERRAL, 'authored' => 'not-a-date']);

        $this->assertSame('authored', FhirSearchSyntaxError::find($result));
        $this->assertSame('must be a FHIR date or dateTime.', FhirSearchSyntaxError::clientMessage($result));
        $this->assertSame([], $this->data($result));
    }

    #[Test]
    public function thePatientBindReachesEachService(): void
    {
        $this->service->getAll([], 'patient-uuid');

        $this->assertSame('patient-uuid', $this->orders->lastBind);
        $this->assertSame('patient-uuid', $this->referrals->lastBind);
    }
}

/**
 * A mapped service that records what reaches it and answers with its own name. It extends the
 * referral service only for a complete, typed FhirServiceBase; getAll() and supportsCategory()
 * are its own. The router keys services by class, as the other mapped services do, so each store
 * below is its own class.
 */
abstract class RoutingTestStore extends FhirServiceRequestReferralService
{

    public int $calls = 0;
    public ?string $lastBind = null;
    public ?string $failWith = null;
    public ?string $syntaxErrorOn = null;
    /** @var list<string> */
    public array $categoriesRouted = [];

    /**
     * @param list<string> $categories
     */
    public function __construct(private readonly string $name, private readonly array $categories)
    {
        parent::__construct();
    }

    public function supportsCategory(string $category): bool
    {
        $supported = in_array($category, $this->categories, true);
        if ($supported) {
            $this->categoriesRouted[] = $category;
        }
        return $supported;
    }

    /**
     * @param mixed $fhirSearchParameters
     * @param mixed $puuidBind
     */
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $this->calls++;
        $this->lastBind = is_string($puuidBind) ? $puuidBind : null;
        $result = new ProcessingResult();
        if ($this->syntaxErrorOn !== null) {
            FhirSearchSyntaxError::record($result, new SearchParameterSyntaxException($this->syntaxErrorOn, 'unparsable', clientMessage: 'must be a FHIR date or dateTime.'));
            return $result;
        }
        if ($this->failWith !== null) {
            $result->setValidationMessages([$this->failWith => 'not valid']);
            return $result;
        }
        $result->addData($this->name);
        return $result;
    }
}

class RoutingTestOrders extends RoutingTestStore
{
}

class RoutingTestReferrals extends RoutingTestStore
{
}
