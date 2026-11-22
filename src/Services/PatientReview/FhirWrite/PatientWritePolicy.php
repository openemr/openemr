<?php

/**
 * Whether a site accepts a patient app's FHIR write. This is the one place the client rule lives.
 *
 * Every write this policy allows is held for staff review, in every mode, because unverified
 * patient data must not reach the chart or other readers before staff see it. So the mode doesn't
 * decide whether data is trusted, only which apps may submit it. Public clients are a separate
 * choice because nothing proves which app is calling: a lost or spoofed app could submit false
 * data under a patient's login, and staff review is where that gets caught.
 *
 * The default is Off, so a site has to choose a client mode itself. Check this on each request as
 * well as when a token is issued, so that turning the mode down stops tokens already issued.
 *
 * @see https://github.com/openemr/openemr/issues/14999
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use Symfony\Component\HttpFoundation\ParameterBag;

final readonly class PatientWritePolicy
{
    /**
     * @param list<PatientWritableType> $enabledTypes
     */
    public function __construct(
        private PatientWriteMode $mode,
        private array $enabledTypes,
    ) {
    }

    /**
     * An unrecognized mode is treated as Off, so a bad stored value can't open writes.
     */
    public static function fromGlobals(ParameterBag $globals): self
    {
        $mode = PatientWriteMode::tryFrom($globals->getString(GlobalConnectorsEnum::FHIR_PATIENT_WRITE_MODE->value))
            ?? PatientWriteMode::Off;
        $enabledTypes = array_values(array_filter(
            PatientWritableType::cases(),
            static fn (PatientWritableType $type): bool => $globals->getBoolean($type->setting()->value),
        ));

        return new self($mode, $enabledTypes);
    }

    /**
     * Whether this client may submit this type. Both the mode and the type's setting must allow it.
     */
    public function allowsWrite(PatientWriteClientType $client, PatientWritableType $type): bool
    {
        return $this->mode->allowsClient($client) && in_array($type, $this->enabledTypes, true);
    }

    /**
     * The types to advertise in scopes_supported, which lists scopes the server must support,
     * and to let through the API's permission gates. Nothing is advertised while the mode is
     * Off, whichever types are switched on, and never a type the API doesn't serve yet.
     *
     * @return list<PatientWritableType>
     */
    public function advertisedTypes(): array
    {
        if ($this->mode === PatientWriteMode::Off) {
            return [];
        }
        return array_values(array_filter(
            $this->enabledTypes,
            static fn (PatientWritableType $type): bool => $type->servedByApi(),
        ));
    }

    /**
     * Whether the API's permission gates should let a patient app's write to this FHIR resource
     * type through to the hold, where the client is checked. Only advertised types pass.
     */
    public function opensResource(string $fhirResourceType): bool
    {
        foreach ($this->advertisedTypes() as $type) {
            if ($type->fhirResourceType()->value === $fhirResourceType) {
                return true;
            }
        }
        return false;
    }

    /**
     * Whether a token may be issued with a patient write scope for this resource type to this
     * kind of client. Checked again on every request, so a token issued before the mode was
     * turned down still can't write.
     */
    public function grantsWriteScope(PatientWriteClientType $client, string $fhirResourceType): bool
    {
        foreach ($this->advertisedTypes() as $type) {
            if ($type->fhirResourceType()->value === $fhirResourceType && $this->allowsWrite($client, $type)) {
                return true;
            }
        }
        return false;
    }
}
