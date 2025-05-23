<?php

class AmcItemizedActionData implements JsonSerializable
{
    private $numeratorActionData;
    private $denominatorActionData;

    public function __construct()
    {
        $this->numeratorActionData = [];
        $this->denominatorActionData = [];
    }

    /**
     * Merges the actions in the passed in $obj with the current action data.  Any existing keys are overwritten
     * @param AmcItemizedActionData $obj
     */
    public function addActionObject(AmcItemizedActionData $obj)
    {
        foreach ($obj->getNumeratorActionData() as $key => $data) {
            // note this will overwrite any existing keys... hopefully that's ok
            $this->numeratorActionData[$key] = $data;
        }
        foreach ($obj->getDenominatorActionData() as $key => $data) {
            // note this will overwrite any existing keys... hopefully that's ok
            $this->denominatorActionData[$key] = $data;
        }
    }

    public function addDenominatorActionData($action, bool $value, $details, $label = '')
    {
        $this->addActionData($action, $value, $details, $label, false);
    }

    public function addNumeratorActionData($action, bool $value, $details, $label = '')
    {
        $this->addActionData($action, $value, $details, $label, true);
    }

    public function addActionData($action, bool $value, $details, $label = '', $isNumerator = true)
    {
        // make sure we can serialize the details
        if (is_object($details) && !$details instanceof JsonSerializable) {
            throw new \InvalidArgumentException("Details must be serializable to a JSON");
        }
        $data = ['value' => $value, 'details' => $details];
        if (!empty($label)) {
            $data['label'] = $label;
        }
        if ($isNumerator) {
            $this->numeratorActionData[$action] = $data;
        } else {
            $this->denominatorActionData[$action] = $data;
        }
    }

    public function getNumeratorActionData(): array
    {
        return $this->numeratorActionData;
    }

    public function getDenominatorActionData(): array
    {
        return $this->denominatorActionData;
    }

    public function getActionData(): array
    {
        return ['numerator' => $this->getNumeratorActionData(), 'denominator' => $this->getDenominatorActionData()];
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): mixed
    {
        return $this->getActionData();
    }
}
