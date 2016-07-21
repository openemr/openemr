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
 * @version $Id: 9e64e7e9e66982fa11e85ccde7b9ab5756076872 $
 * @package phing.tasks.ext.pdo
 */

require_once 'phing/tasks/ext/pdo/PDOQuerySplitter.php';

/**
 * Dummy query splitter: converts entire input into single
 * SQL string
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.pdo
 * @version $Id: 9e64e7e9e66982fa11e85ccde7b9ab5756076872 $
 */
class DummyPDOQuerySplitter extends PDOQuerySplitter
{
    /**
     * Returns entire SQL source
     *
     * @return string|null
     */
    public function nextQuery()
    {
        $sql = null;

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

            $sql .= " " . $line . "\n";

            /**
             * fix issue with PDO and wrong formated multistatements
             * @issue 1108
             */
            if (StringHelper::endsWith($delimiter, $line)) {
                break;
            }

        }

        return $sql;
    }
}
