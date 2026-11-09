<?php

/**
 * A socket connection to a phiMail Direct Messaging server, speaking its
 * line-based protocol.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Uri;
use OpenEMR\Services\SoftwareVersion;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

final readonly class PhiMailConnection
{
    // The protocol ends lines with LF on every platform, so PHP_EOL would be wrong.
    private const LF = "\n";
    private const OK = 'OK' . self::LF;
    private const CONNECT_TRIES = 3;
    private const CONNECT_TIMEOUT_SECONDS = 10;
    private const BLOB_CHUNK_BYTES = 1024;
    // Version of the phiMail client protocol this code speaks.
    private const PROTOCOL_VERSION = '1.3.2';

    /** @var resource */
    private mixed $stream;

    /**
     * @param mixed $stream an open stream to a phiMail server
     */
    public function __construct(
        mixed $stream,
        private Filesystem $filesystem = new Filesystem(),
    ) {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('Expected an open stream');
        }
        $this->stream = $stream;
    }

    /**
     * Connect to the server and introduce this client.
     *
     * @throws PhiMailConnectionException
     */
    public static function open(PhiMailServer $server, SoftwareVersion $version, LoggerInterface $logger): self
    {
        if (!$server->enabled) {
            throw new PhiMailConnectionException(PhiMailConnectionFailure::Disabled);
        }
        if ($server->testMode) {
            $logger->debug('Connecting to phiMail in test mode. This should not be used for production', ['ca' => $server->caFile]);
        }

        [$target, $secure] = self::target($server->address);
        $options = ($secure && $server->caFile !== null)
            ? ['ssl' => ['verify_peer' => true, 'cafile' => $server->caFile]]
            : [];
        $context = stream_context_create($options);

        $stream = false;
        $errno = 0;
        $errstr = '';
        for ($try = 1; $try <= self::CONNECT_TRIES; $try++) {
            $stream = @stream_socket_client($target, $errno, $errstr, self::CONNECT_TIMEOUT_SECONDS, STREAM_CLIENT_CONNECT, $context);
            if ($stream !== false) {
                break;
            }
        }
        if ($stream === false) {
            throw new PhiMailConnectionException(PhiMailConnectionFailure::Unreachable, $errno ?? 0, $errstr ?? '');
        }

        $connection = new self($stream);
        $connection->handshake($version);
        $logger->debug('Connected to phiMail', ['server' => $target]);
        return $connection;
    }

    /**
     * Send INFO VER, which the server requires before any other command.
     * Closes the connection when the server refuses it.
     *
     * @throws PhiMailConnectionException
     */
    public function handshake(SoftwareVersion $version): void
    {
        $info = sprintf('INFO VER OEMR %s %s %s', $version, self::PROTOCOL_VERSION, PHP_VERSION);
        try {
            $this->expectOk($info . self::LF);
        } catch (PhiMailException $e) {
            $this->close();
            throw new PhiMailConnectionException(PhiMailConnectionFailure::HandshakeRejected, previous: $e);
        }
    }

    /**
     * @throws PhiMailStreamException
     */
    public function write(string $data): void
    {
        if (@fwrite($this->stream, $data) === false || !@fflush($this->stream)) {
            throw new PhiMailStreamException('Failed writing to the phiMail server');
        }
    }

    /**
     * @return string the next line from the server, including its line feed
     * @throws PhiMailStreamException
     */
    public function readLine(): string
    {
        $line = fgets($this->stream);
        if ($line === false) {
            throw new PhiMailStreamException('Failed reading from the phiMail server');
        }
        return $line;
    }

    /**
     * Send a command and require an OK reply.
     *
     * @throws PhiMailUnexpectedResponseException when the server replies with anything else
     * @throws PhiMailStreamException
     */
    public function expectOk(string $data): void
    {
        $this->write($data);
        $reply = $this->readLine();
        if ($reply !== self::OK) {
            throw new PhiMailUnexpectedResponseException($reply);
        }
    }

    /**
     * Download the next $length bytes into a new file in $directory. Stops
     * early if the server closes the connection.
     *
     * The file is readable only by its owner, since it holds message content.
     *
     * @return string path of the downloaded file
     * @throws PhiMailBlobException
     * @throws IOException when a failed download cannot be removed
     */
    public function readBlob(int $length, string $directory): string
    {
        if (!file_exists($directory)) {
            throw new PhiMailBlobException('M13 temp dir does not exist: ' . $directory);
        }

        // Without a suffix, tempnam() calls PHP's tempnam(), which creates the file
        // with mode 0600. A suffix would create it under the umask and any default ACL.
        try {
            $path = $this->filesystem->tempnam($directory, 'direct-');
        } catch (IOException $e) {
            throw new PhiMailBlobException('M14 failed creating a temp file in: ' . $directory, 0, $e);
        }
        // PHP's tempnam() falls back to the system temp dir when it cannot use $directory.
        // realpath() cannot resolve a stream wrapper URL, so compare those as given.
        if (dirname($path) !== (realpath($directory) ?: $directory)) {
            $this->filesystem->remove($path);
            throw new PhiMailBlobException('M14 failed creating a temp file in: ' . $directory);
        }

        $file = @fopen($path, 'w');
        if ($file === false) {
            $this->filesystem->remove($path);
            throw new PhiMailBlobException('M14 failed opening temp file: ' . $path);
        }

        $remaining = $length;
        try {
            while (!feof($this->stream) && $remaining > 0) {
                $chunk = @fread($this->stream, min($remaining, self::BLOB_CHUNK_BYTES));
                if ($chunk === false) {
                    throw new PhiMailBlobException(sprintf('M15 failure reading blob chunk after %d bytes', $length - $remaining));
                }
                if (@fwrite($file, $chunk) === false) {
                    throw new PhiMailBlobException(sprintf('M15 failure writing blob chunk after %d bytes', $length - $remaining));
                }
                $remaining -= strlen($chunk);
            }
        } catch (PhiMailBlobException $e) {
            fclose($file);
            $this->filesystem->remove($path);
            throw $e;
        }

        // Plain-file writes are unbuffered, so every byte is already written and close cannot lose data.
        fclose($file);
        return $path;
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    /**
     * Turn the configured server address into a stream_socket_client() target.
     *
     * @return array{string, bool} the target, and whether it uses TLS
     * @throws PhiMailConnectionException
     */
    private static function target(string $address): array
    {
        try {
            $uri = new Uri($address);
        } catch (MalformedUriException $e) {
            throw new PhiMailConnectionException(PhiMailConnectionFailure::InvalidAddress, previous: $e);
        }

        // Uri drops a port that is the scheme's default, so restore it.
        return match ($uri->getScheme()) {
            'tcp', 'http' => ['tcp://' . $uri->getHost() . ':' . ($uri->getPort() ?? 80), false],
            'https' => ['ssl://' . $uri->getHost() . ':' . ($uri->getPort() ?? 443), true],
            'ssl', 'sslv3', 'tls' => [$address, true],
            default => throw new PhiMailConnectionException(PhiMailConnectionFailure::InvalidAddress),
        };
    }
}
