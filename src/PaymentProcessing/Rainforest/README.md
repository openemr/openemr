# Rainforest Payments

This integrates [Rainforest](https://www.rainforestpay.com) as a payment provider for OpenEMR's payment infrastructure, allowing clients to pay their bills online.
Rainforest is not used as part of insurance claims.

## Requirements

* OpenEMR 8.0.0 or higher
* PHP 8.2+
* Merchant account from Rainforest

## Installation

Rainforest integration, like other payment providers, is currently in OpenEMR core.
This is a limitation of the current payments systems - converting it to a module (along with other providers) is a future goal that will be a significant undertaking.

## Configuration

### Rainforest Dashboard

You will need to gather several identifiers and API keys from the Rainforest dashboard:
- Platform ID
- Merchant ID
- API Key
- Webhook Secret

To get the Webhook Secret, you first must create a Webhook in the dashboard.

Developers > Webhooks, `+ Add Endpoint`

URL: https://{your domain}/interface/webhooks/payment/rainforest.php
Subscribed events: select all options that start with `payin.`.

After creating the webhook, you'll be taken to a detail page with the status and history.
It contains a `Signing Secret`, which is the Webhook Secret.

### OpenEMR

From the main portal, go to `Admin > Config`.

Be sure to save changes for each section as you make them!

#### Billing

- Hide billing features: unchecked

#### Portal

- Enable Patient Portal: checked
- Allow Online Payments: checked

#### Connectors section:

- Accept Credit Card transaction from  Front Payments: checked
- Select Credit Card Payment Gateway: `Gateway for Rainforest payments`
- Set Gateway to Production mode: checked in a real deployment, unchecked when testing
- Rainforest API Key: paste the value from the Rainforest dashboard (should start with `apikey_` or `sbx_apikey_` in test mode)
- Rainforest Merchant ID: paste the value from the Rainforest dashboard (should start with `mid_` or `sbx_mid_` in test mode)
- Rainforest Platform ID: paste the value from the Rainforest dashboard (should start with `plt_` or `sbx_plt_` in test mode)
- Rainforest Webhook Secret: paste the value from the Rainforest dashboard (should start with `whsec_`)

- Gateway Publishable Key: leave blank
- Gateway API Login Auth Name or Secret: leave blank
- Gateway Transaction Key: leave blank

## Usage

Rainforest payments work the same as other payment providers within OpenEMR.

With the above settings, patients will be able to make payments on their bills through the patient portal.
Providers can also collect payments from patients using the "front payments" system.

## Code Structure

All relative to the `OpenEMR\PaymentProcessing\Rainforest` namespace.

`Api`: Interacts with the Rainforest API and performs various data format shifts

Other classes in this namespace are data structures for API interaction.

`Apis\...`: Classes used to power HTTP APIs that are used by OpenEMR UIs

`Webhooks\...`: Classes relating to webhook validation and processing.

- `Dispatcher`: Matches inbound webhooks to the processor classes that are able to handle them, based on the event type
- `ProcessorInterface`: Defines an interface for classes that process webhooks
- `Verifier`: Looks at raw HTTP requests and authenticates their signatures
- `Webhook`: A data structure representing a parsed, verified webhook body

> [!NOTE]
> Rainforest uses Svix for their webhooks; as such, the validation logic could potentially be made more general and compatible with other services.

Other classes in the directory are generally implementations of `ProcessorInterface`.

### Related code

- `interface/webhooks/payment/rainforest.php`: Endpoint to receive and handle the webhooks

There are also touch points in `portal/patient_portal.php` and `interface/patient_file/front_payment.php`, which tie this in to the main payments UI.
The `.rainforest.php` files next to them are appreciably the same, but cannot be unified (at this time) due to subtle differences in how auth and session handling works.
