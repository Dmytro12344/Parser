<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;


class VacuumingCommand extends Command
{
    protected function configure() : void
    {
        $this->setName('app:vacuuming')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));


        for($k=0; $k<20; $k++) {

            $fullAddress = trim($this->XPathContent($crawler, "//span[@class='address']", $k));

            $result = array_values([
                'category' => $this->filterContent($crawler, '#serviciimini')->attr('value'),
                'name' => $this->filterContent($crawler, '.result > .item-heading > a')->eq($k)->text(),
                'phone' => $this->XPathContent($crawler, "//li[@itemprop='telephone']", $k),
                'postal' => $this->getPostal($fullAddress),
                'city' => $this->getCity($fullAddress),
                'street' => $this->getAddress($fullAddress),
            ]);


            $this->writeToFile([$result]);

        }

        $output->writeln([

        ]);
    }

    /**
     * @param Crawler $crawler
     * @param string $xPath
     * @return string
     */
    public function XPathContent(Crawler $crawler, string $xPath, $k) : string
    {
        $filter = $crawler->filterXPath($xPath)->eq($k);

        if($filter->count() > 0){
            return $filter->text();
        }
        return '';
    }

    /**
     * @param $crawler
     * @param string $filter ('.class > #id ')
     * @return string
     * returns filtered content
     */
    public function filterContent(Crawler $crawler, string $filter) : object
    {
        return $crawler->filter($filter);
    }

    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }

    public function getPostal($fullAddress)
    {
        $results = explode(" ", $fullAddress);

        foreach($results as $key => $item){
            if($results[$key] === 'Postal'){
                return substr($results[$key + 1], 0, -1);
            }
        }
        return " ";
    }



    public function getCity($fullAddress)
    {
        $address = explode(" ", $fullAddress);
        foreach($address as $key => $item){
            if($item === 'Jud.'){
                return substr($address[$key - 1], 0, -1);
            }

            if($item === 'Cod'){
                return substr($address[$key - 1], 0, -1);
            }

            if($item === 'COM.'){
                return  substr($address[$key + 1], 0, -1);
            }
        }
        return " ";
    }

    public function getAddress($fullAddress)
    {
        $address = explode(" ", $fullAddress);
        $street = " ";

        if($address[0] === 'COM.'){
            return '';
        }

        foreach($address as $key =>$item){

            if($item === 'COM.'){
                break;
            }

            if($item === 'Cod'){
                for($i = 0; $i < $key -1; $i++){
                    $street .= $address[$i];
                }
                return substr($street, 0, -1);
            }
            if($item === 'Jud.'){
                for($i = 0; $i < $key -1; $i++){
                    $street .= $address[$i];
                }
                return substr($street, 0, -1);
            }
        }

        return $street;
    }






}