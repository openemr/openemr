/**
 * View logic for Patient Data
 *
 * application logic specific to the Patient profile page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
var page = {
    patientData: new model.PatientCollection(),
    patient: null,
    portalpatient: null,
    isInitialized: false,
    isInitializing: false,
    fetchParams: {filter: '', orderBy: '', orderDesc: '', page: 1, patientId: cpid, lookupList: null},
    fetchInProgress: false,
    dialogIsOpen: false,
    isEdited: false,

    /**
     *
     */
    init: function () {
        // ensure initialization only occurs once
        if (page.isInitialized || page.isInitializing) return;
        page.isInitializing = true;
        if (cuser < 1)
            // where is savePatientButton used?
            $('#savePatientButton').hide();

        $("#donePatientButton").addClass("disabled");
        $("#replaceAllButton").hide();

        // portal or audit
        $("#formHelp").click(function (e) {
            e.preventDefault();
            $('#profileHelp').toggle();
        });

        $("#savePatientButton").click(function (e) {
            e.preventDefault();
            page.updateModel(1);
        });
        $("#donePatientButton").click(function (e) {
            e.preventDefault();
            if ($(this).hasClass("disabled")) {
                alert(window.top.xl("You must change a value in order to save"));
                return;
            }
            page.updateModel();
        });
        $("#replaceAllButton").click(function (e) {
            e.preventDefault();
            page.revertAll();
            $("#donePatientButton").show();
        });

        /**
         * Once the profile row is loaded, shows it, or an empty form when there is no chart yet.
         */
        page.onCollectionLoaded = function () {
            if (!page.isInitialized) {
                var m = page.patientData.first();
                m = (m === undefined) ? "" : m;
                if (m) {
                    if (m.get('pid') < 1) {
                        m = "";
                    }
                }
                page.showDetailForm(m);
            }
            page.isInitialized = true;
            page.isInitializing = false;
        };

        /**
         * Once the form is drawn, sets up its date pickers and field handlers.
         */
        page.onModelRendered = function () {
            $(function () {
                $('.jquery-date-picker').datetimepicker({
                    i18n: {
                        en: {
                            months: datepicker_xlMonths,
                            dayOfWeekShort: datepicker_xlDayofwkshort,
                            dayOfWeek: datepicker_xlDayofwk
                        },
                    },
                    yearStart: datepicker_yearStart,
                    rtl: datepicker_rtl,
                    format: datepicker_format,
                    scrollInput: datepicker_scrollInput,
                    scrollMonth: datepicker_scrollMonth,
                    timepicker: false
                });
                // listen for a submit request.
                window.addEventListener("message", (e) => {
                    if (e.origin !== window.location.origin) {
                        signerAlertMsg("Unauthorized! Request is not same origin!", 15000);
                        return false;
                    }
                    if (e.data.submitForm === true) {
                        page.updateModel(1);
                    }
                });
            });
            // initialize any special controls
            $(function () {
                if (page.portalpatient) {
                    if (page.portalpatient.get('pid')) {
                        $("#replaceAllButton").show();
                        page.isEdited = true;
                        $.each(page.portalpatient.attributes, function (key, value) {
                            if (value != page.patient.get(key)) {
                                if (($("input[name=" + key + "]").attr('type') === 'radio' || $('#' + key).is('select')) && value == "") {
                                    value = 'Unassigned';
                                }
                                $('#' + key + 'InputContainer span.help-inline').html(
                                '<a class="editval text-primary" onclick="page.toggleVal(this); return false;" data-tstate=new data-id="' + app.escapeHtml(key) + '">' + app.escapeHtml(value) + '</a>');
                                $('#' + key + 'InputContainer span.help-inline').show();
                            }
                        });
                    }
                }
                page.replaceAll();
                // for mobile devices the change event does not fire during edit and so we are using input even though
                // the event will fire more frequently... we want that instead of user having to tap or click something
                // else in order to get the save button to be turned on.
                $('form :input').on("input", function () {
                    $("#donePatientButton").removeClass("disabled");
                    $('#savePatientButton').show();
                });
            });

            $(".controls .inline-inputs").find(':input:checked').parent('.btn').addClass('active');

            $(function () {
                $('.jquery-date-time-picker').datetimepicker({
                    i18n: {
                        en: {
                            months: datetimepicker_xlMonths,
                            dayOfWeekShort: datetimepicker_xlDayofwkshort,
                            dayOfWeek: datetimepicker_xlDayofwk
                        },
                    },
                    yearStart: datetimepicker_yearStart,
                    rtl: datetimepicker_rtl,
                    format: datetimepicker_format,
                    step: datetimepicker_step,
                    scrollInput: datepicker_scrollInput,
                    scrollMonth: datepicker_scrollMonth,
                    timepicker: true
                });
                // hide excluded from view. from layout edit option 'Exclude in Portal'
                if (typeof exclude !== 'undefined') {
                    exclude.forEach(id => {
                        let elHide = document.getElementById(id) ?? '';
                        if (elHide) {
                            elHide.style.display = "none";
                        }
                    });
                }

                $("#dob").on('blur', function () {
                    let bday = $(this).val() ?? '';
                    let age = Math.round(Math.abs((new Date().getTime() - new Date(bday).getTime())));
                    age = Math.round(age / 1000 / 60 / 60 / 24);
                    // need to be at least 30 days old otherwise likely an error.
                    if (age < 30) {
                        let msg = xl("Invalid Date format or value! Type date as YYYY-MM-DD or use the calendar.");
                        $(this).val('');
                        $(this).prop('placeholder', 'Invalid Date');
                        alert(msg);
                        return false;
                    }
                });

                $("#ss").on('blur', function () {
                    let el = this;
                    let numbers = el.value.replace(/[^0-9]/g, '');
                    if (numbers.length === 9) {
                        el.value = numbers.substr(0, 3) + '-' + numbers.substr(3, 2) + '-' + numbers.substr(5, 4);
                    }
                });
            });
            /* no idea why previous form inits were here! */
            if (page.portalpatient) {
                $('#note').val(page.portalpatient.get('note') ?? '');
            }

            $("#dismissHelp").click(function (e) {
                e.preventDefault();
                $('#profileHelp').toggle();
            });
            page.isInitialized = true;
            page.isInitializing = false;
        };

        this.fetchPatientData({page: 1, patientId: cpid});
    },
    /**
     * Replace field with edit
     * @param element
     */
    replaceVal: function (el) {
        var a = $(el).data('id');
        if (!document.getElementById(a)) {
			$('input[name='+a+'][value="' +  app.escapeHtml(page.portalpatient.get(a)) + '"'+']').prop('checked', true).closest('label').css({"class":"text-primary"});
		}
		else{
            $('#' + a).prop('value', page.portalpatient.get(a))
				$('#'+a).css({"class":"text-primary","font-weight":"normal"});
        }
        var v = app.escapeHtml(page.patient.get(a));
        if (($("input[name=" + a + "]").attr('type') == 'radio' || $('#' + a).is('select')) && v == "")
            v = 'Unassigned';
        $('#' + a + 'InputContainer span.help-inline').html('');
		$('#'+a+'InputContainer span.help-inline').html( '<a class="editval text-danger" style="font-size: 16px" onclick="page.revertVal(this); return false;" data-tstate=chart data-id="'+a+'">'+v+'</a>');
        $('#' + a + 'InputContainer span.help-inline').show();
    },
    /**
     * Puts a field back to the chart value, discarding the change the patient asked for.
     */
    revertVal: function (el) {
        var a = $(el).data('id');
        if (!document.getElementById(a)) {
			$('input[name='+a+'][value="' +  app.escapeHtml(page.patient.get(a)) + '"'+']').prop('checked', true).closest('label').css({"class":"text-danger"});
		}
		else{
            $('#' + a).prop('value', page.patient.get(a))
				$('#'+a).css({"class":"text-danger","font-weight":"normal"});
        }
        var v = app.escapeHtml(page.portalpatient.get(a));
        if (($("input[name=" + a + "]").attr('type') == 'radio' || $('#' + a).is('select')) && v == "")
            v = 'Unassigned';
        $('#' + a + 'InputContainer span.help-inline').html('');
		$('#'+a+'InputContainer span.help-inline').html( '<a class="editval text-primary" style="font-size: 16px" onclick="page.replaceVal(this); return false;" data-tstate="new" data-id="'+a+'">'+v+'</a>');
        $('#' + a + 'InputContainer span.help-inline').show();
        if (!$("#donePatientButton").is(":visible")) {
            $("#donePatientButton").show();
        }
        if (!$("#savePatientButton").is(":visible")) {
            $('#savePatientButton').show();
        }
    },

    /**
     * Replace all fields with edited ie mass replace
     * @param none
     */
    replaceAll: function () {
        $('.editval').each(function () {
            page.replaceVal(this);
        });
    },
    revertAll: function () {
        $('.editval').each(function () {
            page.revertVal(this);
        });
        $("#replaceAllButton").hide();
    },
    /**
     * Fetch the collection data from the server
     * @param params
     * @param hideLoader
     */
    fetchPatientData: function (params, hideLoader) {
        // persist the params so that paging/sorting/filtering will play together nicely
        page.fetchParams = params;

        if (page.fetchInProgress) {
            if (console) console.log('supressing fetch because it is already in progress');
        }

        page.fetchInProgress = true;

        if (!hideLoader) app.showProgress('modelLoader');

        page.patientData.fetch(params).then(function () {
            app.hideProgress('modelLoader');
            page.fetchInProgress = false;
            page.onCollectionLoaded();
        }, function (error) {
            app.appendAlert(app.getErrorMessage(error), 'danger', 15000, 'collectionAlert');
            app.hideProgress('modelLoader');
            page.fetchInProgress = false;
        });
    },

    /**
     * show the form for editing a model
     * @param m
     */
    showDetailForm: function (m) {
        page.patient = m ? m : new model.PatientModel();
        if (page.patient.id == null || page.patient.id === '') {
            page.renderModelView();
            app.hideProgress('modelLoader');
        } else {
            app.showProgress('modelLoader');
            page.patient.fetch({'patientId': cpid}).then(function () {
                page.getEditedPatient(page.portalpatient);
            }, function (error) {
                app.appendAlert(app.getErrorMessage(error), 'danger', 15000, 'modelAlert');
                app.hideProgress('modelLoader');
            });
        }
    },
    /**
     * get edited from audit table if any
     * @param m
     */
    getEditedPatient: function (m) {
        page.portalpatient = m ? m : new model.PortalPatientModel();
        page.portalpatient.set('id', page.patient.get('id'))
        page.portalpatient.fetch({'patientId': cpid}).then(function () {
            // audit profile data returned. render needs to be here due to chaining
            page.renderModelView();
        }, function () {
            // still have to render live data even if no changes pending
            page.renderModelView();
        });
    },
    /**
     * Draw the profile form from its template, filled with the current profile.
     */
    renderModelView: function () {
        const container = view.renderTemplate('patientModelTemplate', 'patientModelContainer');
        page.fillForm(container, page.patient);
        page.onModelRendered();
        app.hideProgress('modelLoader');
    },

    /**
     * Fill the text inputs from the profile fields named by their ids, and check the radio
     * buttons whose value matches the field named by their name. Selects come from the server
     * already chosen.
     * @param container the drawn form
     * @param record the profile
     */
    fillForm: function (container, record) {
        container.querySelectorAll('input[id]:not([type=radio])').forEach(function (input) {
            input.defaultValue = record.get(input.id) || '';
        });
        container.querySelectorAll('input[type=radio][name]').forEach(function (radio) {
            const value = record.get(radio.name);
            radio.defaultChecked = value !== null && value !== undefined && String(value) === radio.value;
        });
    },

    /**
     * update the model that is currently displayed in the dialog
     */
    updateModel: function (live = 0) {
        // reset any previous errors
        $('#modelAlert').html('');
        $('.form-group').removeClass('error');
        $('.help-inline').html('');

        // if this is new then on success we need to add it to the collection
        var isNew = page.patient.isNew();

        app.showProgress('modelLoader');

        if (live !== 1)
            page.patient.urlRoot = 'api/portalpatient';

        page.patient.save({
            'title': $('select#title').val(),
            'language': $('input#language').val(),
            //'financial': $('input#financial').val(),
            'fname': $('input#fname').val(),
            'lname': $('input#lname').val(),
            'mname': $('input#mname').val(),
            'dob': $('input#dob').val(),
            'street': $('input#street').val(),
            'postalCode': $('input#postalCode').val(),
            'city': $('input#city').val(),
            'state': $('select#state').val(),
            'countryCode': $('input#countryCode').val(),
            'driversLicense': $('input#driversLicense').val(),
            'ss': $('input#ss').val(),
            'occupation': $('textarea#occupation').val(),
            'phoneHome': $('input#phoneHome').val(),
            'phoneBiz': $('input#phoneBiz').val(),
            'phoneContact': $('input#phoneContact').val(),
            'phoneCell': $('input#phoneCell').val(),
            'pharmacyId': $('input#pharmacyId').val() || 0,
            'status': $('select#status').val(),
            'contactRelationship': $('input#contactRelationship').val(),
            'date': $('input#date').val(),
            'sex': $('select#sex').val(),
            'referrer': $('input#referrer').val(),
            'referrerid': $('input#referrerid').val(),
            'providerid': $('select#providerid').val(),
            'refProviderid': $('select#refProviderid').val() || 0,
            'email': $('input#email').val(),
            'emailDirect': $('input#emailDirect').val(),
            'ethnoracial': $('input#ethnoracial').val(),
            'race': $('select#race').val(),
            'ethnicity': $('select#ethnicity').val(),
            'religion': $('select#religion').val(),
            //'interpreter': $('input#interpreter').val(),
            //'migrantseasonal': $('input#migrantseasonal').val(),
            'familySize': $('input#familySize').val(),
            'monthlyIncome': $('input#monthlyIncome').val(),
            //	'billingNote': $('textarea#billingNote').val(),
            //	'homeless': $('input#homeless').val(),
            //	'financialReview': $('input#financialReview').val()+' '+$('input#financialReview-time').val(),
            'pubpid': $('input#pubpid').val(),
            'pid': $('input#pid').val(),
            'hipaaMail': $('input[name=hipaaMail]:checked').val(),
            'hipaaVoice': $('input[name=hipaaVoice]:checked').val(),
            'hipaaNotice': $('input[name=hipaaNotice]:checked').val(),
            'hipaaMessage': $('input#hipaaMessage').val(),
            'hipaaAllowsms': $('input[name=hipaaAllowsms]:checked').val(),
            'hipaaAllowemail': $('input[name=hipaaAllowemail]:checked').val(),
            //'referralSource': $('select#referralSource').val(),
            //'pricelevel': $('input#pricelevel').val(),
            //'regdate': $('input#regdate').val(),
            //'contrastart': $('input#contrastart').val(),
            //'completedAd': $('input[name=completedAd]:checked').val(),
            //'adReviewed': $('input#adReviewed').val(),
            //'vfc': $('input#vfc').val(),
            'mothersname': $('input#mothersname').val(),
            'guardiansname': $('input#guardiansname').val(),
            'allowImmRegUse': $('input[name=allowImmRegUse]:checked').val(),
            'allowImmInfoShare': $('input[name=allowImmInfoShare]:checked').val(),
            'allowHealthInfoEx': $('input[name=allowHealthInfoEx]:checked').val(),
            'allowPatientPortal': $('input[name=allowPatientPortal]:checked').val(),
            //'deceasedDate': $('input#deceasedDate').val()+' '+$('input#deceasedDate-time').val(),
            //'deceasedReason': $('input#deceasedReason').val(),
            //'soapImportStatus': $('input#soapImportStatus').val(),
            //'cmsportalLogin': $('input#cmsportalLogin').val(),
            'careTeam': $('select#careTeam').val() || 0,
            'county': $('input#county').val(),
            //'industry': $('textarea#industry').val(),
            'note': $('textarea#note').val()
        }).then(function () {
            if (live !== 1) {
                setTimeout(function () {
                    app.appendAlert('Patient was successfully ' + (isNew ? 'inserted' : 'updated'), 'success', 5000, 'collectionAlert');
                }, 200);
                setTimeout(function () {
                    window.location.href = webRoot + '/portal/home.php';
                }, 2500);
            } else if (live === 1 && register !== '0') { // for testing
                //alert('Save Success');
            } else {
                window.dlgCloseAjax();
            }
            app.hideProgress('modelLoader');
            if (isNew) {
                page.renderModelView();
            }
        }, function (error) {
            app.hideProgress('modelLoader');
            app.appendAlert(app.getErrorMessage(error), 'danger', 15000, 'modelAlert');
            try {
                var json = JSON.parse(error.responseText);
                if (json.errors) {
                    $.each(json.errors, function (key, value) {
                        $('#' + key + 'InputContainer').addClass('error');
                        $('#' + key + 'InputContainer span.help-inline').html(value);
                        $('#' + key + 'InputContainer span.help-inline').show();
                    });
                }
            } catch (e2) {
                if (console) console.log('error parsing server response: ' + e2.message);
            }
        });

    }
};
