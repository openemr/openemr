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
  color: var(--danger) !important;
}
.tabs-anchor {
  margin-right: 3px;
  color: var(--white);
  display: block;
  background: var(--gray);
  text-decoration: none !important;
}
.tabs-anchor:hover {
  color: var(--white);
  text-decoration: none !important;
}
a.active {
  color: var(--white);
  background: var(--primary);
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
            $modalTitle = xla("Warning");
            $modalContent = xla("Do you want to close the tabs?");
            $modalCancel = xla("Cancel");
            $modalClose = xla("Close");
            $message = xlj("Fee sheet tab is already opened");
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
    const self = $(this);
    const closeTab = function() {
        const panelId = self.parent().attr("href").substring(1);
        top.restoreSession();
        twCloseTab(tabsid, panelId);
    }

    const closeSoap = function() {
        top.isSoapEdit = false;
        closeTab();
    }

    if (self[0].id === 'SOAP' && top.isSoapEdit === true) {
        dlgopen('', '', 450, 125, '', '<div class="text-danger">$modalTitle</div>', {
            type: 'Alert',
            html: '<p>$modalContent</p>',
            buttons: [
                {text: '$modalCancel', close: true, style: 'default btn-sm'},
                {text: '$modalClose', close: true, style: 'danger btn-sm', click: closeSoap},
            ],
            allowDrag: false,
            allowResize: false,
        });
    } else {
        closeTab();
    }
  });
}

// Get the ID that will be used for the next added tab. Nothing is changed.
// This may be useful as an iframe's name so it can later call twCloseTab().

function activateTab(tab){
  $('.nav-tabs a[href="#' + tab + '"]').tab('show');
};

function nextPanelId(tabsid){
  return tabsid + '-' + twObject[tabsid].counter + 1;
}

// Add a new tab to the specified tab set and make it the selected tab.
function twAddTab(tabsid, label, content) {
  var oldcount = twObject[tabsid].nav.find(".nav-tabs li").length;
  var panelId = nextPanelId(tabsid);
  var li = "<li class='tabs-tabs'><a data-toggle='tab' class='tabs-anchor' href='#" + panelId + "'>" + label + "<span aria-label='close' class='icon-close' id='" + label + "' role='close'>&times;</span></a> </li>";
  twObject[tabsid].nav.append(li);
  top.restoreSession();
  twObject[tabsid].content.append("<div class='tab-pane tabs-panel' id='" + panelId + "'>" + content + "</div>");
  twObject[tabsid].counter++;
  activateTab(panelId);
  return panelId;
}

var execute = false;
var temp;
// Add a new tab using an iframe loading a specified URL.
function twAddFrameTab(tabsid, label, url) {
  var panelId = nextPanelId(tabsid);
  top.restoreSession();
  if (label === "Fee Sheet") {
    if (!execute) {
      twAddTab(
        tabsid,
        label,
        "<iframe name='" + panelId + "' class='w-100' style='height:94.5vh;border: 0;' src='" + url + "'>Oops</iframe>"
      );
      execute = true;
      temp = panelId;
      return panelId;
    } else {
      asyncAlertMsg($message, 3000, 'warning','') ;
      return false;
    }
  } else {
    twAddTab(
      tabsid,
      label,
      "<iframe name='" + panelId + "' class='w-100' style='height:94.5vh;border: 0;' src='" + url + "'>Oops</iframe>"
    );
    return panelId;
  }
  
  
}

// Remove the specified tab from the specified tab set.
function twCloseTab(tabsid, panelId) {
  let lastTabId = twObject[tabsid].content.find("#" + panelId).prev().attr('id');
  twObject[tabsid].nav.find("[href='#" + panelId + "']").closest("li").remove();
  twObject[tabsid].content.find("#" + panelId).remove();
  top.restoreSession();
  activateTab(lastTabId);

  if(panelId === temp){
    execute = false;
  }
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
            $activateTab = count($this->tabs) == $i ? 'active' : '';
            $s .= "<li class='tabs-tabs' ><a data-toggle='tab' class='tabs-anchor {$activateTab}' href='#{$this->tabsid}-$i'>" . text($val['title']);
            if ($val['closeable']) {
                $s .= " <span aria-label='close' class='icon-close' role='close'>&times;</span>";
            }
            $s .= "</a> </li>\n";
        }
        $s .= "</ul>\n";
        $s .= "<div class='tab-content' id='{$this->tabsid}-tabs'>";
        $i = 0;
        foreach ($this->tabs as $val) {
            ++$i;
            $activateTab = count($this->tabs) == $i ? 'active' : '';
            $s .= "<div class='tab-pane tabs-panel {$activateTab}' id='{$this->tabsid}-$i'>\n";
            $s .= $val['content'];
            $s .= "</div>\n";
        }
        $s .= "</div>\n";
        $s .= "</div>\n";
        return $s;
    }
}
