<?php


namespace Wraps;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class GuzzleWrap
{

    /**
     * @return Client
     * @description: returns new Client (connection to site with proxy)
     */
    public function Client() : Client
    {
        $proxy = file('proxy.csv');
        $rand = mt_rand(1, 460);
        $proxyString = 'http://marekroziecki:c9b605d0@' . trim($proxy[$rand]) . ':60099';

        var_dump($proxyString);

        return new Client([
            'timeout' => 5.0,
            'cookie' => false,
            'proxy' => $proxyString,
        ]);
    }

    /**
     * @return Client
     * @description: returns new Client (connection to site without proxy)
     */
    public function noProxyClient() : Client
    {
        return new Client([
            'timeout' => 3.0,
            'cookie' => true,
        ]);
    }

    /**
     * @param string $url
     * @return string
     * @description: Finds and gets content from site (else will return error message)
     */
    public function getContent(string $url) : string
    {
        $i = 0;

        while($i != 12)
        {
            try
            {
                /**
                 * @description: returns content from site (with proxy connection)
                 */
                return $this->Client()
                    ->request('GET', $url)
                    ->getBody()
                    ->getContents();
            }
            catch(RequestException | GuzzleException $e)
            {
                $i++;
            }
        }

        try
        {
            /**
             * @description: return content from site (without proxy)
             */
            return $this->noProxyClient()
                ->request('GET', $url)
                ->getBody()
                ->getContents();
        } catch(GuzzleException $e)
        {
            /**
             * @description: When any connection is failed returns Error Message
             */
            var_dump($e->getMessage());
            return $e->getMessage();
        }

    }

}