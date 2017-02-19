<?php

namespace Petdiscount\Api;

/**
 * Petdiscount API Client
 *
 * @author Ruthger Idema <ruthger.idema@gmail.com>
 * @license http://creativecommons.org/licenses/MIT/ MIT
 *
 * Special thanks to Picqer for setting their Client Code online with an MIT Licence.
 */

class Client {

    protected $email;
    protected $apikey;
    protected $apihost = 'api.petdiscount.nl/';
    protected $protocol = 'https';
    protected $apiversion = 'v1';
    protected $useragent = 'Petdiscount PHP API Client (petdiscount.nl)';
    protected $clientversion = '0.0.1';
    protected $debug = false;
    protected $skipverification = false;
    protected $timeoutInSeconds = 60;

    public function __construct($email = '', $apikey = '')
    {
        $this->email = $email;
        $this->apikey = $apikey;
    }



    /**
     * Orders
     */

    public function getOrders()
    {
        $result = $this->sendRequest('/orders');
        return $result;
    }

    public function getOrder($orderid)
    {
        $result = $this->sendRequest('/orders/' . $orderid);
        return $result;
    }

    public function addOrder($params)
    {
        $result = $this->sendRequest('/orders', $params, 'POST');
        return $result;
    }



    /**
     * Products
     */

    public function getProducts()
    {
        $result = $this->sendRequest('/products');
        return $result;
    }

    public function getProduct($sku)
    {
        $result = $this->sendRequest('/products/' . $sku);
        return $result;
    }

    /**
     * Enable debug mode gives verbose output on requests and responses
     */
    public function enableDebugmode()
    {
        $this->debug = true;
    }
    /**
     * Disable Curl's SSL verification for testing
     */
    public function disableSslVerification()
    {
        $this->skipverification = true;
    }
    /**
     * @param string $apihost
     */
    public function setApihost($apihost)
    {
        $this->apihost = $apihost;
    }
    /**
     * @param string $protocol http or https
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }
    /**
     * @param string $useragent
     */
    public function setUseragent($useragent)
    {
        $this->useragent = $useragent;
    }
    /**
     * Change the timeout for CURL requests
     * @param int $timeoutInSeconds
     */
    public function setTimeoutInSeconds($timeoutInSeconds)
    {
        $this->timeoutInSeconds = $timeoutInSeconds;
    }
    protected function sendRequest($endpoint, $params = array(), $method = 'GET', $filters = array())
    {
        $ch = curl_init();
        $endpoint = $this->getEndpoint($endpoint, $filters);
        if ($this->debug)
        {
            echo 'URL: ' . $this->getUrl($endpoint) . PHP_EOL;
        }
        curl_setopt($ch, CURLOPT_URL, $this->getUrl($endpoint));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutInSeconds);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->email . ':' . $this->apikey);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent . ' ' . $this->clientversion);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
        if ($method == 'POST')
        {
            $data = $this->prepareData($params);
            if ($this->debug)
                echo 'Data: ' . $data . PHP_EOL;
            if ($method == 'POST')
            {
                curl_setopt($ch, CURLOPT_POST, true);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if ($this->skipverification)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $apiresult = curl_exec($ch);
        $headerinfo = curl_getinfo($ch);
        if ($this->debug)
            echo 'Raw result: ' . $apiresult . PHP_EOL;
        $apiresult_json = json_decode($apiresult, true);
        $result = array();
        $result['success'] = false;
        if ($apiresult === false) // CURL failed
        {
            $result['error'] = true;
            $result['errorcode'] = 0;
            $result['errormessage'] = curl_error($ch);
            return $result;
        }
        curl_close($ch);
        if ( ! in_array($headerinfo['http_code'], array('200', '201', '204'))) // API returns error
        {
            $result['error'] = true;
            $result['errorcode'] = $headerinfo['http_code'];
            if (isset($apiresult))
            {
                $result['errormessage'] = $apiresult;
            }
        } else // API returns success
        {
            $result['success'] = true;
            $result['data'] = (($apiresult_json === null) ? $apiresult : $apiresult_json);
        }
        return $result;
    }
    protected function getUrl($endpoint)
    {
        return $this->protocol . '://' . $this->apihost . '' . $this->apiversion . $endpoint;
    }
    protected function prepareData($params)
    {
        $data = json_encode($params);
        return $data;
    }
    protected function getEndpoint($endpoint, $filters)
    {
        if ( ! empty($filters))
        {
            $i = 0;
            foreach ($filters as $key => $value)
            {
                if ($i == 0)
                {
                    $endpoint .= '?';
                } else
                {
                    $endpoint .= '&';
                }
                $endpoint .= $key . '=' . urlencode($value);
                $i++;
            }
        }
        return $endpoint;
    }
}