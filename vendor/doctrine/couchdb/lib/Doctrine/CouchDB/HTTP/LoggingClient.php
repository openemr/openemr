<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\CouchDB\HTTP;

class LoggingClient implements Client
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Array of requests made to CouchDB with this client.
     *
     * Contains the following keys:
     * - duration - Microseconds it took to execute and process the request
     * - method (GET, POST, ..)
     * - path - The requested url path on the server including parameters
     * - request - The request body if its size is smaller than 10000 chars.
     * - request_size - The size of the request body
     * - response_status - The response HTTP status
     * - response - The body of the response.
     * - response_headers
     *
     * @var array
     */
    public $requests = array();

    /**
     * @var float
     */
    public $totalDuration = 0;

    /**
     * Construct new logging client wrapping the real client.
     * 
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request($method, $path, $data = null, $raw = false, array $headers = array())
    {
        $start = microtime(true);
        
        $response = $this->client->request($method, $path, $data, $raw, $headers);
        
        $duration = microtime(true) - $start;
        $this->requests[] = array(
            'duration' => $duration,
            'method' => $method,
            'path' => rawurldecode($path),
            'request' => $data,
            'request_size' => strlen($data),
            'response_status' => $response->status,
            'response' => $response->body,
            'response_headers' => $response->headers,
        );
        $this->totalDuration += $duration;

        return $response;
    }
}
