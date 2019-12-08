<?php 
    trait CustomHttp {
        public function jsonResponse($data) {
            header('Content-type: application/json');
            echo json_encode($data);
            exit(0);
        }
    }
?>