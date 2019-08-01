<?php

namespace Commands\CZ\Restu\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;
use function GuzzleHttp\Psr7\str;

class VacuumingRestuCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('cz:vacuuming-43')
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
            if($result['name'] !== '') {
                $this->writeToFile([$result]);
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
            return $crawler->filter('.restaurant-detail-header__restaurant-name')->text();
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
            $filter = $crawler->filter('address > a')->text();
            $filter = explode(',', $filter);
            return $filter[0];
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
            $filter = $crawler->filter('address > a')->text();
            $filter = explode(',', $filter);
            return $filter[1];
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
            return preg_replace('/[^ \w-]/', '', $crawler->filter('.restaurant-phone-popup__phone')->text());
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
            return $crawler->filter('.track-fb')->attr('href');
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
            return $crawler->filter('.track-restaurant-web')->attr('href');
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
        $stream = fopen('parsed7.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}