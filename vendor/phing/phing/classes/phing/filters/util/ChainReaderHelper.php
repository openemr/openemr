<?php
/*
 *  $Id: 46536018ea0389c5baca8c49666d35c7524ee15b $
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

include_once 'phing/Project.php';
include_once 'phing/filters/BaseFilterReader.php';
include_once 'phing/types/PhingFilterReader.php';
include_once 'phing/types/FilterChain.php';
include_once 'phing/types/Parameter.php';
include_once 'phing/util/FileUtils.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Process a FilterReader chain.
 *
 * Here, the interesting method is 'getAssembledReader'.
 * The purpose of this one is to create a simple Reader object which
 * apply all filters on another primary Reader object.
 *
 * For example : In copyFile (phing.util.FileUtils) the primary Reader
 * is a FileReader object (more accuratly, a BufferedReader) previously
 * setted for the source file to copy. So, consider this filterchain :
 *
 *     <filterchain>
 *        <stripphpcomments />
 *        <linecontains>
 *            <contains value="foo">
 *        </linecontains>
 *      <tabtospaces tablength="8" />
 *    </filterchain>
 *
 *    getAssembledReader will return a Reader object wich read on each
 *    of these filters. Something like this : ('->' = 'which read data from') :
 *
 *  [TABTOSPACES] -> [LINECONTAINS] -> [STRIPPHPCOMMENTS] -> [FILEREADER]
 *                                                         (primary reader)
 *
 *  So, getAssembledReader will return the TABTOSPACES Reader object. Then
 *  each read done with this Reader object will follow this path.
 *
 *    Hope this explanation is clear :)
 *
 * TODO: Implement the classPath feature.
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @version   $Id: 46536018ea0389c5baca8c49666d35c7524ee15b $
 * @package   phing.filters.util
 */
class ChainReaderHelper
{

    /** Primary reader to wich the reader chain is to be attached */
    private $primaryReader = null;

    /** The site of the buffer to be used. */
    private $bufferSize = 8192;

    /** Chain of filters */
    private $filterChains = array();

    /** The Phing project */
    private $project;

    /*
     * Sets the primary reader
    */
    /**
     * @param Reader $reader
     */
    public function setPrimaryReader(Reader $reader)
    {
        $this->primaryReader = $reader;
    }

    /*
     * Set the project to work with
    */
    /**
     * @param Project $project
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    /*
     * Get the project
    */
    public function getProject()
    {
        return $this->project;
    }

    /*
     * Sets the buffer size to be used.  Defaults to 8192,
     * if this method is not invoked.
    */
    /**
     * @param $size
     */
    public function setBufferSize($size)
    {
        $this->bufferSize = $size;
    }

    /*
     * Sets the collection of filter reader sets
    */
    /**
     * @param $fchain
     */
    public function setFilterChains(&$fchain)
    {
        $this->filterChains = & $fchain;
    }

    /*
     * Assemble the reader
    */
    /**
     * @return FilterReader|null|Parameterizable|Reader
     * @throws Exception
     */
    public function getAssembledReader()
    {

        $instream = $this->primaryReader;
        $filterReadersCount = count($this->filterChains);
        $finalFilters = array();

        // Collect all filter readers of all filter chains used ...
        for ($i = 0; $i < $filterReadersCount; $i++) {
            $filterchain = & $this->filterChains[$i];
            $filterReaders = $filterchain->getFilterReaders();
            $readerCount = count($filterReaders);
            for ($j = 0; $j < $readerCount; $j++) {
                $finalFilters[] = $filterReaders[$j];
            }
        }

        // ... then chain the filter readers.
        $filtersCount = count($finalFilters);
        if ($filtersCount > 0) {
            for ($i = 0; $i < $filtersCount; $i++) {
                $filter = $finalFilters[$i];

                if ($filter instanceof PhingFilterReader) {

                    // This filter reader is an external class.
                    $className = $filter->getClassName();
                    $classpath = $filter->getClasspath();
                    $project = $filter->getProject();

                    if ($className !== null) {
                        $cls = Phing::import($className, $classpath);
                        $impl = new $cls();
                    }

                    if (!($impl instanceof FilterReader)) {
                        throw new Exception($className . " does not extend phing.system.io.FilterReader");
                    }

                    $impl->setReader($instream); // chain
                    $impl->setProject($this->getProject()); // what about $project above ?

                    if ($impl instanceof Parameterizable) {
                        $impl->setParameters($filter->getParams());
                    }

                    $instream = $impl; // now that it's been chained

                } elseif (($filter instanceof ChainableReader) && ($filter instanceof Reader)) {
                    if ($this->getProject() !== null && ($filter instanceof BaseFilterReader)) {
                        $filter->setProject($this->getProject());
                    }
                    $instream = $filter->chain($instream);
                } else {
                    throw new Exception("Cannot chain invalid filter: " . get_class($filter));
                }
            }
        }

        return $instream;
    }

}
