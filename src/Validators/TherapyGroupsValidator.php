<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

class TherapyGroupsValidator extends BaseValidator
{
    protected function configureValidator()
    {
        parent::configureValidator();

        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context) {
                $context->required('group_name')->lengthBetween(2, 255);
                $context->required('group_type');
                $context->required('group_participation');
                $context->required('group_status');
            }
        );
    }
}
