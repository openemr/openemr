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
 * Manager class for Parallel processes.
 *
 * This class will manage the workers and make sure all processes are executed
 * in parallel and not too many at the same time.
 *
 * @category DocBlox
 * @package  Parallel
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://docblox-project.org
 */
class DocBlox_Parallel_Manager extends ArrayObject
{
    /** @var int The maximum number of processes to run simultaneously */
    protected $process_limit = 2;

    /** @var boolean Tracks whether this manager is currently executing */
    protected $is_running = false;

    /**
     * Tries to autodetect the optimal number of process by counting the number
     * of processors.
     *
     * @param array  $input          Input for the array object.
     * @param int    $flags          flags for the array object.
     * @param string $iterator_class Iterator class for this array object.
     */
    public function __construct(
        $input = array(),
        $flags = 0,
        $iterator_class = "ArrayIterator"
    ) {
        parent::__construct($input, $flags, $iterator_class);

        if (is_readable('/proc/cpuinfo')) {
            $processors = 0;
            exec("cat /proc/cpuinfo | grep processor | wc -l", $processors);
            $this->setProcessLimit(reset($processors));
        }
    }

    /**
     * Adds a worker to the queue.
     *
     * This method will prepare a worker to be executed in parallel once the
     * execute method is invoked.
     * A fluent interface is provided so that you can chain multiple workers
     * in one call.
     *
     * Example:
     *
     *    $cb1 = function () { var_dump('a'); sleep(1); };
     *    $cb2 = function () { var_dump('b'); sleep(1); };
     *
     *    $mgr = new DocBlox_Parallel_Manager();
     *    $mgr->setProcessLimit(2)
     *        ->addWorker(new DocBlox_Parallel_Worker($cb1))
     *        ->addWorker(new DocBlox_Parallel_Worker($cb2))
     *        ->execute();
     *
     * @param int                     $index  The key for this worker.
     * @param DocBlox_Parallel_Worker $newval The worker to add onto the queue.
     *
     * @see DocBlox_Parallel_Manager::execute()
     *
     * @throws RuntimeException         if this method is invoked while the
     *                                  manager is busy executing tasks.
     * @throws InvalidArgumentException if the provided element is not of type
     *                                  DocBlox_Parallel_Worker.
     *
     * @return void
     */
    public function offsetSet($index, $newval)
    {
        if (!$newval instanceof DocBlox_Parallel_Worker) {
            throw new InvalidArgumentException(
                'Provided element must be of type DocBlox_Parallel_Worker'
            );
        }
        if ($this->isRunning()) {
            throw new RuntimeException(
                'Workers may not be added during execution of the manager'
            );
        }

        parent::offsetSet($index, $newval);
    }

    /**
     * Convenience method to make the addition of workers explicit and allow a
     * fluent interface.
     *
     * @param DocBlox_Parallel_Worker $worker The worker to add onto the queue.
     *
     * @return self
     */
    public function addWorker(DocBlox_Parallel_Worker $worker)
    {
        $this[] = $worker;

        return $this;
    }

    /**
     * Sets how many processes at most to execute at the same time.
     *
     * A fluent interface is provided so that you can chain multiple workers
     * in one call.
     *
     * @param int $process_limit The limit, minimum of 1
     *
     * @see DocBlox_Parallel_Manager::addWorker() for an example
     *
     * @return self
     */
    public function setProcessLimit($process_limit)
    {
        if ($process_limit < 1) {
            throw new InvalidArgumentException(
                'Number of simultaneous processes may not be less than 1'
            );
        }

        $this->process_limit = $process_limit;

        return $this;
    }

    /**
     * Returns the current limit on the amount of processes that can be
     * executed at the same time.
     *
     * @return int
     */
    public function getProcessLimit()
    {
        return $this->process_limit;
    }

    /**
     * Returns whether the manager is executing the workers.
     *
     * @return boolean
     */
    public function isRunning()
    {
        return $this->is_running;
    }

    /**
     * Executes each worker.
     *
     * This method loops through the list of workers and tries to fork as
     * many times as the ProcessLimit dictates at the same time.
     *
     * @return void
     */
    public function execute()
    {
        /** @var int[] $processes */
        $processes = $this->startExecution();

        /** @var DocBlox_Parallel_Worker $worker */
        foreach ($this as $worker) {

            // if requirements are not met, execute workers in series.
            if (!$this->checkRequirements()) {
                $worker->execute();
                continue;
            }

            $this->forkAndRun($worker, $processes);
        }

        $this->stopExecution($processes);
    }

    /**
     * Notifies manager that execution has started, checks requirements and
     * returns array for child processes.
     *
     * If forking is not available because library requirements are not met
     * than the list of workers is processed in series and a E_USER_NOTICE is
     * triggered.
     *
     * @return int[]
     */
    protected function startExecution()
    {
        $this->is_running = true;

        // throw a E_USER_NOTICE if the requirements are not met.
        if (!$this->checkRequirements()) {
            trigger_error(
                'The PCNTL extension is not available, running workers in series '
                . 'instead of parallel',
                E_USER_NOTICE
            );
        }

        return array();
    }

    /**
     * Waits for all processes to have finished and notifies the manager that
     * execution has stopped.
     *
     * @param int[] &$processes List of running processes.
     *
     * @return void
     */
    protected function stopExecution(array &$processes)
    {
        // starting of processes has ended but some processes might still be
        // running wait for them to finish
        while (!empty($processes)) {
            pcntl_waitpid(array_shift($processes), $status);
        }

        /** @var DocBlox_Parallel_Worker $worker */
        foreach ($this as $worker) {
            $worker->pipe->push();
        }

        $this->is_running = false;
    }

    /**
     * Forks the current process and calls the Worker's execute method OR
     * handles the parent process' execution.
     *
     * This is the really tricky part of the forking mechanism. Here we invoke
     * {@link http://www.php.net/manual/en/function.pcntl-fork.php pcntl_fork}
     * and either execute the forked process or deal with the parent's process
     * based on in which process we are.
     *
     * To fully understand what is going on here it is recommended to read the
     * PHP manual page on
     * {@link http://www.php.net/manual/en/function.pcntl-fork.php pcntl_fork}
     * and associated articles.
     *
     * If there are more workers than may be ran simultaneously then this method
     * will wait until a slot becomes available and then starts the next worker.
     *
     * @param DocBlox_Parallel_Worker $worker     The worker to process.
     * @param int[]                   &$processes The list of running processes.
     *
     * @throws RuntimeException if we are unable to fork.
     *
     * @return void
     */
    protected function forkAndRun(
        DocBlox_Parallel_Worker $worker,
        array &$processes
    ) {
        $worker->pipe = new DocBlox_Parallel_WorkerPipe($worker);

        // fork the process and register the PID
        $pid = pcntl_fork();

        switch ($pid) {
            case -1:
                throw new RuntimeException('Unable to establish a fork');
            case 0: // Child process
                $worker->execute();

                $worker->pipe->pull();

                // Kill -9 this process to prevent closing of shared file handlers.
                // Not doing this causes, for example, MySQL connections to be cleaned.
                posix_kill(getmypid(), SIGKILL);
            default: // Parent process
                // Keep track if the worker children
                $processes[] = $pid;

                if (count($processes) >= $this->getProcessLimit()) {
                    pcntl_waitpid(array_shift($processes), $status);
                }
                break;
        }
    }

    /**
     * Returns true when all requirements are met.
     *
     * @return bool
     */
    protected function checkRequirements()
    {
        return (bool) (extension_loaded('pcntl'));
    }
}
