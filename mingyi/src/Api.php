<?php

namespace Mingyi\Common;

use GuzzleHttp\Client;

class Api
{

    private $clients = [];

    /**
     * @param $name string
     * @return ApiHttpClient
     * @throws \Exception
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->clients)) {
            return $this->clients[$name];
        }

        $env_name = sprintf('%s_API', strtoupper($name));
        $base_uri = env($env_name, '');
        if (!$base_uri) {
            throw new \Exception(sprintf('api server %s uri undefined', $name));
        }
        $token = env($env_name . '_TOKEN', '');
        $client = new ApiHttpClient(new Client([
            'base_uri'    => $base_uri,
            'http_errors' => false,
            'timeout'     => 15,
            'headers'     => [
                'token' => $token,
            ],
        ]));

        $this->clients[$name] = $client;
        return $client;
    }
}
