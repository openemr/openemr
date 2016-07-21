<?php
/*
 * $Id: 5288eb0042a1c3bf2b1d34ed509a6c46adce87fd $
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

require_once 'phing/listener/DefaultLogger.php';
include_once 'phing/system/util/Properties.php';

/**
 * Uses CSS class that must be defined in the HTML page
 * where the Phing output is displayed.
 *
 * If used with the -logfile option, the output
 * will contain the text wrapped in html <span> elements
 * with those css classes.
 *
 * The default classes used for differentiating
 * the message levels can be changed by editing the
 * phing/listener/defaults.properties file.
 *
 * This file can contain 5 key/value pairs:
 * HtmlColorLogger.ERROR_CLASS=_your_css_class_name_
 * HtmlColorLogger.WARNING_CLASS=_your_css_class_name_
 * HtmlColorLogger.INFO_CLASS=_your_css_class_name_
 * HtmlColorLogger.VERBOSE_CLASS=_your_css_class_name_
 * HtmlColorLogger.DEBUG_CLASS=_your_css_class_name_
 *
 * This stems from the Ansi Color Logger done by Hans Lellelid:
 *
 * @author     Anton Stöckl <anton@stoeckl.de> (Phing HTML Color Logger)
 * @author     Hans Lellelid <hans@xmpl.org> (Phing Ansi Color Logger)
 * @author     Magesh Umasankar (Ant)
 * @package    phing.listener
 * @version    $Id: 5288eb0042a1c3bf2b1d34ed509a6c46adce87fd $
 */
class HtmlColorLogger extends DefaultLogger
{

    const CLASS_ERR = 'phing_err';
    const CLASS_VERBOSE = 'phing_verbose';
    const CLASS_DEBUG = 'phing_debug';
    const CLASS_WARN = 'phing_warn';
    const CLASS_INFO = 'phing_info';

    const PREFIX = '<span class="';
    const SUFFIX = '">';
    const END_COLOR = '</span>';

    private $errColor;
    private $warnColor;
    private $infoColor;
    private $verboseColor;
    private $debugColor;

    private $colorsSet = false;

    /**
     * Construct new HtmlColorLogger
     * Perform initializations that cannot be done in var declarations.
     */
    public function __construct()
    {
        parent::__construct();
        $this->errColor = self::PREFIX . self::CLASS_ERR . self::SUFFIX;
        $this->warnColor = self::PREFIX . self::CLASS_WARN . self::SUFFIX;
        $this->infoColor = self::PREFIX . self::CLASS_INFO . self::SUFFIX;
        $this->verboseColor = self::PREFIX . self::CLASS_VERBOSE . self::SUFFIX;
        $this->debugColor = self::PREFIX . self::CLASS_DEBUG . self::SUFFIX;
    }

    /**
     * Set the colors to use from a property file specified in the
     * special phing property file "phing/listener/defaults.properties".
     */
    final private function setColors()
    {

        $systemColorFile = new PhingFile(Phing::getResourcePath("phing/listener/defaults.properties"));

        try {
            $prop = new Properties();

            $prop->load($systemColorFile);

            $err = $prop->getProperty("HtmlColorLogger.ERROR_CLASS");
            $warn = $prop->getProperty("HtmlColorLogger.WARNING_CLASS");
            $info = $prop->getProperty("HtmlColorLogger.INFO_CLASS");
            $verbose = $prop->getProperty("HtmlColorLogger.VERBOSE_CLASS");
            $debug = $prop->getProperty("HtmlColorLogger.DEBUG_CLASS");
            if ($err !== null) {
                $this->errColor = self::PREFIX . $err . self::SUFFIX;
            }
            if ($warn !== null) {
                $this->warnColor = self::PREFIX . $warn . self::SUFFIX;
            }
            if ($info !== null) {
                $this->infoColor = self::PREFIX . $info . self::SUFFIX;
            }
            if ($verbose !== null) {
                $this->verboseColor = self::PREFIX . $verbose . self::SUFFIX;
            }
            if ($debug !== null) {
                $this->debugColor = self::PREFIX . $debug . self::SUFFIX;
            }
        } catch (IOException $ioe) {
            //Ignore exception - we will use the defaults.
        }
    }

    /**
     * @see DefaultLogger#printMessage
     * @param string       $message
     * @param OutputStream $stream
     * @param int          $priority
     */
    final protected function printMessage($message, OutputStream $stream, $priority)
    {
        if ($message !== null) {

            if (!$this->colorsSet) {
                $this->setColors();
                $this->colorsSet = true;
            }

            $search = array('<', '>');
            $replace = array('&lt;', '&gt;');
            $message = str_replace($search, $replace, $message);

            $search = array("\t", "\n", "\r");
            $replace = array('&nbsp;&nbsp;&nbsp;', '<br>', '');
            $message = str_replace($search, $replace, $message);

            if (preg_match('@^( +)([^ ].+)@', $message, $matches)) {
                $len = strlen($matches[1]);
                $space = '&nbsp;';
                for ($i = 1; $i < $len; $i++) {
                    $space .= '&nbsp;';
                }
                $message = $space . $matches[2];
            }

            switch ($priority) {
                case Project::MSG_ERR:
                    $message = $this->errColor . $message . self::END_COLOR;
                    break;
                case Project::MSG_WARN:
                    $message = $this->warnColor . $message . self::END_COLOR;
                    break;
                case Project::MSG_INFO:
                    $message = $this->infoColor . $message . self::END_COLOR;
                    break;
                case Project::MSG_VERBOSE:
                    $message = $this->verboseColor . $message . self::END_COLOR;
                    break;
                case Project::MSG_DEBUG:
                    $message = $this->debugColor . $message . self::END_COLOR;
                    break;
            }

            $stream->write($message . '<br/>');
        }
    }
}
