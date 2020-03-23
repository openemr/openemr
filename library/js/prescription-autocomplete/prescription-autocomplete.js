/*
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */



$(function () {
    $("#sendScript").toggle();
    $('#pharmacy-autocomplete').autocomplete({

        source: function (request, response) {
            // Fetch data
            let autocompleteUrl = $("#pharmacyselect").data('autocomplete-url');
            $.ajax({
                url: autocompleteUrl+'?term='+request.term,
                type: 'GET',
                dataType: "json",
                data: {
                    search: request.term
                },
                success: function (data) {
                    var resp = $.map(data,function(obj){
                        //console.log(obj.id);
                        var   phadd = obj.id + ' ' +obj.name + ' ' + obj.line1 + ' ' +obj.city;
                        return phadd;
                    });
                    response(resp);
                }
            });
        }
    });

});

let p = document.getElementById('savePharmacy');
p.addEventListener('click', savePharm);

function savePharm(e) {
    top.restoreSession();
    let pharmId = document.getElementById('pharmacy-autocomplete').value;
    let id = pharmId.split(" ");
    let setPharm = "../../src/Rx/Weno/PatientPharmacyController.php";//$("#savePharmUrl").data('savePharm-url');
    if (id[0] > 0) {
        $.ajax({
            url: setPharm+'?id=' + encodeURIComponent(id[0]),
            type: 'GET',
            dataType: 'json',
            success: function (result) {
                $("#savePharmacy").toggle();
                getReturnJson(result)
            },
            error: function(xhr, status, error){
                console.log(xhr);
                console.log(status);
                console.log(error);
                console.warn(xhr.responseText);
            }
        });
        e.preventDefault();
        $("#sendScript").toggle();
        return false;
    } else {
        $("#savePharmacy").toggle();
        e.preventDefault();
        $("#sendScript").toggle();
    }
}

let s = document.getElementById('sendScript');
s.addEventListener('click', sendScripts);
function sendScripts(e) {
    top.restoreSession();
    //While the user is waiting for the return response show this
    $('#success').html("<i class='fa fa-refresh fa-spin fa-3x fa-fw'></i>");
    //use ajax to get the list of prescriptions in the database
    let scripts = $('#prescriptIds').data('ids');
    //decided this time to send the prescriptions from the server instead of the browser
    //that is why only the prescription id is needed to be transmitted
    let sendrx = "../../interface/weno/transmitrx.php";
    let request = [];
    let responses = [];
    //Make sure there has been at least one id passed
    if (scripts) {
        request.push(
            $.ajax({
                url: sendrx+'?scripts=' + encodeURIComponent(scripts),  //send a list of id's to the server to build the rx message and send
                method: 'GET',
                success: function (response) {
                    parser = new DOMParser();
                    responses.push(response);
                    var announce = "Send Complete - Prescription(s) Return Status + Sign Off";
                    //return message to show next steps.
                    $('#success').html('<p><h4 class="bg-info">' + announce + '</h4></p>');
                    //In case there is more than one response. The response is in XML
                    $.each(responses, function (index, response) {
                        //the XML has to be parsed to get the URL to sign the eRx
                        const url = $(response).find('IFrameURL').text();
                        console.log('result: ' + response); //Writing the entire response to console
                        console.log('url: ' + url); //for viewing the URL in console
                        if (url) {
                            //$('#success').append(window.location.replace(url)); //This could be the future
                            //for now we are going to use this workaround to pop the signature page out of the dlg window.
                            $('#success').append('<a href='+url+' target="_blank">Final Step Click Here to Sign Prescription</a>');

                        } else {
                            //If there is an error returned show what it is
                            const erx_error = $(response).find('Error').text();
                            $('#success').append(erx_error);
                        }
                    });

                },
                error: function (xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);

                }

            })
        );
        //hide transmit button after send
        $("#sendScript").toggle();
    } else {
        alert('Let\'s call support https://omp.openmedpractice.com/dev/mantisbt-2.18.0');
    }

    e.preventDefault();

}


