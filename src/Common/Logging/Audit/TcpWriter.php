<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

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
class TcpWriter implements WriterInterface
{
    public function __construct(
        private string $host,
        private int $port,
        private string $localCert,
        private string $caCert,
    ) {
    }

    public function writeMessage(string $message): bool
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

        $socket = @stream_socket_client(
            'tls://' . $this->host . ":" . $this->port,
            $errno,
            $errstr,
            $timeout,
            $flags,
            $ctx
        );

        if ($socket === false) {
            return false;
        }

        try {
            return fwrite($socket, $message) !== false;
        } finally {
            fclose($socket);
        }
    }
}
