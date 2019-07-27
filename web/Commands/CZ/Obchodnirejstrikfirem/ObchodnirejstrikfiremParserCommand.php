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
        $this->setName('cz:start-2')
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
        $categories = file('web/Commands/CZ/Obchodnirejstrikfirem/list.txt', FILE_SKIP_EMPTY_LINES);

        foreach($categories as $key => $category){

            $link = $this->convertLink(trim($category));
            $crawlerHelper = new Crawler($guzzle->getContent($link));

            for($i = 1; $i <= $this->getTotalPages($crawlerHelper); $i++) {
                $crawler = new Crawler($guzzle->getContent($this->convertLink(trim($category), $i)));
                $pool = new Pool($guzzle->Client(), $this->getProfileLink($crawler, $this->getTotalRecords($crawler)), [
                    'concurrency' => 5,
                    'fulfilled' =>
                        function ($response, $index){
                            $crawler = new Crawler($response->getBody()->getContents());

                            $result =  array_values([
                                'name' => $this->getCompanyName($crawler),
                                'phone' => $this->getPhone($crawler),
                                'address' => $this->getAddress($crawler),
                                'city' => $this->getCity($crawler),
                                'postal' => $this->getPostal($crawler),
                            ]);

                            $this->writeToFile([$result]);

                            var_dump($result);
                        },
                    'rejected' =>
                        function ($reason, $index) use ($output) {
                            $output->writeln([
                                "$reason -> $index REJECTED"
                            ]);
                        },
                ]);
                $promise = $pool->promise();
                $promise->wait();
            }
        }
    }


    protected function getCompanyName(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('#stred2 > h1')->text();
        } catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param $i
     * @return string
     * Returns company phone
     */
    public function getPhone(Crawler $crawler) : string
    {
        try {
            $filter = $crawler->filterXPath("//td[@style='text-align:justify;padding:10px 0 10px 0']")->eq(0)->text();
            $number = explode(':', $filter);

            if (isset($number[1])) {
                $number[1] = str_replace('Fax', '', trim($number[1]));
                $number[1] = str_replace('Tel2', '', trim($number[1]));
                return $number[1];
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
     * Returns company Full Address (street, postal, city)
     */
    protected function getFullAddress(Crawler $crawler) : string
    {
        try {
            return trim($crawler->filterXPath("//td[@style='text-align:justify;padding-top:10px']")->html());
        } catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company address
     */
    protected function getAddress(Crawler $crawler) : string
    {
        try {
            $fullAddress = explode('<br>', $this->getFullAddress($crawler));
            if (isset($fullAddress[3])) {
                $fullAddress[3] = str_replace(',', '', trim($fullAddress[3]));
                return $fullAddress[3];
            }
            return '';
        } catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company Cod Postal
     */
    protected function getPostal(Crawler $crawler) : string
    {
        try {
            $fullAddress = explode('<br>', $this->getFullAddress($crawler));
            if (isset($fullAddress[4])) {
                $fullAddress[4] = str_replace(',', '', trim($fullAddress[4]));
                return $fullAddress[4];
            }
            return '';
        } catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company City
     */
    protected function getCity(Crawler $crawler) : string
    {
        $fullAddress = explode('<br>', $this->getFullAddress($crawler));
        if(isset($fullAddress[2])) {
            $fullAddress[2] = str_replace(',', '', trim($fullAddress[2]));
            return $fullAddress[2];
        }
        return '';
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total pages from site
     */
    protected function getTotalPages(Crawler $crawler) : int
    {
        try {
            $filter = $crawler->filter('.UnivBox3 + div')->text();
            $filter = explode('/', trim($filter));
            $totalPages = substr($filter[1], 0, -1);
            return (int)$totalPages;
        } catch (\Exception $e){
            return 1;
        }
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    public function getTotalRecords(Crawler $crawler) : int
    {
        try{
            return $crawler->filterXPath("//div[@style='margin-bottom:10px']")->count();
        } catch (\Exception $e){
            return 0;
        }
    }


    protected function getProfileLink(Crawler $crawler, int $total) : \Generator
    {
        $url = 'http://www.obchodnirejstrikfirem.cz';

        for($i = 0; $i < $total; $i++) {

            $filter = $crawler->filterXPath("//td[@valign='bottom']")->eq($i);
            $urn = $filter->filter('a')->attr('href');
            yield new Request('GET', $url . $urn);
        }
    }

    protected function convertLink(string $category, int $page=1) : string
    {
        $url = 'https://obchody.sluzby.cz/';
        return urldecode("$url$category/$page");
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