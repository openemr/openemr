<?php

/**
 * reStructuredText rendering task for Phing, the PHP build tool.
 *
 * PHP version 5
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @author     Christian Weiske <cweiske@cweiske.de>
 * @license    LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link       http://www.phing.info/
 * @version    SVN: $Id: 5b4d68c019e0f3f6f497b8ec7c268c5600116ba0 $
 */

require_once 'phing/Task.php';
require_once 'phing/util/FileUtils.php';

/**
 * reStructuredText rendering task for Phing, the PHP build tool.
 *
 * PHP version 5
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @author     Christian Weiske <cweiske@cweiske.de>
 * @license    LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link       http://www.phing.info/
 */
class rSTTask extends Task
{
    /**
     * @var string Taskname for logger
     */
    protected $taskName = 'rST';

    /**
     * Result format, defaults to "html".
     * @see $supportedFormats for all possible options
     *
     * @var string
     */
    protected $format = 'html';

    /**
     * Array of supported output formats
     *
     * @var array
     * @see $format
     * @see $targetExt
     */
    protected static $supportedFormats = array(
        'html',
        'latex',
        'man',
        'odt',
        's5',
        'xml'
    );

    /**
     * Maps formats to file extensions
     *
     * @var array
     */
    protected static $targetExt = array(
        'html' => 'html',
        'latex' => 'tex',
        'man' => '3',
        'odt' => 'odt',
        's5' => 'html',
        'xml' => 'xml',
    );

    /**
     * Input file in rST format.
     * Required
     *
     * @var string
     */
    protected $file = null;

    /**
     * Additional rst2* tool parameters.
     *
     * @var string
     */
    protected $toolParam = null;

    /**
     * Full path to the tool, i.e. /usr/local/bin/rst2html
     *
     * @var string
     */
    protected $toolPath = null;

    /**
     * Output file or directory. May be omitted.
     * When it ends with a slash, it is considered to be a directory
     *
     * @var string
     */
    protected $destination = null;

    protected $filesets = array(); // all fileset objects assigned to this task
    protected $mapperElement = null;

    /**
     * all filterchains objects assigned to this task
     *
     * @var array
     */
    protected $filterChains = array();

    /**
     * mode to create directories with
     *
     * @var integer
     */
    protected $mode = 0;

    /**
     * Only render files whole source files are newer than the
     * target files
     *
     * @var boolean
     */
    protected $uptodate = false;

    /**
     * Sets up this object internal stuff. i.e. the default mode.
     */
    public function __construct()
    {
        $this->mode = 0777 - umask();
    }

    /**
     * Init method: requires the PEAR System class
     */
    public function init()
    {
        require_once 'System.php';
    }

    /**
     * The main entry point method.
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        $tool = $this->getToolPath($this->format);
        if (count($this->filterChains)) {
            $this->fileUtils = new FileUtils();
        }

        if ($this->file != '') {
            $file = $this->file;
            $targetFile = $this->getTargetFile($file, $this->destination);
            $this->render($tool, $file, $targetFile);

            return;
        }

        if (!count($this->filesets)) {
            throw new BuildException(
                '"file" attribute or "fileset" subtag required'
            );
        }

        // process filesets
        $mapper = null;
        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
        }

        $project = $this->getProject();
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($project);
            $fromDir = $fs->getDir($project);
            $srcFiles = $ds->getIncludedFiles();

            foreach ($srcFiles as $src) {
                $file = new PhingFile($fromDir, $src);
                if ($mapper !== null) {
                    $results = $mapper->main($file);
                    if ($results === null) {
                        throw new BuildException(
                            sprintf(
                                'No filename mapper found for "%s"',
                                $file
                            )
                        );
                    }
                    $targetFile = reset($results);
                } else {
                    $targetFile = $this->getTargetFile($file, $this->destination);
                }
                $this->render($tool, $file, $targetFile);
            }
        }
    }

    /**
     * Renders a single file and applies filters on it
     *
     * @param string $tool       conversion tool to use
     * @param string $source     rST source file
     * @param string $targetFile target file name
     *
     * @return void
     */
    protected function render($tool, $source, $targetFile)
    {
        if (count($this->filterChains) == 0) {
            return $this->renderFile($tool, $source, $targetFile);
        }

        $tmpTarget = tempnam(sys_get_temp_dir(), 'rST-');
        $this->renderFile($tool, $source, $tmpTarget);

        $this->fileUtils->copyFile(
            new PhingFile($tmpTarget),
            new PhingFile($targetFile),
            true,
            false,
            $this->filterChains,
            $this->getProject(),
            $this->mode
        );
        unlink($tmpTarget);
    }

    /**
     * Renders a single file with the rST tool.
     *
     * @param string $tool       conversion tool to use
     * @param string $source     rST source file
     * @param string $targetFile target file name
     *
     * @return void
     *
     * @throws BuildException When the conversion fails
     */
    protected function renderFile($tool, $source, $targetFile)
    {
        if ($this->uptodate && file_exists($targetFile)
            && filemtime($source) <= filemtime($targetFile)
        ) {
            //target is up to date
            return;
        }
        //work around a bug in php by replacing /./ with /
        $targetDir = str_replace('/./', '/', dirname($targetFile));
        if (!is_dir($targetDir)) {
            $this->log("Creating directory '$targetDir'", Project::MSG_VERBOSE);
            mkdir($targetDir, $this->mode, true);
        }

        $cmd = $tool
            . ' --exit-status=2'
            . ' ' . $this->toolParam
            . ' ' . escapeshellarg($source)
            . ' ' . escapeshellarg($targetFile)
            . ' 2>&1';

        $this->log('command: ' . $cmd, Project::MSG_VERBOSE);
        exec($cmd, $arOutput, $retval);
        if ($retval != 0) {
            $this->log(implode("\n", $arOutput), Project::MSG_INFO);
            throw new BuildException('Rendering rST failed');
        }
        $this->log(implode("\n", $arOutput), Project::MSG_DEBUG);
    }

    /**
     * Finds the rst2* binary path
     *
     * @param string $format Output format
     *
     * @return string Full path to rst2$format
     *
     * @throws BuildException When the tool cannot be found
     */
    protected function getToolPath($format)
    {
        if ($this->toolPath !== null) {
            return $this->toolPath;
        }

        $tool = 'rst2' . $format;
        $path = System::which($tool);
        if (!$path) {
            throw new BuildException(
                sprintf('"%s" not found. Install python-docutils.', $tool)
            );
        }

        return $path;
    }

    /**
     * Determines and returns the target file name from the
     * input file and the configured destination name.
     *
     * @param string $file        Input file
     * @param string $destination Destination file or directory name,
     *                            may be null
     *
     * @return string Target file name
     *
     * @uses $format
     * @uses $targetExt
     */
    public function getTargetFile($file, $destination = null)
    {
        if ($destination != ''
            && substr($destination, -1) !== '/'
            && substr($destination, -1) !== '\\'
        ) {
            return $destination;
        }

        if (strtolower(substr($file, -4)) == '.rst') {
            $file = substr($file, 0, -4);
        }

        return $destination . $file . '.' . self::$targetExt[$this->format];
    }

    /**
     * The setter for the attribute "file"
     *
     * @param string $file Path of file to render
     *
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * The setter for the attribute "format"
     *
     * @param string $format Output format
     *
     * @return void
     *
     * @throws BuildException When the format is not supported
     */
    public function setFormat($format)
    {
        if (!in_array($format, self::$supportedFormats)) {
            throw new BuildException(
                sprintf(
                    'Invalid output format "%s", allowed are: %s',
                    $format,
                    implode(', ', self::$supportedFormats)
                )
            );
        }
        $this->format = $format;
    }

    /**
     * The setter for the attribute "destination"
     *
     * @param string $destination Output file or directory. When it ends
     *                            with a slash, it is taken as directory.
     *
     * @return void
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * The setter for the attribute "toolparam"
     *
     * @param string $param Additional rst2* tool parameters
     *
     * @return void
     */
    public function setToolparam($param)
    {
        $this->toolParam = $param;
    }

    /**
     * The setter for the attribute "toolpath"
     *
     * @param $path
     * @throws BuildException
     * @internal param string $param Full path to tool path, i.e. /usr/local/bin/rst2html
     *
     * @return void
     *
     */
    public function setToolpath($path)
    {
        if (!file_exists($path)) {
            $fullpath = System::which($path);
            if ($fullpath === false) {
                throw new BuildException(
                    'Tool does not exist. Path: ' . $path
                );
            }
            $path = $fullpath;
        }
        if (!is_executable($path)) {
            throw new BuildException(
                'Tool not executable. Path: ' . $path
            );
        }
        $this->toolPath = $path;
    }

    /**
     * The setter for the attribute "uptodate"
     *
     * @param string $uptodate True/false
     *
     * @return void
     */
    public function setUptodate($uptodate)
    {
        $this->uptodate = (boolean) $uptodate;
    }

    /**
     * Add a set of files to be rendered.
     *
     * @param FileSet $fileset Set of rst files to render
     *
     * @return void
     */
    public function addFileset(FileSet $fileset)
    {
        $this->filesets[] = $fileset;
    }

    /**
     * Nested creator, creates one Mapper for this task
     *
     * @return Mapper The created Mapper type object
     *
     * @throws BuildException
     */
    public function createMapper()
    {
        if ($this->mapperElement !== null) {
            throw new BuildException(
                'Cannot define more than one mapper', $this->location
            );
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * Creates a filterchain, stores and returns it
     *
     * @return FilterChain The created filterchain object
     */
    public function createFilterChain()
    {
        $num = array_push($this->filterChains, new FilterChain($this->project));

        return $this->filterChains[$num - 1];
    }
}
