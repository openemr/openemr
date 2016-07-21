<?php

/*
 *  $Id: 811cc87eb5f11dc6d7833b1fc89a5193ceb268dd $
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
require_once 'phing/TaskContainer.php';

/**
 * Sequential is a container task that contains other Phing Task objects.
 *
 * The sequential task has no attributes and does not support any nested
 * elements apart from Ant tasks. Any valid Ant task may be embedded within the
 * sequential task.
 *
 * @since 2.1.2
 * @package phing.tasks.system
 */
class SequentialTask extends Task implements TaskContainer
{

    /** Optional Vector holding the nested tasks */
    protected $nestedTasks = array();

    /**
     * Add a nested task to Sequential.
     * @param Task $nestedTask Nested task to execute Sequential
     */
    public function addTask(Task $nestedTask)
    {
        $this->nestedTasks[] = $nestedTask;
    }

    /**
     * Execute all nestedTasks.
     * @throws BuildException if one of the nested tasks fails.
     */
    public function main()
    {
        foreach ($this->nestedTasks as $task) {
            $task->perform();
        }
    }
}
