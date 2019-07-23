<?php


namespace Commands\CZ\Zlatestranky;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;


class ZlatestrankyParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('start-1')
            ->setDescription('Starts download from www.firmy.cz')
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
        $links = file('web/Commands/CZ/Zlatestranky/list.txt', FILE_SKIP_EMPTY_LINES);

        foreach($links as $key => $link){

            $crawler = new Crawler($guzzle->getContent($link . '1'));
            $totalPage = $this->getTotalPages($crawler);

                $pool = new Pool($guzzle->Client(), $this->getContent($totalPage, $link), [
                    'concurrency' => 7,
                    'fulfilled' => function ($response, $index) {

                        $crawler = new Crawler($response->getBody()->getContents());
                        $totalRecords = $this->getTotalRecords($crawler);
                        var_dump($totalRecords);
                        for($i = 0; $i <= $totalRecords -1; $i++){

                            $result = array_values([
                                'category' => trim($this->getCategory($crawler, $i)),
                                'name' => trim($this->getCompanyName($crawler, $i)),
                                'address' => trim($this->getAddress($crawler, $i)),
                                'postal' => trim($this->getPostal($crawler, $i)),
                                'city' => trim($this->getCity($crawler, $i)),
                                'phone' => trim($this->getPhone($crawler, $i)),
                                'email' => '',
                                'site' => trim($this->getSite($crawler, $i)),
                            ]);

                            var_dump($result);
                            $this->writeToFile([$result]);
                        }
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

    protected function getCategory(Crawler $crawler, int $k) : string
    {
        return $crawler->filter('.icon-list')->eq($k)->filter('li > a')->text();
    }

    protected function getSite(Crawler $crawler, int $k) : string
    {

        if($crawler->filterXPath("//div[@role='toolbar']")->eq($k)
            ->filterXPath("//div[@role='group']")->eq(1)
            ->filter('a')->eq(0)->count() > 0){

            $site = $crawler->filterXPath("//div[@role='toolbar']")->eq($k)
                ->filterXPath("//div[@role='group']")->eq(1)
                ->filter('a')->eq(0)->attr('href');

            $position = strrpos($site, 'www.');

            if($position !== false){
                return $site;
            }

        }


        return '';
    }

    /**
     * @param $crawler
     * @param int $k
     * @return string
     */
    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        return $crawler->filter('.row > .col-xs-9 > h3 > a')->eq($k)->text();
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

        for($i = 3; $i <= @count($city); $i++){
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
        try {

            $key = $crawler->filter('.pagination')->children()->count();
            $totalPages = $crawler->filter('.pagination > li')->eq($key - 3)->text();
            return (int)$totalPages;

        }catch (\Exception $exception){
            return 0;
        }
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    public function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('.list-results')->children()->count();
    }

    /**
     * @param $total
     * @param $url
     * @return \Generator
     * Generate new Request to get information from profile
     */
    public function getContent(int $total, string $url) : \Generator
    {
        for ($k = 1; $k <= $total; $k++) {
            $uri = $url . $k;
            var_dump($uri);
            yield new Request('GET', $uri);
        }
    }

    /**
     * @param array $arr
     * Writes to file
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed1.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }


}