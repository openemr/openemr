<?php
/*
 *  $Id: d35bf0481e55f713cf67a71878d978f224baa05e $
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
include_once 'phing/filters/ConcatFilter.php';
include_once 'phing/filters/HeadFilter.php';
include_once 'phing/filters/IconvFilter.php';
include_once 'phing/filters/TailFilter.php';
include_once 'phing/filters/LineContains.php';
include_once 'phing/filters/LineContainsRegexp.php';
include_once 'phing/filters/EscapeUnicode.php';
include_once 'phing/filters/ExpandProperties.php';
include_once 'phing/filters/PhpArrayMapLines.php';
include_once 'phing/filters/PrefixLines.php';
include_once 'phing/filters/ReplaceRegexp.php';
include_once 'phing/filters/ReplaceTokens.php';
include_once 'phing/filters/ReplaceTokensWithFile.php';
include_once 'phing/filters/SortFilter.php';
include_once 'phing/filters/StripPhpComments.php';
include_once 'phing/filters/StripLineBreaks.php';
include_once 'phing/filters/StripLineComments.php';
include_once 'phing/filters/StripWhitespace.php';
include_once 'phing/filters/SuffixLines.php';
include_once 'phing/filters/TabToSpaces.php';
include_once 'phing/filters/TidyFilter.php';
include_once 'phing/filters/TranslateGettext.php';
include_once 'phing/filters/XincludeFilter.php';
include_once 'phing/filters/XsltFilter.php';

/**
 * FilterChain may contain a chained set of filter readers.
 *
 * @author    Yannick Lecaillez <yl@seasonfive.com>
 * @version   $Id: d35bf0481e55f713cf67a71878d978f224baa05e $
 * @package   phing.types
 */
class FilterChain extends DataType
{

    private $filterReaders = array();

    /**
     * @param null $project
     */
    public function __construct($project = null)
    {
        if ($project) {
            $this->project = $project;
        }
    }

    /**
     * @return array
     */
    public function getFilterReaders()
    {
        return $this->filterReaders;
    }

    /**
     * @param ConcatFilter $o
     */
    public function addConcatFilter(ConcatFilter $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param ExpandProperties $o
     */
    public function addExpandProperties(ExpandProperties $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param TranslateGettext $o
     */
    public function addGettext(TranslateGettext $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param HeadFilter $o
     */
    public function addHeadFilter(HeadFilter $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param IconvFilter $o
     */
    public function addIconvFilter(IconvFilter $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param TailFilter $o
     */
    public function addTailFilter(TailFilter $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param LineContains $o
     */
    public function addLineContains(LineContains $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param LineContainsRegexp $o
     */
    public function addLineContainsRegExp(LineContainsRegexp $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param PrefixLines $o
     */
    public function addPrefixLines(PrefixLines $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param SuffixLines $o
     */
    public function addSuffixLines(SuffixLines $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param PrefixLines $o
     */
    public function addEscapeUnicode(EscapeUnicode $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param PhpArrayMapLines $o
     */
    public function addPhpArrayMapLines(PhpArrayMapLines $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param ReplaceTokens $o
     */
    public function addReplaceTokens(ReplaceTokens $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param ReplaceTokensWithFile $o
     */
    public function addReplaceTokensWithFile(ReplaceTokensWithFile $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param ReplaceRegexp $o
     */
    public function addReplaceRegexp(ReplaceRegexp $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param StripPhpComments $o
     */
    public function addStripPhpComments(StripPhpComments $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param StripLineBreaks $o
     */
    public function addStripLineBreaks(StripLineBreaks $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param StripLineComments $o
     */
    public function addStripLineComments(StripLineComments $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param StripWhitespace $o
     */
    public function addStripWhitespace(StripWhitespace $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param TidyFilter $o
     */
    public function addTidyFilter(TidyFilter $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param TabToSpaces $o
     */
    public function addTabToSpaces(TabToSpaces $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param XincludeFilter $o
     */
    public function addXincludeFilter(XincludeFilter $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param XsltFilter $o
     */
    public function addXsltFilter(XsltFilter $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param PhingFilterReader $o
     */
    public function addFilterReader(PhingFilterReader $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /**
     * @param SortFilter $o
     */
    public function addSortFilter(SortFilter $o)
    {
        $o->setProject($this->project);
        $this->filterReaders[] = $o;
    }

    /*
     * Makes this instance in effect a reference to another FilterChain
     * instance.
     *
     * <p>You must not set another attribute or nest elements inside
     * this element if you make it a reference.</p>
     *
     * @param  $r the reference to which this instance is associated
     * @throws BuildException if this instance already has been configured.
    */
    /**
     * @param Reference $r
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {

        if (count($this->filterReaders) !== 0) {
            throw $this->tooManyAttributes();
        }

        // change this to get the objects from the other reference
        $o = $r->getReferencedObject($this->getProject());
        if ($o instanceof FilterChain) {
            $this->filterReaders = $o->getFilterReaders();
        } else {
            throw new BuildException($r->getRefId() . " doesn't refer to a FilterChain");
        }
        parent::setRefid($r);
    }
}
