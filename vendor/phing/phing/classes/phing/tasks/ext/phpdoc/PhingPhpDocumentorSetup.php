<?php
/**
 * $Id: 3c0527274eb0d0c2c6ed888ff23fe5b38367bb04 $
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

require_once 'PhpDocumentor/phpDocumentor/Setup.inc.php';

/**
 * Phing subclass of the phpDocumentor_setup class provided with PhpDocumentor to work around limitations in PhpDocumentor API.
 *
 * This class is necessary because phpDocumentor_setup does not expose a complete API for setting configuration options.  Because
 * this class must directly modify some "private" GLOBAL(!) configuration variables, it is liable to break if the PhpDocumentor
 * internal implementation changes.  Obviously this is far from ideal, but there's also no solution given the inflexibility of the
 * PhpDocumentor design.
 *
 * @author Hans Lellelid <hans@xmpl.org>@author hans
 * @version $Id: 3c0527274eb0d0c2c6ed888ff23fe5b38367bb04 $
 * @package phing.tasks.ext.phpdoc
 */
class PhingPhpDocumentorSetup extends phpDocumentor_setup
{

    /**
     * Constructs a new PhingPhpDocumentorSetup.
     *
     * @param null $configdir
     * @param object $task The task we're working with, so we can pass it on to the ErrorTracker
     * @internal param string $configDir Directory in which to look for configuration files.
     */
    public function __construct($configdir = null, $task)
    {
        global $_phpDocumentor_cvsphpfile_exts, $_phpDocumentor_setting, $_phpDocumentor_phpfile_exts;

        $this->setup = new Io();
        $this->render = new phpDocumentor_IntermediateParser("Default Title");

        $GLOBALS['_phpDocumentor_install_dir'] = $configdir;
        $this->parseIni();

        // These redundant-looking lines seem to actually make a difference.
        // See: http://phing.info/trac/ticket/150
        $_phpDocumentor_phpfile_exts = $GLOBALS['_phpDocumentor_phpfile_exts'];
        $_phpDocumentor_cvsphpfile_exts = $GLOBALS['_phpDocumentor_cvsphpfile_exts'];

        if (tokenizer_ext) {
            $this->parse = new phpDocumentorTParser();
        } else {
            $this->parse = new Parser();
        }

        $this->setMemoryLimit();

        include_once 'phing/tasks/ext/phpdoc/PhingPhpDocumentorErrorTracker.php';

        // Inject our own error tracker to PhpDocumentor
        $GLOBALS['phpDocumentor_errors'] = new PhingPhpDocumentorErrorTracker();
        $GLOBALS['phpDocumentor_errors']->setTask($task);

    }

    /**
     * Set whether to generate sourcecode for each file parsed.
     *
     * This method exists as a hack because there is no API exposed for this in PhpDocumentor.
     * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
     * is subject to break if PhpDocumentor internals changes.
     *
     * @param bool $b
     */
    public function setGenerateSourcecode($b)
    {
        global $_phpDocumentor_setting;
        $_phpDocumentor_setting['sourcecode'] = (boolean) $b;
    }

    /**
     * Set an array of README/INSTALL/CHANGELOG file paths.
     *
     * This method exists as a hack because there is no API exposed for this in PhpDocumentor.
     * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
     * is subject to break if PhpDocumentor internals changes.
     *
     * @param array $files Absolute paths to files.
     */
    public function setRicFiles($files)
    {
        global $_phpDocumentor_RIC_files;
        $_phpDocumentor_RIC_files = $files;
    }

    /**
     * Set comma-separated list of tags to ignore.
     *
     * This method exists as a hack because there is no API exposed for this in PhpDocumentor.
     * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
     * is subject to break if PhpDocumentor internals changes.
     *
     * @param string $tags
     */
    public function setIgnoreTags($tags)
    {
        global $_phpDocumentor_setting;
        $ignoretags = explode(',', $tags);
        $ignoretags = array_map('trim', $ignoretags);
        $tags = array();
        foreach ($ignoretags as $tag) {
            if (!in_array(
                $tag,
                array('@global', '@access', '@package', '@ignore', '@name', '@param', '@return', '@staticvar', '@var')
            )
            ) {
                $tags[] = $tag;
            }
        }
        $_phpDocumentor_setting['ignoretags'] = $tags;
    }

    /**
     * Set whether to parse dirs as PEAR repos.
     *
     * This method exists as a hack because there is no API exposed for this in PhpDocumentor.
     * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
     * is subject to break if PhpDocumentor internals changes.
     *
     * @param bool $b
     */
    public function setPear($b)
    {
        global $_phpDocumentor_setting;
        $_phpDocumentor_setting['pear'] = (boolean) $b;
    }

    /**
     * Set fullpath to directory to look in for examples.
     *
     * This method exists as a hack because there is no API exposed for this in PhpDocumentor.
     * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
     * is subject to break if PhpDocumentor internals changes.
     *
     * @param string $dir
     */
    public function setExamplesDir($dir)
    {
        global $_phpDocumentor_setting;
        $_phpDocumentor_setting['examplesdir'] = $dir;
    }

    /**
     * Sets the default package name.
     *
     * This method exists as a hack because there is no API exposed for this in PhpDocumentor.
     * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
     * is subject to break if PhpDocumentor internals changes.
     *
     * @param string $name
     */
    public function setDefaultPackageName($name)
    {
        $GLOBALS['phpDocumentor_DefaultPackageName'] = trim($name);
    }

    /**
     * Sets the default category name.
     *
     * This method exists as a hack because there is no API exposed for this in PhpDocumentor.
     * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
     * is subject to break if PhpDocumentor internals changes.
     *
     * @param string $name
     */
    public function setDefaultCategoryName($name)
    {
        $GLOBALS['phpDocumentor_DefaultCategoryName'] = trim($name);
    }

    /**
     * Enables quiet mode.
     *
     * This method exists as a hack because the API exposed for this method in PhpDocumentor
     * doesn't work correctly.
     *
     * Note that because we are setting a "private" GLOBAL(!!) config var with this value, this
     * is subject to break if PhpDocumentor internals changes.
     *
     */
    public function setQuietMode()
    {
        global $_phpDocumentor_setting;
        $_phpDocumentor_setting['quiet'] = true;
        parent::setQuietMode();
    }

    /**
     * Control whether or not warnings will be shown for undocumented elements.
     * Useful for identifying classes and methods that haven't yet been
     * documented.
     *
     * @param bool $bEnable
     */
    public function setUndocumentedelements($bEnable)
    {
        $this->render->setUndocumentedElementWarningsMode($bEnable);
    }

    /**
     * custom tags, will be recognized and put in tags[] instead of
     * unknowntags[]
     *
     * This method exists as a hack because the API exposed for this method in
     * PhpDocumentor doesn't work correctly.
     *
     * Note that because we are setting a "private" GLOBAL(!!) config var with
     * this value, this is subject to break if PhpDocumentor internals changes.
     *
     * @param string $sCustomtags
     */
    public function setCustomtags($sCustomtags)
    {
        global $_phpDocumentor_setting;
        $_phpDocumentor_setting['customtags'] = $sCustomtags;
    }

    /**
     * Files to ignore
     *
     * @param string $sIgnore
     */
    public function setIgnore($sIgnore)
    {
        global $_phpDocumentor_setting;
        $_phpDocumentor_setting['ignore'] = $sIgnore;
    }
}
