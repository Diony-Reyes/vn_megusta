<?php

define('MERCHANT_ID', 'visanetdr_000000430807001');
define('PROFILE_ID',  '47EC6C53-9B6F-47AA-89AA-70CE446D13AD');
define('ACCESS_KEY',  '8456a8f5ecb83ba4a25ec239d6ae0a71');
define('SECRET_KEY',  '7bf10c540f08438e8ec4f9e3d2959c8a9af5cb18e69c474c85be66a4fc4be4bb713257dd94c64f66b3e7a93401a719d6908bd73dc3cc45e1a2a97b36978c4244cfc05243c3424b529b326dedb6c5ecc192fae525d24143529907928d17fe1e836dddfde958dc47e889da876608861dafb89cef95133146fe87dec264283d605a');

// DF TEST: 1snn5n9w, LIVE: k8vif92e
define('DF_ORG_ID', '1snn5n9w');

// PAYMENT URL
define('CYBS_BASE_URL', 'https://testsecureacceptance.cybersource.com/silent');

define('PAYMENT_URL', CYBS_BASE_URL . '/pay');
// define('PAYMENT_URL', '/sa-sop/debug.php');

define('TOKEN_CREATE_URL', CYBS_BASE_URL . '/token/create');
define('TOKEN_UPDATE_URL', CYBS_BASE_URL . '/token/update');

// EOF
