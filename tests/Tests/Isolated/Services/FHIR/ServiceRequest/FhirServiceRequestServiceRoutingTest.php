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

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\ServiceRequest;

use OpenEMR\Common\Database\QueryPagination;
use OpenEMR\Services\FHIR\FhirSearchSyntaxError;
use OpenEMR\Services\FHIR\FhirServiceRequestService;
use OpenEMR\Services\FHIR\ServiceRequest\FhirServiceRequestReferralService;
use OpenEMR\Services\Search\SearchParameterSyntaxException;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

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
    public function theSessionAndLoggerReachTheServices(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $logger = new NullLogger();

        $this->service->setSession($session);
        $this->service->setLogger($logger);

        foreach ([$this->orders, $this->referrals] as $store) {
            $this->assertSame($session, $store->getSession());
            $this->assertSame($logger, $store->logger());
        }
    }

    #[Test]
    public function servicesSetAfterTheSessionGetItToo(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $service = new FhirServiceRequestService();
        $service->setSession($session);
        $late = new RoutingTestOrders('orders', [self::LABORATORY]);

        $service->setMappedServices([$late]);

        $this->assertSame($session, $late->getSession());
    }

    #[Test]
    public function anEmptySetOfServicesStaysEmpty(): void
    {
        $this->service->setMappedServices([]);

        $this->assertSame([], $this->service->getMappedServices());
        $result = $this->service->getAll([]);
        $this->assertFalse($result->hasErrors());
        $this->assertSame([], $this->data($result));
    }

    #[Test]
    public function aPageIsCutFromTheCombinedResults(): void
    {
        $this->orders->records = ['o1', 'o2', 'o3'];
        $this->referrals->records = ['r1', 'r2'];

        $first = $this->service->getAll(['_count' => '2']);
        $this->assertSame(['o1', 'o2'], $this->data($first));
        $this->assertTrue($first->getPagination()->hasMoreData());

        $second = $this->service->getAll(['_count' => '2', '_offset' => '2']);
        $this->assertSame(['o3', 'r1'], $this->data($second), 'the page runs on from orders into referrals');
        $this->assertTrue($second->getPagination()->hasMoreData());

        $last = $this->service->getAll(['_count' => '2', '_offset' => '4']);
        $this->assertSame(['r2'], $this->data($last));
        $this->assertFalse($last->getPagination()->hasMoreData());

        // Each store is asked for what the page needs (offset + count); the page is the router's to cut.
        $this->assertSame('6', $this->orders->lastParameters['_count'] ?? null);
        $this->assertSame('6', $this->referrals->lastParameters['_count'] ?? null);
        $this->assertArrayNotHasKey('_offset', $this->referrals->lastParameters);
        $this->assertSame(['o1', 'o2', 'o3', 'r1', 'r2'], $this->data($this->service->getAll([])), 'no _count, no page');
        $this->assertArrayNotHasKey('_count', $this->orders->lastParameters, 'no page, so each store is asked for all its matches');
    }

    #[Test]
    public function eachStoreKeepsOnlyWhatThePageNeeds(): void
    {
        $this->orders->records = self::named('o', 50);
        $this->referrals->records = self::named('r', 50);

        $page = $this->service->getAll(['_count' => '5', '_offset' => '10']);

        $this->assertSame(['o11', 'o12', 'o13', 'o14', 'o15'], $this->data($page));
        $this->assertTrue($page->getPagination()->hasMoreData());
        $this->assertSame('15', $this->orders->lastParameters['_count'] ?? null);
        $this->assertSame(15, $this->orders->kept, 'a store keeps offset + count of its 50, not all of them');
        $this->assertSame(15, $this->referrals->kept);
    }

    #[Test]
    public function theLargestPageIsBoundedToo(): void
    {
        $this->orders->records = self::named('o', 300);
        $this->referrals->records = self::named('r', 300);

        foreach (['200', '500'] as $count) {
            $page = $this->service->getAll(['_count' => $count]);
            $this->assertSame(self::named('o', 200), $this->data($page), "_count=$count is a page of 200");
            $this->assertTrue($page->getPagination()->hasMoreData());
            $this->assertSame('200', $this->orders->lastParameters['_count'] ?? null, "_count=$count");
            $this->assertSame(200, $this->orders->kept, "_count=$count: a store keeps 200 of its 300");
            $this->assertSame(200, $this->referrals->kept);
        }
    }

    #[Test]
    public function aPageEndingPastTheLargestCountAStoreTakesIsStillExact(): void
    {
        // A store's _count stops at QueryPagination::MAX_LIMIT, so it couldn't keep the 256 this page needs.
        $this->orders->records = self::named('o', QueryPagination::MAX_LIMIT + 50);
        $this->referrals->records = self::named('r', 10);
        $offset = QueryPagination::MAX_LIMIT + 45;

        $page = $this->service->getAll(['_count' => '10', '_offset' => (string) $offset]);

        $all = array_merge($this->orders->records, $this->referrals->records);
        $this->assertSame(array_slice($all, $offset, 10), $this->data($page), 'the last five orders, then five referrals');
        $this->assertTrue($page->getPagination()->hasMoreData());
        $this->assertArrayNotHasKey('_count', $this->orders->lastParameters, 'asked for all its matches, then trimmed by the router');
    }

    #[Test]
    public function everyPageIsTheCombinedListCutDirectly(): void
    {
        for ($orders = 0; $orders <= 6; $orders++) {
            for ($referrals = 0; $referrals <= 6; $referrals++) {
                $this->orders->records = self::named('o', $orders);
                $this->referrals->records = self::named('r', $referrals);
                $all = array_merge($this->orders->records, $this->referrals->records);
                for ($count = 1; $count <= 4; $count++) {
                    for ($offset = 0; $offset <= 13; $offset++) {
                        $page = $this->service->getAll(['_count' => (string) $count, '_offset' => (string) $offset]);
                        $case = "$orders orders, $referrals referrals, _count=$count, _offset=$offset";
                        $this->assertSame(array_slice($all, $offset, $count), $this->data($page), $case);
                        $this->assertSame(count($all) > $offset + $count, $page->getPagination()->hasMoreData(), $case);
                        $this->assertLessThanOrEqual($offset + $count, $this->orders->kept, $case);
                        $this->assertLessThanOrEqual($offset + $count, $this->referrals->kept, $case);
                    }
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function named(string $prefix, int $count): array
    {
        return array_map(static fn(int $n): string => $prefix . $n, $count > 0 ? range(1, $count) : []);
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
    /** @var list<string> */
    public array $records = [];
    /** @var array<mixed> */
    public array $lastParameters = [];
    /** How many records the last search kept, after its _count. */
    public int $kept = 0;
    public ?string $syntaxErrorOn = null;
    /** @var list<string> */
    public array $categoriesRouted = [];

    /**
     * @param list<string> $categories
     */
    public function __construct(private readonly string $name, private readonly array $categories)
    {
        parent::__construct();
        $this->records = [$name];
    }

    public function logger(): ?LoggerInterface
    {
        return $this->logger;
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
        $this->lastParameters = is_array($fhirSearchParameters) ? $fhirSearchParameters : [];
        $result = new ProcessingResult();
        if ($this->syntaxErrorOn !== null) {
            FhirSearchSyntaxError::record($result, new SearchParameterSyntaxException($this->syntaxErrorOn, 'unparsable', clientMessage: 'must be a FHIR date or dateTime.'));
            return $result;
        }
        if ($this->failWith !== null) {
            $result->setValidationMessages([$this->failWith => 'not valid']);
            return $result;
        }
        // As FhirServiceBase::getAll() does, a _count sets the result's limit and addData() keeps no more.
        $count = $this->lastParameters['_count'] ?? null;
        if (is_string($count) && ctype_digit($count)) {
            $result->setPagination(new QueryPagination((int) $count));
        }
        foreach ($this->records as $record) {
            $result->addData($record);
        }
        $kept = $result->getData();
        $this->kept = is_array($kept) ? count($kept) : 0;
        return $result;
    }
}

class RoutingTestOrders extends RoutingTestStore
{
}

class RoutingTestReferrals extends RoutingTestStore
{
}
