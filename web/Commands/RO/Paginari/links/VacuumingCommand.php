<?php

namespace Commands\RO\Paginari\links;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;


class VacuumingCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('ro:vacuuming-1')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));
        $totalRecords = $this->getTotalRecords($crawler);
        for($k = 0; $k < $totalRecords; $k++) {
            $fullAddress = $this->fullAddress($crawler, $k);
            $result = [
                //'category' => trim($this->getCompanyCategory($crawler, $k)),
                'name' => trim($this->getCompanyName($crawler, $k)),
                'street' => trim($this->getAddress($fullAddress)),
                'postal' => trim($this->getPostal($fullAddress)),
                'city' => trim($this->getCity($fullAddress)),
                'phone' => trim($this->getCompanyPhone($crawler, $k)),
            ];
            if($result['name'] !== ''){
                $this->writeToFile([$result]);
            }
        }
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    protected function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('.result-items > li')->count();
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company category
     */
    protected function getCompanyCategory(Crawler $crawler, int $k) : string
    {
        return $crawler->filter('#serviciimini')->attr('value');
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     * Returns company name
     */
    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        try{
            return $crawler->filter('.result-items > li')->eq($k)->filter('.mini-header > .item-heading > a')->text();
        } catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return array
     * Returns company full address
     */
    protected function fullAddress(Crawler $crawler, int $k) : array
    {
        try{
            $filter = trim($crawler->filter('.result-items > li')->eq($k)->filter('.address')->text());
            return explode(' ', $filter);
        } catch (\Exception $e){
            return [];
        }
    }

    /**
     * @param $fullAddress
     * @return bool|string
     * Return company cod postal
     */
    public function getPostal(array $fullAddress) : string
    {
        try {
            foreach ($fullAddress as $key => $item) {
                if(strpos($item, ',')){
                    $item = str_replace(',', '', $item);
                }
                if(is_numeric($item) && strlen($item) === 6) {
                    return $item;
                }
            }
            return '';
        } catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param $fullAddress
     * @return string
     * Returns company City
     */
    public function getCity(array $fullAddress) : string
    {
        try {
            $oldAddress = '';
            $cities = file('web/Commands/RO/listOfCity.csv');
            $oldAddress = implode($oldAddress, $fullAddress);
            foreach ($cities as $city) {
                if (strpos($oldAddress, trim($city))) {
                    return $city;
                }
            }

            foreach ($fullAddress as $key => $item) {

                if ($item === 'Jud.') {
                    $oldAddress = explode('Jud.', $oldAddress);
                    if(strpos($oldAddress[1], ',')) {
                        return str_replace(',', ' ', $oldAddress[1]);
                    }
                    return $oldAddress[1];
                }

                if($item === 'Cod') {
                    $oldAddress = explode('Cod', $oldAddress);
                    $oldAddress = explode(',', $oldAddress[0]);
                    if(strpos($oldAddress[count($oldAddress)-1], ',')) {
                        return str_replace(',', ' ', $oldAddress[count($oldAddress)-1]);
                    }
                    return $oldAddress[count($oldAddress)-1];
                }
            }
            return '';
        } catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param array $fullAddress
     * @return string
     * Returns company street
     */
    public function getAddress(array $fullAddress) : string
    {
        try {
            $street = '';
            foreach ($fullAddress as $key => $item) {
                if ($item === 'Cod') {
                    for ($i = 0; $i < $key - 1; $i++) {
                        $street .= $fullAddress[$i] . ' ';
                    }
                    if (strpos($street, ',')) {
                        return str_replace(',', ' ', $street);
                    }
                    return $street;
                }
                if ($item === 'Jud.') {
                    for ($i = 0; $i < $key - 1; $i++) {
                        $street .= $fullAddress[$i] . ' ';
                    }
                    if (strpos($street, ',')) {
                        return str_replace(',', ' ', $street);
                    }
                    return $street;
                }
                if ($item === 'COM.') {
                    return $street;
                }
            }
            $oldAddress = implode($street, $fullAddress);
            if (strpos($oldAddress, ',')) {
                $oldAddress = explode(',', $oldAddress);
                for ($i = 0; $i < @count($oldAddress) - 1; $i++) {
                    $street .= $oldAddress[$i] . ' ';
                }
                return $street;
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
     * Returns company phone
     */
    protected function getCompanyPhone(Crawler $crawler, int $k) : string
    {
        try{
            $filter = $crawler->filter('.result-items > li')->eq($k)->filterXPath("//li[@itemprop='telephone']")->text();
            return preg_replace('/[\D]/', '', $filter);
        } catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param array $arr
     * Writes to file information about company
     */
    protected function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed1.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }




}