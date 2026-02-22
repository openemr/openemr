<?php

/**
 * Handles the exception when validations fail for a telehealth data form
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Exception;

use Throwable;

class TelehealthValidationException extends \InvalidArgumentException
{
    /**
     * @param mixed[] $validationErrors
     */
    public function __construct(private $errors, $message = "", $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getValidationErrors()
    {
        return $this->errors;
    }
}
