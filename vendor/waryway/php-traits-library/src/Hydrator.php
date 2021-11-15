<?php
namespace Waryway\PhpTraitsLibrary;

use stdClass;

trait Hydrator
{
    protected $strict = false;

    public function __get($name) {
        if (property_exists($this,$name)) {
            return $this->$name;    
        }
    }
    
    public function __set($name, $value) {
        if ($this->strict && !property_exists($this,$name)) {
            $this->notExists($name);
        } else {
            $this->$name = $value;
        }
    }
    
    public function __isset($name) {
        if (property_exists($this,$name)) {
            return isset($this->$name);
        } else {
            return false;
        }
    }
    
    public function __unset($name) {
        if (property_exists($this,$name)) {
            unset($this->$name);
        } else {
            $this->notExists($name);
        }
    }
    
    /**
     * Given a class object, hydrate it from a provided stdClass data object.
     *
     * @param $input stdClass - the data to dump into the class being hydrated.
     * @param $strict bool - what to do if the data provided isn't an option in the class being hydrated.
     */
    public function hydrate(stdClass $input, $strict = false) {
        $this->strict = $strict;
        $failedStrict = [];
        foreach($input as $name => $value) {
            if ($this->strict && !property_exists($this,$name)){
                $failedStrict[$name] = $value;
            } else {
                $this->$name = $value;
            }
        }
        
        if (count($failedStrict)) {
            $this->strictError($failedStrict);
        }
    }
    
    private function strictError($failedStrict) {
        trigger_error('Strictly Speaking, the input was too much.' . print_r($failedStrict, true), E_USER_ERROR);
    }
    
    private function notExists($name) {
        trigger_error( 'Property is no declared: ' . $name, E_USER_ERROR );
    }
}
?>