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
 */

include_once 'phing/tasks/system/condition/Condition.php';

/**
 * Compares two files for equality based on size and
 * content. Timestamps are not at all looked at.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system.condition
 */
class FilesMatch implements Condition
{
    /**
     * files to compare
     */
    private $file1, $file2;

    /**
     * Sets the File1 attribute
     *
     * @param PhingFile $file1 The new File1 value
     */
    public function setFile1(PhingFile $file1)
    {
        $this->file1 = $file1;
    }

    /**
     * Sets the File2 attribute
     *
     * @param PhingFile $file2 The new File2 value
     */
    public function setFile2(PhingFile $file2)
    {
        $this->file2 = $file2;
    }

    /**
     * comparison method of the interface
     *
     * @return bool if the files are equal
     * @throws BuildException if it all went pear-shaped
     */
    public function evaluate()
    {
        if ($this->file1 == null || $this->file2 == null) {
            throw new BuildException("both file1 and file2 are required in filesmatch");
        }

        $fu = new FileUtils;

        return $fu->contentEquals($this->file1, $this->file2);
    }
}
