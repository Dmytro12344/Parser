<?php


require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \Commands\CreateStartCommand());
$application->add(new \Commands\StartProcessCommand());
$application->add(new \Commands\SecondStreamCommand());

$application->run();

