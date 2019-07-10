<?php

namespace Mingyi\Common;

use GuzzleHttp\Client;

class ApiHttpClient
{

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $name
     * @param $arguments
     * @return array
     * @throws ApiException
     */
    public function __call($name, $arguments)
    {
        $resp = call_user_func_array([$this->client, $name], $arguments);
        if(strpos($name,'Async')){
            return $resp;
        }
        $data = json_decode($resp->getBody()->getContents(), true);
        if ($resp->getStatusCode() == 404) {
            return null;
        }
        if(!isset($data['success'])){
            return $data;
        }
        if (!$data['success']) {
            $error = $data['errors'];
            throw new ApiException(sprintf(
                'api error: %s %s',
                $error['code'],
                $error['message']
            ));
        }
        return array_get($data, 'data', []);
    }
}
