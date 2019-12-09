<?php 
    trait Clients_Model {
        public function get_client($id) {
            $connect = self::connectorDB();

            $sql = "SELECT patient.id as id,patient.*,insurance_categories.insurance_name as insurance_title,visit_categories.reason as visit_title, 1 as user_type, grupos_sanguineos.name as blood_name, insurance.name as insurance_name 
                    FROM patient
                    LEFT JOIN insurance ON insurance.id = patient.insurance
                    LEFT JOIN insurance_categories ON FIND_IN_SET(insurance_categories.id, patient.insurance) > 0
                    LEFT JOIN grupos_sanguineos ON patient.grupo_sanguineo = grupos_sanguineos.id
                    LEFT JOIN visit_categories ON FIND_IN_SET(visit_categories.id, patient.visitation) > 0
                    WHERE patient.id = {$id}";
            $result = $connect->query($sql);

            return  $result->fetch(PDO::FETCH_LAZY);
        }
    }
?>