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
 * @version SVN: $Id: 2e4cf5659024930ba454c239c5e288e2fd7dab37 $
 * @package phing.tasks.ext.pdo
 */

require_once 'phing/tasks/ext/pdo/PDOQuerySplitter.php';

/**
 * Splits PostgreSQL's dialect of SQL into separate queries
 *
 * Unlike DefaultPDOQuerySplitter this uses a lexer instead of regular
 * expressions. This allows handling complex constructs like C-style comments
 * (including nested ones) and dollar-quoted strings.
 *
 * @author  Alexey Borzov <avb@php.net>
 * @package phing.tasks.ext.pdo
 * @version $Id: 2e4cf5659024930ba454c239c5e288e2fd7dab37 $
 * @link    http://www.phing.info/trac/ticket/499
 * @link    http://www.postgresql.org/docs/current/interactive/sql-syntax-lexical.html#SQL-SYNTAX-DOLLAR-QUOTING
 */
class PgsqlPDOQuerySplitter extends PDOQuerySplitter
{
    /**#@+
     * Lexer states
     */
    const STATE_NORMAL = 0;
    const STATE_SINGLE_QUOTED = 1;
    const STATE_DOUBLE_QUOTED = 2;
    const STATE_DOLLAR_QUOTED = 3;
    const STATE_COMMENT_LINEEND = 4;
    const STATE_COMMENT_MULTILINE = 5;
    const STATE_BACKSLASH = 6;
    /**#@-*/

    /**
     * Nesting depth of current multiline comment
     * @var int
     */
    protected $commentDepth = 0;

    /**
     * Current dollar-quoting "tag"
     * @var string
     */
    protected $quotingTag = '';

    /**
     * Current lexer state, one of STATE_* constants
     * @var int
     */
    protected $state = self::STATE_NORMAL;

    /**
     * Whether a backslash was just encountered in quoted string
     * @var bool
     */
    protected $escape = false;

    /**
     * Current source line being examined
     * @var string
     */
    protected $line = '';

    /**
     * Position in current source line
     * @var int
     */
    protected $inputIndex;

    /**
     * Gets next symbol from the input, false if at end
     *
     * @return string|bool
     */
    public function getc()
    {
        if (!strlen($this->line) || $this->inputIndex >= strlen($this->line)) {
            if (null === ($line = $this->sqlReader->readLine())) {
                return false;
            }
            $project = $this->parent->getOwningTarget()->getProject();
            $this->line = ProjectConfigurator::replaceProperties(
                    $project,
                    $line,
                    $project->getProperties()
                ) . "\n";
            $this->inputIndex = 0;
        }

        return $this->line[$this->inputIndex++];
    }

    /**
     * Bactracks one symbol on the input
     *
     * NB: we don't need ungetc() at the start of the line, so this case is
     * not handled.
     */
    public function ungetc()
    {
        $this->inputIndex--;
    }

    /**
     * Checks whether symbols after $ are a valid dollar-quoting tag
     *
     * @return string|bool Dollar-quoting "tag" if it is present, false otherwise
     */
    protected function checkDollarQuote()
    {
        $ch = $this->getc();
        if ('$' == $ch) {
            // empty tag
            return '';

        } elseif (!ctype_alpha($ch) && '_' != $ch) {
            // not a delimiter
            $this->ungetc();

            return false;

        } else {
            $tag = $ch;
            while (false !== ($ch = $this->getc())) {
                if ('$' == $ch) {
                    return $tag;

                } elseif (ctype_alnum($ch) || '_' == $ch) {
                    $tag .= $ch;

                } else {
                    for ($i = 0; $i < strlen($tag); $i++) {
                        $this->ungetc();
                    }

                    return false;
                }
            }
        }
    }

    /**
     * @return null|string
     */
    public function nextQuery()
    {
        $sql = '';
        $delimiter = $this->parent->getDelimiter();
        $openParens = 0;

        while (false !== ($ch = $this->getc())) {
            switch ($this->state) {
                case self::STATE_NORMAL:
                    switch ($ch) {
                        case '-':
                            if ('-' == $this->getc()) {
                                $this->state = self::STATE_COMMENT_LINEEND;
                            } else {
                                $this->ungetc();
                            }
                            break;
                        case '"':
                            $this->state = self::STATE_DOUBLE_QUOTED;
                            break;
                        case "'":
                            $this->state = self::STATE_SINGLE_QUOTED;
                            break;
                        case '/':
                            if ('*' == $this->getc()) {
                                $this->state = self::STATE_COMMENT_MULTILINE;
                                $this->commentDepth = 1;
                            } else {
                                $this->ungetc();
                            }
                            break;
                        case '$':
                            if (false !== ($tag = $this->checkDollarQuote())) {
                                $this->state = self::STATE_DOLLAR_QUOTED;
                                $this->quotingTag = $tag;
                                $sql .= '$' . $tag . '$';
                                continue 3;
                            }
                            break;
                        case '(':
                            $openParens++;
                            break;
                        case ')':
                            $openParens--;
                            break;
                        // technically we can use e.g. psql's \g command as delimiter
                        case $delimiter[0]:
                            // special case to allow "create rule" statements
                            // http://www.postgresql.org/docs/current/interactive/sql-createrule.html
                            if (';' == $delimiter && 0 < $openParens) {
                                break;
                            }
                            $hasQuery = true;
                            for ($i = 1; $i < strlen($delimiter); $i++) {
                                if ($delimiter[$i] != $this->getc()) {
                                    $hasQuery = false;
                                }
                            }
                            if ($hasQuery) {
                                return $sql;
                            } else {
                                for ($j = 1; $j < $i; $j++) {
                                    $this->ungetc();
                                }
                            }
                    }
                    break;

                case self::STATE_COMMENT_LINEEND:
                    if ("\n" == $ch) {
                        $this->state = self::STATE_NORMAL;
                    }
                    break;

                case self::STATE_COMMENT_MULTILINE:
                    switch ($ch) {
                        case '/':
                            if ('*' != $this->getc()) {
                                $this->ungetc();
                            } else {
                                $this->commentDepth++;
                            }
                            break;

                        case '*':
                            if ('/' != $this->getc()) {
                                $this->ungetc();
                            } else {
                                $this->commentDepth--;
                                if (0 == $this->commentDepth) {
                                    $this->state = self::STATE_NORMAL;
                                    continue 3;
                                }
                            }
                    }

                case self::STATE_SINGLE_QUOTED:
                case self::STATE_DOUBLE_QUOTED:
                    if ($this->escape) {
                        $this->escape = false;
                        break;
                    }
                    $quote = $this->state == self::STATE_SINGLE_QUOTED ? "'" : '"';
                    switch ($ch) {
                        case '\\':
                            $this->escape = true;
                            break;
                        case $quote:
                            if ($quote == $this->getc()) {
                                $sql .= $quote;
                            } else {
                                $this->ungetc();
                                $this->state = self::STATE_NORMAL;
                            }
                    }

                case self::STATE_DOLLAR_QUOTED:
                    if ('$' == $ch && false !== ($tag = $this->checkDollarQuote())) {
                        if ($tag == $this->quotingTag) {
                            $this->state = self::STATE_NORMAL;
                        }
                        $sql .= '$' . $tag . '$';
                        continue 2;
                    }
            }

            if ($this->state != self::STATE_COMMENT_LINEEND && $this->state != self::STATE_COMMENT_MULTILINE) {
                $sql .= $ch;
            }
        }
        if ('' !== $sql) {
            return $sql;
        }

        return null;
    }
}
