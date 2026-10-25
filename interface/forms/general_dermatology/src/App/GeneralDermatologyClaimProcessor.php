<?php

declare(strict_types=1);

namespace GeneralDermatology\App;

use GeneralDermatology\Exception\GeneralDermatologyException;
use GeneralDermatology\Support\FormContext;
use OpenEMR\Billing\BillingProcessor\PriceLevelUtility;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Database\QueryUtils;

class GeneralDermatologyClaimProcessor
{
    /** @var array{claim?: list<array<string, mixed>>, treatment?: list<array<string, mixed>>} */
    private array $claimData = [];

    private BillingUtilities $billing;
    private PriceLevelUtility $price;
    private FormContext $context;

    public function __construct(?FormContext $context = null)
    {
        $this->billing = new BillingUtilities();
        $this->price = new PriceLevelUtility();
        $this->context = $context ?? FormContext::fromRuntime();
    }

    /**
     * @param array{claim?: mixed, treatment?: mixed} $data
     * @return array{claim?: list<array<string, mixed>>, treatment?: list<array<string, mixed>>}
     */
    public function setClaimData(array $data): array
    {
        $claims = $data['claim'] ?? [];
        $treatments = $data['treatment'] ?? [];

        $normalizedClaims = [];
        if (is_array($claims)) {
            foreach ($claims as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $normalizedClaims[] = self::normalizeClaimRow($row);
            }
        }

        $normalizedTreatments = [];
        if (is_array($treatments)) {
            foreach ($treatments as $row) {
                if (is_array($row)) {
                    $normalizedTreatments[] = $row;
                }
            }
        }

        $this->claimData = [
            'claim' => $normalizedClaims,
            'treatment' => $normalizedTreatments,
        ];

        return $this->claimData;
    }

    /**
     * Accept both correct keys (code) and legacy broken keys ('code').
     *
     * @param array<string, mixed> $row
     * @return array{code: string, description: string, modifier: string, justify: list<string>}
     */
    public static function normalizeClaimRow(array $row): array
    {
        $justifyRaw = self::claimField($row, 'justify');
        $justify = [];
        if (is_array($justifyRaw)) {
            foreach ($justifyRaw as $item) {
                $value = trim((string) $item);
                if ($value !== '' && $value !== '0') {
                    $justify[] = $value;
                }
            }
        } elseif (is_string($justifyRaw) && $justifyRaw !== '') {
            $justify[] = $justifyRaw;
        }

        return [
            'code' => trim((string) self::claimField($row, 'code')),
            'description' => trim((string) self::claimField($row, 'description')),
            'modifier' => trim((string) self::claimField($row, 'modifier')),
            'justify' => $justify,
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function claimField(array $row, string $key): mixed
    {
        if (array_key_exists($key, $row)) {
            return $row[$key];
        }

        $quoted = "'" . $key . "'";
        return $row[$quoted] ?? null;
    }

    public function hasBeenBilled(): bool
    {
        $pid = $this->context->pid();
        $encounter = $this->context->encounter();
        if ($pid <= 0 || $encounter === 0 || $encounter === '0') {
            return false;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT 1 AS billed FROM billing WHERE encounter = ? AND pid = ? AND billed = 1 LIMIT 1',
            [$encounter, $pid]
        ) ?: [];

        return !empty($row);
    }

    /**
     * @return array{claim?: list<array<string, mixed>>, treatment?: list<array<string, mixed>>}
     */
    public function getClaimData(): array
    {
        return $this->claimData;
    }

    /**
     * Replace unbilled claim lines for this encounter, then insert current set.
     */
    public function processClaim(): void
    {
        $pid = $this->context->pid();
        $encounter = $this->context->encounter();
        if ($pid <= 0 || $encounter === 0 || $encounter === '0') {
            throw new GeneralDermatologyException('Cannot process claims without an active patient encounter.');
        }

        if ($this->hasBeenBilled()) {
            throw new GeneralDermatologyException('Cannot modify billing for an encounter that has already been billed.');
        }

        $claims = $this->claimData['claim'] ?? [];
        $treatments = $this->claimData['treatment'] ?? [];
        if ($claims === [] && $treatments === []) {
            return;
        }

        $provider = $this->getEncounterProvider($encounter);
        $billing = $this->billing;

        QueryUtils::inTransaction(function () use ($pid, $encounter, $claims, $treatments, $provider, $billing): void {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM billing WHERE encounter = ? AND pid = ? AND billed = 0',
                [$encounter, $pid]
            );

            foreach ($claims as $values) {
                $code = $values['code'];
                if ($code === '') {
                    continue;
                }
                $codeText = $values['description'];
                $modifier = $values['modifier'];
                $units = 1;
                $fee = '0.00';
                if (is_numeric($code)) {
                    $price = $this->getPrice($code);
                    if ($price !== null && $price !== '') {
                        $fee = (string) $price;
                    }
                }
                $justify = self::createJustification($values['justify']);
                $billing::addBilling(
                    $encounter,
                    'CPT4',
                    $code,
                    $codeText,
                    $pid,
                    null,
                    $provider,
                    $modifier,
                    $units,
                    $fee,
                    '',
                    $justify
                );
            }

            foreach ($treatments as $diagnosis) {
                $code = trim((string) ($diagnosis['dx_s'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $codeText = $diagnosis['dx_desc'] ?? null;
                if (is_array($codeText)) {
                    $codeText = implode(', ', array_map('strval', $codeText));
                }
                $billing::addBilling(
                    $encounter,
                    'ICD10',
                    $code,
                    (string) ($codeText ?? ''),
                    $pid,
                    null,
                    null
                );
            }
        });
    }

    private function getEncounterProvider(mixed $encounter): ?int
    {
        $res = QueryUtils::querySingleRow(
            'SELECT provider_id FROM form_encounter WHERE encounter = ?',
            [$encounter]
        ) ?: [];

        return isset($res['provider_id']) ? (int) $res['provider_id'] : null;
    }

    private function getPrice(mixed $code): mixed
    {
        return $this->price->getPriceValue($code);
    }

    /**
     * @deprecated Use processClaim() which already clears unbilled rows transactionally.
     */
    public function removeClaimDataFromBillingTable(): void
    {
        $pid = $this->context->pid();
        $encounter = $this->context->encounter();
        if ($pid <= 0 || $encounter === 0 || $encounter === '0') {
            return;
        }

        QueryUtils::sqlStatementThrowException(
            'DELETE FROM billing WHERE encounter = ? AND pid = ? AND billed = 0',
            [$encounter, $pid]
        );
    }

    /**
     * @param list<string>|mixed $justify
     */
    public static function createJustification(mixed $justify): string
    {
        if (!is_array($justify) || $justify === []) {
            return '';
        }

        $parts = [];
        foreach ($justify as $value) {
            $code = trim((string) $value);
            if ($code === '') {
                continue;
            }
            $parts[] = 'ICD10|' . $code;
        }

        return implode(',', $parts);
    }
}
