<?php

namespace GeneralDermatology\Services\Info;

class ProcessDebridementInfo {
    /**
     * Process debridement information from plan data
     *
     * @param array $treatment The treatment data
     * @param int $planItemHeight Reference to the plan item height to update
     * @param int $fontHeight Font height for calculations
     * @param int $textMaxWidth Max width for text wrapping
     * @return string HTML for debridement information
     */
    public function processDebridementInfo(array $treatment, int &$planItemHeight, int $fontHeight, int $textMaxWidth): string
    {
        $debridementHtml = "";

        // Process skin prep options
        $skinPrep = [];
        if (isset($treatment['debridement-alcohol']) && $treatment['debridement-alcohol'] == 1) {
            $skinPrep[] = "Alcohol";
        }
        if (isset($treatment['debridement-hibiclens']) && $treatment['debridement-hibiclens'] == 1) {
            $skinPrep[] = "Hibiclens";
        }
        if (isset($treatment['debridement-iodine']) && $treatment['debridement-iodine'] == 1) {
            $skinPrep[] = "Iodine";
        }
        if (isset($treatment['debridement-lasercyn']) && $treatment['debridement-lasercyn'] == 1) {
            $skinPrep[] = "Lasercyn";
        }

        // Add free text skin prep if available
        $skinPrepFreeText = '';
        if (isset($treatment['debridement-skin-prep-free-text']) && !empty($treatment['debridement-skin-prep-free-text'])) {
            $skinPrepFreeText = text($treatment['debridement-skin-prep-free-text']);
        }

        if (isset($treatment['debridement-Skin-prep-title'])) {
            $skinPrepOutput = [];
            if (!empty($skinPrep)) {
                $skinPrepOutput[] = implode(", ", $skinPrep);
            }
            if (!empty($skinPrepFreeText)) {
                $skinPrepOutput[] = $skinPrepFreeText;
            }

            if (!empty($skinPrepOutput)) {
                $debridementHtml .= "<b>" . trim($treatment['debridement-Skin-prep-title']) . ":</b> " . implode(", ", $skinPrepOutput) . "<br>";
                $planItemHeight += $fontHeight;
            }
        }

        // Process comprehensive anesthetic options
        $anesthetic = [];
        if (isset($treatment['debridement-1p-lidocaine']) && $treatment['debridement-1p-lidocaine'] == 1) {
            $anesthetic[] = "1% lidocaine";
        }
        if (isset($treatment['debridement-1p-lidocaine-with-epinephrine']) && $treatment['debridement-1p-lidocaine-with-epinephrine'] == 1) {
            $anesthetic[] = "1% lidocaine with epinephrine 1:100,000";
        }
        if (isset($treatment['debridement-2p-lidocaine']) && $treatment['debridement-2p-lidocaine'] == 1) {
            $anesthetic[] = "2% lidocaine";
        }
        if (isset($treatment['debridement-2p-lidocaine-wtih-epinephrine']) && $treatment['debridement-2p-lidocaine-wtih-epinephrine'] == 1) {
            $anesthetic[] = "2% lidocaine with epinephrine 1:100,000";
        }
        if (isset($treatment['debridement-05p-lidocaine-with-epinephrine']) && $treatment['debridement-05p-lidocaine-with-epinephrine'] == 1) {
            $anesthetic[] = "0.5% lidocaine with epinephrine 1:200,000";
        }
        if (isset($treatment['debridement-05p-bupivicaine']) && $treatment['debridement-05p-bupivicaine'] == 1) {
            $anesthetic[] = "0.5% bupivicaine";
        }
        if (isset($treatment['debridement-05p-bupivicaine-wtih-epinephrine']) && $treatment['debridement-05p-bupivicaine-wtih-epinephrine'] == 1) {
            $anesthetic[] = "0.5% bupivicaine with epinephrine 1:200,000";
        }
        if (isset($treatment['debridement-025p-bupivicaine']) && $treatment['debridement-025p-bupivicaine'] == 1) {
            $anesthetic[] = "0.25% bupivicaine";
        }
        if (isset($treatment['debridement-025p-bupivicaine-wtih-epinephrine']) && $treatment['debridement-025p-bupivicaine-wtih-epinephrine'] == 1) {
            $anesthetic[] = "0.25% bupivicaine with epinephrine 1:400,000";
        }
        if (isset($treatment['debridement-025p-marcaine-wtih-epinephrine']) && $treatment['debridement-025p-marcaine-wtih-epinephrine'] == 1) {
            $anesthetic[] = "0.25% marcaine with 1:200,000 epinephrine";
        }
        if (isset($treatment['debridement-09p-bacteriostatic-sodium-chloride']) && $treatment['debridement-09p-bacteriostatic-sodium-chloride'] == 1) {
            $anesthetic[] = "bacteriostatic 0.9% sodium chloride";
        }
        if (isset($treatment['debridement-sterile-water']) && $treatment['debridement-sterile-water'] == 1) {
            $anesthetic[] = "Sterile water";
        }
        if (isset($treatment['debridement-1p-ropivacaine']) && $treatment['debridement-1p-ropivacaine'] == 1) {
            $anesthetic[] = "Ropivacaine 1%";
        }
        if (isset($treatment['debridement-05p-ropivacaine']) && $treatment['debridement-05p-ropivacaine'] == 1) {
            $anesthetic[] = "Ropivacaine 0.5%";
        }
        if (isset($treatment['debridement-84p-sodium-bicarbonate']) && $treatment['debridement-84p-sodium-bicarbonate'] == 1) {
            $anesthetic[] = "WITH 8.4% sodium bicarbonate in 1:10 ratio (sod bicarbonate: anesthetic)";
        }

        // Add free text anesthetic if available
        $anestheticFreeText = '';
        if (isset($treatment['debridement-anesthetic-free-text']) && !empty($treatment['debridement-anesthetic-free-text'])) {
            $anestheticFreeText = text($treatment['debridement-anesthetic-free-text']);
        }

        if (isset($treatment['debridement-Anesthetic-title'])) {
            $anestheticOutput = [];
            if (!empty($anesthetic)) {
                $anestheticOutput[] = implode(", ", $anesthetic);
            }
            if (!empty($anestheticFreeText)) {
                $anestheticOutput[] = $anestheticFreeText;
            }

            if (!empty($anestheticOutput)) {
                $debridementHtml .= "<b>" . trim($treatment['debridement-Anesthetic-title']) . ":</b> " . implode(", ", $anestheticOutput) . "<br>";
                $planItemHeight += $fontHeight;
            }
        }

        // Process anesthetic volume options (corrected field names)
        $anestheticVolume = [];
        if (isset($treatment['debridement-anesthetic-volume-1cc']) && $treatment['debridement-anesthetic-volume-1cc'] == 1) {
            $anestheticVolume[] = "<1cc";
        }
        if (isset($treatment['debridement-anesthetic-volume-1-2cc']) && $treatment['debridement-anesthetic-volume-1-2cc'] == 1) {
            $anestheticVolume[] = "1-2cc";
        }
        if (isset($treatment['debridement-anesthetic-volume-2-3cc']) && $treatment['debridement-anesthetic-volume-2-3cc'] == 1) {
            $anestheticVolume[] = "2-3cc";
        }
        if (isset($treatment['debridement-anesthetic-volume-3-4cc']) && $treatment['debridement-anesthetic-volume-3-4cc'] == 1) {
            $anestheticVolume[] = "3-4cc";
        }
        if (isset($treatment['debridement-anesthetic-volume-4-5cc']) && $treatment['debridement-anesthetic-volume-4-5cc'] == 1) {
            $anestheticVolume[] = "4-5cc";
        }

        // Add free text anesthetic volume if available
        $anestheticVolumeFreeText = '';
        if (isset($treatment['debridement-anesthetic-volume-free-text']) && !empty($treatment['debridement-anesthetic-volume-free-text'])) {
            $anestheticVolumeFreeText = text($treatment['debridement-anesthetic-volume-free-text']);
        }

        if (isset($treatment['debridement-Anesthetic-Volume-title'])) {
            $volumeOutput = [];
            if (!empty($anestheticVolume)) {
                $volumeOutput[] = implode(", ", $anestheticVolume);
            }
            if (!empty($anestheticVolumeFreeText)) {
                $volumeOutput[] = $anestheticVolumeFreeText;
            }

            if (!empty($volumeOutput)) {
                $debridementHtml .= "<b>" . trim($treatment['debridement-Anesthetic-Volume-title']) . ":</b> " . implode(", ", $volumeOutput) . "<br>";
                $planItemHeight += $fontHeight;
            }
        }

        // Add area(s) treated if available
        if (isset($treatment['areas_debridement']) && !empty($treatment['areas_debridement'])) {
            $debridementHtml .= "<b>Area(s) Treated:</b> " . text($treatment['areas_debridement']) . "<br>";
            $planItemHeight += $this->calculateTextHeight($treatment['areas_debridement'], $fontHeight, $textMaxWidth);
        }

        // Process method options (corrected field names)
        $method = [];
        if (isset($treatment['debridement-mechanical']) && $treatment['debridement-mechanical'] == 1) {
            $method[] = "mechanical";
        }
        if (isset($treatment['debridement-surgical']) && $treatment['debridement-surgical'] == 1) {
            $method[] = "surgical";
        }
        if (isset($treatment['debridement-autolytic']) && $treatment['debridement-autolytic'] == 1) {
            $method[] = "autolytic";
        }
        if (isset($treatment['debridement-enzymatic']) && $treatment['debridement-enzymatic'] == 1) {
            $method[] = "enzymatic";
        }

        // Add free text method if available
        $methodFreeText = '';
        if (isset($treatment['debridement-method-free-text']) && !empty($treatment['debridement-method-free-text'])) {
            $methodFreeText = text($treatment['debridement-method-free-text']);
        }

        if (isset($treatment['debridement-Method-title'])) {
            $methodOutput = [];
            if (!empty($method)) {
                $methodOutput[] = implode(", ", $method);
            }
            if (!empty($methodFreeText)) {
                $methodOutput[] = $methodFreeText;
            }

            if (!empty($methodOutput)) {
                $debridementHtml .= "<b>" . trim($treatment['debridement-Method-title']) . ":</b> " . implode(", ", $methodOutput) . "<br>";
                $planItemHeight += $fontHeight;
            }
        }

        // Process aftercare options (NEW SECTION)
        $aftercare = [];
        if (isset($treatment['debridement-mupirocin']) && $treatment['debridement-mupirocin'] == 1) {
            $aftercare[] = "mupirocin";
        }
        if (isset($treatment['debridement-polysporin']) && $treatment['debridement-polysporin'] == 1) {
            $aftercare[] = "polysporin";
        }
        if (isset($treatment['debridement-bacitracin']) && $treatment['debridement-bacitracin'] == 1) {
            $aftercare[] = "bacitracin";
        }
        if (isset($treatment['debridement-petrolatum']) && $treatment['debridement-petrolatum'] == 1) {
            $aftercare[] = "petrolatum";
        }

        // Add free text aftercare if available
        $aftercareFreeText = '';
        if (isset($treatment['debridement-aftercare_free_text']) && !empty($treatment['debridement-aftercare_free_text'])) {
            $aftercareFreeText = text($treatment['debridement-aftercare_free_text']);
        }

        if (isset($treatment['debridement-Aftercare-title'])) {
            $aftercareOutput = [];
            if (!empty($aftercare)) {
                $aftercareOutput[] = implode(", ", $aftercare);
            }
            if (!empty($aftercareFreeText)) {
                $aftercareOutput[] = $aftercareFreeText;
            }

            if (!empty($aftercareOutput)) {
                $debridementHtml .= "<b>" . trim($treatment['debridement-Aftercare-title']) . ":</b> " . implode(", ", $aftercareOutput) . "<br>";
                $planItemHeight += $fontHeight;
            }
        }

        // Process bandage options (corrected field names)
        $bandage = [];
        if (isset($treatment['debridement-pressure-dressing-with-telfa-gauze-and-hypafix']) && $treatment['debridement-pressure-dressing-with-telfa-gauze-and-hypafix'] == 1) {
            $bandage[] = "pressure dressing with telfa, gauze and hypafix";
        }
        if (isset($treatment['debridement-pressure-dressing-with-telfa-gauze-and-papertape']) && $treatment['debridement-pressure-dressing-with-telfa-gauze-and-papertape'] == 1) {
            $bandage[] = "pressure dressing with telfa, gauze and paper tape";
        }
        if (isset($treatment['debridement-regular-bandage']) && $treatment['debridement-regular-bandage'] == 1) {
            $bandage[] = "regular bandage";
        }
        if (isset($treatment['debridement-telfa']) && $treatment['debridement-telfa'] == 1) {
            $bandage[] = "telfa";
        }
        if (isset($treatment['debridement-paper-tape']) && $treatment['debridement-paper-tape'] == 1) {
            $bandage[] = "paper tape";
        }
        if (isset($treatment['debridement-hypafix']) && $treatment['debridement-hypafix'] == 1) {
            $bandage[] = "hypafix";
        }
        if (isset($treatment['debridement-hydrocolloid']) && $treatment['debridement-hydrocolloid'] == 1) {
            $bandage[] = "hydrocolloid";
        }

        // Add free text bandage if available
        $bandageFreeText = '';
        if (isset($treatment['debridement-bandage-free-text']) && !empty($treatment['debridement-bandage-free-text'])) {
            $bandageFreeText = text($treatment['debridement-bandage-free-text']);
        }

        if (isset($treatment['debridement-Bandage-title'])) {
            $bandageOutput = [];
            if (!empty($bandage)) {
                $bandageOutput[] = implode(", ", $bandage);
            }
            if (!empty($bandageFreeText)) {
                $bandageOutput[] = $bandageFreeText;
            }

            if (!empty($bandageOutput)) {
                $debridementHtml .= "<b>" . trim($treatment['debridement-Bandage-title']) . ":</b> " . implode(", ", $bandageOutput) . "<br>";
                $planItemHeight += $fontHeight;
            }
        }

        return $debridementHtml;
    }
    }
