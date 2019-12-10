<?php

define('MERCHANT_ID', 'visanetdr_000000431651001');
define('PROFILE_ID',  'B0397FE6-D1B7-4C00-BBE1-CAA86EA92566');
define('ACCESS_KEY',  'c542e1a45b0c33fc991190fd22d5fd79');
define('SECRET_KEY',  'a2f5de821ca849418cc151b5ee43fbddb1642ebd7c9c4bb2909452f5614aedaafa74a1b7abdd49ec824b22f1ce16aa5d4c6a5faa90254c479dd582dd62bc3d30a6e59f0351d949dc9eb05dcbee3e9dcb734884e2fc734a7787157e1306057f3bc8b31d267e5346139562ca503510faefbc5dea5ae11d48e598c891279772120e');

// DF TEST: 1snn5n9w, LIVE: k8vif92e
define('DF_ORG_ID', '1snn5n9w');

// PAYMENT URL
define('CYBS_BASE_URL', 'https://testsecureacceptance.cybersource.com/silent');

define('PAYMENT_URL', CYBS_BASE_URL . '/pay');
// define('PAYMENT_URL', '/sa-sop/debug.php');

define('TOKEN_CREATE_URL', CYBS_BASE_URL . '/token/create');
define('TOKEN_UPDATE_URL', CYBS_BASE_URL . '/token/update');

// EOF
