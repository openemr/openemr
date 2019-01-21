beforeEach(function() {
  jasmine.addMatchers({
    toHaveLength: function(util, customEqualityTesters) {
      return {
        compare: function(actual, expected) {
          return {
            pass: actual.length === expected
          };
        }
      };
    },
    toHaveBeenCalledWithContext: function(util, customEqualityTesters) {
      return {
        compare: function(actual, expected) {
          return {
            pass: actual.calls.any(function(call) {
              return call.object === expected;
            })
          };
        }
      };
    },
    toHaveItems: function(util, customEqualityTesters) {
      return {
        compare: function(actual, expected) {
          if (actual.length != expected.length) {
            return {pass: false};
          }

          var ret = {};
          ret.pass = actual.every(function(a) {
            var passed = expected.some(function(e) {
              return util.equals(a, e, customEqualityTesters);
            });
            if (!passed) {
              ret.message = "Object wasn't found:\n" +
                JSON.stringify(a, null, 2) + "\n\nExpected:\n" +
                JSON.stringify(expected, null, 2);
            }
            return passed;
          });

          return ret;
        }
      };
    },
    toBeInstanceOf: function(util, customEqualityTesters) {
      return {
        compare: function(actual, expected) {
          return {
            pass: actual instanceof expected
          };
        }
      };
    },
    toBeAPromise: function(util, customEqualityTesters) {
      return {
        compare: function(actual, expected) {
          return {
            pass: actual && typeof actual.then === "function"
          };
        }
      };
    },
    toBeANumber: function(util, customEqualityTesters) {
      return {
        compare: function(actual, expected) {
          return {
            pass: typeof actual === 'number' && !isNaN(actual)
          };
        }
      };
    }
  });
});
