<?php 

namespace Tokenly\SwapbotClient;

use GuzzleHttp\Client as GuzzleClient;

/**
*       
*/
class SwapbotClient
{
    
    function __construct($swapbot_url)
    {
        $this->swapbot_url     = $swapbot_url;
    }

    public function foo() {
        return 'foo';
    }


    /**
     * gets all the available swaps
     *
     * May make multiple requests
     * @return array
     * */
    public function getAvailableSwaps()
    {
        $pg = 0;
        $limit = 100;

        $safety = 20;
        $done = false;
        $all_swaps = [];
        while (!$done) {
            $result = $this->newAPIRequest('GET', '/public/availableswaps', ['limit' => $limit, 'pg' => $pg]);
            if (!$result) { $done = true; break; }
            $all_swaps = array_merge($all_swaps, $result);
            if (count($result) < $limit) { $done = true; break; }
            ++$pg;

            if (--$safety <= 0) {
                break;
            }
        }

        return $all_swaps;
    }


    ////////////////////////////////////////////////////////////////////////

    protected function newAPIRequest($method, $path, $data=[]) {
        $api_path = '/api/v1'.$path;

        $client = new GuzzleClient(['base_url' => $this->swapbot_url,]);

        $request = $client->createRequest($method, $api_path);
        if ($data AND ($method == 'POST' OR $method == 'PATCH')) {
            $request = $client->createRequest($method, $api_path, ['json' => $data]);
        } else if ($method == 'GET') {
            $request = $client->createRequest($method, $api_path, ['query' => $data]);
        }

        // add auth
        // $this->getAuthenticationGenerator()->addSignatureToGuzzleRequest($request, $this->api_token, $this->api_secret_key);
        
        // send request
        try {
            $response = $client->send($request);
        } catch (RequestException $e) {
            if ($response = $e->getResponse()) {
                // interpret the response and error message
                $code = $response->getStatusCode();
                try {
                    $json = $response->json();
                } catch (Exception $parse_json_exception) {
                    // could not parse json
                    $json = null;
                }
                if ($json and isset($json['message'])) {
                    // throw an XChainException with the errorName
                    if (isset($json['errorName'])) {
                        $swapbot_exception = new XChainException($json['message'], $code);
                        $swapbot_exception->setErrorName($json['errorName']);
                        throw $swapbot_exception;
                    }

                    // generic exception
                    throw new Exception($json['message'], $code);
                }
            }

            // if no response, then just throw the original exception
            throw $e;
        }

        $code = $response->getStatusCode();
        if ($code == 204) {
            // empty content
            return [];
        }

        $json = $response->json();
        if (!is_array($json)) { throw new Exception("Unexpected response", 1); }

        return $json;
    }

    // protected function getAuthenticationGenerator() {
    //     $generator = new Generator();
    //     return $generator;
    // }

}
