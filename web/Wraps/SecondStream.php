<?php

namespace Wraps;

use Symfony\Component\DomCrawler\Crawler;

class SecondStream
{
    public function getPars() : void
    {
        $guzzle = new GuzzleWrap();
        $link = 'https://www.zivefirmy.cz/auto-moto-vozidla-autoskla-motocykly-automobily_o897?pg=';
        $uri = 'https://www.zivefirmy.cz';


        $fp = fopen('parsed.csv', 'w+');

        for($i = 2; $i <= 509; $i++)
        {
            $crawler = new Crawler($guzzle->getContent($link . $i));
            for($j=0; $j <= 39; $j++)
            {
                $filter = $crawler->filter('.company-item >.block')->eq($j);
                $new_link = $uri . $filter->filter('.title > a')->attr('href');
                $name = $filter->filter('.title > a')->text();


                $step_two_crawler = new Crawler($guzzle->getContent($new_link));

                unset($new_link);

                $phone = '';
                $address = '';
                $city = '';
                $postal = '';


                if($step_two_crawler->filterXPath("//span[@itemprop='telephone']")->count() > 0)
                {
                    $phone = $step_two_crawler->filterXPath("//span[@itemprop='telephone']")->text();
                }

                if($step_two_crawler->filterXPath("//span[@itemprop='streetAddress']")->count() > 0)
                {
                    $address = $step_two_crawler->filterXPath("//span[@itemprop='streetAddress']")->text();
                }

                if($step_two_crawler->filterXPath("//span[@itemprop='addressLocality']")->count() > 0)
                {
                    $city = $step_two_crawler->filterXPath("//span[@itemprop='addressLocality']")->text();
                } else {
                    $city = $step_two_crawler->filter('.wrapper > .media > .media-body')->text();
                }

                if($step_two_crawler->filterXPath("//span[@itemprop='postalCode']")->count() > 0)
                {
                    $postal = $step_two_crawler->filterXPath("//span[@itemprop='postalCode']")->text();
                }


                $str = [trim($name), trim($city), trim($address), trim($postal), trim($phone)];
                fputcsv($fp, $str);
                var_dump($str);
                var_dump($i);
            }
            $i++;
            echo "\n";
            echo "\n";
            echo "\n";
        }

        fclose($fp);
    }



    /*
        public function globalMas() : array
        {
            return file('list.txt');
        }

        public function linkPars($link, $torn) : string
        {
            trim($link);
            $link = substr_replace($link,"$torn/",strlen($link)-3);
            return $link;
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
    */
}