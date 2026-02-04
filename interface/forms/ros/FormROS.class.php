<?php

/**
 * ROS form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * class
 *
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class FormROS extends ORDataObject
{
    /**
     *
     * @access public
     */


    /**
     *
     * static
     */

    /**
     *
     * @access private
     */

    public $id;
    public $date;
    public $date_of_onset;
    public $pid;
    public $weight_change = "N/A";
    public $weakness = "N/A";
    public $fatigue = "N/A";
    public $anorexia = "N/A";
    public $fever = "N/A";
    public $chills = "N/A";
    public $night_sweats = "N/A";
    public $insomnia = "N/A";
    public $irritability = "N/A";
    public $heat_or_cold = "N/A";
    public $intolerance = "N/A";
    public $change_in_vision = "N/A";
    public $glaucoma_history = "N/A";
    public $eye_pain = "N/A";
    public $irritation = "N/A";
    public $redness = "N/A";
    public $excessive_tearing = "N/A";
    public $double_vision = "N/A";
    public $blind_spots = "N/A";
    public $photophobia = "N/A";
    public $hearing_loss = "N/A";
    public $discharge = "N/A";
    public $pain = "N/A";
    public $vertigo = "N/A";
    public $tinnitus = "N/A";
    public $frequent_colds = "N/A";
    public $sore_throat = "N/A";
    public $sinus_problems = "N/A";
    public $post_nasal_drip = "N/A";
    public $nosebleed = "N/A";
    public $snoring = "N/A";
    public $apnea = "N/A";
    public $breast_mass = "N/A";
    public $breast_discharge = "N/A";
    public $biopsy = "N/A";
    public $abnormal_mammogram = "N/A";
    public $cough = "N/A";
    public $sputum = "N/A";
    public $shortness_of_breath = "N/A";
    public $wheezing = "N/A";
    public $hemoptsyis = "N/A";
    public $asthma = "N/A";
    public $copd = "N/A";
    public $chest_pain = "N/A";
    public $palpitation = "N/A";
    public $syncope = "N/A";
    public $pnd = "N/A";
    public $doe = "N/A";
    public $orthopnea = "N/A";
    public $peripheal = "N/A";
    public $edema = "N/A";
    public $legpain_cramping = "N/A";
    public $history_murmur = "N/A";
    public $arrythmia = "N/A";
    public $heart_problem = "N/A";
    public $dysphagia = "N/A";
    public $heartburn = "N/A";
    public $bloating = "N/A";
    public $belching = "N/A";
    public $flatulence = "N/A";
    public $nausea = "N/A";
    public $vomiting = "N/A";
    public $hematemesis = "N/A";
    public $gastro_pain = "N/A";
    public $food_intolerance = "N/A";
    public $hepatitis = "N/A";
    public $jaundice = "N/A";
    public $hematochezia = "N/A";
    public $changed_bowel = "N/A";
    public $diarrhea = "N/A";
    public $constipation = "N/A";
    public $polyuria = "N/A";
    public $polydypsia = "N/A";
    public $dysuria = "N/A";
    public $hematuria = "N/A";
    public $frequency = "N/A";
    public $urgency = "N/A";
    public $incontinence = "N/A";
    public $renal_stones = "N/A";
    public $utis = "N/A";
    public $hesitancy = "N/A";
    public $dribbling = "N/A";
    public $stream = "N/A";
    public $nocturia = "N/A";
    public $erections = "N/A";
    public $ejaculations = "N/A";
    public $g = "N/A";
    public $p = "N/A";
    public $ap = "N/A";
    public $lc = "N/A";
    public $mearche = "N/A";
    public $menopause = "N/A";
    public $lmp = "N/A";
    public $f_frequency = "N/A";
    public $f_flow = "N/A";
    public $f_symptoms = "N/A";
    public $abnormal_hair_growth = "N/A";
    public $f_hirsutism = "N/A";
    public $joint_pain = "N/A";
    public $swelling = "N/A";
    public $m_redness = "N/A";
    public $m_warm = "N/A";
    public $m_stiffness = "N/A";
    public $muscle = "N/A";
    public $m_aches = "N/A";
    public $fms = "N/A";
    public $arthritis = "N/A";
    public $loc = "N/A";
    public $seizures = "N/A";
    public $stroke = "N/A";
    public $tia = "N/A";
    public $n_numbness = "N/A";
    public $n_weakness = "N/A";
    public $paralysis = "N/A";
    public $intellectual_decline = "N/A";
    public $memory_problems = "N/A";
    public $dementia = "N/A";
    public $n_headache = "N/A";
    public $s_cancer = "N/A";
    public $psoriasis = "N/A";
    public $s_acne = "N/A";
    public $s_other = "N/A";
    public $s_disease = "N/A";
    public $p_diagnosis = "N/A";
    public $p_medication = "N/A";
    public $depression = "N/A";
    public $anxiety = "N/A";
    public $social_difficulties = "N/A";
    public $thyroid_problems = "N/A";
    public $diabetes = "N/A";
    public $abnormal_blood = "N/A";
    public $anemia = "N/A";
    public $fh_blood_problems = "N/A";
    public $bleeding_problems = "N/A";
    public $allergies = "N/A";
    public $frequent_illness = "N/A";
    public $hiv = "N/A";
    public $hai_status = "N/A";

    /**
     * Constructor sets all Form attributes to their default value
     */

    function __construct($id = "")
    {
        if (is_numeric($id)) {
            $this->id = $id;
        } else {
            $id = "";
        }

        $this->date = date("Y-m-d H:i:s");
        $this->date_of_onset = date("Y-m-d");
        $this->_table = "form_ros";

        $this->pid = $GLOBALS['pid'];
        if ($id != "") {
            $this->populate();
        }
    }
    function populate()
    {
        parent::populate();
    }

    function set_id($id)
    {
        if (!empty($id) && is_numeric($id)) {
            $this->id = $id;
        }
    }
    function get_id()
    {
        return $this->id;
    }
    function set_pid($pid)
    {
        if (!empty($pid) && is_numeric($pid)) {
            $this->pid = $pid;
        }
    }
    function get_pid()
    {
        return $this->pid;
    }

    function get_date()
    {
        return $this->date;
    }

    function set_date($date)
    {
        if (!empty($date)) {
            $this->date = $date;
        }
    }

    function get_weight_change()
    {
        return $this->weight_change;
    }
    function set_weight_change($data)
    {
        if (!empty($data)) {
            $this->weight_change = $data;
        }
    }

    function get_weakness()
    {
        return $this->weakness;
    }
    function set_weakness($data)
    {
        if (!empty($data)) {
            $this->weakness = $data;
        }
    }

    function get_fatigue()
    {
        return $this->fatigue;
    }
    function set_fatigue($data)
    {
        if (!empty($data)) {
            $this->fatigue = $data;
        }
    }

    function get_anorexia()
    {
        return $this->anorexia;
    }
    function set_anorexia($data)
    {
        if (!empty($data)) {
            $this->anorexia = $data;
        }
    }

    function get_fever()
    {
        return $this->fever;
    }
    function set_fever($data)
    {
        if (!empty($data)) {
            $this->fever = $data;
        }
    }

    function get_chills()
    {
        return $this->chills;
    }
    function set_chills($data)
    {
        if (!empty($data)) {
            $this->chills = $data;
        }
    }

    function get_night_sweats()
    {
        return $this->night_sweats;
    }
    function set_night_sweats($data)
    {
        if (!empty($data)) {
            $this->night_sweats = $data;
        }
    }

    function get_insomnia()
    {
        return $this->insomnia;
    }
    function set_insomnia($data)
    {
        if (!empty($data)) {
            $this->insomnia = $data;
        }
    }

    function get_irritability()
    {
        return $this->irritability;
    }
    function set_irritability($data)
    {
        if (!empty($data)) {
            $this->irritability = $data;
        }
    }

    function get_heat_or_cold()
    {
        return $this->heat_or_cold;
    }
    function set_heat_or_cold($data)
    {
        if (!empty($data)) {
            $this->heat_or_cold = $data;
        }
    }

    function get_intolerance()
    {
        return $this->intolerance;
    }
    function set_intolerance($data)
    {
        if (!empty($data)) {
            $this->intolerance = $data;
        }
    }

    function get_change_in_vision()
    {
        return $this->change_in_vision;
    }
    function set_change_in_vision($data)
    {
        if (!empty($data)) {
            $this->change_in_vision = $data;
        }
    }
    function get_glaucoma_history()
    {
        return $this->glaucoma_history;
    }
    function set_glaucoma_history($data)
    {
        if (!empty($data)) {
            $this->glaucoma_history = $data;
        }
    }
    function get_eye_pain()
    {
        return $this->eye_pain;
    }
    function set_eye_pain($data)
    {
        if (!empty($data)) {
            $this->eye_pain = $data;
        }
    }
    function get_irritation()
    {
        return $this->irritation;
    }
    function set_irritation($data)
    {
        if (!empty($data)) {
            $this->irritation = $data;
        }
    }
    function get_redness()
    {
        return $this->redness;
    }
    function set_redness($data)
    {
        if (!empty($data)) {
            $this->redness = $data;
        }
    }
    function get_excessive_tearing()
    {
        return $this->excessive_tearing;
    }
    function set_excessive_tearing($data)
    {
        if (!empty($data)) {
            $this->excessive_tearing = $data;
        }
    }
    function get_double_vision()
    {
        return $this->double_vision;
    }
    function set_double_vision($data)
    {
        if (!empty($data)) {
            $this->double_vision = $data;
        }
    }
    function get_blind_spots()
    {
        return $this->blind_spots;
    }
    function set_blind_spots($data)
    {
        if (!empty($data)) {
            $this->blind_spots = $data;
        }
    }
    function get_photophobia()
    {
        return $this->photophobia;
    }
    function set_photophobia($data)
    {
        if (!empty($data)) {
            $this->photophobia = $data;
        }
    }

    function get_hearing_loss()
    {
        return $this->hearing_loss;
    }
    function set_hearing_loss($data)
    {
        if (!empty($data)) {
            $this->hearing_loss = $data;
        }
    }
    function get_discharge()
    {
        return $this->discharge;
    }
    function set_discharge($data)
    {
        if (!empty($data)) {
            $this->discharge = $data;
        }
    }
    function get_pain()
    {
        return $this->pain;
    }
    function set_pain($data)
    {
        if (!empty($data)) {
            $this->pain = $data;
        }
    }
    function get_vertigo()
    {
        return $this->vertigo;
    }
    function set_vertigo($data)
    {
        if (!empty($data)) {
            $this->vertigo = $data;
        }
    }
    function get_tinnitus()
    {
        return $this->tinnitus;
    }
    function set_tinnitus($data)
    {
        if (!empty($data)) {
            $this->tinnitus = $data;
        }
    }
    function get_frequent_colds()
    {
        return $this->frequent_colds;
    }
    function set_frequent_colds($data)
    {
        if (!empty($data)) {
            $this->frequent_colds = $data;
        }
    }
    function get_sore_throat()
    {
        return $this->sore_throat;
    }
    function set_sore_throat($data)
    {
        if (!empty($data)) {
            $this->sore_throat = $data;
        }
    }
    function get_sinus_problems()
    {
        return $this->sinus_problems;
    }
    function set_sinus_problems($data)
    {
        if (!empty($data)) {
            $this->sinus_problems = $data;
        }
    }
    function get_post_nasal_drip()
    {
        return $this->post_nasal_drip;
    }
    function set_post_nasal_drip($data)
    {
        if (!empty($data)) {
            $this->post_nasal_drip = $data;
        }
    }
    function get_nosebleed()
    {
        return $this->nosebleed;
    }
    function set_nosebleed($data)
    {
        if (!empty($data)) {
            $this->nosebleed = $data;
        }
    }
    function get_snoring()
    {
        return $this->snoring;
    }
    function set_snoring($data)
    {
        if (!empty($data)) {
            $this->snoring = $data;
        }
    }
    function get_apnea()
    {
        return $this->apnea;
    }
    function set_apnea($data)
    {
        if (!empty($data)) {
            $this->apnea = $data;
        }
    }
    function get_breast_mass()
    {
        return $this->breast_mass;
    }
    function set_breast_mass($data)
    {
        if (!empty($data)) {
            $this->breast_mass = $data;
        }
    }
    function get_breast_discharge()
    {
        return $this->breast_discharge;
    }
    function set_breast_discharge($data)
    {
        if (!empty($data)) {
            $this->breast_discharge = $data;
        }
    }
    function get_biopsy()
    {
        return $this->biopsy;
    }
    function set_biopsy($data)
    {
        if (!empty($data)) {
            $this->biopsy = $data;
        }
    }
    function get_abnormal_mammogram()
    {
        return $this->abnormal_mammogram;
    }
    function set_abnormal_mammogram($data)
    {
        if (!empty($data)) {
            $this->abnormal_mammogram = $data;
        }
    }
    function get_cough()
    {
        return $this->cough;
    }
    function set_cough($data)
    {
        if (!empty($data)) {
            $this->cough = $data;
        }
    }
    function set_sputum($data)
    {
        if (!empty($data)) {
            $this->sputum = $data;
        }
    }
    function get_sputum()
    {
        return $this->sputum;
    }
    function get_shortness_of_breath()
    {
        return $this->shortness_of_breath;
    }
    function set_shortness_of_breath($data)
    {
        if (!empty($data)) {
            $this->shortness_of_breath = $data;
        }
    }
    function get_wheezing()
    {
        return $this->wheezing;
    }
    function set_wheezing($data)
    {
        if (!empty($data)) {
            $this->wheezing = $data;
        }
    }
    function get_hemoptsyis()
    {
        return $this->hemoptsyis;
    }
    function set_hemoptsyis($data)
    {
        if (!empty($data)) {
            $this->hemoptsyis = $data;
        }
    }
    function get_asthma()
    {
        return $this->asthma;
    }
    function set_asthma($data)
    {
        if (!empty($data)) {
            $this->asthma = $data;
        }
    }
    function get_copd()
    {
        return $this->copd;
    }
    function set_copd($data)
    {
        if (!empty($data)) {
            $this->copd = $data;
        }
    }

    function get_chest_pain()
    {
        return $this->chest_pain;
    }
    function set_chest_pain($data)
    {
        if (!empty($data)) {
            $this->chest_pain = $data;
        }
    }
    function get_palpitation()
    {
        return $this->palpitation;
    }
    function set_palpitation($data)
    {
        if (!empty($data)) {
            $this->palpitation = $data;
        }
    }
    function get_syncope()
    {
        return $this->syncope;
    }
    function set_syncope($data)
    {
        if (!empty($data)) {
            $this->syncope = $data;
        }
    }
    function get_pnd()
    {
        return $this->pnd;
    }
    function set_pnd($data)
    {
        if (!empty($data)) {
            $this->pnd = $data;
        }
    }
    function get_doe()
    {
        return $this->doe;
    }
    function set_doe($data)
    {
        if (!empty($data)) {
            $this->doe = $data;
        }
    }
    function get_orthopnea()
    {
        return $this->orthopnea;
    }
    function set_orthopnea($data)
    {
        if (!empty($data)) {
            $this->orthopnea = $data;
        }
    }
    function get_peripheal()
    {
        return $this->peripheal;
    }
    function set_peripheal($data)
    {
        if (!empty($data)) {
            $this->peripheal = $data;
        }
    }
    function get_edema()
    {
        return $this->edema;
    }
    function set_edema($data)
    {
        if (!empty($data)) {
            $this->edema = $data;
        }
    }
    function get_legpain_cramping()
    {
        return $this->legpain_cramping;
    }
    function set_legpain_cramping($data)
    {
        if (!empty($data)) {
            $this->legpain_cramping = $data;
        }
    }
    function get_history_murmur()
    {
        return $this->history_murmur;
    }
    function set_history_murmur($data)
    {
        if (!empty($data)) {
            $this->history_murmur = $data;
        }
    }
    function get_arrythmia()
    {
        return $this->arrythmia;
    }
    function set_arrythmia($data)
    {
        if (!empty($data)) {
            $this->arrythmia = $data;
        }
    }
    function get_heart_problem()
    {
        return $this->heart_problem;
    }
    function set_heart_problem($data)
    {
        if (!empty($data)) {
            $this->heart_problem = $data;
        }
    }

    function get_polyuria()
    {
        return $this->polyuria;
    }
    function set_polyuria($data)
    {
        if (!empty($data)) {
            $this->polyuria = $data;
        }
    }
    function get_polydypsia()
    {
        return $this->polydypsia;
    }
    function set_polydypsia($data)
    {
        if (!empty($data)) {
            $this->polydypsia = $data;
        }
    }
    function get_dysuria()
    {
        return $this->dysuria;
    }
    function set_dysuria($data)
    {
        if (!empty($data)) {
            $this->dysuria = $data;
        }
    }
    function get_hematuria()
    {
        return $this->hematuria;
    }
    function set_hematuria($data)
    {
        if (!empty($data)) {
            $this->hematuria = $data;
        }
    }
    function get_frequency()
    {
        return $this->frequency;
    }
    function set_frequency($data)
    {
        if (!empty($data)) {
            $this->frequency = $data;
        }
    }
    function get_urgency()
    {
        return $this->urgency;
    }
    function set_urgency($data)
    {
        if (!empty($data)) {
            $this->urgency = $data;
        }
    }
    function get_incontinence()
    {
        return $this->incontinence;
    }
    function set_incontinence($data)
    {
        if (!empty($data)) {
            $this->incontinence = $data;
        }
    }
    function get_renal_stones()
    {
        return $this->renal_stones;
    }
    function set_renal_stones($data)
    {
        if (!empty($data)) {
            $this->renal_stones = $data;
        }
    }
    function get_utis()
    {
        return $this->utis;
    }
    function set_utis($data)
    {
        if (!empty($data)) {
            $this->utis = $data;
        }
    }

    function get_hesitancy()
    {
        return $this->hesitancy;
    }
    function set_hesitancy($data)
    {
        if (!empty($data)) {
            $this->hesitancy = $data;
        }
    }
    function get_dribbling()
    {
        return $this->dribbling;
    }
    function set_dribbling($data)
    {
        if (!empty($data)) {
            $this->dribbling = $data;
        }
    }
    function get_stream()
    {
        return $this->stream;
    }
    function set_stream($data)
    {
        if (!empty($data)) {
            $this->stream = $data;
        }
    }
    function get_nocturia()
    {
        return $this->nocturia;
    }
    function set_nocturia($data)
    {
        if (!empty($data)) {
            $this->nocturia = $data;
        }
    }
    function get_erections()
    {
        return $this->erections;
    }
    function set_erections($data)
    {
        if (!empty($data)) {
            $this->erections = $data;
        }
    }
    function get_ejaculations()
    {
        return $this->ejaculations;
    }
    function set_ejaculations($data)
    {
        if (!empty($data)) {
            $this->ejaculations = $data;
        }
    }

    function get_g()
    {
        return $this->g;
    }
    function set_g($data)
    {
        if (!empty($data)) {
            $this->g = $data;
        }
    }
    function get_p()
    {
        return $this->p;
    }
    function set_p($data)
    {
        if (!empty($data)) {
            $this->p = $data;
        }
    }
    function get_ap()
    {
        return $this->ap;
    }
    function set_ap($data)
    {
        if (!empty($data)) {
            $this->ap = $data;
        }
    }
    function get_lc()
    {
        return $this->lc;
    }
    function set_lc($data)
    {
        if (!empty($data)) {
            $this->lc = $data;
        }
    }
    function get_mearche()
    {
        return $this->mearche;
    }
    function set_mearche($data)
    {
        if (!empty($data)) {
            $this->mearche = $data;
        }
    }
    function get_menopause()
    {
        return $this->menopause;
    }
    function set_menopause($data)
    {
        if (!empty($data)) {
            $this->menopause = $data;
        }
    }
    function get_lmp()
    {
        return $this->lmp;
    }
    function set_lmp($data)
    {
        if (!empty($data)) {
            $this->lmp = $data;
        }
    }
    function get_f_frequency()
    {
        return $this->f_frequency;
    }
    function set_f_frequency($data)
    {
        if (!empty($data)) {
            $this->f_frequency = $data;
        }
    }
    function get_f_flow()
    {
        return $this->f_flow;
    }
    function set_f_flow($data)
    {
        if (!empty($data)) {
            $this->f_flow = $data;
        }
    }
    function get_f_symptoms()
    {
        return $this->f_symptoms;
    }
    function set_f_symptoms($data)
    {
        if (!empty($data)) {
            $this->f_symptoms = $data;
        }
    }
    function get_abnormal_hair_growth()
    {
        return $this->abnormal_hair_growth;
    }
    function set_abnormal_hair_growth($data)
    {
        if (!empty($data)) {
            $this->abnormal_hair_growth = $data;
        }
    }
    function get_f_hirsutism()
    {
        return $this->f_hirsutism;
    }
    function set_f_hirsutism($data)
    {
        if (!empty($data)) {
            $this->f_hirsutism = $data;
        }
    }

    function get_joint_pain()
    {
        return $this->joint_pain;
    }
    function set_joint_pain($data)
    {
        if (!empty($data)) {
            $this->joint_pain = $data;
        }
    }
    function get_swelling()
    {
        return $this->swelling;
    }
    function set_swelling($data)
    {
        if (!empty($data)) {
            $this->swelling = $data;
        }
    }
    function get_m_redness()
    {
        return $this->m_redness;
    }
    function set_m_redness($data)
    {
        if (!empty($data)) {
            $this->m_redness = $data;
        }
    }
    function get_m_warm()
    {
        return $this->m_warm;
    }
    function set_m_warm($data)
    {
        if (!empty($data)) {
            $this->m_warm = $data;
        }
    }
    function get_m_stiffness()
    {
        return $this->m_stiffness;
    }
    function set_m_stiffness($data)
    {
        if (!empty($data)) {
            $this->m_stiffness = $data;
        }
    }
    function get_muscle()
    {
        return $this->muscle;
    }
    function set_muscle($data)
    {
        if (!empty($data)) {
            $this->muscle = $data;
        }
    }
    function get_m_aches()
    {
        return $this->m_aches;
    }
    function set_m_aches($data)
    {
        if (!empty($data)) {
            $this->m_aches = $data;
        }
    }
    function get_fms()
    {
        return $this->fms;
    }
    function set_fms($data)
    {
        if (!empty($data)) {
            $this->fms = $data;
        }
    }
    function get_arthritis()
    {
        return $this->arthritis;
    }
    function set_arthritis($data)
    {
        if (!empty($data)) {
            $this->arthritis = $data;
        }
    }

    function get_loc()
    {
        return $this->loc;
    }
    function set_loc($data)
    {
        if (!empty($data)) {
            $this->loc = $data;
        }
    }
    function get_seizures()
    {
        return $this->seizures;
    }
    function set_seizures($data)
    {
        if (!empty($data)) {
            $this->seizures = $data;
        }
    }
    function get_stroke()
    {
        return $this->stroke;
    }
    function set_stroke($data)
    {
        if (!empty($data)) {
            $this->stroke = $data;
        }
    }
    function get_tia()
    {
        return $this->tia;
    }
    function set_tia($data)
    {
        if (!empty($data)) {
            $this->tia = $data;
        }
    }
    function get_n_numbness()
    {
        return $this->n_numbness;
    }
    function set_n_numbness($data)
    {
        if (!empty($data)) {
            $this->n_numbness = $data;
        }
    }
    function get_n_weakness()
    {
        return $this->n_weakness;
    }
    function set_n_weakness($data)
    {
        if (!empty($data)) {
            $this->n_weakness = $data;
        }
    }
    function get_paralysis()
    {
        return $this->paralysis;
    }
    function set_paralysis($data)
    {
        if (!empty($data)) {
            $this->paralysis = $data;
        }
    }
    function get_intellectual_decline()
    {
        return $this->intellectual_decline;
    }
    function set_intellectual_decline($data)
    {
        if (!empty($data)) {
            $this->intellectual_decline = $data;
        }
    }
    function get_memory_problems()
    {
        return $this->memory_problems;
    }
    function set_memory_problems($data)
    {
        if (!empty($data)) {
            $this->memory_problems = $data;
        }
    }
    function get_dementia()
    {
        return $this->dementia;
    }
    function set_dementia($data)
    {
        if (!empty($data)) {
            $this->dementia = $data;
        }
    }
    function get_n_headache()
    {
        return $this->n_headache;
    }
    function set_n_headache($data)
    {
        if (!empty($data)) {
            $this->n_headache = $data;
        }
    }

    function get_s_cancer()
    {
        return $this->s_cancer;
    }
    function set_s_cancer($data)
    {
        if (!empty($data)) {
            $this->s_cancer = $data;
        }
    }
    function get_psoriasis()
    {
        return $this->psoriasis;
    }
    function set_psoriasis($data)
    {
        if (!empty($data)) {
            $this->psoriasis = $data;
        }
    }
    function get_s_acne()
    {
        return $this->s_acne;
    }
    function set_s_acne($data)
    {
        if (!empty($data)) {
            $this->s_acne = $data;
        }
    }
    function get_s_other()
    {
        return $this->s_other;
    }
    function set_s_other($data)
    {
        if (!empty($data)) {
            $this->s_other = $data;
        }
    }
    function get_s_disease()
    {
        return $this->s_disease;
    }
    function set_s_disease($data)
    {
        if (!empty($data)) {
            $this->s_disease = $data;
        }
    }

    function get_p_diagnosis()
    {
        return $this->p_diagnosis;
    }
    function set_p_diagnosis($data)
    {
        if (!empty($data)) {
            $this->p_diagnosis = $data;
        }
    }
    function get_p_medication()
    {
        return $this->p_medication;
    }
    function set_p_medication($data)
    {
        if (!empty($data)) {
            $this->p_medication = $data;
        }
    }
    function get_depression()
    {
        return $this->depression;
    }
    function set_depression($data)
    {
        if (!empty($data)) {
            $this->depression = $data;
        }
    }
    function get_anxiety()
    {
        return $this->anxiety;
    }
    function set_anxiety($data)
    {
        if (!empty($data)) {
            $this->anxiety = $data;
        }
    }
    function get_social_difficulties()
    {
        return $this->social_difficulties;
    }
    function set_social_difficulties($data)
    {
        if (!empty($data)) {
            $this->social_difficulties = $data;
        }
    }

    function get_thyroid_problems()
    {
        return $this->thyroid_problems;
    }
    function set_thyroid_problems($data)
    {
        if (!empty($data)) {
            $this->thyroid_problems = $data;
        }
    }
    function get_diabetes()
    {
        return $this->diabetes;
    }
    function set_diabetes($data)
    {
        if (!empty($data)) {
            $this->diabetes = $data;
        }
    }
    function get_abnormal_blood()
    {
        return $this->abnormal_blood;
    }
    function set_abnormal_blood($data)
    {
        if (!empty($data)) {
            $this->abnormal_blood = $data;
        }
    }

    function get_anemia()
    {
        return $this->anemia;
    }
    function set_anemia($data)
    {
        if (!empty($data)) {
            $this->anemia = $data;
        }
    }
    function get_fh_blood_problems()
    {
        return $this->fh_blood_problems;
    }
    function set_fh_blood_problems($data)
    {
        if (!empty($data)) {
            $this->fh_blood_problems = $data;
        }
    }
    function get_bleeding_problems()
    {
        return $this->bleeding_problems;
    }
    function set_bleeding_problems($data)
    {
        if (!empty($data)) {
            $this->bleeding_problems = $data;
        }
    }
    function get_allergies()
    {
        return $this->allergies;
    }
    function set_allergies($data)
    {
        if (!empty($data)) {
            $this->allergies = $data;
        }
    }
    function get_frequent_illness()
    {
        return $this->frequent_illness;
    }
    function set_frequent_illness($data)
    {
        if (!empty($data)) {
            $this->frequent_illness = $data;
        }
    }
    function get_hiv()
    {
        return $this->hiv;
    }
    function set_hiv($data)
    {
        if (!empty($data)) {
            $this->hiv = $data;
        }
    }
    function get_hai_status()
    {
        return $this->hai_status;
    }
    function set_hai_status($data)
    {
        if (!empty($data)) {
            $this->hai_status = $data;
        }
    }

    function get_options()
    {
        $ret = ["N/A" => xlt('N/A'),"YES" => xlt('YES'),"NO" => xlt('NO')];
        return $ret;
    }

    function get_dysphagia()
    {
        return $this->dysphagia;
    }
    function set_dysphagia($data)
    {
        if (!empty($data)) {
            $this->dysphagia = $data;
        }
    }
    function get_heartburn()
    {
        return $this->heartburn;
    }
    function set_heartburn($data)
    {
        if (!empty($data)) {
            $this->heartburn = $data;
        }
    }
    function get_bloating()
    {
        return $this->bloating;
    }
    function set_bloating($data)
    {
        if (!empty($data)) {
            $this->bloating = $data;
        }
    }
    function get_belching()
    {
        return $this->belching;
    }
    function set_belching($data)
    {
        if (!empty($data)) {
            $this->belching = $data;
        }
    }
    function get_flatulence()
    {
        return $this->flatulence;
    }
    function set_flatulence($data)
    {
        if (!empty($data)) {
            $this->flatulence = $data;
        }
    }
    function get_nausea()
    {
        return $this->nausea;
    }
    function set_nausea($data)
    {
        if (!empty($data)) {
            $this->nausea = $data;
        }
    }
    function get_vomiting()
    {
        return $this->vomiting;
    }
    function set_vomiting($data)
    {
        if (!empty($data)) {
            $this->vomiting = $data;
        }
    }
    function get_hematemesis()
    {
        return $this->hematemesis;
    }
    function set_hematemesis($data)
    {
        if (!empty($data)) {
            $this->hematemesis = $data;
        }
    }
    function get_gastro_pain()
    {
        return $this->gastro_pain;
    }
    function set_gastro_pain($data)
    {
        if (!empty($data)) {
            $this->gastro_pain = $data;
        }
    }
    function get_food_intolerance()
    {
        return $this->food_intolerance;
    }
    function set_food_intolerance($data)
    {
        if (!empty($data)) {
            $this->food_intolerance = $data;
        }
    }
    function get_hepatitis()
    {
        return $this->hepatitis;
    }
    function set_hepatitis($data)
    {
        if (!empty($data)) {
            $this->hepatitis = $data;
        }
    }
    function get_jaundice()
    {
        return $this->jaundice;
    }
    function set_jaundice($data)
    {
        if (!empty($data)) {
            $this->jaundice = $data;
        }
    }
    function get_hematochezia()
    {
        return $this->hematochezia;
    }
    function set_hematochezia($data)
    {
        if (!empty($data)) {
            $this->hematochezia = $data;
        }
    }
    function get_changed_bowel()
    {
        return $this->changed_bowel;
    }
    function set_changed_bowel($data)
    {
        if (!empty($data)) {
            $this->changed_bowel = $data;
        }
    }
    function get_diarrhea()
    {
        return $this->diarrhea;
    }
    function set_diarrhea($data)
    {
        if (!empty($data)) {
            $this->diarrhea = $data;
        }
    }
    function get_constipation()
    {
        return $this->constipation;
    }
    function set_constipation($data)
    {
        if (!empty($data)) {
            $this->constipation = $data;
        }
    }
    function toString($html = false)
    {
        $string = "\n" . "ID: " . $this->id . "\n";
        return $html ? nl2br($string) : $string;
    }

    function persist()
    {
        parent::persist();
    }
}   // end of Form
