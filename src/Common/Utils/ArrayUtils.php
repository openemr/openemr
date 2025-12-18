<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class ArrayUtils
{
    /**
     * Filter out not allowed fields from $data
     *
     * @throws InvalidArgumentException when allowedFields has unknown fields
     */
    public static function filter(array $data, array $allowedFields = []): array
    {
        $unknownFields = array_diff($allowedFields, array_keys($data));
        Assert::isEmpty($unknownFields, sprintf(
            'Unknown allowed fields: %s. Valid ones: %s.',
            implode(', ', $unknownFields),
            implode(', ', array_keys($data)),
        ));

        if ([] === $allowedFields) {
            return $data;
        }

        return array_intersect_key($data, array_flip($allowedFields));
    }
}
