<?php
/*
 * $Id: 2fc86a029661c59a2ca1088fd26199d2c4764f73 $
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

/**
 * FileSet adapter to SPL's Iterator.
 *
 * @package phing.types
 * @author Alexey Shockov <alexey@shockov.com>
 * @since 2.4.0
 * @internal
 */
class IterableFileSet
    extends FileSet
    implements IteratorAggregate
{
    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getFiles());
    }

    /**
     * @return array
     */
    private function getFiles()
    {
        $directoryScanner = $this->getDirectoryScanner($this->getProject());
        $files = $directoryScanner->getIncludedFiles();

        $baseDirectory = $directoryScanner->getBasedir();
        foreach ($files as $index => $file) {
            $files[$index] = realpath($baseDirectory . '/' . $file);
        }

        return $files;
    }
}
