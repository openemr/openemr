/*
 * jQuery Panel Slider plugin v1.0.0
 * https://github.com/eduardomb/jquery-panelslider
*/
(function($) {
  'use strict';

  var TRANSITION_END = [
    'transitionend',
    'webkitTransitionEnd',
    'oTransitionEnd',
    'MSTransitionEnd'
  ].join(' ');

  var sliding = false;

  function _slideIn($panel) {
    var options = $panel.data('ps-options');

    if ($('body').hasClass(options.bodyClass) || sliding) {
      return;
    }

    sliding = true;

    $panel.addClass('ps-active-panel');
    $('body').addClass(options.bodyClass).one(TRANSITION_END, function(e) {
      sliding = false;

      if (typeof options.onOpen == 'function') {
        options.onOpen();
      }
    });
  }

  $.panelslider = function(element, options) {
    element.panelslider(options);
  };

  $.panelslider.close = function(callback) {
    var active = $('.ps-active-panel'),
        options = active.data('ps-options');

    if (!active.length || sliding) {
      return;
    }

    sliding = true;

    active.removeClass('ps-active-panel');
    $('body').removeClass(options.bodyClass).one(TRANSITION_END, function(e) {
      sliding = false;

      if (callback) {
        // HACK: Prevent google chrome to invoke the callback prematurally.
        setTimeout(function() {
          callback();
        }, 0);
      }
    });
  };

  // Bind click outside panel and ESC key to close panel if clickClose is true
  $(document).bind('click keyup', function(e) {
    var active = $('.ps-active-panel');

    if (e.type == 'keyup' && e.keyCode != 27) {
      return;
    }

    if (active.length && active.data('ps-options').clickClose) {
      $.panelslider.close();
    }
  });

  // Prevent click on panel to close it
  $(document).on('click', '.ps-active-panel', function(e) {
    e.stopPropagation();
  });

  $.fn.panelslider = function(options) {
    var defaults = {
      bodyClass: 'ps-active', // Class to be added to body when panel is opened
      clickClose: true,       // If true closes panel when clicking outside it
      onOpen: null            // Callback after the panel opens
    };
    var $panel = $(this.attr('href'));

    $panel.data('ps-options', $.extend({}, defaults, options));

    this.click(function(e) {
      var active = $('.ps-active-panel');

      // Open if no panel is active.
      if (!active.length) {
        _slideIn($panel);

      // Closes if the target panel is active.
      } else if (active[0] == $panel[0]) {
        $.panelslider.close();

      // If another panel is active, close it before opening the target.
      } else {
        $.panelslider.close(function() {
          _slideIn($panel);
        });
      }

      e.preventDefault();
      e.stopPropagation();
    });

    return this;
  };
})(jQuery);
