<?php

/**
 * @author    Eric Stern <erics@opencoreemr.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      https://www.open-emr.org
 * @package   OpenEMR
 */

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest\Webhooks;

use Money\{
    Currencies\ISOCurrencies,
    Currency,
    Formatter\DecimalMoneyFormatter,
    Money,
};
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\PaymentProcessing\Rainforest\Metadata;
use OpenEMR\PaymentProcessing\Recorder;
use Psr\Log\LoggerInterface;

/**
 * Webhook event handler that records payments happening and associates it with
 * the AR data for the encounters.
 */
readonly class RecordPayment implements ProcessorInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function getEventTypes(): array
    {
        // For the moment, we're listening to authorized rather than succeeded
        // for two main reasons:
        // 1) We need CLI tooling to kick payments forward in the sandbox
        //    environment, and it doesn't make sense to build that until the
        //    new CLI modules are complete
        // 2) Their docs indicate it's a good notification point, which aligns
        //    with "this should go through".
        //
        // It's not perfect, and this really needs improvements so the entire
        // process becomes idempotent (across different webhook events for the
        // same payment, too!). See downstream commentary on WH infra.
        return [
            'payin.authorized',
        ];
    }

    public function handle(Webhook $webhook): void
    {
        if ($webhook->eventType !== 'payin.authorized') {
            throw new \DomainException(sprintf(
                '%s cannot handle event type "%s"',
                self::class,
                $webhook->eventType,
            ));
        }
        // This transaction should be done in a general recording service, but
        // PaymentProcessing/Recorder isn't sophisticated enough yet.
        //
        // Also, most of the data munging should be done prior to DB
        // interactions, but the use() would be bonkers.
        QueryUtils::inTransaction(function () use ($webhook): void {

            $r = new Recorder();

            /**
             * @var array{
             *   amount: int,
             *   currency_code: non-empty-string,
             *   metadata: array<string, mixed>,
             *   payin_id: string,
             * }
             */
            $data = $webhook->data;
            $reference = $data['payin_id'];
            $existingSessionId = $r->getSessionIdForReference($reference);
            if ($existingSessionId !== null) {
                $this->logger->notice('ar_session exists for rainforest payin_id {id}, skipping further processing', [
                    'id' => $reference,
                ]);
                return;
            }

            $metadata = Metadata::fromParsedJson($data['metadata']);

            $currency = new Currency($data['currency_code']);
            $authorizedAmount = new Money((string) $data['amount'], $currency);
            $dmf = new DecimalMoneyFormatter(new ISOCurrencies());
            if (!self::metadataMatchesAuthorizedAmount($metadata, $authorizedAmount)) {
                $metadataTotal = self::sumMetadataEncounterAmounts($metadata, $currency);
                $this->logger->error(
                    'Rainforest webhook metadata amount does not match authorized amount; refusing to record credits',
                    [
                        'payin_id' => $data['payin_id'],
                        'authorized' => $dmf->format($authorizedAmount),
                        'metadata_total' => $dmf->format($metadataTotal),
                        'currency' => $data['currency_code'],
                        'metadata_patient_id' => $metadata->patientId,
                        'metadata_encounter_count' => count($metadata->encounters),
                    ],
                );
                // Bail without writing any rows. Operator alerting +
                // manual reconciliation happens off the log; the payment
                // is safe on the gateway side and can be reconciled by
                // hand once the discrepancy is understood.
                return;
            }

            $patientId = $metadata->patientId;

            $memo = sprintf('Rainforest transaction id %s', $data['payin_id']);

            $sessionId = $r->createSession([
                'payerId' => '',
                'userId' => '',
                'reference' => $reference,
                'payTotal' => $authorizedAmount,
                'paymentType' => Recorder::PAYMENT_TYPE_PATIENT,
                'description' => '',
                'adjustmentCode' => Recorder::ADJUSTMENT_CODE_PATIENT_PAYMENT,
                'patientId' => $patientId,
                'paymentMethod' => Recorder::PAYMENT_METHOD_CREDIT_CARD,
            ]);

            $activities = array_map(fn($enc): array => [
                'patientId' => $patientId,
                'encounterId' => $enc->id,
                'codeType' => $enc->codeType,
                'code' => $enc->code,
                'modifier' => '',
                'payerType' => '0',
                'postUser' => '', // does this need to be filled?
                'sessionId' => $sessionId,
                'payAmount' => $dmf->format($enc->amount),
                'adjustmentAmount' => '0.00',
                'memo' => $memo,
                'accountCode' => 'PP', // this and paymentType above different for copay?
            ], $metadata->encounters);

            foreach ($activities as $activity) {
                $r->recordActivity($activity);
            }
        });
        // insert into payments? not all existing paths seem to do this
        //
        // update onsite_portal_activity (doesn't seem required, def. not on
        // all paths)
    }

    /**
     * Reconcile the metadata-declared per-encounter amounts against the
     * gateway-authorized top-level amount.
     *
     * Rainforest signs the top-level `amount` as part of the payin — that
     * is the only authoritative amount the gateway actually charged the
     * card. The `metadata` payload was chosen at payin-config creation
     * time; its per-encounter breakdown originally came from a client-
     * supplied JSON body and remains caller-choosable. It is signed only
     * because Rainforest signs the whole envelope.
     *
     * Writing `ar_activity.pay_amount` from the per-encounter metadata
     * value therefore would let a caller credit an arbitrary encounter
     * for an arbitrary amount as long as the amounts happen to sum to
     * the total actually charged. Rejecting webhooks whose metadata sum
     * differs from the authorized amount closes that path.
     *
     * Split out as a static helper so the reconciliation invariant is
     * testable without standing up a database transaction.
     */
    public static function metadataMatchesAuthorizedAmount(
        Metadata $metadata,
        Money $authorizedAmount,
    ): bool {
        $total = self::sumMetadataEncounterAmounts(
            $metadata,
            $authorizedAmount->getCurrency(),
        );
        return $total->equals($authorizedAmount);
    }

    /**
     * Sum the per-encounter amounts declared in metadata, in the given
     * currency. Empty encounter arrays return a zero-money in the same
     * currency. All encounter amounts are expected to be in the same
     * currency as `$currency` — Money::add() will throw if they are not.
     */
    private static function sumMetadataEncounterAmounts(
        Metadata $metadata,
        Currency $currency,
    ): Money {
        $zero = new Money('0', $currency);
        return array_reduce(
            $metadata->encounters,
            static fn(Money $sum, $enc): Money => $sum->add($enc->amount),
            $zero,
        );
    }
}
