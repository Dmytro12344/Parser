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

            //check to correct telephone
            if($filter->filterXPath("//*[@itemprop='telephone']")->count() > 0)
            {
                $companyTelephone = substr(preg_replace("/[^0-9]+/","", $filter->filterXPath("//*[@itemprop='telephone']")->text()), 1);

            } else {
                continue;
            }

            if($filter->filterXPath("//*[@itemprop='address']")->count() > 0)
            {
                $fullAddress = $filter->filterXPath("//*[@itemprop='address']")->text();


                //check to correct POSTAL COD
                if(!empty(strpos($fullAddress, 'Cod Postal')))
                {
                    $postal = explode(" ", strrchr($fullAddress, 'Postal'));
                    if(isset($postal[1]) && substr($postal[1], -1) === ',') {
                        $result = rtrim($postal[1], ',');
                        $companyPostalCod = $result;
                    } else {
                        $companyPostalCod = '';
                    }
                } else {
                    $companyPostalCod = '';
                }

                //check to correct CIty
                if(!is_numeric(strrchr($fullAddress,' ')))
                {
                    $companyCity = strrchr($fullAddress,' ');
                } else {
                    $companyCity = '';
                }

                //check to correct Address
                if(!ctype_upper(strrchr($fullAddress, ' ')))
                {
                    if(!empty(strrchr($fullAddress, 'Jud. '))) {
                        $companyAddress = $this->before(strrchr($fullAddress, ', Jud. '), $fullAddress);
                    } elseif(!empty(strrchr($fullAddress, 'Cod Postal '))) {
                        $companyAddress = $this->before(strrchr($fullAddress, ', Cod Postal '), $fullAddress);
                    } else {
                        $companyAddress = $this->before(strrchr($fullAddress, ', '), $fullAddress);
                    }
                    if (!empty(strrchr($companyAddress, 'Cod Postal ')))
                    {
                        $companyAddress = $this->before(strrchr($companyAddress, ', Cod '), $companyAddress);
                    }
                } else {
                    continue;
                }
            } else {
                continue;
            }

            //correct format for file.txt
            $str = trim($companyName) . '}##{' .trim($companyAddress) . '}##{'
               . trim($companyCity) . "}##{" . $companyPostalCod . '}##{' . $companyTelephone . "\n";

            $fp = fopen("file.txt", "a");
            fwrite($fp, $str);
            fclose($fp);


            echo $str;

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