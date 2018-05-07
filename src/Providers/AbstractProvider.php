<?php

namespace Muskid\Providers;

class AbstractProvider
{

    /**
     *  base api url
     * @var String
     */
    protected $baseUrl;


    protected static $guzzleOptions = ['http_errors' => false,'verify'=>false];

    protected function getHttpClient()
    {
        return new \GuzzleHttp\Client(self::$guzzleOptions);
    }

}