<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

final class GpsPoint extends AbstractValidator
{

    const OUT_OF_BOUNDS = 'gpsPointOutOfBounds';
    const CONVERT_ERROR = 'gpsPointConvertError';
    const INCOMPLETE_COORDINATE = 'gpsPointIncompleteCoordinate';

    /**
     * @var array
     */
    protected $messageTemplates = [
        'gpsPointOutOfBounds' => '%value% is out of Bounds.',
        'gpsPointConvertError' => '%value% can not converted into a Decimal Degree Value.',
        'gpsPointIncompleteCoordinate' => '%value% did not provided a complete Coordinate',
    ];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        if (strpos($value, ',') === false) {
            $this->error(GpsPoint::INCOMPLETE_COORDINATE, $value);
            return false;
        }

        list($lat, $long) = explode(',', $value);

        if ($this->isValidCoordinate($lat, 90.0000) && $this->isValidCoordinate($long, 180.000)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $value
     * @param $maxBoundary
     * @return bool
     */
    private function isValidCoordinate($value, $maxBoundary)
    {
        $this->value = $value;

        $value = $this->removeWhiteSpace($value);
        if ($this->isDMSValue($value)) {
            $value = $this->convertValue($value);
        } else {
            $value = $this->removeDegreeSign($value);
        }

        if ($value === false || $value === null) {
            $this->error(self::CONVERT_ERROR);
            return false;
        }

        $doubleLatitude = (double)$value;

        if ($doubleLatitude <= $maxBoundary && $doubleLatitude >= $maxBoundary * -1) {
            return true;
        }

        $this->error(self::OUT_OF_BOUNDS);
        return false;
    }

    /**
     * Determines if the give value is a Degrees Minutes Second Definition
     *
     * @param $value
     * @return bool
     */
    private function isDMSValue($value)
    {
        return preg_match('/([°\'"]+[NESW])/', $value) > 0;
    }


    /**
     * @param string $value
     * @return bool|string
     */
    private function convertValue($value)
    {
        $matches = [];
        $result = preg_match_all('/(\d{1,3})°(\d{1,2})\'(\d{1,2}[\.\d]{0,6})"[NESW]/i', $value, $matches);

        if ($result === false || $result === 0) {
            return false;
        }

        return $matches[1][0] + $matches[2][0] / 60 + ((double)$matches[3][0]) / 3600;
    }

    /**
     * @param string $value
     * @return string
     */
    private function removeWhiteSpace($value)
    {
        return preg_replace('/\s/', '', $value);
    }

    /**
     * @param string $value
     * @return string
     */
    private function removeDegreeSign($value)
    {
        return str_replace('°', '', $value);
    }
}
