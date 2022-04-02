<?php

class AmcItemizedActionData implements JsonSerializable
{
    private $actionData;

    public function __construct()
    {
        $this->actionData = [];
    }

    /**
     * Merges the actions in the passed in $obj with the current action data.  Any existing keys are overwritten
     * @param AmcItemizedActionData $obj
     */
    public function addActionObject(AmcItemizedActionData $obj)
    {
        foreach ($obj->actionData as $key => $data) {
            // note this will overwrite any existing keys... hopefully that's ok
            $this->actionData[$key] = $data;
        }
    }

    public function addActionData($action, bool $value, $details, $label = '')
    {
        // make sure we can serialize the details
        if (is_object($details) && !$details instanceof JsonSerializable) {
            throw new \InvalidArgumentException("Details must be serializable to a JSON");
        }
        $data = ['value' => $value, 'details' => $details];
        if (!empty($label)) {
            $data['label'] = $label;
        }
        $this->actionData[$action] = $data;
    }

    public function getActionData(): array
    {
        return $this->actionData;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->getActionData();
    }
}
