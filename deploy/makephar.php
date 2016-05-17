<?php

$progectRoot = realpath(getcwd().'/../');

ini_set('phar.readonly', 0);

$phar = new Phar('mmp.phar');
$phar->startBuffering();
$defaultStub = $phar->createDefaultStub('migration.php');

$phar->buildFromDirectory($progectRoot, '/\.php$/');

$phar->addFromString(
    $progectRoot.'/lib/helpController.class.php',
    str_replace('migration.php', 'mmp', file_get_contents($progectRoot.'/lib/helpController.class.php'))
);

$stub = "#!/usr/bin/php \n".$defaultStub;

$phar->setStub($stub);

$phar->stopBuffering();

copy('mmp.phar', 'mmp');

chmod('mmp', 0777);

