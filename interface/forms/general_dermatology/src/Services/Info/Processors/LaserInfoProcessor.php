<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

/**
 * LaserInfoProcessor
 *
 * Extracts and formats laser procedure-specific information.
 * Handles fields like laser type, spot size, pulse duration, fluence, cooling method, endpoint, etc.
 *
 * This processor demonstrates polymorphic behavior:
 * - Extends AbstractProcedureInfoProcessor (inheritance)
 * - Implements the ProcedureInfoProcessor interface (polymorphism)
 * - Uses inherited utility methods (code reuse)
 * - Custom extraction logic for laser-specific fields
 */
class LaserInfoProcessor extends AbstractProcedureInfoProcessor
{
    /**
     * Extract laser-specific fields from treatment data
     *
     * @param array $treatment The treatment data
     * @return array Associative array of extracted laser fields
     */
    public function extractFields(array $treatment): array
    {
        $fields = [];

        // Basic laser info
        $fields['laser_type'] = $this->getField($treatment, 'laser-Laser-free-text');
        $fields['treatment_number'] = $this->getField($treatment, 'laser-Treatment-free-text');

        // Laser parameters
        $fields['spot_size'] = $this->getField($treatment, 'laser-Spot-Size-free-text');
        $fields['pulse_duration'] = $this->getField($treatment, 'laser-Pulse-Duration-free-text');
        $fields['fluence'] = $this->getField($treatment, 'laser-Fluence-free-text');
        $fields['number_of_pulses'] = $this->getField($treatment, 'laser-Pulses-free-text');
        $fields['number_of_passes'] = $this->getField($treatment, 'laser-Passes-free-text');

        // Anesthetic options
        $anesthetics = [
            'laser-none' => 'None',
            'laser-topical-5p-lidocaine' => 'Topical 5% Lidocaine',
            'laser-topical-23p-lidocaine-7p-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'laser-1p-lidocaine' => '1% Lidocaine',
            'laser-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'laser-2p-lidocaine' => '2% Lidocaine',
            'laser-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'laser-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'laser-05p-bupivicaine' => '0.5% Bupivicaine',
            'laser-ice' => 'Ice',
            'laser-fan' => 'Fan',
            'laser-vibration' => 'Vibration',
        ];
        $fields['anesthetic'] = $this->extractCheckboxGroup($treatment, 'laser', $anesthetics);
        $fields['anesthetic_free_text'] = $this->getField($treatment, 'laser-anesthetic-free-text');

        // Anesthetic volume
        $volumeOptions = [
            'laser-anesthetic-volume-1cc' => '<1cc',
            'laser-anesthetic-volume-1-2cc' => '1-2cc',
            'laser-anesthetic-volume-2-3cc' => '2-3cc',
            'laser-anesthetic-volume-3-4cc' => '3-4cc',
            'laser-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        $fields['anesthetic_volume'] = $this->extractCheckboxGroup($treatment, 'laser', $volumeOptions);
        $fields['anesthetic_volume_free_text'] = $this->getField($treatment, 'laser-anesthetic-volume-free-text');

        // Pre-op medications
        $fields['pre_op_meds_free_text'] = $this->getField($treatment, 'laser-pre-op-meds-free-text');

        // Eye protection
        $eyeProtection = [
            'laser-metal-shields' => 'Metal Shields',
            'laser-disposable-stickers' => 'Disposable Laser Stickers',
            'laser-gauze' => 'Gauze',
        ];
        $fields['eye_protection'] = $this->extractCheckboxGroup($treatment, 'laser', $eyeProtection);
        $fields['eye_protection_free_text'] = $this->getField($treatment, 'laser-patient-protective-eyewear-free-text');

        // Cooling method
        $coolingOptions = [
            'laser-none-cool' => 'None',
            'laser-cryogen-spray' => 'Cryogen Spray',
            'laser-dcd' => 'DCD',
            'laser-contact' => 'Contact',
            'laser-chilled-air' => 'Chilled Air',
            'laser-fan-cool' => 'Fan',
            'laser-gel-based' => 'Gel-Based',
            'laser-ice-cool' => 'Ice',
        ];
        $fields['cooling_method'] = $this->extractCheckboxGroup($treatment, 'laser', $coolingOptions);
        $fields['cooling_free_text'] = $this->getField($treatment, 'laser-cooling-free-text');

        // Endpoint
        $endpointOptions = [
            'laser-purpura' => 'Purpura',
            'laser-whitening' => 'Whitening',
            'laser-erythema' => 'Erythema',
        ];
        $fields['endpoint'] = $this->extractCheckboxGroup($treatment, 'laser', $endpointOptions);
        $fields['endpoint_free_text'] = $this->getField($treatment, 'laser-end-point-free-text');

        // Aftercare
        $aftercareOptions = [
            'laser-spf' => 'SPF',
            'laser-petrolatum' => 'Petrolatum',
            'laser-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'laser-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        $fields['aftercare'] = $this->extractCheckboxGroup($treatment, 'laser', $aftercareOptions);
        $fields['aftercare_free_text'] = $this->getField($treatment, 'laser-aftercare_free_text');

        // Areas treated and notes
        $fields['areas_treated'] = $this->getField($treatment, 'areas_laser');
        $fields['notes'] = $this->getField($treatment, 'laser-Notes-free-text');

        return $fields;
    }

    /**
     * Format extracted laser fields for PDF display
     *
     * @param array $fields The extracted fields from extractFields()
     * @return string HTML formatted procedure details
     */
    public function formatForDisplay(array $fields): string
    {
        $sections = [];

        // Basic info section
        $basicInfo = '';
        if (!$this->isFieldEmpty($fields['laser_type'] ?? null)) {
            $basicInfo .= $this->formatField('Laser Type', $fields['laser_type']);
        }
        if (!$this->isFieldEmpty($fields['treatment_number'] ?? null)) {
            $basicInfo .= $this->formatField('Treatment #', $fields['treatment_number']);
        }
        if ($basicInfo) {
            $sections['Basic Information'] = $basicInfo;
        }

        // Laser parameters section
        $parameters = '';
        if (!$this->isFieldEmpty($fields['spot_size'] ?? null)) {
            $parameters .= $this->formatField('Spot Size', $fields['spot_size']);
        }
        if (!$this->isFieldEmpty($fields['pulse_duration'] ?? null)) {
            $parameters .= $this->formatField('Pulse Duration', $fields['pulse_duration']);
        }
        if (!$this->isFieldEmpty($fields['fluence'] ?? null)) {
            $parameters .= $this->formatField('Fluence', $fields['fluence']);
        }
        if (!$this->isFieldEmpty($fields['number_of_pulses'] ?? null)) {
            $parameters .= $this->formatField('Number of Pulses', $fields['number_of_pulses']);
        }
        if (!$this->isFieldEmpty($fields['number_of_passes'] ?? null)) {
            $parameters .= $this->formatField('Number of Passes', $fields['number_of_passes']);
        }
        if ($parameters) {
            $sections['Laser Parameters'] = $parameters;
        }

        // Anesthetic section
        $anesthetic = '';
        if (!empty($fields['anesthetic'] ?? [])) {
            $anesthetic .= $this->formatFieldList('Anesthetic Options', $fields['anesthetic']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_free_text'] ?? null)) {
            $anesthetic .= $this->formatField('Additional Anesthetic Notes', $fields['anesthetic_free_text']);
        }
        if (!empty($fields['anesthetic_volume'] ?? [])) {
            $anesthetic .= $this->formatFieldList('Anesthetic Volume', $fields['anesthetic_volume']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_volume_free_text'] ?? null)) {
            $anesthetic .= $this->formatField('Volume Notes', $fields['anesthetic_volume_free_text']);
        }
        if ($anesthetic) {
            $sections['Anesthetic'] = $anesthetic;
        }

        // Pre-operative section
        $preOp = '';
        if (!empty($fields['eye_protection'] ?? [])) {
            $preOp .= $this->formatFieldList('Eye Protection', $fields['eye_protection']);
        }
        if (!$this->isFieldEmpty($fields['eye_protection_free_text'] ?? null)) {
            $preOp .= $this->formatField('Eye Protection Notes', $fields['eye_protection_free_text']);
        }
        if (!$this->isFieldEmpty($fields['pre_op_meds_free_text'] ?? null)) {
            $preOp .= $this->formatField('Pre-op Medications', $fields['pre_op_meds_free_text']);
        }
        if ($preOp) {
            $sections['Pre-operative'] = $preOp;
        }

        // Cooling section
        $cooling = '';
        if (!empty($fields['cooling_method'] ?? [])) {
            $cooling .= $this->formatFieldList('Cooling Method', $fields['cooling_method']);
        }
        if (!$this->isFieldEmpty($fields['cooling_free_text'] ?? null)) {
            $cooling .= $this->formatField('Cooling Notes', $fields['cooling_free_text']);
        }
        if ($cooling) {
            $sections['Cooling'] = $cooling;
        }

        // Endpoint section
        $endpoint = '';
        if (!empty($fields['endpoint'] ?? [])) {
            $endpoint .= $this->formatFieldList('Endpoint', $fields['endpoint']);
        }
        if (!$this->isFieldEmpty($fields['endpoint_free_text'] ?? null)) {
            $endpoint .= $this->formatField('Endpoint Notes', $fields['endpoint_free_text']);
        }
        if ($endpoint) {
            $sections['Endpoint'] = $endpoint;
        }

        // Treatment areas section
        $areas = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areas .= $this->formatField('Areas Treated', $fields['areas_treated']);
        }
        if ($areas) {
            $sections['Treatment Areas'] = $areas;
        }

        // Aftercare section
        $aftercare = '';
        if (!empty($fields['aftercare'] ?? [])) {
            $aftercare .= $this->formatFieldList('Aftercare (Applied In-Office)', $fields['aftercare']);
        }
        if (!$this->isFieldEmpty($fields['aftercare_free_text'] ?? null)) {
            $aftercare .= $this->formatField('Aftercare Notes', $fields['aftercare_free_text']);
        }
        if ($aftercare) {
            $sections['Aftercare'] = $aftercare;
        }

        // Notes section
        $notesHtml = '';
        if (!$this->isFieldEmpty($fields['notes'] ?? null)) {
            $notesHtml = $this->formatField('Notes', $fields['notes']);
            $sections['Notes'] = $notesHtml;
        }

        // Combine all sections
        $html = $this->combineSections($sections);

        // Wrap in procedure-details container
        return $this->wrapProcedureDetails($html);
    }
}
