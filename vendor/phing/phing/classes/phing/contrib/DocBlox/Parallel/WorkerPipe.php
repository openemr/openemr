<?php
/**
 * DocBlox
 *
 * PHP Version 5
 *
 * @category  DocBlox
 * @package   Parallel
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://docblox-project.org
 */

/**
 * Class that represents a named pipe for a Worker.
 *
 * This class manages the named pipe for a worker and is able to push and pull
 * specific data to facilitate IPC (interprocess communication).
 *
 * @category DocBlox
 * @package  Parallel
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://docblox-project.org
 */
class DocBlox_Parallel_WorkerPipe
{
    /** @var DocBlox_Parallel_Worker worker class that is associated */
    protected $worker;

    /** @var string Path to the pipe */
    protected $path;

    /**
     * Initializes the named pipe.
     *
     * @param DocBlox_Parallel_Worker $worker Associated worker.
     */
    public function __construct(DocBlox_Parallel_Worker $worker)
    {
        $this->worker = $worker;

        $this->path = tempnam(sys_get_temp_dir(), 'dpm_');
        posix_mkfifo($this->path, 0750);
    }

    /**
     * If the named pipe was not cleaned up, do so now.
     */
    public function __destruct()
    {
        if (file_exists($this->path)) {
            $this->release();
        }
    }

    /**
     * Pull the worker data into the named pipe.
     *
     * @return void
     */
    public function pull()
    {
        $this->writePipeContents();
    }

    /**
     * Push the worker data back onto the worker and release the pipe.
     *
     * @return void
     */
    public function push()
    {
        list($result, $error, $return_code) = $this->readPipeContents();
        $this->release();

        $this->worker->setResult($result);
        $this->worker->setError($error);
        $this->worker->setReturnCode($return_code);
    }

    /**
     * Convenience method to show relation to readPipeContents.
     *
     * @return void
     */
    protected function writePipeContents()
    {
        // push the gathered data onto a name pipe
        $pipe = fopen($this->path, 'w');
        fwrite(
            $pipe,
            serialize(
                array(
                    $this->worker->getResult(),
                    $this->worker->getError(),
                    $this->worker->getReturnCode()
                )
            )
        );
        fclose($pipe);
    }

    /**
     * Returns the unserialized contents of the pipe.
     *
     * @return array
     */
    protected function readPipeContents()
    {
        $pipe = @fopen($this->path, 'r+');

        if (! $pipe) {
            $arguments = $this->worker->getArguments();
            return array(
                '',
                'Worker died unexpectedly',
                255
            );
        }

        $result = unserialize(fread($pipe, filesize($this->path)));
        fclose($pipe);

        return $result;
    }

    /**
     * Releases the pipe.
     *
     * @return void
     */
    protected function release()
    {
        @unlink($this->path);
    }
}
