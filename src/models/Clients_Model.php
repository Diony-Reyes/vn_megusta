<?php 
    trait Clients_Model {
        public function get_client($id) {
            $connect = self::connectorDB();

            $sql = "SELECT firstName, lastName, email, phone
                    FROM cpnc_User
                    WHERE id = {$id}";
            $result = $connect->query($sql);

            return  $result->fetch(PDO::FETCH_LAZY);
        }
    }
?>