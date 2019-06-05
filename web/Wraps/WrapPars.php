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
        $str =[];

        $fp = fopen('parsed.csv', 'w');



        for($i = 1; $i <= $countLinks; $i++) {
            for ($j = 1; $j <= 500; $j++) {
                $crawler = new Crawler($guzzle->getContent($this->linkPars($linksMas[$i], $j)));
                for ($k = 0; $k < 20; $k++) {
                    $filter = $crawler->filter('ul>li>.result')->eq($k);


                    if($filter->filterXPath("//*[@itemprop='name']")->count() <= 0)
                    {
                        continue 3;
                    }
                    $companyName = $filter->filterXPath("//*[@itemprop='name']")->text();




                    //check to correct telephone
                    if ($filter->filterXPath("//*[@itemprop='telephone']")->count() > 0) {
                        $companyTelephone = substr(preg_replace("/[^0-9]+/", "", $filter->filterXPath("//*[@itemprop='telephone']")->text()), 1);

                    } else {
                        continue;
                    }

                    if ($filter->filterXPath("//*[@itemprop='address']")->count() > 0) {
                        $fullAddress = $filter->filterXPath("//*[@itemprop='address']")->text();


                        //check to correct POSTAL COD
                        if (!empty(strpos($fullAddress, 'Cod Postal'))) {
                            $postal = explode(" ", strrchr($fullAddress, 'Postal'));
                            if (isset($postal[1]) && substr($postal[1], -1) === ',') {
                                $result = rtrim($postal[1], ',');
                                $companyPostalCod = $result;
                            } else {
                                $companyPostalCod = '';
                            }
                        } else {
                            $companyPostalCod = '';
                        }

                        //check to correct CIty

                        if (!is_numeric(strrchr($fullAddress, ' '))) {
                            $companyCity = strrchr($fullAddress, ' ');
                        } else {
                            $result = $this->before(strrchr($fullAddress, ', Cod Postal '), $fullAddress);
                            $companyCity = strrchr($result, ' ');
                        }

                        //check to correct Address
                        if (!ctype_upper(strrchr($fullAddress, ' '))) {
                            if (!empty(strrchr($fullAddress, 'Jud. '))) {
                                $companyAddress = $this->before(strrchr($fullAddress, ', Jud. '), $fullAddress);
                            } elseif (!empty(strrchr($fullAddress, 'Cod Postal '))) {
                                $companyAddress = $this->before(strrchr($fullAddress, ', Cod Postal '), $fullAddress);
                            } else {
                                $companyAddress = $this->before(strrchr($fullAddress, ', '), $fullAddress);
                            }
                            if (!empty(strrchr($companyAddress, 'Cod Postal '))) {
                                $companyAddress = $this->before(strrchr($companyAddress, ', Cod '), $companyAddress);
                            }
                            if (!empty(strrchr($companyAddress, $companyCity))) {
                                $companyAddress = $companyAddress = $this->before(strrchr($companyAddress, ", $companyCity"), $companyAddress);
                            }
                        } else {
                            continue;
                        }
                    } else {
                        continue;
                    }


                    $str[] = [trim($companyName), trim($companyAddress), trim($companyCity), trim($companyPostalCod),trim($companyTelephone)];

                    foreach($str as $value)
                    {
                        fputcsv($fp, $value);
                    }
                    var_dump($str);

                }
                echo "\n";
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
}