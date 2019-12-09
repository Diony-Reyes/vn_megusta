<?php 
    trait Clients {
        public function get_client_info($patient_id) {
            $result = $this->get_client($patient_id);
            return $result;
        }
    }
?>