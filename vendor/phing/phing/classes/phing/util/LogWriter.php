<?php

/**
 * $Id: 7f4acf8203b01fce283e1a066159aa51146b6831 $
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

require_once 'phing/system/io/Writer.php';
require_once 'phing/Task.php';

/**
 * Extends the Writer class to output messages to Phing's log
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 7f4acf8203b01fce283e1a066159aa51146b6831 $
 * @package phing.util
 */
class LogWriter extends Writer
{
    private $task = null;

    private $level = null;

    /**
     * Constructs a new LogWriter object
     * @param Task $task
     * @param int $level
     */
    public function __construct(Task $task, $level = Project::MSG_INFO)
    {
        $this->task = $task;
        $this->level = $level;
    }

    /**
     * @see Writer::write()
     * @param string $buf
     * @param null $off
     * @param null $len
     */
    public function write($buf, $off = null, $len = null)
    {
        $lines = explode("\n", $buf);

        foreach ($lines as $line) {
            if ($line == "") {
                continue;
            }

            $this->task->log($line, $this->level);
        }
    }

    /**
     * @see Writer::reset()
     */
    public function reset()
    {
    }

    /**
     * @see Writer::close()
     */
    public function close()
    {
    }

    /**
     * @see Writer::open()
     */
    public function open()
    {
    }

    /**
     * @see Writer::getResource()
     */
    public function getResource()
    {
        return $this->task;
    }
}
