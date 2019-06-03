<?php


namespace Wraps;

use GuzzleHttp\Client;

class GuzzleWrap
{
    public function getContent(string $url) : string
    {
        $client = new Client();
        $responses = $client
            ->request('GET', $url)
            ->getBody()
            ->getContents();


        //$content =  json_decode($responses, true);
        return $responses;
    }
}