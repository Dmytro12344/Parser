<?php


namespace Wraps;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class GuzzleWrap
{

    public function Client() : Client
    {
        $proxy = file('proxy.csv');
        $rand = mt_rand(1, 249);
        $proxyString = 'http://marekroziecki:pLnWYmR3@' . trim($proxy[$rand]) . ':60099';

        var_dump($proxyString);

        return new Client([
            'timeout' => 3.0,
            'cookie' => true,
            'proxy' => [
                'https' => $proxyString,
            ],
        ]);
    }

    public function getContent(string $url) : string
    {
        try{

        $responses = $this->Client()
            ->request('GET', $url, ['http_errors' => false])
            ->getBody()
            ->getContents();
        } catch (RequestException $e)
        {
            return $this->getContent($url);
        }

        return $responses;
    }

}