'use strict';

require('./prism');

/**
 * Global `fabricator` object
 * @namespace
 */
var fabricator = window.fabricator = {};


/**
 * Default options
 * @type {Object}
 */
fabricator.options = {
	toggles: {
		labels: true,
		notes: true,
		code: false
	},
	menu: false,
	mq: '(min-width: 60em)'
};

// open menu by default if large screen
fabricator.options.menu = window.matchMedia(fabricator.options.mq).matches;

/**
 * Feature detection
 * @type {Object}
 */
fabricator.test = {};

// test for sessionStorage
fabricator.test.sessionStorage = (function () {
	var test = '_f';
	try {
		sessionStorage.setItem(test, test);
		sessionStorage.removeItem(test);
		return true;
	} catch(e) {
		return false;
	}
}());

// create storage object if it doesn't exist; store options
if (fabricator.test.sessionStorage) {
	sessionStorage.fabricator = sessionStorage.fabricator || JSON.stringify(fabricator.options);
}


/**
 * Cache DOM
 * @type {Object}
 */
fabricator.dom = {
	root: document.querySelector('html'),
	primaryMenu: document.querySelector('.f-menu'),
	menuItems: document.querySelectorAll('.f-menu li a'),
	menuToggle: document.querySelector('.f-menu-toggle')
};


/**
 * Get current option values from session storage
 * @return {Object}
 */
fabricator.getOptions = function () {
	return (fabricator.test.sessionStorage) ? JSON.parse(sessionStorage.fabricator) : fabricator.options;
};


/**
 * Build color chips
 */
fabricator.buildColorChips = function () {

	var chips = document.querySelectorAll('.f-color-chip'),
		color;

	for (var i = chips.length - 1; i >= 0; i--) {
		color = chips[i].querySelector('.f-color-chip__color').innerHTML;
		chips[i].style.borderTopColor = color;
		chips[i].style.borderBottomColor = color;
	}

	return this;

};


/**
 * Add `f-active` class to active menu item
 */
fabricator.setActiveItem = function () {

	/**
	 * @return {Array} Sorted array of menu item 'ids'
	 */
	var parsedItems = function () {

		var items = [],
			id, href;

		for (var i = fabricator.dom.menuItems.length - 1; i >= 0; i--) {

			// remove active class from items
			fabricator.dom.menuItems[i].classList.remove('f-active');

			// get item href
			href = fabricator.dom.menuItems[i].getAttribute('href');

			// get id
			if (href.indexOf('#') > -1) {
				id = href.split('#').pop();
			} else {
				id = href.split('/').pop().replace(/\.[^/.]+$/, '');
			}

			items.push(id);

		}

		return items.reverse();

	};


	/**
	 * Match the 'id' in the window location with the menu item, set menu item as active
	 */
	var setActive = function () {

		var href = window.location.href,
			items = parsedItems(),
			id, index;

		// get window 'id'
		if (href.indexOf('#') > -1) {
			id = window.location.hash.replace('#', '');
		} else {
			id = window.location.pathname.split('/').pop().replace(/\.[^/.]+$/, '');
		}

		// In case the first menu item isn't the index page.
		if (id === '') {
			id = 'index';
		}

		// find the window id in the items array
		index = (items.indexOf(id) > -1) ? items.indexOf(id) : 0;

		// set the matched item as active
		fabricator.dom.menuItems[index].classList.add('f-active');

	};

	window.addEventListener('hashchange', setActive);

	setActive();

	return this;

};


/**
 * Click handler to primary menu toggle
 * @return {Object} fabricator
 */
fabricator.menuToggle = function () {

	// shortcut menu DOM
	var toggle = fabricator.dom.menuToggle;

	var options = fabricator.getOptions();

	// toggle classes on certain elements
	var toggleClasses = function () {
		options.menu = !fabricator.dom.root.classList.contains('f-menu-active');
		fabricator.dom.root.classList.toggle('f-menu-active');

		if (fabricator.test.sessionStorage) {
			sessionStorage.setItem('fabricator', JSON.stringify(options));
		}
	};

	// toggle classes on click
	toggle.addEventListener('click', function () {
		toggleClasses();
	});

	// close menu when clicking on item (for collapsed menu view)
	var closeMenu = function () {
		if (!window.matchMedia(fabricator.options.mq).matches) {
			toggleClasses();
		}
	};

	for (var i = 0; i < fabricator.dom.menuItems.length; i++) {
		fabricator.dom.menuItems[i].addEventListener('click', closeMenu);
	}

	return this;

};


/**
 * Handler for preview and code toggles
 * @return {Object} fabricator
 */
fabricator.allItemsToggles = function () {

	var items = {
		labels: document.querySelectorAll('[data-f-toggle="labels"]'),
		notes: document.querySelectorAll('[data-f-toggle="notes"]'),
		code: document.querySelectorAll('[data-f-toggle="code"]')
	};

	var toggleAllControls = document.querySelectorAll('.f-controls [data-f-toggle-control]');

	var options = fabricator.getOptions();

	// toggle all
	var toggleAllItems = function (type, value) {

		var button = document.querySelector('.f-controls [data-f-toggle-control=' + type + ']'),
			_items = items[type];

		for (var i = 0; i < _items.length; i++) {
			if (value) {
				_items[i].classList.remove('f-item-hidden');
			} else {
				_items[i].classList.add('f-item-hidden');
			}
		}

		// toggle styles
		if (value) {
			button.classList.add('f-active');
		} else {
			button.classList.remove('f-active');
		}

		// update options
		options.toggles[type] = value;

		if (fabricator.test.sessionStorage) {
			sessionStorage.setItem('fabricator', JSON.stringify(options));
		}

	};

	for (var i = 0; i < toggleAllControls.length; i++) {

		toggleAllControls[i].addEventListener('click', function (e) {

			// extract info from target node
			var type = e.currentTarget.getAttribute('data-f-toggle-control'),
				value = e.currentTarget.className.indexOf('f-active') < 0;

			// toggle the items
			toggleAllItems(type, value);

		});

	}

	// persist toggle options from page to page
	for (var toggle in options.toggles) {
		if (options.toggles.hasOwnProperty(toggle)) {
			toggleAllItems(toggle, options.toggles[toggle]);
		}
	}

	return this;

};


/**
 * Handler for single item code toggling
 */
fabricator.singleItemToggle = function () {

	var itemToggleSingle = document.querySelectorAll('.f-item-group [data-f-toggle-control]');

	// toggle single
	var toggleSingleItemCode = function (e) {
		var group = this.parentNode.parentNode.parentNode,
			type = e.currentTarget.getAttribute('data-f-toggle-control');

		group.querySelector('[data-f-toggle=' + type + ']').classList.toggle('f-item-hidden');
	};

	for (var i = 0; i < itemToggleSingle.length; i++) {
		itemToggleSingle[i].addEventListener('click', toggleSingleItemCode);
	}

	return this;

};


/**
 * Automatically select code when code block is clicked
 */
fabricator.bindCodeAutoSelect = function () {

	var codeBlocks = document.querySelectorAll('.f-item-code');

	var select = function (block) {
		var selection = window.getSelection();
		var range = document.createRange();
		range.selectNodeContents(block.querySelector('code'));
		selection.removeAllRanges();
		selection.addRange(range);
	};

	for (var i = codeBlocks.length - 1; i >= 0; i--) {
		codeBlocks[i].addEventListener('click', select.bind(this, codeBlocks[i]));
	}

};


/**
 * Open/Close menu based on session var.
 * Also attach a media query listener to close the menu when resizing to smaller screen.
 */
fabricator.setInitialMenuState = function () {

	// root element
	var root = document.querySelector('html');

	var mq = window.matchMedia(fabricator.options.mq);

	// if small screen
	var mediaChangeHandler = function (list) {
		if (!list.matches) {
			root.classList.remove('f-menu-active');
		} else {
			if (fabricator.getOptions().menu) {
				root.classList.add('f-menu-active');
			} else {
				root.classList.remove('f-menu-active');
			}
		}
	};

	mq.addListener(mediaChangeHandler);
	mediaChangeHandler(mq);

	return this;

};


/**
 * Initialization
 */
(function () {

	// invoke
	fabricator
		.setInitialMenuState()
		.menuToggle()
		.allItemsToggles()
		.singleItemToggle()
		.buildColorChips()
		.setActiveItem()
		.bindCodeAutoSelect();

}());
