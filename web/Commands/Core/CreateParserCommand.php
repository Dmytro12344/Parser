<?php

namespace Commands\Core;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;


class CreateParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('create:new:parser')
            ->setDescription('Creates new parser')
            ->setHelp('This command create new parser');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        /** Set project type */
        $helperType = $this->getHelper('question');
        $questionType = new ChoiceQuestion(
            'Please, choice needed type of parser',
            array('empty',
                'async with profile',
                'not async with profile',
                'async without profile',
                'not async without profile')
        );
        $questionType->setErrorMessage('Type %s is invalid.');
        $type = $helperType->ask($input, $output, $questionType);


        /** Set project country */
        $helper = $this->getHelper('question');
        $questionCountry = new ChoiceQuestion(
            'Please, choice needed country of parser',
            array('CZ', 'PL', 'RO')
        );
        $questionCountry->setErrorMessage('Country %s is invalid.');
        $country = $helper->ask($input, $output, $questionCountry);


        /** Set project name */
        $questionName = new Question("Please enter the name of the Parser \n", '');
        $name = $helper->ask($input, $output, $questionName);


        $file = 'web/Commands/'. $country .'/' . $name . '/' . $this->convertType($type)['dir'] . '/' . $name . 'ParserCommand.php';
        $parserNameDir = 'web/Commands/'. $country .'/' . $name;
        $parserTypeDir = 'web/Commands/'. $country .'/' . $name . '/' . $this->convertType($type)['dir'];
        $this->dirControl($parserNameDir, $parserTypeDir);
        $this->fileControl($file, $type, $country, $name);
    }

    protected function fileControl(string $file, string $type, string $country, string $name) : void
    {
        if(!file_exists($file)){
            $fp = fopen($file, 'w+');
            fwrite($fp, $this->generatePHPFile($type, $country, $name));
            fclose($fp);
            echo 'File was successfully created';
        } else {
            echo 'This file has already been created.';
        }
    }

    protected function generatePHPFile(string $type, string $country, string $name) : string
    {
        if($this->convertType($type)['type'] === 'asyncWithoutProf'){
            return $this->asyncWithoutProfile($this->convertType($type)['dir'], $country, $name);
        }


        return 'Rejected';
    }

    protected function asyncWithoutProfile(string $type, string $country, string $name) : string
    {
        $file  = "<?php \n\n";
        $file .= "namespace Commands\{$country}\{$name}\{$type}; \n\n";
        $file .= "use Symfony\Component\Console\Command\Command;\n";
        $file .= "use Symfony\Component\Console\Input\InputInterface;\n";
        $file .= "use Symfony\Component\Console\Output\OutputInterface;\n";
        $file .= "use Symfony\Component\DomCrawler\Crawler;\n";
        $file .= "use Symfony\Component\Process\Exception\ProcessFailedException;\n";
        $file .= "use Symfony\Component\Process\Process;\n";
        $file .= "use Wraps\GuzzleWrap; \n\n";
        $file .= "class {$name}ParserCommand extends Command \n { \n";
        $file .= " /** \n* Command config \n*/ \n  protected function configure() : void \n { \n";



        return $file;
    }

    protected function dirControl(string $nameDir, string $typeDir) : void
    {
        if(!is_dir($nameDir)){
            mkdir($nameDir);
        }

        if(!is_dir($typeDir)){
            mkdir($typeDir);
        }
    }

    protected function convertType(string $type) : array
    {
        if($type === 'async without profile'){
            return ['dir' => 'async', 'type' => 'asyncWithoutProf'];
        }

        if($type === 'async with profile'){
            return ['dir' => 'async', 'type' => 'asyncWithProf'];
        }

        if($type === 'not async with profile'){
            return ['dir' => 'notAsync', 'type' => 'notAsyncWithProf'];
        }


        if($type === 'not async without profile'){
            return ['dir' => 'notAsync', 'type' => 'notAsyncWithoutProf'];
        }

        return ['dir' => '', 'type' => ''];
    }



}