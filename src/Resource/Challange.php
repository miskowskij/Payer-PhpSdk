<?php namespace Payer\Sdk\Resource;
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

use Payer\Sdk\Security\Encryptor;

use Payer\Sdk\Transport\Http\Response;
use Payer\Sdk\Transport\Http\Request;
use Payer\Sdk\Transport\Http;

use Payer\Sdk\PayerGatewayInterface;

class Challange extends PayerResource
{

    /**
     * Resource Location
     *
     * @var string
     */
    protected $relativePath = '/pages/helper/getChallange.jsp';

    public function __construct(PayerGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function ping()
    {
        $rest = $this->gateway->getRestService();
        $response =  Response::fromJson($rest->ping());

        return Response::fromArray(
            array(
                'status' => $response['status']
            )
        );
    }

    /**
     * Fetches the Challange hash object
     *
     * NOTICE: This method depends on valid Post credentials

     * @return string The Challange hash object array as json
     *
     */
    public function create()
    {
        $http = new Http;

        $post = $this->gateway->getPostService();
        $credentials = $post->getCredentials();

        $request = new Request(
            $this->gateway->getDomain() . $this->relativePath . '?website=' . $credentials['agent_id']
        );

        $response = $http->request($request);
        $obj = Response::fromJson($response->getData());

        $challange = $obj['auth']['challange'];
        $hash = Encryptor::makeMd5Hash($credentials['key_1'], $challange);

        return Response::fromArray(
            array(
                'id'        => $obj['auth']['id'],
                'agent_id'  => $obj['auth']['website'],
                'hash'      => $hash,
                'status'    => $obj['auth']['status']
            )
        );
    }

}