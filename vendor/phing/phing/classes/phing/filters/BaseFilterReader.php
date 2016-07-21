<?php

/*
 *  $Id: 85cbd982d211b5d05761bd3043d2583b890bdc64 $
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

include_once 'phing/system/io/FilterReader.php';
include_once 'phing/system/io/StringReader.php';

/**
 * Base class for core filter readers.
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @version   $Id: 85cbd982d211b5d05761bd3043d2583b890bdc64 $
 * @see       FilterReader
 * @package   phing.filters
 */
class BaseFilterReader extends FilterReader
{

    /** Have the parameters passed been interpreted? */
    protected $initialized = false;

    /** The Phing project this filter is part of. */
    protected $project = null;

    /**
     * Constructor used by Phing's introspection mechanism.
     * The original filter reader is only used for chaining
     * purposes, never for filtering purposes (and indeed
     * it would be useless for filtering purposes, as it has
     * no real data to filter). ChainedReaderHelper uses
     * this placeholder instance to create a chain of real filters.
     *
     * @param Reader $in
     */
    public function __construct($in = null)
    {
        if ($in === null) {
            $dummy = "";
            $in = new StringReader($dummy);
        }
        parent::__construct($in);
    }

    /**
     * Returns the initialized status.
     *
     * @return boolean whether or not the filter is initialized
     */
    public function getInitialized()
    {
        return $this->initialized;
    }

    /**
     * Sets the initialized status.
     *
     * @param boolean $initialized Whether or not the filter is initialized.
     */
    public function setInitialized($initialized)
    {
        $this->initialized = (boolean) $initialized;
    }

    /**
     * Sets the project to work with.
     *
     * @param object|Project $project The project this filter is part of.
     *                        Should not be <code>null</code>.
     */
    public function setProject(Project $project)
    {
        // type check, error must never occur, bad code of it does
        $this->project = $project;
    }

    /**
     * Returns the project this filter is part of.
     *
     * @return object The project this filter is part of
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Reads characters.
     *
     * @param  int $len  Maximum number of characters to read.
     *
     * @return string Characters read, or -1 if the end of the stream
     *                    has been reached
     *
     * @throws IOException If an I/O error occurs
     */
    public function read($len = null)
    {
        return $this->in->read($len);
    }

    /**
     * Reads a line of text ending with '\n' (or until the end of the stream).
     * The returned String retains the '\n'.
     *
     * @return string the line read, or <code>null</code> if the end of the
     *             stream has already been reached
     *
     * @throws IOException if the underlying reader throws one during
     *                     reading
     */
    public function readLine()
    {
        $line = null;

        while (($ch = $this->in->read(1)) !== -1) {
            $line .= $ch;
            if ($ch === "\n") {
                break;
            }
        }

        return $line;
    }

    /**
     * Returns whether the end of file has been reached with input stream.
     *
     * @return boolean
     */
    public function eof()
    {
        return $this->in->eof();
    }

    /**
     * Convenience method to support logging in filters.
     *
     * @param string $msg   Message to log.
     * @param int    $level Priority level.
     *
     * @return void
     */
    public function log($msg, $level = Project::MSG_INFO)
    {
        if ($this->project !== null) {
            $this->project->log("[filter:" . get_class($this) . "] " . $msg, $level);
        }
    }
}
