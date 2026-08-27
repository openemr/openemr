<?php

/**
 * StatementEngine tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    $moduleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-lbf-statements/src/';
    if (!is_dir($moduleSrc)) {
        throw new RuntimeException('LBF statements module source not found at ' . $moduleSrc);
    }
    spl_autoload_register(static function (string $class) use ($moduleSrc): void {
        $prefix = 'OpenEMR\\Modules\\LbfStatements\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = $moduleSrc . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\LbfStatements {

    use OpenEMR\Modules\LbfStatements\StatementApplier;
    use OpenEMR\Modules\LbfStatements\StatementEngine;
    use OpenEMR\Modules\LbfStatements\StatementParagraph;
    use PHPUnit\Framework\TestCase;

    final class StatementEngineTest extends TestCase
    {
        private StatementEngine $engine;
        private StatementApplier $applier;

        /** @var list<array<string, mixed>> */
        private array $demoRules = [
            [
                'form_id' => 'LBFstmt_demo',
                'source_field_id' => 'num',
                'op' => 'band',
                'min_value' => 10,
                'max_value' => 20,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
                'statement_text' => 'In range.',
                'enabled' => 1,
            ],
        ];

        protected function setUp(): void
        {
            $this->engine = new StatementEngine();
            $this->applier = new StatementApplier();
        }

        public function testBandBelowRange(): void
        {
            $this->assertSame('', $this->paragraph('LBFstmt_demo', ['num' => '5'], $this->demoRules));
        }

        public function testBandInRange(): void
        {
            $this->assertSame('In range.', $this->paragraph('LBFstmt_demo', ['num' => '15'], $this->demoRules));
        }

        public function testBandAboveRange(): void
        {
            $this->assertSame('', $this->paragraph('LBFstmt_demo', ['num' => '25'], $this->demoRules));
        }

        public function testInterpolateSource(): void
        {
            $rules = [[
                'form_id' => 'LBFstmt_demo',
                'source_field_id' => 'num',
                'op' => 'band',
                'min_value' => 1,
                'max_value' => 10,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
                'statement_text' => 'Measured {source} cm',
                'enabled' => 1,
            ]];
            $this->assertSame('Measured 4 cm.', $this->paragraph('LBFstmt_demo', ['num' => '4'], $rules));
        }

        public function testRatioLt(): void
        {
            $rules = [[
                'form_id' => 'LBFdemo',
                'source_field_id' => 'ivs',
                'source_field_id_2' => 'pw',
                'op' => 'ratio_lt',
                'min_value' => 1.1,
                'max_value' => 1.3,
                'min_inclusive' => 1,
                'max_inclusive' => 0,
                'statement_text' => 'Concentric.',
                'enabled' => 1,
            ]];
            $this->assertSame(
                'Concentric.',
                $this->paragraph('LBFdemo', ['ivs' => '1.4', 'pw' => '1.2'], $rules)
            );
        }

        public function testParseSeverityExactAndPipe(): void
        {
            $rules = [[
                'form_id' => 'LBFdemo',
                'source_field_id' => 'grade',
                'op' => 'parse_severity',
                'match_token' => 'mild AR, mild',
                'statement_text' => 'There is mild aortic regurgitation.',
                'enabled' => 1,
            ]];
            $this->assertSame(
                'There is mild aortic regurgitation.',
                $this->paragraph('LBFdemo', ['grade' => 'mild AR'], $rules)
            );
            $this->assertSame(
                'There is mild aortic regurgitation.',
                $this->paragraph('LBFdemo', ['grade' => 'other|mild'], $rules)
            );
        }

        public function testParseSeverityAllowsSpaceAndPlusInOptionId(): void
        {
            $rules = [[
                'form_id' => 'LBFdemo',
                'source_field_id' => 'find',
                'op' => 'parse_severity',
                'match_token' => 'mild + dilated',
                'statement_text' => 'Mildly dilated.',
                'enabled' => 1,
            ]];
            $this->assertSame(
                'Mildly dilated.',
                $this->paragraph('LBFdemo', ['find' => 'mild + dilated'], $rules)
            );
        }

        public function testDisabledRuleDoesNotFire(): void
        {
            $rules = $this->demoRules;
            $rules[0]['enabled'] = 0;
            $this->assertSame('', $this->paragraph('LBFstmt_demo', ['num' => '15'], $rules));
        }

        public function testOverwriteAndAppend(): void
        {
            $actions = $this->engine->evaluate('LBFstmt_demo', ['num' => '15'], $this->demoRules);
            $over = $this->applier->apply(['sum' => 'old'], $actions, 'overwrite', 'sum');
            $this->assertSame('In range.', $over['sum']);
            $app = $this->applier->apply(['sum' => 'old'], $actions, 'append', 'sum');
            $this->assertSame('old In range.', $app['sum']);
        }

        /**
         * @param array<string, string> $values
         * @param list<array<string, mixed>> $rules
         */
        private function paragraph(string $formId, array $values, array $rules): string
        {
            return StatementParagraph::fromActions($this->engine->evaluate($formId, $values, $rules));
        }
    }
}
