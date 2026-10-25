<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class IntralesionalInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $injectableFields = [
            'intralesional-kenalog' => 'Kenalog',
            'intralesional-candida-albicans' => 'Candida Albicans',
            'intralesional-bleomycin' => 'Bleomycin',
            'intralesional-5-fluorouracil' => '5-Fluorouracil',
            'intralesional-kenalog-fluorouracil' => 'Kenalog + 5-Fluorouracil',
        ];

        $anestheticFields = [
            'intralesional-none' => 'None',
            'intralesional-topical-5-lidocaine' => 'Topical 5% Lidocaine',
            'intralesional-topical-23-lidocaine-7-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'intralesional-topical-23-lidocaine-7-tetracaine-1-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'intralesional-topical-20-benzocaine-10-lidocaine-4-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'intralesional-topical-30-lidocaine' => 'Topical 30% Lidocaine',
            'intralesional-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'intralesional-ice' => 'Ice',
            'intralesional-fan' => 'Fan',
            'intralesional-vibration' => 'Vibration',
        ];

        $concentrationFields = [
            'intralesional-2mg' => '2.5 mg/mL',
            'intralesional-3mg' => '3.3 mg/mL',
            'intralesional-5mg' => '5 mg/mL',
            'intralesional-10mg' => '10 mg/mL',
            'intralesional-20mg' => '20 mg/mL',
            'intralesional-40mg' => '40 mg/mL',
        ];

        return [
            'injectables' => $this->extractCheckboxGroup($treatment, 'intralesional', $injectableFields),
            'injectable_other' => $this->getField($treatment, 'intralesional-Injectable-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_intralesional'),
            'lesion_count' => $this->getField($treatment, 'areas-count-intralesional'),
            'anesthetics' => $this->extractCheckboxGroup($treatment, 'intralesional', $anestheticFields),
            'anesthetic_other' => $this->getField($treatment, 'intralesional-anesthetic-free-text'),
            'concentration' => $this->extractCheckboxGroup($treatment, 'intralesional', $concentrationFields),
            'concentration_other' => $this->getField($treatment, 'intralesional-concentration-free-text'),
            'total_volume' => $this->getField($treatment, 'intralesional-injection-total-volume-free-text'),
            'lot_number' => $this->getField($treatment, 'intralesional-lot-free-text'),
            'expiration' => $this->getField($treatment, 'intralesional-expiration-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        if (empty($fields)) {
            return '';
        }

        $sections = [];

        // Injectable section
        if (!empty($fields['injectables'] ?? [])) {
            $injectableHtml = $this->formatFieldList('Injectable', $fields['injectables']);
            if (!$this->isFieldEmpty($fields['injectable_other'] ?? null)) {
                $injectableHtml .= $this->formatField('Other Injectable', $fields['injectable_other']);
            }
            $sections['Injectable'] = $injectableHtml;
        }

        // Treatment areas section
        $areasHtml = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areasHtml .= $this->formatField('Area(s) Treated', $fields['areas_treated']);
        }
        if (!$this->isFieldEmpty($fields['lesion_count'] ?? null)) {
            $areasHtml .= $this->formatField('Number of Lesions Treated', $fields['lesion_count']);
        }
        if ($areasHtml) {
            $sections['Treatment Areas'] = $areasHtml;
        }

        // Anesthetic section
        if (!empty($fields['anesthetics'] ?? [])) {
            $anestheticHtml = $this->formatFieldList('Anesthetic', $fields['anesthetics']);
            if (!$this->isFieldEmpty($fields['anesthetic_other'] ?? null)) {
                $anestheticHtml .= $this->formatField('Other Anesthetic', $fields['anesthetic_other']);
            }
            $sections['Anesthetic'] = $anestheticHtml;
        }

        // Concentration section
        $concentrationHtml = '';
        if (!empty($fields['concentration'] ?? [])) {
            $concentrationHtml .= $this->formatFieldList('Concentration', $fields['concentration']);
        }
        if (!$this->isFieldEmpty($fields['concentration_other'] ?? null)) {
            $concentrationHtml .= $this->formatField('Concentration', $fields['concentration_other']);
        }
        if ($concentrationHtml) {
            $sections['Concentration'] = $concentrationHtml;
        }

        // Volume, Lot, Expiration section
        $detailsHtml = '';
        if (!$this->isFieldEmpty($fields['total_volume'] ?? null)) {
            $detailsHtml .= $this->formatField('Injection Total Volume', $fields['total_volume']);
        }
        if (!$this->isFieldEmpty($fields['lot_number'] ?? null)) {
            $detailsHtml .= $this->formatField('Lot #', $fields['lot_number']);
        }
        if (!$this->isFieldEmpty($fields['expiration'] ?? null)) {
            $detailsHtml .= $this->formatField('Expiration', $fields['expiration']);
        }
        if ($detailsHtml) {
            $sections['Product Details'] = $detailsHtml;
        }

        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
