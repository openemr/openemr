var _, localize, strings;

strings = {};

localize = function(localStrings) {
  return strings = localStrings;
};

_ = function(string) {
  var translation;
  translation = strings[string];
  return translation || string;
};

module.exports = {
  localize: localize,
  _: _
};
