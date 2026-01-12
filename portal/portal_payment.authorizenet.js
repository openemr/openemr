function sendPaymentDataToAnet(e) {
    e.preventDefault();
    const authData = {};
    authData.clientKey = publicKey;
    authData.apiLoginID = apiKey;

    const cardData = {};
    cardData.cardNumber = document.getElementById("cardNumber").value;
    cardData.month = document.getElementById("expMonth").value;
    cardData.year = document.getElementById("expYear").value;
    cardData.cardCode = document.getElementById("cardCode").value;
    cardData.fullName = document.getElementById("cardHolderName").value;
    cardData.zip = document.getElementById("cczip").value;

    const secureData = {};
    secureData.authData = authData;
    secureData.cardData = cardData;

    Accept.dispatchData(secureData, acceptResponseHandler);

    function acceptResponseHandler(response) {
        if (response.messages.resultCode === "Error") {
            let i = 0;
            let errorMsg = '';
            while (i < response.messages.message.length) {
                errorMsg = errorMsg + response.messages.message[i].code + ": " +response.messages.message[i].text;
                console.log(errorMsg);
                i = i + 1;
            }
            alert(errorMsg);
        } else {
            paymentFormUpdate(response.opaqueData);
        }
    }
}

function paymentFormUpdate(opaqueData) {
    // this is card tokenized
    document.getElementById("dataDescriptor").value = opaqueData.dataDescriptor;
    document.getElementById("dataValue").value = opaqueData.dataValue;
    let oForm = document.forms['paymentForm'];
    oForm.elements['mode'].value = "AuthorizeNet";
    let inv_values = JSON.stringify(getFormObj('invoiceForm'));
    document.getElementById("invValues").value = inv_values;

    // empty out the fields before submitting to server.
    document.getElementById("cardNumber").value = "";
    document.getElementById("expMonth").value = "";
    document.getElementById("expYear").value = "";
    document.getElementById("cardCode").value = "";

    // Submit payment to server
    fetch('./lib/paylib.php', {
        method: 'POST',
        body: new FormData(oForm)
    }).then(function(response) {
        if (!response.ok) {
            throw Error(response.statusText);
        }
        return response.text();
    }).then(function(data) {
        if(data !== 'ok') {
            alert(data);
            return;
        }
        alert(chargeMsg);
        window.location.reload(false);
    }).catch(function(error) {
        alert(error)
    });
}
