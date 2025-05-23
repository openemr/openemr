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
     * @var array
     */
    private $errors;

    public function __construct($validationErrors, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->errors = $validationErrors;
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
