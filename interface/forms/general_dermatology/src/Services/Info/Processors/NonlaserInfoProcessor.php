<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class NonlaserInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $anestheticOptions = [
            'nonlaser-none' => 'None',
            'nonlaser-topical-5p-lidocaine' => 'Topical 5% Lidocaine',
            'nonlaser-topical-23p-lidocaine-7p-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'nonlaser-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'nonlaser-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'nonlaser-topical-30p-lidocaine' => 'Topical 30% Lidocaine',
            'nonlaser-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'nonlaser-ice' => 'Ice',
            'nonlaser-fan' => 'Fan',
            'nonlaser-vibration' => 'Vibration',
            'nonlaser-1p-lidocaine' => '1% Lidocaine',
            'nonlaser-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'nonlaser-2p-lidocaine' => '2% Lidocaine',
            'nonlaser-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'nonlaser-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'nonlaser-05p-bupivicaine' => '0.5% Bupivacaine',
            'nonlaser-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivacaine with Epinephrine 1:200,000',
            'nonlaser-025p-bupivicaine' => '0.25% Bupivacaine',
            'nonlaser-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivacaine with Epinephrine 1:400,000',
            'nonlaser-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'nonlaser-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'nonlaser-sterile-water' => 'Sterile Water',
            'nonlaser-1p-ropivacaine' => 'Ropivacaine 1%',
            'nonlaser-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'nonlaser-84p-sodium-bicarbonate' => 'WITH 8.4% Sodium Bicarbonate',
        ];
        
        $volumeOptions = [
            'nonlaser-anesthetic-volume-1cc' => '<1cc',
            'nonlaser-anesthetic-volume-1-2cc' => '1-2cc',
            'nonlaser-anesthetic-volume-2-3cc' => '2-3cc',
            'nonlaser-anesthetic-volume-3-4cc' => '3-4cc',
            'nonlaser-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        
        $preOpMedsOptions = ['nonlaser-none-meds' => 'None'];
        
        $eyewearOptions = [
            'nonlaser-none-needed' => 'None Needed',
            'nonlaser-metal-shields' => 'Metal Shields',
            'nonlaser-disposable-stickers' => 'Disposable Laser Stickers',
            'nonlaser-gauze' => 'Gauze',
        ];
        
        $coolingOptions = [
            'nonlaser-none-cool' => 'None',
            'nonlaser-cryogen-spray' => 'Cryogen Spray',
            'nonlaser-dcd' => 'DCD',
            'nonlaser-contact' => 'Contact',
            'nonlaser-chilled-air' => 'Chilled Air',
            'nonlaser-fan-cool' => 'Fan',
            'nonlaser-gel-based' => 'Gel-Based',
            'nonlaser-ice-cool' => 'Ice',
        ];
        
        $endpointOptions = [
            'nonlaser-purpura' => 'Purpura',
            'nonlaser-whitening' => 'Whitening',
            'nonlaser-erythema' => 'Erythema',
        ];
        
        $postOpMedsOptions = ['nonlaser-none-meds-post' => 'None'];
        
        $aftercareOptions = [
            'nonlaser-spf' => 'SPF',
            'nonlaser-petrolatum' => 'Petrolatum',
            'nonlaser-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'nonlaser-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        
        return [
            'device' => $this->getField($treatment, 'nonlaser-Device-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_nonlaser'),
            'treatment_number' => $this->getField($treatment, 'nonlaser-Treatment-free-text'),
            'anesthetic' => $this->extractCheckboxGroup($treatment, 'nonlaser', $anestheticOptions),
            'anesthetic_free_text' => $this->getField($treatment, 'nonlaser-anesthetic-free-text'),
            'anesthetic_volume' => $this->extractCheckboxGroup($treatment, 'nonlaser', $volumeOptions),
            'anesthetic_volume_free_text' => $this->getField($treatment, 'nonlaser-anesthetic-volume-free-text'),
            'pre_op_meds' => $this->extractCheckboxGroup($treatment, 'nonlaser', $preOpMedsOptions),
            'pre_op_meds_free_text' => $this->getField($treatment, 'nonlaser-pre-op-meds-free-text'),
            'eyewear' => $this->extractCheckboxGroup($treatment, 'nonlaser', $eyewearOptions),
            'eyewear_free_text' => $this->getField($treatment, 'nonlaser-patient-protective-eyewear-free-text'),
            'depth' => $this->getField($treatment, 'nonlaser-Depth-free-text'),
            'spot_size' => $this->getField($treatment, 'nonlaser-Spot-Size-free-text'),
            'pulse_duration' => $this->getField($treatment, 'nonlaser-Pulse-Duration-free-text'),
            'fluence' => $this->getField($treatment, 'nonlaser-Fluence-free-text'),
            'pulses' => $this->getField($treatment, 'nonlaser-Pulses-free-text'),
            'passes' => $this->getField($treatment, 'nonlaser-Passes-free-text'),
            'cooling' => $this->extractCheckboxGroup($treatment, 'nonlaser', $coolingOptions),
            'cooling_free_text' => $this->getField($treatment, 'nonlaser-cooling-free-text'),
            'endpoint' => $this->extractCheckboxGroup($treatment, 'nonlaser', $endpointOptions),
            'endpoint_free_text' => $this->getField($treatment, 'nonlaser-end-point-free-text'),
            'post_op_meds' => $this->extractCheckboxGroup($treatment, 'nonlaser', $postOpMedsOptions),
            'post_op_meds_free_text' => $this->getField($treatment, 'nonlaser-post-op-meds-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'nonlaser', $aftercareOptions),
            'aftercare_free_text' => $this->getField($treatment, 'nonlaser-aftercare_free_text'),
            'notes' => $this->getField($treatment, 'nonlaser-Notes-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];
        
        // Device Information Section
        $deviceHtml = '';
        if (!$this->isFieldEmpty($fields['device'] ?? null)) {
            $deviceHtml .= $this->formatField('Device', $fields['device']);
        }
        if (!$this->isFieldEmpty($fields['treatment_number'] ?? null)) {
            $deviceHtml .= $this->formatField('Treatment Number', $fields['treatment_number']);
        }
        if ($deviceHtml) {
            $sections['Device Information'] = $deviceHtml;
        }
        
        // Treatment Areas Section
        $areasHtml = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areasHtml .= $this->formatField('Areas Treated', $fields['areas_treated']);
        }
        if ($areasHtml) {
            $sections['Treatment Areas'] = $areasHtml;
        }
        
        // Anesthetic Section
        $anestheticHtml = '';
        if (!empty($fields['anesthetic'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Anesthetic', $fields['anesthetic']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Notes', $fields['anesthetic_free_text']);
        }
        if (!empty($fields['anesthetic_volume'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Volume', $fields['anesthetic_volume']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_volume_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Volume Notes', $fields['anesthetic_volume_free_text']);
        }
        if ($anestheticHtml) {
            $sections['Anesthetic'] = $anestheticHtml;
        }
        
        // Pre-operative Section
        $preOpHtml = '';
        if (!empty($fields['pre_op_meds'] ?? [])) {
            $preOpHtml .= $this->formatFieldList('Pre-op Medications', $fields['pre_op_meds']);
        }
        if (!$this->isFieldEmpty($fields['pre_op_meds_free_text'] ?? null)) {
            $preOpHtml .= $this->formatField('Pre-op Notes', $fields['pre_op_meds_free_text']);
        }
        if (!empty($fields['eyewear'] ?? [])) {
            $preOpHtml .= $this->formatFieldList('Protective Eyewear', $fields['eyewear']);
        }
        if (!$this->isFieldEmpty($fields['eyewear_free_text'] ?? null)) {
            $preOpHtml .= $this->formatField('Eyewear Notes', $fields['eyewear_free_text']);
        }
        if ($preOpHtml) {
            $sections['Pre-operative'] = $preOpHtml;
        }
        
        // Device Parameters Section
        $parametersHtml = '';
        if (!$this->isFieldEmpty($fields['depth'] ?? null)) {
            $parametersHtml .= $this->formatField('Depth', $fields['depth']);
        }
        if (!$this->isFieldEmpty($fields['spot_size'] ?? null)) {
            $parametersHtml .= $this->formatField('Spot Size', $fields['spot_size']);
        }
        if (!$this->isFieldEmpty($fields['pulse_duration'] ?? null)) {
            $parametersHtml .= $this->formatField('Pulse Duration', $fields['pulse_duration']);
        }
        if (!$this->isFieldEmpty($fields['fluence'] ?? null)) {
            $parametersHtml .= $this->formatField('Fluence', $fields['fluence']);
        }
        if (!$this->isFieldEmpty($fields['pulses'] ?? null)) {
            $parametersHtml .= $this->formatField('Number of Pulses', $fields['pulses']);
        }
        if (!$this->isFieldEmpty($fields['passes'] ?? null)) {
            $parametersHtml .= $this->formatField('Number of Passes', $fields['passes']);
        }
        if ($parametersHtml) {
            $sections['Device Parameters'] = $parametersHtml;
        }
        
        // Cooling Section
        $coolingHtml = '';
        if (!empty($fields['cooling'] ?? [])) {
            $coolingHtml .= $this->formatFieldList('Cooling Method', $fields['cooling']);
        }
        if (!$this->isFieldEmpty($fields['cooling_free_text'] ?? null)) {
            $coolingHtml .= $this->formatField('Cooling Notes', $fields['cooling_free_text']);
        }
        if ($coolingHtml) {
            $sections['Cooling'] = $coolingHtml;
        }
        
        // Endpoint Section
        $endpointHtml = '';
        if (!empty($fields['endpoint'] ?? [])) {
            $endpointHtml .= $this->formatFieldList('Endpoint', $fields['endpoint']);
        }
        if (!$this->isFieldEmpty($fields['endpoint_free_text'] ?? null)) {
            $endpointHtml .= $this->formatField('Endpoint Notes', $fields['endpoint_free_text']);
        }
        if ($endpointHtml) {
            $sections['Endpoint'] = $endpointHtml;
        }
        
        // Post-operative Section
        $postOpHtml = '';
        if (!empty($fields['post_op_meds'] ?? [])) {
            $postOpHtml .= $this->formatFieldList('Post-op Medications', $fields['post_op_meds']);
        }
        if (!$this->isFieldEmpty($fields['post_op_meds_free_text'] ?? null)) {
            $postOpHtml .= $this->formatField('Post-op Notes', $fields['post_op_meds_free_text']);
        }
        if ($postOpHtml) {
            $sections['Post-operative'] = $postOpHtml;
        }
        
        // Aftercare Section
        $aftercareHtml = '';
        if (!empty($fields['aftercare'] ?? [])) {
            $aftercareHtml .= $this->formatFieldList('Aftercare (Applied In-Office)', $fields['aftercare']);
        }
        if (!$this->isFieldEmpty($fields['aftercare_free_text'] ?? null)) {
            $aftercareHtml .= $this->formatField('Aftercare Notes', $fields['aftercare_free_text']);
        }
        if ($aftercareHtml) {
            $sections['Aftercare'] = $aftercareHtml;
        }
        
        // Notes Section
        $notesHtml = '';
        if (!$this->isFieldEmpty($fields['notes'] ?? null)) {
            $notesHtml .= $this->formatField('Notes', $fields['notes']);
        }
        if ($notesHtml) {
            $sections['Notes'] = $notesHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
