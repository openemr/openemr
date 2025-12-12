document.getElementById('paynowbutton').onclick = function (e) {
    // e.preventDefault();
    // e.stopPropagation();
    // alert('button click');

    const amountField = document.getElementById('form_paytotal')
    // This assumes USD for the forseeable future.
    const dollars = amountField.value
    const currency = 'USD'

    /**
     * @param { session_key: string, payin_config_id: string } responseData
     */
    const createRainforstComponent = function (responseData) {
        const component = document.createElement('rainforest-payment');
        component.setAttribute('session-key', responseData.session_key)
        component.setAttribute('payin-config-id', responseData.payin_config_id);
        console.log(component)
        // document.getElementById('card-element').innerHTML = component
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
        type: 'POST',
        url: 'portal_payment.rainforest.php',
        data: { dollars, currency },
        dataType: 'json',
        success: createRainforstComponent,
    })
};
