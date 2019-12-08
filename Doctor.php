<?php 
    class Doctor {
        function doctorName($doctor_id) {
            $sql = 'SELECT CONCAT(`doctor_firstname`, " ", `doctor_lastname`) as name FROM doctor where `id` like 1';
            return $this->db->getConnection()->exec($sql);
        }
    }
?>