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

/**
 * Connection handler using PHPs stream wrappers.
 *
 * Requires PHP being compiled with --with-curlwrappers for now, since the PHPs
 * own HTTP implementation is somehow b0rked.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Kore Nordmann <kore@arbitracker.org>
 */
class StreamClient extends AbstractHTTPClient
{
    /**
     * Perform a request to the server and return the result
     *
     * Perform a request to the server and return the result converted into a
     * Response object. If you do not expect a JSON structure, which
     * could be converted in such a response object, set the forth parameter to
     * true, and you get a response object returned, containing the raw body.
     *
     * @param string $method
     * @param string $path
     * @param string $data
     * @param bool $raw
     * @param array $headers
     * @return Response
     * @throws HTTPException
     */
    public function request( $method, $path, $data = null, $raw = false, array $headers = array())
    {
        $basicAuth = '';
        if ( $this->options['username'] ) {
            $basicAuth .= "{$this->options['username']}:{$this->options['password']}@";
        }
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }
        $stringHeader = '';
        if ($headers != null) {
            foreach ($headers as $key => $val) {
                $stringHeader .= $key . ": " . $val . "\r\n";
            }
        }

        // TODO SSL support?
        $httpFilePointer = @fopen(
            'http://' . $basicAuth . $this->options['host']  . ':' . $this->options['port'] . $path,
            'r',
            false,
            stream_context_create(
                array(
                    'http' => array(
                        'method'        => $method,
                        'content'       => $data,
                        'ignore_errors' => true,
                        'max_redirects' => 0,
                        'user_agent'    => 'Doctrine CouchDB ODM $Revision$',
                        'timeout'       => $this->options['timeout'],
                        'header'        => $stringHeader,
                    ),
                )
            )
        );

        // Check if connection has been established successfully
        if ( $httpFilePointer === false ) {
            $error = error_get_last();
            throw HTTPException::connectionFailure(
                $this->options['ip'],
                $this->options['port'],
                $error['message'],
                0
            );
        }

        // Read request body
        $body = '';
        while ( !feof( $httpFilePointer ) ) {
            $body .= fgets( $httpFilePointer );
        }

        $metaData = stream_get_meta_data( $httpFilePointer );
        // The structure of this array differs depending on PHP compiled with
        // --enable-curlwrappers or not. Both cases are normally required.
        $rawHeaders = isset( $metaData['wrapper_data']['headers'] )
            ? $metaData['wrapper_data']['headers'] : $metaData['wrapper_data'];

        $headers = array();
        foreach ( $rawHeaders as $lineContent ) {
            // Extract header values
            if ( preg_match( '(^HTTP/(?P<version>\d+\.\d+)\s+(?P<status>\d+))S', $lineContent, $match ) ) {
                $headers['version'] = $match['version'];
                $headers['status']  = (int) $match['status'];
            } else {
                list( $key, $value ) = explode( ':', $lineContent, 2 );
                $headers[strtolower( $key )] = ltrim( $value );
            }
        }

        if ( empty($headers['status']) ) {
            throw HTTPException::readFailure(
                $this->options['ip'],
                $this->options['port'],
                'Received an empty response or not status code',
                0
            );
        }

        // Create response object from couch db response
        if ( $headers['status'] >= 400 )
        {
            return new ErrorResponse( $headers['status'], $headers, $body );
        }
        return new Response( $headers['status'], $headers, $body, $raw );
    }

}

