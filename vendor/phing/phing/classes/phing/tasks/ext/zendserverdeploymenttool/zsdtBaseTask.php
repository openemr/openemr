<?php
/**
 * $Id: e456c8757c8c6d9d2971d63d37d8347a86cc46a0 $
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
 * Class ZendServerDeploymentToolTask
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.zendserverdevelopmenttools
 */
abstract class zsdtBaseTask extends Task
{
    protected $action;

    protected $arguments = '';

    /** @var string $descriptor */
    protected $descriptor;

    /** @var string $schema */
    protected $schema;

    /** @var array $path */
    private $path = array(
        'NIX' => '/usr/local/zend/bin/zdpack',
        'WIN' => 'C:\Program Files (x86)\Zend\ZendServer\bin\zdpack',
        'USR' => ''
    );

    /**
     * The package descriptor file.
     *
     * @param string $descriptor
     *
     * @return void
     */
    public function setDescriptor($descriptor)
    {
        $this->descriptor = escapeshellarg($descriptor);
    }

    /**
     * The path to the package descriptor schema used for validation.
     *
     * @param string $schema
     *
     * @return void
     */
    public function setSchema($schema)
    {
        $this->schema = escapeshellarg($schema);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->path['USR'] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function main()
    {
        $this->validate();

        $command = '';
        if ($this->path['USR'] !== '') {
            $command .= $this->path['USR'];
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command .= escapeshellarg($this->path['WIN']);
        } else {
            $command .= $this->path['NIX'];
        }

        $commandString = sprintf('%s %s %s', $command, $this->action, $this->arguments);
        $msg = exec($commandString . ' 2>&1', $output, $code);

        if ($code !== 0) {
            throw new BuildException("Build package failed. \n Msg: " . $msg . " \n Pack command: " . $commandString);
        }
    }

    /**
     * Validates argument list.
     *
     * @return void
     */
    protected function validate()
    {
        if ($this->schema !== null) {
            $this->arguments .= "--schema=$this->schema ";
        }
    }
}
