# AnythingSlider jQuery Plugin

A very robust jQuery-based slider plugin. Need to link to a specific slide? No problem. Like a choice of themes? Got it. Need callbacks for when specific slider actions happen? Sure. Need custom tab names? You got it. Need more than one slider per page? Easy. 

* Having problems with installing or getting the plugin to work? Ask your question in the [CSS-Tricks forums](http://css-tricks.com/forums/) or on [StackOverflow](http://stackoverflow.com/questions/tagged/anythingslider).
* Find a bug or have an enhancement request? Submit it [here](https://github.com/CSS-Tricks/AnythingSlider/issues)

## [Main Demo](http://css-tricks.github.com/AnythingSlider/)

* [Documentation](https://github.com/CSS-Tricks/AnythingSlider/wiki) ([FAQ](https://github.com/CSS-Tricks/AnythingSlider/wiki/FAQ)).
* [Original CSS-Tricks demo](http://css-tricks.com/examples/AnythingSlider/).
* [Latest demos!](http://css-tricks.github.com/AnythingSlider/).
* [Download](https://github.com/CSS-Tricks/AnythingSlider/zipball/master).

## Related Projects

Download the full repo for a full set of all the cool stuff AnythingSlider can do.

* More themes - [AnythingSlider-Themes](https://github.com/CSS-Tricks/AnythingSlider-Themes)
* Fx bookmarklet - [AnythingSlider-FX-Builder](https://github.com/CSS-Tricks/AnythingSlider-Fx-Builder)

CMS plugins/mods

* Wordpress plugin - [AnythingSlider-for-WordPress](https://github.com/jacobdubail/AnythingSlider-for-WordPress)
* Movable Type plugin - [mt-plugin-anythingslider](https://github.com/meancode/mt-plugin-anythingslider)
* Joomla 2.5 mod - [mod_anythingslider](https://github.com/CSS-Tricks/mod_anythingslider)

## Change Log

# Version 1.9.4

* Added aspectRatio so that the slider scales according to this ratio when the expand option is given. Thanks [npn66nicke](https://github.com/npn66nicke)!

# Version 1.9.3

* Updated all css themes (added `s` to css3 transition duration) so it will pass css3 validation. Fixes [issue #556](https://github.com/CSS-Tricks/AnythingSlider/issues/556).
* Fade mode with `resumeOnVisible` set to `true` will now set the panel visibility to hidden when it is not the current panel. Fixes [issue #559](https://github.com/CSS-Tricks/AnythingSlider/issues/559).
* Added a method to customize the `toggleControls` to only hide/show the arrows while the slide show is playing.
  * To toggle both the navigation arrows and controls, set the `toggleControls` option is `true`.
  * To toggle only the navigation arrows, but not the controls, set the `toggleControls` option to anything other than false - use `"true"` (or any string within the quotes).
  * This fulfills the enhancement request from [issue #560](https://github.com/CSS-Tricks/AnythingSlider/issues/560).
* Added a `component.json` file to allow registering this plugin with bower. Fulfills [issue #566](https://github.com/CSS-Tricks/AnythingSlider/issues/566).

# Version 1.9.2

* Fixed video extension to now properly work with multiple sliders. See [issue #548](https://github.com/CSS-Tricks/AnythingSlider/issues/548).

# Version 1.9.1

* Added `onVideoInitialized` callback to the video extension.
  * This callback function is called after the video extension has initialized.

      ```js
      onVideoInitialized: function(slider){ }
      ```

  * The function is passed a `base` (aka `slider`) parameter which can also be obtained using `$('#slider').data('AnythingSlider')`.
  * All video functions are contained in `slider.video` and video options are contained within `slider.video.options`.
* Added `onSliderResize` callback & `slideshow_resize` event.
  * This callback function is called whenever the slider resizes, but only when the `expand` option is `true`.
  * The function/event is passed an `event` object and `base` parameter.

      ```js
      onSliderResize: function(event, slider){ }
      ```

  * Thanks to [wearetelescopic](https://github.com/wearetelescopic) for sharing.

# Version 1.9.0

* Core updates:
  * Support for jQuery's `addBack` and/or `andSelf` applied; see [issue #508](https://github.com/CSS-Tricks/AnythingSlider/pull/508).
  * The `playRtl` option no longer swaps direction of the arrows; see [issue #526](https://github.com/CSS-Tricks/AnythingSlider/issues/526).
  * The combination of `stopAtEnd:true`, `infiniteSlides:false` and `showMultiple` > `1`, no longer shows empty panels. Fixes [issue #515](https://github.com/CSS-Tricks/AnythingSlider/issues/515).
  * Deprecated the `addWmodeToObject` option. Replaced by video extension's `wmode` option; see below for more details.

* AnythingSlider Video Extension updates:
  * Sadly, I didn't have time to completely rewrite this extension, but I think I got everything working properly again.
  * Also, I didn't get a chance to do extensive video testing in IE, Safari or Opera... and since the newest Safari will no longer be available for Windows, I'll need some feedback on how it's working in that browser.
  * The video extension no longer "automatically loads" itself
      * You will now need to initialize this extension along with AnythingSlider (defaults shown below):

          ```javascript
          $('#slider')
            .anythingSlider()
            .anythingSliderVideo({
              // video id prefix; suffix from $.fn.anythingSliderVideo.videoIndex
              videoId         : 'asvideo',
              // auto load YouTube api script
              youtubeAutoLoad : true,
              // YouTube iframe parameters, for a full list see:
              // https://developers.google.com/youtube/player_parameters#Parameters
              youtubeParams   : {
                modestbranding : 1,
                iv_load_policy : 3,
                fs : 1,
                wmode: 'opaque' // this is set by the wmode option above, so no need to include it here
              }
            });
          ```

      * This fixes [issue #167](https://github.com/CSS-Tricks/AnythingSlider/issues/167).

  * YouTube:
      * YouTube video should now properly pause and resume as it now dynamically loads the YouTube iframe api (set `youtubeAutoLoad` option to `false` to disable). Fixes issues [#191](https://github.com/CSS-Tricks/AnythingSlider/issues/191), [#263](https://github.com/CSS-Tricks/AnythingSlider/issues/263) &amp; [#333](https://github.com/CSS-Tricks/AnythingSlider/issues/333).
      * Add any YouTube iframe parameters within the video extension options, as seen above.
          * Also, [go here for a full list of iframe parameters](https://developers.google.com/youtube/player_parameters#Parameters).
          * This will allow you to hide video controls (`controls: 0`) - see [issue #501](https://github.com/CSS-Tricks/AnythingSlider/issues/501).
          * Autoplaying videos (`autoplay: 1`) will still be problematic, for these reasons:
              * If multiple videos exists in the slider, they will all start autoplaying at once.
              * If there is a single video and it isn't in the starting panel, it will start playing in the background.
              * Autoplaying won't work in some mobile browsers like Chrome or Safari ([ref](https://developers.google.com/youtube/iframe_api_reference#Autoplay_and_scripted_playback)) - see [issue #454](https://github.com/CSS-Tricks/AnythingSlider/issues/454).
      * The YouTube iframe wmode parameter is automatically set by the AnythingSlider `addWmodeToObject` option.
      * YouTube embedded video still requires swfobject, but does not use the above `youtubeParams` option.
  * HTML5 video now recognizes the `resumeOnVisible` option properly. See [issue #525](https://github.com/CSS-Tricks/AnythingSlider/issues/525).
  * The `videoId` option automatically adds an ID to each video; this option contains the id prefix. The suffix is now properly added so having multiple video initialization blocks will no longer repeat the same Id.
  * Changed the video extension to only use a GPL license, to match the main plugin.

# Version 1.8.18

* Modified vertical `mode` to now work with `showMultiple` and show multiple slides.
  * When showing more panels vertically, the plugin keeps the set panel size and just adds it to the bottom. So, if you set the slider to 300x200 and show two panels vertically, it will end up being 400 pixels in height, plus a bit more for the navigation. The plugin did the same with width, so it's just following this pattern.
  * When using vertical `mode` and `expand` is `true`, the panels will be forced to fit within the height contraints, so the above pattern is not followed.
  * If `resizeContents` is `false` the panels will be left aligned, and the slider will resize it's width and height to match the biggest panel.
  * This feature request fulfills [issue #378](https://github.com/CSS-Tricks/AnythingSlider/issues/378).
  * This feature has not been rigourously tested with all different combinations, so if you find any problems please report them by opening up an [issue](https://github.com/CSS-Tricks/AnythingSlider/issues).

# Version 1.8.17

* Minified version updated, as it was still an older version.

# Version 1.8.16

* Merged in video extension update to prevent errors in iOS devices that don't support Flash. See [pull #485](https://github.com/CSS-Tricks/AnythingSlider/pull/485). Thanks [mlms13](https://github.com/mlms13)!
