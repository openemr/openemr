<?php
/*
 *  $Id: 89cfb9d4709a4e67b4f77d4da5b6c6249624230f $
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

require_once 'phing/tasks/ext/PearPackageTask.php';

/**
 * A task to create a PEAR package.xml version 2.0 file.
 *
 * This class uses the PEAR_PackageFileManager2 class to perform the work.
 *
 * This class is designed to be very flexible -- i.e. account for changes to the package.xml w/o
 * requiring changes to this class.  We've accomplished this by having generic <option> and <mapping>
 * nested elements.  All options are set using PEAR_PackageFileManager2::setOptions().
 *
 * The <option> tag is used to set a simple option value.
 * <code>
 * <option name="option_name" value="option_value"/>
 * or <option name="option_name">option_value</option>
 * </code>
 *
 * The <mapping> tag represents a complex data type.  You can use nested <element> (and nested <element> with
 * <element> tags) to represent the full complexity of the structure.  Bear in mind that what you are creating
 * will be mapped to an associative array that will be passed in via PEAR_PackageFileManager2::setOptions().
 * <code>
 * <mapping name="option_name">
 *  <element key="key_name" value="key_val"/>
 *  <element key="key_name" value="key_val"/>
 * </mapping>
 * </code>
 *
 * Here's an over-simple example of how this could be used:
 * <code>
 * <pearpkg2 name="phing" dir="${build.src.dir}">
 *  <fileset dir="src">
 *      <include name="**"/>
 *  </fileset>
 *  <option name="outputdirectory" value="./build"/>
 *  <option name="packagefile" value="package2.xml"/>
 *  <option name="packagedirectory" value="./${build.dist.dir}"/>
 *  <option name="baseinstalldir" value="${pkg.prefix}"/>
 *  <option name="channel" value="my.pear-channel.com"/>
 *  <option name="summary" value="${pkg.summary}"/>
 *  <option name="description" value="${pkg.description}"/>
 *  <option name="apiversion" value="${pkg.version}"/>
 *  <option name="apistability" value="beta"/>
 *  <option name="releaseversion" value="${pkg.version}"/>
 *  <option name="releasestability" value="beta"/>
 *  <option name="license" value="none"/>
 *  <option name="phpdep" value="5.0.0"/>
 *  <option name="pearinstallerdep" value="1.4.6"/>
 *  <option name="packagetype" value="php"/>
 *  <option name="notes" value="${pkg.relnotes}"/>
 *  <mapping name="maintainers">
 *   <element>
 *    <element key="handle" value="hlellelid"/>
 *    <element key="name" value="Hans"/>
 *    <element key="email" value="hans@xmpl.org"/>
 *    <element key="role" value="lead"/>
 *    <element key="active" value="yes"/>
 *   </element>
 *  </mapping>
 * </pearpkg2>
 * </code>
 *
 * Look at the build.xml in the Phing base directory (assuming you have the full distro / CVS version of Phing) to
 * see a more complete example of how to call this script.
 *
 * @author   Stuart Binge <stuart.binge@complinet.com>
 * @author   Hans Lellelid <hans@xmpl.org>
 * @package  phing.tasks.ext
 * @version  $Id: 89cfb9d4709a4e67b4f77d4da5b6c6249624230f $
 */
class PearPackage2Task extends PearPackageTask
{

    public function init()
    {
        include_once 'PEAR/PackageFileManager2.php';
        if (!class_exists('PEAR_PackageFileManager2')) {
            throw new BuildException("You must have installed PEAR_PackageFileManager in order to create a PEAR package.xml version 2.0 file.");
        }
    }

    protected function setVersion2Options()
    {
        $this->pkg->setPackage($this->package);
        $this->pkg->setDate(strftime('%Y-%m-%d'));
        $this->pkg->setTime(strftime('%H:%M:%S'));

        $newopts = array();
        foreach ($this->options as $opt) {
            switch ($opt->getName()) {
                case 'summary':
                    $this->pkg->setSummary($opt->getValue());
                    break;

                case 'description':
                    $this->pkg->setDescription($opt->getValue());
                    break;

                case 'uri':
                    $this->pkg->setUri($opt->getValue());
                    break;

                case 'license':
                    $this->pkg->setLicense($opt->getValue());
                    break;

                case 'channel':
                    $this->pkg->setChannel($opt->getValue());
                    break;

                case 'apiversion':
                    $this->pkg->setAPIVersion($opt->getValue());
                    break;

                case 'releaseversion':
                    $this->pkg->setReleaseVersion($opt->getValue());
                    break;

                case 'releasestability':
                    $this->pkg->setReleaseStability($opt->getValue());
                    break;

                case 'apistability':
                    $this->pkg->setAPIStability($opt->getValue());
                    break;

                case 'notes':
                    $this->pkg->setNotes($opt->getValue());
                    break;

                case 'packagetype':
                    $this->pkg->setPackageType($opt->getValue());
                    break;

                case 'phpdep':
                    $this->pkg->setPhpDep($opt->getValue());
                    break;

                case 'pearinstallerdep':
                    $this->pkg->setPearinstallerDep($opt->getValue());
                    break;

                default:
                    $newopts[] = $opt;
                    break;
            }
        }
        $this->options = $newopts;

        $newmaps = array();
        foreach ($this->mappings as $map) {
            switch ($map->getName()) {
                case 'deps':
                    $deps = $map->getValue();
                    foreach ($deps as $dep) {
                        $type = isset($dep['optional']) ? 'optional' : 'required';
                        $min = isset($dep['min']) ? $dep['min'] : $dep['version'];
                        $max = isset($dep['max']) ? $dep['max'] : null;
                        $rec = isset($dep['recommended']) ? $dep['recommended'] : null;
                        $channel = isset($dep['channel']) ? $dep['channel'] : false;
                        $uri = isset($dep['uri']) ? $dep['uri'] : false;

                        if (!empty($channel)) {
                            $this->pkg->addPackageDepWithChannel(
                                $type,
                                $dep['name'],
                                $channel,
                                $min,
                                $max,
                                $rec
                            );
                        } elseif (!empty($uri)) {
                            $this->pkg->addPackageDepWithUri(
                                $type,
                                $dep['name'],
                                $uri
                            );
                        }
                    };
                    break;

                case 'extdeps':
                    $deps = $map->getValue();
                    foreach ($deps as $dep) {
                        $type = isset($dep['optional']) ? 'optional' : 'required';
                        $min = isset($dep['min']) ? $dep['min'] : $dep['version'];
                        $max = isset($dep['max']) ? $dep['max'] : $dep['version'];
                        $rec = isset($dep['recommended']) ? $dep['recommended'] : $dep['version'];

                        $this->pkg->addExtensionDep(
                            $type,
                            $dep['name'],
                            $min,
                            $max,
                            $rec
                        );
                    };
                    break;

                case 'maintainers':
                    $maintainers = $map->getValue();

                    foreach ($maintainers as $maintainer) {
                        if (!isset($maintainer['active'])) {
                            $maintainer['active'] = 'yes';
                        } else {
                            $maintainer['active'] = $maintainer['active'] === false ? 'no' : 'yes';
                        }
                        $this->pkg->addMaintainer(
                            $maintainer['role'],
                            $maintainer['handle'],
                            $maintainer['name'],
                            $maintainer['email'],
                            $maintainer['active']
                        );
                    }
                    break;

                case 'replacements':
                    $replacements = $map->getValue();

                    foreach ($replacements as $replacement) {
                        $this->pkg->addReplacement(
                            $replacement['path'],
                            $replacement['type'],
                            $replacement['from'],
                            $replacement['to']
                        );
                    }
                    break;

                case 'role':
                    foreach ($map->getValue() as $role) {
                        $this->pkg->addRole($role['extension'], $role['role']);
                    }
                    break;

                default:
                    $newmaps[] = $map;
            }
        }
        $this->mappings = $newmaps;
    }

    /**
     * Main entry point.
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        if ($this->dir === null) {
            throw new BuildException("You must specify the \"dir\" attribute for PEAR package 2 task.");
        }

        if ($this->package === null) {
            throw new BuildException("You must specify the \"name\" attribute for PEAR package 2 task.");
        }

        $this->pkg = new PEAR_PackageFileManager2();

        $this->setVersion2Options();
        $this->setOptions();

        $this->pkg->addRelease();
        $this->pkg->generateContents();
        $e = $this->pkg->writePackageFile();
        if (PEAR::isError($e)) {
            throw new BuildException("Unable to write package file.", new Exception($e->getMessage()));
        }
    }

}
