<?php

/**
 * Pieces of the patient Transactions screens: TransactionDisplay.
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
use OpenEMR\Services\TransactionDisplay;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TransactionDisplayTest extends TestCase
{
    /** @var array<string, array{present: bool, value: mixed}> */
    private array $globals = [];

    protected function setUp(): void
    {
        $bag = OEGlobalsBag::getInstance();
        foreach (['date_display_format', 'time_display_format'] as $key) {
            $this->globals[$key] = ['present' => $bag->has($key), 'value' => $bag->get($key)];
        }
        $bag->set('date_display_format', 1); // mm/dd/yyyy
        $bag->set('time_display_format', 0); // 24 hour
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

    /** Built like TwigContainer::getTwig(), without the kernel. */
    private static function twig(): Environment
    {
        $twig = new Environment(new FilesystemLoader([dirname(__DIR__, 4) . '/templates']), ['autoescape' => false]);
        $twig->addExtension(new TwigExtension(OEGlobalsBag::getInstance()));
        return $twig;
    }

    public function testTheTypeSelectIsTheMarkupThePageAlwaysPrinted(): void
    {
        $types = [
            ['grp_form_id' => 'LBTref', 'grp_title' => 'Referral'],
            ['grp_form_id' => 'LBTptreq', 'grp_title' => 'Patient Request'],
        ];
        $this->assertSame(
            "<select name='title' id='title' class='form-control' onchange='titleChanged()'>\n"
            . "<option value='LBTref'>Referral</option>\n"
            . "<option value='LBTptreq' selected>Patient Request</option>\n"
            . "</select>\n",
            (new TransactionDisplay(self::twig()))->typeSelect($types, 'LBTptreq')
        );
    }

    public function testTheTypeSelectEscapesLayoutValues(): void
    {
        $html = (new TransactionDisplay(self::twig()))
            ->typeSelect([['grp_form_id' => "LBT'x", 'grp_title' => 'Notes <b>&</b>']], null);
        $this->assertStringContainsString("<option value='LBT&#039;x'>Notes &lt;b&gt;&amp;&lt;/b&gt;</option>", $html);
        $this->assertStringNotContainsString('selected', $html);
    }

    /**
     * @return array<string, array{array<string, ?string>, string}>
     */
    public static function transactions(): array
    {
        return [
            'a referral: its Referral Date, no time' => [['refer_date' => '2026-10-19', 'date' => '2026-10-20 09:30:00'], '10/19/2026'],
            'no Referral Date: when it was saved' => [['date' => '2026-10-20 09:30:00'], '10/20/2026 09:30'],
            'an empty Referral Date' => [['refer_date' => '', 'date' => '2026-10-20 09:30:00'], '10/20/2026 09:30'],
            'a Referral Date of 0, which empty() skipped' => [['refer_date' => '0', 'date' => '2026-10-20 09:30:00'], '10/20/2026 09:30'],
            'a Referral Date too short to format' => [['refer_date' => 'soon', 'date' => '2026-10-20 09:30:00'], 'soon'],
        ];
    }

    /**
     * @param array<string, ?string> $transaction
     */
    #[DataProvider('transactions')]
    public function testTheListDate(array $transaction, string $listed): void
    {
        $this->assertSame($listed, TransactionDisplay::listDate($transaction));
    }
}
