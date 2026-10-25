<?php

namespace GeneralDermatology\App;

class SeverityList
{
    public function severityArray(): array
    {
        return array(
            '' => 'Unknown',
			'self-limited' => 'Self-limited problem',
			'undiagnosed' => 'Undiagnosed new problem with uncertain prognosis',
			'chronic' => 'Stable chronic Illness.',
            'acute' => 'Acute uncomplicated injury.',
			'chronic-injury' => 'Chronic Illness with exacerbation or side effects of treatment',
			'acute-complicated-injury' => 'Acute complicated injury +/- systemic symptoms',
			'chronic-illness' => 'Chronic illnesses with severe	exacerbation, progression, or side effects of treatment'
        );
    }

    public function endNotesArray(): array
    {
        return array(
            'review_external_notes' => 'Reviewed external notes',
            'review_test_results' => 'Reviewed results of tests',
            'order_tests' => 'Ordering tests',
            'obtain_hx' => 'Obtaining history from independent historian(s)',
            'independent_test_another_hcp' => 'Independent interpretation of a test performed by another healthcare provider',
            'discuss_test_outside_hcp' => 'Discussion of management or test interpretation with outside healthcare provider',
            'document_time_hcp' => 'Documentation time by this Healthcare Provider',
            'consult_family' => 'Counseling patient or family member',
            'perform_diag_procedure' => 'Performing diagnostic procedure',
        );
    }
}
