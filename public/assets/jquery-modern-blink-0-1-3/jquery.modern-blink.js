/*!
 * jQuery Modern Blink plugin
 * https://github.com/leonderijke/jQuery-Modern-Blink
 *
 * Version: 0.1.3
 * Author: @leonderijke
 * Licensed under the MIT license
 */

;(function ( $, window, document, undefined ) {
	"use strict";

	var domPrefixes = 'Webkit Moz O ms'.split( ' ' ),
		prefix = '',
		supportsAnimations = false,
		keyframeprefix = '',
		keyframes = '',
		defaults = {
			// Duration specified in milliseconds (integer)
			duration:       1000,

			// Number of times the element should blink ("infinite" or integer)
			iterationCount: "infinite",

			// Whether to start automatically or not (boolean)
			auto:          true
		},
		animationCss,
		i;

	if( document.documentElement.style.animationName ) {
		supportsAnimations = true;
	}

	if ( !supportsAnimations ) {
		for( i = 0; i < domPrefixes.length; i++ ) {
			if( document.documentElement.style[ domPrefixes[ i ] + 'AnimationName' ] !== undefined ) {
				prefix = domPrefixes[ i ];
				keyframeprefix = '-' + prefix.toLowerCase() + '-';
				supportsAnimations = true;
				break;
			}
		}
	}

	if ( supportsAnimations ) {
		keyframes = '@' + keyframeprefix + 'keyframes modernBlink { '+
						'50% { opacity: 0; }'+
					'}';

		var styleSheet = null;
		if ( document.styleSheets && document.styleSheets.length ) {
			for ( i = 0; i < document.styleSheets.length; i++ ) {
				if ( document.styleSheets[ i ].href.indexOf( window.location.hostname ) == -1) {
					continue;
				}

				styleSheet = document.styleSheets[ i ];
				break;
			}
		}

		if ( styleSheet !== null ) {
			styleSheet.insertRule( keyframes, 0 );
		}
		else {
			var s = document.createElement( 'style' );
			s.innerHTML = keyframes;
			document.getElementsByTagName( 'head' )[ 0 ].appendChild( s );
		}
	}

	function ModernBlink( element, options ) {
		this.el = $(element);

		this.options = $.extend( {}, defaults, options );

		this._init();
	}

	/*
	 * @function _init
	 * Wraps the element, starts the animation
	 */
	ModernBlink.prototype._init = function _init() {
		if ( this.options.auto ) {
			this.start();
		}

		this._bindEventHandlers();
	};

	/*
	 * @function start
	 * Starts the animation
	 */
	ModernBlink.prototype.start = function start( event ) {
		if ( supportsAnimations ) {
			this.el.css({
				'animation-name':            'modernBlink',
				'animation-duration':        '' + this.options.duration + 'ms',
				'animation-iteration-count': '' + this.options.iterationCount
			});
		} else {
			this._fallbackAnimation( this.options.iterationCount );
		}
	};

	/*
	 * @function stop
	 * Stops the animation
	 */
	ModernBlink.prototype.stop = function stop( event ) {
		if ( supportsAnimations ) {
			return this.el.css({
				'animation-name'            : '',
				'animation-duration'        : '',
				'animation-iteration-count' : ''
			});
		}
		return this.el.stop( true, true );
	};

	/*
	 * @function _fallbackAnimation
	 * Provides a jQuery Animation fallback for browsers not supporting CSS Animations
	 */
	ModernBlink.prototype._fallbackAnimation = function _fallbackAnimation( iterationCount ) {
		var self = this,
			duration = this.options.duration / 2;

		if ( iterationCount > 0 || iterationCount === 'infinite' ) {
			iterationCount = iterationCount === "infinite" ? "infinite" : iterationCount - 1;

			this.el.animate( { 'opacity': 0 }, duration ).promise().done( function() {
				self.el.animate( { 'opacity': 1 }, duration );
				self._fallbackAnimation( iterationCount );
			});
		}
	};

	/*
	 * @function _bindEventHandlers
	 * Binds some useful event handlers to the element
	 */
	ModernBlink.prototype._bindEventHandlers = function _bindEventHandlers() {
		this.el.on( 'modernBlink.start', $.proxy( this.start, this ) );
		this.el.on( 'modernBlink.stop', $.proxy( this.stop, this ) );
	};

	/*
	 * @function modernBlink
	 * jQuery plugin wrapper around ModernBlink
	 *
	 * @param options object
	 */
	$.fn.modernBlink = function ( options ) {
		return this.each( function () {
			if ( !$.data( this, "plugin_modernBlink" ) ) {
				$.data( this, "plugin_modernBlink", new ModernBlink( this, options ) );
			} else {
				options = ( options || "" ).replace( /^_/ , "" );
				if ( $.isFunction( ModernBlink.prototype[ options ] ) ) {
					$.data( this, 'plugin_modernBlink' )[ options ]();
				}
			}
		});
	};

})( jQuery, window, document );
