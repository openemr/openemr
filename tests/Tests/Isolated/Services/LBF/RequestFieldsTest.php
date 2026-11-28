<?php

/**
 * RequestFields tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\LBF {
    use OpenEMR\Services\LBF\RequestFields;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\HttpFoundation\Exception\BadRequestException;
    use Symfony\Component\HttpFoundation\Request;

    final class RequestFieldsTest extends TestCase
    {
        /**
         * A posted string is used, and the query is not.
         */
        public function testPostedStringWins(): void
        {
            $request = Request::create('/x?form_id=from-query', 'POST', ['form_id' => 'from-body']);
            $this->assertSame('from-body', RequestFields::string($request, 'form_id'));
        }

        /**
         * A missing body field may come from the query.
         */
        public function testQueryFillsAMissingBodyField(): void
        {
            $request = Request::create('/x?form_id=from-query', 'POST');
            $this->assertSame('from-query', RequestFields::string($request, 'form_id'));
        }

        /**
         * A list does not fall through to the query, and it is not an empty string.
         */
        public function testListIsABadRequest(): void
        {
            $request = Request::create('/x?form_id=from-query', 'POST', ['form_id' => ['a', 'b']]);
            $this->expectException(BadRequestException::class);
            RequestFields::string($request, 'form_id');
        }
    }
}
