describe('Check destructor', function () {
    describe('Init picker and after this init again with command destroy', function () {
        it('Should remove picker from DOM and remove all listeners from original input', function (done) {
            var input = getInput();
            $(input).datetimepicker();
            var dtp = $(input).data('xdsoft_datetimepicker');
            expect(dtp).to.be.not.equal(null);
            expect(dtp[0].tagName).to.be.equal('DIV');
            expect(dtp[0].classList.contains('xdsoft_datetimepicker')).to.be.true;
            expect(dtp.is(':hidden')).to.be.true;

            $(input).datetimepicker('destroy');
            expect($(input).data('xdsoft_datetimepicker')).to.be.equal(null);

            $(input).trigger('mousedown')
            setTimeout(function () {
                expect(dtp.is(':hidden')).to.be.true;
                done();
            }, 150)
        });
    });
});