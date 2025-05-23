<?php

namespace OpenEMR\Services;

use Exception;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\PHPFHIRResponseParser;

trait QuestionnaireTraits
{
    /**
     * @param $group
     * @param $item
     * @param $displayName
     * @return mixed|string|null
     */
    public function getInstrumentName($group, $item = null, $displayName = true)
    {
        if ($item && $this->isRepeating($item)) {
            $group = $item;
        }

        $name = $this->getText($group);
        $linkId = null;
        if (empty($name)) {
            $name = "Top Level";
        } else {
            $linkId = $this->getLinkId($group) ?? '';
        }
        if (!$displayName) {
            return $linkId;
        }

        return $name;
    }

    /**
     * @param $item
     * @return bool
     */
    public function isRepeating($item): bool
    {
        if ($item->get_fhirElementName() !== 'Questionnaire.Item') {
            return false;
        }
        $repeats = $item->getRepeats();

        return $repeats && $repeats->getValue();
    }

    /**
     * @param $o
     * @return mixed|null
     */
    public function getValue($o)
    {
        if ($o === null) {
            return null;
        }
        while (method_exists((object)$o, 'getValue')) {
            $o = $o->getValue();
        }

        return $o;
    }

    /**
     * @param $item
     * @return mixed|void
     */
    public function getText($item)
    {
        $text = $item->getText();
        if ($text && method_exists($text, "getValue")) {
            return $text->getValue();
        }
    }

    /**
     * @param $item
     * @return mixed|null
     */
    public function getLinkId($item)
    {
        if (!in_array($item->get_fhirElementName(), ['Questionnaire.Item', 'QuestionnaireResponse.Item'])) {
            return null;
        }

        return $item->getLinkId()->getValue();
    }

    /**
     * @param $group
     * @param $item
     * @return mixed|null
     */
    public function getInstrumentPath($group, $item = null)
    {
        if ($item && $this->isRepeating($item)) {
            $group = $item;
        }

        return $this->getLinkId($group);
    }

    /**
     * @param $item
     * @return mixed|void|null
     */
    public function getType($item)
    {
        if ($item->get_fhirElementName() === 'Questionnaire.Item') {
            $type = $this->getValue($item->getType());
            return $type;
        }
    }

    /**
     * @param $data
     * @return array|object|string
     * @throws Exception
     */
    public function parse($data, $autoLoad = false)
    {
        $parser = new PHPFHIRResponseParser($autoLoad);
        if (is_array($data) || is_object($data)) {
            // this is so the parser can set up necessary namespaces
            $data = $this->jsonSerialize($data);
        }
        return $parser->parse($data);
    }

    /**
     * @param $fhirObjectOrArray
     * @return false|string
     * @throws Exception
     */
    public function jsonSerialize($fhirObjectOrArray): mixed
    {
        $a = $this->fhirObjectToArray($fhirObjectOrArray);
        return json_encode($a, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $fhirObjectOrArray
     * @return array|mixed
     * @throws Exception
     */
    public function fhirObjectToArray($fhirObjectOrArray)
    {
        if (is_array($fhirObjectOrArray)) {
            return $fhirObjectOrArray;
        } elseif (is_object($fhirObjectOrArray)) {
            $a = $fhirObjectOrArray->jsonSerialize();
            $a = json_decode(json_encode($a), true);
            $handle = function (&$a) use (&$handle) {
                foreach ($a as $key => &$value) {
                    if (gettype($key) === 'string' && $key[0] === '_') {
                        unset($a[$key]);
                        continue;
                    }
                    if (is_array($value)) {
                        $handle($value);
                    }
                }
            };
            $handle($a);
            return $a;
        } else {
            throw new Exception(xlt('A valid FHIR object or array must be specified.'));
        }
    }

    /**
     * @param $FHIRObject
     * @return false|string
     */
    public function xmlSerialize($FHIRObject)
    {
        $dom = dom_import_simplexml($FHIRObject->xmlSerialize())->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * @param $mixed
     * @return array|mixed|string
     * @throws Exception
     */
    public function getTypedValue($mixed)
    {
        $arr = '';
        $vs = $this->fhirObjectToArray($mixed);
        foreach ($vs as $k => $v) {
            $a[$k] = $v;
            $arr = $this->parseAnswer($a, true);
        }

        return $arr;
    }

    /**
     * @param $answer
     * @param $display
     * @return array|mixed
     */
    private function parseAnswer($answer, $display = false)
    {
        $obv = array();
        $type = key($answer);
        switch ($type) {
            case "valueBoolean":
                $obv['type'] = 'boolean';
                $obv['display'] = $answer[$type];
                break;
            case "valueDecimal":
                $obv['type'] = 'decimal';
                $obv['display'] = $answer[$type];
                break;
            case "valueInteger":
                $obv['type'] = 'integer';
                $obv['display'] = $answer[$type];
                break;
            case "valueDate":
                $obv['type'] = 'date';
                $obv['display'] = $answer[$type];
                break;
            case "valueDateTime":
                $obv['type'] = 'datetime';
                $obv['display'] = $answer[$type];
                break;
            case "valueTime":
                $obv['type'] = 'time';
                $obv['display'] = $answer[$type];
                break;
            case "valueString":
                $obv['type'] = 'string';
                $obv['display'] = $answer[$type];
                break;
            case "valueUri":
                $obv['type'] = 'uri';
                $obv['display'] = $answer[$type];
                break;
            case "valueCoding":
                $obv = array(
                    'type' => 'coding',
                    'system' => $answer[$type]['system'] ?? null,
                    'code' => $answer[$type]['code'],
                    'display' => $answer[$type]['display'],
                );
                break;
            case "valueQuantity":
                $obv['type'] = 'quantity';
                $obv['display'] = $answer[$type];
                break;
            case "valueAttachment":
                // todo
                break;
            case "valueReference":
                break;
        }
        if ($display) {
            return $obv['display'];
        }
        return $obv;
    }

    /**
     * @param $item
     * @return mixed|string
     */
    public function getFieldName($item)
    {
        $n = $item->getLinkId()->getValue();
        return $n ?? null;
    }

    /**
     * @param $timestamp
     * @return mixed
     */
    public function formatFHIRDateTime($timestamp)
    {
        return $this->getDateTime($timestamp)->format('Y-m-d\TH:i:sP');
    }

    /**
     * @param $mixed
     * @return mixed|DateTime
     */
    private function getDateTime($mixed)
    {
        $type = gettype($mixed);

        if ($type === 'string') {
            return new \DateTime($mixed);
        } elseif ($type === 'integer') {
            $d = new \DateTime();
            $d->setTimestamp($mixed);
            return $d;
        } else {
            // Assume this is already a DateTime object.
            return $mixed;
        }
    }

    /**
     * @param $timestamp
     * @return mixed
     */
    public function formatFHIRTime($timestamp)
    {
        return $this->getDateTime($timestamp)->format('H:i:s');
    }

    /**
     * @param $mixed
     * @return mixed
     */
    public function formatOeDateTime($mixed)
    {
        return $this->getDateTime($mixed)->format('Y-m-d H:i');
    }

    /**
     * @param $mixed
     * @return mixed
     */
    public function formatOeDateTimeWithSeconds($mixed)
    {
        return $this->getDateTime($mixed)->format('Y-m-d H:i:s');
    }

    /**
     * @param $mixed
     * @return mixed
     */
    public function formatOeTime($mixed)
    {
        return $this->getDateTime($mixed)->format('H:i');
    }

    /**
     * @param      $response
     * @param bool $encode
     * @return array|false|string
     * @throws Exception
     */
    public function extractResponseMetaData($response, bool $encode = false)
    {
        if (is_string($response)) {
            $response = json_decode($response, true);
        }
        $fhirObj = new FHIRQuestionnaireResponse($response);
        $meta['id'] = $fhirObj->getId();
        $meta['encounter'] = $fhirObj->getEncounter();
        $meta['qid'] = $this->getValue($fhirObj->getQuestionnaire());
        if ($encode) {
            return $this->jsonSerialize($meta);
        }
        return $meta;
    }

    /**
     * @param $response
     * @param $meta
     * @return mixed
     * @throws Exception
     */
    public function insertResponseMetaData($response, $meta, bool $encode = true)
    {
        if (is_string($response)) {
            $response = json_decode($response, true);
        }
        if (is_string($meta)) {
            $meta = json_decode($meta, true);
        }
        $fhirObj = new FHIRQuestionnaireResponse($response);
        $fhirObj->setId(new FHIRId($meta['id']));
        $encRef = new FHIRReference();
        $encRef->setReference(new FHIRString($meta['encounter']));
        $fhirObj->setEncounter($encRef);
        $fhirObj->setQuestionnaire(new FHIRCanonical($meta['qid']));
        if ($encode) {
            return $this->jsonSerialize($fhirObj);
        }
        return $fhirObj;
    }

    /**
     * @param      $answer
     * @param bool $display
     * @return array|mixed
     */
    private function setAnswer($answer, bool $display = false)
    {
        $ans_set = [];
        if (count($answer ?? []) > 1) {
            foreach ($answer as $ans) {
                $a = $this->parseAnswer($ans, $display);
                $ans_set[] = $a;
            }
        } elseif (!empty($answer[0])) {
            $a = $this->parseAnswer($answer[0], $display);
            return $a;
        } else {
            $a = $this->parseAnswer($answer, $display);
            return $a;
        }
        return $ans_set;
    }
}
