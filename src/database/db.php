<?php 
    trait db {
        private $dbHost = "10.0.0.80";
        private $dbUser = "josue";
        private $dbPassword = "Admintec001";
        private $dbName = "megusta_site";

        // connection
        public function connectionDB() {
            $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName";
            $dbConnection = new PDO($mysqlConnect, $this->dbUser, $this->dbPassword);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $dbConnection;
        }

        // connector 
        private function connectorDB() {
            return $this->connectionDB();
        }
    }
?>