describe('event', function() {

  beforeEach(function() {
    browser().navigateTo(mainUrl);
  });

  var s = '[ng-controller="Ctrl5"] ';
  var a = s+' input[type="checkbox"]';

  it('should check of roles are changed to new value before checklist-change is fired', function() {
    element(s+'button[ng-click="checkFirst()"]').click();
    check(a, [1,0,0,0]);
    expect(element(s+'pre').text()).toEqual("\"a\"");
  });

  it('should check that checklist-before-change can deny change of values', function() {
    element(s+'button[ng-click="checkAll()"]').click();
    check(a, [1,1,0,1]);
    expect(element(s+'pre').text()).toEqual("\"a,c,u\"");
  });

});