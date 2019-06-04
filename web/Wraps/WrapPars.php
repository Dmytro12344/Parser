<?php


namespace Wraps;

use \Exception;
use Symfony\Component\DomCrawler\Crawler;

class WrapPars
{
    public function getPars($html) : void
    {
        $j = 0;
        $crawler = new Crawler($html);

        for($i = 0; $i < 20; $i++) {
            $filter = $crawler->filter('ul>li>.result')->eq($i);
            $companyName = $filter->filterXPath("//*[@itemprop='name']")->text();

            if($filter->filterXPath("//*[@itemprop='telephone']")->count() > 0)
            {
                $companyTelephone = $filter->filterXPath("//*[@itemprop='telephone']")->text();
            } else {
                continue;
            }

            if($filter->filterXPath("//*[@itemprop='address']")->count() > 0)
            {
                $fullAddress = $filter->filterXPath("//*[@itemprop='address']")->text();

                if(!empty(strpos($fullAddress, 'Cod Postal')))
                {
                    $postal = explode(" ", strrchr($fullAddress, 'Postal'));
                    $result = rtrim($postal[1], ',');
                    $companyPostalCod = $result;
                } else {
                    $companyPostalCod = '';
                }

                if(!is_numeric(strrchr($fullAddress,' ')))
                {
                    $companyCity = strrchr($fullAddress,' ');
                } else {
                    $companyCity = '';
                }



                $companyAddress = $this->before(', Cod Postal', $fullAddress);
            } else {
                continue;
            }




            echo $companyCity . "\n";
            //echo trim($companyName) .trim($companyTelephone) . trim($companyAddress) . "\n";

        }
    }










    function after ($after, $string) : string
    {
        if(!is_bool(strpos($string, $after))) {
            return substr($string, strpos($string, $after) + strlen($after));
        }
        return '';
    }


    function before ($before, $string) : string
    {
        $str = substr($string, 0, strpos($string, $before));
        if(is_string($str)){
            return $str;
        }
        return '';
    }
}