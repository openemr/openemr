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
        return new AtnaSink(
            clock: ServiceContainer::getClock(),
            enabled: $bag->getBoolean('enable_atna_audit'),
            host: $bag->getString('atna_audit_host'),
            port: $bag->getInt('atna_audit_port'),
            localCert: $bag->getString('atna_audit_localcert'),
            caCert: $bag->getString('atna_audit_cacert'),
        );
    }

    public function __construct(
        private ClockInterface $clock,
        private bool $enabled,
        private string $host,
        private int $port,
        private string $localCert,
        private string $caCert,
    ) {
    }

    // Future: handle a better-typed DTO
    public function record(string $user, string $group, string $event, int $patientId, int $outcome, string $comments): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $connection = $this->createTlsConn();
        if ($connection === false) {
            // Log that this failed?
            return;
        }

        $message = $this->createRfc3881Msg($user, $group, $event, $patientId, $outcome, $comments);
        fwrite($connection, $message);
        fclose($connection);
    }

    /**
     * Create an XML audit record corresponding to RFC 3881.
     * The parameters passed are the column values (from table 'log')
     * for a single audit record.
     *
     * @param  $user
     * @param  $group
     * @param string $event
     * @param  $patient_id
     * @param  $outcome
     * @param  $comments
     * @return string
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

        $patientRecordForMsg = ($eventIdDisplayName == 'Patient Record' && $patient_id != 0)
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
        return match (substr((string) $event, -7)) {
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

        $eventIdDisplayName = $event;

        if (str_contains((string) $event, 'patient-record')) {
            $eventIdDisplayName = 'Patient Record';
        } elseif (str_contains((string) $event, 'view')) {
            $eventIdDisplayName = 'Patient Record';
        } elseif (str_contains((string) $event, 'login')) {
            $eventIdDisplayName = 'Login';
        } elseif (str_contains((string) $event, 'logout')) {
            $eventIdDisplayName = 'Logout';
        } elseif (str_contains((string) $event, 'scheduling')) {
            $eventIdDisplayName = 'Patient Care Assignment';
        } elseif (str_contains((string) $event, 'security-administration')) {
            $eventIdDisplayName = 'Security Administration';
        }

        return $eventIdDisplayName;
    }


    private function isEnabled(): bool
    {
        return $this->enabled && $this->host !== '';
    }

    /**
     * Create a TLS (SSLv3) connection to the given host/port.
     * $localcert is the path to a PEM file with a client certificate and private key.
     * $cafile is the path to the CA certificate file, for
     *  authenticating the remote machine's certificate.
     * If $cafile is "", the remote machine's certificate is not verified.
     * If $localcert is "", we don't pass a client certificate in the connection.
     *
     * Return a stream resource that can be used with fwrite(), fread(), etc.
     * Returns FALSE on error.
     *
     * @return resource|false
     */
    private function createTlsConn()
    {
        $sslopts = [];
        if ($this->caCert !== '') {
            $sslopts['cafile'] = $this->caCert;
            $sslopts['verify_peer'] = true;
            $sslopts['verify_depth'] = 10;
        }

        if ($this->localCert !== '') {
            $sslopts['local_cert'] = $this->localCert;
        }

        $opts = ['tls' => $sslopts, 'ssl' => $sslopts];
        $ctx = stream_context_create($opts);
        $timeout = 60;
        $flags = STREAM_CLIENT_CONNECT;

        return @stream_socket_client(
            'tls://' . $this->host . ":" . $this->port,
            $errno,
            $errstr,
            $timeout,
            $flags,
            $ctx
        );
    }
}
