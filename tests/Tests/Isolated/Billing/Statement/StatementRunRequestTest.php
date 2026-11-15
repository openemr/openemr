<?php

/**
 * The statement run a statements form posts: its button, checked invoices and Without Update.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Statement\StatementDelivery;
use OpenEMR\Billing\Statement\StatementRunRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class StatementRunRequestTest extends TestCase
{
    public function testASearchIsNotARun(): void
    {
        $this->assertNull(StatementRunRequest::fromPost(self::post(['form_search' => 'Search', 'form_cb' => ['11' => 'on']])));
    }

    #[DataProvider('buttonProvider')]
    public function testTheButtonPickedTheDelivery(string $button, StatementDelivery $delivery): void
    {
        $run = StatementRunRequest::fromPost(self::post([$button => 'x']));

        $this->assertSame($delivery, $run?->delivery);
    }

    /**
     * @return array<string, array{string, StatementDelivery}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function buttonProvider(): array
    {
        return [
            'print' => ['form_print', StatementDelivery::Print],
            'download' => ['form_download', StatementDelivery::Download],
            'pdf' => ['form_pdf', StatementDelivery::Pdf],
            'email' => ['form_email', StatementDelivery::Email],
            'portal' => ['form_portalnotify', StatementDelivery::Portal],
        ];
    }

    public function testTheCheckedInvoicesAndWithoutUpdate(): void
    {
        $run = StatementRunRequest::fromPost(self::post([
            'form_pdf' => 'x',
            'form_cb' => ['300' => 'on', '306' => 'on', 'x' => 'on'],
            'form_without' => '1',
        ]));

        $this->assertNotNull($run);
        $this->assertSame([300, 306], $run->checkedInvoices);
        $this->assertTrue($run->withoutUpdate);
    }

    public function testNothingChecked(): void
    {
        $run = StatementRunRequest::fromPost(self::post(['form_print' => 'x']));

        $this->assertNotNull($run);
        $this->assertSame([], $run->checkedInvoices);
        $this->assertFalse($run->withoutUpdate);
    }

    /**
     * @param array<string, mixed> $fields
     * @return InputBag<bool|float|int|string>
     */
    private static function post(array $fields): InputBag
    {
        return (new Request(request: $fields))->request;
    }
}
