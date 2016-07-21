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

include_once 'phing/Phing.php';
include_once 'phing/Project.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/PrintStream.php';

/**
 * A little diagnostic helper that output some information that may help
 * in support. It should quickly give correct information about the
 * phing system.
 */
class Diagnostics
{
    /** utility class */
    private function __construct()
    {
        // hidden constructor
    }

    /**
     * return the list of files existing in PHING_HOME/vendor
     *
     * @param string $type
     *
     * @return array the list of jar files existing in ant.home/lib or
     *               <tt>null</tt> if an error occurs.
     */
    public static function listLibraries($type)
    {
        $home = Phing::getProperty(Phing::PHING_HOME);
        if ($home == null) {
            return null;
        }
        $currentWorkingDir = getcwd();
        chdir($home);
        exec('composer show --' . $type, $packages, $code);
        chdir($currentWorkingDir);

        return $packages;
    }

    /**
     * Print a report to the given stream.
     *
     * @param PrintStream $out the stream to print the report to.
     */
    public static function doReport(PrintStream $out)
    {
        $out->println(str_pad('Phing diagnostics report', 79, "-", STR_PAD_BOTH));
        self::header($out, "Version");
        $out->println(Phing::getPhingVersion());

        self::header($out, "Project properties");
        self::doReportProjectProperties($out);

        self::header($out, "System properties");
        self::doReportSystemProperties($out);

        self::header($out, "PHING_HOME/vendor package listing");
        self::doReportPhingVendorLibraries($out);

        self::header($out, "COMPOSER_HOME/vendor package listing");
        self::doReportComposerSystemLibraries($out);

        self::header($out, "Tasks availability");
        self::doReportTasksAvailability($out);

        self::header($out, "Temp dir");
        self::doReportTempDir($out);
    }

    private static function header(PrintStream $out, $section)
    {
        $out->println(str_repeat('-', 79));
        $out->prints(" ");
        $out->println($section);
        $out->println(str_repeat('-', 79));
    }

    /**
     * Report a listing of system properties existing in the current phing.
     *
     * @param PrintStream $out the stream to print the properties to.
     */
    private static function doReportSystemProperties(PrintStream $out)
    {
        $phing = new Phing();

        $phingprops = $phing->getProperties();

        foreach ($phingprops as $key => $value) {
            $out->println($key . " : " . $value);
        }
    }

    /**
     * Report a listing of project properties.
     *
     * @param PrintStream $out the stream to print the properties to.
     */
    private static function doReportProjectProperties(PrintStream $out)
    {
        $project = new Project();
        $project->init();

        $sysprops = $project->getProperties();

        foreach ($sysprops as $key => $value) {
            $out->println($key . " : " . $value);
        }
    }

    /**
     * Report the content of PHING_HOME/vendor directory
     *
     * @param PrintStream $out the stream to print the content to
     */
    private static function doReportPhingVendorLibraries(PrintStream $out)
    {
        $libs = self::listLibraries('installed');
        self::printLibraries($libs, $out);
    }

    /**
     * Report the content of the global composer library directory
     *
     * @param PrintStream $out the stream to print the content to
     */
    private static function doReportComposerSystemLibraries(PrintStream $out)
    {
        $libs = self::listLibraries('platform');
        self::printLibraries($libs, $out);
    }

    /**
     * list the libraries
     *
     * @param array $libs array of libraries (can be null)
     * @param PrintStream $out output stream
     */
    private static function printLibraries($libs, PrintStream $out)
    {
        if ($libs == null) {
            $out->println("No such directory.");
            return;
        }

        foreach ($libs as $lib) {
            $out->println($lib);
        }
    }

    /**
     * Create a report about all available task in phing.
     *
     * @param PrintStream $out the stream to print the tasks report to
     *                         <tt>null</tt> for a missing stream (ie mapping).
     */
    private static function doReportTasksAvailability(PrintStream $out)
    {
        $project = new Project();
        $project->init();
        $tasks = $project->getTaskDefinitions();
        ksort($tasks);
        foreach ($tasks as $shortName => $task) {
            $out->println($shortName);
        }
    }

    /**
     * try and create a temp file in our temp dir; this
     * checks that it has space and access.
     * We also do some clock reporting.
     *
     * @param PrintStream $out
     */
    private static function doReportTempDir(PrintStream $out)
    {
        $tempdir = PhingFile::getTempDir();
        if ($tempdir == null) {
            $out->println("Warning: php.tmpdir is undefined");
            return;
        }
        $out->println("Temp dir is " . $tempdir);
        $tempDirectory = new PhingFile($tempdir);

        if (!$tempDirectory->exists()) {
            $out->println("Warning, php.tmpdir directory does not exist: " . $tempdir);
            return;

        }

        $now = time();
        $tempFile = PhingFile::createTempFile('diag', 'txt', $tempDirectory);
        $fileWriter = new FileWriter($tempFile);
        $fileWriter->write('some test text');
        $fileWriter->close();

        $filetime = $tempFile->lastModified();

        $tempFile->delete();

        $out->println("Temp dir is writeable");
        $drift = $filetime - $now;
        $out->println("Temp dir alignment with system clock is " . $drift . " s");
        if (abs($drift) > 10) {
            $out->println("Warning: big clock drift -maybe a network filesystem");
        }
    }
}
