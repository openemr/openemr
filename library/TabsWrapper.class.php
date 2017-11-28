<?php
/**
 * library/TabsWrapper.class.php
 *
 * Wrapper for implementing tabs. Currently based on jQuery UI Tabs.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see
 * http://www.gnu.org/licenses/licenses.html#GPL .
 *
 * @package OpenEMR
 * @license http://www.gnu.org/licenses/licenses.html#GPL GNU GPL V3+
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */

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
    //
    public function declareInitialTab(
        $title = 'Initial Tab', // Literals not translated here because they are just examples.
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
        if (!defined('INCLUDED_JQUERY_UI_CSS')) {
            define(INCLUDED_JQUERY_UI_CSS, '1-12-1');
            $s .= "<link rel='stylesheet' href='$web_root/public/assets/jquery-ui-" . INCLUDED_JQUERY_UI_CSS . "/themes/base/jquery-ui.css'>\n";
        }
        $s .= <<<EOD
<style>
EOD;
        if (!defined('INCLUDED_TW_ONETIME_CSS')) {
            define(INCLUDED_TW_ONETIME_CSS, true);
            $s .= <<<EOD
.ui-tabs .ui-tabs-panel {
  padding: 0px 0px;
}
.ui-tabs .ui-tabs-nav .ui-tabs-anchor {
  padding: .1em .4em;
}
EOD;
        }
        $s .= <<<EOD
#{$this->tabsid} li .ui-icon-close {
  float: left;
  margin: 0.2em 0.2em 0 0;
  cursor: pointer;
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
        if (!defined('INCLUDED_JQUERY_UI')) {
            define(INCLUDED_JQUERY_UI, '1-12-1');
            $s .= "<script src='$web_root/public/assets/jquery-ui-" . INCLUDED_JQUERY_UI . "/jquery-ui.min.js'></script>\n";
        }
        if (!defined('INCLUDED_TW_ONETIME_JS')) {
            define(INCLUDED_TW_ONETIME_JS, true);
            $s .= <<<EOD
<script>

// We use one object to contain an object of context for each tab set.
// Most windows will have only one tab set but we cater to a more general case.
// This avoids excessive pollution of the window's name space.
var twObject = {};

// Call this to initialize a tab set.
// tabsid is a unique identifier usable as a DOM element ID.
function twSetup(tabsid) {
  var tabs = $('#' + tabsid).tabs({
    heightStyle: "content"
  });
  twObject[tabsid] = {};
  twObject[tabsid].tabs = tabs;
  twObject[tabsid].counter = 100;
  // Close icon: removing the tab on click
  tabs.on("click", "span.ui-icon-close", function() {
    var mytabsid = $(this).closest("div").attr("id");
    var panelId = $(this).prev().attr("href").substring(1);
    twCloseTab(mytabsid, panelId);
  });
}

// Get the ID that will be used for the next added tab. Nothing is changed.
// This may be useful as an iframe's name so it can later call twCloseTab().
function twNextTabId(tabsid) {
  return tabsid + '-' + (twObject[tabsid].counter + 1);
}

// Add a new tab to the specified tab set and make it the selected tab.
function twAddTab(tabsid, label, content) {
  var oldcount = twObject[tabsid].tabs.find(".ui-tabs-nav li").length;
  var panelId = tabsid + '-' + (++twObject[tabsid].counter);
  var li = "<li><a href='#" + panelId + "'>" + label + "</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>";
  twObject[tabsid].tabs.find(".ui-tabs-nav").append(li);
  twObject[tabsid].tabs.append("<div id='" + panelId + "'>" + content + "</div>");
  twObject[tabsid].tabs.tabs("refresh");
  twObject[tabsid].tabs.tabs("option", "active", oldcount);
  return panelId;
}

// Add a new tab using an iframe loading a specified URL.
function twAddFrameTab(tabsid, label, url) {
  var panelId = twNextTabId(tabsid);
  twAddTab(
    tabsid,
    label,
    "<iframe name='" + panelId + "' frameborder='0' style='height:100%;width:100%;' src='" + url + "'>Oops</iframe>"
  );
  return panelId;
}

// Remove the specified tab from the specified tab set.
function twCloseTab(tabsid, panelId) {
  twObject[tabsid].tabs.find("[href='#" + panelId + "']").closest("li").remove();
  twObject[tabsid].tabs.find("#" + panelId).remove();
  twObject[tabsid].tabs.tabs("refresh");
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
        $s .= "<div id='{$this->tabsid}'><ul>\n";
        $i = 0;
        foreach ($this->tabs as $val) {
            ++$i;
            $s .= "<li><a href='#{$this->tabsid}-$i'>" . text($val['title']) . "</a>";
            if ($val['closeable']) {
                $s .= " <span class='ui-icon ui-icon-close' role='presentation'>" . xlt('Remove Tab') . "</span>";
            }
            $s .= "</li>\n";
        }
        $s .= "</ul>\n";
        $i = 0;
        foreach ($this->tabs as $val) {
            ++$i;
            $s .= "<div id='{$this->tabsid}-$i'>\n";
            $s .= $val['content'];
            $s .= "</div>\n";
        }
        $s .= "</div>\n";
        return $s;
    }
}
