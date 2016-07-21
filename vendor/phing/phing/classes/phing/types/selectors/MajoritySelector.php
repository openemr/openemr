<?php

/*
 * $Id: acddb2aa573b441a7b734797950b89d1a6ffed1a $
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
 * This selector is here just to shake up your thinking a bit. Don't get
 * too caught up in boolean, there are other ways you can evaluate a
 * collection of selectors. This one takes a vote of the selectors it
 * contains, and majority wins. You could also have an "all-but-one"
 * selector, a "weighted-average" selector, and so on. These are left
 * as exercises for the reader (as are the usecases where this would
 * be necessary).
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Bruce Atherton <bruce@callenish.com> (Ant)
 * @package phing.types.selectors
 */
class MajoritySelector extends BaseSelectorContainer
{

    private $allowtie = true;

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "";
        if ($this->hasSelectors()) {
            $buf .= "{majorityselect: ";
            $buf .= parent::toString();
            $buf .= "}";
        }

        return $buf;
    }

    /**
     * @param $tiebreaker
     */
    public function setAllowtie($tiebreaker)
    {
        $this->allowtie = $tiebreaker;
    }

    /**
     * Returns true (the file is selected) if most of the other selectors
     * agree. In case of a tie, go by the allowtie setting. That defaults
     * to true, meaning in case of a tie, the file is selected.
     *
     * @param PhingFile $basedir the base directory the scan is being done from
     * @param string $filename is the name of the file to check
     * @param PhingFile $file is a PhingFile object for the filename that the selector
     * can use
     * @return whether the file should be selected or not
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {

        $this->validate();

        $yesvotes = 0;
        $novotes = 0;

        $selectors = $this->selectorElements();
        for ($i = 0, $size = count($selectors); $i < $size; $i++) {
            $result = $selectors[$i]->isSelected($basedir, $filename, $file);
            if ($result) {
                $yesvotes = $yesvotes + 1;
            } else {
                $novotes = $novotes + 1;
            }
        }
        if ($yesvotes > $novotes) {
            return true;
        } else {
            if ($novotes > $yesvotes) {
                return false;
            }
        }
        // At this point, we know we have a tie.
        return $this->allowtie;
    }
}
