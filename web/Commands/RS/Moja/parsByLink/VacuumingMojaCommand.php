<?php

namespace Commands\RS\Moja\parsByLink;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingMojaCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:vacuuming-4')
            ->setDescription('Starts download information about companies from https://www.moja-delatnost.rs')
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
        var_dump($crawler);die;
        $totalRecords = $this->getTotalRecords($crawler);
        var_dump($totalRecords);die();
        for($i = 0; $i < $totalRecords; $i++) {
            $result = [
                'category' => trim($this->getCategory($crawler)),
                'name' => trim($this->getCompanyName($crawler, $i)),
                'address' => trim($this->getStreet($crawler, $i)),
                'postal' => trim($this->getPostal($crawler, $i)),
                'city' => trim($this->getCity($crawler, $i)),
                'phone' => trim($this->getPhone($crawler, $i)),
                'email' => trim($this->getEmail($crawler, $i)),
                'site' => trim($this->getSite($crawler, $i)),
            ];
            var_dump($result);
            if($result['name'] !== '' && $result['address'] !== '' && $result['postal'] !== '') {
                $this->writeToFile([$result]);
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
            return (int)$crawler->filter('.partner-detail')->count();
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
    protected function getStreet(Crawler $crawler, int $k) : string
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
    protected function getPostal(Crawler $crawler, int $k) : string
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
    protected function getCity(Crawler $crawler, int $k) : string
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
    protected function getPhone(Crawler $crawler, int $k) : string
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
     * @param array $arr
     * Writes to file
    */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed2.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}