<?php

/**
 * Reading the review actor from a portal or staff session.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview;

use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewActorType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class SessionReviewActorIsolatedTest extends TestCase
{
    /**
     * @param array<string, mixed> $values
     */
    private function session(array $values): Session
    {
        $session = new Session(new MockArraySessionStorage());
        foreach ($values as $key => $value) {
            $session->set($key, $value);
        }
        return $session;
    }

    public function testAPortalSessionIsThePatient(): void
    {
        $actor = SessionReviewActor::from($this->session([
            'patient_portal_onsite_two' => 1,
            'pid' => '7',
            'portal_username' => 'ann7',
            'authUserID' => 3,
        ]));

        $this->assertSame(ReviewActorType::Patient, $actor->type);
        $this->assertSame(7, $actor->id);
        $this->assertSame('ann7', $actor->name);
    }

    public function testAStaffSessionIsTheUser(): void
    {
        $actor = SessionReviewActor::from($this->session(['authUserID' => '3', 'authUser' => 'drsmith']));

        $this->assertSame(ReviewActorType::User, $actor->type);
        $this->assertSame(3, $actor->id);
        $this->assertSame('drsmith', $actor->name);
    }

    public function testASessionWithNeitherIsRefused(): void
    {
        $this->expectException(\DomainException::class);
        SessionReviewActor::from($this->session(['authUserID' => '0']));
    }

    public function testAPortalSessionWithoutAPatientIsRefused(): void
    {
        $this->expectException(\DomainException::class);
        SessionReviewActor::patient($this->session(['patient_portal_onsite_two' => 1, 'pid' => 'x']));
    }
}
