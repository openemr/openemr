<?php

namespace OpenEMR\Tests\Unit\ClinicalDecisionRules;

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\CodeManager;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\Rule;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleCriteria;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleManager;
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
        $this->controller = new class($this->ruleManagerMock, $this->codeManagerMock) extends ControllerEdit {
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
//        $_POST['id'] = 'test_rule_id';
        $_POST['fld_ruleTypes'] = 'type1';
        $_POST['fld_title'] = 'Test Title';
        $_POST['fld_developer'] = 'Test Developer';
        $_POST['fld_funding_source'] = 'Test Funding';
        $_POST['fld_release'] = 'Test Release';
        $_POST['fld_web_reference'] = 'http://test.com';
        $_POST['fld_bibliographic_citation'] = 'Test Citation';
        $_POST['fld_linked_referential_cds'] = 'Test Linked CDS';

        $this->ruleManagerMock->expects($this->once())
            ->method('updateSummary')
            ->with(
                null,
                'type1',
                'Test Title',
                'Test Developer',
                'Test Funding',
                'Test Release',
                'http://test.com',
                'Test Citation',
                'Test Linked CDS'
            )
            ->willReturn('test_rule_id');

        $this->controller->_action_submit_summary();
        $this->assertMatchesRegularExpression('/index\.php\?action=edit!intervals&id=test_rule_id/', $this->controller->viewBean->_redirect);
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
