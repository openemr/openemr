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
 *
 * @version $Id: 918ec6d58131d6aea966b154be71a83c27e94b7c $
 * @package phing.tasks.ext.pdo
 */

/**
 * Base class for classes that split SQL source into separate queries
 *
 * @author  Alexey Borzov <avb@php.net>
 * @package phing.tasks.ext.pdo
 * @version $Id: 918ec6d58131d6aea966b154be71a83c27e94b7c $
 */
abstract class PDOQuerySplitter
{
    /**
     * Task that uses the splitter
     * @var PDOSQLExecTask
     */
    protected $parent;

    /**
     * Reader with SQL source
     * @var BufferedReader
     */
    protected $sqlReader;

    /**
     * Constructor, sets the parent task and reader with SQL source
     *
     * @param PDOSQLExecTask $parent
     * @param Reader         $reader
     */
    public function __construct(PDOSQLExecTask $parent, Reader $reader)
    {
        $this->parent = $parent;
        $this->sqlReader = new BufferedReader($reader);
    }

    /**
     * Returns next query from SQL source, null if no more queries left
     *
     * @return string|null
     */
    abstract public function nextQuery();
}
