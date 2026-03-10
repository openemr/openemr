<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

use Psr\Clock\ClockInterface;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;

readonly class AtnaSink
{
    /**
     * Event action codes indicate whether the event is read/write.
     * C = create, R = read, U = update, D = delete, E = execute
     */
    private const EVENT_ACTION_CODE_EXECUTE = 'E';
    private const EVENT_ACTION_CODE_CREATE = 'C';
    private const EVENT_ACTION_CODE_INSERT = 'C';
    private const EVENT_ACTION_CODE_SELECT = 'R';
    private const EVENT_ACTION_CODE_UPDATE = 'U';
    private const EVENT_ACTION_CODE_DELETE = 'D';

    private const RFC3881_MSG_PRIMARY_TEMPLATE = <<<MSG
<13>%s %s
<?xml version="1.0" encoding="ASCII"?>
 <AuditMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="healthcare-security-audit.xsd">
  <EventIdentification EventActionCode="%s" EventDateTime="%s" EventOutcomeIndicator="%s">
   <EventID code="eventIDcode" displayName="%s" codeSystemName="DCM" />
  </EventIdentification>
  <ActiveParticipant UserID="%s" UserIsRequestor="true" NetworkAccessPointID="%s" NetworkAccessPointTypeCode="2" >
   <RoleIDCode code="110153" displayName="Source" codeSystemName="DCM" />
  </ActiveParticipant>
  <ActiveParticipant UserID="%s" UserIsRequestor="false" NetworkAccessPointID="%s" NetworkAccessPointTypeCode="2" >
   <RoleIDCode code="110152" displayName="Destination" codeSystemName="DCM" />
  </ActiveParticipant>
  <AuditSourceIdentification AuditSourceID="%s" />
  <ParticipantObjectIdentification ParticipantObjectID="%s" ParticipantObjectTypeCode="1" ParticipantObjectTypeCodeRole="6" >
   <ParticipantObjectIDTypeCode code="11" displayName="User Identifier" codeSystemName="RFC-3881" />
  </ParticipantObjectIdentification>
  %s
 </AuditMessage>
MSG;

    private const RFC3881_MSG_PATIENT_TEMPLATE = <<<MSG
<ParticipantObjectIdentification ParticipantObjectID="%s" ParticipantObjectTypeCode="1" ParticipantObjectTypeCodeRole="1">
 <ParticipantObjectIDTypeCode code="2" displayName="Patient Number" codeSystemName="RFC-3881" />
</ParticipantObjectIdentification>
MSG;

    public static function fromGlobals(OEGlobalsBag $bag): AtnaSink
    {
        $writer = new TcpWriter(
            host: $bag->getString('atna_audit_host'),
            port: $bag->getInt('atna_audit_port'),
            localCert: $bag->getString('atna_audit_localcert'),
            caCert: $bag->getString('atna_audit_cacert'),
        );
        return new AtnaSink(
            clock: ServiceContainer::getClock(),
            writer: $writer,
            enabled: $bag->getBoolean('enable_atna_audit'),
        );
    }

    public function __construct(
        private ClockInterface $clock,
        private WriterInterface $writer,
        private bool $enabled,
    ) {
    }

    // Future: handle a better-typed DTO
    public function record(string $user, string $group, string $event, int $patientId, int $outcome, string $comments): void
    {
        if (!$this->enabled) {
            return;
        }

        $message = $this->createRfc3881Msg($user, $group, $event, $patientId, $outcome, $comments);
        $this->writer->writeMessage($message);
    }

    /**
     * Create an XML audit record corresponding to RFC 3881.
     * The parameters passed are the column values (from table 'log')
     * for a single audit record.
     */
    protected function createRfc3881Msg(string $user, string $group, string $event, int $patient_id, int $outcome, string $comments): string
    {
        $eventActionCode = $this->determineRFC3881EventActionCode($event);
        $eventIdDisplayName = $this->determineRFC3881EventIdDisplayName($event);

        $eventDateTime = $this->clock->now()->format(DATE_ATOM);

        /* For EventOutcomeIndicator, 0 = success and 4 = minor error */
        $eventOutcome = ($outcome === 1) ? 0 : 4;

        /*
         * Variables used in ActiveParticipant section, which identifies
         * the IP address and application of the source and destination.
         */
        $srcUserID = $_SERVER['SERVER_NAME'] . '|OpenEMR';
        $srcNetwork = $_SERVER['SERVER_ADDR'];
        $destUserID = OEGlobalsBag::getInstance()->get('atna_audit_host');
        $destNetwork = OEGlobalsBag::getInstance()->get('atna_audit_host');

        $patientRecordForMsg = ($eventIdDisplayName == 'Patient Record' && $patient_id !== 0)
            ? sprintf(self::RFC3881_MSG_PATIENT_TEMPLATE, $patient_id)
            : '';
        /* Add the syslog header  with $eventDateTime and $_SERVER['SERVER_NAME'] */
        return sprintf(
            self::RFC3881_MSG_PRIMARY_TEMPLATE,
            $eventDateTime,
            $_SERVER['SERVER_NAME'],
            $eventActionCode,
            $eventDateTime,
            $eventOutcome,
            $eventIdDisplayName,
            $srcUserID,
            $srcNetwork,
            $destUserID,
            $destNetwork,
            $srcUserID,
            $user,
            $patientRecordForMsg,
        );
    }

    /**
     * Event action codes indicate whether the event is read/write.
     * C = create, R = read, U = update, D = delete, E = execute
     */
    protected function determineRFC3881EventActionCode(string $event): string
    {
        return match (substr($event, -7)) {
            '-create' => self::EVENT_ACTION_CODE_CREATE,
            '-insert' => self::EVENT_ACTION_CODE_INSERT,
            '-select' => self::EVENT_ACTION_CODE_SELECT,
            '-update' => self::EVENT_ACTION_CODE_UPDATE,
            '-delete' => self::EVENT_ACTION_CODE_DELETE,
            default => self::EVENT_ACTION_CODE_EXECUTE,
        };
    }

    /**
     * The choice of event codes is up to OpenEMR.
     * We're using the same event codes as
     * https://iheprofiles.projects.openhealthtools.org/
     */
    protected function determineRFC3881EventIdDisplayName(string $event): string
    {
        return match (true) {
            str_contains($event, 'patient-record') => 'Patient Record',
            str_contains($event, 'view') => 'Patient Record',
            str_contains($event, 'login') => 'Login',
            str_contains($event, 'logout') => 'Logout',
            str_contains($event, 'scheduling') => 'Patient Care Assignment',
            str_contains($event, 'security-administration') => 'Security Administration',
            default => $event,
        };
    }
}
