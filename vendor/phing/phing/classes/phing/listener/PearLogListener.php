<?php
/*
 *  $Id: 63dcb060d3671bbf7d28433c4871c89c0119c5c1 $
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

require_once 'phing/BuildListener.php';

/**
 * Writes build messages to PEAR Log.
 *
 * By default it will log to file in current directory w/ name 'phing.log'.  You can customize
 * this behavior by setting properties:
 * - pear.log.type
 * - pear.log.name
 * - pear.log.ident (note that this class changes ident to project name)
 * - pear.log.conf (note that array values are currently unsupported in Phing property files)
 *
 * <code>
 *  phing -f build.xml -logger phing.listener.PearLogger -Dpear.log.type=file -Dpear.log.name=/path/to/log.log
 * </code>
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: 63dcb060d3671bbf7d28433c4871c89c0119c5c1 $
 * @see       BuildEvent
 * @package   phing.listener
 */
class PearLogListener implements BuildListener
{

    /**
     *  Size of the left column in output. The default char width is 12.
     * @var int
     */
    const LEFT_COLUMN_SIZE = 12;

    /**
     *  Time that the build started
     * @var int
     */
    protected $startTime;

    /**
     * Maps Phing Project::MSG_* constants to PEAR_LOG_* constants.
     * @var array
     */
    protected static $levelMap = array(
        Project::MSG_DEBUG => PEAR_LOG_DEBUG,
        Project::MSG_INFO => PEAR_LOG_INFO,
        Project::MSG_VERBOSE => PEAR_LOG_NOTICE,
        Project::MSG_WARN => PEAR_LOG_WARNING,
        Project::MSG_ERR => PEAR_LOG_ERR
    );
    /**
     * Whether logging has been configured.
     * @var boolean
     */
    protected $logConfigured = false;

    /**
     * @var Log PEAR Log object.
     */
    protected $logger;

    /**
     * Configure the logger.
     */
    protected function configureLogging()
    {

        $type = Phing::getDefinedProperty('pear.log.type');
        $name = Phing::getDefinedProperty('pear.log.name');
        $ident = Phing::getDefinedProperty('pear.log.ident');
        $conf = Phing::getDefinedProperty('pear.log.conf');

        if ($type === null) {
            $type = 'file';
        }
        if ($name === null) {
            $name = 'phing.log';
        }
        if ($ident === null) {
            $ident = 'phing';
        }
        if ($conf === null) {
            $conf = array();
        }

        include_once 'Log.php';
        if (!class_exists('Log')) {
            throw new BuildException("Cannot find PEAR Log class for use by PearLogger.");
        }

        $this->logger = Log::singleton($type, $name, $ident, $conf, self::$levelMap[$this->msgOutputLevel]);
    }

    /**
     * Get the configured PEAR logger to use.
     * This method just ensures that logging has been configured and returns the configured logger.
     * @return Log
     */
    protected function logger()
    {
        if (!$this->logConfigured) {
            $this->configureLogging();
        }

        return $this->logger;
    }

    /**
     *  Sets the start-time when the build started. Used for calculating
     *  the build-time.
     *
     * @param  BuildEvent  The BuildEvent
     */
    public function buildStarted(BuildEvent $event)
    {
        $this->startTime = Phing::currentTimeMillis();
        $this->logger()->setIdent($event->getProject()->getName());
        $this->logger()->info("Starting build with buildfile: " . $event->getProject()->getProperty("phing.file"));
    }

    /**
     *  Logs whether the build succeeded or failed, and any errors that
     *  occurred during the build. Also outputs the total build-time.
     *
     * @param  BuildEvent  The BuildEvent
     * @see    BuildEvent::getException()
     */
    public function buildFinished(BuildEvent $event)
    {
        $error = $event->getException();
        if ($error === null) {
            $msg = "Finished successful build.";
        } else {
            $msg = "Build failed. [reason: " . $error->getMessage() . "]";
        }
        $this->logger()->log(
            $msg . " Total time: " . DefaultLogger::formatTime(Phing::currentTimeMillis() - $this->startTime)
        );
    }

    /**
     * Logs the current target name
     *
     * @param  BuildEvent  The BuildEvent
     * @see    BuildEvent::getTarget()
     */
    public function targetStarted(BuildEvent $event)
    {
    }

    /**
     *  Fired when a target has finished. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @param  BuildEvent  The BuildEvent
     * @see    BuildEvent::getException()
     */
    public function targetFinished(BuildEvent $event)
    {
    }

    /**
     *  Fired when a task is started. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @param  BuildEvent  The BuildEvent
     * @see    BuildEvent::getTask()
     */
    public function taskStarted(BuildEvent $event)
    {
    }

    /**
     *  Fired when a task has finished. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @param  BuildEvent  The BuildEvent
     * @see    BuildEvent::getException()
     */
    public function taskFinished(BuildEvent $event)
    {
    }

    /**
     *  Logs a message to the configured PEAR logger.
     *
     * @param  BuildEvent  The BuildEvent
     * @see    BuildEvent::getMessage()
     */
    public function messageLogged(BuildEvent $event)
    {
        if ($event->getPriority() <= $this->msgOutputLevel) {
            $msg = "";
            if ($event->getTask() !== null) {
                $name = $event->getTask();
                $name = $name->getTaskName();
                $msg = str_pad("[$name] ", self::LEFT_COLUMN_SIZE, " ", STR_PAD_LEFT);
            }
            $msg .= $event->getMessage();
            $this->logger()->log($msg, self::$levelMap[$event->getPriority()]);
        }
    }
}
