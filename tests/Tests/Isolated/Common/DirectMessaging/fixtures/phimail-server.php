<?php

/**
 * Child-process fixture: a scripted phiMail server for PhiMailReceiverBackgroundServiceTest.
 *
 * Listens on a free local port and prints "port <n>". For each argument, reads
 * one command line from the client and answers with the argument. Then prints
 * "closed" if the client closed the connection, or "open" if it did not.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

// @codeCoverageIgnoreStart
// This script runs in a child process, which coverage does not instrument.
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if ($server === false) {
    fwrite(STDERR, "listen failed: {$errstr}" . PHP_EOL);
    exit(1);
}
$address = stream_socket_get_name($server, false);
if ($address === false) {
    fwrite(STDERR, 'could not read the listening address' . PHP_EOL);
    exit(1);
}
// Write past any output buffer, so the test sees the port before the accept wait starts
fwrite(STDOUT, 'port ' . substr($address, strrpos($address, ':') + 1) . PHP_EOL);
fflush(STDOUT);

// Allow for slow CI runners, where coverage tooling loads in this process too
$conn = stream_socket_accept($server, 30);
if ($conn === false) {
    fwrite(STDERR, 'no client connected' . PHP_EOL);
    exit(1);
}
stream_set_timeout($conn, 5);

foreach (array_slice($argv ?? [], 1) as $reply) {
    if (fgets($conn) === false) {
        echo 'client hung up early', PHP_EOL;
        exit(0);
    }
    fwrite($conn, $reply . "\n");
    fflush($conn);
}

$extra = fgets($conn);
echo ($extra === false && !stream_get_meta_data($conn)['timed_out']) ? 'closed' : 'open', PHP_EOL;
// @codeCoverageIgnoreEnd
