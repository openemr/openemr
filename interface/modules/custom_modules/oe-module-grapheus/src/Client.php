<?php

/**
 * Talks to the Grapheus service with the clinician's own key. Everything the
 * browser does goes through here, so the key never reaches the browser.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Exetazo\Grapheus;

class Client
{
    public function __construct(private string $server, private string $key)
    {
        $this->server = rtrim($server, '/');
    }

    /** @return array{status:int, body:array} */
    public function call(string $method, string $path, $body = null, ?string $contentType = null, int $timeout = 60): array
    {
        $ch = curl_init($this->server . $path);
        $headers = ['Authorization: Bearer ' . $this->key, 'Accept: application/json'];
        if ($body !== null) {
            if ($contentType === null) {
                $body = json_encode($body);
                $contentType = 'application/json';
            }
            $headers[] = 'Content-Type: ' . $contentType;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($raw === false) {
            return ['status' => 0, 'body' => ['ok' => false, 'error' => 'Could not reach Grapheus: ' . $err]];
        }
        $json = json_decode((string) $raw, true);
        return ['status' => $status, 'body' => is_array($json) ? $json : ['ok' => false, 'error' => 'Unexpected answer from Grapheus (' . $status . ').']];
    }
}
