<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class UrlCheckerService
 * @package App\Service
 */
class UrlCheckerService
{
    /**
     * @param $url
     * @return int
     */
    public function checkUrl($url)
    {
        $response_code = 500;

        if (empty($url)) {
            return $response_code;
        }

        $client = new Client();

        try {
            $response = $client->request('GET', $url);
            $response_code = $response->getStatusCode();
        } catch (GuzzleException $e) {
            $response_code = $e->getCode();
        }

        return $response_code;
    }
}