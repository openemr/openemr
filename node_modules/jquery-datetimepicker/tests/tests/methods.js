describe('Test methods', function () {
    describe('Show', function () {
        it('Should show picker', function () {
            var input= $(getInput());
            var picker = input
                .datetimepicker()
                .datetimepicker('show')
                    .data(PICKER);

            expect(picker.is(':hidden')).to.be.false;
        });
    });
    describe('Hide', function () {
        it('Should hide picker', function () {
            var input= $(getInput());
            var picker = input
                .datetimepicker()
                .datetimepicker('show')
                .data(PICKER);

            expect(picker.is(':hidden')).to.be.false;
            input.datetimepicker('hide')
            expect(picker.is(':hidden')).to.be.true;
        });
    });
    describe('Toggle', function () {
        it('Should hide/show picker', function () {
            var input= $(getInput());
            var picker = input
                .datetimepicker()
                .datetimepicker('show')
                .data(PICKER);

            expect(picker.is(':hidden')).to.be.false;
            input.datetimepicker('toggle')
            expect(picker.is(':hidden')).to.be.true;
            input.datetimepicker('toggle')
            expect(picker.is(':hidden')).to.be.false;
        });
    });
    describe('Reset', function () {
        it('Should restore default value', function (done) {
            var input= $('<input type="text" value="15.12.2008"/>').appendTo(document.body);

            var picker = input
                .datetimepicker({format: 'd.m.Y'})
                .datetimepicker('show')
                .data(PICKER);

            setTimeout(function () {
                var select = picker.find('td[data-date="16"][data-month="11"][data-year="2008"]');
                expect(select.length).to.be.equal(1);
                select.trigger('click');
                expect(input.val()).to.be.equal('16.12.2008');
                input.datetimepicker('reset');
                expect(input.val()).to.be.equal('15.12.2008');
                input.datetimepicker('destroy').remove();
                done();
            }, 100)
        });
    });
});