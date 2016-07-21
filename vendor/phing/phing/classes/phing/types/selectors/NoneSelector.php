<?php
/*
 * $Id: fd4957709aa83a67caa6cc34ffafeeae062d0625 $
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

require_once 'phing/types/selectors/BaseSelectorContainer.php';

/**
 * This selector has a collection of other selectors. All of those selectors
 * must refuse to select a file before the file is considered selected by
 * this selector.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @author Bruce Atherton <bruce@callenish.com> (Ant)
 * @package phing.types.selectors
 */
class NoneSelector extends BaseSelectorContainer
{

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "";
        if ($this->hasSelectors()) {
            $buf .= "{noneselect: ";
            $buf .= parent::toString();
            $buf .= "}";
        }

        return $buf;
    }

    /**
     * Returns true (the file is selected) only if all other selectors
     * agree that the file should not be selected.
     *
     * @param PhingFile $basedir the base directory the scan is being done from
     * @param string $filename is the name of the file to check
     * @param PhingFile $file is a java.io.File object for the filename that the selector
     * can use
     * @return bool whether the file should be selected or not
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {

        $this->validate();

        $selectors = $this->selectorElements();

        for ($i = 0, $size = count($selectors); $i < $size; $i++) {
            $result = $selectors[$i]->isSelected($basedir, $filename, $file);
            if ($result) {
                return false;
            }
        }

        return true;
    }

}
