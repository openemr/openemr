<?php

/**
 * AiPatientSearchService Isolated Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dhanvi <dhanvi@openemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Search\AiPatientSearchService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class AiPatientSearchServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear any leftover key
        OEGlobalsBag::getInstance()->set('gemma_api_key', '');
        OEGlobalsBag::getInstance()->set('gemma_model_name', '');
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('gemma_api_key', '');
        OEGlobalsBag::getInstance()->set('gemma_model_name', '');
        parent::tearDown();
    }

    public function testIsAvailableReturnsFalseWhenNoKey(): void
    {
        $service = new AiPatientSearchService(new NullLogger());
        $this->assertFalse($service->isAvailable());
    }

    public function testIsAvailableReturnsTrueWhenKeyIsSet(): void
    {
        OEGlobalsBag::getInstance()->set('gemma_api_key', 'test-api-key-12345');
        $service = new AiPatientSearchService(new NullLogger());
        $this->assertTrue($service->isAvailable());
    }

    public function testParseSearchQueryReturnsNullWhenEmpty(): void
    {
        OEGlobalsBag::getInstance()->set('gemma_api_key', 'test-api-key-12345');
        $service = new AiPatientSearchService(new NullLogger());
        $this->assertNull($service->parseSearchQuery(''));
        $this->assertNull($service->parseSearchQuery('   '));
    }

    public function testParseSearchQueryReturnsNullWhenNoApiKey(): void
    {
        OEGlobalsBag::getInstance()->set('gemma_api_key', '');
        $service = new AiPatientSearchService(new NullLogger());
        $this->assertNull($service->parseSearchQuery('John Smith'));
    }
}
