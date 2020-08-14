<?php

define('BASEDIR', dirname(dirname(__FILE__)));
define('BUILDDIR', BASEDIR . '/tasks');

$exclude = [
	'build',
    '.gitignore',
    'composer.json',
    'composer.lock',
    'composer.phar',
    'README.md',
    '.git',
    '.idea',
	'tasks'
];

$filter = function ($file, $key, $iterator) use ($exclude) {
    if ($iterator->hasChildren() && !in_array($file->getFilename(), $exclude)) {
        return true;
    }
    return $file->isFile() && !in_array($file->getFilename(), $exclude);
};

$innerIterator = new RecursiveDirectoryIterator(BASEDIR, RecursiveDirectoryIterator::SKIP_DOTS);

$iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator($innerIterator, $filter));

$phar = new \Phar('phpandroid.phar', 0, 'phpandroid.phar');
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();
$phar->buildFromIterator($iterator, BASEDIR);
$phar->setStub(file_get_contents(BUILDDIR . '/stub.php'));
$phar->stopBuffering();