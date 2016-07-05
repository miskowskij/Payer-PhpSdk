<?php namespace Payer\Test;
/**
 * Copyright 2016 Payer Financial Services AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * PHP version 5.3
 *
 * @package   Payer_Sdk
 * @author    Payer <teknik@payer.se>
 * @copyright 2016 Payer Financial Services AB
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache license v2.0
 */

use Payer\Sdk\Exception\InvalidRequestException;

use Payer\Sdk\Resource\Challenge;
use Payer\Sdk\Resource\GetAddress;
use Payer\Sdk\Resource\Invoice;
use Payer\Sdk\Resource\Order;
use Payer\Sdk\Resource\Purchase;

use Payer\Sdk\PayerGatewayInterface;

use Payer\Sdk\Transport\Http\Response;

class PayerResourceStub
{

    /**
     * Payer Challenge Resource
     *
     * @var \Payer\Sdk\Resource\Challenge
     *
     */
    public $challenge;

    /**
     * Payer GetAddress Resource
     *
     * @var \Payer\Sdk\Resource\GetAddress
     *
     */
    public $getAddress;

    /**
     * Payer Invoice Resource
     *
     * @var \Payer\Sdk\Resource\Invoice
     *
     */
    public $invoice;

    /**
     * Payer Order Resource
     *
     * @var \Payer\Sdk\Resource\Order
     *
     */
    public $order;

    /**
     * Payer Purchase Resource
     *
     * @var \Payer\Sdk\Resource\Purchase
     *
     */
    public $purchase;

    /**
     * Dummy Purchase Data
     *
     * @var array
     *
     */
    public $purchaseData;

    /**
     * Dummy Order Data
     *
     * @var array
     *
     */
    public $orderData;


    public function __construct(PayerGatewayInterface $gateway)
    {
        $this->challenge = new Challenge($gateway);
        $this->getAddress = new GetAddress($gateway);
        $this->invoice = new Invoice($gateway);
        $this->order = new Order($gateway);
        $this->purchase = new Purchase($gateway);

        $this->_setupDummyData();
    }

    /**
     * Creates a Challenge token
     *
     * @return array
     */
    public function createDummyChallenge()
    {
        $challengeResponse = Response::fromJson(
            $this->challenge->create()
        );
        print_r($challengeResponse);;

        return $challengeResponse;
    }

    /**
     * Creates a non-activated invoice
     *
     * @return array
     * @throws InvalidRequestException
     *
     */
    public function createDummyInvoice()
    {
        $createOrderResponse = $this->createDummyOrder();

        $commitData = array(
            'order_id' => $createOrderResponse['order_id']
        );

        $createInvoiceResponse = Response::fromJson(
            $this->order->commit($commitData)
        );
        print_r($createInvoiceResponse);

        return $createInvoiceResponse;
    }

    /**
     * Creates an activated invoice
     *
     * @return array
     * @throws InvalidRequestException
     *
     */
    public function createActivatedDummyInvoice()
    {
        $createOrderResponse = $this->createDummyOrder();

        $commitData = array(
            'order_id' => $createOrderResponse['order_id']
        );

        $createInvoiceResponse = Response::fromJson(
            $this->order->commit($commitData)
        );

        $activateInvoiceResponse = Response::fromJson(
            $this->invoice->activate(
                array(
                    'invoice_number' => $createInvoiceResponse['invoice_number']
                )
            )
        );
        print_r($activateInvoiceResponse);

        return $activateInvoiceResponse;
    }

    /**
     * Creates a non-commited order
     *
     * @return array
     * @throws InvalidRequestException
     *
     */
    public function createDummyOrder()
    {
        $createOrderResponse = Response::fromJson(
            $this->order->create($this->orderData)
        );
        print_r($createOrderResponse);

        return $createOrderResponse;
    }

    /**
     * Setups dummy order and purchase data
     *
     * @return void
     *
     */
    private function _setupDummyData()
    {
        $this->orderData = array(
            'charset' => 'UTF-8',
            'description' => 'Payer Sdk Test ' . date('Y-m-d H:i:s'),
            'reference_id' => base64_encode(rand()),
            'test_mode' => true,
            'customer' => array(
                'identity_number' => '199001011234',
                'first_name'    => 'Test',
                'last_name'     => 'Person',
                'address'       => array(
                    'co'            => '',
                    'address_1'     => 'Testvägen 123',
                    'address_2'     => ''
                ),
                'zip_code'      => 12345,
                'city'          => 'Teststaden',
                'country_code'  => 'SE',
                'phone' => array(
                    'home'      => '1234567890',
                    'work'      => '0987654321',
                    'mobile'    => '111222333444'
                ),
                'email' => 'demo@payer.se',
                    //'organisation' => array(
                    //    'name'      => 'Test Company',
                    //    'reference' => 'Test person'
                    //)
            ),
            'options' => array(
                //'delivery_type' => 'print',
                //'template_type' => 2,
                //'style'         => 'relaxed'
            ),
            'items' => array(
                array(
                    'type'                  => 'freeform',
                    'line_number'           => 1,
                    'article_number'        => 'ABC123',
                    'description'           => "This is an freeform description",
                    'unit_price'            => 40,
                    'unit_vat_percentage'   => 20,
                    'quantity'              => 10,
                    'unit'                  => null,
                    //'account'               => null,
                    //'dist_agent_id'         => null
                ),
                array(
                    'type'                  => 'infoline',
                    'line_number'           => 2,
                    'article_number'        => 'ABC123',
                    'description'           => "This is an infoline description",
                    'unit'                  => null,
                    //'account'               => null,
                    //'dist_agent_id'         => null
                )
            )
        );

        $this->purchaseData = array(
            'payment' => array(
                'currency'  => 'SEK',
                'language'  => 'sv',
                'method'    => 'card',
                'url' => array(
                    'authorize' => 'http://example.com/PurchaseAuthorizationExample.php',   // Authorization Resource
                    'settle'    => 'http://example.com/PurchaseSettlementExample.php',      // Settlement Resource
                    'redirect'  => 'http://example.com',
                    'success'   => 'http://example.com'
                )
            ),
            'order' => $this->orderData
        );

    }

}