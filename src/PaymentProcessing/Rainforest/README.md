# Rainforest Payments

This integrates [Rainforest](https://www.rainforestpay.com) as a payment provider for OpenEMR's payment infrastructure, allowing clients to pay their bills online.

## Requirements

* OpenEMR XXX
* PHP 8.2+
* Merchant account through Rainforest

## Installation

Rainforest integreation, like other payment providers, is currently in OpenEMR core.
This is likely to change in the future; expect to see it converted into a module soon.

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
