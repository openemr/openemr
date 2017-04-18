# eModal for Bootstrap [![Build Status](https://travis-ci.org/saribe/eModal.svg?branch=master)](https://travis-ci.org/saribe/eModal)
**eModal** is a Easy way to manage modal dialogs using bootstrap.

## Current Version
1.2.65

## Quick Start

1. Link to eModal.js `<script src="//rawgit.com/saribe/eModal/master/dist/eModal.min.js"></script>`
2. use eModal to display a modal for alert, ajax, prompt, confirm or iframe

		// Display an alert modal with default title (Attention)
		eModal.alert('You shall not pass!');

### Other Options
	// Display a confirm modal, with custom title.
	eModal.confirm('Do you really want to pass?', 'Question from Gandalf')
          .then(confirmCallback, optionalCancelCallback);

	// Display a ajax modal, with a title
	eModal.ajax('http://mydomail.com/page.html', 'Jobs - Form apply')
          .then(ajaxOnLoadCallback);

	// Display an prompt modal, with a title
	eModal.prompt('What is the best song ever?', 'Fill the input')

	// Display an modal whith iframe inside, with a title
	eModal.iframe('http://saribe.github.io/toastr8/', 'Hot news')

	// eModal default settings with your custom html loading template
	eModal.setEModalOptions({
        loadingHtml: '<span class="fa fa-circle-o-notch fa-spin fa-3x text-primary"></span><h4>Loading</h4>',
        ...
    });

## Breaking changes

The callback argument for prompt, confirm, ajax and iframe, now are provided in then function.

     V 1.1.X
        eModal
            .confirm(question, title, function(trueOrFalse) { trueOrFalse ? doTrue() : doFalse(); });

    V 1.2.X
        eModal
            .confirm(question, title)
            .then(doTrue, doFalse);

## Demo and documentation
- Demo can be found at http://saribe.github.io/eModal

## Copyright
Copyright © 2014-2015

## License
Under MIT license - http://www.opensource.org/licenses/mit-license.php
