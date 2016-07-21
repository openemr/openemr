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
 * @version SVN: $Id: 43afd902ddb58980b64121603ad61d308ca88975 $
 * @package phing.tasks.ext.pdo
 */

require_once 'phing/tasks/ext/pdo/PDOQuerySplitter.php';

/**
 * Splits SQL source into queries using simple regular expressions
 *
 * Extracted from PDOSQLExecTask::runStatements()
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @author  Alexey Borzov <avb@php.net>
 * @package phing.tasks.ext.pdo
 * @version $Id: 43afd902ddb58980b64121603ad61d308ca88975 $
 */
class DefaultPDOQuerySplitter extends PDOQuerySplitter
{
    /**
     * Delimiter type, one of PDOSQLExecTask::DELIM_ROW or PDOSQLExecTask::DELIM_NORMAL
     * @var string
     */
    private $delimiterType;

    /**
     * Leftover SQL from previous line
     * @var string
     */
    private $sqlBacklog = '';

    /**
     * Constructor, sets the parent task, reader with SQL source and delimiter type
     *
     * @param PDOSQLExecTask $parent
     * @param Reader         $reader
     * @param string         $delimiterType
     */
    public function __construct(PDOSQLExecTask $parent, Reader $reader, $delimiterType = PDOSQLExecTask::DELIM_NORMAL)
    {
        parent::__construct($parent, $reader);
        $this->delimiterType = $delimiterType;
    }

    /**
     * Returns next query from SQL source, null if no more queries left
     *
     * In case of "row" delimiter type this searches for strings containing only
     * delimiters. In case of "normal" delimiter type, this uses simple regular
     * expression logic to search for delimiters.
     *
     * @return string|null
     */
    public function nextQuery()
    {
        $sql = "";
        $hasQuery = false;

        while (($line = $this->sqlReader->readLine()) !== null) {
            $delimiter = $this->parent->getDelimiter();
            $project = $this->parent->getOwningTarget()->getProject();
            $line = ProjectConfigurator::replaceProperties(
                $project,
                trim($line),
                $project->getProperties()
            );

            if (($line != $delimiter) && (
                    StringHelper::startsWith("//", $line) ||
                    StringHelper::startsWith("--", $line) ||
                    StringHelper::startsWith("#", $line))
            ) {
                continue;
            }

            if (strlen($line) > 4
                && strtoupper(substr($line, 0, 4)) == "REM "
            ) {
                continue;
            }

            // MySQL supports defining new delimiters
            if (preg_match('/DELIMITER [\'"]?([^\'" $]+)[\'"]?/i', $line, $matches)) {
                $this->parent->setDelimiter($matches[1]);
                continue;
            }

            if ($this->sqlBacklog !== "") {
                $sql = $this->sqlBacklog;
                $this->sqlBacklog = "";
            }

            $sql .= " " . $line . "\n";

            // SQL defines "--" as a comment to EOL
            // and in Oracle it may contain a hint
            // so we cannot just remove it, instead we must end it
            if (strpos($line, "--") !== false) {
                $sql .= "\n";
            }

            // DELIM_ROW doesn't need this (as far as i can tell)
            if ($this->delimiterType == PDOSQLExecTask::DELIM_NORMAL) {

                $reg = "#((?:\"(?:\\\\.|[^\"])*\"?)+|'(?:\\\\.|[^'])*'?|" . preg_quote($delimiter) . ")#";

                $sqlParts = preg_split($reg, $sql, 0, PREG_SPLIT_DELIM_CAPTURE);
                $this->sqlBacklog = "";
                foreach ($sqlParts as $sqlPart) {
                    // we always want to append, even if it's a delim (which will be stripped off later)
                    $this->sqlBacklog .= $sqlPart;

                    // we found a single (not enclosed by ' or ") delimiter, so we can use all stuff before the delim as the actual query
                    if ($sqlPart === $delimiter) {
                        $sql = $this->sqlBacklog;
                        $this->sqlBacklog = "";
                        $hasQuery = true;
                    }
                }
            }

            if ($hasQuery || ($this->delimiterType == PDOSQLExecTask::DELIM_ROW && $line == $delimiter)) {
                // this assumes there is always a delimter on the end of the SQL statement.
                $sql = StringHelper::substring(
                    $sql,
                    0,
                    strlen($sql) - strlen($delimiter)
                    - ($this->delimiterType == PDOSQLExecTask::DELIM_ROW ? 2 : 1)
                );

                return $sql;
            }
        }

        // Catch any statements not followed by ;
        if ($sql !== "") {
            return $sql;
        }

        return null;
    }
}
