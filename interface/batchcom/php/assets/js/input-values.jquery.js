/**
 * jQuery form/json converter plugin
 * Original author: @dantenetto 
 * Licensed under the MIT license
 */
(function ($) {
    "use strict";

    var controlsWrapper;
    
    controlsWrapper = {
        _getSelectOneValue: function (select) {
            return select.val();
        },

        _getSelectMultipleValue: function (select) {
            var values = [];
            
            $.each(select[0].options, function (i, option) {
                var $option = $(option);
                
                if ($option.is(':selected')) {
                    values.push($option.val());
                }
            });

            return values;
        },

        getSelectValue: function (select) {
            var type,
                value;

            type = select[0].type;
                
            if (type === 'select-one') {
                value = this._getSelectOneValue(select);
            } else {
                value = this._getSelectMultipleValue(select);
            }

            return value;
        },

        getCheckboxValue: function (checkbox) {
            if (!checkbox.is(':checked')) {
                return '';
            }

            return checkbox.val();
        },

        getValues: function (container, keyFilter) {
            var formObject = {};
        
            container.find('input, select, textarea').each(function (i, node) {
                var key, disabled, type, value, $node;
            
                $node = $(node);
            
                key = $node.attr($.fn.inputValues.opts.attr);
                disabled = $node.is(':disabled');
            
                //skipping disabled or no-matches if necessary
                if (!key 
                    || (!$.fn.inputValues.opts.includeDisabled && disabled)
                    || (keyFilter && (key !== keyFilter))) {
                    return;
                }
            
                switch (node.type) {
                    case 'radio':
                        if (!$node.is(':checked')) {
                            formObject[key] = formObject[key] || '';
                            break;
                        }
                    
                        formObject[key] = $node.val();
                        break;

                    case 'checkbox':
                        if (!$node.is(':checked')) {
                            formObject[key] = formObject[key] || '';
                            break;
                        }

                        if (!formObject.hasOwnProperty(key) || !formObject[key]) {
                            formObject[key] = $node.val();
                            break;
                        }
                    
                        if (!$.isArray(formObject[key])) {
                            value = [formObject[key]];
                            formObject[key] = value;
                        }
                    
                        formObject[key].push($node.val());
                        break;
                
                    case 'select-one':
                        formObject[key] = $node.val();
                        break;
                
                    case 'select-multiple':
                        formObject[key] = [];
                        $.each(node.options, function (i, option) {
                            var $option = $(option);
                            if ($option.is(':selected')) {
                                formObject[key].push($option.val());
                            }
                        });
                        break;
                    
                    //atributos que devem ser ignorados
                    case 'button':
                    case 'reset':
                    case 'image':
                    case undefined:
                        break;

                    default:
                        formObject[key] = $node.val();
                }
            });
        
            if (keyFilter) return formObject[keyFilter];

            return formObject;
        },


        setSelectValue: function (select, value) {
            var i, size, option;

            select.val(null);

            if (!$.isArray(value)) {
                select.val(value);
                return;
            }
            
            for (i = 0, size = value.length; i < size; i += 1) {
                option = select.find('option[value="' + value[i] + '"]');
                option.prop('selected', true);
            }
        },

        //radio or checkbox
        _checkCheckableValue: function (checkable, value) {
            if (!$.isArray(value)) {
                value = [value];
            }
            
            $.each(value, function (i) {
                value[i] = '' + value[i];
            });

            if ($.inArray(checkable.val(), value) > -1) {
                checkable.prop('checked', true);
                return true;
            }

            return false;
        },

        checkCheckboxesValue: function (checkbox, value) {
            var i, size, anyWasChecked = false;

            checkbox.prop('checked', false);

            for (i = 0, size = checkbox.length; i < size; i += 1) {
                if (this._checkCheckableValue(checkbox.eq(i), value)) {
                    anyWasChecked = true;
                }
            }

            return anyWasChecked;
        },

        checkRadiosValue: function (radios, value) {
            var i, size;

            radios.prop('checked', false);

            for (i = 0, size = radios.length; i < size; i += 1) {
                if (this._checkCheckableValue(radios.eq(i), ('' + value))) {
                    return true;
                }
            }

            return false;
        },

        setValues: function (container, values) {
            var key, nodes, filter, type,
                attr = $.fn.inputValues.opts.attr;

            for (key in values) {
                if (!values.hasOwnProperty(key)) continue;

                filter = '[' + attr + '="' + key + '"]';
                nodes = container.find(filter);

                if (nodes.length === 0) { continue; }

                type = nodes[0].type;

                switch (type) {
                    case 'select-one':
                    case 'select-multiple':
                        this.setSelectValue(nodes, values[key]);
                    break;

                    case 'radio':
                        this.checkRadiosValue(nodes, values[key]);
                    break;

                    case 'checkbox':
                        this.checkCheckboxesValue(nodes, values[key]);
                    break;
                    
                    case 'file':
                        //fileinput can only be setted to empty string
                        if (values[key] !== '') continue;

                        nodes.val('');
                    break;
                    
                    //não existe controle de valores para esses tipos de input                    
                    case 'button':
                    case 'image':
                    case 'reset':
                    case undefined:
                    break;
                    
                    default:
                        nodes.val(values[key]);
                }
            }
        }
    };
    
    //publishing
    $.fn.inputValues = function (paramA, paramB) {
        var values;

        //getting all values from element set
        if (!paramA) return controlsWrapper.getValues(this);

        if (typeof paramA === 'string') {
            //getting only values with the specific name
            if (paramB === undefined) return controlsWrapper.getValues(this, paramA);

            values = {};
            values[paramA] = paramB ;
        } else {
            values = paramA;
        }

        controlsWrapper.setValues(this, values);
        
        return this;
    };

    $.fn.inputValues.opts = {
        attr: 'name',
        includeDisabled: false
    };

    $.fn.inputValues.config = function (opts) {
        $.fn.inputValues.opts = $.extend($.fn.inputValues.opts, opts);

        return this;
    };
}(jQuery));
