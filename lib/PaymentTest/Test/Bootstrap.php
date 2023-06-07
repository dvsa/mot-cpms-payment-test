<?php
namespace PaymentCommon\Test;

use PaymentTest\Test\BootstrapTrait;

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('UTC');

/**
 * Test bootstrap, for setting up auto loading and paths
 */
class Bootstrap
{
    use BootstrapTrait;

}

$path = realpath(__DIR__ . '/../');

chdir(dirname($path));
Bootstrap::getInstance()->init($path);
