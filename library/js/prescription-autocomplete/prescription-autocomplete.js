/*
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */



$(document).ready(function () {
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
            url: setPharm+'?id='+id[0],
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
    $('#success').html("<i class='fa fa-refresh fa-spin fa-3x fa-fw'></i>");
    let scripts = $('#prescriptIds').data('ids');
    let sendRx = "../../interface/weno/transmitRx.php";
    let request = [];
    let responses = [];
    if (scripts) {
        request.push(
      $.ajax({
          url: sendRx+'?scripts='+scripts,
          type: 'GET',
          success: function (response) {
              parser = new DOMParser();
              responses.push(response);
              var announce = "Send Complete - Prescription(s) Return Status + Sign Off";
              $('#success').html('<p><h4 class="bg-info">' + announce + '</h4></p>');
              $.each(responses, function (index, response) { // if there is ever more than one response
                  const url = $(response).find('IFrameURL').text(); //retrieve the returned URL for the iframe
                  console.log('result: ' + response);
                  console.log('url: ' + url);
                  if (url) {
                      //$('#success').append(window.location.replace(url));
                      $('#success').append('<a href='+url+' target="_blank">Click Here to Sign Prescription</a>');
                      //$('#weno_iframe').attr('src', url);
                  } else {
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



