<?php

/**
 * Handles validation of patients when inserted as part of a telehealth invitation.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Validators;

use OpenEMR\Validators\PatientValidator;
use Particle\Validator\Validator;

class TelehealthPatientValidator extends PatientValidator
{
    public const TELEHEALTH_INSERT_CONTEXT = "telehealth-insert";

    public function __construct()
    {
        parent::__construct();
    }

    protected function configureValidator()
    {
        parent::configureValidator();
        array_push($this->supportedContexts, self::TELEHEALTH_INSERT_CONTEXT);


        // the only real change from the insert validation is that we make the telehealth email attribute required
        // instead of optional because we cannot send a telehealth invitation without a valid email address.
        $this->validator->context(
            self::TELEHEALTH_INSERT_CONTEXT,
            function (Validator $context) {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    function ($rules) {
                        foreach ($rules as $key => $chain) {
                            // email is required for the telehealth insert
                            if ($key == "email") {
                                $chain->required(true);
                            }
                        }
                    }
                );
            }
        );
    }
}
