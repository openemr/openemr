<?php

// +-----------------------------------------------------------------------+
// | Copyright (c) 2002-2003, Richard Heyes, Harald Radi                        |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// |         Harald Radi <harald.radi@nme.at>                              |
// +-----------------------------------------------------------------------+
//
// $Id$

/**
* HTML_TreeMenu Class
*
* A simple couple of PHP classes and some not so simple
* Jabbascript which produces a tree menu. In IE this menu
* is dynamic, with branches being collapsable. In IE5+ the
* status of the collapsed/open branches persists across page
* refreshes.In any other browser the tree is static. Code is
* based on work of Harald Radi.
*
* Usage.
*
* After installing the package, copy the example php script to
* your servers document root. Also place the TreeMenu.js and the
* images folder in the same place. Running the script should
* then produce the tree.
*
* Thanks go to Chip Chapin (http://www.chipchapin.com) for many
* excellent ideas and improvements.
*
* @author  Richard Heyes <richard@php.net>
* @author  Harald Radi <harald.radi@nme.at>
* @access  public
* @package HTML_TreeMenu
*/

class HTML_TreeMenu
{
    /**
    * Indexed array of subnodes
    * @var array
    */
    public $items;

    /**
    * Constructor
    *
    * @access public
    */
    function __construct()
    {
        // Not much to do here :(
    }

    /**
    * This function adds an item to the the tree.
    *
    * @access public
    * @param  object $node The node to add. This object should be
    *                      a HTML_TreeNode object.
    * @return object       Returns a reference to the new node inside
    *                      the tree.
    */
    function &addItem(&$node)
    {
        $this->items[] = &$node;
        return $this->items[count($this->items) - 1];
    }

    /**
    * Import method for creating HTML_TreeMenu objects/structures
    * out of existing tree objects/structures. Currently supported
    * are Wolfram Kriesings' PEAR Tree class, and Richard Heyes' (me!)
    * Tree class (available here: http://www.phpguru.org/). This
    * method is intended to be used statically, eg:
    * $treeMenu = &HTML_TreeMenu::createFromStructure($myTreeStructureObj);
    *
    * @param  array  $params   An array of parameters that determine
    *                          how the import happens. This can consist of:
    *                            structure   => The tree structure
    *                            type        => The type of the structure, currently
    *                                           can be either 'heyes' or 'kriesing'
    *                            nodeOptions => Default options for each node
    *
    * @return object           The resulting HTML_TreeMenu object
    */
    function createFromStructure($params)
    {
        if (!isset($params['nodeOptions'])) {
            $params['nodeOptions'] = [];
        }

        switch (@$params['type']) {

            /**
            * Wolfram Kriesings' PEAR Tree class
            */
            case 'kriesing':
                $className = strtolower($params['structure']->dataSourceClass::class);
                $isXMLStruct = str_contains($className, '_xml') ? true : false;

                // Get the entire tree, the $nodes are sorted like in the tree view
                // from top to bottom, so we can easily put them in the nodes
                $nodes = $params['structure']->getNode();

                // Make a new menu and fill it with the values from the tree
                $treeMenu  = new HTML_TreeMenu();
                $curNode[0] = &$treeMenu;   // we need the current node as the reference to the

                foreach ($nodes as $aNode) {
                    $events = [];
                    $data = [];

                    // In an XML, all the attributes are saved in an array, but since they might be
                    // used as the parameters, we simply extract them here if we handle an XML-structure
                    if ($isXMLStruct && count($aNode['attributes'])) {
                        foreach ($aNode['attributes'] as $key => $val) {
                            if (!$aNode[$key]) { // dont overwrite existing values
                                $aNode[$key] = $val;
                            }
                        }
                    }

                    // Process all the data that are saved in $aNode and put them in the data and/or events array
                    foreach ($aNode as $key => $val) {
                        if (!is_array($val)) {
                            // Dont get the recursive data in here! they are always arrays
                            if (str_starts_with((string) $key, 'on')) {  // get the events
                                $events[$key] = $val;
                            }

                            // I put it in data too, so in case an options starts with 'on' its also passed to the node ... not too cool i know
                            $data[$key] = $val;
                        }
                    }

                    // Normally the text is in 'name' in the Tree class, so we check both but 'text' is used if found
                    $data['text'] = $aNode['text'] ?: $aNode['name'];

                    // Add the item to the proper node
                    $thisNode = &$curNode[$aNode['level']]->addItem(new HTML_TreeNode($data, $events));
                    $curNode[$aNode['level'] + 1] = &$thisNode;
                }
                break;

            /**
            * Richard Heyes' (me!) second (array based) Tree class
            */
            case 'heyes_array':
                // Need to create a HTML_TreeMenu object ?
                if (!isset($params['treeMenu'])) {
                    $treeMenu = new HTML_TreeMenu();
                    $parentID = 0;
                } else {
                    $treeMenu = &$params['treeMenu'];
                    $parentID = $params['parentID'];
                }

                // Loop thru the trees nodes
                foreach ($params['structure']->getChildren($parentID) as $nodeID) {
                    $data = $params['structure']->getData($nodeID);
                    $parentNode = &$treeMenu->addItem(new HTML_TreeNode(array_merge($params['nodeOptions'], $data)));

                    // Recurse ?
                    if ($params['structure']->hasChildren($nodeID)) {
                        $recurseParams['type']        = 'heyes_array';
                        $recurseParams['parentID']    = $nodeID;
                        $recurseParams['nodeOptions'] = $params['nodeOptions'];
                        $recurseParams['structure']   = &$params['structure'];
                        $recurseParams['treeMenu']    = &$parentNode;
                        HTML_TreeMenu::createFromStructure($recurseParams);
                    }
                }
                break;

            /**
            * Richard Heyes' (me!) original OO based Tree class
            */
            case 'heyes':
            default:
                // Need to create a HTML_TreeMenu object ?
                if (!isset($params['treeMenu'])) {
                    $treeMenu = new HTML_TreeMenu();
                } else {
                    $treeMenu = &$params['treeMenu'];
                }

                // Loop thru the trees nodes
                foreach ($params['structure']->nodes->nodes as $node) {
                    $tag = $node->getTag();
                    $parentNode = &$treeMenu->addItem(new HTML_TreeNode(array_merge($params['nodeOptions'], $tag)));

                    // Recurse ?
                    if (!empty($node->nodes->nodes)) {
                        $recurseParams['structure']   = $node;
                        $recurseParams['nodeOptions'] = $params['nodeOptions'];
                        $recurseParams['treeMenu']    = &$parentNode;
                        HTML_TreeMenu::createFromStructure($recurseParams);
                    }
                }
                break;
        }

        return $treeMenu;
    }
} // HTML_TreeMenu


/**
* HTML_TreeNode class
*
* This class is supplementary to the above and provides a way to
* add nodes to the tree. A node can have other nodes added to it.
*
* @author  Richard Heyes <richard@php.net>
* @author  Harald Radi <harald.radi@nme.at>
* @access  public
* @package HTML_TreeMenu
*/
class HTML_TreeNode
{
    /**
    * The text for this node.
    * @var string
    */
    public $text;

    /**
    * The link for this node.
    * @var string
    */
    public $link;

    /**
    * The icon for this node.
    * @var string
    */
    public $icon;

    /**
    * The icon to show when expanded for this node.
    * @var string
    */
    public $expandedIcon;

    /**
    * The css class for this node
    * @var string
    */
    public $cssClass;

    /**
    * The link target for this node
    * @var string
    */
    public $linkTarget;

    /**
    * Indexed array of subnodes
    * @var array
    */
    public $items;

    /**
    * Whether this node is expanded or not
    * @var bool
    */
    public $expanded;

    /**
    * Whether this node is dynamic or not
    * @var bool
    */
    public $isDynamic;

    /**
    * Should this node be made visible?
    * @var bool
    */
    public $ensureVisible;

    /**
    * The parent node. Null if top level
    * @var object
    */
    public $parent;

    public $id;

    /**
    * Constructor
    *
    * @access public
    * @param  array $options An array of options which you can pass to change
    *                        the way this node looks/acts. This can consist of:
    *                         o text          The title of the node, defaults to blank
    *                         o link          The link for the node, defaults to blank
    *                         o icon          The icon for the node, defaults to blank
    *                         o expandedIcon  The icon to show when the node is expanded
    *                         o cssClass      The CSS class for this node, defaults to blank
    *                         o expanded      The default expanded status of this node, defaults to false
    *                                         This doesn't affect non dynamic presentation types
    *                         o linkTarget    Target for the links. Defaults to linkTarget of the
    *                                         HTML_TreeMenu_Presentation.
    *                         o isDynamic     If this node is dynamic or not. Only affects
    *                                         certain presentation types.
    *                         o ensureVisible If true this node will be made visible despite the expanded
    *                                         settings, and client side persistence. Will not affect
    *                                         some presentation styles, such as Listbox. Default is false
    * @param  array $events An array of javascript events and the corresponding event handlers.
    *                       Additionally to the standard javascript events you can specify handlers
    *                       for the 'onexpand', 'oncollapse' and 'ontoggle' events which will be fired
    *                       whenever a node is collapsed and/or expanded.
    */
    function __construct($options = [], public $events = [])
    {
        $this->text          = '';
        $this->link          = '';
        $this->icon          = '';
        $this->expandedIcon  = '';
        $this->cssClass      = '';
        $this->expanded      = false;
        $this->isDynamic     = true;
        $this->ensureVisible = false;
        $this->linkTarget    = null;
        $this->id            = null;

        $this->parent        = null;

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
    * Allows setting of various parameters after the initial
    * constructor call. Possible options you can set are:
    *  o text
    *  o link
    *  o icon
    *  o cssClass
    *  o expanded
    *  o isDynamic
    *  o ensureVisible
    * ie The same options as in the constructor
    *
    * @access public
    * @param  string $option Option to set
    * @param  string $value  Value to set the option to
    */
    function setOption($option, $value)
    {
        $this->$option = $value;
    }

    /**
    * Adds a new subnode to this node.
    *
    * @access public
    * @param  object $node The new node
    */
    function &addItem($node)
    {
        $node->parent  = &$this;
        $this->items[] = &$node;

        /**
        * If the subnode has ensureVisible set it needs
        * to be handled, and all parents set accordingly.
        */
        if ($node->ensureVisible) {
            $this->_ensureVisible();
        }

        return $this->items[count($this->items) - 1];
    }

    /**
    * Private function to handle ensureVisible stuff
    *
    * @access private
    */
    function _ensureVisible()
    {
        $this->ensureVisible = true;
        $this->expanded      = true;

        if (!is_null($this->parent)) {
            $this->parent->_ensureVisible();
        }
    }
} // HTML_TreeNode


/**
* HTML_TreeMenu_Presentation class
*
* Base class for other presentation classes to
* inherit from.
*/
class HTML_TreeMenu_Presentation
{
    /**
    * The TreeMenu structure
    * @var object
    */
    public $menu;

    /**
    * Base constructor simply sets the menu object
    *
    * @param object $structure The menu structure
    */
    function __construct(&$structure)
    {
        $this->menu = &$structure;
    }

    /**
    * Prints the HTML generated by the toHTML() method.
    * toHTML() must therefore be defined by the derived
    * class.
    *
    * @access public
    * @param  array  Options to set. Any options taken by
    *                the presentation class can be specified
    *                here.
    */
    function printMenu($options = [])
    {
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }

        echo $this->toHTML();
    }
}


/**
* HTML_TreeMenu_DHTML class
*
* This class is a presentation class for the tree structure
* created using the TreeMenu/TreeNode. It presents the
* traditional tree, static for browsers that can't handle
* the DHTML.
*/
class HTML_TreeMenu_DHTML extends HTML_TreeMenu_Presentation
{
    /**
    * Path to the images
    * @var string
    */
    public $images;

    /**
    * Target for the links generated
    * @var string
    */
    public $linkTarget;

    /**
    * Whether to use clientside persistence or not
    * @var bool
    */
    public $userPersistence;

    /**
    * The default CSS class for the nodes
    */
    public $defaultClass;

    /**
    * Whether to skip first level branch images
    * @var bool
    */
    public $noTopLevelImages;

    public $maxDepth;
    public $usePersistence;

    /**
    * Constructor, takes the tree structure as
    * an argument and an array of options which
    * can consist of:
    *  o images            -  The path to the images folder. Defaults to "images"
    *  o linkTarget        -  The target for the link. Defaults to "_self"
    *  o defaultClass      -  The default CSS class to apply to a node. Default is none.
    *  o usePersistence    -  Whether to use clientside persistence. This persistence
    *                         is achieved using cookies. Default is true.
    *  o noTopLevelImages  -  Whether to skip displaying the first level of images if
    *                         there is multiple top level branches.
    *  o maxDepth          -  The maximum depth of indentation. Useful for ensuring
    *                         deeply nested trees don't go way off to the right of your
    *                         page etc. Defaults to no limit.
    *
    * And also a boolean for whether the entire tree is dynamic or not.
    * This overrides any perNode dynamic settings.
    *
    * @param object $structure The menu structure
    * @param array  $options   Array of options
    * @param bool   $isDynamic Whether the tree is dynamic or not
    */
    function __construct(&$structure, $options = [], public $isDynamic = true)
    {
        parent::__construct($structure);

        // Defaults
        $this->images           = 'public/images';
        $this->maxDepth         = 0;        // No limit
        $this->linkTarget       = '_self';
        $this->defaultClass     = '';
        $this->usePersistence   = true;
        $this->noTopLevelImages = false;

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
    * Returns the HTML for the menu. This method can be
    * used instead of printMenu() to use the menu system
    * with a template system.
    *
    * @access public
    * @return string The HTML for the menu
    */
    function toHTML()
    {
        static $count = 0;
        $menuObj     = 'objTreeMenu_' . ++$count;

        $html  = "\n";
        $html .= '<script>' . "\n\t";
        $html .= sprintf(
            '%s = new TreeMenu("%s", "%s", "%s", "%s", %s, %s);',
            $menuObj,
            $this->images,
            $menuObj,
            $this->linkTarget,
            $this->defaultClass,
            $this->usePersistence ? 'true' : 'false',
            $this->noTopLevelImages ? 'true' : 'false'
        );

        $html .= "\n";

        /**
        * Loop through subnodes
        */
        if (isset($this->menu->items)) {
            for ($i = 0; $i < count($this->menu->items); $i++) {
                $html .= $this->_nodeToHTML($this->menu->items[$i], $menuObj);
            }
        }

        $html .= sprintf("\n\t%s.drawMenu();", $menuObj);
        $html .= sprintf("\n\t%s.writeOutput();", $menuObj);

        if ($this->usePersistence && $this->isDynamic) {
            $html .= sprintf("\n\t%s.resetBranches();", $menuObj);
        }

        $html .= "\n</script>";

        return $html;
    }

    /**
    * Prints a node of the menu
    *
    * @access private
    */
    function _nodeToHTML($nodeObj, $prefix, $return = 'newNode', $currentDepth = 0, $maxDepthPrefix = null)
    {
        $prefix = empty($maxDepthPrefix) ? $prefix : $maxDepthPrefix;

        $expanded  = $this->isDynamic ? ($nodeObj->expanded  ? 'true' : 'false') : 'true';
        $isDynamic = $this->isDynamic ? ($nodeObj->isDynamic ? 'true' : 'false') : 'false';
        $html = sprintf(
            "\t %s = %s.addItem(new TreeNode(jsAttr(%s), jsAttr(%s), jsAttr(%s), %s, %s, '%s', '%s', jsAttr(%s)));\n",
            $return,
            $prefix,
            js_escape($nodeObj->text),
            !empty($nodeObj->icon) ?  js_escape($nodeObj->icon) : 'null',
            !empty($nodeObj->link) ? js_escape($nodeObj->link) : 'null',
            $expanded,
            $isDynamic,
            $nodeObj->cssClass,
            $nodeObj->linkTarget,
            !empty($nodeObj->expandedIcon) ? js_escape($nodeObj->expandedIcon) : 'null'
        );

        foreach ($nodeObj->events as $event => $handler) {
            $html .= sprintf(
                "\t %s.setEvent('%s', '%s');\n",
                $return,
                $event,
                str_replace(["\r", "\n", "'"], ['\r', '\n', "\'"], $handler)
            );
        }

        if ($this->maxDepth > 0 and $currentDepth == $this->maxDepth) {
            $maxDepthPrefix = $prefix;
        }

        /**
        * Loop through subnodes
        */
        if (!empty($nodeObj->items)) {
            for ($i = 0; $i < count($nodeObj->items); $i++) {
                $html .= $this->_nodeToHTML($nodeObj->items[$i], $return, $return . '_' . ($i + 1), $currentDepth + 1, $maxDepthPrefix);
            }
        }

        return $html;
    }
} // End class HTML_TreeMenu_DHTML


/**
* HTML_TreeMenu_Listbox class
*
* This class presents the menu as a listbox
*/
class HTML_TreeMenu_Listbox extends HTML_TreeMenu_Presentation
{
    /**
    * The text that is displayed in the first option
    * @var string
    */
    public $promoText;

    /**
    * The character used for indentation
    * @var string
    */
    public $indentChar;

    /**
    * How many of the indent chars to use
    * per indentation level
    * @var integer
    */
    public $indentNum;

    /**
    * Target for the links generated
    * @var string
    */
    public $linkTarget;

    public $submitText;

    /**
    * Constructor
    *
    * @param object $structure The menu structure
    * @param array  $options   Options whic affect the display of the listbox.
    *                          These can consist of:
    *                           o promoText  The text that appears at the the top of the listbox
    *                                        Defaults to "Select..."
    *                           o indentChar The character to use for indenting the nodes
    *                                        Defaults to "&nbsp;"
    *                           o indentNum  How many of the indentChars to use per indentation level
    *                                        Defaults to 2
    *                           o linkTarget Target for the links. Defaults to "_self"
    *                           o submitText Text for the submit button. Defaults to "Go"
    */
    function __construct($structure, $options = [])
    {
        parent::__construct($structure);

        $this->promoText  = null;
        $this->indentChar = '&nbsp;';
        $this->indentNum  = 2;
        $this->linkTarget = '_self';
        $this->submitText = 'Go';

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
    * Returns the HTML generated
    */
    function toHTML()
    {
        static $count = 0;
        $nodeHTML = '';

        /**
        * Loop through subnodes
        */
        if (isset($this->menu->items)) {
            for ($i = 0; $i < count($this->menu->items); $i++) {
                $nodeHTML .= $this->_nodeToHTML($this->menu->items[$i]);
            }
        }

        if ($this->promoText) {
            return sprintf('<option value="">%s</option>%s', text($this->promoText ?? ''), $nodeHTML);
        } else {
            return $nodeHTML;
        }
    }

    /**
    * Returns HTML for a single node
    *
    * @access private
    */
    function _nodeToHTML($node, $prefix = '')
    {
        $html = sprintf('<option value="%s">%s%s</option>', attr($node->id), $prefix, text($node->text));

        /**
        * Loop through subnodes
        */
        if (isset($node->items)) {
            for ($i = 0; $i < count($node->items); $i++) {
                $html .= $this->_nodeToHTML($node->items[$i], $prefix . str_repeat($this->indentChar, $this->indentNum));
            }
        }

        return $html;
    }
} // End class HTML_TreeMenu_Listbox
