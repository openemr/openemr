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

require_once 'phing/listener/XmlLogger.php';

/**
 * Generates a file in the current directory with
 * an JSON description of what happened during a build.
 * The default filename is "log.json", but this can be overridden
 * with the property <code>JsonLogger.file</code>.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.listener
 */
class JsonLogger extends XmlLogger
{
    /**
     * Fired when the build finishes, this adds the time taken and any
     * error stacktrace to the build element and writes the document to disk.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Will not be <code>null</code>.
     * @throws BuildException
     */
    public function buildFinished(BuildEvent $event)
    {
        $elapsedTime = Phing::currentTimeMillis() - $this->getBuildTimerStart();

        $this->getBuildElement()->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::formatTime($elapsedTime));

        if ($event->getException() != null) {
            $this->getBuildElement()->setAttribute(XmlLogger::ERROR_ATTR, $event->getException()->getMessage());
            $errText = $this->getDoc()->createCDATASection($event->getException()->getTraceAsString());
            $stacktrace = $this->getDoc()->createElement(XmlLogger::STACKTRACE_TAG);
            $stacktrace->appendChild($errText);
            $this->getBuildElement()->appendChild($stacktrace);
        }

        $this->getDoc()->appendChild($this->getBuildElement());

        $outFilename = $event->getProject()->getProperty("JsonLogger.file");
        if ($outFilename == null) {
            $outFilename = "log.json";
        }

        try {
            $stream = $this->getOut();
            if ($stream === null) {
                $stream = new FileOutputStream($outFilename);
            }

            $writer = new OutputStreamWriter($stream);
            $writer->write($this->xml2js(simplexml_import_dom($this->getDoc())));
            $writer->close();
        } catch (IOException $exc) {
            try {
                $stream->close(); // in case there is a stream open still ...
            } catch (Exception $x) {
            }
            throw new BuildException("Unable to write log file.", $exc);
        }

        // cleanup:remove the buildElement
        $this->setBuildElement(null);

        array_pop($this->getElementStack());
        array_pop($this->getTimesStack());
    }

    private function xml2js(SimpleXMLElement $xmlnode, $isRoot = true)
    {
        $jsnode = array();

        if (!$isRoot) {
            if (count($xmlnode->attributes()) > 0){
                $jsnode["@attribute"] = array();
                foreach($xmlnode->attributes() as $key => $value)
                    $jsnode["@attribute"][$key] = (string)$value;
            }

            $textcontent = trim((string) $xmlnode);
            if (count($textcontent) > 0)
                $jsnode['_'] = $textcontent;

            foreach ($xmlnode->children() as $childxmlnode) {
                $childname = $childxmlnode->getName();
                if (!array_key_exists($childname, $jsnode))
                    $jsnode[$childname] = array();
                array_push($jsnode[$childname], $this->xml2js($childxmlnode, false));
            }
            return $jsnode;
        } else {
            $nodename = $xmlnode->getName();
            $jsnode[$nodename] = array();
            array_push($jsnode[$nodename], $this->xml2js($xmlnode, false));
            return json_encode($jsnode, JSON_PRETTY_PRINT);
        }
    }
}
