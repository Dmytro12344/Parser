<?php

namespace Wraps\Process;

use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;
use Symfony\Component\Process\Process;

class MainContent
{

    /**
     * @param $paginationURL
     */
    public function setLinks($paginationURL) : void
    {
        $activeProcess = [];
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($paginationURL));

        foreach ($this->getProfile(40, $crawler) as $url) {
            $process = new Process("php application.php app:download-profile-content --url=$url");
            $process->start();
            $activeProcess[] = $process;
            if (count($activeProcess) >= 3) {
                while (count($activeProcess) >= 3) {
                    foreach ($activeProcess as $key => $runningProcess) {
                        if (!$runningProcess->isRunning()) {
                            unset($activeProcess[$key]);
                        }
                    }
                    sleep(1);
                }
            }
        }
    }

    public function getProfileContent($url): void
    {
        $guzzle = new GuzzleWrap();

        $fp = fopen('parsed.csv', 'w+');
        $crawler = new Crawler($guzzle->getContent($url));


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
        var_dump($str);

        fclose($fp);
    }

    /**
     * @param $total
     * @param $crawler
     * @return array
     */
    public function getProfile($total, $crawler) : array
    {
        $uri = 'https://www.zivefirmy.cz';
        $url = [];

        for ($k = 0; $k < $total; $k++) {
            $filter = $crawler->filter('.company-item >.block')->eq($k);
            $url[] = $uri . $filter->filter('.title > a')->attr('href') ."\n";
        }
        return $url;
    }

}
