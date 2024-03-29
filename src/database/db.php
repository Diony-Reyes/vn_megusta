<?php 
    class db {
        private $dbHost = "localhost";
        private $dbUser = "root";
        private $dbPassword = false;
        private $dbName = "buscamed";

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