<?php 
    trait Doctors_Model {
        public function get_doctor_data($id) {
            $connect = $this->connectorDB();
            $sql = "SELECT * FROM doctor WHERE id = {$id}";

            try {
                $result = $connect->query($sql);

                if($result->rowCount() > 0) {
                    $doctor = $result->fetchAll(PDO::FETCH_OBJ);
                    return $doctor;
                } else {
                    echo json_encode("No existe un doctor con el ID: $id");
                    return $msg = ["message"=>"No existe un doctor con el ID: $id"];
                }
            } catch(PDOException $e) {
                return $error = [
                    "error"=> ["text"=>$e->getMessage()]
                ];
            }
        }
    }
?>