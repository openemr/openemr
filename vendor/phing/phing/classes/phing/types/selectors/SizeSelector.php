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

/**
 * Selector that filters files based on their size.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Bruce Atherton <bruce@callenish.com> (Ant)
 *
 * @package phing.types.selectors
 */
class SizeSelector extends BaseExtendSelector
{
    /** @var int $size */
    private $size = -1;

    /** @var int $multiplier */
    private $multiplier = 1;

    /** @var int $sizelimit */
    private $sizelimit = -1;

    /** @var int $cmp */
    private $cmp = 2;

    const SIZE_KEY = "value";
    const UNITS_KEY = "units";
    const WHEN_KEY = "when";

    /** @var array $sizeComparisons */
    private static $sizeComparisons = array("less", "more", "equal");

    /** @var array $byteUnits */
    private static $byteUnits = array(
        "K",
        "k",
        "kilo",
        "KILO",
        "Ki",
        "KI",
        "ki",
        "kibi",
        "KIBI",
        "M",
        "m",
        "mega",
        "MEGA",
        "Mi",
        "MI",
        "mi",
        "mebi",
        "MEBI",
        "G",
        "g",
        "giga",
        "GIGA",
        "Gi",
        "GI",
        "gi",
        "gibi",
        "GIBI",
        "T",
        "t",
        "tera",
        "TERA",
        /* You wish! */
        "Ti",
        "TI",
        "ti",
        "tebi",
        "TEBI"
    );

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "{sizeselector value: ";
        $buf .= $this->sizelimit;
        $buf .= "compare: ";
        if ($this->cmp === 0) {
            $buf .= "less";
        } elseif ($this->cmp === 1) {
            $buf .= "more";
        } else {
            $buf .= "equal";
        }
        $buf .= "}";

        return $buf;
    }

    /**
     * A size selector needs to know what size to base its selecting on.
     * This will be further modified by the multiplier to get an
     * actual size limit.
     *
     * @param int $size the size to select against expressed in units
     *
     * @return void
     */
    public function setValue($size)
    {
        $this->size = $size;
        if (($this->multiplier !== 0) && ($this->size > -1)) {
            $this->sizelimit = $size * $this->multiplier;
        }
    }

    /**
     * Sets the units to use for the comparison. This is a little
     * complicated because common usage has created standards that
     * play havoc with capitalization rules. Thus, some people will
     * use "K" for indicating 1000's, when the SI standard calls for
     * "k". Others have tried to introduce "K" as a multiple of 1024,
     * but that falls down when you reach "M", since "m" is already
     * defined as 0.001.
     * <p>
     * To get around this complexity, a number of standards bodies
     * have proposed the 2^10 standard, and at least one has adopted
     * it. But we are still left with a populace that isn't clear on
     * how capitalization should work.
     * <p>
     * We therefore ignore capitalization as much as possible.
     * Completely mixed case is not possible, but all upper and lower
     * forms are accepted for all long and short forms. Since we have
     * no need to work with the 0.001 case, this practice works here.
     * <p>
     * This function translates all the long and short forms that a
     * unit prefix can occur in and translates them into a single
     * multiplier.
     *
     * @param array $units The units to compare the size to.
     *
     * @return void
     */
    public function setUnits($units)
    {
        $i = array_search($units, self::$byteUnits, true);
        if ($i === false) {
            $i = -1;
        } // make it java-like

        $this->multiplier = 0;
        if (($i > -1) && ($i < 4)) {
            $this->multiplier = 1000;
        } elseif (($i > 3) && ($i < 9)) {
            $this->multiplier = 1024;
        } elseif (($i > 8) && ($i < 13)) {
            $this->multiplier = 1000000;
        } elseif (($i > 12) && ($i < 18)) {
            $this->multiplier = 1048576;
        } elseif (($i > 17) && ($i < 22)) {
            $this->multiplier = 1000000000;
        } elseif (($i > 21) && ($i < 27)) {
            $this->multiplier = 1073741824;
        } elseif (($i > 26) && ($i < 31)) {
            $this->multiplier = 1000000000000;
        } elseif (($i > 30) && ($i < 36)) {
            $this->multiplier = 1099511627776;
        }
        if (($this->multiplier > 0) && ($this->size > -1)) {
            $this->sizelimit = $this->size * $this->multiplier;
        }
    }

    /**
     * This specifies when the file should be selected, whether it be
     * when the file matches a particular size, when it is smaller,
     * or whether it is larger.
     *
     * @param array $cmp The comparison to perform, an EnumeratedAttribute
     *
     * @return void
     */
    public function setWhen($cmp)
    {
        $c = array_search($cmp, self::$sizeComparisons, true);
        if ($c !== false) {
            $this->cmp = $c;
        }
    }

    /**
     * When using this as a custom selector, this method will be called.
     * It translates each parameter into the appropriate setXXX() call.
     *
     * {@inheritdoc}
     *
     * @param array $parameters the complete set of parameters for this selector
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setParameters($parameters)
    {
        parent::setParameters($parameters);
        if ($parameters !== null) {
            for ($i = 0, $size = count($parameters); $i < $size; $i++) {
                $paramname = $parameters[$i]->getName();
                switch (strtolower($paramname)) {
                    case self::SIZE_KEY:
                        try {
                            $this->setValue($parameters[$i]->getValue());
                        } catch (Exception $nfe) {
                            $this->setError(
                                "Invalid size setting "
                                . $parameters[$i]->getValue()
                            );
                        }
                        break;
                    case self::UNITS_KEY:
                        $this->setUnits($parameters[$i]->getValue());
                        break;
                    case self::WHEN_KEY:
                        $this->setWhen($parameters[$i]->getValue());
                        break;
                    default:
                        $this->setError("Invalid parameter " . $paramname);
                }
            }
        }
    }

    /**
     * <p>Checks to make sure all settings are kosher. In this case, it
     * means that the size attribute has been set (to a positive value),
     * that the multiplier has a valid setting, and that the size limit
     * is valid. Since the latter is a calculated value, this can only
     * fail due to a programming error.
     * </p>
     * <p>If a problem is detected, the setError() method is called.
     * </p>
     *
     * {@inheritdoc}
     *
     * @return void
     */
    public function verifySettings()
    {
        if ($this->size < 0) {
            $this->setError("The value attribute is required, and must be positive");
        } elseif ($this->multiplier < 1) {
            $this->setError("Invalid Units supplied, must be K,Ki,M,Mi,G,Gi,T,or Ti");
        } elseif ($this->sizelimit < 0) {
            $this->setError("Internal error: Code is not setting sizelimit correctly");
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * {@inheritdoc}
     *
     * @param PhingFile $basedir A PhingFile object for the base directory
     * @param string $filename The name of the file to check
     * @param PhingFile $file A PhingFile object for this filename
     *
     * @return bool whether the file should be selected or not
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {
        $this->validate();

        // Directory size never selected for
        if ($file->isDirectory()) {
            return true;
        }
        if ($this->cmp === 0) {
            return ($file->length() < $this->sizelimit);
        } elseif ($this->cmp === 1) {
            return ($file->length() > $this->sizelimit);
        } else {
            return ($file->length() === $this->sizelimit);
        }
    }
}
