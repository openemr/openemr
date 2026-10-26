<?php

/**
 * Where referrals stand, as the referral pages show it: ReferralDisplay (#14710).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Common\Twig\TwigExtension;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\ReferralDisplay;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ReferralDisplayTest extends TestCase
{
    private const TODAY = '2026-10-19';

    /** @var array<string, array{present: bool, value: mixed}> */
    private array $globals = [];

    protected function setUp(): void
    {
        $bag = OEGlobalsBag::getInstance();
        foreach (['disable_translation'] as $key) {
            $this->globals[$key] = ['present' => $bag->has($key), 'value' => $bag->get($key)];
        }
        // xl() reaches for the translation tables unless this is set
        $bag->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        $bag = OEGlobalsBag::getInstance();
        foreach ($this->globals as $key => $original) {
            if ($original['present']) {
                $bag->set($key, $original['value']);
                continue;
            }
            $bag->remove($key);
            unset($GLOBALS[$key]);
        }
    }

    /**
     * @param array<int, array<string, ?string>> $referrals what ReferralService::getReferral() would find, by transaction id
     * @param list<int> $asked the ids looked up
     */
    private function display(array $referrals = [], array &$asked = []): ReferralDisplay
    {
        $find = static function (int $transactionId) use ($referrals, &$asked): ?array {
            $asked[] = $transactionId;
            return $referrals[$transactionId] ?? null;
        };
        return new ReferralDisplay(self::twig(), $find, new \DateTimeImmutable(self::TODAY), 14);
    }

    /** Built like TwigContainer::getTwig(), without the kernel. */
    private static function twig(): Environment
    {
        $twig = new Environment(new FilesystemLoader([dirname(__DIR__, 4) . '/templates']), ['autoescape' => false]);
        $twig->addExtension(new TwigExtension(OEGlobalsBag::getInstance()));
        return $twig;
    }

    /** Markup with its whitespace between tags dropped, to compare. */
    private static function squeeze(string $html): string
    {
        return trim((string)preg_replace('/>\s+</', '><', (string)preg_replace('/\s+/', ' ', $html)));
    }

    public function testTheTransactionPageShowsASavedReferralsStatus(): void
    {
        $asked = [];
        $display = $this->display([42 => ['task_status' => 'accepted', 'reply_date' => null, 'refer_date' => '2026-01-01']], $asked);

        $this->assertSame(
            '<div class="col-sm-12 mt-3" id="referral_status"><strong>Status:</strong> Accepted <span class="text-danger">Overdue</span></div>',
            self::squeeze($display->forTransactionPage('LBTref', '42'))
        );
        $this->assertSame([42], $asked);
    }

    /**
     * @return array<string, array{mixed, mixed}>
     */
    public static function notASavedReferral(): array
    {
        return [
            'another transaction type' => ['LBTptreq', 42],
            'a new referral, not saved yet' => ['LBTref', ''],
            'no id' => ['LBTref', null],
            'id 0' => ['LBTref', 0],
        ];
    }

    #[DataProvider('notASavedReferral')]
    public function testTheTransactionPageShowsNothingElse(mixed $formId, mixed $transactionId): void
    {
        $asked = [];
        $this->assertSame('', $this->display([], $asked)->forTransactionPage($formId, $transactionId));
        $this->assertSame([], $asked, 'nothing is looked up');
    }

    public function testTheTransactionPageShowsNothingForAReferralItCannotFind(): void
    {
        $this->assertSame('', $this->display()->forTransactionPage('LBTref', 42));
    }

    public function testTheTransactionsListHasAStatusCellForReferralsOnly(): void
    {
        $asked = [];
        $display = $this->display([7 => ['task_status' => null, 'reply_date' => '2026-10-12', 'refer_date' => '2026-10-01']], $asked);

        $this->assertSame('<td> Replied </td>', self::squeeze($display->transactionCell(['id' => '7', 'title' => 'LBTref'])));
        $this->assertSame('<td></td>', self::squeeze($display->transactionCell(['id' => '8', 'title' => 'LBTptreq'])));
        $this->assertSame([7], $asked, 'only the referral is looked up');
    }

    public function testTheReportHasAStatusCellFromItsRow(): void
    {
        $this->assertSame(
            '<td> Open <span class="text-danger">Overdue</span></td>',
            self::squeeze($this->display()->reportCell(['task_status' => null, 'reply_date' => '', 'refer_date' => '2026-10-05']))
        );
        $this->assertSame(
            '<td> Completed </td>',
            self::squeeze($this->display()->reportCell(['task_status' => 'completed', 'reply_date' => null, 'refer_date' => '2026-01-01']))
        );
    }

    public function testTheShowChoiceIsOpenOverdueOrAll(): void
    {
        $this->assertSame('open', ReferralDisplay::show('open'));
        $this->assertSame('overdue', ReferralDisplay::show('overdue'));
        $this->assertSame('', ReferralDisplay::show(''));
        $this->assertSame('', ReferralDisplay::show('OPEN'));
        $this->assertSame('', ReferralDisplay::show('closed'));
    }

    public function testTheShowFilterSelectsTheChoice(): void
    {
        $html = self::squeeze($this->display()->showFilter('overdue'));
        $this->assertStringStartsWith("<td class='col-form-label'> Show: </td><td><select name='form_show' id='form_show' class='form-control'>", $html);
        $this->assertStringContainsString("<option value=''>All</option><option value='open'>Open</option><option value='overdue' selected>Overdue</option>", $html);
        $this->assertStringNotContainsString('selected', self::squeeze($this->display()->showFilter('')));
    }

    /**
     * @return array<string, array{array<string, ?string>, string, mixed, bool}>
     */
    public static function rows(): array
    {
        $open = ['task_status' => 'accepted', 'reply_date' => null, 'refer_date' => '2026-10-15', 'facility_id' => '3'];
        $overdue = ['refer_date' => '2026-10-01'] + $open;
        $replied = ['reply_date' => '2026-10-16'] + $open;
        $noFacility = ['facility_id' => null] + $open;
        // row, show, facility => listed
        return [
            'all, any facility' => [$replied, '', '', true],
            'open: an open one' => [$open, 'open', '', true],
            'open: a replied one' => [$replied, 'open', '', false],
            'overdue: an overdue one' => [$overdue, 'overdue', '', true],
            'overdue: an open one not yet overdue' => [$open, 'overdue', '', false],
            'its facility' => [$open, '', '3', true],
            'another facility' => [$open, '', '4', false],
            'its facility, compared loosely' => [$open, '', '3.0', true],
            'a facility, and the row has none' => [$noFacility, '', '3', false],
            'no facility (0): the row has one' => [$open, '', '0', false],
            'no facility (0): the row has none' => [$noFacility, '', '0', true],
            'no facility (0): the row has facility 0' => [['facility_id' => '0'] + $open, '', '0', true],
            'its facility but not open' => [$replied, 'open', '3', false],
        ];
    }

    /**
     * @param array<string, ?string> $row
     */
    #[DataProvider('rows')]
    public function testTheReportKeepsRowsUnderItsFilters(array $row, string $show, mixed $facility, bool $listed): void
    {
        $this->assertSame($listed, $this->display()->keeps($row, $show, $facility));
    }

    public function testTheReportSaysTheRecipientsOrganizationElseTheirName(): void
    {
        $this->assertSame('Valley Clinic', ReferralDisplay::referTo(['organization' => 'Valley Clinic', 'referer_to' => 'Ana Ruiz']));
        $this->assertSame('Ana Ruiz', ReferralDisplay::referTo(['organization' => '', 'referer_to' => 'Ana Ruiz']));
        $this->assertSame('Ana Ruiz', ReferralDisplay::referTo(['organization' => null, 'referer_to' => 'Ana Ruiz']));
        $this->assertSame('0', ReferralDisplay::referTo(['organization' => '0', 'referer_to' => 'Ana Ruiz']));
        $this->assertSame('', ReferralDisplay::referTo(['organization' => null, 'referer_to' => null]), 'no longer in the address book');
    }
}
