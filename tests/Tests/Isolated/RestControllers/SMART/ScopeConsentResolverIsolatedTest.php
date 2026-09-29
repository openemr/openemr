<?php

/**
 * Isolated round-trip tests for the SMART consent form: parse requested scopes into cards,
 * simulate the user's checkbox post, resolve, and verify every granted scope passes the
 * grant check against the request.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Validators\ScopeValidatorFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\SMART\ScopeConsentResolver;
use OpenEMR\RestControllers\SMART\ScopePermissionParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScopeConsentResolverIsolatedTest extends TestCase
{
    private const LAB = 'http://terminology.hl7.org/CodeSystem/observation-category|laboratory';
    private const VITALS = 'http://terminology.hl7.org/CodeSystem/observation-category|vital-signs';

    protected function setUp(): void
    {
        // parseScopes() translates resource descriptions, and xl() reaches for the translation
        // tables unless this is set. Declared here rather than inherited from whichever class
        // happened to run first.
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    /**
     * @param list<string> $requested
     * @return array<array-key, mixed>
     */
    private function cards(array $requested): array
    {
        return (new ScopePermissionParser($this->createMock(ScopeRepository::class)))->parseScopes($requested);
    }

    /**
     * The post a browser sends when the user touches nothing: every rendered checkbox checked.
     *
     * @param array<array-key, mixed> $cards
     * @return array<string, array{actions: list<string>, categories: list<string>}>
     */
    private function allChecked(array $cards): array
    {
        $post = [];
        foreach ($cards as $key => $card) {
            $this->assertIsString($key);
            $this->assertIsArray($card);
            $actions = [];
            $this->assertIsArray($card['actions']);
            foreach ($card['actions'] as $action => $state) {
                $this->assertIsArray($state);
                if (is_string($action) && $state['enabled'] === true) {
                    $actions[] = $action;
                }
            }
            $categories = [];
            $this->assertIsArray($card['restrictions']);
            foreach ($card['restrictions'] as $restriction) {
                $this->assertIsArray($restriction);
                $this->assertIsString($restriction['value']);
                $categories[] = $restriction['value'];
            }
            $post[$key] = ['actions' => $actions, 'categories' => $categories];
        }
        return $post;
    }

    /**
     * @param list<string> $requested
     * @param array<array-key, mixed> $grants
     * @param list<string> $plain
     * @return list<string>
     */
    private function resolve(array $requested, array $grants, array $plain = []): array
    {
        return (new ScopeConsentResolver())->resolve($requested, $this->cards($requested), $plain, $grants);
    }

    /**
     * @param list<string> $requested
     * @param list<string> $granted
     */
    private function assertAllGrantable(array $requested, array $granted): void
    {
        $validators = (new ScopeValidatorFactory())->buildScopeValidatorArray($requested);
        foreach ($granted as $scope) {
            $entity = ScopeEntity::createFromString($scope);
            $key = $entity->getScopeLookupKey();
            $this->assertTrue(
                isset($validators[$key]) && $validators[$key]->grantsScope($entity),
                "granted scope {$scope} must pass the grant check against the request"
            );
        }
    }

    /**
     * @return array<string, array{list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function untouchedFormProvider(): array
    {
        return [
            'v1 read + write' => [['patient/QuestionnaireResponse.read', 'patient/QuestionnaireResponse.write']],
            'v2 rs + cud (server-advertised pair)' => [['user/QuestionnaireResponse.rs', 'user/QuestionnaireResponse.cud']],
            'v2 cruds' => [['user/QuestionnaireResponse.cruds']],
            'v1 read then v2 cu' => [['user/QuestionnaireResponse.read', 'user/QuestionnaireResponse.cu']],
            'v2 rs then v1 write' => [['user/QuestionnaireResponse.rs', 'user/QuestionnaireResponse.write']],
            'v2 rs + c' => [['user/QuestionnaireResponse.rs', 'user/QuestionnaireResponse.c']],
            'two contexts' => [['patient/Patient.read', 'user/Patient.read', 'user/Patient.write']],
            'unrestricted Observation' => [['patient/Observation.rs']],
            'restricted Observation' => [['patient/Observation.rs?category=' . self::LAB]],
            'unrestricted + restricted Observation' => [['patient/Observation.rs', 'patient/Observation.rs?category=' . self::LAB]],
        ];
    }

    /**
     * Leaving the form untouched must grant exactly what was requested, in the client's own
     * spelling, for every v1/v2 mix.
     *
     * @param list<string> $requested
     */
    #[DataProvider('untouchedFormProvider')]
    public function testUntouchedFormGrantsExactlyTheRequest(array $requested): void
    {
        $granted = $this->resolve($requested, $this->allChecked($this->cards($requested)));
        $this->assertSame($requested, $granted);
        $this->assertAllGrantable($requested, $granted);
    }

    public function testUncheckingOneActionNarrowsInsteadOfDroppingTheCard(): void
    {
        $requested = ['user/Patient.rs', 'user/Patient.cud'];
        $granted = $this->resolve($requested, ['user-Patient' => ['actions' => ['c', 'r', 'u', 's']]]);
        $this->assertSame(['user/Patient.rs', 'user/Patient.cu'], $granted);
        $this->assertAllGrantable($requested, $granted);
    }

    public function testPartialV1ApprovalDegradesToV2Subset(): void
    {
        $requested = ['patient/Goal.read', 'patient/Goal.write'];
        $granted = $this->resolve($requested, ['patient-Goal' => ['actions' => ['r']]]);
        $this->assertSame(['patient/Goal.r'], $granted);
        $this->assertAllGrantable($requested, $granted);
    }

    public function testUncheckedCardGrantsNothingForThatResource(): void
    {
        $requested = ['user/Patient.rs', 'user/Goal.rs'];
        $granted = $this->resolve($requested, ['user-Goal' => ['actions' => ['r', 's']]]);
        $this->assertSame(['user/Goal.rs'], $granted);
    }

    public function testUncheckingACategoryNarrowsUnrestrictedObservationToTheRest(): void
    {
        $requested = ['patient/Observation.read'];
        $cards = $this->cards($requested);
        $post = $this->allChecked($cards);
        $post['patient-Observation']['categories'] = [self::LAB, self::VITALS];

        $granted = $this->resolve($requested, $post);
        $this->assertSame([
            'patient/Observation.rs?category=' . self::LAB,
            'patient/Observation.rs?category=' . self::VITALS,
        ], $granted);
        $this->assertAllGrantable($requested, $granted);
    }

    public function testRestrictedRequestHonoursItsCategoryCheckbox(): void
    {
        $requested = ['patient/Observation.rs?category=' . self::LAB, 'patient/Observation.rs?category=' . self::VITALS];
        $granted = $this->resolve($requested, ['patient-Observation' => ['actions' => ['r', 's'], 'categories' => [self::VITALS]]]);
        $this->assertSame(['patient/Observation.rs?category=' . self::VITALS], $granted);
    }

    public function testPostedValuesOutsideTheRequestAreIgnored(): void
    {
        $requested = ['patient/Observation.rs?category=' . self::LAB];
        $granted = $this->resolve(
            $requested,
            [
                'patient-Observation' => ['actions' => ['c', 'r', 'u', 'd', 's'], 'categories' => [self::LAB, 'http://evil|anything']],
                'user-Patient' => ['actions' => ['c', 'r', 'u', 'd', 's']],
            ],
            ['user/Patient.cruds']
        );
        $this->assertSame(['patient/Observation.rs?category=' . self::LAB], $granted);
    }

    /**
     * Direct POST callers (the API grant-flow test, customized consent templates) send the
     * requested scope strings in scope[...] with no grant[] data.
     */
    public function testVerbatimRequestedScopesInPlainPostAreHonoured(): void
    {
        $requested = ['openid', 'user/Patient.rs', 'user/Patient.cud'];
        $granted = $this->resolve($requested, [], ['openid', 'user/Patient.rs', 'user/Patient.cud', 'user/Goal.cruds']);
        $this->assertSame($requested, $granted);
    }

    public function testRegistrationFilterKeepsOnlyRegisteredResourceScopes(): void
    {
        $filtered = (new ScopeConsentResolver())->filterToClientRegistration(
            ['openid', 'api:fhir', 'launch/patient', 'user/Patient.cruds', 'user/Goal.rs', 'patient/Observation.rs'],
            ['openid', 'api:fhir', 'launch/patient', 'user/Patient.rs', 'user/Patient.cud', 'patient/Observation.rs?category=' . self::LAB]
        );
        $this->assertSame(['openid', 'api:fhir', 'launch/patient', 'user/Patient.cruds'], $filtered);
    }

    /**
     * A client restored without its registration (as deserializeUserSession() produces) must
     * not have its resource scopes waved through.
     */
    public function testRegistrationFilterFailsClosedWithoutRegisteredScopes(): void
    {
        $filtered = (new ScopeConsentResolver())->filterToClientRegistration(
            ['openid', 'launch/patient', 'user/Patient.rs', 'patient/DocumentReference.$docref'],
            []
        );
        $this->assertSame(['openid', 'launch/patient', 'patient/DocumentReference.$docref'], $filtered);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unsupportedConstraintProvider(): array
    {
        return [
            'category plus another key' => ['patient/Observation.rs?category=' . self::LAB . '&status=final'],
            'non-category key only' => ['patient/Observation.rs?status=final'],
            'array-form category' => ['patient/Observation.rs?category[]=' . self::LAB],
            'repeated category key' => ['patient/Observation.rs?category=' . self::LAB . '&category=' . self::VITALS],
            'empty category' => ['patient/Observation.rs?category='],
        ];
    }

    /**
     * A constraint the user cannot see or toggle on the consent screen must never be granted:
     * not from the card, and not when posted back verbatim.
     */
    #[DataProvider('unsupportedConstraintProvider')]
    public function testUnsupportedConstraintsAreNeverOfferedOrGranted(string $scope): void
    {
        $requested = ['openid', $scope];
        $cards = $this->cards($requested);
        $this->assertSame([], $cards, 'no consent card for an unsupported constraint');

        $everything = ['patient-Observation' => ['actions' => ['c', 'r', 'u', 'd', 's'], 'categories' => [self::LAB, self::VITALS]]];
        $this->assertSame(['openid'], $this->resolve($requested, $everything, ['openid', $scope]));
    }

    public function testUrlEncodedCategoryIsTheSameCategory(): void
    {
        $encoded = 'patient/Observation.rs?category=' . rawurlencode(self::LAB);
        $cards = $this->cards([$encoded]);
        $this->assertSame([], array_diff([self::LAB], $this->categoriesOffered($cards, 'patient-Observation')));

        $this->assertSame([], $this->resolve([$encoded], ['patient-Observation' => ['actions' => ['r', 's'], 'categories' => []]]));
        $this->assertSame([$encoded], $this->resolve([$encoded], ['patient-Observation' => ['actions' => ['r', 's'], 'categories' => [self::LAB]]]));
    }

    /**
     * @param array<array-key, mixed> $cards
     * @return list<string>
     */
    private function categoriesOffered(array $cards, string $key): array
    {
        $card = $cards[$key] ?? null;
        $this->assertIsArray($card);
        $this->assertIsArray($card['restrictions']);
        $values = [];
        foreach ($card['restrictions'] as $restriction) {
            $this->assertIsArray($restriction);
            $this->assertIsString($restriction['value']);
            $values[] = $restriction['value'];
        }
        return $values;
    }

    public function testOperationAndNonResourceScopesFollowTheirOwnCheckbox(): void
    {
        $requested = ['openid', 'launch/patient', 'patient/DocumentReference.$docref', 'patient/DocumentReference.rs'];
        $cards = $this->cards($requested);
        $this->assertSame(['patient-DocumentReference'], array_keys($cards));

        $granted = $this->resolve($requested, $this->allChecked($cards), ['openid', 'patient/DocumentReference.$docref']);
        $this->assertSame(['openid', 'patient/DocumentReference.$docref', 'patient/DocumentReference.rs'], $granted);
    }
}
