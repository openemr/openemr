<?php

namespace OpenEMR\Tests\Unit\ClinicalDecisionRules;

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\CodeManager;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\Rule;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteria;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleManager;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleType;
use PHPUnit\Framework\TestCase;
use OpenEMR\ClinicalDecisionRules\Interface\Controller\ControllerEdit;

class ControllerEditTest extends TestCase
{
    private $controller;
    private $ruleManagerMock;
    private $codeManagerMock;

    protected function setUp(): void
    {
        $this->ruleManagerMock = $this->createMock(RuleManager::class);
        $this->codeManagerMock = $this->createMock(CodeManager::class);

        // Injecting mocks into the ControllerEdit class
        $this->controller = new class ($this->ruleManagerMock, $this->codeManagerMock) extends ControllerEdit {
            public function __construct($ruleManager, $codeManager)
            {
                $this->ruleManager = $ruleManager;
                $this->codeManager = $codeManager;
                parent::__construct();
            }

            // Overriding global functions for testing
            protected function _get($var, $default = '')
            {
                return $_GET[$var] ?? $default;
            }

            protected function _post($var, $default = '')
            {
                return $_POST[$var] ?? $default;
            }
        };
    }

    public function testActionSummary()
    {
        $_GET['id'] = 'test_rule_id';

        $ruleMock = $this->createMock(Rule::class);
        $this->ruleManagerMock->method('getRule')->with('test_rule_id')->willReturn($ruleMock);

        $this->controller->_action_summary();

        $this->assertEquals($ruleMock, $this->controller->viewBean->rule);
        $this->assertEquals("summary.php", $this->controller->viewBean->_view);
    }

    public function testActionSubmitSummaryWithNewRule()
    {
        $values = [
            'title' => 'Test Title'
            ,'developer' => 'Test Developer'
            ,'funding_source' => 'Test Funding'
            ,'release' => 'Test Release'
            ,'web_reference' => 'http://test.com'
            ,'bibliographic_citation' => 'Test Citation'
            ,'linked_referential_cds' => 'Test Linked CDS'
            ,'patient_dob_usage' => 'Test DOB is used'
            ,'patient_ethnicity_usage' => 'Test Eth is used'
            ,'patient_health_status_usage' => 'Test Health Status is used'
            ,'patient_gender_identity_usage' => 'Test Gender Identity is used'
            ,'patient_language_usage' => 'Test Language is used'
            ,'patient_race_usage' => 'Test Race is used'
            ,'patient_sex_usage' => 'Test Sex is used'
            ,'patient_sexual_orientation_usage' => 'Test Sexual Orientation is used'
            ,'patient_sodh_usage' => 'Test SODH is used'
            ,'ruleTypes' => [RuleType::PassiveAlert, RuleType::ActiveAlert]
        ];
        foreach ($values as $key => $value) {
            $_POST['fld_' . $key] = $value;
        }

        // TODO: This isn't a true unit test as it relies on another function... can we fix this?
        $expectedRuleId = 2;
        $fullExpectedRuleId = 'rule_' . $expectedRuleId;

        $this->ruleManagerMock->expects($this->atLeast(1))
            ->method('newRule')
            ->willReturn(new Rule());

        $this->ruleManagerMock->expects($this->once())
            ->method('updateSummaryForRule')
            ->with(self::callback(function ($rule) use ($values): bool {
                self::assertInstanceOf(Rule::class, $rule);
                foreach ($values as $key => $value) {
                    $this->assertEquals($value, $rule->$key);
                }
                $this->assertEmpty($rule->id);
                return true;
            }))
            ->willReturn($fullExpectedRuleId);

        $this->controller->_action_submit_summary();
        $this->assertMatchesRegularExpression('/index\.php\?action=edit!intervals&id=' . $fullExpectedRuleId
            . '/', $this->controller->viewBean->_redirect);
    }

    public function testActionIntervals()
    {
        $_GET['id'] = 'test_rule_id';

        $ruleMock = $this->createMock(Rule::class);
        $this->ruleManagerMock->method('getRule')->with('test_rule_id')->willReturn($ruleMock);

        $this->controller->_action_intervals();

        $this->assertEquals($ruleMock, $this->controller->viewBean->rule);
        $this->assertEquals("intervals.php", $this->controller->viewBean->_view);
    }

    public function testActionSubmitIntervals()
    {
        $_POST['id'] = 'test_rule_id';

        $ruleMock = $this->createMock(Rule::class);
        $this->ruleManagerMock->method('getRule')->with('test_rule_id')->willReturn($ruleMock);

        $this->controller->_action_submit_intervals();
        $this->assertMatchesRegularExpression('/index\.php\?action=detail!view&id=test_rule_id/', $this->controller->viewBean->_redirect);
        $this->markTestIncomplete("Test needs to check if the intervals are updated");
    }

    public function testActionFilter()
    {
        $_GET['id'] = 'test_rule_id';
        $_GET['guid'] = 'test_guid';

        $ruleMock = $this->createMock(Rule::class);
        $criteriaMock = $this->createMock(RuleCriteria::class);

        $this->ruleManagerMock->method('getRule')->with('test_rule_id')->willReturn($ruleMock);
        $this->ruleManagerMock->method('getRuleFilterCriteria')->with($ruleMock, 'test_guid')->willReturn($criteriaMock);

        $this->controller->_action_filter();

        $this->assertEquals($ruleMock, $this->controller->viewBean->rule);
        $this->assertEquals("filter", $this->controller->viewBean->type);
        $this->assertEquals($criteriaMock, $this->controller->viewBean->criteria);
    }
}
