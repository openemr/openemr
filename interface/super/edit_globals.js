/**
 * edit_globals.js is responsible for instantiating any of the javascript that is needed by the individual data types
 * that are contained in edit_globals.php page.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * The following Immediately Invoked Function (IFF) is for the multiSortedListWidget
 * It has two inner classes Widget and WidgetItem
 * The Widget represents a list select box and WidgetItem represents the individual list item that is added and sortable
 * in the DOM.  List items can be moved up, down, and removed.  When a new list item is selected with the selection
 * picker it is added to the list container.
 */
(function(window, oeUI) {

    const WIDGET_NAME = "multiSortedListWidget";
    var widgets = [];

    function Widget(widgetContainer) {
        var _widget = this;
        _widget.picker = null;
        _widget.widgetContainer = widgetContainer || null;
        widgetContainer = null; // remove the reference

        var widgetItems = [];

        _widget.updateSortOrderInput = function() {
            let container = _widget.widgetContainer.querySelector('.gbl-field-multi-sorted-list-container');

            if (!container) {
                console.error("Failed to find DOM Node with class .gbl-field-multi-sorted-list-container for widget " + WIDGET_NAME);
                return;
            }

            let options = container.querySelectorAll("[data-option-id]");
            let inputText = [];
            if (options && options.length) {
                options.forEach(function(item) { inputText.push(item.dataset.optionId); });
            }
            let sortOrderInput = _widget.widgetContainer.querySelector('.gbl-field-multi-sorted-list-value');
            if (!sortOrderInput) {
                console.error("Failed to find DOM Node with class .gbl-field-multi-sorted-list-value for widget " + WIDGET_NAME);
                return;
            }
            sortOrderInput.value = inputText.join(";");
            console.log("sortOrderInput is now '", sortOrderInput.value, ",");
        }

        _widget.addSelectedListOption = function(event) {
            let target = event.currentTarget;
            console.log("Selected option is ", target);
            console.log("Value is ", target.value);

            // algorithm is first, check if we already have the element...
            // if we do remove it from its current position and append it to the end of the list
            let container = _widget.widgetContainer.querySelector('.gbl-field-multi-sorted-list-container');
            if (!container) {
                console.error("Failed to find container with class .gbl-field-multi-sorted-list-container in widget " + WIDGET_NAME);
                return;
            }

            let itemNode =  container.querySelector('[data-option-id="' + target.value + '"]');
            if (!itemNode) {
                let template = _widget.widgetContainer.querySelector('.gbl-field-multi-sorted-list-item-template');
                if (!template) {
                    console.error("Failed to find template with class .gbl-field-multi-sorted-list-item-template in widget " + WIDGET_NAME);
                    return;
                }
                let fragment = template.content.cloneNode(true);
                itemNode = fragment.querySelector('.gbl-field-multi-sorted-list-item');
                if (!itemNode) {
                    console.error("Failed to find node from template with class .gbl-field-multi-sorted-list-item in widget " + WIDGET_NAME);
                    return;
                }
                itemNode.dataset.optionId = target.value;
                let textLabel = itemNode.querySelector('.text-label');
                // set up our display text
                if (target.options[target.selectedIndex] && textLabel) {

                    textLabel.innerText = target.options[target.selectedIndex].innerText;
                } else {
                    console.error("textLabel or selected item was missing in widget " + WIDGET_NAME);
                }
            }

            // move it to the end of the list
            container.appendChild(itemNode);
            container.classList.remove('d-none');

            let emptyList = container.parentNode.querySelector('.gbl-field-multi-sorted-list-empty');
            if (!emptyList) {
                console.error("Failed to find DOM Node with class .gbl-field-multi-sorted-list-empty in widget " + WIDGET_NAME);
            } else {
                emptyList.classList.add('d-none');
            }

            // now reset our selected index
            target.selectedIndex = 0;
            _widget.reload(); // reload everything and reset up all of our events including the new node.
        };

        _widget.init = function() {
            let select = _widget.widgetContainer.querySelector('.gbl-field-multi-sorted-list-picker');
            if (!select) {
                console.error("Failed to find select node in DOM to initialize " + WIDGET_NAME);
                return;
            }
            select.addEventListener('change', _widget.addSelectedListOption);
            _widget.picker = select;

            // now grab all of our items and init our widgets for them
            let container = _widget.widgetContainer.querySelector('.gbl-field-multi-sorted-list-container');
            if (!container) {
                console.error("Failed to find container with class .gbl-field-multi-sorted-list-container in widget " + WIDGET_NAME);
                return;
            }
            let emptyList = container.parentNode.querySelector('.gbl-field-multi-sorted-list-empty');
            if (!emptyList) {
                console.error("Failed to find DOM Node with class .gbl-field-multi-sorted-list-empty in widget " + WIDGET_NAME);
                return;
            }

            // whenever we reload it will reset our styles here.
            let items = container.querySelectorAll('.gbl-field-multi-sorted-list-item');
            if (items && items.length) {
                emptyList.classList.add('d-none');
                container.classList.remove("d-none");
            } else {
                container.classList.add("d-none");
                emptyList.classList.remove('d-none');
            }
            items.forEach(function(item) {
                let widget = new WidgetItem(item, _widget.reload);
                widget.init();
                widgetItems.push(widget);
            });
        };

        _widget.reload = function() {
            _widget.destroy();
            _widget.init();
            _widget.updateSortOrderInput();
        };

        _widget.destroy = function () {
            if (widgetItems && widgetItems.length) {
                widgetItems.forEach(function(w) { w.destroy();});
                widgetItems = [];
            }
            if (_widget.picker) {
                _widget.picker.removeEventListener('change', _widget.addSelectedListOption);
                _widget.picker = null;
            }
        }
    }

    function WidgetItem(node, actionCallback) {
        var _widgetItem = this;
        _widgetItem.node = node;
        _widgetItem.btnRemove = null;
        _widgetItem.btnMoveUp = null;
        _widgetItem.btnMoveDown = null;

        // noop if we don't have a callback
        actionCallback = actionCallback || function() {};

        function moveWidgetUp() {
            event.preventDefault();
            if (_widgetItem.node.previousElementSibling) {
                // if our next sibling is the last node, it will insert at the end
                _widgetItem.node.parentNode.insertBefore(_widgetItem.node, _widgetItem.node.previousElementSibling)
            } else {
                console.error("moveWidget")
            }
            actionCallback();
        }

        function moveWidgetDown(event) {
            event.preventDefault();
            if (_widgetItem.node.nextElementSibling) {
                // if our next sibling is the last node, it will insert at the end
                _widgetItem.node.parentNode.insertBefore(_widgetItem.node, _widgetItem.node.nextElementSibling.nextElementSibling)
            } else {
                console.error("moveWidget")
            }
            actionCallback();
        }

        function deleteWidget() {
            event.preventDefault();
            _widgetItem.node.parentNode.removeChild(_widgetItem.node);
            actionCallback();
        }

        _widgetItem.init = function() {
            if (!_widgetItem.node) {
                throw new Error("DOM node cannot be empty");
            }
            // using our node go through and setup all of our event listeners
            // setup our remove button
            let btnRemove = node.querySelector('.btn-delete');
            if (btnRemove) {
                btnRemove.addEventListener('click', deleteWidget);
                _widgetItem.btnRemove = btnRemove;
            } else {
                console.error("Failed to find DOM Node with class .btn-cancel for widget " + WIDGET_NAME);
            }

            // setup our move button

            let btnMoveUp = node.querySelector('.btn-uparrow');
            if (btnMoveUp) {
                if (node.parentNode.firstElementChild == node) {
                    btnMoveUp.classList.add("d-none");
                } else {
                    btnMoveUp.classList.remove("d-none");
                }
                btnMoveUp.addEventListener('click', moveWidgetUp);
                _widgetItem.btnMoveUp = btnMoveUp;
            } else {
                console.error("Failed to find DOM Node with class .btn-m-uparrow for widget " + WIDGET_NAME);
            }

            let btnMoveDown = node.querySelector('.btn-downarrow');
            if (btnMoveDown) {
                if (node.parentNode.lastElementChild == node) {
                    btnMoveDown.classList.add("d-none");
                } else {
                    btnMoveDown.classList.remove("d-none");
                }
                btnMoveDown.addEventListener('click', moveWidgetDown);
                _widgetItem.btnMoveDown = btnMoveDown;
            } else {
                console.error("Failed to find DOM Node with class .btn-m-uparrow for widget " + WIDGET_NAME);
            }

        };
        _widgetItem.destroy = function() {
            // if we remove all references to this then we clear it out
            _widgetItem.node = null;
            if (_widgetItem.btnRemove) {
                _widgetItem.btnRemove.removeEventListener('click', deleteWidget);
            }
            if (_widgetItem.btnMoveUp) {
                _widgetItem.btnMoveUp.removeEventListener('click', moveWidgetUp);
            }
            if (_widgetItem.btnMoveDown)
            {
                _widgetItem.btnMoveDown.removeEventListener('click', moveWidgetDown);
            }
            _widgetItem.btnRemove = null;
            _widgetItem.btnMoveDown= null;
            _widgetItem.btnMoveUp = null;
        };
    }

    function init() {
        let widgetNodes = window.document.querySelectorAll('.gbl-field-multi-sorted-list-widget');

        if (widgetNodes && widgetNodes.length) {
            widgetNodes.forEach(function(node) {
                let widget = new Widget(node);
                widget.init();
                widgets.push(widget);
            });
        }
    }
    function reload() {
        destroy();
        init();
    }
    function destroy() {
        if (widgets && widgets.length) {
            widgets.forEach(function(w) { w.destroy();});
            widgets = [];
        }
    }
    let multiSortedListWidget = {
        init: init
        ,reload: reload
        ,destroy: destroy
    };
    oeUI.multiSortedListWidget = multiSortedListWidget;
    window.oeUI = oeUI;
})(window, window.oeUI || {});

// launch our multi sorted list widget initialization
window.document.addEventListener("DOMContentLoaded", window.oeUI.multiSortedListWidget.init);