<?php
/**
 * Wrapper for implementing tabs. Currently based on jQuery UI Tabs.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tabs;

class TabsWrapper
{
    public $tabsid;
    public $tabs = array();

    function __construct($tabsid = 'tabs')
    {
        $this->tabsid = $tabsid;
    }

    // Declare an initial tab that will not be dynamically created.
    // Commonly this is called once but you can make as many as you want, or none.
    // Literal arguments below are not translated because they are just examples.
    //
    public function declareInitialTab(
        $title = 'Initial Tab',
        $content = '<p>Content of first tab.</p>',
        $closeable = false
    ) {
        $this->tabs[] = array(
            'title'     => $title,
            'content'   => $content,
            'closeable' => $closeable,
        );
    }

    // Generate styling. Call once for each tab set.
    //
    public function genCss()
    {
        global $web_root;
        $s = '';
        // New convention here. Because this class is a wrapper that is supposed to insulate the
        // caller from the underlying implementation, we cannot expect the caller to make sure that
        // the required DOM libraries have been included. However we cannot blindly load them
        // ourselves because the caller might have already done that for some other reason.
        // Therefore we invent a general purpose convention for communicating which DOM libraries
        // are included and will implement it as needed.
        // Note: require.js may be another option, but trying to keep it simple for now.
        $s .= <<<EOD
<style>
EOD;
        if (!defined('INCLUDED_TW_ONETIME_CSS')) {
            define('INCLUDED_TW_ONETIME_CSS', true);
            $s .= <<<EOD
        .tabs .tabs-panel {
          padding: 0px 0px;
        }
        .tabs .tabs-nav .tabs-anchor {
          padding: .1em .4em;
        }
EOD;
            if ($_SESSION['language_direction'] == 'rtl') {
                $s .= <<<EOD
            .tabs { direction: rtl; }
            .tabs .tabs-nav li.tabs-tab {float: right; }
            .tabs .tabs-nav li a { float: right; }
                
EOD;
            }
        }


        $s .= <<<EOD
#{$this->tabsid} li .icon-close {
  font-weight: bolder;
  margin: 0.2em 0.2em 0 3px;
  cursor: pointer;
}
#{$this->tabsid} li .icon-close:hover {
  color: red!important;
}
.tabs-anchor {
  margin-right: 3px;
  color: white;
  display: block;
  background: grey;
  text-decoration: none !important;
}
.tabs-anchor:hover {
  color: white;
  text-decoration: none !important;
}
a.active {
  color: white ;
  background: #007FFF;
}
</style>
EOD;
        return $s;
    }

    // Generate JavaScript.
    //
    public static function genJavaScript()
    {
        global $web_root;
        $s = '';
        if (!defined('INCLUDED_TW_ONETIME_JS')) {
            define('INCLUDED_TW_ONETIME_JS', true);
            $s .= <<<EOD
<script>

// We use one object to contain an object of context for each tab set.
// Most windows will have only one tab set but we cater to a more general case.
// This avoids excessive pollution of the window's name space.
var twObject = {};

// Call this to initialize a tab set.
// tabsid is a unique identifier usable as a DOM element ID.
function twSetup(tabsid) {
  var nav = $('#' + tabsid);
  var content = $('#' + tabsid + '-tabs');
  twObject[tabsid] = {};
  twObject[tabsid].nav = nav;
  twObject[tabsid].content = content;
  twObject[tabsid].counter = 100;
  // Close icon: removing the tab on click
  nav.on("click", "span.icon-close", function() {
    var panelId = $(this).parent().attr("href").substring(1);
    top.restoreSession();
    twCloseTab(tabsid, panelId);
  });
}

// Get the ID that will be used for the next added tab. Nothing is changed.
// This may be useful as an iframe's name so it can later call twCloseTab().
function twNextTabId(tabsid) {
  return tabsid + '-' + ++twObject[tabsid].counter;
}

function activateTab(tab){
  $('.nav-tabs a[href="#' + tab + '"]').tab('show');
};

// Add a new tab to the specified tab set and make it the selected tab.
function twAddTab(tabsid, label, content) {
  var oldcount = twObject[tabsid].nav.find(".nav-tabs li").length;
  var panelId = tabsid + '-' + (++twObject[tabsid].counter);
  var li = "<li class='tabs-tabs'><a data-toggle='tab' class='tabs-anchor' href='#" + panelId + "'>" + label + "<span aria-label='close' class='icon-close' role='close'>X</span></a> </li>";
  twObject[tabsid].nav.append(li);
  top.restoreSession();
  twObject[tabsid].content.append("<div class='tab-pane tabs-panel' id='" + panelId + "'>" + content + "</div>");
  activateTab(panelId);
  return panelId;
}

// Add a new tab using an iframe loading a specified URL.
function twAddFrameTab(tabsid, label, url) {
  var panelId = twNextTabId(tabsid);
  top.restoreSession();
  twAddTab(
    tabsid,
    label,
    "<iframe name='" + panelId + "' frameborder='0' style='height:95.3%;width:100%;' src='" + url + "'>Oops</iframe>"
  );
  return panelId;
}

// Remove the specified tab from the specified tab set.
function twCloseTab(tabsid, panelId) {
  let lastTabId = twObject[tabsid].content.find("#" + panelId).prev().attr('id');;
  twObject[tabsid].nav.find("[href='#" + panelId + "']").closest("li").remove();
  twObject[tabsid].content.find("#" + panelId).remove();
  top.restoreSession();
  activateTab(lastTabId);
}

</script>
EOD;
        }
        return $s;
    }

    // Generate HTML for the initial state of this tab set.
    // Be sure to first call declareInitialTab() for each initial tab desired, if any.
    public function genHtml()
    {
        $s = '';
        $s .= "<div class='tabs'>\n";
        $s .= "<ul id='{$this->tabsid}' class='nav nav-tabs tabs-nav'>\n";
        $i = 0;
        foreach ($this->tabs as $val) {
            ++$i;
            $s .= "<li class='tabs-tabs' ><a data-toggle='tab' class='tabs-anchor active' href='#{$this->tabsid}-$i'>" . text($val['title']) . "</a>";
            if ($val['closeable']) {
                $s .= " <span class='icon icon-close' role='presentation'>" . xlt('Remove Tab') . "</span>";
            }
            $s .= "</li>\n";
        }
        $s .= "</ul>\n";
        $s .= "<div class='tab-content' id='{$this->tabsid}-tabs'>";
        $i = 0;
        foreach ($this->tabs as $val) {
            ++$i;
            $s .= "<div class='tab-pane tabs-panel active' id='{$this->tabsid}-$i'>\n";
            $s .= $val['content'];
            $s .= "</div>\n";
        }
        $s .= "</div>\n";
        $s .= "</div>\n";
        return $s;
    }
}
