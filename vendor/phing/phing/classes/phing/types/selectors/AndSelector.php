<?php
/*
 * $Id: a48248c2ea6319b26b375825aa437e5b1a9e7f78 $
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
 * This selector has a collection of other selectors, all of which have to
 * select a file in order for this selector to select it.
 *
 * @author Hans Lellelid, hans@xmpl.org (Phing)
 * @author <a href="mailto:bruce@callenish.com">Bruce Atherton</a> (Ant)
 * @package phing.types.selectors
 */
class AndSelector extends BaseSelectorContainer
{

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "";
        if ($this->hasSelectors()) {
            $buf .= "{andselect: ";
            $buf .= parent::toString();
            $buf .= "}";
        }

        return $buf;
    }

    /**
     * Returns true (the file is selected) only if all other selectors
     * agree that the file should be selected.
     *
     * @param PhingFile $basedir the base directory the scan is being done from
     * @param string $filename the name of the file to check
     * @param PhingFile $file a PhingFile object for the filename that the selector
     * can use
     * @return bool whether the file should be selected or not
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {
        $this->validate();
        $selectors = $this->selectorElements();
        for ($i = 0, $size = count($selectors); $i < $size; $i++) {
            $result = $selectors[$i]->isSelected($basedir, $filename, $file);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

}
