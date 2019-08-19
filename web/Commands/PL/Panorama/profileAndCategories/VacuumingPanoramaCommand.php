<?php

namespace Commands\PL\Panorama\profileAndCategories;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingPanoramaCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('pl:vacuuming-2')
            ->setDescription('Starts download from ...')
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
                $this->writeToFile([$result]);
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
            $filter = $crawler->filter('title')->text();
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
    protected function getStreet(Crawler $crawler) : string
    {
        try{
            $filter = trim($crawler->filter('.address > .row > div + div')->text());
            $street2 = '';

            /** First option */
            if(strpos($filter, ',')){
                $street = explode(',', $filter);
                return $street[0];
            }

            /** Second option */
            $street = '';
            $currentStreet = explode(' ',$filter);
            if(strpos($currentStreet[0], '-')){
                for($i = 1; $i < @count($currentStreet); $i++){
                    $street .= $currentStreet[$i] . ' ';
                }
                return $street;
            }

            /** Third option */
            $street = str_replace('-', '', $filter);
            $addressArr = explode(' ', $street);
            foreach($addressArr as $key => $street) {
                if(is_numeric($street) && strlen($street) > 3){
                    for($i = 0; $i < $key; $i++){
                        $street2 .= $addressArr[$i] . ' ';
                    }
                    return $street2;
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
    protected function getPostal(Crawler $crawler) : string
    {
        try{
            $filter = trim($crawler->filter('.address > .row > div + div')->text());
            if(strpos($filter, ',')) {
                $postal = explode(',', $filter);
                $postal = preg_replace('/[^0-9]/', '', $postal[1]);
                if (is_numeric($postal)) {
                    return $postal;
                }
            }

            if(!strpos($filter , ',')){
                $postal = str_replace('-', '', $filter);
                $addressArr = explode(' ', $postal);
                foreach($addressArr as $postal) {
                    if(is_numeric($postal) && strlen($postal) > 3){
                        return $postal;
                    }
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
            $total = $crawler->filter('.breadcrumb > li')->count();
            return $crawler->filter('.breadcrumb > li ')->eq($total -2)->text();
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
            $filter = $crawler->filter('.addax-cs_ip_phonenumber_click')->text();
            $filter = preg_replace('/\D/', '', $filter);
            if(is_numeric($filter)){
                return $filter;
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
        $stream = fopen('parsed.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}