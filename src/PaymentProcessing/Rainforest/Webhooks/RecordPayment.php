<?php

/**
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright (c) 2026 OpenCoreEMR, Inc
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
        assert($webhook->eventType === 'payin.authorized');
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
            $patientId = $metadata->patientId;

            $memo = sprintf('Rainforest transaction id %s', $data['payin_id']);
            $dmf = new DecimalMoneyFormatter(new ISOCurrencies());

            $sessionId = $r->createSession([
                'payerId' => '',
                'userId' => '',
                'reference' => $reference,
                'payTotal' => new Money((string)$data['amount'], new Currency($data['currency_code'])),
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
}
