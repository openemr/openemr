<?php

/**
 * PatientDocumentTreeViewFilterEvent is fired when patient documents are rendered in the html tree view widget.  It
 * enables event listeners to modify the html element that is displayed for each patient document.
 * @see Carecoordination\Listener\CCDAEventsSubscriber::onPatientDocumentTreeViewFilter for an example.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientDocuments;

use HTML_TreeNode;
use CategoryTree;

class PatientDocumentTreeViewFilterEvent
{
    const EVENT_NAME = "patient.document.tree.view.filter";

    /**
     * @var CategoryTree
     */
    private $categoryTreeNode;

    /**
     * @var \HTML_TreeNode
     */
    private $htmlTreeNode;

    /**
     * @var ?string
     */
    private $documentId;

    /**
     * @var ?string
     */
    private $documentName;

    /**
     * @var ?string
     */
    private $categoryId;

    /**
     * @var array
     */
    private $categoryInfo;

    /**
     * @var ?int
     */
    private $pid;

    public function __construct()
    {
    }

    /**
     * @return CategoryTree
     */
    public function getCategoryTreeNode(): CategoryTree
    {
        return $this->categoryTreeNode;
    }

    /**
     * @param CategoryTree $categoryTreeNode
     * @return PatientDocumentTreeViewFilterEvent
     */
    public function setCategoryTreeNode(CategoryTree $categoryTreeNode): PatientDocumentTreeViewFilterEvent
    {
        $this->categoryTreeNode = $categoryTreeNode;
        return $this;
    }

    /**
     * @return HTML_TreeNode
     */
    public function getHtmlTreeNode(): HTML_TreeNode
    {
        return $this->htmlTreeNode;
    }

    /**
     * @param HTML_TreeNode $htmlTreeNode
     * @return PatientDocumentTreeViewFilterEvent
     */
    public function setHtmlTreeNode(HTML_TreeNode $htmlTreeNode): PatientDocumentTreeViewFilterEvent
    {
        $this->htmlTreeNode = $htmlTreeNode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     * @return PatientDocumentTreeViewFilterEvent
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param mixed $documentId
     * @return PatientDocumentTreeViewFilterEvent
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDocumentName()
    {
        return $this->documentName;
    }

    /**
     * @param mixed $documentName
     * @return PatientDocumentTreeViewFilterEvent
     */
    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     * @return PatientDocumentTreeViewFilterEvent
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategoryInfo(): array
    {
        return $this->categoryInfo;
    }

    /**
     * @param array $categoryInfo
     * @return PatientDocumentTreeViewFilterEvent
     */
    public function setCategoryInfo(array $categoryInfo): PatientDocumentTreeViewFilterEvent
    {
        $this->categoryInfo = $categoryInfo;
        return $this;
    }
}
