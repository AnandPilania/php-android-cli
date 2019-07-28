
<?php

require_once __DIR__.'/vendor/autoload.php';
require __DIR__.'/GenerateCommand.php';

$app = new Symfony\Component\Console\Application('Console App', 'v1.0.0');
$app->add(new GenerateCommand());
$app->run();