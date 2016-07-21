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
 * Class that represents the execution of a single task within a parallelized
 * frame.
 *
 * @category DocBlox
 * @package  Parallel
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://docblox-project.org
 */
class DocBlox_Parallel_Worker
{
    /** @var callback the task to execute for this worker */
    protected $task = null;

    /** @var mixed[] A list of argument to pass to the task */
    protected $arguments = array();

    /** @var int The return code to tell the parent process how it went */
    protected $return_code = -1;

    /** @var mixed The result of the given task */
    protected $result = '';

    /** @var string The error message, if an error occurred */
    protected $error = '';

    /**
     * Creates the worker and sets the task to execute optionally including
     * the arguments that need to be passed to the task.
     *
     * @param callback $task      The task to invoke upon execution.
     * @param mixed[]  $arguments The arguments to provide to the task.
     */
    public function __construct($task, array $arguments = array())
    {
        $this->setTask($task);
        $this->arguments = $arguments;
    }

    /**
     * Returns the list of arguments as provided in the constructor.
     *
     * @see DocBlox_Parallel_Worker::__construct()
     *
     * @return mixed[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Returns the task as provided in the constructor.
     *
     * @see DocBlox_Parallel_Worker::__construct()
     *
     * @return callback
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Returns the available return code.
     *
     * This method may return -1 if no return code is available yet.
     *
     * @return int
     */
    public function getReturnCode()
    {
        return $this->return_code;
    }

    /**
     * Sets the return code for this worker.
     *
     * Recommended is to use the same codes as are used with
     * {@link http://www.gnu.org/software/bash/manual/html_node/Exit-Status.html
     * exit codes}.
     *
     * In short: 0 means that the task succeeded and a any other positive value
     * indicates an error condition.
     *
     * @param int $return_code Recommended to be a positive number
     *
     * @throw InvalidArgumentException if the code is not a number or negative
     *
     * @return void
     */
    public function setReturnCode($return_code)
    {
        if (!is_numeric($return_code) || ($return_code < 0)) {
            throw new InvalidArgumentException(
                'Expected the return code to be a positive number'
            );
        }

        $this->return_code = $return_code;
    }

    /**
     * Returns the error message associated with the return code.
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Sets the error message.
     *
     * @param string $error The error message.
     *
     * @return void
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Returns the result for this task run.
     *
     * @return null|mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Sets the result for this task run.
     *
     * @param mixed $result The value that is returned by the task; can be anything.
     *
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * Invokes the task with the given arguments and processes the output.
     *
     * @return void.
     */
    public function execute()
    {
        $this->setReturnCode(0);
        try {
            $this->setResult(
                call_user_func_array($this->getTask(), $this->getArguments())
            );
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            $this->setReturnCode($e->getCode());
        }
    }

    /**
     * Sets the task for this worker and validates whether it is callable.
     *
     * @param callback $task The task to execute when the execute method
     *                       is invoked.
     *
     * @throws InvalidArgumentException if the given argument is not a callback.
     *
     * @see DocBlox_Parallel_Worker::__construct()
     * @see DocBlox_Parallel_Worker::execute()
     *
     * @return void
     */
    protected function setTask($task)
    {
        if (!is_callable($task)) {
            throw new InvalidArgumentException(
                'Worker task is not a callable object'
            );
        }

        $this->task = $task;
    }
}
