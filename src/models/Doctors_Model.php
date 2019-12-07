<?php 
    // require_once __DIR__.'/../database/db.php';
    
    class Doctors_Model {
        // connector 
        private function connectorDB() {
            $db = new db();
            return $db->connectionDB();
        }

        public function get_doctor_by_id($id) {
            $connect = $this->connectorDB();
            $sql = "SELECT CONCAT(`doctor_firstname`, ' ', `doctor_lastname`) as name FROM doctor where `id` like $id";

            try {
                $result = $connect->query($sql);

                if($result->rowCount() > 0) {
                    $doctor = $result->fetchAll(PDO::FETCH_OBJ);
                    echo json_encode($doctor);
                } else {
                    echo json_encode("No existe un doctor con el ID: $id");
                }
            } catch(PDOException $e) {
                echo '{
                        "error": {
                            "text":' .$e->getMessage().'
                    }';
            }
        }
    }
?>