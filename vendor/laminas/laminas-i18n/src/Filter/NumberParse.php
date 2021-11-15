<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Filter;

use Laminas\I18n\Exception;
use Laminas\Stdlib\ErrorHandler;
use NumberFormatter;
use Traversable;

class NumberParse extends AbstractLocale
{
    protected $options = [
        'locale' => null,
        'style'  => NumberFormatter::DEFAULT_STYLE,
        'type'   => NumberFormatter::TYPE_DOUBLE
    ];

    /**
     * @var NumberFormatter
     */
    protected $formatter;

    /**
     * @param array|Traversable|string|null $localeOrOptions
     * @param int  $style
     * @param int  $type
     */
    public function __construct(
        $localeOrOptions = null,
        $style = NumberFormatter::DEFAULT_STYLE,
        $type = NumberFormatter::TYPE_DOUBLE
    ) {
        parent::__construct();
        if ($localeOrOptions !== null) {
            if ($localeOrOptions instanceof Traversable) {
                $localeOrOptions = iterator_to_array($localeOrOptions);
            }

            if (! is_array($localeOrOptions)) {
                $this->setLocale($localeOrOptions);
                $this->setStyle($style);
                $this->setType($type);
            } else {
                $this->setOptions($localeOrOptions);
            }
        }
    }

    /**
     * @param  string|null $locale
     * @return $this
     */
    public function setLocale($locale = null)
    {
        $this->options['locale'] = $locale;
        $this->formatter = null;
        return $this;
    }

    /**
     * @param  int $style
     * @return $this
     */
    public function setStyle($style)
    {
        $this->options['style'] = (int) $style;
        $this->formatter = null;
        return $this;
    }

    /**
     * @return int
     */
    public function getStyle()
    {
        return $this->options['style'];
    }

    /**
     * @param  int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->options['type'] = (int) $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @param  NumberFormatter $formatter
     * @return $this
     */
    public function setFormatter(NumberFormatter $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * @return NumberFormatter
     * @throws Exception\RuntimeException
     */
    public function getFormatter()
    {
        if ($this->formatter === null) {
            $formatter = NumberFormatter::create($this->getLocale(), $this->getStyle());
            if (! $formatter) {
                throw new Exception\RuntimeException(
                    'Can not create NumberFormatter instance; ' . intl_get_error_message()
                );
            }

            $this->formatter = $formatter;
        }

        return $this->formatter;
    }

    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * @see    \Laminas\Filter\FilterInterface::filter()
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (! is_int($value)
            && ! is_float($value)
        ) {
            ErrorHandler::start();

            $result = $this->getFormatter()->parse(
                $value,
                $this->getType()
            );

            ErrorHandler::stop();

            if (false !== $result) {
                return $result;
            }
        }

        return $value;
    }
}
