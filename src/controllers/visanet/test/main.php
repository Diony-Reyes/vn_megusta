<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 'On');
//require realpath(dirname( __FILE__ ) . '/../vendor/autoload.php');
require realpath(dirname( __FILE__ ) . '/../classes/CyberSource/CyberSource.php');
require realpath(dirname( __FILE__ ) . '/config.php');

$c = CyberSource\CyberSource::factory($merchant_id, $transaction_key, CyberSource\CyberSource::ENV_TEST);
$c->default_currency = 'DOP';
// $c->set_proxy($proxy);
// EOF