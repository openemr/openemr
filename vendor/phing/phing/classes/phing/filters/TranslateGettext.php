<?php

/*
 *  $Id: ceb6f10071ec525cd89b9f6dba2564971aa67a72 $
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

require_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Replaces gettext("message id") and _("message id") with the translated string.
 *
 * Gettext is great for creating multi-lingual sites, but in some cases (e.g. for
 * performance reasons) you may wish to replace the gettext calls with the translations
 * of the strings; that's what this task is for.  Note that this is similar to
 * ReplaceTokens, but both the find and the replace aspect is more complicated -- hence
 * this is a separate, stand-alone filter.
 *
 * <p>
 * Example:<br>
 * <pre>
 * <translategettext locale="en_US" domain="messages" dir="${webroot}/local"/>
 * </pre>
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: ceb6f10071ec525cd89b9f6dba2564971aa67a72 $
 * @see       BaseFilterReader
 * @package   phing.filters
 */
class TranslateGettext extends BaseParamFilterReader implements ChainableReader
{

    // constants for specifying keys to expect
    // when this is called using <filterreader ... />
    const DOMAIN_KEY = "domain";
    const DIR_KEY = "dir";
    const LOCALE_KEY = "locale";

    /** The domain to use */
    private $domain = 'messages';

    /** The dir containing LC_MESSAGES */
    private $dir;

    /** The locale to use */
    private $locale;

    /** The system locale before it was changed for this filter. */
    private $storedLocale;

    /**
     * Set the text domain to use.
     * The text domain must correspond to the name of the compiled .mo files.
     * E.g. "messages" ==> $dir/LC_MESSAGES/messages.mo
     *         "mydomain" ==> $dir/LC_MESSAGES/mydomain.mo
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Get the current domain.
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the root locale directory.
     * @param PhingFile $dir
     */
    public function setDir(PhingFile $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Gets the root locale directory.
     * @return PhingFile
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Sets the locale to use for translation.
     * Note that for gettext() to work, you have to make sure this locale
     * is specific enough for your system (e.g. some systems may allow an 'en' locale,
     * but others will require 'en_US', etc.).
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Gets the locale to use for translation.
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Make sure that required attributes are set.
     * @throws BuldException - if any required attribs aren't set.
     */
    protected function checkAttributes()
    {
        if (!$this->domain || !$this->locale || !$this->dir) {
            throw new BuildException("You must specify values for domain, locale, and dir attributes.");
        }
    }

    /**
     * Initialize the gettext/locale environment.
     * This method will change some env vars and locale settings; the
     * restoreEnvironment should put them all back :)
     *
     * @return void
     * @throws BuildException - if locale cannot be set.
     * @see restoreEnvironment()
     */
    protected function initEnvironment()
    {
        $this->storedLocale = getenv("LANG");

        $this->log("Setting locale to " . $this->locale, Project::MSG_DEBUG);
        putenv("LANG=" . $this->locale);
        $ret = setlocale(LC_ALL, $this->locale);
        if ($ret === false) {
            $msg = "Could not set locale to " . $this->locale
                . ". You may need to use fully qualified name"
                . " (e.g. en_US instead of en).";
            throw new BuildException($msg);
        }

        $this->log("Binding domain '" . $this->domain . "' to " . $this->dir, Project::MSG_DEBUG);
        bindtextdomain($this->domain, $this->dir->getAbsolutePath());
        textdomain($this->domain);
    }

    /**
     * Restores environment settings and locale.
     * This does _not_ restore any gettext-specific settings
     * (e.g. textdomain()).
     *
     * @return void
     */
    protected function restoreEnvironment()
    {
        putenv("LANG=" . $this->storedLocale);
        setlocale(LC_ALL, $this->storedLocale);
    }

    /**
     * Performs gettext translation of msgid and returns translated text.
     *
     * This function simply wraps gettext() call, but provides ability to log
     * string replacements.  (alternative would be using preg_replace with /e which
     * would probably be faster, but no ability to debug/log.)
     *
     * @param  array  $matches Array of matches; we're interested in $matches[2].
     * @return string Translated text
     */
    private function xlateStringCallback($matches)
    {
        $charbefore = $matches[1];
        $msgid = $matches[2];
        $translated = gettext($msgid);
        $this->log("Translating \"$msgid\" => \"$translated\"", Project::MSG_DEBUG);

        return $charbefore . '"' . $translated . '"';
    }

    /**
     * Returns the filtered stream.
     * The original stream is first read in fully, and then translation is performed.
     *
     * @param null $len
     * @throws BuildException
     * @return mixed the filtered stream, or -1 if the end of the resulting stream has been reached.
     *
     */
    public function read($len = null)
    {

        if (!$this->getInitialized()) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        // Make sure correct params/attribs have been set
        $this->checkAttributes();

        $buffer = $this->in->read($len);
        if ($buffer === -1) {
            return -1;
        }

        // Setup the locale/gettext environment
        $this->initEnvironment();


        // replace any occurrences of _("") or gettext("") with
        // the translated value.
        //
        // ([^\w]|^)_\("((\\"|[^"])*)"\)
        //  --$1---      -----$2----
        //                 ---$3--  [match escaped quotes or any char that's not a quote]
        //
        // also match gettext() -- same as above

        $buffer = preg_replace_callback(
            '/([^\w]|^)_\("((\\\"|[^"])*)"\)/',
            array($this, 'xlateStringCallback'),
            $buffer
        );
        $buffer = preg_replace_callback(
            '/([^\w]|^)gettext\("((\\\"|[^"])*)"\)/',
            array($this, 'xlateStringCallback'),
            $buffer
        );

        // Check to see if there are any _('') calls and flag an error

        // Check to see if there are any unmatched gettext() calls -- and flag an error

        $matches = array();
        if (preg_match('/([^\w]|^)(gettext\([^\)]+\))/', $buffer, $matches)) {
            $this->log("Unable to perform translation on: " . $matches[2], Project::MSG_WARN);
        }

        $this->restoreEnvironment();

        return $buffer;
    }

    /**
     * Creates a new TranslateGettext filter using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return TranslateGettext A new filter based on this configuration, but filtering
     *                          the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new TranslateGettext($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setDomain($this->getDomain());
        $newFilter->setLocale($this->getLocale());
        $newFilter->setDir($this->getDir());

        return $newFilter;
    }

    /**
     * Parses the parameters if this filter is being used in "generic" mode.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            foreach ($params as $param) {
                switch ($param->getType()) {
                    case self::DOMAIN_KEY:
                        $this->setDomain($param->getValue());
                        break;
                    case self::DIR_KEY:
                        $this->setDir($this->project->resolveFile($param->getValue()));
                        break;

                    case self::LOCALE_KEY:
                        $this->setLocale($param->getValue());
                        break;
                } // switch
            }
        } // if params !== null
    }
}
