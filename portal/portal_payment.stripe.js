const stripe = Stripe(publicKey);
const elements = stripe.elements();// Custom styling can be passed to options when creating an Element.
const style = {
    base: {
        color: '#32325d',
        lineHeight: '1.2rem',
        fontSmoothing: 'antialiased',
        fontSize: '16px',
        '::placeholder': {
            color: '#8e8e8e'
        }
    },
    invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
    }

};
// Create an instance of the card Element.
const card = elements.create('card', {style: style});
// Add an instance of the card Element into the `card-element` <div>.
card.mount('#card-element');
// Handle real-time validation errors from the card Element.
card.addEventListener('change', function (event) {
    let displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});
// Handle form submission.
let form = document.getElementById('stripeSubmit');
form.addEventListener('click', function (event) {
    event.preventDefault();
    stripe.createToken(card).then(function (result) {
        if (result.error) {
            // Inform the user if there was an error.
            let errorElement = document.getElementById('card-errors');
            errorElement.textContent = result.error.message;
        } else {
            // Send the token to server.
            stripeTokenHandler(result.token);
        }
    });
});

// Submit the form with the token ID.
function stripeTokenHandler(token) {
    // Insert the token ID into the form so it gets submitted to the server
    let oForm = document.forms['payment-form'];
    oForm.elements['mode'].value = "Stripe";

    let inv_values = JSON.stringify(getFormObj('invoiceForm'));
    document.getElementById("invValues").value = inv_values;

    let hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    oForm.appendChild(hiddenInput);

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
