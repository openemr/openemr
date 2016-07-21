<?php
/*
 * $Id: 519893a6517940c2efd8ea989a54657275dcf902 $
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
 * A HTTP download task.
 *
 * Downloads a file via HTTP GET method and saves it to a specified directory
 *
 * @package phing.tasks.ext
 * @author  Ole Markus With <o.with@sportradar.com>
 * @version $Id: 519893a6517940c2efd8ea989a54657275dcf902 $
 */
class HttpGetTask extends HttpTask
{
    /**
     * Holds the filename to store the output in
     *
     * @var string
     */
    protected $filename = null;

    /**
     * Holds the save location
     *
     * @var string
     */
    protected $dir = null;

    /**
     * Holds value for "ssl_verify_peer" option
     *
     * @var boolean
     */
    protected $sslVerifyPeer = true;

    /**
     * Holds value for "follow_redirects" option
     *
     * @var null|bool
     */
    protected $followRedirects = null;

    /**
     * Holds the proxy
     *
     * @var string
     */
    protected $proxy = null;

    /**
     * @return HTTP_Request2
     * @throws BuildException
     * @throws HTTP_Request2_LogicException
     */
    protected function createRequest()
    {
        if (!isset($this->dir)) {
            throw new BuildException("Required attribute 'dir' is missing");
        }

        $config = array(
            'ssl_verify_peer' => $this->sslVerifyPeer
        );
        if (isset($this->proxy)) {
            $config['proxy'] = $this->proxy;
        }
        if (null !== $this->followRedirects) {
            $config['follow_redirects'] = $this->followRedirects;
        }

        $request = parent::createRequest();
        $request->setConfig($config);

        $this->log("Fetching " . $this->url);

        return $request;
    }

    /**
     * Saves the response body to a specified directory
     *
     * @param  HTTP_Request2_Response $response
     * @return void
     * @throws BuildException
     */
    protected function processResponse(HTTP_Request2_Response $response)
    {
        if ($response->getStatus() != 200) {
            throw new BuildException(
                "Request unsuccessful. Response from server: " . $response->getStatus()
                . " " . $response->getReasonPhrase()
            );
        }

        $content = $response->getBody();
        $disposition = $response->getHeader('content-disposition');

        if ($this->filename) {
            $filename = $this->filename;

        } elseif ($disposition && 0 == strpos($disposition, 'attachment')
            && preg_match('/filename="([^"]+)"/', $disposition, $m)
        ) {
            $filename = basename($m[1]);

        } else {
            $filename = basename(parse_url($this->url, PHP_URL_PATH));
        }

        if (!is_writable($this->dir)) {
            throw new BuildException("Cannot write to directory: " . $this->dir);
        }

        $filename = $this->dir . "/" . $filename;
        file_put_contents($filename, $content);

        $this->log("Contents from " . $this->url . " saved to $filename");
    }

    /**
     * Sets the filename to store the output in
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Sets the save location
     *
     * @param string $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Sets the ssl_verify_peer option
     *
     * @param bool $value
     */
    public function setSslVerifyPeer($value)
    {
        $this->sslVerifyPeer = $value;
    }

    /**
     * Sets the follow_redirects option
     *
     * @param bool $value
     */
    public function setFollowRedirects($value)
    {
        $this->followRedirects = $value;
    }

    /**
     * Sets the proxy
     *
     * @param string $proxy
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }
}
