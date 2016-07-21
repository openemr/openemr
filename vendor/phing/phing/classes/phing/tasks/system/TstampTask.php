<?php
/*
 *  $Id: 02ac32dcbb53394d677a9ee39479f3b427306896 $
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

require_once 'phing/Task.php';

/**
 * Sets properties to the current time, or offsets from the current time.
 * The default properties are TSTAMP, DSTAMP and TODAY;
 *
 * Based on Ant's Tstamp task.
 *
 * @author   Michiel Rook <mrook@php.net>
 * @version  $Id: 02ac32dcbb53394d677a9ee39479f3b427306896 $
 * @package  phing.tasks.system
 * @since    2.2.0
 */
class TstampTask extends Task
{
    private $customFormats = array();

    private $prefix = "";

    /**
     * Set a prefix for the properties. If the prefix does not end with a "."
     * one is automatically added.
     * @param string $prefix the prefix to use.
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        if (!empty($this->prefix)) {
            $this->prefix .= ".";
        }
    }

    /**
     * Adds a custom format
     *
     * @param TstampCustomFormat custom format
     */
    public function addFormat(TstampCustomFormat $cf)
    {
        $this->customFormats[] = $cf;
    }

    /**
     * Create the timestamps. Custom ones are done before
     * the standard ones.
     *
     * @throws BuildException
     */
    public function main()
    {
        foreach ($this->customFormats as $cf) {
            $cf->execute($this);
        }

        $dstamp = strftime('%Y%m%d');
        $this->prefixProperty('DSTAMP', $dstamp);

        $tstamp = strftime('%H%M');
        $this->prefixProperty('TSTAMP', $tstamp);

        $today = strftime('%B %d %Y');
        $this->prefixProperty('TODAY', $today);
    }

    /**
     * helper that encapsulates prefix logic and property setting
     * policy (i.e. we use setNewProperty instead of setProperty).
     * @param $name
     * @param $value
     */
    public function prefixProperty($name, $value)
    {
        $this->getProject()->setNewProperty($this->prefix . $name, $value);
    }
}

/**
 * @package  phing.tasks.system
 */
class TstampCustomFormat
{
    private $propertyName = "";
    private $pattern = "";
    private $locale = "";

    /**
     * The property to receive the date/time string in the given pattern
     *
     * @param string $propertyName the name of the property.
     */
    public function setProperty($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * The date/time pattern to be used. The values are as
     * defined by the PHP strftime() function.
     *
     * @param pattern
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * The locale used to create date/time string.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * validate parameter and execute the format.
     *
     * @param TstampTask $tstamp reference to task
     * @throws BuildException
     */
    public function execute(TstampTask $tstamp)
    {
        if (empty($this->propertyName)) {
            throw new BuildException("property attribute must be provided");
        }

        if (empty($this->pattern)) {
            throw new BuildException("pattern attribute must be provided");
        }

        if (!empty($this->locale)) {
            setlocale(LC_ALL, $this->locale);
        }

        $value = strftime($this->pattern);
        $tstamp->prefixProperty($this->propertyName, $value);

        if (!empty($this->locale)) {
            // reset locale
            setlocale(LC_ALL, null);
        }
    }
}
