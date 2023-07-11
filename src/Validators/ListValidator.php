<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

class ListValidator extends BaseValidator
{
    protected function configureValidator()
    {
        parent::configureValidator();
        $this->validator->context(self::DATABASE_INSERT_CONTEXT, function (Validator $context) {
            $context->required('title')->lengthBetween(2, 255);
            $context->required('type')->lengthBetween(2, 255);
            $context->required('pid')->numeric();
            $context->optional('diagnosis')->lengthBetween(2, 255);
            $context->required('begdate')->datetime('Y-m-d');
            $context->optional('enddate')->datetime('Y-m-d');
        });

        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function (Validator $context) {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    function ($rules) {
                        foreach ($rules as $chain) {
                            $chain->required(false);
                        }
                    }
                );

                $context->required("id", "Surgery ID")->callback(function ($value) {
                    return $this->validateId("id", "lists", $value);
                })->integer();
            }
        );
    }
}
