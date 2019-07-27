<?php


namespace Commands\CZ\Sluzby\asyncWithProfile;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;

class VacuumingProfileCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cz:vacuuming-3')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        $result = [
            'category' => trim($this->getCategory($crawler)),
            'name' => trim($this->getCompanyName($crawler)),
            'street' => trim($this->getStreet($crawler)),
            'postal' => trim($this->getPostal($crawler)),
            'city' => trim($this->getCity($crawler)),
            'phone' => trim($this->getPhone($crawler)),
            'email' => trim($this->getEmail($crawler)),
            'site' => trim($this->getSite($crawler)),
        ];


        var_dump($result);
        //$this->writeToFile([$result]);
    }

    protected function getEmail(Crawler $crawler) : string
    {
        try {

        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getSite(Crawler $crawler) : string
    {
        return '';
    }

    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return '';
        } catch (\Exception $e){
            return '';
        }
    }

    protected function getCompanyName(Crawler $crawler): string
    {
        try {
            return $crawler->filter('.slt-logotype-title')->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getStreet(Crawler $crawler) : string
    {
        try {
            $fullAddress = $crawler->filter('h3 + address')->html();

            if(strpos($fullAddress, '<nobr>')){
                $fullAddress = explode('<nobr>', $fullAddress);
                $street = str_replace(',', '', $fullAddress[0]);
                if(!is_numeric($street)) {
                    return $street;
                }
            }
            return '';

        } catch (\Exception $e) {
            return '';
        }
    }

    public function getCity(Crawler $crawler) : string
    {
        try {
            return $crawler->filter('h3 + address > nobr')->eq(1)->text();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getPostal(Crawler $crawler) : string
    {
        try {
            $postal = str_replace(',', '', $crawler->filter('h3 + address > nobr')->eq(0)->text());
            if(is_numeric($postal)){
                return $postal;
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getPhone(Crawler $crawler) : string
    {
        try {
            return str_replace(['+', '/', ')', '(', "'", ' '], '', $crawler->filter('.slt-phone > article > a > i + header + strong')->text());
        } catch (\Exception $e) {
            return '';
        }
    }

    public function writeToFile(string $arr) : void
    {
        $stream = fopen('parsed1.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}