<?php

trait VN_Patient_Transactions_Model {
    public function register_transaction($data){
        if ($this->db->insert("vn_patient_transactions", $data)){
			return $this->db->insert_id();
		}
        return false;
    }
    public function pay_appointment($ap_id){
		$this->db->where('id', $ap_id);
        $result = $this->db->update('appointment', [
			'is_paid' => 1
		]);

        if ($result) {
            return true;
        }
        
        return false;
    }
    public function pay_order($id, $appointment_id) {
        $this->db->where('patient_id', $id);
        $this->db->where('appointment_id', $appointment_id);
        $this->db->where('method_payment', null);
        $this->db->where('is_deleted', 0);
        if($this->db->update("order_patient_extra_payments", ["method_payment" => "card"])) {
            return [
                'status' => 'success',
                'orders' => $this->db->get_where("order_patient_extra_payments", array("patient_id"=>$id, "appointment_id"=>$appointment_id, "is_deleted"=>0))->result()
            ];
        } 
        return false;
    }
    
    public function get_transactions($patient_id) {
        $connect = $this->connectorDB();
        $sql = "SELECT * FROM vn_patient_transactions WHERE patient_id = {$patient_id}";

        try {
            $result = $connect->query($sql);

            if($result->rowCount() > 0) {
                return $transactions = $result->fetchAll(PDO::FETCH_OBJ);
            } else {
                return $msg = ["message"=>"El paciente con ID $patient_id no tiene transacciones registradas"];
            }
        } catch(PDOException $e) {
            return $error = [
                "error"=> ["text"=>$e->getMessage()]
            ];
        }
    }

    // public function get_transactions($patient_id) {
    //     $this->db->select('*');
	// 	$this->db->from('vn_patient_transactions');
	// 	$this->db->where('patient_id', $patient_id);
	// 	$query = $this->db->get();	
	// 	return $query->result();
	// }
    
    public function get_transaction($id) {
        $this->db->select('*');
		$this->db->from('vn_patient_transactions');
		$this->db->where('id', $id);
		$query = $this->db->get();	
		$result = $query->row();
		return $result;
	}

    public function get_transactions_by_doctor($doctor_id,$start = false, $amount = false, $columName, $orderDir, $search) {
        $today = date("Y-m-d", strtotime("today"));
        
        $this->db->select('vn_patient_transactions.*, patient.patient_firstname, patient.patient_lastname');
        $this->db->from('vn_patient_transactions');
        $this->db->join('patient', 'FIND_IN_SET(patient.id, vn_patient_transactions.patient_id) > 0', 'left');
        $this->db->where('doctor_id', $doctor_id);

        if(isset($search) && $search != '') {
            foreach(explode(' ', $search) as $key => $value) {
                $this->db->group_start()
                    ->where('CONCAT(patient.patient_firstname, patient.patient_lastname) like "%'.$value.'%"');
                    $this->db->or_group_start()
                        ->where('vn_patient_transactions.id like "%'.$value.'%"')
                    ->group_end();
                    $this->db->or_group_start()
                        ->where('vn_patient_transactions.appointment_id like "%'.$value.'%"')
                    ->group_end();
                    $this->db->or_group_start()
                        ->where('vn_patient_transactions.method_payment like "%'.$value.'%"')
                    ->group_end();
                    $this->db->or_group_start()
                        ->where('vn_patient_transactions.amount like "%'.$value.'%"')
                    ->group_end();
                    $this->db->or_group_start()
                        ->where('vn_patient_transactions.created_at like "%'.$value.'%"')
                    ->group_end();
                    $this->db->or_group_start()
                        ->where('vn_patient_transactions.created_at >= "'.$value.'" AND vn_patient_transactions.created_at <= "'.$today.'"')
                    ->group_end()
                ->group_end();
            }
        };

        if ($start !== false && $amount !== false) {
            $this->db->limit($amount, $start);
        }
        
        if ($start == 1) {
            $this->db->limit('*', $start);    
        }

        if($columName === "patient_name") {
            $this->db->order_by("patient.patient_firstname", $orderDir);
        } else if($columName === "method"){
            $this->db->order_by("method_payment", $orderDir);
        } else if($columName === "payment_date"){
            $this->db->order_by("created_at", $orderDir);
        } else {
            $this->db->order_by($columName, $orderDir);
        }

		$query = $this->db->get();	
		return $query->result();
	}

}