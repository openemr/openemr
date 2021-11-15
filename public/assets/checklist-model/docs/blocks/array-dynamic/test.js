describe('array-dynamic', function() {

  beforeEach(function() {
    browser().navigateTo(mainUrl);
  });

  var s = '[ng-controller="Ctrl9"] ';
  var a = s+' input[type="checkbox"]';

  it('should initialize with correct values', function() {
    check(a, [0,1,0,0]);
    expect(element(s+'pre').text()).toBe('[\n  \"user\"\n]');
  });

  it('should check/uncheck items', function() {
    using(s+'label:eq(0)').input('checked').check(true);
    using(s+'label:eq(1)').input('checked').check(false);
    check(a, [1,0,0,0]);
    expect(element(s+'pre').text()).toBe('[\n  \"guest\"\n]');
  });

  it('should check all', function() {
    element(s+'button[ng-click="checkAll()"]').click();
    check(a, [1,1,1,1]);
    expect(element(s+'pre').text()).toBe('[\n  \"guest\",\n  \"user\",\n  \"customer\",\n  \"admin\"\n]');
  });

  it('should uncheck all', function() {
    element(s+'button[ng-click="uncheckAll()"]').click();
    check(a, [0,0,0,0]);
    expect(element(s+'pre').text()).toBe('[]');
  });

  it('should check first', function() {
    element(s+'button[ng-click="checkFirst()"]').click();
    check(a, [1,0,0,0]);
    expect(element(s+'pre').text()).toBe('[\n  \"guest\"\n]');
  });

  it('should update value', function() {
    using(s+'label:eq(0)').input('checked').check(true);
    check(a, [1,1,0,0]);
    expect(element(s+'pre').text()).toBe('[\n  \"user\",\n  \"guest\"\n]');
    using(s+'.well div:eq(0)').input('role.text').enter("Guest");
    using(s+'.well div:eq(1)').input('role.text').enter("User");
    expect(element(s+'pre').text()).toBe('[\n  \"Guest\",\n  \"User\"\n]');
  });

});