<?php


namespace Commands\CZ\Csfirmy\notAsinc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class ZivefirmyParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('start-11')
            ->setDescription('Starts download from www.zivefirmy.cz')
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
        $links = file('web/Commands/CZ/Zivefirmy/list.txt', FILE_SKIP_EMPTY_LINES);

        foreach($links as $key => $link){
            $crawlerTest = new Crawler($guzzle->getContent(trim($link) . '1'));

            for($i = 1; $i <= $this->getTotalPages($crawlerTest); $i++) {
                var_dump($i);
                try {
                    $crawler = new Crawler($guzzle->getContent(trim($link) . $i));
                    $category = $this->getCategory($crawler);

                    for($j = 0; $j < 99999; $j++){
                    $uri = 'https://www.zivefirmy.cz' . $this->getProfileLink($crawler, $j);
                    $crawlerHelper = new Crawler($guzzle->getContent($uri));
                        $result = array_values([
                            'category' => $category,
                            'name' => trim($this->getCompanyName($crawlerHelper)),
                            'street' => trim($this->getAddress($crawlerHelper)),
                            'postal' => trim($this->getPostal($crawlerHelper)),
                            'city' => trim($this->getCity($crawlerHelper)),
                            'phone' => trim($this->getPhone($crawlerHelper)),
                            'email' => trim($this->getEmail($crawlerHelper)),
                            'site' => trim($this->getSite($crawlerHelper)),
                        ]);

                        var_dump($result);
                        $this->writeToFile([$result]);
                    }
                }
                catch (\Exception $e){
                    continue;
                }

            }
        }

    }

    protected function getCategory(Crawler $crawler) : string
    {
        $filter = $crawler->filter('.content-main > h1')->html();
        $category = explode('<span ', $filter);
        return trim($category[0]);
    }

    protected function getCompanyName(Crawler $crawler) : string
    {
        if($crawler->filterXPath("//span[@itemprop='name']")->count() > 0){
            return $crawler->filterXPath("//span[@itemprop='name']")->text();
        }
        return '';
    }

    public function getPhone(Crawler $crawler) : string
    {
        if($crawler->filterXPath("//span[@itemprop='telephone']")->count() > 0){
            return $crawler->filterXPath("//span[@itemprop='telephone']")->text();
        }

        if($crawler->filterXPath("//span[@itemprop='telephone']")->filter('font > font')->count() > 0){
            return $crawler->filterXPath("//span[@itemprop='telephone']")->filter('font > font')->text();
        }

        return '';
    }

    protected function getEmail(Crawler $crawler) : string
    {
        if($crawler->filterXPath("//a[@itemprop='email']")->count() > 0){
            return $crawler->filterXPath("//a[@itemprop='email']")->text();
        }
        return '';
    }

    protected function getSite(Crawler $crawler) : string
    {
        if($crawler->filterXPath("//span[@class='title']")->count() > 0){
            return $crawler->filterXPath("//span[@class='title']")->text();
        }
        return '';
    }


    protected function getAddress(Crawler $crawler) : string
    {
        if($crawler->filterXPath("//span[@itemprop='streetAddress']")->count() > 0){
            return $crawler->filterXPath("//span[@itemprop='streetAddress']")->text();
        }
        return '';
    }

    protected function getPostal(Crawler $crawler) : string
    {
        if($crawler->filterXPath("//span[@itemprop='postalCode']")->count() > 0){
            return $crawler->filterXPath("//span[@itemprop='postalCode']")->text();
        }
        return '';
    }

    protected function getCity(Crawler $crawler) : string
    {
        if($crawler->filterXPath("//span[@itemprop='addressLocality']")->count() > 0){
            return $crawler->filterXPath("//span[@itemprop='addressLocality']")->text();
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
        $filter =  $crawler->filterXPath("//ul[@class='pagination']")->children()->count();
        $totalPages = $crawler->filter('.pagination > li')->eq($filter -2)->text();
        return (int)$totalPages;
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    public function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('#containerIAS > .company-item')->count();
    }

    protected function getProfileLink(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filter('.block')->eq($k);
        return trim($filter->filter( '.title > a')->eq(0)->attr('href'));
    }

    public function getProfile(int $total, Crawler $crawler) : \Generator
    {
        $url = 'https://www.zivefirmy.cz';

        for ($k = 0; $k < $total; $k++) {
            $uri = $url . $this->getProfileLink($crawler, $k);
            yield new Request('GET', $uri);
        }
    }


    /**
     * @param array $arr
     * Writes to file
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed5.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }


}