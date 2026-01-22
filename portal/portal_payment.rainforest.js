document.getElementById('paynowbutton').onclick = function (e) {
    const amountFields = document.querySelectorAll('input.amount_field');
    const encounters = [...amountFields].map((field) => {
        const data = field.dataset
        const value = field.value
        return {
            id: data.encounterId,
            code: data.code,
            codeType: data.codeType,
            value,
        }
    })
    .filter((enc) => !!enc.value);

    const patientId = document.getElementById('hidden_patient_code').value
    const amountField = document.getElementById('form_paytotal')
    // This assumes USD for the forseeable future.
    const dollars = amountField.value
    const currency = 'USD'
    let data = {
        dollars,
        currency,
        encounters,
        patientId,
    }

    /**
     * @param { session_key: string, payin_config_id: string } responseData
     */
    const createRainforstComponent = function (responseData) {
        const component = document.createElement('rainforest-payment');
        component.setAttribute('session-key', responseData.session_key)
        component.setAttribute('payin-config-id', responseData.payin_config_id);
        console.log(component)
        // document.getElementById('card-element').innerHTML = component
        // FIXME: delete on unload or replace - can show up >1x if user closes
        // box
        document.getElementById('card-element').appendChild(component)

        component.addEventListener('approved', function (data) {
            console.debug(data)
            console.debug(data.detail)
            console.debug(data.detail[0].data)
            alert('success!' + JSON.stringify(data.detail[0].data.payin_id));
            // data should contain `payin_id`, use that on the backend to capture?
            // in theory we can just close the window now and let webhooks deal
            // with everything else??
        })
    }

    $.ajax({
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify(data),
        dataType: 'json',
        success: createRainforstComponent,
        type: 'POST',
        url: 'portal_payment.rainforest.php',
    })
};
