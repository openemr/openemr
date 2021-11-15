<?php

namespace Omnipay\AuthorizeNetApi\Message;

/**
 * The main authorisation transaction request model.
 */

use Academe\AuthorizeNet\Request\Transaction\AuthOnly;
use Academe\AuthorizeNet\Amount\MoneyPhp;
use Academe\AuthorizeNet\Amount\Amount;
use Academe\AuthorizeNet\Request\Model\NameAddress;
use Academe\AuthorizeNet\Payment\CreditCard;
use Academe\AuthorizeNet\Request\Model\Customer;
use Academe\AuthorizeNet\Request\Model\Retail;
use Academe\AuthorizeNet\Request\Model\Order;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\Payment\Track1;
use Academe\AuthorizeNet\Payment\Track2;
use Academe\AuthorizeNet\Payment\OpaqueData;
use Academe\AuthorizeNet\Request\Collections\LineItems;
use Academe\AuthorizeNet\Request\Model\LineItem;
use Academe\AuthorizeNet\Request\Model\CardholderAuthentication;
use Academe\AuthorizeNet\Request\Collections\UserFields;
use Academe\AuthorizeNet\Request\Model\UserField;

use Money\Parser\DecimalMoneyParser;
use Money\Currencies\ISOCurrencies;
use Money\Money;
use Money\Currency;

class AuthorizeRequest extends AbstractRequest
{
    /**
     * @var string The separator used to join the opaque data
     * descriptor and value, when used as a card token.
     */
    const CARD_TOKEN_SEPARATOR = ':';

    /**
     * Return the complete transaction object which will later be wrapped in
     * a \Academe\AuthorizeNet\Request\CreateTransaction object.
     *
     * @returns \Academe\AuthorizeNet\TransactionRequestInterface
     */
    public function getData()
    {
        $amount = new Amount($this->getCurrency(), $this->getAmountInteger());

        $transaction = $this->createTransaction($amount);

        // Build the customer, and add the customer to the transaction
        // if it has any attributes set.

        $customer = new Customer();

        $customer = $customer
            ->withId($this->getCustomerId())
            ->withCustomerType($this->getCustomerType())
            ->withDriversLicense($this->getCustomerDriversLicense())
            ->withTaxId($this->getCustomerTaxId());

        if ($card = $this->getCard()) {
            $billingAddress = trim(
                $card->getBillingAddress1() . ' ' . $card->getBillingAddress2()
            );

            if ($billingAddress === '') {
                $billingAddress = null;
            }

            $billTo = new NameAddress(
                $card->getBillingFirstName(),
                $card->getBillingLastName(),
                $card->getBillingCompany(),
                $billingAddress,
                $card->getBillingCity(),
                $card->getBillingState(),
                $card->getBillingPostcode(),
                $card->getBillingCountry()
            );

            // The billTo may have phone and fax number, but the shipTo does not.
            $billTo = $billTo->withPhoneNumber($card->getBillingPhone());
            $billTo = $billTo->withFaxNumber($card->getBillingFax());

            if ($billTo->hasAny()) {
                $transaction = $transaction->withBillTo($billTo);
            }

            $shippingAddress = trim(
                $card->getShippingAddress1() . ' ' . $card->getShippingAddress2()
            );

            if ($shippingAddress === '') {
                $shippingAddress = null;
            }

            $shipTo = new NameAddress(
                $card->getShippingFirstName(),
                $card->getShippingLastName(),
                $card->getShippingCompany(),
                $shippingAddress,
                $card->getShippingCity(),
                $card->getShippingState(),
                $card->getShippingPostcode(),
                $card->getShippingCountry()
            );

            if ($shipTo->hasAny()) {
                $transaction = $transaction->withShipTo($shipTo);
            }

            if ($card->getEmail()) {
                $customer = $customer->withEmail($card->getEmail());
            }

            // Credit card, track 1 and track 2 are mutually exclusive.

            if ($card->getNumber()) {
                // A credit card has been supplied.

                $card->validate();

                $creditCard = new CreditCard(
                    $card->getNumber(),
                    // Either MMYY or MMYYYY will work.
                    $card->getExpiryMonth() . $card->getExpiryYear()
                );

                if ($card->getCvv()) {
                    $creditCard = $creditCard->withCardCode($card->getCvv());
                }

                $transaction = $transaction->withPayment($creditCard);
            } elseif ($card->getTrack1()) {
                // A card magnetic track has been supplied (aka card present).

                $transaction = $transaction->withPayment(
                    new Track1($card->getTrack1())
                );
            } elseif ($card->getTrack2()) {
                $transaction = $transaction->withPayment(
                    new Track2($card->getTrack2())
                );
            }
        } // credit card

        if ($customer->hasAny()) {
            $transaction = $transaction->withCustomer($customer);
        }

        // Allow "Accept JS" nonce (in two parts) instead of card (aka OpaqueData).

        $descriptor = $this->getOpaqueDataDescriptor();
        $value = $this->getOpaqueDataValue();

        if ($descriptor && $value) {
            $transaction = $transaction->withPayment(
                new OpaqueData($descriptor, $value)
            );
        }

        if ($this->getClientIp()) {
            $transaction = $transaction->withCustomerIp($this->getClientIp());
        }

        // The MarketType and DeviceType is mandatory if tracks are supplied.

        if ($this->getDeviceType()
            || $this->getMarketType()
            || (isset($card) && $card->getTracks())
        ) {
            // TODO: accept optional customerSignature

            $retail = new Retail(
                $this->getMarketType() ?: Retail::MARKET_TYPE_RETAIL,
                $this->getDeviceType() ?: Retail::DEVICE_TYPE_UNKNOWN
            );

            $transaction = $transaction->withRetail($retail);
        }

        // The description and invoice number go into an Order object.

        if ($this->getInvoiceNumber() || $this->getDescription()) {
            $order = new Order(
                $this->getInvoiceNumber(),
                $this->getDescription()
            );

            $transaction = $transaction->withOrder($order);
        }

        // 3D Secure is handled by a thirds party provider.
        // These two fields submit the authentication values provided.
        // It is not really clear if both these fields must be always provided together,
        // or whether just one is permitted.

        if ($this->getAuthenticationIndicator() || $this->getAuthenticationValue()) {
            $cardholderAuthentication = new CardholderAuthentication(
                $this->getAuthenticationIndicator(),
                $this->getAuthenticationValue()
            );

            $transaction = $transaction->withCardholderAuthentication($cardholderAuthentication);
        }

        // Is a basket of items to go into the request?

        if ($this->getItems()) {
            $lineItems = new LineItems();

            $currencies = new ISOCurrencies();
            $moneyParser = new DecimalMoneyParser($currencies);

            foreach ($this->getItems() as $itemId => $item) {
                // Parse to a Money object.

                $itemMoney = $moneyParser->parse((string)$item->getPrice(), $this->getCurrency());

                // Omnipay provides the line price, but the LineItem wants the unit price.

                $itemQuantity = $item->getQuantity();

                if (! empty($itemQuantity)) {
                    // Divide the line price by the quantity to get the item price.

                    $itemMoney = $itemMoney->divide($itemQuantity);
                }

                // Wrap in a MoneyPhp object for the AmountInterface.

                $amount = new MoneyPhp($itemMoney);

                $lineItem = new LineItem(
                    $itemId,
                    $item->getName(),
                    $item->getDescription(),
                    $itemQuantity,
                    $amount, // AmountInterface (unit price)
                    null // $taxable
                );

                $lineItems->push($lineItem);
            }

            if ($lineItems->count()) {
                $transaction = $transaction->withLineItems($lineItems);
            }
        }

        $transaction = $transaction->with([
            'terminalNumber' => $this->getTerminalNumber(),
        ]);

        if ($sourceUserFields = $this->getUserFields()) {
            // Can be provided as key/value array, array of name/value pairs
            // or a readymade collection of models.

            if ($sourceUserFields instanceof UserFields) {
                // Already a collection; just use it.

                $userFields = $sourceUserFields;
            } else {
                $userFields = new UserFields();

                if (is_array($sourceUserFields)) {
                    foreach ($sourceUserFields as $key => $value) {
                        if (is_string($key) && is_string($value)) {
                            // key/value pairs: 'key' => 'value'

                            $userFields->push(new UserField($key, $value));
                        }

                        if (is_array($value) && count($value) === 2) {
                            // name/value pairs: ['name' => 'the name', 'value' => 'the value']

                            $userFields->push(new UserField($value['name'], $value['value']));
                        }

                        if ($value instanceof UserField) {
                            // An array of UserField objects was supplied.

                            $userFields->push($value);
                        }
                    }
                }
            }

            if ($userFields->count()) {
                $transaction = $transaction->withUserFields($userFields);
            }
        }

        return $transaction;
    }

    /**
     * Create a new instance of the transaction object.
     */
    protected function createTransaction(AmountInterface $amount)
    {
        return new AuthOnly($amount);
    }

    /**
     * Accept a transaction and sends it as a request.
     *
     * @param $data TransactionRequestInterface
     * @returns TransactionResponse
     */
    public function sendData($data)
    {
        $responseData = $this->sendTransaction($data);

        return new AuthorizeResponse($this, $responseData);
    }

    /**
     * Value must be one of Retail::DEVICE_TYPE_*
     * @param int $value The retail device type.
     * @return $this
     */
    public function setDeviceType($value)
    {
        return $this->setParameter('deviceType', $value);
    }

    /**
     * @return int
     */
    public function getDeviceType()
    {
        return $this->getParameter('deviceType');
    }

    /**
     * Value must be one of Retail::MARKET_TYPE_*
     * @param int $value The retail market type.
     * @return $this
     */
    public function setMarketType($value)
    {
        return $this->setParameter('marketType', $value);
    }

    /**
     * @return int
     */
    public function getMarketType()
    {
        return $this->getParameter('marketType');
    }

    /**
     * @param string $value Example: 'COMMON.ACCEPT.INAPP.PAYMENT'.
     * @return $this
     */
    public function setOpaqueDataDescriptor($value)
    {
        return $this->setParameter('opaqueDataDescriptor', $value);
    }

    /**
     * @return string
     */
    public function getOpaqueDataDescriptor()
    {
        return $this->getParameter('opaqueDataDescriptor');
    }

    /**
     * @param string $value Long text token usually 216 bytes long.
     * @return $this
     */
    public function setOpaqueDataValue($value)
    {
        return $this->setParameter('opaqueDataValue', $value);
    }

    /**
     * @return string
     */
    public function getOpaqueDataValue()
    {
        return $this->getParameter('opaqueDataValue');
    }

    /**
     * @param string $descriptor
     * @param string $value
     * @return $this
     */
    public function setOpaqueData($descriptor, $value)
    {
        $this->setOpaqueDataDataDescriptor($descriptor);
        $this->setOpaqueDataValue($value);

        return $this;
    }

    /**
     * The opaque data comes in two parts, but Omnipay uses just
     * one parameter for a card token.
     * Join the descriptor and the value with a colon.
     */
    public function setToken($value)
    {
        list($opaqueDataDescriptor, $opaqueDataValue) = explode(static::CARD_TOKEN_SEPARATOR, $value, 2);

        $this->setOpaqueDataDescriptor($opaqueDataDescriptor);
        $this->setOpaqueDataValue($opaqueDataValue);

        return $this;
    }

    public function getToken()
    {
        $opaqueDataDescriptor = $this->getOpaqueDataDescriptor();
        $opaqueDataValue = $this->getOpaqueDataValue();

        if ($opaqueDataDescriptor && $opaqueDataValue) {
            return $opaqueDataDescriptor
                . static::CARD_TOKEN_SEPARATOR
                . $opaqueDataValue;
        }
    }

    public function setUserFields($value)
    {
        return $this->setParameter('userFields', $value);
    }

    public function getUserFields()
    {
        return $this->getParameter('userFields');
    }
}
