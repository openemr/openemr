<?php
/*
 *  $Id: f985ca945225b72bc2dd03a0132579cbdff5d6da $
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

include_once 'phing/types/DataType.php';
include_once 'phing/types/Path.php';
include_once 'phing/mappers/CompositeMapper.php';
include_once 'phing/mappers/ContainerMapper.php';

/**
 * Filename Mapper maps source file name(s) to target file name(s).
 *
 * Built-in mappers can be accessed by specifying they "type" attribute:
 * <code>
 * <mapper type="glob" from="*.php" to="*.php.bak"/>
 * </code>
 * Custom mappers can be specified by providing a dot-path to a include_path-relative
 * class:
 * <code>
 * <mapper classname="myapp.mappers.DevToProdMapper" from="*.php" to="*.php"/>
 * <!-- maps all PHP files from development server to production server, for example -->
 * </code>
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @package phing.types
 */
class Mapper extends DataType
{

    protected $type;
    protected $classname;
    protected $from;
    protected $to;

    /** @var Path $classpath */
    protected $classpath;
    protected $classpathId;

    /** @var ContainerMapper $container */
    private $container = null;

    /**
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Set the classpath to be used when searching for component being defined
     *
     * @param Path $classpath An Path object containing the classpath.
     * @throws BuildException
     */
    public function setClasspath(Path $classpath)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($this->classpath === null) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * Create the classpath to be used when searching for component being defined
     */
    public function createClasspath()
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($this->classpath === null) {
            $this->classpath = new Path($this->project);
        }

        return $this->classpath->createPath();
    }

    /**
     * Reference to a classpath to use when loading the files.
     * @param Reference $r
     * @throws BuildException
     */
    public function setClasspathRef(Reference $r)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->classpathId = $r->getRefId();
        $this->createClasspath()->setRefid($r);
    }

    /**
     * Set the type of FileNameMapper to use.
     * @param $type
     * @throws BuildException
     */
    public function setType($type)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->type = $type;
    }

    /**
     * Add a nested <code>FileNameMapper</code>.
     * @param FileNameMapper $fileNameMapper the <code>FileNameMapper</code> to add.
     * @throws BuildException
     */
    public function add(Mapper $fileNameMapper)
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        if ($this->container == null) {
            if ($this->type == null && $this->classname == null) {
                $this->container = new CompositeMapper();
            } else {
                $m = $this->getImplementation();
                if ($m instanceof ContainerMapper) {
                    $this->container = $m;
                } else {
                    throw new BuildException("$m mapper implementation does not support nested mappers!");
                }
            }
        }
        $this->container->add($fileNameMapper);
        $this->checked = false;
    }

    /**
     * Add a Mapper
     * @param Mapper $mapper the mapper to add
     */
    public function addMapper(Mapper $mapper)
    {
        $this->add($mapper);
    }

    /**
     * Set the class name of the FileNameMapper to use.
     * @param string $classname
     * @throws BuildException
     */
    public function setClassname($classname)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->classname = $classname;
    }

    /**
     * Set the argument to FileNameMapper.setFrom
     * @param $from
     * @throws BuildException
     */
    public function setFrom($from)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->from = $from;
    }

    /**
     * Set the argument to FileNameMapper.setTo
     * @param $to
     * @throws BuildException
     */
    public function setTo($to)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->to = $to;
    }

    /**
     * Make this Mapper instance a reference to another Mapper.
     *
     * You must not set any other attribute if you make it a reference.
     * @param Reference $r
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if ($this->type !== null || $this->from !== null || $this->to !== null) {
            throw DataType::tooManyAttributes();
        }
        parent::setRefid($r);
    }

    /** Factory, returns inmplementation of file name mapper as new instance */
    public function getImplementation()
    {
        if ($this->isReference()) {
            $o = $this->getRef();
            if ($o instanceof FileNameMapper) {
                return $o;
            }
            if ($o instanceof Mapper) {
                return $o->getImplementation();
            }

            $od = $o == null ? "null" : get_class($o);
            throw new BuildException($od . " at reference '" . $r->getRefId() . "' is not a valid mapper reference.");
        }

        if ($this->type === null && $this->classname === null && $this->container == null) {
            throw new BuildException("either type or classname attribute must be set for <mapper>");
        }

        if ($this->container != null) {
            return $this->container;
        }

        if ($this->type !== null) {
            switch ($this->type) {
                case 'chained':
                    $this->classname = 'phing.mappers.ChainedMapper';
                    break;
                case 'composite':
                    $this->classname = 'phing.mappers.CompositeMapper';
                    break;
                case 'cutdirs':
                    $this->classname = 'phing.mappers.CutDirsMapper';
                    break;
                case 'identity':
                    $this->classname = 'phing.mappers.IdentityMapper';
                    break;
                case 'firstmatch':
                    $this->classname = 'phing.mappers.FirstMatchMapper';
                    break;
                case 'flatten':
                    $this->classname = 'phing.mappers.FlattenMapper';
                    break;
                case 'glob':
                    $this->classname = 'phing.mappers.GlobMapper';
                    break;
                case 'regexp':
                case 'regex':
                    $this->classname = 'phing.mappers.RegexpMapper';
                    break;
                case 'merge':
                    $this->classname = 'phing.mappers.MergeMapper';
                    break;
                default:
                    throw new BuildException("Mapper type {$this->type} not known");
                    break;
            }
        }

        // get the implementing class
        $cls = Phing::import($this->classname, $this->classpath);

        $m = new $cls();
        $m->setFrom($this->from);
        $m->setTo($this->to);

        return $m;
    }

    /** Performs the check for circular references and returns the referenced Mapper. */
    private function getRef()
    {
        if (!$this->checked) {
            $stk = array();
            $stk[] = $this;
            $this->dieOnCircularReference($stk, $this->project);
        }

        $o = $this->ref->getReferencedObject($this->project);
        if (!($o instanceof Mapper)) {
            $msg = $this->ref->getRefId() . " doesn't denote a mapper";
            throw new BuildException($msg);
        } else {
            return $o;
        }
    }
}
