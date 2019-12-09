<?php 
    require_once __DIR__ .'/../database/db.php';
    require_once __DIR__.'/../models/VN_Patient_Cards_Model.php';
    require_once __DIR__.'/../models/VN_Patient_Transactions_Model.php';
    require_once __DIR__.'/../models/Doctors_Model.php';
    require_once __DIR__.'/../models/Clients_Model.php';
    require_once __DIR__.'/../database/database.php';
    require_once __DIR__.'/../httpCustom.php';
    require_once 'Clients.php';
    require_once 'visanet/Visanet.php';
    require_once 'visanet/Payments.php';

    class Webservice extends db {
        use VN_Patient_Cards_Model;
        use VN_Patient_Transactions_Model;
        use Doctors_Model;
        use Clients_Model;
        use CustomHttp;
        use Clients;
        use Visanet;
        use Payments;
    }
?>