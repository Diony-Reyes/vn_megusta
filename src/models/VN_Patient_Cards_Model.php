<?php
    require __DIR__ .'/../database/db.php';

    trait VN_Patient_Cards_Model {
        // connector 
        private function connectorDB() {
            $db = new db();
            return $db->connectionDB();
        }
        
        public function add_card($data){
            if ($this->db->insert("vn_patient_cards", $data)){
                return $this->db->insert_id();
            }
            return false;
        }

        public function get_cards($patient_id) {
            $connect = $this->connectorDB();
            $sql = "SELECT * FROM vn_patient_cards where patient_id = {$patient_id}";

            try {
                $result = $connect->query($sql);

                if($result->rowCount() > 0) {
                    return $cards = $result->fetchAll(PDO::FETCH_OBJ);
                } else {
                    return $msg = ["message"=>"El paciente con ID $patient_id no posee tarjetas"];
                }
            } catch(PDOException $e) {
                return $error = [
                    "error"=> ["text"=>$e->getMessage()]
                ];
            }
        }
        
        public function get_card($card_id) {
            $this->db->select('*');
            $this->db->from('vn_patient_cards');
            $this->db->where('id', $card_id);
            $query = $this->db->get();	
            $result = $query->row();
            return $result;
        }

        public function get_preferred($patient_id) {
            $connect = $this->connectorDB();
            $sql = "SELECT * FROM vn_patient_cards WHERE patient_id = {$patient_id} AND preferred = 1";

            try {
                $result = $connect->query($sql);

                if($result->rowCount() > 0) {
                    return $preferred_card = $result->fetchAll(PDO::FETCH_OBJ);
                } else {
                    return $msg = ["message"=>"El paciente con ID $patient_id no posee una tarjeta preferida"];
                }
            } catch(PDOException $e) {
                return $error = [
                    "error"=> ["text"=>$e->getMessage()]
                ];
            }
        }

        // public function get_preferred($patient_id) {
        //     $this->db->select('*');
        //     $this->db->from('vn_patient_cards');
        //     $this->db->where('patient_id', $patient_id);
        //     $this->db->where('preferred','1');
        //     $query = $this->db->get();	
        //     $result = $query->row();
        //     return $result;
        // }

        public function delete_card($patient_id, $card_id) {
            $this->db->where('id', $card_id);
            $this->db->where('patient_id', $patient_id);
            $this->db->delete('vn_patient_cards');
        }
        
        public function update_card($id, $data) {
            $this->db->where('id',$id);
            if($this->db->update('vn_patient_cards',$data)){
                return true;
            }
            return false;
        }

        public function update_massive_preferred_0($patient_id) {
            $this->db->query("UPDATE `vn_patient_cards` SET preferred = 0 WHERE patient_id = {$patient_id}");
        }
    }
?>