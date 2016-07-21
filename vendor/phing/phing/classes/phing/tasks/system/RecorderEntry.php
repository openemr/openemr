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

include_once 'phing/BuildEvent.php';
include_once 'phing/BuildLogger.php';
include_once 'phing/Phing.php';
include_once 'phing/Project.php';
include_once 'phing/SubBuildListener.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/system/io/FileOutputStream.php';
include_once 'phing/system/io/IOException.php';
include_once 'phing/BuildException.php';

/**
 * This is a class that represents a recorder. This is the listener to the
 * build process.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.tasks.system
 */
class RecorderEntry implements BuildLogger, SubBuildListener
{
    /**
     * The name of the file associated with this recorder entry.
     * @var string $filename
     */
    private $filename = null;

    /**
     * The state of the recorder (recorder on or off).
     * @var bool $record
     */
    private $record = true;

    /** The current verbosity level to record at.  */
    private $loglevel;

    /**
     * The output OutputStream to record to.
     * @var OutputStream $out
     */
    private $out = null;

    /** The start time of the last know target.  */
    private $targetStartTime;

    /** Strip task banners if true.  */
    private $emacsMode = false;

    /**
     * project instance the recorder is associated with
     * @var Project $project
     */
    private $project;

    /**
     * @param string $name The name of this recorder (used as the filename).
     */
    public function __construct($name)
    {
        $this->targetStartTime = Phing::currentTimeMillis();
        $this->filename = $name;
        $this->loglevel = Project::MSG_INFO;
    }

    /**
     * @return string the name of the file the output is sent to.
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Turns off or on this recorder.
     *
     * @param bool|null state true for on, false for off, null for no change.
     */
    public function setRecordState($state)
    {
        if ($state != null) {
            $this->flush();
            $this->record = StringHelper::booleanValue($state);
        }
    }

    /** {@inheritDoc}. */
    public function buildStarted(BuildEvent $event)
    {
        $this->log("> BUILD STARTED", Project::MSG_DEBUG);
    }

    /** {@inheritDoc}. */
    public function buildFinished(BuildEvent $event)
    {
        $this->log("< BUILD FINISHED", Project::MSG_DEBUG);

        if ($this->record && $this->out != null) {
            $error = $event->getException();

            if ($error == null) {
                $this->out->write(Phing::getProperty('line.separator') . "BUILD SUCCESSFUL" . PHP_EOL);
            } else {
                $this->out->write(Phing::getProperty('line.separator') . "BUILD FAILED"
                    . Phing::getProperty('line.separator') . PHP_EOL);
                $this->out->write($error->getTraceAsString());
            }
        }
        $this->cleanup();
    }

    /**
     * Cleans up any resources held by this recorder entry at the end
     * of a subbuild if it has been created for the subbuild's project
     * instance.
     *
     * @param BuildEvent $event the buildFinished event
     */
    public function subBuildFinished(BuildEvent $event)
    {
        if ($event->getProject() == $this->project) {
            $this->cleanup();
        }
    }

    /**
     * Empty implementation to satisfy the BuildListener interface.
     *
     * @param BuildEvent $event the buildStarted event
     */
    public function subBuildStarted(BuildEvent $event)
    {
    }

    /** {@inheritDoc}. */
    public function targetStarted(BuildEvent $event)
    {
        $this->log(">> TARGET STARTED -- " . $event->getTarget()->getName(), Project::MSG_DEBUG);
        $this->log(Phing::getProperty('line.separator') . $event->getTarget()->getName() . ":",
            Project::MSG_INFO);
        $this->targetStartTime = Phing::currentTimeMillis();
    }

    /** {@inheritDoc}. */
    public function targetFinished(BuildEvent $event)
    {
        $this->log("<< TARGET FINISHED -- " . $event->getTarget()->getName(), Project::MSG_DEBUG);

        $time = $this->formatTime(Phing::currentTimeMillis() - $this->targetStartTime);

        $this->log($event->getTarget()->getName() . ":  duration " . $time, Project::MSG_VERBOSE);
        flush();
    }

    /** {@inheritDoc}. */
    public function taskStarted(BuildEvent $event)
    {
        $this->log(">>> TASK STARTED -- " . $event->getTask()->getTaskName(), Project::MSG_DEBUG);
    }

    /** {@inheritDoc}. */
    public function taskFinished(BuildEvent $event)
    {
        $this->log("<<< TASK FINISHED -- " . $event->getTask()->getTaskName(), Project::MSG_DEBUG);
        $this->flush();
    }

    /** {@inheritDoc}. */
    public function messageLogged(BuildEvent $event)
    {
        $this->log("--- MESSAGE LOGGED", Project::MSG_DEBUG);

        $buf = '';

        if ($event->getTask() != null) {
            $name = $event->getTask()->getTaskName();

            if (!$this->emacsMode) {
                $label = "[" . $name . "] ";
                $size = DefaultLogger::LEFT_COLUMN_SIZE - strlen($label);

                for ($i = 0; $i < $size; $i++) {
                    $buf .= " ";
                }
                $buf .= $label;
            }
        }
        $buf .= $event->getMessage();

        $this->log($buf, $event->getPriority());
    }


    /**
     * The thing that actually sends the information to the output.
     *
     * @param string $mesg The message to log.
     * @param int $level The verbosity level of the message.
     */
    private function log($mesg, $level)
    {
        if ($this->record && ($level <= $this->loglevel) && $this->out != null) {
            $this->out->write($mesg . PHP_EOL);
        }
    }

    private function flush()
    {
        if ($this->record && $this->out != null) {
            $this->out->flush();
        }
    }

    /** {@inheritDoc}. */
    public function setMessageOutputLevel($level)
    {
        if ($level >= Project::MSG_ERR && $level <= Project::MSG_DEBUG) {
            $this->loglevel = $level;
        }
    }

    /** {@inheritDoc}. */
    public function setOutputStream(OutputStream $output)
    {
        $this->closeFile();
        $this->out = $output;
    }

    /** {@inheritDoc}. */
    public function setEmacsMode($emacsMode)
    {
        $this->emacsMode = $emacsMode;
    }

    /** {@inheritDoc}. */
    public function setErrorStream(OutputStream $err)
    {
        $this->setOutputStream($err);
    }

    private static function formatTime($millis)
    {
        $seconds = $millis / 1000;
        $minutes = $seconds / 60;


        if ($minutes > 0) {
            return $minutes . " minute"
            . ($minutes == 1 ? " " : "s ")
            . ($seconds % 60) . " second"
            . ($seconds % 60 == 1 ? "" : "s");
        } else {
            return $seconds . " second"
            . ($seconds % 60 == 1 ? "" : "s");
        }
    }

    /**
     * Set the project associated with this recorder entry.
     *
     * @param Project $project the project instance
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
        if ($this->project != null) {
            $this->project->addBuildListener($this);
        }
    }

    /**
     * Get the project associated with this recorder entry.
     */
    public function getProject()
    {
        return $this->project;
    }

    public function cleanup()
    {
        $this->closeFile();
        if ($this->project != null) {
            $this->project->removeBuildListener($this);
        }
        $this->project = null;
    }

    /**
     * Initially opens the file associated with this recorder.
     * Used by Recorder.
     * @param bool $append Indicates if output must be appended to the logfile or that
     * the logfile should be overwritten.
     * @throws BuildException
     */
    public function openFile($append)
    {
        $this->openFileImpl($append);
    }

    /**
     * Closes the file associated with this recorder.
     * Used by Recorder.
     */
    public function closeFile()
    {
        if ($this->out != null) {
            $this->out->close();
            $this->out = null;
        }
    }

    /**
     * Re-opens the file associated with this recorder.
     * Used by Recorder.
     * @throws BuildException
     */
    public function reopenFile()
    {
        $this->openFileImpl(true);
    }

    private function openFileImpl($append)
    {
        if ($this->out == null) {
            try {
                $this->out = new FileOutputStream($this->filename, $append);
            } catch (IOException $ioe) {
                throw new BuildException("Problems opening file using a recorder entry", $ioe);
            }
        }
    }
}
