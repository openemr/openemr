<?php
/**
 * $Id: 5fdaa20ab4e93edbfddc6bd90e39655e020c4197 $
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

require_once 'phing/tasks/ext/zendserverdeploymenttool/zsdtBaseTask.php';

/**
 * Class ZendServerDeploymentToolTask
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.zendserverdevelopmenttools
 */
class zsdtPackTask extends zsdtBaseTask
{
    /** @var string $package */
    private $package;

    /** @var string $source */
    private $source;

    /** @var string $scripts */
    private $scripts;

    /** @var string $output */
    private $output;

    /** @var string $phpbin */
    private $phpbin;

    /** @var bool $lint */
    private $lint = false;

    /**
     * A directory containing the data and the script directories, in addition to the package descriptor file.
     *
     * @param string $package
     *
     * @return void
     */
    public function setPackage($package)
    {
        $this->package = escapeshellarg($package);
    }

    /**
     * Performs a PHP lint test on the deployment scripts before creating the package.
     *
     * @param boolean $lint
     *
     * @return void
     */
    public function setLint($lint)
    {
        $this->lint = $lint;
    }

    /**
     * The directory in which the package is created.
     * The package name will be created as "<app-name>-<app-version>.zpk".
     *
     * @param string $output
     *
     * @return void
     */
    public function setOutput($output)
    {
        $this->output = escapeshellarg($output);
    }

    /**
     * The PHP executable to use for lint.
     *
     * @param string $phpbin
     *
     * @return void
     */
    public function setPhpbin($phpbin)
    {
        $this->phpbin = escapeshellarg($phpbin);
    }

    /**
     * The directory which contains the package deployment scripts.
     * The Deployment Tool will search this directory for the expected files and then packs them.
     *
     * @param string $scripts
     *
     * @return void
     */
    public function setScripts($scripts)
    {
        $this->scripts = escapeshellarg($scripts);
    }
    /**
     * The directory that contains the application resources (PHP sources, JavaScript, etc.).
     * The directory's internal structure must match the necessary structure for the application to be functional.
     *
     * @param string $source
     *
     * @return void
     */
    public function setSource($source)
    {
        $this->source = escapeshellarg($source);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function init()
    {
        $this->action = 'pack';
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws BuildException
     */
    protected function validate()
    {
        if ($this->descriptor === null || $this->scripts === null || $this->package === null) {
            throw new BuildException(
                'The deployment tool needs at least the project descriptor, '
                . 'the scripts folder and package folder to be set.'
            );
        } elseif ($this->lint !== false && $this->phpbin === null) {
            throw new BuildException('You set the lint option but not the path to the php executable.');
        }

        parent::validate();

        if ($this->lint !== false) {
            $this->arguments .= '--lint ';
        }

        if ($this->source !== null) {
            $this->arguments .= "--src-dir=$this->source ";
        }

        if ($this->output !== null) {
            $this->arguments .= "--output-dir=$this->output ";
        }

        if ($this->phpbin !== null) {
            $this->arguments .= "--php-exe=$this->phpbin ";
        }

        $this->arguments .= "--scripts-dir=$this->scripts ";
        $this->arguments .= "--package-descriptor=$this->descriptor ";
        $this->arguments .= $this->package;
    }
}
