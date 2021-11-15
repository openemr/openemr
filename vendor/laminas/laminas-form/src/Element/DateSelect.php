<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use ArrayAccess;
use DateTime as PhpDateTime;
use Exception;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\FormInterface;
use Laminas\Validator\Date as DateValidator;
use Laminas\Validator\ValidatorInterface;
use Traversable;

use function array_merge;
use function is_string;
use function sprintf;

class DateSelect extends MonthSelect
{
    /**
     * Select form element that contains values for day
     *
     * @var Select
     */
    protected $dayElement;

    /**
     * Constructor. Add the day select element
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = null, $options = [])
    {
        $this->dayElement = new Select('day');

        parent::__construct($name, $options);
    }

    /**
     * Accepted options for DateSelect (plus the ones from MonthSelect) :
     * - day_attributes: HTML attributes to be rendered with the day element
     *
     * @param array|Traversable $options
     * @return $this
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['day_attributes'])) {
            $this->setDayAttributes($this->options['day_attributes']);
        }

        return $this;
    }

    /**
     * @return Select
     */
    public function getDayElement()
    {
        return $this->dayElement;
    }

    /**
     * Get both the year and month elements
     *
     * @return array
     */
    public function getElements()
    {
        return array_merge([$this->dayElement], parent::getElements());
    }

    /**
     * Set the day attributes
     *
     * @param  array $dayAttributes
     * @return $this
     */
    public function setDayAttributes(array $dayAttributes)
    {
        $this->dayElement->setAttributes($dayAttributes);
        return $this;
    }

    /**
     * Get the day attributes
     *
     * @return array
     */
    public function getDayAttributes()
    {
        return $this->dayElement->getAttributes();
    }

    /**
     * @param  string|array|ArrayAccess|PhpDateTime $value
     * @return $this Provides a fluent interface
     * @throws InvalidArgumentException
     */
    public function setValue($value)
    {
        if (is_string($value)) {
            try {
                $value = new PhpDateTime($value);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Value should be a parsable string or an instance of DateTime');
            }
        }

        if ($value instanceof PhpDateTime) {
            $value = [
                'year'  => $value->format('Y'),
                'month' => $value->format('m'),
                'day'   => $value->format('d'),
            ];
        }

        $this->yearElement->setValue($value['year']);
        $this->monthElement->setValue($value['month']);
        $this->dayElement->setValue($value['day']);

        return $this;
    }

    /**
     * @return String
     */
    public function getValue()
    {
        return sprintf(
            '%s-%s-%s',
            $this->getYearElement()->getValue(),
            $this->getMonthElement()->getValue(),
            $this->getDayElement()->getValue()
        );
    }

    /**
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param  FormInterface $form
     * @return mixed
     */
    public function prepareElement(FormInterface $form)
    {
        parent::prepareElement($form);

        $name = $this->getName();
        $this->dayElement->setName($name . '[day]');
    }

    /**
     * Get validator
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new DateValidator(['format' => 'Y-m-d']);
        }

        return $this->validator;
    }

    /**
     * Should return an array specification compatible with
     * {@link Laminas\InputFilter\Factory::createInput()}.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'filters' => [
                ['name' => 'DateSelect'],
            ],
            'validators' => [
                $this->getValidator(),
            ],
        ];
    }

    /**
     * Clone the element (this is needed by Collection element, as it needs different copies of the elements)
     */
    public function __clone()
    {
        $this->dayElement   = clone $this->dayElement;
        $this->monthElement = clone $this->monthElement;
        $this->yearElement  = clone $this->yearElement;
    }
}
