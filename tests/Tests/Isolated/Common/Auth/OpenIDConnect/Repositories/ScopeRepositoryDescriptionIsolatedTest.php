<?php

/**
 * Isolated tests for ScopeRepository::lookupDescriptionForScope().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Repositories;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ServerScopeListEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;

class ScopeRepositoryDescriptionIsolatedTest extends TestCase
{
    protected function setUp(): void
    {
        // descriptions are translated, and xl() reaches for the translation tables unless this is set
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    /**
     * Scopes without a resource (profile, email, ...) used to be handed to
     * lookupDescriptionForFullScopeString() as a ScopeEntity, which is a TypeError on the
     * array lookup. The consent screen now asks for a description of every non-card scope.
     */
    public function testEveryServerAdvertisedScopeHasALookupWithoutError(): void
    {
        foreach ([false, true] as $systemScopesEnabled) {
            $serverScopes = new ServerScopeListEntity();
            $serverScopes->setSystemScopesEnabled($systemScopesEnabled);
            $repository = new ScopeRepository();
            $repository->setServerScopeList($serverScopes);

            $all = array_values(array_filter($serverScopes->getAllSupportedScopesList(), is_string(...)));
            $this->assertNotEmpty($all);
            foreach ($all as $scope) {
                $repository->lookupDescriptionForScope($scope);
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testResourceLessScopeReturnsItsDescription(): void
    {
        $this->assertSame('', (new ScopeRepository())->lookupDescriptionForScope('profile'));
        $this->assertNotSame('', (new ScopeRepository())->lookupDescriptionForScope('api:fhir'));
    }
}
