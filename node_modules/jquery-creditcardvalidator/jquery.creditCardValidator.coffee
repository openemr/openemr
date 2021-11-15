###
jQuery Credit Card Validator 1.1

Copyright 2012-2015 Pawel Decowski

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
IN THE SOFTWARE.
###

$ = jQuery

$.fn.validateCreditCard = (callback, options) ->
    card_types = [
        {
            name: 'amex'
            range: '34,37'
            valid_length: [ 15 ]
        }
        {
            name: 'diners_club_carte_blanche'
            range: '300-305'
            valid_length: [ 14 ]
        }
        {
            name: 'diners_club_international'
            range: '36'
            valid_length: [ 14 ]
        }
        {
            name: 'jcb'
            range: '3528-3589'
            valid_length: [ 16 ]
        }
        {
            name: 'laser'
            range: '6304, 6706, 6709, 6771'
            valid_length: [ 16..19 ]
        }
        {
            name: 'visa_electron'
            range: '4026, 417500, 4508, 4844, 4913, 4917'
            valid_length: [ 16 ]
        }
        {
            name: 'visa'
            range: '4'
            valid_length: [ 13..19 ]
        }
        {
            name: 'mastercard'
            range: '51-55,2221-2720'
            valid_length: [ 16 ]
        }
        {
            name: 'discover'
            range: '6011, 622126-622925, 644-649, 65'
            valid_length: [ 16 ]
        }
        {
            name: 'dankort'
            range: '5019'
            valid_length: [ 16 ]
        }
        {
            name: 'maestro'
            range: '50, 56-69'
            valid_length: [ 12..19 ]
        }
        {
            name: 'uatp'
            range: '1'
            valid_length: [ 15 ]
        }
    ]

    bind = false

    if callback
        if typeof callback == 'object'
            # callback has been skipped and only options parameter has been passed
            options = callback
            bind = false
            callback = null
        else if typeof callback == 'function'
            bind = true

    options ?= {}

    options.accept ?= (card.name for card in card_types)

    for card_type in options.accept
        if card_type not in (card.name for card in card_types)
            throw Error "Credit card type '#{ card_type }' is not supported"

    get_card_type = (number) ->
        for card_type in (card for card in card_types when card.name in options.accept)
            r = Range.rangeWithString(card_type.range)

            if r.match(number)
                return card_type

        null

    is_valid_luhn = (number) ->
        sum = 0

        for digit, n in number.split('').reverse()
            digit = +digit # the + casts the string to int
            if n % 2
                digit *= 2
                if digit < 10 then sum += digit else sum += digit - 9
            else
                sum += digit

        sum % 10 == 0

    is_valid_length = (number, card_type) ->
        number.length in card_type.valid_length

    validate_number = (number) ->
        card_type = get_card_type number
        luhn_valid = false
        length_valid = false

        if card_type?
            luhn_valid = is_valid_luhn number
            length_valid = is_valid_length number, card_type

        card_type: card_type
        valid: luhn_valid and length_valid
        luhn_valid: luhn_valid
        length_valid: length_valid

    validate = =>
        number = normalize $(this).val()
        validate_number number

    normalize = (number) ->
        number.replace /[ -]/g, ''

    if not bind
        return validate()

    this.on('input.jccv', =>
        $(this).off('keyup.jccv') # if input event is fired (so is supported) then unbind keyup
        callback.call this, validate()
    )

    # bind keyup in case input event isn't supported
    this.on('keyup.jccv', =>
        callback.call this, validate()
    )

    # run validation straight away in case the card number is prefilled
    callback.call this, validate()

    this
