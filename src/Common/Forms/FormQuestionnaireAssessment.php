<?php

/**
 * FormQuestionnaireAssessment represents an encounter forms_questionnaire_assessment database table record used
 * inside OpenEMR.  It sets up default properties from the session but consumers can override the options.
 *
 * @see \OpenEMR\Services\FormService on how this class is saved in the database.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms;

class FormQuestionnaireAssessment extends BaseForm
{
    private ?string $response_id;
    private int $activity;
    private ?string $copyright;
    private ?string $response_meta;
    private ?string $questionnaire_id;
    private ?string $questionnaire;
    private ?string $questionnaire_response;
    private ?string $lform;
    private ?string $lform_response;

    public function __construct()
    {
        parent::__construct();
        $this->setFormdir("questionnaire_assessments");
        $this->setFormName("questionnaire_assessment");
        $this->response_id = null;
        $this->activity = 1;
        $this->copyright = null;
        $this->response_meta = null;
        $this->questionnaire_id = null;
        $this->questionnaire = null;
        $this->questionnaire_response = null;
        $this->lform = null;
        $this->lform_response = null;
    }

    public function getFormTableDataForSave()
    {
        $data = [
            'date' => $this->getDate()->format("Y-m-d H:i:s")
            ,'response_id' => $this->getResponseId()
            ,'pid' => $this->getPid()
            ,'user' => $this->getUser()
            ,'groupname' => $this->getGroupname()
            ,'authorized' => $this->getAuthorized()
            ,'activity' => $this->getActivity()
            ,'copyright' => $this->getCopyright()
            ,'form_name' => $this->getFormName()
            ,'response_meta' => $this->getResponseMeta()
            ,'questionnaire_id' => $this->getQuestionnaireId()
            ,'questionnaire' => $this->getQuestionnaire()
            ,'questionnaire_response' => $this->getQuestionnaireResponse()
            ,'lform' => $this->getLform()
            ,'lform_response' => $this->getLformResponse()
        ];
        return $data;
    }

    public function getFormTableName()
    {
        return "form_questionnaire_assessments";
    }

    /**
     * @return mixed
     */
    public function getResponseId()
    {
        return $this->response_id;
    }

    /**
     * @param mixed $response_id
     * @return FormQuestionnaireAssessment
     */
    public function setResponseId($response_id)
    {
        $this->response_id = $response_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param mixed $activity
     * @return FormQuestionnaireAssessment
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @param mixed $copyright
     * @return FormQuestionnaireAssessment
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponseMeta()
    {
        return $this->response_meta;
    }

    /**
     * @param mixed $response_meta
     * @return FormQuestionnaireAssessment
     */
    public function setResponseMeta($response_meta)
    {
        $this->response_meta = $response_meta;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestionnaireId()
    {
        return $this->questionnaire_id;
    }

    /**
     * @param mixed $questionnaire_id
     * @return FormQuestionnaireAssessment
     */
    public function setQuestionnaireId($questionnaire_id)
    {
        $this->questionnaire_id = $questionnaire_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestionnaireResponse()
    {
        return $this->questionnaire_response;
    }

    /**
     * @param mixed $questionnaire_response
     * @return FormQuestionnaireAssessment
     */
    public function setQuestionnaireResponse($questionnaire_response)
    {
        $this->questionnaire_response = $questionnaire_response;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLform()
    {
        return $this->lform;
    }

    /**
     * @param mixed $lform
     * @return FormQuestionnaireAssessment
     */
    public function setLform($lform)
    {
        $this->lform = $lform;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLformResponse()
    {
        return $this->lform_response;
    }

    /**
     * @param mixed $lform_response
     * @return FormQuestionnaireAssessment
     */
    public function setLformResponse($lform_response)
    {
        $this->lform_response = $lform_response;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * @param mixed $questionnaire
     * @return FormQuestionnaireAssessment
     */
    public function setQuestionnaire($questionnaire)
    {
        $this->questionnaire = $questionnaire;
        return $this;
    }
}
