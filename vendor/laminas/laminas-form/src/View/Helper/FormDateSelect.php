<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper;

use DateTime;
use IntlDateFormatter;
use Laminas\Form\Element\DateSelect as DateSelectElement;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Laminas\Form\View\Helper\FormMonthSelect as FormMonthSelectHelper;

use function is_numeric;
use function sprintf;

class FormDateSelect extends FormMonthSelectHelper
{
    /**
     * Render a date element that is composed of three selects
     *
     * @param  ElementInterface $element
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (! $element instanceof DateSelectElement) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type Laminas\Form\Element\DateSelect',
                __METHOD__
            ));
        }

        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $selectHelper = $this->getSelectElementHelper();
        $pattern      = $this->parsePattern($element->shouldRenderDelimiters());

        $daysOptions   = $this->getDaysOptions($pattern['day']);
        $monthsOptions = $this->getMonthsOptions($pattern['month']);
        $yearOptions   = $this->getYearsOptions($element->getMinYear(), $element->getMaxYear());

        $dayElement   = $element->getDayElement()->setValueOptions($daysOptions);
        $monthElement = $element->getMonthElement()->setValueOptions($monthsOptions);
        $yearElement  = $element->getYearElement()->setValueOptions($yearOptions);

        if ($element->shouldCreateEmptyOption()) {
            $dayElement->setEmptyOption('');
            $yearElement->setEmptyOption('');
            $monthElement->setEmptyOption('');
        }

        $data = [];
        $data[$pattern['day']]   = $selectHelper->render($dayElement);
        $data[$pattern['month']] = $selectHelper->render($monthElement);
        $data[$pattern['year']]  = $selectHelper->render($yearElement);

        $markup = '';
        foreach ($pattern as $key => $value) {
            // Delimiter
            if (is_numeric($key)) {
                $markup .= $value;
            } else {
                $markup .= $data[$value];
            }
        }

        return $markup;
    }

    /**
     * Create a key => value options for days
     *
     * @param  string $pattern Pattern to use for days
     * @return array
     */
    protected function getDaysOptions($pattern)
    {
        $keyFormatter   = new IntlDateFormatter($this->getLocale(), null, null, null, null, 'dd');
        $valueFormatter = new IntlDateFormatter($this->getLocale(), null, null, null, null, $pattern);
        $date           = new DateTime('1970-01-01');

        $result = [];
        for ($day = 1; $day <= 31; $day++) {
            $key   = $keyFormatter->format($date->getTimestamp());
            $value = $valueFormatter->format($date->getTimestamp());
            $result[$key] = $value;

            $date->modify('+1 day');
        }

        return $result;
    }
}
