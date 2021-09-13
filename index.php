<?php

if (php_sapi_name() !== "cli") {
	echo "PHP Android CLI can only run through CLI.";
	exit();
}

require_once __DIR__ . '/vendor/autoload.php';

$app = new Symfony\Component\Console\Application('Console App', 'v1.0.0');
$app->add(new \KSPEdu\PHPAndroidCli\GenerateCommand());
$app->run();
