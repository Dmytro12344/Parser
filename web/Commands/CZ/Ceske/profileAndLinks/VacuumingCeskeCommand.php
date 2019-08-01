<?php

namespace Commands\CZ\Ceske\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingCeskeCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('cz:vacuuming-42')
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
            if($result['name'] !== ''){
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
            return $crawler->filter('.area > .fac-detail-heading')->text();
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
            return $crawler->filter('.fac-pivovar + h2')->text();
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
            return $crawler->filterXPath("//span[@itemprop='streetAddress']")->text();
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
            return str_replace(' ', '', $crawler->filterXPath("//span[@itemprop='postalCode']")->text());
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
            return $crawler->filterXPath("//span[@itemprop='addressLocality']")->text();
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
            $phone = $crawler->filter('.fac-contact-phone')->filter('tr > td')->eq(1)->text();
            $phone = str_replace([' ', '+', '/', '-', '_', ')', '('], '', $phone);
            if(strpos($phone, ',')){
                $phone = explode(',', $phone);
                return $phone[0];
            }
            return $phone;
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
            return '';
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
        $stream = fopen('parsed6.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}