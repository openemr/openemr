<?php

namespace Academe\AuthorizeNet\Request\Transaction;

/**
 *
 */

use PHPUnit\Framework\TestCase;

use GuzzleHttp\Exception\ClientException;
use Academe\AuthorizeNet\Amount\Amount;
use Academe\AuthorizeNet\Payment\CreditCard;
use Academe\AuthorizeNet\Request\Model\Order;
use Academe\AuthorizeNet\Request\Model\Surcharge;
use Academe\AuthorizeNet\Request\Collections\LineItems;
use Academe\AuthorizeNet\Request\Collections\TransactionSettings;
use Academe\AuthorizeNet\Request\Model\LineItem;
use Academe\AuthorizeNet\Request\Model\ExtendedAmount;
use Academe\AuthorizeNet\Request\Model\Customer;
use Academe\AuthorizeNet\Request\Model\NameAddress;
use Academe\AuthorizeNet\Request\Model\CardholderAuthentication;
use Academe\AuthorizeNet\Request\Model\Retail;
use Academe\AuthorizeNet\Request\Model\Setting;
use Academe\AuthorizeNet\Request\Collections\UserFields;
use Academe\AuthorizeNet\Request\Model\UserField;

class AuthCaptureTest extends TestCase
{
    protected $transaction;

    public function setUp()
    {
        $amount = new Amount('GBP', 123);
        $this->transaction = new AuthCapture($amount);

        $creditCard = new CreditCard(
            '4000000000000001',
            '2020-12'
        );

        $this->transaction = $this->transaction->withPayment($creditCard);
    }

    /**
     * A minimaal request.
     * The documentation does not make it clear whether the amount should be
     * a string or a float. We are going for a float for now, but have a hunch
     * that may need to be changed throughout.
     */
    public function testSimple()
    {
        $data = [
            'transactionType' => 'authCaptureTransaction',
            'amount' => '1.23',
            'currencyCode' => 'GBP',
            'payment' => [
                'creditCard' => [
                    'cardNumber' => '4000000000000001',
                    'expirationDate' => '2020-12',
                ],
            ],
        ];

        $this->assertSame($data, $this->transaction->toData(true));

        $this->assertSame(
            '{"transactionType":"authCaptureTransaction","amount":"1.23","currencyCode":"GBP","payment":{"creditCard":{"cardNumber":"4000000000000001","expirationDate":"2020-12"}}}',
            json_encode($this->transaction)
        );
    }

    /**
     * All parameters populated.
     */
    public function testFull()
    {
        $order = new Order('INV123', 'Description');

        $surcharge = new Surcharge(
            new Amount('GBP', 99),
            'Surcharge Description'
        );

        $lineItems = new LineItems();

        $lineItem = new LineItem('itemId', 'name', 'description');

        $lineItems->push($lineItem);

        $tax = new ExtendedAmount(new Amount('GBP', 27), 'Tax Name');
        $duty = new ExtendedAmount(new Amount('GBP', 37), 'Duty Name');
        $shipping = new ExtendedAmount(new Amount('GBP', 47), 'Shipping Name');

        $customer = new Customer(
            Customer::CUSTOMER_TYPE_INDIVIDUAL,
            'CUST-ID',
            'customer@example.com'
        );

        $billTo = new NameAddress('Firstname', 'Lastname', 'Company Name');
        $shipTo = new NameAddress('Firstname', 'Lastname', null, 'Address 1');

        $cardholderAuthentication = new CardholderAuthentication('INDIC', 'VALUE');

        $retail = new Retail(Retail::MARKET_TYPE_ECOMMERCE, Retail::DEVICE_TYPE_SELF_SERVICE);

        $transactionSettings = new TransactionSettings();

        $transactionSettings->push(new Setting('T-SETTING-NAME', 'T-SETTING-VALUE'));

        $userFields = new UserFields();

        $userFields->push(new UserField('USERFIELD-NAME', 'USERFIELD-VALUE'));

        $transaction = $this->transaction->with([
            'terminalNumber' => 'TERM999',
            'createProfile' => true,
            'solutionId' => 'SOL1234',
            'order' => $order,
            'lineItems' => $lineItems,
            'tax' => $tax,
            'duty' => $duty,
            'shipping' => $shipping,
            'taxExempt' => false,
            'poNumber' => 'PO123456',
            'customer' => $customer,
            'billTo' => $billTo,
            'shipTo' => $shipTo,
            'customerIP' => '123.45.67.89',
            'cardholderAuthentication' => $cardholderAuthentication,
            'retail' => $retail,
            'employeeId' => 1234,
            'transactionSettings' => $transactionSettings,
            'userFields' => $userFields,
            'surcharge' => $surcharge,
            'merchantDescriptor' => 'Merchant Desc',
            'tip' => new Amount('GBP', 500),
        ]);

        $data = [
            'transactionType' => 'authCaptureTransaction',
            'amount' => '1.23',
            'currencyCode' => 'GBP',
            'payment' => [
                'creditCard' => [
                    'cardNumber' => '4000000000000001',
                    'expirationDate' => '2020-12',
                ],
            ],
            'profile' => [
                'createProfile' => true,
            ],
            'solution' => [
                'id' => 'SOL1234',
            ],
            'terminalNumber' => 'TERM999',
            'order' => [
                'invoiceNumber' => 'INV123',
                'description' => 'Description',
            ],
            'lineItems' => [
                'lineItem' => [
                    [
                        'itemId' => 'itemId',
                        'name' => 'name',
                        'description' => 'description',
                    ],
                ],
            ],
            'tax' => [
                'amount' => '0.27',
                'name' => 'Tax Name',
            ],
            'duty' => [
                'amount' => '0.37',
                'name' => 'Duty Name',
            ],
            'shipping' => [
                'amount' => '0.47',
                'name' => 'Shipping Name',
            ],
            'taxExempt' => false,
            'poNumber' => 'PO123456',
            'customer' => [
                'type' => 'individual',
                'id' => 'CUST-ID',
                'email' => 'customer@example.com',
            ],
            'billTo' => [
                'firstName' => 'Firstname',
                'lastName' => 'Lastname',
                'company' => 'Company Name',
            ],
            'shipTo' => [
                'firstName' => 'Firstname',
                'lastName' => 'Lastname',
                'address' => 'Address 1',
            ],
            'customerIP' => '123.45.67.89',
            'cardholderAuthentication' => [
                'authenticationIndicator' => 'INDIC',
                'cardholderAuthenticationValue' => 'VALUE',
            ],
            'retail' => [
                'marketType' => 0,
                'deviceType' => 3,
            ],
            'employeeId' => 1234,
            'transactionSettings' => [
                'setting' => [
                    [
                        'settingName' => 'T-SETTING-NAME',
                        'settingValue' => 'T-SETTING-VALUE',
                    ],
                ],
            ],
            'userFields' => [
                'userField' => [
                    [
                        'name' => 'USERFIELD-NAME',
                        'value' => 'USERFIELD-VALUE',
                    ],
                ],
            ],
            'surcharge' => [
                'amount' => '0.99',
                'description' => 'Surcharge Description',
            ],
            'merchantDescriptor' => 'Merchant Desc',
            'tip' => '5.00',
        ];

        $this->assertSame($data, $transaction->toData(true));
    }
}
