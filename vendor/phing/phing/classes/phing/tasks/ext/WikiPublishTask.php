<?php
/*
 *  $Id: f156eaee85b94f50511ae48d36758ea1bc57ed63 $
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

require_once 'phing/Task.php';

/**
 * Publish Wiki document using Wiki API.
 *
 * @author  Piotr Lewandowski <piotr@cassis.pl>
 * @package phing.tasks.ext
 */
class WikiPublishTask extends Task
{
    /**
     * Wiki API url
     *
     * @var string
     */
    private $apiUrl;
    /**
     * Wiki API user name
     *
     * @var string
     */
    private $apiUser;
    /**
     * Wiki API password
     *
     * @var string
     */
    private $apiPassword;
    /**
     * Wiki document Id. Document can be identified by title instead.
     *
     * @var int
     */
    private $id;
    /**
     * Wiki document title
     *
     * @var string
     */
    private $title;
    /**
     * Wiki document content
     *
     * @var string
     */
    private $content;
    /**
     * Publishing mode (append, prepend, overwrite).
     *
     * @var string
     */
    private $mode = 'append';
    /**
     * Publish modes map
     * @var array
     */
    private $modeMap = array(
        'overwrite' => 'text',
        'append' => 'appendtext',
        'prepend' => 'prependtext',
    );
    /**
     * Curl handler
     *
     * @var resource
     */
    private $curl;
    /**
     * Wiki api edit token
     *
     * @var string
     */
    private $apiEditToken;
    /**
     * Temporary cookies file
     *
     * @var string
     */
    private $cookiesFile;

    /**
     * @param string $apiPassword
     */
    public function setApiPassword($apiPassword)
    {
        $this->apiPassword = $apiPassword;
    }

    /**
     * @return string
     */
    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUser
     */
    public function setApiUser($apiUser)
    {
        $this->apiUser = $apiUser;
    }

    /**
     * @return string
     */
    public function getApiUser()
    {
        return $this->apiUser;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $mode
     * @throws BuildException
     */
    public function setMode($mode)
    {
        if (false === isset($this->modeMap[$mode])) {
            throw new BuildException('Mode is invalid (' . $mode . ', should be one of ' . implode(
                    ',',
                    array_keys($this->modeMap)
                ) . ')');
        }
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Prepare CURL object
     *
     * @throws BuildException
     */
    public function init()
    {
        $this->cookiesFile = tempnam(sys_get_temp_dir(), 'WikiPublish.' . uniqid() . '.cookies');

        $this->curl = curl_init();
        if (false === is_resource($this->curl)) {
            throw new BuildException('Curl init failed (' . $this->apiUrl . ')');
        }

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookiesFile);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookiesFile);
    }

    /**
     * The main entry point method
     */
    public function main()
    {
        $this->validateAttributes();
        $this->callApiLogin();
        $this->callApiEdit();
    }

    /**
     * Close curl connection and clean up
     */
    public function __destruct()
    {
        if (null !== $this->curl && is_resource($this->curl)) {
            curl_close($this->curl);
        }
        if (null !== $this->cookiesFile && file_exists($this->cookiesFile)) {
            unlink($this->cookiesFile);
        }
    }

    /**
     * Validates attributes coming in from XML
     *
     * @throws BuildException
     */
    private function validateAttributes()
    {

        if (null === $this->apiUrl) {
            throw new BuildException('Wiki apiUrl is required');
        }

        if (null === $this->id && null === $this->title) {
            throw new BuildException('Wiki page id or title is required');
        }

    }

    /**
     * Call Wiki webapi login action
     *
     * @param string|null $token
     *
     * @throws BuildException
     */
    private function callApiLogin($token = null)
    {
        $postData = array('lgname' => $this->apiUser, 'lgpassword' => $this->apiPassword);
        if (null !== $token) {
            $postData['lgtoken'] = $token;
        }

        $result = $this->callApi('action=login', $postData);
        try {
            $this->checkApiResponseResult('login', $result);
        } catch (BuildException $e) {
            if (null !== $token) {
                throw $e;
            }
            // if token is required then call login again with token
            $this->checkApiResponseResult('login', $result, 'NeedToken');
            if (isset($result['login']) && isset($result['login']['token'])) {
                $this->callApiLogin($result['login']['token']);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Call Wiki webapi edit action
     */
    private function callApiEdit()
    {
        $this->callApiTokens();
        $result = $this->callApi('action=edit&token=' . urlencode($this->apiEditToken), $this->getApiEditData());
        $this->checkApiResponseResult('edit', $result);
    }

    /**
     * Return prepared data for Wiki webapi edit action
     *
     * @return array
     */
    private function getApiEditData()
    {
        $result = array(
            'minor' => '',
        );
        if (null !== $this->title) {
            $result['title'] = $this->title;
        }
        if (null !== $this->id) {
            $result['pageid'] = $this->id;
        }
        $result[$this->modeMap[$this->mode]] = $this->content;

        return $result;
    }

    /**
     * Call Wiki webapi tokens action
     *
     * @throws BuildException
     */
    private function callApiTokens()
    {
        $result = $this->callApi('action=tokens&type=edit');
        if (false == isset($result['tokens']) || false == isset($result['tokens']['edittoken'])) {
            throw new BuildException('Wiki token not found');
        }

        $this->apiEditToken = $result['tokens']['edittoken'];
    }

    /**
     * Call Wiki webapi
     *
     * @param string     $queryString
     * @param array|null $postData
     *
     * @return array
     * @throws BuildException
     */
    protected function callApi($queryString, $postData = null)
    {
        $this->setPostData($postData);

        $url = $this->apiUrl . '?' . $queryString . '&format=php';

        curl_setopt($this->curl, CURLOPT_URL, $url);

        $response = curl_exec($this->curl);
        $responseCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if (200 !== $responseCode) {
            throw new BuildException('Wiki webapi call failed (http response ' . $responseCode . ')');
        }

        $result = @unserialize($response);
        if (false === $result) {
            throw new BuildException('Couldn\'t unserialize Wiki webapi response');
        }

        return $result;
    }

    /**
     * Set POST data for curl call
     *
     * @param array|null $data
     */
    private function setPostData($data = null)
    {
        if (null === $data) {
            curl_setopt($this->curl, CURLOPT_POST, false);

            return;
        }
        $postData = '';
        foreach ($data as $key => $value) {
            $postData .= urlencode($key) . '=' . urlencode($value) . '&';
        }
        if ($postData != '') {
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, substr($postData, 0, -1));
        }
    }

    /**
     * Validate Wiki webapi response
     *
     * @param string $action
     * @param array  $response
     * @param string $expect
     *
     * @throws BuildException
     */
    private function checkApiResponseResult($action, $response, $expect = 'Success')
    {
        if (isset($response['error'])) {
            throw new BuildException(
                'Wiki response error (action: ' . $action . ', error code: ' . $response['error']['code'] . ')');
        }
        if (false == isset($response[$action]) || false == isset($response[$action]['result'])) {
            throw new BuildException('Wiki response result not found (action: ' . $action . ')');
        }
        if ($response[$action]['result'] !== $expect) {
            throw new BuildException(
                'Unexpected Wiki response result ' . $response[$action]['result'] . ' (expected: ' . $expect . ')');
        }
    }
}
