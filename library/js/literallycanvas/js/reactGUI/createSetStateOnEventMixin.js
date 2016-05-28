var React, createSetStateOnEventMixin;

React = require('./React-shim');

module.exports = createSetStateOnEventMixin = function(eventName) {
  return {
    componentDidMount: function() {
      return this.unsubscribe = this.props.lc.on(eventName, (function(_this) {
        return function() {
          return _this.setState(_this.getState());
        };
      })(this));
    },
    componentWillUnmount: function() {
      return this.unsubscribe();
    }
  };
};
