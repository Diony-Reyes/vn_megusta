<?php

define('MERCHANT_ID', 'visanetdr');
define('PROFILE_ID',  '1CC3C3AE-530B-42B0-8588-F2718FC3F6B1');
define('ACCESS_KEY',  '8c05763cd9e130ffb974c0ff93c8cc68');
define('SECRET_KEY',  '3dfd997608184f25ad2a3b735fb48ea0d8d19cc7a87d48a7bd884c1426a3b2681772eaa6c0db4ef0bd088067ada3c6a268831401b69f4649bc70cefa35af11980751f21f5c214d7180b3e6f98507bac9bc2a72d36d114f09898907d059b94c38973370d267cd4530a32acb9a1d426faa83de647068a3415b95712a624c1a0ff3');

// DF TEST: 1snn5n9w, LIVE: k8vif92e
define('DF_ORG_ID', '1snn5n9w');

// PAYMENT URL
define('CYBS_BASE_URL', 'https://testsecureacceptance.cybersource.com');

define('PAYMENT_URL', CYBS_BASE_URL . '/pay');
// define('PAYMENT_URL', '/sa-sop/debug.php');

define('TOKEN_CREATE_URL', CYBS_BASE_URL . '/token/create');
define('TOKEN_UPDATE_URL', CYBS_BASE_URL . '/token/update');

// EOF
