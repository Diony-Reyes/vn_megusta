<?php 
    require_once __DIR__.'/../models/VN_Patient_Cards_Model.php';
    require_once __DIR__.'/../database/database.php';
    require_once __DIR__.'/../httpCustom.php';
    require_once 'visanet/Visanet.php';
    require_once 'visanet/Payments.php';

    class Webservice {
        use VN_Patient_Cards_Model;
        use CustomHttp;
        use Visanet;
        use Payments;
    }
?>