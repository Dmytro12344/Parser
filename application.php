<?php


require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \Commands\CreateStartCommand());


$application->add(new \Commands\Process\StartProcessCommand());
$application->add(new \Commands\Process\MainContentCommand());
$application->add(new \Commands\Process\ProfileProcessCommand());

$application->run();

