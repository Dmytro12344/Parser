<?php


namespace Commands\CZ\Sluzby;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJar;


class ObchodyParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('cz:start-3')
            ->setDescription('Starts download from www.obchody.sluzby.cz')
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
        $links = file('web/Commands/CZ/Obchody/list.txt', FILE_SKIP_EMPTY_LINES);

        foreach($links as $key => $link){

            for($i = 1; $i <= 28; $i++) {
                $crawler = new Crawler($guzzle->getContent(trim($link).$i));

                for($j = 0; $j < $this->getTotalRecords($crawler); $j ++) {
                    $result = array_values([
                        'name' => $this->getCompanyName($crawler, $j),
                        'telephone' => $this->getPhone($crawler, $j),
                        'address' => $this->getAddress($crawler, $j),
                        'postal' => $this->getPostal($crawler, $j),
                        'city' => $this->getCity($crawler, $j),
                    ]);

                    var_dump($result);

                    $this->writeToFile([$result]);
                }
            }

        }
    }

    /**
     * @param $crawler
     * @param int $k
     * @return string
     */
    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        return $crawler->filterXPath("//header[@itemprop='itemreviewed']")->eq($k)->filter('strong > a')->text();
    }

    /**
     * @param Crawler $crawler
     * @param $i
     * @return string
     * Returns company phone
     */
    public function getPhone(Crawler $crawler, $i) : string
    {
        return '';
    }


    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company address
     */
    protected function getAddress(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filterXPath("//span[@itemprop='contentLocation']")->eq($k)->filter('span')->eq(1)->text();

        if(isset($filter)) {
            return substr($crawler->filterXPath("//span[@itemprop='contentLocation']")->eq($k)->filter('span')->eq(1)->text(), 0, -1);
        }

        return '';
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company Cod Postal
     */
    protected function getPostal(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filterXPath("//span[@itemprop='contentLocation']")->eq($k)->filter('span')->count();

        if($filter === 2){
            return substr($crawler->filterXPath("//span[@itemprop='contentLocation']")->eq($k)->filter('span')->eq(1)->text(), 0, -1);
        }

        return substr($crawler->filterXPath("//span[@itemprop='contentLocation']")->eq($k)->filter('span')->eq(2)->text(), 0, -1);

    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company City
     */
    protected function getCity(Crawler $crawler, int $k)
    {
        return $crawler->filterXPath("//span[@itemprop='contentLocation']")->eq($k)->filter('strong')->text();
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total pages from site
     */
    protected function getTotalPages(Crawler $crawler) : int
    {
        $key = $crawler->filter('.pagination')->children()->count();
        $totalPages = $crawler->filter('.pagination > li')->eq($key - 3)->text();
        return (int)$totalPages;
    }

    /**
     * @param Crawler $crawler
     * @param int $total
     * @return \Generator
     */
    protected function getProfileLink(Crawler $crawler, int $total) : \Generator
    {
        $url = 'http://www.obchodnirejstrikfirem.cz';

        for($i = 0; $i < $total; $i++) {

            $filter = $crawler->filterXPath("//header[@itemprop='itemreviewed']")->eq($i);
            $urn = $filter->filter('strong > a')->attr('href');
            var_dump($urn);

            $proxy = file('proxy.csv');
            $rand = mt_rand(1, 247);
            $proxyString = 'http://marekroziecki:pLnWYmR3@' . trim($proxy[$rand]) . ':60099';


            yield new Request('GET', $url . $urn);
        }
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    public function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('.slp-list-offers-item')->count();
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
            $jar  =  new \GuzzleHttp\Cookie\CookieJar;

            $proxy = file('proxy.csv');
            $rand = mt_rand(1, 247);
            $proxyString = 'http://marekroziecki:pLnWYmR3@' . trim($proxy[$rand]) . ':60099';


            yield new Request('GET', $uri, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:66.0) Gecko/20100101 Firefox/66.0',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Accept-Language' => 'en-US,en;q=0.5',
                ],
                'proxy' => $proxyString,
                'allow_redirects' => [
                    'max'             => 10,        // allow at most 10 redirects.
                    'strict'          => true,      // use "strict" RFC compliant redirects.
                    'referer'         => true,      // add a Referer header
                    'protocols'       => ['https'], // only allow https URLs
                    'track_redirects' => true
                ]

            ]);
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