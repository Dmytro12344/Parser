<?php

namespace Wraps;

use GuzzleHttp\Psr7\Request;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Pool;


class WrapPars
{
    protected $link = 'https://www.zivefirmy.cz/auto-moto-vozidla-autoskla-motocykly-automobily_o897?pg=';
    protected $links = [];

    public function getLinks() : void
    {
        $guzzle = new GuzzleWrap();

        for($i = 1 ; $i <= 509; $i++){
            $crawler = new Crawler($guzzle->getContent($this->link . $i));
            $this->getProfile(40, $crawler);
            var_dump($this->links);
        }
    }


    public function getPars(): void
    {
        $guzzle = new GuzzleWrap();
        $fp = fopen('parsed.csv', 'w+');

        for ($i = 1; $i <= 509; $i++) {
            $crawler = new Crawler($guzzle->getContent($this->link . $i));

            $pool = new Pool($guzzle->Client(), $this->getProfile(40, $crawler), [
                'concurrency' => 5,
                'fulfilled' => function ($response, $index) use (&$fp) {

                    $crawler = new Crawler($response->getBody()->getContents());

                    $name = $crawler->filterXPath("//h1[@itemprop='name']")->text();

                    if ($crawler->filterXPath("//span[@itemprop='telephone']")->count() > 0) {
                        $phone = $crawler->filterXPath("//span[@itemprop='telephone']")->text();
                    } else {
                        $phone = '';
                    }

                    if ($crawler->filterXPath("//span[@itemprop='streetAddress']")->count() > 0) {
                        $address = $crawler->filterXPath("//span[@itemprop='streetAddress']")->text();
                    } else {
                        $address = '';
                    }

                    if ($crawler->filterXPath("//span[@itemprop='addressLocality']")->count() > 0) {
                        $city = $crawler->filterXPath("//span[@itemprop='addressLocality']")->text();
                    } else {
                        $city = $crawler->filter('.wrapper > .media > .media-body')->text();
                    }

                    if ($crawler->filterXPath("//span[@itemprop='postalCode']")->count() > 0) {
                        $postal = $crawler->filterXPath("//span[@itemprop='postalCode']")->text();
                    } else {
                        $postal = '';
                    }

                    $str = [trim($name), trim($city), trim($address), trim($postal), trim($phone)];
                    fputcsv($fp, $str);
                    var_dump($index);

                },
                'rejected' => function ($reason, $index) {
                    var_dump("$reason $index REJECTED");
                },
            ]);

            $pool->promise()->wait();
        }
        fclose($fp);
    }



    /*
    public function getProfile($total, $crawler)
    {
        $uri = 'https://www.zivefirmy.cz';

        for ($k = 0; $k < $total; $k++) {
            $filter = $crawler->filter('.company-item >.block')->eq($k);
            //$new_link = $uri . $filter->filter('.title > a')->attr('href');
            $this->links[] = $uri . $filter->filter('.title > a')->attr('href');
            //yield new Request('GET', $new_link);
        }
    } */
}



/*
    public function globalMas() : array
    {
        return file('list.txt');
    }



    public function after ($after, $string) : string
    {
        if(!is_bool(strpos($string, $after))) {
            return substr($string, strpos($string, $after) + strlen($after));
        }
        return '';
    }

    public function before($before, $string) : string
    {
        $str = substr($string, 0, strpos($string, $before));
        if(is_string($str)){
            return $str;
        }
        return '';
    }

} */