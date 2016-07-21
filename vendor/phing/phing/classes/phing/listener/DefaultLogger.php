<?php
/*
 *  $Id: 87e06a24aac53cc2f19fc0574489ae2f657de4bc $
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

require_once 'phing/listener/StreamRequiredBuildLogger.php';
include_once 'phing/BuildEvent.php';

/**
 * Writes a build event to the console.
 *
 * Currently, it only writes which targets are being executed, and
 * any messages that get logged.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: 87e06a24aac53cc2f19fc0574489ae2f657de4bc $
 * @see       BuildEvent
 * @package   phing.listener
 */
class DefaultLogger implements StreamRequiredBuildLogger
{

    /**
     *  Size of the left column in output. The default char width is 12.
     * @var int
     */
    const LEFT_COLUMN_SIZE = 12;

    /**
     *  The message output level that should be used. The default is
     *  <code>Project::MSG_VERBOSE</code>.
     * @var int
     */
    protected $msgOutputLevel = Project::MSG_ERR;

    /**
     *  Time that the build started
     * @var int
     */
    protected $startTime;

    /**
     * @var OutputStream Stream to use for standard output.
     */
    protected $out;

    /**
     * @var OutputStream Stream to use for error output.
     */
    protected $err;

    protected $emacsMode = false;

    /**
     *  Construct a new default logger.
     */
    public function __construct()
    {

    }

    /**
     *  Set the msgOutputLevel this logger is to respond to.
     *
     *  Only messages with a message level lower than or equal to the given
     *  level are output to the log.
     *
     *  <p> Constants for the message levels are in Project.php. The order of
     *  the levels, from least to most verbose, is:
     *
     *  <ul>
     *    <li>Project::MSG_ERR</li>
     *    <li>Project::MSG_WARN</li>
     *    <li>Project::MSG_INFO</li>
     *    <li>Project::MSG_VERBOSE</li>
     *    <li>Project::MSG_DEBUG</li>
     *  </ul>
     *
     *  The default message level for DefaultLogger is Project::MSG_ERR.
     *
     * @param int $level The logging level for the logger.
     * @see BuildLogger#setMessageOutputLevel()
     */
    public function setMessageOutputLevel($level)
    {
        $this->msgOutputLevel = (int) $level;
    }

    /**
     * Sets the output stream.
     * @param OutputStream $output
     * @see BuildLogger#setOutputStream()
     */
    public function setOutputStream(OutputStream $output)
    {
        $this->out = $output;
    }

    /**
     * Sets the error stream.
     * @param OutputStream $err
     * @see BuildLogger#setErrorStream()
     */
    public function setErrorStream(OutputStream $err)
    {
        $this->err = $err;
    }

    /**
     * Sets this logger to produce emacs (and other editor) friendly output.
     *
     * @param bool $emacsMode <code>true</code> if output is to be unadorned so that
     *                  emacs and other editors can parse files names, etc.
     */
    public function setEmacsMode($emacsMode)
    {
        $this->emacsMode = $emacsMode;
    }

    /**
     *  Sets the start-time when the build started. Used for calculating
     *  the build-time.
     *
     * @param BuildEvent $event
     * @internal param The $object BuildEvent
     */
    public function buildStarted(BuildEvent $event)
    {
        $this->startTime = Phing::currentTimeMillis();
        if ($this->msgOutputLevel >= Project::MSG_INFO) {
            $this->printMessage(
                "Buildfile: " . $event->getProject()->getProperty("phing.file"),
                $this->out,
                Project::MSG_INFO
            );
        }
    }

    /**
     *  Prints whether the build succeeded or failed, and any errors that
     *  occurred during the build. Also outputs the total build-time.
     *
     * @param BuildEvent $event
     * @internal param The $object BuildEvent
     * @see    BuildEvent::getException()
     */
    public function buildFinished(BuildEvent $event)
    {
        $error = $event->getException();
        if ($error === null) {
            $msg = PHP_EOL . $this->getBuildSuccessfulMessage() . PHP_EOL;
        } else {
            $msg = PHP_EOL . $this->getBuildFailedMessage() . PHP_EOL;
            if (Project::MSG_VERBOSE <= $this->msgOutputLevel || !($error instanceof BuildException)) {
                $msg .= $error->__toString() . PHP_EOL;
            } else {
                $msg .= $error->getMessage();
            }
        }
        $msg .= PHP_EOL . "Total time: " . self::formatTime(Phing::currentTimeMillis() - $this->startTime) . PHP_EOL;

        if ($error === null) {
            $this->printMessage($msg, $this->out, Project::MSG_VERBOSE);
        } else {
            $this->printMessage($msg, $this->err, Project::MSG_ERR);
        }
    }

    /**
     * Get the message to return when a build failed.
     * @return string The classic "BUILD FAILED"
     */
    protected function getBuildFailedMessage()
    {
        return "BUILD FAILED";
    }

    /**
     * Get the message to return when a build succeeded.
     * @return string The classic "BUILD FINISHED"
     */
    protected function getBuildSuccessfulMessage()
    {
        return "BUILD FINISHED";
    }

    /**
     *  Prints the current target name
     *
     * @param BuildEvent $event
     * @internal param The $object BuildEvent
     * @see    BuildEvent::getTarget()
     */
    public function targetStarted(BuildEvent $event)
    {
        if (Project::MSG_INFO <= $this->msgOutputLevel
            && $event->getTarget()->getName() != ''
        ) {
            $showLongTargets = $event->getProject()->getProperty("phing.showlongtargets");
            $msg = PHP_EOL . $event->getProject()->getName() . ' > ' . $event->getTarget()->getName(
                ) . ($showLongTargets ? ' [' . $event->getTarget()->getDescription() . ']' : '') . ':' . PHP_EOL;
            $this->printMessage($msg, $this->out, $event->getPriority());
        }
    }

    /**
     *  Fired when a target has finished. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @param BuildEvent $event
     * @internal param The $object BuildEvent
     * @see    BuildEvent::getException()
     */
    public function targetFinished(BuildEvent $event)
    {
    }

    /**
     *  Fired when a task is started. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @param BuildEvent $event
     * @internal param The $object BuildEvent
     * @see    BuildEvent::getTask()
     */
    public function taskStarted(BuildEvent $event)
    {
    }

    /**
     *  Fired when a task has finished. We don't need specific action on this
     *  event. So the methods are empty.
     *
     * @param  BuildEvent $event  The BuildEvent
     * @see    BuildEvent::getException()
     */
    public function taskFinished(BuildEvent $event)
    {
    }

    /**
     *  Print a message to the stdout.
     *
     * @param BuildEvent $event
     * @see    BuildEvent::getMessage()
     */
    public function messageLogged(BuildEvent $event)
    {
        $priority = $event->getPriority();
        if ($priority <= $this->msgOutputLevel) {
            $msg = "";
            if ($event->getTask() !== null && !$this->emacsMode) {
                $name = $event->getTask();
                $name = $name->getTaskName();
                $msg = str_pad("[$name] ", self::LEFT_COLUMN_SIZE, " ", STR_PAD_LEFT);
            }

            $msg .= $event->getMessage();

            if ($priority != Project::MSG_ERR) {
                $this->printMessage($msg, $this->out, $priority);
            } else {
                $this->printMessage($msg, $this->err, $priority);
            }
        }
    }

    /**
     *  Formats a time micro integer to human readable format.
     *
     * @param  integer The time stamp
     * @return string
     */
    public static function formatTime($micros)
    {
        $seconds = $micros;
        $minutes = $seconds / 60;
        if ($minutes > 1) {
            return sprintf(
                "%1.0f minute%s %0.2f second%s",
                $minutes,
                ($minutes === 1 ? " " : "s "),
                $seconds - floor($seconds / 60) * 60,
                ($seconds % 60 === 1 ? "" : "s")
            );
        } else {
            return sprintf("%0.4f second%s", $seconds, ($seconds % 60 === 1 ? "" : "s"));
        }
    }

    /**
     * Prints a message to console.
     *
     * @param  string $message The message to print.
     *                            Should not be <code>null</code>.
     * @param OutputStream|resource $stream The stream to use for message printing.
     * @param  int $priority The priority of the message.
     *                            (Ignored in this implementation.)
     * @throws IOException
     * @return void
     */
    protected function printMessage($message, OutputStream $stream, $priority)
    {
        $stream->write($message . PHP_EOL);
    }
}
