<?php

/*
 *  $Id: d9741f5e9084e1c59c5dfeb32524d56fe052ecfc $
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

include_once 'phing/tasks/system/PropertyTask.php';

/**
 * Task for setting properties from an XML file in buildfiles.
 *
 * @author    Jonathan Bond-Caron <jbondc@openmv.com>
 * @version   $Id: d9741f5e9084e1c59c5dfeb32524d56fe052ecfc $
 * @package   phing.tasks.ext
 * @since     2.4.0
 * @link      http://ant.apache.org/manual/CoreTasks/xmlproperty.html
 */
class XmlPropertyTask extends PropertyTask
{

    private $_keepRoot = true;
    private $_collapseAttr = false;
    private $_delimiter = ',';
    private $_required = false;

    /** Set a file to use as the source for properties.
     * @param $file
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $file = new PhingFile($file);
        }
        $this->file = $file;
    }

    /** Get the PhingFile that is being used as property source. */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Prefix to apply to properties loaded using <code>file</code>.
     * A "." is appended to the prefix if not specified.
     * @param  string $prefix prefix string
     * @return void
     * @since 2.0
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        if (!StringHelper::endsWith(".", $prefix)) {
            $this->prefix .= ".";
        }
    }

    /**
     * @return string
     * @since 2.0
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Keep the xml root tag as the first value in the property name
     *
     * @param bool $yesNo
     */
    public function setKeepRoot($yesNo)
    {
        $this->_keepRoot = (bool) $yesNo;
    }

    /**
     * @return bool
     */
    public function getKeepRoot()
    {
        return $this->_keepRoot;
    }

    /**
     * Treat attributes as nested elements.
     *
     * @param bool $yesNo
     */
    public function setCollapseAttributes($yesNo)
    {
        $this->_collapseAttr = (bool) $yesNo;
    }

    /**
     * @return bool
     */
    public function getCollapseAttributes()
    {
        return $this->_collapseAttr;
    }

    /**
     * Delimiter for splitting multiple values.
     *
     * @param string $d
     */
    public function setDelimiter($d)
    {
        $this->_delimiter = $d;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->_delimiter;
    }

    /**
     * File required or not.
     *
     * @param string $d
     */
    public function setRequired($d)
    {
        $this->_required = $d;
    }

    /**
     * @return string
     */
    public function getRequired()
    {
        return $this->_required;
    }

    /**
     * set the property in the project to the value.
     * if the task was give a file or env attribute
     * here is where it is loaded
     */
    public function main()
    {

        if ($this->file === null) {
            throw new BuildException("You must specify file to load properties from", $this->getLocation());
        }

        $props = $this->loadFile($this->file);
        $this->addProperties($props);
    }

    /**
     * load properties from an XML file.
     * @param PhingFile $file
     * @throws BuildException
     * @return Properties
     */
    protected function loadFile(PhingFile $file)
    {
        $props = new Properties();
        $this->log("Loading " . $file->getAbsolutePath(), Project::MSG_INFO);
        try { // try to load file
            if ($file->exists()) {
                return $this->_getProperties($file);

            } else {
                if ($this->getRequired()) {
                    throw new BuildException("Could not load required properties file.", $ioe);
                } else {
                    $this->log(
                        "Unable to find property file: " . $file->getAbsolutePath() . "... skipped",
                        Project::MSG_WARN
                    );
                }
            }
        } catch (IOException $ioe) {
            throw new BuildException("Could not load properties from file.", $ioe);
        }
    }

    /**
     * Parses an XML file and returns properties
     *
     * @param string $filePath
     *
     * @throws IOException
     * @return Properties
     */
    protected function _getProperties($filePath)
    {

        // load() already made sure that file is readable
        // but we'll double check that when reading the file into
        // an array

        if (($lines = @file($filePath)) === false) {
            throw new IOException("Unable to parse contents of $filePath");
        }

        $prop = new Properties();

        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            throw new IOException("Unable to parse XML file $filePath");
        }

        $path = array();

        if ($this->_keepRoot) {
            $path[] = dom_import_simplexml($xml)->tagName;

            $prefix = implode('.', $path);

            if (!empty($prefix)) {
                $prefix .= '.';
            }

            // Check for attributes
            foreach ($xml->attributes() as $attribute => $val) {
                if ($this->_collapseAttr) {
                    $prop->setProperty($prefix . "$attribute", (string) $val);
                } else {
                    $prop->setProperty($prefix . "($attribute)", (string) $val);
                }
            }
        }

        $this->_addNode($xml, $path, $prop);

        return $prop;
    }

    /**
     * Adds an XML node
     *
     * @param SimpleXMLElement $node
     * @param array            $path Path to this node
     * @param Properties       $prop Properties will be added as they are found (by reference here)
     *
     * @return void
     */
    protected function _addNode($node, $path, $prop)
    {
        foreach ($node as $tag => $value) {

            $prefix = implode('.', $path);

            if (!empty($prefix) > 0) {
                $prefix .= '.';
            }

            // Check for attributes
            foreach ($value->attributes() as $attribute => $val) {
                if ($this->_collapseAttr) {
                    $prop->setProperty($prefix . "$tag.$attribute", (string) $val);
                } else {
                    $prop->setProperty($prefix . "$tag($attribute)", (string) $val);
                }
            }

            // Add tag
            if (count($value->children())) {
                $this->_addNode($value, array_merge($path, array($tag)), $prop);
            } else {
                $val = (string) $value;

                /* Check for * and ** on 'exclude' and 'include' tag / ant seems to do this? could use FileSet here
                if ($tag == 'exclude') {
                }*/

                // When property already exists, i.e. multiple xml tag
                // <project>
                //    <exclude>file/a.php</exclude>
                //    <exclude>file/a.php</exclude>
                // </project>
                //
                // Would be come project.exclude = file/a.php,file/a.php
                $p = empty($prefix) ? $tag : $prefix . $tag;
                $prop->append($p, (string) $val, $this->_delimiter);
            }
        }
    }
}
