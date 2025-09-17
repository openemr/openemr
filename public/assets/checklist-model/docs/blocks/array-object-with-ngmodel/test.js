describe('array-object-with-ngmodel', function() {

  beforeEach(function() {
    browser().navigateTo(mainUrl);
  });

  var s = '[ng-controller="Ctrl3a"] ';
  var a = s+' input[type="checkbox"]';

  it('should initialize with correct values', function() {
    check(a, [0,1,0,0]);
    expect(element(s+'pre').text()).toMatch('id');
    expect(element(s+'pre').text()).toMatch('user');
  });

  it('should check/uncheck items', function() {
    using(s+'label:eq(0)').input('justAnotherCheckedVar').check(true);
    using(s+'label:eq(1)').input('justAnotherCheckedVar').check(false);
    check(a, [1,0,0,0]);
    expect(element(s+'pre').text()).toMatch('id');
    expect(element(s+'pre').text()).toMatch('guest');
  });

  it('should check all', function() {
    element(s+'button[ng-click="checkAll()"]').click();
    check(a, [1,1,1,1]);
    expect(element(s+'pre').text()).toMatch('id');
    expect(element(s+'pre').text()).toMatch('guest');
    expect(element(s+'pre').text()).toMatch('user');
    expect(element(s+'pre').text()).toMatch('customer');
    expect(element(s+'pre').text()).toMatch('admin');
  });

  it('should uncheck all', function() {
    element(s+'button[ng-click="uncheckAll()"]').click();
    check(a, [0,0,0,0]);
    expect(element(s+'pre').text()).toBe('[]');
  });

  it('should check first', function() {
    element(s+'button[ng-click="checkFirst()"]').click();
    check(a, [1,0,0,0]);
    expect(element(s+'pre').text()).toMatch('id');
    expect(element(s+'pre').text()).toMatch('guest');
  });

});