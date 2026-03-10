<?php

/**
 * TLS TCP writer for ATNA audit messages
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

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
