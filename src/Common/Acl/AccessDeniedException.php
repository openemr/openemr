<?php

/**
 * AccessDeniedException represents a system exception that access was denied due to a ACL Violation
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Acl;

use Throwable;

class AccessDeniedException extends \Exception
{
    /**
     * @param string $requiredSection
     * @param string $subCategory
     * @param string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(
        private readonly string $requiredSection,
        private $subCategory = '',
        $message = "",
        $code = 0,
        ?Throwable $previous = null
    ) {
        if (empty($message)) {
            $message = xlt('ACL check failed');
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getRequiredSection(): string
    {
        return $this->requiredSection;
    }

    /**
     * @return string
     */
    public function getSubCategory(): string
    {
        return $this->subCategory;
    }
}
