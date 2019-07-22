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
            array('empty', 'asyncWithProfile', 'notAsyncWithProfile', 'asyncWithotProfile', 'notAsyncWithoutProfile')
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


        $file = 'web/Commands/'. $country .'/' . $name . '/' . $name . 'ParserCommand.php';
        $fileDir = 'web/Commands/'. $country .'/' . $name;

        if(!is_dir($fileDir)){
            mkdir('web/Commands/'. $country .'/' . $name);
        }

        if(!file_exists($file)){
            $fp = fopen($file, 'w+');

            fclose($fp);

            $output->writeln([
                'File was successfully created',
            ]);

        } else {
            $output->writeln([
                'This file has already been created.',
            ]);
        }



        $output->writeLn([

        ]);
    }



}