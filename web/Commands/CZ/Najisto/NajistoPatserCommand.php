<?php


namespace Commands\CZ\Najisto;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;


class NajistoPatserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('start-4')
            ->setDescription('Starts download from www.najisto.centrum.cz')
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

        for($j = 1; $j <= 82; $j++) {
            $link = "https://najisto.centrum.cz/?fp=1&p=$j&what=zlatnictv%25C3%25AD";
            $crawlerHelper = new Crawler($guzzle->getContent($link));

            for ($i = 0; $i <= 200; $i++) {
                try {

                    $result = array_values([
                        'name' => trim($this->getCompanyName($crawlerHelper, $i)),
                        'street' => trim($this->getAddress($crawlerHelper, $i)),
                        'postal' => trim($this->getPostal($crawlerHelper, $i, $guzzle)),
                        'city' => trim($this->getCity($crawlerHelper, $i)),
                        'phone' => trim($this->getPhone($crawlerHelper, $i)),
                    ]);
                }
                catch (\Exception $e){
                    continue;
                }
                var_dump($result);
                $this->writeToFile([$result]);
            }
        }
    }


    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filterXPath("//span[@itemprop='name']")->eq($k)->text();
        return $filter ?? '';
    }

    protected function getAddress(Crawler $crawler, $k) : string
    {
        $filter = $crawler->filterXPath("//span[@itemprop='streetAddress']")->eq($k)->text();
        return $filter ?? '';
    }

    protected function getPostal(Crawler $crawler, int $k, GuzzleWrap $guzzle) : string
    {
        $uri = $crawler->filter('.companyInfo > .companyTitle > a')->eq($k)->attr('href');

        $crawlerHelper = new Crawler($guzzle->getContent($uri));
        $postal = $crawlerHelper->filterXPath("//li[@class='addressZip']")->text();

        return $postal ?? '';
    }

    protected function getCity(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filterXPath("//span[@itemprop='addressLocality']")->eq($k)->text();
        if(isset($filter) && !empty($filter)){
            return $filter;
        }
        return '';
    }

    public function getPhone(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filterXPath("//li[@class='cellphoneNumber']")->eq($k)->text();
        return $filter ?? '';
    }


    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    public function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('.microBranch')->count();
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