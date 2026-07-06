<?php

/**
 * NursingCard Unit Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Patient\Cards;

use OpenEMR\Events\Patient\Summary\Card\CardInterface;
use OpenEMR\Events\Patient\Summary\Card\CardModel;
use OpenEMR\Patient\Cards\NursingCard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Testable subclass that bypasses DB and kernel dependencies.
 * Overrides the two protected methods that make real DB calls.
 */
class TestableNursingCard extends NursingCard
{
    /** @var array<string,mixed>|null */
    private ?array $fakeAdmission;
    private bool $fakeCollapsed;

    /**
     * @param array<string,mixed>|null $admission
     */
    public function __construct(
        int $pid,
        ?array $admission = null,
        bool $collapsed = false,
        ?\Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher = null
    ) {
        $this->fakeAdmission = $admission;
        $this->fakeCollapsed = $collapsed;
        parent::__construct($pid, $dispatcher);
    }

    /** @return array<string,mixed>|null */
    protected function getActiveAdmission(int $pid): ?array
    {
        return $this->fakeAdmission;
    }

    protected function resolveInitiallyCollapsed(): bool
    {
        return $this->fakeCollapsed;
    }
}

class NursingCardTest extends TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        // OEGlobalsBag reads from $GLOBALS — provide minimum required keys.
        $GLOBALS['webroot'] = '';
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /** @param array<string,mixed>|null $admission */
    private function makeCard(?array $admission = null, bool $collapsed = false): TestableNursingCard
    {
        return new TestableNursingCard(
            pid: 1,
            admission: $admission,
            collapsed: $collapsed,
            dispatcher: $this->dispatcher
        );
    }

    // -------------------------------------------------------------------------
    // Structural / contract tests
    // -------------------------------------------------------------------------

    public function testExtendsCardModel(): void
    {
        $this->assertSame(CardModel::class, get_parent_class(NursingCard::class));
    }

    public function testImplementsCardInterface(): void
    {
        $this->assertContains(CardInterface::class, class_implements(NursingCard::class) ?: []);
    }

    // -------------------------------------------------------------------------
    // Card configuration tests
    // -------------------------------------------------------------------------

    public function testCardHasCorrectIdentifier(): void
    {
        $this->assertSame('nursing_admission', $this->makeCard()->getIdentifier());
    }

    public function testCardHasCorrectTemplateFile(): void
    {
        $this->assertSame('patient/partials/nursing.html.twig', $this->makeCard()->getTemplateFile());
    }

    public function testCardAclRequiresMedAccess(): void
    {
        $this->assertSame(['patients', 'med'], $this->makeCard()->getAcl());
    }

    public function testCardCanCollapse(): void
    {
        $this->assertTrue($this->makeCard()->canCollapse());
    }

    public function testCardCannotAdd(): void
    {
        $this->assertFalse($this->makeCard()->canAdd());
    }

    public function testCardCannotEdit(): void
    {
        $this->assertFalse($this->makeCard()->canEdit());
    }

    // -------------------------------------------------------------------------
    // Template variable tests
    // -------------------------------------------------------------------------

    public function testTemplateVariablesContainPid(): void
    {
        $card = new TestableNursingCard(pid: 42, dispatcher: $this->dispatcher);
        $this->assertSame(42, $card->getTemplateVariables()['pid']);
    }

    public function testTemplateVariablesContainNullAdmissionWhenNoActiveAdmission(): void
    {
        $vars = $this->makeCard(admission: null)->getTemplateVariables();
        $this->assertNull($vars['admission']);
    }

    public function testTemplateVariablesContainAdmissionWhenPatientIsAdmitted(): void
    {
        $admission = [
            'encounter_id'   => 10,
            'encounter'      => 100,
            'admission_date' => '2026-01-15 08:00:00',
            'nro_registro'   => 'REG-001',
            'departamento'   => 'ICU',
            'servicio'       => 'Cardiology',
            'cuarto'         => 'A',
            'cama'           => '3',
        ];

        $vars = $this->makeCard(admission: $admission)->getTemplateVariables();

        $this->assertIsArray($vars['admission']);
        $this->assertSame($admission['departamento'], $vars['admission']['departamento']);
        $this->assertSame($admission['cama'], $vars['admission']['cama']);
    }

    public function testAdmissionDataIncludesExpectedKeys(): void
    {
        $admission = [
            'encounter_id'   => 5,
            'encounter'      => 50,
            'admission_date' => '2026-06-01 10:00:00',
            'nro_registro'   => 'R-005',
            'departamento'   => 'General Ward',
            'servicio'       => 'Internal Medicine',
            'cuarto'         => 'B',
            'cama'           => '7',
        ];

        $vars = $this->makeCard(admission: $admission)->getTemplateVariables();

        $admissionVars = $vars['admission'];
        $this->assertIsArray($admissionVars);
        foreach (['encounter_id', 'encounter', 'admission_date', 'nro_registro', 'departamento', 'servicio', 'cuarto', 'cama'] as $key) {
            $this->assertArrayHasKey($key, $admissionVars, "Missing key: $key");
        }
    }

    // -------------------------------------------------------------------------
    // Collapse state tests
    // -------------------------------------------------------------------------

    public function testCardIsInitiallyCollapsedWhenSettingIsOff(): void
    {
        $card = $this->makeCard(collapsed: true);
        $this->assertTrue($card->isInitiallyCollapsed());
    }

    public function testCardIsExpandedWhenSettingIsOn(): void
    {
        $card = $this->makeCard(collapsed: false);
        $this->assertFalse($card->isInitiallyCollapsed());
    }
}
