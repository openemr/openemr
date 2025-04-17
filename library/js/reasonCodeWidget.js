/**
 * reasonCodeWidget is used with observation,vitals, and procedures to toggle and select reason codes and their accompanying
 * statii / text descriptions.  It requires a launch button to be present with the selector attribute of .reason-code-btn
 *
 * The launch button must have a data-toggle-container property with the id of the DOM node containing the reason window
 * that you want to be displayed on the screen.  The widget will setup the code selector widget on the input that has
 * a class of .code-selector-popup.  It will also populate the following fields if they are present in the DOM:
 *
 * .code-selector-popup -> text code that will be sent to the server.  In the format of CODE_SYSTEM:CODE
 * .code-selector-text-display -> text description of the selected code or empty if code is deleted
 * .code-selector-text -> text descriptoin of the selected code or empty (intended to be a hidden attribute to be sent to the server)
 *
 * If the reasonCodeWidget is toggled off it will clear all of the reason code values inside the container.
 *
 * Widgets can be instantiated by calling the init() method, cleanup is done via the destroy() method.  If you are manipulating
 * the DOM (copying, moving, etc) and want to clear event handlers and reinstantiate you can call the reload() method.
 *
 * Note only one code selector window can be instantiated at a time.  The widget will override the default set_related callback
 * in the document window and restore it once the find code popup window closes.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function(window, oeUI) {

    let widgetPtrs = [];
    let previousSelCodeFunction = window.set_related || null;
    let webroot = null;
    let reasonCodeTypes = "";
    let currentWindowOpenWidget = null;

    function getNegationContainerFromWidget(widget) {
        if (!(widget && widget.dataset)) {
            window.console.error("Failed to find reason code button");
            return false;
        }
        let containerId = widget.dataset['toggleContainer'];
        if (!containerId) {
            window.console.error("Missing data-toggle-container");
            return false;
        }
        let node = document.getElementById(containerId);
        if (!node) {
            console.error("Failed to find reason widget container node with id " + containerId);
        }
        return node;
    }

    function noop() {
        // do nothing here.
    }

        /**
         * Represents a single instance of a ReasonCode Widget.  Tracks the dom nodes and event callbacks associated
         * with this instance.
         * @param btnToggleNode
         * @constructor
         */
    function Widget(btnToggleNode) {
        var _this = this;

        this.btnToggle = btnToggleNode;
        this.reasonCodeContainer = getNegationContainerFromWidget(btnToggleNode);
        this.reasonCodeInput = null;
        this.reasonCodeText = null;
        this.reasonCodeTextInput = null;
        this.reasonStatusNode = null;

        this.handleCodeSelected = function(codetype, code, selector, codedesc) {
            // restore this first thing.
            _this.restoreCallback(); // if there is any error we want to restore our window callback first thing.
            if (typeof codetype != "string") {
                // called externally so we have to check this
                console.error("codetype was invalid data type");
            }
            if (typeof code != "string") {
                console.error("code was invalid data type");
            }
            if (typeof codedesc != "string") {
                console.error("codedesc was invalid data type");
            }

            if (_this.reasonCodeInput) {
                if (codetype.trim() !== "") {
                    _this.reasonCodeInput.value = (codetype + ":" + code);
                } else {
                    _this.reasonCodeInput.value = "";
                }
            }
            if (_this.reasonCodeTextInput) {
                _this.reasonCodeTextInput.value = (codedesc || "").trim();
            }
            // hide out our text if we arempty
            if (_this.reasonCodeText) {
                _this.reasonCodeText.innerText  = (codedesc || "").trim();
                if (codedesc.trim() != "") {
                    _this.reasonCodeText.classList.remove("d-none");
                } else {
                    _this.reasonCodeText.classList.add("d-none");
                }
            }
        };

        this.restoreCallback = function() {
            // could be undefined but we need this for the code selector anyways
            window.set_related = previousSelCodeFunction;
        };

        this.launchCodeSelector = function() {
            let opts = {
                callBack: {
                    call: _this.restoreCallback
                }
            };
            // we are going to replace our global function for the time, and it will get setback in the callback
            previousSelCodeFunction = window.set_related;
            window.set_related =  _this.handleCodeSelected;
            window.top.restoreSession();
            let urlFragment = "?default=SNOMED-CT";
            if (reasonCodeTypes) {
                urlFragment = "?codetype=" + reasonCodeTypes;
            }
            window.dlgopen(webroot + "/interface/patient_file/encounter/find_code_popup.php" + urlFragment
                , '_blank', 700, 400, false, undefined, opts);
        };

        this.destroy = function() {
            if (_this.reasonCodeInput) {
                _this.reasonCodeInput.removeEventListener('click', _this.launchCodeSelector);
            }
            if (_this.btnToggle) {
                _this.btnToggle.removeEventListener('click', toggleReasonCode);
            }
            _this.reasonCodeInput = null;
            _this.reasonCodeText = null;
            _this.reasonCodeTextInput = null;
            _this.reasonStatusNode = null;
            _this.btnToggle = null;
            _this.reasonCodeContainer = null;
        };

        this.show = function() {
            toggleDisplay(true);
        };

        this.hide = function() {
            toggleDisplay(false);
        };

        function toggleDisplay(shouldDisplay) {
            if (shouldDisplay) {
                _this.reasonCodeContainer.classList.remove("d-none");
            } else {
                _this.reasonCodeContainer.classList.add("d-none");
                resetValues();
            }
        }

        function resetValues() {
            // reset our values here
            if (_this.reasonCodeInput) {
                _this.reasonCodeInput.value = "";

            }
            if (_this.reasonCodeText) {
                _this.reasonCodeText.innerText = "";
                _this.reasonCodeText.classList.add("d-none");
            }
            if (_this.reasonCodeTextInput) {
                _this.reasonCodeTextInput.value = "";
            }
            if (_this.reasonStatusNode) {
                _this.reasonStatusNode.selectedIndex = 0;
            }
        }

        function toggleReasonCode(event) {
            event.preventDefault();
            var target = event.currentTarget;
            if (!(target && target.dataset)) {
                window.console.error("Failed to find reason code button");
                return false;
            }
            toggleDisplay(_this.reasonCodeContainer.classList.contains("d-none"))
            return false;
        }

        function init() {

            if (_this.reasonCodeContainer) {
                _this.reasonCodeInput = _this.reasonCodeContainer.querySelector('.code-selector-popup');
                if (_this.reasonCodeInput) {
                    _this.reasonCodeInput.addEventListener('click', _this.launchCodeSelector);
                } else {
                    console.error("Failed to find input node with selector .code-selector-popup");
                }
                _this.reasonCodeText = _this.reasonCodeContainer.querySelector('.code-selector-text-display');
                _this.reasonCodeTextInput = _this.reasonCodeContainer.querySelector('.code-selector-text');
                _this.reasonStatusNode = _this.reasonCodeContainer.querySelector('select');
            } else {
                window.console.error("Failed to find reasonCodeContainer ")
            }

            if (_this.btnToggle && btnToggleNode.addEventListener) {
                _this.btnToggle.addEventListener('click', toggleReasonCode);
            }
            else {
                window.console.error("Widget called to be setup but was null");
            }

        }
        init();
    }

    function destroyWidget(widget) {
        if (widget && widget.removeEventListener) {
            widget.removeEventListener('click', toggleNegationRationale);
        } else {
            window.console.error("Widget called to be destroyed but was not a valid widget", widget);
        }
    }

    function destroyWidgetList() {
        if (widgetPtrs && widgetPtrs.length) {
            widgetPtrs.forEach(function(widget) {
                widget.destroy();
            });
        }
        widgetPtrs = [];
    }

    function reload() {
        destroy();
        init(webroot); // make sure we keep the same webroot here.
    }

    function init(webRootValue, reasonCodeTypes) {
        widgetPtrs = [];
        // destroy if we have anything setup right now
        widgetPtrNodes = document.querySelectorAll('.reason-code-btn');
        if (!(widgetPtrNodes && widgetPtrNodes.forEach)) {
            return; // nothing to do here
        }
        widgetPtrNodes.forEach(function(node) {
            let widget = new Widget(node);
            widgetPtrs.push(widget);
        });
        webroot = webRootValue;
    }
    function destroy() {
        destroyWidgetList();
    }

    function getWidgetForNode(node) {
        return widgetPtrs.filter(function(widget) {
            return widget.btnToggle == node;
        });
    }

    // any other functions can go here
    oeUI.reasonCodeWidget = {
        init: init
        ,destroy: destroy
        ,reload: reload
        ,getWidget: getWidgetForNode
    };
    window.oeUI = oeUI;
}
// we wrap everything into an oeUI so we don't get namespace clashes.
)(window, window.oeUI || {});