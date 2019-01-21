//     jasmine-promise 0.2.0
//     (c) 2014-2015 Nicklas Ansman
//     jasmine-promise may be freely distributed under the MIT license.
//     For all details and documentation:
//     https://github.com/ansman/jasmine-promise
(function() {
  it.promise = function(desc, func) {
    it(desc, function(done) {
      var promise = func.call(this);

      if (!promise || typeof promise.then !== "function") {
        throw new Error("Got non promise back");
      }

      var error = jasmine.createSpy("errorHandler").and.callFake(function(msg) {
        expect(msg || "Unknown error").not.toBeDefined();
      });

      function success() {
        expect(error).not.toHaveBeenCalled();
      }

      promise.then(undefined, error).then(success).then(done);
    });
  };
})();
