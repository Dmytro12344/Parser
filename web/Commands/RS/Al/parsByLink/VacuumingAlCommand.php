<?php

namespace Commands\RS\Al\parsByLink;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingAlCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:vacuuming-6')
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
        $totalRecords = $this->getTotalRecords($crawler);

        for($k=0; $k < $totalRecords; $k++) {
            $phone = trim($this->getPhone($crawler, $k));

            if($phone !== '') {
                $result =
                    trim($this->getCompanyName($crawler, $k)) . '}##{' .
                    trim($this->getStreet($crawler, $k)) . '}##{' .
                    trim($this->getCity($crawler, $k)) . '}##{' .
                    trim($this->getPostal($crawler, $k)) . '}##{' .
                    $phone . "\n";

                var_dump($result);
                $this->writeToFile($result);
            }
        }
    }

    /**
     * @param Crawler $crawler
     * @return int
    */
    protected function getTotalRecords(Crawler $crawler) : int
    {
        try{
            return (int)$crawler->filter('.ui-widget-content > tr')->count();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getCategory(Crawler $crawler, int $k) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        try{
            return $crawler->filter('.ui-widget-content > tr')->eq($k)->filter('td')->eq(0)->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getStreet(Crawler $crawler, int $k) : string
    {
        try{
            $filter = $crawler->filter('.ui-widget-content > tr')->eq($k)->filter('td')->eq(8)->text();

            if(strpos($filter, ',')){
                $filter = explode(',', $filter);
                return $filter[0];
            }
            return $filter;

        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getPostal(Crawler $crawler, int $k) : string
    {
        try{
            return $crawler->filter('.ui-widget-content > tr')->eq($k)->filter('td')->eq(10)->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getCity(Crawler $crawler, int $k) : string
    {
        try{
            return $crawler->filter('.ui-widget-content > tr')->eq($k)->filter('td')->eq(9)->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getPhone(Crawler $crawler, int $k) : string
    {
        try{
            $filter = $crawler->filter('.ui-widget-content > tr')->eq($k)->filter('td')->eq(6)->text();
            return str_replace([' ', '+', '/'], '', $filter);
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getEmail(Crawler $crawler, int $k) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getSite(Crawler $crawler, int $k) : string
    {
        try{
            return 'PLACE FOR LOGICK';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param string $str
     * Writes to file
    */
    public function writeToFile(string $str) : void
    {
        $stream = fopen('parsed5.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}