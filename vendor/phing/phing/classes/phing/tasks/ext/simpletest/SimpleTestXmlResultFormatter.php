<?php
/**
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

require_once 'phing/tasks/ext/simpletest/SimpleTestResultFormatter.php';
@include_once 'simpletest/xml.php';

/**
 * Prints plain text output of the test to a specified Writer.
 *
 * @author Michiel Rook <mrook@php.net>
 *
 * @package phing.tasks.ext.simpletest
 * @since 2.2.0
 */
class SimpleTestXmlResultFormatter extends SimpleTestResultFormatter
{
    /**
     * @var XmlReporter
     */
    private $logger = null;

    private $xmlData = "";

    /**
     *
     */
    public function __construct()
    {
        $this->logger = new XmlReporter();
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return ".xml";
    }

    /**
     * @return string
     */
    public function getPreferredOutfile()
    {
        return "testsuites";
    }

    private function captureStart()
    {
        ob_start();
    }

    private function captureStop()
    {
        $this->xmlData .= ob_get_contents();
        ob_end_clean();
    }

    /**
     * @param string $test_name
     * @param int $size
     */
    public function paintGroupStart($test_name, $size)
    {
        parent::paintGroupStart($test_name, $size);

        $this->captureStart();
        $this->logger->paintGroupStart($test_name, $size);
        $this->captureStop();
    }

    /**
     * @param string $test_name
     */
    public function paintGroupEnd($test_name)
    {
        parent::paintGroupEnd($test_name);

        $this->captureStart();
        $this->logger->paintGroupEnd($test_name);
        $this->captureStop();

        if (count($this->_test_stack) == 0) {
            if ($this->out) {
                $this->out->write($this->xmlData);
                $this->out->close();
            }
        }
    }

    /**
     * @param string $test_name
     */
    public function paintCaseStart($test_name)
    {
        $this->captureStart();
        $this->logger->paintCaseStart($test_name);
        $this->captureStop();
    }

    /**
     * @param string $test_name
     */
    public function paintCaseEnd($test_name)
    {
        $this->captureStart();
        $this->logger->paintCaseEnd($test_name);
        $this->captureStop();
    }

    /**
     * @param string $test_name
     */
    public function paintMethodStart($test_name)
    {
        $this->captureStart();
        $this->logger->paintMethodStart($test_name);
        $this->captureStop();
    }

    /**
     * @param string $test_name
     */
    public function paintMethodEnd($test_name)
    {
        $this->captureStart();
        $this->logger->paintMethodEnd($test_name);
        $this->captureStop();
    }

    /**
     * @param string $message
     */
    public function paintPass($message)
    {
        $this->captureStart();
        $this->logger->paintPass($message);
        $this->captureStop();
    }

    /**
     * @param string $message
     */
    public function paintError($message)
    {
        $this->captureStart();
        $this->logger->paintError($message);
        $this->captureStop();
    }

    /**
     * @param string $message
     */
    public function paintFail($message)
    {
        $this->captureStart();
        $this->logger->paintFail($message);
        $this->captureStop();
    }

    /**
     * @param Exception $exception
     */
    public function paintException($exception)
    {
        $this->captureStart();
        $this->logger->paintException($exception);
        $this->captureStop();
    }

    /**
     * @param string $message
     */
    public function paintSkip($message)
    {
        $this->captureStart();
        $this->logger->paintSkip($message);
        $this->captureStop();
    }

    /**
     * @param string $message
     */
    public function paintMessage($message)
    {
        $this->captureStart();
        $this->logger->paintMessage($message);
        $this->captureStop();
    }

    /**
     * @param string $message
     */
    public function paintFormattedMessage($message)
    {
        $this->captureStart();
        $this->logger->paintFormattedMessage($message);
        $this->captureStop();
    }

    /**
     * @param string $type
     * @param mixed $payload
     */
    public function paintSignal($type, $payload)
    {
        $this->captureStart();
        $this->logger->paintSignal($type, $payload);
        $this->captureStop();
    }
}
