<?php


namespace Commands\CZ\Obchodnirejstrikfirem;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;


class ObchodnirejstrikfiremParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('start-2')
            ->setDescription('Starts download from www.obchodnirejstrikfirem.cz')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $links = file('web/Commands/CZ/Obchodnirejstrikfirem/list.txt', FILE_SKIP_EMPTY_LINES);

        foreach($links as $key => $link){
            $crawler = new Crawler($guzzle->getContent($link . '1/'));
            $totalPage = $this->getTotalPages($crawler);

                $pool = new Pool($guzzle->Client(), $this->getContent($totalPage, $link), [
                    'concurrency' => 5,
                    'fulfilled' => function ($response, $index) use ($guzzle) {
                        $crawlerProfile = new Crawler($response->getBody()->getContents());
                        $totalRecords = $this->getTotalRecords($crawlerProfile);

                        $profilePool = new Pool($guzzle->Client(), $this->getProfileContent($crawlerProfile, $totalRecords), [
                                'concurrency' => 5,
                                'fulfilled' => function ($response, $index) {
                                    $crawler = new Crawler($response->getBody()->getContents());
                                    //var_dump($index);
                                    $this->getCompanyName($crawler);

                                },
                                'rejected' => function ($reason, $index) {
                                    var_dump("REJECTED $index");
                                }
                            ]);
                            $promise = $profilePool->promise();
                            $promise->wait();




                    },
                    'rejected' => function ($reason, $index) use ($output) {
                        $output->writeln([
                            "$reason -> $index REJECTED"
                        ]);
                    },
                ]);
                $promise = $pool->promise();
                $promise->wait();
        }
    }

    /**
     * @param $crawler
     * @return string
     */
    protected function getCompanyName(Crawler $crawler)
    {
        var_dump($crawler->filter('h1')->text());
         //return $crawler->filter('.levystred2 > .stred2 > h1')->text();
    }

    /**
     * @param Crawler $crawler
     * @param $i
     * @return string
     * Returns company phone
     */
    public function getPhone(Crawler $crawler, $i) : string
    {
        return $crawler->filter('.btn-toolbar > .btn-group > button')->eq($i)->text();
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company Full Address (street, postal, city)
     */
    protected function getFullAddress(Crawler $crawler, int $k) : string
    {
        $filter =  $crawler->filter('.icon-list')->eq($k);
        return $filter->filter('li')->eq(0)->text();
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company address
     */
    protected function getAddress(Crawler $crawler, int $k) : string
    {
        $fullAddress = explode(",", $this->getFullAddress($crawler, $k));
        return $fullAddress[0];
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company Cod Postal
     */
    protected function getPostal(Crawler $crawler, int $k) : string
    {
        $fullAddress = explode(',', $this->getFullAddress($crawler, $k));
        $postal = explode(" ", $fullAddress[1]);

        if(is_numeric($postal[1]) && is_numeric($postal[2])){
            return $postal[1] . $postal[2];
        }

        return " ";
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company City
     */
    protected function getCity(Crawler $crawler, int $k)
    {
        $fullAddress = explode(",", $this->getFullAddress($crawler, $k));
        $city =  explode(" ", $fullAddress[1]);
        $currentCity = '';

        if(!is_numeric($city[1]) && !is_numeric($city[2])){
            $j = 1;

            while($j !== count($city)){
                $currentCity .= @$city[$j] . ' ';
                $j++;
            }

            return $currentCity;
        }

        for($i = 3; $i <= count($city); $i++){
            $currentCity .= @$city[$i] . ' ';
        }

       return $currentCity;
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total pages from site
     */
    protected function getTotalPages(Crawler $crawler) : int
    {
        $filter = $crawler->filter('.UnivBox3 + div')->text();
        $filter = explode('/', trim($filter));
        $totalPages = substr($filter[1], 0, -1);
        return (int)$totalPages;
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    public function getTotalRecords(Crawler $crawler)
    {
        return $crawler->filterXPath("//div[@style='margin-bottom:10px']")->count();
    }

    /**
     * @param $total
     * @param $url
     * @return \Generator
     * Generate new Request to get information from page
     */
    public function getContent(int $total, string $url) : \Generator
    {
        for ($k = 1; $k <= $total; $k++) {
            $uri = $url . $k . '/';
            yield new Request('GET', $uri);
        }
    }

    protected function getProfileLink(Crawler $crawler, $k) : string
    {
        $filter = $crawler->filterXPath("//td[@valign='bottom']")->eq($k);
        $urn = $filter->filter('a')->attr('href');
        return $urn;
    }

    protected function getProfileContent(Crawler $crawler, int $total) : \Generator
    {
        $url = 'http://www.obchodnirejstrikfirem.cz';

        for ($k = 1; $k <= $total; $k++) {
            $urn = $this->getProfileLink($crawler, $k);
            $uri = $url . $urn ;
            yield new Request('GET', $uri);
        }
    }

    /**
     * @param array $arr
     * Writes to file
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }


}