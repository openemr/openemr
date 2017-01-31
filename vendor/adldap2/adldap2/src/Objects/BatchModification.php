<?php

namespace Adldap\Objects;

class BatchModification
{
    /**
     * The original value of the attribute before modification.
     *
     * @var null
     */
    protected $original = null;

    /**
     * The attribute of the modification.
     *
     * @var int|string
     */
    protected $attribute;

    /**
     * The values of the modification.
     *
     * @var array
     */
    protected $values = [];

    /**
     * The modtype integer of the batch modification.
     *
     * @var int
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param null|string $attribute
     * @param null|int    $type
     * @param array       $values
     */
    public function __construct($attribute = null, $type = null, $values = [])
    {
        $this->setAttribute($attribute)
            ->setType($type)
            ->setValues($values);
    }

    /**
     * Sets the original value of the attribute before modification.
     *
     * @param mixed $original
     *
     * @return $this
     */
    public function setOriginal($original = null)
    {
        $this->original = $original;

        return $this;
    }

    /**
     * Returns the original value of the attribute before modification.
     *
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Sets the attribute of the modification.
     *
     * @param string $attribute
     *
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Returns the attribute of the modification.
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Sets the values of the modification.
     *
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values = [])
    {
        $this->values = array_map(function($value){
            // We need to make sure all values given to a batch modification are
            // strings, otherwise we'll receive an LDAP exception when
            // we try to process the modification.
            return (string) $value;
        }, $values);

        return $this;
    }

    /**
     * Returns the values of the modification.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Sets the type of the modification.
     *
     * @param int $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the type of the modification.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Builds the type of modification automatically
     * based on the current and original values.
     *
     * @return $this
     */
    public function build()
    {
        $filtered = array_diff(array_map('trim', $this->values), ['']);

        if (is_null($this->original)) {
            // If the original value is null, we'll assume
            // that the attribute doesn't exist yet.
            if (!empty($filtered)) {
                // If the filtered array is not empty, we'll
                // assume the developer is looking to
                // add attributes to the model.
                $this->setType(LDAP_MODIFY_BATCH_ADD);
            }

            // If the filtered array is empty and there is no original
            // value, then we can ignore this attribute since
            // we can't push null values to AD.
        } else {
            if (empty($filtered)) {
                // If there's an original value and the array is
                // empty then we can assume the developer is
                // looking to completely remove all values
                // of the specified attribute.
                $this->setType(LDAP_MODIFY_BATCH_REMOVE_ALL);
            } else {
                // If the array isn't empty then we can assume the
                // developer is trying to replace all attributes.
                $this->setType(LDAP_MODIFY_BATCH_REPLACE);
            }
        }

        return $this;
    }

    /**
     * Returns the built batch modification array.
     *
     * @return array|null
     */
    public function get()
    {
        $attrib = $this->attribute;
        $modtype = $this->type;
        $values = $this->values;

        switch ($modtype) {
            case LDAP_MODIFY_BATCH_REMOVE_ALL:
                // A values key cannot be provided when
                // a remove all type is selected.
                return compact('attrib', 'modtype');
            case LDAP_MODIFY_BATCH_REMOVE:
                return compact('attrib', 'modtype', 'values');
            case LDAP_MODIFY_BATCH_ADD:
                return compact('attrib', 'modtype', 'values');
            case LDAP_MODIFY_BATCH_REPLACE:
                return compact('attrib', 'modtype', 'values');
            default:
                // If the modtype isn't recognized, we'll return null.
                return;
        }
    }
}
