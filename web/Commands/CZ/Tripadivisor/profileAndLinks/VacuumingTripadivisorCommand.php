<?php

namespace Commands\CZ\Tripadivisor\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingTripadivisorCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('cz:vacuuming-12')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));
        $result = [
            'category' => trim($this->getCategory($crawler)),
            'name' => trim($this->getCompanyName($crawler)),
            'address' => trim($this->getStreet($crawler)),
            'postal' => trim($this->getPostal($crawler)),
            'city' => trim($this->getCity($crawler)),
            'phone' => trim($this->getPhone($crawler)),
            'email' => trim($this->getEmail($crawler)),
            'site' => trim($this->getSite($crawler)),
        ];
            var_dump($result);
            if($result['name'] !== '' ) {
                $this->writeToFile([$result]);
            }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getTotalRecords(Crawler $crawler) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCompanyName(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.ui_header')->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getStreet(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.street-address')->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPostal(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filter('.locality')->text();
            $filter = str_replace(',', '', $filter);
            $filter = explode(' ', $filter);
            for($i = 0; $i < @count($filter); $i++){
                if(is_numeric($filter[$i]) && is_numeric($filter[$i+1])){
                    return $filter[$i] . $filter[$i+1];
                }
            }
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCity(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filter('.locality')->text();
            $filter = str_replace(',', '', $filter);
            $filter = explode(' ', $filter);
            $city = '';
            for($i = 0; $i < @count($filter); $i++){
                if(is_numeric($filter[$i]) && is_numeric($filter[$i+1])){
                    $pos = $i;
                    for($j = 0; $j < $pos; $j++){
                        $city .= $filter[$j] . ' ';
                    }
                    return $city;
                }
            }

            for ($i = 0; $i < @count($filter); $i++){
                $city .= $filter[$i] . ' ';
            }
            return $city;
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPhone(Crawler $crawler) : string
    {
        try{
            return str_replace(['-', '+', ' ', ')', '('], '', $crawler->filter('.phone + .is-hidden-mobile')->text());
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getEmail(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filter('.restaurants-detail-overview-cards-LocationOverviewCard__detailLink--iyzJI > span > a ')->attr('href');
            $filter = explode(':', $filter);
            $filter = explode('?', $filter[1]);
            return $filter[0];
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getSite(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filter('.is-hidden-mobile + .menu')->attr('onclick');
            $filter = explode(',', $filter);
            $filter = str_replace([')', ' ', '(', '"', "'"], '', $filter[@count($filter) -1]);
            if(strpos($filter, 'www')){
                return $filter;
            }
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param array $arr
     * Writes to file
    */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed8.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}