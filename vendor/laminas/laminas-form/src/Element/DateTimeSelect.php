<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use DateTime as PhpDateTime;
use Exception;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\FormInterface;
use Laminas\Validator\Date as DateValidator;
use Laminas\Validator\ValidatorInterface;
use Traversable;

use function is_string;
use function sprintf;

class DateTimeSelect extends DateSelect
{
    /**
     * Select form element that contains values for hour
     *
     * @var Select
     */
    protected $hourElement;

    /**
     * Select form element that contains values for minute
     *
     * @var Select
     */
    protected $minuteElement;

    /**
     * Select form element that contains values for second
     *
     * @var Select
     */
    protected $secondElement;

    /**
     * Is the seconds select shown when the element is rendered?
     *
     * @var bool
     */
    protected $shouldShowSeconds = false;

    /**
     * Constructor. Add the hour, minute and second select elements
     *
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     */
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);

        $this->hourElement   = new Select('hour');
        $this->minuteElement = new Select('minute');
        $this->secondElement = new Select('second');
    }

    /**
     * Set options for DateTimeSelect element.
     *
     * Accepted options for DateTimeSelect (plus the ones from DateSelect):
     *
     * - hour_attributes: HTML attributes to be rendered with the hour element
     * - minute_attributes: HTML attributes to be rendered with the minute element
     * - second_attributes: HTML attributes to be rendered with the second element
     * - should_show_seconds: if set to true, the seconds select is shown
     *
     * @param array|Traversable $options
     * @return $this
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['hour_attributes'])) {
            $this->setHourAttributes($this->options['hour_attributes']);
        }

        if (isset($this->options['minute_attributes'])) {
            $this->setMinuteAttributes($this->options['minute_attributes']);
        }

        if (isset($this->options['second_attributes'])) {
            $this->setSecondAttributes($this->options['second_attributes']);
        }

        if (isset($this->options['should_show_seconds'])) {
            $this->setShouldShowSeconds($this->options['should_show_seconds']);
        }

        return $this;
    }

    /**
     * @return Select
     */
    public function getHourElement()
    {
        return $this->hourElement;
    }

    /**
     * @return Select
     */
    public function getMinuteElement()
    {
        return $this->minuteElement;
    }

    /**
     * @return Select
     */
    public function getSecondElement()
    {
        return $this->secondElement;
    }

    /**
     * Set the hour attributes
     *
     * @param  array $hourAttributes
     * @return $this
     */
    public function setHourAttributes(array $hourAttributes)
    {
        $this->hourElement->setAttributes($hourAttributes);
        return $this;
    }

    /**
     * Get the hour attributes
     *
     * @return array
     */
    public function getHourAttributes()
    {
        return $this->hourElement->getAttributes();
    }

    /**
     * Set the minute attributes
     *
     * @param  array $minuteAttributes
     * @return $this
     */
    public function setMinuteAttributes(array $minuteAttributes)
    {
        $this->minuteElement->setAttributes($minuteAttributes);
        return $this;
    }

    /**
     * Get the minute attributes
     *
     * @return array
     */
    public function getMinuteAttributes()
    {
        return $this->minuteElement->getAttributes();
    }

    /**
     * Set the second attributes
     *
     * @param  array $secondAttributes
     * @return $this
     */
    public function setSecondAttributes(array $secondAttributes)
    {
        $this->secondElement->setAttributes($secondAttributes);
        return $this;
    }

    /**
     * Get the second attributes
     *
     * @return array
     */
    public function getSecondAttributes()
    {
        return $this->secondElement->getAttributes();
    }

    /**
     * If set to true, this indicate that the second select is shown. If set to true, the seconds will be
     * assumed to always be 00
     *
     * @param  bool $shouldShowSeconds
     * @return $this
     */
    public function setShouldShowSeconds($shouldShowSeconds)
    {
        $this->shouldShowSeconds = (bool) $shouldShowSeconds;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldShowSeconds()
    {
        return $this->shouldShowSeconds;
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setValue($value)
    {
        if (is_string($value)) {
            try {
                $value = new PhpDateTime($value);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Value should be a parsable string or an instance of \DateTime');
            }
        }

        if (null === $value) {
            $value = new PhpDateTime();
        }

        if ($value instanceof PhpDateTime) {
            $value = [
                'year'   => $value->format('Y'),
                'month'  => $value->format('m'),
                'day'    => $value->format('d'),
                'hour'   => $value->format('H'),
                'minute' => $value->format('i'),
                'second' => $value->format('s'),
            ];
        }

        if (! isset($value['second'])) {
            $value['second'] = '00';
        }

        $this->yearElement->setValue($value['year']);
        $this->monthElement->setValue($value['month']);
        $this->dayElement->setValue($value['day']);
        $this->hourElement->setValue($value['hour']);
        $this->minuteElement->setValue($value['minute']);
        $this->secondElement->setValue($value['second']);

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return sprintf(
            '%s-%s-%s %s:%s:%s',
            $this->getYearElement()->getValue(),
            $this->getMonthElement()->getValue(),
            $this->getDayElement()->getValue(),
            $this->getHourElement()->getValue(),
            $this->getMinuteElement()->getValue(),
            $this->getSecondElement()->getValue()
        );
    }

    /**
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param  FormInterface $form
     * @return void
     */
    public function prepareElement(FormInterface $form)
    {
        parent::prepareElement($form);

        $name = $this->getName();
        $this->hourElement->setName($name . '[hour]');
        $this->minuteElement->setName($name . '[minute]');
        $this->secondElement->setName($name . '[second]');
    }

    /**
     * Get validator
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new DateValidator(['format' => 'Y-m-d H:i:s']);
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
                ['name' => 'DateTimeSelect'],
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
        $this->dayElement    = clone $this->dayElement;
        $this->monthElement  = clone $this->monthElement;
        $this->yearElement   = clone $this->yearElement;
        $this->hourElement   = clone $this->hourElement;
        $this->minuteElement = clone $this->minuteElement;
        $this->secondElement = clone $this->secondElement;
    }
}
