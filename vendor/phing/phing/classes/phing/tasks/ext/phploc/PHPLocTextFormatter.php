<?php
/**
 * $Id: d9e8d9450dcecf7f3691236655b802512460618b $
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

require_once 'phing/tasks/ext/phploc/AbstractPHPLocFormatter.php';

/**
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: d9e8d9450dcecf7f3691236655b802512460618b $
 * @package phing.tasks.ext.phploc
 */
class PHPLocTextFormatter extends AbstractPHPLocFormatter
{
    public function printResult(array $count, $countTests = false)
    {
        if ($this->getUseFile()) {
            $outputClass = '\\Symfony\\Component\\Console\\Output\\StreamOutput';
            $stream = fopen($this->getToDir() . DIRECTORY_SEPARATOR . $this->getOutfile(), 'a+');
            $output = new $outputClass($stream);
        } else {
            $outputClass = '\\Symfony\\Component\\Console\\Output\\ConsoleOutput';
            $output = new $outputClass();
        }

        $printerClass = '\\SebastianBergmann\\PHPLOC\\Log\\Text';
        $printer = new $printerClass();
        $printer->printResult($output, $count, $countTests);
    }
}
