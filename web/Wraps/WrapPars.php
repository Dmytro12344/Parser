<?php


namespace Wraps;

use \Exception;
use Symfony\Component\DomCrawler\Crawler;

class WrapPars
{
    public function getPars() : void
    {
        $guzzle = new GuzzleWrap();
        $linksMas = $this->globalMas();
        $countLinks = count($linksMas);


        $fp = fopen('parsed.csv', 'w');



        for($i = 1; $i <= $countLinks; $i++)
        {
            for ($j = 1; $j <= 500; $j++)
            {
                $crawler = new Crawler($guzzle->getContent($this->linkPars($linksMas[$i], $j)));

                for ($k = 0; $k < 20; $k++)
                {
                    $filter = $crawler->filter('ul>li>.result')->eq($k);
                    $str =[];

                    if ($filter->filterXPath("//*[@itemprop='name']")->count() <= 0)
                    {
                        continue 3;
                    }
                    if ($filter->filterXPath("//*[@itemprop='telephone']")->count() <= 0)
                    {
                        continue;
                    }
                    if ($filter->filterXPath("//*[@itemprop='address']")->count() <= 0)
                    {
                        continue;
                    }

                    $companyName = $filter->filterXPath("//*[@itemprop='name']")->text();
                    $companyTelephone = substr(preg_replace("/[^0-9]+/", "", $filter->filterXPath("//*[@itemprop='telephone']")->text()), 1);
                    $fullAddress = $filter->filterXPath("//*[@itemprop='address']")->text();
                    $companyPostalCod = $this->getPostalCod($fullAddress);
                    $companyCity = $this->getCity($fullAddress);
                    $companyAddress = $this->getAddress($fullAddress);





                    $str[] = [trim($companyName), trim($companyAddress), trim($companyCity), trim($companyPostalCod), trim($companyTelephone)];

                    foreach ($str as $value){
                        fputcsv($fp, $value);
                    }
                    var_dump($str);


                echo "\n";
            }
            }
        }
        fclose($fp);
    }









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

    public function getPostalCod($address) : string
    {
        if (!empty(strpos($address, 'Cod Postal'))) {
            $postal = explode(" ", strrchr($address, 'Postal'));
            if (isset($postal[1]) && substr($postal[1], -1) === ',') {
                $result = rtrim($postal[1], ',');
                return $PostalCod = $result;
            } else {
                return $PostalCod = '';
            }
        } else {
            return $PostalCod = '';
        }
    }

    public function getCity($address) : string
    {
        if (!is_numeric(strrchr($address, ' ')))
        {
            return $City = strrchr($address, ' ');
        } else {
            $result = $this->before(strrchr($address, ', Cod Postal '), $address);
            return $City = strrchr($result, ' ');
        }
    }

    public function getAddress($fullAddress) : string
    {
            if (!empty(strrchr($fullAddress, 'Jud. '))) {
                $companyAddress = $this->before(strrchr($fullAddress, ', Jud. '), $fullAddress);
            } elseif (!empty(strrchr($fullAddress, 'Cod Postal '))) {
                $companyAddress = $this->before(strrchr($fullAddress, ', Cod Postal '), $fullAddress);
            } else {
                $companyAddress = $this->before(strrchr($fullAddress, ', '), $fullAddress);
            }
            if (!empty(strrchr($companyAddress, 'Cod Postal '))) {
                $companyAddress = $this->before(strrchr($companyAddress, ', Cod Postal'), $companyAddress);
            }
            if (!empty(strrchr($companyAddress, $this->getCity($fullAddress)))) {
                $companyCity = $this->getCity($fullAddress);
                $companyAddress = $this->before(strrchr($companyAddress, ", $companyCity"), $companyAddress);
            }

        return $companyAddress;
    }
}