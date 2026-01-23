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

    const container = document.getElementById('payment-form');
    container.innerHTML = '<p>Loading...</p>';

    /**
     * @param { session_key: string, payin_config_id: string } responseData
     */
    const createRainforstComponent = function (responseData) {
        const component = document.createElement('rainforest-payment');
        component.setAttribute('session-key', responseData.session_key)
        component.setAttribute('payin-config-id', responseData.payin_config_id);

        container.innerHTML = '';
        container.replaceChildren(component);

        component.addEventListener('approved', function (data) {
            // console.debug(data)
            // console.debug(data.detail)
            // console.debug(data.detail[0].data)
            // Show a general success message; let webhooks deal with the rest.
            alert('Payment complete! It may take a few minutes to be reflected in the dashboard.');
            window.location.reload();
        })
    }

    $.ajax({
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify(data),
        dataType: 'json',
        success: createRainforstComponent,
        type: 'POST',
        url: '{{ endpoint }}',
    })
};
