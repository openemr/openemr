/**
 * Copyright (c) 2013 JeongHoon Byun aka "Outsider", <http://blog.outsider.ne.kr/>
 * Licensed under the MIT license.
 * <http://outsider.mit-license.org/>
 */
describe('Summernote directive', function() {
  'use strict';

  var $rootScope, $compile, $timeout;

  beforeEach(module('summernote'));
  beforeEach(inject(function(_$compile_, _$rootScope_, _$timeout_) {
    $compile = _$compile_;
    $rootScope = _$rootScope_;
    $timeout = _$timeout_;
  }));

  describe('initialization', function() {

    it('has "summernote" class', function () {
      var element = $compile('<summernote></summernote>')($rootScope);
      $rootScope.$digest();

      expect($(element.get(0)).hasClass('summernote')).to.be.true;
    });

    it('works with "summernote" element', function () {
      var element = $compile('<summernote></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.next().hasClass('note-editor')).to.be.true;
    });

    it('works with "summernote" attribute', function () {
      var element = $compile('<div summernote></div>')($rootScope);
      $rootScope.$digest();

      expect(element.next().hasClass('note-editor')).to.be.true;
    });

    it('works with multiple "summernote" elements', function () {
      var element = $compile('<summernote></summernote><br><summernote></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.next('.note-editor')).to.length(2);
    });
  });

  describe('"height" option', function() {

    it('should be 0 unless it specified', function () {
      var element = $compile('<summernote></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').outerHeight()).to.be.equal(0);
    });

    it('should be 400 if it specified', function () {
      var element = $compile('<summernote height="400"></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').outerHeight()).to.be.equal(400);
    });

    it('should set with multiple directives', function () {
      var element = $compile('<summernote height="200"></summernote><br><summernote height="400"></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').eq(0).outerHeight()).to.be.equal(200);
      expect(element.next().find('.note-editable').eq(1).outerHeight()).to.be.equal(400);
    });
  });

  describe('"min-height" option', function() {
    it('should be 300 if it specified', function () {
      var element = $compile('<summernote min-height="300"></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').css('min-height')).to.be.equal('300px');
    });
  });

  describe('"max-height" option', function() {
    it('should be 500 if it specified', function () {
      var element = $compile('<summernote max-height="500"></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').css('max-height')).to.be.equal('500px');
    });
  });

  describe('"placeholder" option', function() {
    it('should placeholder', function () {
      var element = $compile('<summernote placeholder="This is a placeholder"></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.next().find('.note-placeholder')).to.length(1);
      expect(element.next().find('.note-placeholder').text()).to.be.equal('This is a placeholder');
    });
  });

  describe('"focus" option', function() {
    it('should be focused if it specified', function () {
      var el = $('<summernote focus height="400"></summernote>').appendTo(document.body);
      var element = $compile(el)($rootScope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').get(0)).to.be.equal(document.activeElement);

      el.next().remove();
      el.remove();
    });
  });

  describe('"airmode" option', function() {

    it('should be on', function () {
      var element = $compile('<summernote airMode></summernote>')($rootScope);
      $rootScope.$digest();

      expect(element.data('summernote').options.airMode).to.be.true;
    });


    it('should be on using config', function () {
      var scope = $rootScope.$new();
      scope.summernoteConfig = {airMode: true};
      var element = $compile('<summernote config="summernoteConfig"></summernote>')(scope);
      $rootScope.$digest();

      expect(element.data('summernote').options.airMode).to.be.true;

      element.next().remove();
      element.remove();
    });

  });

  describe('summernoteConfig', function() {
    var scope;

    beforeEach(function() {
      scope = $rootScope.$new();
      scope.summernoteConfig = {
        height: 300,
        minHeight: 200,
        maxHeight: 500,
        focus: true,
        toolbar: [
          ['style', ['bold', 'italic', 'underline', 'clear']],
          ['fontsize', ['fontsize']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['height', ['height']]
        ]
      };
    });

    it('"height" should be 300', function() {
      var element = $compile('<summernote config="summernoteConfig"></summernote>')(scope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').outerHeight()).to.be.equal(300);
    });

    it('"minHeight" should be 300', function() {
      var element = $compile('<summernote config="summernoteConfig"></summernote>')(scope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').css('min-height')).to.be.equal('200px');
    });

    it('"maxHeight" should be 300', function() {
      var element = $compile('<summernote config="summernoteConfig"></summernote>')(scope);
      $rootScope.$digest();

      expect(element.next().find('.note-editable').css('max-height')).to.be.equal('500px');
    });

    it('toolbar should be customized', function() {
      var element = $compile('<summernote config="summernoteConfig"></summernote>')(scope);
      $rootScope.$digest();

      expect(element.next().find('.note-toolbar > .note-fontsize')).to.length(1);
      expect(element.next().find('.note-toolbar > .note-help')).to.length(0);
    });

    it('"lang" needs the lang file', function() {
      var fn = $compile('<summernote lang="ko-KR"></summernote>');

      try {
        fn($rootScope);
      } catch(e) {
        expect(e.message).to.be.exist;
      }
    });

    it('"lang" load the lang file correctly', function() {
      var element = $compile('<summernote lang="de-DE"></summernote>')(scope);
      $rootScope.$digest();

      expect(element.next().find('.note-toolbar > .note-view .btn-fullscreen').attr('data-original-title'))
        .to.be.equal('Vollbild');
    });
  });

  describe('destroy', function() {
    var scope;

    beforeEach(function() {
      scope = $rootScope.$new();
    });

    it('shoud be destroyed when scope is destroyed.', function() {
      // given
      var element = $compile('<summernote></summernote>')(scope);
      scope.$digest();
      expect(element.next().hasClass('note-editor')).to.be.true;
      // when
      scope.$destroy();
      // then
      expect(element.next().hasClass('note-editor')).to.be.false;
    });

    it('should clean up summernnote', function () {
      // given
      scope.summernoteConfig = {height: 300};
      scope.test = [];
      var element = $compile('<div ng-repeat="t in test"><summernote ng-model="t.c" config="summernoteConfig"></summernote></div>')(scope);
      scope.$digest();

      scope.test.push({c: ''});
      scope.$digest();
      expect($(element.next().children().get(0)).hasClass('summernote')).to.be.true;

      // when
      scope.test.pop();
      scope.$digest();

      // then
      expect($(element.next().children().get(0)).hasClass('summernote')).to.be.false;
    });
  });

  describe('ngModel', function() {
    var scope;

    beforeEach(function() {
      scope = $rootScope.$new();
      scope.summernoteConfig = {focus: true};
    });

    it('text should be synchronized when value are changed in outer scope', function() {
      // given
      var oldText = 'Hello World!', newText = 'new text';
      scope.text = oldText;
      var element = $compile('<summernote ng-model="text"></summernote>')(scope);
      scope.$digest();
      expect(element.summernote('code')).to.be.equal(oldText);
      // when
      scope.text = newText;
      scope.$digest();
      // then
      expect(element.summernote('code')).to.be.equal(newText);
    });

    it('text should be synchronized when value are changed in summernote', function() {
      var oldText = 'Hello World!', newText = 'new text';
      // given
      scope.text = oldText;
      var el = $('<summernote ng-Model="text"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      expect(element.summernote('code')).to.be.equal(oldText);
      // when
      element.summernote('code', newText);
      $(element.next().find('.note-editable').eq(0)).trigger('input'); // to trigger onChange
      scope.$digest();
      // then
      $timeout.flush();
      expect(scope.text).to.be.equal(newText);

      el.next().remove();
      el.remove();
    });

    it('element text should be blank in outer scope before digesting', function() {
      // given
      var blankText = '', helloText = 'Hello World';
      var element = $compile('<summernote ng-model="text"></summernote>')(scope);
      scope.text = helloText;
      expect(element.html()).to.be.equal(blankText);
      // when
      scope.$digest();
      // then
      expect(element.html()).to.be.equal(helloText);
    });

    it('text should be synchronized when text is changed using toolbar', function() {
      var selectText = function(element){
        var doc = document,
            range;
        if (doc.body.createTextRange) {
          range = document.body.createTextRange();
          range.moveToElementText(element);
          range.select();
        } else if (window.getSelection) {
          var selection = window.getSelection();
          range = document.createRange();
          range.selectNodeContents(element);
          selection.removeAllRanges();
          selection.addRange(range);
        }
      };

      var oldText = 'Hello World!';
      // given
      scope.text = oldText;
      var el = $('<summernote ng-Model="text"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      expect(element.summernote('code')).to.be.equal(oldText);
      // when
      selectText($(element.next().find('.note-editable'))[0]);
      $(element.next().find('.note-font').find('button').eq(0)).click();
      scope.$digest();
      // then
      $timeout.flush();
      expect(scope.text).to.be.equal(element.summernote('code'));

      el.next().remove();
      el.remove();
    });

    it('text chould be synchronized when text is changed in codeview mode', function() {
      var oldText = 'Hello World!', newText = 'new text';
      // given
      scope.text = oldText;
      var el = $('<summernote ng-Model="text"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      expect(element.summernote('code')).to.be.equal(oldText);
      // when
      element.next().find('.note-view').find('button.btn-codeview').click();
      scope.text = newText;
      scope.$digest();
      // then
      expect(element.summernote('code')).to.be.equal(newText);

      el.next().remove();
      el.remove();
    });

    it('text should be synchronized in use codeview when text is changed in outer scope', function() {
      var oldText = 'Hello World!', newText = 'new text';
      // given
      scope.text = oldText;
      var el = $('<summernote ng-Model="text"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      expect(element.summernote('code')).to.be.equal(oldText);
      // when
      element.next().find('.note-view').find('button.btn-codeview').click();
      element.next().find('.note-codable').val(newText);
      $(element.next().find('.note-codable').eq(0)).trigger('keyup');
      scope.$digest();
      // then
      $timeout.flush();
      expect(scope.text).to.be.equal(newText);

      el.next().remove();
      el.remove();
    });

    it('should be synchronized when image inserted', function(done) {
      // given
      var text = 'Hello World';
      scope.text = text;
      var el = $('<summernote ng-Model="text"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      // when
      var preventBubbling = function(e) { e.stopPropagation(); };
      $('.note-toolbar').on('click', preventBubbling);

      $(element.next().find('.note-insert').find('button[data-original-title=Picture]')).click(); // image

      var imageModal$ = element.next().find('.modal.in .modal-dialog');
      expect(imageModal$).to.length(1);
      var imgUrl = 'https://www.gravatar.com/avatar/748a6dc8b4eaba0fde62909e39be7987?s=200';
      imageModal$.find('.note-image-url').val(imgUrl);
      imageModal$.find('.note-image-url').trigger('keyup');
      imageModal$.find('.note-image-btn').click();

      // then
      var timer = setInterval(function() {
        if (element.summernote('code') !== text) {
          expect(element.summernote('code')).to.match(/gravatar/);

          // tear down
          $('.note-toolbar').off('click', preventBubbling);
          el.next().remove();
          el.remove();
          clearInterval(timer);
          done();
        }
      }, 200);
    });

    it('should empty summernote when model is empty', function() {
      // given
      scope.text = 'Hello World';
      var element = $compile($('<summernote ng-Model="text"></summernote>').appendTo(document.body))(scope);
      scope.$digest();

      // when
      scope.text = '';
      scope.$digest();

      // then
      expect(element.summernote('code')).to.not.equal('');
      expect(element.summernote('isEmpty')).to.equal(true);
    });
  });

  describe('callbacks', function() {
    var scope;

    beforeEach(function() {
      scope = $rootScope.$new();
      scope.summernoteConfig = {focus: false};
    });

    it('onInit should be invoked', function(done) {
      scope.init = function() {
        expect(true).to.be.true;
        done();
      };
      $compile('<summernote on-init="init()"></summernote>')(scope);
      scope.$digest();
    });

    it('onEnter should be invoked', function(done) {
      scope.enter = function() {
        // then
        expect(true).to.be.true;
        done();
      };
      // given
      var el = $('<summernote on-enter="enter()"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      // when
      var e= jQuery.Event('keydown');
      e.keyCode = 13; // Enter key
      element.next().find('.note-editable').trigger(e);
      scope.$digest();
      // tear down
      el.next().remove();
      el.remove();
    });

    it('onFocus should be invoked', function(done) {
      scope.focus = function(e) {
        // then
        expect(e).to.be.exist;
        done();
      };
      // given
      var el = $('<summernote on-focus="focus(evt)"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      // when
      // TODO: check the reason why it need triggering focus twice
      element.next().find('.note-editable').focus().focus();
      scope.$digest();
      // tear down
      el.next().remove();
      el.remove();
    });

    it('onBlur should be invoked', function(done) {
      scope.blur = function(e) {
        // then
        expect(e).to.be.exist;
        done();
      };
      // given
      var el = $('<summernote on-blur="blur(evt)"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      // when
      element.next().find('.note-editable').blur();
      scope.$digest();
      // tear down
      el.next().remove();
      el.remove();
    });

    it('onPaste should be invoked', function(done) {
      scope.paste = function() {
        // then
        expect(true).to.be.true;
        done();
      };
      // given
      var el = $('<summernote on-paste="paste(evt)"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      // when
      var event = jQuery.Event('paste');
      event.originalEvent = '';
      element.next().find('.note-editable').trigger(event);

      scope.$digest();
      // tear down
      el.next().remove();
      el.remove();
    });

    it('onKeyup should be invoked', function(done) {
      scope.keyup = function(e) {
        // then
        expect(e).to.be.exist;
        done();
      };
      // given
      var el = $('<summernote on-keyup="keyup(evt)"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      // when
      element.next().find('.note-editable').keyup();
      scope.$digest();
      // tear down
      el.next().remove();
      el.remove();
    });

    it('onKeydown should be invoked', function(done) {
      scope.keydown = function(e) {
        // then
        expect(e).to.be.exist;
        done();
      };
      // given
      var el = $('<summernote on-keydown="keydown(evt)"></summernote>').appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      // when
      element.next().find('.note-editable').keydown();
      scope.$digest();
      // tear down
      el.next().remove();
      el.remove();
    });

    it('onChange should be invoked', function(done) {
      var selectText = function(element){
        var doc = document,
            range;
        if (doc.body.createTextRange) {
          range = document.body.createTextRange();
          range.moveToElementText(element);
          range.select();
        } else if (window.getSelection) {
          var selection = window.getSelection();
          range = document.createRange();
          range.selectNodeContents(element);
          selection.removeAllRanges();
          selection.addRange(range);
        }
      };

      scope.change = function(contents) {
        // then
        // FIXME: summernote v0.7.0 has a bug, so `contents` is Event obejct not string of contents
        //expect(/Hello World/.test(contents)).to.be.ok;
        expect(true).to.be.ok;
        done();
      };
      // given
      var oldText = 'Hello World!';
      scope.text = oldText;
      var el = $('<summernote ng-Model="text" on-change="change(contents)"></summernote>')
                  .appendTo(document.body);
      var element = $compile(el)(scope);
      scope.$digest();
      // when
      selectText($(element.next().find('.note-editable'))[0]);
      $(element.next().find('.note-font').find('button').eq(0)).click();
      scope.$digest();
      // tear down
      el.next().remove();
      el.remove();
    });

    // TODO: add tests for onImageUpload
  });

  describe('"editable" attribute', function() {
    var scope;

    beforeEach(function() {
      scope = $rootScope.$new();
    });

    it('should be assigned as editable object', function () {
      var el = $('<summernote editable="myEditable"></summernote>');
      var element = $compile(el)(scope);
      scope.$digest();

      expect(element.next().find('.note-editable').get(0)).to.be.equal(scope.myEditable.get(0));

      el.next().remove();
      el.remove();
    });
  });

  describe('"editor" attribute', function() {
    var scope;

    beforeEach(function() {
      scope = $rootScope.$new();
    });

    it('should be assigned as editor object', function () {
      var el = $('<summernote editor="snote"></summernote>');
      var element = $compile(el)(scope);
      scope.$digest();

      expect(element.get(0)).to.be.equal(scope.snote.get(0));

      el.next().remove();
      el.remove();
    });
  });

  describe('transclude', function() {
    it('set initialize text with inner text', function() {
      // given
      var scope = $rootScope.$new();
      var html = '<span style="font-weight: bold;">init text</span>';
      // when
      var element = $compile('<summernote>'+html+'</summernote>')(scope);
      scope.$digest();
      // then
      expect(element.summernote('code')).to.be.equal(html);
    });

    it('set blank html if no text in summernote directive', function() {
      // given
      var scope = $rootScope.$new();
      // when
      var element = $compile('<summernote></summernote>')(scope);
      scope.$digest();
      // then
      expect(element.summernote('code')).to.be.equal('<p><br></p>');
    });
  });
});
