<?php 
    class db {
        private $dbHost = "localhost";
        private $dbUser = "root"; // "josue";
        private $dbPassword = false; //'Admintec001';
        private $dbName = "buscamed"; //"megusta_site";

        // connection
        public function connectionDB() {
            $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName";
            $dbConnection = new PDO($mysqlConnect, $this->dbUser, $this->dbPassword);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $dbConnection;
        }
    }
?>