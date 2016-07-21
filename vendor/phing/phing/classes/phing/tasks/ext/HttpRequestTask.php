<?php
/**
 * $Id: ab6a2d3903492a67c2a16f15ae667d8866af6c1e $
 *
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
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/tasks/ext/HttpTask.php';

/**
 * A HTTP request task.
 * Making an HTTP request and try to match the response against an provided
 * regular expression.
 *
 * @package phing.tasks.ext
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id: ab6a2d3903492a67c2a16f15ae667d8866af6c1e $
 * @since   2.4.1
 */
class HttpRequestTask extends HttpTask
{
    /**
     * Holds the regular expression that should match the response
     *
     * @var string
     */
    protected $responseRegex = '';

    /**
     * Whether to enable detailed logging
     *
     * @var boolean
     */
    protected $verbose = false;

    /**
     * Holds the events that will be logged
     *
     * @var array<string>
     */
    protected $observerEvents = array(
        'connect',
        'sentHeaders',
        'sentBodyPart',
        'receivedHeaders',
        'receivedBody',
        'disconnect',
    );

    /**
     * Holds the request method
     *
     * @var string
     */
    protected $method = null;

    /**
     * Holds additional post parameters for the request
     *
     * @var Parameter[]
     */
    protected $postParameters = array();

    /**
     * Sets the response regex
     *
     * @param string $regex
     */
    public function setResponseRegex($regex)
    {
        $this->responseRegex = $regex;
    }

    /**
     * Sets whether to enable detailed logging
     *
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = StringHelper::booleanValue($verbose);
    }

    /**
     * Sets a list of observer events that will be logged if verbose output is enabled.
     *
     * @param string $observerEvents List of observer events
     */
    public function setObserverEvents($observerEvents)
    {
        $this->observerEvents = array();

        $token = ' ,;';
        $ext = strtok($observerEvents, $token);

        while ($ext !== false) {
            $this->observerEvents[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * The setter for the method
     * @param $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Creates post body parameters for this request
     *
     * @return Parameter The created post parameter
     */
    public function createPostParameter()
    {
        $num = array_push($this->postParameters, new Parameter());

        return $this->postParameters[$num - 1];
    }

    /**
     * Load the necessary environment for running this task.
     *
     * @throws BuildException
     */
    public function init()
    {
        parent::init();

        $this->authScheme = HTTP_Request2::AUTH_BASIC;

        // Other dependencies that should only be loaded when class is actually used
        require_once 'HTTP/Request2/Observer/Log.php';
    }

    /**
     * Creates and configures an instance of HTTP_Request2
     *
     * @return HTTP_Request2
     */
    protected function createRequest()
    {
        $request = parent::createRequest();

        if ($this->method == HTTP_Request2::METHOD_POST) {
            $request->setMethod(HTTP_Request2::METHOD_POST);

            foreach ($this->postParameters as $postParameter) {
                $request->addPostParameter($postParameter->getName(), $postParameter->getValue());
            }
        }

        if ($this->verbose) {
            $observer = new HTTP_Request2_Observer_Log();

            // set the events we want to log
            $observer->events = $this->observerEvents;

            $request->attach($observer);
        }

        return $request;
    }

    /**
     * Checks whether response body matches the given regexp
     *
     * @param  HTTP_Request2_Response $response
     * @return void
     * @throws BuildException
     */
    protected function processResponse(HTTP_Request2_Response $response)
    {
        if ($this->responseRegex !== '') {
            $matches = array();
            preg_match($this->responseRegex, $response->getBody(), $matches);

            if (count($matches) === 0) {
                throw new BuildException('The received response body did not match the given regular expression');
            } else {
                $this->log('The response body matched the provided regex.');
            }
        }
    }
}
