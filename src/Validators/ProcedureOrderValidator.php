<?php

/**
 * ProcedureOrderValidator - Validates procedure order data for insert and update operations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use Particle\Validator\Chain;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

class ProcedureOrderValidator extends BaseValidator
{
    /**
     * Valid values for order_status (matches ord_status list_options)
     */
    private const ORDER_STATUSES = ['pending', 'routed', 'complete', 'canceled'];

    /**
     * Valid values for order_priority (matches ord_priority list_options)
     */
    private const ORDER_PRIORITIES = ['high', 'normal', 'routine', 'urgent', 'asap', 'stat'];

    /**
     * Valid values for order_intent (FHIR request-intent)
     */
    private const ORDER_INTENTS = ['order', 'plan', 'directive', 'proposal'];

    /**
     * Valid values for procedure_order_type (FHIR ServiceRequest category)
     */
    private const ORDER_TYPES = ['laboratory_test', 'imaging', 'clinical_test', 'procedure'];

    /**
     * Validates that a procedure order UUID exists in the database.
     *
     * @param string $uuid The UUID to check
     * @return bool True if the UUID exists, false otherwise
     */
    public function isExistingUuid($uuid): bool
    {
        try {
            $uuidLookup = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException) {
            return false;
        }

        $result = QueryUtils::querySingleRow(
            'SELECT uuid AS uuid FROM procedure_order WHERE uuid = ?',
            [$uuidLookup]
        );

        $existingUuid = $result['uuid'] ?? null;
        return $existingUuid != null;
    }

    /**
     * Configures validations for the Procedure Order DB Insert and Update use-case.
     */
    protected function configureValidator(): void
    {
        parent::configureValidator();

        $validator = $this->getInnerValidator();

        // insert validations
        $validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context): void {
                // Required fields
                $context->required('patient_id', 'Patient ID')->numeric();
                $context->required('provider_id', 'Provider ID')->numeric();
                $context->required('encounter_id', 'Encounter ID')->numeric();
                $context->required('lab_id', 'Lab ID')->numeric();

                // Optional enum fields
                $context->optional('order_status', 'Order Status')
                    ->inArray(self::ORDER_STATUSES);
                $context->optional('order_priority', 'Order Priority')
                    ->inArray(self::ORDER_PRIORITIES);
                $context->optional('order_intent', 'Order Intent')
                    ->inArray(self::ORDER_INTENTS);
                $context->optional('procedure_order_type', 'Order Type')
                    ->inArray(self::ORDER_TYPES);

                // Optional format fields
                $context->optional('date_ordered', 'Date Ordered')->datetime('Y-m-d');
                $context->optional('date_collected', 'Date Collected')->datetime('Y-m-d');
                $context->optional('order_diagnosis', 'Order Diagnosis')->lengthBetween(1, 255);
                $context->optional('clinical_hx', 'Clinical History')->lengthBetween(1, 255);
                $context->optional('patient_instructions', 'Patient Instructions');
                $context->optional('billing_type', 'Billing Type')->lengthBetween(1, 4);
                $context->optional('specimen_fasting', 'Specimen Fasting')->lengthBetween(1, 31);
            }
        );

        // update validations copied from insert
        $validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function (Validator $context): void {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    function (array $rules): void {
                        foreach ($rules as $chain) {
                            \assert($chain instanceof Chain);
                            $chain->required(false);
                        }
                    }
                );
                // additional uuid validations
                $context->required("uuid", "uuid")->callback(function (string $value): true {
                    if (!$this->isExistingUuid($value)) {
                        throw new InvalidValueException(
                            "UUID " . $value . " does not exist",
                            $value
                        );
                    }
                    return true;
                })->string();
            }
        );
    }
}
