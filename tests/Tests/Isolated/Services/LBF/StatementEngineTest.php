<?php

/**
 * StatementEngine tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\LBF {

    use OpenEMR\Services\LBF\StatementApplier;
    use OpenEMR\Services\LBF\StatementEngine;
    use OpenEMR\Services\LBF\StatementParagraph;
    use PHPUnit\Framework\Attributes\DataProvider;
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

        /**
         * Create fixtures for this test case.
         */
        protected function setUp(): void
        {
            $this->engine = new StatementEngine();
            $this->applier = new StatementApplier();
        }

        /**
         * A value below the band does not fire.
         */
        public function testBandBelowRange(): void
        {
            $this->assertSame('', $this->paragraph('LBFstmt_demo', ['num' => '5'], $this->demoRules));
        }

        /**
         * A value inside the band fires the sentence.
         */
        public function testBandInRange(): void
        {
            $this->assertSame('In range.', $this->paragraph('LBFstmt_demo', ['num' => '15'], $this->demoRules));
        }

        /**
         * A value above the band does not fire.
         */
        public function testBandAboveRange(): void
        {
            $this->assertSame('', $this->paragraph('LBFstmt_demo', ['num' => '25'], $this->demoRules));
        }

        /**
         * A percent or a contained range fires. A range that mostly sits in
         * the only band still fires. A range past the band stays quiet.
         */
        public function testBandAcceptsPercentAndContainedRange(): void
        {
            $this->assertSame('In range.', $this->paragraph('LBFstmt_demo', ['num' => '15%'], $this->demoRules));
            $this->assertSame('In range.', $this->paragraph('LBFstmt_demo', ['num' => '12-18 mmHg'], $this->demoRules));
            $this->assertSame('In range.', $this->paragraph('LBFstmt_demo', ['num' => '18-22%'], $this->demoRules));
            $this->assertSame('', $this->paragraph('LBFstmt_demo', ['num' => '22-30%'], $this->demoRules));
            $this->assertSame('', $this->paragraph('LBFstmt_demo', ['num' => 'WNL'], $this->demoRules));
        }

        /**
         * A range that crosses two bands uses the one that covers more of it.
         */
        public function testRangeUsesTheBandThatCoversMoreOfIt(): void
        {
            $rules = [
                [
                    'form_id' => 'LBFdemo',
                    'source_field_id' => 'num',
                    'op' => 'band',
                    'min_value' => 10,
                    'max_value' => 20,
                    'min_inclusive' => 1,
                    'max_inclusive' => 1,
                    'statement_text' => 'Low.',
                    'enabled' => 1,
                ],
                [
                    'form_id' => 'LBFdemo',
                    'source_field_id' => 'num',
                    'op' => 'band',
                    'min_value' => 21,
                    'max_value' => 30,
                    'min_inclusive' => 1,
                    'max_inclusive' => 1,
                    'statement_text' => 'High.',
                    'enabled' => 1,
                ],
            ];
            $this->assertSame('Low.', $this->paragraph('LBFdemo', ['num' => '18-22'], $rules));
            $this->assertSame('High.', $this->paragraph('LBFdemo', ['num' => '22-28'], $rules));
        }

        /**
         * A blank source reads the second field, and only for the matching sex.
         */
        public function testBlankSourceReadsTheSecondFieldForThatSex(): void
        {
            $rules = [
                [
                    'form_id' => 'LBFdemo',
                    'source_field_id' => 'sized_men',
                    'source_field_id_2' => 'size',
                    'op' => 'band',
                    'min_value' => 6.4,
                    'max_value' => 6.8,
                    'min_inclusive' => 1,
                    'max_inclusive' => 0,
                    'match_token' => 'Male',
                    'statement_text' => 'Moderately dilated.',
                    'enabled' => 1,
                ],
                [
                    'form_id' => 'LBFdemo',
                    'source_field_id' => 'sized_women',
                    'source_field_id_2' => 'size',
                    'op' => 'band',
                    'min_value' => 6.2,
                    'max_value' => null,
                    'min_inclusive' => 1,
                    'max_inclusive' => 1,
                    'match_token' => 'Female',
                    'statement_text' => 'Severely dilated.',
                    'enabled' => 1,
                ],
            ];
            $values = ['sized_men' => '', 'sized_women' => '', 'size' => '6.4'];
            $this->assertSame('Moderately dilated.', $this->paragraph('LBFdemo', $values, $rules, 'Male'));
            $this->assertSame('Severely dilated.', $this->paragraph('LBFdemo', $values, $rules, 'Female'));
            $values['sized_men'] = '5.0';
            $this->assertSame('', $this->paragraph('LBFdemo', $values, $rules, 'Male'));
        }

        /**
         * Each echo band, including a reported range that crosses a boundary.
         */
        #[DataProvider('echoBandProvider')]
        public function testEchoBands(string $field, string $value, string $sex, string $expected): void
        {
            $sexArg = $sex === '' ? null : $sex;
            $this->assertSame(
                $expected,
                $this->paragraph('LBFEchocardiogram', [$field => $value], $this->echoRules(), $sexArg)
            );
        }

        /**
         * The filled boxes on a study that reported three findings.
         */
        public function testReportedRangeAndSharedDiameter(): void
        {
            $values = [
                'left_vent_ef' => '45-50%',
                'leftatriummeasure' => '4.2',
                'right_vent_dias' => '3.4',
                'aoritc_valve_flow' => '1.4',
                'left_vent' => '6.4',
                'pa_sys_pres_dat' => 'Wnl',
            ];
            $this->assertSame(
                'The left atrium is mildly dilated. EF is mildly reduced. Cardiology evaluation is strongly recommended. The left ventricle is moderately dilated. RV dimension is normal. There is no aortic stenosis.',
                $this->paragraph('LBFEchocardiogram', $values, $this->echoRules(), 'Male')
            );
        }

        /**
         * Substitute the source value into the sentence.
         */
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

        /**
         * Fire ratio_lt when the first field is below the second.
         */
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

        /**
         * Match a severity token exactly or in a pipe list.
         */
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

        /**
         * Match list option ids that contain spaces or plus.
         */
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

        /**
         * Skip a rule whose enabled flag is off.
         */
        public function testDisabledRuleDoesNotFire(): void
        {
            $rules = $this->demoRules;
            $rules[0]['enabled'] = 0;
            $this->assertSame('', $this->paragraph('LBFstmt_demo', ['num' => '15'], $rules));
        }

        /**
         * Overwrite, append missing sentences, and skip duplicates.
         */
        public function testOverwriteAndAppend(): void
        {
            $actions = $this->engine->evaluate('LBFstmt_demo', ['num' => '15'], $this->demoRules);
            $over = $this->applier->apply(['sum' => 'old'], $actions, 'overwrite', 'sum');
            $this->assertSame('In range.', $over['sum']);
            $app = $this->applier->apply(['sum' => 'old'], $actions, 'append', 'sum');
            $this->assertSame('old In range.', $app['sum']);
            $dup = $this->applier->apply(['sum' => 'In range. Extra note.'], $actions, 'append', 'sum');
            $this->assertSame('In range. Extra note.', $dup['sum']);
            $partial = $this->applier->apply(['sum' => 'Something in range today.'], $actions, 'append', 'sum');
            $this->assertSame('Something in range today. In range.', $partial['sum']);
            $mixed = $this->applier->apply(['sum' => 'A.'], [], 'append', 'sum', 'A. B.');
            $this->assertSame('A. B.', $mixed['sum']);
            $accent = $this->applier->apply(['sum' => 'ÉLEVÉ.'], [], 'append', 'sum', 'élevé.');
            $this->assertSame('ÉLEVÉ.', $accent['sum']);
        }

        /**
         * @param array<string, string> $values
         * @param list<array<string, mixed>> $rules
         */
        private function paragraph(string $formId, array $values, array $rules, ?string $patientSex = null): string
        {
            return StatementParagraph::fromActions($this->engine->evaluate($formId, $values, $rules, $patientSex));
        }

        /**
         * @return list<array<string, mixed>>
         */
        private function echoRules(): array
        {
            $rules = [];
            $band = static function (
                string $field,
                string $text,
                float|null $min,
                float|null $max,
                int $minInc,
                int $maxInc,
                string $field2 = '',
                string $sex = ''
            ) use (&$rules): void {
                $rules[] = [
                    'form_id' => 'LBFEchocardiogram',
                    'source_field_id' => $field,
                    'source_field_id_2' => $field2,
                    'op' => 'band',
                    'min_value' => $min,
                    'max_value' => $max,
                    'min_inclusive' => $minInc,
                    'max_inclusive' => $maxInc,
                    'match_token' => $sex,
                    'statement_text' => $text,
                    'enabled' => 1,
                ];
            };
            $band('leftatriummeasure', 'The left atrium is mildly dilated.', 4.1, 4.4, 1, 1);
            $band('leftatriummeasure', 'The left atrium is moderately dilated.', 4.5, 5.0, 1, 1);
            $band('leftatriummeasure', 'The left atrium is severely dilated.', 5.1, null, 1, 1);
            $band('left_vent_ef', 'EF is extremely reduced. Cardiology evaluation is strongly recommended.', null, 34, 1, 0);
            $band('left_vent_ef', 'EF is moderately severely reduced. Cardiology evaluation is strongly recommended.', 35, 39, 1, 1);
            $band('left_vent_ef', 'EF is moderately reduced. Cardiology evaluation is strongly recommended.', 40, 44, 1, 1);
            $band('left_vent_ef', 'EF is mildly reduced. Cardiology evaluation is strongly recommended.', 45, 49, 1, 1);
            $band('left_vent_ef', 'EF is normal.', 50, 65, 1, 1);
            $band('left_vent_ef', 'The LV is hyperdynamic.', 66, null, 0, 1);
            $band('lvedd_men', 'LV dimension is normal.', 3.7, 5.6, 1, 1, 'left_vent', 'Male');
            $band('lvedd_men', 'The left ventricle is mildly dilated.', 5.6, 6.3, 0, 1, 'left_vent', 'Male');
            $band('lvedd_men', 'The left ventricle is moderately dilated.', 6.4, 6.8, 1, 0, 'left_vent', 'Male');
            $band('lvedd_men', 'The left ventricle is severely dilated.', 6.8, null, 1, 1, 'left_vent', 'Male');
            $band('lvedd_women', 'LV dimension is normal.', 3.5, 5.1, 1, 1, 'left_vent', 'Female');
            $band('lvedd_women', 'The left ventricle is mildly dilated.', 5.2, 5.6, 1, 1, 'left_vent', 'Female');
            $band('lvedd_women', 'The left ventricle is moderately dilated.', 5.7, 6.1, 1, 1, 'left_vent', 'Female');
            $band('lvedd_women', 'The left ventricle is severely dilated.', 6.2, null, 1, 1, 'left_vent', 'Female');
            $band('right_vent_dias', 'RV dimension is normal.', null, 4.1, 1, 0);
            $band('right_vent_dias', 'RV is enlarged.', 4.2, null, 1, 1);
            $band('aoritc_valve_flow', 'There is no aortic stenosis.', null, 2.5, 1, 0);
            $band('aoritc_valve_flow', 'There is mild aortic stenosis.', 2.6, 2.9, 1, 1);
            $band('aoritc_valve_flow', 'There is moderate aortic stenosis.', 3.0, 4.0, 1, 0);
            $band('aoritc_valve_flow', 'There is severe aortic stenosis.', 4.0, null, 1, 1);
            $band('aoritc_valve_open', 'There is severe aortic stenosis.', null, 1.0, 1, 0);
            $band('aoritc_valve_open', 'There is moderate aortic stenosis.', 1.0, 1.5, 1, 0);
            $band('aoritc_valve_open', 'The aortic valve area is normal.', 1.5, null, 1, 1);
            $band('av_pht_ms', 'There is severe aortic regurgitation.', null, 200, 1, 0);
            $band('av_pht_ms', 'There is moderate aortic regurgitation.', 200, 500, 1, 1);
            $band('av_pht_ms', 'There is mild aortic regurgitation.', 500, null, 0, 1);
            $band('ivc_diam_cm', 'IVC is normal in size.', null, 2.0, 1, 0);
            $band('ivc_diam_cm', 'IVC is dilated.', 2.1, null, 1, 1);
            $band('ra_measure_mm', 'The right atrium is normal in size.', null, 45, 1, 0);
            $band('ra_measure_mm', 'The right atrium is dilated.', 46, null, 1, 1);
            $band('mv_area_cm2', 'There is severe mitral stenosis.', null, 1.0, 1, 0);
            $band('mv_area_cm2', 'There is moderate mitral stenosis.', 1.0, 1.5, 1, 0);
            $band('mv_area_cm2', 'There is minimal mitral stenosis.', 1.5, null, 1, 1);
            $band('mv_grad_mmhg', 'There is minimal mitral valve stenosis.', null, 0.5, 1, 0);
            $band('mv_grad_mmhg', 'There is moderate mitral valve stenosis.', 5, 10, 1, 0);
            $band('mv_grad_mmhg', 'There is severe mitral valve stenosis.', 10, null, 1, 1);
            $band('pa_sys_pres_dat', 'The pulmonary artery pressure is normal.', null, 20, 1, 0);
            $band('pa_sys_pres_dat', 'There is mild pulmonary hypertension.', 21, 40, 1, 1);
            $band('pa_sys_pres_dat', 'There is moderate pulmonary hypertension. Cardiology evaluation is strongly recommended.', 41, 50, 1, 1);
            $band('pa_sys_pres_dat', 'There is severe pulmonary hypertension. Cardiology evaluation is strongly recommended.', 51, null, 0, 1);
            $band('ea_ratio', 'There is impaired relaxation of LV.', null, 1, 1, 0);
            $band('ea_ratio', 'E/A ratio is normal.', 1, 1.5, 1, 1);
            $band('ee_avg', "E/e' ratio of greater than 14 is consistent with diastolic dysfunction of LV.", 14, null, 0, 1);
            $band('e_septal', "Septal e' velocity is normal.", null, 7, 1, 0);
            $band('e_lateral', "Lateral e' velocity is normal.", null, 10, 1, 0);
            return $rules;
        }

        /**
         * @return array<string, array{string, string, string, string}>
         *
         * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
         */
        public static function echoBandProvider(): array
        {
            $efMild = 'EF is mildly reduced. Cardiology evaluation is strongly recommended.';
            $efMod = 'EF is moderately reduced. Cardiology evaluation is strongly recommended.';
            $efWorse = 'EF is moderately severely reduced. Cardiology evaluation is strongly recommended.';
            $efExtreme = 'EF is extremely reduced. Cardiology evaluation is strongly recommended.';
            $efNormal = 'EF is normal.';
            $hyper = 'The LV is hyperdynamic.';
            return [
                'ef 33' => ['left_vent_ef', '33', '', $efExtreme],
                'ef 34 quiet' => ['left_vent_ef', '34', '', ''],
                'ef 35' => ['left_vent_ef', '35', '', $efWorse],
                'ef 40-45' => ['left_vent_ef', '40-45%', '', $efMod],
                'ef 45-50' => ['left_vent_ef', '45-50%', '', $efMild],
                'ef 50' => ['left_vent_ef', '50%', '', $efNormal],
                'ef 55-60' => ['left_vent_ef', '55-60%', '', $efNormal],
                'ef 60-65' => ['left_vent_ef', '60-65%', '', $efNormal],
                'ef 65-70' => ['left_vent_ef', '65-70%', '', $hyper],
                'ef 66 quiet' => ['left_vent_ef', '66', '', ''],
                'ef 30-35' => ['left_vent_ef', '30-35%', '', $efExtreme],
                'ef 35-40' => ['left_vent_ef', '35-40%', '', $efWorse],
                'ef words' => ['left_vent_ef', 'WNL', '', ''],
                'la 4.2' => ['leftatriummeasure', '4.2', '', 'The left atrium is mildly dilated.'],
                'la 4.0 quiet' => ['leftatriummeasure', '4.0', '', ''],
                'lv man 6.4' => ['left_vent', '6.4', 'Male', 'The left ventricle is moderately dilated.'],
                'lv woman 6.4' => ['left_vent', '6.4', 'Female', 'The left ventricle is severely dilated.'],
                'lv man 5.4' => ['left_vent', '5.4', 'Male', 'LV dimension is normal.'],
                'lv woman 5.4' => ['left_vent', '5.4', 'Female', 'The left ventricle is mildly dilated.'],
                'lv words' => ['left_vent', 'WNL', 'Male', ''],
                'rv 3.4' => ['right_vent_dias', '3.4', '', 'RV dimension is normal.'],
                'rv 4.1 quiet' => ['right_vent_dias', '4.1', '', ''],
                'rv 4.2' => ['right_vent_dias', '4.2', '', 'RV is enlarged.'],
                'av 1.4' => ['aoritc_valve_flow', '1.4', '', 'There is no aortic stenosis.'],
                'av 2.5 quiet' => ['aoritc_valve_flow', '2.5', '', ''],
                'av 2.6' => ['aoritc_valve_flow', '2.6', '', 'There is mild aortic stenosis.'],
                'av 4' => ['aoritc_valve_flow', '4.0', '', 'There is severe aortic stenosis.'],
                'ava 0.9' => ['aoritc_valve_open', '0.9', '', 'There is severe aortic stenosis.'],
                'ava 1.2' => ['aoritc_valve_open', '1.2', '', 'There is moderate aortic stenosis.'],
                'ava 1.8' => ['aoritc_valve_open', '1.8', '', 'The aortic valve area is normal.'],
                'pht 180' => ['av_pht_ms', '180', '', 'There is severe aortic regurgitation.'],
                'pht 300' => ['av_pht_ms', '300', '', 'There is moderate aortic regurgitation.'],
                'pht 600' => ['av_pht_ms', '600', '', 'There is mild aortic regurgitation.'],
                'ivc 1.8' => ['ivc_diam_cm', '1.8', '', 'IVC is normal in size.'],
                'ivc 2.0 quiet' => ['ivc_diam_cm', '2.0', '', ''],
                'ivc 2.4' => ['ivc_diam_cm', '2.4', '', 'IVC is dilated.'],
                'ra 44' => ['ra_measure_mm', '44', '', 'The right atrium is normal in size.'],
                'ra 45 quiet' => ['ra_measure_mm', '45', '', ''],
                'ra 48' => ['ra_measure_mm', '48', '', 'The right atrium is dilated.'],
                'mva 0.8' => ['mv_area_cm2', '0.8', '', 'There is severe mitral stenosis.'],
                'mva 1.2' => ['mv_area_cm2', '1.2', '', 'There is moderate mitral stenosis.'],
                'mva 1.6' => ['mv_area_cm2', '1.6', '', 'There is minimal mitral stenosis.'],
                'mvg 0.4' => ['mv_grad_mmhg', '0.4', '', 'There is minimal mitral valve stenosis.'],
                'mvg 2 quiet' => ['mv_grad_mmhg', '2', '', ''],
                'mvg 5' => ['mv_grad_mmhg', '5', '', 'There is moderate mitral valve stenosis.'],
                'mvg 12' => ['mv_grad_mmhg', '12', '', 'There is severe mitral valve stenosis.'],
                'pap 19' => ['pa_sys_pres_dat', '19', '', 'The pulmonary artery pressure is normal.'],
                'pap 20 quiet' => ['pa_sys_pres_dat', '20', '', ''],
                'pap 35 mmHg' => ['pa_sys_pres_dat', '35 mmHg', '', 'There is mild pulmonary hypertension.'],
                'pap 51 quiet' => ['pa_sys_pres_dat', '51', '', ''],
                'pap 60' => ['pa_sys_pres_dat', '60', '', 'There is severe pulmonary hypertension. Cardiology evaluation is strongly recommended.'],
                'pap words' => ['pa_sys_pres_dat', 'Wnl', '', ''],
                'ea 0.8' => ['ea_ratio', '0.8', '', 'There is impaired relaxation of LV.'],
                'ea 1.2' => ['ea_ratio', '1.2', '', 'E/A ratio is normal.'],
                'ee 14 quiet' => ['ee_avg', '14', '', ''],
                'ee 15' => ['ee_avg', '15', '', "E/e' ratio of greater than 14 is consistent with diastolic dysfunction of LV."],
                'septal 6' => ['e_septal', '6', '', "Septal e' velocity is normal."],
                'lateral 9' => ['e_lateral', '9', '', "Lateral e' velocity is normal."],
            ];
        }
    }
}
