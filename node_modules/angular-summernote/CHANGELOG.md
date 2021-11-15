# 0.8.1 (2016-03-29)
* Fix a performance issue that introduced in v0.8.0. There was a
  reactivating editor problem in case of ngModel is provided and
  editor's contents updated.
  [#117](https://github.com/summernote/angular-summernote/issues/117),
  [#119](https://github.com/summernote/angular-summernote/issues/119)
* Support dialogsinbody attribute
  [#121](https://github.com/summernote/angular-summernote/pull/121)

# 0.8.0 (2016-02-04)
* Support Summernote v0.8.x
* Support [AngularJS 1.5.x]
* Keep callbacks in the configuration object
  [#112](https://github.com/summernote/angular-summernote/pull/112)
* Fix a summernote history stack issue with empty model
  [#109](https://github.com/summernote/angular-summernote/pull/109)

# 0.7.1 (2016-01-22)
* Fix a bug that load 2 editor on IE(it is a workaround)
  [#98](https://github.com/summernote/angular-summernote/issues/98)
* Fix a bug when content is empty
  [#105](https://github.com/summernote/angular-summernote/pull/105)
* Support placeholder, min height and max height options
  [#97](https://github.com/summernote/angular-summernote/pull/97),
  [#104](https://github.com/summernote/angular-summernote/pull/104)
* Supoort on-media-delete callback
  [#92](https://github.com/summernote/angular-summernote/issues/92)

# 0.7.0 (2015-12-11)
* Make compatible with summernote v0.7.0

# 0.5.2 (2015-11-29)
* fix a broken ngModel binding with angular 1.3
  [#84](https://github.com/summernote/angular-summernote/issues/84)

# 0.5.1 (2015-10-05)
* Support initial text from inner markup in directive
  [#77](https://github.com/summernote/angular-summernote/issues/77)

# 0.5.0 (2015-08-19)
* Support [AngularJS 1.4.x](http://angularjs.blogspot.kr/2015/05/angular-140-jaracimrman-existence.html)

# 0.4.2 (2015-08-19)
* bug fixes
    * fix "Maximum call stack size exceeded" error in airmode
      [#62](https://github.com/summernote/angular-summernote/issues/62)
    * clean ngModel when content is empty
      [#53](https://github.com/summernote/angular-summernote/issues/53)

# 0.4.0 (2015-05-25)
## Breaking changes
* Support Summernote v0.6.4+. It's not compatible with the version under v0.6.4.
  If you use summernote v0.6.3-, use angular-summernote v0.3.2.
* Now, editor object exposed via `editor` attribute.

## Features
* Support `ngModelOptions`
* Support `onToolbarClick` event
* Publish in npm registry

# 0.3.2 (2015-02-13)
* bug fixes
    * fix to avoid inprog error with outer scope
      [#34](https://github.com/summernote/angular-summernote/pull/34))

# 0.3.1 (2014-12-25)
* summernote organization maintains angular-summernote now.
* Upgrade summernote to [v0.6.0](https://github.com/summernote/summernote/releases/tag/v0.6.0)
* bug fixes
    * fix Referencing a DOM node in Expression Error(see
      [#25](https://github.com/summernote/angular-summernote/issues/25))

# 0.3.0 (2014-10-19)
Support [AngularJS 1.3.0](http://angularjs.blogspot.kr/2014/10/angularjs-130-superluminal-nudge.html)
* bug fixes
  ([#20](https://github.com/summernote/angular-summernote/issues/20))

# 0.2.4 (2014-10-04)
* bug fixes
  ([#19](https://github.com/summernote/angular-summernote/issues/19))

# 0.2.3 (2014-09-04)
* update with [summernote v0.5.8](https://github.com/HackerWins/summernote/releases/tag/v0.5.8)
* add `onChange` event
* support airmode

## Bug fixes

* ngModel is synchronized with insert images
  ([#15](https://github.com/summernote/angular-summernote/issues/15))

# 0.2.2 (2014-05-11)

* update with [summernote v0.5.1](https://github.com/HackerWins/summernote/releases/tag/v0.5.1)
* add `onPaste` event

## Bug Fixes

* ngModel is synchronized when summernote's codeview mode is enabled.
  ([#7](https://github.com/summernote/angular-summernote/issues/7))

# 0.2.1 (2014-02-23)

## Bug Fixes

* ngModel is syncronized when text is changed using toolbar
  ([#4](https://github.com/summernote/angular-summernote/issues/4))

# 0.2.0 (2014-01-26)

This release adds `ngModel` support

## Features

* support `ngModel` attribute(`code` attribute is removed)

## Breaking Changes

* use `ngModel` attribute instead `code` attribute for 2-ways binding.

  To migrate your code change your markup like below.
    
  Before:

```html
<summernote code="text"></summernote>
```

  After:

```html
<summernote ng-model="text"></summernote>
```

# 0.1.1 (2014-01-18)

_Very first, initial release_.

## Features

`summernote` direcive was released with the following directives:

* `summernote` directive
* `height` and `focus` attributes
* `config` attribute
* `code` attribute 
* `on-init`, `on-enter`, `on-foucs`, `on-blur`, `on-keyup`,
  `on-keydown` and `on-image-upload` attributes for event listeners
* `lang` attribute for i18n
